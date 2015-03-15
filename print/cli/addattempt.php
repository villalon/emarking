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
 * @package local
 * @subpackage galyleo
 * @copyright 2015 Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('CLI_SCRIPT', true);

require_once (dirname ( dirname ( dirname (dirname ( __FILE__ ) ) ) ) . '/config.php');
require_once($CFG->libdir.'/clilib.php');
require_once ($CFG->dirroot . '/mod/quiz/locallib.php');

global $DB;

// now get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false),
												array('h'=>'help'));

if ($unrecognized) {
	$unrecognized = implode("\n  ", $unrecognized);
	cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
	$help =
	"Generates attempts for a specific quiz for all students in the course, and exports a LaTeX
	containing the quiz questions per student.
Options:
-h, --help            Print out this help
Example:
\$sudo -u www-data /usr/bin/php local/galyleo/generateoffline.php
"; //TODO: localize - to be translated later when everything is finished
	echo $help;
	die;
}

cli_heading('Offline quiz generator'); // TODO: localize

$prompt = "\nEnter quiz course module id"; // TODO: localize
$cmid = cli_input($prompt);

if (! $cm = get_coursemodule_from_id ( 'quiz', $cmid )) {
	cli_error ( 'Invalid cm id' );
}

if (! $course = $DB->get_record ( 'course', array (
		'id' => $cm->course 
) )) {
	cli_error ( 'Invalid course in cm' );
}

$query = 'SELECT u.*
			FROM {user_enrolments} ue
			JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
			JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
			JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
			JOIN {user} u ON (ue.userid = u.id)
			GROUP BY u.id';

// Se toman los resultados del query dentro de una variable.
$users = $DB->get_records_sql ( $query, array (
		$course->id 
) );

foreach ( $users as $user ) {
	
	complete_user_login ( $user );
	
	$context = context_module::instance ( $cmid );
	
	// $PAGE->set_context ( $context );
	
	// Get the quiz object
	$quizobj = quiz::create ( $cm->instance, $user->id );
	
	// TODO get to know what usage by activity means
	$quba = question_engine::make_questions_usage_by_activity ( 'mod_quiz', $quizobj->get_context () );
	$quba->set_preferred_behaviour ( $quizobj->get_quiz ()->preferredbehaviour );
	
	// Create the new attempt and initialize the question sessions
	$attemptnumber = 1;
	$lastattempt = null;
	$timenow = time (); // Update time now, in case the server is running really slowly.
	
	$attempt = quiz_create_attempt ( $quizobj, $attemptnumber, $lastattempt, $timenow, false, $user->id );
	$attempt = quiz_start_new_attempt ( $quizobj, $quba, $attempt, $attemptnumber, $timenow );
	
	$transaction = $DB->start_delegated_transaction ();
	$attempt = quiz_attempt_save_started ( $quizobj, $quba, $attempt );
	$transaction->allow_commit ();
	
	$attemptobj = quiz_attempt::create ( $attempt->id );
	
	$slots = $attemptobj->get_slots ();
	foreach ( $slots as $slot ) {
		$qattempt = $attemptobj->get_question_attempt ( $slot );
		$question = $qattempt->get_question ();
		var_dump ( $question->questiontext );
		echo "---------------------------------\n";
		foreach ( $question->answers as $qanswer ) {
			var_dump ( $qanswer->answer );
			var_dump ( $qanswer->fraction );
			var_dump ( $qanswer->feedback );
		echo "---------------------------------\n";
		}
	}
}