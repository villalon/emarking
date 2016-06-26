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
 * This file keeps track of upgrades to the emarking module
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations.
 * The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do. The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2013-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once ($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('locallib.php');

// Obtener curso desde el URL
$courseid = required_param('course', PARAM_INT);

// Validar que el curso exista
if(!$course = $DB->get_record('course', array('id'=>$courseid))) {
    print_error(get_string('invalidcourse', 'mod_emarking'));
}

// Rows per page
$perpage = 10;

// Sorting para for table
$tsort = optional_param('tsort', 'timemodified ASC', PARAM_ALPHA);

// Page to show (when paginating)
$page = optional_param ( 'page', 0, PARAM_INT );

// Id of course to remove from notifications
$deleteid = optional_param('delete', 0, PARAM_INT);

// If deletion is confirmed
$confirmdelete = optional_param('confirm', false, PARAM_BOOL);

// We use the system context
$context = context_course::instance($course->id);

// The page url
$url = new moodle_url('/mod/emarking/orm/quizzes.php', array('course'=>$courseid));

// We require login
require_login($course);

// We require the user to have site configuration permission
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_url($url);
$PAGE->set_course($course);
$PAGE->set_title(get_string('quizprinting', 'mod_emarking'));
$PAGE->set_pagelayout('incourse');

// The page header and heading
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('quizprinting', 'mod_emarking'));

// We create a flexible table (sortable)
$showpages = new flexible_table ('quizzes');

// Table headers
$headers = array ();
$headers[] = get_string ('category');
$headers[] = get_string ('course');
$headers[] = get_string ('name');
$headers[] = get_string ('attempts', 'mod_quiz');
$headers[] = get_string ('modified', 'question');
$headers[] = get_string ( 'actions');

// Define flexible table (can be sorted in different ways)
$showpages->define_headers($headers);

// Table columns
$columns = array();
$columns[] = 'category';
$columns[] = 'course';
$columns[] = 'name';
$columns[] = 'attempts';
$columns[] = 'timemodified';
$columns[] = 'actions';
$showpages->define_columns($columns);

// Define a base url
$showpages->define_baseurl($url);

// The sortable and non sortable columns
$showpages->no_sorting('actions');
$showpages->sortable ( true, 'timemodified', SORT_ASC);
$showpages->pageable ( true );

// We get the count for the data
$numquizzes = $DB->count_records('quiz', array('course'=>$course->id));

// Set the page size
$showpages->pagesize ( $perpage, $numquizzes);

// Setup the table
$showpages->setup ();

// If table is sorted set the query string
if($showpages->get_sql_sort()) {
    $tsort = $showpages->get_sql_sort();
} else {
    $tsort = 'timemodified ASC';
}

$quizmodule = $DB->get_record('modules', array('name'=>'quiz'));

$enroledsql = emarking_get_count_enroled_students_sql($course->id);

$quizzessql = '
    SELECT QS.*, ES.enroledstudents
    FROM (
    SELECT q.id,
           c.fullname as course,
           c.id as courseid,
           q.name,
           q.timemodified,
           cc.name as category,
           cc.id as categoryid,
           cm.id as cmid,
           count(distinct userid) as attempts
    FROM {quiz} AS q 
    INNER JOIN {course} AS c ON (q.course = ? AND q.course = c.id)
    INNER JOIN {course_categories} AS cc ON (c.category = cc.id)
    INNER JOIN {course_modules} AS cm ON (cm.instance = q.id AND cm.module = ?)
    LEFT JOIN {quiz_attempts} AS qa ON (qa.quiz = q.id)
    GROUP BY q.id) AS QS
    INNER JOIN (' . $enroledsql .') AS ES ON (QS.courseid = ES.id)
    ORDER BY ' . $tsort;

// Get the notifications sorted according to table
$quizzes = $DB->get_recordset_sql($quizzessql, array($course->id, $quizmodule->id, $course->id), $page * $perpage, $perpage );

