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
 * @copyright 2012 Jorge Villalon <villalon@gmail.com> 
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once('locallib.php');

$categoryid = required_param('category', PARAM_INT);

if(!$category = $DB->get_record('course_categories', array('id' => $categoryid))) {

	print_error(get_string('invalidcategoryid', 'mod_emarking'));

}

$context = context_coursecat::instance($categoryid);

$url = new moodle_url('/mod/emarking/statistics.php', array('category'=>$categoryid));
$ordersurl = new moodle_url('/mod/emarking/printorders.php', array('category'=>$categoryid, 'status'=>1));
$categoryurl = new moodle_url('/course/index.php', array('categoryid'=>$categoryid));

if(!has_capability('mod/emarking:printordersview', $context)) {
	print_error('Not allowed!');
}

$PAGE->set_url($url);
$PAGE->set_pagelayout('course');
$PAGE->navbar->add ($category->name, $categoryurl);
$PAGE->navbar->add(get_string('printorders', 'mod_emarking'),$ordersurl);
$PAGE->navbar->add (get_string('statistics', 'mod_emarking'));
$PAGE->set_context($context);
$PAGE->set_heading(get_site()->fullname);

require_login();

$pagenumber = optional_param('pag', 1,PARAM_INT );

$PAGE->set_title(get_string('statistics', 'mod_emarking'));

echo $OUTPUT->header();

echo $OUTPUT->tabtree(emarking_printoders_tabs($category), "statistics" );

$sqlstats = 'select
ifnull(year,\'Total\') as year,
ifnull(month,\'Total anual\') as month,
round(sum(totalpagestoprint)) as totalpages,
count(*) as totalexams from (
select
year(from_unixtime(e.examdate)) as year,
month(from_unixtime(e.examdate)) as month,
case
when e.usebackside = 1 then (e.totalstudents + e.extraexams) * (e.extrasheets + e.totalpages) / 2
else (e.totalstudents + e.extraexams) * (e.extrasheets + e.totalpages)
end as totalpagestoprint
from {emarking_exams} e, {course} c
where e.course = c.id and c.category = ?
and e.status = 2 order by examdate asc, c.shortname
) as Exams
group by year, month with rollup';

$stats = $DB->get_recordset_sql($sqlstats, array($categoryid));

$statstable = new html_table();
$statstable->head = array(
		ucfirst(get_string('year')),
		get_string('month'),
		get_string('totalpages', 'mod_emarking'),
		get_string('totalexams', 'mod_emarking'));
$statstable->attributes['style'] = 'margin-left: auto; margin-right: auto;';
foreach($stats as $st) {
	$statstable->data[] = array($st->year,
			$st->month,
			$st->totalpages,
			$st->totalexams
	);
}


echo $OUTPUT->heading(get_string('statisticstotals', 'mod_emarking'));
echo html_writer::table($statstable);
echo $OUTPUT->footer();