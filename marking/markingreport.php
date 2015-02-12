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
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
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
$url = new moodle_url ( '/mod/emarking/markingreport.php', array (
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
		FROM {grade_items} AS gi
		INNER JOIN {emarking_submission} AS e ON (gi.iteminstance = ? and gi.itemtype = 'mod' and gi.itemmodule = 'emarking' AND gi.iteminstance = e.emarking)
		WHERE e.grade >= 0 AND e.status >= " . EMARKING_STATUS_RESPONDED, array (
		$emarking->id 
) );

if (! $totalsubmissions || $totalsubmissions == 0) {
	echo $OUTPUT->notification ( get_string ( 'nosubmissionsgraded', 'mod_emarking' ), 'notifyproblem' );
	echo $OUTPUT->footer ();
	die ();
}

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
if ($parallels && count ( $parallels ) > 0) {
	foreach ( $parallels as $pcourse ) {
		$assid = '';
		if ($emarkingsform->get_data () && property_exists ( $emarkingsform->get_data (), "emarkingid_$pcourse->id" )) {
			eval ( "\$assid = \$emarkingsform->get_data()->emarkingid_$pcourse->id;" );
			if ($assid > 0) {
				$emarkingids .= ',' . $assid;
				$totalemarkings ++;
			}
		}
	}
}

// counts the total of disticts categories
$sqlcats = "select count(distinct(c.category)) as categories
from {emarking} as a
inner join {course} as c on (a.course = c.id)
where a.id in ($emarkingids)";

$totalcategories = $DB->count_records_sql ( $sqlcats );

