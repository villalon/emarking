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
 * @package mod
 * @subpackage emarking
 * @copyright 2014-2015 Jorge Villalón
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once($CFG->dirroot . "/grade/grading/form/rubric/renderer.php");
require_once("forms/osm_form.php");
global $DB, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$url = new moodle_url('/mod/emarking/marking/markers.php', array(
    'id' => $cm->id));
// First check that the user is logged in.
require_login($course->id);
if (isguestuser()) {
    die();
}
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('settings', 'mod_emarking'));
// Verify capability for security issues.
if (! has_capability('mod/emarking:supervisegrading', $context)) {
    $item = array(
        'context' => $context,
        'objectid' => $emarking->id);
    // Add to Moodle log so some auditing can be done.
    \mod_emarking\event\unauthorizedaccess_attempted::create($item)->trigger();
    print_error(get_string('noneditingteacherconfiguration', 'mod_emarking'));
}
echo $OUTPUT->header();
// Heading and tabs if we are within a course module.
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "osmsettings");
$mform = new emarking_osm_form(null,
        array(
            'context' => $context,
            'id' => $cm->id,
            'emarking' => $emarking,
            "action" => "addpages"));
// Data was submitted.
if ($mform->get_data()) {
    $emarking->peervisibility = $mform->get_data()->peervisibility;
    $emarking->anonymous = $mform->get_data()->anonymous;
    $emarking->justiceperception = $mform->get_data()->justiceperception;
    $emarking->linkrubric = isset($mform->get_data()->linkrubric) ? 1 : 0;
    $emarking->collaborativefeatures = isset($mform->get_data()->collaborativefeatures) ? 1 : 0;
    $emarking->custommarks = $mform->get_data()->custommarks;
    $emarking->qualitycontrol = isset($mform->get_data()->qualitycontrol) ? 1 : 0;
    $emarking->enableduedate = isset($mform->get_data()->enableduedate) ? 1 : 0;
    $emarking->markingduedate = $mform->get_data()->markingduedate;
    $emarking->regraderestrictdates = isset($mform->get_data()->regraderestrictdates) ? 1 : 0;
    $emarking->regradesopendate = $mform->get_data()->regradesopendate;
    $emarking->regradesclosedate = $mform->get_data()->regradesclosedate;
    if ($DB->update_record("emarking", $emarking)) {
        echo $OUTPUT->notification(get_string("transactionsuccessfull", "mod_emarking"), "notifysuccess");
    } else {
        echo $OUTPUT->notification(get_string("fatalerror", "mod_emarking"), "notifyproblem");
    }
}
// CAREFUL: This is a hack so the set_data method doesn't use the activity id when the cmid
// must be used.
$emarking->id = $cm->id; // TODO: Remove hack if possible.
$mform->set_data($emarking);
$mform->display();
echo $OUTPUT->footer();