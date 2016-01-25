<?php

/**
 * The main evapares configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_evapares
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
require_once ($CFG->libdir . "/formslib.php");

/**
 * Module instance settings form
 *
 * @package    mod_evapares
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class emarking_cost_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $DB;
        $categoryid = required_param('category', PARAM_INT);
        $mform = $this->_form;
        $arraycategory = array();
        if($categories = $DB->get_records('course_categories')){
        	foreach($categories as $category){
        		$arraycategory[$category->id] = $category->name;
        		
        	}
        }

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));
		$mform->addElement('select', 'category','Category', $arraycategory);    
		$mform->setDefault('category', $categoryid);
        $mform->addElement('text', 'cost','Cost of printing one page');
        $mform->setType('cost', PARAM_INT);
        $mform->addElement('text', 'costcenter','Cost Center number');
        $mform->setType('costcenter', PARAM_INT);
        
        $this->add_action_buttons();
 
    }
}