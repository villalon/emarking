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
require_once($CFG->dirroot.'/course/moodleform_mod.php');

class courses_form extends moodleform {

	function definition(){
		global $DB, $USER;
		
		$mform = $this->_form;
		$instance = $this->_customdata;
		
		$userid = $instance['0'];
		$category = $instance['1'];
		$cid = $instance['2'];

		$teachercoursessql = "SELECT c.id AS courseid,
				cc.name AS categoryname,
				c.shortname AS coursename,
				CONCAT (u.firstname, ' ', u.lastname)AS name
				FROM {user} AS u
				INNER JOIN {role_assignments} AS ra ON (ra.userid = u.id AND u.id=?)
				INNER JOIN {context} AS ct ON (ct.id = ra.contextid)
				INNER JOIN {course} AS c ON (c.id = ct.instanceid)
				INNER JOIN {course_categories} AS cc ON (cc.id = c.category AND cc.name = ?)
				INNER JOIN {role} AS r ON (r.id = ra.roleid AND r.shortname IN ('teacher', 'editingteacher'))
				INNER JOIN {emarking_exams} AS ee ON (ee.course = c.id)
                GROUP BY courseid";
		
		$teachercourses = $DB->get_records_sql($teachercoursessql, array($USER->id, $category));
		
		$shortname = array('Seleccione');
		foreach($teachercourses as $coursedata){
			$shortname[$coursedata->coursename] = $coursedata->coursename;
 		}

		$courses = array_unique($shortname);
		
		$mform->addElement('select', 'courses', get_string('course','mod_emarking'), $courses);
		$mform->setType('courses' , PARAM_TEXT);

		$mform->addElement("hidden", "category", $category);
		$mform->setType( "category", PARAM_TEXT);
		
		$mform->addElement("hidden", "course", $cid);
		$mform->setType( "course", PARAM_INT);
		
		$this->add_action_buttons(false, get_string('search', 'mod_emarking'));
		
	}
	
	function validation($data, $files){
			
		$errors = array();
	
		if($data['courses'] == 0){
			$errors['courses'] = 'Debe seleccionar un curso';
		}
	
		return $errors;
			
	}
}
