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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/moodleform_mod.php');
/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2013 onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class justice_form_criterion extends moodleform {
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
        $rubriccriteria = $this->_customdata ["rubriccriteria"];
        // Add header.
        $mform->addElement('header', 'general', get_string('justice', 'mod_emarking'));
        $mform->addElement('html',
                '<table class="addmarkerstable"><tr><td>' . '</td><td>' .
                         get_string('justiceperceptionprocesscriterion', 'mod_emarking') . '</td><td>' .
                         get_string('justiceperceptionexpectationcriterion', 'mod_emarking') . '</td></tr>');
        foreach ($rubriccriteria->rubric_criteria as $criterion) {
            $mform->addElement('html', '<tr><td>' . $criterion ['description'] . '</td><td>');
            // Overall fairness.
            $mform->addElement('select', 'of-' . $criterion ['id'], '', $oflevels);
            $mform->addElement('html', '</td><td>');
            // Expectation vs reality.
            $mform->addElement('select', 'er-' . $criterion ['id'], '', $erlevels);
            $mform->addElement('html', '</td></tr>');
        }
        $mform->addElement('html', '</table>');
        // Comment.
        $mform->addElement('textarea', 'comment', get_string('comment', 'mod_emarking'),
                array(
                    'wrap' => 'virtual',
                    'rows' => 7,
                    'cols' => 80));
        $mform->addRule('comment', get_string('maximumchars', '', 1500), 'maxlength', 1500, 'client');
        $mform->setType('comment', PARAM_TEXT);
        // Action buttons.
        $this->add_action_buttons(false);
    }
    // Custom validation should be added here.
    public function validation($data, $files) {
        $errors = array();
        foreach ($data as $key => $value) {
            if ($value == 'null') {
                if (core_text::substr($key, 0, 2) == 'of') {
                    $errors [$key] = get_string('overallfairnessrequired', 'mod_emarking');
                } else {
                    $errors [$key] = get_string('expectationrealityrequired', 'mod_emarking');
                }
            }
        }
        return $errors;
    }
}