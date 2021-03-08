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

require_once ('locallib.php');

require_login ();

$confirm = optional_param ( 'confirm',FALSE ,PARAM_BOOL );
$activityid = optional_param ( 'id',0 ,PARAM_INT );
if($activityid > 0) {
    if(!$activity=$DB->get_record('emarking_activities',array('id'=>$activityid))) {
        print_error('Invalid activity');
    }
}
$title = $confirm ? 'Borrando actividad' : 'Confirmar borrado de actividad';
$context = context_system::instance ();
$url = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/deleteactivity.php', array('id'=>$activityid));
$PAGE->set_pagelayout ( 'standard' );
$PAGE->set_context ( $context );
$PAGE->set_url ( $url );
$PAGE->set_title ( $title );
$PAGE->navbar->add(get_string('activities', 'mod_emarking'), $activitiesurl);
if($activityid > 0) {
    $PAGE->navbar->add($activity->title, $activityurl);
}
$PAGE->navbar->add($title);

echo $OUTPUT->header ();
echo $OUTPUT->heading ( $title );

if (!$confirm) {
    $urlconfirmdeleteactivity = new moodle_url('/mod/emarking/activities/deleteactivity.php', array('id'=>$activityid, 'confirm' => 1));
    echo $OUTPUT->box('Está a punto de borrar la actividad, esto no se puede deshacer. ¿Desea continuar?');
    echo $OUTPUT->single_button($urlconfirmdeleteactivity, get_string('continue'), 'GET');
} else {
	$result = $DB->delete_records('emarking_activities', array('id' => $activity->id));
    $activities = new moodle_url('/mod/emarking/activities/search.php');
    echo $OUTPUT->notification(get_string('transactionsuccessfull', 'mod_emarking'), 'notifysuccess');
    echo $OUTPUT->single_button($activities, get_string('continue'), 'GET');
}

echo $OUTPUT->footer ();


