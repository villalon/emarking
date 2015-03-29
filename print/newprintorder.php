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
 * Page to send a new print order
 *
 * @package    mod
 * @subpackage emarking
 * @copyright  2013 Jorge Villalón
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname (dirname( dirname ( dirname ( __FILE__ )) ) ) . '/config.php');
require_once ($CFG->dirroot . "/mod/emarking/forms/exam_form.php");
require_once ("$CFG->dirroot/repository/lib.php");
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");

global $DB, $USER;

// Obtain parameter from URL
$cmid = optional_param ( 'cm', 0, PARAM_INT );
$courseid = optional_param ( 'course', 0, PARAM_INT );
$examid = optional_param ( 'id', 0, PARAM_INT );

if($cmid > 0 && !$cm = get_coursemodule_from_id('emarking', $cmid)) {
	print_error ( get_string('invalidid','mod_emarking' ) . " id: $cmid" );
}

if($cmid > 0 && !$emarking = $DB->get_record('emarking', array('id'=>$cm->instance))) {
	print_error ( get_string('invalidid','mod_emarking' ) . " id: $cmid" );
}

if($cmid > 0) {
	$courseid = $cm->course;
}

// Validate that the parameter corresponds to a course
if (! $course = $DB->get_record ( 'course', array ('id' => $courseid))) {
	print_error ( get_string('invalidcourseid','mod_emarking' ) . " id: $courseid" );

}

// If we are editing an instance
$exam = null;
if ($examid) {
	if (! $exam = $DB->get_record ( 'emarking_exams', array ('id' => $examid))) {
		print_error ( get_string('invalidexamid','mod_emarking' ) );		
	}
	
	if ($exam->status >= EMARKING_EXAM_SENT_TO_PRINT) {
		print_error ( get_string('examalreadysent','mod_emarking' ) );
	}
	
	list($canbedeleted, $multicourse) = emarking_exam_get_parallels($exam);
	
	if(!$canbedeleted) {
		print_error(get_string('examalreadysent', 'mod_emarking'));
	}	
}

// For uploading exams it should always user course context, as no emarking activity is needed
// to send a print order
$context = context_course::instance ( $course->id );
$courseurl =new moodle_url ($CFG->dirroot.'/course/view.php',array('id'=>$course->id));

// First check that the user is logged in
require_login ( $course->id );
if (isguestuser ()) {
	die ();
}

// URL for current page and eMarking home
if($cmid > 0) {
	$url = new moodle_url ( '/mod/emarking/print/newprintorder.php', array ('cm' => $cm->id));
	$cancelurl = new moodle_url ( '/mod/emarking/view.php', array ('id' => $cmid));
} else {
	$url = new moodle_url ( '/mod/emarking/print/newprintorder.php', array ('course' => $course->id));
	$cancelurl = new moodle_url ( '/course/view.php', array ('id' => $course->id));
}

$PAGE->set_context ( $context );
$PAGE->set_course($course);
if($cmid > 0)
	$PAGE->set_cm($cm);
$PAGE->set_url ( $url );
$PAGE->set_heading ( $course->fullname );
$PAGE->set_pagelayout ( 'incourse' );
$PAGE->navbar->add(get_string('newprintorder','mod_emarking'));

// Verify capability for security issues
if (! has_capability ( 'mod/emarking:uploadexam', $context )) {
	$item = array (
			'context' => context_module::instance ( $cm->id ),
			'objectid' => $cm->id 
	);
	// Add to Moodle log so some auditing can be done
	\mod_emarking\event\invalidaccess_granted::create ( $item )->trigger ();
	print_error ( get_string('invalidaccess','mod_emarking' ) );
}

// Options for uploading files
$options = array (
		'subdirs' => 0,
		'maxbytes' => get_max_upload_file_size ( $CFG->maxbytes, $course->maxbytes, $course->maxbytes ),
		'maxfiles' => 1,
		'accepted_types' => '*.pdf',
		'return_types' => FILE_INTERNAL 
);

// Creates form from emarking exam form which extends moodleform
$mform = new emarking_exam_form ( null, 
		array ('courseid' => $course->id, 'options' => $options, 'examid' => $examid, 'cmid' => $cmid) );

if ($examid > 0) {
	$mform->set_data ($exam);
} elseif($cmid > 0) {
	$defaultdata = new stdClass();
	$defaultdata->name = $cm->name;
	$mform->set_data ($defaultdata);
}
// The form submits to the same page, therefore these ifs control the different stages
	
