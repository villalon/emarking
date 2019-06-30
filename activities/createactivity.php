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
 * @package mod_emarking
 * @copyright 2017 Francisco Ralph fco.ralph@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . '/config.php');
global $PAGE, $DB, $USER, $CFG, $OUTPUT;

require_once ('forms/create_activity_basic.php');
require_once ('forms/create_activity_instructions.php');
require_once ('forms/create_activity_teaching.php');
require_once ('locallib.php');
require_login ();

$step = optional_param ( 'step',1 ,PARAM_INT );
$activityid = optional_param ( 'id',0 ,PARAM_INT );

$context = context_system::instance ();
$PAGE->set_pagelayout ( 'standard' );
$PAGE->set_context ( $context );
$url = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/createactivity.php' );
$PAGE->set_url ( $url );
if($activityid > 0) {
    $PAGE->set_title ( 'Editar actividad' );
} else {
    $PAGE->set_title ( 'Crear actividad' );
}

echo $OUTPUT->header ();

$draftid_editor = file_get_submitted_draft_itemid ( 'instructions' );
file_prepare_draft_area ( $draftid_editor, $context->id, 'mod_emarking', 'instructions', $activityid, null );

// print the header
?>
<h2>Crear una actividad</h2>
<?php
$basic = new mod_emarking_activities_create_activity_basic (NULL, array('id'=>$activityid));
if($activityid!=0){
    $activity=$DB->get_record('emarking_activities',array('id'=>$activityid));
    $activity->instructions = array (
        'text' => $activity->instructions,
        '',
        'itemid' => $draftid_editor
    );
    $activity->planification = array (
        'text' => $activity->planification,
        '',
        'itemid' => $draftid_editor
    );
    $activity->writing = array (
        'text' => $activity->writing,
        '',
        'itemid' => $draftid_editor
    );
    $activity->editing = array (
        'text' => $activity->editing,
        '',
        'itemid' => $draftid_editor
    );
    $activity->teaching = array (
        'text' => $activity->teaching,
        '',
        'itemid' => $draftid_editor
    );
    $activity->languageresources = array (
        'text' => $activity->languageresources,
        '',
        'itemid' => $draftid_editor
    );
    $basic->set_data ( $activity );
}
$basic->display();

if ($fromformbasic = $basic->get_data ()) {
	//if is creating or editing a rubric
	if($fromformbasic->editing==0){
	   $activityid=add_new_activity_basic ( $fromformbasic);
	   $forkUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/createactivity.php', array('id' => $activityid, 'step' => 2));
	   redirect($forkUrl, 0);
	}else{
		edit_activity_basic ( $fromformbasic,$activityid);
	}
}

echo $OUTPUT->footer ();


