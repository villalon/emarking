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
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

global $CFG;
require ($CFG->dirroot . '/lib/coursecatlib.php');
require_once $CFG->dirroot . '/mod/emarking/lib.php';
function get_string_status($status) {
	switch ($status) {
		case EMARKING_REGRADE_MISASSIGNED_SCORE :
			return get_string ( 'missasignedscore', 'mod_emarking' );
		case EMARKING_REGRADE_UNCLEAR_FEEDBACK :
			return get_string ( 'unclearfeedback', 'mod_emarking' );
		case EMARKING_REGRADE_STATEMENT_PROBLEM :
			return get_string ( 'statementproblem', 'mod_emarking' );
		case EMARKING_REGRADE_OTHER :
			return get_string ( 'other', 'mod_emarking' );
		default :
			return 'INVALID STATUS';
	}
}

/**
 * Exports all grades and scores in an exam in Excel format
 *
 * @param unknown $emarking        	
 */
function emarking_download_excel($emarking) {
	global $DB;
	
	$csvsql = "
		SELECT cc.fullname AS course,
			e.name AS exam,
			u.id,
			u.idnumber,
			u.lastname,
			u.firstname,
			cr.description,
			IFNULL(l.score, 0) AS score,
			IFNULL(c.bonus, 0) AS bonus,
			IFNULL(l.score,0) + IFNULL(c.bonus,0) AS totalscore,
			s.grade
		FROM {emarking} AS e 
		INNER JOIN {emarking_submission} AS s ON (e.id = :emarkingid AND e.id = s.emarking)
		INNER JOIN {course} AS cc ON (cc.id = e.course)
		INNER JOIN {user} AS u ON (s.student = u.id)
		INNER JOIN {emarking_page} AS p ON (p.submission = s.id)
		INNER JOIN {emarking_comment} AS c ON (c.page = p.id)
		INNER JOIN {gradingform_rubric_levels} AS l ON (c.levelid = l.id)
		INNER JOIN {gradingform_rubric_criteria} AS cr ON (cr.id = l.criterionid)
		ORDER BY cc.fullname ASC, e.name ASC, u.lastname ASC, u.firstname ASC, cr.sortorder";
	
	// Get data and generate a list of questions
	$rows = $DB->get_recordset_sql ( $csvsql, array (
			'emarkingid' => $emarking->id 
	) );
	
	$questions = array ();
	foreach ( $rows as $row ) {
		if (array_search ( $row->description, $questions ) === FALSE)
			$questions [] = $row->description;
	}
	
	$current = 0;
	$laststudent = 0;
	$headers = array (
			'00course' => get_string ( 'course' ),
			'01exam' => get_string ( 'exam', 'mod_emarking' ),
			'02idnumber' => get_string ( 'idnumber' ),
			'03lastname' => get_string ( 'lastname' ),
			'04firstname' => get_string ( 'firstname' ) 
	);
	$tabledata = array ();
	$data = null;
	
	$rows = $DB->get_recordset_sql ( $csvsql, array (
			'emarkingid' => $emarking->id 
	) );
	
	$studentname = '';
	$lastrow = null;
	foreach ( $rows as $row ) {
		$index = 10 + array_search ( $row->description, $questions );
		$keyquestion = $index . "" . $row->description;
		if (! isset ( $headers [$keyquestion] )) {
			$headers [$keyquestion] = $row->description;
		}
		if ($laststudent != $row->id) {
			if ($laststudent > 0) {
				$tabledata [$studentname] = $data;
				$current ++;
			}
			$data = array (
					'00course' => $row->course,
					'01exam' => $row->exam,
					'02idnumber' => $row->idnumber,
					'03lastname' => $row->lastname,
					'04firstname' => $row->firstname,
					$keyquestion => $row->totalscore,
					'99grade' => $row->grade 
			);
			$laststudent = intval ( $row->id );
			$studentname = $row->lastname . ',' . $row->firstname;
		} else {
			$data [$keyquestion] = $row->totalscore;
		}
		$lastrow = $row;
	}
	$studentname = $lastrow->lastname . ',' . $lastrow->firstname;
	$tabledata [$studentname] = $data;
	$headers ['99grade'] = get_string ( 'grade' );
	ksort ( $tabledata );
	
	$current = 0;
	$newtabledata = array ();
	foreach ( $tabledata as $data ) {
		foreach ( $questions as $q ) {
			$index = 10 + array_search ( $q, $questions );
			if (! isset ( $data [$index . "" . $q] )) {
				$data [$index . "" . $q] = '0.000';
			}
		}
		ksort ( $data );
		$current ++;
		$newtabledata [] = $data;
	}
	
	$tabledata = $newtabledata;
	
	$downloadfilename = clean_filename ( "$emarking->name.xls" );
	// Creating a workbook
	$workbook = new MoodleExcelWorkbook ( "-" );
	// Sending HTTP headers
	$workbook->send ( $downloadfilename );
	// Adding the worksheet
	$myxls = $workbook->add_worksheet ( get_string ( 'emarking', 'mod_emarking' ) );
	
	// Writing the headers in the first row
	$row = 0;
	$col = 0;
	foreach ( array_values ( $headers ) as $d ) {
		$myxls->write_string ( $row, $col, $d );
		$col ++;
	}
	// Writing the data
	$row = 1;
	foreach ( $tabledata as $data ) {
		$col = 0;
		foreach ( array_values ( $data ) as $d ) {
			if ($row > 0 && $col >= 5) {
				$myxls->write_number ( $row, $col, $d );
			} else {
				$myxls->write_string ( $row, $col, $d );
			}
			$col ++;
		}
		$row ++;
	}
	$workbook->close ();
}
/**
 * Returns an array with all possible statuses for an eMarking submission
 *
 * @return multitype:string
 */
function emarking_get_statuses_as_array() {
	$statuses = array ();
	$statuses [] = EMARKING_STATUS_MISSING;
	$statuses [] = EMARKING_STATUS_ABSENT;
	$statuses [] = EMARKING_STATUS_SUBMITTED;
	$statuses [] = EMARKING_STATUS_GRADING;
	$statuses [] = EMARKING_STATUS_RESPONDED;
	$statuses [] = EMARKING_STATUS_REGRADING;
	$statuses [] = EMARKING_STATUS_ACCEPTED;
	return $statuses;
}

/**
 * Creates an array with the navigation tabs for emarking
 *
 * @param unknown $context
 *        	The course context to validate capabilit
 * @param unknown $cm
 *        	The course module (emarking activity)
 * @return multitype:tabobject
 */
function emarking_tabs($context, $cm, $emarking = null) {
	global $CFG;
	global $USER;
	
	if ($emarking == null) {
		throw new moodle_exception ( 'Invalid parameters' );
	}
	
	$usercangrade = has_capability ( 'mod/assign:grade', $context );
	
	$tabs = array ();
	// Home tab
	$examstab = new tabobject ( "home", $CFG->wwwroot . "/mod/emarking/exams.php?id={$cm->id}", get_string ( "printdigitize", 'mod_emarking' ) );
	// $examstab->subtree [] = new tabobject ( "myexams", $CFG->wwwroot . "/mod/emarking/exams.php?id={$cm->id}", get_string ( "myexams", 'mod_emarking' ) );
	$examstab->subtree [] = new tabobject ( "myexams", $CFG->wwwroot . "/mod/emarking/exams.php?id={$cm->id}", get_string ( "myexams", 'mod_emarking' ) );
	
	$examstab->subtree [] = new tabobject ( "newprintorder", $CFG->wwwroot . "/mod/emarking/newprintorder.php?cm={$cm->id}", get_string ( "newprintorder", 'mod_emarking' ) );
	$examstab->subtree [] = new tabobject ( "uploadanswers", $CFG->wwwroot . "/mod/emarking/upload.php?id={$cm->id}", get_string ( 'uploadanswers', 'mod_emarking' ) );
	
	// Grade tab
	$gradetab = new tabobject ( "grade", $CFG->wwwroot . "/mod/emarking/view.php?id={$cm->id}", get_string ( 'annotatesubmission', 'mod_emarking' ) );
	$gradetab->subtree [] = new tabobject ( "mark", $CFG->wwwroot . "/mod/emarking/view.php?id={$cm->id}", get_string ( "marking", 'mod_emarking' ) );
	if (! $usercangrade) {
		if ($CFG->emarking_enablejustice && $emarking->peervisibility) {
			$gradetab->subtree [] = new tabobject ( "ranking", $CFG->wwwroot . "/mod/emarking/ranking.php?id={$cm->id}", get_string ( "ranking", 'mod_emarking' ) );
			$gradetab->subtree [] = new tabobject ( "viewpeers", $CFG->wwwroot . "/mod/emarking/viewpeers.php?id={$cm->id}", get_string ( "justice.peercheck", 'mod_emarking' ) );
		}
		$gradetab->subtree [] = new tabobject ( "regrade", $CFG->wwwroot . "/mod/emarking/regrades.php?id={$cm->id}", get_string ( "regrades", 'mod_emarking' ) );
	} else {
		if (has_capability ( 'mod/emarking:regrade', $context ))
			$gradetab->subtree [] = new tabobject ( "regrades", $CFG->wwwroot . "/mod/emarking/regraderequests.php?cmid={$cm->id}", get_string ( "regrades", 'mod_emarking' ) );
		if (has_capability ( 'mod/emarking:assignmarkers', $context ))
			$gradetab->subtree [] = new tabobject ( "markers", $CFG->wwwroot . "/mod/emarking/markers.php?id={$cm->id}", get_string ( "markers", 'mod_emarking' ) );
	}
	
	if (isset ( $CFG->local_uai_debug ) && $CFG->local_uai_debug == 1) {
		$gradetab->subtree [] = new tabobject ( "comment", $CFG->wwwroot . "/mod/emarking/comment.php?id={$cm->id}&action=list", "comment" );
	}
	
	// Grade report tab
	$gradereporttab = new tabobject ( "gradereport", $CFG->wwwroot . "/mod/emarking/gradereport.php?id={$cm->id}", get_string ( "reports", "mod_emarking" ) );
	
	$gradereporttab->subtree [] = new tabobject ( "report", $CFG->wwwroot . "/mod/emarking/gradereport.php?id={$cm->id}", get_string ( "gradereport", "grades" ) );
	$gradereporttab->subtree [] = new tabobject ( "markingreport", $CFG->wwwroot . "/mod/emarking/markingreport.php?id={$cm->id}", get_string ( "markingreport", 'mod_emarking' ) );
	$gradereporttab->subtree [] = new tabobject ( "comparison", $CFG->wwwroot . "/mod/emarking/comparativereport.php?id={$cm->id}", get_string ( "comparativereport", "mod_emarking" ) );
	$gradereporttab->subtree [] = new tabobject ( "ranking", $CFG->wwwroot . "/mod/emarking/ranking.php?id={$cm->id}", get_string ( "ranking", 'mod_emarking' ) );
	
	// Tabs sequence
	if ($usercangrade) {
		$tabs [] = $gradetab;
		$tabs [] = $gradereporttab;
		if (has_capability ( 'mod/emarking:uploadexam', $context ))
			$tabs [] = $examstab;
			// Crowd tabs
		if ($CFG->emarking_crowdexperiment) {
			$tabs [] = new tabobject ( "crowd", $CFG->wwwroot . "/mod/emarking/crowd/marking.php?cmid={$cm->id}", "Delphi" );
		}
		if ($emarking->experimentalgroups) {
			$tabs [] = new tabobject ( "experimentalgroups", $CFG->wwwroot . "/mod/emarking/experimentalgroups.php?id={$cm->id}", "Experimental Groups" );
		}
	} else {
		$tabs = $gradetab->subtree;
	}
	
	return $tabs;
}

/**
 * Validates if current user has the editingteacher role in a certain course
 *
 * @param unknown $courseid
 *        	the course to validate
 * @return boolean if the current user is enroled as teacher
 */
function emarking_user_is_teacher($courseid) {
	global $DB, $USER;
	
	$coursecontext = context_course::instance ( $courseid );
	$roles = $DB->get_records ( 'role', array (
			'archetype' => 'editingteacher' 
	) );
	$useristeacher = false;
	foreach ( $roles as $role ) {
		$useristeacher = $useristeacher || user_has_role_assignment ( $USER->id, $role->id, $coursecontext->id );
	}
	return $useristeacher;
}

/**
 * Creates an array with the navigation tabs for emarking
 *
 * @param unknown $context
 *        	The course context to validate capabilit
 * @param unknown $cm
 *        	The course module (emarking activity)
 * @return multitype:tabobject
 */
function emarking_printoders_tabs($category) {
	global $CFG;
	global $USER;
	
	$tabs = array ();
	
	// Home tab
	$tabs [] = new tabobject ( "printorders", $CFG->wwwroot . "/mod/emarking/printorders.php?category={$category->id}&status=1", get_string ( "printorders", 'mod_emarking' ) );
	$tabs [] = new tabobject ( "printordershistory", $CFG->wwwroot . "/mod/emarking/printorders.php?category={$category->id}&status=2", get_string ( "records", 'mod_emarking' ) );
	$tabs [] = new tabobject ( "statistics", $CFG->wwwroot . "/mod/emarking/statistics.php?category={$category->id}", get_string ( "statistics", 'mod_emarking' ) );
	
	return $tabs;
}
function emarking_get_gradingarea($emarking) {
	global $DB;
	
	$gradingarea = $DB->get_record_sql ( "
			SELECT ga.id, count(rc.id) AS criteria
			FROM {grading_areas} AS ga
			INNER JOIN {context} AS c ON (ga.contextid = c.id AND c.contextlevel = 70)
			INNER JOIN {course_modules} AS cm ON (c.instanceid = cm.id)
			INNER JOIN {modules} AS mm ON (cm.module = mm.id AND mm.name='emarking')
			INNER JOIN {emarking} AS nm ON (cm.instance = nm.id)
			INNER JOIN {grading_definitions} AS gd ON (gd.areaid = ga.id)
			INNER JOIN {gradingform_rubric_criteria} AS rc ON (rc.definitionid = gd.id)
			WHERE ga.activemethod = 'rubric' AND nm.id = ?
			GROUP BY ga.id", array (
			$emarking->id 
	) );
	
	return $gradingarea;
}

/**
 * Verifies if there's a logo for the personalized header, and if there is it copies it to the
 * module
 */
function emarking_verify_logo() {
	$fs = get_file_storage ();
	$syscontext = context_system::instance ();
	// Copy any new stamps to this instance.
	if ($files = $fs->get_area_files ( $syscontext->id, 'core', 'logo', 1, "filename", false )) {
		
		foreach ( $files as $file ) {
			$filename = $file->get_filename ();
			if ($filename !== '.') {
				
				$existingfile = $fs->get_file ( $syscontext->id, 'mod_emarking', 'logo', 1, '/', $file->get_filename () );
				if (! $existingfile) {
					$newrecord = new stdClass ();
					$newrecord->contextid = $syscontext->id;
					$newrecord->itemid = 1;
					$newrecord->filearea = 'logo';
					$newrecord->component = 'mod_emarking';
					$fs->create_file_from_storedfile ( $newrecord, $file );
				}
			}
		}
	}
}

/**
 * Verifies if there's a logo for the personalized header, and if there is it copies it to the
 * module
 */
function emarking_get_logo_file() {
	$fs = get_file_storage ();
	$syscontext = context_system::instance ();
	
	if ($files = $fs->get_area_files ( $syscontext->id, 'mod_emarking', 'logo', 1, "filename", false )) {
		
		foreach ( $files as $file ) {
			$filename = $file->get_filename ();
			if ($filename !== '.') {
				
				$existingfile = $fs->get_file ( $syscontext->id, 'mod_emarking', 'logo', 1, '/', $file->get_filename () );
				if ($existingfile) {
					return $existingfile;
				}
			}
		}
	}
	
	return false;
}

/**
 * Extracts all pages in a big PDF file as separate PDF files, deleting the original PDF if successfull.
 *
 * @param unknown $newfile
 *        	PDF file to extract
 * @param unknown $tempdir
 *        	Temporary folder
 * @param string $doubleside
 *        	Extract every two pages (for both sides scanning)
 * @return number unknown number of pages extracted
 */
function emarking_pdf_count_pages($newfile, $tempdir, $doubleside = true) {
	global $CFG;
	
	require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php");
	require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi.php");
	
	$doc = new FPDI ();
	$files = $doc->setSourceFile ( $newfile );
	$doc->Close ();
	
	return $files;
}

/**
 * Counts files in dir using an optional suffix
 *
 * @param unknown $dir
 *        	Folder to count files from
 * @param string $suffix
 *        	File extension to filter
 */
function emarking_count_files_in_dir($dir, $suffix = ".pdf") {
	return count ( emarking_get_files_list ( $dir, $suffix ) );
}

/**
 * Gets a list of files filtered by extension from a folder
 *
 * @param unknown $dir
 *        	Folder
 * @param string $suffix
 *        	Extension to filter
 * @return multitype:unknown Array of filenames
 */
