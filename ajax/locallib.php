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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

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
		WHERE ec.levelid = :levelid AND ec.draft = :draft',
        array('levelid'=>$rubriclevel, 'draft'=>$draft->id));
    
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
 * @return Ambigous <multitype:string , multitype:string unknown Ambigous <multitype:boolean , NULL, multitype:number NULL Ambigous > >
 */
function emarking_finish_marking($emarking, $submission, $draft, $user, $context, $cm, $issupervisor) {
    global $DB;
    
    /** General feedback to include in the marking **/
    $generalfeedback = required_param('feedback', PARAM_RAW_TRIMMED);
    
    // Firstly create the response pdf
    if (emarking_create_response_pdf($draft,$user,$context, $cm->id)) {
    
        // If the pdf was created successfully then update the final grade and feedback
        list($finalgrade, $previouslvlid, $previouscomment) = emarking_set_finalgrade(
            0,
            null,
            $submission,
            $draft,
            $emarking,
            $context,
            $generalfeedback,
            false,
            $cm->id);
    
    
        // It is only publish if there just one draft
        if($DB->count_records('emarking_draft',
            array('emarkingid'=>$submission->emarking,
                'submissionid'=>$submission->id,
                'qualitycontrol'=>0))==1) {
                emarking_publish_grade($draft);
            }
    
            $nextsubmission = emarking_get_next_submission($emarking, $draft, $context, $user, $issupervisor);
    
            // Send the output
            $output = array('error'=>'',
                'message'=>'Feedback created successfully',
                'finalgrade'=>$finalgrade,
                'previouslvlid'=>$previouslvlid,
                'previouscomment'=>$previouscomment,
                'nextsubmission'=>$nextsubmission
            );
    } else {
        // Response couldn't be created
        $output = array('error'=>'Could not create response from eMarking.');
    }
    
    return $output;
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
		WHERE ec.levelid = :level AND ec.draft = :draft"
        , array('level'=>$rubriclevel,'draft'=>$draft->id))){
        emarking_json_error("Invalid comment",array('levelid'=>$rubriclevel,'draft'=>$draft->id));
    }
    
    // Delete the comment
    $DB->delete_records('emarking_comment',array('id'=>$comment->id));
    
    // Update the final grade for the submission
    list($finalgrade, $previouslvlid, $previouscomment) =
    emarking_set_finalgrade(
        $rubriclevel,
        '',
        $submission,
        $draft,
        $emarking,
        $context,
        null,
        true);
    
    // Send the output if everything went well
    if($finalgrade === false) {
        $output = array('error'=>'Invalid values from finalgrade');
    } else {
        $output = array('error'=>'',
            'grade' => $finalgrade,
            'lvlidprev' => $previouslvlid,
            'timemodified' => time());
    }
    
    return $output;
}

/**
 * Deletes a marker or page from a criterion
 * 
 * @param unknown $action
 * @return multitype:string number unknown NULL
 */
function emarking_delete_criterion_from_markerpage($action) {
    global $DB, $USER;
    
    /** Comment position within the page **/
    $markerpageid = required_param('id', PARAM_INT);
    
    /** Comment position within the page **/
    $criterion = required_param('criterion', PARAM_INT);
    
    if($action === 'delmarker')
        $DB->delete_records('emarking_marker_criterion', array('marker'=>$markerpageid, 'criterion'=>$criterion));
    else if($action === 'delpage')
        $DB->delete_records('emarking_page_criterion', array('page'=>$markerpageid, 'criterion'=>$criterion));
    
    // Send output info
    $output = array('error'=>'',
        'id' => $markerpageid,
        'timemodified' => time(),
        'userid'=>$USER->id,
        'username'=>$USER->firstname . " " . $USER->lastname);
    
    return $output;
}

/**
 * Deletes a comment
 * 
 * @return multitype:string number unknown
 */
function emarking_delete_comment() {
    global $DB;
    
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
    
    return $output;
}

/**
 * Checks the grading permission and logs unauthorized access
 * @param unknown $readonly
 * @param unknown $cm
 */
