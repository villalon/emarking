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
 * This is a one-line short description of the file
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2015-onwards Jorge Villalon <villalon@gmail.com>
 * @author Nicolás Pérez, Jorge Villalón
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . '/formslib.php');
class CommentForm extends moodleform {
    // Add elements to form.
    public function definition() {
        global $CFG;
        $cmid = $this->_customdata ['cmid'];
        $mform = $this->_form; // Don't forget the underscore!
        $mform->addElement('header', 'predefinedcomment', get_string('createcomment', 'mod_emarking'));
        $mform->addElement('editor', 'comment', get_string('formnewcomment', 'mod_emarking'));
        $mform->setType('comment', PARAM_TEXT);
        $mform->addRule('comment', get_string('writecomment', 'mod_emarking'), 'required');
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $this->add_action_buttons(true, get_string('savechanges', 'mod_emarking'));
    }
    public function validation($data, $files) {
        $errors = array();
        $comment = $data ['comment'] ['text'];
        if (strlen(trim($comment)) == 0) {
            $error ['comment'] = get_string('writecomment', 'mod_emarking');
        }
        return $errors;
    }
}
class EditCommentForm extends moodleform {
    // Add elements to form.
    public function definition() {
        global $CFG;
        $mform = $this->_form; // Don't forget the underscore!
        $instance = $this->_customdata;
        $commenttext = $instance ['text'];
        $cmid = $instance ['cmid'];
        $commentid = $instance ['commentid'];
        $mform->addElement('editor', 'comment', get_string('formeditcomment', 'mod_emarking'));
        $mform->setType('comment', PARAM_TEXT);
        $mform->setDefault('comment', array(
            'text' => $commenttext));
        $mform->addRule('comment', get_string('editcomment', 'mod_emarking'), 'required');
        $mform->addElement('hidden', 'action', 'edit');
        $mform->setType('action', PARAM_TEXT);
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'commentid', $commentid);
        $mform->setType('commentid', PARAM_INT);
        $this->add_action_buttons(true, get_string('savechanges', 'mod_emarking'));
    }
}
class EditExperimentalGroupForm extends moodleform {
    // Add elements to form.
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form;
        $instance = $this->_customdata;
        $id = $instance ['id'];
        $groupid = $instance ['groupid'];
        $mform->addElement('date_time_selector', 'datestart', "Date Start");
        $mform->setType('startdate', PARAM_INT);
        $mform->addRule('datestart', get_string('editcomment', 'mod_emarking'), 'required');
        $mform->addElement('date_time_selector', 'dateend', "Date End");
        $mform->setType('dateend', PARAM_INT);
        $mform->addRule('dateend', get_string('editcomment', 'mod_emarking'), 'required');
        $mform->addElement('checkbox', 'linkrubric', "Vicular rúbrica a comentario");
        $mform->setType('linkrubric', PARAM_INT);
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'groupid', $groupid);
        $mform->setType('groupid', PARAM_INT);
        $mform->addElement('hidden', 'action', "edit");
        $mform->setType('action', PARAM_ALPHANUM);
        $this->add_action_buttons(true, "Save");
    }
}