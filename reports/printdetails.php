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
 * @copyright 2015 Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/reports/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/reports/forms/dates_form.php');
global $DB, $USER;
$categoryid = required_param('category', PARAM_INT);
$period = optional_param('period', 0, PARAM_INT);
$startdate = optional_param('start', time() - (3600 * 24 * 365), PARAM_INT);
$enddate = optional_param('end', time(), PARAM_INT);
if (! $category = $DB->get_record('course_categories', array(
    'id' => $categoryid))) {
    print_error(get_string('invalidcategoryid', 'mod_emarking'));
}
$context = context_coursecat::instance($categoryid);
$start = new DateTime();
$start->setTimestamp($startdate);
$end = new DateTime();
$end->setTimestamp($enddate);
$step = 'month';
$diff = $end->diff($start);
$months = $diff->y * 12 + $diff->m;
$url = new moodle_url('/mod/emarking/reports/printdetails.php', array(
    'category' => $categoryid));
$ordersurl = new moodle_url('/mod/emarking/print/printorders.php', array(
    'category' => $categoryid,
    'status' => 1));
$categoryurl = new moodle_url('/course/index.php', array(
    'categoryid' => $categoryid));
if (! has_capability('mod/emarking:printordersview', $context)) {
    print_error('Not allowed!');
}
$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add($category->name, $categoryurl);
$PAGE->navbar->add(get_string('printorders', 'mod_emarking'), $ordersurl);
$PAGE->navbar->add(get_string('statistics', 'mod_emarking'));
$PAGE->set_context($context);
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
require_login();
$pagenumber = optional_param('pag', 1, PARAM_INT);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('statisticstotals', 'mod_emarking'));
echo $OUTPUT->tabtree(emarking_printoders_tabs($category), "printdetails");
$form = new emarking_dates_form(null,
        array(
            'period' => $period,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'category' => $categoryid));
$form->display();
if ($form->get_data()) {
    if ($form->get_data()->period == 50) {
        $startdate = $form->get_data()->startdate;
        $enddate = $form->get_data()->enddate;
    }
    // TODO: More periods.
}
$filter = "WHERE (cc.path like '%/$categoryid/%' OR cc.path like '%/$categoryid')";
$datefilter = " AND e.examdate >= $startdate AND e.examdate < $enddate";
$sqlstats = "
    SELECT EXAMS.id AS courseid,
	category,
    coursename,
    editingteachernames,
    totalpages,
    exams,
    pagesperexam,
    categorypath,
    editingteacherids
FROM (
SELECT
	c.id,
    c.fullname AS coursename,
	COUNT(DISTINCT e.id) AS exams,
    SUM((e.totalpages + e.extrasheets) * (e.totalstudents + e.extraexams)) AS totalpages,
    AVG((e.totalpages + e.extrasheets) * (e.totalstudents + e.extraexams)) AS pagesperexam,
    cc.name AS category,
    cc.path AS categorypath
FROM {emarking_exams} AS e
INNER JOIN {course} AS c ON (e.course = c.id)
INNER JOIN {user} AS u ON (e.requestedby = u.id)
INNER JOIN {course_categories} AS cc ON (c.category = cc.id)
$filter
$datefilter
GROUP BY c.id) AS EXAMS
INNER JOIN (
SELECT id,
	fullname,
    SUM(editingteachers) AS editingteachers,
    SUM(teachers) AS teachers,
    SUM(students) AS students,
    MAX(editingteachernames) AS editingteachernames,
    MAX(teachernames) AS teachernames,
    MAX(studentnames) AS studentnames,
    MAX(editingteacherids) AS editingteacherids,
    MAX(teacherids) AS teacherids,
    MAX(studentids) AS studentids
FROM (
SELECT c.id,
	c.fullname,
    ro.shortname,
    CASE WHEN ro.shortname = 'editingteacher' THEN COUNT(DISTINCT uen.id) ELSE 0 END AS editingteachers,
    CASE WHEN ro.shortname = 'editingteacher'
        THEN GROUP_CONCAT(uen.firstname, ' ', uen.lastname SEPARATOR '#') ELSE '' END AS editingteachernames,
    CASE WHEN ro.shortname = 'editingteacher' THEN GROUP_CONCAT(uen.id)  ELSE '' END AS editingteacherids,
    CASE WHEN ro.shortname = 'teacher' THEN COUNT(DISTINCT uen.id) ELSE 0 END AS teachers,
    CASE WHEN ro.shortname = 'teacher'
        THEN GROUP_CONCAT(uen.firstname, ' ', uen.lastname SEPARATOR '#') ELSE '' END AS teachernames,
    CASE WHEN ro.shortname = 'teacher' THEN GROUP_CONCAT(uen.id)  ELSE '' END AS teacherids,
    CASE WHEN ro.shortname = 'student' THEN COUNT(DISTINCT uen.id) ELSE 0 END AS students,
    CASE WHEN ro.shortname = 'student'
        THEN GROUP_CONCAT(uen.firstname, ' ', uen.lastname SEPARATOR '#') ELSE '' END AS studentnames,
    CASE WHEN ro.shortname = 'student' THEN GROUP_CONCAT(uen.id)  ELSE '' END AS studentids
FROM {course} c
INNER JOIN {course_categories} cc ON (c.category = cc.id)
INNER JOIN {context} ctx ON (ctx.contextlevel = 50 AND ctx.instanceid = c.id)
INNER JOIN {enrol} en ON (en.courseid = c.id)
INNER JOIN {user_enrolments} ue ON (ue.enrolid = en.id)
INNER JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = ue.userid)
INNER JOIN {role} ro ON (ra.roleid = ro.id)
INNER JOIN {user} uen ON (ue.userid = uen.id)
$filter
GROUP BY c.id, ro.id) AS COURSE
GROUP BY COURSE.id) AS TEACHERS
ON (EXAMS.id = TEACHERS.id)";
$stats = $DB->get_recordset_sql($sqlstats, array(
    $categoryid));
$statstable = new html_table();
$statstable->head = array(
    ucfirst(get_string('course')),
    ucfirst(get_string('teachers')),
    get_string('exams', 'mod_emarking'),
    ucfirst(get_string('pages', 'mod_emarking')),
    get_string('pagesperexam', 'mod_emarking'));
$statstable->attributes ['style'] = 'margin-left: auto; margin-right: auto;';
$data = array();
foreach ($stats as $st) {
    $teachers = explode('#', $st->editingteachernames);
    $teacherids = explode(',', $st->editingteacherids);
    $teacherlinks = '<ul>';
    for ($i = 0; $i < count($teachers); $i ++) {
        $teacherurl = new moodle_url("/user/profile.php", array(
            "id" => $teacherids [$i]));
        $teacherlinks .= '<li>' . $OUTPUT->action_link($teacherurl, $teachers [$i]) . '</li>';
    }
    $teacherlinks .= '</ul>';
    $statstable->data [] = array(
        $st->coursename,
        $teacherlinks,
        $st->exams,
        $st->totalpages,
        $st->pagesperexam);
}
echo html_writer::table($statstable);
echo $OUTPUT->footer();