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
