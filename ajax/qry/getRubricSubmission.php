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
 * @copyright 2013-2015 Jorge Villalón <villalon@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$markerscriteria = $DB->get_records('emarking_marker_criterion', array('emarking'=>$submission->emarking));
$markersassigned = count($markerscriteria) > 0 && !$readonly && !$issupervisor;

$rubricdesc = $DB->get_recordset_sql(
        "SELECT
		d.name AS rubricname,
		a.id AS criterionid,
		a.description ,
		b.definition,
		b.id AS levelid,
		b.score,
		IFNULL(E.id,0) AS commentid,
		IFNULL(E.pageno,0) AS commentpage,
		E.rawtext AS commenttext,
		E.markerid AS markerid,
		IFNULL(E.textformat,2) AS commentformat,
		IFNULL(E.bonus,0) AS bonus,
		IFNULL(er.id,0) AS regradeid,
		IFNULL(er.motive,0) AS motive,
		er.comment AS regradecomment,
		IFNULL(er.markercomment, '') AS regrademarkercomment,
		IFNULL(er.accepted,0) AS regradeaccepted
		FROM {course_modules} AS c
		INNER JOIN {context} AS mc ON (c.id = :coursemodule AND c.id = mc.instanceid)
		INNER JOIN {grading_areas} AS ar ON (mc.id = ar.contextid)
		INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
		INNER JOIN {gradingform_rubric_criteria} AS a ON (d.id = a.definitionid)
		INNER JOIN {gradingform_rubric_levels} AS b ON (a.id = b.criterionid)
		LEFT JOIN (
		SELECT ec.*,
			ec.draft AS draftid
		FROM {emarking_draft} AS es
		INNER JOIN {emarking_comment} AS ec ON (ec.draft = :draft AND es.id = ec.draft)
		) AS E ON (E.levelid = b.id)
		LEFT JOIN {emarking_regrade} AS er ON (er.criterion = a.id AND er.draft = E.draftid)
		ORDER BY a.sortorder ASC, b.score ASC",
        array('coursemodule'=>$cm->id, 'draft'=>$draft->id));
    
$rubriclevels = array();
foreach ($rubricdesc as $rd) {
	// For each level we check if the criterion was created
	if(!isset($rubriclevels[$rd->criterionid])) {
		$rubriclevels[$rd->criterionid] = new stdClass();
		$rubriclevels[$rd->criterionid]->id = $rd->criterionid;
		$rubriclevels[$rd->criterionid]->description = $rd->description;
		$rubriclevels[$rd->criterionid]->levels = array();
		$rubriclevels[$rd->criterionid]->maxscore = $rd->score;
		$rubriclevels[$rd->criterionid]->rubricname = $rd->rubricname;
		$rubriclevels[$rd->criterionid]->bonus = $rd->bonus;
		$rubriclevels[$rd->criterionid]->regradeid = $rd->regradeid;
		$rubriclevels[$rd->criterionid]->motive = $rd->motive;
		$rubriclevels[$rd->criterionid]->regradecomment = $rd->regradecomment;
		$rubriclevels[$rd->criterionid]->regrademarkercomment = $rd->regrademarkercomment;
		$rubriclevels[$rd->criterionid]->regradeaccepted = $rd->regradeaccepted;
		$rubriclevels[$rd->criterionid]->markerassigned = 1;
		if($markersassigned && !is_siteadmin($USER)) {
			$rubriclevels[$rd->criterionid]->markerassigned = 0;
			foreach($markerscriteria as $markercriterion) {
				if($rd->criterionid == $markercriterion->criterion && $markercriterion->marker == $USER->id) {
					$rubriclevels[$rd->criterionid]->markerassigned = 1;
				}
			}
		}
	}

	// If the current level has a greater bonus than default, set it for the criterion
	if(abs($rd->bonus) > abs($rubriclevels[$rd->criterionid]->bonus)) {
		$rubriclevels[$rd->criterionid]->bonus = $rd->bonus;
	}

	// If the level has a regrade request, we set it for the criterion
	if($rd->regradeid > 0) {
		$rubriclevels[$rd->criterionid]->regradeid = $rd->regradeid;
		$rubriclevels[$rd->criterionid]->motive = $rd->motive;
		$rubriclevels[$rd->criterionid]->regradecomment = $rd->regradecomment;
		$rubriclevels[$rd->criterionid]->regradeaccepted = $rd->regradeaccepted;
	}

	$level = new stdClass();
	$level->id = $rd->levelid;
	$level->description = $rd->definition;
	$level->score = $rd->score;
	$level->commentid = $rd->commentid;
	$level->commenttext = $rd->commenttext;
	$level->markerid = $rd->markerid?$rd->markerid:0;
	$level->commentpage = $rd->commentpage;
	$rubriclevels[$rd->criterionid]->levels[] = $level;
	if($rd->score > $rubriclevels[$rd->criterionid]->maxscore) {
		$rubriclevels[$rd->criterionid]->maxscore = $rd->score;
	}
}

$results = $rubriclevels;