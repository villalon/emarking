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
 * Library of interface functions and constants for module emarking
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the emarking specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package mod_emarking
 * @copyright 2013-2015 Jorge Villalón
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
// EMarking type.
define('EMARKING_TYPE_PRINT_ONLY', 0);
define('EMARKING_TYPE_NORMAL', 1);
define('EMARKING_TYPE_MARKER_TRAINING', 2);
define('EMARKING_TYPE_STUDENT_TRAINING', 3);
define('EMARKING_TYPE_PEER_REVIEW', 4);
define('EMARKING_TYPE_PRINT_SCAN', 5);
// Print orders status.
define('EMARKING_EXAM_UPLOADED', 1);
define('EMARKING_EXAM_SENT_TO_PRINT', 2);
define('EMARKING_EXAM_PRINTED', 3);
// Anonymous definitions.
define('EMARKING_ANON_STUDENT', 0);
define('EMARKING_ANON_BOTH', 1);
define('EMARKING_ANON_NONE', 2);
define('EMARKING_ANON_MARKER', 3);
// Justice perception status.
define('EMARKING_JUSTICE_DISABLED', 0);
define('EMARKING_JUSTICE_PER_SUBMISSION', 2);
define('EMARKING_JUSTICE_PER_CRITERION', 3);
// Grading status.
define('EMARKING_STATUS_MISSING', 0); // Not submitted.
define('EMARKING_STATUS_ABSENT', 5); // Absent.
define('EMARKING_STATUS_SUBMITTED', 10); // Submitted.
define('EMARKING_STATUS_GRADING', 15); // Feedback generated.
define('EMARKING_STATUS_PUBLISHED', 20); // Feedback generated.
define('EMARKING_STATUS_REGRADING', 30); // Student did not accept. asked for regrade.
define('EMARKING_STATUS_REGRADING_RESPONDED', 35); // Regrades were processed.
define('EMARKING_STATUS_ACCEPTED', 40); // Student accepted the submission.
// Regrade motives.
define('EMARKING_REGRADE_MISASSIGNED_SCORE', 1);
define('EMARKING_REGRADE_UNCLEAR_FEEDBACK', 2);
define('EMARKING_REGRADE_STATEMENT_PROBLEM', 3);
define('EMARKING_REGRADE_ERROR_CARRIED_FORWARD', 4);
define('EMARKING_REGRADE_CORRECT_ALTERNATIVE_ANSWER', 5);
define('EMARKING_REGRADE_OTHER', 10);
// Answer key status.
define('EMARKING_ANSWERKEY_NONE', 0);
define('EMARKING_ANSWERKEY_REQUESTED', 1);
define('EMARKING_ANSWERKEY_REJECTED', 2);
define('EMARKING_ANSWERKEY_ACCEPTED', 3);
// Moodle core API.
/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature
 *            FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function emarking_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE :
            return true;
        case FEATURE_ADVANCED_GRADING :
            return true;
        case FEATURE_GRADE_OUTCOMES :
            return true;
        case FEATURE_RATE :
            return false;
        default :
            return null;
    }
}
/**
 * Saves a new instance of the emarking into the database
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $emarking
 *            An object from the form in mod_form.php
 * @param mod_emarking_mod_form $mform
 * @return int The id of the newly inserted emarking record
 */
