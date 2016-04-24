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
 * @copyright 2012-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
define('NO_DEBUG_DISPLAY', true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/grading/lib.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/grading/form/rubric/lib.php');
require_once($CFG->dirroot . '/lib/filestorage/file_storage.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/marking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/print/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/ajax/locallib.php');
global $CFG, $DB, $OUTPUT, $PAGE, $USER;
// Required and optional params for ajax interaction in emarking.
$ids = required_param('ids', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);
$pageno = optional_param('pageno', 0, PARAM_INT);
$testingmode = optional_param('testing', false, PARAM_BOOL);
// If we are in testing mode then submission 1 is the only one admitted.
if ($testingmode) {
    $username = required_param('username', PARAM_ALPHANUMEXT);
    $password = required_param('password', PARAM_RAW_TRIMMED);
    if (! $user = authenticate_user_login($username, $password)) {
        emarking_json_error('Invalid username or password');
    }
    complete_user_login($user);
    // Limit testing to submission id 1.
    $ids = 1;
}
// If it's just a heartbeat, answer as quickly as possible.
if ($action === 'heartbeat') {
    emarking_json_array(array(
        'time' => time()));
    die();
}
// Verify that user is logged in, otherwise return error.
if (! isloggedin() && ! $testingmode) {
    emarking_json_error('User is not logged in', array(
        'url' => $CFG->wwwroot . '/login/index.php'));
}
// A valid submission is required.
if (! $draft = $DB->get_record('emarking_draft', array(
    'id' => $ids))) {
    emarking_json_error('Invalid draft');
}
// A valid submission is required.
if (! $submission = $DB->get_record('emarking_submission', array(
    'id' => $draft->submissionid))) {
    emarking_json_error('Invalid submission');
}
// Assignment to which the submission belong.
if (! $emarking = $DB->get_record('emarking', array(
    'id' => $draft->emarkingid))) {
    emarking_json_error('Invalid emarking activity');
}
// The submission's student.
$userid = $submission->student;
$ownsubmission = $USER->id == $userid;
// User object for student.
if ($emarking->type == EMARKING_TYPE_MARKER_TRAINING) {
    $ownsubmission = false;
    $user = null;
} else if (! $user = $DB->get_record('user', array(
    'id' => $userid))) {
    emarking_json_error('Invalid user from submission');
}
// The course to which the assignment belongs.
if (! $course = $DB->get_record('course', array(
    'id' => $emarking->course))) {
    emarking_json_error('Invalid course');
}
// The marking process course module.
if (! $cm = get_coursemodule_from_instance('emarking', $emarking->id, $course->id)) {
    emarking_json_error('Invalid emarking course module');
}
// Create the context within the course module.
$context = context_module::instance($cm->id);
// We validate again as we require a valid user within the context of a cm.
require_login($course->id, false, $cm);
if (isguestuser()) {
    die();
}
// After validating login in the cm we can check more specific permissions.
$usercangrade = has_capability('mod/emarking:grade', $context) || ($emarking->type == EMARKING_TYPE_PEER_REVIEW &&
         $draft->teacher == $USER->id && has_capability('mod/emarking:submit', $context));
$usercanregrade = has_capability('mod/emarking:regrade', $context);
$issupervisor = has_capability('mod/emarking:supervisegrading', $context) || is_siteadmin($USER);
$isgroupmode = $cm->groupmode == SEPARATEGROUPS;
$studentanonymous = $emarking->anonymous === '0' || $emarking->anonymous === '1';
if ($ownsubmission || $issupervisor) {
    $studentanonymous = false;
}
$markeranonymous = $emarking->anonymous === '1' || $emarking->anonymous === '3';
if ($issupervisor) {
    $markeranonymous = false;
}
$linkrubric = $emarking->linkrubric;
// Readonly by default for security.
$readonly = true;
// If the user can grade and the submission was at least submitted but not published yet, otherwise has to be a supervisor.
if (($usercangrade && $submission->status >= EMARKING_STATUS_SUBMITTED && $submission->status < EMARKING_STATUS_PUBLISHED) ||
         ($issupervisor && $submission->status >= EMARKING_STATUS_PUBLISHED)) {
    $readonly = false;
}
if (($emarking->type == EMARKING_TYPE_MARKER_TRAINING || $emarking->type == EMARKING_TYPE_PEER_REVIEW) &&
         $draft->teacher != $USER->id) {
    if ($issupervisor) {
        $readonly = true;
    } else {
        $item = array(
            'context' => $context,
            'objectid' => $draft->id);
        // Add to Moodle log so some auditing can be done.
        \mod_emarking\event\unauthorizedajax_attempted::create($item)->trigger();
        emarking_json_error('Unauthorized access!');
    }
}
// Validate grading capability and stop and log unauthorized access.
if (! $usercangrade && ! $ownsubmission && ! has_capability('mod/emarking:submit', $context)) {
    $item = array(
        'context' => $context,
        'objectid' => $draft->id);
    // Add to Moodle log so some auditing can be done.
    \mod_emarking\event\unauthorizedajax_attempted::create($item)->trigger();
    emarking_json_error('Unauthorized access!');
}
// Ping action for fast validation of user logged in and communication with server.
if ($action === 'ping') {
    include('../version.php');
    // Start with a default Node JS path, and get the configuration one if any.
    $nodejspath = 'http://127.0.0.1:9091';
    if (isset($CFG->emarking_nodejspath)) {
        $nodejspath = $CFG->emarking_nodejspath;
    }
    emarking_json_array(
            array(
                'user' => $USER->id,
                'student' => $userid,
                'username' => $USER->firstname . ' ' . $USER->lastname,
                'realUsername' => $USER->username,
                'groupID' => $emarking->id,
                'sesskey' => $USER->sesskey,
                'adminemail' => $CFG->supportemail,
                'cm' => $cm->id,
                'studentanonymous' => $studentanonymous ? 'true' : 'false',
                'markeranonymous' => $markeranonymous ? 'true' : 'false',
                'readonly' => $readonly,
                'supervisor' => $issupervisor,
                'markingtype' => $emarking->type,
                'totalTests' => $totaltest,
                'inProgressTests' => $inprogesstest,
                'publishedTests' => $publishtest,
                'heartbeat' => $emarking->heartbeatenabled,
                'linkrubric' => $linkrubric,
                'collaborativefeatures' => $emarking->collaborativefeatures,
                'coursemodule' => $cm->id,
                'nodejspath' => $nodejspath,
                'motives' => emarking_get_regrade_motives(),
                'version' => $plugin->version));
}
$url = new moodle_url('/mod/emarking/ajax/a.php', array(
    'ids' => $ids,
    'action' => $action,
    'pageno' => $pageno));
// Switch according to action.
switch ($action) {
    case 'addchatmessage' :
        $output = emarking_add_chat_message();
        emarking_json_array($output);
        break;
    case 'addcomment' :
        emarking_check_grade_permission($readonly, $draft, $context);
        $output = emarking_add_comment($submission, $draft);
        emarking_json_array($output);
        break;
    case 'addmark' :
        emarking_check_grade_permission($readonly, $draft, $context);
        // Add to Moodle log so some auditing can be done.
        \mod_emarking\event\emarking_graded::create_from_draft($draft, $submission, $context)->trigger();
        $output = emarking_add_mark($submission, $draft, $emarking, $context);
        emarking_json_array($output);
        break;
    case 'addregrade' :
        emarking_check_add_regrade_permission($ownsubmission, $draft, $context);
        // Add to Moodle log so some auditing can be done.
        \mod_emarking\event\emarking_graded::create_from_draft($draft, $submission, $context)->trigger();
        $output = emarking_regrade($emarking, $draft);
        emarking_json_array($output);
        break;
    case 'clickcollaborativebuttons' :
        $output = emarking_add_action_collaborativebutton();
        emarking_json_array($output);
        break;
    case 'deletecomment' :
        $output = emarking_delete_comment();
        emarking_json_array($output);
        break;
    case 'deletemark' :
        emarking_check_grade_permission($readonly, $draft, $context);
        // Add to Moodle log so some auditing can be done.
        \mod_emarking\event\emarking_graded::create_from_draft($draft, $submission, $context)->trigger();
        $output = emarking_delete_mark($submission, $draft, $emarking, $context);
        emarking_json_array($output);
        break;
    case 'finishmarking' :
        emarking_check_grade_permission($readonly, $draft, $context);
        // Add to Moodle log so some auditing can be done.
        \mod_emarking\event\emarking_published::create_from_draft($draft, $submission, $context)->trigger();
        $results = emarking_get_rubric_submission($submission, $draft, $cm, $readonly, $issupervisor);
        $output = emarking_finish_marking($emarking, $submission, $draft, $user, $context, $cm, $issupervisor);
        emarking_json_array($output);
        break;
    case 'getalltabs' :
        if ($ownsubmission) {
            $submission->seenbystudent = 1;
            $submission->timemodified = time();
            $DB->update_record('emarking_submission', $submission);
        }
        $alltabs = emarking_get_all_pages($emarking, $submission, $draft, $studentanonymous, $context);
        emarking_json_resultset($alltabs);
        break;
    case 'getnextsubmission' :
        $nextsubmission = emarking_get_next_submission($emarking, $draft, $context, $user, $issupervisor);
        emarking_json_array(array(
            'nextsubmission' => $nextsubmission));
        break;
    case 'setanswerkey' :
        $status = required_param('status', PARAM_INT);
        $newvalue = emarking_set_answer_key($submission, $status);
        emarking_json_array(array(
            'newvalue' => $newvalue));
        break;
    case 'getrubric' :
        $results = emarking_get_rubric_submission($submission, $draft, $cm, $readonly, $issupervisor);
        emarking_json_resultset($results);
        break;
    case 'getsubmission' :
        $results = emarking_get_submission_grade($draft);
        $output = $results;
        $output->coursemodule = $cm->id;
        $output->markerfirstname = $USER->firstname;
        $output->markerlastname = $USER->lastname;
        $output->markeremail = $USER->email;
        $output->markerid = $USER->id;
        $results = emarking_get_rubric_submission($submission, $draft, $cm, $readonly, $issupervisor);
        $output->rubric = $results;
        $results = emarking_get_answerkeys_submission($submission);
        $output->answerkeys = $results;
        emarking_json_array($output);
        break;
    case 'getchathistory' :
        $output = emarking_get_chat_history();
        emarking_json_array($output);
        break;
    case 'getvaluescollaborativebuttons' :
        $output = emarking_get_values_collaborative();
        emarking_json_array($output);
        break;
    case 'prevcomments' :
        $results = emarking_get_previous_comments($submission, $draft);
        emarking_json_resultset($results);
        break;
    case 'rotatepage' :
        if (! $issupervisor) {
            emarking_json_error('Invalid access');
        }
        list($imageurl, $anonymousurl, $imgwidth, $imgheight) = emarking_rotate_image($pageno, $submission, $context);
        if (strlen($imageurl) == 0) {
            emarking_json_error('Image is empty');
        }
        $output = array(
            'imageurl' => $imageurl,
            'anonymousimageurl' => $anonymousurl,
            'width' => $imgwidth,
            'height' => $imgheight);
        emarking_json_array($output);
        break;
    case 'sortpages' :
        $neworder = required_param('neworder', PARAM_SEQUENCE);
        $neworderarr = explode(',', $neworder);
        if (! emarking_sort_submission_pages($submission, $neworderarr)) {
            emarking_json_error('Error trying to resort pages!');
        }
        $output = array(
            'neworder' => $neworder);
        emarking_json_array($output);
        break;
    case 'updcomment' :
        emarking_check_grade_permission($readonly, $draft, $context);
        // Add to Moodle log so some auditing can be done.
        \mod_emarking\event\emarking_graded::create_from_draft($draft, $submission, $context)->trigger();
        $newgrade = emarking_update_comment($submission, $draft, $emarking, $context);
        emarking_json_array(
                array(
                    'message' => 'Success!',
                    'newgrade' => $newgrade,
                    'timemodified' => time()));
        break;
    default :
        emarking_json_error('Invalid action!');
}