$sql = "select  *,
case
when categoryid is null then 'TOTAL'
when emarkingid is null then concat('SUBTOTAL ', categoryname)
else coursename
end as seriesname
from (
select 	categoryid as categoryid,
categoryname,
emarkingid as emarkingid,
modulename,
coursename,
count(*) as students,
sum(pass) as pass,
round((sum(pass) / count(*)) * 100,2) as pass_ratio,
SUBSTRING_INDEX(
SUBSTRING_INDEX(
group_concat(grade order by grade separator ',')
, ','
, 25/100 * COUNT(*) + 1)
, ','
, -1
) as percentile_25,
SUBSTRING_INDEX(
SUBSTRING_INDEX(
group_concat(grade order by grade separator ',')
, ','
, 50/100 * COUNT(*) + 1)
, ','
, -1
) as percentile_50,
SUBSTRING_INDEX(
SUBSTRING_INDEX(
group_concat(grade order by grade separator ',')
, ','
, 75/100 * COUNT(*) + 1)
, ','
, -1
) as percentile_75,
min(grade) as minimum,
max(grade) as maximum,
round(avg(grade),2) as average,
round(stddev(grade),2) as stdev,
sum(histogram_01) as histogram_1,
sum(histogram_02) as histogram_2,
sum(histogram_03) as histogram_3,
sum(histogram_04) as histogram_4,
sum(histogram_05) as histogram_5,
sum(histogram_06) as histogram_6,
sum(histogram_07) as histogram_7,
sum(histogram_08) as histogram_8,
sum(histogram_09) as histogram_9,
sum(histogram_10) as histogram_10,
sum(histogram_11) as histogram_11,
sum(histogram_12) as histogram_12,
round(sum(rank_1)/count(*),3) as rank_1,
round(sum(rank_2)/count(*),3) as rank_2,
round(sum(rank_3)/count(*),3) as rank_3,
min(mingrade) as mingradeemarking,
min(maxgrade) as maxgradeemarking
from (
select
round(ss.grade,2) as grade, -- Nota final (calculada o manual via calificador)
i.grademax as maxgrade, -- Nota máxima del emarking
i.grademin as mingrade, -- Nota mínima del emarking
case when ss.grade is null then 0 -- Indicador de si la nota es null
else 1
end as attended,
case when ss.grade >= i.gradepass then 1
else 0
end as pass,
case when ss.grade >= 0 AND ss.grade < i.grademin + (i.grademax - i.grademin) / 12 * 1 then 1 else 0 end as histogram_01,
case when ss.grade >= i.grademin + (i.grademax - i.grademin) / 12 * 1  AND ss.grade < i.grademin + (i.grademax - i.grademin) / 12 * 2 then 1 else 0 end as histogram_02,
case when ss.grade >= i.grademin + (i.grademax - i.grademin) / 12 * 2  AND ss.grade < i.grademin + (i.grademax - i.grademin) / 12 * 3 then 1 else 0 end as histogram_03,
case when ss.grade >= i.grademin + (i.grademax - i.grademin) / 12 * 3  AND ss.grade < i.grademin + (i.grademax - i.grademin) / 12 * 4 then 1 else 0 end as histogram_04,
case when ss.grade >= i.grademin + (i.grademax - i.grademin) / 12 * 4  AND ss.grade < i.grademin + (i.grademax - i.grademin) / 12 * 5 then 1 else 0 end as histogram_05,
case when ss.grade >= i.grademin + (i.grademax - i.grademin) / 12 * 5  AND ss.grade < i.grademin + (i.grademax - i.grademin) / 12 * 6 then 1 else 0 end as histogram_06,
case when ss.grade >= i.grademin + (i.grademax - i.grademin) / 12 * 6  AND ss.grade < i.grademin + (i.grademax - i.grademin) / 12 * 7 then 1 else 0 end as histogram_07,
case when ss.grade >= i.grademin + (i.grademax - i.grademin) / 12 * 7  AND ss.grade < i.grademin + (i.grademax - i.grademin) / 12 * 8 then 1 else 0 end as histogram_08,
case when ss.grade >= i.grademin + (i.grademax - i.grademin) / 12 * 8  AND ss.grade < i.grademin + (i.grademax - i.grademin) / 12 * 9 then 1 else 0 end as histogram_09,
case when ss.grade >= i.grademin + (i.grademax - i.grademin) / 12 * 9  AND ss.grade < i.grademin + (i.grademax - i.grademin) / 12 * 10 then 1 else 0 end as histogram_10,
case when ss.grade >= i.grademin + (i.grademax - i.grademin) / 12 * 10  AND ss.grade < i.grademin + (i.grademax - i.grademin) / 12 * 11 then 1 else 0 end as histogram_11,
case when ss.grade >= i.grademin + (i.grademax - i.grademin) / 12 * 11 then 1 else 0 end as histogram_12,
case when ss.grade - i.grademin < (i.grademax - i.grademin) / 3 then 1 else 0 end as rank_1,
case when ss.grade - i.grademin >= (i.grademax - i.grademin) / 3 AND ss.grade - i.grademin  < (i.grademax - i.grademin) / 2 then 1 else 0 end as rank_2,
case when ss.grade - i.grademin >= (i.grademax - i.grademin) / 2  then 1 else 0 end as rank_3,
c.category as categoryid,
cc.name as categoryname,
i.iteminstance as emarkingid,
a.name as modulename,
c.fullname as coursename
from mdl_grade_items as i
inner join mdl_emarking as a on (i.itemtype = 'mod' AND i.itemmodule = 'emarking' and i.iteminstance in ($emarkingids) AND i.iteminstance = a.id)
inner join mdl_course as c on (i.courseid = c.id)
inner join mdl_course_categories as cc on (c.category = cc.id)
inner join mdl_emarking_submission as ss on (a.id = ss.emarking)
where ss.grade is not null AND ss.status >= 20
order by emarkingid asc, ss.grade asc) as G
group by categoryid, emarkingid
with rollup) as T";

$emarkingstats = $DB->get_recordset_sql ( $sql );

$mingrade = 0;
$maxgrade = 0;
$averages = '';

$histogram_courses = '';
$histogram_totals = '';
$histograms = array ();
$histograms_totals = array ();
$histogramlabels = array ();

$pass_ratio = '';

$databoxplot = "";

