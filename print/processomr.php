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
 * This page deals with processing responses during an attempt at a quiz.
 *
 * People will normally arrive here from a form submission on attempt.php or
 * summary.php, and once the responses are processed, they will be redirected to
 * attempt.php or summary.php.
 *
 * This code used to be near the top of attempt.php, if you are looking for CVS history.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2015 Jorge Villalon
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->dirroot . '/mod/quiz/locallib.php');

/*
 * 
 * 
 * <div>
 * <input type="submit" value="Submit all and finish" id="single_button551889227d9063">
 * <input type="hidden" name="attempt" value="28">
 * <input type="hidden" name="finishattempt" value="1">
 * <input type="hidden" name="timeup" value="0">
 * <input type="hidden" name="slots" value="">
 * <input type="hidden" name="sesskey" value="tyFu0CKDOj">
 * </div>
 * 
 * POST data from attempt 32 
 * array (size=23)
  'q44:1_:flagged' => string '0' (length=1)
  'q44:1_:sequencecheck' => string '1' (length=1)
  'q44:1_answer' => string '0' (length=1)
  'q44:2_:flagged' => string '0' (length=1)
  'q44:2_:sequencecheck' => string '1' (length=1)
  'q44:2_answer' => string '1' (length=1)
  'q44:3_:flagged' => string '0' (length=1)
  'q44:3_:sequencecheck' => string '1' (length=1)
  'q44:3_answer' => string '2' (length=1)
  'q44:4_:flagged' => string '0' (length=1)
  'q44:4_:sequencecheck' => string '1' (length=1)
  'q44:4_answer' => string '3' (length=1)
  'q44:5_:flagged' => string '0' (length=1)
  'q44:5_:sequencecheck' => string '1' (length=1)
  'q44:5_answer' => string '0' (length=1)
  'next' => string 'Next' (length=4)
  'attempt' => string '32' (length=2)
  'thispage' => string '0' (length=1)
  'nextpage' => string '-1' (length=2)
  'timeup' => string '0' (length=1)
  'sesskey' => string 'tyFu0CKDOj' (length=10)
  'scrollpos' => string '' (length=0)
  'slots' => string '1,2,3,4,5' (length=9)
 */
// Remember the current time as the time any responses were submitted
// (so as to make sure students don't get penalized for slow processing on this page).
$timenow = time();

// Get submitted parameters.
$attemptid = required_param('attempt', PARAM_INT);


$transaction = $DB->start_delegated_transaction();
$attemptobj = quiz_attempt::create($attemptid);

$quizobj = $attemptobj->get_quizobj();
$quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
$quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

$answer = array();
$slotsstring = array();
$simpleanswer = array();
foreach($attemptobj->get_slots('all') as $k => $slot) {
    $qa = $attemptobj->get_question_attempt($slot);
    $correct = $qa->get_correct_response();
    $sequencecheckcount = $qa->get_sequence_check_count();
    $answer['q'.$attemptobj->get_uniqueid().':'.$slot.'_:flagged'] = '0';
    $answer['q'.$attemptobj->get_uniqueid().':'.$slot.'_:sequencecheck'] = "$sequencecheckcount";
    $answer['q'.$attemptobj->get_uniqueid().':'.$slot.'_answer'] = $correct['answer'];
    $slotsstring[] = $slot;
    $simpleanswer[$slot] = $correct;
    
    $data = $qa->get_submitted_data($answer);
    $data2 = $qa->get_database_id();
    $data3 = $qa->get_qt_field_name('answer');
    $data4 = $quba->prepare_simulated_post_data($simpleanswer);
    var_dump($qa->get_field_prefix());
    var_dump($data);echo"<hr>";
    var_dump($data2);echo"<hr>";
    var_dump($data3);echo"<hr>";
    var_dump($data4);echo"<hr>";
}
$answer['next'] = 'Next';
$answer['attempt'] = "$attemptid";
$answer['thispage'] = '0';
$answer['nextpage'] = '-1';
$answer['timeup'] = '0';
$answer['sesskey'] = "$USER->sesskey";
$answer['scrollpos'] = '';
$answer['slots'] = implode(',', $slotsstring);

var_dump($simpleanswer);
// If the attempt is already closed, send them to the review page.
if ($attemptobj->is_finished()) {
    throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'attemptalreadyclosed', null, $attemptobj->review_url());
}

// Don't log - we will end with a redirect to a page that is logged.
try {
    $attemptobj->process_submitted_actions($timenow, false, $simpleanswer);
} catch (question_out_of_sequence_exception $e) {
    print_error('submissionoutofsequencefriendlymessage', 'question', $attemptobj->attempt_url(null, $thispage));
} catch (Exception $e) {
    // This sucks, if we display our own custom error message, there is no way
    // to display the original stack trace.
    $debuginfo = '';
    if (! empty($e->debuginfo)) {
        $debuginfo = $e->debuginfo;
    }
    print_error('errorprocessingresponses', 'question', $attemptobj->attempt_url(null, $thispage), $e->getMessage(), $debuginfo);
}

// $attemptobj->process_finish($timenow, ! false);

// Send the user to the review page.
$transaction->allow_commit();

echo "Done";