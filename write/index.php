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
 * @copyright 2018 Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ("../lib.php");
require_once ("../activities/locallib.php");
require_once ("../locallib.php");
require_once ('../forms/write_form.php');
require_once($CFG->libdir . '/formslib.php');
require_once ($CFG->libdir . '/pdflib.php');
require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
global $USER, $OUTPUT, $DB, $CFG, $PAGE;
// Obtains basic data from cm id.
list ($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// Check that user is logued in the course.
require_login($course->id);
if (isguestuser()) {
    die();
}
$urlemarking = new moodle_url("/mod/emarking/write/index.php", array('id'=>$cm->id));
$activity = null;
if($usedactivity = $DB->get_record('emarking_used_activities', array('emarkingid'=>$emarking->id))) {
    $activity = $DB->get_record('emarking_activities', array('id'=>$usedactivity->activityid));
}

// Page navigation and URL settings.
$PAGE->set_url($urlemarking);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout(emarking_get_layout());
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
// Require jquery for modal.
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$mform = new emarking_write_form(null, array('cmid'=>$cm->id));
if($mform->get_data()) {
    $html = $mform->get_data()->mytextfield['text'];
    $pdf = emarking_create_student_pdf($html, $emarking, $course);
    $tempdir = emarking_get_temp_dir_path($cm->id);
    if (!file_exists($tempdir)) {
        emarking_initialize_directory($tempdir, true);
    }
    $pdffilename=emarking_clean_filename($USER->lastname.$USER->firstname).'.pdf';
    $pathname = $tempdir . '/' . $pdffilename;
    if (@file_exists($pathname)) {
        unlink($pathname);
    }
    $numpages = $pdf->getNumPages();
    $pdf->Output($pathname, 'F');
    $result = emarking_upload_answers($emarking, $pathname, $course, $cm, false, false, $USER);
    echo $OUTPUT->header();
    echo $OUTPUT->notification('Texto subido', 'notifysuccess');
    echo $OUTPUT->footer();    
    die;
}

echo $OUTPUT->header();
?>
<style>
.mform .fitem .felement {
    float: none;
    width: 100%;
}
.mform .fitem .fitemtitle {
    float: none;
    width: 100%;
    text-align: left;
}
</style>
<?php

if($activity) {
    echo $OUTPUT->heading('Instrucciones', 4);
    echo $OUTPUT->box($activity->instructions);
    echo $OUTPUT->heading('Planificación', 4);
    echo $OUTPUT->box($activity->planification);
    echo $OUTPUT->heading('Escritura', 4);
    echo $OUTPUT->box($activity->writing);
}
$mform->display();
echo $OUTPUT->footer();
