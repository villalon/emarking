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
 * Unauthorized access through the ajax interface in eMarking
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2015 Xiu-Fong Lin, 2016 Jorge Villalon
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_emarking\event;
defined('MOODLE_INTERNAL') || die();
class unauthorizedajax_attempted extends \core\event\base {
    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' attempted to use the eMarking ajax interface with no permissions on draft " .
               "'$this->objectid' for the " .
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
            'unauthorized access',
            'a.php?id=' . $this->contextinstanceid,
            $this->objectid,
            $this->contextinstanceid);
    }
    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('unauthorizedaccess', 'mod_emarking');
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
        $this->data ['edulevel'] = self::LEVEL_OTHER;
        $this->data ['objecttable'] = 'emarking_draft';
    }
}