function emarking_get_files_list($dir, $suffix = ".pdf") {
	$files = scandir ( $dir );
	$cleanfiles = array ();
	
	foreach ( $files as $filename ) {
		if (! is_dir ( $filename ) && substr ( $filename, - 4, 4 ) === $suffix)
			$cleanfiles [] = $filename;
	}
	
	return $cleanfiles;
}

/**
 * Gets course names for all courses that share the same exam file
 *
 * @param unknown $exam        	
 * @return multitype:boolean unknown
 */
function emarking_exam_get_parallels($exam) {
	global $DB;
	
	// Checking if exam is for multicourse
	$courses = array ();
	$canbedeleted = true;
	
	// Find all exams with the same PDF file
	$multi = $DB->get_records ( 'emarking_exams', array (
			'file' => $exam->file 
	), 'course ASC' );
	foreach ( $multi as $mult ) {
		if ($mult->status >= EMARKING_EXAM_SENT_TO_PRINT) {
			$canbedeleted = false;
		}
		if ($mult->id != $exam->id) {
			$shortname = $DB->get_record ( 'course', array (
					'id' => $mult->course 
			) );
			$courses [] = $shortname->shortname;
		}
	}
	$multicourse = implode ( ", ", $courses );
	
	return array (
			$canbedeleted,
			$multicourse 
	);
}
/**
 * Calculates the total number of pages an exam will have for printing statistics
 * according to extra sheets, extra exams and if it has a personalized header and
 * if it uses the backside
 *
 * @param unknown $exam
 *        	the exam object
 * @param unknown $numpages
 *        	total pages in document
 * @return number total pages to print
 */
function emarking_exam_total_pages_to_print($exam) {
	if (! $exam)
		return 0;
	
	$total = $exam->totalpages + $exam->extrasheets;
	if ($exam->totalstudents > 0) {
		$total = $total * ($exam->totalstudents + $exam->extraexams);
	}
	if ($exam->usebackside) {
		$total = $total / 2;
	}
	return $total;
}

/**
 * Devuelve el path por defecto de archivos temporales de emarking.
 * Normalmente debiera ser moodledata\temp\emarking
 *
 * @param unknown $postfix
 *        	Postfijo (típicamente el id de assignment)
 * @return string El path al directorio temporal
 */
function emarking_get_temp_dir_path($postfix) {
	global $CFG;
	return $CFG->dataroot . "/temp/emarking/" . $postfix;
}
function emarking_get_or_create_submission($emarking, $student) {
	global $DB, $USER;
	
	if ($submission = $DB->get_record ( 'emarking_submission', array (
			'emarking' => $emarking->id,
			'student' => $student->id 
	) )) {
		return $submission;
	}
	
	$submission = new stdClass ();
	$submission->emarking = $emarking->id;
	$submission->student = $student->id;
	$submission->status = EMARKING_STATUS_SUBMITTED;
	$submission->timecreated = time ();
	$submission->timemodified = time ();
	$submission->teacher = $USER->id;
	$submission->grade = 0;
	$submission->sort = rand ( 1, 9999999 );
	
	$submission->id = $DB->insert_record ( 'emarking_submission', $submission );
	
	$draft = $submission;
	$draft->emarkingid = $emarking->id;
	$draft->submissionid = $submission->id;
	$draft->groupid = 0;
	
	$draft->id = $DB->insert_record ( 'emarking_draft', $draft );
	
	return $draft;
}
function emarking_get_string_for_status($status) {
	switch ($status) {
		case EMARKING_STATUS_ACCEPTED :
			return get_string ( 'statusaccepted', 'mod_emarking' );
		case EMARKING_STATUS_ABSENT :
			return get_string ( 'statusabsent', 'mod_emarking' );
		case EMARKING_STATUS_GRADING :
			return get_string ( 'statusgrading', 'mod_emarking' );
		case EMARKING_STATUS_MISSING :
			return get_string ( 'statusmissing', 'mod_emarking' );
		case EMARKING_STATUS_REGRADING :
			return get_string ( 'statusregrading', 'mod_emarking' );
		case EMARKING_STATUS_RESPONDED :
			return get_string ( 'statusresponded', 'mod_emarking' );
		case EMARKING_STATUS_SUBMITTED :
			return get_string ( 'statussubmitted', 'mod_emarking' );
		default :
			return get_string ( 'statuserror', 'mod_emarking' );
	}
}

/**
 * Uploads a PDF file as a student's submission for a specific assignment
 *
 * @param object $emarking
 *        	the assignment object from dbrecord
 * @param unknown_type $context
 *        	the coursemodule
 * @param unknown_type $course
 *        	the course object
 * @param unknown_type $path        	
 * @param unknown_type $filename        	
 * @param unknown_type $student        	
 * @param unknown_type $numpages        	
 * @param unknown_type $merge        	
 * @return boolean
 */
// exportado y cambiado
function emarking_submit($emarking, $context, $path, $filename, $student, $pagenumber = 0) {
	global $DB, $USER, $CFG;
	
	// All libraries for grading
	require_once ("$CFG->dirroot/grade/grading/lib.php");
	require_once $CFG->dirroot . '/grade/lib.php';
	require_once ("$CFG->dirroot/grade/grading/form/rubric/lib.php");
	
	// Calculate anonymous file name from original file name
	$filenameparts = explode ( ".", $filename );
	$anonymousfilename = $filenameparts [0] . "_a." . $filenameparts [1];
	
	// Verify that both image files (anonymous and original) exist
	if (! file_exists ( $path . "/" . $filename ) || ! file_exists ( $path . "/" . $anonymousfilename )) {
		return false;
	}
	
	// Filesystem
	$fs = get_file_storage ();
	
	// Copy file from temp folder to Moodle's filesystem
	$file_record = array (
			'contextid' => $context->id,
			'component' => 'mod_emarking',
			'filearea' => 'pages',
			'itemid' => $emarking->id,
			'filepath' => '/',
			'filename' => $filename,
			'timecreated' => time (),
			'timemodified' => time (),
			'userid' => $student->id,
			'author' => $student->firstname . ' ' . $student->lastname,
			'license' => 'allrightsreserved' 
	);
	
	// If the file already exists we delete it
	if ($fs->file_exists ( $context->id, 'mod_emarking', 'pages', $emarking->id, '/', $filename )) {
		$previousfile = $fs->get_file ( $context->id, 'mod_emarking', 'pages', $emarking->id, '/', $filename );
		$previousfile->delete ();
	}
	
	// Info for the new file
	$fileinfo = $fs->create_file_from_pathname ( $file_record, $path . '/' . $filename );
	
	// Now copying the anonymous version of the file
	$file_record ['filename'] = $anonymousfilename;
	
	// Check if anoymous file exists and delete it
	if ($fs->file_exists ( $context->id, 'mod_emarking', 'pages', $emarking->id, '/', $anonymousfilename )) {
		$previousfile = $fs->get_file ( $context->id, 'mod_emarking', 'pages', $emarking->id, '/', $anonymousfilename );
		$previousfile->delete ();
	}
	
	$fileinfoanonymous = $fs->create_file_from_pathname ( $file_record, $path . '/' . $anonymousfilename );
	
	$submission = emarking_get_or_create_submission ( $emarking, $student );
	
	// Get the page from previous uploads. If exists update it, if not insert a new page
	$page = $DB->get_record ( 'emarking_page', array (
			'submission' => $submission->id,
			'student' => $student->id,
			'page' => $pagenumber 
	) );
	
	if ($page != null) {
		$page->file = $fileinfo->get_id ();
		$page->fileanonymous = $fileinfoanonymous->get_id ();
		$page->timemodified = time ();
		$page->teacher = $USER->id;
		$DB->update_record ( 'emarking_page', $page );
	} else {
		$page = new stdClass ();
		$page->student = $student->id;
		$page->page = $pagenumber;
		$page->file = $fileinfo->get_id ();
		$page->fileanonymous = $fileinfoanonymous->get_id ();
		$page->submission = $submission->id;
		$page->timecreated = time ();
		$page->timemodified = time ();
		$page->teacher = $USER->id;
		
		$page->id = $DB->insert_record ( 'emarking_page', $page );
	}
	
	// Update submission info
	$submission->teacher = $page->teacher;
	$submission->timemodified = $page->timemodified;
	$DB->update_record ( 'emarking_draft', $submission );
	
	return true;
}

/**
 * Uploads a PDF file as a student's submission for a specific assignment
 *
 * @param object $assignment
 *        	the assignment object from dbrecord
 * @param unknown_type $cm
 *        	the coursemodule
 * @param unknown_type $course
 *        	the course object
 * @param unknown_type $path        	
 * @param unknown_type $filename        	
 * @param unknown_type $student        	
 * @param unknown_type $numpages        	
 * @param unknown_type $merge        	
 * @return boolean
 */
function emarking_sort_submission_pages($submission, $neworder) {
	global $DB;
	
	// Verify that the new order is an array
	if (! is_array ( $neworder )) {
		return false;
	}
	
	// Verify that it contains the numbers from 0 to length -1
	$sortedbypage = array_merge ( $neworder );
	asort ( $sortedbypage );
	$newindices = array ();
	$i = 0;
	foreach ( $sortedbypage as $k => $v ) {
		if (intval ( $v ) != $i) {
			return false;
		}
		$i ++;
		$newindices [intval ( $v ) + 1] = $k + 1;
	}
	
	// Get all the pages involved
	if (! $pages = $DB->get_records ( 'emarking_page', array (
			'submission' => $submission->id 
	), 'page ASC' )) {
		return false;
	}
	
	// Get the total pages in the sumission
	$numpages = count ( $pages );
	
	// Verify the new order has the same number of pages as the submission
	if ($numpages != count ( $neworder ))
		return false;
		
		// Update each page according to the new sort order
	$i = 0;
	foreach ( $pages as $page ) {
		$newindex = $newindices [$page->page];
		$page->page = $newindex;
		$DB->update_record ( 'emarking_page', $page );
		$i ++;
	}
	
	return true;
}

/**
 * Recursively remove a directory.
 * Enter description here ...
 *
 * @param unknown_type $dir        	
 */
function emarking_rrmdir($dir) {
	foreach ( glob ( $dir . '/*' ) as $file ) {
		if (is_dir ( $file ))
			emarking_rrmdir ( $file );
		else
			unlink ( $file );
	}
	rmdir ( $dir );
}

/**
 * Esta funcion copia el archivo solicitado mediante el Hash (lo busca en la base de datos) en la carpeta temporal especificada.
 *
 * @param String $tempdir
 *        	Carpeta a la cual queremos copiar el archivo
 * @param String $hash
 *        	hash del archivo en base de datos
 * @param String $prefix
 *        	???
 * @return mixed
 */
// exportado y cambiado
function emarking_get_path_from_hash($tempdir, $hash, $prefix = '', $create = true) {
	global $CFG;
	
	// Obtiene filesystem
	$fs = get_file_storage ();
	
	// Obtiene archivo gracias al hash
	if (! $file = $fs->get_file_by_hash ( $hash )) {
		return false;
	}
	
	// Se copia archivo desde Moodle a temporal
	$newfile = emarking_clean_filename ( $tempdir . '/' . $prefix . $file->get_filename () );
	
	$file->copy_content_to ( $newfile );
	
	return $newfile;
}

/**
 * Replace "acentos", spaces from file names.
 * Evita problemas en Windows y Linux.
 *
 * @param unknown $filename
 *        	El nombre original del archivo
 * @return unknown El nombre sin acentos, espacios.
 */
function emarking_clean_filename($filename, $slash = false) {
	$replace = array (
			' ',
			'á',
			'é',
			'í',
			'ó',
			'ú',
			'ñ',
			'Ñ',
			'Á',
			'É',
			'Í',
			'Ó',
			'Ú',
			'(',
			')' 
	);
	$replacefor = array (
			'-',
			'a',
			'e',
			'i',
			'o',
			'u',
			'n',
			'N',
			'A',
			'E',
			'I',
			'O',
			'U',
			'-',
			'-' 
	);
	if ($slash) {
		$replace [] = '/';
		$replacefor [] = '-';
	}
	$newfile = str_replace ( $replace, $replacefor, $filename );
	return $newfile;
}

/**
 * Erraces all the content of a directory, then ir creates te if they don't exist.
 *
 * @param unknown $dir
 *        	Directorio
 * @param unknown $delete
 *        	Borrar archivos previamente
 */
function emarking_initialize_directory($dir, $delete) {
	if ($delete) {
		// First erase all files
		if (is_dir ( $dir )) {
			emarking_rrmdir ( $dir );
		}
	}
	
	// Si no existe carpeta para temporales se crea
	if (! is_dir ( $dir )) {
		if (! mkdir ( $dir, 0777, true )) {
			print_error ( get_string ( 'initializedirfail', 'mod_emarking', $dir ) );
		}
	}
}

/**
 * Sends an sms message using UAI's service with infobip.com.
 * Returns true if successful, false otherwise.
 *
 * @param string $message
 *        	the message to be sent
 * @param string $number
 *        	the mobile number
 */
function emarking_send_sms($message, $number) {
	global $CFG;
	
	$postUrl = $CFG->emarking_smsurl;
	
	$xmlString = "<SMS>
	<authentification>
	<username>$CFG->emarking_smsuser</username>
	<password>$CFG->emarking_smspassword</password>
	</authentification>
	<message>
	<sender>Webcursos</sender>
	<text>$message</text>
	<recipients>
	<gsm>$number</gsm>
	</recipients>
	</message>

	</SMS>";
	
	// previamente formateado en XML
	$fields = "XML=" . urlencode ( $xmlString );
	
	// Se require cURL
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $postUrl );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	
	// Respuesta del POST
	$response = curl_exec ( $ch );
	curl_close ( $ch );
	
	if (! $response) {
		return false;
	}
	
	try {
		$xml = new SimpleXmlElement ( $response );
	} catch ( exception $e ) {
		return false;
	}
	
	if ($xml && $xml->status == 1) {
		return true;
	}
	
	return false;
}

/**
 * Creates a personalized exam file.
 *
 * @param unknown $examid        	
 * @return NULL
 */
