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
	$examstab = new tabobject ( "home", $CFG->wwwroot . "/mod/emarking/print/exams.php?id={$cm->id}", get_string ( "printdigitize", 'mod_emarking' ) );
	$examstab->subtree [] = new tabobject ( "myexams", $CFG->wwwroot . "/mod/emarking/print/exams.php?id={$cm->id}", get_string ( "myexams", 'mod_emarking' ) );
	
	$examstab->subtree [] = new tabobject ( "newprintorder", $CFG->wwwroot . "/mod/emarking/print/newprintorder.php?cm={$cm->id}", get_string ( "newprintorder", 'mod_emarking' ) );
	$examstab->subtree [] = new tabobject ( "uploadanswers", $CFG->wwwroot . "/mod/emarking/print/upload.php?id={$cm->id}", get_string ( 'uploadanswers', 'mod_emarking' ) );
	
	// Grade tab
	$gradetab = new tabobject ( "grade", $CFG->wwwroot . "/mod/emarking/view.php?id={$cm->id}", get_string ( 'annotatesubmission', 'mod_emarking' ) );
	$gradetab->subtree [] = new tabobject ( "mark", $CFG->wwwroot . "/mod/emarking/view.php?id={$cm->id}", get_string ( "marking", 'mod_emarking' ) );
	if (! $usercangrade) {
		if ($CFG->emarking_enablejustice && $emarking->peervisibility) {
			$gradetab->subtree [] = new tabobject ( "ranking", $CFG->wwwroot . "/mod/emarking/reports/ranking.php?id={$cm->id}", get_string ( "ranking", 'mod_emarking' ) );
			$gradetab->subtree [] = new tabobject ( "viewpeers", $CFG->wwwroot . "/mod/emarking/reports/viewpeers.php?id={$cm->id}", get_string ( "justice.peercheck", 'mod_emarking' ) );
		}
		$gradetab->subtree [] = new tabobject ( "regrade", $CFG->wwwroot . "/mod/emarking/marking/regrades.php?id={$cm->id}", get_string ( "regrades", 'mod_emarking' ) );
	} else {
		if (has_capability ( 'mod/emarking:regrade', $context ))
			$gradetab->subtree [] = new tabobject ( "regrades", $CFG->wwwroot . "/mod/emarking/marking/regraderequests.php?cmid={$cm->id}", get_string ( "regrades", 'mod_emarking' ) );
		if (has_capability ( 'mod/emarking:assignmarkers', $context ))
			$gradetab->subtree [] = new tabobject ( "markers", $CFG->wwwroot . "/mod/emarking/marking/markers.php?id={$cm->id}", get_string ( "markers", 'mod_emarking' ) );
	}
	
	if (isset ( $CFG->local_uai_debug ) && $CFG->local_uai_debug == 1) {
		$gradetab->subtree [] = new tabobject ( "comment", $CFG->wwwroot . "/mod/emarking/marking/comment.php?id={$cm->id}&action=list", "comment" );
	}
	
	// Grade report tab
	$gradereporttab = new tabobject ( "gradereport", $CFG->wwwroot . "/mod/emarking/reports/gradereport.php?id={$cm->id}", get_string ( "reports", "mod_emarking" ) );
	
	$gradereporttab->subtree [] = new tabobject ( "report", $CFG->wwwroot . "/mod/emarking/reports/gradereport.php?id={$cm->id}", get_string ( "gradereport", "grades" ) );
	$gradereporttab->subtree [] = new tabobject ( "markingreport", $CFG->wwwroot . "/mod/emarking/reports/markingreport.php?id={$cm->id}", get_string ( "markingreport", 'mod_emarking' ) );
	$gradereporttab->subtree [] = new tabobject ( "comparison", $CFG->wwwroot . "/mod/emarking/reports/comparativereport.php?id={$cm->id}", get_string ( "comparativereport", "mod_emarking" ) );
	$gradereporttab->subtree [] = new tabobject ( "ranking", $CFG->wwwroot . "/mod/emarking/reports/ranking.php?id={$cm->id}", get_string ( "ranking", 'mod_emarking' ) );
	
	// Tabs sequence
	if ($usercangrade) {
		$tabs [] = $gradetab;
		$tabs [] = $gradereporttab;
		if (has_capability ( 'mod/emarking:uploadexam', $context ))
			$tabs [] = $examstab;
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
	$tabs [] = new tabobject ( "printorders", $CFG->wwwroot . "/mod/emarking/print/printorders.php?category={$category->id}&status=1", get_string ( "printorders", 'mod_emarking' ) );
	$tabs [] = new tabobject ( "printordershistory", $CFG->wwwroot . "/mod/emarking/print/printorders.php?category={$category->id}&status=2", get_string ( "records", 'mod_emarking' ) );
	$tabs [] = new tabobject ( "statistics", $CFG->wwwroot . "/mod/emarking/print/statistics.php?category={$category->id}", get_string ( "statistics", 'mod_emarking' ) );
	
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
			$thismessagehtml .= '<p><a href="' . $CFG->wwwroot . '/mod/emarking/print/printorders.php?category=' . $course->category . '">' . get_string ( 'printorders', 'mod_emarking' ) . '</a></p>';
		} else if (has_capability ( 'mod/emarking:receivenotification', $context, $user )) {
			$thismessagehtml .= '<p><a href="' . $CFG->wwwroot . '/mod/emarking/print/exams.php?course=' . $course->id . '">' . get_string ( 'printorders', 'mod_emarking' ) . ' ' . $course->fullname . '</a></p>';
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

