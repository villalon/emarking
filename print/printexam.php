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
 * This page processes a zip file containing scanned answers from students
 * that were already identified using the emarking desktop tool
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2012 Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define ( 'NO_OUTPUT_BUFFERING', true );
require_once (dirname ( dirname ( dirname (dirname ( __FILE__) ) ) ) . '/config.php');
require_once ("$CFG->dirroot/lib/weblib.php");
require_once ($CFG->dirroot . '/repository/lib.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ($CFG->dirroot . '/mod/emarking/forms/printexam_form.php');
require_once ($CFG->dirroot . '/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php');
require_once ($CFG->dirroot . '/mod/assign/feedback/editpdf/fpdi/fpdi.php');
require_once ($CFG->dirroot . '/mod/emarking/print/locallib.php');

require_once ($CFG->dirroot . '/mod/emarking/lib/phpqrcode/phpqrcode.php');

global $DB, $CFG, $USER;

$examid = required_param ( 'exam', PARAM_INT );
$confirm = optional_param ( 'confirm', false, PARAM_BOOL );
$debugprinting = optional_param ( 'debug', false, PARAM_BOOL );

// Validate exam
if (! $exam = $DB->get_record ( 'emarking_exams', array (
		'id' => $examid 
) )) {
	print_error ( get_string ( 'invalidemarkingid', 'mod_emarking' ) . ':' . $examid );
}

// Validate course
if (! $course = $DB->get_record ( 'course', array (
		'id' => $exam->course 
) )) {
	print_error ( get_string ( 'invalidcourseid', 'mod_emarking' ) . ': ' . $exam->course );
}

// Get context for module
$context = context_course::instance ( $course->id );

// Validate user is logged in and is not guest
require_login ();
if (isguestuser ()) {
	die ();
}

$printers = explode ( ',', $CFG->emarking_printername );
if (count ( $printers ) <= 0) {
	print_error ( 'No printers cnofigured. Please notify administrator.' );
}

$url = new moodle_url ( '/mod/emarking/print/printexam.php', array (
		'exam' => $exam->id 
) );

$PAGE->set_pagelayout ( 'incourse' );
$PAGE->set_popup_notification_allowed ( false );
$PAGE->set_url ( $url );
$PAGE->set_context ( $context );
$PAGE->set_course ( $course );
$PAGE->set_title ( get_string ( 'processtitle', 'mod_emarking' ) );
$PAGE->set_heading ( $course->fullname );
$PAGE->navbar->add ( get_string ( 'printexam', 'mod_emarking' ) );

$form = new emarking_printexam_form ( null, array (
		'examid' => $exam->id, 'debug' => $debugprinting 
) );

if ($form->is_cancelled ()) {
	$continue_url = new moodle_url ( '/mod/emarking/print/printorders.php', array (
			'category' => $course->category 
	) );
	redirect ( $continue_url );
}
echo $OUTPUT->header ();
echo $OUTPUT->heading ( get_string ( 'printexam', 'mod_emarking' ) );

$result = exec ( 'lpstat -p -d' );
$parts = explode ( ":", $result );
if (count ( $parts ) != 2 && !$debugprinting) {
	print_error ( 'Invalid printer setup. You must install cups and set a default printer for eMarking to be able to print.' );
} else {
	$printer = strtoupper ( trim ( $parts [1] ) );
	echo $OUTPUT->box ( 'Default printer: ' . $printer );
}

if ($form->get_data ()) {
	
	$printer = $printers [$form->get_data ()->printername];
	
	if(!$debugprinting) {
	// TODO This is outrageous!
	if ($printer == "Edificio-A-CentralDeApuntes") {
		$target = "10.50.2.124";
	}
	
	// TODO This is outrageous!
	if ($printer == "Edificio-C-mesonSecretaria") {
		$target = "10.60.2.8";
	}
	
	// TODO This is outrageous!
	if ($printer == "secretaria-edificio-D") {
		$target = "10.110.2.244";
	}
	
	// codigo extra borrar
	$cmd_result = shell_exec ( "ping -c 1 -w 1 " . $target );
	$result = explode ( ",", $cmd_result );
	if (eregi ( "0 received", $result [1] )) {
		$estado = "Fatal error trying to print, printer is off line";
	} elseif (eregi ( "1 received", $result [1] )) {
		$estado = "OK";
	} else {
		$estado = "Fatal error trying to print, printer is unknown";
	}
	
	if ($estado != "OK") {
		print_error ( $estado );
	}
	}
	
	$pbar = new progress_bar ( 'printing', 500, true );
	if ($exam->printrandom == 1) {
		$rs = emarking_get_groups_for_printing ( $course->id );
		foreach ( $rs as $r ) {
			$rsg = emarking_download_exam ( $exam->id, // Id of exam to print
true, // Print using multiple pdfs
$r->id, // id group for print random
$pbar, true, $printer );
			if (! $rsg) { // Send directly to printer
				print_error ( 'Fatal error trying to print' );
			}
			
			$archive_folder = $rsg; // the folder which you archivate
			$dirs [] = $rsg;
			$dir = preg_replace ( '/[\/]{2,}/', '/', $archive_folder . "/" );
			
			$dh = opendir ( $dir );
			while ( $file = readdir ( $dh ) ) {
				if ($file != '.' && $file != '..') {
					$files [] = $dir . $file;
				}
			}
			closedir ( $dh );
		}
		
		foreach ( $files as $f ) {
			unlink ( $f );
		}
		foreach ( $dirs as $d ) {
			rmdir ( $d );
		}
	} else {
		if (! emarking_download_exam ( $exam->id, // Id of exam to print
                                       true, // Print using multiple pdfs
                                       null, 
		                               $pbar, 
		                               true, 
		                               $printer,
		                               false,
		                               $debugprinting )) { // Send directly to printer
			print_error ( 'Fatal error trying to print' );
		}
	}
	$continue_url = new moodle_url ( '/mod/emarking/print/printorders.php', array (
			'category' => $course->category 
	) );
	echo $OUTPUT->continue_button ( $continue_url );
} else {
	// Confirm processing and select printer
	echo $form->display ();
}

echo $OUTPUT->footer ();
