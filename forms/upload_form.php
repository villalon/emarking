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
 * This form is used to upload a zip file containing digitized answers
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2011 onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/course/lib.php');
class mod_emarking_upload_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata;
        // Header.
        $mform->addElement('header', 'digitizedfile', get_string('digitizedfile', 'mod_emarking'));
        // File picker for the digitized answers.
        $mform->addElement('filepicker', 'assignment_file', get_string('uploadexamfile', 'mod_emarking'), null,
                $instance ['options']);
        $mform->addRule('assignment_file', get_string('filerequiredzip', 'mod_emarking'), 'required', null, 'client');
        $mform->setType('assignment_file', PARAM_FILE);
        $mform->addHelpButton('assignment_file', 'filerequiredzip', 'mod_emarking');
        // A merge to indicate if the new files should be merged with any previous submissions.
        $mform->addElement('hidden', 'merge', false);
        $mform->setType('merge', PARAM_BOOL);
        // The course module id.
        $mform->addElement('hidden', 'id', $instance ['coursemoduleid']);
        $mform->setType('id', PARAM_INT);
        // The activity id.
        $mform->addElement('hidden', 'emarkingid', $instance ['emarkingid']);
        $mform->setType('emarkingid', PARAM_INT);
        // Header.
        $mform->addElement('header', 'qrprocessingtitle', get_string('qrprocessingtitle', 'mod_emarking'));
        $mform->addElement('static', 'qrprocessing', '',
                '<a href="' . new moodle_url('/mod/emarking/emarkingdesktop.zip') . '">' . get_string('qrprocessing',
                        'mod_emarking') . '</a>');
        // Action buttons.
        $this->add_action_buttons(true, get_string('processtitle', 'mod_emarking'));
    }
}