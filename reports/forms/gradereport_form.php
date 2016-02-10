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
class emarking_gradereport_form extends moodleform {
    public function definition() {
        global $DB, $CFG;
        // Custom data from page.
        $mform = $this->_form;
        $instance = $this->_customdata;
        $course = $instance ['course'];
        $cm = $instance ['cm'];
        $courseid = $course->id;
        $emarkingid = $instance ['id'];
        $parallelcourses = $instance ['parallels'];
        // Hidden id to continue processing.
        $mform->addElement('hidden', 'id', $cm->id);
        $mform->setType('id', PARAM_INT);
        // If there are parallel courses show a menu for the categories.
        if ($parallelcourses && count($parallelcourses) > 0) {
            $mform->addElement('header', 'parallels_title', get_string('parallelcourses', 'mod_emarking'));
            if ($parallelcourses) {
                foreach ($parallelcourses as $pcourse) {
                    $pemarkings = $DB->get_records_menu('emarking', array(
                        'course' => $pcourse->id), 'name', 'id,name');
                    $pemarkings = array(
                        '-1' => get_string('choose', 'mod_emarking') . '...') + $pemarkings;
                    $mform->addElement('select', 'emarkingid_' . $pcourse->id, $pcourse->fullname, $pemarkings);
                }
                // Action buttons with no cancel.
                $this->add_action_buttons(false);
            }
            $mform->setExpanded('parallels_title', false);
        }
    }
}