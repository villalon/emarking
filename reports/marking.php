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
 * @copyright 2014 Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
global $DB, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// URLs for current page.
$url = new moodle_url('/mod/emarking/reports/marking.php', array(
    'id' => $cm->id));
// Validate the user has grading capabilities.
if (! has_capability('mod/assign:grade', $context)) {
    print_error('No tiene permisos para ver reportes de notas');
}
// First check that the user is logged in.
require_login($course->id);
if (isguestuser()) {
    die();
}
// Page settings (URL, breadcrumbs and title).
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add(get_string('markingreport', 'mod_emarking'));
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
// Print eMarking tabs.
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "markingreport");
// Get rubric instance.
list($gradingmanager, $gradingmethod, $definition, $rubriccontroller) = emarking_validate_rubric($context, true, true);
// Calculates the number of criteria for this evaluation.
$numcriteria = count($definition->rubric_criteria);
$markingstats = $DB->get_record_sql(
        "
                SELECT  COUNT(distinct id) AS activities,
                COUNT(DISTINCT student) AS students,
                MAX(pages) AS maxpages,
                MIN(pages) AS minpages,
                ROUND(AVG(comments), 2) AS pctmarked,
                SUM(missing) as missing,
                SUM(submitted) as submitted,
                SUM(grading) as grading,
                SUM(graded) as graded,
                SUM(published) as published,
                SUM(regrading) as regrading
                FROM (
                SELECT  s.student,
                s.id as submissionid,
                CASE WHEN d.status < 10 THEN 1 ELSE 0 END AS missing,
                CASE WHEN d.status = 10 THEN 1 ELSE 0 END AS submitted,
                CASE WHEN d.status > 10 AND d.status < 20 AND COUNT(DISTINCT c.id) < $numcriteria THEN 1 ELSE 0 END AS grading,
                CASE WHEN d.status > 10 AND d.status < 20 AND COUNT(DISTINCT c.id) = $numcriteria THEN 1 ELSE 0 END AS graded,
                CASE WHEN d.status = 20 THEN 1 ELSE 0 END AS published,
                CASE WHEN d.status > 20 THEN 1 ELSE 0 END AS regrading,
                d.timemodified,
                d.grade,
                d.generalfeedback,
                count(distinct p.id) as pages,
                CASE WHEN 0 = $numcriteria THEN 0 ELSE COUNT(DISTINCT c.id) / $numcriteria END AS comments,
                COUNT(distinct r.id) as regrades,
                nm.course,
                nm.id,
                ROUND(SUM(l.score),2) AS score,
                ROUND(SUM(c.bonus),2) AS bonus,
                s.sort
                FROM {emarking}  nm
                INNER JOIN {emarking_submission}  s ON (nm.id = :emarkingid AND s.emarking = nm.id)
                INNER JOIN {emarking_page}  p ON (p.submission = s.id)
                INNER JOIN {emarking_draft}  d ON (d.submissionid = s.id AND d.qualitycontrol=0)
                LEFT JOIN {emarking_comment}  c on (c.page = p.id AND c.draft = d.id AND c.levelid > 0)
                LEFT JOIN {gradingform_rubric_levels}  l ON (c.levelid = l.id)
                LEFT JOIN {emarking_regrade}  r ON (r.draft = d.id AND r.criterion = l.criterionid AND r.accepted = 0)
                GROUP BY nm.id, s.student
) as T
                GROUP by id", array(
            'emarkingid' => $emarking->id));
