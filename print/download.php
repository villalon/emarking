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
 * @copyright 2012-2015 Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

require_once ($CFG->dirroot . '/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php');
require_once ($CFG->dirroot . '/mod/assign/feedback/editpdf/fpdi/fpdi.php');

require_once ($CFG->dirroot . '/mod/emarking/lib/phpqrcode/phpqrcode.php');
require_once ($CFG->dirroot . '/mod/emarking/lib.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ('locallib.php');
require_once ($CFG->libdir . '/eventslib.php');
require_once ($CFG->dirroot . '/mod/emarking/classes/event/invalidtoken_granted.php');

global $USER;

// We validate login first as this page can be reached by the copy center
// whom will not be logged in the course for downloading
if (! isloggedin()) {
    echo json_encode(array(
        'error' => 'User is not logged in'
    ));
    die();
}

require_login();

$sesskey = required_param('sesskey', PARAM_ALPHANUM);
$examid = optional_param('examid', 0, PARAM_INT);
$token = optional_param('token', 0, PARAM_INT);
$multiplepdfs = optional_param('multi', false, PARAM_BOOL);

// Validate session key
if ($sesskey != $USER->sesskey) {
    echo json_encode(array(
        'error' => 'Invalid session key'
    ));
    die();
}

// If we have the token and session id ok we get the exam id from the session
if ($token > 9999) {
    $examid = $_SESSION[$USER->sesskey . 'examid'];
}

// We get the exam object
if (! $exam = $DB->get_record('emarking_exams', array(
    'id' => $examid
))) {
    echo json_encode(array(
        'error' => 'Invalid exam id'
    ));
    die();
}

// We get the course from the exam
if (! $course = $DB->get_record('course', array(
    'id' => $exam->course
))) {
    print_error('Invalid exam course id');
    die();
}

$contextcat = context_coursecat::instance($course->category);
$contextcourse = context_course::instance($course->id);

$url = '/mod/emarking/print/download.php?examid=' . $exam->id . '&token=' . $token . '&sesskey=' . $sesskey;

// Validate capability in the category context
if (! (has_capability('mod/emarking:downloadexam', $contextcat) || ($CFG->emarking_teachercandownload && has_capability('mod/emarking:downloadexam', $contextcourse)))) {
    $item = array(
        'context' => $contextcourse
    );
    // Add to Moodle log so some auditing can be done
    \mod_emarking\event\invalidaccess_granted::create($item)->trigger();
    echo json_encode(array(
        'error' => get_string('invalidaccess', 'mod_emarking')
    ));
    die();
}

// If a token was sent and it was not valid, log and die
if ($token > 9999 && $_SESSION[$USER->sesskey . 'smstoken'] !== $token) {
    $item = array(
        'context' => $contextcourse
    );
    // Add to Moodle log so some auditing can be done
    \mod_emarking\event\invalidtoken_granted::create($item)->trigger();
    
    echo json_encode(array(
        'error' => get_string('eventinvalidtokengranted', 'mod_emarking')
    ));
    die();
}

// A token was sent to validate download it will have 5 digits, otherwise it should be 0
if ($token > 9999 && $_SESSION[$USER->sesskey . 'smstoken'] === $token) {
    $now = new DateTime();
    $tokendate = new DateTime();
    $tokendate->setTimestamp($_SESSION[$USER->sesskey . 'smsdate']);
    $diff = $now->diff($tokendate);
    if ($diff->i > 5 && false) {
        echo json_encode(array(
            'error' => 'The time to download the exam expired, please try again.'
        ));
        die();
    }
    $item = array(
        'context' => $contextcourse
    );
    // Add to Moodle log so some auditing can be done
    \mod_emarking\event\successfully_downloaded::create($item)->trigger();
    
    emarking_download_exam($examid, $multiplepdfs, null, null, null, null, true);
    die();
}

// If the token was not sent, then create new token,
// save data in session variables and send through email or mobile phone
$newtoken = rand(10000, 99999); // Generate random 5 digits token
$date = new DateTime();

$_SESSION[$USER->sesskey . "smstoken"] = $newtoken; // Save token in session
$_SESSION[$USER->sesskey . "smsdate"] = $date->getTimestamp(); // Save timestamp to calculate token age
$_SESSION[$USER->sesskey . "examid"] = $examid; // Save exam id for extra security

if ($CFG->emarking_usesms) {
    
    // Validate mobile phone number
    if (! preg_match('/^\+569\d{8}$/', $USER->phone2)) {
        echo json_encode(array(
            'error' => 'Invalid phone number, we expect a full international number (ex: +56912345678)'
        ));
        die();
    }
    
    // Send sms
    if (emarking_send_sms(get_string('yourcodeis', 'mod_emarking') . ": $newtoken", $USER->phone2)) {
        echo json_encode(array(
            'error' => '',
            'message' => get_string('smssent', 'mod_emarking')
        ));
    } else {
        echo json_encode(array(
            'error' => 'Could not connect to SMS server',
            'message' => ''
        ));
    }
} else {
    if (emarking_send_email_code($newtoken, $USER, $course->fullname, $exam->name)) {
        echo json_encode(array(
            'error' => '',
            'message' => get_string('emailsent', 'mod_emarking')
        ));
    } else {
        echo json_encode(array(
            'error' => 'Could not connect to email server',
            'message' => ''
        ));
    }
}