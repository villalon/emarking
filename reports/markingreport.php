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
$url = new moodle_url ( '/mod/emarking/reports/markingreport.php', array (
		'id' => $cm->id 
) );

// Course context is used in reports
$context = context_module::instance ( $cm->id );

// Validate the user has grading capabilities
if (! has_capability ( 'mod/assign:grade', $context )) {
	print_error ( 'No tiene permisos para ver reportes de notas' );
}

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
$PAGE->navbar->add ( get_string ( 'markingreport', 'mod_emarking' ) );

echo $OUTPUT->header ();
echo $OUTPUT->heading_with_help ( get_string ( 'markingreport', 'mod_emarking' ), 'markingreport', 'mod_emarking' );

// Print eMarking tabs
echo $OUTPUT->tabtree ( emarking_tabs ( $context, $cm, $emarking ), "markingreport" );

// Get rubric instance
list ( $gradingmanager, $gradingmethod ) = emarking_validate_rubric ( $context );

// Get the rubric controller from the grading manager and method
$rubriccontroller = $gradingmanager->get_controller ( $gradingmethod );
$definition = $rubriccontroller->get_definition ();

// Calculates the number of criteria for this evaluation
$numcriteria = 0;
if ($rubriccriteria = $rubriccontroller->get_definition ()) {
	$numcriteria = count ( $rubriccriteria->rubric_criteria );
}
// Counts the total of exams
$totalsubmissions = $DB->count_records_sql ( "
		SELECT COUNT(e.id) AS total
		FROM {emarking_submission} AS e
		WHERE e.emarking = ? AND e.grade >= 0 AND e.status >= " . EMARKING_STATUS_RESPONDED, array (
		$emarking->id 
) );

if (! $totalsubmissions || $totalsubmissions == 0) {
	echo $OUTPUT->notification ( get_string ( 'nosubmissionsgraded', 'mod_emarking' ), 'notifyproblem' );
	echo $OUTPUT->footer ();
	die ();
}

$emarkingids = '' . $emarking->id;

$extracategory = optional_param ( 'categories', 0, PARAM_INT );

// Check for parallel courses

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

// Counts the total of disticts categories
$sqlcats = "SELECT 
                COUNT(DISTINCT(c.category)) as categories
                FROM {emarking} AS a
                INNER JOIN {course} AS c ON (a.course = c.id)
                WHERE a.id IN (?)";

$totalcategories = $DB->count_records_sql ( $sqlcats, array (
		$emarkingids 
) );

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
// Sql to get the quantity of tests in each state(submitted, grading, graded, regrading).
$sqlstats = "SELECT	COUNT(distinct id) AS activities,
		COUNT(DISTINCT student) AS students,
		MAX(pages) AS maxpages,
		MIN(pages) AS minpages,
		ROUND(AVG(comments), 2) AS pctmarked,
		SUM(missing) as missing,
		SUM(submitted) as submitted,
		SUM(grading) as grading,
		SUM(graded) as graded,
		SUM(regrading) as regrading
		FROM (
		SELECT	s.student,
		s.id as submissionid,
		CASE WHEN d.status < 10 THEN 1 ELSE 0 END AS missing,
		CASE WHEN d.status = 10 THEN 1 ELSE 0 END AS submitted,
		CASE WHEN d.status > 10 AND s.status < 20 THEN 1 ELSE 0 END AS grading,
		CASE WHEN d.status = 20 THEN 1 ELSE 0 END AS graded,
		CASE WHEN d.status > 20 THEN 1 ELSE 0 END AS regrading,
		d.timemodified,
		d.grade,
		d.generalfeedback,
		count(distinct p.id) as pages,
		CASE WHEN 0 = ? THEN 0 ELSE count(distinct c.id) / ? END as comments,
		count(distinct r.id) as regrades,
		nm.course,
		nm.id,
		round(SUM(l.score),2) as score,
		round(SUM(c.bonus),2) as bonus,
		s.sort
		FROM {emarking} AS nm
		INNER JOIN {emarking_submission} AS s ON (nm.id = ? AND s.emarking = nm.id)
        INNER JOIN {emarking_draft} as d ON (d.submissionid = s.id)
		INNER JOIN {emarking_page} AS p ON (p.submission = s.id)
		LEFT JOIN {emarking_comment} as c on (c.page = p.id AND c.levelid > 0 AND c.draft = d.id)
		LEFT JOIN {gradingform_rubric_levels} as l ON (c.levelid = l.id)
		LEFT JOIN {emarking_regrade} as r ON (r.draft = d.id AND r.criterion = l.criterionid AND r.accepted = 0)
		GROUP BY nm.id, s.student
) as T
		GROUP by id";

