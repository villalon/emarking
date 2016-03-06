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
 * This page processes shows all regraderequests in an emarking activity
 * 
 * @package mod
 * @subpackage emarking
 * @copyright 2012-onwards Jorge Villalon <villalon@gmail.com>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
global $CFG, $OUTPUT, $PAGE, $DB; // To suppress eclipse warnings.
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
require_login($course, true);
$usercangrade = has_capability('mod/emarking:grade', $context);
$usercanregrade = has_capability('mod/emarking:regrade', $context);
$issupervisor = has_capability('mod/emarking:supervisegrading', $context);
$filteruser = ! $issupervisor && ! $usercangrade;
$url = new moodle_url("/mod/emarking/marking/regraderequests.php", array(
    "id" => $cm->id));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_url($url);
$PAGE->navbar->add(get_string('regrades', 'mod_emarking'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "regrades");
list($gradingmanager, $gradingmethod, $definition, $rubriccontroller) = emarking_validate_rubric($context);
$sqlfilter = $filteruser ? " AND u.id = $USER->id" : "";
$sql = "select
			rg.*,
			u.id AS userid,
			u.firstname,
			u.lastname,
			c.description AS criterion,
			c.id AS criterionid,
			dr.id AS ids,
			dr.status AS status,
            T.maxscore,
            T.minscore,
        CASE WHEN b.score IS NULL AND comment.bonus IS NULL THEN T.minscore
            WHEN b.score IS NULL THEN ROUND(T.minscore + comment.bonus,2)
            WHEN comment.bonus IS NULL THEN ROUND(b.score,2)
			ELSE ROUND(b.score + comment.bonus,2) END
        AS currentscore,
		ROUND(T.maxscore,2) AS maxscore,
        ROUND(T.minscore,2) AS minscore,
		comment.bonus as currentbonus,
        ol.score as originalscore,
        rg.bonus as originalbonus,
        ol.definition as originaldefinition,
        b.definition as currentdefinition
FROM {emarking} e
INNER JOIN {emarking_submission} s ON (s.emarking = :emarking AND s.emarking = e.id)
INNER JOIN {emarking_draft} dr ON (dr.submissionid = s.id AND dr.qualitycontrol=0)
INNER JOIN {emarking_regrade} rg ON (rg.draft = dr.id)
INNER JOIN {gradingform_rubric_levels} ol on (ol.id = rg.levelid)
INNER JOIN {user} u on (s.student = u.id $sqlfilter)
INNER JOIN (
			SELECT
			s.id AS emarkingid,
			a.id AS criterionid,
			MAX(l.score) AS maxscore,
            MIN(l.score) AS minscore
			FROM {emarking} s
			INNER JOIN {course_modules}  cm on (s.id = :emarkingid2 AND s.id = cm.instance)
			INNER JOIN {context}  c on (c.instanceid = cm.id)
			INNER JOIN {grading_areas}  ar on (ar.contextid = c.id)
			INNER JOIN {grading_definitions}  d on (ar.id = d.areaid)
			INNER JOIN {gradingform_rubric_criteria}  a on (d.id = a.definitionid)
			INNER JOIN {gradingform_rubric_levels}  l on (a.id = l.criterionid)
			GROUP BY s.id, criterionid
		) T ON (s.emarking = T.emarkingid AND T.criterionid = ol.criterionid)
INNER JOIN {course} co ON (e.course = co.id)
INNER JOIN {gradingform_rubric_criteria} c on (ol.criterionid = c.id)
LEFT JOIN {emarking_comment} comment ON (comment.draft = dr.id AND comment.criterionid = c.id AND comment.levelid > 0)
LEFT JOIN {gradingform_rubric_levels} b on (b.id = comment.levelid)
LEFT JOIN {emarking_page} page ON (page.submission = s.id AND comment.page = page.id)
ORDER BY u.lastname ASC, c.sortorder";
$records = $DB->get_records_sql($sql, array(
    "emarking" => $emarking->id,
    "emarkingid2" => $emarking->id));
if (count($records) == 0) {
    echo $OUTPUT->notification(get_string('noregraderequests', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->single_button(new moodle_url("/mod/emarking/marking/regrades.php", array(
        "id" => $cm->id)), get_string("regraderequest", "mod_emarking"), "GET");
    echo $OUTPUT->footer();
    die();
}
$table = new html_table();
$table->head = array(
    get_string('student', 'grades') . '-' . get_string('criterion', 'mod_emarking'),
    get_string('motive', 'mod_emarking'),
    get_string('grade', 'mod_emarking'),
    get_string('regrade', 'mod_emarking'),
    '&nbsp;');
$data = array();
$totalregrades = 0;
foreach ($records as $record) {
    $totalregrades ++;
    if ($record->accepted) {
        $statusicon = $OUTPUT->pix_icon("i/valid", get_string('replied', 'mod_emarking'));
    } else {
        $statusicon = $OUTPUT->pix_icon("i/flagged", get_string('sent', 'mod_emarking'));
    }
    $regradecomment = emarking_get_text_view_more($record->comment, 50, $record->id);
    $motive = emarking_get_regrade_type_string($record->motive) . ': ' . $regradecomment;
    // Student info.
    $urlstudent = new moodle_url('/user/view.php', array(
        'id' => $record->userid,
        'course' => $course->id));
    $studentcriterion = $record->userid == $USER->id ? '' : $OUTPUT->action_link($urlstudent,
            $record->firstname . ' ' . $record->lastname) . '<br/>';
    $studentcriterion .= $record->criterion;
    $studentcriterion .= '<br/>' . emarking_time_ago($record->timecreated, true);
    // Original grade.
    $original = ' [' . round($record->originalscore, 2) . '/' . round($record->maxscore, 2) . ']';
    // After regrade.
    $current = $record->accepted ? '[' . round($record->currentscore, 2) . '/' . round($record->maxscore, 2) . '] ' : '';
    $current .= '&nbsp;' . $statusicon;
    $current .= $record->accepted ? '<br/>' . $record->markercomment : '';
    $current .= '&nbsp;' . emarking_time_ago($record->timemodified, true);
    // Actions.
    $urlsub = new moodle_url('/mod/emarking/marking/index.php', array(
        'id' => $record->ids));
    $actions = '';
    if ($usercangrade || $issupervisor) {
        $actions .= $OUTPUT->action_link($urlsub, get_string('annotatesubmission', 'mod_emarking') . "&nbsp;|&nbsp;",
                new popup_action('click', $urlsub, 'emarking' . $record->ids,
                        array(
                            'menubar' => 'no',
                            'titlebar' => 'no',
                            'status' => 'no',
                            'toolbar' => 'no',
                            'width' => 860,
                            'height' => 600)), array(
                    "class" => "rowactions"));
    }
    if ($record->userid == $USER->id && $record->accepted == 0) {
        $url = new moodle_url("/mod/emarking/marking/regrades.php",
                array(
                    "id" => $cm->id,
                    "criterion" => $record->criterionid,
                    "delete" => true));
        $actions .= $OUTPUT->action_link($url, get_string("delete") . "&nbsp;|&nbsp;", null,
                array(
                    "class" => "rowactions"));
        $url = new moodle_url("/mod/emarking/marking/regrades.php",
                array(
                    "id" => $cm->id,
                    "criterion" => $record->criterionid,
                    "edit" => true));
        $actions .= $OUTPUT->action_link($url, get_string("edit") . "&nbsp;|&nbsp;", null,
                array(
                    "class" => "rowactions"));
    }
    $array = array();
    $array [] = $studentcriterion;
    $array [] = $motive;
    $array [] = $original;
    $array [] = $current;
    $array [] = $actions;
    $data [] = $array;
}
$table->data = $data;
echo html_writer::table($table);
if (count($definition->rubric_criteria) > $totalregrades && $filteruser) {
    echo $OUTPUT->single_button(new moodle_url("/mod/emarking/marking/regrades.php", array(
        "id" => $cm->id)), get_string("regraderequest", "mod_emarking"), "GET");
}
echo $OUTPUT->footer();