$data = array ();
foreach ( $emarkingstats as $stats ) {
	
	if ($totalcategories == 1 && ! strncmp ( $stats->seriesname, 'SUBTOTAL', 8 )) {
		continue;
	}
	if ($totalemarkings == 1 && ! strncmp ( $stats->seriesname, 'TOTAL', 5 )) {
		continue;
	}
	
	if (! strncmp ( $stats->seriesname, 'SUBTOTAL', 8 ) || ! strncmp ( $stats->seriesname, 'TOTAL', 5 ))
		$histogram_totals .= "'$stats->seriesname',";
	else
		$histogram_courses .= "'$stats->seriesname (N=$stats->students)',";
	for($i = 1; $i <= 12; $i ++) {
		$histogramvalue = '';
		eval ( "\$histogramvalue = \$stats->histogram_$i;" );
		if (! strncmp ( $stats->seriesname, 'SUBTOTAL', 8 ) || ! strncmp ( $stats->seriesname, 'TOTAL', 5 )) {
			if (! isset ( $histograms_totals [$i] ))
				$histograms_totals [$i] = $histogramvalue . ',';
			else
				$histograms_totals [$i] .= $histogramvalue . ',';
		} else {
			if (! isset ( $histograms [$i] ))
				$histograms [$i] = $histogramvalue . ',';
			else
				$histograms [$i] .= $histogramvalue . ',';
		}
		
		if ($i % 2 != 0) {
			if ($i <= 6) {
				$histogramlabels [$i] = '< ' . ($stats->mingradeemarking + ($stats->maxgradeemarking - $stats->mingradeemarking) / 12 * $i);
			} else {
				$histogramlabels [$i] = '>= ' . ($stats->mingradeemarking + ($stats->maxgradeemarking - $stats->mingradeemarking) / 12 * ($i - 1));
			}
		} else {
			$histogramlabels [$i] = '';
		}
	}
	
	// Set the values for the box plot
	$series = $stats->seriesname;
	$min = $stats->minimum;
	$max = $stats->maximum;
	$mean = $stats->average;
	$firstquantile = $stats->percentile_25;
	$thirdquantile = $stats->percentile_75;
	$median = $stats->percentile_50;
	
	$databoxplot .= '["' . $series . '",' . $min . ',' . $firstquantile . ',' . $thirdquantile . ',' . $max . ',' . $median . ',' . $mean . '],';
	
	// Pass Ratio Graph data
	$pass_ratio .= "['$stats->seriesname (N=$stats->students)',$stats->rank_1,$stats->rank_2,$stats->rank_3],";
	
	// Get the diferent indicators from the general stats query and pass it on to the table
	$mingrade = $stats->mingradeemarking;
	$maxgrade = $stats->maxgradeemarking;
	$averages .= "['$stats->seriesname (N=$stats->students)',$stats->average, $stats->minimum, $stats->maximum],";
	
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

$table = new html_table ();
$table->attributes ['style'] = "width: 100%; text-align:center;";
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
$table->data = $data;
echo html_writer::table ( $table );

$markingstats = $DB->get_record_sql ( "
		SELECT	COUNT(distinct id) AS activities,
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
		CASE WHEN s.status < 10 THEN 1 ELSE 0 END AS missing,
		CASE WHEN s.status = 10 THEN 1 ELSE 0 END AS submitted,
		CASE WHEN s.status > 10 AND s.status < 20 THEN 1 ELSE 0 END AS grading,
		CASE WHEN s.status = 20 THEN 1 ELSE 0 END AS graded,
		CASE WHEN s.status > 20 THEN 1 ELSE 0 END AS regrading,
		s.timemodified,
		s.grade,
		s.generalfeedback,
		count(distinct p.id) as pages,
		CASE WHEN 0 = $numcriteria THEN 0 ELSE count(distinct c.id) / $numcriteria END as comments,
		count(distinct r.id) as regrades,
		nm.course,
		nm.id,
		round(sum(l.score),2) as score,
		round(sum(c.bonus),2) as bonus,
		s.sort
		FROM {emarking} AS nm
		INNER JOIN {emarking_submission} AS s ON (nm.id = :emarkingid AND s.emarking = nm.id)
		INNER JOIN {emarking_page} AS p ON (p.submission = s.id)
		LEFT JOIN {emarking_comment} as c on (c.page = p.id AND c.levelid > 0)
		LEFT JOIN {gradingform_rubric_levels} as l ON (c.levelid = l.id)
		LEFT JOIN {emarking_regrade} as r ON (r.submission = s.id AND r.criterion = l.criterionid AND r.accepted = 0)
		GROUP BY nm.id, s.student
) as T
		GROUP by id", array (
		'emarkingid' => $emarkingids 
) );

