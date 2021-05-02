<?php
require_once($CFG->libdir . '/formslib.php');

class emarking_write_form extends moodleform {
    public function definition() {
        global $CFG;

        // Load the files we're going to need.
        require_once("$CFG->libdir/form/editor.php");
        require_once("$CFG->dirroot/mod/emarking/simpleeditor.php");

        $mform = $this->_form;
        $instance = $this->_customdata;
        $cmid = $instance ['cmid'];

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);

        require_once("$CFG->libdir/form/editor.php");
        \MoodleQuickForm::registerElementType('simpleeditor', "$CFG->libdir/form/editor.php", 'MoodleQuickForm_simpleeditor');

        $editoroptions = array(
            'subdirs' => 0,
            'maxbytes' => 0,
            'maxfiles' => 0,
            'changeformat' => 0,
            'context' => null,
            'noclean' => 0,
            'trusttext' => 0,
            'enable_filemanagement' => false,
            'atto:toolbar' => 'style1 = bold, italic',
        );
        $mform->addElement('simpleeditor', 'mytextfield', null, null, $editoroptions);
        $mform->setType('mytextfield', PARAM_RAW);

        $this->add_action_buttons(false, 'Enviar actividad');
    }
} 