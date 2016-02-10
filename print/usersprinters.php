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
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . "/mod/emarking/forms/printers_form.php");
global $DB, $USER, $PAGE, $OUTPUT;
require_login();
if (isguestuser()) {
    die();
}
// Action = { view, edit, delete, create }, all options page.
$action = optional_param("action", "view", PARAM_TEXT);
$idprinter = optional_param("idprinter", null, PARAM_INT);
$iduser = optional_param("iduser", null, PARAM_INT);
$sesskey = optional_param("sesskey", null, PARAM_ALPHANUM);
$context = context_system::instance();
if (! has_capability("mod/emarking:manageprinters", $context) || ! is_siteadmin($USER)) {
    print_error(get_string("notallowedprintermanagement", "mod_emarking"));
}
$urlprinters = new moodle_url("/mod/emarking/print/usersprinters.php");
// Page navigation and URL settings.
$PAGE->set_url($urlprinters);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");
if ($action == "add") {
    $addform = new emarking_addrelationship_userprint_form();
    if ($addform->is_cancelled()) {
        $action = "view";
    } else if ($creationdata = $addform->get_data()) {
        if (isset($creationdata->users) && isset($creationdata->printers)) {
            $selectusers = $creationdata->users;
            $selectprinters = $creationdata->printers;
            $records = array();
            foreach ($selectusers as $iduser) {
                foreach ($selectprinters as $idprinter) {
                    $record = new stdClass();
                    $record->id_user = $iduser;
                    $record->id_printer = $idprinter;
                    $record->datecreated = time();
                    $records [] = $record;
                }
            }
            $DB->insert_records("emarking_users_printers", $records);
        }
        $action = "view";
    }
}
if ($action == "delete") {
    if ($idprinter == null || $iduser == null) {
        print_error(get_string("dontexistrelationship", "mod_emarking"));
        $action = "view";
    } else {
        if ($relationship = $DB->get_record("emarking_users_printers",
                array(
                    "id_user" => $iduser,
                    "id_printer" => $idprinter))) {
            if ($sesskey == $USER->sesskey) {
                $DB->delete_records("emarking_users_printers",
                        array(
                            "id_user" => $iduser,
                            "id_printer" => $idprinter));
                $action = "view";
            } else {
                print_error(get_string("usernotloggedin", "mod_emarking"));
            }
        } else {
            print_error(get_string("dontexistrelationship", "mod_emarking"));
            $action = "view";
        }
    }
}
if ($action == "view") {
    $datasql = "
            SELECT
            up.id,
            u.id AS iduser,
            u.username,
            u.lastname,
            u.email,
            p.id AS idprinter,
            p.name
			FROM {user} u
            INNER JOIN {emarking_users_printers} up ON (u.id = up.id_user)
			INNER JOIN {emarking_printers} p ON (up.id_printer = p.id)";
    $usersprinters = $DB->get_records_sql($datasql);
    $printerstable = new html_table();
    if (count($usersprinters) > 0) {
        $printerstable->head = array(
            get_string("username", "mod_emarking"),
            get_string("email", "mod_emarking"),
            get_string("printername", "mod_emarking"),
            get_string("adjustments", "mod_emarking"));
        foreach ($usersprinters as $relationship) {
            $deleteurlprinter = new moodle_url("/mod/emarking/print/usersprinters.php",
                    array(
                        "action" => "delete",
                        "idprinter" => $relationship->idprinter,
                        "iduser" => $relationship->iduser,
                        "sesskey" => sesskey()));
            $deleteiconprinter = new pix_icon("t/delete", get_string("delete", "mod_emarking"));
            $deleteactionprinter = $OUTPUT->action_icon($deleteurlprinter, $deleteiconprinter,
                    new confirm_action(get_string("doyouwantdeleterelationship", "mod_emarking")));
            $printerstable->data [] = array(
                $relationship->username . " " . $relationship->lastname,
                $relationship->email,
                $relationship->name,
                $deleteactionprinter);
        }
    }
    $buttonurl = new moodle_url("/mod/emarking/print/usersprinters.php", array(
        "action" => "add"));
    $toprow = array();
    $toprow [] = new tabobject(get_string("adminprints", "mod_emarking"), new moodle_url("/mod/emarking/print/printers.php"),
            get_string("adminprints", "mod_emarking"));
    $toprow [] = new tabobject(get_string("permitsviewprinters", "mod_emarking"),
            new moodle_url("/mod/emarking/print/usersprinters.php"), get_string("permitsviewprinters", "mod_emarking"));
}
if ($action == "add") {
    $PAGE->set_title(get_string("addprinter", "mod_emarking"));
    echo $OUTPUT->header();
    $PAGE->set_heading(get_string("addprinter", "mod_emarking"));
    echo $OUTPUT->heading(get_string("addprinter", "mod_emarking"));
    $addform->display();
}
if ($action == "view" && $CFG->emarking_enableprinting) {
    $PAGE->set_title(get_string("managepermissions", "mod_emarking"));
    echo $OUTPUT->header();
    $PAGE->set_heading(get_string("managepermissions", "mod_emarking"));
    echo $OUTPUT->heading(get_string("managepermissions", "mod_emarking"));
    echo $OUTPUT->tabtree($toprow, get_string("permitsviewprinters", "mod_emarking"));
    if (count($usersprinters) == 0) {
        echo html_writer::nonempty_tag("h4", get_string("emptypermissions", "mod_emarking"),
                array(
                    'align' => 'center'));
    } else {
        echo html_writer::table($printerstable);
    }
    if (! $DB->get_records("emarking_printers")) {
        echo html_writer::nonempty_tag("h4", get_string("emptyprinters", "mod_emarking"), array(
            'align' => 'center'));
        $buttonurl = new moodle_url("/mod/emarking/print/printers.php", array(
            "action" => "add"));
        echo html_writer::nonempty_tag("div", $OUTPUT->single_button($buttonurl, get_string("addprinter", "mod_emarking")),
                array(
                    "align" => "center"));
    } else {
        echo html_writer::nonempty_tag("div", $OUTPUT->single_button($buttonurl, get_string("addpermission", "mod_emarking")),
                array(
                    'align' => 'center'));
    }
} else if (! $CFG->emarking_enableprinting) {
    echo html_writer::nonempty_tag("h4",
            get_string("notenablemanageprinters", "mod_emarking", $CFG->wwwroot . "/admin/settings.php?section=modsettingemarking"),
            array(
                "align" => "center"));
}
echo $OUTPUT->footer();