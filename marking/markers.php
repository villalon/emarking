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
 * @copyright 2014 Jorge Villalón
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once($CFG->dirroot . "/grade/grading/form/rubric/renderer.php");
require_once("forms/markers_form.php");
require_once("forms/pages_form.php");
global $DB, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// Obtain parameter from URL.
$criterionid = optional_param('criterion', 0, PARAM_INT);
$markerid = optional_param('marker', 0, PARAM_INT);
$action = optional_param('action', 'view', PARAM_ALPHA);
if (! $exam = $DB->get_record('emarking_exams', array(
    'emarking' => $emarking->id))) {
    print_error(get_string('invalidid', 'mod_emarking') . " id: $emarking->id");
}
if ($criterionid > 0) {
    $criterion = $DB->get_record('gradingform_rubric_criteria', array(
        'id' => $criterionid));
    if ($criterion == null) {
        print_error("Invalid criterion id");
    }
}
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
$PAGE->navbar->add(get_string('markers', 'mod_emarking'));
// Verify capability for security issues.
if (! has_capability('mod/emarking:assignmarkers', $context)) {
    $item = array(
        'context' => $context,
        'objectid' => $emarking->id);
    // Add to Moodle log so some auditing can be done.
    \mod_emarking\event\unauthorizedaccess_attempted::create($item)->trigger();
    print_error(get_string('invalidaccess', 'mod_emarking'));
}
echo $OUTPUT->header();
// Heading and tabs if we are within a course module.
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "markers");
// Get rubric instance.
list($gradingmanager, $gradingmethod, $definition) = emarking_validate_rubric($context);
$mformmarkers = new emarking_markers_form(null,
        array(
            'context' => $context,
            'criteria' => $definition->rubric_criteria,
            'id' => $cm->id,
            'totalpages' => $exam->totalpages,
            'emarking' => $emarking,
            "action" => "addmarkers"));
if ($mformmarkers->get_data()) {
    $newmarkers = process_mform($mformmarkers, "addmarkers", $emarking);
}
if ($action === 'deletemarkers') {
    $DB->delete_records('emarking_marker_criterion',
            array(
                'emarking' => $emarking->id,
                'criterion' => $criterion->id));
    echo $OUTPUT->notification(get_string("transactionsuccessfull", "mod_emarking"), 'notifysuccess');
} else if ($action === 'deletesinglemarker') {
    $DB->delete_records('emarking_marker_criterion',
            array(
                'emarking' => $emarking->id,
                'marker' => $markerid,
                'criterion' => $criterion->id));
    echo $OUTPUT->notification(get_string("transactionsuccessfull", "mod_emarking"), 'notifysuccess');
}
$nummarkerscriteria = $DB->count_records("emarking_marker_criterion", array(
    "emarking" => $emarking->id));
$markercriteria = $DB->get_recordset_sql(
        "
        SELECT
        id,
        description,
        GROUP_CONCAT(uid) AS markers,
        sortorder
    FROM (
    SELECT
        c.id,
        c.description,
        c.sortorder,
        u.id as uid
    FROM {gradingform_rubric_criteria} c
    LEFT JOIN {emarking_marker_criterion} mc ON (c.definitionid = :definition AND mc.emarking = :emarking AND c.id = mc.criterion)
    LEFT JOIN {user} u ON (mc.marker = u.id)
    WHERE c.definitionid = :definition2
    ORDER BY c.id ASC, u.lastname ASC) T
    GROUP BY id",
        array(
            "definition" => $definition->id,
            "definition2" => $definition->id,
            "emarking" => $emarking->id));
$data = array();
foreach ($markercriteria as $d) {
    $urldelete = new moodle_url('/mod/emarking/marking/markers.php',
            array(
                'id' => $cm->id,
                'criterion' => $d->id,
                'action' => 'deletemarkers'));
    $outcomeshtml = "";
    if ($d->markers) {
        $markers = explode(",", $d->markers);
        foreach ($markers as $marker) {
            $u = $DB->get_record("user", array(
                "id" => $marker));
            $urldeletesingle = new moodle_url('/mod/emarking/marking/markers.php',
                    array(
                        'id' => $cm->id,
                        'criterion' => $d->id,
                        'marker' => $marker,
                        'action' => 'deletesinglemarker'));
            $outcomeshtml .= html_writer::div(
                    $OUTPUT->user_picture($u) . html_writer::link($urldeletesingle, "X",
                            array(
                                "class" => "deletewidget")), 'widget');
        }
        $outcomeshtml .= $OUTPUT->action_link($urldelete, get_string("deleterow", "mod_emarking"), null,
                array(
                    "class" => "rowactions"));
    }
    $data [] = array(
        $d->description,
        $outcomeshtml);
}
$table = new html_table();
$table->head = array(
    get_string("criterion", "mod_emarking"),
    get_string("assignedmarkers", "mod_emarking"));
