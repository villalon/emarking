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
 * @copyright 2012 Jorge Villalon <jorge.villalon@uai.cl>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once ($CFG->libdir . '/pdflib.php');
require_once ('../../../mod/assign/feedback/editpdf/fpdi/fpdi.php');
require_once ('../../../mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php');
require_once($CFG->dirroot."/mod/emarking/lib/openbub/ans_pdf_open.php");
require_once($CFG->dirroot."/mod/emarking/print/locallib.php");

$CFG->debugdisplay = 0;
global $DB, $USER;

$cmid = required_param('cmid', PARAM_INT);
$debug = optional_param('debug', false, PARAM_BOOL);

if (! $cm = get_coursemodule_from_id('quiz', $cmid)) {
    print_error('Invalid cm id');
}

$context = context_module::instance($cm->id);

if(!$course = $DB->get_record('course', array('id'=>$cm->course))) {
    print_error('Invalid course');
}

$url = new moodle_url('/mod/emarking/print/printquiz.php', array('cmid'=>$cmid));

// We set up the page: context, course, url, navigation, heading and layout
$PAGE->set_context($context);
$PAGE->set_course($course);
if($cmid > 0)
    $PAGE->set_cm($cm);
$PAGE->set_url($url);
$PAGE->navbar->add(get_string('emarking', 'mod_emarking'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

emarking_create_quiz_pdf($cm, $debug, $context, $course);

    // Create a new BubPdf object.
    $BubPdf = new BubPdf('P', 'in', 'LETTER', true);
    $BubPdf->SetPrintHeader(false);
    $BubPdf->SetPrintFooter(false);
    
    // NewExam sets the margins, etc
    BP_NewExam($BubPdf, $CorrectAnswersProvided = TRUE);
    
    BP_StudentAnswerSheetStart($BubPdf);
    
    // A simple 12 question exam
    for($i=1; $i<35; $i++) {
        BP_AddAnswerBubbles($BubPdf, 'A', 4, 1, FALSE, FALSE);
    }
    
    BP_StudentAnswerSheetComplete($BubPdf);
    
    // the CreateExam call can be used to retrieve an array of the zone assignments
    $myZones = BP_CreateExam($BubPdf);
    
$BubPdf->Output();

