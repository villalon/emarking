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
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once('forms/comparativereport_form.php');
global $DB, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$emarkingid = optional_param('eid', 0, PARAM_INT);
// URLs for current page.
$url = new moodle_url('/mod/emarking/reports/comparativereport.php', array(
    'id' => $cm->id));
// Validate the user has grading capabilities.
require_capability('mod/emarking:grade', $context);
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
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('comparativereport', 'mod_emarking'));
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
// Print eMarking tabs.
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "comparison");
// Get rubric definitions for both activities.
list($gradingmanager, $gradingmethod, $definition) = emarking_validate_rubric($context, true);
$totalsubmissions = $DB->count_records_sql(
        "
		SELECT COUNT(distinct e.id) AS total
		FROM {emarking_submission} e
        INNER JOIN {emarking_draft} d ON (d.submissionid = e.id AND d.qualitycontrol=0)
		WHERE e.emarking = :emarkingid AND d.status >= " . EMARKING_STATUS_PUBLISHED,
        array(
            'emarkingid' => $emarking->id));
if (! $totalsubmissions || $totalsubmissions == 0) {
    echo $OUTPUT->notification(get_string('nosubmissionspublished', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}
$emarkingsform = new emarking_comparativereport_form(null, array(
    'course' => $course,
    'cm' => $cm));
if ($emarkingsform->get_data()) {
    // Get the emarking activity to compare this one to.
    $emarking2 = $DB->get_record('emarking', array(
        'id' => $emarkingsform->get_data()->emarking2));
    // Get rubric definition for second activity.
    $cm2 = get_coursemodule_from_instance('emarking', $emarking2->id);
    $context2 = context_module::instance($cm2->id);
    list($gradingmanager2, $gradingmethod2, $definition2) = emarking_validate_rubric($context2, false, false);
    if ($gradingmethod2 == null) {
        echo $OUTPUT->notification(get_string('rubrcismustbeidentical', 'mod_emarking'), 'notifyproblem');
        echo $OUTPUT->footer();
        die();
    }
    $criteria = array_values($definition->rubric_criteria);
    $criteria2 = array_values($definition2->rubric_criteria);
    $maxscores = array();
    $problems = false;
    for ($i = 0; $i < count($criteria); $i ++) {
        if ($criteria [$i] ['description'] !== $criteria2 [$i] ['description']) {
            {
                $problems = true;
                break;
            }
        }
        $levels = array_values($criteria [$i] ['levels']);
        $levels2 = array_values($criteria2 [$i] ['levels']);
        $problems = $problems || (count($levels) != count($levels2));
        $maxscore = 0;
        for ($j = 0; $j < count($levels); $j ++) {
            if ($levels [$j] ['definition'] !== $levels2 [$j] ['definition'] || $levels [$j] ['score'] != $levels2 [$j] ['score']) {
                $problems = true;
                break;
            }
            if ($maxscore < $levels [$j] ['score']) {
                $maxscore = $levels [$j] ['score'];
            }
        }
        $maxscores [$criteria [$i] ['id']] = $maxscore;
    }
    $problems = $problems || (count($criteria) != count($criteria2));
    if ($problems) {
        echo $OUTPUT->notification(get_string('rubrcismustbeidentical', 'mod_emarking'), 'notifyproblem');
        echo $OUTPUT->footer();
        die();
    }
    $sql = "
			SELECT E1.student,
				E1.name,
				E1.description,
				E1.definition,
				E1.score,
				E1.bonus,
				E1.rawtext AS comment,
	            E1.criterionid,
				E2.name AS name2,
				E2.definition AS definition2,
				E2.score AS score2,
				E2.bonus AS bonus2,
				E2.rawtext AS comment2
			FROM (
				SELECT e1.name, es1.student, ec1.bonus, l1.definition, l1.score, c1.description, ec1.rawtext, c1.id as criterionid
				FROM {emarking} e1
				INNER JOIN {emarking_submission} es1 ON (e1.id = :emarking1 AND es1.emarking = e1.id)
				INNER JOIN {emarking_draft} d1 ON (d1.submissionid = es1.id AND d1.qualitycontrol=0)
	            INNER JOIN {emarking_page} ep1 ON (ep1.submission = es1.id)
				INNER JOIN {emarking_comment} ec1 ON (ec1.page = ep1.id AND ec1.draft = d1.id)
				INNER JOIN {gradingform_rubric_levels} l1 ON (ec1.levelid = l1.id)
				INNER JOIN {gradingform_rubric_criteria} c1 ON (l1.criterionid = c1.id)
				ORDER BY student, description, definition) AS E1
			INNER JOIN (
				SELECT e1.name, es1.student, ec1.bonus, l1.definition, l1.score, c1.description, ec1.rawtext
				FROM {emarking} e1
				INNER JOIN {emarking_submission} es1 ON (e1.id = :emarking2 AND es1.emarking = e1.id)
				INNER JOIN {emarking_draft} d1 ON (d1.submissionid = es1.id AND d1.qualitycontrol=0)
	            INNER JOIN {emarking_page} ep1 ON (ep1.submission = es1.id)
				INNER JOIN {emarking_comment} ec1 ON (ec1.page = ep1.id AND ec1.draft = d1.id)
				INNER JOIN {gradingform_rubric_levels} l1 ON (ec1.levelid = l1.id)
				INNER JOIN {gradingform_rubric_criteria} c1 ON (l1.criterionid = c1.id)
				ORDER BY student, description, definition) AS E2
			ON (E1.student = E2.student AND E1.description = E2.description)";
    $comparison = $DB->get_recordset_sql($sql,
            array(
                'emarking1' => $emarking->id,
                'emarking2' => $emarking2->id));
    $laststudent = 0;
    $data = array();
    $userdata = array();
    foreach ($comparison as $record) {
        if ($record->student != $laststudent) {
            if ($laststudent > 0) {
                $data [] = $userdata;
            }
            $laststudent = $record->student;
            $userdata = array();
            $student = $DB->get_record('user', array(
                'id' => $record->student));
            $userdata [get_string('student', 'grades')] = $student->lastname . ", " . $student->firstname;
        }
        $pre = $maxscores [$record->criterionid] > 0 ?
            ($record->score + $record->bonus) / $maxscores [$record->criterionid] * 100 : 0;
        $post = $maxscores [$record->criterionid] > 0 ?
            ($record->score2 + $record->bonus2) / $maxscores [$record->criterionid] * 100 : 0;
        $title = number_format($pre) . " / " . number_format($post);
        $diff = $pre - $post;
        $arrow = "<i class=\"fa fa-arrow-right\" style=\"color:yellow; text-shadow: 1px 1px #aaa;\"></i>";
        if ($diff > 20) {
            $arrow = "<i class=\"fa fa-arrow-up\" style=\"color:green; text-shadow: 1px 1px #aaa;\"></i>";
        }
        if ($diff < - 20) {
            $arrow = "<i class=\"fa fa-arrow-down\" style=\"color:red; text-shadow: 1px 1px #aaa;\"></i>";
        }
        $userdata [$record->description] = $OUTPUT->box(number_format($diff, 0) . "% " . $arrow, null, null,
                array(
                    'title' => $title));
    }
    $headers = array();
    if ($laststudent > 0) {
        $data [] = $userdata;
        $headers = array_keys($userdata);
    }
    $table = new html_table();
    $table->head = $headers;
    $table->data = $data;
    echo $OUTPUT->box_start('generalbox', null, array(
        'style' => 'overflow:scroll'));
    echo html_writer::table($table);
    echo $OUTPUT->box_end();
} else {
    $emarkingsform->display();
}
echo $OUTPUT->footer();