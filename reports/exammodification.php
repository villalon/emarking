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
 * Prints a particular instance of evapares
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Mihail Pozarski <mipozarski@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/reports/forms/exammodification_form.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/reports/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/print/locallib.php');
global $CFG, $DB, $OUTPUT;
// Exam id.
$examid = required_param('exam', PARAM_INT);
// Category id.
$categoryid = required_param('category', PARAM_INT);
// Form action.
$action = optional_param("action", "view", PARAM_TEXT);
// Status icon.
$statusicon = optional_param('status', 1, PARAM_INT);
// User must be logged in.
require_login();
if (isguestuser()) {
    die();
}
// Validate category.
if (! $category = $DB->get_record('course_categories', array(
    'id' => $categoryid))) {
    print_error(get_string('invalidcategoryid', 'mod_emarking'));
}
if (! $exam = $DB->get_record('emarking_exams', array(
    'id' => $examid))) {
    print_error(get_string('invalidcategoryid', 'mod_emarking'));
}
// We are in the category context.
$context = context_coursecat::instance($categoryid);
// And have viewcostreport capability.
if (! has_capability('mod/emarking:viewcostreport', $context)) {
    // TODO: Log invalid access to printreport.
    print_error('Not allowed!');
}
// This page url.
$url = new moodle_url('/mod/emarking/reports/costconfig.php', array(
    'category' => $categoryid));
// Url that lead you to the category page.
$categoryurl = new moodle_url('/course/index.php', array(
    'categoryid' => $categoryid));
$pagetitle = get_string('costreport', 'mod_emarking');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add($category->name, $categoryurl);
$PAGE->navbar->add(get_string('printorders', 'mod_emarking'), $url);
$PAGE->navbar->add($pagetitle);
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
// Add the emarking cost form for categories.
$addform = new emarking_exammodification_form();
$alliterations = array();
// If the form is cancelled redirects you to the report center.
if ($addform->is_cancelled()) {
    $backtocourse = new moodle_url('/mod/emarking/print/printorders.php', array(
        'category' => $categoryid));
    redirect($backtocourse);
} else if ($datas = $addform->get_data()) {
    // Saves the form info in to variables.
    $examid = $datas->exam;
    $cost = $datas->cost;
    // Parameters for getting the category cost if it exist.
    $categoryparams = array(
        $examid);
    $parametrosupdate = array(
        $cost,
        $examid);
    $sqlupdate = "UPDATE mdl_emarking_exams
		 		SET printingcost = ?
				WHERE id = ?";
    $DB->execute($sqlupdate, $parametrosupdate);
    // Redirect to the table with all the category costs.
    redirect(
            new moodle_url("/mod/emarking/print/printorders.php",
                    array(
                        "category" => $datas->category,
                        "status" => $datas->status)));
}
$examstable = new html_table();
$examstable->head = array(
    get_string('date'),
    get_string('exam', 'mod_emarking'),
    get_string('course'),
    get_string('details', 'mod_emarking'),
    get_string('requestedby', 'mod_emarking'),
    get_string('cost', 'mod_emarking'),
    $statusicon == 1 ? get_string('sent', "mod_emarking") : get_string('examdateprinted', 'mod_emarking'),
    ucfirst(get_string('pages', 'mod_emarking')));
$examstable->size = array(
    '15%',
    '15%',
    '15%',
    '10%',
    '10%',
    '10%',
    '7%',
    '7%',
    '10%');
$examstable->align = array(
    'left',
    'center',
    'center',
    'center',
    'center',
    'center',
    'center',
    'center',
    'center');
// Parameters for SQL calls.
if ($statusicon == 1) {
    $statuses = array(
        EMARKING_EXAM_UPLOADED);
} else {
    $statuses = array(
        EMARKING_EXAM_SENT_TO_PRINT,
        EMARKING_EXAM_PRINTED);
}
$sqlparams = array(
    $examid);
$sql = "SELECT e.*,
c.id as courseid,
c.fullname as coursefullname,
u.id as userid,
CONCAT(u.firstname, ' ', u.lastname) as userfullname,
cc.name as category,
e.printingcost as cost
FROM {emarking_exams} e
INNER JOIN {course} c ON (e.course = c.id)
INNER JOIN {user} u ON (e.requestedby = u.id)
INNER JOIN {course_categories} cc ON (cc.id = c.category)
WHERE e.id = ?";
// Getting all print orders.
$exams = $DB->get_records_sql($sql, $sqlparams); // Status = 1 means still not downloaded.
$currentdate = time();
$current = 0;
foreach ($exams as $exam) {
    // Url for the course.
    $urlcourse = new moodle_url('/course/view.php', array(
        'id' => $exam->course));
    // Url for the user profile of the person who requested the exam.
    $urlprofile = new moodle_url('/user/profile.php', array(
        'id' => $exam->userid));
    // Calculate the total pages and pages to print for this exam.
    $pagestoprint = emarking_exam_total_pages_to_print($exam);
    $actions = html_writer::start_tag("div", array(
        "class" => "printactions"));
    $actions .= html_writer::end_tag("div");
    // Calculating date differences to identify exams that are late, are for today and so on.
    if (date("d/m/y", $exam->examdate) === date("d/m/y", $currentdate)) {
        $examstable->rowclasses [$current] = 'examtoday';
    } else if ($currentdate < $exam->examdate) {
        $examstable->rowclasses [$current] = 'examisok';
    } else {
        $examstable->rowclasses [$current] = 'examislate';
    }
    $notification = $exam->notified ? $OUTPUT->pix_icon('t/approve', get_string('printnotificationsent', 'mod_emarking')) :
            '<a href="' . $CFG->wwwroot . '/mod/emarking/print/sendprintnotification.php?id=' . $exam->id . '">' .
             $OUTPUT->pix_icon('i/email', get_string('printsendnotification', 'mod_emarking')) . '</a>';
    $enrolments = html_writer::start_tag("div", array(
        "class" => "printdetails"));
    $enrolments .= emarking_enrolments_div($exam);
    $enrolments .= html_writer::end_tag("div");
    if ($statusicon == 1) {
        $examstable->data [] = array(
            date("l jS F g:ia", $exam->examdate),
            $exam->name,
            $OUTPUT->action_link($urlcourse, $exam->coursefullname),
            $exam->category . '<br/>' . $enrolments,
            $OUTPUT->action_link($urlprofile, $exam->userfullname),
            '$' . number_format($exam->cost),
            emarking_time_ago($exam->timecreated),
            $pagestoprint);
    } else {
        $examstable->data [] = array(
            date("l jS F g:ia", $exam->examdate),
            $exam->name,
            $OUTPUT->action_link($urlcourse, $exam->coursefullname),
            $exam->category . '<br/>' . $enrolments,
            $OUTPUT->action_link($urlprofile, $exam->userfullname),
            '$' . $exam->cost,
            emarking_time_ago($exam->printdate),
            $pagestoprint);
    }
    $current ++;
}
// If there is no data or is it not cancelled show the header, the tabs and the form.
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle . ' ' . $category->name);
echo html_writer::table($examstable); // Print the table.
// Display the form.
$addform->display();
echo $OUTPUT->footer();