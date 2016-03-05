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
global $DB, $USER;
$categoryid = required_param('category', PARAM_INT);
$startdate = optional_param('start', time() - (3600 * 24 * 365), PARAM_INT);
$enddate = optional_param('end', time(), PARAM_INT);
if (! $category = $DB->get_record('course_categories', array(
    'id' => $categoryid))) {
    print_error(get_string('invalidcategoryid', 'mod_emarking'));
}
$context = context_coursecat::instance($categoryid);
$url = new moodle_url('/mod/emarking/reports/print.php', array(
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
echo $OUTPUT->tabtree(emarking_printoders_tabs($category), "print");
$filter = "WHERE (cc.path like '%/$categoryid/%' OR cc.path like '%/$categoryid')";
$sqlstats = "
    SELECT
        CASE WHEN year IS NULL THEN 'Total' ELSE year END AS year,
        CASE WHEN month IS NULL THEN 'Total anual' ELSE month END AS month,
        ROUND(SUM(totalpagestoprint)) AS totalpages,
        COUNT(DISTINCT EXAMS.id) AS totalexams,
        COUNT(DISTINCT EXAMS.courseid) AS totalcourses
    FROM (
        SELECT
            e.id,
            c.id as courseid,
            YEAR(from_unixtime(e.examdate)) AS year,
            MONTH(from_unixtime(e.examdate)) AS month,
            CASE
                WHEN e.usebackside = 1 THEN (e.totalstudents + e.extraexams) * (e.extrasheets + e.totalpages) / 2
                ELSE (e.totalstudents + e.extraexams) * (e.extrasheets + e.totalpages)
            END AS totalpagestoprint
            FROM {emarking_exams} AS e
            INNER JOIN {course} AS c ON (e.status = 2 AND e.course = c.id)
            INNER JOIN {course_categories} AS cc ON (c.category = cc.id)
            $filter
    ORDER BY examdate asc, c.shortname
        ) AS EXAMS
    GROUP BY year, month
    WITH ROLLUP";
$stats = $DB->get_recordset_sql($sqlstats);
$statstable = new html_table();
$statstable->head = array(
    ucfirst(get_string('year')),
    get_string('month'),
    get_string('totalpagesprint', 'mod_emarking'),
    get_string('totalexams', 'mod_emarking'));
$statstable->attributes ['style'] = 'margin-left: auto; margin-right: auto;';
$data = array();
foreach ($stats as $st) {
    $statstable->data [] = array(
        $st->year,
        $st->month,
        $st->totalpages,
        $st->totalexams);
    $data [$st->year] [$st->month] ['pages'] = $st->totalpages;
    $data [$st->year] [$st->month] ['exams'] = $st->totalexams;
    $data [$st->year] [$st->month] ['courses'] = $st->totalcourses;
}
$start = new DateTime();
$start->setTimestamp($startdate);
$end = new DateTime();
$end->setTimestamp($enddate);
$step = 'month';
$diff = $end->diff($start);
$months = $diff->y * 12 + $diff->m;
$chartdata = array();
for ($i = 0; $i < $months; $i ++) {
    $thisdate = clone $start;
    $thisdate->add(DateInterval::createFromDateString($i . ' month'));
    $y = intval($thisdate->format('Y'));
    $m = intval($thisdate->format('m'));
    $row = array();
    $row [] = $thisdate->format('M');
    if (isset($data [$y] [$m])) {
        $row [] = $data [$y] [$m] ['pages'];
        $row [] = $data [$y] [$m] ['exams'];
        $row [] = $data [$y] [$m] ['courses'];
    } else {
        $row [] = 0;
        $row [] = 0;
        $row [] = 0;
    }
    $chartdata [] = $row;
}
$charttitle = new stdClass();
$charttitle->start = $start->format('d M Y');
$charttitle->end = $end->format('d M Y');
list($html, $js) = emarking_get_google_chart("print",  // DIV id.
        array(
            get_string("range", "mod_emarking"),
            get_string("exams", "mod_emarking"),
            core_text::strtotitle(get_string("pages", "mod_emarking")),
            get_string("courses")), // Headers.
$chartdata, // Data.
get_string("printordersrange", "mod_emarking", $charttitle), // Chart title.
get_string("months")); // X axis label.
echo $html;
echo html_writer::table($statstable);
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
          // TODO: Show friendly message when we couldn't load Google's library.
      google.load("visualization", "1", {packages:["corechart"]});
      <?php echo $js ?>
    </script>
<?php
echo $OUTPUT->footer();