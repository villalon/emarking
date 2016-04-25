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
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2012-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Adds a regrade, including comment, from a marker
 *
 * @param unknown $emarking
 * @param unknown $draft
 * @return multitype:string number unknown NULL
 */
function emarking_regrade($emarking, $draft) {
    global $DB, $USER;
    // Level id represents the level in the rubric.
    $rubriclevel = required_param('level', PARAM_INT);
    // Page number.
    $motive = required_param('motive', PARAM_INT);
    // Comment text.
    $comment = required_param('comment', PARAM_RAW_TRIMMED);
    // Verify that dates are ok.
    if (! emarking_is_regrade_requests_allowed($emarking)) {
        emarking_json_error('Regrade requests are not allowed for this activity.');
    }
    // Get the rubric info from the level.
    if (! $rubricinfo = emarking_get_rubricinfo_by_level($rubriclevel)) {
        emarking_json_error("Invalid rubric info");
    }
    $emarkingcomment = emarking_get_comment_draft_by_levelid($draft, $rubriclevel);
    // Check if there was already a regrade request.
    $newrecord = false;
    if (! $emarkingregrade = $DB->get_record('emarking_regrade',
            array(
                'draft' => $draft->id,
                'criterion' => $rubricinfo->criterionid))) {
        $emarkingregrade = new stdClass();
        $newrecord = true;
    }
    // Make the changes that are for new records and previous.
    $emarkingregrade->motive = $motive;
    $emarkingregrade->comment = $comment;
    $emarkingregrade->accepted = 0;
    $emarkingregrade->timemodified = time();
    // If the record is new then add the basic information.
    if ($newrecord) {
        $emarkingregrade->student = $USER->id;
        $emarkingregrade->draft = $draft->id;
        $emarkingregrade->criterion = $rubricinfo->criterionid;
        $emarkingregrade->timecreated = time();
        $emarkingregrade->markercomment = null;
        if ($emarkingcomment) {
            $emarkingregrade->levelid = $emarkingcomment->levelid;
            $emarkingregrade->markerid = $emarkingcomment->markerid;
            $emarkingregrade->bonus = $emarkingcomment->bonus;
        }
    }
    // Insert or update the regrade request.
    if ($newrecord) {
        $emarkingregrade->id = $DB->insert_record('emarking_regrade', $emarkingregrade);
    } else {
        $DB->update_record('emarking_regrade', $emarkingregrade);
    }
    // Update the submission.
    $draft->timemodified = time();
    $draft->status = EMARKING_STATUS_REGRADING;
    $DB->update_record('emarking_draft', $draft);
    // Send the output.
    $output = array(
        'error' => '',
        'regradeid' => $emarkingregrade->id,
        'comment' => $comment,
        'criterionid' => $rubricinfo->criterionid,
        'motive' => $motive,
        'timemodified' => time());
    return $output;
}
/**
 * Marks a draft as finished
 *
 * @param unknown $emarking
 * @param unknown $submission
 * @param unknown $draft
 * @param unknown $user
 * @param unknown $context
 * @param unknown $cm
 * @param unknown $issupervisor
 * @return Ambigous <multitype:string , multitype:string unknown Ambigous <multitype:boolean , NULL, multitype:number NULL Ambigous
 *         > >
 */
function emarking_finish_marking($emarking, $submission, $draft, $user, $context, $cm, $issupervisor) {
    global $DB;
    // General feedback to include in the marking.
    $generalfeedback = required_param('feedback', PARAM_RAW_TRIMMED);
    // Firstly create the response pdf.
    if (emarking_create_response_pdf($draft, $user, $context, $cm->id)) {
        // If the pdf was created successfully then update the final grade and feedback.
        list($finalgrade, $previouslvlid, $previouscomment) = emarking_set_finalgrade(0, null, $submission, $draft, $emarking,
                $context, $generalfeedback, false, $cm->id);
        // It is only publish if there just one draft.
        if ($DB->count_records('emarking_draft',
                array(
                    'emarkingid' => $submission->emarking,
                    'submissionid' => $submission->id,
                    'qualitycontrol' => 0)) == 1) {
            emarking_publish_grade($draft);
        }
        $nextsubmission = emarking_get_next_submission($emarking, $draft, $context, $user, $issupervisor);
        // Send the output.
        $output = array(
            'error' => '',
            'message' => 'Feedback created successfully',
            'finalgrade' => $finalgrade,
            'previouslvlid' => $previouslvlid,
            'previouscomment' => $previouscomment,
            'nextsubmission' => $nextsubmission);
    } else {
        // Response couldn't be created.
        $output = array(
            'error' => 'Could not create response from eMarking.');
    }
    return $output;
}
/**
 * 
 * @param unknown $draft
 * @param unknown $levelid
 * @return unknown
 */
function emarking_get_comment_draft_by_levelid($draft, $levelid) {
    global $DB;
    $emarkingcomment = $DB->get_record('emarking_comment', array(
            'levelid' => $levelid,
            'draft' => $draft->id));
    return $emarkingcomment;
}
/**
 *
 * @param unknown $draft            
 * @param unknown $levelid            
 * @return unknown
 */
