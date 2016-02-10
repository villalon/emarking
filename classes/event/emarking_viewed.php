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
 * The mod_emarking viewed event.
 * 
 * @package mod_emarking
 * @copyright 2015 Jorge Villalón
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_emarking\event;

defined('MOODLE_INTERNAL') || die();
/**
 * The mod_emarking viewed event class.
 * 
 * @package mod_emarking
 * @since Moodle 2.6
 * @copyright 2015 Jorge Villalón
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class emarking_viewed extends \core\event\base {
    /**
     * Returns description of what happened.
     * 
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' viewed the e-marking activity with id '$this->objectid' for the " .
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
            'view exam',
            'view.php?id=' . $this->contextinstanceid,
            $this->objectid,
            $this->contextinstanceid);
    }
    /**
     * Return localised event name.
     * 
     * @return string
     */
    public static function get_name() {
        return get_string('emarkingviewed', 'mod_emarking');
    }
    /**
     * Get URL related to the action.
     * 
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/emarking/view.php', array(
            'id' => $this->contextinstanceid));
    }
    /**
     * Init method.
     * 
     * @return void
     */
    protected function init() {
        $this->data ['crud'] = 'r';
        $this->data ['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data ['objecttable'] = 'emarking';
    }
}