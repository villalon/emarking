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
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once($CFG->dirroot . "/mod/emarking/print/locallib.php");
global $DB, $USER;
// Id of the exam to be deleted.
$examid = required_param('id', PARAM_INT);
// Deleting was confirmed.
$confirm = optional_param('confirm', false, PARAM_BOOL);
// We obtain the exam object from the DB.
if (! $exam = $DB->get_record('emarking_exams', array(
    'id' => $examid))) {
    print_error(get_string('invalidexamid', 'mod_emarking') . $examid);
}
// We get the parallel course according to the regex.
list($canbedeleted, $multicourse) = emarking_exam_get_parallels($exam);
if (! $canbedeleted) {
    print_error(get_string('examalreadysent', 'mod_emarking'));
}
// We get the course.
if (! $course = $DB->get_record('course', array(
    'id' => $exam->course))) {
    print_error(get_string('invalidcourse', 'mod_emarking'));
}
// We use the course context or module context depending on the link.
$context = context_course::instance($course->id);
// User must be logged in and can not be guest.
require_login($course->id);
if (isguestuser()) {
    die();
}
if (! has_capability('mod/emarking:uploadexam', $context)) {
    print_error("Invalid access, trying to delete an exam");
}
$url = new moodle_url('/mod/emarking/print/deleteexam.php', array(
    'id' => $exam->id,
    'course' => $course->id));
$continueurl = new moodle_url('/mod/emarking/print/deleteexam.php',
        array(
            'id' => $exam->id,
            'confirm' => 1,
            'course' => $course->id));
$cancelurl = new moodle_url('/mod/emarking/print/exams.php', array(
    'course' => $course->id));
// We set up the page: context, course, url, navigation, heading and layout.
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_url($url);
$PAGE->navbar->add(get_string('emarking', 'mod_emarking'));
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->set_pagelayout('incourse');
// If the user confirmed, delete the files from the exam, the exam object and redirect.
if ($confirm) {
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'emarking', 'exams', $exam->id);
    $DB->delete_records('emarking_exams', array(
        'file' => $exam->file));
    redirect($cancelurl, get_string('examdeleted', 'mod_emarking'), 2);
    echo $OUTPUT->header();
    echo $OUTPUT->footer();
    die();
}
// Show the confirmation message.
echo $OUTPUT->header();
echo $OUTPUT->confirm(get_string('examdeleteconfirm', 'mod_emarking', $exam->name), $continueurl, $cancelurl);
echo $OUTPUT->footer();
die();