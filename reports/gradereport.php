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
 * @copyright 2014 Jorge Villalon <villalon@gmail.com>
 * @copyright 2015 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @copyright 2015 Xiu-Fong Lin <xlin@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . '/config.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ('forms/gradereport_form.php');

global $DB, $USER;

// Get course module id
$cmid = required_param ( 'id', PARAM_INT );

// Validate course module
if (! $cm = get_coursemodule_from_id ( 'emarking', $cmid )) {
	print_error ( 'Módulo inválido' );
}

// Validate module
if (! $emarking = $DB->get_record ( 'emarking', array (
		'id' => $cm->instance 
) )) {
	print_error ( 'Prueba inválida' );
}
// Validate course
if (! $course = $DB->get_record ( 'course', array (
		'id' => $emarking->course 
) )) {
	print_error ( 'Curso inválido' );
}

// URLs for current page
$url = new moodle_url ( '/mod/emarking/gradereport.php', array (
		'id' => $cm->id 
) );

// Course context is used in reports
$context = context_module::instance ( $cm->id );

// Validate the user has grading capabilities
require_capability ( 'mod/emarking:grade', $context );

// First check that the user is logged in
require_login ( $course->id );
if (isguestuser ()) {
	die ();
}

// Page settings (URL, breadcrumbs and title)
$PAGE->set_context ( $context );
$PAGE->set_course ( $course );
$PAGE->set_cm ( $cm );
$PAGE->set_url ( $url );
$PAGE->set_pagelayout ( 'incourse' );
$PAGE->set_heading ( $course->fullname );
$PAGE->navbar->add ( get_string ( 'gradereport', 'grades' ) );

echo $OUTPUT->header ();
echo $OUTPUT->heading_with_help ( get_string ( 'gradereport', 'mod_emarking' ), 'gradereport', 'mod_emarking' );

// Print eMarking tabs.
echo $OUTPUT->tabtree ( emarking_tabs ( $context, $cm, $emarking ), "report" );

// Counts the total of exams.
$totalsubmissions = $DB->count_records_sql ( "
		SELECT COUNT(dr.id) AS total 
		FROM {emarking_draft} AS dr
		INNER JOIN {emarking_submission} AS e ON (e.emarking = :emarking AND e.id = dr.submissionid AND dr.qualitycontrol=0)
		WHERE dr.grade >= 0 AND dr.status >= :status", array (
		'emarking' => $emarking->id,
		'status' => EMARKING_STATUS_RESPONDED 
) );
// Check if there are any submissions to be shown.
if (! $totalsubmissions || $totalsubmissions == 0) {
	echo $OUTPUT->notification ( get_string ( 'nosubmissionsgraded', 'mod_emarking' ), 'notifyproblem' );
	echo $OUTPUT->footer ();
	die ();
}
// Initialization of the variable $emakingids, with the actual emarking id as the first one on the sequence.
$emarkingids = '' . $emarking->id;

$extracategory = optional_param ( 'categories', 0, PARAM_INT );
// check for parallel courses
if ($CFG->emarking_parallelregex) {
	$parallels = emarking_get_parallel_courses ( $course, $extracategory, $CFG->emarking_parallelregex );
} else {
	$parallels = false;
}
// Form that lets you choose if you want to add to the report the other courses
$emarkingsform = new emarking_gradereport_form ( null, array (
		'course' => $course,
		'cm' => $cm,
		'parallels' => $parallels,
		'id' => $emarkingids 
) );
$emarkingsform->display ();
// Get the IDs from the parallel courses
$totalemarkings = 1;
/*
 * This if bring all the parallel courses that were match in the regex and concatenate each other with a coma "," in between,
 * With this if the variable $parallels exists and count how manny id are inside the array to check if they are more than 0,
 * then it check if the the test exist and check their properties, if the evaluation of this match it brings their ids, and concatenates them.
 */
if ($parallels && count ( $parallels ) > 0) {
	foreach ( $parallels as $pcourse ) {
		$parallelids = '';
		if ($emarkingsform->get_data () && property_exists ( $emarkingsform->get_data (), "emarkingid_$pcourse->id" )) {
			eval ( "\$parallelids = \$emarkingsform->get_data()->emarkingid_$pcourse->id;" );
			if ($parallelids > 0) {
				$emarkingids .= ',' . $parallelids;
				$totalemarkings ++;
			}
		}
	}
}
// counts the total of disticts categories
$sqlcats = "SELECT 
                COUNT(DISTINCT(c.category)) as categories
                FROM {emarking} AS a
                INNER JOIN {course} AS c ON (a.course = c.id)
                WHERE a.id IN (?)";