function emarking_check_grade_permission($readonly, $draft, $context) {
    // Checks and logs attempt if we are within an grading action
    if($readonly) {
        $item = array (
            'context' => $context,
            'objectid' => $draft->id,
        );
        // Add to Moodle log so some auditing can be done
        \mod_emarking\event\unauthorizedajax_attempted::create ( $item )->trigger ();
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
        $emarking_comment->criterionid = $rubricinfo->criterionid;
        $emarking_comment->bonus = $bonus;
        $emarking_comment->textformat = 2;
    
        // Insert the record
        $commentid = $DB->insert_record('emarking_comment', $emarking_comment );
    
        $raterid= $USER->id;
    
        // Update the final grade
        list($finalgrade, $previouslevel, $previouscomment) =
        emarking_set_finalgrade(
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
            $output = array('error'=>'Invalid values from finalgrade');
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
        
        return $output;
}

/**
 * Adds a marker or page to a criterion
 * 
 * @param unknown $emarking
 * @return multitype:string number unknown NULL
 */
function emarking_add_criterion_markerpage($emarking) {
    global $DB, $USER;
    
    /** Comment position within the page **/
    $markerpageid = required_param('id', PARAM_INT);
    $criteria = required_param('criteria', PARAM_SEQUENCE);
    
    // Get the criteria already associated to the marker or page from the database
    if($action === 'addmarker')
        $current = $DB->get_records('emarking_marker_criterion', array('emarking'=>$emarking->id, 'marker'=>$markerpageid));
    else if($action === 'addpage')
        $current = $DB->get_records('emarking_page_criterion', array('emarking'=>$emarking->id, 'page'=>$markerpageid));
    
    // Create an array with the existing criteria for this user
    $existingCriteria = array();
    foreach($current as $currentCriterion) {
        $existingCriteria[] = $currentCriterion->criterion;
    }
    
    // Get the criteria to add as an array of integers
    $criteriaArray = explode(',', $criteria);
    
    // The final array which will be added
    $criteriaToAdd = array();
    
    // Validate that criteria to add is not among the already associated
    foreach($criteriaArray as $criterion) {
        if(!in_array($criterion, $existingCriteria) && is_numeric($criterion)) {
            $criteriaToAdd[] = $criterion;
        }
    }
    
    // Now for each of the criterion to add insert the corresponding record
    foreach($criteriaToAdd as $criterionToAdd) {
    
        // Create the new comment record
        $marker_criterion = new stdClass();
        $marker_criterion->emarking = $emarking->id;
        if($action === 'addmarker')
            $marker_criterion->marker = $markerpageid;
        else if($action === 'addpage')
            $marker_criterion->page = $markerpageid;
        $marker_criterion->criterion = $criterionToAdd;
        $marker_criterion->timecreated = time();
        $marker_criterion->timemodified = time();
    
        // Insert it into the database
        if($action === 'addmarker')
            $newid = $DB->insert_record('emarking_marker_criterion', $marker_criterion);
        else if($action === 'addpage')
            $newid = $DB->insert_record('emarking_page_criterion', $marker_criterion);
    }
    
    // Send output info
    $output = array('error'=>'',
        'id' => $markerpageid,
        'timemodified' => time(),
        'userid'=>$USER->id,
        'username'=>$USER->firstname . " " . $USER->lastname);
    
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
    
    // $userid = required_param('markerid',PARAM_INT);
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
    $emarking_comment->draft = $draft->id;
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
    
    return $output;
}

/**
 * Adds a action button collaborative
 * @return id insert or update
 */
function emarking_add_action_collaborativebutton() {
	global $DB;

	$markerid = required_param("markerid", PARAM_INT);
	$commentid = required_param("commentid", PARAM_INT);
	$type = required_param("type", PARAM_INT);
	$status = required_param("status", PARAM_INT);
	
	$text = optional_param("text", null, PARAM_TEXT);
	
	// Discussion mark
	if($type == 4){
		$collaborativebutton =  new stdClass();
		$collaborativebutton->commentid = $commentid;
		$collaborativebutton->type = $type;
		$collaborativebutton->status = $status;
		$collaborativebutton->text = $text;
		$collaborativebutton->markerid = $markerid;
		$collaborativebutton->createdtime = time();
	
		// Insert it into the database
		$id = $DB->insert_record('emarking_collaborative_work', $collaborativebutton );
	
	}else{
		if($collaborativebutton = $DB->get_record("emarking_collaborative_work",array(
				"commentid" => $commentid,
				"markerid" => $markerid,
				"type" => $type
		))){
			$collaborativebutton->status = $status;
			$collaborativebutton->text = $text;
	
			// Update it into the database
			$id = $DB->update_record("emarking_collaborative_work", $collaborativebutton);
		}else{
			$collaborativebutton =  new stdClass();
			$collaborativebutton->commentid = $commentid;
			$collaborativebutton->type = $type;
			$collaborativebutton->status = $status;
			$collaborativebutton->text = $text;
			$collaborativebutton->markerid = $markerid;
			$collaborativebutton->createdtime = time();
	
			// Insert it into the database
			$id = $DB->insert_record('emarking_collaborative_work', $collaborativebutton );
		}
	}
	// Send output info
	if($id){
		$id = array($id);
		emarking_json_resultset($id);
		break;
	}
	$output = $id;
}


/**
 * Adds a chat message
 * @return multitype:string number Ambigous <boolean, number>
 */
function emarking_add_chat_message() {
    global $DB;
    
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
    
    return $output;
}

function emarking_get_submission_grade($draft) {
    global $DB;
    
    $draftssql = "
    SELECT d.id
    FROM {emarking_draft} AS d
    INNER JOIN {emarking_submission} AS s ON (d.submissionid = s.id)
    WHERE s.id = :submissionid";
    
    $drafts = $DB->get_records_sql($draftssql, array("submissionid"=>$draft->submissionid));
    $draftids = array();
    foreach($drafts as $d) {
        $draftids[] = $d->id;
    }
    $draftids = implode(",", $draftids);
    
    // Gets the grade for this submission if any
    $gradesql = "SELECT d.id,
    IFNULL(d.grade,nm.grademin) as finalgrade,
    IFNULL(d.timecreated, d.timemodified) as timecreated,
    IFNULL(d.timemodified,d.timecreated) as timemodified,
    IFNULL(d.generalfeedback,'') as feedback,
    d.qualitycontrol,
    nm.name as activityname,
    nm.grademin,
    nm.grade as grademax,
    IFNULL(u.firstname, '') as firstname,
    IFNULL(u.lastname, '') as lastname,
    IFNULL(u.id, 0) as studentid,
    u.email as email,
    c.fullname as coursename,
    c.shortname as courseshort,
    c.id as courseid,
    nm.custommarks,
    nm.regraderestrictdates,
    nm.regradesopendate,
    nm.regradesclosedate,
    nm.markingduedate,
    '$draftids' as drafts
    FROM {emarking_draft} as d
    INNER JOIN {emarking} as nm ON (d.id = ? AND d.emarkingid = nm.id)
    INNER JOIN {emarking_submission} as s ON (s.id = d.submissionid)
    LEFT JOIN {user} as u on (s.student = u.id)
    LEFT JOIN {course} as c on (c.id = nm.course)
    LEFT JOIN {user} as um on (d.teacher = um.id)";
    
    $results = $DB->get_record_sql($gradesql, array($draft->id));
    
    return $results;
}

function emarking_get_values_collaborative() {
    global $DB;
    
    $commentid = required_param('commentid', PARAM_INT);
    $type = optional_param('type',null,PARAM_INT);
    
    $filter = "";
    if($type != null){
    	$filter = "AND cw.type = $type";
    }
    
    $sqlvaluesbuttons = "SELECT cw. id, cw.markerid, cw.type, CONCAT(u.username, ' ', u.lastname) AS markername, FROM_UNIXTIME(cw.createdtime) AS date, cw.text
    FROM {emarking_collaborative_work} AS cw JOIN {user} AS u ON (cw.markerid = u.id)
    WHERE cw.commentid = ? AND cw.status = ? $filter
    ORDER BY cw.createdtime ASC";
    
    $collaborativevalues = $DB->get_records_sql($sqlvaluesbuttons,array($commentid,'1'));
    
    if(!$collaborativevalues || $collaborativevalues == null) {
    	$collaborativevalues = array();
    	emarking_json_resultset($collaborativevalues);
    	break;
    }else{
    	foreach ($collaborativevalues as $obj){
    		$output[]=$obj;
    	}
    }

    return $collaborativevalues;
}

function emarking_get_markers_configuration($context, $emarking) {
    global $DB;
    
    $results = array();
    
    // Get rubric instance
    list($gradingmanager, $gradingmethod, $definition) = emarking_validate_rubric($context);
    
    $results['rubricname'] = $definition->name;
    
    $results['criteria'] = array();
    foreach($definition->rubric_criteria as $criterion) {
        $results['criteria'][] = array('id' => $criterion['id'],'description' => $criterion['description']);
    }
    
    // Generate markers list
    $results['markers'] = array();
    $indices = array();
    
    // Get all users with permission to grade in emarking
    $markers=get_enrolled_users($context, 'mod/emarking:grade');
    
    // Add all users to markers list, we set criterion to 0
    $i=0;
    foreach($markers as $marker) {
        $results['markers'][] = array('id'=>$marker->id, 'fullname'=>$marker->firstname . ' ' . $marker->lastname, 'criteria'=>array());
        $indices[$marker->id] = $i;
        $i++;
    }
    
    // We get previous configuration of criteria for markers and set accordingly
    $markerscriteria = $DB->get_records('emarking_marker_criterion', array('emarking'=>$emarking->id));
    foreach($markerscriteria as $markercriterion) {
        $results['markers'][$indices[$markercriterion->marker]]['criteria'][] = array('id'=>$markercriterion->criterion);
    }
    
    
    // Generate pages list
    $results['pages'] = array();
    $indices = array();
    
    // We create a list of pages according to the total pages configured for emarking
    // All pages are set to criterion 0
    for($i=1; $i<=$emarking->totalpages; $i++) {
        $results['pages'][] = array('page'=>$i, 'criteria'=>array());
    }
    
    // We load previous configuration of page criterion assignments
    $pagescriteria = $DB->get_records('emarking_page_criterion', array('emarking'=>$emarking->id));
    foreach($pagescriteria as $pagecriterion) {
        $results['pages'][($pagecriterion->page-1	)]['criteria'][] = array('id'=>$pagecriterion->criterion);
    }

    return $results;
}

function emarking_get_rubric_submission($submission, $draft, $cm, $readonly, $issupervisor) {
    global $DB, $USER;
    
    $markerscriteria = $DB->get_records('emarking_marker_criterion', array('emarking'=>$submission->emarking));
    $markersassigned = count($markerscriteria) > 0 && !$readonly && !$issupervisor;
    
    $rubricdesc = $DB->get_recordset_sql(
        "SELECT
		d.name AS rubricname,
		a.id AS criterionid,
		a.description,
        a.sortorder,
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
		INNER JOIN {context} AS mc ON (c.id = :coursemodule AND mc.contextlevel = 70 AND c.id = mc.instanceid)
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
            $rubriclevels[$rd->criterionid]->sortorder = $rd->sortorder;
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

    return $results;
}

function emarking_get_chat_history() {
    global $DB;
    
    $room = required_param('room', PARAM_INT);
    $source = required_param('source', PARAM_INT);
    
    $sqlchathistory = " SELECT ec.*,  u.firstname, u.lastname, u.email
			FROM {emarking_chat} as ec
			INNER JOIN {user} as u on u.id=ec.userid
		    WHERE ec.room=:room AND ec.source=:source
		";
    $params = array('room'=>$room, 'source'=>$source);
    $results = $DB->get_records_sql($sqlchathistory, $params);
    
    if(!$results) {
        $results = array();
    }else{
    
    
        foreach ($results as $obj){
            $obj->url=$CFG->wwwroot."/mod/emarking/marking/index.php";
            $output[]=$obj;
        }
    
    }
    
    return $results;
}

function emarking_get_comments_submission($draft, $pageno) {
    global $DB;
    
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
    $params = array('pageno'=>$pageno, 'draft'=>$draft->id);
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
    
    return $results;
}

function emarking_get_previous_comments($submission, $draft) {
    global $DB, $USER;
    
    $results = $DB->get_records_sql(
        "SELECT MIN(T.id) AS id,
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
			SELECT c.id AS id,
			c.rawtext AS text,
			c.textformat AS format,
			1 AS used,
			c.timemodified AS lastused,
			c.markerid,
            c.criterionid,
            d.id AS draftid,
            CASE WHEN c.markerid = :user THEN 1 ELSE 0 END AS owncomment,
            CASE WHEN d.id = :draft THEN c.pageno ELSE 0 END AS page
			FROM mdl_emarking_submission AS es
			INNER JOIN {emarking_draft} AS d ON (es.emarking = :emarking AND d.submissionid = es.id)
			INNER JOIN {emarking_comment} AS c ON (c.draft = d.id)
			WHERE c.textformat IN (1,2) AND LENGTH(rawtext) > 0
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
			WHERE emarkingid = :emarking2) as T
			GROUP BY text
			ORDER BY text"
        , array(
            'user' => $USER->id,
            'draft' => $draft->id,
            'emarking'=>$submission->emarking,
            'emarking2'=>$submission->emarking));
    
    return $results;
}

function emarking_update_comment($submission, $draft, $emarking, $context) {
    global $CFG, $DB;
    
    require_once("$CFG->dirroot/grade/grading/form/rubric/lib.php");
    
    // Required and optional params for emarking
    $userid = required_param('markerid', PARAM_INT);
    $commentid = required_param('cid', PARAM_INT);
    $commentrawtext = required_param('comment', PARAM_RAW_TRIMMED);
    $bonus = optional_param('bonus', -1, PARAM_FLOAT);
    $levelid = optional_param('levelid', 0, PARAM_INT);
    $format = optional_param('format', 2, PARAM_INT);
    $regradeid = optional_param('regradeid', 0, PARAM_INT);
    $regrademarkercomment = optional_param('regrademarkercomment', null, PARAM_RAW_TRIMMED);
    $regradeaccepted = optional_param('regradeaccepted', 0, PARAM_INT);
    
    
    $posx = required_param('posx', PARAM_INT);
    $posy = required_param('posy', PARAM_INT);
    
    /**  Measures the correction window **/
    $winwidth = optional_param('windowswidth', '-1' , PARAM_NUMBER);
    $winheight = optional_param('windowsheight', '-1', PARAM_NUMBER);
    
    
    if(!$comment = $DB->get_record('emarking_comment', array('id'=>$commentid))){
        emarking_json_error("Invalid comment",array("id"=>$commentid));
    }
    
    if($regradeid > 0 && !$regrade = $DB->get_record('emarking_regrade', array('id'=>$regradeid))){
        emarking_json_error("Invalid regrade",array("id"=>$regradeid));
    }
    
    $previousbonus = $comment->bonus;
    $previouslvlid = $comment->levelid;
    $previouscomment = $comment->rawtext;
    
    if($bonus < 0) {
        $bonus = $previousbonus;
    }
    
    if($commentrawtext === 'delphi') {
        $commentrawtext = $previouscomment;
    }
    
    if($previouslvlid > 0 && $levelid <= 0) {
        emarking_json_error("Invalid level id for a rubric id which has a previous level",
            array("id"=>$commentid,"levelid"=>$previouslvlid));
    }
    
    /** transformation pixels screen to percentages **/
    if($winheight != -1 && $winheight != -1){
        $posx = ($posx/$winwidth);
        $posy = ($posy/$winheight);
         
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
    
    if($comment->levelid > 0) {
        if($diff > 0.01 || $previouslvlid <> $levelid || $previouscomment !== $commentrawtext) {
            emarking_set_finalgrade(
                $levelid,
                $commentrawtext,
                $submission,
                $draft,
                $emarking,
                $context,
                null);
        }
    }
    
    if($regradeid > 0) {
        $regrade->markercomment = $regrademarkercomment;
        $regrade->timemodified = time();
        $regrade->accepted = $regradeaccepted;
        $DB->update_record('emarking_regrade', $regrade);
        
    	$remainingregrades = $DB->count_records("emarking_regrade", array("draft"=>$draft->id, "accepted"=>0));
    	
    	if($remainingregrades == 0) {
    		$draft->status = EMARKING_STATUS_REGRADING_RESPONDED;
    		$draft->timemodified = time();
    		$DB->update_record("emarking_draft", $draft);
    	}
    }
    
    $results = emarking_get_submission_grade($draft);
    
    
    $newgrade = $results->finalgrade;
    
    return $newgrade;
}