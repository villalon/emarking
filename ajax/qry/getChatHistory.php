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
 * @copyright 2015 Francisco García <francisco.garcia.ralphn@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *         
 */
$room = required_param('room', PARAM_INT);
$source = required_param('source', PARAM_INT);

$sqlchathistory = " SELECT ec.*,  u.firstname, u.lastname, u.email
			FROM {emarking_chat} as ec
			INNER JOIN {user} as u on u.id=ec.userid
		    WHERE ec.room=:room AND ec.source=:source
		";
$params = array('room'=>$room, 'source'=>$source);
$results = $DB->get_records_sql($sqlchathistory, $params);

if(!$results) {
	$results = array();
}else{


foreach ($results as $obj){
	$obj->url=$CFG->wwwroot."/mod/emarking/marking/index.php";
	$output[]=$obj;
}

}
