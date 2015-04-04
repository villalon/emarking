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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2015 Jorge Villalón {@link http://www.uai.cl},
 * @copyright 2015 Nicolas Perez
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define ( 'AJAX_SCRIPT', true );
// define ( 'NO_DEBUG_DISPLAY', true );

global $CFG, $DB, $OUTPUT, $PAGE, $USER;
require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . '/config.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ($CFG->dirroot . '/mod/emarking/ajax/qry/reportsquerylib.php');

// Get course module id
$cmid = required_param ( "cmid", PARAM_NUMBER );
// Get action
$action = required_param ( "action", PARAM_TEXT );
// Get ids for multy section
$ids = optional_param ( "emarkingids", '', PARAM_SEQUENCE );
// Callback for from webpage
$callback = optional_param ( 'callback', null, PARAM_RAW_TRIMMED );

// Validate course module
if (! $cm = get_coursemodule_from_id ( 'emarking', $cmid )) {
	print_error ( get_string ( 'invalidcoursemodule', 'mod_emarking' ) );
}

// Validate module
if (! $emarking = $DB->get_record ( 'emarking', array ('id' => $cm->instance))) {
	print_error ( get_string ( 'invalidexamid', 'mod_emarking' ) );
}
// Set context
$context = context_module::instance ( $cmid );
// Validates there is a rubric for this evaluation
list ( $gradingmanager, $gradingmethod ) = emarking_validate_rubric ( $context );
$rubriccontroller = $gradingmanager->get_controller ( $gradingmethod );
$definition = $rubriccontroller->get_definition ();

// Calculates the number of criteria for this evaluation
$numcriteria = 0;
if ($rubriccriteria = $rubriccontroller->get_definition ()) {
	$numcriteria = count ( $rubriccriteria->rubric_criteria );
}

$totalemarkings = count ( explode ( ',', $ids ) );

// Headers
// header ( 'Content-Type: text/javascript' );
// header('Content-Type: text/html; charset=utf-8');
// header ( 'Cache-Control: no-cache' );
// header ( 'Pragma: no-cache' );

if ($action == "markingreport") {
	
	// Gets all variables needed to pass to GWT for the graph making in markingreport.php
	$grading = get_status ( $cmid, $emarking->id );
	list ( $contributioners, $contributions ) = get_markers_contributions ( $grading, $emarking->id );
	list ( $advancedescription, $advanceresponded, $advanceregrading, $advancegrading ) = get_question_advance ( $cmid, $emarking->id );
	list ( $markeradvance_marker, $markeradvance_corregido, $markeradvance_porcorregir, $markeradvance_porrecorregir ) = get_marker_advance ( $cmid, $emarking->id );
	
	$final = Array (
			'Grading' => $grading,
			'Contributioners' => $contributioners,
			'Contributions' => $contributions,
			'Advancedescription' => $advancedescription,
			'Advanceresponded' => $advanceresponded,
			'Advanceregrading' => $advanceregrading,
			'Advancegrading' => $advancegrading,
			'MarkeradvanceMarker' => $markeradvance_marker,
			'MarkeradvanceCorregido' => $markeradvance_corregido,
			'MarkeradvancePorcorregir' => $markeradvance_porcorregir,
			'MarkeradvancePorrecorregir' => $markeradvance_porrecorregir 
	);
	// Array cration for future json
	$output = $final;
	$jsonOutputs = array (
			'error' => '',
			'values' => $output 
	);
	// Encode array into json
	$jsonOutput = json_encode ( $jsonOutputs );
	if ($callback)
		$jsonOutput = $callback . "(" . $jsonOutput . ");";
	echo $jsonOutput;
} else if ($action == "gradereport") {
	
	// Counts the total of disticts categories
	$sqlcats = 'SELECT COUNT(DISTINCT(c.category)) AS categories
				FROM {emarking} AS a
				INNER JOIN {course} AS c ON (a.course = c.id)
				WHERE a.id IN (:ids)';
	
	$totalcategories = $DB->count_records_sql ( $sqlcats, array('ids'=>$ids) );
	$grading = get_status ( $numcriteria, $emarking->id );
	$emarkingstats = get_emarking_stats ( $ids );
	
	// Gets all variables needed to pass to GWT for the graph making in gradereport.php
	$marks = get_marks ( $emarkingstats, $totalcategories, $totalemarkings );
	$coursemarks = get_courses_marks ( $emarkingstats, $totalcategories, $totalemarkings );
	$pass_ratio = get_pass_ratio ( $emarkingstats, $totalcategories, $totalemarkings );
	list ( $efficiencycriterion, $efficiencyrate ) = get_efficiency ( $ids );
	
	$final = Array (
			'Marks' => $marks,
			'CourseMarks' => $coursemarks,
			'PassRatio' => $pass_ratio,
			'EfficiencyCriterion' => $efficiencycriterion,
			'EfficiencyRate' => $efficiencyrate 
	);
	// Array cration for future json
	$output = $final;
	$jsonOutputs = array (
			'error' => '',
			'values' => $output 
	);
	// Encode array into json
	$jsonOutput = json_encode ( $jsonOutputs );
	if ($callback)
		$jsonOutput = $callback . "(" . $jsonOutput . ");";
	echo $jsonOutput;
}

