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
* @copyright 2017 Hans Jeria (hansjeria@gmail.com)
* @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
global $PAGE, $DB, $USER, $CFG;


require_once ($CFG->dirroot. '/mod/emarking/activities/locallib.php');

$type = optional_param('type', 1, PARAM_INT);
$oa = optional_param('oa', '', PARAM_TEXT);
$pc = optional_param('pc', '', PARAM_TEXT);
$genero = optional_param('genero', '', PARAM_TEXT);
$search = optional_param('search', '', PARAM_TEXT);

// Checkbox de la busqueda por Objetivo de aprendizaje, 2do formulario de busqueda
$chekbox13 = optional_param('13', 0, PARAM_INT);
$chekbox14  = optional_param('14', 0, PARAM_INT);
$chekbox15  = optional_param('15', 0, PARAM_INT);
$chekbox16  = optional_param('16', 0, PARAM_INT);
$chekbox17  = optional_param('17', 0, PARAM_INT);
$chekbox18  = optional_param('18', 0, PARAM_INT);
$chekbox19  = optional_param('19', 0, PARAM_INT);
$chekbox20  = optional_param('20', 0, PARAM_INT);
$chekbox21  = optional_param('21', 0, PARAM_INT);
$chekbox22  = optional_param('22', 0, PARAM_INT);

$PAGE->set_context(context_system::instance());
$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/search.php');
$PAGE->set_url($url);
$PAGE->set_title('escribiendo');

$teacherroleid = 3;
if (isloggedin ()) {
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
// Se incluye formulario para busqueda
include_once $CFG->dirroot. '/mod/emarking/activities/forms/search.php';
switch ($type){
	case 1:
		$search = $DB->sql_like_escape($search);
		$activitiessql = "SELECT *
			FROM {emarking_activities}
			WHERE parent IS NULL AND
				(title LIKE '%$search%' OR
				description LIKE '%$search%' OR
				audience LIKE '%$search%' OR
				instructions LIKE '%$search%' OR
				teaching LIKE '%$search%' OR
				languageresources LIKE '%$search%')
				AND status = 1";
		$results = $DB->get_records_sql($activitiessql);
		break;
	case 2;
		$oa = str_replace("°", "", $oa);
		$sqlwhere = 'WHERE parent IS NULL 
				AND status = 1 
				AND learningobjectives like "'.$oa.'[%"';
		if($chekbox13){
			$sqlwhere .= 'AND learningobjectives like "%13%"';
		}
		if($chekbox14){
			$sqlwhere .= 'AND learningobjectives like "%14%"';
		}
		if($chekbox15){
			$sqlwhere .= 'AND learningobjectives like "%15%"';
		}
		if($chekbox16){
			$sqlwhere .= 'AND learningobjectives like "%16%"';
		}
		if($chekbox17){
			$sqlwhere .= 'AND learningobjectives like "%17%"';
		}
		if($chekbox18){
			$sqlwhere .= 'AND learningobjectives like "%18%"';
		}
		if($chekbox19){
			$sqlwhere .= 'AND learningobjectives like "%19%"';
		}
		if($chekbox20){
			$sqlwhere .= 'AND learningobjectives like "%20%"';
		}
		if($chekbox21){
			$sqlwhere .= 'AND learningobjectives like "%21%"';
		}
		if($chekbox22){
			$sqlwhere .= 'AND learningobjectives like "%22%"';
		}
		$activitiessql = "SELECT *
				FROM {emarking_activities} ".$sqlwhere;
		$results = $DB->get_records_sql($activitiessql);
		break;
	case 3:
		$pc = $DB->sql_like_escape($pc);
		$activitiessql = 'SELECT *
				FROM {emarking_activities}
				WHERE comunicativepurpose = ? AND parent IS NULL AND status = ?';
		$results = $DB->get_records_sql($activitiessql, array(
			$pc,
			1
		));
		break;
	case 4:
		$genero= $DB->sql_like_escape($genero);
		$activitiessql = 'SELECT *
				FROM {emarking_activities}
				WHERE genre = ? AND parent IS NULL AND status = ?';
		$results = $DB->get_records_sql($activitiessql, array(
			$genero,
			1
		));
		break;
}
// Display results search
include 'views/results.php';
// The same footer to each pages
include 'views/footer.html';