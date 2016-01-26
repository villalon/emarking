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
 * Prints a particular instance of evapares
*
* You can have a rather longer description of the file as well,
* if you like, and it can span multiple lines.
*
* @package    mod_evapares
* @copyright  2015 Your Name
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->dirroot.'/mod/emarking/reports/forms/cost_form.php');
require_once ($CFG->dirroot.'/mod/emarking/locallib.php');
require_once ($CFG->dirroot.'/mod/emarking/reports/locallib.php');

global $CFG, $DB, $OUTPUT;
$categoryid = required_param('category', PARAM_INT);
$action = optional_param("action", "view", PARAM_TEXT);
// Validate category
if (! $category = $DB->get_record('course_categories', array(
		'id' => $categoryid
))) {
	print_error(get_string('invalidcategoryid', 'mod_emarking'));
}
// We are in the category context
$context = context_coursecat::instance($categoryid);
// User must be logged in
require_login();
if (isguestuser()) {
	die();
}
// And have viewcostreport capability
if (! has_capability('mod/emarking:viewcostreport', $context)) {
	// TODO: Log invalid access to printreport
	print_error('Not allowed!');
}

$url = new moodle_url('/mod/emarking/reports/categorycosttable.php', array(
		'category' => $categoryid
));

$categoryurl = new moodle_url('/course/index.php', array(
		'categoryid' => $categoryid
));

$pagetitle = get_string('costreport', 'mod_emarking');

$PAGE->set_context($context);
$PAGE->set_url($url);
//$PAGE->requires->js('/mod/emarking/js/printorders.js');
$PAGE->set_pagelayout('course');
$PAGE->navbar->add($category->name, $categoryurl);
$PAGE->navbar->add(get_string('printorders', 'mod_emarking'), $url);
$PAGE->navbar->add($pagetitle);
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));

echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle . ' ' . $category->name);
echo $OUTPUT->tabtree(emarking_costconfig_tabs($category), get_string("costcategorytable", 'mod_emarking'));

$categorycost = emarking_getcategorycosttabledata($categoryid);
$categorycosttable = new html_table();
$categorycosttable->head = array(get_string('teacherranking', 'emarking'), 'Number of activities');
$categorycosttable->data = $categorycost;
echo html_writer::table($categorycosttable);

echo $OUTPUT->footer(); 