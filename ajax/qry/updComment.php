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
 * @package   eMarking
 * @copyright 2013 Jorge Villalón <villalon@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("$CFG->dirroot/grade/grading/form/rubric/lib.php");

// Required and optional params for emarking
$commentid = required_param('cid', PARAM_INT);
$posx = required_param('posx', PARAM_INT);
$posy = required_param('posy', PARAM_INT);
$commentrawtext = required_param('comment', PARAM_RAW_TRIMMED);
$bonus = optional_param('bonus', 0, PARAM_FLOAT);
$levelid = optional_param('levelid', 0, PARAM_INT);
$format = required_param('format', PARAM_INT);
$regradeid = optional_param('regradeid', 0, PARAM_INT);
$regrademarkercomment = optional_param('regrademarkercomment', null, PARAM_RAW_TRIMMED);
$regradeaccepted = optional_param('regradeaccepted', 0, PARAM_INT);

/**  Measures the correction window **/
$winwidth = required_param('windowswidth', PARAM_NUMBER);
$winheight =required_param('windowsheight', PARAM_NUMBER);

if(!$comment = $DB->get_record('emarking_comment', array('id'=>$commentid))){
	emarking_json_error("Invalid comment",array("id"=>$commentid));
}

if($regradeid > 0 && !$regrade = $DB->get_record('emarking_regrade', array('id'=>$regradeid))){
	emarking_json_error("Invalid regrade",array("id"=>$regradeid));
}

$previousbonus = $comment->bonus;
$previouslvlid = $comment->levelid;
$previouscomment = $comment->rawtext;

if($previouslvlid > 0 && $levelid <= 0) {
	emarking_json_error("Invalid level id for a rubric id which has a previous level",array("id"=>$commentid,"levelid"=>$previouslvlid));
}

/** transformation pixels screen to percentages **/

$posx = ($posx/$winwidth);
$posy = ($posy/$winheight);

$comment->posx = $posx;
$comment->posy = $posy;
$comment->id = $commentid;
$comment->rawtext = $commentrawtext;
$comment->bonus = $bonus;
$comment->textformat = $format;
$comment->levelid = $levelid;


$DB->update_record('emarking_comment', $comment);

$diff = abs($previousbonus - $bonus);

if($comment->levelid > 0) {
	if($diff > 0.01 || $previouslvlid <> $levelid || $previouscomment !== $commentrawtext) {	
		emarking_set_finalgrade($userid, $levelid, $commentrawtext, $submission, $emarking, $context, null);
	}
}

if($regradeid > 0) {
	$regrade->markercomment = $regrademarkercomment;
	$regrade->timemodified = time();
	$regrade->accepted = $regradeaccepted;
	$DB->update_record('emarking_regrade', $regrade);
}

include "getSubmissionGrade.php";
$newgrade = $results->finalgrade;

?>