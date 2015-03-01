<?php
global $CFG;
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ($CFG->dirroot . '/mod/emarking/reports/forms/gradereport_form.php');
//require_once ($CFG->dirroot . '/mod/emarking/locallib.php');

function get_status($cmid, $emarkingid) {
	global $DB;
	$context = context_module::instance ( $cmid );
	
	list ( $gradingmanager, $gradingmethod ) = emarking_validate_rubric ( $context );
	$rubriccontroller = $gradingmanager->get_controller ( $gradingmethod );
	$definition = $rubriccontroller->get_definition ();
	// Calculates the number of criteria for this evaluation
	
	$numcriteria = 0;
	if ($rubriccriteria = $rubriccontroller->get_definition ()) {
		$numcriteria = count ( $rubriccriteria->rubric_criteria );
	}
	
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
			INNER JOIN {emarking_draft} AS dr ON (dr.submissionid = s.id AND dr.qualitycontrol = 0)
	        INNER JOIN {emarking_page} AS p ON (p.submission = s.id)
			LEFT JOIN {emarking_comment} as c on (c.page = p.id AND c.levelid > 0 AND c.draft = dr.id)
			LEFT JOIN {gradingform_rubric_levels} as l ON (c.levelid = l.id)
			LEFT JOIN {emarking_regrade} as r ON (r.draft = dr.id AND r.criterion = l.criterionid AND r.accepted = 0)
			GROUP BY nm.id, s.student
	) as T
			GROUP by id", array (
			'emarkingid' => $emarkingid 
	) );
	$grading [] = array (
			'minpages' => $markingstats->minpages,
			'maxpages' => $markingstats->maxpages,
			'activities' => $markingstats->activities,
			'pctmarked' => $markingstats->pctmarked,
			'missing' => $markingstats->missing,
			'submitted' => $markingstats->submitted,
			'grading' => $markingstats->grading,
			'graded' => $markingstats->graded,
			'regrading' => $markingstats->regrading 
	);
	
	return $grading;
}
function get_totalsubmissions($cmid, $emarkingid) {
	$grading = get_status ( $cmid, $emarkingid );
	$totalsubmissions = $grading [0] ['missing'] + $grading [0] ['submitted'] + $grading [0] ['grading'] + $grading [0] ['graded'] + $grading [0] ['regrading'];
	return $totalsubmissions;
}
function get_markers($cmid, $emarkingid) {
	global $DB;
	$totalsubmissions = get_totalsubmissions ( $cmid, $emarkingid );
	$sqlcontributorstats = "SELECT
		ec.markerid,
		CONCAT(u.firstname , ' ', u.lastname) AS markername,
		COUNT(distinct ec.id) AS comments
        FROM {emarking_submission} AS s
        INNER JOIN {emarking} AS e ON (e.id=? AND s.emarking=e.id)
		INNER JOIN {emarking_draft} AS dr ON (dr.submissionid = s.id AND dr.qualitycontrol = 0)
	    INNER JOIN {course_modules} AS cm ON (e.id=cm.instance)
        INNER JOIN {context} AS c ON (s.status>=10 AND cm.id = c.instanceid )
        INNER JOIN {grading_areas} AS ar ON (c.id = ar.contextid)
        INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
        INNER JOIN {grading_instances} AS i ON (d.id=i.definitionid)
        INNER JOIN {gradingform_rubric_fillings} AS f ON (i.id=f.instanceid)
        INNER JOIN {gradingform_rubric_levels} AS b ON (b.id = f.levelid)
        INNER JOIN {gradingform_rubric_criteria} AS a ON (a.id = f.criterionid)
        INNER JOIN {emarking_comment} as ec ON (b.id = ec.levelid AND ec.draft = dr.id)
        INNER JOIN {user} as u ON (ec.markerid = u.id)
		GROUP BY ec.markerid";
	$markingstatstotalcontribution = $DB->get_records_sql ( $sqlcontributorstats, array (
			$emarkingid 
	) );
	$contributioners = array ();
	$contributions = array ();
	
	foreach ( $markingstatstotalcontribution as $contributioner ) {
		$contributioners [] = array (
				"user" => $contributioner->markername 
		);
	}
	return $contributioners;
}
function get_contribution_per_marker($cmid, $emarkingid) {
	global $DB;
	$context = context_module::instance ( $cmid );
	
	list ( $gradingmanager, $gradingmethod ) = emarking_validate_rubric ( $context );
	$rubriccontroller = $gradingmanager->get_controller ( $gradingmethod );
	$definition = $rubriccontroller->get_definition ();
	// Calculates the number of criteria for this evaluation
	
	$numcriteria = 0;
	if ($rubriccriteria = $rubriccontroller->get_definition ()) {
		$numcriteria = count ( $rubriccriteria->rubric_criteria );
	}
	$totalsubmissions = get_totalsubmissions ( $cmid, $emarkingid );
	$sqlcontributorstats = "SELECT
		ec.markerid,
		CONCAT(u.firstname , ' ', u.lastname) AS markername,
		COUNT(distinct ec.id) AS comments
        FROM {emarking_submission} AS s
        INNER JOIN {emarking} AS e ON (s.emarking=e.id)
		INNER JOIN {emarking_draft} AS dr ON (dr.submissionid = s.id AND dr.qualitycontrol = 0)
	    INNER JOIN {course_modules} AS cm ON (e.id=cm.instance AND e.id=?)
        INNER JOIN {context} AS c ON (s.status>=10 AND cm.id = c.instanceid )
        INNER JOIN {grading_areas} AS ar ON (c.id = ar.contextid)
        INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
        INNER JOIN {grading_instances} AS i ON (d.id=i.definitionid)
        INNER JOIN {gradingform_rubric_fillings} AS f ON (i.id=f.instanceid)
        INNER JOIN {gradingform_rubric_levels} AS b ON (b.id = f.levelid)
        INNER JOIN {gradingform_rubric_criteria} AS a ON (a.id = f.criterionid)
        INNER JOIN {emarking_comment} as ec ON (b.id = ec.levelid AND ec.draft=dr.id)
        INNER JOIN {user} as u ON (ec.markerid = u.id)
		GROUP BY ec.markerid";
	$markingstatstotalcontribution = $DB->get_records_sql ( $sqlcontributorstats, array (
			$emarkingid 
	) );
	$contributioners = array ();
	$contributions = array ();
	
	foreach ( $markingstatstotalcontribution as $contributioner ) {
		
		$contributions [] = array (
				"contrib" => round ( ($contributioner->comments) * 100 / ($totalsubmissions * $numcriteria), 2 ) 
		);
	}
	return $contributions;
}
function get_marks($cmid, $emarkingid, $extracategory, $ids) {
	global $DB, $CFG;
	
	if (! $emarking = $DB->get_record ( 'emarking', array (
			'id' => $emarkingid 
	) )) {
		print_error ( 'Prueba inválida' );
	}
	if (! $cm = get_coursemodule_from_id ( 'emarking', $cmid )) {
		print_error ( 'Módulo inválido' );
	}
	
	// Validate course
	if (! $course = $DB->get_record ( 'course', array (
			'id' => $emarking->course 
	) )) {
		print_error ( 'Curso inválido' );
	}
	require_login ( $course->id );
	if (isguestuser ()) {
		die ();
	}
	// Getting context
	$context = context_module::instance ( $cmid );
	$emarkingids = '' . $emarking->id;
	
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
	//var_dump($emarkingids);
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
	a.id as emarkingid,
	a.name as modulename,
	c.fullname as coursename
	from {emarking} AS a
	INNER JOIN {grade_items} AS i on (i.itemtype = 'mod' AND i.itemmodule = 'emarking' and i.iteminstance in ( '.$ids.') AND i.iteminstance = a.id)
	INNER JOIN {emarking_submission} AS ss on (a.id = ss.emarking)
	INNER JOIN {emarking_draft} AS dr ON (dr.submissionid = ss.id AND dr.qualitycontrol=0)
	INNER JOIN {course} AS c on (i.courseid = c.id)
	INNER JOIN {course_categories} AS cc on (c.category = cc.id)
	WHERE dr.grade is not null AND dr.status >= 20
	ORDER BY emarkingid asc, dr.grade asc) as G
	GROUP BY categoryid, emarkingid
	with rollup) as T";
	
	$emarkingstats = $DB->get_recordset_sql ( $sql );
	
	$histogram_courses = '';
	$histogram_totals = '';
	$histograms = array();
	$histograms_totals = array();
	$histogramlabels = array();
	$data = array ();
	$marks = array();
	//var_dump($emarkingstats);
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
		$marks [] = array (
				'series' => $stats->seriesname,
				'min' => $stats->minimum,
				'max' => $stats->maximum,
				'mean' => $stats->average,
				'firstQ' => $stats->percentile_25,
				'thirdQ' => $stats->percentile_75,
				'median' => $stats->percentile_50 
		);
	}
	return $marks;
}
function get_courses_marks($cmid, $emarkingid, $extracategory, $ids) {
	global $DB, $CFG;
	
	if (! $emarking = $DB->get_record ( 'emarking', array (
			'id' => $emarkingid 
	) )) {
		print_error ( 'Prueba inválida' );
	}
	if (! $cm = get_coursemodule_from_id ( 'emarking', $cmid )) {
		print_error ( 'Módulo inválido' );
	}
	
	
	// Validate course
	if (! $course = $DB->get_record ( 'course', array (
			'id' => $emarking->course 
	) )) {
		print_error ( 'Curso inválido' );
	}
	
	// Getting context
	$context = context_module::instance ( $cmid );
	$emarkingids = '' . $emarking->id;
	
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
	from {grade_items} as i
	inner join {emarking} as a on (i.itemtype = 'mod' AND i.itemmodule = 'emarking' and i.iteminstance in ( '.$ids.') AND i.iteminstance = a.id)
	inner join {course} as c on (i.courseid = c.id)
	inner join {course_categories} as cc on (c.category = cc.id)
	inner join {emarking_submission} as ss on (a.id = ss.emarking)
	where ss.grade is not null AND ss.status >= 20
	order by emarkingid asc, ss.grade asc) as G
	group by categoryid, emarkingid
	with rollup) as T";
	
	$emarkingstats = $DB->get_recordset_sql ( $sql );
	
	$data = array ();
	foreach ( $emarkingstats as $stats ) {
		if ($totalcategories == 1 && ! strncmp ( $stats->seriesname, 'SUBTOTAL', 8 )) {
			continue;
		}
		if ($totalemarkings == 1 && ! strncmp ( $stats->seriesname, 'TOTAL', 5 )) {
			continue;
		}
		
		$histogram_courses = '';
		$histogram_totals = '';
		$histograms = array ();
		$histograms_totals = array ();
		$histogramlabels = array ();
		
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
					$histograms [$i] = $histogramvalue;
				else
					$histograms [$i] .= $histogramvalue;
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
		$coursemarks [] = array (
				"cero" => $histograms [1],
				"uno" => $histograms [2],
				"dos" => $histograms [3],
				"tres" => $histograms [4],
				"cuatro" => $histograms [5],
				"cinco" => $histograms [6],
				"seis" => $histograms [7],
				"siete" => $histograms [8],
				"ocho" => $histograms [9],
				"nueve" => $histograms [10],
				"diez" => $histograms [11],
				"once" => $histograms [12]
		);
	}
	

	
	
	return $coursemarks;
}
function get_pass_ratio($cmid, $emarkingid, $extracategory, $ids) {
	global $DB, $CFG;
	if (! $emarking = $DB->get_record ( 'emarking', array (
			'id' => $emarkingid 
	) )) {
		print_error ( 'Prueba inválida' );
	}
	if (! $cm = get_coursemodule_from_id ( 'emarking', $cmid )) {
		print_error ( 'Módulo inválido' );
	}
	
	// Validate course
	if (! $course = $DB->get_record ( 'course', array (
			'id' => $emarking->course 
	) )) {
		print_error ( 'Curso inválido' );
	}
	// Getting context
	$context = context_module::instance ( $cmid );
	$emarkingids = '' . $emarking->id;
	
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
	from {grade_items} as i
	inner join {emarking} as a on (i.itemtype = 'mod' AND i.itemmodule = 'emarking' and i.iteminstance in ( '.$ids.') AND i.iteminstance = a.id)
	inner join {course} as c on (i.courseid = c.id)
	inner join {course_categories} as cc on (c.category = cc.id)
	inner join {emarking_submission} as ss on (a.id = ss.emarking)
	where ss.grade is not null AND ss.status >= 20
	order by emarkingid asc, ss.grade asc) as G
	group by categoryid, emarkingid
	with rollup) as T";
	
	$emarkingstats = $DB->get_recordset_sql ( $sql );
	
	$data = array ();
	foreach ( $emarkingstats as $stats ) {
		if ($totalcategories == 1 && ! strncmp ( $stats->seriesname, 'SUBTOTAL', 8 )) {
			continue;
		}
		if ($totalemarkings == 1 && ! strncmp ( $stats->seriesname, 'TOTAL', 5 )) {
			continue;
		}
		
		$histogram_courses = '';
		$histogram_totals = '';
		$histograms = array ();
		$histograms_totals = array ();
		$histogramlabels = array ();
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

		$pass_ratio [] = array (
				'seriesname' => $stats->seriesname . "(N=" . $stats->students . ")",
				'rank1' => $stats->rank_1,
				'rank2' => $stats->rank_2,
				'rank3' => $stats->rank_3
		);
		// "['$stats->seriesname (N=$stats->students)',$stats->rank_1,$stats->rank_2,$stats->rank_3],";
	}
	
	return $pass_ratio;
}
function get_efficiency($cmid, $emarkingid, $extracategory, $ids) {
	global $DB, $CFG;
	if (! $emarking = $DB->get_record ( 'emarking', array (
			'id' => $emarkingid 
	) )) {
		print_error ( 'Prueba inválida' );
	}
	if (! $cm = get_coursemodule_from_id ( 'emarking', $cmid )) {
		print_error ( 'Módulo inválido' );
	}
	
	// Validate course
	if (! $course = $DB->get_record ( 'course', array (
			'id' => $emarking->course 
	) )) {
		print_error ( 'Curso inválido' );
	}
	
	// Getting context
	$context = context_module::instance ( $cmid );
	
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
	// Gets the stats by criteria
	$sqlcriteria = '
				SELECT co.fullname,
				co.id AS courseid,
				e.id AS emarkingid,
				a.id AS criterionid,
				a.description,
				round(avg(b.score),1) AS avgscore,
				round(stddev(b.score),1) AS stdevscore,
				round(min(b.score),1) AS minscore,
				round(max(b.score),1) AS maxscore,
				round(avg(b.score)/t.maxscore,1) AS effectiveness,
				t.maxscore AS maxcriterionscore
	
				FROM {emarking_submission} AS s
				INNER JOIN {emarking} AS e ON (s.emarking IN ( '. $ids.' ) AND s.emarking=e.id)
				INNER JOIN {emarking_draft} AS dr ON (s.id=dr.submissionid AND dr.qualitycontrol=0 AND dr.status >= 20)
	            INNER JOIN {course_modules} AS cm ON e.id=cm.instance
				INNER JOIN {context} AS c ON cm.id=c.instanceid
				INNER JOIN {grading_areas} AS ga ON c.id=ga.contextid
				INNER JOIN {grading_definitions} AS gd ON ga.id=gd.areaid
				INNER JOIN {grading_instances} AS i ON (gd.id=i.definitionid)
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
	
	echo $sqlcriteria . "<hr>";
	$criteriastats = $DB->get_recordset_sql ( $sqlcriteria );
	
	$forcount = $DB->get_recordset_sql ( $sqlcriteria ); // run the sql again to get the count
	$count = iterator_count ( $forcount );
	
	$parallels_names_criteria = '';
	$effectivenessnum = 0;
	$effectivenesscriteria = array ();
	$effectivenesseffectiveness = array ();
	$lastdescription = random_string ();
	$lastcriteria = '';
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
			
			$effectivenesscriteria [$effectivenessnum] = $description;
			$lastdescription = $description;
		}
		$effectivenesseffectiveness [$effectivenessnum] = $stats->effectiveness;
		$effectivenessnum ++;
	}
	
	$effectiveness [0] = $effectivenesscriteria;
	$effectiveness [1] = $effectivenesseffectiveness;
	return $effectiveness;
}
function get_efficiency_criterion($cmid, $emarkingid, $extracategory) {
	$efficiency = get_efficiency ( $cmid, $emarkingid, $extracategory );
	$count = 0;
	$display = array ();
	$return = array ();
	foreach ( $efficiency [0] as $criterion ) {
		$nombre = "criterion" . $count;
		$display ["count"] = $count + 1;
		$display [$nombre] = $criterion;
		$count ++;
	}
	$return [0] = $display;
	return $return;
}