function emarking_download_exam($examid, $multiplepdfs = false, $groupid = null, $pbar = null, $sendprintorder = false, $printername = null) {
	global $DB, $CFG, $USER, $OUTPUT;
	
	// Se obtiene el examen
	if (! $downloadexam = $DB->get_record ( 'emarking_exams', array (
			'id' => $examid 
	) )) {
		return null;
	}
	
	// Contexto del curso para verificar permisos
	$context = context_course::instance ( $downloadexam->course );
	
	if (! has_capability ( 'mod/emarking:downloadexam', $context )) {
		return null;
	}
	
	// Verify that remote printing is enable, otherwise disable a printing order
	if ($sendprintorder && (! $CFG->emarking_enableprinting || $printername == null)) {
		return null;
	}
	
	$course = $DB->get_record ( 'course', array (
			'id' => $downloadexam->course 
	) );
	$coursecat = $DB->get_record ( 'course_categories', array (
			'id' => $course->category 
	) );
	
	if ($downloadexam->printrandom == 1) {
		$enrolincludes = 'manual,self,meta';
	} else {
		$enrolincludes = 'manual,self';
	}
	
	if ($CFG->emarking_enrolincludes && strlen ( $CFG->emarking_enrolincludes ) > 1) {
		$enrolincludes = $CFG->emarking_enrolincludes;
	}
	if (isset ( $downloadexam->enrolments ) && strlen ( $downloadexam->enrolments ) > 1) {
		$enrolincludes = $downloadexam->enrolments;
	}
	$enrolincludes = explode ( ",", $enrolincludes );
	
	// Get all the files uploaded as forms for this exam
	$fs = get_file_storage ();
	$files = $fs->get_area_files ( $context->id, 'mod_emarking', 'exams', $examid );
	
	// We filter only the PDFs
	$pdffileshash = array ();
	foreach ( $files as $filepdf ) {
		if ($filepdf->get_mimetype () === 'application/pdf') {
			$pdffileshash [] = array (
					'hash' => $filepdf->get_pathnamehash (),
					'filename' => $filepdf->get_filename () 
			);
		}
	}
	
	// Verify that at least we have a PDF
	if (count ( $pdffileshash ) < 1) {
		return null;
	}
	
	if ($downloadexam->headerqr == 1) {
		if ($groupid != null) {
			$filedir = $CFG->dataroot . "/temp/emarking/$context->id" . "/group_" . $groupid;
		} else {
			$filedir = $CFG->dataroot . "/temp/emarking/$context->id";
		}
		$fileimg = $CFG->dataroot . "/temp/emarking/$context->id/qr";
		$userimgdir = $CFG->dataroot . "/temp/emarking/$context->id/u";
		
		emarking_initialize_directory ( $filedir, true );
		emarking_initialize_directory ( $fileimg, true );
		emarking_initialize_directory ( $userimgdir, true );
		
		if ($groupid != null) {
			// Se toman los resultados del query dentro de una variable.
			$students = emarking_get_students_of_groups ( $downloadexam->course, $groupid );
		} else {
			// Se toman los resultados del query dentro de una variable.
			$students = emarking_get_students_for_printing ( $downloadexam->course );
		}
		
		$nombre = array ();
		
		$current = 0;
		// Los resultados del query se recorren mediante un foreach loop.
		foreach ( $students as $student ) {
			if (array_search ( $student->enrol, $enrolincludes ) === false) {
				continue;
			}
			$nombre [] = substr ( "$student->lastname, $student->firstname", 0, 65 );
			$rut [] = $student->idnumber;
			$moodleid [] = $student->id;
			// Get the image file for student
			$imgfound = false;
			if ($CFG->emarking_pathuserpicture && is_dir ( $CFG->emarking_pathuserpicture )) {
				$idstring = "" . $student->idnumber;
				$revid = strrev ( $idstring );
				$idpath = $CFG->emarking_pathuserpicture;
				$idpath .= "/" . substr ( $revid, 0, 1 );
				$idpath .= "/" . substr ( $revid, 1, 1 );
				if (file_exists ( $idpath . "/user$idstring.png" )) {
					$userimg [] = $idpath . "/user$idstring.png";
					$imgfound = true;
				}
			}
			if (! $imgfound) {
				$usercontext = context_user::instance ( $student->id );
				$imgfile = $DB->get_record ( 'files', array (
						'contextid' => $usercontext->id,
						'component' => 'user',
						'filearea' => 'icon',
						'filename' => 'f1.png' 
				) );
				if ($imgfile)
					$userimg [] = emarking_get_path_from_hash ( $userimgdir, $imgfile->pathnamehash, "u" . $student->id, true );
				else
					$userimg [] = $CFG->dirroot . "/pix/u/f1.png";
			}
		}
		$numberstudents = count ( $nombre );
		
		for($i = $numberstudents; $i < $numberstudents + $downloadexam->extraexams; $i ++) {
			$nombre [$i] = '..............................................................................';
			$rut [$i] = '0';
			$moodleid [$i] = '0';
			$userimg [$i] = $CFG->dirroot . "/pix/u/f1.png";
		}
		
		$newfile = emarking_get_path_from_hash ( $filedir, $pdffileshash [$current] ['hash'] );
		$path = $filedir . "/" . str_replace ( ' ', '-', $pdffileshash [$current] ['filename'] );
		$hash = hash_file ( 'md5', $path );
		
		$logoisconfigured = false;
		if ($logofile = emarking_get_logo_file ()) {
			$logofilepath = emarking_get_path_from_hash ( $filedir, $logofile->get_pathnamehash () );
			$logoisconfigured = true;
		}
		
		$file1 = $filedir . "/" . emarking_clean_filename ( $course->shortname, true ) . "_" . emarking_clean_filename ( $downloadexam->name, true ) . ".pdf";
		
		$pdf = new FPDI ();
		$cp = $pdf->setSourceFile ( $path );
		if ($cp > 99) {
			print_error ( get_string ( 'page', 'mod_emarking' ) );
		}
		
		if ($multiplepdfs || $groupid != null) {
			$zip = new ZipArchive ();
			if ($groupid != null) {
				$file1 = $filedir . "/" . emarking_clean_filename ( $course->shortname, true ) . "_" . "GRUPO_" . $groupid . "_" . emarking_clean_filename ( $downloadexam->name, true ) . ".zip";
			} else {
				$file1 = $filedir . "/" . emarking_clean_filename ( $course->shortname, true ) . "_" . emarking_clean_filename ( $downloadexam->name, true ) . ".zip";
			}
			
			if ($zip->open ( $file1, ZipArchive::CREATE ) !== true) {
				return null;
			}
		}
		
		if ($sendprintorder) {
			if ($pbar != null) {
				$pbar->update ( 0, count ( $nombre ), '' );
			}
		}
		
		$jobs [] = array ();
		
		if ($downloadexam->printlist == 1) {
			
			$flag = 0;
			// lista de alumnos
			if ($flag == 0) {
				$pdf->SetAutoPageBreak ( false );
				$pdf->AddPage ();
				
				$left = 85;
				$top = 8;
				$pdf->SetFont ( 'Helvetica', 'B', 12 );
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( "LISTA DE ALUMNOS" ) );
				
				$left = 15;
				$top = 16;
				$pdf->SetFont ( 'Helvetica', '', 8 );
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( "Asignatura: " . $course->fullname ) );
				
				$left = 15;
				$top = 22;
				$pdf->SetFont ( 'Helvetica', '', 8 );
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( "N° Inscritos: " . count ( $nombre ) ) );
				
				// $year = date("Y");
				// $month= date("F");
				// $day= date("m");
				
				setlocale ( LC_ALL, "es_ES" );
				$left = 15;
				$top = 28;
				$pdf->SetFont ( 'Helvetica', '', 8 );
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( "Fecha: " . strftime ( "%A %d de %B del %Y" ) ) );
				
				$left = 15;
				$top = 36;
				$pdf->SetXY ( $left, $top );
				$pdf->Cell ( 10, 10, "N°", 1, 0, 'L' );
				$pdf->Cell ( 120, 10, "Nombres", 1, 0, 'L' );
				$pdf->Cell ( 50, 10, "Firmas", 1, 0, 'L' );
				
				$t = 0;
				$t2 = 46;
				for($a = 0; $a <= count ( $nombre ) - 1; $a ++) {
					
					if ($n == 24 || $n == 48 || $n == 72 || $n == 96 || $n == 120) {
						$pdf->AddPage ();
						$t = 0;
						$t2 = 8;
					}
					
					$top = $t2 + $t;
					$n = $a + 1;
					$pdf->SetFont ( 'Helvetica', '', 8 );
					$pdf->SetXY ( $left, $top );
					$pdf->Cell ( 10, 10, $n . ")", 1, 0, 'L' );
					$pdf->Cell ( 120, 10, core_text::strtoupper ( $nombre [$a] ), 1, 0, 'L' );
					$pdf->Cell ( 50, 10, "", 1, 0, 'L' );
					// $pdf->Write ( 1, core_text::strtoupper ( $nombre [$a] ) );
					$t = $t + 10;
				}
				$flag = 1;
				
				if ($multiplepdfs || $groupid != null) {
					if ($groupid != null) {
						$pdffile = $filedir . "/Lista_de_alumnos_" . "GRUPO_" . $groupid . ".pdf";
						$pdf->Output ( $pdffile, "F" ); // se genera el nuevo pdf
						$zip->addFile ( $pdffile, "GRUPO_" . $groupid . ".pdf" );
					} else {
						$pdffile = $filedir . "/Lista_de_alumnos_" . emarking_clean_filename ( $course->shortname, true ) . ".pdf";
						$pdf->Output ( $pdffile, "F" ); // se genera el nuevo pdf
						$zip->addFile ( $pdffile, "Lista_de_alumnos_" . emarking_clean_filename ( $course->shortname, true ) . ".pdf" );
					}
				}
				$printername = explode ( ',', $CFG->emarking_printername );
				if ($sendprintorder) {
					if ($printername [$_POST ["printername"]] != "Edificio-C-mesonSecretaria") {
						$command = "lp -d " . $printername [$_POST ["printername"]] . " -o StapleLocation=UpperLeft -o fit-to-page -o media=Letter " . $pdffile;
					} else {
						$command = "lp -d " . $printername [$_POST ["printername"]] . " -o StapleLocation=SinglePortrait -o PageSize=Letter -o Duplex=none " . $pdffile;
					}
					
					$printresult = exec ( $command );
					if ($CFG->debug) {
						echo "$command <br>";
						echo "$printresult <hr>";
					}
				}
			}
		}
		
		// Here we produce a PDF file for each student
		for($k = 0; $k <= count ( $nombre ) - 1; $k ++) {
			
			// If there are multiplepdfs we have to produce one per student
			if ($multiplepdfs || $sendprintorder || $groupid != null) {
				$pdf = new FPDI ();
			}
			
			if ($multiplepdfs || $sendprintorder || $groupid != null || count ( $pdffileshash ) > 1) {
				$current ++;
				if ($current > count ( $pdffileshash ) - 1)
					$current = 0;
				$newfile = emarking_get_path_from_hash ( $filedir, $pdffileshash [$current] ['hash'] );
				$path = $filedir . "/" . str_replace ( ' ', '-', $pdffileshash [$current] ['filename'] );
				$cp = $pdf->setSourceFile ( $path );
			}
			
			$pdf->SetAutoPageBreak ( false );
			for($i = 1; $i <= $cp + $downloadexam->extrasheets; $i = $i + 1) {
				$h = rand ( 1, 999999 );
				$img = $fileimg . "/qr" . $h . "_" . $rut [$k] . "_" . $i . "_" . $hash . ".png";
				$imgrotated = $fileimg . "/qr" . $h . "_" . $rut [$k] . "_" . $i . "_" . $hash . "r.png";
				// Se genera QR con id, curso y número de página
				$qrstring = "$moodleid[$k] - $downloadexam->course - $i";
				QRcode::png ( $qrstring, $img ); // se inserta QR
				QRcode::png ( $qrstring . " - R", $imgrotated ); // se inserta QR
				$gdimg = imagecreatefrompng ( $imgrotated );
				$rotated = imagerotate ( $gdimg, 180, 0 );
				imagepng ( $rotated, $imgrotated );
				$pdf->AddPage (); // Agrega una nueva página
				if ($i <= $cp) {
					$tplIdx = $pdf->importPage ( $i ); // Se importan las páginas del documento pdf.
					$pdf->useTemplate ( $tplIdx, 0, 0, 0, 0, $adjustPageSize = true ); // se inserta como template el archivo pdf subido
				}
				/*
				 * Ahora se escribe texto sobre las páginas ya importadas. Se fija la fuente, el tipo y el tamaño de la letra. Se señala el título. Se da el nombre, apellido y rut del alumno al cual pertenece la prueba. Se indica el curso correspondiente a la evaluación. Se introduce una imagen. Esta corresponde al QR que se genera con los datos
				 */
				
				if ($CFG->emarking_includelogo && $logoisconfigured) {
					$pdf->Image ( $logofilepath, 2, 8, 30 );
				}
				
				$left = 58;
				$top = 8;
				$pdf->SetFont ( 'Helvetica', '', 12 );
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( $downloadexam->name ) );
				$pdf->SetFont ( 'Helvetica', '', 9 );
				$top += 5;
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( get_string ( 'name' ) . ": " . $nombre [$k] ) );
				$top += 4;
				if ($rut [$k] && strlen ( $rut [$k] ) > 0) {
					$pdf->SetXY ( $left, $top );
					$pdf->Write ( 1, get_string ( 'idnumber', 'mod_emarking' ) . ": " . $rut [$k] );
					$top += 4;
				}
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( get_string ( 'course' ) . ": " . $course->fullname ) );
				$top += 4;
				if (file_exists ( $userimg [$k] )) {
					$pdf->Image ( $userimg [$k], 35, 8, 15, 15, "PNG", null, "T", true );
				}
				$totals = new stdClass ();
				$totals->identified = $i;
				$totals->total = $cp + $downloadexam->extrasheets;
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( get_string ( 'page' ) . ": " . get_string ( 'aofb', 'mod_emarking', $totals ) ) );
				$pdf->Image ( $img, 176, 3, 34 ); // y antes era -2
				$pdf->Image ( $imgrotated, 0, $pdf->getPageHeight () - 35, 34 );
				unlink ( $img );
				unlink ( $imgrotated );
			}
			
			if ($multiplepdfs || $sendprintorder || $groupid != null) {
				
				$pdffile = $filedir . "/" . emarking_clean_filename ( $qrstring ) . ".pdf";
				
				if (file_exists ( $pdffile )) {
					$pdffile = $filedir . "/" . emarking_clean_filename ( $qrstring ) . "_" . $k . ".pdf";
					$pdf->Output ( $pdffile, "F" ); // se genera el nuevo pdf
					$zip->addFile ( $pdffile, emarking_clean_filename ( $qrstring ) . "_" . $k . ".pdf" );
				} else {
					$pdffile = $filedir . "/" . emarking_clean_filename ( $qrstring ) . ".pdf";
					$pdf->Output ( $pdffile, "F" ); // se genera el nuevo pdf
					$zip->addFile ( $pdffile, emarking_clean_filename ( $qrstring ) . ".pdf" );
				}
				
				$jobs [$k] ["param_1_pbar"] = $k + 1;
				$jobs [$k] ["param_2_pbar"] = count ( $nombre );
				$jobs [$k] ["param_3_pbar"] = 'Imprimiendo pruebas de ' . core_text::strtoupper ( $nombre [$k] );
				$jobs [$k] ["name_job"] = $pdffile;
			}
		}
		
		$printername = explode ( ',', $CFG->emarking_printername );
		
		if ($sendprintorder) {
			foreach ( $jobs as &$valor ) {
				if (! empty ( $valor )) {
					if ($pbar != null) {
						$pbar->update ( $valor ["param_1_pbar"], $valor ["param_2_pbar"], $valor ["param_3_pbar"] );
					}
					
					if ($printername [$_POST ["printername"]] != "Edificio-C-mesonSecretaria") {
						$command = "lp -d " . $printername [$_POST ["printername"]] . " -o StapleLocation=UpperLeft -o fit-to-page -o media=Letter " . $valor ["name_job"];
					} else {
						$command = "lp -d " . $printername [$_POST ["printername"]] . " -o StapleLocation=SinglePortrait -o PageSize=Letter -o Duplex=none " . $valor ["name_job"];
					}
					
					$printresult = exec ( $command );
					if ($CFG->debug) {
						echo "$command <br>";
						echo "$printresult <hr>";
					}
				}
			}
		}
		
		if ($multiplepdfs || $groupid != null) {
			// Generate Bat File
			$printerarray = array ();
			foreach ( explode ( ',', $CFG->emarking_printername ) as $printer ) {
				$printerarray [] = $printer;
			}
			
			$contenido = "@echo off\r\n";
			$contenido .= "TITLE Sistema de impresion\r\n";
			$contenido .= "color ff\r\n";
			$contenido .= "cls\r\n";
			$contenido .= ":MENUPPL\r\n";
			$contenido .= "cls\r\n";
			$contenido .= "echo #######################################################################\r\n";
			$contenido .= "echo #                     Sistema de impresion                            #\r\n";
			$contenido .= "echo #                                                                     #\r\n";
			$contenido .= "echo # @copyright 2014 Eduardo Miranda                                     #\r\n";
			$contenido .= "echo # Fecha Modificacion 23-04-2014                                       #\r\n";
			$contenido .= "echo #                                                                     #\r\n";
			$contenido .= "echo #   Para realizar la impresion debe seleccionar una de las impresoras #\r\n";
			$contenido .= "echo #   configuradas.                                                     #\r\n";
			$contenido .= "echo #                                                                     #\r\n";
			$contenido .= "echo #                                                                     #\r\n";
			$contenido .= "echo #######################################################################\r\n";
			$contenido .= "echo #   Seleccione una impresora:                                         #\r\n";
			
			$i = 0;
			while ( $i < count ( $printerarray ) ) {
				$contenido .= "echo #   " . $i . " - " . $printerarray [$i] . "                                                   #\r\n";
				$i ++;
			}
			
			$contenido .= "echo #   " . $i ++ . " - Cancelar                                                      #\r\n";
			$contenido .= "echo #                                                                     #\r\n";
			$contenido .= "echo #######################################################################\r\n";
			$contenido .= "set /p preg01= Que desea hacer? [";
			
			$i = 0;
			while ( $i <= count ( $printerarray ) ) {
				if ($i == count ( $printerarray )) {
					$contenido .= $i;
				} else {
					$contenido .= $i . ",";
				}
				
				$i ++;
			}
			$contenido .= "]\r\n";
			
			$i = 0;
			while ( $i < count ( $printerarray ) ) {
				
				$contenido .= "if %preg01%==" . $i . " goto MENU" . $i . "\r\n";
				$i ++;
			}
			
			$contenido .= "if %preg01%==" . $i ++ . " goto SALIR\r\n";
			$contenido .= "goto MENU\r\n";
			$contenido .= "pause\r\n";
			
			$i = 0;
			while ( $i < count ( $printerarray ) ) {
				
				$contenido .= ":MENU" . $i . "\r\n";
				$contenido .= "cls\r\n";
				$contenido .= "set N=%Random%%random%\r\n";
				$contenido .= "plink central.apuntes mkdir -m 0777 ~/pruebas/%N%\r\n";
				$contenido .= "pscp *.pdf central.apuntes:pruebas/%N%\r\n";
				$contenido .= "plink central.apuntes cp ~/pruebas/script_pruebas.sh ~/pruebas/%N%\r\n";
				$contenido .= "plink central.apuntes cd pruebas/%N%;./script_pruebas.sh " . $printerarray [$i] . "\r\n";
				$contenido .= "plink central.apuntes rm -dfr ~/pruebas/%N%\r\n";
				$contenido .= "EXIT\r\n";
				
				$i ++;
			}
			
			$contenido .= ":SALIR\r\n";
			$contenido .= "CLS\r\n";
			$contenido .= "ECHO Cancelando...\r\n";
			$contenido .= "EXIT\r\n";
			
			$random = rand ();
			
			mkdir ( $CFG->dataroot . '/temp/emarking/' . $random . '_bat/', 0777 );
			// chmod($random."_bat/", 0777);
			
			$fp = fopen ( $CFG->dataroot . "/temp/emarking/" . $random . "_bat/imprimir.bat", "x" );
			fwrite ( $fp, $contenido );
			fclose ( $fp );
			// Generate zip file
			$zip->addFile ( $CFG->dataroot . "/temp/emarking/" . $random . "_bat/imprimir.bat", "imprimir.bat" );
			$zip->close ();
			unlink ( $CFG->dataroot . "/temp/emarking/" . $random . "_bat/imprimir.bat" );
			rmdir ( $CFG->dataroot . "/temp/emarking/" . $random . "_bat" );
		} else if (! $sendprintorder) {
			$pdf->Output ( $file1, "F" ); // se genera el nuevo pdf
		}
		
		$downloadexam->status = EMARKING_EXAM_SENT_TO_PRINT;
		$downloadexam->printdate = time ();
		$DB->update_record ( 'emarking_exams', $downloadexam );
		
		if ($sendprintorder) {
			$pbar->update_full ( 100, 'Impresión completada exitosamente' );
			return $filedir;
		}
		
		if ($groupid != null) {
			unlink ( $file1 );
			return $filedir;
		} else {
			ob_start (); // modificación: ingreso de esta linea, ya que anterior revisión mostraba error en el archivo
			
			header ( 'Content-Description: File Transfer' );
			header ( 'Content-Type: application/x-download' );
			header ( 'Content-Disposition: attachment; filename=' . basename ( $file1 ) );
			header ( 'Content-Transfer-Encoding: binary' );
			header ( 'Expires: 0' );
			header ( 'Cache-Control: must-revalidate' );
			header ( 'Pragma: public' );
			ob_clean ();
			flush ();
			
			readfile ( $file1 );
			unlink ( $file1 ); // borra archivo temporal en moodledata
			exit ();
		}
		
		return false;
	} else {
		$students = emarking_get_students_for_printing ( $downloadexam->course );
		$filedir = $CFG->dataroot . "/temp/emarking/$context->id";
		emarking_initialize_directory ( $filedir, true );
		$printername = explode ( ',', $CFG->emarking_printername );
		$totalAlumn = 0;
		$pdffiles = array ();
		
		for($current = 0; $current < count ( $pdffileshash ); $current ++) {
			$newfile = emarking_get_path_from_hash ( $filedir, $pdffileshash [$current] ['hash'] );
			$path = $filedir . "/" . str_replace ( ' ', '-', $pdffileshash [$current] ['filename'] );
			
			$pdf = new FPDI ();
			$cp = $pdf->setSourceFile ( $path );
			if ($cp > 99) {
				print_error ( get_string ( 'page', 'mod_emarking' ) );
			}
			
			$pdf->SetAutoPageBreak ( false );
			
			$s = 1;
			
			while ( $s <= $cp + $downloadexam->extrasheets ) {
				$pdf->AddPage ();
				if ($s <= $cp) {
					$tplIdx = $pdf->importPage ( $s ); // Se importan las páginas del documento pdf.
					$pdf->useTemplate ( $tplIdx, 0, 0, 0, 0, $adjustPageSize = true ); // se inserta como template el archivo pdf subido
				}
				$s ++;
			}
			
			$pdffile = $filedir . "/" . $current . emarking_clean_filename ( $file->filename );
			$pdf->Output ( $pdffile, "F" );
			$pdffiles [] = $pdffile;
		}
		
		$totalAlumn = count ( $students );
		
		if ($pbar != null) {
			$pbar->update ( 0, $totalAlumn, '' );
		}
		
		for($k = 0; $k <= $totalAlumn + $downloadexam->extraexams - 1; $k ++) {
			$pdffile = $pdffiles [$k % count ( $pdffileshash )];
			if ($printername [$_POST ["printername"]] != "Edificio-C-mesonSecretaria") {
				$command = "lp -d " . $printername [$_POST ["printername"]] . " -o StapleLocation=UpperLeft -o fit-to-page -o media=Letter " . $pdffile;
			} else {
				$command = "lp -d " . $printername [$_POST ["printername"]] . " -o StapleLocation=SinglePortrait -o PageSize=Letter -o Duplex=none " . $pdffile;
			}
			
			// $printresult = exec ( $command );
			if ($CFG->debug) {
				echo "$command <br>";
				echo "$printresult <hr>";
			}
			
			if ($pbar != null) {
				$pbar->update ( $k, $totalAlumn, '' );
			}
		}
		
		$pbar->update_full ( 100, 'Impresión completada exitosamente' );
		
		return true;
		/*
		 * $downloadexam->status = EMARKING_EXAM_SENT_TO_PRINT; $downloadexam->printdate = time (); $DB->update_record ( 'emarking_exams', $downloadexam ); $downloadURL = $CFG->wwwroot . '/pluginfile.php/' . $file->contextid . '/mod_emarking/' . $file->filearea . '/' . $file->itemid . '/' . $file->filename; $startdownload = true; echo '<meta http-equiv="refresh" content="2;url=' . $downloadURL . '">'; return true;
		 */
	}
}

