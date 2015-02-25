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
/** Page number **/
$motive = required_param('motive', PARAM_INT);
/** Comment text **/
$comment = required_param('comment', PARAM_RAW_TRIMMED);

// Verify that dates are ok
if(!emarking_is_regrade_requests_allowed($emarking)) {
	emarking_json_error('Regrade requests are not allowed for this activity.');
}

// Get the rubric info from the level
if(!$rubricinfo = $DB->get_record_sql("
		SELECT c.definitionid, l.definition, l.criterionid, l.score, c.description
		FROM {gradingform_rubric_levels} as l
		INNER JOIN {gradingform_rubric_criteria} as c on (l.criterionid = c.id)
		WHERE l.id = ?", array($rubriclevel) )) {
		emarking_json_error("Invalid rubric info");
}

$emarking_comment = $DB->get_record_sql('
		SELECT ec.* 
		FROM {emarking_comment} AS ec
		INNER JOIN {emarking_page} AS ep 
			ON (ec.levelid = :levelid AND ec.draft = :draft AND ep.submission = :submissionid AND ec.page = ep.id)', 
		array('levelid'=>$rubriclevel, 'draft'=>$draft->id, 'submissionid'=>$submission->id));

// Check if there was already a regrade request
$newrecord=false;
if(!$emarking_regrade = $DB->get_record('emarking_regrade', 
		array('draft'=>$draft->id, 'criterion'=>$rubricinfo->criterionid))) {
	$emarking_regrade = new stdClass();
	$newrecord=true;
}

// Make the changes that are for new records and previous
$emarking_regrade->motive = $motive;
$emarking_regrade->comment = $comment;
$emarking_regrade->accepted = 0;
$emarking_regrade->timemodified = time();

// If the record is new then add the basic information
if($newrecord) {
	$emarking_regrade->student = $USER->id;
	$emarking_regrade->draft = $draft->id;
	$emarking_regrade->criterion = $rubricinfo->criterionid;
	$emarking_regrade->timecreated = time();
	$emarking_regrade->markercomment = null;
	
	if($emarking_comment) {
		$emarking_regrade->levelid = $emarking_comment->levelid;
		$emarking_regrade->markerid = $emarking_comment->markerid;
		$emarking_regrade->bonus = $emarking_comment->bonus;
	}
}

// Insert or update the regrade request
if($newrecord) {
	$emarking_regrade->id = $DB->insert_record('emarking_regrade', $emarking_regrade );
} else {
	$DB->update_record('emarking_regrade', $emarking_regrade );
}

// Update the submission
$draft->timemodified = time();
$draft->status = EMARKING_STATUS_REGRADING;
$DB->update_record('emarking_draft', $draft);

// Send the output
$output = array('error'=>'',
		'regradeid' => $emarking_regrade->id,
		'comment' => $comment,
		'criterionid' => $rubricinfo->criterionid,
		'motive' => $motive,
		'timemodified' => time());
