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

/** Comment position within the page **/
$posx = required_param('posx', PARAM_INT);
$posy = required_param('posy', PARAM_INT);

/**  Measures the correction window **/
$winwidth = required_param('windowswidth', PARAM_NUMBER);
$winheight =required_param('windowsheight', PARAM_NUMBER);

/** Height and Width **/
$width= required_param('width', PARAM_INT);
$height = required_param('height', PARAM_INT);


/** Page number **/
$pageno = required_param('pageno', PARAM_INT);

/** The comment itself **/
$comment = required_param('comment', PARAM_RAW_TRIMMED);

/** Comment format **/
$format = required_param('format', PARAM_INT);

// Get the page for this submission and page number
if(!$page = $DB->get_record('emarking_page', array('submission'=>$submission->id, 'page'=>$pageno))) {
	emarking_json_error("Invalid page for insterting comment");
}
/**if the comment belongs to a rubric criterion  **/
$criterionid = optional_param("criterionid", 0, PARAM_INT);

/**if the comment belongs to a rubric criterion  **/
$colour = optional_param("colour", NULL, PARAM_ALPHANUM);

/** transformation pixels screen to percentages **/

$posx = ($posx/$winwidth);
$posy = ($posy/$winheight);

// Create the new comment record
$emarking_comment = new stdClass();
$emarking_comment->page = $page->id;
$emarking_comment->posx = $posx;
$emarking_comment->posy = $posy;
$emarking_comment->width = $width;
$emarking_comment->height = $height;
$emarking_comment->pageno = $pageno;
$emarking_comment->timecreated = time();
$emarking_comment->timemodified = time();
$emarking_comment->rawtext = $comment;
$emarking_comment->markerid = $USER->id;
$emarking_comment->colour = $colour;
$emarking_comment->levelid = 0;
$emarking_comment->criterionid = $criterionid;
$emarking_comment->textformat = $format;

// Insert it into the database
$commentid = $DB->insert_record('emarking_comment', $emarking_comment );

// Send output info
$output = array('error'=>'',
		'id' => $commentid,
		'timemodified' => time(),
		'markerid'=>$USER->id,
		'markername'=>$USER->firstname . " " . $USER->lastname);
