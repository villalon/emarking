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
 * @copyright 2014 Jorge Villalon <jorge.villalon@uai.cl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot."/mod/emarking/locallib.php");

global $DB, $USER;

$submissionid = required_param('ids', PARAM_INT);
$cmid = required_param('cm', PARAM_INT);
$newstatus = required_param('status', PARAM_INT);
$confirm = required_param('status', PARAM_INT);

if(!$cm = get_coursemodule_from_id('emarking', $cmid)) {
	print_error(get_string('invalidid', 'mod_emarking') . $cmid);
}

if(!$submission = $DB->get_record('emarking_submission', array('id'=>$submissionid))) {
	print_error(get_string('invalidsubmission', 'mod_emarking') . $submissionid);
}

if(!$emarking = $DB->get_record('emarking', array('id'=>$submission->emarking))) {
	print_error(get_string('invalidsubmission', 'mod_emarking') . $submission->emarking);
}

if(!$course = $DB->get_record('course', array('id'=>$emarking->course))) {
	print_error(get_string('invalidcourse', 'mod_emarking'));
}

$statuses = emarking_get_statuses_as_array();

if(!in_array($newstatus, $statuses)) {
	print_error("Invalid status");
}

$context = context_module::instance($cm->id);

require_login($course->id);
if (isguestuser()) {
	die();
}

$useristeacher = emarking_user_is_teacher($course->id);

if(!is_siteadmin($USER) && (!$useristeacher || !has_capability('mod/assign:grade', $context))) {
	print_error('Invalid access, this will be notified!');
}

$url = new moodle_url('/mod/emarking/updatesubmission.php', array('ids'=>$submission->id, 'cm'=>$cm->id, 'status'=>$newstatus));
$continueurl = new moodle_url('/mod/emarking/updatesubmission.php', array('ids'=>$submission->id,'confirm'=>1,'cm'=>$cm->id, 'status'=>$newstatus));
$cancelurl = new moodle_url('/mod/emarking/view.php', array('id'=>$cm->id));

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_url($url);
$PAGE->navbar->add(get_string('emarking', 'mod_emarking'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

if($confirm) {
	$submission->status = $newstatus;
	$DB->update_record('emarking_submission',$submission);
	redirect($cancelurl, get_string('transactionsuccessfull', 'mod_emarking'),2);
	die();
}

echo $OUTPUT->header();

$submission->newstatus = $newstatus;
echo $OUTPUT->confirm(get_string('updatesubmissionconfirm', 'mod_emarking', $submission), $continueurl, $cancelurl);

echo $OUTPUT->footer();
die();

