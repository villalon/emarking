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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
 * eMarking
 *
 * @package Emarking
 * @copyright 2015 Xiu-Fong Lin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_emarking\event;

defined ( 'MOODLE_INTERNAL' ) || die ();
/**
 * eMarking.
 *
 * @property-read array $other {
 *                starts the emarking
 *                }
 *
 * @since Moodle 2.8.2
 * @copyright 2015 Xiu-Fong Lin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
*/
class invalidaccess_granted extends \core\event\base {
	protected function init() {
		$this->data ['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
		$this->data ['edulevel'] = self::LEVEL_PARTICIPATING;
	}
	public static function get_name() {
		return get_string ( 'eventinvalidaccessgranted', 'mod_emarking' );
	}
	public function get_description() {
		return get_string('invalidaccess', 'mod_emarking');
	}
	public function get_legacy_logdata() {
		// Override if you are migrating an add_to_log() call.
		return array (
				$this->courseid,
				'emarking',
				'view',
				$this->objectid,
				$this->contextinstanceid
		);
	}
}