$totalcategories = $DB->count_records_sql ( $sqlcats, array (
		$emarkingids 
) );

// Get the grading manager, then method and finally controller
// Calls the grading manager, to iniciate the other functions.
$gradingmanager = get_grading_manager ( $context, 'mod_emarking', 'attempt' );
// Type of method.(check to see if it is a rubric)
$gradingmethod = $gradingmanager->get_active_method ();
// The rubic controller has everything about the rubric, from configurations to the rubric it self
$rubriccontroller = $gradingmanager->get_controller ( $gradingmethod );
// Rubric
$rubicdefinition = $rubriccontroller->get_definition ();
// Search for stats regardig the exames (eg: max, min, number of students,etc)
$sql = "SELECT  *,
CASE
WHEN categoryid is null THEN 'TOTAL'
WHEN emarkingid is null THEN concat('SUBTOTAL ', categoryname)
ELSE coursename
END AS seriesname
FROM (
SELECT 	categoryid AS categoryid,
categoryname,
emarkingid AS emarkingid,
modulename,
coursename,
COUNT(*) AS students,
SUM(pass) AS pass,
ROUND((SUM(pass) / count(*)) * 100,2) AS pass_ratio,
SUBSTRING_INDEX(
SUBSTRING_INDEX(
group_concat(grade order by grade separator ',')
, ','
, 25/100 * COUNT(*) + 1)
, ','
, -1
) AS percentile_25,
SUBSTRING_INDEX(
SUBSTRING_INDEX(
group_concat(grade order by grade separator ',')
, ','
, 50/100 * COUNT(*) + 1)
, ','
, -1
) AS percentile_50,
SUBSTRING_INDEX(
SUBSTRING_INDEX(
group_concat(grade order by grade separator ',')
, ','
, 75/100 * COUNT(*) + 1)
, ','
, -1
) AS percentile_75,
MIN(grade) AS minimum,
MAX(grade) AS maximum,
ROUND(avg(grade),2) AS average,
ROUND(stddev(grade),2) AS stdev,
SUM(histogram_01) AS histogram_1,
SUM(histogram_02) AS histogram_2,
SUM(histogram_03) AS histogram_3,
SUM(histogram_04) AS histogram_4,
SUM(histogram_05) AS histogram_5,
SUM(histogram_06) AS histogram_6,
SUM(histogram_07) AS histogram_7,
SUM(histogram_08) AS histogram_8,
SUM(histogram_09) AS histogram_9,
SUM(histogram_10) AS histogram_10,
SUM(histogram_11) AS histogram_11,
SUM(histogram_12) AS histogram_12,
ROUND(SUM(rank_1)/count(*),3) AS rank_1,
ROUND(SUM(rank_2)/count(*),3) AS rank_2,
ROUND(SUM(rank_3)/count(*),3) AS rank_3,
MIN(mingrade) AS mingradeemarking,
MIN(maxgrade) AS maxgradeemarking
FROM (
SELECT
ROUND(dr.grade,2) AS grade, -- Nota final (calculada o manual via calificador)
a.grade AS maxgrade, -- Nota máxima del emarking
a.grademin AS mingrade, -- Nota mínima del emarking
CASE WHEN dr.grade is null THEN 0 -- Indicador de si la nota es null
ELSE 1
END AS attended,
CASE WHEN dr.grade >= 4 THEN 1 -- TODO: REPLACE
ELSE 0
END AS pass,
CASE WHEN dr.grade >= 0 AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 1 THEN 1 ELSE 0 END AS histogram_01,
CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 1  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 2 THEN 1 ELSE 0 END AS histogram_02,
CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 2  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 3 THEN 1 ELSE 0 END AS histogram_03,
CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 3  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 4 THEN 1 ELSE 0 END AS histogram_04,
CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 4  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 5 THEN 1 ELSE 0 END AS histogram_05,
CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 5  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 6 THEN 1 ELSE 0 END AS histogram_06,
CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 6  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 7 THEN 1 ELSE 0 END AS histogram_07,
CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 7  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 8 THEN 1 ELSE 0 END AS histogram_08,
CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 8  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 9 THEN 1 ELSE 0 END AS histogram_09,
CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 9  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 10 THEN 1 ELSE 0 END AS histogram_10,
CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 10  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 11 THEN 1 ELSE 0 END AS histogram_11,
CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 11 THEN 1 ELSE 0 END AS histogram_12,
CASE WHEN dr.grade - a.grademin < (a.grade - a.grademin) / 3 THEN 1 ELSE 0 END AS rank_1,
CASE WHEN dr.grade - a.grademin >= (a.grade - a.grademin) / 3 AND dr.grade - a.grademin  < (a.grade - a.grademin) / 2 THEN 1 ELSE 0 END AS rank_2,
CASE WHEN dr.grade - a.grademin >= (a.grade - a.grademin) / 2  THEN 1 ELSE 0 END AS rank_3,
c.category AS categoryid,
cc.name AS categoryname,
a.id AS emarkingid,
a.name AS modulename,
c.fullname AS coursename
FROM {emarking} AS a
INNER JOIN {emarking_submission} AS ss ON (a.id = ss.emarking AND a.id IN ($emarkingids))
INNER JOIN {emarking_draft} AS dr ON (dr.submissionid = ss.id AND dr.qualitycontrol=0)
INNER JOIN {course} AS c ON (a.course = c.id)
INNER JOIN {course_categories} AS cc ON (c.category = cc.id)
WHERE dr.grade is not null AND dr.status >= 20
ORDER BY emarkingid asc, dr.grade asc) AS G
GROUP BY categoryid, emarkingid
WITH ROLLUP) AS T";

