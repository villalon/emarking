
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
require_once ($CFG->dirroot. '/mod/emarking/activities/locallib.php');
require_once ($CFG->libdir . '/coursecatlib.php');
require_once ($CFG->dirroot . "/mod/emarking/lib.php");
require_login ();

$PAGE->set_context(context_system::instance());
$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/index.php');
$PAGE->set_url($url);
$PAGE->set_title('escribiendo');

$image = new moodle_url ( $CFG->wwwroot . '/user/pix.php/' . $USER->id . '/f1.jpg' );
$createActivity = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/crear.php/' );
$userData = $DB->get_record ( 'user', array (
		'id' => $USER->id
) );
$countActivities = $DB->count_records_sql ( "select count(*) from {emarking_activities} where userid=?", array (
		$USER->id
) );
$countRubrics = $DB->count_records_sql ( "select count(*) from {grading_definitions} where usercreated=?", array (
		$USER->id
) );

$editProfileUrl = new moodle_url ( $CFG->wwwroot . '/user/edit.php/', array (
		'id' => $USER->id
) );

if ($countRubrics == 1) {
	$rubrics = $DB->get_record ( 'grading_definitions', array (
			'usercreated' => $USER->id
	) );
} elseif ($countRubrics >= 1) {

	$rubrics = $DB->get_records ( 'grading_definitions', array (
			'usercreated' => $USER->id
	) );
}

if ($countActivities == 1) {
	$activities = $DB->get_record ( 'emarking_activities', array (
			'userid' => $USER->id
	) );
} elseif ($countActivities >= 1) {

	$activities = $DB->get_records ( 'emarking_activities', array (
			'userid' => $USER->id
	) );
}
$usercourses = enrol_get_users_courses ( $USER->id );
$coursesarray=array();
if($categories =coursecat::make_categories_list('mod/emarking:downloadexam')){
	foreach ($categories as $key => $category){
	$courses= $DB->get_records('course',array('category'=>$key));
	foreach ($courses as $key => $course){
		$coursesasteacher [] = $course;
		$coursesarray[]=$course->id;
	}
	}
}

foreach ( $usercourses as $usercourse ) {

	if(in_array($usercourse->id,$coursesarray)){
		continue;
	}
	$coursecontext = context_course::instance ( $usercourse->id );

	$coursesasteacher [] = $usercourse;
	$coursesarray [] =$usercourse->id;
}

//print the header
include 'views/header.php';

//print the body
include 'views/my.php';

//print the footer
include 'views/footer.html';
