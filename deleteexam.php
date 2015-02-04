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
 * @copyright 2012 Jorge Villalon <jorge.villalon@uai.cl>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once("$CFG->dirroot/repository/lib.php");
require_once($CFG->dirroot."/mod/emarking/locallib.php");

global $DB, $USER;

$examid = required_param('id', PARAM_INT);
$cmid = optional_param('cm', 0, PARAM_INT);
$courseid = optional_param('course', 0, PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);

if($cmid > 0 && !$cm = get_coursemodule_from_id('emarking', $cmid)) {
	print_error(get_string('invalidid', 'mod_emarking') . $cmid);

}

if(!$exam = $DB->get_record('emarking_exams', array('id'=>$examid))) {

	print_error(get_string('invalidexamid', 'mod_emarking') . $examid);

}

list($canbedeleted, $multicourse) = emarking_exam_get_parallels($exam);

if(!$canbedeleted) {

	print_error(get_string('examalreadysent', 'mod_emarking'));

}

if(!$course = $DB->get_record('course', array('id'=>$exam->course))) {

	print_error(get_string('invalidcourse', 'mod_emarking'));

}

if($cmid > 0)
	$context = context_module::instance($cm->id);
else
	$context = context_course::instance($course->id);

require_login($course->id);
if (isguestuser()) {
	die();
}

if($cmid > 0) {
	$url = new moodle_url('/mod/emarking/deleteexam.php', array('id'=>$exam->id, 'cm'=>$cm->id));
	$continueurl = new moodle_url('/mod/emarking/deleteexam.php', array('id'=>$exam->id,'confirm'=>1,'cm'=>$cm->id));
	$cancelurl = new moodle_url('/mod/emarking/exams.php', array('id'=>$cm->id));
} else {
	$url = new moodle_url('/mod/emarking/deleteexam.php', array('id'=>$exam->id, 'course'=>$course->id));
	$continueurl = new moodle_url('/mod/emarking/deleteexam.php', array('id'=>$exam->id,'confirm'=>1,'course'=>$course->id));
	$cancelurl = new moodle_url('/mod/emarking/exams.php', array('course'=>$course->id));
}

$PAGE->set_context($context);
$PAGE->set_course($course);
if($cmid > 0)
	$PAGE->set_cm($cm);
$PAGE->set_url($url);
$PAGE->navbar->add(get_string('emarking', 'mod_emarking'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

if($confirm) {
	$fs = get_file_storage();

	$fs->delete_area_files($context->id, 'emarking', 'exams', $exam->id);
	$DB->delete_records('emarking_exams',array('file'=>$exam->file));

	redirect($cancelurl, get_string('examdeleted', 'mod_emarking'),2);

	echo $OUTPUT->header();
	echo $OUTPUT->footer();
	die();
}

echo $OUTPUT->header();

echo $OUTPUT->confirm(get_string('examdeleteconfirm', 'mod_emarking',$exam->name), $continueurl, $cancelurl);

echo $OUTPUT->footer();
die();

