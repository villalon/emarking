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

require_once ($CFG->libdir . '/formslib.php');

/**
 * @package mod
 * @subpackage emarking
 * @copyright 2014 onwards Jorge Villalon {@link http://www.villalon.cl}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class emarking_markers_form extends moodleform {

	/**
	 * Defines forms elements
	 */
	public function definition() {
		global $COURSE, $DB,$CFG;

		$criteria = $this->_customdata['criteria'];
		$context = $this->_customdata['context'];
		$cmid = $this->_customdata['id'];
		$emarking = $this->_customdata['emarking'];
		$action = $this->_customdata['action'];
		
		$mform = $this->_form;

		// Add header
		$mform->addElement('header', 'general', get_string('assignmarkerstocriteria', 'mod_emarking'));

		// Hide course module id
		$mform->addElement('hidden', 'id', $cmid);
		$mform->setType ( 'id', PARAM_INT);

		// Hide course module id
		$mform->addElement('hidden', 'action', $action);
		$mform->setType ( 'action', PARAM_ALPHA);

		// Array of motives for regrading
		$markers=get_enrolled_users($context, 'mod/assign:grade');
		$chkmarkers = array();
		foreach($markers as $marker) {
		    $chkmarkers[$marker->id] = $marker->firstname . " " . $marker->lastname;
		}
		
		$mform->addElement('html', '<table><tr><td>');
		
		if($action === "addmarker") {
		$select = $mform->addElement('select', 'data', get_string('extraexams', 'mod_emarking'), $chkmarkers, null);
		$select->setMultiple(true);
		} else {
		// $mform->addHelpButton('extraexams', 'extraexams', 'mod_emarking');
		
		$chkpages = array();
		for($i=1;$i<=$emarking->totalpages;$i++) {
		    $chkpages[$i] = get_string('page', 'mod_emarking') . " " . $i;
		}
		$select = $mform->addElement('select', 'data', get_string('extraexams', 'mod_emarking'), $chkpages, null);
		$select->setMultiple(true);
		}
		
		$criteriaitems = array();
		foreach($criteria as $criterion) {
		    $criteriaitems[$criterion['id']] = $criterion['description'];
		}
		
		$mform->addElement('html', '</td><td>');
		
		$select = $mform->addElement('select', 'criteria', get_string('extraexams', 'mod_emarking'), $criteriaitems, null);
		$select->setMultiple(true);
		// $mform->addHelpButton('extraexams', 'extraexams', 'mod_emarking');

		$mform->addElement('html', '</td></tr></table>');
		
		// Add action buttons
		$this->add_action_buttons();
	}
}
