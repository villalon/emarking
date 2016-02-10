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
global $CFG, $DB, $OUTPUT, $PAGE, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// Check that user is logued in the course.
require_login();
if (isguestuser()) {
    die();
}
$markerid = optional_param('marker', 0, PARAM_INT);
$examid = optional_param('exam', 0, PARAM_INT);
$criterionid = optional_param('criterion', 0, PARAM_INT);
// Get the course module for the emarking, to build the emarking url.
$urlemarking = new moodle_url('/mod/emarking/marking/agreement.php', array(
    'id' => $cm->id));
$issupervisor = has_capability('mod/emarking:supervisegrading', $context);
// Get rubric instance.
list($gradingmanager, $gradingmethod, $definition) = emarking_validate_rubric($context, true);
$filter = "";
$markercolumn = get_string("yourmarking", "mod_emarking");
$text = $markercolumn;
if ($examid != 0) {
    $filter .= "AND submission = $examid";
    $text = get_string("exam", "mod_emarking") . " " . $examid;
} else if ($markerid != 0) {
    $marker = $DB->get_record('user', array(
        'id' => $markerid));
    $markercolumn = $marker->firstname . " " . $marker->lastname;
    $text = "<A HREF=\"" . $CFG->wwwroot . "/user/profile.php?id=" . $markerid . "\">" .
            get_string("marker", "mod_emarking") . " " . $markercolumn . "</A>";
} else if ($criterionid != 0) {
    $filter .= "AND criterionid = $criterionid";
    $text = $definition->rubric_criteria [$criterionid] ['description'];
}
if ($markerid == 0) {
    $markerid = $USER->id;
}
list($enrolledmarkers, $userismarker) = emarking_get_markers_in_training($emarking->id, $context, true);
$markersnames = array();
foreach ($enrolledmarkers as $enrolledmarker) {
    $markersnames [$enrolledmarker->id] = $enrolledmarker->firstname . " " . $enrolledmarker->lastname;
}
// Page navigation and URL settings.
$PAGE->set_url($urlemarking);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$sqldata = "
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
		FROM {course_modules} c
		INNER JOIN {context} mc ON (c.id = ? AND mc.contextlevel = 70 AND c.id = mc.instanceid)
		INNER JOIN {grading_areas} ar ON (mc.id = ar.contextid)
		INNER JOIN {grading_definitions} d ON (ar.id = d.areaid)
		INNER JOIN {gradingform_rubric_criteria} a ON (d.id = a.definitionid)
		INNER JOIN {gradingform_rubric_levels} b ON (a.id = b.criterionid)
        INNER JOIN {emarking_submission} es ON (es.emarking = c.instance)
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
		FROM {emarking_submission} es
        INNER JOIN {emarking_draft} ed ON (ed.emarkingid = ? AND es.id = ed.submissionid)
		INNER JOIN {emarking_comment} ec ON (ed.id = ec.draft AND ec.levelid > 0)
 		GROUP BY es.student,ec.levelid
        ORDER BY es.student,ec.levelid) MARK
        ON (b.id = MARK.levelid AND a.id = MARK.criterionid AND es.student = MARK.student)
ORDER BY a.sortorder,b.score) MARKS
WHERE 1 = 1
$filter
GROUP BY student,criterionid
ORDER BY student,sortorder
";
$params = array(
    $cm->id,
    $cm->instance);
