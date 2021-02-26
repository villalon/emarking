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
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2012-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_emarking\task;

class generate_personalized_exams extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens.
        return get_string('personalizedexamsgeneration', 'mod_emarking');
    }
    public function execute() {
        global $CFG, $DB;
        if ($CFG->version > 2020010100) {
            require_once ($CFG->dirroot . "/lib/pdflib.php");
            require_once ($CFG->dirroot.'/mod/assign/feedback/editpdf/fpdi/autoload.php');
            require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/Tcpdf/Fpdi.php");
        }
        elseif ($CFG->version > 2015111600) {
            require_once ($CFG->dirroot . "/lib/pdflib.php");
            require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi_bridge.php");
            require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi.php");
        } else {
            require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php");
            require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi.php");
        }
        require_once ($CFG->dirroot . "/mod/emarking/lib/phpqrcode/phpqrcode.php");
        require_once ($CFG->dirroot . '/mod/emarking/lib.php');
        require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
        require_once ($CFG->dirroot . '/mod/emarking/print/locallib.php');

        emarking_generate_personalized_exams();
    }
}