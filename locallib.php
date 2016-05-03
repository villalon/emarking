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
 * @copyright 2012-2016 Jorge Villalon <jorge.villalon@uai.cl>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/lib/coursecatlib.php');
require_once($CFG->dirroot . '/mod/emarking/lib.php');
/**
 * 
 * @param unknown $text
 * @param unknown $maxlength
 * @param unknown $id
 * @return unknown|string
 */
function emarking_get_text_view_more($text, $maxlength, $id) {
    global $OUTPUT;
    if (strlen($text) <= $maxlength) {
        return $text;
    }
    $short = substr($text, 0, $maxlength);
    return $short . "... " . emarking_view_more(core_text::strtolower(get_string("viewmore", "mod_emarking")), $text, "regrade",
            $id);
}
/**
 * 
 * @return multitype:Ambigous <NULL, string> unknown string
 */
function emarking_get_user_lang() {
    global $USER;
    $lang = $USER->lang;
    $parts = explode("_", $lang);
    $specific = null;
    if (count($parts) > 1) {
        $specific = strtoupper($parts [1]);
        $lang = $parts [0] . '_' . $specific;
    }
    return array(
        $lang,
        $parts [0],
        $specific);
}
/**
 * Obtains course module ($cm), course, emarking and context
 * objects from cm id in the URL
 *
 * @return multitype:stdClass context_module unknown mixed
 */
