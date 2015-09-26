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
 * @package mod
 * @subpackage emarking
 * @copyright 2011-2015 Jorge Villalón
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . '/course/moodleform_mod.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ($CFG->dirroot . '/repository/lib.php');

/**
 * Module instance settings form
 */
class mod_emarking_mod_form extends moodleform_mod
{
    
    // Extra HTML to be added at the end of the form, used for javascript functions.
    private $extraScript = "";

    /**
     * Defines forms elements
     */
    public function definition()
    {
        global $COURSE, $DB, $CFG, $USER;
        
        $mform = $this->_form;
        $instance = $this->_customdata;
        
        // Exam id, in case we are in editing mode
        $examid = 0;
        $exam = null;
        $emarking = null;
        if ($this->_instance) {
            $emarking = $DB->get_record('emarking', array(
                'id' => $this->_instance
            ));
            $exam = $DB->get_record("emarking_exams", array(
                "emarking" => $emarking->id
            ));
            if ($exam) {
                $examid = $exam->id;
            }
        }
        
        // Verifies that the logo image set in settings is copied to regular filearea
        emarking_verify_logo();
        
        // Calculates context for validating permissions
        // If we have the module available, we use it, otherwise we fallback to course
        $ctx = context_course::instance($COURSE->id);
        if ($this->current && $this->current->coursemodule) {
            $cm = get_coursemodule_from_id('emarking', $this->current->module, $COURSE->id);
            if ($cm) {
                $ctx = context_module::instance($cm->id);
            }
        }
        
        // Numbers 1 to 100. Used in pages and min and max grades
        $numbers1to100 = array();
        for ($i = 0; $i <= 100; $i ++) {
            $numbers1to100[] = $i;
        }
        
        // Numbers from 0 to 2 for extra exams and sheets
        $numberarray = array();
        for ($j = 0; $j < 3; $j ++) {
            $numberarray[$j] = $j;
        }
        
        // Today
        $date = new DateTime();
        $date->setTimestamp(usertime(time()));
        
        // Expected pages for submissions
        $types = array(
            EMARKING_TYPE_PRINT_ONLY => get_string('type_print_only', 'mod_emarking'),
            EMARKING_TYPE_PRINT_SCAN => get_string('type_print_scan', 'mod_emarking'),
            EMARKING_TYPE_NORMAL => get_string('type_normal', 'mod_emarking')
        );
        
        if (! $this->_instance || ($emarking && $emarking->type == EMARKING_TYPE_MARKER_TRAINING)) {
            $types[EMARKING_TYPE_MARKER_TRAINING] = get_string('type_markers_training', 'mod_emarking');
        }
        // 3 => get_string('type_student_training', 'mod_emarking'),
        // 4 => get_string('type_peer_review', 'mod_emarking')
        
        // MARKING TYPE
        $mform->addElement('select', 'type', get_string('markingtype', 'mod_emarking'), $types, array(
            "onchange" => "showFullForm()"
        ));
        $mform->addHelpButton('type', 'markingtype', 'mod_emarking');
        $mform->setType('type', PARAM_INT);
        
        // EXAM NAME
        $mform->addElement('text', 'name', get_string("examname", "mod_emarking"), array(
            'size' => '64'
        ));
        if (! empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'examname', 'mod_emarking');
        
        if ($exam && $exam->status >= EMARKING_EXAM_SENT_TO_PRINT) {
            $mform->freeze('name');
        }
        
        // PRINT CONFIGURATION
        $mform->addElement('header', 'print', get_string("print", "mod_emarking"));
        
        // EXAM PDF FILE(S) OR PREVIOUSLY SENT EXAM
        
        // Check if there are any exams with no emarking activity associated
        $availableexams = $DB->get_records("emarking_exams", array(
            "course" => $COURSE->id,
            "emarking" => 0
        ));

        if($CFG->version > 2014111008) {
            $this->standard_intro_elements(get_string('examinfo', 'mod_emarking'));
        } else {
            $this->add_intro_editor();
        }
        
        $examsarray = array();
        if ($availableexams && ! $exam) {
            $examsarray[0] = get_string("no");
            foreach ($availableexams as $avexam) {
                $examsarray[$avexam->id] = $avexam->name;
            }
            // Expected pages for submissions
            $mform->addElement('select', 'exam', get_string('orsentexam', 'mod_emarking'), $examsarray, array(
                "onchange" => "previousExamUpdate();"
            ));
            $mform->addHelpButton('exam', 'orsentexam', 'mod_emarking');
            $mform->setDefault('exam', 0);
            $mform->setType('exam', PARAM_INT);
            $mform->disabledIf('exam', 'type', 'eq', '2');
        } else 
            if ($exam) {
                $mform->addElement('hidden', 'exam', $exam->id);
                $mform->setType('exam', PARAM_INT);
            } else {
                $mform->addElement('hidden', 'exam', 0);
                $mform->setType('exam', PARAM_INT);
            }
        
        $examfilename = get_string('pdffile', 'mod_emarking');
        // If we are editing, we
        if (($exam && $exam->status < EMARKING_EXAM_SENT_TO_PRINT)
            || !$this->_instance) {
                
                if($this->_instance) {
                    $examfilename = get_string('pdffileupdate', 'mod_emarking');
                } else {
                    $examfilename = get_string('pdffile', 'mod_emarking');
                }
            
            $mform->addElement('filemanager', 'exam_files', $examfilename, null, array(
                'subdirs' => 0,
                'maxbytes' => 0,
                'maxfiles' => 10,
                'accepted_types' => array(
                    '.pdf'
                ),
                'return_types' => FILE_INTERNAL
            ));
            
            $mform->setType('exam_files', PARAM_FILE);
            $mform->addHelpButton('exam_files', 'pdffile', 'mod_emarking');
            
            $examdate = new DateTime();
            $examdate->setTimestamp(usertime(time()));
            $examdate->modify('+2 days');
            $examdate->modify('+10 minutes');
            
            $examw = date("w", $date->getTimestamp());
            
            // Sundays and saturdays shouldn't be selected by default
            if ($examw == 0) {
                $examdate->modify('+1 days');
            }
            
            if ($examw == 6) {
                $examdate->modify('+2 days');
            }
            
            // Exam date
            
            $mform->addElement('date_time_selector', 'examdate', get_string('examdate', 'mod_emarking'), array(
                'startyear' => date('Y'),
                'stopyear' => date('Y') + 1,
                'step' => 5,
                'defaulttime' => $examdate->getTimestamp(),
                'optional' => false
            ), $instance['options']);
            $mform->addHelpButton('examdate', 'examdate', 'mod_emarking');
            
            if ($exam && $exam->status >= EMARKING_EXAM_SENT_TO_PRINT) {
                $mform->freeze('examdate');
                $mform->freeze('exam_files');
            }
        } else {
            // Add message explaining why they can't change files or dates anymore
            $mform->addElement('static', 'examdownloaded', get_string("pdffile", "mod_emarking"), get_string("examalreadysent", "mod_emarking"));
        }
        
        // Print students list
        $mform->addElement('checkbox', 'printlist', get_string('printlist', 'mod_emarking'));
        $mform->setType('printlist', PARAM_BOOL);
        $mform->addHelpButton('printlist', 'printlist', 'mod_emarking');
        $mform->setAdvanced('printlist');
        $mform->setDefault('printlist', false);
        
        // print double sided
        $mform->addElement('checkbox', 'printdoublesided', get_string('printdoublesided', 'mod_emarking'));
        $mform->setType('printdoublesided', PARAM_BOOL);
        $mform->addHelpButton('printdoublesided', 'printdoublesided', 'mod_emarking');
        $mform->setDefault('printdoublesided', false);
        $mform->setAdvanced('printdoublesided');
        
        // Personalized header (using QR)
        $mform->addElement('checkbox', 'headerqr', get_string('headerqr', 'mod_emarking'));
        $mform->setType('headerqr', PARAM_BOOL);
        $mform->addHelpButton('headerqr', 'headerqr', 'mod_emarking');
        $mform->setDefault('headerqr', true);
        $mform->disabledIf('headerqr', 'printdoublesided', 'checked');
        $mform->setAdvanced('headerqr');
        
        // Extra sheets per student
        $mform->addElement('select', 'extrasheets', get_string('extrasheets', 'mod_emarking'), $numberarray, null);
        $mform->addHelpButton('extrasheets', 'extrasheets', 'mod_emarking');
        $mform->setAdvanced('extrasheets');
        
        // Extra students
        $mform->addElement('select', 'extraexams', get_string('extraexams', 'mod_emarking'), $numberarray, null);
        $mform->addHelpButton('extraexams', 'extraexams', 'mod_emarking');
        $mform->setAdvanced('extraexams');
        
        // Obtain parallel courses
        if ($parallelcourses = emarking_get_parallel_courses($COURSE)) {
            // Add a checkbox for each parallel course
            $checkboxes = array();
            foreach ($parallelcourses as $course) {
                $checkboxes[] = $mform->createElement('checkbox', $course->shortname, null, $course->fullname, 'checked');
            }
            
            // If there's any parallel course we add the multicourse option
            if (count($checkboxes) > 0) {
                $mform->addGroup($checkboxes, 'multicourse', get_string('multicourse', 'mod_emarking'), array(
                    '<br/>'
                ), true);
                $mform->addHelpButton('multicourse', 'multicourse', 'mod_emarking');
                $mform->setAdvanced('multicourse');
                if ($examid == 0) {
                    $mform->addElement('button', 'selectall', get_string('selectall', 'mod_emarking'), array(
                        
                        'onClick' => 'selectAllCheckboxes(this.form,true);'
                    ));
                    $mform->setAdvanced('selectall');
                    
                    $mform->addElement('button', 'deselectall', get_string('selectnone', 'mod_emarking'), array(
                        
                        'onClick' => 'selectAllCheckboxes(this.form,false);'
                    ));
                    $mform->setAdvanced('deselectall');
                } else {
                    foreach ($parallelcourses as $cid => $course) {
                        
                        $selected = false;
                        if ($examid > 0 && $parallel = $DB->get_record('emarking_exams', array(
                            'file' => $exam->file,
                            'course' => $cid
                        ))) {
                            $selected = true;
                        }
                        $mform->setType("multicourse[$course->shortname]", PARAM_BOOL);
                        if ($selected) {
                            $mform->setDefault("multicourse[$course->shortname]", true);
                        }
                    }
                }
            }
            
            $this->extraScript .= "<script>function selectAllCheckboxes(form,checked) { " . "for (var i = 0; i < form.elements.length; i++ ) { " . "    if (form.elements[i].type == 'checkbox' && form.elements[i].id.indexOf('multiseccion') > 0) { " . "        form.elements[i].checked = checked; " . "    } " . "} " . "}</script>";
        }
        
        $mform->addElement('hidden', 'action', 'uploadfile');
        $mform->setType('action', PARAM_ALPHA);
        
        // Enrolment methods to include in printing
        $enrolcheckboxes = array();
        $enrolavailables = $this->getAvailableEnrolments();
        foreach ($enrolavailables as $enrolment) {
            $enrolcheckboxes[] = $mform->createElement('checkbox', $enrolment, null, get_string('enrol' . $enrolment, 'mod_emarking'), 'checked');
        }
        
        $mform->addGroup($enrolcheckboxes, 'enrolments', get_string('enrolments', 'mod_emarking'), array(
            '<br/>'
        ), true);
        $mform->addHelpButton('enrolments', 'enrolments', 'mod_emarking');
        $mform->setAdvanced("enrolments");
        
        // If we are editing, we use the previous enrolments
        if ($examid > 0 && isset($exam->enrolments)) {
            $enrolincludes = explode(",", $exam->enrolments);
            foreach ($enrolincludes as $enroldefault) {
                if (in_array($enroldefault, $enrolavailables)) {
                    $mform->setDefault("enrolments[$enroldefault]", true);
                }
            }
            // If we are creating a new one, the default comes from the plugin settings
        } else 
            if ($CFG->emarking_enrolincludes && strlen($CFG->emarking_enrolincludes) > 1) {
                $enrolincludes = explode(",", $CFG->emarking_enrolincludes);
                foreach ($enrolincludes as $enroldefault) {
                    if (in_array($enroldefault, $enrolavailables)) {
                        $mform->setDefault("enrolments[$enroldefault]", true);
                    }
                }
            }
        
        $mform->addElement('header', 'scan', get_string('scan', "mod_emarking"));
        
        // Due date settings
        $mform->addElement('html', '<div id="scanisenabled">' . get_string('scanisenabled', 'mod_emarking') . '</div>');
        $mform->addElement('html', '<div id="osmisenabled">' . get_string('osmisenabled', 'mod_emarking') . '</div>');
        
        $mform->addElement('header', 'osm', get_string('onscreenmarking', "mod_emarking"));
        
        // Students can see peers answers
        $ynoptions = array(
            0 => get_string('no'),
            1 => get_string('yespeerisanonymous', 'mod_emarking')
        );
        $mform->addElement('select', 'peervisibility', get_string('viewpeers', 'mod_emarking'), $ynoptions);
        $mform->addHelpButton('peervisibility', 'viewpeers', 'mod_emarking');
        $mform->setDefault('peervisibility', 0);
        $mform->setType('peervisibility', PARAM_INT);
        $mform->disabledIf('peervisibility', 'type', 'eq', '2');
        
        // Expected pages for submissions
        $mform->addElement('hidden', 'totalpages', 0);
        $mform->setType('totalpages', PARAM_INT);
        
        // Anonymous eMarking setting
        $anonymousoptions = array(
            EMARKING_ANON_STUDENT => get_string('studentanonymous_markervisible', 'mod_emarking'),
            EMARKING_ANON_BOTH => get_string('studentanonymous_markeranonymous', 'mod_emarking'),
            EMARKING_ANON_NONE => get_string('studentvisible_markervisible', 'mod_emarking'),
            EMARKING_ANON_MARKER => get_string('studentvisible_markeranonymous', 'mod_emarking')
        );
        if (has_capability('mod/emarking:manageanonymousmarking', $ctx)) {
            $mform->addElement('select', 'anonymous', get_string('anonymous', 'mod_emarking'), $anonymousoptions);
            $mform->addHelpButton('anonymous', 'anonymous', 'mod_emarking');
        } else {
            $mform->addElement('hidden', 'anonymous');
        }
        $mform->setDefault('anonymous', 0);
        $mform->setType('anonymous', PARAM_INT);
        $mform->disabledIf('anonymous', 'type', 'eq', '2');
        
        // Justice perception eMarking setting
        $justiceoptions = array(
            EMARKING_JUSTICE_DISABLED => get_string('justicedisabled', 'mod_emarking'),
            EMARKING_JUSTICE_PER_SUBMISSION => get_string('justicepersubmission', 'mod_emarking'),
            EMARKING_JUSTICE_PER_CRITERION => get_string('justicepercriterion', 'mod_emarking')
        );
        if (has_capability('mod/emarking:manageanonymousmarking', $ctx)) {
            $mform->addElement('select', 'justiceperception', get_string('justiceperception', 'mod_emarking'), $justiceoptions);
            $mform->addHelpButton('justiceperception', 'justiceperception', 'mod_emarking');
        } else {
            $mform->addElement('hidden', 'justiceperception');
        }
        $mform->setDefault('justiceperception', 0);
        $mform->setType('justiceperception', PARAM_INT);
        $mform->disabledIf('justiceperception', 'type', 'eq', '2');
        
        $mform->addElement('checkbox', 'linkrubric', get_string('linkrubric', 'mod_emarking'));
        $mform->addHelpButton('linkrubric', 'linkrubric', 'mod_emarking');
        
        $mform->addElement('checkbox', 'collaborativefeatures', get_string('collaborativefeatures', 'mod_emarking'));
        $mform->addHelpButton('collaborativefeatures', 'collaborativefeatures', 'mod_emarking');
        
        // Custom marks
        if (has_capability('mod/emarking:managespecificmarks', $ctx)) {
            $mform->addElement('textarea', 'custommarks', get_string('specificmarks', 'mod_emarking'), array(
                'rows' => 5,
                'cols' => 100,
                'class' => 'smalltext'
            ));
            $mform->addHelpButton('custommarks', 'specificmarks', 'mod_emarking');
        } else {
            $mform->addElement('hidden', 'custommarks');
        }
        $mform->setDefault('custommarks', '');
        $mform->setType('custommarks', PARAM_TEXT);
        $mform->setAdvanced('custommarks');
        $mform->disabledIf('custommarks', 'type', 'eq', '2');
        
        // Due date settings
        $mform->addElement('checkbox', 'qualitycontrol', get_string('enablequalitycontrol', 'mod_emarking'));
        $mform->addHelpButton('qualitycontrol', 'enablequalitycontrol', 'mod_emarking');
        $mform->setAdvanced('qualitycontrol');
        $mform->disabledIf('qualitycontrol', 'type', 'eq', '2');
        
        // Get all users with permission to grade in emarking
        $markers = get_enrolled_users($ctx, 'mod/emarking:grade');
        $chkmarkers = array();
        foreach ($markers as $marker) {
            $chkmarkers[] = $mform->createElement('checkbox', 'marker-' . $marker->id, null, $marker->firstname . " " . $marker->lastname);
        }
        
        // Add markers group as checkboxes
        $mform->addGroup($chkmarkers, 'markers', get_string('markersqualitycontrol', 'mod_emarking'), array(
            '<br />'
        ), false);
        $mform->addHelpButton('markers', 'markersqualitycontrol', 'mod_emarking');
        $mform->setType('markers', PARAM_INT);
        $mform->disabledIf('markers', 'qualitycontrol');
        $mform->setAdvanced('markers');
        $mform->disabledIf('markers', 'type', 'eq', '2');
        
        // Due date settings
        $mform->addElement('checkbox', 'enableduedate', get_string('enableduedate', 'mod_emarking'));
        $mform->setAdvanced('enableduedate');
        
        $mform->addElement('date_time_selector', 'markingduedate', get_string('markingduedate', 'mod_emarking'), array(
            'startyear' => date('Y'),
            'stopyear' => date('Y') + 1,
            'step' => 5,
            'defaulttime' => $date->getTimestamp(),
            'optional' => false
        ), null);
        $mform->addHelpButton('markingduedate', 'markingduedate', 'mod_emarking');
        $mform->setAdvanced('markingduedate');
        $mform->disabledIf('markingduedate', 'enableduedate');
        
        // Regrade settings, dates and enabling
        $mform->addElement('checkbox', 'regraderestrictdates', get_string('regraderestrictdates', 'mod_emarking'));
        $mform->addHelpButton('regraderestrictdates', 'regraderestrictdates', 'mod_emarking');
        $mform->setAdvanced('regraderestrictdates');
        
        $mform->addElement('date_time_selector', 'regradesopendate', get_string('regradesopendate', 'mod_emarking'), array(
            'startyear' => date('Y'),
            'stopyear' => date('Y') + 1,
            'step' => 5,
            'defaulttime' => $date->getTimestamp(),
            'optional' => false
        ), null);
        $mform->addHelpButton('regradesopendate', 'regradesopendate', 'mod_emarking');
        $mform->setAdvanced('regradesopendate');
        $mform->disabledIf('regradesopendate', 'regraderestrictdates');
        
        $date->modify('+2 months');
        $mform->addElement('date_time_selector', 'regradesclosedate', get_string('regradesclosedate', 'mod_emarking'), array(
            'startyear' => date('Y'),
            'stopyear' => date('Y') + 1,
            'step' => 5,
            'defaulttime' => $date->getTimestamp(),
            'optional' => false
        ), null);
        $mform->addHelpButton('regradesclosedate', 'regradesclosedate', 'mod_emarking');
        $mform->setAdvanced('regradesclosedate');
        $mform->disabledIf('regradesclosedate', 'regraderestrictdates');
        
        $mform->addElement('header', 'markerstraining', get_string('type_markers_training', 'mod_emarking'));
        $mform->setExpanded('markerstraining');
        
        $delphidate = new DateTime();
        $delphidate->setTimestamp(usertime(time()));
        
        $delphidate->modify("+1 week");
        // Delphi agreement date settings
        $mform->addElement('date_time_selector', 'firststagedate', get_string('firststagedate', 'mod_emarking'), array(
            'startyear' => date('Y'),
            'stopyear' => date('Y') + 1,
            'step' => 5,
            'defaulttime' => $delphidate->getTimestamp(),
            'optional' => false
        ), null);
        $mform->addHelpButton('firststagedate', 'firststagedate', 'mod_emarking');
        $mform->disabledIf('firststagedate', 'type', 'neq', '2');
        
        $delphidate->modify("+1 week");
        // Delphi agreement date settings
        $mform->addElement('date_time_selector', 'secondstagedate', get_string('secondstagedate', 'mod_emarking'), array(
            'startyear' => date('Y'),
            'stopyear' => date('Y') + 1,
            'step' => 5,
            'defaulttime' => $delphidate->getTimestamp(),
            'optional' => false
        ), null);
        $mform->addHelpButton('secondstagedate', 'secondstagedate', 'mod_emarking');
        $mform->disabledIf('secondstagedate', 'type', 'neq', '2');
        
        // Expected pages for submissions
        $agreements = array(
            "0" => get_string('agreementflexibility00', 'mod_emarking'),
            "0.2" => get_string('agreementflexibility20', 'mod_emarking'),
            "0.4" => get_string('agreementflexibility40', 'mod_emarking')
        );
        // 3 => get_string('type_student_training', 'mod_emarking'),
        // 4 => get_string('type_peer_review', 'mod_emarking')
        
        // MARKING TYPE
        $mform->addElement('select', 'agreementflexibility', get_string('agreementflexibility', 'mod_emarking'), $agreements);
        $mform->addHelpButton('agreementflexibility', 'agreementflexibility', 'mod_emarking');
        $mform->setType('agreementflexibility', PARAM_INT);
        
        // -------------------------------------------------------------------------------
        // add standard grading elements...
        if (! $this->_features->rating || $this->_features->gradecat) {
            $mform->addElement('header', 'modstandardgrade', get_string('grade'));
        }
        
        // if supports grades and grades arent being handled via ratings
        if (! $this->_features->rating) {
            $mform->addElement('select', 'grademin', get_string('grademin', 'grades'), $numbers1to100);
            $mform->setDefault('grademin', 1);
            
            $mform->addElement('select', 'grade', get_string('grademax', 'grades'), $numbers1to100);
            $mform->setDefault('grade', 7);
        }
        
        if ($this->_features->advancedgrading and ! empty($this->current->_advancedgradingdata['methods']) and ! empty($this->current->_advancedgradingdata['areas'])) {
            
            if (count($this->current->_advancedgradingdata['areas']) == 1) {
                // if there is just one gradable area (most cases), display just the selector
                // without its name to make UI simplier
                $areadata = reset($this->current->_advancedgradingdata['areas']);
                $areaname = key($this->current->_advancedgradingdata['areas']);
                // Regrade settings, dates and enabling
                $mform->addElement('hidden', 'advancedgradingmethod_' . $areaname, 'rubric');
                $mform->setType('advancedgradingmethod_' . $areaname, PARAM_ALPHA);
                
                /*
                 * $mform->addElement('select', 'advancedgradingmethod_' . $areaname, get_string('gradingmethod', 'core_grading'), array(
                 * null => "sIMPLE GRADING",
                 * 'rubric' => "Rubrica"
                 * ));
                 * $mform->addHelpButton('advancedgradingmethod_' . $areaname, 'gradingmethod', 'core_grading');
                 * $mform->setAdvanced('advancedgradingmethod_' . $areaname);
                 * $mform->disabledIf('advancedgradingmethod_' . $areaname, 'type', 'eq', '2');
                 */
            } else {
                throw new Exception("The emarking module should not define more than one grading area");
            }
        }
        
        if ($this->_features->gradecat) {
            $mform->addElement('select', 'gradecat', get_string('gradecategoryonmodform', 'grades'), grade_get_categories_menu($COURSE->id, $this->_outcomesused));
            $mform->addHelpButton('gradecat', 'gradecategoryonmodform', 'grades');
            $mform->setAdvanced('gradecat');
            $mform->disabledIf('gradecat', 'type', 'eq', '2');
        }
        
        // Regrade settings, dates and enabling
        $mform->addElement('checkbox', 'adjustslope', get_string('adjustslope', 'mod_emarking'));
        $mform->addHelpButton('adjustslope', 'adjustslope', 'mod_emarking');
        $mform->setAdvanced('adjustslope');
        $mform->disabledIf('adjustslope', 'type', 'eq', '2');
        
        $mform->addElement('text', 'adjustslopegrade', get_string('adjustslopegrade', 'mod_emarking'), array(
            'size' => '5'
        ));
        $mform->setType('adjustslopegrade', PARAM_FLOAT);
        $mform->setDefault('adjustslopegrade', 0);
        $mform->addHelpButton('adjustslopegrade', 'adjustslopegrade', 'mod_emarking');
        $mform->disabledIf('adjustslopegrade', 'adjustslope');
        $mform->setAdvanced('adjustslopegrade');
        $mform->disabledIf('adjustslopegrade', 'type', 'eq', '2');
        
        $mform->addElement('text', 'adjustslopescore', get_string('adjustslopescore', 'mod_emarking'), array(
            'size' => '5'
        ));
        $mform->setType('adjustslopescore', PARAM_FLOAT);
        $mform->setDefault('adjustslopescore', 0);
        $mform->addHelpButton('adjustslopescore', 'adjustslopescore', 'mod_emarking');
        $mform->disabledIf('adjustslopescore', 'adjustslope');
        $mform->setAdvanced('adjustslopescore');
        $mform->disabledIf('adjustslopescore', 'type', 'eq', '2');
        
        // -------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        
        // Regrade settings, dates and enabling
        $mform->addElement('hidden', 'heartbeatenabled', true);
        $mform->setType('heartbeatenabled', PARAM_BOOL);
        
        $mform->addElement('hidden', 'downloadrubricpdf', true);
        $mform->setType('downloadrubricpdf', PARAM_BOOL);
        
        // If we are in editing mode we can not change the type anymore
        if ($this->_instance) {
            $freeze = array();
            if ($emarking->type == EMARKING_TYPE_MARKER_TRAINING) {
                $freeze[] = 'type';
            }
            if ($emarking->type == EMARKING_TYPE_NORMAL) {
                $freeze[] = 'qualitycontrol';
            }
            if ($exam && $exam->status >= EMARKING_EXAM_SENT_TO_PRINT) {
                $freeze[] = 'printlist';
                $freeze[] = 'printdoublesided';
                $freeze[] = 'headerqr';
                $freeze[] = 'extrasheets';
                $freeze[] = 'extraexams';
                $freeze[] = 'enrolments';
                if($mform->elementExists('multicourse')) {
                    $freeze[] = 'multicourse';
                }
            }
            $mform->freeze($freeze);
        }
        
        // -------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
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
            
            $exam = $DB->get_record("emarking_exams", array(
                "emarking" => $this->_instance
            ));
            if ($exam) {
                $default_values["examdate"] = $exam->examdate;
                $default_values["printlist"] = $exam->printlist;
                $default_values["printdoublesided"] = $exam->usebackside;
                $default_values["headerqr"] = $exam->headerqr;
                $default_values["extrasheets"] = $exam->extrasheets;
                $default_values["extraexams"] = $exam->extraexams;
                
                $enrolavailables = $this->getAvailableEnrolments();
                
                // If we are editing, we use the previous enrolments
                if (isset($exam->enrolments)) {
                    $enrolincludes = explode(",", $exam->enrolments);
                    foreach ($enrolincludes as $enroldefault) {
                        if (in_array($enroldefault, $enrolavailables)) {
                            $default_values["enrolments[$enroldefault]"] = true;
                        }
                    }
                    // If we are creating a new one, the default comes from the plugin settings
                } else 
                    if ($CFG->emarking_enrolincludes && strlen($CFG->emarking_enrolincludes) > 1) {
                        $enrolincludes = explode(",", $CFG->emarking_enrolincludes);
                        foreach ($enrolincludes as $enroldefault) {
                            if (in_array($enroldefault, $enrolavailables)) {
                                $default_values["enrolments[$enroldefault]"] = true;
                            }
                        }
                    }
            }
        }
    }

    function validation($data, $files)
    {
        global $CFG, $COURSE, $USER, $DB;
        
        require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
        
        if ($data["type"] < 0 || $data["type"] > 5) {
            return array(
                "type" => get_string("markingtypemandatory", "mod_emarking")
            );
        }
        
        // Calculates context for validating permissions
        // If we have the module available, we use it, otherwise we fallback to course
        $ctx = context_course::instance($COURSE->id);
        if ($this->current && $this->current->coursemodule) {
            $cm = get_coursemodule_from_id('emarking', $this->current->module, $COURSE->id);
            if ($cm) {
                $ctx = context_module::instance($cm->id);
            }
        }
        
        $errors = array();
        
        // Verify that we have enough markers
        if ($data['type'] == EMARKING_TYPE_MARKER_TRAINING) {
            
            // Get all users with permission to grade in emarking
            $markers = get_enrolled_users($ctx, 'mod/emarking:grade');
            $totalmarkers = 0;
            foreach ($markers as $marker) {
                if (has_capability('mod/emarking:supervisegrading', $ctx, $marker)) {
                    continue;
                }
                $totalmarkers ++;
            }
            
            if ($totalmarkers < 2) {
                $errors['type'] = get_string('notenoughmarkersfortraining', 'mod_emarking');
                return;
            }
        }
        
        // Get the exam if we are updating an emarking activity
        $exam = null;
        if (isset($data['exam']) && $data['exam'] > 0) {
            if(!$exam = $DB->get_record("emarking_exams", array("id"=>$data["exam"]))) {
                $errors["exam"] = "Invalid data from form";
            }
        }

        // If there is no associated exam yet
        if (! $exam) {
            // The exam date comes from the date selector
            $examdate = new DateTime();
            $examdate->setTimestamp(usertime($data['examdate']));
            
            // Day of week from 0 Sunday to 6 Saturday
            $examw = date("w", $examdate->getTimestamp());
            // Hour of the day un 00 to 23 format
            $examh = date("H", $examdate->getTimestamp());
            
            // If minimum days for printing is enabled
            if (isset($CFG->emarking_minimumdaysbeforeprinting) && $CFG->emarking_minimumdaysbeforeprinting > 0) {
                
                // Sundays are forbidden, saturdays from 6am to 4pm TODO: Move this settings to eMarking settings
                if ($examw == 0 || ($examw == 6 && ($examh < 6 || $examh > 16))) {
                    $errors['examdate'] = get_string('examdateinvaliddayofweek', 'mod_emarking');
                }
                
                // User date. Important because the user sees a date selector based on her timezone settings, not the server's
                $date = usertime(time());
                
                // Today is the date according to the user's timezone
                $today = new DateTime();
                $today->setTimestamp($date);
                
                // We have a minimum difference otherwise we wouldn't be in this part of the code
                $mindiff = intval($CFG->emarking_minimumdaysbeforeprinting);
                
                // If today is saturday or sunday, demand for a bigger difference
                $todayw = date("w", $today->getTimestamp());
                $todayw = $todayw ? $todayw : 7;
                
                if ($todayw > 5) {
                    $mindiff += $todayw - 5;
                }
                
                // DateInterval calculated with diff
                $diff = $today->diff($examdate, false);
                
                // The difference using the invert from DateInterval so we know it is in the past
                $realdiff = $diff->days * ($diff->invert ? - 1 : 1);
                
                // If the difference is not enough, show an error
                if ($realdiff < $mindiff) {
                    $a = new stdClass();
                    $a->mindays = $mindiff;
                    $errors['examdate'] = get_string('examdateinvalid', 'mod_emarking', $a);
                }
            }
        }
        
        // If there's no previous exam to associate, and we are creating a new
        // e-marking, we need the PDF file
        if ($data["exam"] == 0 && ! $this->_instance) {
            
            // We get the draftid from the form
            $draftid = file_get_submitted_draft_itemid('exam_files');
            $usercontext = context_user::instance($USER->id);
            $fs = get_file_storage();
            $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftid);
            
            $tempdir = emarking_get_temp_dir_path($COURSE->id);
            emarking_initialize_directory($tempdir, true);
            
            $numpagesprevious = - 1;
            $exampdfs = array();
            foreach ($files as $uploadedfile) {
                if ($uploadedfile->get_mimetype() !== 'application/pdf')
                    continue;
                
                $filename = $uploadedfile->get_filename();
                $filename = emarking_clean_filename($filename);
                $newfilename = $tempdir . '/' . $filename;
                
                $pdffile = emarking_get_path_from_hash($tempdir, $uploadedfile->get_pathnamehash());
                
                // Executes pdftk burst to get all pages separated
                $numpages = emarking_pdf_count_pages($newfilename, $tempdir, false);
                
                if (! is_numeric($numpages) || $numpages < 1) {
                    $errors["exam_files"] = get_string('invalidpdfnopages', 'mod_emarking');
                    return $errors;
                }
                
                if ($numpagesprevious >= 0 && $numpagesprevious != $numpages) {
                    $errors["exam_files"] = get_string('invalidpdfnumpagesforms', 'mod_emarking');
                    return $errors;
                }
                
                $exampdfs[] = array(
                    'pathname' => $pdffile,
                    'filename' => $filename
                );
            }
            
            if (count($exampdfs) == 0) {
                $errors["exam_files"] = get_string('invalidpdfnopages', 'mod_emarking');
                return $errors;
            }
        }
        
        // Validate the adjusted slope
        $adjustslope = isset($data['adjustslope']) ? $data['adjustslope'] : false;
        $adjustslopescore = isset($data['adjustslopescore']) ? $data['adjustslopescore'] : 0;
        $adjustslopegrade = isset($data['adjustslopegrade']) ? $data['adjustslopegrade'] : 0;
        $grademin = $data['grademin'];
        $grademax = $data['grade'];
        $regradesopendate = $data['regradesopendate'];
        $regradesclosedate = $data['regradesclosedate'];
        
        // Make sure the minimum score is not greater than the maximum score
        if ($grademin >= $grademax) {
            $errors['grademin'] = get_string('gradescheck', 'mod_emarking');
            $errors['grade'] = get_string('gradescheck', 'mod_emarking');
        }
        
        // If we are adjusting the slope
        if ($adjustslope) {
            // Make sure the grade is greater than the minimum grade
            if ($adjustslopegrade <= $grademin) {
                $errors['adjustslopegrade'] = get_string('adjustslopegrademustbegreaterthanmin', 'mod_emarking');
            }
            
            // Make sure the grade is lower than the maximum grade
            if ($adjustslopegrade > $grademax) {
                $errors['adjustslopegrade'] = get_string('adjustslopegrademustbelowerthanmax', 'mod_emarking');
            }
            
            // And that the score for adjusting is greater than 0
            if ($adjustslopescore <= 0) {
                $errors['adjustslopescore'] = get_string('adjustslopescoregreaterthanzero', 'mod_emarking');
            }
        }
        
        // Validate regrade dates
        if ($regradesopendate > $regradesclosedate) {
            $errors['regradesopendate'] = get_string('verifyregradedate', 'mod_emarking');
            $errors['regradesclosedate'] = get_string('verifyregradedate', 'mod_emarking');
        }
        
        // Validate custom marks
        $custommarks = isset($data['custommarks']) ? $data['custommarks'] : '';
        $custommarks = str_replace('\r\n', '\n', $custommarks);
        
        if (strlen($custommarks) > 0) {
            $parts = explode("\n", $custommarks);
            $linenumber = 0;
            foreach ($parts as $line) {
                $linenumber ++;
                if (strlen(trim($line)) == 0)
                    continue;
                
                $subparts = explode("#", $line);
                if (count($subparts) != 2) {
                    if (! isset($errors['custommarks'])) {
                        $errors['custommarks'] = get_string('invalidcustommarks', 'mod_emarking');
                    }
                    $errors['custommarks'] .= "$linenumber ";
                }
            }
        }
        
        $qualitycontrol = isset($data['enablequalitycontrol']) ? $data['enablequalitycontrol'] : false;
        if ($data['type'] == EMARKING_TYPE_NORMAL && $qualitycontrol) {
            // Get all users with permission to grade in emarking
            // Get all users with permission to grade in emarking
            $markers = get_enrolled_users($ctx, 'mod/emarking:grade');
            $totalmarkers = 0;
            foreach ($markers as $marker) {
                if (isset($data['marker-' . $marker->id])) {
                    $totalmarkers ++;
                }
            }
            if ($totalmarkers == 0)
                $errors['markers'] = get_string('notenoughmarkersforqualitycontrol', 'mod_emarking');
        }
        
        if ($data["exam"] > 0 && $this->_instance) {
            $previousexam = $DB->get_record("emarking_exams", array(
                "emarking" => $this->_instance
            ));
            if ($previousexam && ($previousexam->id != $data["exam"])) {
                $errors["exam"] = "An exam is already assigned for this emarking activity";
            }
        }
        
        return $errors;
    }

    function getAvailableEnrolments()
    {
        global $COURSE;
        
        // Enrolment methods to include in printing
        $enrolavailables = array();
        $enrolments = enrol_get_instances($COURSE->id, true);
        foreach ($enrolments as $enrolment) {
            if (! in_array($enrolment->enrol, $enrolavailables)) {
                $enrolavailables[] = $enrolment->enrol;
            }
        }
        
        return $enrolavailables;
    }

    function display()
    {
        parent::display();
        
        echo "<script>
	        function showFullForm() {
	           var e = document.getElementById('id_type');
               var strUser = e.options[e.selectedIndex].value;
	           if (strUser == '0') {
                    document.getElementById('id_print').style.display = 'block';
                    document.getElementById('id_scan').style.display = 'none';
                    document.getElementById('id_osm').style.display = 'none';
                    document.getElementById('id_markerstraining').style.display = 'none';
                    document.getElementById('id_modstandardgrade').style.display = 'none';
                    document.getElementById('id_modstandardelshdr').style.display = 'block';
                } else if (strUser == '1') {
                    document.getElementById('id_print').style.display = 'block';
                    document.getElementById('id_scan').style.display = 'block';
                    document.getElementById('scanisenabled').style.display = 'none';
                    document.getElementById('osmisenabled').style.display = 'block';
                    document.getElementById('id_osm').style.display = 'block';
                    document.getElementById('id_markerstraining').style.display = 'none';
                    document.getElementById('id_modstandardgrade').style.display = 'block';
                    document.getElementById('id_modstandardelshdr').style.display = 'block';
                } else if(strUser == '2') {
                    document.getElementById('id_print').style.display = 'none';
	                document.getElementById('id_scan').style.display = 'none';
                    document.getElementById('id_osm').style.display = 'none';
                    document.getElementById('id_markerstraining').style.display = 'block';
                    document.getElementById('id_modstandardgrade').style.display = 'none';
                    document.getElementById('id_modstandardelshdr').style.display = 'block';
                } else if(strUser == '5') {
                    document.getElementById('id_print').style.display = 'block';
	                document.getElementById('id_scan').style.display = 'block';
                    document.getElementById('scanisenabled').style.display = 'block';
                    document.getElementById('osmisenabled').style.display = 'none';
                    document.getElementById('id_osm').style.display = 'none';
                    document.getElementById('id_markerstraining').style.display = 'none';
                    document.getElementById('id_modstandardgrade').style.display = 'none';
                    document.getElementById('id_modstandardelshdr').style.display = 'block';
                } else {
                    document.getElementById('id_print').style.display = 'none';
                    document.getElementById('id_scan').style.display = 'none';
                    document.getElementById('id_experimental').style.display = 'none';
                    document.getElementById('id_marking').style.display = 'none';
                    document.getElementById('id_regrade').style.display = 'none';
                    document.getElementById('id_modstandardgrade').style.display = 'none';
                    document.getElementById('id_modstandardelshdr').style.display = 'none';
                }
                
            document.getElementById('fitem_id_introeditor').style.display = 'none';
            document.getElementById('id_submitbutton2').style.display = 'none';
            
	       }
            showFullForm();
	        </script>";
        
        // #id_experimental, #id_marking, #id_regrade, #id_modstandardgrade, #id_modstandardelshdr
        echo "<script>
	        function previousExamUpdate() {
	           var e = document.getElementById('id_exam');
               var strUser = e.options[e.selectedIndex].value;
	           if (strUser == '0') {
                    document.getElementById('fitem_id_exam_files').style.display = 'block';
                    document.getElementById('fitem_id_examdate').style.display = 'block';
	                document.getElementById('fitem_id_printlist').style.display = 'block';
                    document.getElementById('fitem_id_printdoublesided').style.display = 'block';
                    document.getElementById('fitem_id_headerqr').style.display = 'block';
                    document.getElementById('fitem_id_extrasheets').style.display = 'block';
                    document.getElementById('fitem_id_extraexams').style.display = 'block';
                    document.getElementById('fgroup_id_multicourse').style.display = 'block';
                    document.getElementById('fgroup_id_enrolments').style.display = 'block';
                    document.getElementById('fitem_id_selectall').style.display = 'block';
                    document.getElementById('fitem_id_deselectall').style.display = 'block';
                    document.getElementById('id_print').getElementsByClassName('moreless-actions')[0].style.display = 'block';
                } else {
                    document.getElementById('fitem_id_exam_files').style.display = 'none';
                    document.getElementById('fitem_id_examdate').style.display = 'none';
	                document.getElementById('fitem_id_printlist').style.display = 'none';
                    document.getElementById('fitem_id_printdoublesided').style.display = 'none';
                    document.getElementById('fitem_id_headerqr').style.display = 'none';
                    document.getElementById('fitem_id_extrasheets').style.display = 'none';
                    document.getElementById('fitem_id_extraexams').style.display = 'none';
                    document.getElementById('fgroup_id_multicourse').style.display = 'none';
                    document.getElementById('fgroup_id_enrolments').style.display = 'none';
                    document.getElementById('fitem_id_selectall').style.display = 'none';
                    document.getElementById('fitem_id_deselectall').style.display = 'none';
                    document.getElementById('id_print').getElementsByClassName('moreless-actions')[0].style.display = 'none';
                }
	       }
	        </script>";
        
        echo $this->extraScript;
    }
}
