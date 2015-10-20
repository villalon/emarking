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
 * @copyright 2012-2015 Jorge Villalon <jorge.villalon@uai.cl>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once $CFG->dirroot . '/lib/coursecatlib.php';
require_once $CFG->dirroot . '/mod/emarking/lib.php';

function emarking_get_regrade_motives_array() {
    $output = array(
    EMARKING_REGRADE_MISASSIGNED_SCORE,
    EMARKING_REGRADE_CORRECT_ALTERNATIVE_ANSWER,
    EMARKING_REGRADE_ERROR_CARRIED_FORWARD,
    EMARKING_REGRADE_UNCLEAR_FEEDBACK,
    EMARKING_REGRADE_STATEMENT_PROBLEM,
    EMARKING_REGRADE_OTHER);
    
    return $output;
}

/**
 * Get regrade type in human readable string
 *
 * @param unknown $type            
 * @return Ambigous <string, lang_string>|string
 */
function emarking_get_regrade_type_string($type)
{
    switch ($type) {
        case EMARKING_REGRADE_MISASSIGNED_SCORE:
            return get_string('missasignedscore', 'mod_emarking');
        case EMARKING_REGRADE_UNCLEAR_FEEDBACK:
            return get_string('unclearfeedback', 'mod_emarking');
        case EMARKING_REGRADE_STATEMENT_PROBLEM:
            return get_string('statementproblem', 'mod_emarking');
        case EMARKING_REGRADE_ERROR_CARRIED_FORWARD:
            return get_string('errorcarriedforward', 'mod_emarking');
        case EMARKING_REGRADE_CORRECT_ALTERNATIVE_ANSWER:
            return get_string('correctalternativeanswer', 'mod_emarking');
        case EMARKING_REGRADE_OTHER:
            return get_string('other', 'mod_emarking');
        default:
            return 'INVALID STATUS';
    }
}

/**
 * Returns a string indicating how long ago something happened
 *
 * @param int $time
 *            in unixtime
 * @param string $small
 *            indicates if the time should be enclosed in a div with a small font
 * @return string
 */
function emarking_time_ago($time, $small = false)
{
    $time = time() - $time; // to get the time since that moment
    
    $tokens = array(
        31536000 => get_string('year'),
        2592000 => get_string('month'),
        604800 => get_string('week'),
        86400 => get_string('day'),
        3600 => get_string('hour'),
        60 => get_string('minute'),
        1 => get_string('second', 'mod_emarking')
    );
    
    foreach ($tokens as $unit => $text) {
        if ($time < $unit)
            continue;
        $numberOfUnits = floor($time / $unit);
        $message = core_text::strtotitle(get_string('ago', 'core_message', $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '')));
        if($small) {
            $message = html_writer::div($message, "timeago");
        }
        return $message;
    }
}

/**
 * Returns the HTML for a jquery dialog which will show the content
 * @param string $title
 * @param string $content
 * @param string $prefix
 * @param int $id
 * @return string
 */
function emarking_view_more($title, $content, $prefix, $id) {
    $output = "<div id='$prefix" . "-$id' style='display:none;'>";
    $output .= $content;
    $output .= "</div>";
    $output .= "<a style='cursor:pointer; font-size: 8pt;' onclick='$(\"#$prefix"."-$id\").dialog({title:\"$title\",show: { effect: \"scale\", duration: 200 },modal:true,buttons:{".get_string("close", "mod_emarking").": function(){\$(this).dialog(\"close\");}}});'>"
    . $title . "</a>";

    return $output;
}
/**
 * Gets the icon used the a submission status
 *
 * @param int $status
 *            the submission status
 * @param boolean $prefix
 *            if the icon should include the status description as prefix
 * @param int $pctmarked
 *            if a percentage of marking for the draft is given, when 100% replaces grading for ready for publishing
 * @return string the icon HTML
 */
function emarking_get_draft_status_icon($status, $prefix = false, $pctmarked = 0)
{
    global $OUTPUT;
    
    $html = '';
    switch ($status) {
        case EMARKING_STATUS_MISSING:
            $html = $OUTPUT->pix_icon('i/warning', emarking_get_string_for_status($status));
            break;
        case EMARKING_STATUS_ABSENT:
            $html = $OUTPUT->pix_icon('t/block', emarking_get_string_for_status($status));
            break;
        case EMARKING_STATUS_SUBMITTED:
            $html = $OUTPUT->pix_icon('i/user', emarking_get_string_for_status($status));
            break;
        case EMARKING_STATUS_GRADING:
            if ($pctmarked > 0) {
                $html = $OUTPUT->pix_icon('i/grade_partiallycorrect', emarking_get_string_for_status($status, $pctmarked));
            } else {
                $html = $OUTPUT->pix_icon('i/grade_partiallycorrect', emarking_get_string_for_status($status));
            }
            break;
        case EMARKING_STATUS_PUBLISHED:
            $html = $OUTPUT->pix_icon('i/grade_correct', emarking_get_string_for_status($status));
            break;
        case EMARKING_STATUS_REGRADING:
            $html = $OUTPUT->pix_icon('i/flagged', emarking_get_string_for_status($status));
            break;
        case EMARKING_STATUS_REGRADING_RESPONDED:
            $html = $OUTPUT->pix_icon('i/unflagged', emarking_get_string_for_status($status));
            break;
        case EMARKING_STATUS_ACCEPTED:
            $html = $OUTPUT->pix_icon('t/locked', emarking_get_string_for_status($status));
            break;
        default:
            $html = $OUTPUT->pix_icon('t/block', emarking_get_string_for_status(EMARKING_STATUS_ABSENT));
            break;
    }
    
    if ($prefix) {
        $html = emarking_get_string_for_status($status, $pctmarked) . "&nbsp;" . $html;
    }
    
    return $html;
}

/**
 * All possible statuses for an eMarking submission
 *
 * @return multitype:string array of strings
 */