function emarking_add_instance(stdClass $data, mod_emarking_mod_form $mform = null) {
    global $DB, $CFG, $COURSE, $USER;
    $data->timecreated = time();
    $id = $DB->insert_record('emarking', $data);
    $data->id = $id;
    emarking_grade_item_update($data);
    if ($data->type == EMARKING_TYPE_MARKER_TRAINING || $data->type == EMARKING_TYPE_PEER_REVIEW || ! $mform) {
        return $id;
    }
    foreach ($data as $k => $v) {
        $parts = explode('-', $k);
        if (count($parts) > 1 && $parts [0] === 'marker') {
            $markerid = intval($parts [1]);
            $marker = new stdClass();
            $marker->emarking = $id;
            $marker->marker = $markerid;
            $marker->qualitycontrol = 1;
            $DB->insert_record('emarking_markers', $marker);
        }
    }
    $context = context_course::instance($COURSE->id);
    $examid = 0;
    // If there's no previous exam to associate, and we are creating a new
    // EMarking, we need the PDF file.
    if ($data->exam == 0) {
        // We get the draftid from the form.
        $draftid = file_get_submitted_draft_itemid('exam_files');
        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftid);
        $tempdir = emarking_get_temp_dir_path($COURSE->id);
        emarking_initialize_directory($tempdir, true);
        $numpagesprevious = - 1;
        $exampdfs = array();
        foreach ($files as $uploadedfile) {
            if ($uploadedfile->get_mimetype() !== 'application/pdf') {
                continue;
            }
            $filename = $uploadedfile->get_filename();
            $filename = emarking_clean_filename($filename);
            $newfilename = $tempdir . '/' . $filename;
            $pdffile = emarking_get_path_from_hash($tempdir, $uploadedfile->get_pathnamehash());
            // Executes pdftk burst to get all pages separated.
            $numpages = emarking_pdf_count_pages($newfilename, $tempdir, false);
            $exampdfs [] = array(
                'pathname' => $pdffile,
                'filename' => $filename);
        }
    } else {
        $examid = $data->exam;
    }
    $studentsnumber = emarking_get_students_count_for_printing($COURSE->id);
    // A new exam object is created and its attributes filled from form data.
    if ($examid == 0) {
        $exam = new stdClass();
        $exam->course = $COURSE->id;
        $exam->courseshortname = $COURSE->shortname;
        $exam->name = $mform->get_data()->name;
        $exam->examdate = $mform->get_data()->examdate;
        $exam->emarking = $id;
        $exam->headerqr = isset($mform->get_data()->headerqr) ? 1 : 0;
        $exam->printrandom = isset($mform->get_data()->printrandom) ? 1 : 0;
        $exam->printlist = isset($mform->get_data()->printlist) ? 1 : 0;
        $exam->extrasheets = $mform->get_data()->extrasheets;
        $exam->extraexams = $mform->get_data()->extraexams;
        $exam->usebackside = isset($mform->get_data()->printdoublesided) ? 1 : 0;
        if ($examid == 0) {
            $exam->timecreated = time();
        }
        $exam->timemodified = time();
        $exam->requestedby = $USER->id;
        $exam->totalstudents = $studentsnumber;
        $exam->comment = $mform->get_data()->comment;
        // Get the enrolments as a comma separated values.
        $enrollist = array();
        if (! empty($mform->get_data()->enrolments)) {
            $enrolments = $mform->get_data()->enrolments;
            foreach ($enrolments as $key => $enrolment) {
                if (! empty($enrolment)) {
                    $enrollist [] = $key;
                }
            }
        }
        $exam->enrolments = implode(",", $enrollist);
        $exam->printdate = 0;
        $exam->status = EMARKING_EXAM_UPLOADED;
        // Calculate total pages for exam.
        $exam->totalpages = $numpages;
        $exam->printingcost = emarking_get_category_cost($COURSE->id);
        $exam->id = $DB->insert_record('emarking_exams', $exam);
        foreach ($exampdfs as $exampdf) {
            // Save the submitted file to check if it's a PDF.
            $filerecord = array(
                'component' => 'mod_emarking',
                'filearea' => 'exams',
                'contextid' => $context->id,
                'itemid' => $exam->id,
                'filepath' => '/',
                'filename' => $exampdf ['filename']);
            $file = $fs->create_file_from_pathname($filerecord, $exampdf ['pathname']);
        }
        // Update exam object to store the PDF's file id.
        $exam->file = $file->get_id();
        if (! $DB->update_record('emarking_exams', $exam)) {
            $fs->delete_area_files($contextid, 'emarking', 'exams', $exam->id);
            print_error(get_string('errorsavingpdf', 'mod_emarking'));
        }
        // Send new print order notification.
        emarking_send_newprintorder_notification($exam, $COURSE);
        // If it is a multi-course submission, insert several exams.
        if (! empty($mform->get_data()->multicourse)) {
            $multicourse = $mform->get_data()->multicourse;
            foreach ($multicourse as $key => $mcourse) {
                if (! empty($key)) {
                    if ($thiscourse = $DB->get_record('course', array(
                        'shortname' => $key))) {
                        $studentsnumber = emarking_get_students_count_for_printing($thiscourse->id);
                        list($newemarkingid, $newcmid, $sectionid) = emarking_copy_to_cm($data, $thiscourse->id);
                        $newexam = $exam;
                        $newexam->id = null;
                        $newexam->totalstudents = $studentsnumber;
                        $newexam->course = $thiscourse->id;
                        $newexam->courseshortname = $thiscourse->shortname;
                        $newexam->emarking = $newemarkingid;
                        $newexam->id = $DB->insert_record('emarking_exams', $newexam);
                        $thiscontext = context_course::instance($thiscourse->id);
                        // Create file records for all new exams.
                        foreach ($exampdfs as $exampdf) {
                            // Save the submitted file to check if it's a PDF.
                            $filerecord = array(
                                'component' => 'mod_emarking',
                                'filearea' => 'exams',
                                'contextid' => $thiscontext->id,
                                'itemid' => $newexam->id,
                                'filepath' => '/',
                                'filename' => $exampdf ['filename']);
                            $file = $fs->create_file_from_pathname($filerecord, $exampdf ['pathname']);
                        }
                        // Send new print order notification.
                        emarking_send_newprintorder_notification($newexam, $thiscourse);
                    }
                }
            }
        }
    } else {
        $exam = $DB->get_record("emarking_exams", array(
            "id" => $examid));
        $exam->emarking = $id;
        $exam->timemodified = time();
        $DB->update_record('emarking_exams', $exam);
    }
    $headerqr = isset($mform->get_data()->headerqr) ? 1 : 0;
    setcookie("emarking_headerqr", $headerqr, time() + 3600 * 24 * 365 * 10, '/');
    $defaultexam = new stdClass();
    $defaultexam->headerqr = $exam->headerqr;
    $defaultexam->printrandom = $exam->printrandom;
    $defaultexam->printlist = $exam->printlist;
    $defaultexam->extrasheets = $exam->extrasheets;
    $defaultexam->extraexams = $exam->extraexams;
    $defaultexam->usebackside = $exam->usebackside;
    $defaultexam->enrolments = $exam->enrolments;
    setcookie("emarking_exam_defaults", json_encode($defaultexam), time() + 3600 * 24 * 365 * 10, '/');
    return $id;
}
/**
 * Creates a copy of the emarking in the database.
 *
 * @param unknown $originalemarking
 * @return boolean|multitype:unknown NULL Ambigous <boolean, number>
 */