function emarking_get_comments_draft_by_criterion($draft, $criterionid) {
    global $DB;
    $previouscomments = $DB->get_records_sql(
            "SELECT ec.*
		FROM {emarking_comment} ec
		WHERE draft = ?
            AND levelid IN (
			SELECT id FROM {gradingform_rubric_levels} WHERE criterionid = ?)",
            array(
                $draft->id,
                $criterionid));
    return $previouscomments;
}
/**
 * 
 * @param unknown $submissionid
 * @return string
 */
function emarking_get_draft_ids_by_submission($submissionid) {
    global $DB;
    $drafts = $DB->get_records('emarking_draft', array('submissionid' => $submissionid));
    $draftids = array();
    foreach ($drafts as $d) {
        $draftids [] = $d->id;
    }
    $draftids = implode(",", $draftids);
    return $draftids;
}
/**
 * 
 * @param unknown $draft
 * @param unknown $criterionid
 * @return unknown
 */
function emarking_get_regrade_draft_by_criterion($draft, $criterionid) {
    global $DB;
    $regrade = $DB->get_record('emarking_regrade',
            array(
                    'draft' => $draft->id,
                    'criterion' => $criterionid));
    return $regrade;
}
/**
 * 
 * @param unknown $submission
 * @param unknown $pageno
 * @return unknown
 */
function emarking_get_page_submission_by_pageno($submission, $pageno) {
    global $DB;
    $page = $DB->get_record('emarking_page', array(
        'submission' => $submission->id,
        'page' => $pageno));
    return $page;
}
/**
 *
 * @param unknown $levelid            
 * @return unknown
 */
function emarking_get_rubricinfo_by_level($levelid) {
    global $DB;
    $rubricinfo = $DB->get_record_sql(
            "
		SELECT
		    grc.definitionid,
            grc.description,
		    grl.criterionid,
            grl.definition,
            grl.score
		FROM {gradingform_rubric_levels} grl
		INNER JOIN {gradingform_rubric_criteria} grc
            ON (grl.criterionid = grc.id)
		WHERE grl.id = ?", array(
                $levelid));
    return $rubricinfo;
}
/**
 * Deletes a mark from a draft
 *
 * @param unknown $submission
 * @param unknown $draft
 * @param unknown $emarking
 * @param unknown $context
 * @return Ambigous <multitype:string , multitype:string unknown number >
 */
function emarking_delete_mark($submission, $draft, $emarking, $context) {
    global $DB;
    // The rubric level to be deleted in the corresponding submission.
    $rubriclevel = required_param('level', PARAM_INT);
    // Basic validation.
    if ($rubriclevel <= 0) {
        emarking_json_error("Invalid rubric level id");
    }
    // Get the comment corresponding the the level in this submission.
    if (! $comment = emarking_get_comment_draft_by_levelid($draft, $rubriclevel)) {
        emarking_json_error("Invalid comment", array(
            'levelid' => $rubriclevel,
            'draft' => $draft->id));
    }
    // Delete the comment.
    $DB->delete_records('emarking_comment', array(
        'id' => $comment->id));
    // Update the final grade for the submission.
    list($finalgrade, $previouslvlid, $previouscomment) = emarking_set_finalgrade($rubriclevel, '', $submission, $draft, $emarking,
            $context, null, true);
    // Send the output if everything went well.
    if ($finalgrade === false) {
        $output = array(
            'error' => 'Invalid values from finalgrade');
    } else {
        $output = array(
            'error' => '',
            'grade' => $finalgrade,
            'lvlidprev' => $previouslvlid,
            'timemodified' => time());
    }
    return $output;
}
/**
 * Deletes a comment
 *
 * @return multitype:string number unknown
 */
function emarking_delete_comment() {
    global $DB;
    // The comment to delete.
    $commentid = required_param('id', PARAM_INT);
    // Basic validation.
    if ($commentid <= 0) {
        emarking_json_error("Invalid comment id");
    }
    // Get the comment from the database.
    if (! $comment = $DB->get_record("emarking_comment", array(
        "id" => $commentid))) {
        emarking_json_error("Comment not found with id");
    }
    // Verify comment is not a mark.
    if ($comment->textformat == 2) {
        emarking_json_error("You can not delete a mark as a comment");
    }
    // Delete the comment record.
    $DB->delete_records('emarking_comment', array(
        'id' => $commentid));
    // Send the output.
    $output = array(
        'error' => '',
        'id' => $commentid,
        'timemodified' => time());
    return $output;
}
/**
 * Checks the grading permission and logs unauthorized access
 *
 * @param unknown $readonly
 * @param unknown $cm
 */
function emarking_check_grade_permission($readonly, $draft, $context) {
    // Checks and logs attempt if we are within an grading action.
    if ($readonly) {
        $item = array(
            'context' => $context,
            'objectid' => $draft->id);
        // Add to Moodle log so some auditing can be done.
        \mod_emarking\event\unauthorizedajax_attempted::create($item)->trigger();
        emarking_json_error('Unauthorized access!');
    }
}
/**
 * Checks the requesto for regrade permission and logs unauthorized access
 *
 * @param unknown $readonly
 * @param unknown $cm
 */