function emarking_get_statuses_as_array()
{
    $statuses = array();
    $statuses[] = EMARKING_STATUS_MISSING;
    $statuses[] = EMARKING_STATUS_ABSENT;
    $statuses[] = EMARKING_STATUS_SUBMITTED;
    $statuses[] = EMARKING_STATUS_GRADING;
    $statuses[] = EMARKING_STATUS_PUBLISHED;
    $statuses[] = EMARKING_STATUS_REGRADING;
    $statuses[] = EMARKING_STATUS_ACCEPTED;
    return $statuses;
}

/**
 * Returns an associative array with regrade motives IDs and their
 * descriptions
 * 
 * @return multitype:associative array with keys and descriptions
 */
function emarking_get_regrade_motives() {
    $motives = array();
    
    foreach(emarking_get_regrade_motives_array() as $m) {
        $motive = new stdClass();
        $motive->id = $m;
        $motive->description = emarking_get_regrade_type_string($m);
        $motives[] = $motive;
    }
    
    return $motives;
}

/**
 * Creates an array with the navigation tabs for emarking
 *
 * @param unknown $context
 *            The course context to validate capabilit
 * @param unknown $cm
 *            The course module (emarking activity)
 * @return multitype:tabobject
 */
function emarking_tabs($context, $cm, $emarking)
{
    global $CFG;
    global $USER;
    
    $usercangrade = has_capability("mod/emarking:grade", $context);
    
    $tabs = array();
    
    // Print tab
    $printtab = new tabobject("myexams", $CFG->wwwroot . "/mod/emarking/print/exam.php?id={$cm->id}", get_string("print", 'mod_emarking'));
    
    // Scan tab
    $scantab = new tabobject("scan", $CFG->wwwroot . "/mod/emarking/view.php?id={$cm->id}&scan=1", get_string('scan', 'mod_emarking'));
    $scantab->subtree[] = new tabobject("scanlist", $CFG->wwwroot . "/mod/emarking/view.php?id={$cm->id}&scan=1", get_string("exams", 'mod_emarking'));
    if($usercangrade) {
        $scantab->subtree[] = new tabobject("uploadanswers", $CFG->wwwroot . "/mod/emarking/print/uploadanswers.php?id={$cm->id}", get_string('uploadanswers', 'mod_emarking'));
    }
    
    // Settings tab
    $settingstab = new tabobject("settings", $CFG->wwwroot . "/mod/emarking/marking/settings.php?id={$cm->id}", get_string("settings", 'mod_emarking'));
    
    // Grade tab
    $markingtab = new tabobject("grade", $CFG->wwwroot . "/mod/emarking/view.php?id={$cm->id}", get_string('onscreenmarking', 'mod_emarking'));
    $markingtab->subtree[] = new tabobject("mark", $CFG->wwwroot . "/mod/emarking/view.php?id={$cm->id}", get_string("marking", 'mod_emarking'));
    if (! $usercangrade) {
        if ($emarking->peervisibility) {
            $markingtab->subtree[] = new tabobject("ranking", $CFG->wwwroot . "/mod/emarking/reports/ranking.php?id={$cm->id}", get_string("ranking", 'mod_emarking'));
            $markingtab->subtree[] = new tabobject("viewpeers", $CFG->wwwroot . "/mod/emarking/reports/viewpeers.php?id={$cm->id}", get_string("reviewpeersfeedback", 'mod_emarking'));
        }
        $markingtab->subtree[] = new tabobject("regrade", $CFG->wwwroot . "/mod/emarking/marking/regrades.php?id={$cm->id}", get_string("regrades", 'mod_emarking'));
    } else {
        if (has_capability('mod/emarking:regrade', $context) && $emarking->type == EMARKING_TYPE_NORMAL)
            $markingtab->subtree[] = new tabobject("regrades", $CFG->wwwroot . "/mod/emarking/marking/regraderequests.php?id={$cm->id}", get_string("regrades", 'mod_emarking'));
    }
    
    // Settings for marking
    if ($emarking->type == EMARKING_TYPE_NORMAL) {
        $settingstab->subtree[] = new tabobject("osmsettings", $CFG->wwwroot . "/mod/emarking/marking/settings.php?id={$cm->id}", get_string("marking", 'mod_emarking'));
        $settingstab->subtree[] = new tabobject("comment", $CFG->wwwroot . "/mod/emarking/marking/predefinedcomments.php?id={$cm->id}&action=list", get_string("predefinedcomments", 'mod_emarking'));
        if (has_capability('mod/emarking:assignmarkers', $context)) {
            $settingstab->subtree[] = new tabobject("markers", $CFG->wwwroot . "/mod/emarking/marking/markers.php?id={$cm->id}", get_string("markerspercriteria", 'mod_emarking'));
            $settingstab->subtree[] = new tabobject("pages", $CFG->wwwroot . "/mod/emarking/marking/pages.php?id={$cm->id}", core_text::strtotitle(get_string("pagespercriteria", 'mod_emarking')));
        }
    }
    
    // Grade report tab
    $gradereporttab = new tabobject("gradereport", $CFG->wwwroot . "/mod/emarking/reports/marking.php?id={$cm->id}", get_string("reports", "mod_emarking"));
    
    $gradereporttab->subtree[] = new tabobject("markingreport", $CFG->wwwroot . "/mod/emarking/reports/marking.php?id={$cm->id}", get_string("marking", 'mod_emarking'));
    $gradereporttab->subtree[] = new tabobject("report", $CFG->wwwroot . "/mod/emarking/reports/grade.php?id={$cm->id}", get_string("grades", "grades"));
    $gradereporttab->subtree[] = new tabobject("ranking", $CFG->wwwroot . "/mod/emarking/reports/ranking.php?id={$cm->id}", get_string("ranking", 'mod_emarking'));
    $gradereporttab->subtree[] = new tabobject("comparison", $CFG->wwwroot . "/mod/emarking/reports/comparativereport.php?id={$cm->id}", get_string("comparativereport", "mod_emarking"));
    
    // Active types tab
    $activatescan = new tabobject("enablescan", $CFG->wwwroot . "/mod/emarking/print/enablefeatures.php?id={$cm->id}&type=" . EMARKING_TYPE_PRINT_SCAN, get_string("enablescan", "mod_emarking"));
    
    // Active types tab
    $activateosm = new tabobject("enableosm", $CFG->wwwroot . "/mod/emarking/print/enablefeatures.php?id={$cm->id}&type=" . EMARKING_TYPE_NORMAL, get_string("enableosm", "mod_emarking"));
    
    // Tabs sequence
    if ($usercangrade) {
        // Print tab goes always except for markers training
        if ($emarking->type == EMARKING_TYPE_PRINT_ONLY || $emarking->type == EMARKING_TYPE_PRINT_SCAN || $emarking->type == EMARKING_TYPE_NORMAL) {
            if (has_capability('mod/emarking:uploadexam', $context)) {
                $tabs[] = $printtab;
            }
        }
        
        // Scan or enablescan tab
        if ($emarking->type == EMARKING_TYPE_PRINT_SCAN || $emarking->type == EMARKING_TYPE_NORMAL) {
            $tabs[] = $scantab;
        } else if ($emarking->type == EMARKING_TYPE_PRINT_ONLY) {
                $tabs[] = $activatescan;
        }
        
        // OSM tabs, either marking, reports and settings or enable osm
        if ($emarking->type == EMARKING_TYPE_NORMAL) {
            $tabs[] = $markingtab;
            $tabs[] = $gradereporttab;
            $tabs[] = $settingstab;
        } else if ($emarking->type == EMARKING_TYPE_PRINT_ONLY || $emarking->type == EMARKING_TYPE_PRINT_SCAN) {
            $tabs[] = $activateosm;
        }
    } else if($emarking->type == EMARKING_TYPE_PRINT_SCAN) {
        // This case is for students (user can not grade)
        $tabs = $scantab->subtree;
    } else if($emarking->type == EMARKING_TYPE_PRINT_ONLY) {
        // This case is for students (user can not grade)
        $tabs = array();
    } else {
        // This case is for students (user can not grade)
        $tabs = $markingtab->subtree;
    } 
    
    return $tabs;
}

