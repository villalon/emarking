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
 * @copyright 2016 Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('NO_OUTPUT_BUFFERING', true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/marking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/print/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/marking/forms/export_form.php');
global $DB, $CFG, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$draftid = required_param('did', PARAM_INT);
// Validate user is logged in and is not guest.
require_login($course->id);
if (isguestuser()) {
    die();
}
if(!$draft = $DB->get_record('emarking_draft', array('id'=>$draftid))) {
    print_error('Invalid draft id');
}
if(!$submission = $DB->get_record('emarking_submission', array('id'=>$draft->submissionid))) {
    print_error('Invalid submission id');
}
if(!$user = $DB->get_record('user', array('id'=>$submission->student))) {
    print_error('Invalid draft id, no user');
}
$pdffilename = 'response_' . $emarking->id . '_' . $draft->id . '.pdf';
$fs = get_file_storage();
$pdffile = $fs->get_file($context->id, 'mod_emarking', 'response', $draft->id, '/', $pdffilename);
if($pdffile->get_timecreated() < $draft->timemodified) {
    $pdffile = NULL;
}
if(!$pdffile && !emarking_create_response_pdf($draft, $user, $context, $cm->id)) {
   print_error('Couldnt create feedback PDF');
}
$url = $CFG->wwwroot . "/pluginfile.php/". $context->id . "/mod_emarking/response/".$draft->id."/".$pdffilename.'?'.random_string();
redirect($url);
die();