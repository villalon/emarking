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
 * @copyright 2013 onwards Jorge Villalon {@link http://www.villalon.cl}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/moodleform_mod.php');
class justice_form extends moodleform {
    /**
     * Defines forms elements
     */
    public function definition() {
        global $COURSE, $DB, $CFG;
        $mform = $this->_form;
        $erlevels = array(
            'null' => get_string('choose', 'mod_emarking'),
            - 4 => get_string("er-4", "mod_emarking"),
            - 3 => get_string("er-3", "mod_emarking"),
            - 2 => get_string("er-2", "mod_emarking"),
            - 1 => get_string("er-1", "mod_emarking"),
            0 => get_string("er0", "mod_emarking"),
            1 => get_string("er1", "mod_emarking"),
            2 => get_string("er2", "mod_emarking"),
            3 => get_string("er3", "mod_emarking"),
            4 => get_string("er4", "mod_emarking"));
        $oflevels = array(
            'null' => get_string('choose', 'mod_emarking'),
            - 4 => get_string("of-4", "mod_emarking"),
            - 3 => get_string("of-3", "mod_emarking"),
            - 2 => get_string("of-2", "mod_emarking"),
            - 1 => get_string("of-1", "mod_emarking"),
            0 => get_string("of0", "mod_emarking"),
            1 => get_string("of1", "mod_emarking"),
            2 => get_string("of2", "mod_emarking"),
            3 => get_string("of3", "mod_emarking"),
            4 => get_string("of4", "mod_emarking"));
        // Add header.
        $mform->addElement('header', 'general', get_string('justice', 'mod_emarking'));
        // Overall fairness.
        $mform->addElement('select', 'overall_fairness', get_string('justiceperceptionprocess', 'mod_emarking'), $oflevels);
        $mform->addRule('overall_fairness', get_string('overallfairnessrequired', 'mod_emarking'), 'required', null, 'client');
        // Expectation vs reality.
        $mform->addElement('select', 'expectation_reality', get_string('justiceperceptionexpectation', 'mod_emarking'), $erlevels);
        $mform->addRule('expectation_reality',
                get_string('expectationrealityrequired', 'mod_emarking'), 'required', null, 'client');
        // Comment.
        $mform->addElement('textarea', 'comment', get_string('comment', 'mod_emarking'),
                array(
                    'wrap' => 'virtual',
                    'rows' => 20,
                    'cols' => 50));
        $mform->addRule('comment', get_string('maximumchars', '', 1500), 'maxlength', 1500, 'client');
        $mform->setType('comment', PARAM_TEXT);
        // Action buttons.
        $this->add_action_buttons(false);
    }
    // Custom validation should be added here.
    public function validation($data, $files) {
        $errors = array();
        if ($data ["overall_fairness"] == 'null') {
            $errors ["overall_fairness"] = get_string('overallfairnessrequired', 'mod_emarking');
        }
        if ($data ["expectation_reality"] == 'null') {
            $errors ["expectation_reality"] = get_string('expectationrealityrequired', 'mod_emarking');
        }
        return $errors;
    }
}