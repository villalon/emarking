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
 * This page allows to enable scan in an emarking activity
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2012-onwards Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config.php");
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once($CFG->dirroot . "/mod/emarking/print/locallib.php");
global $DB, $USER, $CFG;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// The user confirmed enabling.
$newtype = required_param("type", PARAM_INT);
// The user confirmed enabling.
$confirm = optional_param("confirm", false, PARAM_BOOL);
// First check that the user is logged in.
require_login();
if (isguestuser()) {
    die();
}
$courseid = $cm->course;
// Ony users that can grade can see exams.
require_capability("mod/emarking:grade", $context);
if (! ($newtype == EMARKING_TYPE_PRINT_SCAN || $newtype == EMARKING_TYPE_NORMAL) ||
         ! ($emarking->type == EMARKING_TYPE_PRINT_ONLY || $emarking->type == EMARKING_TYPE_PRINT_SCAN) ||
         $emarking->type == $newtype) {
    print_error("Invalid parameters for enabling features");
}
// URLs for current page and redirects.
$url = new moodle_url("/mod/emarking/print/enablefeatures.php", array(
    "id" => $cm->id,
    "type" => $newtype));
$continue = new moodle_url("/mod/emarking/print/enablefeatures.php",
        array(
            "id" => $cm->id,
            "type" => $newtype,
            "confirm" => "true"));
$cancel = new moodle_url("/mod/emarking/view.php", array(
    "id" => $cm->id));
if ($confirm) {
    $emarking->type = $newtype;
    if (! $DB->update_record("emarking", $emarking)) {
        print_error("Error updating emarking activity");
    }
    $success = new moodle_url("/mod/emarking/view.php", array(
        "id" => $cm->id,
        "enabled" => $newtype));
    redirect($success);
    die();
}
// URL for adding a new print order.
$params = $cm->id > 0 ? array(
    "cm" => $cm->id) : array(
    "course" => $course->id);
// Label and title according to type.
$label = $newtype == EMARKING_TYPE_PRINT_SCAN ? "enablescan" : "enableosm";
$title = get_string($label, "mod_emarking");
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title(get_string("emarking", "mod_emarking"));
$PAGE->set_pagelayout("incourse");
$PAGE->navbar->add($title);
echo $OUTPUT->header();
// Heading and tabs if we are within a course module.
echo $OUTPUT->heading($emarking->name);
$emarking->message = core_text::strtolower($title);
echo $OUTPUT->box(get_string("updateemarkingtype", "mod_emarking", $emarking));
echo $OUTPUT->confirm(get_string("areyousure", "mod_emarking"), $continue, $cancel);
echo $OUTPUT->footer();