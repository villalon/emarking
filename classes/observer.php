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
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
/**
 * Event observer.
 * Verifies when a user is enrolled or disenrolled so the
 * corresponding submissions can be hide.
 */
class mod_emarking_observer {
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        global $DB;
        $unenrolleduserid = $event->relateduserid;
        $enroltype = $event->get_data()['other']['enrol'];
        $courseid = $event->courseid;
        if (! emarking_unenrol_student($unenrolleduserid, $courseid)) {
            error_log("Error updating drafts from $unenrolleduserid in course $courseid");
        }
    }
    public static function user_enrolment_created(\core\event\user_enrolment_created $event) {
        global $DB;
        $unenrolleduserid = $event->relateduserid;
        $enroltype = $event->get_data()['other']['enrol'];
        $courseid = $event->courseid;
        $data = array(
            'uid' => $unenrolleduserid,
            'enrol' => $enroltype,
            'course' => $courseid);
        error_log(print_r($data, true));
    }
    public static function user_enrolment_updated(\core\event\user_enrolment_updated $event) {
        global $DB;
        $unenrolleduserid = $event->relateduserid;
        $enroltype = $event->get_data()['other']['enrol'];
        $courseid = $event->courseid;
        $data = array(
            'uid' => $unenrolleduserid,
            'enrol' => $enroltype,
            'course' => $courseid);
        error_log(print_r($data, true));
    }
}