// If the cancel button was pressed, redirect to emarking home
if ($mform->is_cancelled ()) {
	redirect ( $cancelurl );
} 
// If the form contains data
else if ($data = $mform->get_data()) {

	// If we are editing and data was submitted delete parallel course if any
	if($examid > 0) {
		$DB->delete_records_select('emarking_exams',"file = $exam->file AND id != $examid");
	}
	
	// We get the draftid from the form
	$draftid =  file_get_submitted_draft_itemid('exam_files');
	$usercontext = context_user::instance($USER->id);
	$fs = get_file_storage ();
	$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftid);
	
	$tempdir = emarking_get_temp_dir_path( $course->id );
	emarking_initialize_directory ( $tempdir, true );
	
	$numpagesprevious=-1;
	$exampdfs = array();
	foreach($files as $uploadedfile) {
		if($uploadedfile->get_mimetype() !== 'application/pdf')
			continue;
		
		$filename = $uploadedfile->get_filename();
		$filename = emarking_clean_filename ( $filename );
		$newfilename = $tempdir . '/' . $filename;
		
		$pdffile = emarking_get_path_from_hash($tempdir, $uploadedfile->get_pathnamehash());
		
		// Executes pdftk burst to get all pages separated
		$numpages = emarking_pdf_count_pages ( $newfilename, $tempdir, false );
		
		if (! is_numeric ( $numpages ) || $numpages < 1 || ($numpagesprevious >= 0 && $numpagesprevious != $numpages)) {
			echo $OUTPUT->header ();
			echo $OUTPUT->heading ( get_string ( 'error' ) );
			echo $OUTPUT->error_text ( get_string('invalidpdfnopages','mod_emarking' ) );
			echo $OUTPUT->continue_button ( $cancelurl );
			die ();
		}
		
		$exampdfs[] = array('pathname'=>$pdffile, 'filename'=>$filename);
	}
	
	$studentsnumber = emarking_get_students_count_for_printing($course->id);
	
	// A new exam object is created and its attributes filled from form data
	if ($examid == 0)
		$exam = new stdClass ();
	$exam->course = $course->id;
	$exam->courseshortname = $course->shortname;
	$exam->name = $mform->get_data ()->name;
	$exam->examdate = $mform->get_data ()->examdate;
	
	$exam->emarking = isset ( $mform->get_data ()->emarking ) ? 1 : 0;
	$exam->headerqr = isset ( $mform->get_data ()->headerqr ) ? 1 : 0;
	$exam->printrandom = isset ( $mform->get_data ()->printrandom ) ? 1 : 0;
	$exam->printlist = isset ( $mform->get_data ()->printlist ) ? 1 : 0;
	
	if (! isset ( $mform->get_data ()->printdoublesided ))
		$mform->get_data ()->printdoublesided = false;
	
	$exam->extrasheets = $mform->get_data ()->extrasheets;
	$exam->extraexams = $mform->get_data ()->extraexams;
	$exam->usebackside = $mform->get_data ()->printdoublesided;
	if ($examid == 0)
		$exam->timecreated = time ();
	$exam->timemodified = time ();
	$exam->requestedby = $USER->id;
	
	$exam->totalstudents = $studentsnumber;
	
	// Get the enrolments as a comma separated values
	$enrollist = array();

	if (! empty ( $mform->get_data ()->enrolments )) {
		$enrolments = $mform->get_data ()->enrolments;
		foreach ( $enrolments as $key => $enrolment ) {
			if (! empty ( $enrolment )) {
				$enrollist[] = $key;
			}
		}
	}
	
	$exam->enrolments = implode(",", $enrollist);
	
	$exam->printdate = 0;
	$exam->status = isset ( $mform->get_data()->sendtoprint ) ? EMARKING_EXAM_SENT_TO_PRINT : EMARKING_EXAM_UPLOADED;
	
	// Calculate total pages for exam
	$exam->totalpages = $numpages;

	// Exam is inserted and new id recovered from operation
	if ($examid == 0)
		$exam->id = $DB->insert_record ( 'emarking_exams', $exam );
	else
		$DB->update_record ( 'emarking_exams', $exam );
	
		// If we are editing a print order and a file was submitted, delete the previous file
		if($examid > 0) {
			$fs->delete_area_files ( $context->id, 'mod_emarking', 'exams', $exam->id );
		}
		
		foreach($exampdfs as $exampdf) {
			// Save the submitted file to check if it's a PDF
			$filerecord = array('component' => 'mod_emarking', 'filearea' => 'exams',
				'contextid' => $context->id, 'itemid' => $exam->id, 'filepath' => '/',
				'filename' => $exampdf['filename']);
			$file = $fs->create_file_from_pathname($filerecord, $exampdf['pathname']);
		}
		// Update exam object to store the PDF's file id
		$exam->file = $file->get_id ();
		if (! $DB->update_record ( 'emarking_exams', $exam )) {
			$fs->delete_area_files ( $contextid, 'emarking', 'exams', $exam->id );
			
			print_error ( get_string('errorsavingpdf','mod_emarking' ) );
		}
	
	
	// Send new print order notification
	emarking_send_newprintorder_notification ( $exam, $course );

	// If it is a multi-course submission, insert several exams
	if (! empty ( $mform->get_data ()->multisecciones )) {
		$multisecciones = $mform->get_data ()->multisecciones;
		foreach ( $multisecciones as $key => $multiseccion ) {
			if (! empty ( $multiseccion )) {
				if ($thiscourse = $DB->get_record ( 'course', array ('shortname' => $key))) {
					
					$studentsnumber = emarking_get_students_count_for_printing($thiscourse->id);
					
					$newexam = $exam;
					$newexam->id = null;
					$newexam->totalstudents = $studentsnumber;
					$newexam->course = $thiscourse->id;
					$newexam->id = $DB->insert_record ( 'emarking_exams', $newexam );
					// Send new print order notification
					emarking_send_newprintorder_notification ( $newexam, $thiscourse );
				}
			}
		}
	}
	
	// Done, now redirect to index
	if($cmid > 0) {
		$previewurl = new moodle_url ( '/mod/emarking/print/exams.php', array ('id' => $cm->id, 'examid' => $exam->id));
	} else {
		$previewurl = new moodle_url ( '/mod/emarking/print/exams.php', array ('course' => $course->id, 'examid' => $exam->id));
	}
	redirect($previewurl, get_string('newprintordersuccess','mod_emarking' ));

	die ();
}

// Moodle header
echo $OUTPUT->header ();

echo $OUTPUT->heading_with_help(get_string('newprintorder','mod_emarking'), 'newprintorder', 'mod_emarking');

if($cmid > 0) {
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "newprintorder" );
}

$mform->display ();

echo $OUTPUT->footer ();

