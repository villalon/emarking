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
 * The main emarking configuration form
 * It uses the standard core Moodle formslib.
 * For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2011-2016 Jorge Villalón
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->dirroot . '/repository/lib.php');
/**
 * Module instance settings form
 */
class emarking_export_form extends moodleform {
    /**
     * Defines forms elements
     */
    public function definition() {
        global $COURSE, $DB, $CFG, $USER;
        $mform = $this->_form;
        $instance = $this->_customdata;
        $cmid = $instance ['id'];
        $course = $instance ['course'];
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $emarkings = array();
        if ($parallelemarkings = emarking_get_parallel_emarkings($course, true)) {
            foreach ($parallelemarkings as $emarkingdest) {
                $emarkings [$emarkingdest->id] = $emarkingdest->fullname . " - " . $emarkingdest->name;
            }
            $select = $mform->addElement('select', 'emarkingdst', get_string('emarkingdst', 'mod_emarking'), $emarkings);
            $select->setMultiple(true);
            $mform->addHelpButton('emarkingdst', 'emarkingdst', 'mod_emarking');
            $mform->setDefault('emarkingdst', 0);
            $mform->setType('emarkingdst', PARAM_INT);
            $mform->addElement('checkbox', 'override', get_string('override', 'mod_emarking'));
            $mform->addHelpButton('override', 'override', 'mod_emarking');
            $mform->addElement('checkbox', 'overridemarkers', get_string('overridemarkers', 'mod_emarking'));
            $mform->addHelpButton('overridemarkers', 'overridemarkers', 'mod_emarking');
                        // Buttons.
            $this->add_action_buttons(true, get_string('submit'));
        } else {
            $mform->addElement('static', 'dummy', get_string('noparallelemarkings', 'mod_emarking'));
        }
    }
    public function validation($data, $files) {
        global $CFG, $COURSE, $USER, $DB;
        // Calculates context for validating permissions.
        // If we have the module available, we use it, otherwise we fallback to course.
        $ctx = context_course::instance($COURSE->id);
        $errors = array();
        $totalmarkers = 0;
        if ($totalmarkers == 0) {
            $errors ['markers'] = get_string('notenoughmarkersforqualitycontrol', 'mod_emarking');
        }
        return $errors;
    }
}