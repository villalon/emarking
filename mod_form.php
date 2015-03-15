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
 * The main emarking configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package mod_emarking
 * @copyright 2011 Your Name
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once ($CFG->dirroot . '/course/moodleform_mod.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');

/**
 * Module instance settings form
 */
class mod_emarking_mod_form extends moodleform_mod {
	
	/**
	 * Defines forms elements
	 */
	public function definition() {
		global $COURSE, $DB, $CFG;
		$mform = $this->_form;
		
		// Verifies that the logo image set in settings is copied to regular filearea
		emarking_verify_logo ();
		
		// Calculates context for validating permissions
		// If we have the module available, we use it, otherwise we fallback to course
		$ctx = context_course::instance ( $COURSE->id );
		if ($this->current && $this->current->coursemodule) {
			$cm = get_coursemodule_from_id ( 'emarking', $this->current->module, $COURSE->id );
			if ($cm) {
				$ctx = context_module::instance ( $cm->id );
			}
		}
		
		// -------------------------------------------------------------------------------
		// Adding the "general" fieldset, where all the common settings are showed
		$mform->addElement ( 'header', 'general', get_string ( 'general', 'form' ) );
		
		// Expected pages for submissions
		$types = array (
				1 => get_string('type_normal', 'mod_emarking'),
				2 => get_string('type_markers_training', 'mod_emarking'),
				3 => get_string('type_student_training', 'mod_emarking'),
				// 4 => get_string('type_peer_review', 'mod_emarking')
				);
		
		$mform->addElement ( 'select', 'type', get_string ( 'markingtype', 'mod_emarking' ), $types );
		$mform->addHelpButton ( 'type', 'markingtype', 'mod_emarking' );
		$mform->setType ( 'type', PARAM_INT );
		
		// Adding the standard "name" field
		$mform->addElement ( 'text', 'name', get_string ( 'name' ), array (
				'size' => '64' 
		) );
		if (! empty ( $CFG->formatstringstriptags )) {
			$mform->setType ( 'name', PARAM_TEXT );
		} else {
			$mform->setType ( 'name', PARAM_CLEAN );
		}
		$mform->addRule ( 'name', null, 'required', null, 'client' );
		$mform->addRule ( 'name', get_string ( 'maximumchars', '', 255 ), 'maxlength', 255, 'client' );
		$mform->addHelpButton ( 'name', 'modulename', 'mod_emarking' );
		
		// Adding the standard "intro" and "introformat" fields
		$this->add_intro_editor ();

		// -------------------------------------------------------------------------------
		// Experimental features
		$mform->addElement ( 'header', 'marking', get_string ( 'marking', 'mod_emarking' ) );
		
		// Expected pages for submissions
		$pages = array ();
		for($i = 0; $i <= 100; $i ++) {
			$pages [] = $i;
		}
		$mform->addElement ( 'select', 'totalpages', get_string ( 'totalpages', 'mod_emarking' ), $pages );
		$mform->addHelpButton ( 'totalpages', 'totalpages', 'mod_emarking' );
		$mform->setDefault ( 'totalpages', 0 );
		$mform->setType ( 'totalpages', PARAM_INT );
		
		$string['studentanonymous_markervisible'] = 'Student anonymous / Marker visible';
		$string['studentanonymous_markeranonymous'] = 'Student anonymous / Marker anonymous';
		$string['studentvisible_markervisible'] = 'Student visible / Marker visible';
		$string['studentvisible_markeranonymous'] = 'Student visible / Marker anonymous';
		
		// Anonymous eMarking setting
		$anonymousoptions = array (
				0 => get_string ( 'studentanonymous_markervisible', 'mod_emarking' ),
				1 => get_string ( 'studentanonymous_markeranonymous', 'mod_emarking' ) ,
				2 => get_string ( 'studentvisible_markervisible', 'mod_emarking' ),
				3 => get_string ( 'studentvisible_markeranonymous', 'mod_emarking' ) 
		);
		if (has_capability ( 'mod/emarking:manageanonymousmarking', $ctx )) {
			$mform->addElement ( 'select', 'anonymous', get_string ( 'anonymous', 'mod_emarking' ), $anonymousoptions );
			$mform->addHelpButton ( 'anonymous', 'anonymous', 'mod_emarking' );
		} else {
			$mform->addElement ( 'hidden', 'anonymous' );
		}
		$mform->setDefault ( 'anonymous', 0 );
		$mform->setType ( 'anonymous', PARAM_INT );
		
		// Custom marks
		if (has_capability ( 'mod/emarking:managespecificmarks', $ctx )) {
			$mform->addElement ( 'textarea', 'custommarks', get_string ( 'specificmarks', 'mod_emarking' ), array (
					'rows' => 17,
					'cols' => 100,
					'class' => 'smalltext'
			) );
			$mform->addHelpButton ( 'custommarks', 'specificmarks', 'mod_emarking' );
		} else {
			$mform->addElement ( 'hidden', 'custommarks' );
		}
		$mform->setDefault ( 'custommarks', '' );
		$mform->setType ( 'custommarks', PARAM_TEXT );
		
		// Due date settings
		$mform->addElement ( 'checkbox', 'qualitycontrol', get_string ( 'enablequalitycontrol', 'mod_emarking' ) );
		$mform->addHelpButton ( 'qualitycontrol', 'enablequalitycontrol', 'mod_emarking' );
		$mform->disabledIf ( 'qualitycontrol', 'type', 'neq', '1' );
		
		// Get all users with permission to grade in emarking
		$markers=get_enrolled_users($ctx, 'mod/emarking:grade');
		$chkmarkers = array();
		foreach($markers as $marker) {
			$chkmarkers[] = $mform->createElement ( 'checkbox', 'marker-'.$marker->id, null, $marker->firstname . " " . $marker->lastname);
		}
		
		// Add markers group as checkboxes
		$mform->addGroup($chkmarkers, 'markers', get_string('markersqualitycontrol','mod_emarking'), array('<br />'), false);
		$mform->addHelpButton ( 'markers', 'markersqualitycontrol', 'mod_emarking' );
		$mform->setType ( 'markers', PARAM_INT);
		$mform->disabledIf ( 'markers', 'qualitycontrol' );
		
		// -------------------------------------------------------------------------------
		// Experimental features
		$mform->addElement ( 'header', 'regrade', get_string ( 'date' ) .'s & ' . get_string ( 'regrade', 'mod_emarking' ) );
		
		// -------------------------------------------------------------------------------
		// Adding modules for eMarking process
		
		$date = new DateTime ();
		$date->setTimestamp ( usertime ( time () ) );
		
		// Due date settings
		$mform->addElement ( 'checkbox', 'enableduedate', get_string ( 'enableduedate', 'mod_emarking' ) );		
		
		$mform->addElement ( 'date_time_selector', 'markingduedate', get_string ( 'markingduedate', 'mod_emarking' ), 
				array (
				'startyear' => date('Y'),
				'stopyear' => date ( 'Y' ) + 1,
				'step' => 5,
				'defaulttime' => $date->getTimestamp(),
				'optional' => false
				), null );
		$mform->addHelpButton ( 'markingduedate', 'markingduedate', 'mod_emarking' );
		$mform->disabledIf ( 'markingduedate', 'enableduedate' );
		
		
		// Regrade settings, dates and enabling
		$mform->addElement ( 'checkbox', 'regraderestrictdates', get_string ( 'regraderestrictdates', 'mod_emarking' ) );
		$mform->addHelpButton ( 'regraderestrictdates', 'regraderestrictdates', 'mod_emarking' );
		
		$mform->addElement ( 'date_time_selector', 'regradesopendate', get_string ( 'regradesopendate', 'mod_emarking' ), array (
				'startyear' => date ( 'Y' ),
				'stopyear' => date ( 'Y' ) + 1,
				'step' => 5,
				'defaulttime' => $date->getTimestamp (),
				'optional' => false 
		), null );
		$mform->addHelpButton ( 'regradesopendate', 'regradesopendate', 'mod_emarking' );
		$mform->disabledIf ( 'regradesopendate', 'regraderestrictdates' );
		
		$date->modify ( '+2 months' );
		$mform->addElement ( 'date_time_selector', 'regradesclosedate', get_string ( 'regradesclosedate', 'mod_emarking' ), array (
				'startyear' => date ( 'Y' ),
				'stopyear' => date ( 'Y' ) + 1,
				'step' => 5,
				'defaulttime' => $date->getTimestamp (),
				'optional' => false 
		), null );
		$mform->addHelpButton ( 'regradesclosedate', 'regradesclosedate', 'mod_emarking' );
		$mform->disabledIf ( 'regradesclosedate', 'regraderestrictdates' );
		
		// Students can see peers answers
		$ynoptions = array (
				0 => get_string('no'),
				1 => get_string('yes')
		);
		$mform->addElement ( 'select', 'peervisibility', get_string ( 'viewpeers', 'mod_emarking' ), $ynoptions );
		$mform->addHelpButton ( 'peervisibility', 'viewpeers', 'mod_emarking' );
		$mform->setDefault ( 'peervisibility', 0 );
		$mform->setType ( 'peervisibility', PARAM_INT );
		
		// -------------------------------------------------------------------------------
		// add standard grading elements...
		if (! $this->_features->rating || $this->_features->gradecat) {
			$mform->addElement ( 'header', 'modstandardgrade', get_string ( 'grade' ) );
		}
		
		$grades = array ();
		for($i = 0; $i <= 100; $i++) {
			$grades [] = $i;
		}
		
		if ($this->_features->advancedgrading and ! empty ( $this->current->_advancedgradingdata ['methods'] ) and ! empty ( $this->current->_advancedgradingdata ['areas'] )) {
			
			if (count ( $this->current->_advancedgradingdata ['areas'] ) == 1) {
				// if there is just one gradable area (most cases), display just the selector
				// without its name to make UI simplier
				$areadata = reset ( $this->current->_advancedgradingdata ['areas'] );
				$areaname = key ( $this->current->_advancedgradingdata ['areas'] );
				$mform->addElement ( 'select', 'advancedgradingmethod_' . $areaname, get_string ( 'gradingmethod', 'core_grading' ), array (
						'rubric' => "Rubrica" 
				) );
				$mform->addHelpButton ( 'advancedgradingmethod_' . $areaname, 'gradingmethod', 'core_grading' );
			} else {
				throw new Exception ( "The emarking module should not define more than one grading area" );
			}
		}
		
		if ($this->_features->gradecat) {
			$mform->addElement ( 'select', 'gradecat', get_string ( 'gradecategoryonmodform', 'grades' ), grade_get_categories_menu ( $COURSE->id, $this->_outcomesused ) );
			$mform->addHelpButton ( 'gradecat', 'gradecategoryonmodform', 'grades' );
		}
		
			// if supports grades and grades arent being handled via ratings
		if (! $this->_features->rating) {			
			$mform->addElement ( 'select', 'grademin', get_string ( 'grademin', 'grades' ), $pages );
			$mform->setDefault ( 'grademin', 1 );
			
			$mform->addElement ( 'select', 'grade', get_string ( 'grademax', 'grades' ), $pages );
			$mform->setDefault ( 'grade', 7 );
		}
		
		// Regrade settings, dates and enabling
		$mform->addElement ( 'checkbox', 'adjustslope', get_string ( 'adjustslope', 'mod_emarking' ) );
		$mform->addHelpButton ( 'adjustslope', 'adjustslope', 'mod_emarking' );		
		
		$mform->addElement ( 'text', 'adjustslopegrade', get_string ( 'adjustslopegrade', 'mod_emarking' ), array ('size' => '5'));
		$mform->setType ( 'adjustslopegrade', PARAM_FLOAT);
		$mform->setDefault ( 'adjustslopegrade', 0 );			
		$mform->addHelpButton ( 'adjustslopegrade', 'adjustslopegrade', 'mod_emarking' );
		$mform->disabledIf ( 'adjustslopegrade', 'adjustslope' );
		
		$mform->addElement('text', 'adjustslopescore', get_string('adjustslopescore', 'mod_emarking'), array ('size' => '5'));
		$mform->setType ( 'adjustslopescore', PARAM_FLOAT);
		$mform->setDefault ( 'adjustslopescore', 0);
		$mform->addHelpButton ( 'adjustslopescore', 'adjustslopescore', 'mod_emarking' );
		$mform->disabledIf ( 'adjustslopescore', 'adjustslope' );

		// -------------------------------------------------------------------------------
		// add standard elements, common to all modules
		$this->standard_coursemodule_elements ();
		
		
		// -------------------------------------------------------------------------------
		// Experimental features
		$mform->addElement ( 'header', 'experimental', get_string ( 'experimental', 'mod_emarking' ) );
		
		// Regrade settings, dates and enabling
		$mform->addElement ( 'checkbox', 'heartbeatenabled', get_string ( 'heartbeatenabled', 'mod_emarking' ) );
		$mform->addHelpButton ( 'heartbeatenabled', 'heartbeatenabled', 'mod_emarking' );
		
		$mform->addElement ( 'checkbox', 'downloadrubricpdf', get_string ( 'downloadrubricpdf', 'mod_emarking' ) );
		$mform->addHelpButton ( 'downloadrubricpdf', 'downloadrubricpdf', 'mod_emarking' );
		
		$mform->addElement ( 'checkbox', 'linkrubric', get_string ( 'linkrubric', 'mod_emarking' ) );
		$mform->addHelpButton ( 'linkrubric', 'linkrubric', 'mod_emarking' );
		
		$mform->addElement ( 'checkbox', 'collaborativefeatures', get_string ( 'collaborativefeatures', 'mod_emarking' ) );
		$mform->addHelpButton ( 'collaborativefeatures', 'collaborativefeatures', 'mod_emarking' );

		// If we are in editing mode we can not change the type anymore
		if($this->_instance) {
			$emarking = $DB->get_record('emarking', array('id'=>$this->_instance));
			$freeze = array();
			$freeze[] = 'type';
			if($emarking->type == EMARKING_TYPE_NORMAL) {
				$freeze[] = 'qualitycontrol';
			}
			$mform->freeze($freeze);
		}
		
		// -------------------------------------------------------------------------------
		// add standard buttons, common to all modules
		$this->add_action_buttons ();
	}

