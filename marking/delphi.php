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
 * @copyright 2015 Francisco García <frgarcia@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once($CFG->dirroot . "/mod/emarking/marking/locallib.php");
require_once($CFG->dirroot . '/lib/excellib.class.php');
global $CFG, $DB, $OUTPUT, $PAGE;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// Check that user is logued in the course.
require_login();
if (isguestuser()) {
    die();
}
$exportcsv = optional_param('exportcsv', null, PARAM_ALPHA);
// Get the course module for the emarking, to build the emarking url.
$urlemarking = new moodle_url('/mod/emarking/marking/delphi.php', array(
    'id' => $cm->id));
// Check if user has an editingteacher role.
$issupervisor = has_capability('mod/emarking:supervisegrading', $context);
$usercangrade = has_capability('mod/assign:grade', $context);
// Download Excel if it is the case.
if ($exportcsv && $usercangrade && $issupervisor) {
    if ($exportcsv === 'delphi') {
        emarking_download_excel_markers_training($emarking);
    } else if ($exportcsv === 'agreement') {
        emarking_download_excel_markers_agreement($cm, $emarking);
    }
    die();
}
// Get rubric instance.
list($gradingmanager, $gradingmethod, $definition) = emarking_validate_rubric($context, true);
// Page navigation and URL settings.
$PAGE->set_url($urlemarking);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$markerid = 0;
$filter = "";
$sqlagreement = "
SELECT
    submission,
	student,
    criterionid,
    description,
    GROUP_CONCAT(levelid SEPARATOR '-') AS levels,
    GROUP_CONCAT(count SEPARATOR '-') AS counts,
    GROUP_CONCAT(markercount SEPARATOR '-') AS markercounts,
    GROUP_CONCAT(markers SEPARATOR '-') AS markers,
    GROUP_CONCAT(drafts SEPARATOR '#') AS drafts,
    GROUP_CONCAT(teachers SEPARATOR '#') AS teachers,
    GROUP_CONCAT(comments SEPARATOR '#') AS comments,
    MAX(count) / SUM(count) AS agreement
    FROM (
	SELECT
	a.id AS criterionid,
    a.description,
    a.sortorder,
	b.id AS levelid,
    b.definition,
    es.student,
	IFNULL(MARK.count, 0) AS count,
    IFNULL(MARK.markercount, 0) AS markercount,
    IFNULL(MARK.markers, '') AS markers,
    IFNULL(MARK.drafts, '') AS drafts,
    IFNULL(MARK.teachers, '') AS teachers,
    IFNULL(MARK.comments, '') AS comments,
    es.id AS submission
		FROM mdl_course_modules AS c
		INNER JOIN {context} AS mc ON (c.id = ? AND mc.contextlevel = 70 AND c.id = mc.instanceid)
		INNER JOIN {grading_areas} AS ar ON (mc.id = ar.contextid)
		INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
		INNER JOIN {gradingform_rubric_criteria} AS a ON (d.id = a.definitionid)
		INNER JOIN {gradingform_rubric_levels} AS b ON (a.id = b.criterionid)
        INNER JOIN {emarking_submission} AS es ON (es.emarking = c.instance)
	LEFT JOIN (
		SELECT
		GROUP_CONCAT(ed.id SEPARATOR '#') AS drafts,
		GROUP_CONCAT(ed.teacher SEPARATOR '#') AS teachers,
		GROUP_CONCAT(ec.id SEPARATOR '#') AS comments,
		es.student,
		ec.criterionid,
		ec.levelid,
		COUNT(DISTINCT ec.markerid) AS count,
        CASE WHEN GROUP_CONCAT(ec.markerid SEPARATOR '#') LIKE CONCAT('%$markerid%') THEN 1 ELSE 0 END AS markercount,
        GROUP_CONCAT(ec.markerid SEPARATOR '#') AS markers
		FROM {emarking_submission} AS es
        INNER JOIN {emarking_draft} AS ed ON (ed.emarkingid = ? AND es.id = ed.submissionid)
		INNER JOIN {emarking_comment} AS ec ON (ed.id = ec.draft AND ec.levelid > 0)
 		GROUP BY es.student,ec.levelid
        ORDER BY es.student,ec.levelid) AS MARK
        ON (b.id = MARK.levelid AND a.id = MARK.criterionid AND es.student = MARK.student)
ORDER BY a.sortorder,b.score) AS MARKS
WHERE 1 = 1
$filter
GROUP BY student,criterionid
ORDER BY student,sortorder
";
$params = array(
    $cm->id,
    $cm->instance);
