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
$action = required_param ( 'action', PARAM_ALPHA);

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

$url = new moodle_url('/mod/emarking/marking/addmarkers.php', array('id'=>$cmid, 'action'=>$action));
$cancelurl = new moodle_url('/mod/emarking/marking/markers.php',array('id'=>$cmid));

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

// Get rubric instance
list($gradingmanager, $gradingmethod) = emarking_validate_rubric($context);

// As we have a rubric we can get the controller
$rubriccontroller = $gradingmanager->get_controller($gradingmethod);
if(!$rubriccontroller instanceof gradingform_rubric_controller) {
	print_error(get_string('invalidrubric', 'mod_emarking'));
}

$definition = $rubriccontroller->get_definition();
$mform = new emarking_markers_form(null, 
		array('context'=>$context, 'criteria'=>$definition->rubric_criteria, 'id'=>$cmid, 'emarking'=>$emarking, "action"=>$action));

if($mform->is_cancelled()) {
    redirect($cancelurl);
    die();
}

echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);

echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "markers" );

if($mform->get_data()) {

    $toinsert = array();
    foreach($mform->get_data()->data as $data) {
        foreach($mform->get_data()->criteria as $criterion) {
            $association = new stdClass();
            $association->data = $data;
            $association->criterion = $criterion;
            $toinsert[] = $association;
        }
    }
    
    if($action === "addmarker") {
        $blocknum = $DB->get_field_sql("SELECT max(block) FROM {emarking_marker_criterion} WHERE emarking = ?", array($emarking->id));
    } else {
        $blocknum = $DB->get_field_sql("SELECT max(block) FROM {emarking_page_criterion} WHERE emarking = ?", array($emarking->id));
    }

    if(!$blocknum) {
        $blocknum = 1;
    } else {
        $blocknum++;;
    }
    
    foreach($toinsert as $data)  {
        if($action === "addmarker") {
            $association = $DB->get_record("emarking_marker_criterion", array("emarking"=>$emarking->id, "criterion"=>$data->criterion, "marker"=>$data->data));
            $tablename = "emarking_marker_criterion";
        } else {
            $association = $DB->get_record("emarking_page_criterion", array("emarking"=>$emarking->id, "criterion"=>$data->criterion, "page"=>$data->data));
            $tablename = "emarking_page_criterion";
        }
        if($association) {
            $association->block = $blocknum;
            $DB->update_record($tablename, $association);
        } else {
            $association = new stdClass();
            $association->emarking = $emarking->id;
            $association->criterion = $data->criterion;
            $association->block = $blocknum;
            $association->timecreated = time();
            $association->timemodified = time();
            
            if($action === "addmarker") {
                $association->marker = $data->data;
            } else {
                $association->page = $data->data;                
            }
            
            $association->id = $DB->insert_record($tablename, $association);
        }
    }
	echo $OUTPUT->notification(get_string('saved', 'mod_emarking'),'notifysuccess');
}


$mform->display();


echo $OUTPUT->footer();