function emarking_check_add_regrade_permission($ownsubmission, $draft, $context) {
    // Checks and logs attempt if we are within an grading action.
    if (!$ownsubmission) {
        $item = array(
            'context' => $context,
            'objectid' => $draft->id);
        // Add to Moodle log so some auditing can be done.
        \mod_emarking\event\unauthorizedajax_attempted::create($item)->trigger();
        emarking_json_error('Unauthorized access!');
    }
}
/**
 * Adds a mark to a draft
 *
 * @param unknown $submission
 * @param unknown $draft
 * @param unknown $emarking
 * @param unknown $context
 * @return Ambigous <multitype:string , multitype:string unknown number NULL Ambigous <boolean, number> >
 */
function emarking_add_mark($submission, $draft, $emarking, $context) {
    global $DB, $USER;
    // Level id represents the level in the rubric.
    $rubriclevel = required_param('level', PARAM_INT);
    // Mark position in the page.
    $posx = required_param('posx', PARAM_INT);
    $posy = required_param('posy', PARAM_INT);
    // Page number.
    $pageno = required_param('pageno', PARAM_INT);
    // Comment text.
    $comment = required_param('comment', PARAM_RAW_TRIMMED);
    // Bonus, positive or negative to be added to the level points.
    $bonus = optional_param('bonus', 0, PARAM_FLOAT);
    // Measures the correction window.
    $winwidth = required_param('windowswidth', PARAM_NUMBER);
    $winheight = required_param('windowsheight', PARAM_NUMBER);
    // Get the page for the comment.
    if (! $page = emarking_get_page_submission_by_pageno($submission, $pageno)) {
        emarking_json_error('Invalid page for submission');
    }
    // Get the rubric information so we can get the max score.
    $rubricinfo = emarking_get_rubricinfo_by_level($rubriclevel);
    $removeid = 0;
    // Get the maximum score for the criterion in which we are adding a mark.
    $maxscorerecord = $DB->get_record_sql(
            "
		SELECT MAX(score) AS maxscore
		FROM {gradingform_rubric_levels}
		WHERE criterionid = ?
		GROUP BY criterionid", array(
                $rubricinfo->criterionid));
    // Get all the previous comments with the same criterion.
    $previouscomments = emarking_get_comments_draft_by_criterion($draft, $rubricinfo->criterionid);
    // Delete all records from the same criterion.
    foreach ($previouscomments as $prevcomment) {
        $DB->delete_records('emarking_comment', array(
            'id' => $prevcomment->id));
        $removeid = $prevcomment->id;
    }
    // Transformation pixels screen to percentages.
    $posx = ($posx / $winwidth);
    $posy = ($posy / $winheight);
    // Create the new mark.
    $emarkingcomment = new stdClass();
    $emarkingcomment->page = $page->id;
    $emarkingcomment->draft = $draft->id;
    $emarkingcomment->posx = $posx;
    $emarkingcomment->posy = $posy;
    $emarkingcomment->width = '140';
    $emarkingcomment->pageno = $pageno;
    $emarkingcomment->timecreated = time();
    $emarkingcomment->timemodified = time();
    $emarkingcomment->rawtext = $comment;
    $emarkingcomment->markerid = $USER->id;
    $emarkingcomment->colour = 'yellow';
    $emarkingcomment->levelid = $rubriclevel;
    $emarkingcomment->criterionid = $rubricinfo->criterionid;
    $emarkingcomment->bonus = $bonus;
    $emarkingcomment->textformat = 2;
    // Insert the record.
    $commentid = $DB->insert_record('emarking_comment', $emarkingcomment);
    $raterid = $USER->id;
    // Update the final grade.
    list($finalgrade, $previouslevel, $previouscomment) = emarking_set_finalgrade($rubriclevel, $comment, $submission, $draft,
            $emarking, $context, null);
    // When we add a mark we also have to include its regrade information (that may not be included).
    $regrade = emarking_get_regrade_draft_by_criterion($draft, $rubricinfo->criterionid);
    // If there was no regrade create default information (as empty).
    if (! $regrade) {
        $regrade = new stdClass();
        $regrade->id = 0;
        $regrade->accepted = 0;
        $regrade->comment = '';
        $regrade->motive = 0;
        $regrade->markercomment = '';
    } else {
        $regrade->accepted = 1;
        $regrade->markercomment = $comment;
        $regrade->timemodified = time();
        $DB->update_record('emarking_regrade', $regrade);
    }
    // Send the output.
    if ($finalgrade === false) {
        $output = array(
            'error' => 'Invalid values from finalgrade');
    } else {
        $output = array(
            'error' => '',
            'grade' => $finalgrade,
            'comment' => $emarkingcomment->rawtext,
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
            'markername' => $USER->firstname . ' ' . $USER->lastname,
            'timemodified' => time(),
            'regradeid' => $regrade->id,
            'regradeaccepted' => $regrade->accepted,
            'regrademotive' => $regrade->motive,
            'regradecomment' => $regrade->comment,
            'regrademarkercomment' => $regrade->markercomment);
    }
    return $output;
}
/**
 * Adds a comment to a draft
 *
 * @param unknown $submission
 * @param unknown $draft
 * @return multitype:string number NULL Ambigous <boolean, number>
 */