/**
 * Navigation tabs for printing orders
 *
 * @param unknown $category
 *            The category object
 * @return multitype:tabobject array of tabobjects
 */
function emarking_printoders_tabs($category)
{
    $tabs = array();
    
    // Print orders
    $tabs[] = new tabobject("printorders", new moodle_url("/mod/emarking/print/printorders.php", array(
        "category" => $category->id,
        "status" => 1
    )), get_string("printorders", 'mod_emarking'));
    
    // Print orders history
    $tabs[] = new tabobject("printordershistory", new moodle_url("/mod/emarking/print/printorders.php", array(
        "category" => $category->id,
        "status" => 2
    )), get_string("records", 'mod_emarking'));
    
    // Statistics
    $tabs[] = new tabobject("statistics", new moodle_url("/mod/emarking/print/statistics.php", array(
        "category" => $category->id
    )), get_string("statistics", 'mod_emarking'));
    
    return $tabs;
}

/**
 * Verifies if there's a logo for the personalized header, and if there is one
 * it copies it to the module area
 */
function emarking_verify_logo()
{
    $fs = get_file_storage();
    $syscontext = context_system::instance();
    
    // Copy any new logo to this instance.
    if ($files = $fs->get_area_files($syscontext->id, 'core', 'logo', 1, "filename", false)) {
        
        foreach ($files as $file) {
            
            $filename = $file->get_filename();
            if ($filename !== '.') {
                
                $existingfiles = $fs->get_area_files($syscontext->id, 'mod_emarking', 'logo', 1, "filename", false);
                
                $replace = false;
                foreach ($existingfiles as $existingfile) {
                    if ($existingfile->get_timemodified() < $file->get_timemodified()) {
                        $existingfile->delete();
                        $replace = true;
                    }
                }
                
                if ($replace) {
                    $newrecord = new stdClass();
                    $newrecord->contextid = $syscontext->id;
                    $newrecord->itemid = 1;
                    $newrecord->filearea = 'logo';
                    $newrecord->component = 'mod_emarking';
                    $fs->create_file_from_storedfile($newrecord, $file);
                }
            }
        }
    }
}

/**
 * Verifies if there's a logo for the personalized header, and if there is it copies it to the
 * module
 */
function emarking_get_logo_file($filedir)
{
    $fs = get_file_storage();
    $syscontext = context_system::instance();
    
    if ($files = $fs->get_area_files($syscontext->id, 'mod_emarking', 'logo', 1, "filename", false)) {
        
        foreach ($files as $file) {
            $filename = $file->get_filename();
            if ($filename !== '.') {
                
                $existingfile = $fs->get_file($syscontext->id, 'mod_emarking', 'logo', 1, '/', $file->get_filename());
                if ($existingfile) {
                    return emarking_get_path_from_hash($filedir, $existingfile->get_pathnamehash());
                }
            }
        }
    }
    
    return false;
}

/**
 * Returns a human readable status for a draft
 *
 * @param int $status
 *            the draft status
 * @param int $pctmarked
 *            the percentage of the marking
 * @return Ambigous <string, lang_string>
 */
function emarking_get_string_for_status($status, $pctmarked = 0)
{
    switch ($status) {
        case EMARKING_STATUS_ACCEPTED:
            return get_string('statusaccepted', 'mod_emarking');
        case EMARKING_STATUS_ABSENT:
            return get_string('statusabsent', 'mod_emarking');
        case EMARKING_STATUS_GRADING:
            return $pctmarked == 100 ? get_string('statusgradingfinished', 'mod_emarking') : get_string('statusgrading', 'mod_emarking');
        case EMARKING_STATUS_MISSING:
            return get_string('statusmissing', 'mod_emarking');
        case EMARKING_STATUS_REGRADING:
            return get_string('statusregrading', 'mod_emarking');
        case EMARKING_STATUS_PUBLISHED:
            return get_string('statuspublished', 'mod_emarking');
        case EMARKING_STATUS_SUBMITTED:
            return get_string('statussubmitted', 'mod_emarking');
        default:
            return get_string('statuserror', 'mod_emarking');
    }
}

