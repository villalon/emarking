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

/** General feedback to include in the marking **/
$generalfeedback = required_param('feedback', PARAM_RAW_TRIMMED);

// Firstly create the response pdf
if (emarking_create_response_pdf($submission,$user,$context, $cm->id)) {
	
	// If the pdf was created successfully then update the final grade and feedback
	list($finalgrade, $previouslvlid, $previouscomment) = emarking_set_finalgrade(
		$submission->student, 
		0, 
		null, 
		$submission, 
		$emarking, 
		$context, 
		$generalfeedback, 
		false,
		$cm->id);
	

	
	// It is only publish if there just one draft
	if($DB->count_records("emarking_draft", array("emarkingid"=>$submission->emarkingid, "submissionid"=>$submission->submissionid))==1){
		emarking_publish_grade($submission);
	}

	$nextsubmission = emarking_get_next_submission($emarking, $submission);
	
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