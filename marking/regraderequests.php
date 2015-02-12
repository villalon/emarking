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

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
global $CFG,$OUTPUT, $PAGE, $DB;//To suppress eclipse warnings
require_once($CFG->dirroot.'/mod/emarking/locallib.php');

$cmid = required_param('cmid', PARAM_INT);

if(!$cm = get_coursemodule_from_id('emarking',$cmid)) {
	error('Invalid course module id');
}

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    error('You must specify a valid course ID');
}

if(!$emarking = $DB->get_record('emarking', array('id'=>$cm->instance))) {
	error('Invalid emarking id');
}

require_login($course, true);

$context = context_module::instance($cm->id);

require_capability ( 'mod/emarking:grade', $context );
require_capability ( 'mod/emarking:regrade', $context );

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('justice','mod_emarking'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading(get_string('justice.my.evaluations','mod_emarking'));
$PAGE->set_url(new moodle_url("/mod/emarking/regraderequests.php?id=$cmid"));
$PAGE->navbar->add(get_string('regrades', 'mod_emarking'));	


echo $OUTPUT->header();
echo $OUTPUT->heading_with_help(get_string('regrades', 'mod_emarking'), 'regrades', 'mod_emarking');
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "regrades" );

$sql = "SELECT
			rg.*,
			u.id as userid,
			u.firstname,
			u.lastname,
			c.description as criterio,
			s.id as ids,
			s.status as status
		FROM {emarking_regrade} as rg
		INNER JOIN {emarking_submission} AS s ON (s.id = rg.submission AND s.emarking = :emarking)
		INNER JOIN {user} AS u ON (u.id = s.student)
		INNER JOIN {gradingform_rubric_criteria} as c ON (c.id = rg.criterion)
		ORDER BY u.lastname ASC";
$records = $DB->get_records_sql($sql,array("emarking"=>$cm->instance));

if(count($records) == 0) {
	echo $OUTPUT->notification(get_string('noregraderequests', 'mod_emarking'), 'notifyproblem');
	echo $OUTPUT->footer();
	die();
}

$table = new html_table();
$table->head = array(
    get_string('student','grades'),
    get_string('criterion', 'mod_emarking'),
    get_string('motive', 'mod_emarking'),
    get_string('comment', 'mod_emarking'),
    get_string('regradedatecreated', 'mod_emarking'),
    get_string('regradelastchange', 'mod_emarking'),
    get_string('status', 'mod_emarking'),
    get_string('actions', 'mod_emarking')
);

$data = array();
foreach($records as $record){

    $array = array();
    $url = new moodle_url('/user/view.php',array('id'=>$record->userid,'course'=>$course->id));
    $urlsub = new moodle_url('/mod/emarking/ajax/a.php',array('ids'=>$record->ids,'action'=>"emarking"));
    $array[] = '<a href="'.$url.'">'.$record->firstname.' '.$record->lastname.'</a>';
    $array[] = $record->criterio;
    //Stepstaken = 0 if no steps are taken, 1, if only first is taken, 2 if we are done.
    $stepstaken=0;
    $array[] = get_string_status($record->motive);
    $array[] = $record->comment;
    $array[] = date("d/m/y H:i", $record->timecreated);
    $array[] = date("d/m/y H:i", $record->timemodified);
    $status = 'Solicitada';
    if($record->accepted)
    	$status = 'Contestada';

    $array[] = $status;
    $array[] = $OUTPUT->action_link($urlsub, null,
			new popup_action ( 'click', $urlsub, 'emarking' . $record->ids, array (
								'menubar' => 'no',
								'titlebar' => 'no',
								'status' => 'no',
								'toolbar' => 'no' 
						)), null, new pix_icon('i/manual_item', get_string('annotatesubmission','mod_emarking')));

    $data[] = $array;
}
$table->data = $data;

echo html_writer::table($table);
echo $OUTPUT->footer();