$table->colclasses = array(
    null,
    null);
$table->data = $data;
$numpagescriteria = $DB->count_records("emarking_page_criterion", array(
    "emarking" => $emarking->id));
echo $OUTPUT->heading(get_string("currentstatus", "mod_emarking"), 4);
if ($nummarkerscriteria == 0 && $numpagescriteria == 0) {
    echo $OUTPUT->box(get_string("markerscanseewholerubric", "mod_emarking"));
    echo $OUTPUT->box(get_string("markerscanseeallpages", "mod_emarking"));
} else if ($nummarkerscriteria > 0 && $numpagescriteria == 0) {
    echo $OUTPUT->box(get_string("markerscanseeselectedcriteria", "mod_emarking"));
    echo $OUTPUT->box(get_string("markerscanseeallpages", "mod_emarking"));
} else if ($nummarkerscriteria == 0 && $numpagescriteria > 0) {
    echo $OUTPUT->notification(get_string("markerscanseenothing", "mod_emarking"), "notifyproblem");
} else {
    echo $OUTPUT->box(get_string("markerscanseeselectedcriteria", "mod_emarking"));
    echo $OUTPUT->box(get_string("markerscanseepageswithcriteria", "mod_emarking"));
}
echo html_writer::table($table);
$mformmarkers->display();
echo $OUTPUT->footer();
function process_mform($mform, $action, $emarking) {
    global $DB, $OUTPUT;
    if ($mform->get_data()) {
        if ($action !== $mform->get_data()->action) {
            return;
        }
        if ($action === "addmarkers") {
            $datalist = $mform->get_data()->datamarkers;
        } else {
            $datalist = $mform->get_data()->datapages;
        }
        $toinsert = array();
        foreach ($datalist as $data) {
            if ($action === "addmarkers") {
                $criteria = $mform->get_data()->criteriamarkers;
            } else {
                $criteria = $mform->get_data()->criteriapages;
            }
            foreach ($criteria as $criterion) {
                $association = new stdClass();
                $association->data = $data;
                $association->criterion = $criterion;
                $toinsert [] = $association;
            }
        }
        if ($action === "addmarkers") {
            $blocknum = $DB->get_field_sql("SELECT max(block) FROM {emarking_marker_criterion} WHERE emarking = ?",
                    array(
                        $emarking->id));
        } else {
            $blocknum = $DB->get_field_sql("SELECT max(block) FROM {emarking_page_criterion} WHERE emarking = ?",
                    array(
                        $emarking->id));
        }
        if (! $blocknum) {
            $blocknum = 1;
        } else {
            $blocknum ++;
        }
        foreach ($toinsert as $data) {
            if ($action === "addmarkers") {
                $association = $DB->get_record("emarking_marker_criterion",
                        array(
                            "emarking" => $emarking->id,
                            "criterion" => $data->criterion,
                            "marker" => $data->data));
                $tablename = "emarking_marker_criterion";
            } else {
                $association = $DB->get_record("emarking_page_criterion",
                        array(
                            "emarking" => $emarking->id,
                            "criterion" => $data->criterion,
                            "page" => $data->data));
                $tablename = "emarking_page_criterion";
            }
            if ($association) {
                $association->block = $blocknum;
                $DB->update_record($tablename, $association);
            } else {
                $association = new stdClass();
                $association->emarking = $emarking->id;
                $association->criterion = $data->criterion;
                $association->block = $blocknum;
                $association->timecreated = time();
                $association->timemodified = time();
                if ($action === "addmarkers") {
                    $association->marker = $data->data;
                } else {
                    $association->page = $data->data;
                }
                $association->id = $DB->insert_record($tablename, $association);
            }
        }
        echo $OUTPUT->notification(get_string('saved', 'mod_emarking'), 'notifysuccess');
    }
}