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
 * @copyright 2014 Jorge Villalón {@link http://www.uai.cl}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
define('NO_DEBUG_DISPLAY', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once("$CFG->dirroot/grade/grading/lib.php");
require_once $CFG->dirroot.'/grade/lib.php';
require_once("$CFG->dirroot/grade/grading/form/rubric/lib.php");
require_once("$CFG->dirroot/lib/filestorage/file_storage.php");
require_once($CFG->dirroot."/mod/emarking/locallib.php");

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

// Required and optional params for ajax interaction in emarking
$action = required_param('action', PARAM_ALPHA);
$cmid = required_param('cmid', PARAM_INTEGER);

if(!$cm = get_coursemodule_from_id('emarking', $cmid)) {
	print_error ( get_string('invalidid','mod_emarking' ) . " id: $cmid" );
}

if(!$emarking = $DB->get_record('emarking', array('id'=>$cm->instance))) {
	print_error ( get_string('invalidid','mod_emarking' ) . " id: $cm->instance" );
}

// Validate that the parameter corresponds to a course
if (! $course = $DB->get_record ( 'course', array ('id' => $emarking->course))) {
	print_error ( get_string('invalidcourseid','mod_emarking' ) . " id: $emarking->course" );
}

// Verify that user is logged in, otherwise return error
if(!isloggedin())
	emarking_json_error('User is not logged in', array('url'=>$CFG->wwwroot . '/login/index.php'));

// Create the context within the course module
$context = context_module::instance($cm->id);

$usercangrade = has_capability('mod/emarking:grade', $context);
$usercanregrade = has_capability('mod/emarking:regrade', $context);
$issupervisor = has_capability('mod/emarking:supervisegrading', $context) || is_siteadmin($USER);

// Ping action for fast validation of user logged in and communication with server
if($action === 'ping') {	
	emarking_json_array(array(
			'user'=>$USER->id
			));
}

// Now require login so full security is checked
require_login($course->id, false, $cm);

$url = new moodle_url('/mod/emarking/ajax/m.php', array('cmid'=>$cmid, 'action'=>$action));

// Switch according to action
switch($action) {

	case 'markersconfig':
		// Add to Moodle log so some auditing can be done
		$item = array (
				'context' => context_module::instance ( $cm->id ),
				'objectid' => $cm->id,
		);
		// Add to Moodle log so some auditing can be done
		\mod_emarking\event\markersconfig_called::create ( $item )->trigger ();
		
		$module = new stdClass();
		include "qry/qryGetMarkersConfiguration.php";
		emarking_json_array($results);
		break;

	case 'addmarker':
	case 'addpage':
		include 'act/actAddCriterion2Marker.php';
		emarking_json_array($output);
		break;
		
	case 'delmarker':
	case 'delpage':
		include 'act/actDeleteCriterion2Marker.php';
		emarking_json_array($output);
		break;
	default:
		emarking_json_error('Invalid action!' . $action);
}
?>