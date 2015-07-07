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
 * @copyright 2012 Jorge Villalón {@link http://www.uai.cl}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
define('NO_DEBUG_DISPLAY', true);

require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->libdir . '/formslib.php');
require_once ($CFG->libdir . '/gradelib.php');
require_once ("$CFG->dirroot/grade/grading/lib.php");
require_once $CFG->dirroot . '/grade/lib.php';
require_once ("$CFG->dirroot/grade/grading/form/rubric/lib.php");
require_once ("$CFG->dirroot/lib/filestorage/file_storage.php");
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/marking/locallib.php");

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

// Required and optional params for ajax interaction in emarking
$ids = required_param('ids', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);
$pageno = optional_param('pageno', 0, PARAM_INT);
$testingmode = optional_param('testing', false, PARAM_BOOL);

// If we are in testing mode then submission 1 is the only one admitted
if ($testingmode) {
    $username = required_param('username', PARAM_ALPHANUMEXT);
    $password = required_param('password', PARAM_RAW_TRIMMED);
    
    if (! $user = authenticate_user_login($username, $password))
        emarking_json_error('Invalid username or password');
    
    complete_user_login($user);
    
    // Limit testing to submission id 1
    $ids = 1;
}

// If it's just a heartbeat, answer as quickly as possible
if ($action === 'heartbeat') {
    emarking_json_array(array(
        'time' => time()
    ));
    die();
}

// Verify that user is logged in, otherwise return error
if (! isloggedin() && ! $testingmode) {
    emarking_json_error('User is not logged in', array(
        'url' => $CFG->wwwroot . '/login/index.php'
    ));
}
    
// A valid submission is required
if (! $draft = $DB->get_record('emarking_draft', array(
    'id' => $ids
))) {
    emarking_json_error('Invalid draft');
}

// A valid submission is required
if (! $submission = $DB->get_record('emarking_submission', array(
    'id' => $draft->submissionid
))) {
    emarking_json_error('Invalid submission');
}

// The submission's student
$userid = $submission->student;

// User object for student
if (! $user = $DB->get_record('user', array(
    'id' => $userid
))) {
    emarking_json_error('Invalid user from submission');
}

// Assignment to which the submission belong

if (! $emarking = $DB->get_record("emarking", array(
    "id" => $draft->emarkingid
))) {
    emarking_json_error('Invalid assignment');
}

// Progress querys
$totaltest = $DB->count_records_sql("SELECT COUNT(*) from {emarking_draft} WHERE  emarkingid = $emarking->id");
$inprogesstest = $DB->count_records_sql("SELECT COUNT(*) from {emarking_draft} WHERE  emarkingid = $emarking->id AND status = 15");
$publishtest = $DB->count_records_sql("SELECT COUNT(*) from {emarking_draft} WHERE  emarkingid = $emarking->id AND status > 15");

// Agree level query
$agreeRecords = $DB->get_records_sql("
		SELECT d.id, 
		STDDEV(d.grade)*2/6 as dispersion, 
		d.submissionid, 
		COUNT(d.id) as conteo
		FROM {emarking_draft} d
		INNER JOIN {emarking_submission} s ON (s.emarking = $emarking->id AND s.id = d.submissionid)
		INNER JOIN {emarking_page} p ON (p.submission = d.id)
		INNER JOIN {emarking_comment} c ON (c.page= p.id) 
		GROUP BY d.submissionid
		HAVING COUNT(*) > 1");

// Set agree level average of all active grading assignments
if ($agreeRecords) {
    $agreeLevel = array();
    foreach ($agreeRecords as $dispersion) {
        $agreeLevel[] = (float) $dispersion->dispersion;
    }
    $agreeLevelAvg = round(100 * (1 - (array_sum($agreeLevel) / count($agreeLevel))), 1);
} else {
    $agreeLevelAvg = 0;
}

// Set agree level average of current active assignment
$agreeAssignment = $DB->get_record_sql("SELECT d.submissionid, 
										STDDEV(d.grade)*2/6 as dispersion, 
										COUNT(d.id) as conteo
										FROM {emarking_draft} d
										WHERE d.submissionid = ? 
										GROUP BY d.submissionid", array(
    $draft->submissionid
));
if ($agreeAssignment) {
    $agreeAsignmentLevelAvg = $agreeAssignment->dispersion;
} else {
    $agreeAssignmentLevelAvg = 0;
}

// The course to which the assignment belongs
if (! $course = $DB->get_record("course", array(
    "id" => $emarking->course
))) {
    emarking_json_error('Invalid course');
}

