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
 * @copyright 2011 onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ($CFG->libdir . '/formslib.php');
require_once ($CFG->dirroot . '/course/lib.php');

class mod_emarking_upload_pdf_form extends moodleform {

    public function definition() {
        global $CFG, $OUTPUT, $COURSE;
        
        // Options for uploading the zip file within the form.
        $options = array(
            'subdirs' => 0,
            'maxbytes' => get_max_upload_file_size($CFG->maxbytes, $COURSE->maxbytes, $COURSE->maxbytes),
            'maxfiles' => 1,
            'accepted_types' => '.pdf',
            'return_types' => FILE_INTERNAL
        );

        $mform = $this->_form;
        $instance = $this->_customdata;
        // Header.
        $mform->addElement('header', 'digitizedfilepdf', get_string('uploadsubmission', 'mod_emarking'));
        $mform->addHelpButton('digitizedfilepdf', 'uploadsubmission', 'mod_emarking');
        // The course module id.
        $mform->addElement('hidden', 'id', $instance['coursemoduleid']);
        $mform->setType('id', PARAM_INT);
        // The course module id.
        $mform->addElement('hidden', 'sid', $instance['studentid']);
        $mform->setType('sid', PARAM_INT);
        // File picker for the digitized answers.
        $mform->addElement('filepicker', 'submissionfile', get_string('uploadexamfilepdf', 'mod_emarking'), null, $options);
        $mform->setType('submissionfile', PARAM_FILE);
        $mform->addHelpButton('submissionfile', 'filerequiredpdf', 'mod_emarking');
        $mform->addRule('submissionfile', get_string('filerequiredpdf', 'mod_emarking'), 'required');
        // Action buttons.
        $this->add_action_buttons(true);
    }

    public function validation($data, $files) {
        global $DB;
        $errors = array();
        
        return $errors;
    }
}