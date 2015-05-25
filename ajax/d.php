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
 * @package mod
 * @subpackage emarking
 * @copyright 2012 Jorge Villalón {@link http://www.uai.cl}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
define('NO_DEBUG_DISPLAY', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once $CFG->dirroot.'/mod/emarking/locallib.php';
require_once $CFG->libdir.'/accesslib.php';

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$action = required_param('action', PARAM_ALPHA);
$username = required_param('username', PARAM_ALPHANUMEXT);
$password = required_param('password', PARAM_RAW_TRIMMED);
$courseid = optional_param('course', -1, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);

if(!$user = authenticate_user_login($username, $password))
	emarking_json_error('Invalid username or password');

// This is the correct way to fill up $USER variable
complete_user_login($user);

if($action === 'students') {
	if(!$course = $DB->get_record('course', array('id'=>$courseid)))
		emarking_json_error('Invalid course id');

	$rs = emarking_get_students_for_printing($course->id);
	$results = array();
	foreach($rs as $r) {
		$results[] = $r;
	}
	emarking_json_resultset($results);
} else if($action === 'courses') {
    
	$rs = get_user_capability_course($capability, $user->id);
	$results = array();
	foreach($rs as $r) {
		$results[] = $r;
	}
	emarking_json_resultset($results);
} else if($action === 'activities') {
	if(!$course = $DB->get_record('course', array('id'=>$courseid)))
		emarking_json_error('Invalid course id');
	
	$rs = get_coursemodules_in_course('emarking', $course->id);
	$results = array();
	foreach($rs as $r) {
		$results[] = $r;
	}
	emarking_json_resultset($results);
} else if($action === 'courseinfo') {
	if(!$course = $DB->get_record('course', array('id'=>$courseid)))
		emarking_json_error('Invalid course id');

	$results = array();
	$results[] = $course;
	emarking_json_resultset($results);
} else if($action === 'coursesearch') {
	$q = required_param('q', PARAM_TEXT);
	$results = array();
	if(strlen($q)>2)
		$results = $DB->get_records_select('course', "fullname LIKE ?", array('%'.$q.'%'),'id DESC','id, shortname, fullname', 0, 100);
	emarking_json_resultset($results);
} else if($action === 'upload') {
	$emarkingid = optional_param('nmid', -666, PARAM_INT);
	$emarkingname = optional_param('name', 'Uploaded emarking', PARAM_RAW_TRIMMED);
	
	require_once($CFG->dirroot . "/course/lib.php");
	require_once($CFG->dirroot . "/mod/emarking/lib.php");
	require_once($CFG->dirroot . "/mod/emarking/mod_form.php");
	
	if(!$course = $DB->get_record('course', array('id'=>$courseid)))
		emarking_json_error('Invalid course id');
	
	if($emarkingid == -666) {
		$emarking = new stdClass();
		$emarking->name = $emarkingname;
		$emarking->intro = "Automatically uploaded emarking from file ";
		$emarking->anonymous = 2;
		$emarking->custommarks = '';
		$emarking->course = $course->id;
		$emarking->grade = 7;
		$emarking->grademin = 1;
		$emarking->id = emarking_add_instance($emarking);
		
		$emarkingmod = $DB->get_record('modules', array('name'=>'emarking'));
		
		// Add coursemodule
		$mod = new stdClass();
		$mod->course = $emarking->course;
		$mod->module = $emarkingmod->id;
		$mod->instance = $emarking->id;
		$mod->section = 0;
		$mod->visible = 0;     // Hide the forum
		$mod->visibleold = 0;  // Hide the forum
		$mod->groupmode = 0;
		$mod->grade = 100;
		
		if (!$cmid = add_course_module($mod)) {
			emarking_json_error('cannotcreateinstanceforteacher');
		}
		$sectionid = course_add_cm_to_section($mod->course, $cmid, 0);
		
		$emarkingid = $emarking->id;
	}
	
	if(!$emarking = $DB->get_record('emarking', array('id'=>$emarkingid)))
		emarking_json_error('Invalid emarking id');
	
	if(!$cm = get_coursemodule_from_instance('emarking', $emarking->id, $course->id))
		emarking_json_error('Invalid course module');
	
	if(count($_FILES) < 1 || !is_uploaded_file($_FILES['uploadedfile']['tmp_name']))
		emarking_json_error('You must upload a file');
	
	$extension = strtolower(substr($_FILES['uploadedfile']['name'], strrpos($_FILES['uploadedfile']['name'], "."), strlen($_FILES['uploadedfile']['name'])-strrpos($_FILES['uploadedfile']['name'], ".")));
	
	if($extension !== '.zip')
		emarking_json_error('Invalid extension file '.$extension.' in '. $_FILES['uploadedfile']['tmp_name'] .'. Only zip files are allowed');
	
	// Setup de directorios temporales
	$tempdir = emarking_get_temp_dir_path($emarking->id);
	
	emarking_initialize_directory($tempdir, true);
	
	$fileid = $tempdir . "/" . random_string() . ".zip";
	
	if(!move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $fileid))
		emarking_json_error("Couldn't move file " . $_FILES['uploadedfile']['tmp_name'] . " to " . $fileid);
	
	
	emarking_upload_answers($emarking, $fileid, $course, $cm);
	
	$output = array($emarking-id => $emarking);
	emarking_json_resultset($output);
}
emarking_json_error('Invalid action');
?>