$agreements = $DB->get_recordset_sql($sqldata, $params);
// TODO: si no tengo outliers, es decir no soy ayudante, no crear la tabla.
$firststagetable = new html_table();
$head = array();
$head [] = get_string("criterion", "mod_emarking");
$head [] = get_string("exam", "mod_emarking");
if ($userismarker || $markerid != $USER->id) {
    $head [] = $markercolumn;
}
$head [] = get_string("agreement", "mod_emarking");
$head [] = get_string("status", "mod_emarking");
$firststagetable->head = $head;
$sum = array();
foreach ($agreements as $agree) {
    $sum [] = $agree->agreement;
    $levels = explode('-', $agree->levels);
    $counts = explode('-', $agree->counts);
    $drafts = explode('#', $agree->drafts);
    $markerids = explode('#', $agree->teachers);
    $commentids = explode('#', $agree->comments);
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
    $square = "";
    $squareagreement = "";
    foreach ($definition->rubric_criteria [$agree->criterionid] ['levels'] as $data) {
        if ($markerselection [$data ['id']] == 1) {
            $square .= html_writer::div("&nbsp;", "agreement-yours-not-selected",
                    array(
                        "title" => $data ['definition']));
        } else {
            $square .= html_writer::div("&nbsp;", "agreement-yours-selected",
                    array(
                        "title" => $data ['definition']));
        }
        $title = implode("\n", $markers [$data ['id']]);
        if ($data ['id'] === $agreedlevel [$agree->criterionid] ["level"]) {
            $squareagreement .= html_writer::div("&nbsp;", "agreement-not-selected",
                    array(
                        "title" => $title));
        } else if (count($markers [$data ['id']]) > 0) {
            $squareagreement .= html_writer::div("&nbsp;", "agreement-some-selected",
                    array(
                        "title" => $title));
        } else {
            $squareagreement .= html_writer::div("&nbsp;", "agreement-selected", array(
                "title" => $title));
        }
    }
    $popup = "";
    $status = "";
    for ($i = 0; $i < count($drafts); $i ++) {
        if (intval($drafts [$i]) == 0) {
            continue;
        }
        if (! is_siteadmin() && $USER->id != $markerids [$i] && ! $issupervisor) {
            continue;
        }
        // EMarking popup url.
        $popupurl = new moodle_url('/mod/emarking/marking/index.php', array(
            'id' => $drafts [$i]));
        $popup .= $OUTPUT->action_link($popupurl, get_string("viewsubmission", "mod_emarking") . " " . $agree->submission,
                new popup_action('click', $popupurl, 'emarking' . $agree->student,
                        array(
                            'menubar' => 'no',
                            'titlebar' => 'no',
                            'status' => 'no',
                            'toolbar' => 'no',
                            'width' => 860,
                            'height' => 600))) . "<br/>";
        if ($userismarker && $markerids [$i] == $USER->id) {
            $link = new moodle_url('/mod/emarking/marking/modify.php',
                    array(
                        'id' => $cm->id,
                        'crid' => $agree->criterionid,
                        'cid' => $commentids [$i],
                        'emarkingid' => $emarking->id,
                        'lid' => $agreedlevel [$agree->criterionid] ["level"]));
            $popuplink = $OUTPUT->action_link($link, get_string("annotatesubmission", "mod_emarking"),
                    new popup_action('click', $link, '',
                            array(
                                'menubar' => 'no',
                                'titlebar' => 'no',
                                'status' => 'no',
                                'toolbar' => 'no',
                                'width' => 860,
                                'height' => 600)));
            $status .= $popuplink . "<br/>";
        } else {
            $status = round($agree->agreement * 100, 1);
        }
    }
    $data = array();
    $data [] = $agree->description;
    $data [] = $popup;
    if ($userismarker || $markerid != $USER->id) {
        $data [] = $square;
    }
    $data [] = $squareagreement;
    $data [] = $status;
    $firststagetable->data [] = $data;
}
$totalagreement = array_sum($sum);
$avgagreement = count($sum) == 0 ? 0 : $totalagreement / count($sum);
$avgagreement = round($avgagreement * 100, 0);
// Show header.
echo $OUTPUT->header();
echo emarking_tabs_markers_training($context, $cm, $emarking, 100, $avgagreement);
echo $OUTPUT->heading($text);
echo html_writer::table($firststagetable);
// Get the course module for the emarking, to build the emarking url.
$urldelphi = new moodle_url('/mod/emarking/marking/delphi.php', array(
    'id' => $cm->id));
echo $OUTPUT->single_button($urldelphi, get_string("back"));
echo $OUTPUT->footer();
?>
<script type="text/javascript">
function popUpClosed() {
    window.location.reload();
}
</script>