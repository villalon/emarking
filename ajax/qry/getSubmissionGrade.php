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
$gradesql = "SELECT d.id, 
	IFNULL(d.grade,nm.grademin) as finalgrade, 
	IFNULL(d.timecreated, d.timemodified) as timecreated,
	IFNULL(d.timemodified,d.timecreated) as timemodified,
	IFNULL(d.generalfeedback,'') as feedback,
	d.qualitycontrol,
	nm.name as activityname,
	nm.grademin,
	nm.grade as grademax,
	u.firstname,
	u.lastname,
	u.id as studentid,
	u.email as email,
	c.fullname as coursename,
	c.shortname as courseshort,
	c.id as courseid,
	IFNULL(um.firstname,'') as markerfirstname,
	IFNULL(um.lastname,'') as markerlastname,
	IFNULL(um.email,'') as markeremail,
	IFNULL(um.id,0) as markerid,
	nm.custommarks,
	nm.regraderestrictdates,
	nm.regradesopendate,
	nm.regradesclosedate,
	nm.markingduedate
FROM {emarking_draft} as d
	INNER JOIN {emarking} as nm ON (d.id = ? AND d.emarkingid = nm.id)
	INNER JOIN {emarking_submission} as s ON (s.id = d.submissionid)
	LEFT JOIN {user} as u on (s.student = u.id)
	LEFT JOIN {course} as c on (c.id = nm.course)
	LEFT JOIN {user} as um on (d.teacher = um.id)";
$results = $DB->get_record_sql($gradesql, array($draft->id));

