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
require_once ($CFG->dirroot . '/mod/emarking/reports/forms/gradereport_form.php');
require_once ($CFG->dirroot . '/mod/emarking/reports/statstable.php');

global $DB, $USER;

// Get course module id
$cmid = required_param ( 'id', PARAM_INT );

// Validate course module
if (! $cm = get_coursemodule_from_id ( 'emarking', $cmid )) {
	print_error ( get_string('invalidcoursemodule','mod_emarking'));
}

// Validate module
if (! $emarking = $DB->get_record ( 'emarking', array (
		'id' => $cm->instance 
) )) {
	print_error ( get_string('invalidexamid','mod_emarking') );
}
// Validate course
if (! $course = $DB->get_record ( 'course', array (
		'id' => $emarking->course 
) )) {
	print_error ( get_string('invalidcourseid','mod_emarking') );
}

// URLs for current page
$url = new moodle_url ( '/mod/emarking/reports/markingreport.php', array (
		'id' => $cm->id 
) );

// Course context is used in reports
$context = context_module::instance ( $cm->id );

// Validate the user has grading capabilities
require_capability('mod/assign:grade', $context);

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
echo $OUTPUT->heading_with_help ( get_string ( 'markingreport', 'mod_emarking' ), get_string ( 'markingreport', 'mod_emarking' ), 'mod_emarking' );

// Print eMarking tabs
echo $OUTPUT->tabtree ( emarking_tabs ( $context, $cm, $emarking ), get_string ( 'markingreport', 'mod_emarking' ) );

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
//Initializatetion of the variable $emarkingidsfortable, its an array with all the parallels ids, this will be used in the stats table.
$emarkingidsfortable=array();
$emarkingidsfortable[0]=$emarking->id;
// Check for parallel courses
if ($CFG->emarking_parallelregex) {
	$parallels = emarking_get_parallel_courses ( $course, $CFG->emarking_parallelregex );
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
/*
 * This if bring all the parallel courses that were match in the regex and concatenate each other with a coma "," in between,
 * With this if the variable $parallels exists and count how manny id are inside the array to check if they are more than 0,
 * then it check if the the test exist and check their properties, if the evaluation of this match it brings their ids, and concatenates them.
 */
$totalemarkings = 1;
if ($parallels && count ( $parallels ) > 0) {
	foreach ( $parallels as $pcourse ) {
		$parallelids = '';
		if ($emarkingsform->get_data () && property_exists ( $emarkingsform->get_data (), "emarkingid_$pcourse->id" )) {
			eval ( "\$parallelids = \$emarkingsform->get_data()->emarkingid_$pcourse->id;" );
			if ($parallelids > 0) {
				$emarkingids .= ',' . $parallelids;
				$emarkingidsfortable[$totalemarkings]=$parallelids;
				$totalemarkings ++;
			}
		}
	}
}

// Print the stats table
echo get_stats_table($emarkingidsfortable, $totalemarkings);

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
		COUNT(distinct p.id) as pages,
		CASE WHEN 0 = ? THEN 0 ELSE COUNT(distinct c.id) / ? END as comments,
		count(distinct r.id) as regrades,
		nm.course,
		nm.id,
		ROUND(SUM(l.score),2) as score,
		ROUND(SUM(c.bonus),2) as bonus,
		s.sort
		FROM {emarking} AS nm
		INNER JOIN {emarking_submission} AS s ON (nm.id = ? AND s.emarking = nm.id)
        INNER JOIN {emarking_draft} as d ON (d.submissionid = s.id)
		INNER JOIN {emarking_page} AS p ON (p.submission = s.id)
		LEFT JOIN {emarking_comment} as c ON (c.page = p.id AND c.levelid > 0 AND c.draft = d.id)
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