// Add each row to the table
foreach($quizzes as $quiz) {
    $data = array();
    $data[] = $OUTPUT->action_link(new moodle_url('/course/index.php', array('categoryid'=>$quiz->categoryid)), $quiz->category);
    $data[] = $OUTPUT->action_link(new moodle_url('/course/view.php', array('id'=>$quiz->courseid)), $quiz->course);
    $data[] = $OUTPUT->action_link(new moodle_url('/mod/quiz/view.php', array('id'=>$quiz->cmid)), $quiz->name);
    $data[] = $OUTPUT->action_link(new moodle_url('/mod/quiz/report.php', array('id'=>$quiz->cmid, 'mode'=>'overview')), $quiz->attempts . ' / ' . $quiz->enroledstudents);
    $data[] = date ( "d M H:i", $quiz->timemodified );
    
    $actions = '';
    // Descargar PDF del quiz
    if($quiz->attempts > 0) {
        // Preview
        $actions .= $OUTPUT->action_link(new moodle_url('/mod/emarking/orm/printquiz.php', array('cmid'=>$quiz->cmid, 'debug'=>true)),
            null,  
            new popup_action('click', new moodle_url('/mod/emarking/orm/printquiz.php', array('cmid'=>$quiz->cmid, 'debug'=>true)), 'emarking', array (
            'menubar' => 'no',
            'titlebar' => 'no',
            'status' => 'no',
            'toolbar' => 'no'
        )),
            null,
            new pix_icon('t/preview', get_string('previewquiz', 'mod_emarking'), null, array('style'=>'margin-left:5px;')));
        
        // Descargar PDF de solamente respuestas
        $actions .= $OUTPUT->action_link(new moodle_url('/mod/emarking/orm/printquiz.php', array('cmid'=>$quiz->cmid, 'answers'=>true)),
            null,  
            new popup_action('click', new moodle_url('/mod/emarking/orm/printquiz.php', array('cmid'=>$quiz->cmid, 'answers'=>true)), 'emarking', array (
            'menubar' => 'no',
            'titlebar' => 'no',
            'status' => 'no',
            'toolbar' => 'no'
        )),
            null,
            new pix_icon('t/check', get_string('answersheetsquiz', 'mod_emarking'), null, array('style'=>'margin-left:5px;')));
        
        // Descargar PDF
        $actions .= $OUTPUT->action_link(new moodle_url('/mod/emarking/orm/printquiz.php', array('cmid'=>$quiz->cmid)),
            null,  
            new popup_action('click', new moodle_url('/mod/emarking/orm/printquiz.php', array('cmid'=>$quiz->cmid)), 'emarking', array (
            'menubar' => 'no',
            'titlebar' => 'no',
            'status' => 'no',
            'toolbar' => 'no'
        )),
            null,
            new pix_icon('t/down', get_string('printquiz', 'mod_emarking'), null, array('style'=>'margin-left:5px;')));
        
        // Subir respuestas
        $actions .= $OUTPUT->action_icon(new moodle_url('/mod/emarking/orm/processomr.php', array('cmid'=>$quiz->cmid, 'upload'=>true)), 
            new pix_icon('t/up', get_string('uploadanswers', 'mod_emarking'), null, array('style'=>'margin-left:5px;')));
    
    }
    
    // Crear intentos
    $actions .= $OUTPUT->action_icon(new moodle_url('/mod/emarking/orm/processomr.php', array('cmid'=>$quiz->cmid, 'create'=>true)), 
          new pix_icon('t/cohort', get_string('createattempts', 'mod_emarking'), null, array('style'=>'margin-left:5px;'))
        , new confirm_action(get_string('confirmcreateattempts', 'mod_emarking')));
    
    $data[] = $actions;
    $showpages->add_data($data);
}

// Print the table
$showpages->print_html();

echo $OUTPUT->footer();
