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
 * This is a one-line short description of the file
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2014-2015 Jorge Villalon (villalon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/lib.php');
require_once($CFG->dirroot . '/mod/emarking/forms/import_excel_form.php');
require_once($CFG->libdir . '/csvlib.class.php');
global $USER, $OUTPUT, $DB, $CFG, $PAGE;
$id = required_param('id', PARAM_INT);
if(!$activity = $DB->get_record('emarking_activities', array('id'=>$id))) {
	print_error('Id de actividad inválido');
}
// Import rubric URL.
$url = new moodle_url('/mod/emarking/activities/importrubric.php', array(
    'id' => $id));
// Emarking URL.
$urlactivity = new moodle_url('/mod/emarking/activities/activity.php', array(
    'id' => $id));
require_login();
if (isguestuser()) {
    die();
}
$context = context_system::instance();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add($activity->title, $urlactivity);
$PAGE->navbar->add(get_string('importrubric', 'mod_emarking'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('importrubric', 'mod_emarking') . ' actividad:' . $activity->title);
// Form Display.
$importrubricform = new emarking_import_excel_form(null, array(
    'cmid' => $id));
if ($importrubricform->get_data()) {
    // Use csv importer from Moodle.
    $iid = csv_import_reader::get_new_iid('emarking-import-rubric');
    $reader = new csv_import_reader($iid, 'emarking-import-rubric');
    $content = $importrubricform->get_data()->comments;
    $reader->load_csv_content($content, 'utf8', 'tab');
    $columns = array();
    $columns [] = html_writer::div(get_string('criterion', 'mod_emarking'));
    $columns [] = html_writer::div(get_string('rubriclevel', 'mod_emarking'));
    $data = array();
    $ignoredcolumns = $reader->get_columns();
    if (! isset($importrubricform->get_data()->headers)) {
        $data [] = emarking_table_from_line($ignoredcolumns);
        $definitiondata [] = $ignoredcolumns;
    }
    $reader->init();
    $current = 1;
    while ( $line = $reader->next() ) {
        $data [] = emarking_table_from_line($line);
        $definitiondata [] = $line;
        $current ++;
    }
    // Import was confirmed, now create the rubric definition.
    if (isset($_REQUEST ['submitbutton']) && $_REQUEST ['submitbutton'] === get_string('confirm')) {
        $definition = new stdClass();
        $definition->returnurl = $url;
        $definition->name = 'Imported rubric';
        $definition->description_editor = array(
            'text' => '',
            'format' => 1);
        $definition->rubric ['criteria'] = array();
        $definition->status = 20;
        $definition->saverubric = null;
        $current = 0;
        $newlevelid = 0;
        $newlevelidpts = 0;
        foreach ($definitiondata as $row) {
                $criterion = array();
                $criterion ['sortorder'] = count($definition->rubric ['criteria']) + 1;
                $criterion ['description'] = $row [0];
                $criterion ['levels'] = array();
                for ($i = 1; $i < count($row); $i ++) {
                    $level = array();
                    $level ['definition'] = $row [$i];
                    $level ['score'] = 0;
                    $levelid = 'NEWID' . $newlevelid;
                    if (! empty(trim($row [$i]))) {
                    	$criterion ['levels'] [$levelid] = $level;
                        $newlevelid ++;
                    }
                }
                $i = 0;
                foreach ($criterion ['levels'] as $lvlid => $lvl) {
                	$score = count($criterion ['levels']) - $i;
                	$criterion ['levels'][$lvlid]['score'] = $score;
                	$i++;
                }
                $criterionid = count($definition->rubric ['criteria']) + 1;
                $definition->rubric ['criteria'] ['NEWID' . $criterionid] = $criterion;
            $current ++;
        }
        $transaction = $DB->start_delegated_transaction();
        $rubric = new stdClass();
        $rubric->name = get_string('rubric', 'gradingform_rubric') . ' ' . $activity->title;
        $rubric->description = '';
        $rubric->usercreated = $USER->id;
        $rubric->timecreated = time();
        $rubric->usermodified = $USER->id;
        $rubric->timemodified = time();
        $rubric->id = $DB->insert_record('emarking_rubrics', $rubric);
        foreach($definition->rubric['criteria'] as $crit) {
        	$criterion = new stdClass();
        	$criterion->rubricid = $rubric->id;
        	$criterion->description = $crit['description'];
        	$criterion->id = $DB->insert_record('emarking_rubrics_criteria', $criterion);
        	foreach($crit['levels'] as $lvlid => $lvl) {
        		$level = new stdClass();
        		$level->criterionid = $criterion->id;
        		$level->score = $lvl['score'];
        		$level->definition = $lvl['definition'];
        		$level->id = $DB->insert_record('emarking_rubrics_levels', $level);
        	}
        }
        $activity->rubricid = $rubric->id;
        $DB->update_record('emarking_activities', $activity);
        $DB->commit_delegated_transaction($transaction);
        echo $OUTPUT->notification(get_string('changessaved', 'mod_emarking'), 'notifysuccess');
        echo $OUTPUT->single_button($urlactivity, get_string('continue'), 'GET');
    } else {
        echo $OUTPUT->notification(get_string('confirmimport', 'mod_emarking'), 'notifymessage');
        for ($i = 0; $i < count($data); $i ++) {
            for ($j = 0; $j < count($data [$i] [1]->data [0]); $j ++) {
                $data [$i] [1]->data [0] [$j] = html_writer::div($data [$i] [1]->data [0] [$j], 'level-wrapper');
            }
            $data [$i] [1] = html_writer::table($data [$i] [1]);
        }
        $table = new html_table();
        $table->attributes ['class'] = 'criteria';
        $table->id = 'rubric-criteria';
        $table->data = $data;
        $table->head = $columns;
        $table->colclasses = array(
            'description',
            'levels');
        for ($i = 0; $i < count($data); $i ++) {
            $table->rowclasses [$i] = 'criterion' . ($i % 2 == 0 ? ' even' : ' odd');
        }
        echo html_writer::div(html_writer::table($table), 'gradingform_rubric');
        $importrubricform->add_action_buttons(true, get_string('confirm'));
        $importrubricform->display();
        echo '<style>.hidden #fitem_id_ {display:none;}</style>';
    }
} else {
    // Action buttons.
    $importrubricform->add_action_buttons(true, get_string('submit'));
    $importrubricform->display();
}
echo $OUTPUT->footer();
function emarking_table_from_line($line) {
    $levelstable = new html_table();
    $levelstable->attributes ['class'] = 'none';
    $levelstable->data = array();
    $levelstable->data [0] = array();
    $levelstable->size = array();
    $levelstable->colclasses = array();
    for ($i = 1; $i < count($line); $i ++) {
        if(strlen(trim($line [$i])) > 0) {
        $levelstable->data [0] [] = html_writer::div($line [$i], 'definition');
        $levelstable->size [] = round(100 / (count($line) - 1), 1) . '%';
        $levelstable->colclasses [] = 'level';
        }
    }
    $levelstable->rowclasses[0] = null;
    return array(
        $line [0],
        $levelstable);
}
function emarking_extract_rubric_points($cell) {
    $value = $cell;
    $pattern = '/^(\d+[\.,]?\d*)\D*/';
    if (preg_match($pattern, $value, $matches)) {
        return $matches [1];
    } else {
        return - 1;
    }
}