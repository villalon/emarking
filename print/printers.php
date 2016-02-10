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
 *
 * @package mod
 * @subpackage emarking
 * @copyright Hans Jeria (hansjeria@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config.php");
require_once($CFG->dirroot . "/mod/emarking/forms/printers_form.php");
global $DB, $USER, $PAGE, $OUTPUT;
require_login();
if (isguestuser()) {
    die();
}
// Action = { view, edit, delete, create }, all page options.
$action = optional_param("action", "view", PARAM_TEXT);
$idprinter = optional_param("idprinter", null, PARAM_INT);
$sesskey = optional_param("sesskey", null, PARAM_ALPHANUM);
$context = context_system::instance();
if (! has_capability("mod/emarking:manageprinters", $context) || ! is_siteadmin($USER)) {
    print_error(get_string("notallowedprintermanagement", "mod_emarking"));
}
$urlprinters = new moodle_url("/mod/emarking/print/printers.php");
// Page navigation and URL settings.
$PAGE->set_url($urlprinters);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");
if ($action == "add") {
    $addform = new emarking_addprinter_form();
    if ($addform->is_cancelled()) {
        $action = "view";
    } else if ($creationdata = $addform->get_data()) {
        $record = new stdClass();
        $record->name = $creationdata->name;
        $record->command = $creationdata->command;
        $record->ip = $creationdata->ip;
        $record->datecreated = time();
        $DB->insert_record("emarking_printers", $record);
        $action = "view";
    }
}
if ($action == "edit") {
    if ($idprinter == null) {
        print_error(get_string("printerdoesnotexist", "mod_emarking"));
        $action = "view";
    } else {
        if ($printer = $DB->get_record("emarking_printers", array(
            "id" => $idprinter))) {
            $editform = new emarking_editionprinter_form(null, array(
                "idprinter" => $idprinter));
            $defaultdata = new stdClass();
            $defaultdata->name = $printer->name;
            $defaultdata->command = $printer->command;
            $defaultdata->ip = $printer->ip;
            $editform->set_data($defaultdata);
            if ($editform->is_cancelled()) {
                $action = "view";
            } else if ($editform->get_data() && $sesskey == $USER->sesskey) {
                $record = new stdClass();
                $record->id = $editform->get_data()->idprinter;
                $record->name = $editform->get_data()->name;
                $record->command = $editform->get_data()->command;
                $record->ip = $editform->get_data()->ip;
                $record->datecreated = time();
                $DB->update_record("emarking_printers", $record);
                $action = "view";
            }
        } else {
            print_error(get_string("printerdoesnotexist", "mod_emarking"));
            $action = "view";
        }
    }
}
if ($action == "delete") {
    if ($idprinter == null) {
        print_error(get_string("printerdoesnotexist", "mod_emarking"));
        $action = "view";
    } else {
        if ($printer = $DB->get_record("emarking_printers", array(
            "id" => $idprinter))) {
            if ($sesskey == $USER->sesskey) {
                $DB->delete_records("emarking_printers", array(
                    "id" => $printer->id));
                $DB->delete_records_select("emarking_users_printers", "id_printer = ?", array(
                    $printer->id));
                $action = "view";
            } else {
                print_error(get_string("usernotloggedin", "mod_emarking"));
            }
        } else {
            print_error(get_string("printerdoesnotexist", "mod_emarking"));
            $action = "view";
        }
    }
}
if ($action == "view") {
    $printers = $DB->get_records("emarking_printers");
    $printerstable = new html_table();
    if (count($printers) > 0) {
        $printerstable->head = array(
            get_string("printername", "mod_emarking"),
            get_string("ip", "mod_emarking"),
            get_string("commandcups", "mod_emarking"),
            get_string("insertiondate", "mod_emarking"),
            get_string("adjustments", "mod_emarking"));
        foreach ($printers as $printer) {
            $deleteurlprinter = new moodle_url("/mod/emarking/print/printers.php",
                    array(
                        "action" => "delete",
                        "idprinter" => $printer->id,
                        "sesskey" => sesskey()));
            $deleteiconprinter = new pix_icon("t/delete", get_string("delete", "mod_emarking"));
            $deleteactionprinter = $OUTPUT->action_icon($deleteurlprinter, $deleteiconprinter,
                    new confirm_action(get_string("doyouwantdeleteprinter", "mod_emarking")));
            $editurlprinter = new moodle_url("/mod/emarking/print/printers.php",
                    array(
                        "action" => "edit",
                        "idprinter" => $printer->id,
                        "sesskey" => sesskey()));
            $editiconprinter = new pix_icon("i/edit", get_string("edit", "mod_emarking"));
            $editactionprinter = $OUTPUT->action_icon($editurlprinter, $editiconprinter,
                    new confirm_action(get_string("doyouwanteditprinter", "mod_emarking")));
            $printerstable->data [] = array(
                $printer->name,
                $printer->ip,
                $printer->command,
                date("d-m-Y", $printer->datecreated),
                $deleteactionprinter . $editactionprinter);
        }
    }
    $buttonurl = new moodle_url("/mod/emarking/print/printers.php", array(
        "action" => "add"));
    $toprow = array();
    $toprow [] = new tabobject(get_string("adminprints", "mod_emarking"), new moodle_url("/mod/emarking/print/printers.php"),
            get_string("adminprints", "mod_emarking"));
    $toprow [] = new tabobject(get_string("permitsviewprinters", "mod_emarking"),
            new moodle_url("/mod/emarking/print/usersprinters.php"), get_string("permitsviewprinters", "mod_emarking"));
}
if ($action == "add") {
    $PAGE->set_title(get_string("addprinter", "mod_emarking"));
    $PAGE->set_heading(get_string("addprinter", "mod_emarking"));
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string("addprinter", "mod_emarking"));
    $addform->display();
}
if ($action == "edit") {
    $PAGE->set_title(get_string("editprinter", "mod_emarking"));
    $PAGE->set_heading(get_string("editprinter", "mod_emarking"));
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string("editprinter", "mod_emarking"));
    $editform->display();
}
if ($action == "view" && $CFG->emarking_enableprinting) {
    $PAGE->set_title(get_string("adminprints", "mod_emarking"));
    $PAGE->set_heading(get_string("adminprints", "mod_emarking"));
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string("adminprints", "mod_emarking"));
    echo $OUTPUT->tabtree($toprow, get_string("adminprints", "mod_emarking"));
    if (count($printers) == 0) {
        echo html_writer::nonempty_tag("h4", get_string("emptyprinters", "mod_emarking"), array(
            "align" => "center"));
    } else {
        echo html_writer::table($printerstable);
    }
    echo html_writer::nonempty_tag("div", $OUTPUT->single_button($buttonurl, get_string("addprinter", "mod_emarking")),
            array(
                "align" => "center"));
} else if (! $CFG->emarking_enableprinting) {
    echo html_writer::nonempty_tag("h4",
            get_string("notenablemanageprinters", "mod_emarking", $CFG->wwwroot . "/admin/settings.php?section=modsettingemarking"),
            array(
                "align" => "center"));
}
echo $OUTPUT->footer();