    function data_preprocessing(&$default_values)
    {
        global $DB;
        
        parent::data_preprocessing($default_values);
        
        if ($this->_instance) {
            $markers = $DB->get_records('emarking_markers', array(
                'emarking' => $this->_instance
            ));
            foreach ($markers as $marker) {
                $default_values['marker-' . $marker->marker] = 1;
            }
        }
    }

	function validation($data, $files) {
		global $CFG, $COURSE;
	
		// Calculates context for validating permissions
		// If we have the module available, we use it, otherwise we fallback to course
		$ctx = context_course::instance ( $COURSE->id );
		if ($this->current && $this->current->coursemodule) {
			$cm = get_coursemodule_from_id ( 'emarking', $this->current->module, $COURSE->id );
			if ($cm) {
				$ctx = context_module::instance ( $cm->id );
			}
		}		
		
		$errors = array ();

		// Validate the adjusted slope
		$adjustslope = isset($data ['adjustslope']) ?  $data ['adjustslope'] : false;
		$adjustslopescore = isset($data ['adjustslopescore']) ? $data ['adjustslopescore'] : 0;
		$adjustslopegrade = isset($data ['adjustslopegrade']) ? $data ['adjustslopegrade'] : 0;
		$grademin = $data ['grademin'];

		// If we are adjusting the slope
		if($adjustslope) {
			// Make sure the grade is greater than the minimum grade
			if($adjustslopegrade <= $grademin) {
				$errors ['adjustslopegrade'] = get_string('adjustslopegrademustbegreaterthanmin','mod_emarking');
			}
		
			// Make sure the grade is lower than the maximum grade
			if($adjustslopegrade > $grademax) {
				$errors ['adjustslopegrade'] = get_string('adjustslopegrademustbelowerthanmax','mod_emarking');
			}
		
			// And that the score for adjusting is greater than 0
			if($adjustslopescore <= 0) {
				$errors ['adjustslopescore'] = get_string('adjustslopescoregreaterthanzero','mod_emarking');
			}
		}
		
		// Validate custom marks
		$custommarks = isset($data['custommarks']) ? $data['custommarks'] : '';
		$custommarks = str_replace('\r\n', '\n', $custommarks);
		
		if(strlen($custommarks)>0) {
			$parts = explode("\n", $custommarks);
			$linenumber = 0;
			foreach($parts as $line) {
				$linenumber++;
				if(strlen(trim($line)) == 0)
					continue;
				
				$subparts = explode("#", $line);
				if(count($subparts) != 2) {
					if(!isset($errors['custommarks'])) {
						$errors['custommarks'] = get_string('invalidcustommarks','mod_emarking');
					}
					$errors['custommarks'] .= "$linenumber ";
				}
			}
		}
		
		// Validate the eMarking activity type
		if($data['type'] < 1 || $data['type'] > 4) {
			$errors['type'] = 'Invalid marking type';
		}
		
		if($data['type'] == EMARKING_TYPE_MARKER_TRAINING) {
		// Get all users with permission to grade in emarking
		$markers=get_enrolled_users($ctx, 'mod/emarking:grade');
		$totalmarkers=0;
		foreach($markers as $marker) {
			if(has_capability('mod/emarking:supervisegrading', $ctx, $marker)) {
				continue;
			}
			$totalmarkers++;
		}
		if($totalmarkers == 0)
			$errors['type'] = get_string('notenoughmarkersfortraining', 'mod_emarking');
		}
		
		$qualitycontrol = isset($data ['enablequalitycontrol']) ?  $data ['enablequalitycontrol'] : false;
		if($data['type'] == EMARKING_TYPE_NORMAL && $qualitycontrol) {
		// Get all users with permission to grade in emarking
				// Get all users with permission to grade in emarking
		$markers=get_enrolled_users($ctx, 'mod/emarking:grade');
		$totalmarkers=0;
		foreach($markers as $marker) {
			if(isset($data['marker-'.$marker->id])) {
				$totalmarkers++;
			}
		}
		if($totalmarkers == 0)
			$errors['markers'] = get_string('notenoughmarkersforqualitycontrol', 'mod_emarking');
		}

		return $errors;
	}
}
