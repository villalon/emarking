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
 * @copyright 2014 onwards Jorge Villalon {@link http://www.villalon.cl}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
class emarking_outcomes_form extends moodleform {
    /**
     * Defines forms elements
     */
    public function definition() {
        global $COURSE, $DB, $CFG;
        $criteria = $this->_customdata ['criteria'];
        $context = $this->_customdata ['context'];
        $cmid = $this->_customdata ['id'];
        $emarking = $this->_customdata ['emarking'];
        $action = $this->_customdata ['action'];
        $outcomes = $this->_customdata ['outcomes'];
        $mform = $this->_form;
        // Add header.
        $mform->addElement('header', 'general', get_string('assignoutcomestocriteria', 'mod_emarking'));
        // Hide course module id.
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        // Hide action.
        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('html', '<table class="addmarkerstable"><tr><td>');
        // Array of motives for regrading.
        $chkoutcomes = array();
        foreach ($outcomes as $outcome) {
            $chkoutcomes [$outcome->id] = $outcome->shortname;
        }
        $select = $mform->addElement('select', 'dataoutcomes', get_string('outcome', 'grades'), $chkoutcomes, null);
        $select->setMultiple(false);
        $criteriaitems = array();
        foreach ($criteria as $criterion) {
            $criteriaitems [$criterion ['id']] = $criterion ['description'];
        }
        $mform->addElement('html', '</td><td>');
        $select = $mform->addElement('select', 'criteriaoutcomes', get_string('criteria', 'mod_emarking'), $criteriaitems, null);
        $select->setMultiple(true);
        $mform->addElement('html', '</td></tr></table>');
        // Add action buttons.
        $this->add_action_buttons();
    }
}