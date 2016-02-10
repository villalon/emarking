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
 * This is a one-line short description of the file
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2014-2015 Nicolas Perez (niperez@alumnos.uai.cl)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once($CFG->dirroot . "/mod/emarking/lib.php");
require_once($CFG->dirroot . "/mod/emarking/marking/form.php");
require_once($CFG->dirroot . "/mod/emarking/forms/import_excel_form.php");
require_once($CFG->libdir . '/csvlib.class.php');
global $USER, $OUTPUT, $DB, $CFG, $PAGE;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// Action var is needed to change the action wished to perfomr: list, create, edit, delete.
$action = optional_param('action', 'list', PARAM_TEXT);
$commentid = optional_param('commentid', 0, PARAM_INT);
// Emarking URL.
$urlemarking = new moodle_url('/mod/emarking/marking/predefinedcomments.php', array(
    'id' => $cm->id));
require_login($course->id);
if (isguestuser()) {
    die();
}
$PAGE->set_url($urlemarking);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('predefinedcomments', 'mod_emarking'));
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
// Output of the tabtree.
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "comment");
// Action action on delete.
if ($action == "delete") {
    // Getting record to delete.
    $DB->delete_records('emarking_predefined_comment', array(
        'id' => $commentid));
    echo $OUTPUT->notification(get_string('changessaved', 'mod_emarking'), 'notifysuccess');
    $action = "list";
}
// Action on edit.
if ($action == "edit") {
    // Geting previous data, so we can reuse it.
    if (! $editingcomment = $DB->get_record('emarking_predefined_comment', array(
        'id' => $commentid))) {
        print_error(get_string('invalidid', 'mod_emarking'));
    }
    // Creating new form and giving the var it needs to pass.
    $editcommentform = new EditCommentForm(null,
            array(
                'text' => $editingcomment->text,
                'cmid' => $cm->id,
                'commentid' => $commentid));
    // Condition of form cancelation.
    if ($editcommentform->is_cancelled()) {
        $action = "list";
    } else if ($fromform = $editcommentform->get_data()) {
        // Setup of var record to update record in moodle DB.
        $editingcomment->text = $fromform->comment ['text'];
        $editingcomment->markerid = $USER->id;
        // Updating the record.
        $DB->update_record('emarking_predefined_comment', $editingcomment);
        echo $OUTPUT->notification(get_string('changessaved', 'mod_emarking'), 'notifysuccess');
        $action = "list";
    } else {
        $editcommentform->display();
    }
}
// Action actions on "list".
if ($action == 'list') {
    // Create button url.
    $urlcreate = new moodle_url('/mod/emarking/marking/predefinedcomments.php',
            array(
                'id' => $cm->id,
                'action' => 'create'));
    $predefinedcomments = $DB->get_records('emarking_predefined_comment', array(
        'emarkingid' => $emarking->id));
    // Creating list.
    $table = new html_table();
    $table->head = array(
        get_string('comment', 'mod_emarking'),
        get_string('creator', 'mod_emarking'),
        get_string('actions', 'mod_emarking'));
    foreach ($predefinedcomments as $predefinedcomment) {
        $deleteurlcomment = new moodle_url('',
                array(
                    'action' => 'delete',
                    'id' => $cm->id,
                    'commentid' => $predefinedcomment->id));
        $deleteiconcomment = new pix_icon('t/delete', get_string('delete'));
        $deleteactioncomment = $OUTPUT->action_icon($deleteurlcomment, $deleteiconcomment,
                new confirm_action(get_string('questiondeletecomment', 'mod_emarking')));
        $editurlcomment = new moodle_url('',
                array(
                    'action' => 'edit',
                    'id' => $cm->id,
                    'commentid' => $predefinedcomment->id));
        $editiconcomment = new pix_icon('i/edit', get_string('edit'));
        $editactioncomment = $OUTPUT->action_icon($editurlcomment, $editiconcomment);
        $creatorname = $DB->get_record('user', array(
            'id' => $predefinedcomment->markerid));
        $table->data [] = array(
            $predefinedcomment->text,
            $creatorname->username,
            $editactioncomment . $deleteactioncomment);
    }
    // Form display.
    $predefinedform = new emarking_import_excel_form(null, array(
        'cmid' => $cm->id));
    if ($predefinedform->get_data()) {
        // Use csv importer from Moodle.
        $iid = csv_import_reader::get_new_iid('emarking-predefined-comments');
        $reader = new csv_import_reader($iid, 'emarking-predefined-comments');
        $content = $predefinedform->get_data()->comments;
        $reader->load_csv_content($content, 'utf8', "tab");
        $data = array();
        if (isset($predefinedform->get_data()->headers)) {
            $columns = $reader->get_columns()[0];
        } else {
            $columns = get_string("comment", "mod_emarking");
            $data [] = array(
                $reader->get_columns()[0]);
        }
        $reader->init();
        $current = 0;
        while ( $line = $reader->next() ) {
            if (count($line) > 0) {
                $data [] = array(
                    $line [0]);
            }
            $current ++;
        }
        if (isset($_REQUEST ["submitbutton"]) && $_REQUEST ["submitbutton"] === get_string("confirm")) {
            foreach ($data as $comment) {
                $predefinedcomment = new stdClass();
                $predefinedcomment->emarkingid = $emarking->id;
                $predefinedcomment->text = $comment [0];
                $predefinedcomment->markerid = $USER->id;
                $predefinedcomment->id = $DB->insert_record("emarking_predefined_comment", $predefinedcomment);
            }
            echo $OUTPUT->notification(get_string("changessaved", "mod_emarking"), "notifysuccess");
            $continue = new moodle_url("/mod/emarking/marking/predefinedcomments.php",
                    array(
                        "id" => $cm->id));
            echo $OUTPUT->single_button($continue, get_string("continue"));
        } else {
            echo $OUTPUT->notification(get_string("onlyfirstcolumn", "mod_emarking"), "notifymessage");
            $table = new html_table();
            $table->data = $data;
            $table->head = array(
                $columns);
            echo html_writer::table($table);
            $predefinedform->add_action_buttons(true, get_string('confirm'));
            $predefinedform->display();
        }
    } else {
        // Showing table.
        echo html_writer::table($table);
        // Action buttons.
        $predefinedform->add_action_buttons(true, get_string('submit'));
        $predefinedform->display();
    }
}
echo $OUTPUT->footer();