// The marking process course module
if (! $cm = get_coursemodule_from_instance("emarking", $emarking->id, $course->id)) {
    emarking_json_error('Invalid emarking course module');
}

    
    // Create the context within the course module
$context = context_module::instance($cm->id);

$usercangrade = has_capability('mod/emarking:grade', $context);
$usercanregrade = has_capability('mod/emarking:regrade', $context);
$issupervisor = has_capability('mod/emarking:supervisegrading', $context) || is_siteadmin($USER);
$isgroupmode = $cm->groupmode == SEPARATEGROUPS;

$studentanonymous = $emarking->anonymous === "0" || $emarking->anonymous === "1";
if($USER->id == $submission->student || $issupervisor) {
    $studentanonymous = false;
}
$markeranonymous = $emarking->anonymous === "1" || $emarking->anonymous === "3";
if($issupervisor) {
    $markeranonymous = false;
}

if ($submission->status >= EMARKING_STATUS_PUBLISHED && ! $usercanregrade) {
    $readonly = true;
}

// Get markers info
$markers = get_enrolled_users($context, 'mod/emarking:grade');
$markersToSend = array();
foreach ($markers as $marker) {
    $markersToSend[] = array(
        
        "id" => $marker->id,
        "username" => $marker->username,
        "firstname" => $marker->firstname,
        "lastname" => $marker->lastname
    );
}

// Get actual user role
$userRole = null;
if ($usercangrade == 1 && $issupervisor == 0) {
    $userRole = "marker";
} else 
    if ($usercangrade == 1 && $issupervisor == 1) {
        $userRole = "teacher";
    }

$linkrubric = $emarking->linkrubric;

// $totaltest, $inprogesstest, $publishtest
// Ping action for fast validation of user logged in and communication with server
if ($action === 'ping') {
    emarking_json_array(array(
        'user' => $USER->id,
        'student' => $userid,
        'username' => $USER->firstname . " " . $USER->lastname,
        'realUsername' => $USER->username, // real username, not name and lastname.
        'role' => $userRole,
        'groupID' => $emarking->id, // emarkig->id assigned to groupID for chat and wall rooms.
        'sesskey' => $USER->sesskey,
        'cm' => $cm->id,
        'studentanonymous' => $studentanonymous ? "true" : "false",
        'markeranonymous' => $markeranonymous ? "true" : "false",
        'hascapability' => $usercangrade,
        'supervisor' => $issupervisor,
        'markers' => json_encode($markersToSend),
        'markingtype' => $emarking->type,
        'totalTests' => $totaltest, // Progress bar indicator
        'inProgressTests' => $inprogesstest, // Progress bar indicator
        'publishedTests' => $publishtest, // Progress bar indicator
        'agreeLevel' => $agreeLevelAvg, // General agree bar indicator (avg of all overlapped students).
        'heartbeat' => $emarking->heartbeatenabled,
        'linkrubric' => $linkrubric,
        'collaborativefeatures' => $emarking->collaborativefeatures
    ));
}

// Now require login so full security is checked
require_login($course->id, false, $cm);

$url = new moodle_url('/mod/emarking/ajax/a.php', array(
    'ids' => $ids,
    'action' => $action,
    'pageno' => $pageno
));

$readonly = true;
// Validate grading capability and stop and log unauthorized access
if (! $usercangrade) {
    // If the student owns the exam
    if ($USER->id == $userid) {
        $readonly = true;
    } else {
        if (has_capability('mod/emarking:submit', $context)) { // If the student belongs to the course and is allowed to submit
            $readonly = true;
        } else { // This is definitely a hacking attempt
            $item = array(
                'context' => context_module::instance($cm->id),
                'objectid' => $cm->id
            );
            // Add to Moodle log so some auditing can be done
            \mod_emarking\event\unauthorized_granted::create($item)->trigger();
            emarking_json_error('Unauthorized access!');
        }
    }
} else {
    $readonly = false;
}

