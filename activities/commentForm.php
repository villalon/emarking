<?php
//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");
 
class comment_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
 
        $mform = $this->_form; // Don't forget the underscore! 
 
        $mform->addElement('text', 'comment', "Comenta"); // Add elements to your form
        $mform->setType('comment', PARAM_RAW);                   //Set type of element
        $mform->setDefault('comment', 'Comenta!');        //Default value
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}

?>