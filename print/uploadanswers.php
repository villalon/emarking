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
 * @copyright 2012 Jorge Villalon <villalon@gmail.com>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
global $USER, $DB, $CFG;
require_once($CFG->dirroot . "/mod/emarking/forms/upload_form.php");
require_once($CFG->dirroot . "/repository/lib.php");
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once($CFG->dirroot . "/mod/emarking/print/locallib.php");
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// Get the course module for the emarking, to build the emarking url.
$url = new moodle_url('/mod/emarking/print/uploadanswers.php', array(
    'id' => $cm->id));
$urlemarking = new moodle_url('/mod/emarking/view.php', array(
    'id' => $cm->id));
// Check that user is logged in and is not guest.
require_login($course->id);
if (isguestuser()) {
    die();
}
// Set navigation parameters.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('uploadanswers', 'mod_emarking'));
// Options for uploading the zip file within the form.
$options = array(
    'subdirs' => 0,
    'maxbytes' => get_max_upload_file_size($CFG->maxbytes, $course->maxbytes, $course->maxbytes),
    'maxfiles' => 1,
    'accepted_types' => '.zip',
    'return_types' => FILE_INTERNAL);
$mform = new mod_emarking_upload_form(null,
        array(
            'coursemoduleid' => $cm->id,
            'emarkingid' => $emarking->id,
            'options' => $options));
// If the user cancelled the form, redirect to activity.
if ($mform->is_cancelled()) {
    redirect($urlemarking);
} else if ($mform->get_data()) {
    // Save uploaded file in Moodle filesystem and check.
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_emarking', 'upload', $emarking->id);
    $file = $mform->save_stored_file('assignment_file', $context->id, 'mod_emarking', 'upload', $emarking->id, '/',
            emarking_clean_filename($mform->get_new_filename('assignment_file')));
    // Validate that file was correctly uploaded.
    if (! $file) {
        print_error("Could not upload file");
    }
    // Check that the file is a zip.
    if ($file->get_mimetype() !== 'application/zip') {
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox');
        echo $OUTPUT->heading(get_string('error'));
        echo $OUTPUT->error_text(get_string('fileisnotzip', 'mod_emarking'));
        echo $OUTPUT->continue_button($urlemarking);
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        die();
    }
    // Parameters for execution.
    $merge = isset($mform->get_data()->merge) ? false : true; // Inverted as question in form was inverted.
    $nocache = rand(1, 999999);
    // File is ok, process.
    // Setup de directorios temporales.
    $tempdir = emarking_get_temp_dir_path($emarking->id);
    emarking_initialize_directory($tempdir, true);
    // Gets file hash.
    $newfile = emarking_get_path_from_hash($tempdir, $file->get_pathnamehash(), '', true);
    // Display confirmation page before moving to process.
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('confirmprocess', 'mod_emarking'));
    echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "uploadanswers");
    echo $OUTPUT->box_start('generalbox');
    // If the user confirms it goes to process.php.
    $confirmurl = new moodle_url('/mod/emarking/print/processanswers.php',
            array(
                'id' => $cm->id,
                'merge' => $merge,
                'file' => $file->get_pathnamehash(),
                'emarkingid' => $emarking->id));
    // Message changes if it will be merged.
    $confirmessage = $merge ? 'confirmprocessfilemerge' : 'confirmprocessfile';
    // Show confirmation buttons.
    echo $OUTPUT->confirm(
            get_string($confirmessage, 'mod_emarking',
                    array(
                        'file' => $file->get_filename(),
                        'assignment' => $emarking->name)), $confirmurl, $urlemarking);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    die();
}
// Display form for uploading zip file.
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "uploadanswers");
$mform->display();
echo $OUTPUT->footer();