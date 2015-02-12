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
 * @copyright 2012 Jorge Villalon <jorge.villalon@uai.cl>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * 
 * @param unknown $submission
 */
function emarking_multi_publish_grade($submission) {
	global $CFG, $DB, $USER;

	require_once ($CFG->libdir . '/gradelib.php');

	if ($submission->status <= EMARKING_STATUS_ABSENT)
		return;

	// Copy final grade to gradebook
	$grade_item = grade_item::fetch ( array (
			'itemmodule' => 'emarking',
			'iteminstance' => $submission->emarking
	) );

	$feedback = $submission->generalfeedback ? $submission->generalfeedback : '';

	$grade_item->update_final_grade ( $submission->student, $submission->grade, 'editgrade', $feedback, FORMAT_HTML, $USER->id );

	if ($submission->status <= EMARKING_STATUS_RESPONDED) {
		$submission->status = EMARKING_STATUS_RESPONDED;
	}

	$submission->timemodified = time ();
	$DB->update_record ( 'emarking_draft', $submission );

	$realsubmission = $DB->get_record ( "emarking_submission", array (
			"id" => $submission->id
	) );
	$realsubmission->status = $submission->status;
	$realsubmission->timemodified = $submission->timemodified;
	$realsubmission->generalfeedback = $submission->generalfeedback;
	$realsubmission->grade = $submission->grade;
	$realsubmission->teacher = $submission->teacher;
	$DB->update_record ( 'emarking_submission', $realsubmission );
}

/**
 *
 * @param unknown $submission
 */
function emarking_publish_grade($submission) {
	global $CFG, $DB, $USER;

	require_once ($CFG->libdir . '/gradelib.php');

	if ($submission->status <= EMARKING_STATUS_ABSENT)
		return;

	// Copy final grade to gradebook
	$grade_item = grade_item::fetch ( array (
			'itemmodule' => 'emarking',
			'iteminstance' => $submission->emarkingid
	) );

	$feedback = $submission->generalfeedback ? $submission->generalfeedback : '';

	$grade_item->update_final_grade ( $submission->student, $submission->grade, 'editgrade', $feedback, FORMAT_HTML, $USER->id );

	if ($submission->status <= EMARKING_STATUS_RESPONDED) {
		$submission->status = EMARKING_STATUS_RESPONDED;
	}

	$submission->timemodified = time ();
	$DB->update_record ( 'emarking_draft', $submission );

	$realsubmission = $DB->get_record ( "emarking_submission", array (
			"id" => $submission->submissionid
	) );
	$realsubmission->status = $submission->status;
	$realsubmission->timemodified = $submission->timemodified;
	$realsubmission->generalfeedback = $submission->generalfeedback;
	$realsubmission->grade = $submission->grade;
	$realsubmission->teacher = $submission->teacher;
	$DB->update_record ( 'emarking_submission', $realsubmission );
}

function emarking_publish_all_grades($emarking) {
	global $DB, $USER, $CFG;

	$studentsubmissions = $DB->get_records ( "emarking_submission", array (
			'emarking' => $emarking->id
	) );

	foreach ( $studentsubmissions as $submission ) {
		if ($submission->status >= EMARKING_STATUS_RESPONDED)
			emarking_publish_grade ( $submission );
	}

	return true;
}
