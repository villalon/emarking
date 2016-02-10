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
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
global $DB, $USER;
// Obtain parameter from URL.
$examid = required_param('id', PARAM_INT);
require_login();
if (! $exam = $DB->get_record('emarking_exams', array(
    'id' => $examid))) {
    print_error(get_string('invalid_exam_id', 'mod_emarking'));
}
if (! $requestedby = $DB->get_record('user', array(
    'id' => $exam->requestedby))) {
    print_error(get_string('invalid_exam_id', 'mod_emarking'));
}
if (! $course = $DB->get_record('course', array(
    'id' => $exam->course))) {
    print_error(get_string('invalid_exam_id', 'mod_emarking'));
}
$context = context_coursecat::instance($course->category);
if (! has_capability('mod/emarking:downloadexam', $context)) {
    print_error('Invalid access');
}
$postsubject = $course->fullname . ': ' . $exam->name . '. ' . get_string('printnotification', 'mod_emarking') . ' [' . $exam->id .
         ']';
// Create the email to be sent.
$posthtml = '<html>';
$posthtml .= '<table><tr><th colspan="2">' . get_string('printnotification', 'mod_emarking') . '</th></tr>';
$posthtml .= '<tr><td>' . get_string('examid', 'mod_emarking') . '</td><td>' . $exam->id . '</td></tr>';
$posthtml .= '<tr><td>' . get_string('fullnamecourse') . '</td><td>' . $course->fullname . '</td></tr>';
$posthtml .= '<tr><td>' . get_string('shortnamecourse') . '</td><td>' . $course->shortname . '</td></tr>';
$posthtml .= '<tr><td>' . get_string('requestedby', 'mod_emarking') . '</td><td>' . $requestedby->username . '</td></tr>';
$posthtml .= '<tr><td>' . get_string('examdate', 'mod_emarking') . '</td><td>' . date("d M Y - H:i", $exam->examdate) .
             '</td></tr>';
$posthtml .= '</table>';
$posthtml .= '</html>';
// Create the email to be sent.
$posttext = get_string('printnotification', 'mod_emarking') . '\n';
$posttext .= get_string('examid', 'mod_emarking') . ' : ' . $exam->id . '\n';
$posttext .= get_string('fullnamecourse') . ': ' . $course->fullname . '\n';
$posttext .= get_string('shortnamecourse') . ': ' . $course->shortname . '\n';
$posttext .= get_string('requestedby', 'mod_emarking') . ': ' . $requestedby->username . '\n';
$posttext .= get_string('examdate', 'mod_emarking') . ': ' . date("d M Y - H:i", $exam->examdate) . '\n';
emarking_send_notification($exam, $course, $postsubject, $posttext, $posthtml);
$exam->notified = 1;
$exam->status = EMARKING_EXAM_PRINTED;
$DB->update_record('emarking_exams', $exam);
redirect(new moodle_url('/mod/emarking/print/printorders.php', array(
    'category' => $course->category,
    'status' => '2')), get_string('printnotificationsent', 'mod_emarking'), 2);