if (! $markingstats) {
    echo $OUTPUT->notification(get_string('nosubmissionspublished', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}
$datatable = "['Status', 'Students'],
                ['" .
         emarking_get_string_for_status(EMARKING_STATUS_ABSENT) . "',          $markingstats->missing],
                ['" .
         emarking_get_string_for_status(EMARKING_STATUS_SUBMITTED) . "',               $markingstats->submitted],
                ['" .
         emarking_get_string_for_status(EMARKING_STATUS_GRADING) . "',         $markingstats->grading],
                ['" .
         get_string('statusgradingfinished', 'mod_emarking') . "',               $markingstats->graded],
                ['" .
         emarking_get_string_for_status(EMARKING_STATUS_PUBLISHED) . "',               $markingstats->published],
                ['" .
         emarking_get_string_for_status(EMARKING_STATUS_REGRADING) . "',               $markingstats->regrading],
                ";
$totalsubmissions = $markingstats->submitted + $markingstats->grading + $markingstats->graded + $markingstats->published + $markingstats->regrading;
$totalprogress = round($markingstats->graded / $totalsubmissions * 100, 2);
if ($numcriteria == 0 || $totalsubmissions == 0) {
    echo $OUTPUT->notification(get_string('nosubmissionsgraded', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}
$markingstatspercriterion = $DB->get_records_sql(
        "
                SELECT
                a.id,
                e.name,
                d.name,
                a.description,
                ROUND(AVG(bb.score),2) as avgscore,
                ROUND(STDDEV(bb.score),2) as stdevscore,
                ROUND(MIN(b.score),2) as minscore,
                ROUND(MAX(b.score),2) as maxscore,
                ROUND(AVG(ec.bonus),2) AS avgbonus,
                ROUND(STDDEV(ec.bonus),2) AS stdevbonus,
                ROUND(MAX(ec.bonus),2) AS maxbonus,
                ROUND(MIN(ec.bonus),2) AS minbonus,
                COUNT(distinct s.id) AS submissions,
                COUNT(distinct ec.id) AS comments,
                COUNT(distinct r.id) AS regrades
                FROM {course_modules}  c
                INNER JOIN {context}  mc ON (c.id = :cmid AND c.id = mc.instanceid)
                INNER JOIN {grading_areas}  ar ON (mc.id = ar.contextid)
                INNER JOIN {grading_definitions}  d ON (ar.id = d.areaid)
                INNER JOIN {gradingform_rubric_criteria}  a ON (d.id = a.definitionid)
                INNER JOIN {gradingform_rubric_levels}  b ON (a.id = b.criterionid)
                INNER JOIN {emarking}  e ON (e.id = c.instance)
                INNER JOIN {emarking_submission}  s ON (s.emarking = e.id)
                INNER JOIN {emarking_draft}  dr ON (dr.submissionid = s.id AND dr.qualitycontrol=0)
                INNER JOIN {emarking_page}  p ON (p.submission = s.id)
                LEFT JOIN {emarking_comment}  ec on (ec.page = p.id AND ec.draft = dr.id AND ec.levelid = b.id)
                LEFT JOIN {emarking_regrade}  r ON (r.draft = d.id AND r.criterion = a.id)
                LEFT JOIN {gradingform_rubric_levels}  bb ON (a.id = bb.criterionid AND ec.levelid = bb.id)
                WHERE dr.status >= 10
                GROUP BY a.id
                ORDER BY a.sortorder
                ", array(
            'cmid' => $cm->id));
$datatablecriteria = "['Criterio', 'Corregido', 'Por recorregir', 'Por corregir'],";
foreach ($markingstatspercriterion as $statpercriterion) {
    $description = trim(preg_replace('/\s\s+/', ' ', $statpercriterion->description));
    $description = preg_replace("/\r?\n/", "\\n", addslashes($description));
    $datatablecriteria .= "['$description', " . ($statpercriterion->comments - $statpercriterion->regrades) .
             ", $statpercriterion->regrades, " . ($statpercriterion->submissions - $statpercriterion->comments) . "],";
}
$markingstatstotalcontribution = $DB->get_records_sql(
        "
                SELECT
                ec.markerid,
                CONCAT(u.firstname , ' ', u.lastname) AS markername,
                COUNT(distinct ec.id) AS comments
                FROM {emarking}  e
                INNER JOIN {emarking_submission}  s ON (e.id = :emarkingid AND s.emarking = e.id)
                INNER JOIN {emarking_draft}  d ON (d.submissionid = s.id AND d.qualitycontrol=0)
                INNER JOIN {emarking_page}  p ON (p.submission = s.id)
                INNER JOIN {emarking_comment}  ec on (ec.page = p.id AND ec.draft = d.id AND ec.levelid > 0)
                INNER JOIN {user}  u ON (ec.markerid = u.id)
                WHERE d.status >= 10
                GROUP BY ec.markerid
                ", array(
            'emarkingid' => $emarking->id));
$datatabletotalcontribution = "['Status', 'Comments'],";
$totalcomments = 0;
foreach ($markingstatstotalcontribution as $contribution) {
    $datatabletotalcontribution .= "['$contribution->markername',   $contribution->comments],";
    $totalcomments += $contribution->comments;
}
$datatabletotalcontribution .= "['Por corregir', " . ($totalsubmissions * $numcriteria - $totalcomments) . "],";
$markingstatspermarker = $DB->get_recordset_sql(
        "
                SELECT
                a.id,
                a.description,
                T.*
                FROM {course_modules}  c
                INNER JOIN {context}  mc ON (c.id = :cmid AND c.id = mc.instanceid)
                INNER JOIN {grading_areas}  ar ON (mc.id = ar.contextid)
                INNER JOIN {grading_definitions}  d ON (ar.id = d.areaid)
                INNER JOIN {gradingform_rubric_criteria}  a ON (d.id = a.definitionid)
                INNER JOIN {emarking_marker_criterion}  emc ON (emc.emarking = c.instance)
                INNER JOIN (
                    SELECT bb.criterionid,
                    ec.markerid,
                    u.lastname AS markername,
                    ROUND(AVG(bb.score),2) as avgscore,
                    ROUND(STDDEV(bb.score),2) as stdevscore,
                    ROUND(MIN(bb.score),2) as minscore,
                    ROUND(MAX(bb.score),2) as maxscore,
                    ROUND(AVG(ec.bonus),2) AS avgbonus,
                    ROUND(STDDEV(ec.bonus),2) AS stdevbonus,
                    ROUND(MAX(ec.bonus),2) AS maxbonus,
                    ROUND(MIN(ec.bonus),2) AS minbonus,
                    COUNT(distinct ec.id) AS comments,
                    COUNT(distinct r.id) AS regrades
                    FROM {emarking}  e
                    INNER JOIN {emarking_submission}  s ON (e.id = :emarkingid AND s.emarking = e.id)
                    INNER JOIN {emarking_draft}  dr ON (dr.submissionid = s.id AND dr.qualitycontrol=0)
                    INNER JOIN {emarking_page}  p ON (p.submission = s.id)
                    LEFT JOIN {emarking_comment}  ec on (ec.page = p.id AND ec.draft = dr.id)
                    LEFT JOIN {gradingform_rubric_levels}  bb ON (ec.levelid = bb.id)
                    LEFT JOIN {emarking_regrade}  r ON (r.draft = s.id AND r.criterion = bb.criterionid)
                    LEFT JOIN {user}  u ON (ec.markerid = u.id)
                    WHERE dr.status >= 10
                    GROUP BY ec.markerid, bb.criterionid) AS T
                ON (a.id = T.criterionid AND emc.marker = T.markerid)
                GROUP BY T.markerid, a.id
                ", array(
            'cmid' => $cm->id,
            'emarkingid' => $emarking->id));
$datamarkersavailable = false;
$datatablemarkers = "";
$datatablecontribution = "['".get_string('marker', 'mod_emarking')."', 'Corregido', 'Por recorregir', 'Por corregir'],";
foreach ($markingstatspermarker as $permarker) {
    $description = trim(preg_replace('/\s\s+/', ' ', $permarker->description));
    $datatablemarkers .= "['$permarker->markername $description',
        " . ($permarker->minscore) . ",
        " . ($permarker->avgscore - $permarker->stdevscore) . ",
        " . ($permarker->avgscore + $permarker->stdevscore) . ",
        " . ($permarker->maxscore) . ",
        ],";
    $datatablecontribution .= "['$permarker->markername $description',
        " . ($permarker->comments - $permarker->regrades) . ",
        " . ($permarker->regrades) . ",
        " . ($totalsubmissions - $permarker->comments) . "
        ],";
    $datamarkersavailable = true;
}
$progress = round($totalcomments / ($totalsubmissions * $numcriteria) * 100, 2);
?>
<?php echo $OUTPUT->heading(get_string('markingstatusincludingabsents', 'mod_emarking'), 3, 'charttitle'); ?>
<div id="statusdonut" style="width: 100%; text-align:center;"><?php echo $OUTPUT->pix_icon('i/loading', '')?></div>
<?php echo $OUTPUT->heading(get_string('permarkercontribution', 'mod_emarking'), 3, 'charttitle'); ?>
<div id="contributiondonut" style="width: 100%; text-align:center;"><?php echo $OUTPUT->pix_icon('i/loading', '')?></div>
<?php echo $OUTPUT->heading(get_string('statuspercriterion', 'mod_emarking'), 3, 'charttitle'); ?>
<div id="criteriabarchart" style="width: 100%; text-align:center;"><?php echo $OUTPUT->pix_icon('i/loading', '')?></div>
<?php
if ($datamarkersavailable) { //           title: 'Puntajes asignados por ayudante',
    echo $OUTPUT->heading(get_string('permarkercontribution', 'mod_emarking'), 3, 'charttitle'); ?>
<div id="markerscontribution" style="width: 100%; height: 500px;"></div>
<?php echo $OUTPUT->heading(get_string('permarkerscores', 'mod_emarking'), 3, 'charttitle'); ?>
<div id="markerscandle" style="width: 100%; height: 500px;"></div>
<?php
}
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
          // TODO: Show friendly message when we couldn't load Google's library.
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawStatusDonut);
      google.setOnLoadCallback(drawContributionDonut);
      google.setOnLoadCallback(drawCriteriaBarChart);
<?php
if ($datamarkersavailable) {
?>
      google.setOnLoadCallback(drawMarkersCandlestick);
      google.setOnLoadCallback(drawMarkersBarChart);
<?php
}
?>
        function drawStatusDonut() {
                var data = google.visualization.arrayToDataTable(
                                [
                                <?php echo $datatable; ?>
                        ]);
                var options = {
                        pieHole: 0,
                        legend: {position: 'bottom'},
                        'height': 300
                    };
                var chart = new google.visualization.PieChart(document.getElementById('statusdonut'));
                chart.draw(data, options);
    }
        function drawContributionDonut() {
                var data = google.visualization.arrayToDataTable(
                                [
                                <?php echo $datatabletotalcontribution; ?>
                        ]);
                var options = {
                        pieHole: 0,
                        legend: {position: 'bottom'},
                        'height': 300
                    };
                var chart = new google.visualization.PieChart(document.getElementById('contributiondonut'));
                chart.draw(data, options);
    }
    function drawCriteriaBarChart() {
                var data = google.visualization.arrayToDataTable(
                                [
                                <?php echo $datatablecriteria; ?>
                        ]);
                var options = {
                    legend: { position: 'top', maxLines: 3 },
                    bar: { groupWidth: '75%' },
                    isStacked: true,
                    'height': <?php echo $numcriteria * 50 ?>,
                };
                var chart = new google.visualization.BarChart(document.getElementById('criteriabarchart'));
                chart.draw(data, options);
        }
<?php
if ($datamarkersavailable) {
?>
        function drawMarkersCandlestick() {
        var data = google.visualization.arrayToDataTable([
          <?php echo $datatablemarkers; ?>
        ], true);
        var options = {
          tooltip: { trigger: 'selection' },
          legend:'none'
        };
        var chart = new google.visualization.CandlestickChart(document.getElementById('markerscandle'));
        chart.draw(data, options);
      }
        function drawMarkersBarChart() {
                var data = google.visualization.arrayToDataTable(
                                [
                                <?php echo $datatablecontribution; ?>
                        ]);
                var options = {
                        title: 'Avance por ayudante',
                    legend: { position: 'top', maxLines: 3 },
                    bar: { groupWidth: '75%' },
                    isStacked: true,
                };
                var chart = new google.visualization.ColumnChart(document.getElementById('markerscontribution'));
                chart.draw(data, options);
        }
<?php
}
?>
$(window).resize(function(){
    drawStatusDonut();
    drawContributionDonut();
    drawCriteriaBarChart();
<?php
if ($datamarkersavailable) {
?>
drawMarkersCandlestick();
drawMarkersBarChart();
<?php } ?>
});
</script>
<?php
echo $OUTPUT->footer();