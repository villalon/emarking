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

/** The rubric level to be deleted in the corresponding submission **/
$rubriclevel = required_param('level', PARAM_INT);

// Basic validation
if($rubriclevel <= 0) {
	emarking_json_error("Invalid rubric level id");
}

// Get the comment corresponding the the level in this submission
if(!$comment = $DB->get_record_sql(
		"SELECT ec.*
		FROM {emarking_comment} AS ec
		INNER JOIN {emarking_page} AS ep 
			ON (ec.levelid = :level AND ep.submission = :submission AND ec.page = ep.id)"
		, array('level'=>$rubriclevel,'submission'=>$submission->id))){
	emarking_json_error("Invalid comment",array('levelid'=>$rubriclevel,'pagesid'=>$submission->id));
}

// Delete the comment
$DB->delete_records('emarking_comment',array('id'=>$comment->id));

// Update the final grade for the submission
list($finalgrade, $previouslvlid, $previouscomment) = 
	emarking_set_finalgrade(
			$userid, 
			$rubriclevel, 
			'', 
			$submission, 
			$emarking, 
			$context, 
			null, 
			true);

// Send the output if everything went well
if($finalgrade === false) {
	$output = array('error'=>'Invalid values from finalgrade',
			'grade' => $finalgrade,
			'lvlidprev' => $previouslvlid,
			'timemodified' => time());
} else {
	$output = array('error'=>'',
			'grade' => $finalgrade,
			'lvlidprev' => $previouslvlid,
			'timemodified' => time());
}