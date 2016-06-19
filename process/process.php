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
 * This page shows a list of exams sent for printing.
* It can be reached from a block within a category or from an EMarking
* course module
*
* @package mod
* @subpackage emarking
* @copyright 2012-2015 Jorge Villalon <jorge.villalon@uai.cl>
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config.php");
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once($CFG->dirroot . "/mod/emarking/print/locallib.php");
global $DB, $USER, $CFG, $OUTPUT;
// Course id, if the user comes from a course.
$courseid = required_param("course", PARAM_INT);
// If the user is downloading a print form.
$downloadform = optional_param("downloadform", false, PARAM_BOOL);
// First check that the user is logged in.
require_login();
if (isguestuser()) {
	die();
}
// Validate that the parameter corresponds to a course.
if (! $course = $DB->get_record("course", array(
		"id" => $courseid))) {
		print_error(get_string("invalidcourseid", "mod_emarking"));
}
// Both contexts, from course and category, for permissions later.
$context = context_course::instance($course->id);
// URL for current page.
$url = new moodle_url("/mod/emarking/print/exams.php", array(
		"course" => $course->id));
// URL for adding a new print order.
$params = array(
		"course" => $course->id);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string("emarking", "mod_emarking"));
$PAGE->set_pagelayout("incourse");
$PAGE->navbar->add(get_string("process", "mod_emarking"));
if (has_capability("mod/emarking:downloadexam", $context)) {
	$PAGE->requires->js("/mod/emarking/js/printorders.js");
}

	echo $OUTPUT->header();
	
	echo $OUTPUT->footer();
