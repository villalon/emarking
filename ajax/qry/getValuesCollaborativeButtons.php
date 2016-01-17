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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Hans Jeria <hansjeria@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *         
 */

$commentid = required_param('commentid', PARAM_INT);

$sqlvaluesbuttons = "SELECT cw.markerid, cw.type, CONCAT(u.username, ' ', u.lastname) AS markername
		FROM {emarking_collaborative_work} AS cw JOIN {user} AS u ON (cw.markerid = u.id)
		WHERE commentid=:commentid";

$collaborativevalues = $DB->get_records_sql($sqlvaluesbuttons,array("commentid"=>$commentid));

if(!$collaborativevalues) {
	$collaborativevalues = array();
}else{
	foreach ($collaborativevalues as $obj){
		$output[]=$obj;
	}
}