/**
 * Get all courses from a student.
 *
 * @param unknown_type $userid        	
 */
function emarking_get_courses_student($userid) {
	global $DB;
	
	$query = 'SELECT cc.id, cc.shortname, cc.fullname
			FROM {user_enrolments} ue
			JOIN {enrol} e ON (ue.userid = ? AND e.id = ue.enrolid)
			JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
			JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 3 AND ra.userid = ue.userid)
			JOIN {course} cc ON (e.courseid = cc.id)
			ORDER BY fullname ASC';
	
	// Se toman los resultados del query dentro de una variable.
	$rs = $DB->get_recordset_sql ( $query, array (
			$userid 
	) );
	
	return $rs;
}

/**
 * Get all emarking activities in a course.
 *
 * @param unknown_type $courseid        	
 */
function emarking_get_activities_course($courseid) {
	global $DB;
	
	$query = 'SELECT id, name
			FROM {emarking}
			WHERE course = ?
			ORDER BY name ASC';
	
	// Se toman los resultados del query dentro de una variable.
	$rs = $DB->get_recordset_sql ( $query, array (
			$courseid 
	) );
	
	return $rs;
}

/**
 *
 *
 *
 * Get all students from a course, for printing.
 *
 * @param unknown_type $courseid        	
 */
function emarking_get_students_for_printing($courseid) {
	global $DB;
	
	$query = 'SELECT u.id, u.idnumber, u.firstname, u.lastname, e.enrol
			FROM {user_enrolments} ue
			JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
			JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
			JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
			JOIN {user} u ON (ue.userid = u.id)
			ORDER BY lastname ASC';
	
	// Se toman los resultados del query dentro de una variable.
	$rs = $DB->get_recordset_sql ( $query, array (
			$courseid 
	) );
	
	return $rs;
}

/**
 *
 *
 *
 * Get all students from a group, for printing.
 *
 * @param unknown_type $groupid,$courseid        	
 */
function emarking_get_students_of_groups($courseid, $groupid) {
	global $DB;
	
	$query = 'SELECT u.id, u.idnumber, u.firstname, u.lastname, e.enrol
				FROM {user_enrolments} ue
				JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
				JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
				JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
				JOIN {user} u ON (ue.userid = u.id)
				JOIN {groups_members} gm ON (gm.userid = u.id AND gm.groupid = ?)
				ORDER BY lastname ASC';
	
	// Se toman los resultados del query dentro de una variable.
	$rs = $DB->get_recordset_sql ( $query, array (
			$courseid,
			$groupid 
	) );
	
	return $rs;
}

/**
 *
 *
 *
 * Get all groups from a course, for printing.
 *
 * @param unknown_type $courseid        	
 */
function emarking_get_groups_for_printing($courseid) {
	global $DB;
	
	$query = 'select id from {groups} where courseid = ? ';
	
	// Se toman los resultados del query dentro de una variable.
	$rs = $DB->get_recordset_sql ( $query, array (
			$courseid 
	) );
	
	return $rs;
}

/**
 *
 *
 *
 * Get students count from a course, for printing.
 *
 * @param unknown_type $courseid        	
 */
function emarking_get_students_count_for_printing($courseid) {
	global $DB;
	
	$query = 'SELECT count(u.id) as total
			FROM {user_enrolments} ue
			JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
			JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
			JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
			JOIN {user} u ON (ue.userid = u.id)
			GROUP BY e.courseid';
	
	// Se toman los resultados del query dentro de una variable.
	$rs = $DB->get_record_sql ( $query, array (
			$courseid 
	) );
	
	return $rs->total;
}
/**
 *
 *
 *
 * Send email with the downloading code.
 *
 * @param unknown_type $code        	
 * @param unknown_type $user        	
 * @param unknown_type $coursename        	
 * @param unknown_type $examname        	
 */
function emarking_send_email_code($code, $user, $coursename, $examname) {
	global $CFG;
	
	$posttext = 'Código de seguridad eMarking\n'; // TODO: Internacionalizar
	$posttext .= $coursename . ' ' . $examname . '\n';
	$posttext .= 'Su código: ' . $code . '';
	
	$thismessagehtml = '<html>';
	$thismessagehtml .= '<h3>Código de seguridad eMarking</h3>';
	$thismessagehtml .= $coursename . ' ' . $examname . '<br>';
	$thismessagehtml .= 'Su código:<br>' . $code . '<br>';
	$thismessagehtml .= '</html>';
	
	$subject = "Código de seguridad eMarking";
	
	$headers = "From: $CFG->supportname  \r\n" . "Reply-To: $CFG->noreplyaddress\r\n" . 'Content-Type: text/html; charset="utf-8"' . "\r\n" . 'X-Mailer: PHP/' . phpversion ();
	
	$eventdata = new stdClass ();
	$eventdata->component = 'mod_emarking';
	$eventdata->name = 'notification';
	$eventdata->userfrom = get_admin ();
	$eventdata->userto = $user;
	$eventdata->subject = $subject;
	$eventdata->fullmessage = $posttext;
	$eventdata->fullmessageformat = FORMAT_HTML;
	$eventdata->fullmessagehtml = $thismessagehtml;
	$eventdata->smallmessage = $subject;
	
	$eventdata->notification = 1;
	
	return message_send ( $eventdata );
}
/**
 *
 *
 *
 * creates email to course manager, teacher and non-editingteacher, when a printing order has been created.
 *
 * @param unknown_type $exam        	
 * @param unknown_type $course        	
 */
function emarking_send_newprintorder_notification($exam, $course) {
	global $USER;
	
	$postsubject = $course->fullname . ' : ' . $exam->name . '. ' . get_string ( 'newprintorder', 'mod_emarking' ) . ' [' . $exam->id . ']';
	
	$examhasqr = $exam->headerqr ? get_string ( 'yes' ) : get_string ( 'no' );
	
	$pagestoprint = emarking_exam_total_pages_to_print ( $exam );
	
	// Create the email to be sent
	$posthtml = '';
	$posthtml .= '<table><tr><th colspan="2">' . get_string ( 'newprintorder', 'mod_emarking' ) . '</th></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'examid', 'mod_emarking' ) . '</td><td>' . $exam->id . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'fullnamecourse' ) . '</td><td>' . $course->fullname . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'shortnamecourse' ) . '</td><td>' . $course->shortname . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'requestedby', 'mod_emarking' ) . '</td><td>' . $USER->lastname . ' ' . $USER->firstname . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'examdate', 'mod_emarking' ) . '</td><td>' . date ( "d M Y - H:i", $exam->examdate ) . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'extrasheets', 'mod_emarking' ) . '</td><td>' . $exam->extrasheets . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'extraexams', 'mod_emarking' ) . '</td><td>' . $exam->extraexams . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'headerqr', 'mod_emarking' ) . '</td><td>' . $examhasqr . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'totalpagesprint', 'mod_emarking' ) . '</td><td>' . $pagestoprint . '</td></tr>';
	$posthtml .= '</table>';
	$posthtml .= '';
	
	// Create the email to be sent
	$posttext = get_string ( 'newprintorder', 'mod_emarking' ) . '\n';
	$posttext .= get_string ( 'examid', 'mod_emarking' ) . ' : ' . $exam->id . '\n';
	$posttext .= get_string ( 'fullnamecourse' ) . ': ' . $course->fullname . '\n';
	$posttext .= get_string ( 'shortnamecourse' ) . ': ' . $course->shortname . '\n';
	$posttext .= get_string ( 'requestedby', 'mod_emarking' ) . ': ' . $USER->lastname . ' ' . $USER->firstname . '\n';
	$posttext .= get_string ( 'examdate', 'mod_emarking' ) . ': ' . date ( "d M Y - H:i", $exam->examdate ) . '\n';
	$posttext .= get_string ( 'extrasheets', 'mod_emarking' ) . ': ' . $exam->extrasheets . '\n';
	$posttext .= get_string ( 'extraexams', 'mod_emarking' ) . ': ' . $exam->extraexams . '\n';
	$posttext .= get_string ( 'headerqr', 'mod_emarking' ) . ': ' . $examhasqr . '\n';
	$posttext .= get_string ( 'totalpagesprint', 'mod_emarking' ) . ': ' . $pagestoprint . '\n';
	
	emarking_send_notification ( $exam, $course, $postsubject, $posttext, $posthtml );
}

/**
 * Sends email to course manager, teacher and non-editingteacher, when a printing order has been created
 *
 * @param unknown $exam        	
 * @param unknown $course        	
 * @param unknown $postsubject        	
 * @param unknown $posttext        	
 * @param unknown $posthtml        	
 */
function emarking_send_notification($exam, $course, $postsubject, $posttext, $posthtml) {
	global $USER, $CFG;
	
	$context = context_course::instance ( $course->id );
	
	// Notify users that a new exam was sent. First, get all roles that have the capability in this context or higher
	$roles = get_roles_with_cap_in_context ( $context, 'mod/emarking:receivenotification' );
	foreach ( $roles [0] as $role ) {
		$needed = $role;
	}
	$forbidden = $roles [1];
	
	// Get all users with any of the needed roles in the course context
	$userstonotify = get_role_users ( $needed, $context );
	
	// Get the category context
	$contextcategory = context_coursecat::instance ( $course->category );
	
	// Add all users with needed roles in the course category
	foreach ( get_role_users ( $needed, $contextcategory ) as $userfromcategory ) {
		$userstonotify [] = $userfromcategory;
	}
	
	// Now get all users that has any of the roles needed, no checking if they have roles forbidden as it is only
	// a notification
	foreach ( $userstonotify as $user ) {
		
		$thismessagehtml = $posthtml;
		
		// Downloading predominates over receiving notification
		if (has_capability ( 'mod/emarking:downloadexam', $contextcategory, $user )) {
			$thismessagehtml .= '<p><a href="' . $CFG->wwwroot . '/mod/emarking/printorders.php?category=' . $course->category . '">' . get_string ( 'printorders', 'mod_emarking' ) . '</a></p>';
		} else if (has_capability ( 'mod/emarking:receivenotification', $context, $user )) {
			$thismessagehtml .= '<p><a href="' . $CFG->wwwroot . '/mod/emarking/exams.php?course=' . $course->id . '">' . get_string ( 'printorders', 'mod_emarking' ) . ' ' . $course->fullname . '</a></p>';
		}
		
		$eventdata = new stdClass ();
		$eventdata->component = 'mod_emarking';
		$eventdata->name = 'notification';
		$eventdata->userfrom = $USER;
		$eventdata->userto = $user->id;
		$eventdata->subject = $postsubject;
		$eventdata->fullmessage = $posttext;
		$eventdata->fullmessageformat = FORMAT_HTML;
		$eventdata->fullmessagehtml = $thismessagehtml;
		$eventdata->smallmessage = $postsubject;
		
		$eventdata->notification = 1;
		message_send ( $eventdata );
	}
}

/**
 *
 *
 *
 * Returns all paralles to a course based on de code defined for the bibliography regular expression.
 *
 * @param stdClass $course        	
 */
