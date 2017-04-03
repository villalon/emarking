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


$PAGE->set_context(context_system::instance());
$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/index.php');
$PAGE->set_url($url);
$PAGE->set_title('escribiendo');
$query = "SELECT id, genre, description, title FROM mdl_emarking_activities
		WHERE status = 1 AND parent IS NULL	
		ORDER BY RAND()
			LIMIT 4"; 
$activities = $DB->get_records_sql($query);
$activityArray=array();
foreach ($activities as $activity){
	$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php', array('id'=>$activity->id));
	$activityArray[]=array(
			'title'=>$activity->title,
			'genre'=>$activity->genre,
			'description'=>$activity->description,
			'link'=>$url
	);
}

//print the header
include 'views/header.php';

//print the body
include 'views/index.php';

//print the footer
include 'views/footer.html';
