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
 * @copyright  2014 Jorge Villalón
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__ )))) . '/config.php');
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/grade/grading/form/rubric/renderer.php");
require_once ("forms/markers_form.php");

global $DB, $USER;

// Obtain parameter from URL
$cmid = required_param ( 'id', PARAM_INT );

if(!$cm = get_coursemodule_from_id('emarking', $cmid)) {
	print_error ( get_string('invalidid','mod_emarking' ) . " id: $cmid" );
}

if(!$emarking = $DB->get_record('emarking', array('id'=>$cm->instance))) {
	print_error ( get_string('invalidid','mod_emarking' ) . " id: $cmid" );
}

// Validate that the parameter corresponds to a course
if (! $course = $DB->get_record ( 'course', array ('id' => $emarking->course))) {
	print_error ( get_string('invalidcourseid','mod_emarking' ) . " id: $courseid" );
}

$context = context_module::instance ( $cm->id );

$url = new moodle_url('/mod/emarking/marking/markers.php',array('id'=>$cmid));

// First check that the user is logged in
require_login($course->id);

if (isguestuser ()) {
	die ();
}

$PAGE->set_context ( $context );
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_url ( $url );
$PAGE->set_pagelayout ( 'incourse' );
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('markers','mod_emarking'));

// Verify capability for security issues
if (! has_capability ( 'mod/emarking:assignmarkers', $context )) {
	$item = array (
			'context' => context_module::instance ( $cm->id ),
			'objectid' => $cm->id,
	);
	// Add to Moodle log so some auditing can be done
	\mod_emarking\event\markers_assigned::create ( $item )->trigger ();
	print_error ( get_string('invalidaccess','mod_emarking' ) );
}

echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);

echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "markers" );

// Get rubric instance
list($gradingmanager, $gradingmethod) = emarking_validate_rubric($context);

// As we have a rubric we can get the controller
$rubriccontroller = $gradingmanager->get_controller($gradingmethod);
if(!$rubriccontroller instanceof gradingform_rubric_controller) {
	print_error(get_string('invalidrubric', 'mod_emarking'));
}

$definition = $rubriccontroller->get_definition();
$mform = new emarking_markers_form(null, 
		array('context'=>$context, 'criteria'=>$definition->rubric_criteria, 'id'=>$cmid, 'emarking'=>$emarking));

if($mform->get_data()) {
	$DB->delete_records('emarking_marker_criterion', array('emarking'=>$emarking->id));
	
	foreach($mform->get_data() as $key => $value) {
		$parts = explode("-", $key);
		if(count($parts) != 3 || $parts[0] !== 'assign')
			continue;
		$criterionid = intval($parts[1]);
		$markerid = intval($parts[2]);
		
		$markercriterion = new stdClass();
		$markercriterion->emarking = $emarking->id;
		$markercriterion->marker = $markerid;
		$markercriterion->criterion = $criterionid;
		$markercriterion->timecreated = time();
		$markercriterion->timemodified = time();
		
		$DB->insert_record('emarking_marker_criterion', $markercriterion);
	}

	$DB->delete_records('emarking_page_criterion', array('emarking'=>$emarking->id));
	
	foreach($mform->get_data() as $key => $value) {
		$parts = explode("-", $key);
		if(count($parts) != 3 || $parts[0] !== 'page')
			continue;
		$criterionid = intval($parts[1]);
		$pagenumber = intval($parts[2]);
		
		$pagecriterion = new stdClass();
		$pagecriterion->emarking = $emarking->id;
		$pagecriterion->page = $pagenumber;
		$pagecriterion->criterion = $criterionid;
		$pagecriterion->timecreated = time();
		$pagecriterion->timemodified = time();
		
		$DB->insert_record('emarking_page_criterion', $pagecriterion);
	}

	echo $OUTPUT->notification(get_string('saved', 'mod_emarking'),'notifysuccess');
}

$markersdata = array();

$markerscriteria = $DB->get_records('emarking_marker_criterion', array('emarking'=>$emarking->id));
foreach($markerscriteria as $markercriterion) {
	$markersdata['assign-'.$markercriterion->criterion.'-'.$markercriterion->marker] = "1";
}

$pagescriteria = $DB->get_records('emarking_page_criterion', array('emarking'=>$emarking->id));
foreach($pagescriteria as $pagecriterion) {
	$markersdata['page-'.$pagecriterion->criterion.'-'.$pagecriterion->page] = "1";
}

$mform->set_data($markersdata);
$mform->display();


echo $OUTPUT->footer();
