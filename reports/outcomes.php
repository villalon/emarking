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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once('forms/gradereport_form.php');
global $DB, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// URLs for current page.
$url = new moodle_url('/mod/emarking/reports/outcomes.php', array(
    'id' => $cm->id));
// First check that the user is logged in.
require_login($course->id);
if (isguestuser()) {
    die();
}
// Validate the user has grading capabilities.
require_capability('mod/emarking:grade', $context);
// Page settings (URL, breadcrumbs and title).
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add(get_string('gradereport', 'grades'));
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
// Print eMarking tabs.
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "outcomesreport");
list($gradingmanager, $gradingmethod, $definition, $rubriccontroller) = emarking_validate_rubric($context);
$totalsubmissions = $DB->count_records_sql(
        "
                SELECT COUNT(DISTINCT s.id) AS total
                FROM {emarking_submission} s
                INNER JOIN {emarking_draft} d
                    ON (s.emarking = :emarking AND d.status >= " .
                 EMARKING_STATUS_PUBLISHED . " AND d.submissionid = s.id AND d.grade > 0 AND d.qualitycontrol=0)
                ", array(
                    'emarking' => $emarking->id));
if (! $totalsubmissions || $totalsubmissions == 0) {
    echo $OUTPUT->notification(get_string('nosubmissionspublished', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}
$emarkingids = '' . $emarking->id;
$extracategory = optional_param('categories', 0, PARAM_INT);
$parallels = emarking_get_parallel_courses($course);
$emarkingsform = new emarking_gradereport_form(null,
        array(
            'course' => $course,
            'cm' => $cm,
            'parallels' => $parallels,
            'id' => $emarkingids));
$emarkingsform->display();
$totalemarkings = 1;
if ($parallels && count($parallels) > 0) {
    foreach ($parallels as $pcourse) {
        $assid = '';
        if ($emarkingsform->get_data() && property_exists($emarkingsform->get_data(), "emarkingid_$pcourse->id")) {
            $varname = 'emarkingid_' . $pcourse . '->id';
            $assid = $emarkingsform->get_data()->$varname;
            if ($assid > 0) {
                $emarkingids .= ',' . $assid;
                $totalemarkings ++;
            }
        }
    }
}
$scales = $DB->get_records_sql(
        "
    SELECT s.*
    FROM {scale} s
    INNER JOIN {grade_outcomes} go ON (s.id = go.scaleid)
    INNER JOIN {emarking_outcomes_criteria} eoc ON (eoc.outcome = go.id AND eoc.emarking IN ($emarkingids))
    GROUP BY s.id");
$scaleslevels = array();
foreach ($scales as $scale) {
    if (isset($scaleslevels [$scale->id])) {
        continue;
    }
    $levels = explode(",", $scale->scale);
    for ($i = 0; $i < count($levels); $i ++) {
        $levels [$i] = trim($levels [$i]);
    }
    $scaleslevels [$scale->id] = $levels;
}
$totalscales = count($scaleslevels);
if (! $totalscales || $totalscales == 0) {
    echo $OUTPUT->notification(get_string('outcomesnotconfigured', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
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
                FROM {emarking_submission} s
                INNER JOIN {emarking_draft} d ON (s.emarking IN (' .
         $emarkingids .
         ') AND d.submissionid = s.id AND d.status >= 20 AND d.qualitycontrol = 0)
                INNER JOIN {emarking_comment} ec ON (ec.draft = d.id)
                INNER JOIN {gradingform_rubric_levels} l ON (ec.levelid = l.id)
                INNER JOIN {gradingform_rubric_criteria} a ON (l.criterionid = a.id)
                INNER JOIN (
                                SELECT
                                s.id AS emarkingid,
                                a.id AS criterionid,
                                MAX(l.score) AS maxscore,
                                MIN(l.score) AS minscore
                                FROM {emarking} s
                                INNER JOIN {grading_definitions} d ON (d.id = :definitionid AND s.id IN (' .
         $emarkingids . '))
                                INNER JOIN {gradingform_rubric_criteria} a ON (d.id = a.definitionid)
                                INNER JOIN {gradingform_rubric_levels} l ON (a.id = l.criterionid)
                                GROUP BY s.id, criterionid) AS T
                      ON (s.emarking = T.emarkingid AND T.criterionid = a.id)
                INNER JOIN {emarking} sg ON (s.emarking = sg.id)
                INNER JOIN {course} co ON (sg.course = co.id)
                INNER JOIN {emarking_outcomes_criteria} eoc ON (eoc.criterion = a.id AND eoc.emarking = sg.id)
                INNER JOIN {grade_outcomes} go ON (go.id = eoc.outcome)
                INNER JOIN {scale} sc ON (go.scaleid = sc.id)
			GROUP BY s.emarking,d.id,go.id) AS G
GROUP BY G.emarkingid,G.id,G.level';
$outcomestats = $DB->get_recordset_sql($sqlcriteria, array(
    'definitionid' => $definition->id));
$dataoutcomes = array();
$lastoutcomeid = 0;
$totalstudents = array();
foreach ($outcomestats as $outcome) {
    $lastoutcomeid = $outcome->id;
    $thislevels = $scaleslevels [$outcome->scaleid];
    if (! isset($dataoutcomes [$outcome->id])) {
        $dataoutcomes [$outcome->id] = array();
        $dataoutcomes [$outcome->id] ["title"] = $outcome->shortname;
        for ($i = 0; $i < count($thislevels); $i ++) {
            $dataoutcomes [$outcome->id] [ $thislevels[$i]] = 0;
        }
        $totalstudents [$outcome->id] = 0;
    }
    $dataoutcomes [$outcome->id] [$outcome->level] = $outcome->students;
    $totalstudents [$outcome->id] += $outcome->students;
}
$headers = array();
foreach ($scaleslevels as $level) {
    $headers = $level;
    $headers = array_merge(array(
        "Title"), $headers);
}
$data = array();
$jsons = array();
$json = '';
foreach ($dataoutcomes as $outcomeid => $outcomedata) {
    foreach ($outcomedata as $k => $v) {
        if ($k === "title") {
            $outcomedata [$k] = $v;
            $json = "[ ['Scale', '$v'], ";
        } else {
            $outcomedata [$k] = $totalstudents[$outcomeid] > 0 ? round($v / $totalstudents[$outcomeid] * 100, 1) . "%" : 0;
            $json .= " ['$k', " . ($totalstudents[$outcomeid] > 0 ? ($v / $totalstudents[$outcomeid]) : 0) . "], ";
        }
    }
    $data [] = $outcomedata;
    $json .= ']';
    $jsons[] = $json;
}
$table = new html_table();
$table->attributes ['style'] = "width: 100%; text-align:center; font-size:12px;";
$table->head = $headers;
$table->align = array(
    'left',
    'center',
    'center',
    'center',
    'center',
    'center');
$table->data = $data;
echo $OUTPUT->box_start(null, null, array(
    'style' => 'overflow:scroll'));
echo html_writer::table($table);
echo $OUTPUT->box_end();
$height = (count($data) * 150);
for($i = 1; $i <= count($jsons); $i++) { ?>
<div id="chart_criteria<?php echo $i?>" style="width: 100%; height: <?php echo $height ?>px;"></div>
<?php
} ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
          // TODO: Show friendly message when we couldn't load Google's library.
      google.load("visualization", "1", {packages:["corechart"]});
      <?php for ($j = 1; $j <= count($jsons); $j++) { ?>
      google.setOnLoadCallback(drawCriteria<?php echo $j?>);
      // Per criteria effectiveness.
        function drawCriteria<?php echo $j?>() {
          var data = google.visualization.arrayToDataTable(<?php echo $jsons[$j-1] ?>);
          var formatter = new google.visualization.NumberFormat({pattern: '#,###.##%'});
          var options = {
            title: '<?php echo get_string('studentachievement', 'mod_emarking') ?>',
            xAxis: {title: '<?php echo get_string('level', 'mod_emarking') ?>', titleTextStyle: {color: 'black'}},
            vAxis: {format:'#,###%', minValue:0, maxValue:1},
                legend: 'top'
          };
          var chart = new google.visualization.ColumnChart(document.getElementById('chart_criteria<?php echo $j?>'));
          chart.draw(data, options);
        }
      <?php } ?>
    </script>
<?php
echo $OUTPUT->footer();