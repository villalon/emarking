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
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/config.php');
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/mod/emarking/locallib.php');
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

// Print eMarking tabs
echo $OUTPUT->tabtree ( emarking_tabs ( $context, $cm, $emarking ), "report" );

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

// Get the grading manager, then method and finally controller
$gradingmanager = get_grading_manager ( $context, 'mod_emarking', 'attempt' );
$gradingmethod = $gradingmanager->get_active_method ();
$rubriccontroller = $gradingmanager->get_controller ( $gradingmethod );
$definition = $rubriccontroller->get_definition ();
// Search for stats regardig the exames (eg: max, min, number of students,etc)
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

// Gets the stats by criteria
$sqlcriteria = '
				SELECT co.fullname,
				co.id AS courseid,
				s.emarking AS emarkingid,
				a.id AS criterionid,
				a.description,
				round(avg(b.score),1) AS avgscore,
				round(stddev(b.score),1) AS stdevscore,
				round(min(b.score),1) AS minscore,
				round(max(b.score),1) AS maxscore,
				round(avg(b.score)/t.maxscore,1) AS effectiveness,
				t.maxscore AS maxcriterionscore

				FROM {emarking_submission} AS s
				INNER JOIN {emarking} AS e ON s.emarking=e.id
				INNER JOIN {course_modules} AS cm ON e.id=cm.instance
				INNER JOIN {context} AS c ON cm.id=c.instanceid
				INNER JOIN {grading_areas} AS ga ON c.id=ga.contextid
				INNER JOIN {grading_definitions} AS gd ON ga.id=gd.areaid
				INNER JOIN {grading_instances} AS i ON (gd.id=i.definitionid  AND s.emarking in (' . $emarkingids . ') AND s.status >= 20)
				INNER JOIN {gradingform_rubric_fillings} AS f ON i.id=f.instanceid
				INNER JOIN {gradingform_rubric_criteria} AS a ON f.criterionid=a.id
				INNER JOIN {gradingform_rubric_levels} AS b ON f.levelid=b.id
				INNER JOIN (SELECT s.id AS emarkingid,
				            a.id AS criterionid,
				            max(l.score) AS maxscore
				            FROM {emarking} AS s
							INNER JOIN {course_modules} AS cm ON (s.id = cm.instance)
							INNER JOIN {context} AS c ON (c.instanceid = cm.id)
							INNER JOIN {grading_areas} AS ar ON (ar.contextid = c.id)
							INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
							INNER JOIN {gradingform_rubric_criteria} AS a ON (d.id = a.definitionid)
							INNER JOIN {gradingform_rubric_levels} AS l ON (a.id = l.criterionid)
							GROUP BY s.id, criterionid) AS t ON (s.emarking=t.emarkingid AND a.id = t.criterionid)
				INNER JOIN {course} AS co ON e.course=co.id
				GROUP BY s.emarking,a.id
				ORDER BY a.description,emarkingid';

$criteriastats = $DB->get_recordset_sql ( $sqlcriteria );

$forcount = $DB->get_recordset_sql ( $sqlcriteria ); // run the sql again to get the count
$count = iterator_count ( $forcount );

$parallels_names_criteria = '';
$effectivenessnum = - 1;
$effectiveness [0] = '';
$lastdescription = random_string ();
$lastcriteria = '';
$lastcourse = '';
$parallels_ids = array ();
foreach ( $criteriastats as $stats ) {
	
	if (! isset ( $parallels_ids [$stats->courseid] )) {
		$parallels_names_criteria .= "'$stats->fullname (N=$count)',";
		$parallels_ids [$stats->courseid] = $stats->fullname;
	}
	$description = trim ( preg_replace ( '/\s\s+/', ' ', $stats->description ) );
	$criteriaid = $stats->criterionid;
	// FIXME arreglar cuando el nombre de 2 descripciones es la misma
	if ($lastdescription !== $description) {
		$effectivenessnum ++;
		if ($effectivenessnum > 0) {
			$effectiveness [$effectivenessnum - 1] .= "]";
		}
		$effectiveness [$effectivenessnum] = "['$description', ";
		$lastdescription = $description;
	}
	$effectiveness [$effectivenessnum] .= $stats->effectiveness . ', ';
}

if ($effectivenessnum >= 0) {
	$effectiveness [$effectivenessnum] .= ']';
}
// STATS PER CRITERIA GRAPH DATA
$effectivenessstring = "[\n['Criterio', " . $parallels_names_criteria . "],";
foreach ( $effectiveness as $effectiverow ) {
	$effectivenessstring .= "\n" . $effectiverow . ", ";
}
$effectivenessstring .= " ] ";

// Data stats Table
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
$reportsdir = $CFG->wwwroot. '/mod/emarking/reports';
?>

    <link rel="stylesheet" type="text/css"  href= "<?php echo $reportsdir ?>/css/reports.css"/>
    <script type="text/javascript" language="javascript"src="<?php echo $reportsdir ?>/reports.nocache.js"></script>
	<div id='reports' cmid='<?php echo $cmid ?>'
		 action='gradereport' 
		 url='<?php echo$CFG->wwwroot ?>/mod/emarking/ajax/reports.php' ></div>

<?php
echo $OUTPUT->footer ();