$markingstats = $DB->get_record_sql ( $sqlstats, array (
		$numcriteria,
		$numcriteria,
		$emarkingids 
) );
// Check if there is any submission graded.
if (! $markingstats) {
	echo $OUTPUT->notification ( get_string ( 'nosubmissionsgraded', 'mod_emarking' ), 'notifyproblem' );
	echo $OUTPUT->footer ();
	die ();
}
// Total submissions.
$totalsubmissions = $markingstats->submitted + $markingstats->grading + $markingstats->graded + $markingstats->regrading;
// Progress percentage.
$totalprogress = round ( $markingstats->graded / $totalsubmissions * 100, 2 );
// Checks if there is submissions graded.
if ($numcriteria == 0 || $totalsubmissions == 0) {
	echo $OUTPUT->notification ( get_string ( 'nosubmissionsgraded', 'mod_emarking' ), 'notifyproblem' );
	echo $OUTPUT->footer ();
	die ();
}
// Sql to get the amount of contribution.
$sqlcontribution = "SELECT
		e.id,
		COUNT(distinct ec.id) AS comments
		
        FROM {emarking_submission} AS s
        INNER JOIN {emarking} AS e ON (s.emarking=e.id)
        INNER JOIN {emarking_draft} AS dr ON (dr.submissionid = s.id AND dr.qualitycontrol=0)
        INNER JOIN {course_modules} AS cm ON (e.id=cm.instance AND e.id IN (?))
		INNER JOIN {course} AS co ON (cm.course=co.id)
		INNER JOIN {context} AS c ON (s.status>=10 AND cm.id = c.instanceid )
        INNER JOIN {grading_areas} AS ar ON (c.id = ar.contextid)
        INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
        INNER JOIN {grading_instances} AS i ON (d.id=i.definitionid)
        INNER JOIN {gradingform_rubric_fillings} AS f ON (i.id=f.instanceid)
        INNER JOIN {gradingform_rubric_levels} AS b ON (b.id = f.levelid)
        INNER JOIN {gradingform_rubric_criteria} AS a ON (a.id = f.criterionid)
        INNER JOIN {emarking_comment} as ec ON (b.id = ec.levelid AND ec.draft = dr.id)
        INNER JOIN {user} as u ON (ec.markerid = u.id)
		GROUP BY e.id ";
$markingcontribution = $DB->get_records_sql ( $sqlcontribution, array (
		$emarkingids 
) );
// Get the total coments.
$totalcomments = 0;
foreach ( $markingcontribution as $contribution ) {
	$totalcomments += $contribution->comments;
}
// Calculate the percentage of progression.
$progress = round ( (($totalcomments) / ($totalsubmissions * $numcriteria) * 100), 2 );
// Print a heading with the progression percetage and the published tests.
echo $OUTPUT->heading ( get_string ( 'marking', 'mod_emarking' ) . " : " . $progress . "% (" . $totalprogress . "% " . get_string ( 'published', 'mod_emarking' ) . ")", 3 );
$reportsdir = $CFG->wwwroot . '/mod/emarking/reports/reportsweb';
?>

<link rel="stylesheet" type="text/css"
	href="<?php echo $reportsdir ?>/css/Reports.css" />
<script type="text/javascript" language="javascript"
	src="<?php echo $reportsdir ?>/reports.nocache.js"></script>
<div id='reports' cmid='<?php echo $cmid ?>' action='markingreport'
	url='<?php echo $CFG->wwwroot ?>/mod/emarking/ajax/reports.php'></div>
<?php
echo $CFG->wwwroot . '/mod/emarking/ajax/reports.php';
echo $OUTPUT->footer ();