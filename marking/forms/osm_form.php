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
 * The main emarking configuration form
 * It uses the standard core Moodle formslib.
 * For more info about them, please
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
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->dirroot . '/repository/lib.php');
/**
 * Module instance settings form
 */
class emarking_osm_form extends moodleform {
    /**
     * Defines forms elements
     */
    public function definition() {
        global $COURSE, $DB, $CFG, $USER;
        $mform = $this->_form;
        $instance = $this->_customdata;
        $context = $instance ['context'];
        $cmid = $instance ['id'];
        $emarking = $instance ['emarking'];
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'type', $emarking->type);
        $mform->setType('type', PARAM_INT);
        // Today.
        $date = new DateTime();
        $date->setTimestamp(usertime(time()));
        // Students can see peers answers.
        $ynoptions = array(
            0 => get_string('no'),
            1 => get_string('yespeerisanonymous', 'mod_emarking'));
        $mform->addElement('select', 'peervisibility', get_string('viewpeers', 'mod_emarking'), $ynoptions);
        $mform->addHelpButton('peervisibility', 'viewpeers', 'mod_emarking');
        $mform->setDefault('peervisibility', 0);
        $mform->setType('peervisibility', PARAM_INT);
        $mform->disabledIf('peervisibility', 'type', 'eq', '2');
        // Expected pages for submissions.
        $mform->addElement('hidden', 'totalpages', 0);
        $mform->setType('totalpages', PARAM_INT);
        // Anonymous eMarking setting.
        $anonymousoptions = array(
            0 => get_string('studentanonymous_markervisible', 'mod_emarking'),
            1 => get_string('studentanonymous_markeranonymous', 'mod_emarking'),
            2 => get_string('studentvisible_markervisible', 'mod_emarking'),
            3 => get_string('studentvisible_markeranonymous', 'mod_emarking'));
        if (has_capability('mod/emarking:manageanonymousmarking', $context)) {
            $mform->addElement('select', 'anonymous', get_string('anonymous', 'mod_emarking'), $anonymousoptions);
            $mform->addHelpButton('anonymous', 'anonymous', 'mod_emarking');
        } else {
            $mform->addElement('hidden', 'anonymous');
        }
        $mform->setDefault('anonymous', 0);
        $mform->setType('anonymous', PARAM_INT);
        $mform->disabledIf('anonymous', 'type', 'eq', '2');
        // Justice perception eMarking setting.
        $justiceoptions = array(
            EMARKING_JUSTICE_DISABLED => get_string('justicedisabled', 'mod_emarking'),
            EMARKING_JUSTICE_PER_SUBMISSION => get_string('justicepersubmission', 'mod_emarking'),
            EMARKING_JUSTICE_PER_CRITERION => get_string('justicepercriterion', 'mod_emarking'));
        $mform->addElement('select', 'justiceperception', get_string('justiceperception', 'mod_emarking'), $justiceoptions);
        $mform->addHelpButton('justiceperception', 'justiceperception', 'mod_emarking');
        $mform->setDefault('justiceperception', 0);
        $mform->setType('justiceperception', PARAM_INT);
        $mform->disabledIf('justiceperception', 'type', 'eq', '2');
        $mform->addElement('checkbox', 'linkrubric', get_string('linkrubric', 'mod_emarking'));
        $mform->addHelpButton('linkrubric', 'linkrubric', 'mod_emarking');
        $mform->addElement('checkbox', 'collaborativefeatures', get_string('collaborativefeatures', 'mod_emarking'));
        $mform->addHelpButton('collaborativefeatures', 'collaborativefeatures', 'mod_emarking');
        // Custom marks.
        if (has_capability('mod/emarking:managespecificmarks', $context)) {
            $mform->addElement('textarea', 'custommarks', get_string('specificmarks', 'mod_emarking'),
                    array(
                        'rows' => 5,
                        'cols' => 100,
                        'class' => 'smalltext'));
            $mform->addHelpButton('custommarks', 'specificmarks', 'mod_emarking');
        } else {
            $mform->addElement('hidden', 'custommarks');
        }
        $mform->setDefault('custommarks', '');
        $mform->setType('custommarks', PARAM_TEXT);
        $mform->setAdvanced('custommarks');
        $mform->disabledIf('custommarks', 'type', 'eq', '2');
        // Due date settings.
        $mform->addElement('checkbox', 'qualitycontrol', get_string('enablequalitycontrol', 'mod_emarking'));
        $mform->addHelpButton('qualitycontrol', 'enablequalitycontrol', 'mod_emarking');
        $mform->setAdvanced('qualitycontrol');
        $mform->disabledIf('qualitycontrol', 'type', 'eq', '2');
        // Get all users with permission to grade in emarking.
        $markers = get_enrolled_users($context, 'mod/emarking:grade');
        $chkmarkers = array();
        foreach ($markers as $marker) {
            $chkmarkers [] = $mform->createElement('checkbox', 'marker-' . $marker->id, null,
                    $marker->firstname . " " . $marker->lastname);
        }
        // Add markers group as checkboxes.
        $mform->addGroup($chkmarkers, 'markers', get_string('markersqualitycontrol', 'mod_emarking'),
                array(
                    '<br />'), false);
        $mform->addHelpButton('markers', 'markersqualitycontrol', 'mod_emarking');
        $mform->setType('markers', PARAM_INT);
        $mform->disabledIf('markers', 'qualitycontrol');
        $mform->setAdvanced('markers');
        $mform->disabledIf('markers', 'type', 'eq', '2');
        // Due date settings.
        $mform->addElement('checkbox', 'enableduedate', get_string('enableduedate', 'mod_emarking'));
        $mform->setAdvanced('enableduedate');
        $mform->addElement('date_time_selector', 'markingduedate', get_string('markingduedate', 'mod_emarking'),
                array(
                    'startyear' => date('Y'),
                    'stopyear' => date('Y') + 1,
                    'step' => 5,
                    'defaulttime' => $date->getTimestamp(),
                    'optional' => false), null);
        $mform->addHelpButton('markingduedate', 'markingduedate', 'mod_emarking');
        $mform->setAdvanced('markingduedate');
        $mform->disabledIf('markingduedate', 'enableduedate');
        // Regrade settings, dates and enabling.
        $mform->addElement('checkbox', 'regraderestrictdates', get_string('regraderestrictdates', 'mod_emarking'));
        $mform->addHelpButton('regraderestrictdates', 'regraderestrictdates', 'mod_emarking');
        $mform->setAdvanced('regraderestrictdates');
        $mform->disabledIf('regraderestrictdates', 'type', 'eq', '2');
        $mform->addElement('date_time_selector', 'regradesopendate', get_string('regradesopendate', 'mod_emarking'),
                array(
                    'startyear' => date('Y'),
                    'stopyear' => date('Y') + 1,
                    'step' => 5,
                    'defaulttime' => $date->getTimestamp(),
                    'optional' => false), null);
        $mform->addHelpButton('regradesopendate', 'regradesopendate', 'mod_emarking');
        $mform->setAdvanced('regradesopendate');
        $mform->disabledIf('regradesopendate', 'regraderestrictdates');
        $mform->disabledIf('regradesopendate', 'type', 'eq', '2');
        $date->modify('+2 months');
        $mform->addElement('date_time_selector', 'regradesclosedate', get_string('regradesclosedate', 'mod_emarking'),
                array(
                    'startyear' => date('Y'),
                    'stopyear' => date('Y') + 1,
                    'step' => 5,
                    'defaulttime' => $date->getTimestamp(),
                    'optional' => false), null);
        $mform->addHelpButton('regradesclosedate', 'regradesclosedate', 'mod_emarking');
        $mform->setAdvanced('regradesclosedate');
        $mform->disabledIf('regradesclosedate', 'regraderestrictdates');
        $mform->disabledIf('regradesclosedate', 'type', 'eq', '2');
        // Buttons.
        $this->add_action_buttons(true, get_string('submit'));
    }
    public function data_preprocessing(&$defaultvalues) {
        global $DB;
        parent::data_preprocessing($defaultvalues);
        if ($this->_instance) {
            $markers = $DB->get_records('emarking_markers', array(
                'emarking' => $this->_instance));
            foreach ($markers as $marker) {
                $defaultvalues ['marker-' . $marker->marker] = 1;
            }
        }
        $defaultvalues ["id"] = $this->_customdata ["id"];
    }
    public function validation($data, $files) {
        global $CFG, $COURSE, $USER, $DB;
        require_once($CFG->dirroot . "/mod/emarking/print/locallib.php");
        // Calculates context for validating permissions.
        // If we have the module available, we use it, otherwise we fallback to course.
        $ctx = context_course::instance($COURSE->id);
        $errors = array();
        // Validate regrade dates.
        if ($data ['regradesopendate'] > $data ['regradesclosedate']) {
            $errors ['regradesopendate'] = get_string('verifyregradedate', 'mod_emarking');
            $errors ['regradesclosedate'] = get_string('verifyregradedate', 'mod_emarking');
        }
        // Validate custom marks.
        $custommarks = isset($data ['custommarks']) ? $data ['custommarks'] : '';
        $custommarks = str_replace('\r\n', '\n', $custommarks);
        if (strlen($custommarks) > 0) {
            $parts = explode("\n", $custommarks);
            $linenumber = 0;
            foreach ($parts as $line) {
                $linenumber ++;
                if (strlen(trim($line)) == 0) {
                    continue;
                }
                $subparts = explode("#", $line);
                if (count($subparts) != 2) {
                    if (! isset($errors ['custommarks'])) {
                        $errors ['custommarks'] = get_string('invalidcustommarks', 'mod_emarking');
                    }
                    $errors ['custommarks'] .= "$linenumber ";
                }
            }
        }
        $qualitycontrol = isset($data ['enablequalitycontrol']) ? $data ['enablequalitycontrol'] : false;
        if ($data ['type'] == EMARKING_TYPE_NORMAL && $qualitycontrol) {
            // Get all users with permission to grade in emarking.
            $markers = get_enrolled_users($ctx, 'mod/emarking:grade');
            $totalmarkers = 0;
            foreach ($markers as $marker) {
                if (isset($data ['marker-' . $marker->id])) {
                    $totalmarkers ++;
                }
            }
            if ($totalmarkers == 0) {
                $errors ['markers'] = get_string('notenoughmarkersforqualitycontrol', 'mod_emarking');
            }
        }
        return $errors;
    }
}