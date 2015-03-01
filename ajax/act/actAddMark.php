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
 * @package mod
 * @subpackage emarking
 * @copyright 2012 Jorge Villalón {@link http://www.uai.cl}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Level id represents the level in the rubric **/
$rubriclevel = required_param('level', PARAM_INT);
/** Mark position in the page **/
$posx = required_param('posx', PARAM_INT);
$posy = required_param('posy', PARAM_INT);
/** Page number **/
$pageno = required_param('pageno', PARAM_INT);
/** Comment text **/
$comment = required_param('comment', PARAM_RAW_TRIMMED);
/** Bonus, positive or negative to be added to the level points **/
$bonus = optional_param('bonus', 0, PARAM_FLOAT);

/**  Measures the correction window **/
$winwidth = required_param('windowswidth', PARAM_NUMBER);
$winheight =required_param('windowsheight', PARAM_NUMBER);

// Get the page for the comment
if(!$page = $DB->get_record('emarking_page', 
		array('submission'=>$submission->id, 'page'=>$pageno))) {
	emarking_json_error('Invalid page for submission');
}

$rubricinfo = null;
$removeid = 0;

// Get the rubric information so we can get the max score
$rubricinfo = $DB->get_record_sql("
		SELECT c.definitionid, l.definition, l.criterionid, l.score, c.description
		FROM {gradingform_rubric_levels} as l
		INNER JOIN {gradingform_rubric_criteria} as c on (l.criterionid = c.id)
		WHERE l.id = ?", array($rubriclevel) );

// Get the maximum score for the criterion in which we are adding a mark
$maxscorerecord = $DB->get_record_sql("
		SELECT MAX(l.score) as maxscore
		FROM {gradingform_rubric_levels} as l
		WHERE l.criterionid = ?
		GROUP BY l.criterionid", array($rubricinfo->criterionid) );

// Get all the previous comments with the same criterion
$previouscomments = $DB->get_records_sql(
    "SELECT ec.*
		FROM {emarking_comment} AS ec
		WHERE draft = ?
            AND levelid in (
			SELECT id FROM {gradingform_rubric_levels} WHERE criterionid = ?)",
		array($draft->id, $rubricinfo->criterionid));

// Delete all records from the same criterion
foreach($previouscomments as $prevcomment) {
	$DB->delete_records('emarking_comment',array('id'=>$prevcomment->id));
	$removeid = $prevcomment->id;
}

/** transformation pixels screen to percentages **/

$posx = ($posx/$winwidth);
$posy = ($posy/$winheight);

// Create the new mark
$emarking_comment = new stdClass();
$emarking_comment->page = $page->id;
$emarking_comment->draft = $draft->id;
$emarking_comment->posx = $posx;
$emarking_comment->posy = $posy;
$emarking_comment->width = '140';
$emarking_comment->pageno = $pageno;
$emarking_comment->timecreated = time();
$emarking_comment->timemodified = time();
$emarking_comment->rawtext = $comment;
$emarking_comment->markerid = $USER->id;
$emarking_comment->colour = 'yellow';
$emarking_comment->levelid = $rubriclevel;
$emarking_comment->bonus = $bonus;
$emarking_comment->textformat = 2;

// Insert the record
$commentid = $DB->insert_record('emarking_comment', $emarking_comment );

$raterid= $USER->id;

// Update the final grade
list($finalgrade, $previouslevel, $previouscomment) = 
	emarking_set_finalgrade(
			$userid, 
			$rubriclevel, 
			$comment, 
			$submission, 
			$draft,
			$emarking, 
			$context, 
			null);

// When we add a mark we also have to include its regrade information (that may not be included)
$regrade = $DB->get_record('emarking_regrade', 
		array('draft'=>$draft->id, 'criterion'=>$rubricinfo->criterionid));

// If there was no regrade create default information (as empty)
if(!$regrade) {
	$regrade = new stdClass();
	$regrade->id = 0;
	$regrade->accepted = 0;
	$regrade->comment = '';
	$regrade->motive = 0;
	$regrade->markercomment = '';
} else {
	$regrade->accepted=1;
	$regrade->markercomment = $comment;
	$regrade->timemodified = time();
	$DB->update_record('emarking_regrade', $regrade);
}

// Send the output
if($finalgrade === false) {
	$output = array('error'=>'Invalid values from finalgrade',
			'grade' => $finalgrade,
			'comment' => $emarking_comment->rawtext,
			'criterionid' => $rubricinfo->criterionid,
			'definition' => $rubricinfo->definition,
			'description' => $rubricinfo->description,
			'score' => $rubricinfo->score,
			'maxscore' => $maxscorerecord->maxscore,
			'posx' => $posx,
			'posy' => $posy,
			'replaceid' => $removeid,
			'lvlid' => $rubriclevel,
			'id' => $commentid,
			'lvlidprev' => $previouslevel,
			'type' => 'rubricscore',
			'criteriondesc' => $rubricinfo->description,
			'leveldesc' => $rubricinfo->definition,
			'markerid' => $USER->id,
			'markername' => $USER->firstname.' '.$USER->lastname,
			'timemodified' => time(),
			'regradeid'=>$regrade->id,
			'regradeaccepted'=>$regrade->accepted,
			'regrademotive'=>$regrade->motive,
			'regradecomment'=>$regrade->comment,
			'regrademarkercomment'=>$regrade->markercomment
	);

} else {
	$output = array('error'=>'',
			'grade' => $finalgrade,
			'comment' => $emarking_comment->rawtext,
			'criterionid' => $rubricinfo->criterionid,
			'definition' => $rubricinfo->definition,
			'description' => $rubricinfo->description,
			'score' => $rubricinfo->score,
			'maxscore' => $maxscorerecord->maxscore,
			'posx' => $posx,
			'posy' => $posy,
			'replaceid' => $removeid,
			'lvlid' => $rubriclevel,
			'id' => $commentid,
			'lvlidprev' => $previouslevel,
			'type' => 'rubricscore',
			'criteriondesc' => $rubricinfo->description,
			'leveldesc' => $rubricinfo->definition,
			'markerid' => $USER->id,
			'markername' => $USER->firstname.' '.$USER->lastname,
			'timemodified' => time(),
			'regradeid'=>$regrade->id,
			'regradeaccepted'=>$regrade->accepted,
			'regrademotive'=>$regrade->motive,
			'regradecomment'=>$regrade->comment,
			'regrademarkercomment'=>$regrade->markercomment
	);
}