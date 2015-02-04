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

$commentid = required_param('commentid', PARAM_INT);
$rownum = optional_param('rownum',1,PARAM_INT);

if($rownum < 1)
	$rownum = 1;

if(!$comment = $DB->get_record('emarking_comment', array('id'=>$commentid))) {
	emarking_json_error('Invalid comment id for searching.');
}

$params = array($submission->emarkingid,$submission->id,$comment->levelid);

$similaranswers = $DB->get_records_sql(
		"SELECT c.id, c.rawtext as text, c.pageno, c.pagesid
		FROM {emarking_comment} as c
		WHERE pagesid in (
		SELECT id FROM {emarking_page}
		WHERE assignment in (SELECT emarking FROM {emarking_page} WHERE id = ?))
		AND pagesid <> ? AND c.levelid = ?
		ORDER BY c.id"
		, $params);
$count = 0;
foreach($similaranswers as $answer) {
	$count++;
	if($count == $rownum) {
		if(!$submission = $DB->get_record('emarking_draft',array('id'=>$answer->pagesid))) {
			emarking_json_error('Invalid submission');
		}

		if (!$emarking = $DB->get_record("emarking", array("id"=>$submission->emarkingid))) {
			emarking_json_error('Invalid assignment');
		}

		if (! $course = $DB->get_record("course", array("id"=>$emarking->course))) {
			emarking_json_error('Invalid course');
		}

		if (! $cm = get_coursemodule_from_instance("emarking", $emarking->id, $course->id)) {
			emarking_json_error('Invalid course module');
		}

		list($imageurl, $imgwidth, $imgheight, $numfiles) = emarking_get_page_image($pageno, $submission, $context->id);
		emarking_json_array(array('error'=>'',
		'url'=>$imageurl,
		'width'=>$imgwidth,
		'height'=>$imgheight,
		'pagecount'=>$numfiles,
		));
	}
}

?>