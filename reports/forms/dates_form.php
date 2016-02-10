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

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/course/lib.php');
/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2015 onwards Jorge Villalón {@link http://www.uai.cl}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class emarking_dates_form extends moodleform {
    public function definition() {
        global $DB, $CFG;
        // Custom data from page.
        $mform = $this->_form;
        $instance = $this->_customdata;
        $categoryid = $instance ['category'];
        $period = $instance ['period'];
        // Hidden id to continue processing.
        $mform->addElement('hidden', 'category', $categoryid);
        $mform->setType('category', PARAM_INT);
        // Title.
        // $mform->addElement('header','config_title', get_string('configuration','mod_emarking'));.
        $periods = array();
        $periods [0] = get_string('select');
        $periods [1] = 'YTD';
        $periods [2] = 'LastMarchTD';
        $periods [3] = 'LastAugustTD';
        $periods [50] = 'Custom dates';
        $mform->addElement('select', 'period', get_string('emarking', 'mod_emarking'), $periods,
                array(
                    "onchange" => "showFullForm(this.form)"));
        $mform->setType('period', PARAM_INT);
        $mform->setDefault('period', 0);
        $options = array(
            'startyear' => 1970,
            'stopyear' => 2020,
            'timezone' => 99,
            'optional' => false);
        $mform->addElement('date_selector', 'startdate', get_string('date'));
        $mform->disabledIf('startdate', 'period', 'neq', '50');
        $mform->addElement('date_selector', 'enddate', get_string('date'));
        $mform->disabledIf('enddate', 'period', 'neq', '50');
        $mform->disable_form_change_checker();
        // Action buttons with no cancel.
        $this->add_action_buttons(false, get_string('apply', 'mod_emarking'));
    }
    public function validation($data, $files) {
        global $CFG;
        $period = $data ['period'];
        $startdate = $data ['startdate'];
        $enddate = $data ['enddate'];
        $errors = array();
        // Validate dates if using a custom period.
        if ($period == 50 && ($startdate > time() || $startdate >= $enddate || $enddate > time())) {
            $errors ['startdate'] = get_string('examdateinvalid', 'mod_emarking');
            $errors ['enddate'] = get_string('examdateinvalid', 'mod_emarking');
            return $errors;
        }
        return $errors;
    }
    public function display() {
        parent::display();
        echo "<script>
	        function showFullForm(form) {
	           var e = document.getElementById('id_period');
               if(!e) {
                  return;
               }
               var period = e.options[e.selectedIndex].value;
            // Custom period shows dates.
	           if (period == 50) {
                    document.getElementById('fitem_id_startdate').style.display = 'block';
                    document.getElementById('fitem_id_enddate').style.display = 'block';
                    document.getElementById('fitem_id_submitbutton').style.display = 'block';
                } else {
                    document.getElementById('fitem_id_startdate').style.display = 'none';
                    document.getElementById('fitem_id_enddate').style.display = 'none';
                    document.getElementById('fitem_id_submitbutton').style.display = 'none';
	                if(period > 0)
	                   form.submit();
                }
	       }
            showFullForm();
	        </script>";
    }
}