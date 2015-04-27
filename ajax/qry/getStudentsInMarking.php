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
 * @package   mod
 * @subpackage emarking
 * @copyright 2013 Jorge Villalón <villalon@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$studentanonymous = $anonymous == 0 || $anonymous == 4;
$studentinfo = $studentanonymous ?  
	'u.id as studentid, \''.get_string('anonymousstudent', 'mod_emarking').'\' as firstname, \'\' as lastname, \'\' as email, \'\' as idnumber' : 
	'u.id as studentid, u.firstname, u.lastname, u.email, u.idnumber';

$orderby = $studentanonymous ? 'ORDER BY sub.timemodified DESC' : 'ORDER BY u.lastname, u.firstname ASC';

if($cm->groupmode == SEPARATEGROUPS && !is_siteadmin($USER)) {
	$results = $DB->get_records_sql(
			"SELECT $studentinfo ,
			t.id as teacherid, 
			CONCAT(t.firstname,' ',t.lastname) as teachername, 
			t.email as teacheremail,
			sub.id, 
			sub.timecreated, 
			sub.timemodified, 
			sub.grade,
			sub.status
			FROM {emarking_draft} as sub
			INNER JOIN {user} as u ON (sub.emarkingid = ? AND sub.student = u.id)
			INNER JOIN {emarking} as asi on (sub.emarkingid = asi.id)
			LEFT JOIN {user} as t ON (sub.teacher = t.id)
			WHERE u.id in (SELECT userid
			FROM {groups_members}
			WHERE groupid in (SELECT groupid
			FROM {groups_members} as gm
			INNER JOIN {groups} as g on (gm.groupid = g.id)
			WHERE gm.userid = ? AND g.courseid = asi.course))
			$orderby",
			array($submission->emarkingid, $userid));
} else {
	$results = $DB->get_records_sql(
			"SELECT $studentinfo ,
			t.id as teacherid,
			CONCAT(t.firstname,' ',t.lastname) as teachername,
			t.email as teacheremail,
			sub.id, 
			sub.timecreated,
			sub.timemodified,
			sub.grade,
			sub.status
			FROM {emarking_draft} as sub
			INNER JOIN {user} as u ON (sub.emarkingid = ? AND sub.student = u.id)
			LEFT JOIN {user} as t ON (sub.teacher = t.id)
			$orderby",
			array($submission->emarkingid));
}
