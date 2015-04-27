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
 * Utility to import OMR fonts into emarking
 * @package mod
 * @subpackage emarking
 * @copyright 2015 Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
require_once ($CFG->libdir . '/pdflib.php');
require_once ($CFG->dirroot . '/mod/emarking/print/locallib.php');

emarking_import_omr_fonts(true);

echo "Done";