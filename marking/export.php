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
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('NO_OUTPUT_BUFFERING', true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/marking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/print/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/marking/forms/export_form.php');
global $DB, $CFG, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$emarkingdst = optional_param_array('emarkingdst', array(), PARAM_INT);
$rubricoverride = optional_param('override', false, PARAM_BOOL);
$markersoverride = optional_param('overridemarkers', false, PARAM_BOOL);
// Validate user is logged in and is not guest.
require_login($course->id);
if (isguestuser()) {
    die();
}
$url = new moodle_url('/mod/emarking/marking/export.php', array(
    'id' => $cm->id));
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
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "export");
list($gradingmanager, $gradingmethod, $definition, $rubriccontroller) = emarking_validate_rubric($context);
if (count($emarkingdst) > 0) {
    $destinationemarkings = array();
    foreach ($emarkingdst as $destinationid) {
        if ($emarkingdestination = $DB->get_record('emarking', array(
            'id' => $destinationid))) {
            $destinationemarkings [] = $emarkingdestination;
        } else {
            print_error("Invalid emarking destination");
        }
    }
    foreach ($destinationemarkings as $emarkingdestination) {
        echo $OUTPUT->box_start();
        echo $OUTPUT->heading($emarkingdestination->name, 4);
        $result = emarking_copy_settings($emarking, $emarkingdestination, $rubricoverride, $markersoverride);
        if ($result) {
            echo $OUTPUT->notification(get_string("transactionsuccessfull", "mod_emarking"), 'notifysuccess');
        } else {
            echo $OUTPUT->notification(get_string("exportsettingsfailed", "mod_emarking"), 'notifyproblem');
        }
        echo $OUTPUT->box_end();
    }
    $continueurl = new moodle_url('/mod/emarking/marking/export.php', array(
        'id' => $cm->id));
    echo $OUTPUT->continue_button($continueurl);
    echo $OUTPUT->footer();
    die();
}
$form = new emarking_export_form(null, array(
    'id' => $cm->id,
    'course' => $course), 'post');
if (!$form->get_data()) {
    $form->display();
}
echo $OUTPUT->footer();