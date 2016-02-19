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
 * @package mod
 * @subpackage emarking
 * @copyright 2012-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
define('NO_DEBUG_DISPLAY', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->libdir . '/accesslib.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$action = required_param('action', PARAM_ALPHA);
$username = required_param('username', PARAM_RAW_TRIMMED);
$password = required_param('password', PARAM_RAW_TRIMMED);
$courseid = optional_param('course', -1, PARAM_INT);

if ($courseid > 0 && ! $course = $DB->get_record('course', array(
    'id' => $courseid))) {
    emarking_json_error('Invalid course id');
    $context = context_course::instance($course->id);
} else {
    $context = context_system::instance();
}

$PAGE->set_context($context);

if (! $user = authenticate_user_login($username, $password)) {
    emarking_json_error('Invalid username or password');
}

// This is the correct way to fill up $USER variable.
complete_user_login($user);

if (! has_capability("mod/emarking:addinstance", $context)) {
    emarking_json_error('Access denied');
}

if ($action === 'students') {
    $rs = emarking_get_students_for_printing($course->id);
    $results = array();
    foreach ($rs as $r) {
        $results [] = $r;
    }
    emarking_json_resultset($results);
} else if($action === 'courses') {
	$rs = get_user_capability_course($capability, $user->id);
	$results = array();
	foreach($rs as $r) {
		$results[] = $r;
	}
	emarking_json_resultset($results);
} else if ($action === 'activities') {
    $rs = get_coursemodules_in_course('emarking', $course->id);
    $results = array();
    foreach ($rs as $r) {
        $results [] = $r;
    }
    emarking_json_resultset($results);
} else if ($action === 'courseinfo') {
    $results = array();
    $results [] = $course;
    emarking_json_resultset($results);
}

emarking_json_error('Invalid action');