function emarking_get_parallel_courses($course, $extracategory, $regex) {
	global $CFG, $DB;
	
	if ($regex && preg_match_all ( '/' . $regex . '/', $course->shortname, $regs )) {
		if (isset ( $regs [1] [0] ) && isset ( $regs [2] [0] ) && isset ( $regs [3] [0] )) {
			$coursecode = $regs [1] [0];
			
			$term = $regs [2] [0];
			$year = $regs [3] [0];
			
			$categories = $course->category;
			/*if ($extracategory > 0)
				$categories .= ',' . $extracategory;*/
			$seccionesparalelas = $DB->get_records_select ( 'course', "
				shortname like '%$coursecode%-%-$term-$year'
				and id != $course->id", null, 'shortname ASC', '*' );
			
			return $seccionesparalelas;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
/**
 *
 *
 *
 * TODO: poner explicacion de lo que hace
 *
 * @param unknown_type $course        	
 * @return unknown
 */
function emarking_get_categories_for_parallels_menu($course) {
	global $DB;
	
	$category = $DB->get_record ( 'course_categories', array (
			'id' => $course->category 
	) );
	$categories = explode ( '/', $category->path );
	
	$sql = "select id, name
	from {course_categories}
	where (path like '/$categories[1]' or path like '/$categories[1]/%' or path like '%/$categories[1]/%')
	and id <> $course->category and id <> $categories[1]
	order by depth";
	
	$result = $DB->get_records_select_menu ( 'course_categories', "(path like '/$categories[1]' or path like '/$categories[1]/%' or path like '%/$categories[1]/%')
			and id <> $course->category and id <> $categories[1]", null, 'depth', 'id, name' );
	
	return ($result);
}

/**
 *
 *
 *
 * Creates a grade scale.
 *
 * @param unknown_type $min        	
 * @param unknown_type $max        	
 * @param unknown_type $grade        	
 * @param unknown_type $mingrade        	
 * @param unknown_type $maxgrade        	
 */
function emarking_scale_grade($min, $max, $grade, $mingrade, $maxgrade) {
	$gradepct = ($grade - $mingrade) / ($maxgrade - $mingrade);
	
	return round ( $min + ($max - $min) * $gradepct, 1 );
}

/**
 * Unzip the source_file in the destination dir
 *
 * @param
 *        	string The path to the ZIP-file.
 * @param
 *        	string The path where the zipfile should be unpacked, if false the directory of the zip-file is used
 * @param
 *        	boolean Indicates if the files will be unpacked in a directory with the name of the zip-file (true) or not (false) (only if the destination directory is set to false!)
 * @param
 *        	boolean Overwrite existing files (true) or not (false)
 *        	
 * @return boolean Succesful or not
 */
function emarking_unzip($src_file, $dest_dir = false, $create_zip_name_dir = true, $overwrite = true) {
	global $CFG;
	
	if ($zip = zip_open ( $src_file )) {
		if ($zip) {
			$splitter = ($create_zip_name_dir === true) ? "." : "/";
			if ($dest_dir === false)
				$dest_dir = substr ( $src_file, 0, strrpos ( $src_file, $splitter ) ) . "/";
				
				// Create the directories to the destination dir if they don't already exist
			emarking_create_dirs ( $dest_dir );
			
			// For every file in the zip-packet
			while ( $zip_entry = zip_read ( $zip ) ) {
				// Now we're going to create the directories in the destination directories
				
				// If the file is not in the root dir
				$pos_last_slash = strrpos ( zip_entry_name ( $zip_entry ), "/" );
				if ($pos_last_slash !== false) {
					// Create the directory where the zip-entry should be saved (with a "/" at the end)
					emarking_create_dirs ( $dest_dir . substr ( zip_entry_name ( $zip_entry ), 0, $pos_last_slash + 1 ) );
				}
				
				// Open the entry
				if (zip_entry_open ( $zip, $zip_entry, "r" )) {
					
					// The name of the file to save on the disk
					$file_name = $dest_dir . zip_entry_name ( $zip_entry );
					
					// Check if the files should be overwritten or not
					if ($overwrite === true || $overwrite === false && ! is_file ( $file_name )) {
						// Get the content of the zip entry
						$fstream = zip_entry_read ( $zip_entry, zip_entry_filesize ( $zip_entry ) );
						
						file_put_contents ( $file_name, $fstream );
						// Set the rights
						chmod ( $file_name, 0777 );
					}
					
					// Close the entry
					zip_entry_close ( $zip_entry );
				}
			}
			// Close the zip-file
			zip_close ( $zip );
		}
	} else {
		return false;
	}
	
	return true;
}

/**
 * This function creates recursive directories if it doesn't already exist
 *
 * @param
 *        	String The path that should be created
 *        	
 * @return void
 */
function emarking_create_dirs($path) {
	if (! is_dir ( $path )) {
		$directory_path = "";
		$directories = explode ( "/", $path );
		array_pop ( $directories );
		
		foreach ( $directories as $directory ) {
			$directory_path .= $directory . "/";
			if (! is_dir ( $directory_path )) {
				mkdir ( $directory_path );
				chmod ( $directory_path, 0777 );
			}
		}
	}
}
function emarking_get_totalscore($submission, $controller, $fillings) {
	global $DB;
	
	$curscore = 0;
	foreach ( $fillings ['criteria'] as $id => $record ) {
		$curscore += $controller->get_definition ()->rubric_criteria [$id] ['levels'] [$record ['levelid']] ['score'];
	}
	
	$bonus = 0;
	if ($bonusfromcomments = $DB->get_record_sql ( "
			SELECT 1, IFNULL(SUM(ec.bonus),0) AS totalbonus
			FROM {emarking_comment} AS ec
			INNER JOIN {emarking_page} AS ep ON (ep.submission = :submission AND ec.page = ep.id)
			WHERE ec.levelid > 0", array (
			'submission' => $submission->id 
	) )) {
		$bonus = floatval ( $bonusfromcomments->totalbonus );
	}
	
	return $curscore + $bonus;
}
function emarking_calculate_grades_users($emarking, $userid = 0) {
	global $DB, $USER, $CFG;
	
	require_once ($CFG->dirroot . '/grade/grading/lib.php');
	
	if (! $cm = get_coursemodule_from_instance ( 'emarking', $emarking->id )) {
		return;
	}
	
	$context = context_module::instance ( $cm->id );
	
	// Get the grading manager, then method and finally controller
	$gradingmanager = get_grading_manager ( $context, 'mod_emarking', 'attempt' );
	$gradingmethod = $gradingmanager->get_active_method ();
	$controller = $gradingmanager->get_controller ( $gradingmethod );
	$range = $controller->get_grade_range ();
	$rubricscores = $controller->get_min_max_score ();
	$totalrubricscore = $rubricscores ['maxscore'];
	
	$filter = 'WHERE 1=1';
	if ($userid > 0)
		$filter = 'WHERE es.student = ' . $userid;
	$studentscores = $DB->get_records_sql ( "
			SELECT es.id,
			es.student,
			sum(ifnull(rl.score,0)) as score,
			sum(ifnull(ec.bonus,0)) as bonus,
			sum(ifnull(rl.score,0)) + sum(ifnull(ec.bonus,0)) as totalscore
			FROM {emarking_submission} AS es
			INNER JOIN {emarking_page} AS ep ON (es.emarking = :emarking AND ep.submission = es.id)
			LEFT JOIN {emarking_comment} AS ec ON (ec.page = ep.id AND ec.levelid > 0)
			LEFT JOIN {gradingform_rubric_levels} AS rl ON (ec.levelid = rl.id)
			$filter
			AND es.status >= 10
			GROUP BY es.emarking, es.id", array (
			'emarking' => $emarking->id 
	) );
	
	foreach ( $studentscores as $studentscore ) {
		$totalscore = min ( floatval ( $studentscore->totalscore ), $totalrubricscore );
		
		$finalgrade = emarking_calculate_grade ( $emarking, $totalscore, $totalrubricscore );
		
		$submission = $DB->get_record ( 'emarking_submission', array (
				'id' => $studentscore->id 
		) );
		$submission->grade = $finalgrade;
		$DB->update_record ( 'emarking_submission', $submission );
	}
	
	return true;
}

/**
 * Calculates the grade according to score
 * and corrects if there is a slope adjustment
 *
 * @param unknown $emarking        	
 * @param unknown $totalscore        	
 * @param unknown $totalrubricscore        	
 * @return Ambigous <number, mixed>
 */
function emarking_calculate_grade($emarking, $totalscore, $totalrubricscore) {
	if (isset ( $emarking->adjustslope ) && $emarking->adjustslope) {
		$finalgrade = min ( $emarking->grade, ((($emarking->adjustslopegrade - $emarking->grademin) / $emarking->adjustslopescore) * $totalscore) + $emarking->grademin );
	} else {
		$finalgrade = ((($emarking->grade - $emarking->grademin) / $totalrubricscore) * $totalscore) + $emarking->grademin;
	}
	
	return $finalgrade;
}
function emarking_publish_all_grades($emarking) {
	global $DB, $USER, $CFG;
	
	$studentsubmissions = $DB->get_records ( "emarking_submission", array (
			'emarking' => $emarking->id 
	) );
	
	foreach ( $studentsubmissions as $submission ) {
		if ($submission->status >= EMARKING_STATUS_RESPONDED)
			emarking_publish_grade ( $submission );
	}
	
	return true;
}
function emarking_set_finalgrade($userid = 0, $levelid = 0, $levelfeedback = '', $submission = null, $emarking = null, $context = null, $generalfeedback = null, $delete = false, $cmid = 0) {
	global $USER, $DB, $CFG;
	
	require_once ($CFG->dirroot . '/grade/grading/lib.php');
	
	// Validate parameters
	if ($userid == 0 || ($levelid == 0 && $cmid == 0) || $submission == null || $context == null) {
		return array (
				false,
				false,
				false 
		);
	}
	
	if ($levelid > 0) {
		// Firstly get the rubric definition id and criterion id from the level
		$rubricinfo = $DB->get_record_sql ( "
				SELECT c.definitionid, l.definition, l.criterionid, l.score, c.description
				FROM {gradingform_rubric_levels} as l
				INNER JOIN {gradingform_rubric_criteria} as c on (l.criterionid = c.id)
				WHERE l.id = ?", array (
				$levelid 
		) );
	} elseif ($cmid > 0) {
		// Firstly get the rubric definition id and criterion id from the level
		$rubricinfo = $DB->get_record_sql ( "
				SELECT
				d.id as definitionid
				FROM {course_modules} AS c
				inner join {context} AS mc on (c.id = ? AND c.id = mc.instanceid)
				inner join {grading_areas} AS ar on (mc.id = ar.contextid)
				inner join {grading_definitions} AS d on (ar.id = d.areaid)
				", array (
				$cmid 
		) );
	} else {
		return null;
	}
	
	// Get the grading manager, then method and finally controller
	$gradingmanager = get_grading_manager ( $context, 'mod_emarking', 'attempt' );
	$gradingmethod = $gradingmanager->get_active_method ();
	$controller = $gradingmanager->get_controller ( $gradingmethod );
	$controller->set_grade_range ( array (
			"$emarking->grademin" => $emarking->grademin,
			"$emarking->grade" => $emarking->grade 
	), true );
	$definition = $controller->get_definition ();
	
	// Get the grading instance we should already have
	$gradinginstancerecord = $DB->get_record ( 'grading_instances', array (
			'itemid' => $submission->id,
			'definitionid' => $definition->id 
	) );
	
	// Use the last marking rater id to get the instance
	$raterid = $USER->id;
	$itemid = null;
	if ($gradinginstancerecord) {
		if ($gradinginstancerecord->raterid > 0) {
			$raterid = $gradinginstancerecord->raterid;
		}
		$itemid = $gradinginstancerecord->id;
	}
	
	// Get or create grading instance (in case submission has not been graded)
	$gradinginstance = $controller->get_or_create_instance ( $itemid, $raterid, $submission->id );
	
	$rubricscores = $controller->get_min_max_score ();
	
	// Get the fillings and replace the new one accordingly
	$fillings = $gradinginstance->get_rubric_filling ();
	
	if ($levelid > 0) {
		if ($delete) {
			if (! $minlevel = $DB->get_record_sql ( '
					SELECT id, score
					FROM {gradingform_rubric_levels}
					WHERE criterionid = ?
					ORDER BY score ASC LIMIT 1', array (
					$rubricinfo->criterionid 
			) )) {
				return array (
						false,
						false,
						false 
				);
			}
			$newfilling = array (
					"remark" => '',
					"levelid" => $minlevel->id 
			);
		} else {
			$newfilling = array (
					"remark" => $levelfeedback,
					"levelid" => $levelid 
			);
		}
		if (isset ( $fillings ['criteria'] [$rubricinfo->criterionid] ['levelid'] ) && isset ( $fillings ['criteria'] [$rubricinfo->criterionid] ['remark'] )) {
			$previouslvlid = $fillings ['criteria'] [$rubricinfo->criterionid] ['levelid'];
			$previouscomment = $fillings ['criteria'] [$rubricinfo->criterionid] ['remark'];
		} else {
			$previouslvlid = 0;
			$previouscomment = null;
		}
		$fillings ['criteria'] [$rubricinfo->criterionid] = $newfilling;
	} else {
		$previouslvlid = 0;
		$previouscomment = null;
	}
	
	$fillings ['raterid'] = $raterid;
	$gradinginstance->update ( $fillings );
	$rawgrade = $gradinginstance->get_grade ();
	
	$grade_item = grade_item::fetch ( array (
			'itemmodule' => 'emarking',
			'iteminstance' => $submission->emarkingid 
	) );
	
	$previousfeedback = '';
	$previousfeedback = $submission->generalfeedback == null ? '' : $submission->generalfeedback;
	
	if ($generalfeedback == null) {
		$generalfeedback = $previousfeedback;
	}
	
	$totalscore = emarking_get_totalscore ( $submission, $controller, $fillings );
	$finalgrade = emarking_calculate_grade ( $emarking, $totalscore, $rubricscores ['maxscore'] );
	
	$submission->grade = $finalgrade + $gradebonus;
	$submission->generalfeedback = $generalfeedback;
	$submission->status = $emarking->status < EMARKING_STATUS_RESPONDED ? EMARKING_STATUS_GRADING : EMARKING_STATUS_REGRADING;
	$submission->timemodified = time ();
	
	if ($DB->count_records ( "emarking_draft", array (
			"emarkingid" => $submission->emarkingid,
			"submissionid" => $submission->submissionid 
	) ) > 1) {
		$DB->update_record ( 'emarking_draft', $submission );
	} else {
		$DB->update_record ( 'emarking_draft', $submission );
		$DB->update_record ( 'emarking_submission', $submission );
	}
	
	return array (
			$finalgrade + $gradebonus,
			$previouslvlid,
			$previouscomment 
	);
}

/**
 *
 * @param unknown $submission        	
 */
function emarking_publish_grade($submission) {
	global $CFG, $DB, $USER;
	
	require_once ($CFG->libdir . '/gradelib.php');
	
	if ($submission->status <= EMARKING_STATUS_ABSENT)
		return;
		
		// Copy final grade to gradebook
	$grade_item = grade_item::fetch ( array (
			'itemmodule' => 'emarking',
			'iteminstance' => $submission->emarkingid 
	) );
	
	$feedback = $submission->generalfeedback ? $submission->generalfeedback : '';
	
	$grade_item->update_final_grade ( $submission->student, $submission->grade, 'editgrade', $feedback, FORMAT_HTML, $USER->id );
	
	if ($submission->status <= EMARKING_STATUS_RESPONDED) {
		$submission->status = EMARKING_STATUS_RESPONDED;
	}
	
	$submission->timemodified = time ();
	$DB->update_record ( 'emarking_draft', $submission );
	
	$realsubmission = $DB->get_record ( "emarking_submission", array (
			"id" => $submission->submissionid 
	) );
	$realsubmission->status = $submission->status;
	$realsubmission->timemodified = $submission->timemodified;
	$realsubmission->generalfeedback = $submission->generalfeedback;
	$realsubmission->grade = $submission->grade;
	$realsubmission->teacher = $submission->teacher;
	$DB->update_record ( 'emarking_submission', $realsubmission );
}

/**
 * Calculates the next submission to be graded when a marker is currently grading
 * a specific submission
 *
 * @param unknown $emarking        	
 * @param unknown $submission        	
 * @param unknown $context        	
 * @return number
 */