function emarking_copy_to_cm($originalemarking, $destinationcourse) {
    global $CFG, $DB;
    require_once($CFG->dirroot . "/course/lib.php");
    require_once($CFG->dirroot . "/mod/emarking/mod_form.php");
    $emarkingmod = $DB->get_record('modules', array(
        'name' => 'emarking'));
    $emarking = new stdClass();
    $emarking = $originalemarking;
    $emarking->id = null;
    $emarking->course = $destinationcourse;
    $emarking->id = emarking_add_instance($emarking);
    // Add coursemodule.
    $mod = new stdClass();
    $mod->course = $destinationcourse;
    $mod->module = $emarkingmod->id;
    $mod->instance = $emarking->id;
    $mod->section = 0;
    $mod->visible = 0; // Hide the forum.
    $mod->visibleold = 0; // Hide the forum.
    $mod->groupmode = 0;
    $mod->grade = 100;
    if (! $cmid = add_course_module($mod)) {
        return false;
    }
    $sectionid = course_add_cm_to_section($mod->course, $cmid, 0);
    return array(
        $emarking->id,
        $cmid,
        $sectionid);
}
/**
 * Updates an instance of the emarking in the database
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object|\stdClass $emarking
 *            An object from the form in mod_form.php
 * @param mod_emarking_mod_form $mform
 * @return boolean Success/Fail
 */
