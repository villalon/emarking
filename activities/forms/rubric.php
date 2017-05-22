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

require_once ($CFG->dirroot . '/grade/grading/form/rubric/rubriceditor.php');

MoodleQuickForm::registerElementType ( 'rubriceditor', $CFG->dirroot . '/grade/grading/form/rubric/rubriceditor.php', 'MoodleQuickForm_rubriceditor' );
class local_ciae_rubric_form extends moodleform {
	public function definition() {
		global $CFG, $OUTPUT, $COURSE, $DB;
		
		$mform = $this->_form; // Don't forget the underscore!
		                       // Paso 1 Información básica
		$mform->addElement ( 'header', 'db', 'Información Rúbrica', null );
		
		
		// name
		$mform->addElement('text', 'name', get_string('name', 'gradingform_rubric'), array('size' => 52, 'aria-required' => 'true'));
		$mform->addRule('name', get_string('required'), 'required', null, 'client');
		$mform->setType('name', PARAM_TEXT);
		
		// description
		$options = gradingform_rubric_controller::description_form_field_options($this->_customdata['context']);
		$mform->addElement('editor', 'description_editor', get_string('description', 'gradingform_rubric'), null, $options);
		$mform->setType('description_editor', PARAM_RAW);
		
		
		
		
		// rubric editor
		$element = $mform->addElement('rubriceditor', 'rubric', get_string('rubric', 'gradingform_rubric'));
		$mform->setType('rubric', PARAM_RAW);
		
		
		$buttonarray = array();
		$buttonarray[] = &$mform->createElement('submit', 'saverubric', get_string('saverubric', 'gradingform_rubric'));
		if ($this->_customdata['allowdraft']) {
			$buttonarray[] = &$mform->createElement('submit', 'saverubricdraft', get_string('saverubricdraft', 'gradingform_rubric'));
		}
		$editbutton = &$mform->createElement('submit', 'editrubric', ' ');
		$editbutton->freeze();
		$buttonarray[] = &$editbutton;
		$buttonarray[] = &$mform->createElement('cancel'); 
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		//$mform->closeHeaderBefore('buttonar');
		
		
		
	
		
		?>
		<script>
		$(document).ready(function(){
			$("#options").hide();
		});

        </script>

<?php
	}
	// Custom validation should be added here
	function validation($data, $files) {
		return true;
	}
	
	/**
	 * Return submitted data if properly submitted or returns NULL if validation fails or
	 * if there is no submitted data.
	 *
	 * @return object submitted data; NULL if not valid or not submitted or cancelled
	 */
	public function get_data() {
		$data = parent::get_data ();
		if (! empty ( $data->saverubric )) {
			$data->status = gradingform_controller::DEFINITION_STATUS_READY;
		} else if (! empty ( $data->saverubricdraft )) {
			$data->status = gradingform_controller::DEFINITION_STATUS_DRAFT;
		}
		return $data;
	}
	
	/**
	 * Check if there are changes in the rubric and it is needed to ask user whether to
	 * mark the current grades for re-grading.
	 * User may confirm re-grading and continue,
	 * return to editing or cancel the changes
	 *
	 * @param gradingform_rubric_controller $controller        	
	 */
	public function need_confirm_regrading($controller) {
		$data = $this->get_data ();
		if (isset ( $data->rubric ['regrade'] )) {
			// we have already displayed the confirmation on the previous step
			return false;
		}
		if (! isset ( $data->saverubric ) || ! $data->saverubric) {
			// we only need confirmation when button 'Save rubric' is pressed
			return false;
		}
		if (! $controller->has_active_instances ()) {
			// nothing to re-grade, confirmation not needed
			return false;
		}
		$changelevel = $controller->update_or_check_rubric ( $data );
		if ($changelevel == 0) {
			// no changes in the rubric, no confirmation needed
			return false;
		}
		
		// freeze form elements and pass the values in hidden fields
		// TODO MDL-29421 description_editor does not freeze the normal way, uncomment below when fixed
		$form = $this->_form;
		foreach ( array (
				'rubric',
				'name'/*, 'description_editor'*/) as $fieldname ) {
			$el = & $form->getElement ( $fieldname );
			$el->freeze ();
			$el->setPersistantFreeze ( true );
			if ($fieldname == 'rubric') {
				$el->add_regrade_confirmation ( $changelevel );
			}
		}
		
		// replace button text 'saverubric' and unfreeze 'Back to edit' button
		$this->findButton ( 'saverubric' )->setValue ( get_string ( 'continue' ) );
		$el = & $this->findButton ( 'editrubric' );
		$el->setValue ( get_string ( 'backtoediting', 'gradingform_rubric' ) );
		$el->unfreeze ();
		
		return true;
	}
	protected function &findButton($elementname) {
		$form = $this->_form;
		$buttonar = & $form->getElement ( 'buttonar' );
		$elements = & $buttonar->getElements ();
		foreach ( $elements as $el ) {
			if ($el->getName () == $elementname) {
				return $el;
			}
		}
		return null;
	}
}