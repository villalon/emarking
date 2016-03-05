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
 * @copyright 2016 Jorge Villalón
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once($CFG->dirroot . "/grade/grading/form/rubric/renderer.php");
require_once($CFG->libdir . "/gradelib.php");
require_once("forms/outcomes_form.php");
require_once("forms/scalelevels_form.php");
global $DB, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// Obtain parameter from URL.
$criterionid = optional_param('criterion', 0, PARAM_INT);
$action = optional_param('action', 'view', PARAM_ALPHA);
$outcome = optional_param('outcome', 0, PARAM_INT);
if ($criterionid > 0) {
    $criterion = $DB->get_record('gradingform_rubric_criteria', array(
        'id' => $criterionid));
    if ($criterion == null) {
        print_error("Invalid criterion id");
    }
}
$url = new moodle_url('/mod/emarking/marking/outcomes.php', array(
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
$PAGE->navbar->add(get_string('outcomes', 'grades'));
// Verify capability for security issues.
if (! has_capability('mod/emarking:assignmarkers', $context)) {
    print_error(get_string('invalidaccess', 'mod_emarking'));
}
echo $OUTPUT->header();
// Heading and tabs if we are within a course module.
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "outcomes");
$courseoutcomes = grade_outcome::fetch_all_available($course->id);
if (count($courseoutcomes) == 0) {
    echo $OUTPUT->notification(get_string("coursehasnooutcomes", "mod_emarking"), 'notifyproblem');
    $outcomesurl = new moodle_url("/grade/edit/outcome/course.php", array(
        "id" => $course->id));
    echo $OUTPUT->single_button($outcomesurl, get_string("gotooutcomessettings", "mod_emarking"));
    echo $OUTPUT->footer();
    die();
}
$sqloutcomes = "
                SELECT o.*,
                    s.name as scalename,
                    s.scale as scalelevels,
                    s.description as scaledescription
                FROM {grade_outcomes} o
                INNER JOIN {grade_items} gi ON (gi.courseid = :courseid
                        AND gi.itemtype = 'mod' AND gi.itemmodule = 'emarking' AND gi.iteminstance = :emarkingid
                        AND gi.gradetype = 2 AND gi.outcomeid = o.id)
                INNER JOIN {grade_outcomes_courses} oc ON (oc.courseid = gi.courseid AND o.id = oc.outcomeid)
                INNER JOIN {scale} s ON (o.scaleid = s.id)";
$emarkingoutcomes = $DB->get_records_sql($sqloutcomes, array(
    'courseid' => $course->id,
    'emarkingid' => $emarking->id));
if (count($emarkingoutcomes) == 0) {
    echo $OUTPUT->notification(get_string("emarkinghasnooutcomes", "mod_emarking"), 'notifyproblem');
    $outcomesurl = new moodle_url("/course/modedit.php", array(
        "update" => $cm->id,
        "return" => 1));
    echo $OUTPUT->single_button($outcomesurl, get_string("gotoemarkingsettings", "mod_emarking"), 'get');
    echo $OUTPUT->footer();
    die();
}
// Get rubric instance.
list($gradingmanager, $gradingmethod, $definition) = emarking_validate_rubric($context, true);
$mformoutcomes = new emarking_outcomes_form(null,
        array(
            'context' => $context,
            'criteria' => $definition->rubric_criteria,
            'id' => $cm->id,
            'emarking' => $emarking,
            'action' => 'addoutcomes',
            'outcomes' => $emarkingoutcomes));
if ($mformoutcomes->get_data()) {
    $newmarkers = process_mform($mformoutcomes, "addoutcomes", $emarking);
}
if ($action === 'deleteoutcomes') {
    $DB->delete_records('emarking_outcomes_criteria',
            array(
                'emarking' => $emarking->id,
                'criterion' => $criterion->id));
    echo $OUTPUT->notification(get_string("transactionsuccessfull", "mod_emarking"), 'notifysuccess');
} else if ($action === 'deletesingleoutcome') {
    $DB->delete_records('emarking_outcomes_criteria',
            array(
                'emarking' => $emarking->id,
                'criterion' => $criterion->id,
                'outcome' => $outcome));
    echo $OUTPUT->notification(get_string("transactionsuccessfull", "mod_emarking"), 'notifysuccess');
}
$numoutcomescriteria = $DB->count_records("emarking_outcomes_criteria", array(
    "emarking" => $emarking->id));
$outcomescriteria = $DB->get_recordset_sql(
        "
        SELECT
        id,
        description,
        GROUP_CONCAT(oid) AS outcomes,
        sortorder
    FROM (
    SELECT
        c.id,
        c.description,
        c.sortorder,
        o.id as oid
    FROM {gradingform_rubric_criteria} c
    LEFT JOIN {emarking_outcomes_criteria} mc ON (c.definitionid = :definition AND mc.emarking = :emarking AND c.id = mc.criterion)
    LEFT JOIN {grade_outcomes} o ON (mc.outcome = o.id)
    WHERE c.definitionid = :definition2
    ORDER BY c.id ASC, o.fullname ASC) as T
    GROUP BY id",
        array(
            "definition" => $definition->id,
            "definition2" => $definition->id,
            "emarking" => $emarking->id));
$data = array();
foreach ($outcomescriteria as $d) {
    $urldelete = new moodle_url('/mod/emarking/marking/outcomes.php',
            array(
                'id' => $cm->id,
                'criterion' => $d->id,
                'action' => 'deleteoutcomes'));
    $outcomeshtml = "";
    $actions = "";
    if ($d->outcomes) {
        $outcomes = explode(",", $d->outcomes);
        foreach ($outcomes as $outcome) {
            $o = $DB->get_record("grade_outcomes", array(
                "id" => $outcome));
            $urldeletesingle = new moodle_url('/mod/emarking/marking/outcomes.php',
                    array(
                        'id' => $cm->id,
                        'criterion' => $d->id,
                        'outcome' => $o->id,
                        'action' => 'deletesingleoutcome'));
            $outcomeshtml .= html_writer::div(
                    $o->shortname . html_writer::link($urldeletesingle, "X",
                            array(
                                "class" => "deletewidget")), 'widget', array(
                        'title' => $o->fullname));
        }
        $outcomeshtml .= $OUTPUT->action_link($urldelete, get_string("deleterow", "mod_emarking"), null,
                array(
                    "class" => "rowactions"));
    }
    $data [] = array(
        $d->description,
        $outcomeshtml,
        $actions);
}
$table = new html_table();
$table->head = array(
    get_string("criterion", "mod_emarking"),
    get_string("assignedoutcomes", "mod_emarking"),
    "&nbsp;");
$table->colclasses = array(
    null,
    null,
    'actions');
$table->data = $data;
if ($numoutcomescriteria == 0) {
    echo $OUTPUT->box(get_string("nooutcomesassigned", "mod_emarking"));
}
echo html_writer::table($table);
$mformoutcomes->display();
$sqlscales = "
    SELECT s.*
    FROM {grade_outcomes_courses} goc
    INNER JOIN {course} c ON (c.id = :courseid AND goc.courseid = c.id)
    INNER JOIN {grade_outcomes} go ON (go.id = goc.outcomeid)
    INNER JOIN {scale} s ON (s.id = go.scaleid)
    GROUP BY s.id";
$scales = $DB->get_records_sql($sqlscales, array(
    'courseid' => $course->id));
$mformscalelevels = new emarking_scalelevels_form(null,
        array(
            'context' => $context,
            'criteria' => $definition->rubric_criteria,
            'id' => $cm->id,
            'emarking' => $emarking,
            'scales' => $scales));
if ($mformscalelevels->get_data()) {
    process_mform_scale_levels($mformscalelevels, $action, $emarking);
}
$mformscalelevels->display();
echo $OUTPUT->footer();
function process_mform($mform, $action, $emarking) {
    global $DB, $OUTPUT;
    if ($mform->get_data()) {
        if ($action !== $mform->get_data()->action
                || $action !== "addoutcomes") {
            return;
        }
        $datalist = $mform->get_data()->dataoutcomes;
        $toinsert = array();
        $criteria = $mform->get_data()->criteriaoutcomes;
        foreach ($criteria as $criterion) {
            $association = new stdClass();
            $association->outcome = $datalist;
            $association->criterion = $criterion;
            $toinsert [] = $association;
        }
        foreach ($toinsert as $data) {
            if ($action === "addoutcomes") {
                $association = $DB->get_records("emarking_outcomes_criteria",
                        array(
                            "emarking" => $emarking->id,
                            "criterion" => $data->criterion));
                if ($association) {
                    $DB->delete_records('emarking_outcomes_criteria', 
                            array(
                                'emarking' => $emarking->id,
                                'criterion' => $data->criterion));
                }
                $tablename = "emarking_outcomes_criteria";
            }
            $association = new stdClass();
            $association->emarking = $emarking->id;
            $association->criterion = $data->criterion;
            $association->timecreated = time();
            $association->timemodified = time();
            $association->outcome = $data->outcome;
            $association->id = $DB->insert_record($tablename, $association);
        }
        echo $OUTPUT->notification(get_string('saved', 'mod_emarking'), 'notifysuccess');
    }
}
function process_mform_scale_levels($mform, $action, $emarking) {
    global $DB, $OUTPUT;
    if ($mform->get_data()) {
        $scales = $mform->get_customdata()['scales'];
        $toinsert = array();
        foreach ($scales as $scale) {
            $levels = explode(',', $scale->scale);
            if (! isset($toinsert [$scale->id])) {
                $toinsert [$scale->id] = array();
            }
            for ($i = 0; $i < count($levels); $i ++) {
                $elementname = 'scalelevels-' . $scale->id . '-' . trim($levels [$i]);
                $toinsert [$scale->id] [] = $mform->get_data()->$elementname;
            }
        }
        foreach ($toinsert as $scaleid => $percentages) {
            if (!$association = $DB->get_record("emarking_scale_levels",
                    array(
                        "emarking" => $emarking->id,
                        "scale" => $scaleid))) {
                $association = new stdClass();
                $association->emarking = $emarking->id;
                $association->scale = $scaleid;
                $association->timecreated = time();
            }
            $association->levels = implode(',', $percentages);
            if (isset($association->id) && $association->id > 0) {
                $DB->update_record('emarking_scale_levels', $association);
            } else {
                $association->id = $DB->insert_record('emarking_scale_levels', $association);
            }
        }
        echo $OUTPUT->notification(get_string('saved', 'mod_emarking'), 'notifysuccess');
    }
}