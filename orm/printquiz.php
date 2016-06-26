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
 * This file keeps track of upgrades to the emarking module
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations.
 * The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do. The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2013-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
@ini_set('max_execution_time', 0);
define('NO_OUTPUT_BUFFERING', true);

require_once (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once ($CFG->libdir . '/pdflib.php');
require_once ($CFG->dirroot . '/mod/assign/feedback/editpdf/fpdi/fpdi.php');
require_once ($CFG->dirroot . '/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php');
require_once ($CFG->dirroot . "/mod/emarking/lib/openbub/ans_pdf_open.php");
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
require_once ("locallib.php");

$CFG->debugdisplay = 0;
global $DB, $USER;

$cmid = required_param('cmid', PARAM_INT);
$answersheetsonly = optional_param('answers', false, PARAM_BOOL);
$debug = optional_param('debug', false, PARAM_BOOL);

if (! $cm = get_coursemodule_from_id('quiz', $cmid)) {
    print_error('Invalid cm id');
}

$context = context_module::instance($cm->id);

if (! $course = $DB->get_record('course', array(
    'id' => $cm->course
))) {
    print_error('Invalid course');
}

if (! $quiz = $DB->get_record('quiz', array(
    'id' => $cm->instance
))) {
    print_error('Invalid quiz');
}

$url = new moodle_url('/mod/emarking/print/printquiz.php', array(
    'cmid' => $cmid
));

// We set up the page: context, course, url, navigation, heading and layout
$PAGE->set_context($context);
$PAGE->set_course($course);
if ($cmid > 0)
    $PAGE->set_cm($cm);
$PAGE->set_url($url);
$PAGE->navbar->add(get_string('printquiz', 'mod_emarking'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

emarking_verify_logo();

if ($debug) {
    emarking_create_quiz_pdf($cm, $debug, $context, $course, $answersheetsonly, null);
    die();
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('creatingquiz','mod_emarking') . ' ' . $quiz->name);
$downloadurl = emarking_create_quiz_pdf($cm, $debug, $context, $course, $answersheetsonly, true);
if($downloadurl) {
    echo $OUTPUT->notification(get_string('pdfquizcreated', 'mod_emarking'), 'notifysuccess');
    echo $OUTPUT->action_link($downloadurl, 'Download file');
}
echo $OUTPUT->footer();