function emarking_add_comment($submission, $draft) {
    global $DB, $USER;
    // Comment position within the page.
    $posx = required_param('posx', PARAM_INT);
    $posy = required_param('posy', PARAM_INT);
    // Measures the correction window.
    $winwidth = required_param('windowswidth', PARAM_NUMBER);
    $winheight = required_param('windowsheight', PARAM_NUMBER);
    // Height and Width.
    $width = required_param('width', PARAM_INT);
    $height = required_param('height', PARAM_INT);
    // Page number.
    $pageno = required_param('pageno', PARAM_INT);
    // The comment itself.
    $comment = required_param('comment', PARAM_RAW_TRIMMED);
    // Comment format.
    $format = required_param('format', PARAM_INT);
    // Get the page for this submission and page number.
    if (! $page = $DB->get_record('emarking_page', array(
        'submission' => $submission->id,
        'page' => $pageno))) {
        emarking_json_error("Invalid page for insterting comment");
    }
    // If the comment belongs to a rubric criterion.
    $criterionid = optional_param("criterionid", 0, PARAM_INT);
    // If the comment belongs to a rubric criterion.
    $colour = optional_param("colour", null, PARAM_ALPHANUM);
    // Transformation pixels screen to percentages.
    $posx = ($posx / $winwidth);
    $posy = ($posy / $winheight);
    // Create the new comment record.
    $emarkingcomment = new stdClass();
    $emarkingcomment->page = $page->id;
    $emarkingcomment->draft = $draft->id;
    $emarkingcomment->posx = $posx;
    $emarkingcomment->posy = $posy;
    $emarkingcomment->width = $width;
    $emarkingcomment->height = $height;
    $emarkingcomment->pageno = $pageno;
    $emarkingcomment->timecreated = time();
    $emarkingcomment->timemodified = time();
    $emarkingcomment->rawtext = $comment;
    $emarkingcomment->markerid = $USER->id;
    $emarkingcomment->colour = $colour;
    $emarkingcomment->levelid = 0;
    $emarkingcomment->criterionid = $criterionid;
    $emarkingcomment->textformat = $format;
    // Insert it into the database.
    $commentid = $DB->insert_record('emarking_comment', $emarkingcomment);
    // Send output info.
    $output = array(
        'error' => '',
        'id' => $commentid,
        'timemodified' => time(),
        'markerid' => $USER->id,
        'markername' => $USER->firstname . " " . $USER->lastname);
    return $output;
}
/**
 * Adds a action button collaborative
 *
 * @return id insert or update
 */
function emarking_add_action_collaborativebutton() {
    global $DB;
    $markerid = required_param("markerid", PARAM_INT);
    $commentid = required_param("commentid", PARAM_INT);
    $type = required_param("type", PARAM_INT);
    $status = required_param("status", PARAM_INT);
    $text = optional_param("text", null, PARAM_TEXT);
    // Discussion mark.
    if ($type == 4) {
        $collaborativebutton = new stdClass();
        $collaborativebutton->commentid = $commentid;
        $collaborativebutton->type = $type;
        $collaborativebutton->status = $status;
        $collaborativebutton->text = $text;
        $collaborativebutton->markerid = $markerid;
        $collaborativebutton->createdtime = time();
        // Insert it into the database.
        $id = $DB->insert_record('emarking_collaborative_work', $collaborativebutton);
    } else {
        if ($collaborativebutton = $DB->get_record("emarking_collaborative_work",
                array(
                    "commentid" => $commentid,
                    "markerid" => $markerid,
                    "type" => $type))) {
            $collaborativebutton->status = $status;
            $collaborativebutton->text = $text;
            // Update it into the database.
            $id = $DB->update_record("emarking_collaborative_work", $collaborativebutton);
        } else {
            $collaborativebutton = new stdClass();
            $collaborativebutton->commentid = $commentid;
            $collaborativebutton->type = $type;
            $collaborativebutton->status = $status;
            $collaborativebutton->text = $text;
            $collaborativebutton->markerid = $markerid;
            $collaborativebutton->createdtime = time();
            // Insert it into the database.
            $id = $DB->insert_record('emarking_collaborative_work', $collaborativebutton);
        }
    }
    // Send output info.
    if ($id) {
        $id = array(
            $id);
        emarking_json_resultset($id);
        break;
    }
    $output = $id;
}
/**
 * Adds a chat message
 *
 * @return multitype:string number Ambigous <boolean, number>
 */