function emarking_update_instance(stdClass $emarking, mod_emarking_mod_form $mform = null) {
    global $DB, $CFG, $COURSE;
    if ($emarking->type != EMARKING_TYPE_MARKER_TRAINING) {
        // If there is NO exam for the emarking activity and the user selected she
        // wouldn't use a previous exam there is something wrong.
        if ((! $exam = $DB->get_record("emarking_exams", array(
            "emarking" => $emarking->instance))) && $mform->get_data()->exam == 0) {
            return false;
        }
        // If there is NO exam with the id selected by the user there is something wrong
        // (When the emarking already has an exam, the data comes in a hidden field.
        if (! $exam = $DB->get_record("emarking_exams", array(
            "id" => $mform->get_data()->exam))) {
            return false;
        }
        // We update the exam row.
        $exam->name = $emarking->name;
        $exam->comment = $mform->get_data()->comment;
        $exam->emarking = $emarking->instance;
        $DB->update_record("emarking_exams", $exam);
    }
    if (! isset($mform->get_data()->linkrubric)) {
        $emarking->linkrubric = 0;
    }
    if (! isset($mform->get_data()->collaborativefeatures)) {
        $emarking->collaborativefeatures = 0;
    }
    if (! isset($mform->get_data()->enableduedate)) {
        $emarking->markingduedate = null;
    }
    if (! isset($mform->get_data()->enablescan)) {
        $emarking->enablescan = 0;
    }
    if (! isset($mform->get_data()->enableosm)) {
        $emarking->enableosm = 0;
    }
    $emarking->introformat = FORMAT_HTML;
    $emarking->timemodified = time();
    $emarking->id = $emarking->instance;
    $ret = $DB->update_record('emarking', $emarking);
    emarking_grade_item_update($emarking);
    return $ret;
}
/**
 * Removes an instance of the emarking from the database
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id
 *            Id of the module instance
 * @return boolean Success/Failure
 */
function emarking_delete_instance($id) {
    global $DB;
    if (! $emarking = $DB->get_record('emarking', array(
        'id' => $id))) {
        return false;
    }
    $exam = $DB->get_record("emarking_exams", array(
        "emarking" => $id));
    // If we have an associated exam that was already sent to print
    // we can't delete the e-marking activity.
    if ($exam && $exam->status >= EMARKING_EXAM_SENT_TO_PRINT) {
        throw new moodle_exception("emarking/cannotdeleteprintedemarking",
                "An already printed emarking activity can not be deleted.");
    }
    // Delete dependent records.
    $tran = $DB->start_delegated_transaction();
    if (! $DB->delete_records('emarking_marker_criterion', array(
        'emarking' => $emarking->id))) {
        $tran->rollback(new Exception('Could not delete marker_criterion objects'));
        return false;
    }
    if (! $DB->delete_records('emarking_markers', array(
        'emarking' => $emarking->id))) {
        $tran->rollback(new Exception('Could not delete markers objects'));
        return false;
    }
    if (! $DB->delete_records('emarking_predefined_comment', array(
        'emarkingid' => $emarking->id))) {
        $tran->rollback(new Exception('Could not delete predefined comment objects'));
        return false;
    }
    $submissions = $DB->get_records('emarking_submission', array(
        'emarking' => $emarking->id));
    foreach ($submissions as $submission) {
        if (! $DB->delete_records('emarking_perception', array(
            'submission' => $submission->id))) {
            $tran->rollback(new Exception('Could not delete perception objects'));
            return false;
        }
        $drafts = $DB->get_records('emarking_draft', array(
            'submissionid' => $submission->id));
        foreach ($drafts as $draft) {
            if (! $DB->delete_records('emarking_regrade', array(
                'draft' => $draft->id))) {
                $tran->rollback(new Exception('Could not delete regrade objects'));
                return false;
            }
            $pages = $DB->get_records('emarking_page', array(
                'submission' => $submission->id));
            foreach ($pages as $page) {
                if (! $DB->delete_records('emarking_page_criterion',
                        array(
                            'page' => $page->id,
                            'emarking' => $emarking->id))) {
                    $tran->rollback(new Exception('Could not delete page_criterion objects'));
                    return false;
                }
                if (! $DB->delete_records('emarking_comment',
                        array(
                            'page' => $page->id,
                            'draft' => $draft->id))) {
                    $tran->rollback(new Exception('Could not delete comment objects'));
                    return false;
                }
                if (! $DB->delete_records('emarking_page', array(
                    'id' => $page->id))) {
                    $tran->rollback(new Exception('Could not delete page object'));
                    return false;
                }
            }
            if (! $DB->delete_records('emarking_draft', array(
                'id' => $draft->id))) {
                $tran->rollback(new Exception('Could not delete draft object'));
                return false;
            }
        }
        if (! $DB->delete_records('emarking_submission', array(
            'id' => $submission->id))) {
            $tran->rollback(new Exception('Could not delete submission object'));
            return false;
        }
    }
    if (! $DB->delete_records('emarking_exams', array(
        'emarking' => $emarking->id))) {
        $tran->rollback(new Exception('Could not delete emarking_exam object'));
        return false;
    }
    if (! $DB->delete_records('emarking', array(
        'id' => $emarking->id))) {
        $tran->rollback(new Exception('Could not delete emarking object'));
        return false;
    }
    $tran->allow_commit();
    // We do not delete exams for DB purposes.
    return true;
}
/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param
 *            $course
 * @param
 *            $user
 * @param
 *            $mod
 * @param
 *            $emarking
 * @return stdClass|null
 */