/**
 * Changes the order of the pages for a submission, according to
 * a new order
 *
 * @param unknown $submission
 *            submission object
 * @param unknown $neworder
 *            an array with the new order containing the page numbers
 * @return boolean true if successfull, false otherwise
 */
function emarking_sort_submission_pages($submission, $neworder)
{
    global $DB;
    
    // Verify that the new order is an array
    if (! is_array($neworder)) {
        return false;
    }
    
    // Verify that it contains the numbers from 0 to length -1
    $sortedbypage = array_merge($neworder);
    asort($sortedbypage);
    $newindices = array();
    $i = 0;
    foreach ($sortedbypage as $k => $v) {
        if (intval($v) != $i) {
            return false;
        }
        $i ++;
        $newindices[intval($v) + 1] = $k + 1;
    }
    
    // Get all the pages involved
    if (! $pages = $DB->get_records('emarking_page', array(
        'submission' => $submission->id
    ), 'page ASC')) {
        return false;
    }
    
    // Get the total pages in the sumission
    $numpages = count($pages);
    
    // Verify the new order has the same number of pages as the submission
    if ($numpages != count($neworder))
        return false;
        
        // Update each page according to the new sort order
    $i = 0;
    foreach ($pages as $page) {
        $newindex = $newindices[$page->page];
        $page->page = $newindex;
        $DB->update_record('emarking_page', $page);
        $i ++;
    }
    
    return true;
}

/**
 * Get all students from a course, for printing.
 *
 * @param unknown_type $courseid            
 */
function emarking_get_students_for_printing($courseid)
{
    global $DB;
    
    $query = 'SELECT u.id, u.idnumber, u.firstname, u.lastname, GROUP_CONCAT(e.enrol) as enrol
				FROM {user_enrolments} ue
				JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
				JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
				JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
				JOIN {user} u ON (ue.userid = u.id)
                GROUP BY u.id
				ORDER BY lastname ASC';
    
    // list($query, $params) = get_enrolled_sql(context_course::instance($courseid), 'mod/emarking:submit', 0 , true);
    
    $params = array(
        $courseid
    );
    
    // Se toman los resultados del query dentro de una variable.
    $rs = $DB->get_recordset_sql($query, $params);
    
    return $rs;
}

/**
 * Get all students from a group, for printing.
 *
 * @param unknown_type $groupid,$courseid            
 */
function emarking_get_students_of_groups($courseid, $groupid)
{
    global $DB;
    
    $query = 'SELECT u.id, u.idnumber, u.firstname, u.lastname, e.enrol
				FROM {user_enrolments} ue
				JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
				JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
				JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
				JOIN {user} u ON (ue.userid = u.id)
				JOIN {groups_members} gm ON (gm.userid = u.id AND gm.groupid = ?)
				ORDER BY lastname ASC';
    
    // Se toman los resultados del query dentro de una variable.
    $rs = $DB->get_recordset_sql($query, array(
        $courseid,
        $groupid
    ));
    
    return $rs;
}

/**
 * Get all groups from a course, for printing.
 *
 * @param unknown_type $courseid            
 */
function emarking_get_groups_for_printing($courseid)
{
    global $DB;
    
    $query = 'select id from {groups} where courseid = ? ';
    
    // Se toman los resultados del query dentro de una variable.
    $rs = $DB->get_recordset_sql($query, array(
        $courseid
    ));
    
    return $rs;
}

/**
 * Sends email to course manager, teacher and non-editingteacher,
 * when a printing order has been created
 *
 * @param unknown $exam            
 * @param unknown $course            
 * @param unknown $postsubject            
 * @param unknown $posttext            
 * @param unknown $posthtml            
 */
function emarking_send_notification($exam, $course, $postsubject, $posttext, $posthtml)
{
    global $USER, $CFG;
    
    $context = context_course::instance($course->id);
    
    $userstonotify = array();
    // Notify users that a new exam was sent. First, get all roles that have the capability in this context or higher
    $roles = get_roles_with_cap_in_context($context, 'mod/emarking:receivenotification');
    foreach ($roles[0] as $role) {
        // Get all users with any of the needed roles in the course context
        foreach (get_role_users($role, $context, true, 'u.id, u.username', null, true) as $usertonotify) {
            $userstonotify[$usertonotify->id] = $usertonotify;
        }
    }
    $forbidden = $roles[1];
    
    // Get the category context
    $contextcategory = context_coursecat::instance($course->category);
    
    // Now get all users that has any of the roles needed, no checking if they have roles forbidden as it is only
    // a notification
    foreach ($userstonotify as $user) {
        
        $thismessagehtml = $posthtml;
        
        // Downloading predominates over receiving notification
        if (has_capability('mod/emarking:downloadexam', $contextcategory, $user)) {
            $thismessagehtml .= '<p><a href="' . $CFG->wwwroot . '/mod/emarking/print/printorders.php?category=' . $course->category . '">' . get_string('printorders', 'mod_emarking') . '</a></p>';
        } else 
            if (has_capability('mod/emarking:receivenotification', $context, $user)) {
                $thismessagehtml .= '<p><a href="' . $CFG->wwwroot . '/mod/emarking/print/exams.php?course=' . $course->id . '">' . get_string('printorders', 'mod_emarking') . ' ' . $course->fullname . '</a></p>';
            }
        
        $eventdata = new stdClass();
        $eventdata->component = 'mod_emarking';
        $eventdata->name = 'notification';
        $eventdata->userfrom = $USER;
        $eventdata->userto = $user->id;
        $eventdata->subject = $postsubject;
        $eventdata->fullmessage = $posttext;
        $eventdata->fullmessageformat = FORMAT_HTML;
        $eventdata->fullmessagehtml = $thismessagehtml;
        $eventdata->smallmessage = $postsubject;
        
        $eventdata->notification = 1;
        
        message_send($eventdata);
    }
}

