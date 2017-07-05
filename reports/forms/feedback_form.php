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
 * @copyright 2015 onwards Jorge Villalón {@link http://www.uai.cl}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class emarking_feedback_form extends moodleform {
    public function definition() {
        global $DB, $CFG;
        // Custom data from page.
        $mform = $this->_form;
        $instance = $this->_customdata;
        $id = $instance ['cmid'];
        // Hidden id to continue processing.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        // Title.
        $mform->addElement('textarea', 'markingfeedback', get_string('markingfeedback', 'mod_emarking'), 'wrap="virtual" rows="10" cols="50"');
        $mform->setType('markingfeedback', PARAM_RAW);
        // Action buttons with no cancel.
        $this->add_action_buttons(false, get_string('savechanges', 'mod_emarking'));
    }
}