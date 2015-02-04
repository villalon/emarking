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
 * @package mod
 * @subpackage emarking
 * @copyright 2012 Jorge Villalón {@link http://www.uai.cl}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Comment position within the page **/
$markerpageid = required_param('id', PARAM_INT);
$criterion = required_param('criterion', PARAM_INT);

if($action === 'delmarker')
	$DB->delete_records('emarking_marker_criterion', array('marker'=>$markerpageid, 'criterion'=>$criterion));
else if($action === 'delpage')
	$DB->delete_records('emarking_page_criterion', array('page'=>$markerpageid, 'criterion'=>$criterion));
	
// Send output info
$output = array('error'=>'',
		'id' => $markerpageid,
		'timemodified' => time(),
		'userid'=>$USER->id,
		'username'=>$USER->firstname . " " . $USER->lastname);
