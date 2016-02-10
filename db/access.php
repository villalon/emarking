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
 * Capability definitions for the emarking module
 * The capabilities are loaded into the database table when the module is
 * installed or updated.
 * Whenever the capability definitions are updated,
 * the module version number should be bumped up.
 * The system has four possible values for a capability:
 * CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT, and inherit (not set).
 * It is important that capability names are unique. The naming convention
 * for capabilities that are specific to modules and blocks is as follows:
 * [mod/block]/<plugin_name>:<capabilityname>
 * component_name should be the same as the directory name of the mod or block.
 * Core moodle capabilities are defined thus:
 * moodle/<capabilityclass>:<capabilityname>
 * Examples: mod/forum:viewpost
 * block/recent_activity:view
 * moodle/site:deleteuser
 * The variable name for the capability definitions array is $capabilities
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2013 Jorge Villalón <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
$capabilities = array(
    'mod/emarking:addinstance' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW),
        'clonepermissionsfrom' => 'moodle/course:manageactivities'),
    'mod/emarking:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'guest' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW)),
    'mod/emarking:submit' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'student' => CAP_ALLOW)),
    'mod/emarking:assignmarkers' => array(
        'riskbitmask' => RISK_MANAGETRUST,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'guest' => CAP_PROHIBIT,
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW)),
    'mod/emarking:receivenotification' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSECAT,
        'legacy' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_PROHIBIT,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_PROHIBIT)),
    'mod/emarking:receivedigitizingnotification' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSECAT,
        'legacy' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_PROHIBIT,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_PROHIBIT)),
    'mod/emarking:downloadexam' => array(
        // Capability type (write, read, etc.).
        'captype' => 'read',
        // Context in which the capability can be set (course, category, etc.).
        'contextlevel' => CONTEXT_SYSTEM,
        // Default values for different roles (only teachers and managers can modify).
        'archetypes' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_PROHIBIT,
            'editingteacher' => CAP_PROHIBIT)),
    'mod/emarking:uploadexam' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_PROHIBIT,
            'editingteacher' => CAP_ALLOW)),
    'mod/emarking:printordersview' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_PROHIBIT,
            'editingteacher' => CAP_PROHIBIT,
            'manager' => CAP_ALLOW)),
    'mod/emarking:manageanonymousmarking' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_PROHIBIT,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW)),
    'mod/emarking:managespecificmarks' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_PROHIBIT,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW)),
    'mod/emarking:managedelphiprocess' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_PROHIBIT,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW)),
    'mod/emarking:viewpeerstatistics' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW)),
    'mod/emarking:grade' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_PROHIBIT)),
    'mod/emarking:regrade' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_INHERIT,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_PROHIBIT)),
    'mod/emarking:supervisegrading' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_PREVENT,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW)),
    'mod/emarking:manageprinters' => array(
        'riskbitmask' => RISK_MANAGETRUST | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_PROHIBIT,
            'editingteacher' => CAP_PROHIBIT,
            'manager' => CAP_ALLOW)),
    'mod/emarking:viewcostreport' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_PROHIBIT,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW)));