function emarking_user_outline($course, $user, $mod, $emarking) {
    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}
/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course
 *            the current course record
 * @param stdClass $user
 *            the record of the user we are generating report for
 * @param cm_info $mod
 *            course module info
 * @param stdClass $emarking
 *            the module instance record
 * @return void, is supposed to echp directly
 */
function emarking_user_complete($course, $user, $mod, $emarking) {
}
/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in emarking activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function emarking_print_recent_activity($course, $viewfullnames, $timestart) {
    return false; // True if anything was printed, otherwise false.
}
/**
 * Prepares the recent activity data
 * This callback function is supposed to populate the passed array with
 * custom activity records.
 * These records are then rendered into HTML via
 * {@link emarking_print_recent_mod_activity()}.
 *
 * @param array $activities
 *            sequentially indexed array of objects with the 'cmid' property
 * @param int $index
 *            the index in the $activities to use for the next record
 * @param int $timestart
 *            append activity since this time
 * @param int $courseid
 *            the id of the course we produce the report for
 * @param int $cmid
 *            course module id
 * @param int $userid
 *            check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid
 *            check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function emarking_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
}
/**
 * Prints single activity item prepared by {@see emarking_get_recent_mod_activity()}
 *
 * @return void
 */
function emarking_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}
/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc .
 * ..
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function emarking_cron() {
    return true;
}
/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function emarking_get_extra_capabilities() {
    return array();
}
// Gradebook API.
/**
 * Is a given scale used by the instance of emarking?
 * This function returns if a scale is being used by one emarking
 * if it has support for grading and scales.
 * Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $emarkingid
 *            ID of an instance of this module
 * @return bool true if the scale is used by the given emarking instance
 */
function emarking_scale_used($emarkingid, $scaleid) {
    global $DB;
    return false;
}
/**
 * Checks if scale is being used by any instance of emarking.
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any emarking instance
 */
function emarking_scale_used_anywhere($scaleid) {
    return false;
}
/**
 * Creates or updates grade item for the give emarking instance
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $emarking
 *            instance object with extra cmidnumber and modname property
 * @param null $grades
 * @return int 0 if ok, error code otherwise
 */
