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
 * @copyright 2014-onwards Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
global $DB, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$draftid = required_param('ids', PARAM_INT);
$newstatus = required_param('status', PARAM_INT);
$confirm = required_param('status', PARAM_INT);
if (! $draft = $DB->get_record('emarking_draft', array(
    'id' => $draftid))) {
    print_error(get_string('invalidraft', 'mod_emarking') . $draftid);
}
if (! $submission = $DB->get_record('emarking_submission', array(
    'id' => $draft->submissionid))) {
    print_error(get_string('invalidsubmission', 'mod_emarking') . $draft->submissionid);
}
$statuses = emarking_get_statuses_as_array();
if (! in_array($newstatus, $statuses)) {
    print_error("Invalid status");
}
require_login($course->id);
if (isguestuser()) {
    die();
}
if (! is_siteadmin($USER) &&
         (! has_capability('mod/emarking:supervisegrading', $context) || ! has_capability('mod/emarking:grade', $context))) {
    print_error('Invalid access, this will be notified!');
}
$url = new moodle_url('/mod/emarking/marking/updatesubmission.php',
        array(
            'ids' => $submission->id,
            'id' => $cm->id,
            'status' => $newstatus));
$continueurl = new moodle_url('/mod/emarking/marking/updatesubmission.php',
        array(
            'ids' => $submission->id,
            'confirm' => 1,
            'id' => $cm->id,
            'status' => $newstatus));
$cancelurl = new moodle_url('/mod/emarking/view.php', array(
    'id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_url($url);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('emarking', 'mod_emarking'));
$PAGE->set_pagelayout('incourse');
if ($confirm) {
    $draft->status = $newstatus;
    $DB->update_record('emarking_draft', $draft);
    redirect($cancelurl, get_string('transactionsuccessfull', 'mod_emarking'), 2);
    die();
}
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
$draft->newstatus = $newstatus;
echo $OUTPUT->confirm(get_string('updatesubmissionconfirm', 'mod_emarking', $draft), $continueurl, $cancelurl);
echo $OUTPUT->footer();
die();