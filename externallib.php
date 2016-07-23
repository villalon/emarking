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
 * Web services implementation for EMarking
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Jorge Villalón <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once ("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/mod/emarking/locallib.php");
require_once("$CFG->dirroot/mod/emarking/print/locallib.php");

class mod_emarking_external extends external_api {

    /**
     * Returns description of method parameters
     * 
     * @return external_function_parameters
     */
    public static function fix_page_parameters() {
        return new external_function_parameters(array(
            'pages' => new external_multiple_structure(
                    new external_single_structure(array(
                        'cmid' => new external_value(PARAM_INT, 'id of course module', VALUE_REQUIRED),
                'fileid' => new external_value(PARAM_INT, 'id of file', VALUE_REQUIRED),
                'studentid' => new external_value(PARAM_INT, 'student id', VALUE_REQUIRED),
                'pagenumber' => new external_value(PARAM_INT, 'page number', VALUE_REQUIRED)
            )))
        )
        );
    }
    
    public static function fix_page_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'page id'),
                )
                )
            );
    }
    
    public static function fix_page($pages) { //Don't forget to set it as static
        global $CFG, $DB;
    
        $params = self::validate_parameters(self::fix_page_parameters(), array('pages'=>$pages));
    
        $transaction = $DB->start_delegated_transaction(); //If an exception is thrown in the below code, all DB queries in this code will be rollback.
    
        $newpages = array();
    
        foreach ($params['pages'] as $page) {
            $page = (object)$page;
    
            if (!$student = $DB->get_record('user', array('id'=>$page->studentid))) {
                throw new invalid_parameter_exception('No student with the specified id');
            }
            list ($cm, $emarking, $course, $context) = emarking_get_cm_course_instance_by_id($page->cmid);
            // now security checks
            self::validate_context($context);
            require_capability('mod/emarking:uploadexam', $context);
            
            // finally fix the page
            $page->id = emarking_fix_page($page->fileid, $student, $emarking, $context, $page->pagenumber);
            $newpages[] = array('id' => $page->id);
        }
    
        $transaction->allow_commit();
    
        return $newpages;
    }
}