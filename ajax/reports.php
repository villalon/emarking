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
define('AJAX_SCRIPT', true);
// define ( 'NO_DEBUG_DISPLAY', true );

global $CFG, $DB, $OUTPUT, $PAGE, $USER;
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ($CFG->dirroot . '/mod/emarking/ajax/qry/reportsquerylib.php');

// Get course module id
$cmid = required_param("cmid", PARAM_NUMBER);
$action = required_param("action", PARAM_TEXT);
$ids = optional_param("emarkingids", '', PARAM_SEQUENCE);

// Validate course module
if (! $cm = get_coursemodule_from_id('emarking', $cmid)) {
    print_error('Módulo inválido');
}

// Validate module
if (! $emarking = $DB->get_record('emarking', array(
    'id' => $cm->instance
))) {
    print_error('Prueba inválida');
}

$context = context_module::instance($cmid);

list ($gradingmanager, $gradingmethod) = emarking_validate_rubric($context);
$rubriccontroller = $gradingmanager->get_controller($gradingmethod);
$definition = $rubriccontroller->get_definition();
// Calculates the number of criteria for this evaluation

$numcriteria = 0;
if ($rubriccriteria = $rubriccontroller->get_definition()) {
    $numcriteria = count($rubriccriteria->rubric_criteria);
}

// Callback para from webpage
$callback = optional_param('callback', null, PARAM_RAW_TRIMMED);

$totalemarkings = count(explode(',', $ids));

// Headers
// header ( 'Content-Type: text/javascript' );
// header('Content-Type: text/html; charset=utf-8');
// header ( 'Cache-Control: no-cache' );
// header ( 'Pragma: no-cache' );

// var_dump($action);die("este es el action");
if ($action == "markingreport") {
    
    $grading = get_status($cmid, $emarking->id);
    list($contributioners,$contributions) = get_markers_contributions($cmid, $emarking->id);
    list($advancedescription, $advanceresponded, $advanceregrading, $advancegrading) = get_question_advance($cmid, $emarking->id);
    list($markeradvance_marker,$markeradvance_corregido, $markeradvance_porcorregir, $markeradvance_porrecorregir) = get_marker_advance($cmid, $emarking->id);

    $final = Array(
        "Grading" => $grading,
        "Contributioners" => $contributioners,
        "Contributions" => $contributions,
        "Advancedescription" => $advancedescription,
        "Advanceresponded" => $advanceresponded,
        "Advanceregrading" => $advanceregrading,
        "Advancegrading" => $advancegrading,
        "MarkeradvanceMarker" => $markeradvance_marker,
        "MarkeradvanceCorregido" => $markeradvance_corregido,
        "MarkeradvancePorcorregir" => $markeradvance_porcorregir,
        "MarkeradvancePorrecorregir" => $markeradvance_porrecorregir
    );
    
    $output = $final;
    $jsonOutputs = array(
        'error' => '',
        'values' => $output
    );
    $jsonOutput = json_encode($jsonOutputs);
    if ($callback)
        $jsonOutput = $callback . "(" . $jsonOutput . ");";
    echo $jsonOutput;
} else 
    if ($action == "gradereport") {
        
        // counts the total of disticts categories
        $sqlcats = "select count(distinct(c.category)) as categories
from {emarking} as a
inner join {course} as c on (a.course = c.id)
where a.id in ($ids)";
        
        $totalcategories = $DB->count_records_sql($sqlcats);
        
        $grading = get_status($numcriteria, $emarking->id);
        $emarkingstats = get_emarking_stats($ids);
        $marks = get_marks($emarkingstats, $totalcategories, $totalemarkings);
        $emarkingstats = get_emarking_stats($ids);
        $coursemarks = get_courses_marks($emarkingstats, $totalcategories, $totalemarkings);
        $emarkingstats = get_emarking_stats($ids);
        $pass_ratio = get_pass_ratio($emarkingstats, $totalcategories, $totalemarkings);
        list($efficiencycriterion, $efficiencyrate) = get_efficiency ($ids);
        
        $final = Array(
            "Marks" => $marks,
            "CourseMarks" => $coursemarks,
            "PassRatio" => $pass_ratio,
            "EfficiencyCriterion" => $efficiencycriterion,
            "EfficiencyRate" => $efficiencyrate
        );
        $output = $final;
        $jsonOutputs = array(
            'error' => '',
            'values' => $output
        );
        $jsonOutput = json_encode($jsonOutputs);
        if ($callback)
            $jsonOutput = $callback . "(" . $jsonOutput . ");";
        echo $jsonOutput;
    }

