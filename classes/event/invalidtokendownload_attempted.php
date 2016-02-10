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
 * eMarking
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2015 Xiu-Fong Lin, 2016 Jorge Villalon
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_emarking\event;
defined('MOODLE_INTERNAL') || die();
class invalidtokendownload_attempted extends \core\event\base {
    protected function init() {
        $this->data ['crud'] = 'r';
        $this->data ['edulevel'] = self::LEVEL_OTHER;
        $this->data ['objecttable'] = 'emarking';
    }
    public static function get_name() {
        return get_string('invalidtokendownload', 'mod_emarking');
    }
    public function get_description() {
        return "The user with id '$this->userid' attempted to download an exam with a wrong token on eMarking activity " .
               "'$this->objectid' for the " .
               "course module id '$this->contextinstanceid'.";
    }
    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array(
            $this->courseid,
            'emarking',
            'view',
            $this->objectid,
            $this->contextinstanceid);
    }
}