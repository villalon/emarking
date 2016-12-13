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
 * @copyright 2012-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
global $USER, $DB, $CFG;
require_once ($CFG->dirroot . "/mod/emarking/forms/upload_pdf_form.php");
require_once ($CFG->dirroot . "/repository/lib.php");
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
// Obtains basic data from cm id.
list ($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$sid = required_param('sid', PARAM_INT);
// Get the course module for the emarking, to build the emarking url.
$url = new moodle_url('/mod/emarking/marking/uploadsubmission.php', array(
    'id' => $cm->id,
    'sid' =>$sid
));
$urlemarking = new moodle_url('/mod/emarking/view.php', array(
    'id' => $cm->id
));
// Check that user is logged in and is not guest.
require_login($course->id);
if (isguestuser()) {
    die();
}
if(!$student = $DB->get_record('user', array('id'=>$sid))) {
    print_error('Invalid student id');
}
require_capability('mod/emarking:submit', $context);
$usercanmanageanswersfiles = has_capability('mod/emarking:uploadexam', $context) || is_siteadmin();
if($USER->id != $student->id && !$usercanmanageanswersfiles) {
    $item = array(
        'context' => $context,
        'objectid' => $sid);
    // Add to Moodle log so some auditing can be done.
    \mod_emarking\event\unauthorizedaccess_attempted::create($item)->trigger();
    print_error('Invalid access! This incident will be reported.');
}
// Set navigation parameters.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('uploadsubmission', 'mod_emarking'));
$mform = new mod_emarking_upload_pdf_form(null, array(
    'coursemoduleid' => $cm->id,
    'studentid' => $student->id
));
// If the user cancelled the form, redirect to activity.
if ($mform->is_cancelled()) {
    redirect($urlemarking);
    die();
}
if ($mform->get_data()) {
    // Save uploaded file in Moodle filesystem and check.
    $fs = get_file_storage();
    $itemid = $student->id;
    $filemimetypes = array(
        'dummy',
        'application/pdf'
    );
    $fs->delete_area_files($context->id, 'mod_emarking', 'submission', $itemid);
    $filename = emarking_clean_filename($mform->get_new_filename('submissionfile'));
    $file = $mform->save_stored_file('submissionfile', $context->id, 'mod_emarking', 'submission', $itemid, '/', $filename);
    // Validate that file was correctly uploaded.
    if (!$file) {
        print_error('Could not upload file');
    }
    // Check that the file is a pdf.
    if (!array_search($file->get_mimetype(), $filemimetypes)) {
        $fs->delete_area_files($context->id, 'mod_emarking', 'submission', $itemid);
        print_error(get_string('invalidfilenotpdf', 'mod_emarking'));
    } else {
        $transaction = $DB->start_delegated_transaction();
        $filepath = $file->copy_content_to_temp();
        rename($filepath, $filepath . '.pdf');
        emarking_upload_answers($emarking, $filepath . '.pdf', $course, $cm, false, false, $student);
        $DB->commit_delegated_transaction($transaction);
        // Display confirmation page before moving to process.
        redirect($urlemarking, get_string('uploadanswersuccessful', 'mod_emarking'), 3);
        die();
    }
}
// Display form for uploading zip file.
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), 'myexams');
if(has_capability('mod/emarking:submit', $context)) {
    $mform->display();
}
echo $OUTPUT->footer();