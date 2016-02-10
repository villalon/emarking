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
 * @copyright 2014 Jorge Villalón <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class emarking_printexam_form extends moodleform {
    // Extra HTML to be added at the end of the form, used for javascript functions.
    public function definition() {
        global $DB, $CFG, $USER;
        $mform = $this->_form;
        $instance = $this->_customdata;
        $examid = $instance ['examid'];
        $debug = $instance ['debug'];
        // Exam totalpages goes hidden as well.
        $mform->addElement('hidden', 'exam', $examid);
        $mform->setType('exam', PARAM_INT);
        // Exam totalpages goes hidden as well.
        $mform->addElement('hidden', 'debug', $debug);
        $mform->setType('debug', PARAM_BOOL);
        $mform->addElement('header', 'selectprinter', get_string('selectprinter', 'mod_emarking'));
        if (is_siteadmin($USER)) {
            $sqlprinters = "SELECT p.id, p.name
					FROM {emarking_printers} p";
            $printersarray = $DB->get_records_sql($sqlprinters);
        } else {
            $sqlprinters = "SELECT p.id, p.name
					FROM {emarking_printers} p
					INNER JOIN {emarking_users_printers} up ON (p.id = up.id_printer)
					WHERE up.id_user = ?";
            $printersarray = $DB->get_records_sql($sqlprinters, array(
                $USER->id));
        }
        $selectprinters = array();
        foreach ($printersarray as $printer) {
            $selectprinters [$printer->id] = $printer->name;
        }
        // Extra sheets per student.
        $mform->addElement('select', 'printername', get_string('printername', 'mod_emarking'), $selectprinters, null);
        $mform->addHelpButton('printername', 'printername', 'mod_emarking');
        // Buttons.
        $this->add_action_buttons(true, get_string('submit'));
    }
}