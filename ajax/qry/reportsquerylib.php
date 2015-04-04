<?php
global $CFG;
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ($CFG->dirroot . '/mod/emarking/reports/forms/gradereport_form.php');

/**
 * This function gets all stadistical data from corrections on one instrument of evaluation.
 * @param unknown $numcriteria
 * @param unknown $emarkingid
 * @return multitype:multitype:number
 */
function get_status($numcriteria, $emarkingid) {
	global $DB;
	
	$markingstats = $DB->get_record_sql ( '
			SELECT	COUNT(distinct id) AS activities,
			COUNT(DISTINCT student) AS students,
			MAX(pages) AS maxpages,
			MIN(pages) AS minpages,
			ROUND(AVG(comments), 2) AS pctmarked,
			SUM(missing) AS missing,
			SUM(submitted) AS submitted,
			SUM(grading) AS grading,
			SUM(graded) AS graded,
			SUM(regrading) AS regrading
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
			COUNT(distinct p.id) AS pages,
			CASE WHEN 0 = :numcriteria THEN 0 ELSE COUNT(distinct c.id) / :numcriteria2 END AS comments,
			COUNT(distinct r.id) AS regrades,
			nm.course,
			nm.id,
			ROUND(SUM(l.score),2) AS score,
			ROUND(SUM(c.bonus),2) AS bonus,
			dr.sort
			FROM {emarking} AS nm
			INNER JOIN {emarking_submission} AS s ON (nm.id = :emarkingid AND s.emarking = nm.id)
			INNER JOIN {emarking_draft} AS dr ON (dr.submissionid = s.id AND dr.qualitycontrol = 0)
	        INNER JOIN {emarking_page} AS p ON (p.submission = s.id)
			LEFT JOIN {emarking_comment} AS c on (c.page = p.id AND c.levelid > 0 AND c.draft = dr.id)
			LEFT JOIN {gradingform_rubric_levels} AS l ON (c.levelid = l.id)
			LEFT JOIN {emarking_regrade} AS r ON (r.draft = dr.id AND r.criterion = l.criterionid AND r.accepted = 0)
			GROUP BY nm.id, s.student
	) AS T
			GROUP by id', array (
			'numcriteria' => $numcriteria,
			'numcriteria2' => $numcriteria,
			'emarkingid' => $emarkingid 
	) );
	// Filling in array with status elements
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
function get_totalsubmissions($grading) {
	$totalsubmissions = $grading [0] ['missing'] + $grading [0] ['submitted'] + $grading [0] ['grading'] + $grading [0] ['graded'] + $grading [0] ['regrading'];
	return $totalsubmissions;
}

/**
 * Gets the contribution, on the evaluation instrument, from each of the correctors.
 * @param unknown $cmid
 * @param unknown $emarkingid
 * @return multitype:multitype:multitype:number
 */
