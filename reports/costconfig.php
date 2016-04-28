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
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Mihail Pozarski <mipozarski@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/reports/forms/cost_form.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/reports/locallib.php');
global $DB, $OUTPUT;

$categoryid = required_param('category', PARAM_INT);

// User must be logged in.
require_login();
if (isguestuser()) {
    die();
}

// Validate category.
if (! $category = $DB->get_record('course_categories', array(
    'id' => $categoryid))) {
    print_error(get_string('invalidcategoryid', 'mod_emarking'));
}

// We are in the category context.
$context = context_coursecat::instance($categoryid);

// And have viewcostreport capability.
if (! has_capability('mod/emarking:viewcostreport', $context)) {
    // TODO: Log invalid access to printreport.
    print_error(get_string('notallowedcostreport', 'mod_emarking'));
}

// This page url.
$url = new moodle_url('/mod/emarking/reports/costconfig.php', array(
    'category' => $categoryid));
// Url that lead you to the category page.
$categoryurl = new moodle_url('/course/index.php', array(
    'categoryid' => $categoryid));

$pagetitle = get_string('costreport', 'mod_emarking');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add($category->name, $categoryurl);
$PAGE->navbar->add(get_string('printorders', 'mod_emarking'), $url);
$PAGE->navbar->add($pagetitle);
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));

// Add the emarking cost form for categories.
$addform = new emarking_cost_form(null,array(
			"category" => $categoryid
	));

// If the form is cancelled redirects you to the report center.
if ($addform->is_cancelled()) {
    $backtocourse = new moodle_url('/mod/emarking/reports/categorycosttable.php', array(
        'category' => $categoryid));
    redirect($backtocourse);
    
} else if ($data = $addform->get_data()) {
    // Saves the form info in to variables.
    $category = $data->category;
    $cost = $data->cost;
    $costcenter = $data->costcenter;
    // Parameters for getting the category cost if it exist.
    $categoryparams = array(
        $category);
    // Sql that get the specific category cost if it exist.
    $sqlupdate = "SELECT cc.id AS id, ecc.printingcost AS printingcost
					FROM {course_categories} AS cc
					LEFT JOIN {emarking_category_cost} AS ecc ON (cc.id = ecc.category)
				    WHERE cc.id = ?";
    // Run the sql with its parameters.
    $costes = $DB->get_records_sql($sqlupdate, $categoryparams);
    $result = array();
    foreach ($costes as $costs) {
        // If there is no printing cost insert it.
        if ($costs->printingcost == null) {
            $record = new stdClass();
            $record->category = $costs->id;
            $record->printingcost = $cost;
            $record->costcenter = $costcenter;
            $result [] = $record;  
        } else {
            $parametrosupdate = array(
                $cost,
                $costcenter,
                $costs->id);
            $sqlupdate = "UPDATE {emarking_category_cost}
		 				SET printingcost = ?, costcenter = ?
						WHERE category = ?";
            $DB->execute($sqlupdate, $parametrosupdate);
        }
    }
    if(isset($result)){
    	$DB->insert_records("emarking_category_cost", $result);
    }
    // Redirect to the table with all the category costs.
    redirect(new moodle_url("/mod/emarking/reports/categorycosttable.php", array(
        "category" => $data->category)));
}
// If there is no data or is it not cancelled show the header, the tabs and the form.
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle . ' ' . $category->name);
echo $OUTPUT->tabtree(emarking_costconfig_tabs($category), get_string("costconfigtab", 'mod_emarking'));
// Display the form.
$addform->display();
echo $OUTPUT->footer();