if (! $markingstats) {
	echo $OUTPUT->notification ( get_string ( 'nosubmissionsgraded', 'mod_emarking' ), 'notifyproblem' );
	echo $OUTPUT->footer ();
	die ();
}

$totalsubmissions = $markingstats->submitted + $markingstats->grading + $markingstats->graded + $markingstats->regrading;
$datatable = "['','" . emarking_get_string_for_status ( EMARKING_STATUS_ABSENT ) . "','" . emarking_get_string_for_status ( EMARKING_STATUS_SUBMITTED ) . "','" . emarking_get_string_for_status ( EMARKING_STATUS_GRADING ) . "','" . emarking_get_string_for_status ( EMARKING_STATUS_RESPONDED ) . "',	'" . emarking_get_string_for_status ( EMARKING_STATUS_REGRADING ) . "'],
			['',($markingstats->missing) *100/$totalsubmissions, ($markingstats->submitted) *100/$totalsubmissions,($markingstats->grading) *100/$totalsubmissions,($markingstats->graded) *100/$totalsubmissions,($markingstats->regrading)*100/$totalsubmissions]";

$totalprogress = round ( $markingstats->graded / $totalsubmissions * 100, 2 );

if ($numcriteria == 0 || $totalsubmissions == 0) {
	echo $OUTPUT->notification ( get_string ( 'nosubmissionsgraded', 'mod_emarking' ), 'notifyproblem' );
	echo $OUTPUT->footer ();
	die ();
}
$sqlstatscriterion = "SELECT  a.id,
							co.fullname AS course,
							e.name,
							d.name,
							a.description,
							COUNT(distinct s.id) AS submissions,
							COUNT(distinct ec.id) AS comments,
							COUNT(distinct r.id) AS regrades
					        
					        FROM {emarking_submission} AS s
					        INNER JOIN {emarking} AS e ON (s.emarking=e.id)
							INNER JOIN {course_modules} AS cm ON (e.id=cm.instance)
							INNER JOIN {course} AS co ON (cm.course=co.id)
					        INNER JOIN {context} AS c ON (s.status>=10 AND cm.id = c.instanceid AND cm.id = ? )
					        INNER JOIN {grading_areas} AS ar ON (c.id = ar.contextid)
					        INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
					        INNER JOIN {grading_instances} AS i ON (d.id=i.definitionid)
					        INNER JOIN {gradingform_rubric_fillings} AS f ON (i.id=f.instanceid)
					        INNER JOIN {gradingform_rubric_levels} AS b ON (b.id = f.levelid)
					        INNER JOIN {gradingform_rubric_criteria} AS a ON (a.id = f.criterionid)
					        INNER JOIN {emarking_comment} as ec ON (b.id = ec.levelid)
					        LEFT JOIN {emarking_regrade} as r ON (r.submission = s.id AND r.criterion = a.id)
							GROUP BY a.id
							ORDER BY a.sortorder";
$markingstatspercriterion = $DB->get_records_sql ( $sqlstatscriterion, array (
		$cm->id 
) );

$datatablecriteria = "['Criterio', '" . emarking_get_string_for_status ( EMARKING_STATUS_RESPONDED ) . "', '" . emarking_get_string_for_status ( EMARKING_STATUS_REGRADING ) . "', '" . emarking_get_string_for_status ( EMARKING_STATUS_GRADING ) . "'],";