function emarking_get_cm_course_instance() {
    global $DB;
    // Course module id.
    $cmid = required_param('id', PARAM_INT);
    // Validate course module.
    if (! $cm = get_coursemodule_from_id('emarking', $cmid)) {
        print_error(get_string('invalidcoursemodule', 'mod_emarking') . " id: $cmid");
    }
    // Validate eMarking activity //TODO: validar draft si está selccionado.
    if (! $emarking = $DB->get_record('emarking', array(
        'id' => $cm->instance))) {
        print_error(get_string('invalidid', 'mod_emarking') . " id: $cm->id");
    }
    // Validate course.
    if (! $course = $DB->get_record('course', array(
        'id' => $emarking->course))) {
        print_error(get_string('invalidcourseid', 'mod_emarking'));
    }
    $context = context_module::instance($cm->id);
    return array(
        $cm,
        $emarking,
        $course,
        $context);
}
/**
 * 
 * @return multitype:string
 */
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
function emarking_get_regrade_type_string($type) {
    switch ($type) {
        case EMARKING_REGRADE_MISASSIGNED_SCORE :
            return get_string('missasignedscore', 'mod_emarking');
        case EMARKING_REGRADE_UNCLEAR_FEEDBACK :
            return get_string('unclearfeedback', 'mod_emarking');
        case EMARKING_REGRADE_STATEMENT_PROBLEM :
            return get_string('statementproblem', 'mod_emarking');
        case EMARKING_REGRADE_ERROR_CARRIED_FORWARD :
            return get_string('errorcarriedforward', 'mod_emarking');
        case EMARKING_REGRADE_CORRECT_ALTERNATIVE_ANSWER :
            return get_string('correctalternativeanswer', 'mod_emarking');
        case EMARKING_REGRADE_OTHER :
            return get_string('other', 'mod_emarking');
        default :
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
function emarking_time_ago($time, $small = false) {
    return emarking_time_difference($time, time(), $small);
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
function emarking_time_difference($time1, $time2, $small = false) {
    $time = abs($time1 - $time2); // To get the time since that moment.
    $ispast = $time1 <= $time2;
    $tokens = array(
        31536000 => get_string('year'),
        2592000 => get_string('month'),
        604800 => get_string('week'),
        86400 => get_string('day'),
        3600 => get_string('hour'),
        60 => get_string('minute'),
        1 => get_string('second', 'mod_emarking'));
    $tokensplural = array(
        31536000 => get_string('years'),
        2592000 => get_string('months'),
        604800 => get_string('weeks'),
        86400 => get_string('days'),
        3600 => get_string('hours'),
        60 => get_string('minutes'),
        1 => get_string('seconds', 'mod_emarking'));
    foreach ($tokens as $unit => $text) {
        if ($time < $unit) {
            continue;
        }
        $numberofunits = floor($time / $unit);
        $message = $numberofunits . ' ' . (($numberofunits > 1) ? $tokensplural [$unit] : $text);
        if ($ispast) {
            $message = core_text::strtolower(get_string('ago', 'core_message', $message));
        } else {
            $message = core_text::strtolower($message);
        }
        if ($small) {
            $message = html_writer::div($message, "timeago");
        }
        return $message;
    }
}
/**
 * Copies the settings from a source emarking activity to a destination one
 *
 * @param unknown $emarkingsrc
 * @param unknown $emarkingdst
 * @param unknown $rubricoverride
 * @param unknown $markersoverride
 */
function emarking_copy_settings($emarkingsrc, $emarkingdst, $rubricoverride, $markersoverride) {
    global $DB, $OUTPUT;
    $transaction = $DB->start_delegated_transaction();
    $criteriaitems = emarking_match_rubrics($emarkingsrc, $emarkingdst, $rubricoverride);
    try {
        emarking_copy_predefined_comments($emarkingsrc, $emarkingdst);
        emarking_copy_pages($emarkingsrc, $emarkingdst, $criteriaitems);
        if($markersoverride) {
            emarking_copy_markers($emarkingsrc, $emarkingdst, $criteriaitems);
        }
        emarking_copy_outcomes($emarkingsrc, $emarkingdst, $criteriaitems, $rubricoverride);
    } catch (moodle_exception $exception) {
        $DB->rollback_delegated_transaction($transaction, $exception);
        return false;
    }
    // This goes at the end as the emarking object is left unusable.
    echo $OUTPUT->box("Copying emarking settings");
    $emarkingsrc->id = $emarkingdst->id;
    $emarkingsrc->name = $emarkingdst->name;
    $emarkingsrc->intro = $emarkingdst->intro;
    $emarkingsrc->introformat = $emarkingdst->introformat;
    $emarkingsrc->timecreated = $emarkingdst->timecreated;
    $emarkingsrc->course = $emarkingdst->course;
    if (! $DB->update_record('emarking', $emarkingsrc)) {
        $DB->rollback_delegated_transaction($transaction, new moodle_exception("Could not update emarking destination"));
        return false;
    }
    $DB->commit_delegated_transaction($transaction);
    echo $OUTPUT->box("Success!");
    return true;
}
/**
 * Matches the rubrics from two emarking activities making sure it has the same
 * criteria (two criteria are equal if their description are equal).
 *
 * @param unknown $emarkingsrc
 * @param unknown $emarkingdst
 * @param unknown $context
 * @throws moodle_exception
 * @return multitype:unknown
 */
function emarking_match_rubrics($emarkingsrc, $emarkingdst, $copyrubric = false) {
    global $DB;
    $cmsrc = get_coursemodule_from_instance('emarking', $emarkingsrc->id);
    $cmdst = get_coursemodule_from_instance('emarking', $emarkingdst->id);
    $contextsrc = context_module::instance($cmsrc->id);
    $contextdst = context_module::instance($cmdst->id);
    list($gradingmanagersrc, $gradingmethodsrc, $definitionsrc, $rubriccontrollersrc) = emarking_validate_rubric($contextsrc, false,
            false);
    list($gradingmanagerdst, $gradingmethoddst, $definitiondst, $rubriccontrollerdst) = emarking_validate_rubric($contextdst, false,
            false);
    if ($copyrubric) {
        if (! $rubriccontrollerdst) {
            $method = $gradingmanagerdst->get_active_method();
            $rubriccontrollerdst = $gradingmanagerdst->get_controller($method);
        }
        if ($rubriccontrollerdst->get_definition()) {
            $rubriccontrollerdst->delete_definition();
        }
        $rubriccontrollerdst->update_definition($rubriccontrollersrc->get_definition_copy($rubriccontrollerdst));
        $DB->set_field('grading_definitions', 'timecopied', time(), array(
            'id' => $definitionsrc->id));
        list($gradingmanagerdst, $gradingmethoddst, $definitiondst, $rubriccontrollerdst) = emarking_validate_rubric($contextdst,
                false);
    }
    if (! $definitionsrc || ! $definitiondst) {
        throw new moodle_exception(
                "Invalid rubrics for copying, destination emarking doesn't have a rubric and you didn't selected to copy it.");
    }
    $criteriasrc = $definitionsrc->rubric_criteria;
    $criteriadst = $definitiondst->rubric_criteria;
    if (count($criteriasrc) != count($criteriadst)) {
        throw new moodle_exception("Invalid rubric for copying, they don't have the same number of criteria");
    }
    $criteriaitems = array();
    foreach ($criteriasrc as $criterionsrc) {
        foreach ($criteriadst as $criteriondst) {
            if ($criterionsrc ['description'] === $criteriondst ['description']) {
                $criteriaitems [$criterionsrc ['id']] = $criteriondst ['id'];
            }
        }
    }
    if (count($criteriaitems) != count($criteriadst)) {
        throw new moodle_exception("Not every criterion name matches in both source and destination emarking activities.");
    }
    return $criteriaitems;
}
/**
 * Copies the pages settings from a source emarking activity to a destination one
 *
 * @param unknown $emarkingsrc
 * @param unknown $emarkingdst
 */
function emarking_copy_pages($emarkingsrc, $emarkingdst, $criteriaitems) {
    global $DB, $OUTPUT;
    echo $OUTPUT->box("Copying pages settings");
    $DB->delete_records('emarking_page_criterion', array(
        'emarking' => $emarkingdst->id));
    $pagescriteria = $DB->get_records('emarking_page_criterion', array(
        'emarking' => $emarkingsrc->id));
    foreach ($pagescriteria as $pagecriterion) {
        $newpagecriterion = new stdClass();
        $newpagecriterion->emarking = $emarkingdst->id;
        $newpagecriterion->page = $pagecriterion->page;
        $newpagecriterion->criterion = $criteriaitems [$pagecriterion->criterion];
        $newpagecriterion->block = $pagecriterion->block;
        $newpagecriterion->timecreated = time();
        $newpagecriterion->timemodified = time();
        $DB->insert_record('emarking_page_criterion', $newpagecriterion);
    }
    echo $OUTPUT->box("Success!");
}
/**
 * Copies the markers settings from a source emarking activity to a destination one
 *
 * @param unknown $emarkingsrc
 * @param unknown $emarkingdst
 */
function emarking_copy_markers($emarkingsrc, $emarkingdst, $criteriaitems) {
    global $DB, $OUTPUT;
    echo $OUTPUT->box("Copying markers settings");
    $DB->delete_records('emarking_marker_criterion', array(
            'emarking' => $emarkingdst->id));
    $markerscriteria = $DB->get_records('emarking_marker_criterion', array(
            'emarking' => $emarkingsrc->id));
    foreach ($markerscriteria as $markercriterion) {
        $context = context_course::instance($emarkingdst->course);
        if(!has_capability('mod/emarking:grade', $context, $markercriterion->marker)) {
            $manual = enrol_get_plugin('manual');
            $instance = $DB->get_record('enrol', array('courseid'=>$emarkingdst->course, 'enrol'=>'manual'));
            $role = $DB->get_record('role', array('archetype'=>'teacher'));
            $manual->enrol_user($instance, $markercriterion->marker, $role->id);
        }
        $newmarkercriterion = new stdClass();
        $newmarkercriterion->emarking = $emarkingdst->id;
        $newmarkercriterion->marker = $markercriterion->marker;
        $newmarkercriterion->criterion = $criteriaitems [$markercriterion->criterion];
        $newmarkercriterion->block = $markercriterion->block;
        $newmarkercriterion->timecreated = time();
        $newmarkercriterion->timemodified = time();
        $DB->insert_record('emarking_marker_criterion', $newmarkercriterion);
    }
    echo $OUTPUT->box("Success!");
}
/**
 * Copies the outcomes settings from a source emarking activity to a destination one
 *
 * @param unknown $emarkingsrc
 * @param unknown $emarkingdst
 * @param unknown $criteriaitems
 * @param unknown $override
 * @throws moodle_exception
 */
function emarking_copy_outcomes($emarkingsrc, $emarkingdst, $criteriaitems, $override) {
    global $DB, $OUTPUT;
    if (! emarking_validate_outcomes($emarkingsrc, $emarkingdst)) {
        if (! $override) {
            throw new moodle_exception(
                    "Outcomes in destination emarking do not fit the used in the source and no override was indicated");
        } else {
            if (! emarking_copy_course_outcomes($emarkingsrc, $emarkingdst)) {
                throw new moodle_exception("Overriding outcomes failed, probably due to outcomes being used");
            }
        }
    }
    echo $OUTPUT->box("Copying outcomes");
    $DB->delete_records('emarking_outcomes_criteria', array(
        'emarking' => $emarkingdst->id));
    $outcomescriteria = $DB->get_records('emarking_outcomes_criteria', array(
        'emarking' => $emarkingsrc->id));
    foreach ($outcomescriteria as $outcomecriterionsrc) {
        $outcomecriterion = new stdClass();
        $outcomecriterion->emarking = $emarkingdst->id;
        $outcomecriterion->outcome = $outcomecriterionsrc->outcome;
        $outcomecriterion->criterion = $criteriaitems [$outcomecriterionsrc->criterion];
        $outcomecriterion->timecreated = time();
        $DB->insert_record('emarking_outcomes_criteria', $outcomecriterion);
    }
    echo $OUTPUT->box("Success!");
}
/**
 * Validates that all outcomes required from the emarking source
 * activity are available in the destination course
 *
 * @param unknown $emarkingsrc
 * @param unknown $emarkingdst
 * @return boolean
 */
function emarking_validate_outcomes($emarkingsrc, $emarkingdst) {
    global $DB, $CFG;
    require_once($CFG->libdir . "/grade/grade_outcome.php");
    if (! $coursesrc = $DB->get_record('course', array(
        'id' => $emarkingsrc->course))) {
        return false;
    }
    if (! $coursedst = $DB->get_record('course', array(
        'id' => $emarkingdst->course))) {
        return false;
    }
    $outcomessrc = grade_outcome::fetch_all_available($emarkingsrc->course);
    $outcomesdst = grade_outcome::fetch_all_available($emarkingdst->course);
    $destinationoutcomes = array();
    foreach ($outcomesdst as $outcomedst) {
        $destinationoutcomes [] = $outcomedst->id;
    }
    foreach ($outcomessrc as $outcomesrc) {
        if (! array_search($outcomesrc->id, $destinationoutcomes)) {
            return false;
        }
    }
    return true;
}
/**
 * Copies the outcomes (and associates to course if necessary) from
 * a source emarking activity to a destination one
 *
 * @param unknown $emarkingsrc
 * @param unknown $emarkingdst
 * @return boolean
 */
function emarking_copy_course_outcomes($emarkingsrc, $emarkingdst) {
    global $DB, $CFG;
    require_once($CFG->libdir . "/gradelib.php");
    require_once($CFG->libdir . "/grade/grade_outcome.php");
    if (! $coursesrc = $DB->get_record('course', array(
        'id' => $emarkingsrc->course))) {
        return false;
    }
    if (! $coursedst = $DB->get_record('course', array(
        'id' => $emarkingdst->course))) {
        return false;
    }
    $params = array(
        $coursesrc->id,
        $emarkingsrc->id);
    $sql = "SELECT outcomeid, itemnumber
          FROM {grade_items}
         WHERE courseid=? AND outcomeid IS NOT NULL
        AND itemmodule = 'emarking' AND iteminstance = ?
        AND itemtype = 'mod'";
    $tocopy = $DB->get_records_sql($sql, $params);
    $outcomesready = array();
    foreach ($tocopy as $outcomeused) {
        $outcomesready [] = $outcomeused->outcomeid;
    }
    $params = array(
        $coursedst->id,
        $emarkingdst->id);
    $sql = "SELECT outcomeid, itemnumber
          FROM {grade_items}
         WHERE courseid=? AND outcomeid IS NOT NULL
        AND itemmodule = 'emarking' AND iteminstance = ?
        AND itemtype = 'mod'";
    $realused = $DB->get_records_sql($sql, $params);
    $maxitemnumber = 999;
    foreach ($realused as $outcomeused) {
        if (array_search($outcomeused->outcomeid, $outcomesready)) {
            array_remove_by_value($outcomesready, $outcomeused->outcomeid);
        }
        if ($outcomeused->itemnumber > $maxitemnumber) {
            $maxitemnumber = $outcomeused->itemnumber;
        }
    }
    $outcomesdst = grade_outcome::fetch_all_available($emarkingdst->course);
    $outcomesavailable = array();
    foreach ($outcomesdst as $outcomedst) {
        $outcomesavailable [] = $outcomedst->id;
    }
    if ($maxitemnumber < 1000) {
        $maxitemnumber = 1000;
    }
    foreach ($outcomesready as $outcometocopy) {
        $outcome = grade_outcome::fetch(array(
            'id' => $outcometocopy));
        if (! array_search($outcometocopy, $outcomesavailable)) {
            $outcome->use_in($emarkingdst->course);
        }
        $outcomeitem = new grade_item();
        $outcomeitem->courseid = $emarkingdst->course;
        $outcomeitem->itemtype = 'mod';
        $outcomeitem->itemmodule = 'emarking';
        $outcomeitem->iteminstance = $emarkingdst->id;
        $outcomeitem->itemnumber = $maxitemnumber;
        $outcomeitem->itemname = $outcome->fullname;
        $outcomeitem->outcomeid = $outcome->id;
        $outcomeitem->gradetype = GRADE_TYPE_SCALE;
        $outcomeitem->scaleid = $outcome->scaleid;
        $outcomeitem->insert();
        $maxitemnumber ++;
    }
    return true;
}
/**
 * Returns a recordset of emarking objects which are in courses parallel
 * to a course parameter
 *
 * @param unknown $course
 * @param unknown $includeown
 * @return multitype:
 */
function emarking_get_parallel_emarkings($course, $includeown) {
    global $DB;
    $parallelcourses = emarking_get_parallel_courses($course, $includeown);
    if (! $parallelcourses) {
        return false;
    }
    $parallelsids = array();
    foreach ($parallelcourses as $parallelcourse) {
        if ($parallelcourse->id == $course->id) {
            continue;
        }
        foreach (get_coursemodules_in_course('emarking', $parallelcourse->id) as $cmdst) {
            $parallelsids [] = $cmdst->instance;
        }
    }
    $parallelsids = implode(",", $parallelsids);
    $parallelemarkings = $DB->get_records_sql(
            "
        SELECT e.*, c.id as courseid, c.shortname, c.fullname
        FROM {emarking} AS e
        INNER JOIN {course} AS c ON (e.course = c.id AND c.id <> ?)
        WHERE e.id IN ($parallelsids)
        ORDER BY c.fullname, e.name", array(
                $course->id));
    return $parallelemarkings;
}
/**
 * Copies predefined comments from a source emarking activity to a destination one
 *
 * @param unknown $emarkingsrc
 * @param unknown $emarkingdst
 */
function emarking_copy_predefined_comments($emarkingsrc, $emarkingdst) {
    global $DB, $OUTPUT;
    echo $OUTPUT->box("Copying predefined comments");
    $predefinedsrc = $DB->get_records('emarking_predefined_comment', array(
        'emarkingid' => $emarkingsrc->id));
    if (! $predefinedsrc || count($predefinedsrc) == 0) {
        echo $OUTPUT->box("No comments to copy");
        return true;
    }
    $DB->delete_records('emarking_predefined_comment', array(
        'emarkingid' => $emarkingdst->id));
    foreach ($predefinedsrc as $comment) {
        unset($comment->id);
        $comment->emarkingid = $emarkingdst->id;
        $DB->insert_record('emarking_predefined_comment', $comment);
    }
    echo $OUTPUT->box("Success!");
}
/**
 * Returns the HTML for a jquery dialog which will show the content
 *
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
    $output .= "<a class='viewmore' style='' onclick='$(\"#$prefix" .
             "-$id\").dialog({title:\"$title\",show: { effect: \"scale\", duration: 200 },modal:true,buttons:{" .
             get_string("close", "mod_emarking") . ": function(){\$(this).dialog(\"close\");}}});'>" . $title . "</a>";
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
function emarking_get_draft_status_icon($status, $prefix = false, $pctmarked = 0) {
    global $OUTPUT;
    $html = '';
    switch ($status) {
        case EMARKING_STATUS_MISSING :
            $html = $OUTPUT->pix_icon('i/warning', emarking_get_string_for_status($status));
            break;
        case EMARKING_STATUS_ABSENT :
            $html = $OUTPUT->pix_icon('t/block', emarking_get_string_for_status($status));
            break;
        case EMARKING_STATUS_SUBMITTED :
            $html = $OUTPUT->pix_icon('i/user', emarking_get_string_for_status($status));
            break;
        case EMARKING_STATUS_GRADING :
            if ($pctmarked > 0) {
                $html = $OUTPUT->pix_icon('i/grade_partiallycorrect', emarking_get_string_for_status($status, $pctmarked));
            } else {
                $html = $OUTPUT->pix_icon('i/grade_partiallycorrect', emarking_get_string_for_status($status));
            }
            break;
        case EMARKING_STATUS_PUBLISHED :
            $html = $OUTPUT->pix_icon('i/grade_correct', emarking_get_string_for_status($status));
            break;
        case EMARKING_STATUS_REGRADING :
            $html = $OUTPUT->pix_icon('i/flagged', emarking_get_string_for_status($status));
            break;
        case EMARKING_STATUS_REGRADING_RESPONDED :
            $html = $OUTPUT->pix_icon('i/grade_correct', emarking_get_string_for_status($status));
            break;
        case EMARKING_STATUS_ACCEPTED :
            $html = $OUTPUT->pix_icon('t/locked', emarking_get_string_for_status($status));
            break;
        default :
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
function emarking_get_statuses_as_array() {
    $statuses = array();
    $statuses [] = EMARKING_STATUS_MISSING;
    $statuses [] = EMARKING_STATUS_ABSENT;
    $statuses [] = EMARKING_STATUS_SUBMITTED;
    $statuses [] = EMARKING_STATUS_GRADING;
    $statuses [] = EMARKING_STATUS_PUBLISHED;
    $statuses [] = EMARKING_STATUS_REGRADING;
    $statuses [] = EMARKING_STATUS_ACCEPTED;
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
    foreach (emarking_get_regrade_motives_array() as $m) {
        $motive = new stdClass();
        $motive->id = $m;
        $motive->description = emarking_get_regrade_type_string($m);
        $motives [] = $motive;
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
function emarking_tabs($context, $cm, $emarking) {
    global $CFG, $USER;
    $usercangrade = has_capability("mod/emarking:grade", $context);
    $issupervisor = has_capability("mod/emarking:supervisegrading", $context);
    $tabs = array();
    // Print tab.
    $printtab = new tabobject("myexams", $CFG->wwwroot . "/mod/emarking/print/exam.php?id={$cm->id}",
            get_string("print", 'mod_emarking'));
    // Scan tab.
    $scantab = new tabobject("scan", $CFG->wwwroot . "/mod/emarking/view.php?id={$cm->id}&scan=1",
            get_string('scan', 'mod_emarking'));
    $scanlist = new tabobject("scanlist", $CFG->wwwroot . "/mod/emarking/view.php?id={$cm->id}&scan=1",
            get_string("exams", 'mod_emarking'));
    $uploadanswers = new tabobject("uploadanswers", $CFG->wwwroot . "/mod/emarking/print/uploadanswers.php?id={$cm->id}",
            get_string('uploadanswers', 'mod_emarking'));
    $scantab->subtree [] = $scanlist;
    if ($usercangrade && $issupervisor) {
        $scantab->subtree [] = $uploadanswers;
    }
    // Grade tab.
    $markingtab = new tabobject("grade", $CFG->wwwroot . "/mod/emarking/view.php?id={$cm->id}",
            get_string('onscreenmarking', 'mod_emarking'));
    $markingtab->subtree [] = new tabobject("mark", $CFG->wwwroot . "/mod/emarking/view.php?id={$cm->id}",
            get_string("marking", 'mod_emarking'));
    if (! $usercangrade) {
        if ($emarking->peervisibility) {
            $markingtab->subtree [] = new tabobject("ranking", $CFG->wwwroot . "/mod/emarking/reports/ranking.php?id={$cm->id}",
                    get_string("ranking", 'mod_emarking'));
            $markingtab->subtree [] = new tabobject("viewpeers", $CFG->wwwroot . "/mod/emarking/reports/viewpeers.php?id={$cm->id}",
                    get_string("reviewpeersfeedback", 'mod_emarking'));
        }
        if ($emarking->type == EMARKING_TYPE_NORMAL) {
            $markingtab->subtree [] = new tabobject("regrades",
                    $CFG->wwwroot . "/mod/emarking/marking/regraderequests.php?id={$cm->id}",
            get_string("regrades", 'mod_emarking'));
        }
    } else {
        if (has_capability('mod/emarking:regrade', $context) && $emarking->type == EMARKING_TYPE_NORMAL) {
            $markingtab->subtree [] = new tabobject("regrades",
                    $CFG->wwwroot . "/mod/emarking/marking/regraderequests.php?id={$cm->id}",
            get_string("regrades", 'mod_emarking'));
        }
    }
    // Settings tab.
    $settingstab = new tabobject("settings", $CFG->wwwroot . "/mod/emarking/marking/settings.php?id={$cm->id}",
            get_string("settings", 'mod_emarking'));
    // Settings for marking.
    if ($emarking->type == EMARKING_TYPE_NORMAL) {
        $settingstab->subtree [] = new tabobject("osmsettings", $CFG->wwwroot . "/mod/emarking/marking/settings.php?id={$cm->id}",
                get_string("marking", 'mod_emarking'));
        $settingstab->subtree [] = new tabobject("comment",
                $CFG->wwwroot . "/mod/emarking/marking/predefinedcomments.php?id={$cm->id}&action=list",
                get_string("predefinedcomments", 'mod_emarking'));
        if (has_capability('mod/emarking:assignmarkers', $context)) {
            $settingstab->subtree [] = new tabobject("markers", $CFG->wwwroot . "/mod/emarking/marking/markers.php?id={$cm->id}",
                    get_string("markerspercriteria", 'mod_emarking'));
            $settingstab->subtree [] = new tabobject("pages", $CFG->wwwroot . "/mod/emarking/marking/pages.php?id={$cm->id}",
                    core_text::strtotitle(get_string("pagespercriteria", 'mod_emarking')));
            $settingstab->subtree [] = new tabobject("outcomes", $CFG->wwwroot . "/mod/emarking/marking/outcomes.php?id={$cm->id}",
                    core_text::strtotitle(get_string("outcomes", "grades")));
            $settingstab->subtree [] = new tabobject("importrubric",
                    $CFG->wwwroot . "/mod/emarking/marking/importrubric.php?id={$cm->id}&action=list",
                    get_string("importrubric", 'mod_emarking'));
            $settingstab->subtree [] = new tabobject("export", $CFG->wwwroot . "/mod/emarking/marking/export.php?id={$cm->id}",
                    core_text::strtotitle(get_string("export", "mod_data")));
        }
    }
    // Grade report tab.
    $gradereporttab = new tabobject("gradereport", $CFG->wwwroot . "/mod/emarking/reports/marking.php?id={$cm->id}",
            get_string("reports", "mod_emarking"));
    $gradereporttab->subtree [] = new tabobject("markingreport", $CFG->wwwroot . "/mod/emarking/reports/marking.php?id={$cm->id}",
            get_string("marking", 'mod_emarking'));
    $gradereporttab->subtree [] = new tabobject("report", $CFG->wwwroot . "/mod/emarking/reports/grade.php?id={$cm->id}",
            get_string("grades", "grades"));
    $gradereporttab->subtree [] = new tabobject("ranking", $CFG->wwwroot . "/mod/emarking/reports/ranking.php?id={$cm->id}",
            get_string("ranking", 'mod_emarking'));
    if ($emarking->justiceperception > EMARKING_JUSTICE_DISABLED) {
        $gradereporttab->subtree [] = new tabobject("justicereport",
                $CFG->wwwroot . "/mod/emarking/reports/justice.php?id={$cm->id}", get_string("justice", 'mod_emarking'));
    }
    $gradereporttab->subtree [] = new tabobject("outcomesreport", $CFG->wwwroot . "/mod/emarking/reports/outcomes.php?id={$cm->id}",
            get_string("outcomes", "grades"));
    // Tabs sequence.
    if ($usercangrade) {
        // Print tab goes always except for markers training.
        if ($emarking->type == EMARKING_TYPE_PRINT_ONLY || $emarking->type == EMARKING_TYPE_PRINT_SCAN ||
                 $emarking->type == EMARKING_TYPE_NORMAL) {
            if (has_capability('mod/emarking:uploadexam', $context)) {
                $tabs [] = $printtab;
            }
        }
        // Scan or enablescan tab.
        if ($emarking->type == EMARKING_TYPE_PRINT_SCAN) {
            $tabs [] = $scantab;
        } else if ($emarking->type == EMARKING_TYPE_NORMAL && $issupervisor) {
            $markingtab->subtree [] = $uploadanswers;
        }
        // OSM tabs, either marking, reports and settings or enable osm.
        if ($emarking->type == EMARKING_TYPE_NORMAL) {
            $tabs [] = $markingtab;
            $tabs [] = $gradereporttab;
            if($issupervisor) {
                $tabs [] = $settingstab;
            }
        }
    } else if ($emarking->type == EMARKING_TYPE_PRINT_SCAN) {
        // This case is for students (user can not grade).
        $tabs = $scantab->subtree;
    } else if ($emarking->type == EMARKING_TYPE_PRINT_ONLY) {
        // This case is for students (user can not grade).
        $tabs = array();
    } else {
        // This case is for students (user can not grade).
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
function emarking_printoders_tabs($category) {
    $tabs = array();
    // Print orders.
    $tabs [] = new tabobject("printorders",
            new moodle_url("/mod/emarking/print/printorders.php",
                    array(
                        "category" => $category->id,
                        "status" => 1)), get_string("printorders", 'mod_emarking'));
    // Print orders history.
    $tabs [] = new tabobject("printordershistory",
            new moodle_url("/mod/emarking/print/printorders.php",
                    array(
                        "category" => $category->id,
                        "status" => 2)), get_string("records", 'mod_emarking'));
    // Statistics.
    $statstab = new tabobject("statistics",
            new moodle_url("/mod/emarking/reports/print.php", array(
                "category" => $category->id)), get_string("reports", 'mod_emarking'));
    // Print statistics.
    $statstab->subtree [] = new tabobject("print",
            new moodle_url("/mod/emarking/reports/print.php", array(
                "category" => $category->id)), get_string("statistics", 'mod_emarking'));
    // Print statistics details.
    $statstab->subtree [] = new tabobject("printdetails",
            new moodle_url("/mod/emarking/reports/printdetails.php", array(
                "category" => $category->id)), get_string("printdetails", 'mod_emarking'));
    $tabs [] = $statstab;
    return $tabs;
}
/**
 * Verifies if there's a logo for the personalized header, and if there is one
 * it copies it to the module area
 */
function emarking_verify_logo() {
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
function emarking_get_logo_file($filedir) {
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
function emarking_get_string_for_status($status, $pctmarked = 0) {
    switch ($status) {
        case EMARKING_STATUS_ACCEPTED :
            return get_string('statusaccepted', 'mod_emarking');
        case EMARKING_STATUS_ABSENT :
            return get_string('statusabsent', 'mod_emarking');
        case EMARKING_STATUS_GRADING :
            return $pctmarked == 100 ? get_string('statusgradingfinished', 'mod_emarking') : get_string('statusgrading',
                    'mod_emarking');
        case EMARKING_STATUS_MISSING :
            return get_string('statusmissing', 'mod_emarking');
        case EMARKING_STATUS_REGRADING :
            return get_string('statusregrading', 'mod_emarking');
        case EMARKING_STATUS_REGRADING_RESPONDED :
            return get_string('statusregradingresponded', 'mod_emarking');
        case EMARKING_STATUS_PUBLISHED :
            return get_string('statuspublished', 'mod_emarking');
        case EMARKING_STATUS_SUBMITTED :
            return get_string('statussubmitted', 'mod_emarking');
        default :
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
function emarking_sort_submission_pages($submission, $neworder) {
    global $DB;
    // Verify that the new order is an array.
    if (! is_array($neworder)) {
        return false;
    }
    // Verify that it contains the numbers from 0 to length -1.
    $sortedbypage = array_merge($neworder);
    asort($sortedbypage);
    $newindices = array();
    $i = 0;
    foreach ($sortedbypage as $k => $v) {
        if (intval($v) != $i) {
            return false;
        }
        $i ++;
        $newindices [intval($v) + 1] = $k + 1;
    }
    // Get all the pages involved.
    if (! $pages = $DB->get_records('emarking_page', array(
        'submission' => $submission->id), 'page ASC')) {
        return false;
    }
    // Get the total pages in the sumission.
    $numpages = count($pages);
    // Verify the new order has the same number of pages as the submission.
    if ($numpages != count($neworder)) {
        return false;
    }
        // Update each page according to the new sort order.
    $i = 0;
    foreach ($pages as $page) {
        $newindex = $newindices [$page->page];
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
function emarking_get_students_for_printing($courseid) {
    global $DB;
    $query = 'SELECT u.id, u.idnumber, u.firstname, u.lastname, GROUP_CONCAT(e.enrol) as enrol
				FROM {user_enrolments} ue
				JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
				JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
				JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
				JOIN {user} u ON (ue.userid = u.id)
                GROUP BY u.id
				ORDER BY lastname ASC';
    $params = array(
        $courseid);
    // Se toman los resultados del query dentro de una variable.
    $rs = $DB->get_recordset_sql($query, $params);
    return $rs;
}
/**
 * Get all students from a group, for printing.
 *
 * @param unknown_type $groupid,$courseid
 */
function emarking_get_students_of_groups($courseid, $groupid) {
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
        $groupid));
    return $rs;
}
/**
 * Get all groups from a course, for printing.
 *
 * @param unknown_type $courseid
 */
function emarking_get_groups_for_printing($courseid) {
    global $DB;
    $query = 'select id from {groups} where courseid = ? ';
    // Se toman los resultados del query dentro de una variable.
    $rs = $DB->get_recordset_sql($query, array(
        $courseid));
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
function emarking_send_notification($exam, $course, $postsubject, $posttext, $posthtml) {
    global $USER, $CFG;
    $context = context_course::instance($course->id);
    $userstonotify = array();
    // Notify users that a new exam was sent. First, get all roles that have the capability in this context or higher.
    $roles = get_roles_with_cap_in_context($context, 'mod/emarking:receivenotification');
    foreach ($roles [0] as $role) {
        // Get all users with any of the needed roles in the course context.
        foreach (get_role_users($role, $context, true, 'u.id, u.username', null, true) as $usertonotify) {
            $userstonotify [$usertonotify->id] = $usertonotify;
        }
    }
    $forbidden = $roles [1];
    // Get the category context.
    $contextcategory = context_coursecat::instance($course->category);
    // Now get all users that has any of the roles needed, no checking if they have roles forbidden as it is only
    // a notification.
    foreach ($userstonotify as $user) {
        $thismessagehtml = $posthtml;
        // Downloading predominates over receiving notification.
        if (has_capability('mod/emarking:downloadexam', $contextcategory, $user)) {
            $thismessagehtml .= '<p><a href="' . $CFG->wwwroot . '/mod/emarking/print/printorders.php?category=' .
            $course->category . '">' . get_string('printorders', 'mod_emarking') . '</a></p>';
        } else if (has_capability('mod/emarking:receivenotification', $context, $user)) {
            $thismessagehtml .= '<p><a href="' . $CFG->wwwroot . '/mod/emarking/print/exams.php?course=' . $course->id . '">' .
                     get_string('printorders', 'mod_emarking') . ' ' . $course->fullname . '</a></p>';
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
 * @param bool $includeown
 *            if the own course should be included
 * @return multitype:|boolean array with code parts or false if could not parse
 */
function emarking_get_parallel_courses($course, $includeown = false) {
    global $CFG, $DB;
    // Parses the shortname.
    list($academicperiod, $campus, $coursecode, $section, $term, $year) = emarking_parse_shortname($course->shortname);
    $sqlown = $includeown ? "" : "AND id != $course->id";
    // If identified the parts run the query.
    if ($coursecode) {
        $sql = " shortname LIKE '%-$coursecode-%-$term-$year'
				$sqlown";
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
 * ([0-9]+)-([SV])-([0-9A-Z]+)-([0-9]+)-([12V])-([0-9]+).*
 *
 * @param String $shortname
 * @return multitype: array with each part
 */
function emarking_parse_shortname($shortname) {
    global $CFG;
    $regex = $CFG->emarking_parallelregex;
    $academicperiod = null;
    $campus = null;
    $coursecode = null;
    $section = null;
    $term = null;
    $year = null;
    if ($regex && preg_match_all('/' . $regex . '/', $shortname, $regs)) {
        $academicperiod = $regs [1] [0];
        $campus = $regs [2] [0];
        $coursecode = $regs [3] [0];
        $section = $regs [4] [0];
        $term = $regs [5] [0];
        $year = $regs [6] [0];
    }
    return array(
        $academicperiod,
        $campus,
        $coursecode,
        $section,
        $term,
        $year);
}
/**
 * Unzip the source_file in the destination dir
 *
 * @param
 *            string The path to the ZIP-file.
 * @param
 *            string The path where the zipfile should be unpacked, if false the directory of the zip-file is used
 * @param
 *            boolean Indicates if the files will be unpacked in a directory with the name of the zip-file (true) or not (false)
 *            (only if the destination directory is set to false!)
 * @param
 *            boolean Overwrite existing files (true) or not (false)
 * @return boolean Succesful or not
 */
function emarking_unzip($srcfile, $destdir = false, $createzipnamedir = true, $overwrite = true) {
    global $CFG;
    if ($zip = zip_open($srcfile)) {
        if ($zip) {
            $splitter = ($createzipnamedir === true) ? "." : "/";
            if ($destdir === false) {
                $destdir = substr($srcfile, 0, strrpos($srcfile, $splitter)) . "/";
            }
                // Create the directories to the destination dir if they don't already exist.
            emarking_create_dirs($destdir);
            // For every file in the zip-packet.
            while ( $zipentry = zip_read($zip) ) {
                // Now we're going to create the directories in the destination directories.
                // If the file is not in the root dir.
                $poslastslash = strrpos(zip_entry_name($zipentry), "/");
                if ($poslastslash !== false) {
                    // Create the directory where the zip-entry should be saved (with a "/" at the end).
                    emarking_create_dirs($destdir . substr(zip_entry_name($zipentry), 0, $poslastslash + 1));
                }
                // Open the entry.
                if (zip_entry_open($zip, $zipentry, "r")) {
                    // The name of the file to save on the disk.
                    $filename = $destdir . zip_entry_name($zipentry);
                    // Check if the files should be overwritten or not.
                    if ($overwrite === true || $overwrite === false && ! is_file($filename)) {
                        // Get the content of the zip entry.
                        $fstream = zip_entry_read($zipentry, zip_entry_filesize($zipentry));
                        file_put_contents($filename, $fstream);
                        // Set the rights.
                        chmod($filename, 0777);
                    }
                    // Close the entry.
                    zip_entry_close($zipentry);
                }
            }
            // Close the zip-file.
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
 * @return void
 */
function emarking_create_dirs($path) {
    if (! is_dir($path)) {
        $directorypath = "";
        $directories = explode("/", $path);
        array_pop($directories);
        foreach ($directories as $directory) {
            $directorypath .= $directory . "/";
            if (! is_dir($directorypath)) {
                mkdir($directorypath);
                chmod($directorypath, 0777);
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
function emarking_get_totalscore($draft, $controller, $fillings) {
    global $DB;
    $curscore = 0;
    foreach ($fillings ['criteria'] as $id => $record) {
        $curscore += $controller->get_definition()->rubric_criteria [$id] ['levels'] [$record ['levelid']] ['score'];
    }
    $bonus = 0;
    if ($bonusfromcomments = $DB->get_record_sql(
            "
			SELECT 1, IFNULL(SUM(ec.bonus),0) AS totalbonus
			FROM {emarking_comment} ec
            INNER JOIN {gradingform_rubric_levels} b ON (b.id = ec.levelid)
			WHERE ec.levelid > 0 AND ec.draft = :draft", array(
                'draft' => $draft->id))) {
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
function emarking_get_next_submission($emarking, $draft, $context, $student, $issupervisor) {
    global $DB, $USER;
    $levelids = 0;
    if ($criteria = $DB->get_records('emarking_marker_criterion',
            array(
                'emarking' => $emarking->id,
                'marker' => $USER->id))) {
        $criterionarray = array();
        foreach ($criteria as $criterion) {
            $criterionarray [] = $criterion->criterion;
        }
        $criteriaids = implode(",", $criterionarray);
        $levelssql = "SELECT * FROM {gradingform_rubric_levels} WHERE criterionid in ($criteriaids)";
        $levels = $DB->get_records_sql($levelssql);
        $levelsarray = array();
        foreach ($levels as $level) {
            $levelsarray [] = $level->id;
        }
        $levelids = implode(",", $levelsarray);
    }
    $sqlemarkingtype = "";
    if ($emarking->type == EMARKING_TYPE_MARKER_TRAINING || $emarking->type == EMARKING_TYPE_PEER_REVIEW) {
        $sqlemarkingtype = "AND d.teacher = $USER->id";
    }
    $sortsql = ($emarking->anonymous < 2 && ! $issupervisor) ? " d.sort ASC" : " u.lastname ASC";
    $criteriafilter = $levelids == 0 ? "" : " AND d.id NOT IN (SELECT d.id
	FROM {emarking_draft} as d
	INNER JOIN {emarking_submission} AS s ON (s.id = d.submissionid AND s.emarking = $emarking->id)
	INNER JOIN {emarking_page} as p ON (d.status < 20 AND p.submission = s.id)
	INNER JOIN {emarking_comment} as c ON (c.page = p.id AND c.draft = d.id AND c.levelid IN ($levelids))
	GROUP BY d.id)";
    $sortfilter = ($emarking->anonymous < EMARKING_ANON_NONE && ! $issupervisor) ?
        " AND d.sort > $draft->sort" : " AND u.lastname > '$student->lastname'";
    $basesql = "SELECT d.id, d.status, d.sort, COUNT(rg.id) as regrades
			FROM {emarking_draft} AS d
            INNER JOIN {emarking_submission} AS s ON (d.submissionid = s.id $sqlemarkingtype)
			INNER JOIN {user} as u ON (s.student = u.id)
            LEFT JOIN {emarking_regrade} as rg ON (rg.draft = d.id AND rg.accepted = 0)
			WHERE s.emarking = :emarkingid AND d.id <> :draftid AND d.status >= 10";
    $sql = "$basesql
	$criteriafilter
	$sortfilter
	GROUP BY d.id
	ORDER BY $sortsql";
    // Gets the next submission id, limits start from 0 and get a total of 1.
    $nextsubmissions = $DB->get_records_sql($sql,
            array(
                'emarkingid' => $emarking->id,
                'draftid' => $draft->id));
    $id = 0;
    foreach ($nextsubmissions as $nextsubmission) {
        if ($nextsubmission->status < EMARKING_STATUS_PUBLISHED ||
                 ($nextsubmission->status >= EMARKING_STATUS_PUBLISHED && $nextsubmission->regrades > 0)) {
            $id = $nextsubmission->id;
            break;
        }
    }
    // If we could not find a submission using the sortorder, we try from the beginning.
    if ($id == 0) {
        $sql = "$basesql
		$criteriafilter
		GROUP BY d.id
		ORDER BY $sortsql";
        $nextsubmissions = $DB->get_records_sql($sql,
                array(
                    'emarkingid' => $emarking->id,
                    'draftid' => $draft->id));
        foreach ($nextsubmissions as $nextsubmission) {
            if ($nextsubmission->status < EMARKING_STATUS_PUBLISHED ||
                     ($nextsubmission->status >= EMARKING_STATUS_PUBLISHED && $nextsubmission->regrades > 0)) {
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
function emarking_rotate_image($pageno, $submission, $context) {
    global $CFG, $DB;
    ini_set('memory_limit', '256M');
    // If the page does not exist return false.
    if (! $page = $DB->get_record('emarking_page',
            array(
                'submission' => $submission->id,
                'student' => $submission->student,
                'page' => $pageno))) {
        return false;
    }
    if (! $student = $DB->get_record('user', array(
        'id' => $submission->student))) {
        return false;
    }
    // Now get the file from the Moodle storage.
    $fs = get_file_storage();
    if (! $file = $fs->get_file_by_id($page->file)) {
        print_error(
                'Attempting to display image for non-existant submission ' . $context->id . "_" . $submission->emarkingid . "_" .
                         $pagefilename);
    }
    // Si el archivo es una imagen.
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
        // Copy file from temp folder to Moodle's filesystem.
        $filerecord = array(
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
            'license' => 'allrightsreserved');
        if (! $fileanonymous = $fs->get_file_by_id($page->fileanonymous)) {
            print_error(
                    'Attempting to display image for non-existant submission ' . $context->id . "_" . $submission->emarkingid .
                    "_" . $pagefilename);
        }
        $size = getimagesize($tmppath . '.png');
        $image = imagecreatefrompng($tmppath . '.png');
        $white = imagecolorallocate($image, 255, 255, 255);
        $y2 = round($size [1] / 10, 0);
        imagefilledrectangle($image, 0, 0, $size [0], $y2, $white);
        if (! imagepng($image, $tmppath . '_a.png')) {
            return false;
        }
        clearstatcache();
        $filenameanonymous = $fileanonymous->get_filename();
        $timecreatedanonymous = $fileanonymous->get_timecreated();
        // Copy file from temp folder to Moodle's filesystem.
        $filerecordanonymous = array(
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
            'license' => 'allrightsreserved');
        if ($fs->file_exists($context->id, 'mod_emarking', 'pages', $submission->emarking, '/', $filename)) {
            $file->delete();
        }
        $fileinfo = $fs->create_file_from_pathname($filerecord, $tmppath . '.png');
        if ($fs->file_exists($context->id, 'mod_emarking', 'pages', $submission->emarking, '/', $filenameanonymous)) {
            $fileanonymous->delete();
        }
        $fileinfoanonymous = $fs->create_file_from_pathname($filerecordanonymous, $tmppath . '_a.png');
        $page->file = $fileinfo->get_id();
        $page->fileanonymous = $fileinfoanonymous->get_id();
        $DB->update_record('emarking_page', $page);
        $imgurl = file_encode_url($CFG->wwwroot . '/pluginfile.php',
                '/' . $context->id . '/mod_emarking/pages/' . $submission->emarking . '/' . $fileinfo->get_filename());
        $imgurl .= "?r=" . random_string(15);
        $imgurlanonymous = file_encode_url($CFG->wwwroot . '/pluginfile.php',
                '/' . $context->id . '/mod_emarking/pages/' . $submission->emarking . '/' . $fileinfoanonymous->get_filename());
        $imgurlanonymous .= "?r=" . random_string(15);
        return array(
            $imgurl,
            $imgurlanonymous,
            $imageinfo ['width'],
            $imageinfo ['height']);
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
function emarking_validate_rubric($context, $die = true, $showform = true) {
    global $OUTPUT, $CFG, $COURSE;
    require_once($CFG->dirroot . '/grade/grading/lib.php');
    // Get rubric instance.
    $gradingmanager = get_grading_manager($context, 'mod_emarking', 'attempt');
    $gradingmethod = $gradingmanager->get_active_method();
    $definition = null;
    $rubriccontroller = null;
    if ($gradingmethod !== 'rubric') {
        $gradingmanager->set_active_method('rubric');
        $gradingmethod = $gradingmanager->get_active_method();
    }
    $rubriccontroller = $gradingmanager->get_controller($gradingmethod);
    $definition = $rubriccontroller->get_definition();
    $managerubricurl = $gradingmanager->get_management_url();
    $importrubricurl = new moodle_url("/mod/emarking/marking/importrubric.php", array(
        "id" => $context->instanceid));
    // Validate that activity has a rubric ready.
    if ($gradingmethod !== 'rubric' || ! $definition || $definition == null) {
        if ($showform) {
            echo $OUTPUT->notification(get_string('rubricneeded', 'mod_emarking'), 'notifyproblem');
            if (has_capability("mod/emarking:addinstance", $context)) {
                echo "<table>";
                echo "<tr><td>" . $OUTPUT->single_button($managerubricurl, get_string('createrubric', 'mod_emarking'), "GET") .
                         "</td>";
                echo "<td>" . $OUTPUT->single_button($importrubricurl, get_string('importrubric', 'mod_emarking'), "GET") .
                         "</td></tr>";
                echo "</table>";
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
        $gradingmethod,
        $definition,
        $rubriccontroller);
}
/**
 * Outputs a json string based on a json array
 *
 * @param unknown $jsonoutput
 */
function emarking_json_output($jsonoutput) {
    global $OUTPUT;
    // Callback para from webpage.
    $callback = optional_param('callback', null, PARAM_RAW_TRIMMED);
    // Headers.
    header('Content-Type: application/javascript');
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    if ($callback) {
        $jsonoutput = $callback . "(" . $jsonoutput . ");";
    }
    echo $jsonoutput;
    die();
}
/**
 * Returns a json string for a resultset
 *
 * @param unknown $resultset
 */
function emarking_json_resultset($resultset) {
    // Verify that parameters are OK. Resultset should not be null.
    if (! is_array($resultset) && ! $resultset) {
        emarking_json_error('Invalid parameters for encoding json. Results are null.');
    }
    // First check if results contain data.
    if (is_array($resultset)) {
        $output = array(
            'error' => '',
            'values' => array_values($resultset));
        emarking_json_output(json_encode($output));
    } else {
        $output = array(
            'error' => '',
            'values' => $resultset);
        emarking_json_output(json_encode($resultset));
    }
}
/**
 * Returns a json array
 *
 * @param unknown $output
 */
function emarking_json_array($output) {
    // Verify that parameter is OK. Output should not be null.
    if (! $output) {
        emarking_json_error('Invalid parameters for encoding json. output is null.');
    }
    $output = array(
        'error' => '',
        'values' => $output);
    emarking_json_output(json_encode($output));
}
/**
 * Returns a json string for an error
 *
 * @param unknown $message
 * @param string $values
 */
function emarking_json_error($message, $values = null) {
    $output = array(
        'error' => $message,
        'values' => $values);
    emarking_json_output(json_encode($output));
}
/**
 * This function return if the emarking activity accepts
 * regrade requests at the current time.
 *
 * @param unknown $emarking
 * @return boolean
 */
function emarking_is_regrade_requests_allowed($emarking) {
    $requestswithindate = false;
    if (! $emarking->regraderestrictdates) {
        $requestswithindate = true;
    } else if ($emarking->regradesopendate < time() && $emarking->regradesclosedate > time()) {
        $requestswithindate = true;
    }
    return $requestswithindate;
}
/**
 * Obtains all categories that are children to a specific one
 *
 * @param unknown $idcategory
 * @return Ambigous <string, unknown>
 */
function emarking_get_categories_childs($idcategory) {
    $coursecat = coursecat::get($idcategory);
    $ids = array();
    $ids [] = $idcategory;
    foreach ($coursecat->get_children() as $id => $childcategory) {
        $ids [] = $id;
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
function emarking_unenrol_student($userid, $courseid) {
    global $DB;
    if (! $emarkingactivities = $DB->get_records('emarking', array(
        'course' => $courseid))) {
        // Nothing to do as there are no emarking activities in the course.
        return true;
    }
    foreach ($emarkingactivities as $emarking) {
        $submission = $DB->get_record('emarking_submission',
                array(
                    'emarking' => $emarking->id,
                    'student' => $userid));
        if (! $submission) {
            // The student has no submissions in this emarking activity. Skip her.
            continue;
        }
        // As the submission exists, we update all drafts to ABSENT.
        $DB->set_field('emarking_draft', 'status', EMARKING_STATUS_ABSENT,
                array(
                    'submissionid' => $submission->id));
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
function emarking_get_draft_status_info($d, $numcriteria, $numcriteriauser, $emarking, $rubriccriteria) {
    global $OUTPUT;
    // If the draft is published or the student was absent just show the icon.
    if ($d->status <= EMARKING_STATUS_ABSENT || $d->status == EMARKING_STATUS_PUBLISHED ||
             ($d->status == EMARKING_STATUS_GRADING && $d->pctmarked == 100)) {
        return emarking_get_draft_status_icon($d->status, true, 100);
    }
    if (($emarking->type == EMARKING_TYPE_NORMAL || $emarking->type == EMARKING_TYPE_PEER_REVIEW) &&
             ($d->status == EMARKING_STATUS_GRADING || $d->status == EMARKING_STATUS_SUBMITTED)) {
        // Completion matrix.
        $matrix = '';
        $markedcriteria = explode(",", $d->criteriaids);
        $markedcriteriascores = explode(",", $d->criteriascores);
        if (count($markedcriteria) > 0 && $numcriteria > 0) {
            $matrix = "<div id='sub-$d->id' style='display:none;'>
            <table width='100%'>";
            $matrix .= "<tr><th>" . get_string('criterion', 'mod_emarking') . "</th><th style='text-align:center'>" .
                     get_string('corrected', 'mod_emarking') . "</th></tr>";
            foreach ($rubriccriteria->rubric_criteria as $criterion) {
                $matrix .= "<tr><td>" . $criterion ['description'] . "</td><td style='text-align:center'>";
                $key = array_search($criterion ['id'], $markedcriteria);
                if ($key !== false) {
                    $matrix .= $OUTPUT->pix_icon('i/completion-manual-y', round($markedcriteriascores [$key], 1) . "pts");
                } else {
                    $matrix .= $OUTPUT->pix_icon('i/completion-manual-n', null);
                }
                $matrix .= "</td></tr>";
            }
            $matrix .= "</table></div>";
        }
        $matrixlink = "<div class=\"progress\"><a style='cursor:pointer;' " .
        "onclick='$(\"#sub-$d->id\").dialog({modal:true,buttons:{Ok: function(){\$(this).dialog(\"close\");}}});'>
    <div class=\"progress-bar\" role=\"progressbar\" aria-valuenow=\"$d->pctmarked\"
    aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width:$d->pctmarked%\">
    <span class=\"sr-only\">$d->pctmarked%</span>
    </div></a>
    </div>" . $matrix;
        return $matrixlink;
    }
    if ($d->status == EMARKING_STATUS_REGRADING) {
        // Percentage of criteria already marked for this draft.
        $pctmarkedtitle = ($numcriteria - $d->comments) . " pending criteria";
        $matrixlink = "" . ($numcriteriauser > 0 ? $d->pctmarkeduser . "% / " : '') . $d->pctmarked . "%" .
                 ($d->regrades > 0 ? '<br/>' . $d->regrades . ' ' . get_string('regradespending', 'mod_emarking') : '');
    }
    $statushtml = $d->qc == 0 ? emarking_get_draft_status_icon($d->status, true) : $OUTPUT->pix_icon('i/completion-auto-y',
            get_string("qualitycontrol", "mod_emarking"));
    // Add warning icon if there are missing pages in draft.
    if ($emarking->totalpages > 0 && $emarking->totalpages > $d->pages && $d->status > EMARKING_STATUS_MISSING) {
        $statushtml .= $OUTPUT->pix_icon('i/risk_xss', get_string('missingpages', 'mod_emarking'));
    }
    return $statushtml;
}
function emarking_get_category_cost($courseid) {
    global $DB, $CFG;
    $course = $DB->get_record('course', array('id' => $courseid), 'id, category');
    $coursecategory = $course->category;
    $categorycost = null;
    $noinfloop = 0;
    while ( $categorycost == null || $categorycost == 0 ) {
        $categorycostparams = array($coursecategory);
        $sqlcategorycost = "SELECT cc.id, cc.name as name, ccc.printingcost AS cost, cc.parent as parent
        			  FROM mdl_course_categories as cc
			          LEFT JOIN mdl_emarking_category_cost AS ccc ON (cc.id = ccc.category)
        			  WHERE cc.id = ?";
        if ($categorycosts = $DB->get_records_sql($sqlcategorycost, $categorycostparams)) {
            foreach ($categorycosts as $cost) {
            	
                if ($cost->cost == null || $cost->cost == 0) {
                    $coursecategory = $cost->parent;
                    $noinfloop ++;
                }else{
                	$categorycost = $cost->cost;
                	return $categorycost;
                }if ($cost->parent == 0) {
                    $categorycost = $CFG->emarking_defaultcost;
                    return $categorycost;
                }
              
            }
        }
    }
}