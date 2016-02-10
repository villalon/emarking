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
 * This page processes a zip file containing scanned answers from students
 * that were already identified using the emarking desktop tool
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2012 Jorge Villalon <villalon@gmail.com>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('NO_OUTPUT_BUFFERING', true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once("$CFG->dirroot/lib/weblib.php");
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/print/locallib.php');
global $DB, $CFG, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$emarkingid = required_param('emarkingid', PARAM_INT);
$fileid = required_param('file', PARAM_ALPHANUM);
$merge = required_param('merge', PARAM_BOOL);
// Validate user is logged in and is not guest.
require_login($course->id);
if (isguestuser()) {
    die();
}
$url = new moodle_url('/mod/emarking/print/processanswers.php',
        array(
            'emarkingid' => $emarking->id,
            'file' => $fileid,
            'merge' => $merge));
$urlassignment = new moodle_url('/mod/emarking/view.php', array(
    'id' => $cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_popup_notification_allowed(false);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('uploadanswers', 'mod_emarking'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadinganswersheets', 'mod_emarking'));
// Create progress bar.
$pbar = new progress_bar('extractqr', 500, true);
$pbar->create();
// Count documents ignored and processed.
$totaldocumentsprocessed = 0;
$totaldocumentsignored = 0;
// Setup de directorios temporales.
$tempdir = emarking_get_temp_dir_path($emarking->id);
emarking_initialize_directory($tempdir, true);
$zipfile = emarking_get_path_from_hash($tempdir, $fileid);
// Process documents and obtain results.
list($result, $errors, $totaldocumentsprocessed, $totaldocumentsignored) = emarking_upload_answers($emarking, $zipfile, $course,
        $cm, $pbar);
$pbar->update_full(100, get_string('qrdecodingfinished', 'mod_emarking'));
$percentage = 0;
if ($totaldocumentsprocessed > 0) {
    $percentage = round((($totaldocumentsprocessed - $totaldocumentsignored) / $totaldocumentsprocessed) * 100, 2);
}
$table = new html_table();
$table->attributes ['style'] = "width: 500px; margin-left:auto; margin-right:auto;";
$table->head = array(
    get_string('results', 'mod_emarking'),
    '&nbsp;');
$table->data [] = array(
    get_string('identifieddocuments', 'mod_emarking'),
    $totaldocumentsprocessed . " ($percentage%)");
$table->data [] = array(
    get_string('ignoreddocuments', 'mod_emarking'),
    $totaldocumentsignored);
if (! $result) {
    $table->data [] = array(
        get_string('errors', 'mod_emarking'),
        $errors);
}
echo "<br/>";
echo html_writer::table($table);
$continueurl = new moodle_url('/mod/emarking/view.php',
        array(
            'id' => $cm->id,
            'scan' => $emarking->type != EMARKING_TYPE_MARKER_TRAINING));
echo $OUTPUT->continue_button($continueurl);
echo $OUTPUT->footer();