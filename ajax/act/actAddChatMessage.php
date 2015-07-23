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
 * @copyright 2015 Francisco García {@link http://www.uai.cl}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/** The message itself **/
$message = required_param('message', PARAM_RAW_TRIMMED);

/** Id  of who sent the message **/
$userid = required_param('userid', PARAM_INT);

/** from where comes the message (course module)  **/
$room = required_param('room', PARAM_INT);

/** Can be Chat, SOS or Wall **/
$source = required_param('source', PARAM_INT);

/**  **/
$draftid = required_param('draftid', PARAM_INT);

/** Urgency of SOS request**/
$urgencylevel = optional_param('urgencylevel',null ,PARAM_INT);

/**Status of SOS  request **/
$status = optional_param('status',null ,PARAM_INT);

/** Id of parent message in SOS request **/
$parentid = optional_param('parentid',null ,PARAM_INT);

// Get the page for this submission and page number
if(!$page = $DB->get_record('emarking_page', array('submission'=>$submission->id, 'page'=>$pageno))) {
	emarking_json_error("Invalid page for insterting comment");
}



// Create the new comment record
$emarking_chat_message = new stdClass();
$emarking_chat_message->timecreated = time();
$emarking_chat_message->userid = $userid;
$emarking_chat_message->message = $message;
$emarking_chat_message->room = $room;
$emarking_chat_message->source = $source;
$emarking_chat_message->draftid = $draftid;
$emarking_chat_message->urgencylevel = $urgencylevel;
$emarking_chat_message->status = $status;
$emarking_chat_message->parentid = $parentid;

// Insert it into the database
$messageid = $DB->insert_record('emarking_chat', $emarking_chat_message );

// Send output info
$output = array('error'=>'',
		'id' => $messageid,
		'timemodified' => time());