$agreements = $DB->get_recordset_sql($sqlagreement, $params);
$sum = array();
$enrolledmarkers = get_enrolled_users($context, 'mod/assign:grade');
$markersnames = array();
foreach ($enrolledmarkers as $enrolledmarker) {
    $markersnames [$enrolledmarker->id] = $enrolledmarker->firstname . " " . $enrolledmarker->lastname;
    $markeragreement [$enrolledmarker->id] = array();
}
foreach ($agreements as $agree) {
    $sum ["criteria"] [$agree->criterionid] [] = $agree->agreement;
    $sum ["student"] [$agree->submission] [] = $agree->agreement;
    $sum ["total"] [] = $agree->agreement;
    $levels = explode('-', $agree->levels);
    $counts = explode('-', $agree->counts);
    $markercounts = explode('-', $agree->markercounts);
    $markersperlevel = explode('-', $agree->markers);
    $agreedlevel = array();
    $markerselection = array();
    for ($i = 0; $i < count($levels); $i ++) {
        if (! isset($agreedlevel [$agree->criterionid]) || $agreedlevel [$agree->criterionid] ["count"] < $counts [$i]) {
            $agreedlevel [$agree->criterionid] = array(
                "level" => $levels [$i],
                "count" => $counts [$i]);
        }
        $markerselection [$levels [$i]] = $markercounts [$i];
        $markers [$levels [$i]] = array();
        $levelmarkers = explode("#", $markersperlevel [$i]);
        foreach ($levelmarkers as $levelmarker) {
            if (isset($markersnames [$levelmarker])) {
                $markers [$levels [$i]] [] = $markersnames [$levelmarker];
            }
        }
    }
    for ($i = 0; $i < count($levels); $i ++) {
        $levelmarkers = explode("#", $markersperlevel [$i]);
        foreach ($levelmarkers as $levelmarker) {
            if (! isset($markeragreement [$levelmarker])) {
                $markeragreement [$levelmarker] = array();
            }
            if ($agreedlevel [$agree->criterionid] ["level"] == $levels [$i]) {
                $markeragreement [$levelmarker] [] = 1;
            } else {
                $markeragreement [$levelmarker] [] = 0;
            }
        }
    }
}
$dataexams = array();
foreach ($sum ["student"] as $studentid => $agreement) {
    $total = array_sum($agreement);
    $avg = count($agreement) == 0 ? 0 : $total / count($agreement);
    $avg = round($avg * 100, 0);
    $dataexams [$studentid] = $avg;
}
$datacriteria = array();
foreach ($sum ["criteria"] as $criterionid => $agreement) {
    $total = array_sum($agreement);
    $avg = count($agreement) == 0 ? 0 : $total / count($agreement);
    $avg = round($avg * 100, 0);
    $datacriteria [$criterionid] = $avg;
}
$datamarkers = array();
foreach ($markeragreement as $markerid => $agreement) {
    $total = array_sum($agreement);
    $avg = count($agreement) == 0 ? 0 : $total / count($agreement);
    $avg = round($avg * 100, 0);
    if (isset($markersnames [$markerid]) && count($agreement) > 0) {
        $datamarkers [$markerid] = $avg;
    }
}
$totalagreement = array_sum($sum ["total"]);
$avgagreement = count($sum ["total"]) == 0 ? 0 : $totalagreement / count($sum ["total"]);
$avgagreement = round($avgagreement * 100, 0);
// Show header.
echo $OUTPUT->header();
$firststagetable = new html_table();
$firststagetable->data [] = array(
    $OUTPUT->heading(get_string("exams", "mod_emarking"), 5));
foreach ($dataexams as $sid => $d) {
    $examurl = new moodle_url("/mod/emarking/marking/agreement.php", array(
        "id" => $cm->id,
        "exam" => $sid));
    $firststagetable->data [] = array(
        $OUTPUT->action_link($examurl, get_string("exam", "mod_emarking") . " " . $sid . emarking_create_progress_graph($d)));
}
$secondstagetable = new html_table();
$secondstagetable->data [] = array(
    $OUTPUT->heading(get_string("criteria", "mod_emarking"), 5));
foreach ($datacriteria as $cid => $d) {
    $criterionurl = new moodle_url("/mod/emarking/marking/agreement.php",
            array(
                "id" => $cm->id,
                "criterion" => $cid));
    $secondstagetable->data [] = array(
        $OUTPUT->action_link($criterionurl,
                $definition->rubric_criteria [$cid] ['description'] . " " . emarking_create_progress_graph($d)));
}
$thirdstagetable = new html_table();
$thirdstagetable->data [] = array(
    $OUTPUT->heading(get_string("marker", "mod_emarking"), 5));
foreach ($datamarkers as $mid => $d) {
    $markerurl = new moodle_url("/mod/emarking/marking/agreement.php", array(
        "id" => $cm->id,
        "marker" => $mid));
    $thirdstagetable->data [] = array(
        $OUTPUT->action_link($markerurl, $markersnames [$mid] . " " . emarking_create_progress_graph($d)));
}
// Get the course module for the emarking, to build the emarking url.
$urlagreement = new moodle_url('/mod/emarking/marking/agreement.php', array(
    'id' => $cm->id));
echo emarking_tabs_markers_training($context, $cm, $emarking, 100, $avgagreement);
// Show export to Excel button if supervisor and there are students to export.
if ($issupervisor && $emarking->type == EMARKING_TYPE_MARKER_TRAINING) {
    $csvurl = new moodle_url('delphi.php', array(
        'id' => $cm->id,
        'exportcsv' => 'delphi'));
    $csvurlagreement = new moodle_url('delphi.php',
            array(
                'id' => $cm->id,
                'exportcsv' => 'agreement'));
    echo $OUTPUT->heading(get_string('exporttoexcel', 'mod_emarking'), 4);
    echo html_writer::start_div('exportbuttons');
    echo $OUTPUT->action_icon($csvurl, new pix_icon('i/grades', get_string('exportgrades', 'mod_emarking')));
    echo $OUTPUT->action_icon($csvurlagreement, new pix_icon('i/report', get_string('exportagreement', 'mod_emarking')));
    echo html_writer::end_div();
}
echo $OUTPUT->heading(get_string('agreement', 'mod_emarking'), 4);
$maintable = new html_table();
$maintable->data [] = array(
    html_writer::table($firststagetable),
    html_writer::table($secondstagetable),
    html_writer::table($thirdstagetable));
echo html_writer::table($maintable);
echo $OUTPUT->footer();