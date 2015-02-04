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

		$mform = $this->_form;

		// Add header
		$mform->addElement('header', 'general', get_string('assignmarkerstocriteria', 'mod_emarking'));

		// Hide course module id
		$mform->addElement('hidden', 'id', $cmid);
		$mform->setType ( 'id', PARAM_INT);

		// Array of motives for regrading
		$markers=get_enrolled_users($context, 'mod/assign:grade');

		foreach($criteria as $criterion) {
			$chkmarkers = array();
			foreach($markers as $marker) {
				$chkmarkers[] = $mform->createElement ( 'checkbox', 'assign-'.$criterion['id'].'-'.$marker->id, null, $marker->firstname . " " . $marker->lastname);
			}

			// Add markers group as checkboxes
			$mform->addGroup($chkmarkers, 'markers-'.$criterion['id'], $criterion['description'], array('<br />'), false);
			$mform->addRule ( 'markers-'.$criterion['id'], get_string ( 'required' ), 'required', null, 'client' );
			$mform->setType ( 'markers-'.$criterion['id'], PARAM_INT);
		}

		if(isset($emarking->totalpages) && $emarking->totalpages > 0) {
			// Add header
			$mform->addElement('header', 'general', ucfirst(get_string('assignpagestocriteria', 'mod_emarking')));

			foreach($criteria as $criterion) {
				$chkpages = array();
				for($i=1;$i<=$emarking->totalpages;$i++) {
					$chkpages[] = $mform->createElement ( 'checkbox', 'page-'.$criterion['id'].'-'.$i, null, get_string('page', 'mod_emarking') . " " . $i);
				}
				// Add pages group as checkboxes
				$mform->addGroup($chkpages, 'pages-'.$criterion['id'], $criterion['description'], array('<br />'), false);
				$mform->addRule ( 'pages-'.$criterion['id'], get_string ( 'required' ), 'required', null, 'client' );
				$mform->setType ( 'pages-'.$criterion['id'], PARAM_INT);
			}
		}


		// Add action buttons
		$this->add_action_buttons();
	}
}
