<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ('forms/gradereport_form.php');

global $DB, $USER;

// Get course module id
$cmid = required_param('id', PARAM_INT);

// Validate course module
if (! $cm = get_coursemodule_from_id('emarking', $cmid)) {
    print_error('Módulo inválido');
}

// Validate module
if (! $emarking = $DB->get_record('emarking', array(
    'id' => $cm->instance
))) {
    print_error('Prueba inválida');
}

// Validate course
if (! $course = $DB->get_record('course', array(
    'id' => $emarking->course
))) {
    print_error('Curso inválido');
}

// URLs for current page
$url = new moodle_url('/mod/emarking/reports/outcomes.php', array(
    'id' => $cm->id
));

// Course context is used in reports
$context = context_module::instance($cm->id);

// Validate the user has grading capabilities
require_capability('mod/emarking:grade', $context);

// First check that the user is logged in
require_login($course->id);
if (isguestuser()) {
    die();
}

// Page settings (URL, breadcrumbs and title)
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->navbar->add(get_string('gradereport', 'grades'));

echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);

// Print eMarking tabs
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "outcomesreport");

list ($gradingmanager, $gradingmethod, $definition, $rubriccontroller) = emarking_validate_rubric($context);

$totalsubmissions = $DB->count_records_sql("
                SELECT COUNT(DISTINCT s.id) AS total 
                FROM {emarking_submission} AS s
                INNER JOIN {emarking_draft} AS d 
                    ON (s.emarking = :emarking AND d.status >= " . EMARKING_STATUS_PUBLISHED . " AND d.submissionid = s.id AND d.grade > 0 AND d.qualitycontrol=0)
                ", array(
    'emarking' => $emarking->id
));

