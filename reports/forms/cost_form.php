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
 * The main cost center configuration form
 * It uses the standard core Moodle formslib.
 * For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Mihail Pozarski <mipozarski@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
require_once($CFG->libdir . "/formslib.php");
class emarking_cost_form extends moodleform {
    /**
     * Defines forms elements
     */
    public function definition() {
        global $DB;
        
        $mform = $this->_form;
        $instance = $this->_customdata;
        $categoryid = $instance["category"];
        
        $arraycategory = array();
        if ($categories = $DB->get_records('course_categories')) {
            foreach ($categories as $category) {
                $arraycategory [$category->id] = $category->name;
            }
        }
        
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('select', 'category', get_string('category', 'mod_emarking'), $arraycategory);
        $mform->setDefault('category', $categoryid);
        $mform->addHelpButton('category', 'categoryselection', 'mod_emarking');
        $mform->addElement('text', 'cost', get_string('costofonepage', 'mod_emarking'));
        $mform->addRule('cost', get_string('numericvalue', 'mod_emarking'), 'required', null, 'client');
        $mform->setType('cost', PARAM_TEXT);
        $mform->addHelpButton('cost', 'numericvalue', 'mod_emarking');
        $mform->addElement('text', 'costcenter', get_string('costcenternumber', 'mod_emarking'));
        $mform->addRule('costcenter', get_string('numericvalue', 'mod_emarking'), 'required', null, 'client');
        $mform->setType('costcenter', PARAM_TEXT);
        $mform->addHelpButton('costcenter', 'validcostcenter', 'mod_emarking');
        $this->add_action_buttons(true);
    }
    public function validation($data, $files) {
    
        $errors = array();
        if (! is_number($data ['cost'])) {
            $errors ['cost'] = get_string('numericplease', 'mod_emarking');
        }
        if (! is_number($data ['costcenter'])) {
            $errors ['costcenter'] = get_string('numericplease', 'mod_emarking');
        }
        return $errors;
    }
}
class emarking_subcategory_form extends moodleform {
	/**
	 * Defines forms elements
	 */
	public function definition() {
		global $DB;
		
		$mform = $this->_form;
		$instance = $this->_customdata;
		$categoryid = $instance["category"];
		
		$arraysubcategory = array();
		$arraysubcategory[$categoryid] = get_string('selectcategory', 'mod_emarking');
		$subcategories = emarking_get_subcategories($categoryid);
		$arraysubcategory = $arraysubcategory + $subcategories;
		
		$mform->addElement('select', 'category', get_string('category', 'mod_emarking'), $arraysubcategory);
		$mform->addHelpButton('category', 'categoryselect', 'mod_emarking');
		$this->add_action_buttons(false,get_string('gotosubcategory', 'mod_emarking'));
		
	}
	public function validation($data, $files) {
	}
}
class emarking_uppercategory_form extends moodleform {
	/**
	 * Defines forms elements
	 */
	public function definition() {
		global $DB;
		$mform = $this->_form;
		$instance = $this->_customdata;
		$categoryid = $instance["category"];
		$arrayuppercategory = array();
		$arrayuppercategory[$categoryid] = get_string('selectcategory', 'mod_emarking');
		$categoriesdepth = $DB->get_record_sql("SELECT depth FROM {course_categories} WHERE id = ?",array('id'=>$categoryid));
		foreach($categoriesdepth as $categorydepth){
			$depth = $categoriesdepth->depth;
		}
		$uppercategoriesquery = "SELECT * FROM {course_categories} WHERE depth < ?";
		$uppercategories = $DB->get_records_sql($uppercategoriesquery, array($depth));
		foreach ($uppercategories as $uppercategory) {
			$arrayuppercategory [$uppercategory->id] = $uppercategory->name;
		}
		$mform->addElement('select', 'category', get_string('category', 'mod_emarking'), $arrayuppercategory);
		$mform->setDefault('category', $categoryid);
		$mform->addHelpButton('category', 'categoryselection', 'mod_emarking');
		$this->add_action_buttons(false,get_string('gotouppercategory', 'mod_emarking'));
	}
	public function validation($data, $files) {
	}
}