function emarking_add_chat_message() {
    global $DB;
    // The message itself.
    $message = required_param('message', PARAM_RAW_TRIMMED);
    // Id of who sent the message.
    $userid = required_param('userid', PARAM_INT);
    // From where comes the message (course module).
    $room = required_param('room', PARAM_INT);
    // Can be Chat, SOS or Wall.
    $source = required_param('source', PARAM_INT);
    // The draft in which the message occured.
    $draftid = required_param('draftid', PARAM_INT);
    // Urgency of SOS request.
    $urgencylevel = optional_param('urgencylevel', null, PARAM_INT);
    // Status of SOS request.
    $status = optional_param('status', null, PARAM_INT);
    // Id of parent message in SOS request.
    $parentid = optional_param('parentid', null, PARAM_INT);
    // Create the new comment record.
    $emarkingchatmessage = new stdClass();
    $emarkingchatmessage->timecreated = time();
    $emarkingchatmessage->userid = $userid;
    $emarkingchatmessage->message = $message;
    $emarkingchatmessage->room = $room;
    $emarkingchatmessage->source = $source;
    $emarkingchatmessage->draftid = $draftid;
    $emarkingchatmessage->urgencylevel = $urgencylevel;
    $emarkingchatmessage->status = $status;
    $emarkingchatmessage->parentid = $parentid;
    // Insert it into the database.
    $messageid = $DB->insert_record('emarking_chat', $emarkingchatmessage);
    // Send output info.
    $output = array(
        'error' => '',
        'id' => $messageid,
        'timemodified' => time());
    return $output;
}
/**
 *
 * @param unknown $draft
 * @return unknown
 */
function emarking_get_submission_grade($draft) {
    global $DB;
    // The list of draft ids for the submission.
    $draftids = emarking_get_draft_ids_by_submission($draft->submissionid);
    // Gets the grade for this submission if any.
    $gradesql = "SELECT d.id,
    IFNULL(d.grade,e.grademin) as finalgrade,
    IFNULL(d.timecreated, d.timemodified) as timecreated,
    IFNULL(d.timemodified,d.timecreated) as timemodified,
    IFNULL(d.generalfeedback,'') as feedback,
    d.qualitycontrol,
    e.name as activityname,
    e.grademin,
    e.grade as grademax,
    IFNULL(u.firstname, '') as firstname,
    IFNULL(u.lastname, '') as lastname,
    IFNULL(u.id, 0) as studentid,
    u.email as email,
    c.fullname as coursename,
    c.shortname as courseshort,
    c.id as courseid,
    e.custommarks,
    e.regraderestrictdates,
    e.regradesopendate,
    e.regradesclosedate,
    e.markingduedate,
    '$draftids' as drafts
    FROM {emarking_draft} d
    INNER JOIN {emarking} e ON (d.id = ? AND d.emarkingid = e.id)
    INNER JOIN {emarking_submission} s ON (s.id = d.submissionid)
    LEFT JOIN {user} u ON (s.student = u.id)
    LEFT JOIN {course} c ON (c.id = e.course)
    LEFT JOIN {user} um on (d.teacher = um.id)";
    $results = $DB->get_record_sql($gradesql, array(
        $draft->id));
    return $results;
}
/**
 *
 * @return multitype:
 */
function emarking_get_values_collaborative() {
    global $DB;
    // The comment id.
    $commentid = required_param('commentid', PARAM_INT);
    $type = optional_param('type', null, PARAM_INT);
    $filter = "";
    if ($type != null) {
        $filter = "AND cw.type = $type";
    }
    $sqlvaluesbuttons = "
            SELECT cw.id,
            cw.markerid,
            cw.type,
            CONCAT(u.username, ' ', u.lastname) AS markername,
            FROM_UNIXTIME(cw.createdtime) AS date,
            cw.text
    FROM {emarking_collaborative_work} cw
    INNER JOIN {user} u ON (cw.markerid = u.id)
    WHERE cw.commentid = ? AND cw.status = ? $filter
    ORDER BY cw.createdtime ASC";
    $collaborativevalues = $DB->get_records_sql($sqlvaluesbuttons, array(
        $commentid,
        '1'));
    if (! $collaborativevalues || $collaborativevalues == null) {
        $collaborativevalues = array();
        emarking_json_resultset($collaborativevalues);
        break;
    } else {
        foreach ($collaborativevalues as $obj) {
            $output [] = $obj;
        }
    }
    return $collaborativevalues;
}
/**
 *
 * @param unknown $context
 * @param unknown $emarking
 * @return Ambigous <multitype:multitype: NULL , multitype:NULL >
 */