if (! $totalsubmissions || $totalsubmissions == 0) {
    echo $OUTPUT->notification(get_string('nosubmissionspublished', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}

$emarkingids = '' . $emarking->id;

$extracategory = optional_param('categories', 0, PARAM_INT);

$parallels = emarking_get_parallel_courses($course);

$emarkingsform = new emarking_gradereport_form(null, array(
    'course' => $course,
    'cm' => $cm,
    'parallels' => $parallels,
    'id' => $emarkingids
));

$emarkingsform->display();

$totalemarkings = 1;
if ($parallels && count($parallels) > 0) {
    foreach ($parallels as $pcourse) {
        $assid = '';
        if ($emarkingsform->get_data() && property_exists($emarkingsform->get_data(), "emarkingid_$pcourse->id")) {
            eval("\$assid = \$emarkingsform->get_data()->emarkingid_$pcourse->id;");
            if ($assid > 0) {
                $emarkingids .= ',' . $assid;
                $totalemarkings ++;
            }
        }
    }
}

$scales = $DB->get_records_sql("
    SELECT s.*
    FROM {scale} AS s
    INNER JOIN {grade_outcomes} AS go ON (s.id = go.scaleid)
    INNER JOIN {emarking_outcomes_criteria} AS eoc ON (eoc.outcome = go.id AND eoc.emarking IN ($emarkingids))
    GROUP BY s.id");

$scaleslevels = array();
foreach($scales as $scale) {
    if(isset($scaleslevels[$scale->id]))
        continue;
    $levels = explode(",", $scale->scale);
    for($i=0;$i<count($levels);$i++)
        $levels[$i] = trim($levels[$i]);
    $scaleslevels[$scale->id] = $levels;
}

$sqlcriteria = '
SELECT id,
	shortname,
    emarkingid,
    level,
    courseid,
    COUNT(DISTINCT studentid) AS students,
    scaleid
FROM (
SELECT
                go.id,
                go.shortname,
                s.emarking AS emarkingid,
                sg.course AS courseid,
                s.student AS studentid,
                CASE WHEN (SUM(l.score)/SUM(T.maxscore) * 100) <= 25 THEN \'Beginning\'
					WHEN (SUM(l.score)/SUM(T.maxscore) * 100) <= 50 THEN \'Development\'
                    WHEN (SUM(l.score)/SUM(T.maxscore) * 100) <= 75 THEN \'Proficient\'
                    ELSE \'Mastery\' END AS level,
                SUM(T.maxscore) AS maxoutcomescore,
                SUM(T.minscore) AS minoutcomescore,
                sc.id AS scaleid
                FROM mdl_emarking_submission AS s
                INNER JOIN mdl_emarking_draft AS d ON (s.emarking IN (' . $emarkingids . ') AND d.submissionid = s.id AND d.status >= 20 AND d.qualitycontrol = 0)
                INNER JOIN mdl_emarking_comment AS ec ON (ec.draft = d.id)
                INNER JOIN mdl_gradingform_rubric_levels AS l ON (ec.levelid = l.id)
                INNER JOIN mdl_gradingform_rubric_criteria AS a ON (l.criterionid = a.id)
                INNER JOIN (
                                SELECT
                                s.id AS emarkingid,
                                a.id AS criterionid,
                                MAX(l.score) AS maxscore,
                                MIN(l.score) AS minscore
                                FROM mdl_emarking AS s
                                INNER JOIN mdl_grading_definitions AS d ON (d.id = :definitionid AND s.id IN (' . $emarkingids . '))
                                INNER JOIN mdl_gradingform_rubric_criteria AS a ON (d.id = a.definitionid)
                                INNER JOIN mdl_gradingform_rubric_levels AS l ON (a.id = l.criterionid)
                                GROUP BY s.id, criterionid) AS T
                      ON (s.emarking = T.emarkingid AND T.criterionid = a.id)
                INNER JOIN mdl_emarking AS sg ON (s.emarking = sg.id)
                INNER JOIN mdl_course AS co ON (sg.course = co.id)
                INNER JOIN mdl_emarking_outcomes_criteria AS eoc ON (eoc.criterion = a.id AND eoc.emarking = sg.id)
                INNER JOIN mdl_grade_outcomes AS go ON (go.id = eoc.outcome)
                INNER JOIN mdl_scale AS sc ON (go.scaleid = sc.id)
			GROUP BY s.emarking,d.id,go.id) AS G
GROUP BY G.emarkingid,G.id,G.level';

$criteriastats = $DB->get_recordset_sql($sqlcriteria, array(
    'definitionid' => $definition->id
));

$datascales = array();
$lastscaleid = 0;
$totalstudents = 0;
foreach ($criteriastats as $stat) {
    $lastscaleid = $stat->scaleid;
    if(!isset($datascales[$stat->scaleid])) {
        $datascales[$stat->scaleid] = array();
        $datascales[$stat->scaleid]["title"] = $stat->shortname;
        for($i=0;$i<count($scaleslevels[$stat->scaleid]);$i++) {
            $datascales[$stat->scaleid][$scaleslevels[$stat->scaleid][$i]] = 0;
        }
    }
    $datascales[$stat->scaleid][$stat->level] = $stat->students;
    $totalstudents += $stat->students;
}

$headers = array();
foreach($scaleslevels as $level) {
    $headers = $level;
    $headers = array_merge(array("Title"), $headers);
}
$data = array();
$json = '[';
foreach($datascales as $scaleid => $scaledata) {
    foreach($scaledata as $k => $v) {
        if($k === "title") {
            $scaledata[$k] = $v;
            $json .= " ['Scale', '$v'], ";
        }
        else {
            $scaledata[$k] = $totalstudents > 0 ? round($v / $totalstudents * 100, 1) . "%" : 0;
            $json .= " ['$k', " . ($totalstudents > 0 ? ($v / $totalstudents) : 0) . "], ";
        }
    }
    $data[] = $scaledata;
}
$json .= "]";

$table = new html_table();
$table->attributes['style'] = "width: 100%; text-align:center; font-size:12px;";
$table->head = $headers;
$table->align = array(
    'left',
    'center',
    'center',
    'center',
    'center',
    'center'
);
$table->data = $data;
echo $OUTPUT->box_start(null, null, array(
    'style' => 'overflow:scroll'
));
echo html_writer::table($table);
echo $OUTPUT->box_end();

$height = (count($data) * 150);

?>
<div id="chart_criteria" style="width: 100%; height: <?php echo $height ?>px;"></div>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
          // TODO: Show friendly message when we couldn't load Google's library
      google.load("visualization", "1", {packages:["corechart"]});
      
      google.setOnLoadCallback(drawCriteria);

        // Per criteria effectiveness
        function drawCriteria() {
          var data = google.visualization.arrayToDataTable(<?php echo $json ?>);

          var formatter = new google.visualization.NumberFormat({pattern: '#,###.##%'});
          
          <?php for($i=1; $i<=$totalemarkings; $i++) { ?>
                formatter.format(data, <?php echo $i?>); // Apply formatter to second column
          <?php } ?>
          
          var options = {
            title: '<?php echo get_string('studentachievement', 'mod_emarking') ?>',
            vAxis: {title: '<?php echo get_string('level', 'mod_emarking') ?>', titleTextStyle: {color: 'black'}},
            hAxis: {format:'#,###%', minValue:0, maxValue:1},
                legend: 'top'
          };

          var chart = new google.visualization.BarChart(document.getElementById('chart_criteria'));
          chart.draw(data, options);
        }
    </script>
<?php 

echo $OUTPUT->footer();