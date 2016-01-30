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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2014-2015 Jorge Villalon (villalon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/lib.php");
require_once ($CFG->dirroot . "/mod/emarking/forms/import_excel_form.php");
require_once ($CFG->libdir . '/csvlib.class.php');

global $USER, $OUTPUT, $DB, $CFG, $PAGE;

// Obtains basic data from cm id
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();

$commentid = optional_param('commentid', 0, PARAM_INT);

// Emarking URL
$urlemarking = new moodle_url('/mod/emarking/marking/predefinedcomments.php', array(
    'id' => $cm->id
));

require_login($course->id);
if (isguestuser()) {
    die();
}

$PAGE->set_url($urlemarking);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('predefinedcomments', 'mod_emarking'));

echo $OUTPUT->header();

echo $OUTPUT->heading($emarking->name);

// output of the tabtree
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "importrubric");

list ($gradingmanager, $gradingmethod, $definition, $rubriccontroller) = emarking_validate_rubric($context, false, true);

// Form Display
$predefinedform = new emarking_import_excel_form(null, array(
    'cmid' => $cm->id
));

if ($predefinedform->get_data()) {
    // Use csv importer from Moodle
    $iid = csv_import_reader::get_new_iid('emarking-predefined-comments');
    $reader = new csv_import_reader($iid, 'emarking-predefined-comments');
    $content = $predefinedform->get_data()->comments;
    $reader->load_csv_content($content, 'utf8', "tab");
    
    $columns = array();
    $columns[] = html_writer::div(get_string("criterion", "mod_emarking"));
    $columns[] = html_writer::div(get_string("rubriclevel", "mod_emarking"));
    
    $data = array();
    $definitiondata = array();
    
    $ignoredcolumns = $reader->get_columns();
    
    if (! isset($predefinedform->get_data()->headers)) {
        $data[] = emarking_table_from_line($ignoredcolumns);
        $definitiondata[] = $ignoredcolumns;
    }
    
    $reader->init();
    $current = 1;
    while ($line = $reader->next()) {
        $definitiondata[] = $line;
        if (count($line) > 0 && $current % 2 == 0) {
            $data[] = emarking_table_from_line($line);
        } else {
            for ($i = 1; $i < count($line); $i ++) {
                $points = floatval(emarking_extract_rubric_points($line[$i]));
                if ($points >= 0) {
                    $data[count($data) - 1][1]->data[0][$i - 1] .= html_writer::div($points . " " . get_string("points", "grades"), "score");
                }
            }
        }
        $current ++;
    }
    
    // Import was confirmed, now create the rubric definition
    if (isset($_REQUEST["submitbutton"]) && $_REQUEST["submitbutton"] === get_string("confirm")) {
        
        $definition = $rubriccontroller->get_definition_for_editing(true);
        
        $returnurl = $gradingmanager->get_management_url();
        
        $definition->returnurl = $returnurl;
        $definition->name = 'Imported rubric';
        $definition->description_editor = array("text"=>"", "format"=>1);
        $definition->rubric['criteria'] = array();
        $definition->status = 20;
        $definition->saverubric = NULL;

        $current = 0;
        $lastcriterion = NULL;
        $newlevelid = 0;
        foreach ($definitiondata as $row) {
            if ($current % 2 == 0) {
                $criterion = array();
                $criterion['sortorder'] = count($definition->rubric['criteria']) + 1;
                $criterion['description'] = $row[0];
                $criterion['levels'] = array();
                
                for ($i = 1; $i < count($row); $i ++) {
                    $level = array();
                    $level['score'] = 0;
                    $level['definition'] = $row[$i];
                    
                    if (! empty(trim($row[$i]))) {
                        $newlevelid++;
                        $criterion['levels']["NEWID".$newlevelid] = $level;
                    }
                }
                
                $criterionid = count($definition->rubric['criteria']) + 1;
                $definition->rubric['criteria']["NEWID".$criterionid] = $criterion;
                $lastcriterion = $criterion;
            } else {
                $criterion = $lastcriterion;
                for ($i = 1; $i < count($row); $i ++) {
                    $value = $row[$i];
                    $pattern = '/^(\d+[\.,]?\d*)\D*/';
                    if (preg_match($pattern, $value, $matches)) {
                        $value = $matches[1];
                    }
                    $criterionid = "NEWID".count($definition->rubric['criteria']);
                    $levelid = "NEWID".($newlevelid - count($row) + $i + 2);
                    if (isset($definition->rubric['criteria'][$criterionid]['levels'][$levelid])) {
                        $definition->rubric['criteria'][$criterionid]['levels'][$levelid]['score'] = "".floatval($value);
                    }
                }
            }
            
            $current ++;
        }
        
        $rubriccontroller->update_definition($definition);
        
        echo $OUTPUT->notification(get_string("changessaved", "mod_emarking"), "notifysuccess");
        echo $OUTPUT->single_button($gradingmanager->get_management_url(), get_string("continue"));
    } else {
        echo $OUTPUT->notification(get_string("onlyfirstcolumn", "mod_emarking"), "notifymessage");
        
        for ($i = 0; $i < count($data); $i ++) {
            for ($j = 0; $j < count($data[$i][1]->data[0]); $j ++) {
                $data[$i][1]->data[0][$j] = html_writer::div($data[$i][1]->data[0][$j], "level-wrapper");
            }
            $data[$i][1] = html_writer::table($data[$i][1]);
        }
        $table = new html_table();
        $table->attributes['class'] = 'criteria';
        $table->data = $data;
        $table->head = $columns;
        $table->colclasses = array(
            'description',
            'levels'
        );
        
        for ($i = 0; $i < count($data); $i ++) {
            $table->rowclasses[$i] = 'criterion';
            if ($i % 2 == 0)
                $table->rowclasses[$i] .= ' even';
            else
                $table->rowclasses[$i] .= ' odd';
        }
        
        echo html_writer::div(html_writer::table($table), "gradingform_rubric");
        
        $predefinedform->add_action_buttons(true, get_string('confirm'));
        $predefinedform->display();
    }
} else {
    // buttons
    $predefinedform->add_action_buttons(true, get_string('submit'));
    $predefinedform->display();
}

echo $OUTPUT->footer();

function emarking_table_from_line($line)
{
    $levelstable = new html_table();
    $levelstable->attributes = array(
        "class" => ""
    );
    $levelstable->data = array();
    $levelstable->data[0] = array();
    $levelstable->size = array();
    $levelstable->colclasses = array();
    for ($i = 1; $i < count($line); $i ++) {
        $levelstable->data[0][] = html_writer::div($line[$i], "definition");
        $levelstable->size[] = round(100 / (count($line) - 1), 1) . "%";
        $levelstable->colclasses[] = "level";
    }
    return array(
        $line[0],
        $levelstable
    );
}

function emarking_extract_rubric_points($cell)
{
    $value = $cell;
    $pattern = '/^(\d+[\.,]?\d*)\D*/';
    if (preg_match($pattern, $value, $matches)) {
        return $matches[1];
    } else {
        return - 1;
    }
}