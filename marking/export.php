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
 * This page processes a zip file containing scanned answers from students
 * that were already identified using the emarking desktop tool
 * 
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Jorge Villalon <villalon@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('NO_OUTPUT_BUFFERING', true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot . '/mod/emarking/marking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/print/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');

global $DB, $CFG, $USER;

$cmid = required_param('id', PARAM_INT);
$emarkingdst = optional_param('emarkingdst', 0, PARAM_INT);

// Validate course module
if(!$cm = get_coursemodule_from_id('emarking', $cmid)) {
	print_error(get_string('invalidcoursemodule', 'mod_emarking'));
}

// Validate emarking activity
if(!$emarking = $DB->get_record('emarking', array('id' => $cm->instance))) {
	print_error(get_string('invalidemarkingid', 'mod_emarking').':' . $emarkingid);
}

// Validate course
if(!$course = $DB->get_record('course', array('id' => $emarking->course))) {
	print_error(get_string('invalidcourseid', 'mod_emarking').': ' . $emarking->course);
}

// Get context for module
$context = context_module::instance($cm->id);

// Validate user is logged in and is not guest
require_login($course->id);
if (isguestuser()) {
	die();
}

$url = new moodle_url('/mod/emarking/marking/export.php',  array('id'=>$cm->id));

$PAGE->set_pagelayout('incourse');
$PAGE->set_popup_notification_allowed(false);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('publishtitle', 'mod_emarking'));

echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);

if($emarkingdst) {
    if($emarkingdestination = $DB->get_record('emarking', array('id'=>$emarkingdst))) {
    $result = emarking_copy_settings($emarking, $emarkingdestination);
    if($result) {
        echo $OUTPUT->notification(get_string("transactionsuccessfull", "mod_emarking"), 'notifysuccess');
    } else {
        echo $OUTPUT->notification(get_string("exportsettingsfailed", "mod_emarking"), 'notifyproblem');
    }
    $continue_url = new moodle_url('/mod/emarking/view.php', array('id'=>$cm->id));
    echo $OUTPUT->continue_button($continue_url);
    echo $OUTPUT->footer();
    die();
    } else {
        print_error("Invalid emarking destination");
    }
}

$parallelcourses = emarking_get_parallel_courses($course);

$parallelsids = array();
foreach($parallelcourses as $parallelcourse) {
    $parallelsids[] = $parallelcourse->id;
}
$parallelsids = implode(",", $parallelsids);

$parallelemarkings = $DB->get_records_sql("
    SELECT e.*
    FROM {emarking} AS e WHERE course IN ($parallelsids)");

foreach($parallelemarkings as $emarkingdest) {
    $copyurl = new moodle_url("/mod/emarking/marking/export.php", 
        array("id"=>$cm->id, "emarkingdst"=>$emarkingdest->id));
    echo $OUTPUT->single_button($copyurl, $emarkingdest->name, 'get');    
}

$continue_url = new moodle_url('/mod/emarking/view.php', array('id'=>$cm->id));
echo $OUTPUT->continue_button($continue_url);
echo $OUTPUT->footer();
