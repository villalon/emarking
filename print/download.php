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
 * @copyright 2012-2015 Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config.php");
if ($CFG->version > 2015111600) {
    require_once($CFG->dirroot . "/lib/pdflib.php");
    require_once($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi_bridge.php");
} else {
    require_once($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php");
}
require_once($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi.php");
require_once($CFG->dirroot . "/mod/emarking/lib/phpqrcode/phpqrcode.php");
require_once($CFG->dirroot . "/mod/emarking/lib.php");
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once("locallib.php");
require_once($CFG->libdir . "/eventslib.php");
require_once($CFG->dirroot . "/mod/emarking/classes/event/invalidtokendownload_attempted.php");
global $USER;
// We validate login first as this page can be reached by the copy center
// whom will not be logged in the course for downloading.
if (! isloggedin() || isguestuser()) {
    echo json_encode(array(
        "error" => get_string("usernotloggedin", "mod_emarking")));
    die();
}
$sesskey = required_param("sesskey", PARAM_ALPHANUM);
$examid = optional_param("examid", 0, PARAM_INT);
$token = optional_param("token", 0, PARAM_INT);
$multiplepdfs = optional_param("multi", false, PARAM_BOOL);
$incourse = optional_param("incourse", false, PARAM_BOOL);
// Validate session key.
if ($sesskey != $USER->sesskey) {
    echo json_encode(array(
        "error" => get_string("invalidsessionkey", "mod_emarking")));
    die();
}
// If we have the token and session id ok we get the exam id from the session.
if ($token > 9999) {
    $examid = $_SESSION [$USER->sesskey . "examid"];
}
// We get the exam object.
if (! $exam = $DB->get_record("emarking_exams", array(
    "id" => $examid))) {
    echo json_encode(array(
        "error" => get_string("invalidexamid", "mod_emarking")));
    die();
}
// We get the course from the exam.
if (! $course = $DB->get_record("course", array(
    "id" => $exam->course))) {
    print_error(get_string("invalidcourseid", "mod_emarking"));
    die();
}
$contextcat = context_coursecat::instance($course->category);
$contextcourse = context_course::instance($course->id);
$url = new moodle_url("/mod/emarking/print/download.php",
        array(
            "examid" => $exam->id,
            "token" => $token,
            "sesskey" => $sesskey));
$coursecategoryurl = new moodle_url("/mod/emarking/print/printorders.php", array(
    "category" => $course->category));
$courseurl = new moodle_url("/mod/emarking/print/exams.php", array(
    "course" => $course->id));
// Validate capability in the category context.
if (! (has_capability("mod/emarking:downloadexam", $contextcat) || has_capability("mod/emarking:downloadexam", $contextcourse))) {
    $item = array(
        "context" => $contextcourse,
        "objectid" => $exam->emarking);
    // Add to Moodle log so some auditing can be done.
    \mod_emarking\event\invalidaccessdownload_attempted::create($item)->trigger();
    echo json_encode(array(
        "error" => get_string("invalidaccess", "mod_emarking")));
    die();
}
// If a token was sent and it was not valid, log and die.
if ($token > 9999 && $_SESSION [$USER->sesskey . "smstoken"] !== $token) {
    $item = array(
        "context" => $contextcourse,
        "objectid" => $exam->emarking);
    // Add to Moodle log so some auditing can be done.
    \mod_emarking\event\invalidtokendownload_attempted::create($item)->trigger();
    $PAGE->set_context($contextcourse);
    $PAGE->set_url($url);
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string("eventinvalidtokengranted", "mod_emarking"), "notifyproblem");
    $buttonurl = $incourse ? $courseurl : $coursecategoryurl;
    echo $OUTPUT->single_button($buttonurl, get_string("back"), "get");
    echo $OUTPUT->footer();
    die();
}
// A token was sent to validate download it will have 5 digits, otherwise it should be 0.
if ($token > 9999 && $_SESSION [$USER->sesskey . "smstoken"] === $token) {
    $now = new DateTime();
    $tokendate = new DateTime();
    $tokendate->setTimestamp($_SESSION [$USER->sesskey . "smsdate"]);
    $diff = $now->diff($tokendate);
    if ($diff->i > 5 && false) {
        $PAGE->set_context($contextcourse);
        $PAGE->set_url($url);
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string("tokenexpired", "mod_emarking"), "notifyproblem");
        $buttonurl = $incourse ? $courseurl : $coursecategoryurl;
        echo $OUTPUT->single_button($buttonurl, get_string("back"), "get");
        echo $OUTPUT->footer();
        die();
    }
    // Add to Moodle log so some auditing can be done.
    \mod_emarking\event\exam_downloaded::create_from_exam($exam, $contextcourse)->trigger();
    emarking_download_exam($examid, $multiplepdfs, null, null, null, null, true);
    die();
}
// If the token was not sent, then create new token,
// save data in session variables and send through email or mobile phone.
$newtoken = rand(10000, 99999); // Generate random 5 digits token.
$date = new DateTime();
$_SESSION [$USER->sesskey . "smstoken"] = $newtoken; // Save token in session.
$_SESSION [$USER->sesskey . "smsdate"] = $date->getTimestamp(); // Save timestamp to calculate token age.
$_SESSION [$USER->sesskey . "examid"] = $examid; // Save exam id for extra security.
if ($CFG->emarking_usesms) {
    // Validate mobile phone number.
    if ($CFG->emarking_mobilephoneregex && ! preg_match('/^' . $CFG->emarking_mobilephoneregex . '$/', $USER->phone2)) {
        echo json_encode(
                array(
                    "error" => get_string("invalidphonenumber", "mod_emarking") . " " . $USER->phone2));
        die();
    }
    // Send sms.
    if (emarking_send_sms(get_string("yourcodeis", "mod_emarking") . ": $newtoken", $USER->phone2)) {
        echo json_encode(
                array(
                    "error" => "",
                    "message" => get_string("smssent", "mod_emarking")));
    } else {
        echo json_encode(
                array(
                    "error" => get_string("smsserverproblem", "mod_emarking"),
                    "message" => ""));
    }
} else {
    if (emarking_send_email_code($newtoken, $USER, $course->fullname, $exam->name)) {
        echo json_encode(
                array(
                    "error" => "",
                    "message" => get_string("emailsent", "mod_emarking")));
    } else {
        echo json_encode(
                array(
                    "error" => get_string("errorsendingemail", "mod_emarking"),
                    "message" => ""));
    }
}