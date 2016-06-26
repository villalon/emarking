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
 * This page inserts data from an array of choices which correspond
 * to the scanned answers using OMR
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2015 Jorge Villalon
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('NO_OUTPUT_BUFFERING', true);

require_once (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once ($CFG->dirroot . '/mod/quiz/locallib.php');
require_once ($CFG->dirroot . '/mod/emarking/print/locallib.php');
require_once($CFG->libdir . '/filelib.php');
require_once ('forms/upload_answers_form.php');
require_once ('locallib.php');

// Get submitted parameters.
$cmid = required_param('cmid', PARAM_INT);
$finish = optional_param('finish', false, PARAM_BOOL);
$create = optional_param('create', false, PARAM_BOOL);
$upload = optional_param('upload', false, PARAM_BOOL);
$csvfile = optional_param('csvfile', 0, PARAM_INT);

// Validate the course module as a quiz
if (! $cm = get_coursemodule_from_id('quiz', $cmid)) {
    print_error('Invalid cm id');
}

// Validate the course to which corresponds
if (! $course = $DB->get_record('course', array(
    'id' => $cm->course
))) {
    print_error('Invalid course in cm');
}

if(!$quiz = $DB->get_record('quiz', array('id'=>$cm->instance))) {
    print_error('Invalid quiz');
}

// Create the context within the course
$context = context_module::instance($cm->id);

// Validate user is logged in and is not guest
require_login($course->id);
if (isguestuser()) {
    die();
}

$url = new moodle_url('/mod/emarking/orm/processomr.php', array(
    'cmid' => $cm->id,
    'finish' => $finish,
    'create' => $create,
    'csvfile' => $csvfile,
));

$urlquizzes = new moodle_url('/mod/emarking/orm/quizzes.php', array('course'=>$cm->course));

$title = $create ? get_string('createattempts', 'mod_emarking') : get_string('uploadanswers', 'mod_emarking');

$PAGE->set_pagelayout('incourse');
$PAGE->set_popup_notification_allowed(false);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

if($upload) {
$answersform = new emarking_upload_answers_form(null, array('cmid'=>$cm->id));

if($answersform->get_data()) {
    // Save uploaded file in Moodle filesystem and check
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_emarking', 'upload', $quiz->id);
    $file = $answersform->save_stored_file('answersfile', $context->id, 'mod_emarking', 'upload', $quiz->id, '/', emarking_clean_filename($answersform->get_new_filename('answersfile')));
    
    // Validate that file was correctly uploaded
    if(!$file) {
        print_error("Could not upload file");
    }
    
    // Check that the file is a zip
    if($file->get_mimetype() !== 'text/csv') {
        echo $OUTPUT->error_text(get_string('fileisnotcsv', 'mod_emarking'));
        echo $OUTPUT->continue_button($urlquizzes);
        echo $OUTPUT->footer();
        die();
    }

    $iid = csv_import_reader::get_new_iid('emarking');
    $reader = new csv_import_reader($iid, 'emarking');
    $reader->load_csv_content($file->get_content(), 'utf8', $answersform->get_data()->delimiter_name);
    
    if(count($reader->get_columns()) < 3 || $reader->get_columns()[0] !== 'userid' || $reader->get_columns()[1] !== 'attemptid') {
        print_error('Invalid CSV file, it requires at least 3 columns. Starting with userid and attemptid.');
    }
    
    $validcolumns = 0;
    $columns = array();
    $columns[0] = 'userid';
    $columns[1] = 'attemptid';
    for($i = 2; $i < count($reader->get_columns()); $i++) {
        if(preg_match('/^Question (\d\d\d)$/', $reader->get_columns()[$i], $matches)) {
            $validcolumns++;
            $columns[$i] = $matches[1];
        } else {
            $columns[$i] = null;
        }
    }
    
    if($validcolumns < 1) {
        print_error('No valid columns');
    }
    
    $reader->init();
    while($line = $reader->next()) {
        $userid = $line[0];
        $attemptid = $line[1];
        $choices = array();
        for($i=2;$i<count($columns);$i++) {
            if($columns[$i]) {
                $code = ord($line[$i]);
                if($code >= 65)
                    $code -= 65;
                else if($code == 0)
                    $code = -1;
                $choices[] = $code;
            }
        }
        
        if(!$user = $DB->get_record('user', array('id' => $userid))) {
            echo "Invalid user $userid<br>";
            continue;
        }
        
        emarking_insert_user_answers($choices, $user, $attemptid);
    }
    
    echo $OUTPUT->notification(get_string('csvimportsuccessfull', 'mod_emarking'), 'notifysuccess');
    echo $OUTPUT->single_button( new moodle_url('/mod/emarking/orm/processomr.php', array('cmid' => $cm->id,'finish' => true)), get_string('finish', 'mod_emarking'));
} else {
    $answersform->display();
}
echo $OUTPUT->footer();
die();
}


// Get the users enrolled
$users = emarking_get_enroled_students($course->id);

$pbar = new progress_bar();
$pbar->create();

if ($create) {
  quiz_delete_all_attempts($quiz);
}

$cur = 1;
$total = count($users);
// Insert answers or finish the attempt for each student
foreach ($users as $user) {
    
    $pbar->update($cur, $total, get_string('processing', 'mod_emarking') . $user->lastname . $user->firstname);
    flush();
    
    // Get the quiz instance for the specific student
    $quizobj = quiz::create($cm->instance, $user->id);
    
    // Get all the attempts
    $attempts = quiz_get_user_attempts($quizobj->get_quizid(), $user->id, 'all');
    
    if ($create) {
        emarking_add_user_attempt($cm, $user);
    } else {
        // For each attempt insert the answers or finish
        foreach ($attempts as $attempt) {
            
            if ($finish) {
                emarking_finish_user_attempt($attempt->id);
            }
        }
    }
    $cur++;
}

$pbar->update_full(100, get_string('finished', 'mod_emarking'));
echo $OUTPUT->single_button(new moodle_url('/mod/emarking/orm/quizzes.php', array('course'=>$cm->course)), get_string('continue'));
echo $OUTPUT->footer();
