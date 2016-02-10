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

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/course/lib.php');
/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2012 Marcelo Epuyao, Jorge Villalón {@link http://www.uai.cl}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class emarking_comparativereport_form extends moodleform {
    public function definition() {
        global $DB, $CFG;
        // Custom data from page.
        $mform = $this->_form;
        $instance = $this->_customdata;
        $course = $instance ['course'];
        $cm = $instance ['cm'];
        $courseid = $course->id;
        // Hidden id to continue processing.
        $mform->addElement('hidden', 'id', $cm->id);
        $mform->setType('id', PARAM_INT);
        // Title.
        $mform->addElement('header', 'config_title', get_string('configuration', 'mod_emarking'));
        $emarkingactivities = $DB->get_records_sql_menu(
                '
				SELECT e.id,
                e.name
				FROM {emarking} e
				INNER JOIN {course_modules} cm ON (e.id = cm.instance AND cm.module = :cmmodule AND cm.course = :courseid)
				WHERE e.id <> :emarkingid ORDER BY e.name ASC',
                array(
                    'courseid' => $courseid,
                    'cmmodule' => $cm->module,
                    'emarkingid' => $cm->instance));
        $emarkingactivities [0] = 'Seleccione';
        $mform->addElement('select', 'emarking2', get_string('emarking', 'mod_emarking'), $emarkingactivities);
        $mform->setType('emarking2', PARAM_INT);
        $mform->setDefault('emarking2', 0);
        // Action buttons with no cancel.
        $this->add_action_buttons(false, "Enviar");
    }
    public function validation($data, $files) {
        global $CFG;
        $value = $data ['emarking2'];
        $errors = array();
        // Validate second eMarking activity value.
        if ($value == 0) {
            $errors ['emarking2'] = get_string('examdateinvalid', 'mod_emarking');
            return $errors;
        }
        return $errors;
    }
}