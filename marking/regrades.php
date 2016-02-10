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
 * @copyright 2012-onwards Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/marking/forms/regrade_form.php');
require_once($CFG->dirroot . '/grade/grading/lib.php');
global $CFG, $OUTPUT, $PAGE, $DB;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$criterionid = optional_param('criterion', null, PARAM_INT);
$delete = optional_param('delete', false, PARAM_BOOL);
require_login($course, true);
if (isguestuser()) {
    die();
}
list($gradingmanager, $gradingmethod, $definition, $rubriccontroller) = emarking_validate_rubric($context);
if ($emarking->type != EMARKING_TYPE_NORMAL) {
    print_error('You can only have regrades in a normal emarking type');
}
if ($criterionid && ! $criterion = $DB->get_record('gradingform_rubric_criteria', array(
    'id' => $criterionid))) {
    print_error("No criterion");
}
if ($criterionid && ! $minlevel = $DB->get_record_sql(
        "
					SELECT id, score
					FROM {gradingform_rubric_levels}
					WHERE criterionid = ?
					ORDER BY score ASC LIMIT 1", array(
            $criterionid))) {
    print_error("Criterion with no minimum level");
}
if (! $submission = $DB->get_record('emarking_submission', array(
    'emarking' => $emarking->id,
    'student' => $USER->id))) {
    print_error("Invalid submission");
}
if (! $draft = $DB->get_record('emarking_draft',
        array(
            'emarkingid' => $emarking->id,
            'submissionid' => $submission->id,
            'qualitycontrol' => 0))) {
    print_error('Fatal error! Couldn\'t find emarking draft');
}
// Check if user has an editingteacher role.
$issupervisor = has_capability('mod/emarking:supervisegrading', $context);
$usercangrade = has_capability('mod/emarking:grade', $context);
$owndraft = $USER->id == $submission->student;
if (! $owndraft && ! $issupervisor) {
    print_error("Invalid access!");
}
$url = new moodle_url('/mod/emarking/marking/regrades.php', array(
    'id' => $cm->id));
$cancelurl = new moodle_url('/mod/emarking/marking/regraderequests.php', array(
    'id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_url($url);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), 'regrades');
if (! $submission->seenbystudent) {
    echo $OUTPUT->notification(get_string('mustseeexambeforeregrade', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}
$numpendingregrades = $DB->count_records('emarking_regrade', array(
    'draft' => $draft->id,
    'accepted' => 0));
$numregrades = $DB->count_records('emarking_regrade', array(
    'draft' => $draft->id));
$regrade = null;
$emarkingcomment = null;
if ($criterionid) {
    $regrade = $DB->get_record('emarking_regrade', array(
        'draft' => $draft->id,
        'criterion' => $criterionid));
    $emarkingcomment = $DB->get_record_sql(
            '
		SELECT ec.*
		FROM {emarking_comment} ec
		WHERE ec.levelid in (
            SELECT id FROM {gradingform_rubric_levels} l
            WHERE l.criterionid = :criterionid) AND ec.draft = :draft',
            array(
                'criterionid' => $criterionid,
                'draft' => $draft->id));
}
$requestswithindate = emarking_is_regrade_requests_allowed($emarking);
$data = new stdClass();
$data->regradesclosedate = userdate($emarking->regradesclosedate);
if (! $requestswithindate) {
    echo $OUTPUT->notification(get_string('regraderestricted', 'mod_emarking', $data), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}
if ($regrade && $regrade->accepted > 0) {
    echo $OUTPUT->notification(get_string('cannotmodifyacceptedregrade', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}
if ($regrade && $delete && $requestswithindate) {
    $result = $DB->delete_records('emarking_regrade', array(
        'draft' => $draft->id,
        'criterion' => $criterionid));
    // If it was the only pending regrade, change draft status.
    if ($numpendingregrades == 1) {
        $draft->status = $numregrades > 1 ? EMARKING_STATUS_REGRADING_RESPONDED : EMARKING_STATUS_PUBLISHED;
        $DB->update_record("emarking_draft", $draft);
    }
    $successmessage = get_string('saved', 'mod_emarking');
    echo $OUTPUT->notification($successmessage, 'notifysuccess');
    echo $OUTPUT->single_button($cancelurl, get_string("continue"), "GET");
    echo $OUTPUT->footer();
    die();
}
$mform = new emarking_regrade_form($url, array(
    "criteria" => $definition,
    "draft" => $draft));
if ($regrade) {
    $mform->set_data($regrade);
}
if ($mform->is_cancelled()) {
    redirect($cancelurl);
} else if ($data = $mform->get_data()) {
    if (! $regrade) {
        $regrade = new stdClass();
        $regrade->timecreated = time();
        if ($emarkingcomment) {
            $regrade->levelid = $emarkingcomment->levelid;
            $regrade->markerid = $emarkingcomment->markerid;
            $regrade->bonus = $emarkingcomment->bonus;
        } else {
            $regrade->levelid = $minlevel->id;
            $regrade->markerid = $USER->id;
            $regrade->bonus = 0;
        }
    } else if ($regrade->levelid == 0) {
        $regrade->levelid = $minlevel->id;
        $regrade->markerid = $USER->id;
        $regrade->bonus = 0;
    }
    $regrade->student = $USER->id;
    $regrade->draft = $draft->id;
    $regrade->motive = $data->motive;
    $regrade->comment = $data->comment;
    $regrade->criterion = $criterionid;
    $regrade->timemodified = time();
    if (isset($regrade->id)) {
        $DB->update_record('emarking_regrade', $regrade);
    } else {
        $regradeid = $DB->insert_record('emarking_regrade', $regrade);
        $regrade->id = $regradeid;
    }
    $submission->status = EMARKING_STATUS_REGRADING;
    $DB->update_record('emarking_submission', $submission);
    $draft->status = EMARKING_STATUS_REGRADING;
    $DB->update_record('emarking_draft', $draft);
    $successmessage = get_string('saved', 'mod_emarking');
    echo $OUTPUT->notification($successmessage, 'notifysuccess');
    echo $OUTPUT->single_button($cancelurl, get_string("continue"), "GET");
} else {
    // Form processing and displaying is done here.
    $mform->display();
}
echo $OUTPUT->footer();