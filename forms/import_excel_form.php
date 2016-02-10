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
 * @copyright 2015 Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
class emarking_import_excel_form extends moodleform {
    public function definition() {
        global $DB, $CFG;
        $mform = $this->_form;
        $instance = $this->_customdata;
        $cmid = $instance ['cmid'];
        // Course module id goes hidden.
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('textarea', 'comments', get_string('pastefromexcel', 'mod_emarking'),
                array(
                    'rows' => 5,
                    'cols' => 100,
                    'class' => 'smalltext'));
        $mform->addHelpButton('comments', 'pastefromexcel', 'mod_emarking');
        $mform->setDefault('comments', '');
        $mform->setType('comments', PARAM_TEXT);
        $mform->addElement('checkbox', 'headers', get_string('datahasheaders', 'mod_emarking'));
        $mform->setDefault('headers', null);
        $mform->setType('headers', PARAM_BOOL);
        $mform->addElement("hidden", "confirm");
        $mform->setDefault("confirm", false);
        $mform->setType("confirm", PARAM_BOOL);
    }
    public function hideelements() {
        $comments = $this->_form->getElement("comments");
        $comments->setAttributes(array(
            "style" => "display:none"));
        $headers = $this->_form->getElement("headers");
        $headers->setAttributes(array(
            "style" => "display:none"));
    }
    public function validation($data, $files) {
        global $CFG;
        $errors = array();
        // Use csv importer from Moodle.
        $iid = csv_import_reader::get_new_iid('emarking-predefined-comments');
        $reader = new csv_import_reader($iid, 'emarking-predefined-comments');
        $content = $data ['comments'];
        $reader->load_csv_content($content, 'utf8', "tab");
        // Validate columns, minimum number and first two to be userid and attemptid.
        if (count($reader->get_columns()) < 0) {
            $errors ['comments'] = get_string('onecolumnrequired', 'mod_emarking');
        }
        $reader->init();
        $current = 0;
        while ( $line = $reader->next() ) {
            $current ++;
        }
        if ($current < 1) {
            $errors ['comments'] = get_string('twolinesrequired', 'mod_emarking');
        }
        return $errors;
    }
}