function get_efficiency_rate($cmid, $emarkingid, $extracategory) {
	$efficiency = get_efficiency ( $cmid, $emarkingid, $extracategory );
	$count = 0;
	$display = array ();
	$return = array ();
	$divisor=count($efficiency[0]);
	$divisible=count($efficiency[1]);
	$parallels = $divisible/$divisor;
	
	$cont=0;
	$arr = array();
	$j=0;
	for($i=1;$i<=count($efficiency[1]);$i++) {
		$index=$j;
		$arr["rate".$index] = $efficiency[1][$i-1];
		$j++;
		if($i % $divisor == 0) {
			$return[] = $arr;
			$arr = array();
			$j=0;
		}
		
	}
	
	return $return;
}
function get_question_advance($cmid, $emarkingid) {
	global $DB;
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
			$cmid 
	) );
	$totalsubmissions = get_totalsubmissions( $cmid, $emarkingid );
	$datatablecriteria = "['Criterio', 'Corregido', 'Por recorregir', 'Por corregir'],";
	$i = 0;
	foreach ( $markingstatspercriterion as $statpercriterion ) {
		
		$description[0] ['description' . $i] = trim ( preg_replace ( '/\s\s+/', ' ', $statpercriterion->description));
		$responded[0] ['responded'.$i] = round ( ($statpercriterion->comments - $statpercriterion->regrades) * 100 / $totalsubmissions, 2 );
		$regrading[0] ['regrading'.$i] = round ( $statpercriterion->regrades * 100 / $totalsubmissions, 2 );
		$grading [0]['grading'.$i] = round ( ($statpercriterion->submissions - $statpercriterion->comments) * 100 / $totalsubmissions, 2 );
		$i++;
	}
	$criteriaadvance [0] = $description;
	$criteriaadvance [1] = $responded;
	$criteriaadvance [2] = $regrading;
	$criteriaadvance [3] = $grading;
	return $criteriaadvance;
}
function get_advance_description($cmid, $emarkingid){
	$description = get_question_advance( $cmid, $emarkingid);
	$count = 0;
	$display = array ();
	$return = array ();
	foreach ( $description [0] as $desc ) {
		$nombre = "description" . $count;
		$contador=0;
		foreach($desc as $d){
			$display ["count"] = $contador + 1;
			$interior = "description".$contador;
			$display[$interior]=$d;

			$contador ++;
		}
		$count ++;
	}
	$return [0] = $display;
	return $return;
}
function get_advance_responded($cmid, $emarkingid){
	$responded = get_question_advance( $cmid, $emarkingid);
	$count = 0;
	$display = array ();
	$return = array ();
	foreach ( $responded [1] as $desc ) {
		$nombre = "responded" . $count;
		$contador=0;
		foreach($desc as $d){
			$interior = "responded".$contador;
			$display[$interior]=$d;
			$display ["count"] = $contador + 1;
				
			$contador ++;
		}
		$count ++;
	}
	$return [0] = $display;
	return $return;
}

