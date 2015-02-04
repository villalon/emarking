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

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * @package mod
 * @subpackage emarking
 * @copyright 2013 onwards Jorge Villalon {@link http://www.villalon.cl}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class crtiteria_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $COURSE, $DB,$CFG;
        
		$mform = $this->_form;
		$instance = $this->_customdata;

		$cmid = $instance ['cmid'];

        // Add header
        $mform->addElement('header', 'general', get_string('criteria', 'mod_emarking'));
        
        // Select criterion
        $mform->addElement('select', 'criterion', 
        		get_string('justiceperceptionexpectation','mod_emarking'), $criteria);
        
        // Action buttons
        $this->add_action_buttons(false);
    }
}
