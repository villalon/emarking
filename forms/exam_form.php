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
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2012 Marcelo Epuyao, Jorge Villalón <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
class emarking_exam_form extends moodleform {
    // Extra HTML to be added at the end of the form, used for javascript functions.
    private $extrascript = "";
    public function definition() {
        global $DB, $CFG;
        // Verifies that the global configurated logo exists, and if it's new it copies it
        // to a normal space within Moodle's filesystem.
        emarking_verify_logo();
        $mform = $this->_form;
        $instance = $this->_customdata;
        $cmid = $instance ['cmid'];
        $courseid = $instance ['courseid'];
        $examid = $instance ['examid'];
        // Multicourse.
        // Get the course record to get the shortname.
        $course = $DB->get_record('course', array(
            'id' => $courseid));
        $exam = $DB->get_record('emarking_exams', array(
            'id' => $examid));
        // Exam id goes hidden.
        $mform->addElement('hidden', 'id', $examid);
        $mform->setType('id', PARAM_INT);
        // Course module id goes hidden as well.
        $mform->addElement('hidden', 'cm', $cmid);
        $mform->setType('cm', PARAM_INT);
        // Course id goes hidden as well.
        $mform->addElement('hidden', 'course', $courseid);
        $mform->setType('course', PARAM_INT);
        // Exam totalpages goes hidden as well.
        $mform->addElement('hidden', 'totalpages');
        $mform->setType('totalpages', PARAM_INT);
        $mform->addElement('header', 'exam_title', get_string('examinfo', 'mod_emarking'));
        $mform->addElement('static', 'coursename', get_string('course'), $course->fullname);
        // Exam name.
        $mform->addElement('text', 'name', get_string('examname', 'mod_emarking'));
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 50), 'maxlength', 50, 'client');
        $mform->setType('name', PARAM_TEXT);
        $mform->addHelpButton('name', 'examname', 'mod_emarking');
        $date = new DateTime();
        $date->setTimestamp(usertime(time()));
        $date->modify('+2 days');
        $date->modify('+10 minutes');
        $examw = date("w", $date->getTimestamp());
        // Sundays and saturdays shouldn't be selected by default.
        if ($examw == 0) {
            $date->modify('+1 days');
        }
        if ($examw == 6) {
            $date->modify('+2 days');
        }
        // Exam date.
        $mform->addElement('date_time_selector', 'examdate', get_string('examdate', 'mod_emarking'),
                array(
                    'startyear' => date('Y'),
                    'stopyear' => date('Y') + 1,
                    'step' => 5,
                    'defaulttime' => $date->getTimestamp(),
                    'optional' => false), $instance ['options']);
        $mform->addRule('examdate', get_string('filerequiredpdf', 'mod_emarking'), 'required', null, 'client');
        $mform->addHelpButton('examdate', 'examdate', 'mod_emarking');
        // Exam PDF file.
        $mform->addElement('filemanager', 'exam_files', get_string('pdffile', 'mod_emarking'), null,
                array(
                    'subdirs' => 0,
                    'maxbytes' => 0,
                    'maxfiles' => 10,
                    'accepted_types' => array(
                        '.pdf'),
                    'return_types' => FILE_INTERNAL));
        $mform->addRule('exam_files', get_string('filerequiredtosendnewprintorder', 'mod_emarking'), 'required', null, 'client');
        $mform->setType('exam_files', PARAM_FILE);
        $mform->addHelpButton('exam_files', 'pdffile', 'mod_emarking');
        // Copy center instructions.
        $mform->addElement('header', 'exam_title', get_string('copycenterinstructions', 'mod_emarking'));
        // Numbers from 0 to 3 for extra exams and sheets.
        $numberarray = array();
        for ($j = 0; $j < 3; $j ++) {
            $numberarray [$j] = $j;
        }
        // Print students list.
        $mform->addElement('checkbox', 'printlist', get_string('printlist', 'mod_emarking'));
        $mform->setType('printlist', PARAM_BOOL);
        $mform->addHelpButton('printlist', 'printlist', 'mod_emarking');
        $mform->setDefault('printlist', false);
        // Print double sided.
        $mform->addElement('checkbox', 'printdoublesided', get_string('printdoublesided', 'mod_emarking'));
        $mform->setType('printdoublesided', PARAM_BOOL);
        $mform->setDefault('printdoublesided', false);
        // Personalized header (using QR).
        $mform->addElement('checkbox', 'headerqr', get_string('headerqr', 'mod_emarking'));
        $mform->setType('headerqr', PARAM_BOOL);
        $mform->addHelpButton('headerqr', 'headerqr', 'mod_emarking');
        $mform->setDefault('headerqr', true);
        $mform->disabledIf('headerqr', 'printdoublesided', 'checked');
        // Extra sheets per student.
        $mform->addElement('select', 'extrasheets', get_string('extrasheets', 'mod_emarking'), $numberarray, null);
        $mform->addHelpButton('extrasheets', 'extrasheets', 'mod_emarking');
        // Extra students.
        $mform->addElement('select', 'extraexams', get_string('extraexams', 'mod_emarking'), $numberarray, null);
        $mform->addHelpButton('extraexams', 'extraexams', 'mod_emarking');
        // Obtain parallel courses.
        if ($parallelcourses = emarking_get_parallel_courses($course)) {
            // Add a checkbox for each parallel course.
            $checkboxes = array();
            foreach ($parallelcourses as $course) {
                $checkboxes [] = $mform->createElement('checkbox', $course->shortname, null, $course->fullname, 'checked');
            }
            // If there's any parallel course we add the multicourse option.
            if (count($checkboxes) > 0) {
                $mform->addGroup($checkboxes, 'multisecciones', get_string('multicourse', 'mod_emarking'),
                        array(
                            '<br/>'), true);
                $mform->addHelpButton('multisecciones', 'multicourse', 'mod_emarking');
                if ($examid == 0) {
                    $mform->addElement('button', 'selectall', get_string('selectall', 'mod_emarking'),
                            array(
                                'onClick' => 'selectAllCheckboxes(this.form,true);'));
                    $mform->addElement('button', 'deselectall', get_string('selectnone', 'mod_emarking'),
                            array(
                                'onClick' => 'selectAllCheckboxes(this.form,false);'));
                } else {
                    foreach ($seccionesparalelas as $cid => $course) {
                        $selected = false;
                        if ($examid > 0 && $parallel = $DB->get_record('emarking_exams',
                                array(
                                    'file' => $exam->file,
                                    'course' => $cid))) {
                            $selected = true;
                        }
                        $mform->setType("multisecciones[$course->shortname]", PARAM_BOOL);
                        if ($selected) {
                            $mform->setDefault("multisecciones[$course->shortname]", true);
                        }
                    }
                }
            }
            $this->extrascript .= "<script>function selectAllCheckboxes(form,checked) { " .
                     "for (var i = 0; i < form.elements.length; i++ ) { " .
                     "    if (form.elements[i].type == 'checkbox' && form.elements[i].id.indexOf('multiseccion') > 0) { " .
                     "        form.elements[i].checked = checked; " . "    } " . "} " . "}</script>";
        }
        $mform->addElement('hidden', 'action', 'uploadfile');
        $mform->setType('action', PARAM_ALPHA);
        // Copy center instructions.
        $mform->addElement('header', 'advanced_settings', get_string('advanced', 'mod_emarking'));
        // Enrolment methods to include in printing.
        $enrolcheckboxes = array();
        $enrolavailables = array();
        $enrolments = enrol_get_instances($courseid, true);
        $flag = 0;
        foreach ($enrolments as $enrolment) {
            if ($enrolment->enrol == "meta") {
                if ($flag == 0) {
                    $flag = 1;
                    $enrolavailables [] = $enrolment->enrol;
                    $enrolcheckboxes [] = $mform->createElement('checkbox', $enrolment->enrol, null,
                            get_string('enrol' . $enrolment->enrol, 'mod_emarking'), 'checked');
                }
            } else {
                $enrolavailables [] = $enrolment->enrol;
                $enrolcheckboxes [] = $mform->createElement('checkbox', $enrolment->enrol, null,
                        get_string('enrol' . $enrolment->enrol, 'mod_emarking'), 'checked');
            }
        }
        $mform->addGroup($enrolcheckboxes, 'enrolments', get_string('includestudentsinexam', 'mod_emarking'),
                array(
                    '<br/>'), true);
        // If we are editing, we use the previous enrolments.
        if ($examid > 0 && isset($exam->enrolments)) {
            $enrolincludes = explode(",", $exam->enrolments);
            foreach ($enrolincludes as $enroldefault) {
                if (in_array($enroldefault, $enrolavailables)) {
                    $mform->setDefault("enrolments[$enroldefault]", true);
                }
            }
            // If we are creating a new one, the default comes from the plugin settings.
        } else if ($CFG->emarking_enrolincludes && strlen($CFG->emarking_enrolincludes) > 1) {
            $enrolincludes = explode(",", $CFG->emarking_enrolincludes);
            foreach ($enrolincludes as $enroldefault) {
                if (in_array($enroldefault, $enrolavailables)) {
                    $mform->setDefault("enrolments[$enroldefault]", true);
                }
            }
        }
        $this->add_action_buttons(true, get_string('submit'));
    }
    public function validation($data, $files) {
        global $CFG;
        $errors = array();
        // The exam date comes from the date selector.
        $examdate = new DateTime();
        $examdate->setTimestamp(usertime($data ['examdate']));
        // Day of week from 0 Sunday to 6 Saturday.
        $examw = date("w", $examdate->getTimestamp());
        // Hour of the day un 00 to 23 format.
        $examh = date("H", $examdate->getTimestamp());
        // Sundays are forbidden, saturdays from 6am to 4pm TODO: Move this settings to eMarking settings.
        if ($examw == 0 || ($examw == 6 && ($examh < 6 || $examh > 16))) {
            $errors ['examdate'] = get_string('examdateinvaliddayofweek', 'mod_emarking');
        }
        // If minimum days for printing is enabled.
        if (isset($CFG->emarking_minimumdaysbeforeprinting) && $CFG->emarking_minimumdaysbeforeprinting > 0) {
            // User date. Important because the user sees a date selector based on her timezone settings, not the server's.
            $date = usertime(time());
            // Today is the date according to the user's timezone.
            $today = new DateTime();
            $today->setTimestamp($date);
            // We have a minimum difference otherwise we wouldn't be in this part of the code.
            $mindiff = intval($CFG->emarking_minimumdaysbeforeprinting);
            // If today is saturday or sunday, demand for a bigger difference.
            $todayw = date("w", $today->getTimestamp());
            $todayw = $todayw ? $todayw : 7;
            if ($todayw > 5) {
                $mindiff += $todayw - 5;
            }
            // DateInterval calculated with diff.
            $diff = $today->diff($examdate, false);
            // The difference using the invert from DateInterval so we know it is in the past.
            $realdiff = $diff->days * ($diff->invert ? - 1 : 1);
            // If the difference is not enough, show an error.
            if ($realdiff < $mindiff) {
                $a = new stdClass();
                $a->mindays = $mindiff;
                $errors ['examdate'] = get_string('examdateinvalid', 'mod_emarking', $a);
            }
        }
        // If print random order within groups.
        if (isset($data ['printrandom']) && $data ['printrandom'] === '1') {
            $groups = groups_get_all_groups($data ["course"]);
            if (count($groups) == 0) {
                $errors ['printrandom'] = get_string('printrandominvalid', 'mod_emarking');
            }
        }
        return $errors;
    }
    public function display() {
        parent::display();
        echo $this->extrascript;
    }
}