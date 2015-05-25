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

$cmid = required_param('id', PARAM_INT);

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
$PAGE->set_title(get_string('emarking','mod_emarking'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_url(new moodle_url("/mod/emarking/marking/regraderequests.php?id=$cmid"));
$PAGE->navbar->add(get_string('regrades', 'mod_emarking'));	


echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "regrades" );

$sql = "SELECT
			rg.*,
			u.id AS userid,
			u.firstname,
			u.lastname,
			c.description AS criterion,
			d.id AS ids,
			d.status AS status
		FROM {emarking_submission} AS s 
		INNER JOIN {emarking_draft} AS d ON (s.emarking = :emarking AND d.submissionid = s.id AND d.qualitycontrol = 0) 
		INNER JOIN {emarking_regrade} as rg ON (d.id = rg.draft)
		INNER JOIN {user} AS u ON (u.id = s.student)
		INNER JOIN {gradingform_rubric_criteria} as c ON (c.id = rg.criterion)
		ORDER BY u.lastname ASC";
$records = $DB->get_records_sql($sql,array("emarking"=>$emarking->id));

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
    get_string('sent', 'mod_emarking'),
    get_string('lastmodification', 'mod_emarking'),
    get_string('status', 'mod_emarking'),
    get_string('actions', 'mod_emarking')
);

$data = array();
foreach($records as $record){

    $array = array();
    $url = new moodle_url('/user/view.php',array('id'=>$record->userid,'course'=>$course->id));
    $urlsub = new moodle_url('/mod/emarking/marking/index.php',array('id'=>$record->ids));
    $array[] = $OUTPUT->action_link($url, $record->firstname.' '.$record->lastname);
    $array[] = $record->criterion;
    $array[] = emarking_get_regrade_type_string($record->motive);
    $array[] = $record->comment;
    $array[] = emarking_time_ago($record->timecreated);
    $array[] = emarking_time_ago($record->timemodified);
    
    if($record->accepted) {
    	$status = $OUTPUT->pix_icon("i/valid", get_string('replied', 'mod_emarking'));
    } else {
        $status = $OUTPUT->pix_icon("i/flagged", get_string('sent', 'mod_emarking'));
    }
    
    $array[] = $status;
    $array[] = $OUTPUT->action_link($urlsub, null,
			new popup_action ( 'click', $urlsub, 'emarking' . $record->ids, array (
								'menubar' => 'no',
								'titlebar' => 'no',
								'status' => 'no',
								'toolbar' => 'no',
			                    'width' => 860,
			                    'height' => 600,
			)), null, new pix_icon('i/manual_item', get_string('annotatesubmission','mod_emarking')));

    $data[] = $array;
}
$table->data = $data;

echo html_writer::table($table);
echo $OUTPUT->footer();
