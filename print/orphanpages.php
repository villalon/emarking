<?php
use core\session\exception;

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
 * @copyright 2012 Jorge Villalon <villalon@gmail.com>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
global $USER, $DB, $CFG;
require_once ($CFG->dirroot . "/repository/lib.php");
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
// Obtains basic data from cm id.
list ($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// Get the course module for the emarking, to build the emarking url.
$url = new moodle_url('/mod/emarking/print/orphanpages.php', array(
    'id' => $cm->id
));
$urlemarking = new moodle_url('/mod/emarking/view.php', array(
    'id' => $cm->id
));
// Check that user is logged in and is not guest.
require_login($course->id);
if (isguestuser()) {
    die();
}
require_capability('mod/emarking:uploadexam', $context);
$delete = optional_param('delete', 0, PARAM_INT);
// Set navigation parameters.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('orphanpages', 'mod_emarking'));
// Save uploaded file in Moodle filesystem and check.
$fs = get_file_storage();
// Display form for uploading zip file.
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), 'uploadanswers');
// Show orphan pages button
$orphanpages = emarking_get_digitized_answer_orphan_pages($context);
$numorphanpages = count($orphanpages);
$deletedsuccessfull = false;
if($delete > 0) {
    // TODO: Delete pages
}
if($deletedsuccessfull) {
    echo $OUTPUT->notification(get_string('transactionsuccessfull', 'mod_emarking'), 'notifysuccess');
}
if ($numorphanpages == 0) {
    echo $OUTPUT->notification(get_string('nodigitizedanswerfiles', 'mod_emarking'), 'notifyproblem');
} else {
    $table = new html_table();
    $table->attributes['style'] = 'display:table;';
    $table->head = array(
        get_string('filename', 'repository'),
        get_string('size'),
        'Mime type',
        get_string('uploaded', 'hub'),
        get_string('status', 'mod_emarking'),
        get_string('actions', 'mod_emarking')
    );
    foreach($orphanpages as $file) {
        if($file->is_directory()) {
            continue;
        }
        $actions = array();
        $deleteurl = new moodle_url('/mod/emarking/print/orphanpages.php', array('id'=>$cm->id, 'delete'=>$file->get_id()));
            $actions[] = $OUTPUT->action_icon($deleteurl, new pix_icon('i/delete', 'delete', null, array(
                'style' => 'width:1.5em;'
            )));
        $table->data[] = array(
            $file->get_filename(),
            display_size($file->get_filesize()),
            $file->get_mimetype(),
            emarking_time_ago($file->get_timecreated()),
            '',
            implode(' ', $actions)
        );
    }
    echo html_writer::table($table);
}
echo $OUTPUT->footer();