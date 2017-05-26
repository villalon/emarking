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


class mod_emarking_activities_create_activity_instructions extends moodleform {

    public function definition() {
        global $CFG, $OUTPUT, $COURSE, $DB;

        $mform = $this->_form; // Don't forget the underscore!
        //Paso 2 Instrucciones
        $mform->addElement('header', 'IA', 'Instrucciones para el estudiante', null);
        $mform->addElement('static', '', '','Cree las instrucciones que se entregarán a los estudiantes.');
        $systemcontext = context_system::instance();
        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'context'=>$systemcontext);
        $mform->addElement('editor', 'instructions', 'Instrucciones',null,$editoroptions);
        $mform->setType('instructions', PARAM_RAW);
        $mform->addElement('editor', 'planification', 'Planificación',null,$editoroptions);
        $mform->setType('planification', PARAM_RAW);
        $mform->addElement('editor', 'writing', 'Escritura',null,$editoroptions);
        $mform->setType('writing', PARAM_RAW);
        $mform->addElement('editor', 'editing', 'Revisión y Edición',null,$editoroptions);
        $mform->setType('editing', PARAM_RAW);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'step', 3);
        $mform->setType('step', PARAM_INT);
        $backUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/createactivity.php', array('id' => 35,'step'=>1));
        $onclick="location.href='$backUrl'";
        $mform->addElement('button', 'intro', 'Atrás',array('onclick'=>$onclick) );
        $this->add_action_buttons(false,'Siguiente');

            
      
        

    }
    //Custom validation should be added here
    
}