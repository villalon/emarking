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
 * @package   eMarking
 * @copyright 2013 Jorge Villalón <villalon@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Gets the grade for this submission if any
$gradesql = "SELECT g.id, 
	ifnull(g.grade,i.grademin) as finalgrade, 
	ifnull(g.timecreated, g.timemodified) as timecreated,
	ifnull(g.timemodified,g.timecreated) as timemodified,
	g.generalfeedback as feedback,
	i.itemname as activityname,
	i.grademin,
	i.grademax,
	u.firstname,
	u.lastname,
	u.id as studentid,
	u.email as email,
	c.fullname as coursename,
	c.shortname as courseshort,
	c.id as courseid,
	um.firstname as markerfirstname,
	um.lastname as markerlastname,
	um.email as markeremail,
	um.id as markerid,
	nm.custommarks,
	nm.regraderestrictdates,
	nm.regradesopendate,
	nm.regradesclosedate,
	nm.markingduedate
FROM {grade_items} as i
	INNER JOIN {emarking} as nm ON (nm.id = i.iteminstance and i.itemmodule = 'emarking')
	LEFT join {emarking_draft} as g ON (g.emarkingid = nm.id AND g.id = ?)
	LEFT join {user} as u on (g.student = u.id)
	LEFT JOIN {course} as c on (c.id = i.courseid)
	LEFT join {user} as um on (g.teacher = um.id)
WHERE i.itemmodule = 'emarking' and i.iteminstance = ?";
$results = $DB->get_record_sql($gradesql, array($submission->id, $submission->emarkingid));