function emarking_get_next_submission($emarking, $submission, $context, $student) {
	global $DB, $USER;
	
	$levelids = 0;
	if ($criteria = $DB->get_records ( 'emarking_marker_criterion', array (
			'emarking' => $emarking->id,
			'marker' => $USER->id 
	) )) {
		
		$criterionarray = array ();
		foreach ( $criteria as $criterion ) {
			$criterionarray [] = $criterion->criterion;
		}
		$criteriaids = implode ( ",", $criterionarray );
		
		$levelssql = "SELECT * FROM {gradingform_rubric_levels} WHERE criterionid in ($criteriaids)";
		$levels = $DB->get_records_sql ( $levelssql );
		$levelsarray = array ();
		foreach ( $levels as $level ) {
			$levelsarray [] = $level->id;
		}
		$levelids = implode ( ",", $levelsarray );
	}
	
	$sortsql = $emarking->anonymous ? " s.sort ASC" : " u.lastname ASC";
	
	$criteriafilter = $levelids == 0 ? "" : " AND s.id NOT IN (SELECT s.id
	FROM {emarking_submission} as s
	INNER JOIN {emarking_page} as p ON (s.emarking = $emarking->id AND s.status < 20 AND p.submission = s.id)
	INNER JOIN {emarking_comment} as c ON (c.page = p.id AND c.levelid IN ($levelids))
	GROUP BY s.id)";
	
	$sortfilter = $emarking->anonymous ? " AND sort > $submission->sort" : " AND u.lastname > '$student->lastname'";
	
	$basesql = "SELECT s.id
			FROM {emarking_draft} as s
			INNER JOIN {user} as u ON (s.student = u.id)
			WHERE s.emarkingid = :emarkingid AND s.submissionid <> :submissionid AND s.status < 20 AND s.status >= 10";
	
	$sql = "$basesql
	$criteriafilter
	$sortfilter
	ORDER BY $sortsql";
	// Gets the next submission id, limits start from 0 and get a total of 1
	$nextsubmissions = $DB->get_records_sql ( $sql, array (
			'emarkingid' => $emarking->id,
			'submissionid' => $submission->id 
	), 0, 1 );
	$id = 0;
	foreach ( $nextsubmissions as $nextsubmission ) {
		$id = $nextsubmission->id;
	}
	
	// If we could not find a submission using the sortorder, we try from the beginning
	if ($id == 0) {
		$sql = "$basesql
		$criteriafilter
		ORDER BY $sortsql";
		
		$nextsubmissions = $DB->get_records_sql ( $sql, array (
				'emarkingid' => $emarking->id,
				'submissionid' => $submission->id 
		), 0, 1 );
		foreach ( $nextsubmissions as $nextsubmission ) {
			$id = $nextsubmission->id;
		}
	}
	return $id;
}

/**
 * This function gets a page to display on the eMarking interface using the page number, user id and emarking id
 *
 * @param unknown $pageno        	
 * @param unknown $submission        	
 * @param string $anonymous        	
 * @param unknown $contextid        	
 * @return multitype:NULL number |multitype:unknown string NULL Ambigous <unknown, NULL>
 */
function emarking_get_page_image($pageno, $submission, $anonymous = false, $contextid) {
	global $CFG, $DB;
	
	$numfiles = $DB->count_records_sql ( '
			SELECT MAX(page) as pages
			FROM {emarking_page}
			WHERE submission=?
			GROUP BY submission', array (
			$submission->id,
			$submission->student 
	) );
	
	if (! $page = $DB->get_record ( 'emarking_page', array (
			'submission' => $submission->id,
			'student' => $submission->student,
			'page' => $pageno 
	) )) {
		
		return array (
				new moodle_url ( '/mod/emarking/pix/missing.png' ),
				800,
				1035,
				$numfiles 
		);
	}
	
	$fileid = $anonymous ? $page->fileanonymous : $page->file;
	
	$fs = get_file_storage ();
	
	if (! $file = $fs->get_file_by_id ( $fileid )) {
		print_error ( 'Attempting to display image for non-existant submission ' . $contextid . "_" . $submission->emarkingid . "_" . $pagefilename );
	}
	
	if ($imageinfo = $file->get_imageinfo ()) {
		$imgurl = file_encode_url ( $CFG->wwwroot . '/pluginfile.php', '/' . $contextid . '/mod_emarking/pages/' . $submission->emarkingid . '/' . $file->get_filename () );
		return array (
				$imgurl,
				$imageinfo ['width'],
				$imageinfo ['height'],
				$numfiles 
		);
	}
	
	return array (
			null,
			0,
			0,
			$numfiles 
	);
}

/**
 * This function gets a page to display on the eMarking interface using the page number, user id and emarking id
 *
 * @param unknown $pageno        	
 * @param unknown $submission        	
 * @param string $anonymous        	
 * @param unknown $contextid        	
 * @return multitype:NULL number |multitype:unknown string NULL Ambigous <unknown, NULL>
 */
