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
 * This file keeps track of upgrades to the emarking module
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations.
 * The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do. The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2013-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ($CFG->dirroot . '/repository/lib.php');
require_once ($CFG->libdir . '/csvlib.class.php');

class emarking_upload_answers_form extends moodleform {
	
	function definition() {
		global $DB, $CFG;
		
		$mform = $this->_form;
		$instance = $this->_customdata;
		
		$cmid = $instance ['cmid'];
		
		// Course module id goes hidden as well
		$mform->addElement ( 'hidden', 'cmid', $cmid );
		$mform->setType ( 'cmid', PARAM_INT );
		
		// Course module id goes hidden as well
		$mform->addElement ( 'hidden', 'upload', true);
		$mform->setType ( 'upload', PARAM_BOOL);

		$choices = csv_import_reader::get_delimiter_list();
		$mform->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'mod_emarking'), $choices);
		if (array_key_exists('cfg', $choices)) {
		    $mform->setDefault('delimiter_name', 'cfg');
		} else if (get_string('listsep', 'langconfig') == ';') {
		    $mform->setDefault('delimiter_name', 'semicolon');
		} else {
		    $mform->setDefault('delimiter_name', 'comma');
		}
		$mform->addHelpButton('delimiter_name', 'csvdelimiter', 'mod_emarking');
		
		// Archivo de respuestas a procesar CSV
		$mform->addElement ( 'filepicker', 'answersfile', get_string ( 'answersfile', 'mod_emarking' ), null, 
							array('subdirs'=>0, 'maxbytes'=>0, 'maxfiles'=>10, 'accepted_types' => array('.csv'), 
							 'return_types'=> FILE_INTERNAL));
		
		$mform->addRule ( 'answersfile', get_string ( 'answersfileisrequired', 'mod_emarking' ), 'required', null, 'client' );
		
		$mform->setType ( 'answersfile', PARAM_FILE );
		$mform->addHelpButton ( 'answersfile', 'answersfile', 'mod_emarking' );
		
		$this->add_action_buttons ( true, get_string ( 'submit' ) );
	}
	
	function validation($data, $files) {
		global $CFG;

		$errors = array();
		
		return $errors;
	}	
}