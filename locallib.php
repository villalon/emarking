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

function emarking_get_icon_status($status) {
    global $OUTPUT;
    
    switch($status) {
    	case EMARKING_STATUS_MISSING:
    	    return $OUTPUT->pix_icon ( 'i/warning', emarking_get_string_for_status($status));
        case EMARKING_STATUS_ABSENT:
    	    return $OUTPUT->pix_icon ( 't/block', emarking_get_string_for_status($status));
    	case EMARKING_STATUS_SUBMITTED:
    	    return $OUTPUT->pix_icon ( 'i/user', emarking_get_string_for_status($status));
    	case EMARKING_STATUS_GRADING:
    	    return $OUTPUT->pix_icon ( 'i/grade_partiallycorrect', emarking_get_string_for_status($status));
    	case EMARKING_STATUS_RESPONDED:
    	    return $OUTPUT->pix_icon ( 'i/grade_correct', emarking_get_string_for_status($status));
    	case EMARKING_STATUS_REGRADING:
    	    return $OUTPUT->pix_icon ( 'i/flagged', emarking_get_string_for_status($status));
    	case EMARKING_STATUS_REGRADING_RESPONDED:
    	    return $OUTPUT->pix_icon ( 'i/unflagged', emarking_get_string_for_status($status));
    	case EMARKING_STATUS_ACCEPTED:
    	    return $OUTPUT->pix_icon ( 't/locked', emarking_get_string_for_status($status));
    }
}

function get_string_type($type) {
	switch ($type) {
		case EMARKING_TYPE_NORMAL :
			return get_string ( 'type_normal', 'mod_emarking' );
		case EMARKING_TYPE_MARKER_TRAINING :
			return get_string ( 'type_markers_training', 'mod_emarking' );
		case EMARKING_TYPE_STUDENT_TRAINING :
			return get_string ( 'type_student_training', 'mod_emarking' );
		case EMARKING_TYPE_PEER_REVIEW:
			return get_string ( 'type_peer_review', 'mod_emarking' );
		default :
			return 'INVALID STATUS';
	}
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
function emarking_tabs($context, $cm, $emarking) {
	global $CFG;
	global $USER;
	
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
		if (has_capability ( 'mod/emarking:regrade', $context )
			&& $emarking->type == EMARKING_TYPE_NORMAL)
			$gradetab->subtree [] = new tabobject ( "regrades", $CFG->wwwroot . "/mod/emarking/marking/regraderequests.php?cmid={$cm->id}", get_string ( "regrades", 'mod_emarking' ) );
		if (has_capability ( 'mod/emarking:assignmarkers', $context )
			&& $emarking->type == EMARKING_TYPE_NORMAL)
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
function emarking_get_parallel_courses($course, $regex) {
	global $CFG, $DB;
	
	if ($regex && preg_match_all ( '/' . $regex . '/', $course->shortname, $regs )) {
			$coursecode = $regs [1] [0];
			
			$term = $regs [2] [0];
			$year = $regs [3] [0];
			
			$seccionesparalelas = $DB->get_records_select ( 'course', "
				shortname like '%$coursecode%-%-$term-$year'
				and id != $course->id", null, 'shortname ASC', '*' );
			
			return $seccionesparalelas;
		
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