function emarking_rotate_image($pageno, $submission, $context) {
	global $CFG, $DB;
	
	ini_set ( 'memory_limit', '256M' );
	
	// If the page does not exist return false
	if (! $page = $DB->get_record ( 'emarking_page', array (
			'submission' => $submission->id,
			'student' => $submission->student,
			'page' => $pageno 
	) )) {
		return false;
	}
	
	if (! $student = $DB->get_record ( 'user', array (
			'id' => $submission->student 
	) )) {
		return false;
	}
	
	// Now get the file from the Moodle storage
	$fs = get_file_storage ();
	
	if (! $file = $fs->get_file_by_id ( $page->file )) {
		print_error ( 'Attempting to display image for non-existant submission ' . $context->id . "_" . $submission->emarkingid . "_" . $pagefilename );
	}
	
	// Si el archivo es una imagen
	if ($imageinfo = $file->get_imageinfo ()) {
		
		$tmppath = $file->copy_content_to_temp ( 'emarking', 'rotate' );
		$image = imagecreatefrompng ( $tmppath );
		$image = imagerotate ( $image, 180, 0 );
		if (! imagepng ( $image, $tmppath . '.png' )) {
			return false;
		}
		clearstatcache ();
		$filename = $file->get_filename ();
		$timecreated = $file->get_timecreated ();
		
		// Copy file from temp folder to Moodle's filesystem
		$file_record = array (
				'contextid' => $context->id,
				'component' => 'mod_emarking',
				'filearea' => 'pages',
				'itemid' => $submission->emarking,
				'filepath' => '/',
				'filename' => $filename,
				'timecreated' => $timecreated,
				'timemodified' => time (),
				'userid' => $student->id,
				'author' => $student->firstname . ' ' . $student->lastname,
				'license' => 'allrightsreserved' 
		);
		
		if (! $fileanonymous = $fs->get_file_by_id ( $page->fileanonymous )) {
			print_error ( 'Attempting to display image for non-existant submission ' . $context->id . "_" . $submission->emarkingid . "_" . $pagefilename );
		}
		
		$size = getimagesize ( $tmppath . '.png' );
		$image = imagecreatefrompng ( $tmppath . '.png' );
		$white = imagecolorallocate ( $image, 255, 255, 255 );
		$y2 = round ( $size [1] / 10, 0 );
		imagefilledrectangle ( $image, 0, 0, $size [0], $y2, $white );
		
		if (! imagepng ( $image, $tmppath . '_a.png' )) {
			return false;
		}
		clearstatcache ();
		$filenameanonymous = $fileanonymous->get_filename ();
		$timecreatedanonymous = $fileanonymous->get_timecreated ();
		
		// Copy file from temp folder to Moodle's filesystem
		$file_record_anonymous = array (
				'contextid' => $context->id,
				'component' => 'mod_emarking',
				'filearea' => 'pages',
				'itemid' => $submission->emarkingid,
				'filepath' => '/',
				'filename' => $filenameanonymous,
				'timecreated' => $timecreatedanonymous,
				'timemodified' => time (),
				'userid' => $student->id,
				'author' => $student->firstname . ' ' . $student->lastname,
				'license' => 'allrightsreserved' 
		);
		
		if ($fs->file_exists ( $context->id, 'mod_emarking', 'pages', $submission->emarkingid, '/', $filename )) {
			$file->delete ();
		}
		$fileinfo = $fs->create_file_from_pathname ( $file_record, $tmppath . '.png' );
		
		if ($fs->file_exists ( $context->id, 'mod_emarking', 'pages', $submission->emarkingid, '/', $filenameanonymous )) {
			$fileanonymous->delete ();
		}
		$fileinfoanonymous = $fs->create_file_from_pathname ( $file_record_anonymous, $tmppath . '_a.png' );
		
		$page->file = $fileinfo->get_id ();
		$page->fileanonymous = $fileinfoanonymous->get_id ();
		$DB->update_record ( 'emarking_page', $page );
		
		$imgurl = file_encode_url ( $CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/mod_emarking/pages/' . $submission->emarkingid . '/' . $fileinfo->get_filename () );
		$imgurl .= "?r=" . random_string ( 15 );
		$imgurlanonymous = file_encode_url ( $CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/mod_emarking/pages/' . $submission->emarkingid . '/' . $fileinfoanonymous->get_filename () );
		$imgurlanonymous .= "?r=" . random_string ( 15 );
		return array (
				$imgurl,
				$imgurlanonymous,
				$imageinfo ['width'],
				$imageinfo ['height'] 
		);
	}
	
	return false;
}

/**
 * Gets a list of the pages allowed to be seen and interact for this user
 *
 * @param unknown $emarking        	
 * @return array of page numbers
 */
function emarking_get_allowed_pages($emarking) {
	global $DB, $USER;
	
	$allowedpages = array ();
	
	// We add page 0 so array_search returns only positive values for normal pages
	$allowedpages [] = 0;
	
	// If there is criteria assigned for this emarking activity
	if ($criteria = $DB->get_records ( 'emarking_page_criterion', array (
			'emarking' => $emarking->id 
	) )) {
		// Organize pages per criterion
		$criteriapages = array ();
		foreach ( $criteria as $cr ) {
			if (! isset ( $criteriapages [$cr->criterion] ))
				$criteriapages [$cr->criterion] = array ();
			$criteriapages [$cr->criterion] [] = $cr->page;
		}
		$filteredbycriteria = true;
		
		// Get criteria the user is allowed to see
		$usercriteria = $DB->get_records ( 'emarking_marker_criterion', array (
				'emarking' => $emarking->id,
				'marker' => $USER->id 
		) );
		
		// Add pages to allowed array if the user can see them
		foreach ( $usercriteria as $uc ) {
			if (isset ( $criteriapages [$uc->criterion] ))
				$allowedpages = array_merge ( $allowedpages, $criteriapages [$uc->criterion] );
		}
		// If there is no criteria assigned, all pages are allowed
	} else {
		// Get the maximum page number in the emarking activity
		if ($max = $DB->get_record_sql ( '
				SELECT MAX(page) AS pagenumber 
				FROM {emarking_submission} AS s 
				INNER JOIN {emarking_page} AS p ON (p.submission = s.id AND s.emarking = :emarking)', array (
				'emarking' => $emarking->id 
		) )) {
			for($i = 1; $i <= $max->pagenumber; $i ++) {
				$allowedpages [] = $i;
			}
			// If no pages yet, we get the total pages from the activity if it is set
		} else if ($emarking->totalpages > 0) {
			for($i = 1; $i <= $emarking->totalpages; $i ++) {
				$allowedpages [] = $i;
			}
			// Finally we assume there are less than 50 pages
		} else {
			for($i = 1; $i <= 50; $i ++) {
				$allowedpages [] = $i;
			}
		}
	}
	
	// Sort the array
	asort ( $allowedpages );
	
	return $allowedpages;
}

/**
 *
 * @param unknown $emarking        	
 * @param unknown $submission        	
 * @param unknown $anonymous        	
 * @param unknown $context        	
 * @return multitype:stdClass
 */
function emarking_get_all_pages($emarking, $submission, $anonymous, $context) {
	global $DB, $CFG, $USER;
	
	$emarkingpages = array ();
	
	// Get criteria to filter pages
	$filterpages = false;
	$allowedpages = array ();
	
	// If user is supervisor, site admin or the student who owns the submission, we should not filter
	if (has_capability ( 'mod/emarking:supervisegrading', $context ) || is_siteadmin () || $USER->id == $submission->student) {
		$filterpages = false;
	} else if (
	// If it is another student (can't grade nor add instances) and peer visibility is allowed, we don't filter
	// but we force it as anonymous
	! has_capability ( 'mod/emarking:grade', $context ) && $emarking->peervisibility) {
		$filterpages = false;
		$anonymous = true;
	} else {
		// Remaining case is for markers
		$filterpages = true;
		
		$allowedpages = emarking_get_allowed_pages ( $emarking );
	}
	
	// In case there are no pages for this submission, we generate missing pages for those allowed
	if (! $pages = $DB->get_records ( 'emarking_page', array (
			'submission' => $submission->id 
	), 'page ASC' )) {
		if ($emarking->totalpages > 0) {
			for($i = 0; $i < $emarking->totalpages; $i ++) {
				$emarkingpage = new stdClass ();
				$emarkingpage->url = $CFG->wwwroot . '/mod/emarking/pix/missing.png';
				$emarkingpage->width = 800;
				$emarkingpage->height = 1035;
				$emarkingpage->totalpages = $emarking->totalpages;
				if ($filterpages) {
					$emarkingpage->showmarker = array_search ( $i + 1, $allowedpages ) !== false ? 1 : 0;
				} else {
					$emarkingpage->showmarker = 1;
				}
				
				$emarkingpages [] = $emarkingpage;
			}
		}
		return $emarkingpages;
	}
	
	$fs = get_file_storage ();
	$numfiles = max ( count ( $pages ), $emarking->totalpages );
	$pagecount = 0;
	
	foreach ( $pages as $page ) {
		$pagecount ++;
		
		$pagenumber = $page->page;
		
		while ( count ( $emarkingpages ) < $pagenumber - 1 ) {
			$emarkingpage = new stdClass ();
			$emarkingpage->url = $CFG->wwwroot . '/mod/emarking/pix/missing.png';
			$emarkingpage->width = 800;
			$emarkingpage->height = 1035;
			$emarkingpage->totalpages = $numfiles;
			
			if ($filterpages) {
				$emarkingpage->showmarker = array_search ( count ( $emarkingpages ) + 1, $allowedpages ) !== false ? 1 : 0;
			} else {
				$emarkingpage->showmarker = 1;
			}
			
			$emarkingpages [] = $emarkingpage;
		}
		
		$fileid = $anonymous ? $page->fileanonymous : $page->file;
		if (! $file = $fs->get_file_by_id ( $fileid )) {
			$emarkingpage = new stdClass ();
			$emarkingpage->url = $CFG->wwwroot . '/mod/emarking/pix/missing.png';
			$emarkingpage->width = 800;
			$emarkingpage->height = 1035;
			$emarkingpage->totalpages = $numfiles;
			
			if ($filterpages) {
				$emarkingpage->showmarker = array_search ( $pagenumber, $allowedpages ) !== false ? 1 : 0;
			} else {
				$emarkingpage->showmarker = 1;
			}
			
			$emarkingpages [] = $emarkingpage;
		}
		
		if ($imageinfo = $file->get_imageinfo ()) {
			$imgurl = file_encode_url ( $CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/mod_emarking/pages/' . $submission->emarkingid . '/' . $file->get_filename () );
			$emarkingpage = new stdClass ();
			$emarkingpage->url = $imgurl . "?r=" . random_string ( 15 );
			$emarkingpage->width = $imageinfo ['width'];
			$emarkingpage->height = $imageinfo ['height'];
			$emarkingpage->totalpages = $numfiles;
			
			if ($filterpages) {
				$emarkingpage->showmarker = array_search ( $pagenumber, $allowedpages ) !== false ? 1 : 0;
			} else {
				$emarkingpage->showmarker = 1;
			}
			
			$emarkingpages [] = $emarkingpage;
		}
	}
	return $emarkingpages;
}
function emarking_validate_rubric($context, $die = true, $showform = true) {
	global $OUTPUT, $CFG;
	
	require_once ($CFG->dirroot . '/grade/grading/lib.php');
	
	// Get rubric instance
	$gradingmanager = get_grading_manager ( $context, 'mod_emarking', 'attempt' );
	$gradingmethod = $gradingmanager->get_active_method ();
	$definition = null;
	if ($gradingmethod === 'rubric') {
		$rubriccontroller = $gradingmanager->get_controller ( $gradingmethod );
		$definition = $rubriccontroller->get_definition ();
	}
	
	$managerubricurl = new moodle_url ( '/grade/grading/manage.php', array (
			'contextid' => $context->id,
			'component' => 'mod_emarking',
			'area' => 'attempt' 
	) );
	
	// Validate that activity has a rubric ready
	if ($gradingmethod !== 'rubric' || ! $definition || $definition == null) {
		if ($showform) {
			echo $OUTPUT->notification ( get_string ( 'rubricneeded', 'mod_emarking' ), 'notifyproblem' );
			echo $OUTPUT->single_button ( $managerubricurl, get_string ( 'createrubric', 'mod_emarking' ) );
		}
		if ($die) {
			echo $OUTPUT->footer ();
			die ();
		}
	}
	if (isset ( $definition->status )) {
		if ($definition->status == 10) {
			
			echo $OUTPUT->notification ( get_string ( 'rubricdraft', 'mod_emarking' ), 'notifyproblem' );
			echo $OUTPUT->single_button ( $managerubricurl, get_string ( 'completerubric', 'mod_emarking' ) );
		}
	}
	
	return array (
			$gradingmanager,
			$gradingmethod 
	);
}
function emarking_upload_answers($emarking, $fileid, $course, $cm, progress_bar $progressbar = null) {
	global $CFG, $DB;
	
	$context = context_module::instance ( $cm->id );
	
	// Setup de directorios temporales
	$tempdir = emarking_get_temp_dir_path ( $emarking->id );
	
	if (! emarking_unzip ( $fileid, $tempdir . "/" )) {
		return array (
				false,
				get_string ( 'errorprocessingextraction', 'mod_emarking' ),
				0,
				0 
		);
	}
	
	$numpages = emarking_count_files_in_dir ( $tempdir, ".png" );
	
	if ($numpages < 1) {
		die ( $tempdir );
		return array (
				false,
				get_string ( 'invalidpdfnopages', 'mod_emarking' ),
				0,
				0 
		);
	}
	
	$totalDocumentsProcessed = 0;
	$totalDocumentsIgnored = 0;
	
	// Read full directory, then start processing
	$files = scandir ( $tempdir );
	
	$doubleside = false;
	
	$pdfFiles = array ();
	foreach ( $files as $fileInTemp ) {
		if (! is_dir ( $fileInTemp ) && strtolower ( substr ( $fileInTemp, - 4, 4 ) ) === ".png") {
			$pdfFiles [] = $fileInTemp;
			if (strtolower ( substr ( $fileInTemp, - 5, 5 ) ) === "b.png") {
				$doubleside = true;
			}
		}
	}
	
	$total = count ( $pdfFiles );
	
	// Process files
	for($current = 0; $current < $total; $current ++) {
		
		$file = $pdfFiles [$current];
		
		$filename = explode ( ".", $file );
		$parts = explode ( "-", $filename [0] );
		if (count ( $parts ) != 3) {
			if ($CFG->debug)
				echo "Ignoring $file as it has invalid name";
			$totalDocumentsIgnored ++;
			continue;
		}
		
		$studentid = $parts [0];
		$courseid = $parts [1];
		$pagenumber = $parts [2];
		
		if (! $student = $DB->get_record ( 'user', array (
				'id' => $studentid 
		) )) {
			$totalDocumentsIgnored ++;
			continue;
		}
		
		if ($courseid != $course->id) {
			$totalDocumentsIgnored ++;
			continue;
		}
		
		if ($progressbar) {
			$progressbar->update ( $current, $total, $student->firstname . " " . $student->lastname );
		}
		
		// 1 pasa a 1 1 * 2 - 1 = 1
		// 1b pasa a 2 1 * 2
		// 2 pasa a 3 2 * 2 -1 = 3
		// 2b pasa a 4 2 * 2
		$anonymouspage = false;
		// First clean the page number if it's anonymous
		if (substr ( $pagenumber, - 2 ) === "_a") {
			$pagenumber = substr ( $pagenumber, 0, strlen ( $pagenumber ) - 2 );
			$anonymouspage = true;
		}
		
		if ($doubleside) {
			if (substr ( $pagenumber, - 1 ) === "b") { // Detecta b
				$pagenumber = intval ( $pagenumber ) * 2;
			} else {
				$pagenumber = intval ( $pagenumber ) * 2 - 1;
			}
		}
		
		if ($anonymouspage) {
			continue;
		}
		
		if (! is_numeric ( $pagenumber )) {
			if ($CFG->debug) {
				echo "Ignored file: $filename[0] page: $pagenumber student id: $studentid course id: $courseid";
			}
			$totalDocumentsIgnored ++;
			continue;
		}
		
		if (emarking_submit ( $emarking, $context, $tempdir, $file, $student, $pagenumber )) {
			$totalDocumentsProcessed ++;
		} else {
			return array (
					false,
					get_string ( 'invalidzipnoanonymous', 'mod_emarking' ),
					$totalDocumentsProcessed,
					$totalDocumentsIgnored 
			);
		}
	}
	
	return array (
			true,
			get_string ( 'invalidpdfnopages', 'mod_emarking' ),
			$totalDocumentsProcessed,
			$totalDocumentsIgnored 
	);
}

/**
 * Creates the PDF version (downloadable) of the whole feedback produced by the teacher/tutor
 *
 * @param int $submissionid        	
 * @return boolean
 */
function emarking_create_response_pdf($submission, $student, $context, $cmid) {
	global $CFG, $DB;
	
	require_once $CFG->libdir . '/pdflib.php';
	
	$fs = get_file_storage ();
	
	if (! $pages = $DB->get_records ( 'emarking_page', array (
			'submission' => $submission->id,
			'student' => $student->id 
	), 'page ASC' )) {
		return false;
	}
	
	$emarking = $DB->get_record ( 'emarking', array (
			'id' => $submission->emarkingid 
	) );
	
	$numpages = count ( $pages );
	
	$sqlcomments = "SELECT ec.id,
			ec.posx,
			ec.posy,
			ec.rawtext,
			ec.pageno,
			grm.maxscore,
			ec.levelid,
			ec.width,
			ec.colour,
			ec.textformat,
			grl.score AS score,
			grl.definition AS leveldesc,
			grc.id AS criterionid,
			grc.description AS criteriondesc,
			u.id AS markerid, CONCAT(u.firstname,' ',u.lastname) AS markername
			FROM {emarking_comment} AS ec
			INNER JOIN {emarking_page} AS ep ON (ep.submission = :submission AND ec.page = ep.id)
			LEFT JOIN {user} AS u ON (ec.markerid = u.id)
			LEFT JOIN {gradingform_rubric_levels} AS grl ON (ec.levelid = grl.id)
			LEFT JOIN {gradingform_rubric_criteria} AS grc ON (grl.criterionid = grc.id)
			LEFT JOIN (
			SELECT grl.criterionid, max(score) AS maxscore
			FROM {gradingform_rubric_levels} AS grl
			GROUP BY grl.criterionid
			) AS grm ON (grc.id = grm.criterionid)
			WHERE ec.pageno > 0
			ORDER BY ec.pageno";
	$params = array (
			'submission' => $submission->id 
	);
	$comments = $DB->get_records_sql ( $sqlcomments, $params );
	
	$commentsperpage = array ();
	
	foreach ( $comments as $comment ) {
		if (! isset ( $commentsperpage [$comment->pageno] )) {
			$commentsperpage [$comment->pageno] = array ();
		}
		
		$commentsperpage [$comment->pageno] [] = $comment;
	}
	
	// Parameters for PDF generation
	$iconsize = 5;
	
	$tempdir = emarking_get_temp_dir_path ( $emarking->id );
	if (! file_exists ( $tempdir )) {
		mkdir ( $tempdir );
	}
	
	// create new PDF document
	$pdf = new TCPDF ( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );
	
	// set document information
	$pdf->SetCreator ( PDF_CREATOR );
	$pdf->SetAuthor ( $student->firstname . ' ' . $student->lastname );
	$pdf->SetTitle ( $emarking->name );
	$pdf->SetSubject ( 'Exam feedback' );
	$pdf->SetKeywords ( 'feedback, emarking' );
	$pdf->SetPrintHeader ( false );
	$pdf->SetPrintFooter ( false );
	
	// set default header data
	$pdf->SetHeaderData ( PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 036', PDF_HEADER_STRING );
	
	// set header and footer fonts
	$pdf->setHeaderFont ( Array (
			PDF_FONT_NAME_MAIN,
			'',
			PDF_FONT_SIZE_MAIN 
	) );
	$pdf->setFooterFont ( Array (
			PDF_FONT_NAME_DATA,
			'',
			PDF_FONT_SIZE_DATA 
	) );
	
	// set default monospaced font
	$pdf->SetDefaultMonospacedFont ( PDF_FONT_MONOSPACED );
	
	// set margins
	$pdf->SetMargins ( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );
	$pdf->SetHeaderMargin ( PDF_MARGIN_HEADER );
	$pdf->SetFooterMargin ( PDF_MARGIN_FOOTER );
	
	// set auto page breaks
	$pdf->SetAutoPageBreak ( TRUE, PDF_MARGIN_BOTTOM );
	
	// set image scale factor
	$pdf->setImageScale ( PDF_IMAGE_SCALE_RATIO );
	
	// set some language-dependent strings (optional)
	if (@file_exists ( dirname ( __FILE__ ) . '/lang/eng.php' )) {
		require_once (dirname ( __FILE__ ) . '/lang/eng.php');
		$pdf->setLanguageArray ( $l );
	}
	
	// ---------------------------------------------------------
	
	// set font
	$pdf->SetFont ( 'times', '', 16 );
	
	foreach ( $pages as $page ) {
		// add a page
		$pdf->AddPage ();
		
		// get the current page break margin
		$bMargin = $pdf->getBreakMargin ();
		// get current auto-page-break mode
		$auto_page_break = $pdf->getAutoPageBreak ();
		// disable auto-page-break
		$pdf->SetAutoPageBreak ( false, 0 );
		// set bacground image
		$pngfile = $fs->get_file_by_id ( $page->file );
		$img_file = emarking_get_path_from_hash ( $tempdir, $pngfile->get_pathnamehash () );
		$pdf->Image ( $img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0 );
		// restore auto-page-break status
		// $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
		// set the starting point for the page content
		$pdf->setPageMark ();
		
		$widthratio = $pdf->getPageWidth () / 800;
		
		if (isset ( $commentsperpage [$page->page] )) {
			foreach ( $commentsperpage [$page->page] as $comment ) {
				
				$content = $comment->rawtext;
				
				if ($comment->textformat == 1) {
					// text annotation
					$pdf->Annotation ( $comment->posx * $widthratio, $comment->posy * $widthratio, 6, 6, $content, array (
							'Subtype' => 'Text',
							'StateModel' => 'Review',
							'State' => 'None',
							'Name' => 'Comment',
							'NM' => 'Comment' . $comment->id,
							'T' => $comment->markername,
							'Subj' => 'example',
							'C' => array (
									0,
									0,
									255 
							) 
					) );
				} elseif ($comment->textformat == 2) {
					$content = $comment->criteriondesc . ': ' . round ( $comment->score, 1 ) . '/' . round ( $comment->maxscore, 1 ) . "\n" . $comment->leveldesc . "\n" . get_string ( 'comment', 'mod_emarking' ) . ': ' . $content;
					// text annotation
					$pdf->Annotation ( $comment->posx * $widthratio, $comment->posy * $widthratio, 6, 6, $content, array (
							'Subtype' => 'Text',
							'StateModel' => 'Review',
							'State' => 'None',
							'Name' => 'Comment',
							'NM' => 'Mark' . $comment->id,
							'T' => $comment->markername,
							'Subj' => 'grade',
							'C' => array (
									255,
									255,
									0 
							) 
					) );
				} elseif ($comment->textformat == 3) {
					$pdf->Image ( $CFG->dirroot . "/mod/emarking/img/check.gif", $comment->posx * $widthratio, $comment->posy * $widthratio, $iconsize, $iconsize, '', '', '', false, 300, '', false, false, 0 );
				} elseif ($comment->textformat == 4) {
					$pdf->Image ( $CFG->dirroot . "/mod/emarking/img/crossed.gif", $comment->posx * $widthratio, $comment->posy * $widthratio, $iconsize, $iconsize, '', '', '', false, 300, '', false, false, 0 );
				}
			}
		}
	}
	// ---------------------------------------------------------
	
	// COGIDO PARA IMPRIMIR RÚBRICA
	if ($emarking->downloadrubricpdf) {
		
		$cm = new StdClass ();
		
		$rubricdesc = $DB->get_recordset_sql ( "SELECT
		d.name AS rubricname,
		a.id AS criterionid,
		a.description ,
		b.definition,
		b.id AS levelid,
		b.score,
		IFNULL(E.id,0) AS commentid,
		IFNULL(E.pageno,0) AS commentpage,
		E.rawtext AS commenttext,
		E.markerid AS markerid,
		IFNULL(E.textformat,2) AS commentformat,
		IFNULL(E.bonus,0) AS bonus,
		IFNULL(er.id,0) AS regradeid,
		IFNULL(er.motive,0) AS motive,
		er.comment AS regradecomment,
		IFNULL(er.markercomment, '') AS regrademarkercomment,
		IFNULL(er.accepted,0) AS regradeaccepted
		FROM {course_modules} AS c
		INNER JOIN {context} AS mc ON (c.id = :coursemodule AND c.id = mc.instanceid)
		INNER JOIN {grading_areas} AS ar ON (mc.id = ar.contextid)
		INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
		INNER JOIN {gradingform_rubric_criteria} AS a ON (d.id = a.definitionid)
		INNER JOIN {gradingform_rubric_levels} AS b ON (a.id = b.criterionid)
		LEFT JOIN (
		SELECT ec.*, es.id AS submissionid
		FROM {emarking_comment} AS ec
		INNER JOIN {emarking_page} AS ep ON (ec.page = ep.id)
		INNER JOIN {emarking_draft} AS es ON (es.id = :submission AND ep.submission = es.id)
		) AS E ON (E.levelid = b.id)
		LEFT JOIN {emarking_regrade} AS er ON (er.criterion = a.id AND er.submission = E.submissionid)
		ORDER BY a.sortorder ASC, b.score ASC", array (
				'coursemodule' => $cmid,
				'submission' => $submission->id 
		) );
		
		$table = new html_table ();
		$data = array ();
		foreach ( $rubricdesc as $rd ) {
			if (! isset ( $data [$rd->criterionid] )) {
				$data [$rd->criterionid] = array (
						$rd->description,
						$rd->definition . " (" . round ( $rd->score, 2 ) . " ptos. )" 
				);
			} else {
				array_push ( $data [$rd->criterionid], $rd->definition . " (" . round ( $rd->score, 2 ) . " ptos. )" );
			}
		}
		$table->data = $data;
		
		// add extra page with rubrics
		$pdf->AddPage ();
		$pdf->Write ( 0, 'Rúbrica', '', 0, 'L', true, 0, false, false, 0 );
		$pdf->SetFont ( 'helvetica', '', 8 );
		
		$tbl = html_writer::table ( $table );
		
		$pdf->writeHTML ( $tbl, true, false, false, false, '' );
	}
	// ---------------------------------------------------------
	
	$pdffilename = 'response_' . $emarking->id . '_' . $student->id . '.pdf';
	$pathname = $tempdir . '/' . $pdffilename;
	
	if (@file_exists ( $pathname )) {
		unlink ( $pathname );
	}
	
	// Close and output PDF document
	$pdf->Output ( $pathname, 'F' );
	
	// Copiar archivo desde temp a Área
	$file_record = array (
			'contextid' => $context->id,
			'component' => 'mod_emarking',
			'filearea' => 'response',
			'itemid' => $student->id,
			'filepath' => '/',
			'filename' => $pdffilename,
			'timecreated' => time (),
			'timemodified' => time (),
			'userid' => $student->id,
			'author' => $student->firstname . ' ' . $student->lastname,
			'license' => 'allrightsreserved' 
	);
	
	// Si el archivo ya existía entonces lo borramos
	if ($fs->file_exists ( $context->id, 'mod_emarking', 'response', $student->id, '/', $pdffilename )) {
		$previousfile = $fs->get_file ( $context->id, 'mod_emarking', 'response', $student->id, '/', $pdffilename );
		$previousfile->delete ();
	}
	
	$fileinfo = $fs->create_file_from_pathname ( $file_record, $pathname );
	
	return true;
}
function emarking_json_output($jsonOutput) {
	// Callback para from webpage
	$callback = optional_param ( 'callback', null, PARAM_RAW_TRIMMED );
	
	// Headers
	header ( 'Content-Type: text/javascript' );
	header ( 'Cache-Control: no-cache' );
	header ( 'Pragma: no-cache' );
	
	if ($callback)
		$jsonOutput = $callback . "(" . $jsonOutput . ");";
	
	echo $jsonOutput;
	die ();
}
function emarking_json_resultset($resultset) {
	
	// Verify that parameters are OK. Resultset should not be null.
	if (! is_array ( $resultset ) && ! $resultset) {
		emarking_json_error ( 'Invalid parameters for encoding json. Results are null.' );
	}
	
	// First check if results contain data
	if (is_array ( $resultset )) {
		$output = array (
				'error' => '',
				'values' => array_values ( $resultset ) 
		);
		emarking_json_output ( json_encode ( $output ) );
	} else {
		$output = array (
				'error' => '',
				'values' => $resultset 
		);
		emarking_json_output ( json_encode ( $resultset ) );
	}
}
function emarking_json_array($output) {
	
	// Verify that parameter is OK. Output should not be null.
	if (! $output) {
		emarking_json_error ( 'Invalid parameters for encoding json. output is null.' );
	}
	
	$output = array (
			'error' => '',
			'values' => $output 
	);
	emarking_json_output ( json_encode ( $output ) );
}
function emarking_json_error($message, $values = null) {
	$output = array (
			'error' => $message,
			'values' => $values 
	);
	emarking_json_output ( json_encode ( $output ) );
}
function emarking_create_printform($context, $exam, $userrequests, $useraccepts, $category, $totalpages, $course) {
	global $CFG;
	
	require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php");
	require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi.php");
	
	$cantsheets = $totalpages / ($exam->totalstudents + $exam->extraexams);
	$totalextraexams = $exam->totalstudents + $exam->extraexams;
	$canttotalpages = $cantsheets * $totalextraexams;
	
	$pdf = new FPDI ();
	$cp = $pdf->setSourceFile ( $CFG->dirroot . "/mod/emarking/img/printformtemplate.pdf" );
	for($i = 1; $i <= $cp; $i ++) {
		$pdf->AddPage (); // Agrega una nueva página
		if ($i <= $cp) {
			$tplIdx = $pdf->importPage ( $i ); // Se importan las pÃƒÂ¡ginas del documento pdf.
			$pdf->useTemplate ( $tplIdx, 0, 0, 0, 0, $adjustPageSize = true ); // se inserta como template el archivo pdf subido
			                                                                   
			// Copia/Impresión/Plotteo
			$pdf->SetXY ( 32, 48.5 );
			$pdf->Write ( 1, "x" );
			// Fecha día
			$pdf->SetXY ( 153, 56 );
			$pdf->Write ( 1, core_text::strtoupper ( date ( 'd' ) ) );
			// Fecha mes
			$pdf->SetXY ( 163, 56 );
			$pdf->Write ( 1, core_text::strtoupper ( date ( 'm' ) ) );
			// Fecha año
			$pdf->SetXY ( 173, 56 );
			$pdf->Write ( 1, core_text::strtoupper ( date ( 'Y' ) ) );
			// Solicitante
			$pdf->SetXY ( 95, 69 );
			$pdf->Write ( 1, core_text::strtoupper ( $useraccepts->firstname . " " . $useraccepts->lastname ) );
			// Centro de Costo
			$pdf->SetXY ( 95, 75.5 );
			$pdf->Write ( 1, core_text::strtoupper ( $category->idnumber ) );
			// Campus UAI
			$pdf->SetXY ( 95, 80.8 );
			$pdf->Write ( 1, core_text::strtoupper ( "" ) );
			// Número originales
			$pdf->SetXY ( 35, 106.5 );
			$pdf->Write ( 1, core_text::strtoupper ( $cantsheets ) );
			// Número copias
			$pdf->SetXY ( 60, 106.5 );
			$pdf->Write ( 1, core_text::strtoupper ( "--" ) );
			// Número impresiones
			$pdf->SetXY ( 84, 106.5 );
			$pdf->Write ( 1, core_text::strtoupper ( $totalextraexams ) );
			// BN
			$pdf->SetXY ( 106, 106.5 );
			$pdf->Write ( 1, "x" );
			// Páginas totales
			$pdf->SetXY ( 135, 106.5 );
			$pdf->Write ( 1, core_text::strtoupper ( $canttotalpages ) );
			// Número impresiones Total
			$pdf->SetXY ( 84, 133.8 );
			$pdf->Write ( 1, core_text::strtoupper ( "" ) );
			// Páginas totales Total
			$pdf->SetXY ( 135, 133.8 );
			$pdf->Write ( 1, core_text::strtoupper ( "" ) );
			// Páginas totales Total
			$pdf->SetXY ( 43, 146 );
			$pdf->Write ( 1, core_text::strtoupper ( $course->fullname . " , " . $exam->name ) );
			// Recepcionado por Nombre
			$pdf->SetXY ( 30, 164.5 );
			$pdf->Write ( 1, core_text::strtoupper ( "" ) );
			// Recepcionado por RUT
			$pdf->SetXY ( 127, 164.5 );
			$pdf->Write ( 1, core_text::strtoupper ( "" ) );
		}
	}
	$pdf->Output ( "PrintForm" . $exam->id . ".pdf", "I" ); // se genera el nuevo pdf
}

/**
 * This function return if the emarking activity accepts
 * regrade requests at the current time.
 *
 * @param unknown $emarking        	
 * @return boolean
 */
function emarking_is_regrade_requests_allowed($emarking) {
	$requestswithindate = false;
	if (! $emarking->regraderestrictdates) {
		$requestswithindate = true;
	} elseif ($emarking->regradesopendate < time () && $emarking->regradesclosedate > time ()) {
		$requestswithindate = true;
	}
	return $requestswithindate;
}
function emarking_get_categories_childs($id_category) {
	$coursecat = coursecat::get ( $id_category );
	
	$ids = $id_category;
	
	foreach ( $coursecat->get_children () as $categories_children ) {
		
		$coursecat_children = coursecat::get ( $categories_children->id );
		
		if ($coursecat_children->has_children ()) {
			$array_children = emarking_get_categories_childs ( $categories_children->id );
			$ids .= "," . $array_children;
		} else {
			$ids .= "," . $categories_children->id;
		}
	}
	
	return $ids;
}
/**
 * Creates the PDF version (downloadable) of the whole feedback produced by the teacher/tutor
 *
 * @param int $submissionid        	
 * @return boolean
 *
 */
function emarking_multi_create_response_pdf($submission, $student, $context, $cmid) {
	global $CFG, $DB;
	
	require_once $CFG->libdir . '/pdflib.php';
	
	$fs = get_file_storage ();
	
	if (! $pages = $DB->get_records ( 'emarking_page', array (
			'submission' => $submission->id,
			'student' => $student->id 
	), 'page ASC' )) {
		return false;
	}
	
	$emarking = $DB->get_record ( 'emarking', array (
			'id' => $submission->emarking 
	) );
	
	$numpages = count ( $pages );
	
	$sqlcomments = "SELECT ec.id,
			ec.posx,
			ec.posy,
			ec.rawtext,
			ec.pageno,
			grm.maxscore,
			ec.levelid,
			ec.width,
			ec.colour,
			ec.textformat,
			grl.score AS score,
			grl.definition AS leveldesc,
			grc.id AS criterionid,
			grc.description AS criteriondesc,
			u.id AS markerid, CONCAT(u.firstname,' ',u.lastname) AS markername
			FROM {emarking_comment} AS ec
			INNER JOIN {emarking_page} AS ep ON (ep.submission = :submission AND ec.page = ep.id)
			LEFT JOIN {user} AS u ON (ec.markerid = u.id)
			LEFT JOIN {gradingform_rubric_levels} AS grl ON (ec.levelid = grl.id)
			LEFT JOIN {gradingform_rubric_criteria} AS grc ON (grl.criterionid = grc.id)
			LEFT JOIN (
			SELECT grl.criterionid, max(score) AS maxscore
			FROM {gradingform_rubric_levels} AS grl
			GROUP BY grl.criterionid
			) AS grm ON (grc.id = grm.criterionid)
			WHERE ec.pageno > 0
			ORDER BY ec.pageno";
	$params = array (
			'submission' => $submission->id 
	);
	$comments = $DB->get_records_sql ( $sqlcomments, $params );
	
	$commentsperpage = array ();
	
	foreach ( $comments as $comment ) {
		if (! isset ( $commentsperpage [$comment->pageno] )) {
			$commentsperpage [$comment->pageno] = array ();
		}
		
		$commentsperpage [$comment->pageno] [] = $comment;
	}
	
	// Parameters for PDF generation
	$iconsize = 5;
	
	$tempdir = emarking_get_temp_dir_path ( $emarking->id );
	if (! file_exists ( $tempdir )) {
		mkdir ( $tempdir );
	}
	
	// create new PDF document
	$pdf = new TCPDF ( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );
	
	// set document information
	$pdf->SetCreator ( PDF_CREATOR );
	$pdf->SetAuthor ( $student->firstname . ' ' . $student->lastname );
	$pdf->SetTitle ( $emarking->name );
	$pdf->SetSubject ( 'Exam feedback' );
	$pdf->SetKeywords ( 'feedback, emarking' );
	$pdf->SetPrintHeader ( false );
	$pdf->SetPrintFooter ( false );
	
	// set default header data
	$pdf->SetHeaderData ( PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 036', PDF_HEADER_STRING );
	
	// set header and footer fonts
	$pdf->setHeaderFont ( Array (
			PDF_FONT_NAME_MAIN,
			'',
			PDF_FONT_SIZE_MAIN 
	) );
	$pdf->setFooterFont ( Array (
			PDF_FONT_NAME_DATA,
			'',
			PDF_FONT_SIZE_DATA 
	) );
	
	// set default monospaced font
	$pdf->SetDefaultMonospacedFont ( PDF_FONT_MONOSPACED );
	
	// set margins
	$pdf->SetMargins ( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );
	$pdf->SetHeaderMargin ( PDF_MARGIN_HEADER );
	$pdf->SetFooterMargin ( PDF_MARGIN_FOOTER );
	
	// set auto page breaks
	$pdf->SetAutoPageBreak ( TRUE, PDF_MARGIN_BOTTOM );
	
	// set image scale factor
	$pdf->setImageScale ( PDF_IMAGE_SCALE_RATIO );
	
	// set some language-dependent strings (optional)
	if (@file_exists ( dirname ( __FILE__ ) . '/lang/eng.php' )) {
		require_once (dirname ( __FILE__ ) . '/lang/eng.php');
		$pdf->setLanguageArray ( $l );
	}
	
	// ---------------------------------------------------------
	
	// set font
	$pdf->SetFont ( 'times', '', 16 );
	
	foreach ( $pages as $page ) {
		// add a page
		$pdf->AddPage ();
		
		// get the current page break margin
		$bMargin = $pdf->getBreakMargin ();
		// get current auto-page-break mode
		$auto_page_break = $pdf->getAutoPageBreak ();
		// disable auto-page-break
		$pdf->SetAutoPageBreak ( false, 0 );
		// set bacground image
		$pngfile = $fs->get_file_by_id ( $page->file );
		$img_file = emarking_get_path_from_hash ( $tempdir, $pngfile->get_pathnamehash () );
		$pdf->Image ( $img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0 );
		// restore auto-page-break status
		// $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
		// set the starting point for the page content
		$pdf->setPageMark ();
		
		$widthratio = $pdf->getPageWidth () / 800;
		
		if (isset ( $commentsperpage [$page->page] )) {
			foreach ( $commentsperpage [$page->page] as $comment ) {
				
				$content = $comment->rawtext;
				
				if ($comment->textformat == 1) {
					// text annotation
					$pdf->Annotation ( $comment->posx * $widthratio, $comment->posy * $widthratio, 6, 6, $content, array (
							'Subtype' => 'Text',
							'StateModel' => 'Review',
							'State' => 'None',
							'Name' => 'Comment',
							'NM' => 'Comment' . $comment->id,
							'T' => $comment->markername,
							'Subj' => 'example',
							'C' => array (
									0,
									0,
									255 
							) 
					) );
				} elseif ($comment->textformat == 2) {
					$content = $comment->criteriondesc . ': ' . round ( $comment->score, 1 ) . '/' . round ( $comment->maxscore, 1 ) . "\n" . $comment->leveldesc . "\n" . get_string ( 'comment', 'mod_emarking' ) . ': ' . $content;
					// text annotation
					$pdf->Annotation ( $comment->posx * $widthratio, $comment->posy * $widthratio, 6, 6, $content, array (
							'Subtype' => 'Text',
							'StateModel' => 'Review',
							'State' => 'None',
							'Name' => 'Comment',
							'NM' => 'Mark' . $comment->id,
							'T' => $comment->markername,
							'Subj' => 'grade',
							'C' => array (
									255,
									255,
									0 
							) 
					) );
				} elseif ($comment->textformat == 3) {
					$pdf->Image ( $CFG->dirroot . "/mod/emarking/img/check.gif", $comment->posx * $widthratio, $comment->posy * $widthratio, $iconsize, $iconsize, '', '', '', false, 300, '', false, false, 0 );
				} elseif ($comment->textformat == 4) {
					$pdf->Image ( $CFG->dirroot . "/mod/emarking/img/crossed.gif", $comment->posx * $widthratio, $comment->posy * $widthratio, $iconsize, $iconsize, '', '', '', false, 300, '', false, false, 0 );
				}
			}
		}
	}
	// ---------------------------------------------------------
	
	// COGIDO PARA IMPRIMIR RÚBRICA
	if ($emarking->downloadrubricpdf) {
		
		$cm = new StdClass ();
		
		$rubricdesc = $DB->get_recordset_sql ( "SELECT
		d.name AS rubricname,
		a.id AS criterionid,
		a.description ,
		b.definition,
		b.id AS levelid,
		b.score,
		IFNULL(E.id,0) AS commentid,
		IFNULL(E.pageno,0) AS commentpage,
		E.rawtext AS commenttext,
		E.markerid AS markerid,
		IFNULL(E.textformat,2) AS commentformat,
		IFNULL(E.bonus,0) AS bonus,
		IFNULL(er.id,0) AS regradeid,
		IFNULL(er.motive,0) AS motive,
		er.comment AS regradecomment,
		IFNULL(er.markercomment, '') AS regrademarkercomment,
		IFNULL(er.accepted,0) AS regradeaccepted
		FROM {course_modules} AS c
		INNER JOIN {context} AS mc ON (c.id = :coursemodule AND c.id = mc.instanceid)
		INNER JOIN {grading_areas} AS ar ON (mc.id = ar.contextid)
		INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
		INNER JOIN {gradingform_rubric_criteria} AS a ON (d.id = a.definitionid)
		INNER JOIN {gradingform_rubric_levels} AS b ON (a.id = b.criterionid)
		LEFT JOIN (
		SELECT ec.*, es.id AS submissionid
		FROM {emarking_comment} AS ec
		INNER JOIN {emarking_page} AS ep ON (ec.page = ep.id)
		INNER JOIN {emarking_draft} AS es ON (es.id = :submission AND ep.submission = es.id)
		) AS E ON (E.levelid = b.id)
		LEFT JOIN {emarking_regrade} AS er ON (er.criterion = a.id AND er.submission = E.submissionid)
		ORDER BY a.sortorder ASC, b.score ASC", array (
				'coursemodule' => $cmid,
				'submission' => $submission->id 
		) );
		
		$table = new html_table ();
		$data = array ();
		foreach ( $rubricdesc as $rd ) {
			if (! isset ( $data [$rd->criterionid] )) {
				$data [$rd->criterionid] = array (
						$rd->description,
						$rd->definition . " (" . round ( $rd->score, 2 ) . " ptos. )" 
				);
			} else {
				array_push ( $data [$rd->criterionid], $rd->definition . " (" . round ( $rd->score, 2 ) . " ptos. )" );
			}
		}
		$table->data = $data;
		
		// add extra page with rubrics
		$pdf->AddPage ();
		$pdf->Write ( 0, 'Rúbrica', '', 0, 'L', true, 0, false, false, 0 );
		$pdf->SetFont ( 'helvetica', '', 8 );
		
		$tbl = html_writer::table ( $table );
		
		$pdf->writeHTML ( $tbl, true, false, false, false, '' );
	}
	// ---------------------------------------------------------
	
	$pdffilename = 'response_' . $emarking->id . '_' . $student->id . '.pdf';
	$pathname = $tempdir . '/' . $pdffilename;
	
	if (@file_exists ( $pathname )) {
		unlink ( $pathname );
	}
	
	// Close and output PDF document
	$pdf->Output ( $pathname, 'F' );
	
	// Copiar archivo desde temp a Área
	$file_record = array (
			'contextid' => $context->id,
			'component' => 'mod_emarking',
			'filearea' => 'response',
			'itemid' => $student->id,
			'filepath' => '/',
			'filename' => $pdffilename,
			'timecreated' => time (),
			'timemodified' => time (),
			'userid' => $student->id,
			'author' => $student->firstname . ' ' . $student->lastname,
			'license' => 'allrightsreserved' 
	);
	
	// Si el archivo ya existía entonces lo borramos
	if ($fs->file_exists ( $context->id, 'mod_emarking', 'response', $student->id, '/', $pdffilename )) {
		$previousfile = $fs->get_file ( $context->id, 'mod_emarking', 'response', $student->id, '/', $pdffilename );
		$previousfile->delete ();
	}
	
	$fileinfo = $fs->create_file_from_pathname ( $file_record, $pathname );
	
	return true;
}
function emarking_multi_publish_grade($submission) {
	global $CFG, $DB, $USER;
	
	require_once ($CFG->libdir . '/gradelib.php');
	
	if ($submission->status <= EMARKING_STATUS_ABSENT)
		return;
		
		// Copy final grade to gradebook
	$grade_item = grade_item::fetch ( array (
			'itemmodule' => 'emarking',
			'iteminstance' => $submission->emarking 
	) );
	
	$feedback = $submission->generalfeedback ? $submission->generalfeedback : '';
	
	$grade_item->update_final_grade ( $submission->student, $submission->grade, 'editgrade', $feedback, FORMAT_HTML, $USER->id );
	
	if ($submission->status <= EMARKING_STATUS_RESPONDED) {
		$submission->status = EMARKING_STATUS_RESPONDED;
	}
	
	$submission->timemodified = time ();
	$DB->update_record ( 'emarking_draft', $submission );
	
	$realsubmission = $DB->get_record ( "emarking_submission", array (
			"id" => $submission->id 
	) );
	$realsubmission->status = $submission->status;
	$realsubmission->timemodified = $submission->timemodified;
	$realsubmission->generalfeedback = $submission->generalfeedback;
	$realsubmission->grade = $submission->grade;
	$realsubmission->teacher = $submission->teacher;
	$DB->update_record ( 'emarking_submission', $realsubmission );
}