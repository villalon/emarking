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
* @package   mod_emarking
* @copyright 2017 Francisco Ralph fco.ralph@gmail.com
* @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
global $PAGE, $DB, $USER, $CFG;

require_once ($CFG->dirroot. '/mod/emarking/activities/generos.php');
require_once ($CFG->dirroot. '/mod/emarking/activities/locallib.php');

$PAGE->set_context(context_system::instance());
$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/search.php');
$PAGE->set_url($url);
$PAGE->set_title('escribiendo');


$teacherroleid = 3;
$logged = false;

if (isloggedin ()) {
	$logged = true;
	$courses = enrol_get_all_users_courses ( $USER->id );
	$countcourses = count ( $courses );
	foreach ( $courses as $course ) {
		$context = context_course::instance ( $course->id );
		$roles = get_user_roles ( $context, $USER->id, true );
		foreach ( $roles as $rol ) {
			if ($rol->roleid == $teacherroleid) {
				$asteachercourses [$course->id] = $course->fullname;
			}
		}
	}
}

include 'views/header.php';
include_once $CFG->dirroot. '/mod/emarking/activities/forms/search.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

	switch ($_POST['type']){
		case 1:
			$search=$_POST['search'];
			$sql="SELECT *
			FROM {emarking_activities}
			WHERE parent IS NULL AND
			(title like '%$search%' OR
			description like '%$search%' OR
			audience like '%$search%' OR
			instructions like '%$search%' OR
			teaching like '%$search%' OR
			languageresources like '%$search%')
			AND status=1";
			$results = $DB->get_records_sql($sql);
			break;
		case 2;
		$oa = str_replace("°", "", $_POST['oa']);
		$sqlwhere ='WHERE learningobjectives like "'.$oa.'[%"';
		if(isset($_POST['13'])){
			$sqlwhere .= 'AND learningobjectives like "%13%"';
		}
		if(isset($_POST['14'])){
			$sqlwhere .= 'AND learningobjectives like "%14%"';
		}
		if(isset($_POST['15'])){
			$sqlwhere .= 'AND learningobjectives like "%15%"';
		}
		if(isset($_POST['16'])){
			$sqlwhere .= 'AND learningobjectives like "%16%"';
		}
		if(isset($_POST['17'])){
			$sqlwhere .= 'AND learningobjectives like "%17%"';
		}
		if(isset($_POST['18'])){
			$sqlwhere .= 'AND learningobjectives like "%18%"';
		}
		if(isset($_POST['19'])){
			$sqlwhere .= 'AND learningobjectives like "%19%"';
		}
		if(isset($_POST['20'])){
			$sqlwhere .= 'AND learningobjectives like "%20%"';
		}
		if(isset($_POST['21'])){
			$sqlwhere .= 'AND learningobjectives like "%21%"';
		}
		if(isset($_POST['22'])){
			$sqlwhere .= 'AND learningobjectives like "%22%"';
		}
		$sql="SELECT * FROM mdl_emarking_activities ".$sqlwhere;
		$results = $DB->get_records_sql($sql);
		break;
		case 3:
			$results=$DB->get_records('emarking_activities',array('comunicativepurpose'=>$_POST['pc'],'parent'=>null));
			break;
		case 4:
			$results=$DB->get_records('emarking_activities',array('genre'=>$_POST['genero'],'parent'=>null));

			break;
	}
	$totalresults=count($results);

	include 'views/results.php';
}
include 'views/footer.html';