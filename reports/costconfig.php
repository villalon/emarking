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
require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once('/forms/cost_form.php');

global $CFG, $DB, $OUTPUT; 
$categoryid = required_param('category', PARAM_INT);

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

$url = new moodle_url('/mod/emarking/reports/costconfig.php', array(
		'category' => $categoryid
));
$ordersurl = new moodle_url('/mod/emarking/reports/costconfig.php', array(
		'category' => $categoryid,

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
$PAGE->navbar->add(get_string('printorders', 'mod_emarking'), $ordersurl);
$PAGE->navbar->add($pagetitle);
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle . ' ' . $category->name);
	
	

	$addform = new emarking_cost_form();

	$alliterations = array();
	
	if( $addform->is_cancelled() ){
		$backtocourse = new moodle_url("course/view.php",array('id'=>$course->id));
		redirect($backtocourse);
		
	}
	else if($datas = $addform->get_data()){
			
			$category = $datas->category;	
			$cost = $datas->cost;
			$costcenter = $datas->costcenter;
	
	
			$categoryparams = array(
					"%/$category/%",
					$category
			);
			// Sql that counts all the resourses since the last time the app was used
			$sqlcategory = "SELECT id 
							FROM mdl_course_categories as cc
							WHERE (cc.path like ? OR cc.id = ?)
                               ";
			// Gets the information of the above query
			if($categories = $DB->get_records_sql($sqlcategory, $categoryparams)){
				foreach($categories AS $category){
					$arraycategories[$category->id] = $category->id; 
			}
			$arrayupdate = array();
			$arrayinsert = array();
			list ( $sqlin, $parametros1 ) = $DB->get_in_or_equal ( $arraycategories );
				$sqlupdate="SELECT cc.id as id, ecc.printingcost as printingcost
						  FROM mdl_course_categories as cc
						  LEFT JOIN mdl_emarking_category_cost as ecc ON (cc.id = ecc.category)
					  WHERE cc.id $sqlin";
			
			$costes = $DB->get_records_sql($sqlupdate,$parametros1);
			
			foreach($costes AS $costs){
				if($costs->printingcost == NULL){
					$record = new stdClass();
					$record->category = $costs->id;
					$record->printingcost = $cost;
					$record->costcenter = $costcenter;
					$arrayinsert[]=$record;
					}
				else {
				$arrayupdate[$costs->id] = $costs->id;
				}
			}
			list ( $sqlin, $parametrosupdate ) = $DB->get_in_or_equal ( $arrayupdate );
			$parametros2 = array(
					$cost,
					$costcenter
			);
			$updateparams=array_merge($parametros2,$parametrosupdate);
			$sqlupdate="UPDATE mdl_emarking_category_cost
			 			SET printingcost = ?, costcenter = ?
						WHERE category $sqlin";	
					$DB->execute($sqlupdate,$updateparams);
			$DB->insert_records("emarking_category_cost", $arrayinsert);

			}
	}


	$addform->display();

echo $OUTPUT->footer();
	