/**
 * Returns all paralles to a course based on the regular expression
 * for parallels defined in settings to extract: academicperiod, campus,
 * coursecode, section, term, year.
 *
 * @param stdClass $course
 *            course object
 * @return multitype:|boolean array with code parts or false if could not parse
 */
function emarking_get_parallel_courses($course)
{
    global $CFG, $DB;
    
    // Parses the shortname
    list ($academicperiod, $campus, $coursecode, $section, $term, $year) = emarking_parse_shortname($course->shortname);
    
    // If identified the parts run the query
    if ($coursecode) {
        $sql = " shortname LIKE '%-$coursecode-%-$term-$year'
				AND id != $course->id";
        $parallels = $DB->get_records_select('course', $sql, null, 'shortname ASC', '*');
        return $parallels;
    } else {
        return false;
    }
}

/**
 * Extracts academic information from shortname.
 * Assuming a regex
 * that includes academicperiod, campus, coursecode, section, term, year.
 *
 * ([0-9]+)-([SV])-([0-9A-Z]+)-([0-9]+)-([12V])-([0-9]+).*
 *
 * @param String $shortname            
 * @return multitype: array with each part
 */
function emarking_parse_shortname($shortname)
{
    global $CFG;
    
    $regex = $CFG->emarking_parallelregex;
    
    $academicperiod = null;
    $campus = null;
    $coursecode = null;
    $section = null;
    $term = null;
    $year = null;
    
    if ($regex && preg_match_all('/' . $regex . '/', $shortname, $regs)) {
        $academicperiod = $regs[1][0];
        $campus = $regs[2][0];
        $coursecode = $regs[3][0];
        $section = $regs[4][0];
        $term = $regs[5][0];
        $year = $regs[6][0];
    }
    
    return array(
        $academicperiod,
        $campus,
        $coursecode,
        $section,
        $term,
        $year
    );
}

/**
 * Unzip the source_file in the destination dir
 *
 * @param
 *            string The path to the ZIP-file.
 * @param
 *            string The path where the zipfile should be unpacked, if false the directory of the zip-file is used
 * @param
 *            boolean Indicates if the files will be unpacked in a directory with the name of the zip-file (true) or not (false) (only if the destination directory is set to false!)
 * @param
 *            boolean Overwrite existing files (true) or not (false)
 *            
 * @return boolean Succesful or not
 */
function emarking_unzip($src_file, $dest_dir = false, $create_zip_name_dir = true, $overwrite = true)
{
    global $CFG;
    
    if ($zip = zip_open($src_file)) {
        if ($zip) {
            $splitter = ($create_zip_name_dir === true) ? "." : "/";
            if ($dest_dir === false)
                $dest_dir = substr($src_file, 0, strrpos($src_file, $splitter)) . "/";
                
                // Create the directories to the destination dir if they don't already exist
            emarking_create_dirs($dest_dir);
            
            // For every file in the zip-packet
            while ($zip_entry = zip_read($zip)) {
                // Now we're going to create the directories in the destination directories
                
                // If the file is not in the root dir
                $pos_last_slash = strrpos(zip_entry_name($zip_entry), "/");
                if ($pos_last_slash !== false) {
                    // Create the directory where the zip-entry should be saved (with a "/" at the end)
                    emarking_create_dirs($dest_dir . substr(zip_entry_name($zip_entry), 0, $pos_last_slash + 1));
                }
                
                // Open the entry
                if (zip_entry_open($zip, $zip_entry, "r")) {
                    
                    // The name of the file to save on the disk
                    $file_name = $dest_dir . zip_entry_name($zip_entry);
                    
                    // Check if the files should be overwritten or not
                    if ($overwrite === true || $overwrite === false && ! is_file($file_name)) {
                        // Get the content of the zip entry
                        $fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                        
                        file_put_contents($file_name, $fstream);
                        // Set the rights
                        chmod($file_name, 0777);
                    }
                    
                    // Close the entry
                    zip_entry_close($zip_entry);
                }
            }
            // Close the zip-file
            zip_close($zip);
        }
    } else {
        return false;
    }
    
    return true;
}

/**
 * This function creates recursive directories if it doesn't already exist
 *
 * @param
 *            String The path that should be created
 *            
 * @return void
 */
function emarking_create_dirs($path)
{
    if (! is_dir($path)) {
        $directory_path = "";
        $directories = explode("/", $path);
        array_pop($directories);
        
        foreach ($directories as $directory) {
            $directory_path .= $directory . "/";
            if (! is_dir($directory_path)) {
                mkdir($directory_path);
                chmod($directory_path, 0777);
            }
        }
    }
}

/**
 * Obtains the total score of a draft
 *
 * @param unknown $draft            
 * @param unknown $controller            
 * @param unknown $fillings            
 * @return number
 */
function emarking_get_totalscore($draft, $controller, $fillings)
{
    global $DB;
    
    $curscore = 0;
    foreach ($fillings['criteria'] as $id => $record) {
        $curscore += $controller->get_definition()->rubric_criteria[$id]['levels'][$record['levelid']]['score'];
    }
    
    $bonus = 0;
    if ($bonusfromcomments = $DB->get_record_sql("
			SELECT 1, IFNULL(SUM(ec.bonus),0) AS totalbonus
			FROM {emarking_comment} AS ec
            INNER JOIN {gradingform_rubric_levels} AS b ON (b.id = ec.levelid)
			WHERE ec.levelid > 0 AND ec.draft = :draft", array(
        'draft' => $draft->id
    ))) {
        $bonus = floatval($bonusfromcomments->totalbonus);
    }
    
    return $curscore + $bonus;
}

/**
 * Calculates the next submission to be graded when a marker is currently grading
 * a specific submission
 *
 * @param unknown $emarking            
 * @param unknown $draft            
 * @param unknown $context            
 * @param unknown $student      
 * @param unknown $issupervisor      
 * @return number
 */
