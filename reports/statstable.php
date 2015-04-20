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
 * @copyright 2015 Xiu-Fong Lin (xlin@alumnos.uai.cl)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *         
 */


/**
 *
 * This functions creates the stats table for gradereport.php y markingreport.php
 *
 * @param array $emarkingid        	
 * @param int $totalemarkings        	
 * @return table
 */
function get_stats_table($emarkingid, $totalemarkings) {
	global $DB;
	// Set the correct syntaxis for the query in $sqlcats
	$emarkingids = '';
	foreach ( $emarkingid as $id ) {
		$emarkingids = $id . ',';
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
	// This generates a link with the ids and generates a IN sql, so the sql stays secure.
	list ( $emarking_ids, $param ) = $DB->get_in_or_equal ( $emarkingid, SQL_PARAMS_NAMED );
	
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
	INNER JOIN {emarking_submission} AS ss ON (a.id = ss.emarking AND a.id {$emarking_ids})
	INNER JOIN {emarking_draft} AS dr ON (dr.submissionid = ss.id AND dr.qualitycontrol=0)
	INNER JOIN {course} AS c ON (a.course = c.id)
	INNER JOIN {course_categories} AS cc ON (c.category = cc.id)
	WHERE dr.grade is not null AND dr.status >= 20
	ORDER BY emarkingid asc, dr.grade asc) AS G
	GROUP BY categoryid, emarkingid
	WITH ROLLUP) AS T";
	
	$emarkingstats = $DB->get_recordset_sql ( $sql, $param );
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
		// Format the ranks by percentages.
		$rank_1 = number_format ( $stats->rank_1 * 100, 1 ) . '%';
		$rank_2 = number_format ( $stats->rank_2 * 100, 1 ) . '%';
		$rank_3 = number_format ( $stats->rank_3 * 100, 1 ) . '%';
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
				$rank_1,
				$rank_2,
				$rank_3 
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
	$statstable = html_writer::table ( $table );
	
	return $statstable;
}