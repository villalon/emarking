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
 * This page processes a zip file containing scanned answers from students
 * that were already identified using the emarking desktop tool
 * 
 * @package mod
 * @subpackage emarking
 * @copyright 2012 Jorge Villalon <villalon@gmail.com>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('NO_OUTPUT_BUFFERING', true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once("$CFG->dirroot/lib/weblib.php");
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/mod/emarking/marking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/print/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
global $DB, $CFG, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$submissions = required_param_array('publish', PARAM_INTEGER);
// Validate user is logged in and is not guest.
require_login($course->id);
if (isguestuser()) {
    die();
}
$url = new moodle_url('/mod/emarking/marking/publish.php', array(
    'id' => $cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_popup_notification_allowed(false);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('publishtitle', 'mod_emarking'));
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
// Create progress bar.
$pbar = new progress_bar('publish', 500, true);
emarking_calculate_grades_users($emarking);
// Count documents ignored and processed.
$totaldocumentsprocessed = 0;
$totaldocumentsignored = 0;
$totalsubmissions = count($submissions);
foreach ($submissions as $submissionid) {
    if (! $draft = $DB->get_record('emarking_draft', array(
        'id' => $submissionid))) {
        $totaldocumentsignored ++;
        continue;
    }
    if (! $submission = $DB->get_record('emarking_submission', array(
        'id' => $draft->submissionid))) {
        $totaldocumentsignored ++;
        continue;
    }
    if (! $student = $DB->get_record('user', array(
        'id' => $submission->student))) {
        $totaldocumentsignored ++;
        continue;
    }
    if (emarking_create_response_pdf($draft, $student, $context, $cm->id)) {
        $totaldocumentsprocessed ++;
        $pbar->update($totaldocumentsprocessed, $totalsubmissions, get_string('publishinggrade', 'mod_emarking') .
                " " . $draft->id);
        if (! emarking_publish_grade($draft)) {
            echo $OUTPUT->notification("Fatal error publishing grade student: " . $student->id . " draft: " . $draft->id,
                    'notifyproblem');
        }
    } else {
        $totaldocumentsignored ++;
    }
}
$pbar->update_full(100, get_string('publishinggradesfinished', 'mod_emarking'));
$percentage = 0;
if ($totaldocumentsprocessed > 0) {
    $percentage = round((($totaldocumentsprocessed - $totaldocumentsignored) / $totaldocumentsprocessed) * 100, 2);
}
$table = new html_table();
$table->attributes ['style'] = "width: 500px; margin-left:auto; margin-right:auto;";
$table->head = array(
    get_string('results', 'mod_emarking'),
    '&nbsp;');
$table->data [] = array(
    get_string('publishedgrades', 'mod_emarking'),
    $totaldocumentsprocessed . " ($percentage%)");
$table->data [] = array(
    get_string('errors', 'mod_emarking'),
    $totaldocumentsignored);
echo "<br/>";
echo html_writer::table($table);
$continueurl = new moodle_url('/mod/emarking/view.php', array(
    'id' => $cm->id));
echo $OUTPUT->continue_button($continueurl);
echo $OUTPUT->footer();