function emarking_get_markers_configuration($context, $emarking) {
    global $DB;
    // Initially we have empty results.
    $results = array();
    // Get rubric instance.
    list($gradingmanager, $gradingmethod, $definition) = emarking_validate_rubric($context);
    $results ['rubricname'] = $definition->name;
    $results ['criteria'] = array();
    foreach ($definition->rubric_criteria as $criterion) {
        $results ['criteria'] [] = array(
            'id' => $criterion ['id'],
            'description' => $criterion ['description']);
    }
    // Generate markers list.
    $results ['markers'] = array();
    $indices = array();
    // Get all users with permission to grade in emarking.
    $markers = get_enrolled_users($context, 'mod/emarking:grade');
    // Add all users to markers list, we set criterion to 0.
    $i = 0;
    foreach ($markers as $marker) {
        $results ['markers'] [] = array(
            'id' => $marker->id,
            'fullname' => $marker->firstname . ' ' . $marker->lastname,
            'criteria' => array());
        $indices [$marker->id] = $i;
        $i ++;
    }
    // We get previous configuration of criteria for markers and set accordingly.
    $markerscriteria = $DB->get_records('emarking_marker_criterion', array(
        'emarking' => $emarking->id));
    foreach ($markerscriteria as $markercriterion) {
        $results ['markers'] [$indices [$markercriterion->marker]] ['criteria'] [] = array(
            'id' => $markercriterion->criterion);
    }
    // Generate pages list.
    $results ['pages'] = array();
    $indices = array();
    // We create a list of pages according to the total pages configured for emarking.
    // All pages are set to criterion 0.
    for ($i = 1; $i <= $emarking->totalpages; $i ++) {
        $results ['pages'] [] = array(
            'page' => $i,
            'criteria' => array());
    }
    // We load previous configuration of page criterion assignments.
    $pagescriteria = $DB->get_records('emarking_page_criterion', array(
        'emarking' => $emarking->id));
    foreach ($pagescriteria as $pagecriterion) {
        $results ['pages'] [($pagecriterion->page - 1)] ['criteria'] [] = array(
            'id' => $pagecriterion->criterion);
    }
    return $results;
}
/**
 *
 * @param unknown $submission
 * @param unknown $draft
 * @param unknown $cm
 * @param unknown $readonly
 * @param unknown $issupervisor
 * @return multitype:stdClass
 */
function emarking_get_rubric_submission($submission, $draft, $cm, $readonly, $issupervisor) {
    global $DB, $USER;
    $markerscriteria = $DB->get_records('emarking_marker_criterion', array(
        'emarking' => $submission->emarking));
    $markersassigned = count($markerscriteria) > 0 && ! $readonly && ! $issupervisor;
    $rubricdesc = $DB->get_recordset_sql(
            "SELECT
		gd.name AS rubricname,
		grc.id AS criterionid,
		grc.description,
        grc.sortorder,
		grl.definition,
		grl.id AS levelid,
		grl.score,
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
		FROM {course_modules} cm
		INNER JOIN {context} c ON (cm.id = :coursemodule AND c.contextlevel = 70 AND cm.id = c.instanceid)
		INNER JOIN {grading_areas} ga ON (c.id = ga.contextid)
		INNER JOIN {grading_definitions} gd ON (ga.id = gd.areaid)
		INNER JOIN {gradingform_rubric_criteria} grc ON (gd.id = grc.definitionid)
		INNER JOIN {gradingform_rubric_levels} grl ON (grc.id = grl.criterionid)
		LEFT JOIN (
		SELECT ec.*,
			ec.draft AS draftid
		FROM {emarking_draft} dd
		INNER JOIN {emarking_comment} ec ON (ec.draft = :draft
            AND dd.id = ec.draft)
		) E ON (E.levelid = grl.id)
		LEFT JOIN {emarking_regrade} er ON (er.criterion = grc.id
            AND er.draft = E.draftid)
		ORDER BY grc.sortorder ASC, grl.score ASC", array(
                'coursemodule' => $cm->id,
                'draft' => $draft->id));
    $rubriclevels = array();
    foreach ($rubricdesc as $rd) {
        // For each level we check if the criterion was created.
        if (! isset($rubriclevels [$rd->criterionid])) {
            $rubriclevels [$rd->criterionid] = new stdClass();
            $rubriclevels [$rd->criterionid]->id = $rd->criterionid;
            $rubriclevels [$rd->criterionid]->description = $rd->description;
            $rubriclevels [$rd->criterionid]->levels = array();
            $rubriclevels [$rd->criterionid]->maxscore = $rd->score;
            $rubriclevels [$rd->criterionid]->rubricname = $rd->rubricname;
            $rubriclevels [$rd->criterionid]->bonus = $rd->bonus;
            $rubriclevels [$rd->criterionid]->sortorder = $rd->sortorder;
            $rubriclevels [$rd->criterionid]->regradeid = $rd->regradeid;
            $rubriclevels [$rd->criterionid]->motive = $rd->motive;
            $rubriclevels [$rd->criterionid]->regradecomment = $rd->regradecomment;
            $rubriclevels [$rd->criterionid]->regrademarkercomment = $rd->regrademarkercomment;
            $rubriclevels [$rd->criterionid]->regradeaccepted = $rd->regradeaccepted;
            $rubriclevels [$rd->criterionid]->markerassigned = 1;
            if ($markersassigned && ! is_siteadmin($USER)) {
                $rubriclevels [$rd->criterionid]->markerassigned = 0;
                foreach ($markerscriteria as $markercriterion) {
                    if ($rd->criterionid == $markercriterion->criterion && $markercriterion->marker == $USER->id) {
                        $rubriclevels [$rd->criterionid]->markerassigned = 1;
                    }
                }
            }
        }
        // If the current level has a greater bonus than default, set it for the criterion.
        if (abs($rd->bonus) > abs($rubriclevels [$rd->criterionid]->bonus)) {
            $rubriclevels [$rd->criterionid]->bonus = $rd->bonus;
        }
        // If the level has a regrade request, we set it for the criterion.
        if ($rd->regradeid > 0) {
            $rubriclevels [$rd->criterionid]->regradeid = $rd->regradeid;
            $rubriclevels [$rd->criterionid]->motive = $rd->motive;
            $rubriclevels [$rd->criterionid]->regradecomment = $rd->regradecomment;
            $rubriclevels [$rd->criterionid]->regradeaccepted = $rd->regradeaccepted;
        }
        $level = new stdClass();
        $level->id = $rd->levelid;
        $level->description = $rd->definition;
        $level->score = $rd->score;
        $level->commentid = $rd->commentid;
        $level->commenttext = $rd->commenttext;
        $level->markerid = $rd->markerid ? $rd->markerid : 0;
        $level->commentpage = $rd->commentpage;
        $rubriclevels [$rd->criterionid]->levels [] = $level;
        if ($rd->score > $rubriclevels [$rd->criterionid]->maxscore) {
            $rubriclevels [$rd->criterionid]->maxscore = $rd->score;
        }
    }
    $results = $rubriclevels;
    return $results;
}
/**
 * Returns the list of draft ids for answer keys
 * @param unknown $submission
 * @return multitype:stdClass
 */