function get_markers_contribution($grading, $emarkingid) {
	global $DB;
	// Get total submission
	$totalsubmissions = get_totalsubmissions ( $grading );
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
        INNER JOIN {emarking_comment} AS ec ON (b.id = ec.levelid AND ec.draft = dr.id)
        INNER JOIN {user} AS u ON (ec.markerid = u.id)
		GROUP BY ec.markerid';
	$markingstatstotalcontribution = $DB->get_records_sql ( $sqlcontributorstats, array (
			$emarkingid 
	) );
	$contributioners = array ();
	$contributions = array ();
	
	// Filling in array with elements of user names and contribution per marker
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
 * @return multitype:multitype:number
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
		// Filling in array with marks statistical elements
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
 * @return multitype:string number
 */
function get_emarking_stats($ids) {
    global $DB;
    
    $sql = "SELECT  *,
    CASE
    WHEN categoryid IS NULL THEN 'TOTAL'
    WHEN emarkingid IS NULL THEN CONCAT('SUBTOTAL ', categoryname)
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
    ROUND((SUM(pass) / COUNT(*)) * 100,2) AS pass_ratio,
    SUBSTRING_INDEX(
    SUBSTRING_INDEX(
    GROUP_CONCAT(grade order by grade separator ',')
    , ','
    , 25/100 * COUNT(*) + 1)
    , ','
    , -1
    ) AS percentile_25,
    SUBSTRING_INDEX(
    SUBSTRING_INDEX(
    GROUP_CONCAT(grade order by grade separator ',')
    , ','
    , 50/100 * COUNT(*) + 1)
    , ','
    , -1
    ) AS percentile_50,
    SUBSTRING_INDEX(
    SUBSTRING_INDEX(
    GROUP_CONCAT(grade order by grade separator ',')
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
    ROUND(SUM(rank_1)/COUNT(*),3) AS rank_1,
    ROUND(SUM(rank_2)/COUNT(*),3) AS rank_2,
    ROUND(SUM(rank_3)/COUNT(*),3) AS rank_3,
    MIN(mingrade) AS mingradeemarking,
    MIN(maxgrade) AS maxgradeemarking
    FROM (
    SELECT
    ROUND(dr.grade,2) AS grade, -- FINAL GRADE (CALCULATED OR VIA MANUAL CLASIFICATOR)
    a.grade AS maxgrade, -- MAX GRADE OF EMARKING
    a.grademin AS mingrade, -- MIN GRADE OF EMARKING
    CASE WHEN dr.grade IS NULL THEN 0 -- NULL GRADE INDICATOR
    ELSE 1
    END AS attended,
    CASE WHEN dr.grade >= i.gradepass THEN 1
    ELSE 0
    END AS pass,
    CASE WHEN dr.grade >= 0 AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 1 THEN 1 ELSE 0 END AS histogram_01,
    CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 1  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 2 then 1 else 0 END AS histogram_02,
    CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 2  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 3 then 1 else 0 END AS histogram_03,
    CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 3  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 4 then 1 else 0 END AS histogram_04,
    CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 4  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 5 then 1 else 0 END AS histogram_05,
    CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 5  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 6 then 1 else 0 END AS histogram_06,
    CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 6  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 7 then 1 else 0 END AS histogram_07,
    CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 7  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 8 then 1 else 0 END AS histogram_08,
    CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 8  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 9 then 1 else 0 END AS histogram_09,
    CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 9  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 10 then 1 else 0 END AS histogram_10,
    CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 10  AND dr.grade < a.grademin + (a.grade - a.grademin) / 12 * 11 then 1 else 0 END AS histogram_11,
    CASE WHEN dr.grade >= a.grademin + (a.grade - a.grademin) / 12 * 11 THEN 1 ELSE 0 END AS histogram_12,
    CASE WHEN dr.grade - a.grademin < (a.grade - a.grademin) / 3 THEN 1 ELSE 0 END AS rank_1,
    CASE WHEN dr.grade - a.grademin >= (a.grade - a.grademin) / 3 AND dr.grade - a.grademin  < (a.grade - a.grademin) / 2 then 1 ELSE 0 END AS rank_2,
    CASE WHEN dr.grade - a.grademin >= (a.grade - a.grademin) / 2  THEN 1 ELSE 0 END AS rank_3,
    c.category as categoryid,
    cc.name AS categoryname,
    a.id AS emarkingid,
    a.name AS modulename,
    c.fullname AS coursename
    FROM {emarking} AS a
    INNER JOIN {grade_items} AS i on (i.itemtype = 'mod' AND i.itemmodule = 'emarking' and i.iteminstance in ($ids) AND i.iteminstance = a.id)
    INNER JOIN {emarking_submission} AS ss on (a.id = ss.emarking)
    INNER JOIN {emarking_draft} AS dr ON (dr.submissionid = ss.id AND dr.qualitycontrol=0)
    INNER JOIN {course} AS c on (i.courseid = c.id)
    INNER JOIN {course_categories} AS cc on (c.category = cc.id)
    WHERE dr.grade IS NOT NULL AND dr.status >= 20
    ORDER BY emarkingid asc, dr.grade asc) AS G
    GROUP BY categoryid, emarkingid
    WITH ROLLUP) AS T";
    
    $emarkingstats = $DB->get_recordset_sql ( $sql );
    
    return $emarkingstats;
}
/**
 * Gets the amount of students who has achieved a mark thats fits in one of the 12 categories. 
 * @param unknown $emarkingstats
 * @param unknown $totalcategories
 * @param unknown $totalemarkings
 * @return multitype:multitype:multitype:number
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
		// Filling in array with elements for the histogram of course marks
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
 * @return multitype:multitype:string number
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
		// Filling in array with elements of ranking for pass ratio
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
 * @return multitype:multitype:multitype:string number
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
		// FIXME  fix when the name of two descriptions are the same
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
 * @return multitype:multitype:multitype:string number
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
					        INNER JOIN {emarking_comment} AS ec ON (b.id = ec.levelid AND ec.draft = dr.id)
					        LEFT JOIN {emarking_regrade} AS r ON (r.draft = dr.id AND r.criterion = a.id)
							GROUP BY a.id
							ORDER BY a.sortorder";
	$markingstatspercriterion = $DB->get_records_sql ( $sqlstatscriterion, array (
			$cmid 
	) );
	$totalsubmissions = get_totalsubmissions( $cmid, $emarkingid );
	$i = 0;
	foreach ( $markingstatspercriterion as $statpercriterion ) {
		
		$description[0] ['description' . $i] = trim ( preg_replace ( '/\s\s+/', ' ', $statpercriterion->description));
		// condition of division by 0.
		if ($totalsubmissions > 0) {
			
			$responded [0] ['responded' . $i] = round ( ($statpercriterion->comments - $statpercriterion->regrades) * 100 / $totalsubmissions, 2 );
			$regrading [0] ['regrading' . $i] = round ( $statpercriterion->regrades * 100 / $totalsubmissions, 2 );
			$grading [0] ['grading' . $i] = round ( ($statpercriterion->submissions - $statpercriterion->comments) * 100 / $totalsubmissions, 2 );
		} else if ($totalsubmissions <= 0) {
			$responded [0] ['responded' . $i] = 0;
			$regrading [0] ['regrading' . $i] = 0;
			$grading [0] ['grading' . $i] = 0;
		}
		$i ++;
	}
	
	return array($description, $responded, $regrading, $grading);
}
/**
 * Gets the correctors progress on the correction of the instrument per status.
 * @param unknown $cmid
 * @param unknown $emarkingid
 * @return multitype:multitype:multitype:string number
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
		ROUND(AVG(bb.score),2) AS avgscore,
		ROUND(STDDEV(bb.score),2) AS stdevscore,
		ROUND(MIN(bb.score),2) AS minscore,
		ROUND(MAX(bb.score),2) AS maxscore,
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
		LEFT JOIN {emarking_comment} AS ec on (ec.page = p.id AND ec.draft = dr.id)
		LEFT JOIN {gradingform_rubric_levels} AS bb ON (ec.levelid = bb.id)
		LEFT JOIN {emarking_regrade} AS r ON (r.draft = dr.id AND r.criterion = bb.criterionid)
		LEFT JOIN {user} AS u ON (ec.markerid = u.id)
		WHERE dr.status >= 10
		GROUP BY ec.markerid, bb.criterionid) AS T
		ON (a.id = T.criterionid )
		INNER JOIN {emarking_marker_criterion} AS emc ON (emc.emarking = c.instance AND emc.marker = T.markerid)
		GROUP BY T.markerid, a.id
		",
			array('cmid'=>$cmid, 'emarkingid'=>$emarkingid));
	$datamarkersavailable = false;
	$datatablemarkers = "";
	
	$totalsubmissions=get_totalsubmissions($cmid, $emarkingid);
	$count =0;
	foreach($markingstatspermarker as $permarker) {
		$description = trim(preg_replace('/\s\s+/', ' ', $permarker->description));
		$markerdescription[0]["corrector".$count]=$permarker->markername.$description;
		$responded[0]["corregido".$count]=$permarker->comments - $permarker->regrades;
		$grading[0]["porcorregir".$count]=$permarker->regrades;
		$regrading[0]["porrecorregir".$count]=$totalsubmissions - $permarker->comments;
		
		$count++;
	}

	return array($markerdescription, $responded, $grading, $regrading);
}