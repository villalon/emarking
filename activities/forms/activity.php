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
 * @package mod
 * @subpackage emarking
 * @copyright CIAE Universidad de Chile
 * @author 2016 Francisco Ralph <francisco.garcia@ciae.uchile.cl>
 * @author 2019 Jorge Villalón <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;
require_once ($CFG->libdir . '/formslib.php');
require_once ($CFG->dirroot . '/course/lib.php');

class mod_emarking_activities_create_activity_basic extends moodleform {
	public function definition() {
		global $CFG, $DB;
		
		$genres = $DB->get_records ( 'emarking_activities_genres', null, 'name ASC' );
		$genrearray = array();
		$genrearray[0] = "Seleccione un género";
		foreach ( $genres as $genre ) {
			$genrearray [$genre->id] = $genre->name;
		}
				
		$mform = $this->_form; // Don't forget the underscore!
		                       // Paso 1 Información básica
		$mform->addElement ( 'header', 'db', 'Información Básica', null );
		// Título
		$mform->addElement ( 'text', 'title', get_string('activity_title', 'mod_emarking'), 'size=150' );
		$mform->setType ( 'title', PARAM_TEXT );
		$mform->addRule ( 'title', get_string ( 'required' ), 'required' );
		$mform->addHelpButton('title', 'activity_title', 'mod_emarking');
		// descripción
		$mform->addElement ( 'textarea', 'description', get_string('activity_description', 'mod_emarking'), 'wrap="virtual" rows="10" cols="100" maxlength="300"' );
		$mform->setType ( 'description', PARAM_TEXT );
		$mform->addHelpButton('description', 'activity_description', 'mod_emarking');
		
		// Curso
		$oas = array ();
		for($i=8;$i>=1;$i--) {
		    for($j=13;$j<23;$j++) {
		      $oas[$i.'-'.$j] = $i . '° básico - ' . $j;
		    }
		    
		}
		$options = array(
		    'multiple' => true,
		    'noselectionstring' => get_string('selectoa', 'mod_emarking'),
		    'placeholder' => get_string('searchoa', 'mod_emarking')
		); 
		$mform->addElement('autocomplete', 'learningobjectives', get_string('oas', 'mod_emarking'), $oas, $options);
		$mform->addHelpButton('learningobjectives', 'oas', 'mod_emarking');
		
		// Propósito comunicativo, en un futuro este campo debe ser de autocompletar
		$mform->addElement ( 'text', 'comunicativepurpose', 'Propósito Comunicativo', 'size=150'  );
		$mform->addRule ( 'comunicativepurpose', get_string ( 'required' ), 'required' );
		$mform->setType ( 'comunicativepurpose', PARAM_TEXT );
		// $mform->addHelpButton('comunicativepurpose', 'pc','ciae');
		// Género
		$mform->addElement ( 'select', 'genre', 'Género', $genrearray );
		$mform->addRule ( 'genre', get_string ( 'required' ), 'required' );
		$mform->setType ( 'genre', PARAM_TEXT );
		// $mform->addHelpButton('genre', 'genero','ciae');
		// Audiencia
		$mform->addElement ( 'text', 'audience', 'Audiencia' , 'size=150' );
		$mform->setType ( 'audience', PARAM_TEXT );
		// $mform->addHelpButton('audience', 'audiencia','ciae');
		// Tiempo estimado
		$tiempoEstimado = array (
				'45' => '45 minutos',
				'90' => '90 minutos',
				'135' => '135 minutos',
				'180' => '180 minutos' 
		);
		$mform->addElement ( 'select', 'estimatedtime', 'Tiempo Estimado', $tiempoEstimado );
		$mform->addRule ( 'estimatedtime', get_string ( 'required' ), 'required' );
		$mform->setType ( 'estimatedtime', PARAM_TEXT );
		//Paso 2 Instrucciones
		$mform->addElement('header', 'IA', 'Instrucciones para el estudiante', null);
		$systemcontext = context_system::instance();
		$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'context'=>$systemcontext);
		$mform->addElement('editor', 'instructions', get_string('activity_instructions', 'mod_emarking'),null,$editoroptions);
		$mform->setType('instructions', PARAM_RAW);
		$mform->addHelpButton('instructions', 'activity_instructions', 'mod_emarking');
		$mform->addElement('editor', 'planification', 'Planificación',null,$editoroptions);
		$mform->setType('planification', PARAM_RAW);
		$mform->addElement('editor', 'writing', 'Escritura',null,$editoroptions);
		$mform->setType('writing', PARAM_RAW);
		//Paso 3 Didáctica
		$mform->addElement('header', 'DI', 'Didáctica', null);
		$mform->addElement('editor', 'teaching', 'Sugerencias',null,$editoroptions);
		$mform->setType('teaching', PARAM_RAW);
		//$mform->setAdvanced('teachingsuggestions');
		$mform->addElement('editor', 'languageresources', 'Recursos del Lenguaje',null,$editoroptions);
		$mform->setType('languageresources', PARAM_RAW);
		//$mform->setAdvanced('languageresources');
		$this->add_action_buttons ( true, 'Guardar cambios' );		
	}
	// Custom validation should be added here
}