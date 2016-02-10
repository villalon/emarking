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
 * When an eMarking exam has been graded
 * 
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Jorge Villalon
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_emarking\event;

defined('MOODLE_INTERNAL') || die();
class emarking_published extends \core\event\base {
    public static function create_from_draft($draft, $submission, $context) {
        $event = self::create(
                array(
                    'context' => $context,
                    'objectid' => $draft->id,
                    'relateduserid' => $submission->student));
        return $event;
    }
    /**
     * Returns description of what happened.
     * 
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' published an eMarking exam with id '$this->objectid' for the " .
                 "course module id '$this->contextinstanceid'.";
    }
    /**
     * Return the legacy event log data.
     * 
     * @return array|null
     */
    protected function get_legacy_logdata() {
        return array(
            $this->courseid,
            'emarking',
            'publish exam',
            'marking/index.php?id=' . $this->objectid,
            $this->objectid,
            $this->contextinstanceid);
    }
    /**
     * Return localised event name.
     * 
     * @return string
     */
    public static function get_name() {
        return get_string('emarkinggraded', 'mod_emarking');
    }
    /**
     * Get URL related to the action.
     * 
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/emarking/marking/index.php', array(
            'id' => $this->objectid));
    }
    /**
     * Init method.
     * 
     * @return void
     */
    protected function init() {
        $this->data ['crud'] = 'c';
        $this->data ['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data ['objecttable'] = 'emarking_draft';
    }
}