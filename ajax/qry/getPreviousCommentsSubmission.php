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

$results = $DB->get_records_sql(
		"SELECT MIN(T.id) AS id, 
			T.text AS text, 
			T.format AS format, 
			COUNT(T.id) AS used, 
			MAX(lastused) AS lastused, 
			GROUP_CONCAT(T.markerid SEPARATOR '-') as markerids,
            SUM(T.owncomment) as owncomment,
            GROUP_CONCAT(T.page SEPARATOR '-') as pages,
            GROUP_CONCAT(T.id SEPARATOR '-') as commentids,
            GROUP_CONCAT(T.criterionid SEPARATOR '-') as criteria,
            GROUP_CONCAT(T.draftid SEPARATOR '-') as drafts
			FROM (
			SELECT c.id AS id, 
			c.rawtext AS text, 
			c.textformat AS format, 
			1 AS used, 
			c.timemodified AS lastused, 
			c.markerid,
            c.criterionid,
            d.id AS draftid,
            CASE WHEN c.markerid = :user THEN 1 ELSE 0 END AS owncomment,
            CASE WHEN d.id = :draft THEN c.pageno ELSE 0 END AS page
			FROM mdl_emarking_submission AS es
			INNER JOIN {emarking_draft} AS d ON (es.emarking = :emarking AND d.submissionid = es.id)
			INNER JOIN {emarking_comment} AS c ON (c.draft = d.id)
			WHERE c.textformat IN (1,2) AND LENGTH(rawtext) > 0
			UNION
			SELECT  id, 
					text, 
					1, 
					1, 
					0, 
					0,
					0,
                    0,
                    0,
                    0
			from {emarking_predefined_comment}
			WHERE emarkingid = :emarking2) as T
			GROUP BY text
			ORDER BY text"
		, array(
		    'user' => $USER->id,
		    'draft' => $draft->id,
		    'emarking'=>$submission->emarking, 
		    'emarking2'=>$submission->emarking));
