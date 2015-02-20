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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   mod
 * @subpackage emarking
 * @copyright 2013 Jorge Villalón <villalon@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$sqlcomments = "SELECT 
		aec.id, 
		aec.posx, 
		aec.posy, 
		aec.rawtext,
		aec.textformat AS format,
		aec.width,
		aec.height,
		aec.colour,
		ep.page AS pageno, 
		IFNULL(aec.bonus,0) AS bonus,
		grm.maxscore, 
		aec.levelid,
		grl.score AS score, 
		grl.definition AS leveldesc, 
		grc.id AS criterionid, 
		grc.description AS criteriondesc,
		u.id AS markerid, 
		CONCAT(u.firstname,' ',u.lastname) AS markername,
		IFNULL(er.id, 0) AS regradeid,
		IFNULL(er.comment, '') AS regradecomment,
		IFNULL(er.motive,0) AS motive,
		IFNULL(er.accepted,0) AS regradeaccepted,
		IFNULL(er.markercomment, '') AS regrademarkercomment,
		IFNULL(er.levelid, 0) AS regradelevelid,
		IFNULL(er.markerid, 0) AS regrademarkerid,
		IFNULL(er.bonus, '') AS regradebonus,
		aec.timecreated
		FROM {emarking_comment} AS aec
		INNER JOIN {emarking_page} AS ep ON (aec.page = ep.id AND ep.page = :pageno AND aec.draft = :draft)
		INNER JOIN {emarking_draft} AS es ON (aec.draft = es.id)
		INNER JOIN {user} AS u ON (aec.markerid = u.id)
		LEFT JOIN {gradingform_rubric_levels} AS grl ON (aec.levelid = grl.id)
		LEFT JOIN {gradingform_rubric_criteria} AS grc ON (grl.criterionid = grc.id)
		LEFT JOIN (
			SELECT grl.criterionid, 
			MAX(score) AS maxscore
			FROM {gradingform_rubric_levels} AS grl
			GROUP BY grl.criterionid
		) AS grm ON (grc.id = grm.criterionid)
		LEFT JOIN {emarking_regrade} AS er ON (er.criterion = grc.id AND er.draft = es.id)
		ORDER BY aec.levelid DESC";
$params = array('pageno'=>$pageno, 'draft'=>$draft->id, 'draft'=>$draft->id);
/**  Measures the correction window **/
$winwidth = required_param('windowswidth', PARAM_NUMBER);
$winheight =required_param('windowsheight', PARAM_NUMBER);

$results = $DB->get_records_sql($sqlcomments, $params);

if(!$results) {
	$results = array();
}else{
	// transformar porcentajes a pixeles
	foreach($results as $result){
		$result->posx = (String)((int)($result->posx * $winwidth));
		$result->posy = (String)((int)($result->posy * $winheight));
	}
	
}


