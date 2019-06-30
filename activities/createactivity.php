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
 * @copyright CIAE, Universidad de Chile
 * @author 2017 Francisco Ralph fco.ralph@gmail.com
 * @author 2019 Jorge Villalón villalon@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . '/config.php');
global $PAGE, $DB, $CFG, $OUTPUT;

require_once ('forms/activity.php');
require_once ('locallib.php');

require_login ();

$activityid = optional_param ( 'id',0 ,PARAM_INT );
if($activityid > 0){
    if(!$activity=$DB->get_record('emarking_activities',array('id'=>$activityid))) {
        print_error('Invalid activity');
    }
}
$title = $activityid > 0 ? 'Editar actividad ' . $activity->title : 'Crear actividad';
$context = context_system::instance ();
$url = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/createactivity.php', array('id'=>$activityid));

$PAGE->set_pagelayout ( 'standard' );
$PAGE->set_context ( $context );
$PAGE->set_url ( $url );
$PAGE->set_title ( $title );

echo $OUTPUT->header ();
echo $OUTPUT->heading ( $title );

$draftid_editor = file_get_submitted_draft_itemid ( 'instructions' );
file_prepare_draft_area ( $draftid_editor, $context->id, 'mod_emarking', 'instructions', $activityid, null );

$activityform = new mod_emarking_activities_create_activity_basic (NULL, array('id'=>$activityid));

if($activityid > 0) {
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
    
    if($activity->learningobjectives) {
        $matches = NULL;
        preg_match("/^(?<curso>\d+)\[(?<oas>\d+(\s*,\s*\d+)*)\]$/i", $activity->learningobjectives, $matches);
        if($matches != NULL && isset($matches['curso']) && isset($matches['oas'])) {
            $curso = $matches['curso'];
            $oas = explode(',',$matches['oas']);
            $lo = array();
            foreach($oas as $oa) {
                $lo[] = $curso . '-' .  $oa;
            }
            $activity->learningobjectives = $lo;
        }
    }
    
    $activityform->set_data ( $activity );
}
$activityform->display();

if ($fromformbasic = $activityform->get_data ()) {
    var_dump($fromformbasic);
}

echo $OUTPUT->footer ();


