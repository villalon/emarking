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
 * @copyright 2016-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once('forms/gradereport_form.php');
global $DB, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// URLs for current page.
$url = new moodle_url('/mod/emarking/reports/preview.php', array(
    'id' => $cm->id));
$filter = required_param('filter', PARAM_ALPHA);
$fids = required_param('fids', PARAM_RAW_TRIMMED);
if($filter === 'tag') {
    $fids = urldecode($fids);
    $title = get_string('viewfeedback', 'mod_emarking') . ' ' . $fids;
} elseif($filter === 'level') {
    $fids = intval($fids);
    if(!$level = $DB->get_record('gradingform_rubric_levels', array('id'=>$fids))) {
        print_error('Invalid level id');
    }
    if(!$criterion = $DB->get_record('gradingform_rubric_criteria', array('id'=>$level->criterionid))) {
        print_error('Invalid criterion id');
    }
    $title = $criterion->description .  '<br/>' . $level->definition;
} else {
    print_error('Invalid filter');
}
// First check that the user is logged in.
require_login($course->id);
if (isguestuser()) {
    die();
}
// Validate the user has grading capabilities.
require_capability('mod/emarking:grade', $context);
// Page settings (URL, breadcrumbs and title).
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_url($url);
$PAGE->set_pagelayout('popup');
$PAGE->navbar->add(get_string('feedbackreport', 'mod_emarking'));
// Require jquery for modal.
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name . '<br/>' . core_text::strtotitle($title));
$totalsubmissions = $DB->count_records_sql(
        "
                SELECT COUNT(DISTINCT s.id) AS total
                FROM {emarking_submission} s
                INNER JOIN {emarking_draft} d
                    ON (s.emarking = :emarking AND d.status >= " .
                 EMARKING_STATUS_PUBLISHED . " AND d.submissionid = s.id AND d.grade > 0 AND d.qualitycontrol=0)
                ", array(
                    'emarking' => $emarking->id));
if (! $totalsubmissions || $totalsubmissions == 0) {
    echo $OUTPUT->notification(get_string('nosubmissionspublished', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}
$sqlfilter = $filter === 'tag' ?
    "AND c.rawtext LIKE '%$fids%' AND LENGTH(c.rawtext) > 0" :
    "AND c.levelid > 0 AND c.levelid = " . $fids;
$sql = "SELECT d.id AS did,
	d.submissionid AS sid,
	p.file,
    p.fileanonymous,
    c.posx,
    c.posy,
    c.rawtext,
    c.levelid,
    l.definition,
    a.description
FROM {emarking_draft} d
INNER JOIN {emarking_page} p ON (p.submission = d.submissionid)
INNER JOIN {emarking_comment} c ON (d.emarkingid = :emarkingid AND c.page = p.id
    $sqlfilter)
LEFT JOIN {gradingform_rubric_levels} l ON (c.levelid > 0 AND c.levelid = l.id)
LEFT JOIN {gradingform_rubric_criteria} a ON (l.criterionid = a.id)
";
$emarkingstats = $DB->get_recordset_sql($sql, array('emarkingid'=>$emarking->id));
$fs = get_file_storage();
echo "
        <style>
        .c0 { width: 800px; }
        .feedbackmarker {
        color: #023D56;
        padding: 5px;
        background-color: #B5C689;
        opacity: 1.0;
        filter: alpha(opacity=100);
        max-width: 230px;
        }
        .feedbackmarker:hover {
        opacity: 0.5;
        filter: alpha(opacity=40);
        }
        .generaltable {
        width: auto;
        }
        </style>
    ";
$pages = array();
foreach($emarkingstats as $stat) {
    if(!isset($pages[$stat->file])) {
        $pages[$stat->file] = array();
    }
    $pages[$stat->file][] = $stat; 
}
$table = new html_table();
$table->head = array('');
$table->data = array();
$current = 0;
foreach($pages as $file => $stat) {
    $fileinfo = $fs->get_file_by_id($file);
    $imageinfo = $fileinfo->get_imageinfo();
    $imagehtml = html_writer::start_div('', array('style'=>'position:relative;width:100%;border:solid 1px black;'));
    $width = 800;
    $height = intval(800 / $imageinfo['width'] * $imageinfo['height']);
    $imagehtml .= '<canvas id="c'.$current.'" width="'.$width.'px" height="'.$height.'px" 
            style="background:url('.$CFG->wwwroot.'/pluginfile.php/'.$fileinfo->get_contextid().'/mod_emarking/pages/'.$fileinfo->get_itemid().'/'.$fileinfo->get_filename().'?r='.random_string(5).'); background-size:cover;"></canvas>';
    foreach($stat as $text) {
    $feedback = $text->levelid > 0 ?
        $text->description . '<br/>' . $text->definition . '<br/>' . $text->rawtext :
        $text->rawtext;
    $posx = round($width * $text->posx,0);
    $posy = round($height * $text->posy,0);
    $imagehtml .= html_writer::div($feedback, 'feedbackmarker', array('style'=>'position:absolute;top:'.$posy.'px;left:'.$posx.'px;'));
    }
    $imagehtml .= html_writer::end_div();
    $table->data[] = array($imagehtml);
    $current++;
}
echo html_writer::table($table);
echo $OUTPUT->footer();