function emarking_get_next_submission($emarking, $draft, $context, $student, $issupervisor)
{
    global $DB, $USER;
    
    $levelids = 0;
    if ($criteria = $DB->get_records('emarking_marker_criterion', array(
        'emarking' => $emarking->id,
        'marker' => $USER->id
    ))) {
        
        $criterionarray = array();
        foreach ($criteria as $criterion) {
            $criterionarray[] = $criterion->criterion;
        }
        $criteriaids = implode(",", $criterionarray);
        
        $levelssql = "SELECT * FROM {gradingform_rubric_levels} WHERE criterionid in ($criteriaids)";
        $levels = $DB->get_records_sql($levelssql);
        $levelsarray = array();
        foreach ($levels as $level) {
            $levelsarray[] = $level->id;
        }
        $levelids = implode(",", $levelsarray);
    }

    $sortsql = ($emarking->anonymous < 2 && !$issupervisor) ? " d.sort ASC" : " u.lastname ASC";
    
    $criteriafilter = $levelids == 0 ? "" : " AND d.id NOT IN (SELECT d.id
	FROM {emarking_draft} as d
	INNER JOIN {emarking_submission} AS s ON (s.id = d.submissionid AND s.emarking = $emarking->id)
	INNER JOIN {emarking_page} as p ON (d.status < 20 AND p.submission = s.id)
	INNER JOIN {emarking_comment} as c ON (c.page = p.id AND c.draft = d.id AND c.levelid IN ($levelids))
	GROUP BY d.id)";
    
    $sortfilter = ($emarking->anonymous < 2  && !$issupervisor) ? " AND d.sort > $draft->sort" : " AND u.lastname > '$student->lastname'";
    
    $basesql = "SELECT d.id, d.status, d.sort, COUNT(rg.id) as regrades
			FROM mdl_emarking_draft AS d
            INNER JOIN mdl_emarking_submission AS s ON (d.submissionid = s.id)
			INNER JOIN mdl_user as u ON (s.student = u.id)
            LEFT JOIN mdl_emarking_regrade as rg ON (rg.draft = d.id AND rg.accepted = 0)
			WHERE s.emarking = :emarkingid AND d.id <> :draftid AND d.status >= 10";
    
    $sql = "$basesql
	$criteriafilter
	$sortfilter
	GROUP BY d.id
	ORDER BY $sortsql";
    
    // Gets the next submission id, limits start from 0 and get a total of 1
    $nextsubmissions = $DB->get_records_sql($sql, array(
        'emarkingid' => $emarking->id,
        'draftid' => $draft->id
    ));
    $id = 0;
    foreach ($nextsubmissions as $nextsubmission) {
        if($nextsubmission->status < 20 || ($nextsubmission->status >= 20 && $nextsubmission->regrades > 0)) {
            $id = $nextsubmission->id;
            break;
        }
    }
    
    // If we could not find a submission using the sortorder, we try from the beginning
    if ($id == 0) {
        $sql = "$basesql
		$criteriafilter
		GROUP BY d.id
		ORDER BY $sortsql";
        
        $nextsubmissions = $DB->get_records_sql($sql, array(
            'emarkingid' => $emarking->id,
            'draftid' => $draft->id
        ));
        foreach ($nextsubmissions as $nextsubmission) {
            if($nextsubmission->status < 20 || ($nextsubmission->status >= 20 && $nextsubmission->regrades > 0)) {
                $id = $nextsubmission->id;
                break;
            }
        }
    }
    return $id;
}

/**
 * This function gets a page to display on the eMarking interface using the page number, user id and emarking id
 *
 * @param unknown $pageno            
 * @param unknown $submission            
 * @param string $anonymous            
 * @param unknown $contextid            
 * @return multitype:NULL number |multitype:unknown string NULL Ambigous <unknown, NULL>
 */
function emarking_rotate_image($pageno, $submission, $context)
{
    global $CFG, $DB;
    
    ini_set('memory_limit', '256M');
    
    // If the page does not exist return false
    if (! $page = $DB->get_record('emarking_page', array(
        'submission' => $submission->id,
        'student' => $submission->student,
        'page' => $pageno
    ))) {
        return false;
    }
    
    if (! $student = $DB->get_record('user', array(
        'id' => $submission->student
    ))) {
        return false;
    }
    
    // Now get the file from the Moodle storage
    $fs = get_file_storage();
    
    if (! $file = $fs->get_file_by_id($page->file)) {
        print_error('Attempting to display image for non-existant submission ' . $context->id . "_" . $submission->emarkingid . "_" . $pagefilename);
    }
    
    // Si el archivo es una imagen
    if ($imageinfo = $file->get_imageinfo()) {
        
        $tmppath = $file->copy_content_to_temp('emarking', 'rotate');
        $image = imagecreatefrompng($tmppath);
        $image = imagerotate($image, 180, 0);
        if (! imagepng($image, $tmppath . '.png')) {
            return false;
        }
        clearstatcache();
        $filename = $file->get_filename();
        $timecreated = $file->get_timecreated();
        
        // Copy file from temp folder to Moodle's filesystem
        $file_record = array(
            'contextid' => $context->id,
            'component' => 'mod_emarking',
            'filearea' => 'pages',
            'itemid' => $submission->emarking,
            'filepath' => '/',
            'filename' => $filename,
            'timecreated' => $timecreated,
            'timemodified' => time(),
            'userid' => $student->id,
            'author' => $student->firstname . ' ' . $student->lastname,
            'license' => 'allrightsreserved'
        );
        
        if (! $fileanonymous = $fs->get_file_by_id($page->fileanonymous)) {
            print_error('Attempting to display image for non-existant submission ' . $context->id . "_" . $submission->emarkingid . "_" . $pagefilename);
        }
        
        $size = getimagesize($tmppath . '.png');
        $image = imagecreatefrompng($tmppath . '.png');
        $white = imagecolorallocate($image, 255, 255, 255);
        $y2 = round($size[1] / 10, 0);
        imagefilledrectangle($image, 0, 0, $size[0], $y2, $white);
        
        if (! imagepng($image, $tmppath . '_a.png')) {
            return false;
        }
        clearstatcache();
        $filenameanonymous = $fileanonymous->get_filename();
        $timecreatedanonymous = $fileanonymous->get_timecreated();
        
        // Copy file from temp folder to Moodle's filesystem
        $file_record_anonymous = array(
            'contextid' => $context->id,
            'component' => 'mod_emarking',
            'filearea' => 'pages',
            'itemid' => $submission->emarking,
            'filepath' => '/',
            'filename' => $filenameanonymous,
            'timecreated' => $timecreatedanonymous,
            'timemodified' => time(),
            'userid' => $student->id,
            'author' => $student->firstname . ' ' . $student->lastname,
            'license' => 'allrightsreserved'
        );
        
        if ($fs->file_exists($context->id, 'mod_emarking', 'pages', $submission->emarking, '/', $filename)) {
            $file->delete();
        }
        $fileinfo = $fs->create_file_from_pathname($file_record, $tmppath . '.png');
        
        if ($fs->file_exists($context->id, 'mod_emarking', 'pages', $submission->emarking, '/', $filenameanonymous)) {
            $fileanonymous->delete();
        }
        $fileinfoanonymous = $fs->create_file_from_pathname($file_record_anonymous, $tmppath . '_a.png');
        
        $page->file = $fileinfo->get_id();
        $page->fileanonymous = $fileinfoanonymous->get_id();
        $DB->update_record('emarking_page', $page);
        
        $imgurl = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/mod_emarking/pages/' . $submission->emarking . '/' . $fileinfo->get_filename());
        $imgurl .= "?r=" . random_string(15);
        $imgurlanonymous = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/mod_emarking/pages/' . $submission->emarking . '/' . $fileinfoanonymous->get_filename());
        $imgurlanonymous .= "?r=" . random_string(15);
        return array(
            $imgurl,
            $imgurlanonymous,
            $imageinfo['width'],
            $imageinfo['height']
        );
    }
    
    return false;
}

