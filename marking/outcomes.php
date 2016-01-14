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
 * Page to send a new print order
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Jorge Villalón
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/grade/grading/form/rubric/renderer.php");
require_once ("forms/outcomes_form.php");

global $DB, $USER;

// Obtain parameter from URL
$cmid = required_param('id', PARAM_INT);
$criterionid = optional_param('criterion', 0, PARAM_INT);
$action = optional_param('action', 'view', PARAM_ALPHA);

if (! $cm = get_coursemodule_from_id('emarking', $cmid)) {
    print_error(get_string('invalidid', 'mod_emarking') . " id: $cmid");
}

if (! $emarking = $DB->get_record('emarking', array(
    'id' => $cm->instance
))) {
    print_error(get_string('invalidid', 'mod_emarking') . " id: $cmid");
}

// Validate that the parameter corresponds to a course
if (! $course = $DB->get_record('course', array(
    'id' => $emarking->course
))) {
    print_error(get_string('invalidcourseid', 'mod_emarking') . " id: $courseid");
}

if ($criterionid > 0) {
    $criterion = $DB->get_record('gradingform_rubric_criteria', array(
        'id' => $criterionid
    ));
    if ($criterion == null) {
        print_error("Invalid criterion id");
    }
}

$context = context_module::instance($cm->id);

$url = new moodle_url('/mod/emarking/marking/outcomes.php', array(
    'id' => $cmid
));

// First check that the user is logged in
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

// Verify capability for security issues
if (! has_capability('mod/emarking:assignmarkers', $context)) {
    $item = array(
        'context' => context_module::instance($cm->id),
        'objectid' => $cm->id
    );
    // Add to Moodle log so some auditing can be done
    \mod_emarking\event\markers_assigned::create($item)->trigger();
    print_error(get_string('invalidaccess', 'mod_emarking'));
}

echo $OUTPUT->header();

// Heading and tabs if we are within a course module
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "outcomes");

// Get rubric instance
list ($gradingmanager, $gradingmethod) = emarking_validate_rubric($context, true);

// As we have a rubric we can get the controller
$rubriccontroller = $gradingmanager->get_controller($gradingmethod);
if (! $rubriccontroller instanceof gradingform_rubric_controller) {
    print_error(get_string('invalidrubric', 'mod_emarking'));
}

$definition = $rubriccontroller->get_definition();

$mform_outcomes = new emarking_outcomes_form(null, array(
    'context' => $context,
    'criteria' => $definition->rubric_criteria,
    'id' => $cmid,
    'emarking' => $emarking,
    'action' => 'addoutcomes'
));

if ($mform_outcomes->get_data()) {
    $newmarkers = process_mform($mform_outcomes, "addoutcomes", $emarking);
}

if ($action === 'deleteoutcomes') {
    $DB->delete_records('emarking_outcomes_criteria', array(
        'emarking' => $emarking->id,
        'criterion' => $criterion->id
    ));
    echo $OUTPUT->notification(get_string("transactionsuccessfull", "mod_emarking"), 'notifysuccess');
}

$numoutcomescriteria = $DB->count_records("emarking_outcomes_criteria", array(
    "emarking" => $emarking->id
));

$outcomescriteria = $DB->get_recordset_sql("
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
    FROM {gradingform_rubric_criteria} as c
    LEFT JOIN {emarking_outcomes_criteria} as mc ON (c.definitionid = :definition AND mc.emarking = :emarking AND c.id = mc.criterion)
    LEFT JOIN {grade_outcomes} as o ON (mc.outcome = o.id)
    WHERE c.definitionid = :definition2
    ORDER BY c.id ASC, o.fullname ASC) as T
    GROUP BY id", array(
    "definition" => $definition->id,
    "definition2" => $definition->id,
    "emarking" => $emarking->id
));

$data = array();
foreach ($outcomescriteria as $d) {
    $urldelete = new moodle_url('/mod/emarking/marking/outcomes.php', array(
        'id' => $cm->id,
        'criterion' => $d->id,
        'action' => 'deleteoutcomes'
    ));
    $outcomeshtml = "";
    if ($d->outcomes) {
        $outcomes = explode(",", $d->outcomes);
        foreach ($outcomes as $outcome) {
            $o = $DB->get_record("grade_outcomes", array(
                "id" => $outcome
            ));
            $outcomeshtml .= html_writer::div($o->shortname, '', array('title'=>$o->fullname, 'style'=>'float:left; margin-right: 5px; border: 1px solid black; padding: 2px;'));
        }
        $outcomeshtml .= $OUTPUT->action_link($urldelete, get_string("deleterow", "mod_emarking"), null, array(
            "class" => "rowactions"
        ));
    }

    $data[] = array(
        $d->description,
        $outcomeshtml
    );
}

$table = new html_table();
$table->head = array(
    get_string("criterion", "mod_emarking"),
    get_string("assignedoutcomes", "mod_emarking")
);
$table->colclasses = array(
    null,
    null
);
$table->data = $data;

if ($numoutcomescriteria == 0) {
    echo $OUTPUT->box(get_string("nooutcomesassigned", "mod_emarking"));
}

echo html_writer::table($table);

$mform_outcomes->display();

echo $OUTPUT->footer();

function process_mform($mform, $action, $emarking)
{
    global $DB, $OUTPUT;
    
    if ($mform->get_data()) {
        if ($action !== $mform->get_data()->action) {
            return;
        }
        if ($action === "addoutcomes") {
            $datalist = $mform->get_data()->dataoutcomes;
        }
        $toinsert = array();
        foreach ($datalist as $data) {
            if ($action === "addoutcomes") {
                $criteria = $mform->get_data()->criteriaoutcomes;
            }
            foreach ($criteria as $criterion) {
                $association = new stdClass();
                $association->outcome = $data;
                $association->criterion = $criterion;
                $toinsert[] = $association;
            }
        }
        
        foreach ($toinsert as $data) {
            if ($action === "addoutcomes") {
                $association = $DB->get_record("emarking_outcomes_criteria", array(
                    "emarking" => $emarking->id,
                    "criterion" => $data->criterion,
                    "outcome" => $data->outcome
                ));
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