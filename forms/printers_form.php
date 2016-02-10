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
require_once($CFG->libdir . "/formslib.php");
require_once($CFG->dirroot . '/mod/emarking/print/locallib.php');
class emarking_addprinter_form extends moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $mform->addElement("text", "name", get_string("printername", "mod_emarking"));
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", get_string("required", "mod_emarking"), "required", null, "client");
        $mform->addElement("text", "ip", get_string("ip", "mod_emarking"));
        $mform->setType("ip", PARAM_TEXT);
        $mform->addRule("ip", get_string("required", "mod_emarking"), "required", null, "client");
        $mform->addElement("text", "command", get_string("commandcups", "mod_emarking"));
        $mform->setType("command", PARAM_TEXT);
        $mform->addRule("command", get_string("required", "mod_emarking"), "required", null, "client");
        $mform->addElement("hidden", "action", "add");
        $mform->setType("action", PARAM_TEXT);
        $this->add_action_buttons(true);
    }
    public function validation($data, $files) {
        global $DB;
        $errors = array();
        $name = $data ["name"];
        $ip = $data ["ip"];
        $commandcups = $data ["command"];
        if (isset($data ["name"]) && ! empty($data ["name"]) && $data ["name"] != "" && $data ["name"] != null) {
            if (! $DB->get_recordset_select("emarking_printers", " name = ?", array(
                $name))) {
                $errors ["name"] = get_string("nameexist", "mod_emarking");
            }
        } else {
            $errors ["name"] = get_string("required", "mod_emarking");
        }
        if (isset($data ["ip"]) && ! empty($data ["ip"]) && $data ["ip"] != "" && $data ["ip"] != null) {
            if (! $DB->get_recordset_select("emarking_printers", " ip = ?", array(
                $ip))) {
                $errors ["ip"] = get_string("ipexist", "mod_emarking");
            }
            // Regex for ipv4 and ipv6.
            $matchipv4 = 0;
            $matchipv6 = 0;
            if (emarking_validate_ipv4_address($ip)) {
                $matchipv4 ++;
            }
            if (emarking_validate_ipv6_address($ip)) {
                $matchipv6 ++;
            }
            if ($matchipv4 == $matchipv6 && $matchipv4) {
                $errors ["ip"] = get_string("ipproblem", "mod_emarking");
            }
        } else {
            $errors ["ip"] = get_string("required", "mod_emarking");
        }
        return $errors;
    }
}
class emarking_editionprinter_form extends moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $instance = $this->_customdata;
        $idprinter = $instance ["idprinter"];
        $mform->addElement("text", "name", get_string("printername", "mod_emarking"));
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", get_string("required", "mod_emarking"), "required", null, "client");
        $mform->addElement("text", "ip", get_string("ip", "mod_emarking"));
        $mform->setType("ip", PARAM_TEXT);
        $mform->addRule("ip", get_string("required", "mod_emarking"), "required", null, "client");
        $mform->addElement("text", "command", get_string("commandcups", "mod_emarking"));
        $mform->setType("command", PARAM_TEXT);
        $mform->addRule("command", get_string("required", "mod_emarking"), "required", null, "client");
        $mform->addElement("hidden", "action", "edit");
        $mform->setType("action", PARAM_TEXT);
        $mform->addElement("hidden", "idprinter", $idprinter);
        $mform->setType("idprinter", PARAM_INT);
        $this->add_action_buttons(true);
    }
    public function validation($data, $files) {
        global $DB;
        $errors = array();
        $name = $data ["name"];
        $ip = $data ["ip"];
        $commandcups = $data ["command"];
        if (isset($data ["name"]) && ! empty($data ["name"]) && $data ["name"] != "" && $data ["name"] != null) {
            if (! $DB->get_recordset_select("emarking_printers", " name = ?", array(
                $name))) {
                $errors ["name"] = get_string("nameexist", "mod_emarking");
            }
        } else {
            $errors ["name"] = get_string("required", "mod_emarking");
        }
        if (isset($data ["ip"]) && ! empty($data ["ip"]) && $data ["ip"] != "" && $data ["ip"] != null) {
            if (! $DB->get_recordset_select("emarking_printers", " ip = ?", array(
                $ip))) {
                $errors ["ip"] = get_string("ipexist", "mod_emarking");
            }
            // Validete ip for regex ipv4 and ipv6.
            if (emarking_validate_ipv4_address($ip) == emarking_validate_ipv6_address($ip)
                    && emarking_validate_ipv4_address($ip)) {
                $errors ["ip"] = get_string("ipproblem", "mod_emarking");
            }
        } else {
            $errors ["ip"] = get_string("required", "mod_emarking");
        }
        return $errors;
    }
}
class emarking_addrelationship_userprint_form extends moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $sqlusers = "SELECT u.id,
                u.username,
                u.lastname,
                u.email
				FROM {user} u
                INNER JOIN {role_assignments} ra ON (u.id = ra.userid)
				LEFT JOIN {role_capabilities} rc ON (rc.roleid = ra.roleid)
				INNER JOIN {role} r ON (r.id = ra.roleid)
				WHERE rc.capability = ?
				AND rc.permission = ?
				GROUP BY u.id";
        $users = $DB->get_records_sql($sqlusers, array(
            'mod/emarking:downloadexam',
            '1'));
        $data = array();
        foreach ($users as $user) {
            $data [$user->id] = $user->username . " " . $user->lastname . " (" . $user->email . ")";
        }
        $selectusers = $mform->addElement("select", "users", get_string("selectusers", "mod_emarking"), $data);
        $selectusers->setMultiple(true);
        if ($printers = $DB->get_records("emarking_printers")) {
            $data = array();
            foreach ($printers as $printer) {
                $data [$printer->id] = $printer->name;
            }
            $selectprinters = $mform->addElement("select", "printers", get_string("selectprinters", "mod_emarking"), $data);
            $selectprinters->setMultiple(true);
        }
        $mform->addElement("hidden", "action", "add");
        $mform->setType("action", PARAM_TEXT);
        $this->add_action_buttons(true);
    }
    public function validation($data, $files) {
        global $DB;
        $errors = array();
        return $errors;
    }
}