function get_advance_regrading($cmid, $emarkingid){
	$regrading = get_question_advance( $cmid, $emarkingid);
	$count = 0;
	$display = array ();
	$return = array ();
	foreach ( $regrading [2] as $desc ) {
		$nombre = "regrading" . $count;
		$contador=0;
		foreach($desc as $d){
			$interior = "regrading".$contador;
			$display[$interior]=$d;	
			$display ["count"] = $contador + 1;
			
			$contador ++;
		}
		$count ++;
	}
	$return [0] = $display;
	return $return;
}

function get_advance_grading($cmid, $emarkingid){
	$grading = get_question_advance( $cmid, $emarkingid);
	$count = 0;
	$display = array ();
	$return = array ();
	foreach ( $grading [3] as $desc ) {
		$nombre = "grading" . $count;
		$contador=0;
		foreach($desc as $d){
			$interior = "grading".$contador;
			$display[$interior]=$d;
			$display ["count"] = $contador + 1;
			$contador ++;
		}
		$count ++;
	}
	$return [0] = $display;
	return $return;
}
function get_marker_advance($cmid, $emarkingid){
	global $DB;
	$markingstatspermarker = $DB->get_recordset_sql("
		SELECT
		a.id,
		a.description,
		T.*
		FROM {course_modules} AS c
		INNER JOIN {context} AS mc ON (c.id = :cmid AND c.id = mc.instanceid)
		INNER JOIN {grading_areas} AS ar ON (mc.id = ar.contextid)
		INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
		INNER JOIN {gradingform_rubric_criteria} AS a ON (d.id = a.definitionid)
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
		ON (a.id = T.criterionid )
		INNER JOIN {emarking_marker_criterion} AS emc ON (emc.emarking = c.instance AND emc.marker = T.markerid)
		GROUP BY T.markerid, a.id
		",
			array('cmid'=>$cmid, 'emarkingid'=>$emarkingid));
	$datamarkersavailable = false;
	$datatablemarkers = "";
	
	$datatablecontribution = "['Corrector', 'Corregido', 'Por recorregir', 'Por corregir'],";

	$totalsubmissions=get_totalsubmissions($cmid, $emarkingid);
	$count =0;
	foreach($markingstatspermarker as $permarker) {
		$description = trim(preg_replace('/\s\s+/', ' ', $permarker->description));
		
		$correctorcriterio["corrector".$count]=$permarker->markername.$description;
		$corregido["corregido".$count]=$permarker->comments - $permarker->regrades;
		$porcorregir["porcorregir".$count]=$permarker->regrades;
		$porrecorregir["porrecorregir".$count]=$totalsubmissions - $permarker->comments;
		
		$count++;
	}
	$markeradvance [0]= $correctorcriterio;
	$markeradvance [1]= $corregido;
	$markeradvance [2]= $porcorregir;
	$markeradvance [3]= $porrecorregir;
	return $markeradvance;
}
function get_markeradvance_marker($cmid, $emarkingid){
	$markeradvance=get_marker_advance($cmid, $emarkingid);
	$count = 0;
	$display = array ();
	$return = array ();
	foreach ( $markeradvance [0] as $advance ) {
		$nombre = "corrector" . $count;
		$display ["count"] = $count + 1;
		$display[$nombre] = $advance;
		$count ++;
	}
	$return [0] = $display;
	return $return;
	
}
function get_markeradvance_corregido($cmid, $emarkingid){
	$markeradvance=get_marker_advance($cmid, $emarkingid);
	$count = 0;
	$display = array ();
	$return = array ();
	foreach ( $markeradvance [1] as $advance ) {
		$nombre = "corregido" . $count;
		$display ["count"] = $count + 1;
		$display[$nombre] = $advance;
		$count ++;
	}
	$return [0] = $display;
	return $return;

}function get_markeradvance_porcorregir($cmid, $emarkingid){
	$markeradvance=get_marker_advance($cmid, $emarkingid);
	$count = 0;
	$display = array ();
	$return = array ();
	foreach ( $markeradvance [2] as $advance ) {
		$nombre = "porcorregir" . $count;
		$display ["count"] = $count + 1;
		$display[$nombre] = $advance;
		$count ++;
	}
	$return [0] = $display;
	return $return;

}function get_markeradvance_porrecorregir($cmid, $emarkingid){
	$markeradvance=get_marker_advance($cmid, $emarkingid);
	$count = 0;
	$display = array ();
	$return = array ();
	foreach ( $markeradvance [3] as $advance ) {
		$nombre = "porrecorregir" . $count;
		$display ["count"] = $count + 1;
		$display[$nombre] = $advance;
		$count ++;
	}
	$return [0] = $display;
	return $return;

}