function emarking_get_answerkeys_submission($submission) {
    global $DB, $USER;
    $results = $DB->get_records_sql('
            SELECT d.id,
                   d.grade,
                   d.teacher
            FROM {emarking_submission} s
            INNER JOIN {emarking_draft} d ON (s.emarking = :emarking AND s.answerkey = 1 AND d.submissionid = s.id)',
            array('emarking' => $submission->emarking));
    return $results;
}
/**
 * Selects a submission as answer key for the specific emarking activity
 * @param unknown $submission
 * @param unknown $newstatus
 * @return unknown
 */
function emarking_set_answer_key($submission, $newstatus) {
    global $DB;
    if($newstatus < EMARKING_ANSWERKEY_NONE || $newstatus > EMARKING_ANSWERKEY_ACCEPTED) {
        throw new Exception('Invalid status for answerkey');
    }
    $submission->answerkey = $newstatus;
    $DB->update_record("emarking_submission", $submission);
    return $newstatus;
}
/**
 *
 * @param unknown $draft
 * @param unknown $pageno
 * @return multitype:
 */
function emarking_get_comments_submission($draft, $pageno) {
    global $DB;
    $sqlcomments = "SELECT
		ec.id,
		ec.posx,
		ec.posy,
		ec.rawtext,
		ec.textformat AS format,
		ec.width,
		ec.height,
		ec.colour,
		ep.page AS pageno,
		IFNULL(ec.bonus,0) AS bonus,
		grm.maxscore,
		ec.levelid,
		grl.score AS score,
		grl.definition AS leveldesc,
		IFNULL(grc.id,0) AS criterionid,
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
		ec.timecreated
		FROM {emarking_comment} ec
		INNER JOIN {emarking_page} ep ON (ec.page = ep.id AND ep.page = :pageno AND ec.draft = :draft)
		INNER JOIN {emarking_draft} d ON (ec.draft = d.id)
		INNER JOIN {user} u ON (ec.markerid = u.id)
		LEFT JOIN {gradingform_rubric_levels} grl ON (ec.levelid = grl.id)
		LEFT JOIN {gradingform_rubric_criteria} grc ON (grl.criterionid = grc.id)
		LEFT JOIN (
			SELECT grls.criterionid,
			MAX(score) AS maxscore
			FROM {gradingform_rubric_levels} grls
			GROUP BY grls.criterionid
		) grm ON (grc.id = grm.criterionid)
		LEFT JOIN {emarking_regrade} er ON (er.criterion = grc.id AND er.draft = es.id)
		ORDER BY ec.levelid DESC";
    $params = array(
        'pageno' => $pageno,
        'draft' => $draft->id);
    // Measures the correction window.
    $winwidth = required_param('windowswidth', PARAM_NUMBER);
    $winheight = required_param('windowsheight', PARAM_NUMBER);
    $results = $DB->get_records_sql($sqlcomments, $params);
    if (! $results) {
        $results = array();
    } else {
        // Transform percentages to pixels.
        foreach ($results as $result) {
            $result->posx = (string) ((int) ($result->posx * $winwidth));
            $result->posy = (string) ((int) ($result->posy * $winheight));
        }
    }
    return $results;
}
/**
 *
 * @param unknown $submission
 * @param unknown $draft
 * @return unknown
 */
