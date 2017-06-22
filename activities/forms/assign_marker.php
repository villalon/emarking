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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This form is used to upload a zip file containing digitized answers
 *
 * @package local
 * @subpackage ciae
 * @copyright 2016 Francisco Ralph <francisco.garcia@ciae.uchile.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ($CFG->libdir . '/formslib.php');
require_once ($CFG->dirroot . '/course/lib.php');


class mod_emarking_activities_assign_marker extends moodleform {
	
	public function definition() {
		global $CFG, $OUTPUT, $COURSE, $DB;
		
$sql="select rs.userid as id,CONCAT( u.firstname,' ',u.lastname) AS name
FROM mdl_role_assignments as rs
INNER JOIN mdl_role as r on (r.id=rs.roleid)
INNER JOIN mdl_user as u on (u.id=rs.userid)
WHERE r.shortname=?
ORDER BY name";
		$result = $DB->get_records_sql($sql, array('corrector'));
		$markers[0] = "Seleccione un corrector de la lista";
		foreach ($result as $marker){
			$markers[$marker->id] = $marker->name;
		}
		$mform = $this->_form; // Don't forget the underscore!
		// Paso 1 Información básica
		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->addElement('hidden', 'action');
		$mform->setType('action', PARAM_TEXT);
		$mform->addElement ( 'select', 'marker', 'Corrector', $markers);
		$mform->addRule ( 'marker', get_string ( 'required' ), 'required' );
		$mform->setType ( 'marker', PARAM_TEXT );
		
		$this->add_action_buttons ( true, 'Guardar y volver' );
		
		
	}
	
}