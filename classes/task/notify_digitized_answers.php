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
namespace mod_emarking\task;
use \stdClass;
class notify_digitized_answers extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens.
        return get_string('digitizedanswersreminder', 'mod_emarking');
    }
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/emarking/print/locallib.php');
        mtrace("Starting notifications of digitizing answers");
        if ($CFG->emarking_daysbeforedigitizingreminder == 0) {
            mtrace("Digitizing answers reminder disabled in emarking config");
            return;
        }
        list($totalmessages) = emarking_send_digitizing_notification();
        mtrace("A total of $totalmessages was sent.");
    }
}