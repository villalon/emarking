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
 * @copyright 2016 Benjamin Espinosa (beespinosa94@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/config.php");
require_once ($CFG->libdir . "/formslib.php");
class cycle_form extends moodleform {
	function definition() {
		$var = array('alo?', 'alo si?', 'si con el');
	
		$mform = $this->_form;
		$instance = $this->_customdata;
		
		$userid = $instance['0'];	
		
		$out = html_writer::div('<h2>'.get_string('filters','mod_emarking').'</h2>');
		echo $out;
		
		$mform->addElement('select', 'category', get_string('category','mod_emarking'), $var);
		
		$mform->addElement('select', 'course', get_string('course','mod_emarking'), $var);
		
		$mform->addElement('select', 'section', get_string('section','mod_emarking'), $var);
		
	}
}