/**
 * Validates that there is a rubric set for the emarking activity
 *
 * @param unknown $context
 *            emarking context
 * @param string $die
 *            die if there is no rubric
 * @param string $showform            
 * @return multitype:unknown list($gradingmanager, $gradingmethod)
 */
function emarking_validate_rubric($context, $die = true, $showform = true)
{
    global $OUTPUT, $CFG, $COURSE;
    
    require_once ($CFG->dirroot . '/grade/grading/lib.php');
    
    // Get rubric instance
    $gradingmanager = get_grading_manager($context, 'mod_emarking', 'attempt');
    $gradingmethod = $gradingmanager->get_active_method();
    $definition = null;
    if ($gradingmethod === 'rubric') {
        $rubriccontroller = $gradingmanager->get_controller($gradingmethod);
        $definition = $rubriccontroller->get_definition();
    }
    
    $managerubricurl = $gradingmanager->get_management_url();
    
    // Validate that activity has a rubric ready
    if ($gradingmethod !== 'rubric' || ! $definition || $definition == null) {
        if ($showform) {
            echo $OUTPUT->notification(get_string('rubricneeded', 'mod_emarking'), 'notifyproblem');
            
            if (has_capability("mod/emarking:addinstance", $context)) {
                echo $OUTPUT->single_button($managerubricurl, get_string('createrubric', 'mod_emarking'));
            }
        }
        if ($die) {
            echo $OUTPUT->footer();
            die();
        }
    }
    if (isset($definition->status)) {
        if ($definition->status == 10) {
            echo $OUTPUT->notification(get_string('rubricdraft', 'mod_emarking'), 'notifyproblem');
            
            if (has_capability("mod/emarking:addinstance", $context)) {
                echo $OUTPUT->single_button($managerubricurl, get_string('completerubric', 'mod_emarking'));
            }
        }
    }
    
    return array(
        $gradingmanager,
        $gradingmethod
    );
}

/**
 * Outputs a json string based on a json array
 *
 * @param unknown $jsonOutput            
 */
function emarking_json_output($jsonOutput)
{
    // Callback para from webpage
    $callback = optional_param('callback', null, PARAM_RAW_TRIMMED);
    
    // Headers
    header('Content-Type: text/javascript');
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    
    if ($callback)
        $jsonOutput = $callback . "(" . $jsonOutput . ");";
    
    echo $jsonOutput;
    die();
}

/**
 * Returns a json string for a resultset
 *
 * @param unknown $resultset            
 */
function emarking_json_resultset($resultset)
{
    
    // Verify that parameters are OK. Resultset should not be null.
    if (! is_array($resultset) && ! $resultset) {
        emarking_json_error('Invalid parameters for encoding json. Results are null.');
    }
    
    // First check if results contain data
    if (is_array($resultset)) {
        $output = array(
            'error' => '',
            'values' => array_values($resultset)
        );
        emarking_json_output(json_encode($output));
    } else {
        $output = array(
            'error' => '',
            'values' => $resultset
        );
        emarking_json_output(json_encode($resultset));
    }
}

/**
 * Returns a json array
 *
 * @param unknown $output            
 */
function emarking_json_array($output)
{
    
    // Verify that parameter is OK. Output should not be null.
    if (! $output) {
        emarking_json_error('Invalid parameters for encoding json. output is null.');
    }
    
    $output = array(
        'error' => '',
        'values' => $output
    );
    emarking_json_output(json_encode($output));
}

/**
 * Returns a json string for an error
 *
 * @param unknown $message            
 * @param string $values            
 */
function emarking_json_error($message, $values = null)
{
    $output = array(
        'error' => $message,
        'values' => $values
    );
    emarking_json_output(json_encode($output));
}

/**
 * This function return if the emarking activity accepts
 * regrade requests at the current time.
 *
 * @param unknown $emarking            
 * @return boolean
 */
