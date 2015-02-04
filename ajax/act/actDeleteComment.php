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

/** The comment to delete **/
$commentid = required_param('id', PARAM_INT);

// Basic validation
if($commentid <= 0) {
	emarking_json_error("Invalid comment id");
}

// Get the comment from the database
if(!$comment = $DB->get_record("emarking_comment", array("id"=>$commentid))) {
	emarking_json_error("Comment not found with id");
}


// Verify comment is not a mark
if($comment->textformat == 2) {
	emarking_json_error("You can not delete a mark as a comment");
}

// Delete the comment record
$DB->delete_records('emarking_comment',array('id'=>$commentid));

// Send the output
$output = array('error'=>'',
		'id' => $commentid,
		'timemodified' => time());
emarking_json_array ( $output );