// Switch according to action
switch ($action) {
    
    case 'addcomment':
        
        // Add to Moodle log so some auditing can be done
        $item = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $cm->id
        );
        \mod_emarking\event\addcomment_added::create($item)->trigger();
        
        include "act/actCheckGradePermissions.php";
        include "act/actAddComment.php";
        emarking_json_array($output);
        break;
    
    case 'addmark':
        
        // Add to Moodle log so some auditing can be done
        $item = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $cm->id
        );
        \mod_emarking\event\addmark_added::create($item)->trigger();
        
        include "act/actCheckGradePermissions.php";
        
        include "act/actAddMark.php";
        emarking_json_array($output);
        break;
    
    case 'addregrade':
        
        // Add to Moodle log so some auditing can be done
        $item = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $cm->id
        );
        \mod_emarking\event\addregrade_added::create($item)->trigger();
        
        // include "act/actCheckRegradePermissions.php";
        
        include "act/actRegrade.php";
        emarking_json_array($output);
        break;
    
    case 'deletecomment':
        include "act/actDeleteComment.php";
        break;
    
    case 'deletemark':
        
        // Add to Moodle log so some auditing can be done
        $item = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $cm->id
        );
        \mod_emarking\event\deletemark_deleted::create($item)->trigger();
        
        include "act/actCheckGradePermissions.php";
        
        include "act/actDeleteMark.php";
        emarking_json_array($output);
        break;
    
    case 'emarking':
        $item = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $cm->id
        );
        // Add to Moodle log so some auditing can be done
        \mod_emarking\event\emarking_graded::create($item)->trigger();
        
        $module = new stdClass();
        include "../version.php";
        include "view/emarking.php";
        break;
    
    case 'finishmarking':
        
        require_once ($CFG->dirroot . '/mod/emarking/marking/locallib.php');
        require_once ($CFG->dirroot . '/mod/emarking/print/locallib.php');
        
        // Add to Moodle log so some auditing can be done
        $item = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $cm->id
        );
        \mod_emarking\event\marking_ended::create($item)->trigger();
        
        include "act/actCheckGradePermissions.php";
        include "qry/getRubricSubmission.php";
        include "act/actFinishMarking.php";
        
        emarking_json_array($output);
        break;
    
    case 'getalltabs':
        $showrubric = optional_param('showrubric', false, PARAM_BOOL);
        $preferredwidth = optional_param('preferredwidth', 860, PARAM_INT);
        $today = usertime(time());
        setcookie('emarking_width',$preferredwidth, $today + 60*60*24*365, "/");
        setcookie('emarking_showrubric',$showrubric ? "1" : "0", $today + 60*60*24*365, "/");
        $alltabs = emarking_get_all_pages($emarking, $submission, $draft, $studentanonymous, $context);
        emarking_json_resultset($alltabs);
        break;
    
    case 'getnextsubmission':
        
        $nextsubmission = emarking_get_next_submission($emarking, $draft, $context, $user);
        emarking_json_array(array(
            'nextsubmission' => $nextsubmission
        ));
        break;
    
    case 'getrubric':
        
        include "qry/getRubricSubmission.php";
        emarking_json_resultset($results);
        break;
    
    case 'getstudents':
        
        include "qry/getStudentsInMarking.php";
        emarking_json_resultset($results);
        break;
    
    case 'getsubmission':
        
        include "qry/getSubmissionGrade.php";
        $output = $results;
        emarking_json_array($output);
        break;
    
    case 'prevcomments':
        
        include "qry/getPreviousCommentsSubmission.php";
        emarking_json_resultset($results);
        break;
    
    case 'rotatepage':
        if (! $issupervisor) {
            emarking_json_error('Invalid access');
        }
        // Add to Moodle log so some auditing can be done
        $item = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $cm->id
        );
        \mod_emarking\event\rotatepage_switched::create($item)->trigger();
        
        list ($imageurl, $anonymousurl, $imgwidth, $imgheight) = emarking_rotate_image($pageno, $submission, $context);
        if (strlen($imageurl) == 0)
            emarking_json_error('Image is empty');
        $output = array(
            'imageurl' => $imageurl,
            'anonymousimageurl' => $anonymousurl,
            'width' => $imgwidth,
            'height' => $imgheight
        );
        emarking_json_array($output);
        break;
    
    case 'sortpages':
        
        // Add to Moodle log so some auditing can be done
        $item = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $cm->id
        );
        \mod_emarking\event\sortpage_switched::create($item)->trigger();
        
        $neworder = required_param('neworder', PARAM_SEQUENCE);
        $neworderarr = explode(',', $neworder);
        if (! emarking_sort_submission_pages($submission, $neworderarr)) {
            emarking_json_error('Error trying to resort pages!');
        }
        $output = array(
            'neworder' => $neworder
        );
        emarking_json_array($output);
        break;
    
    case 'updcomment':
        
        // Add to Moodle log so some auditing can be done
        $item = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $cm->id
        );
        \mod_emarking\event\updcomment_updated::create($item)->trigger();
        
        include "act/actCheckGradePermissions.php";
        
        include "qry/updComment.php";
        emarking_json_array(array(
            'message' => 'Success!',
            'newgrade' => $newgrade,
            'timemodified' => time()
        ));
        break;
    
    default:
        emarking_json_error('Invalid action!');
}
?>