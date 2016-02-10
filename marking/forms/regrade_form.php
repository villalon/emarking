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
 * @copyright 2011 onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
class emarking_regrade_form extends moodleform {
    /**
     * Defines forms elements
     */
    public function definition() {
        global $COURSE, $DB, $CFG;
        $definition = $this->_customdata ['criteria'];
        $draft = $this->_customdata ['draft'];
        $mform = $this->_form;
        // Add header.
        $mform->addElement('header', 'general', get_string('regraderequest', 'mod_emarking'));
        // Obtain criteria that hasn't been regraded yet.
        $regrades = $DB->get_records("emarking_regrade", array(
            "draft" => $draft->id));
        $accepted = array();
        foreach ($regrades as $regrade) {
            $accepted [] = $regrade->criterion;
        }
        $criteria = array();
        $criteria [0] = get_string("select");
        foreach ($definition->rubric_criteria as $criterion) {
            if (! in_array($criterion ["id"], $accepted)) {
                $criteria [$criterion ['id']] = $criterion ['description'];
            }
        }
        $mform->addElement('select', 'criterion', get_string('criterion', 'mod_emarking'), $criteria);
        $mform->setDefault('criterion', 0);
        $mform->setType('criterion', PARAM_INT);
        // Array of motives for regrading.
        $motives = array();
        $motives [] = & $mform->createElement('radio', 'motive', '',
                emarking_get_regrade_type_string(EMARKING_REGRADE_MISASSIGNED_SCORE), EMARKING_REGRADE_MISASSIGNED_SCORE);
        $motives [] = & $mform->createElement('radio', 'motive', '',
                emarking_get_regrade_type_string(EMARKING_REGRADE_CORRECT_ALTERNATIVE_ANSWER),
                EMARKING_REGRADE_CORRECT_ALTERNATIVE_ANSWER);
        $motives [] = & $mform->createElement('radio', 'motive', '',
                emarking_get_regrade_type_string(EMARKING_REGRADE_ERROR_CARRIED_FORWARD), EMARKING_REGRADE_ERROR_CARRIED_FORWARD);
        $motives [] = & $mform->createElement('radio', 'motive', '',
                emarking_get_regrade_type_string(EMARKING_REGRADE_UNCLEAR_FEEDBACK), EMARKING_REGRADE_UNCLEAR_FEEDBACK);
        $motives [] = & $mform->createElement('radio', 'motive', '',
                emarking_get_regrade_type_string(EMARKING_REGRADE_STATEMENT_PROBLEM), EMARKING_REGRADE_STATEMENT_PROBLEM);
        $motives [] = & $mform->createElement('radio', 'motive', '', emarking_get_regrade_type_string(EMARKING_REGRADE_OTHER),
                EMARKING_REGRADE_OTHER);
        // Add motives group as radio buttons.
        $mform->addGroup($motives, 'radioar', get_string('motive', 'mod_emarking'), array(
            '<br />'), false);
        $mform->addRule('radioar', get_string('required'), 'required', null, 'client');
        $mform->setType('radioar', PARAM_INT);
        $mform->addHelpButton('radioar', 'motive', 'mod_emarking');
        // Add justification as text area.
        $mform->addElement('textarea', 'comment', get_string('justification', 'mod_emarking'),
                array(
                    'wrap' => 'virtual',
                    'rows' => 10,
                    'cols' => 80));
        $mform->addRule('comment', get_string('required'), 'required', null, 'client');
        $mform->addRule('comment', get_string('maximumchars', '', 500), 'maxlength', 500, 'client');
        $mform->setType('comment', PARAM_TEXT);
        $mform->addHelpButton('comment', 'justification', 'mod_emarking');
        // Add action buttons.
        $this->add_action_buttons();
    }
    public function validation($data, $files) {
        global $CFG, $COURSE, $USER, $DB;
        $errors = array();
        if ($data ["criterion"] == 0) {
            $errors ["criterion"] = get_string("criterionrequired", "mod_emarking");
        }
        if (strlen($data ["comment"]) < 10) {
            $errors ["comment"] = get_string("justificationrequired", "mod_emarking");
        }
        return $errors;
    }
}