function emarking_grade_item_update(stdClass $emarking, $grades = null) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    require_once($CFG->dirroot . '/mod/emarking/marking/locallib.php');
    if ($grades == null) {
        emarking_calculate_grades_users($emarking);
    }
    $params = array();
    $params ['itemname'] = clean_param($emarking->name, PARAM_NOTAGS);
    $params ['gradetype'] = GRADE_TYPE_VALUE;
    $params ['grademax'] = $emarking->grade;
    $params ['grademin'] = $emarking->grademin;
    if ($grades === 'reset') {
        $params ['reset'] = true;
        $grades = null;
    }
    $ret = grade_update('mod/emarking', $emarking->course, 'mod', 'emarking', $emarking->id, 0, $grades, $params);
    emarking_publish_all_grades($emarking);
    return $ret;
}
/**
 * Update emarking grades in the gradebook
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $emarking
 *            instance object with extra cmidnumber and modname property
 * @param int $userid
 *            update grade of specific user only, 0 means all participants
 * @param bool $nullifnone
 *            not used in emarking.
 * @return void
 */
function emarking_update_grades(stdClass $emarking, $userid = 0, $nullifnone = true) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    if ($emarking->grade == 0) {
        emarking_grade_item_update($emarking);
    } else if ($grades = emarking_get_user_grades($emarking, $userid)) {
        foreach ($grades as $k => $v) {
            $grades [$k]->rawgrade = null;
        }
        emarking_grade_item_update($emarking, $grades);
    } else {
        emarking_grade_item_update($emarking);
    }
}
/**
 * Get emarking grades in a format compatible with the gradebook
 *
 * @param
 *            $emarking
 * @param int $userid
 * @return array
 */
function emarking_get_user_grades($emarking, $userid = 0) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/grade/grading/lib.php');
    emarking_calculate_grades_users($emarking, $userid);
    $gradebookgrades = array();
    $params = array(
        'emarking' => $emarking->id);
    if ($userid > 0) {
        $params ['student'] = $userid;
    }
    $submissions = $DB->get_records('emarking_submission', $params);
    foreach ($submissions as $submission) {
        $gradebookgrade = new stdClass();
        $gradebookgrade->userid = $submission->student;
        $gradebookgrade->datesubmitted = $submission->timecreated;
        $gradebookgrade->rawgrade = $submission->grade;
        $gradebookgrade->usermodified = $submission->teacher;
        $gradebookgrade->dategraded = $submission->timemodified;
        $gradebookgrades [$submission->student] = $gradebookgrade;
    }
    return $gradebookgrades;
}
/**
 * Removes all grades from gradebook
 *
 * @param int $courseid
 *            The ID of the course to reset
 * @param string $type
 *            Optional type of assignment to limit the reset to a particular assignment type
 */
function emarking_reset_gradebook($courseid, $type = '') {
    global $CFG, $DB;
    $params = array(
        'moduletype' => 'emarking',
        'courseid' => $courseid);
    $sql = 'SELECT a.*, cm.idnumber as cmidnumber, a.course as courseid
            FROM {assign} a, {course_modules} cm, {modules} m
            WHERE m.name=:moduletype AND m.id=cm.module AND cm.instance=a.id AND a.course=:courseid';
    if ($emarkings = $DB->get_records_sql($sql, $params)) {
        foreach ($emarkings as $emarking) {
            emarking_grade_item_update($emarking, 'reset');
        }
    }
}
/**
 * Lists all gradable areas for the advanced grading methods framework
 *
 * @return array('string'=>'string') An array with area names as keys and descriptions as values
 */
function emarking_grading_areas_list() {
    return array(
        'attempt' => get_string('attempt', 'mod_emarking'));
}
// File API.
/**
 * Returns the lists of all browsable file areas within the given module context
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function emarking_get_file_areas($course, $cm, $context) {
    return array();
}
/**
 * File browsing support for emarking file areas
 *
 * @package mod_emarking
 * @category files
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function emarking_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}
/**
 * Serves the files from the emarking file areas
 *
 * @package mod_emarking
 * @category files
 * @param stdClass $course
 *            the course object
 * @param stdClass $cm
 *            the course module object
 * @param stdClass $context
 *            the emarking's context
 * @param string $filearea
 *            the name of the file area
 * @param array $args
 *            extra arguments (itemid, path)
 * @param bool $forcedownload
 *            whether or not force download
 * @param array $options
 *            additional options affecting the file serving
 */
