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
        
       if( $categoriescost = $DB->get_records('emarking_category_cost',array('category'=>$categoryid))){
        foreach($categoriescost AS $coursecat){
        	$arraycoursecat[0] = $coursecat->costcenter;
        	$arraycoursecat[1] = $coursecat->printingcost;
        }
       }
        if($categories = $DB->get_records('course_categories')){
        	foreach($categories as $category){
        		$arraycategory[$category->id] = $category->name;
        		
        	}
        }
        
        $mform->addElement('header', 'general', get_string('general', 'form'));
		$mform->addElement('select', 'category','Category', $arraycategory);    
		$mform->setDefault('category', $categoryid);
		$mform->addHelpButton('category', 'categoryselection', 'mod_emarking');
        $mform->addElement('text', 'cost','Cost of printing one page');
        $mform->addRule('cost', 'Must enter a number', 'required', null, 'client');
        $mform->setType('cost', PARAM_INT);
        $mform->addHelpButton('cost', 'numericcost', 'mod_emarking');
        $mform->addElement('text', 'costcenter','Cost Center number');
        $mform->addRule('costcenter', 'Must enter a number', 'required', null, 'client');
        $mform->setType('costcenter', PARAM_INT);
        $mform->addHelpButton('costcenter', 'validcostcenter', 'mod_emarking');        
        $this->add_action_buttons(true);
 
    }
}