foreach ( $markingstatspercriterion as $statpercriterion ) {
	$description = trim ( preg_replace ( '/\s\s+/', ' ', $statpercriterion->description ) );
	$datatablecriteria .= "['$description', " . round ( ($statpercriterion->comments - $statpercriterion->regrades) * 100 / $totalsubmissions, 2 ) . "," . round ( $statpercriterion->regrades * 100 / $totalsubmissions, 2 ) . ", " . round ( ($statpercriterion->submissions - $statpercriterion->comments) * 100 / $totalsubmissions, 2 ) . "],";
}
$sqlcontributorstats = "SELECT
		e.id,
		ec.markerid,
		co.fullname AS course,
		CONCAT(u.firstname , ' ', u.lastname) AS markername,
		COUNT(distinct ec.id) AS comments
		
        FROM {emarking_submission} AS s
        INNER JOIN {emarking} AS e ON (s.emarking=e.id)
		INNER JOIN {course_modules} AS cm ON (e.id=cm.instance AND e.id IN ($emarkingids))
		INNER JOIN {course} AS co ON (cm.course=co.id)
		INNER JOIN {context} AS c ON (s.status>=10 AND cm.id = c.instanceid )
        INNER JOIN {grading_areas} AS ar ON (c.id = ar.contextid)
        INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
        INNER JOIN {grading_instances} AS i ON (d.id=i.definitionid)
        INNER JOIN {gradingform_rubric_fillings} AS f ON (i.id=f.instanceid)
        INNER JOIN {gradingform_rubric_levels} AS b ON (b.id = f.levelid)
        INNER JOIN {gradingform_rubric_criteria} AS a ON (a.id = f.criterionid)
        INNER JOIN {emarking_comment} as ec ON (b.id = ec.levelid)
        INNER JOIN {user} as u ON (ec.markerid = u.id)
		GROUP BY e.id ";
$markingstatstotalcontribution = $DB->get_records_sql ( $sqlcontributorstats );
$getmarkers = "SELECT
e.id,
ec.markerid,
co.fullname AS course,
CONCAT(u.firstname , ' ', u.lastname) AS markername,
COUNT(distinct ec.id) AS comments

FROM {emarking_submission} AS s
INNER JOIN {emarking} AS e ON (s.emarking=e.id)
INNER JOIN {course_modules} AS cm ON (e.id=cm.instance AND e.id IN ($emarkingids))
INNER JOIN {course} AS co ON (cm.course=co.id)
INNER JOIN {context} AS c ON (s.status>=10 AND cm.id = c.instanceid )
INNER JOIN {grading_areas} AS ar ON (c.id = ar.contextid)
INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
INNER JOIN {grading_instances} AS i ON (d.id=i.definitionid)
INNER JOIN {gradingform_rubric_fillings} AS f ON (i.id=f.instanceid)
INNER JOIN {gradingform_rubric_levels} AS b ON (b.id = f.levelid)
INNER JOIN {gradingform_rubric_criteria} AS a ON (a.id = f.criterionid)
INNER JOIN {emarking_comment} as ec ON (b.id = ec.levelid)
INNER JOIN {user} as u ON (ec.markerid = u.id)
GROUP BY ec.markerid ";
$allmarkers = $DB->get_records_sql ( $getmarkers );
// Contribution per contributioner

$datatabletotalcontributioner = "['','" . emarking_get_string_for_status ( EMARKING_STATUS_GRADING ) . "',";
$totalcomments = 0;
foreach ( $markingstatstotalcontribution as $contribution ) {
	$totalcomments += $contribution->comments;
}

$datatabletotalcontributions = "[''," . round ( (($totalsubmissions * $numcriteria - $totalcomments) * 100 / ($totalsubmissions * $numcriteria)), 2 ) . ",";

foreach ( $markingstatstotalcontribution as $contributioner ) {
	$datatabletotalcontributioner .= "'" . $contributioner->markername . "', ";
	$datatabletotalcontributions .= round ( ($contributioner->comments) * 100 / ($totalsubmissions * $numcriteria), 2 ) . ", ";
}
$datatabletotalcontributioner .= "]";
$datatabletotalcontributions .= "]";
$datatabletotalcontribution = $datatabletotalcontributioner . ", " . $datatabletotalcontributions;

