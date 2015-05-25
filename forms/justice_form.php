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
class justice_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $COURSE, $DB,$CFG;
        $mform = $this->_form;
        
        // Add header
        $mform->addElement('header', 'general', get_string('justice', 'mod_emarking'));
        
        // Static html for instruction
        $mform->addElement('static', 'instructions', "",
            get_string('justiceinstructions','mod_emarking'));

        // Array with -4 to 4 levels for justice perception
        $levels = array('null'=>get_string('choose', 'mod_emarking'));
        for($i=4;$i>=-4;$i--){
            $levels[$i]=$i;
        }

        // Overall fairness
        $mform->addElement('select', 'overall_fairness', get_string('justiceperceptionprocess','mod_emarking'), $levels);
        $mform->addRule('overall_fairness', get_string('overallfairnessrequired', 'mod_emarking'), 'required', null, 'client');
        
        // Expectation vs reality
        $mform->addElement('select', 'expectation_reality', get_string('justiceperceptionexpectation','mod_emarking'), $levels);
        $mform->addRule('expectation_reality', get_string('expectationrealityrequired', 'mod_emarking'), 'required', null, 'client');
        
        // Action buttons
        $this->add_action_buttons(false);
    }

    //Custom validation should be added here
    function validation($data, $files) {
        $errors = array();
        if($data["overall_fairness"]=='null')
            $errors["overall_fairness"]=get_string('overallfairnessrequired', 'mod_emarking');
        if($data["expectation_reality"]=='null')
            $errors["expectation_reality"]=get_string('expectationrealityrequired', 'mod_emarking');

        return $errors;
    }
}
