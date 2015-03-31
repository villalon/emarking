<?php
global $CFG;
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ($CFG->dirroot . '/mod/emarking/reports/forms/gradereport_form.php');

/**
 * This function gets all stadistical data from corrections on one instrument of evaluation.
 * @param unknown $numcriteria
 * @param unknown $emarkingid
 * @return multitype:NULL
 */
function get_status($numcriteria, $emarkingid) {
	global $DB;
	
	$markingstats = $DB->get_record_sql ( '
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
			CASE WHEN dr.status < 10 THEN 1 ELSE 0 END AS missing,
			CASE WHEN dr.status = 10 THEN 1 ELSE 0 END AS submitted,
			CASE WHEN dr.status > 10 AND dr.status < 20 THEN 1 ELSE 0 END AS grading,
			CASE WHEN dr.status = 20 THEN 1 ELSE 0 END AS graded,
			CASE WHEN dr.status > 20 THEN 1 ELSE 0 END AS regrading,
			dr.timemodified,
			dr.grade,
			dr.generalfeedback,
			count(distinct p.id) as pages,
			CASE WHEN 0 = :numcriteria THEN 0 ELSE count(distinct c.id) / :numcriteria2 END as comments,
			count(distinct r.id) as regrades,
			nm.course,
			nm.id,
			round(sum(l.score),2) as score,
			round(sum(c.bonus),2) as bonus,
			dr.sort
			FROM {emarking} AS nm
			INNER JOIN {emarking_submission} AS s ON (nm.id = :emarkingid AND s.emarking = nm.id)
			INNER JOIN {emarking_draft} AS dr ON (dr.submissionid = s.id AND dr.qualitycontrol = 0)
	        INNER JOIN {emarking_page} AS p ON (p.submission = s.id)
			LEFT JOIN {emarking_comment} as c on (c.page = p.id AND c.levelid > 0 AND c.draft = dr.id)
			LEFT JOIN {gradingform_rubric_levels} as l ON (c.levelid = l.id)
			LEFT JOIN {emarking_regrade} as r ON (r.draft = dr.id AND r.criterion = l.criterionid AND r.accepted = 0)
			GROUP BY nm.id, s.student
	) as T
			GROUP by id', array (
			'numcriteria' => $numcriteria,
			'numcriteria2' => $numcriteria,
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

/**
 * Gets the total submissions of an instrument of evaluation and returns the number.
 * @param unknown $cmid
 * @param unknown $emarkingid
 * @return number
 */
function get_totalsubmissions($cmid, $emarkingid) {
	$grading = get_status ( $cmid, $emarkingid );
	$totalsubmissions = $grading [0] ['missing'] + $grading [0] ['submitted'] + $grading [0] ['grading'] + $grading [0] ['graded'] + $grading [0] ['regrading'];
	return $totalsubmissions;
}

/**
 * Gets the contribution, on the evaluation instrument, from each of the correctors.
 * @param unknown $cmid
 * @param unknown $emarkingid
 * @return multitype:multitype:multitype:NULL
 */
function get_markers_contribution($cmid, $emarkingid) {
	global $DB;
	$totalsubmissions = get_totalsubmissions ( $cmid, $emarkingid );
	$sqlcontributorstats = 'SELECT
		ec.markerid,
		CONCAT(u.firstname , " ", u.lastname) AS markername,
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
		GROUP BY ec.markerid';
	$markingstatstotalcontribution = $DB->get_records_sql ( $sqlcontributorstats, array (
			$emarkingid 
	) );
	$contributioners = array ();
	$contributions = array ();
	
	foreach ( $markingstatstotalcontribution as $contributioner ) {
		$contributioners [0] = array (
				"user" => $contributioner->markername 
		);
		$contributions [0] = array (
				"contrib" => round ( ($contributioner->comments) * 100 / ($totalsubmissions * $numcriteria), 2 )
		);
	}
	return array($contributioners, $contributions);
}
/**
 * Gets stadistical information from the correction.
 * @param unknown $emarkingstats
 * @param unknown $totalcategories
 * @param unknown $totalemarkings
 * @return multitype:multitype:NULL
 */
function get_marks($emarkingstats, $totalcategories, $totalemarkings) {
	global $DB, $CFG;
	
	$emarkingstats->rewind();
	
	// Search for stats regardig the exames (eg: max, min, number of students,etc)
	$marks = array();

	foreach ( $emarkingstats as $stats ) {
		if ($totalcategories == 1 && ! strncmp ( $stats->seriesname, 'SUBTOTAL', 8 )) {
			continue;
		}
		if ($totalemarkings == 1 && ! strncmp ( $stats->seriesname, 'TOTAL', 5 )) {
			continue;
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
/**
 * Runs a query used in the other functions to get information.
 * @param unknown $ids
 * @return unknown
 */
function get_emarking_stats($ids) {
    global $DB;
    
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
    round(dr.grade,2) as grade, -- Nota final (calculada o manual via calificador)
    a.grade as maxgrade, -- Nota máxima del emarking
    a.grademin as mingrade, -- Nota mínima del emarking
    case when dr.grade is null then 0 -- Indicador de si la nota es null
    else 1
    end as attended,
    case when dr.grade >= i.gradepass then 1
    else 0
    end as pass,
    case when dr.grade >= 0 AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 1 then 1 else 0 end as histogram_01,
    case when dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 1  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 2 then 1 else 0 end as histogram_02,
    case when dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 2  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 3 then 1 else 0 end as histogram_03,
    case when dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 3  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 4 then 1 else 0 end as histogram_04,
    case when dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 4  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 5 then 1 else 0 end as histogram_05,
    case when dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 5  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 6 then 1 else 0 end as histogram_06,
    case when dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 6  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 7 then 1 else 0 end as histogram_07,
    case when dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 7  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 8 then 1 else 0 end as histogram_08,
    case when dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 8  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 9 then 1 else 0 end as histogram_09,
    case when dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 9  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 10 then 1 else 0 end as histogram_10,
    case when dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 10  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 11 then 1 else 0 end as histogram_11,
    case when dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 11 then 1 else 0 end as histogram_12,
    case when dr.grade - a.grademin < (a.grade - a.grademin) / 3 then 1 else 0 end as rank_1,
    case when dr.grade - a.grademin >= (a.grade - a.grademin) / 3 AND dr.grade - a.grademin  < (a.grade - a.grademin) / 2 then 1 else 0 end as rank_2,
    case when dr.grade - a.grademin >= (a.grade - a.grademin) / 2  then 1 else 0 end as rank_3,
    c.category as categoryid,
    cc.name as categoryname,
    a.id as emarkingid,
    a.name as modulename,
    c.fullname as coursename
    from {emarking} AS a
    INNER JOIN {grade_items} AS i on (i.itemtype = 'mod' AND i.itemmodule = 'emarking' and i.iteminstance in ($ids) AND i.iteminstance = a.id)
    INNER JOIN {emarking_submission} AS ss on (a.id = ss.emarking)
    INNER JOIN {emarking_draft} AS dr ON (dr.submissionid = ss.id AND dr.qualitycontrol=0)
    INNER JOIN {course} AS c on (i.courseid = c.id)
    INNER JOIN {course_categories} AS cc on (c.category = cc.id)
    WHERE dr.grade is not null AND dr.status >= 20
    ORDER BY emarkingid asc, dr.grade asc) as G
    GROUP BY categoryid, emarkingid
    with rollup) as T";
    
    $emarkingstats = $DB->get_recordset_sql ( $sql );
    
    return $emarkingstats;
}
/**
 * Gets the amount of students who has achieved a mark thats fits in one of the 12 categories. 
 * @param unknown $emarkingstats
 * @param unknown $totalcategories
 * @param unknown $totalemarkings
 * @return multitype:multitype:Ambigous <> Ambigous <unknown, string>
 */
function get_courses_marks($emarkingstats, $totalcategories, $totalemarkings) {
	global $DB, $CFG;
	
	$emarkingstats->rewind();
	
	$coursemarks = array ();
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
		
		$coursemarks[] = array (
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
/**
 * Gets the ratio of students that fits into 3 categories, of course aproval.
 * @param unknown $emarkingstats
 * @param unknown $totalcategories
 * @param unknown $totalemarkings
 * @return multitype:multitype:string NULL
 */
function get_pass_ratio($emarkingstats, $totalcategories, $totalemarkings) {
	global $DB, $CFG;
	
	$emarkingstats->rewind();
	
	$pass_ratio = array ();
	$data = array ();
	foreach ( $emarkingstats as $stats ) {

	    if ($totalcategories == 1 && ! strncmp ( $stats->seriesname, 'SUBTOTAL', 8 )) {
			continue;
		}
		
		if ($totalemarkings == 1 && ! strncmp ( $stats->seriesname, 'TOTAL', 5 )) {
			continue;
		}

		$pass_ratio [] = array (
				'seriesname' => $stats->seriesname . "(N=" . $stats->students . ")",
				'rank1' => $stats->rank_1,
				'rank2' => $stats->rank_2,
				'rank3' => $stats->rank_3
		);
	}
	
	return $pass_ratio;
}
/**
 * Gets the student efficiency of point achievment in every criteria of the rubric.
 * @param unknown $ids
 * @return multitype:Ambigous <multitype:, unknown> multitype:
 */
function get_efficiency($ids) {
	global $DB, $CFG;

	// Gets the stats by criteria
	$sqlcriteria = "
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
				INNER JOIN {emarking} AS e ON (s.emarking IN ($ids) AND s.emarking=e.id)
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
							GROUP BY s.id, l.criterionid) AS t ON (s.emarking=t.emarkingid AND a.id = t.criterionid)
				INNER JOIN {course} AS co ON e.course=co.id
				GROUP BY s.emarking,a.id
				ORDER BY a.description,emarkingid";
	
	$criteriastats = $DB->get_recordset_sql ( $sqlcriteria );
	$count = count($criteriastats);
	
	$parallels_names_criteria = '';
	$effectiveness = array();
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
			
			$effectivenesscriteria[0]["criterion".$effectivenessnum] = $description;
			$lastdescription = $description;
		}
		$effectivenesseffectiveness[0]["rate".$effectivenessnum] = $stats->effectiveness;
		$effectivenessnum ++;
	}
	
	return array($effectivenesscriteria,$effectivenesseffectiveness);
}
/**
 * Gets the criteria progress on the correction of the instrument per status.
 * @param unknown $cmid
 * @param unknown $emarkingid
 * @return multitype:unknown
 */
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
					        INNER JOIN {emarking_draft} AS dr ON (s.id=dr.submissionid)
	                        INNER JOIN {course_modules} AS cm ON (e.id=cm.instance)
							INNER JOIN {course} AS co ON (cm.course=co.id)
					        INNER JOIN {context} AS c ON (dr.status>=10 AND cm.id = c.instanceid AND cm.id = ? )
					        INNER JOIN {grading_areas} AS ar ON (c.id = ar.contextid)
					        INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
					        INNER JOIN {grading_instances} AS i ON (d.id=i.definitionid)
					        INNER JOIN {gradingform_rubric_fillings} AS f ON (i.id=f.instanceid)
					        INNER JOIN {gradingform_rubric_levels} AS b ON (b.id = f.levelid)
					        INNER JOIN {gradingform_rubric_criteria} AS a ON (a.id = f.criterionid)
					        INNER JOIN {emarking_comment} as ec ON (b.id = ec.levelid AND ec.draft = dr.id)
					        LEFT JOIN {emarking_regrade} as r ON (r.draft = dr.id AND r.criterion = a.id)
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
	
	return array($description, $responded, $regrading, $grading);
}
/**
 * Gets the correctors progress on the correction of the instrument per status.
 * @param unknown $cmid
 * @param unknown $emarkingid
 * @return multitype:string number unknown
 */
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
		INNER JOIN {emarking_draft} AS dr ON (s.id = dr.submissionid)
	    INNER JOIN {emarking_page} AS p ON (p.submission = s.id)
		LEFT JOIN {emarking_comment} as ec on (ec.page = p.id AND ec.draft = dr.id)
		LEFT JOIN {gradingform_rubric_levels} AS bb ON (ec.levelid = bb.id)
		LEFT JOIN {emarking_regrade} as r ON (r.draft = dr.id AND r.criterion = bb.criterionid)
		LEFT JOIN {user} as u ON (ec.markerid = u.id)
		WHERE dr.status >= 10
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
		
		$correctorcriterio[0]["corrector".$count]=$permarker->markername.$description;
		$corregido[0]["corregido".$count]=$permarker->comments - $permarker->regrades;
		$porcorregir[0]["porcorregir".$count]=$permarker->regrades;
		$porrecorregir[0]["porrecorregir".$count]=$totalsubmissions - $permarker->comments;
		
		$count++;
	}

	return array($correctorcriterio,$corregido, $porcorregir, $porrecorregir);
}