function emarking_is_regrade_requests_allowed($emarking)
{
    $requestswithindate = false;
    if (! $emarking->regraderestrictdates) {
        $requestswithindate = true;
    } elseif ($emarking->regradesopendate < time() && $emarking->regradesclosedate > time()) {
        $requestswithindate = true;
    }
    return $requestswithindate;
}

/**
 * Obtains all categories that are children to a specific one
 *
 * @param unknown $id_category            
 * @return Ambigous <string, unknown>
 */
function emarking_get_categories_childs($id_category)
{
    $coursecat = coursecat::get($id_category);
    
    $ids = array();
    $ids[] = $id_category;
    
    foreach ($coursecat->get_children() as $id => $childcategory) {
        $ids[] = $id;
    }
    
    return $ids;
}

/**
 * When a user is unenrolled from a course, we set all her submissions
 * to ABSENT state, so they are not considered in reports and remain
 * hidden from the interfaces until the user is enroled again.
 *
 * @param unknown $userid            
 * @param unknown $courseid            
 */
function emarking_unenrol_student($userid, $courseid)
{
    global $DB;
    
    if (! $emarkingactivities = $DB->get_records('emarking', array(
        'course' => $courseid
    ))) {
        // Nothing to do as there are no emarking activities in the course
        return true;
    }
    
    foreach ($emarkingactivities as $emarking) {
        $submission = $DB->get_record('emarking_submission', array(
            'emarking' => $emarking->id,
            'student' => $userid
        ));
        
        if (! $submission) {
            // The student has no submissions in this emarking activity. Skip her.
            continue;
        }
        
        // As the submission exists, we update all drafts to ABSENT
        $DB->set_field('emarking_draft', 'status', EMARKING_STATUS_ABSENT, array(
            'submissionid' => $submission->id
        ));
        $submission->status = EMARKING_STATUS_ABSENT;
        if ($DB->update_record('emarking_submission', $submission)) {
            
            error_log('Done!');
        } else {
            
            error_log('Problem!');
        }
    }
    
    return true;
}

/**
 * Creates a draft status information in HTML format
 *
 * @param unknown $draftid            
 * @param unknown $status            
 * @param unknown $qc            
 * @param unknown $criteriaids            
 * @param unknown $criteriascores            
 * @param unknown $comments            
 * @param unknown $pctmarked            
 * @param unknown $pctmarkeduser            
 * @param unknown $regrades            
 * @param unknown $pages            
 * @param unknown $numcriteria            
 * @param unknown $numcriteriauser            
 * @param unknown $emarking            
 * @return string
 */
function emarking_get_draft_status_info($draftid, $status, $qc, $criteriaids, $criteriascores, $comments, $pctmarked, $pctmarkeduser, $regrades, $pages, $numcriteria, $numcriteriauser, $emarking, $rubriccriteria)
{
    global $OUTPUT;
    
    // If the draft is published or the student was absent just show the icon
    if ($status <= EMARKING_STATUS_ABSENT || $status == EMARKING_STATUS_PUBLISHED || ($status == EMARKING_STATUS_GRADING && $pctmarked == 100)) {
        return emarking_get_draft_status_icon($status, true, 100);
    }
    
    if ($emarking->type == EMARKING_TYPE_NORMAL && ($status == EMARKING_STATUS_GRADING || $status == EMARKING_STATUS_SUBMITTED)) {
        
        // Completion matrix
        $matrix = '';
        $markedcriteria = explode(",", $criteriaids);
        $markedcriteriascores = explode(",", $criteriascores);
        if (count($markedcriteria) > 0 && $numcriteria > 0) {
            $matrix = "<div id='sub-$draftid' style='display:none;'>
            <table width='100%'>";
            $matrix .= "<tr><th>" . get_string('criterion', 'mod_emarking') . "</th><th style='text-align:center'>" . get_string('corrected', 'mod_emarking') . "</th></tr>";
            foreach ($rubriccriteria->rubric_criteria as $criterion) {
                $matrix .= "<tr><td>" . $criterion['description'] . "</td><td style='text-align:center'>";
                $key = array_search($criterion['id'], $markedcriteria);
                if ($key !== false) {
                    $matrix .= $OUTPUT->pix_icon('i/completion-manual-y', round($markedcriteriascores[$key], 1) . "pts");
                } else {
                    $matrix .= $OUTPUT->pix_icon('i/completion-manual-n', null);
                }
                $matrix .= "</td></tr>";
            }
            $matrix .= "</table></div>";
        }
        $matrixlink = "<div class=\"progress\"><a style='cursor:pointer;' onclick='$(\"#sub-$draftid\").dialog({modal:true,buttons:{Ok: function(){\$(this).dialog(\"close\");}}});'>
    <div class=\"progress-bar\" role=\"progressbar\" aria-valuenow=\"$pctmarked\"
    aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width:$pctmarked%\">
    <span class=\"sr-only\">$pctmarked%</span>
    </div></a>
    </div>" . $matrix;
        return $matrixlink;
    }
    
    if ($status == EMARKING_STATUS_REGRADING) {
        // Percentage of criteria already marked for this draft
        $pctmarkedtitle = ($numcriteria - $comments) . " pending criteria";
        $matrixlink = "" . ($numcriteriauser > 0 ? $pctmarkeduser . "% / " : '') . $pctmarked . "%" . ($regrades > 0 ? '<br/>' . $regrades . ' ' . get_string('regradespending', 'mod_emarking') : '');
    }
    
    $statushtml = $qc == 0 ? emarking_get_draft_status_icon($status, true) : $OUTPUT->pix_icon('i/completion-auto-y', get_string("qualitycontrol", "mod_emarking"));
    
    // Add warning icon if there are missing pages in draft
    if ($emarking->totalpages > 0 && $emarking->totalpages > $pages && $status > EMARKING_STATUS_MISSING) {
        $statushtml .= $OUTPUT->pix_icon('i/risk_xss', get_string('missingpages', 'mod_emarking'));
    }
    
    return $statushtml;
}