function emarking_get_previous_comments($submission, $draft) {
    global $DB, $USER;
    $results = $DB->get_records_sql(
            "SELECT
            MIN(T.id) AS id,
			T.text AS text,
			T.format AS format,
			COUNT(T.id) AS used,
			MAX(lastused) AS lastused,
			GROUP_CONCAT(T.markerid SEPARATOR '-') as markerids,
            SUM(T.owncomment) as owncomment,
            GROUP_CONCAT(T.page SEPARATOR '-') as pages,
            GROUP_CONCAT(T.id SEPARATOR '-') as commentids,
            GROUP_CONCAT(T.criterionid SEPARATOR '-') as criteria,
            GROUP_CONCAT(T.draftid SEPARATOR '-') as drafts
			FROM (
			SELECT ec.id AS id,
			ec.rawtext AS text,
			ec.textformat AS format,
			1 AS used,
			ec.timemodified AS lastused,
			ec.markerid,
            ec.criterionid,
            ec.draft AS draftid,
            CASE WHEN ec.markerid = :user THEN 1 ELSE 0 END AS owncomment,
            CASE WHEN ec.draft = :draft THEN ec.pageno ELSE 0 END AS page
			FROM {emarking_submission} s
			INNER JOIN {emarking_draft} d ON (s.emarking = :emarking AND d.submissionid = s.id)
			INNER JOIN {emarking_comment} ec ON (ec.draft = d.id)
			WHERE ec.textformat IN (1,2) AND LENGTH(rawtext) > 0
			UNION
			SELECT  id,
					text,
					1,
					1,
					0,
					0,
					0,
                    0,
                    0,
                    0
			from {emarking_predefined_comment}
			WHERE emarkingid = :emarking2) AS T
			GROUP BY text
			ORDER BY text",
            array(
                'user' => $USER->id,
                'draft' => $draft->id,
                'emarking' => $submission->emarking,
                'emarking2' => $submission->emarking));
    return $results;
}
/**
 *
 * @param unknown $submission
 * @param unknown $draft
 * @param unknown $emarking
 * @param unknown $context
 * @return unknown
 */
function emarking_update_comment($submission, $draft, $emarking, $context) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/grade/grading/form/rubric/lib.php");
    // Required and optional params for emarking.
    $userid = required_param('markerid', PARAM_INT);
    $commentid = required_param('cid', PARAM_INT);
    $commentrawtext = required_param('comment', PARAM_RAW_TRIMMED);
    $bonus = optional_param('bonus', - 1, PARAM_FLOAT);
    $levelid = optional_param('levelid', 0, PARAM_INT);
    $format = optional_param('format', 2, PARAM_INT);
    $regradeid = optional_param('regradeid', 0, PARAM_INT);
    $regrademarkercomment = optional_param('regrademarkercomment', null, PARAM_RAW_TRIMMED);
    $regradeaccepted = optional_param('regradeaccepted', 0, PARAM_INT);
    $posx = required_param('posx', PARAM_INT);
    $posy = required_param('posy', PARAM_INT);
    // Measures the correction window.
    $winwidth = optional_param('windowswidth', '-1', PARAM_NUMBER);
    $winheight = optional_param('windowsheight', '-1', PARAM_NUMBER);
    if (! $comment = $DB->get_record('emarking_comment', array(
        'id' => $commentid))) {
        emarking_json_error("Invalid comment", array(
            "id" => $commentid));
    }
    if ($regradeid > 0 && ! $regrade = $DB->get_record('emarking_regrade', array(
        'id' => $regradeid))) {
        emarking_json_error("Invalid regrade", array(
            "id" => $regradeid));
    }
    $previousbonus = $comment->bonus;
    $previouslvlid = $comment->levelid;
    $previouscomment = $comment->rawtext;
    if ($bonus < 0) {
        $bonus = $previousbonus;
    }
    if ($commentrawtext === 'delphi') {
        $commentrawtext = $previouscomment;
    }
    if ($previouslvlid > 0 && $levelid <= 0) {
        emarking_json_error("Invalid level id for a rubric id which has a previous level",
                array(
                    "id" => $commentid,
                    "levelid" => $previouslvlid));
    }
    // Transformation pixels screen to percentages.
    if ($winheight != - 1 && $winheight != - 1) {
        $posx = ($posx / $winwidth);
        $posy = ($posy / $winheight);
        $comment->posx = $posx;
        $comment->posy = $posy;
    }
    $comment->id = $commentid;
    $comment->rawtext = $commentrawtext;
    $comment->bonus = $bonus;
    $comment->textformat = $format;
    $comment->levelid = $levelid;
    $comment->markerid = $userid;
    $DB->update_record('emarking_comment', $comment);
    $diff = abs($previousbonus - $bonus);
    if ($comment->levelid > 0) {
        if ($diff > 0.01 || $previouslvlid != $levelid || $previouscomment !== $commentrawtext) {
            emarking_set_finalgrade($levelid, $commentrawtext, $submission, $draft, $emarking, $context, null);
        }
    }
    if ($regradeid > 0) {
        $regrade->markercomment = $regrademarkercomment;
        $regrade->timemodified = time();
        $regrade->accepted = $regradeaccepted;
        $DB->update_record('emarking_regrade', $regrade);
        $remainingregrades = $DB->count_records("emarking_regrade",
                array(
                    "draft" => $draft->id,
                    "accepted" => 0));
        if ($remainingregrades == 0) {
            $draft->status = EMARKING_STATUS_REGRADING_RESPONDED;
            $draft->timemodified = time();
            $DB->update_record("emarking_draft", $draft);
        }
    }
    $results = emarking_get_submission_grade($draft);
    $newgrade = $results->finalgrade;
    return $newgrade;
}