$markingstatspermarker = $DB->get_recordset_sql ( "
		SELECT
		a.id,
		a.description,
		T.*
		FROM {course_modules} AS c
		INNER JOIN {context} AS mc ON (c.id = :cmid AND c.id = mc.instanceid)
		INNER JOIN {grading_areas} AS ar ON (mc.id = ar.contextid)
		INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
		INNER JOIN {gradingform_rubric_criteria} AS a ON (d.id = a.definitionid)
		INNER JOIN {emarking_marker_criterion} AS emc ON (emc.emarking = c.instance)
		INNER JOIN (
		SELECT bb.criterionid,
		ec.markerid,
		u.lastname AS markername,
		ROUND(AVG(bb.score),2) as avgscore,
		ROUND(STDDEV(bb.score),2) as stdevscore,
		ROUND(MIN(bb.score),2) as minscore,
		ROUND(MAX(bb.score),2) as maxscore,
		ROUND(AVG(ec.bonus),2) AS avgbonus,
		ROUND(STDDEV(ec.bonus),2) AS stdevbonus,
		ROUND(MAX(ec.bonus),2) AS maxbonus,
		ROUND(MIN(ec.bonus),2) AS minbonus,
		COUNT(distinct ec.id) AS comments,
		COUNT(distinct r.id) AS regrades
		FROM
		{emarking} AS e
		INNER JOIN {emarking_submission} AS s ON (e.id = :emarkingid AND s.emarking = e.id)
		INNER JOIN {emarking_page} AS p ON (p.submission = s.id)
		LEFT JOIN {emarking_comment} as ec on (ec.page = p.id)
		LEFT JOIN {gradingform_rubric_levels} AS bb ON (ec.levelid = bb.id)
		LEFT JOIN {emarking_regrade} as r ON (r.submission = s.id AND r.criterion = bb.criterionid)
		LEFT JOIN {user} as u ON (ec.markerid = u.id)
		WHERE s.status >= 10
		GROUP BY ec.markerid, bb.criterionid) AS T
		ON (a.id = T.criterionid AND emc.marker = T.markerid)
		GROUP BY T.markerid, a.id
		", array (
		'cmid' => $cm->id,
		'emarkingid' => $emarking->id 
) );
$datamarkersavailable = false;
$datatablemarkers = "";

$datatablecontribution = "['Corrector', '" . emarking_get_string_for_status ( EMARKING_STATUS_RESPONDED ) . "', '" . emarking_get_string_for_status ( EMARKING_STATUS_REGRADING ) . "', '" . emarking_get_string_for_status ( EMARKING_STATUS_GRADING ) . "'],";

foreach ( $markingstatspermarker as $permarker ) {
	$description = trim ( preg_replace ( '/\s\s+/', ' ', $permarker->description ) );
	$datatablemarkers .= "['$permarker->markername $description',
	" . ($permarker->minscore) . ",
	" . ($permarker->avgscore - $permarker->stdevscore) . ",
	" . ($permarker->avgscore + $permarker->stdevscore) . ",
	" . ($permarker->maxscore) . ",
	],";
	
	$datatablecontribution .= "['$permarker->markername $description',
								" . round ( ($permarker->comments - $permarker->regrades) * 100 / $totalsubmissions, 2 ) . ",
								" . round ( ($permarker->regrades) * 100 / $totalsubmissions, 2 ) . ",
								" . round ( ($totalsubmissions - $permarker->comments) * 100 / $totalsubmissions, 2 ) . "
	],";
	
	$datamarkersavailable = true;
}
$progress = round ( (($totalcomments) / ($totalsubmissions * $numcriteria) * 100), 2 );
echo $OUTPUT->heading ( get_string ( 'marking', 'mod_emarking' ) . " : " . $progress . "% (" . $totalprogress . "% publicadas)", 3 );
//$colors = '"#4D4D4D","#5DA5DA","#FAA43A","#60BD68","#F17CB0","#B2912F","#B276B2","#DECF3F","#F15854","#009987","#008270","#006D66","#006056","#008272","#006B5B","#005951","#00493F","#004F42","#004438","#BAEAD6","#A0E5CE","#5EDDC1","#00997C","#007C66","#006854","#9BDBC1","#8EE2BC","#7AD1B5","#54D8A8","#00B28C"';
$reportsdir = $CFG->wwwroot. '/mod/emarking/reports';
?>

	 <link rel="stylesheet" type="text/css"  href= "<?php echo $reportsdir ?>/css/reports.css"/>
    <script type="text/javascript" language="javascript"src="<?php echo $reportsdir ?>/reports.nocache.js"></script>
		<div id='reports' cmid='<?php echo $cmid ?>'
			 action='markingreport' 
			 url='<?php echo $CFG->wwwroot ?>/mod/emarking/ajax/reports.php' ></div>
<?php
//echo '<div id="reports" cmid="'.$cmid.'" action="markingreport"url="'.$CFG->dirroot.'/mod/emarking/ajax/reports.php></div>';
echo $CFG->wwwroot.'/mod/emarking/ajax/reports.php';
echo $OUTPUT->footer();