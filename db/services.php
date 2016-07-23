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
 * Web services definition for EMarking
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Jorge Villalón <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$services = array(
    'EMarking service' => array( // the name of the web service
        'functions' => array(
            'mod_emarking_fix_page'
        ), // web service functions of this service
        'requiredcapability' => 'mod/emarking:uploadexam', // if set, the web service user need this capability to access
                                                           // any function of this service. For example: 'some/capability:specified'
        'restrictedusers' => 0, // if enabled, the Moodle administrator must link some user to this service
                               // into the administration
        'enabled' => 1
    ) // if enabled, the service can be reachable on a default installation

);

$functions = array(
    'mod_emarking_fix_page' => array( // web service function name
        'classname' => 'mod_emarking_external', // class containing the external function
        'methodname' => 'fix_page', // external function name
        'classpath' => 'mod/emarking/externallib.php', // file containing the class/external function
        'description' => 'Fixes an orphan page assignin it to a student.', // human readable description of the web service function
        'type' => 'write', // database rights of the web service function (read, write)
        'ajax' => true,
        'services' => array(
            MOODLE_OFFICIAL_MOBILE_SERVICE
        )
    ) // Optional, only available for Moodle 3.1 onwards. List of built-in services (by shortname) where the function will be included. Services created manually via the Moodle interface are not supported.

);