$emarkingstats = $DB->get_recordset_sql ( $sql );
// Initialization of the variable data.
$data = array ();
foreach ( $emarkingstats as $stats ) {
	// if to count the categories so the table doesn't give us a subtotal for each category
	if ($totalcategories == 1 && ! strncmp ( $stats->seriesname, 'SUBTOTAL', 8 )) {
		continue;
	}
	// if to count the diferent emarkings so the table doesn't give us a total for each emarking
	if ($totalemarkings == 1 && ! strncmp ( $stats->seriesname, 'TOTAL', 5 )) {
		continue;
	}
	// Data for the table
	$data [] = array (
			$stats->seriesname,
			$stats->students,
			$stats->average,
			$stats->stdev,
			$stats->minimum,
			$stats->percentile_25,
			$stats->percentile_50,
			$stats->percentile_75,
			$stats->maximum,
			$stats->rank_1,
			$stats->rank_2,
			$stats->rank_3 
	);
}
// Create the obj table
$table = new html_table ();
// Style of the table
$table->attributes ['style'] = "width: 100%; text-align:center;";
// Table headers.
$table->head = array (
		strtoupper ( get_string ( 'course' ) ),
		strtoupper ( get_string ( 'students' ) ),
		strtoupper ( get_string ( 'average', 'mod_emarking' ) ),
		strtoupper ( get_string ( 'stdev', 'mod_emarking' ) ),
		strtoupper ( get_string ( 'min', 'mod_emarking' ) ),
		strtoupper ( get_string ( 'quartile1', 'mod_emarking' ) ),
		strtoupper ( get_string ( 'median', 'mod_emarking' ) ),
		strtoupper ( get_string ( 'quartile3', 'mod_emarking' ) ),
		strtoupper ( get_string ( 'max', 'mod_emarking' ) ),
		strtoupper ( get_string ( 'lessthan', 'mod_emarking', 3 ) ),
		strtoupper ( get_string ( 'between', 'mod_emarking', array (
				'min' => 3,
				'max' => 4 
		) ) ),
		strtoupper ( get_string ( 'greaterthan', 'mod_emarking', 4 ) ) 
);
// Alignment of the table
$table->align = array (
		'left',
		'center',
		'center',
		'center',
		'center',
		'center',
		'center',
		'center',
		'center',
		'center',
		'center',
		'center' 
);
// Fill the table with the data from the variable $data.
$table->data = $data;
// Print of the table.
echo html_writer::table ( $table );
$reportsdir = $CFG->wwwroot . '/mod/emarking/marking/emarkingreports';
?>
<script type="text/javascript" language="javascript"
	src="<?php echo $reportsdir ?>/emarkingreports.nocache.js"></script>
<div id='reports' cmid='<?php echo $cmid ?>'
	emarkingids='<?php echo $emarkingids?>'
	emarking='<?php echo $emarkingids?>' action='gradereport'
	url='<?php echo$CFG->wwwroot ?>/mod/emarking/ajax/reports.php'></div>

<?php
echo $OUTPUT->footer ();