function emarking_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()) {
    global $DB, $CFG, $USER;
    require_once($CFG->dirroot . '/mod/emarking/locallib.php');
    require_login();
    $filename = array_pop($args);
    $arg0 = array_pop($args);
    $contextcategory = context_coursecat::instance($course->category);
    $contextcourse = context_course::instance($course->id);
    // Security! We always protect the exams filearea.
    if ($filearea === 'exams') {
        send_file_not_found();
    }
    if ($filearea === 'pages') {
        $parts = explode('-', $filename);
        if (count($parts) != 3) {
            send_file_not_found();
        }
        if (! ($parts [0] === intval($parts [0]) . "") || ! ($parts [1] === intval($parts [1]) . "")) {
            send_file_not_found();
        }
        $subparts = explode('.', $parts [2]);
        $isanonymous = substr($subparts [0], - strlen('_a')) === '_a';
        $imageuser = intval($parts [0]);
        $usercangrade = has_capability('mod/emarking:grade', $context);
        $bothenrolled = is_enrolled($contextcourse) && is_enrolled($contextcourse, $imageuser);
        if ($USER->id != $imageuser && // If user does not owns the image.
! $usercangrade && // And can not grade.
! $isanonymous && // And we are not in anonymous mode.
! is_siteadmin($USER) && // And the user is not admin.
! $bothenrolled) {
            send_file_not_found();
        }
    }
    if ($filearea === 'response') {
        $parts = explode('_', $filename);
        if (count($parts) != 3) {
            send_file_not_found();
        }
        if (! ($parts [0] === "response") || ! ($parts [1] === intval($parts [1]) . "")) {
            send_file_not_found();
        }
        $subparts = explode('.', $parts [2]);
        $studentid = intval($subparts [0]);
        $emarkingid = intval($parts [1]);
        if (! $emarking = $DB->get_record('emarking', array(
            'id' => $emarkingid))) {
            send_file_not_found();
        }
        if ($studentid != $USER->id && ! is_siteadmin($USER) && ! has_capability('mod/emarking:supervisegrading', $context)) {
            send_file_not_found();
        }
    }
    $fs = get_file_storage();
    if (! $file = $fs->get_file($context->id, 'mod_emarking', $filearea, $arg0, '/', $filename)) {
        echo $context->id . ".." . $filearea . ".." . $arg0 . ".." . $filename;
        echo "File really not found";
        send_file_not_found();
    }
    send_file($file, $filename);
}
// Navigation API.
/**
 * Extends the global navigation tree by adding emarking nodes if there is a relevant content
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref
 *            An object representing the navigation tree node of the emarking module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function emarking_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}
/**
 * Extends the settings navigation with the emarking settings
 * This function is called when the context for the page is a emarking module.
 * This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav
 *            {@link settings_navigation}
 * @param navigation_node $emarkingnode
 *            {@link navigation_node}
 */
function emarking_extend_settings_navigation(settings_navigation $settingsnav, $emarkingnode) {
    global $PAGE, $DB, $USER, $CFG;
    $course = $PAGE->course;
    // Course context is used as this can work outside of the module.
    $context = $PAGE->context;
    if (is_siteadmin($USER) || (has_capability("mod/emarking:manageprinters", $context) && $CFG->emarking_enableprinting)) {
        $settingnode = $settingsnav->add(get_string('emarkingprints', 'mod_emarking'), null, navigation_node::TYPE_CONTAINER);
        $thingnode = $settingnode->add(get_string('adminprints', 'mod_emarking'),
                new moodle_url("/mod/emarking/print/printers.php", array(
                    'sesskey' => $USER->sesskey)));
        $thingnode = $settingnode->add(get_string('permitsviewprinters', 'mod_emarking'),
                new moodle_url("mod/emarking/print/usersprinters.php", array(
                    'sesskey' => $USER->sesskey)));
    }
}