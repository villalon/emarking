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
 * @copyright 2012 Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ('../../config.php');

require_once ('../../mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php');
require_once ('../../mod/assign/feedback/editpdf/fpdi/fpdi.php');

require_once ('phpqrcode/phpqrcode.php');
require_once ('lib.php');
require_once ('locallib.php');

global $USER, $CFG;

if (! isloggedin ()) {
	// echo json_encode(array('error'=>'User is not logged in'));
	print_error ( 'User is not logged in' );
	die ();
}

require_login ();

$sesskey = required_param ( 'sesskey', PARAM_ALPHANUM );
$examid = optional_param ( 'examid', 0, PARAM_INT );
$token = optional_param ( 'token', 0, PARAM_INT );
$multiplepdfs = optional_param ( 'multi', false, PARAM_BOOL );

// Validate session key
if ($sesskey != $USER->sesskey) {
	// echo json_encode(array('error'=>'Invalid session key'));
	print_error ( 'Invalid session key' );
	die ();
}

if ($token > 9999) {
	$examid = $_SESSION [$USER->sesskey . 'examid'];
}

if (! $exam = $DB->get_record ( 'emarking_exams', array (
		'id' => $examid 
) )) {
	// echo json_encode(array('error'=>'Invalid exam id'));
	print_error ( 'Invalid exam id' );
	die ();
}

if (! $course = $DB->get_record ( 'course', array (
		'id' => $exam->course 
) )) {
	// echo json_encode(array('error'=>'Invalid exam course id'));
	print_error ( 'Invalid exam course id' );
	die ();
}

$contextcat = context_coursecat::instance ( $course->category );
$contextcourse = context_course::instance ( $course->id );

$url = '/mod/emarking/download.php?examid=' . $exam->id . '&token=' . $token . '&sesskey=' . $sesskey;

// Validate capability in the category context
if (! has_capability ( 'mod/emarking:downloadexam', $contextcat ) && ! ($CFG->emarking_teachercandownload && has_capability ( 'mod/emarking:downloadexam', $contextcourse ))) {
	$item = array (
			'context' => context_module::instance ( $cm->id ),
			'objectid' => $cm->id 
	);
	// Add to Moodle log so some auditing can be done
	\mod_emarking\event\invalidaccess_granted::create ( $item )->trigger ();
	echo json_encode ( array (
			'error' => get_string ( 'invalidaccess', 'mod_emarking' ) 
	) );
	
	die ();
}

// A token was sent to validate download
if ($token > 9999) {
	// $_SESSION[$USER->sesskey . 'smstoken']
	if ($_SESSION [$USER->sesskey . 'smstoken'] === $token) {
		$now = new DateTime ();
		$tokendate = new DateTime ();
		$tokendate->setTimestamp ( $_SESSION [$USER->sesskey . 'smsdate'] );
		$diff = $now->diff ( $tokendate );
		if ($diff->i > 5) {
			echo json_encode ( array (
					'error' => 'The time to download the exam expired, please try again.' 
			) );
			die ();
		}
		$item = array (
				'context' => context_module::instance ( $cm->id ),
				'objectid' => $cm->id,
		);
		// Add to Moodle log so some auditing can be done
		\mod_emarking\event\successfully_downloaded::create ( $item )->trigger ();
		
		if ($exam->headerqr == 1) {
			if ($exam->printrandom == 1) {
				$rs = emarking_get_groups_for_printing ( $course->id );
				$zip = new ZipArchive ();
				$files = array ();
				$dirs = array ();
				$archive_name = $CFG->dataroot . "/temp/emarking/" . $contextcourse->id . "/" . "COURSE_" . $course->id . "_" . $exam->name . "_groups.zip"; // name of zip file
				
				if ($zip->open ( $archive_name, ZipArchive::CREATE ) === TRUE) {
					foreach ( $rs as $r ) {
						$rsg = emarking_download_exam ( $examid, $multiplepdfs, $r->id );
						$archive_folder = $rsg; // the folder which you archivate
						$dirs [] = $rsg;
						
						$dir = preg_replace ( '/[\/]{2,}/', '/', $archive_folder . "/" );
						
						$dh = opendir ( $dir );
						while ( $file = readdir ( $dh ) ) {
							if ($file != '.' && $file != '..') {
								$zip->addFile ( $dir . $file, "group_" . $r->id . "/" . $file );
								$files [] = $dir . $file;
							}
						}
						closedir ( $dh );
						
						echo 'Archiving is sucessful!';
					}
					$zip->close ();
					header ( "Content-type: application/zip" );
					header ( "Content-Disposition: attachment; filename=COURSE_" . $course->id . "_" . $exam->name . "_groups.zip" );
					header ( "Pragma: no-cache" );
					header ( "Expires: 0" );
					readfile ( $archive_name );
					foreach ( $files as $f ) {
						unlink ( $f );
					}
					foreach ( $dirs as $d ) {
						rmdir ( $d );
					}
					unlink ( $archive_name );
					exit ();
				} else {
					echo 'Error, can\'t create a zip file!';
				}
			} else {
				emarking_download_exam ( $examid, $multiplepdfs );
			}
		} else {
			$file = $DB->get_record ( 'files', array (
					'id' => $exam->file 
			) );
			
			$downloadURL = $CFG->wwwroot . '/pluginfile.php/' . $file->contextid . '/mod_emarking/' . $file->filearea . '/' . $file->itemid . '/' . $file->filename . '?sesskey=' . $USER->sesskey . '&token=' . $token;
			
			$downloadexam = $DB->get_record ( 'emarking_exams', array (
					'id' => $examid 
			) );
			$downloadexam->printdate = time ();
			$DB->update_record ( 'emarking_exams', $downloadexam );
			redirect ( $downloadURL, '', 0 );
		}
		die ();
	} else {
		$item = array (
				'context' => context_module::instance ( $cm->id ),
				'objectid' => $cm->id,
		);
		// Add to Moodle log so some auditing can be done
		\mod_emarking\event\invalidtoken_granted::create ( $item )->trigger ();
		
		print_error ( 'Token not recognized, please go back and try again.' );
		die ();
	}
} elseif ($token > 0) {
	echo json_encode ( array (
			'error' => 'Invalid token' 
	) );
	die ();
}

// Create new token, save data in session variables and send through email or mobile phone
$newtoken = rand ( 10000, 99999 ); // Generate random 5 digits token
$date = new DateTime ();

$_SESSION [$USER->sesskey . "smstoken"] = $newtoken; // Save token in session
$_SESSION [$USER->sesskey . "smsdate"] = $date->getTimestamp (); // Save timestamp to calculate token age
$_SESSION [$USER->sesskey . "examid"] = $examid; // Save exam id for extra security

if ($CFG->emarking_usesms) {
	
	// Validate mobile phone number
	if (! preg_match ( '/^\+569\d{8}$/', $USER->phone2 )) {
		echo json_encode ( array (
				'error' => 'Invalid phone number' 
		) );
		die ();
	}
	
	if (emarking_send_sms ( "Su codigo es: $newtoken", $USER->phone2 )) {
		echo json_encode ( array (
				'error' => '',
				'message' => 'SMS code sent' 
		) );
	} else {
		echo json_encode ( array (
				'error' => 'Could not connect to SMS server',
				'message' => '' 
		) );
	}
} else {
	if (emarking_send_email_code ( $newtoken, $USER, $course->fullname, $exam->name )) {
		echo json_encode ( array (
				'error' => '',
				'message' => 'Email security code sent' 
		) );
	} else {
		echo json_encode ( array (
				'error' => 'Could not connect to email server',
				'message' => '' 
		) );
	}
}