<?php
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/config.php');
GLOBAL $USER;

	$activityid = required_param('id', PARAM_INT);
	$record=$DB->get_record('emarking_activities',array('id'=>$activityid));
	
	$record->userid 			= $USER->id;
	$record->parent         	= $activityid;
	$record->timecreated 		= time();
	$record->status    			= 1;

if($forked =$DB->get_record('emarking_activities',array('userid'=>$USER->id,'parent'=>$activityid))){
	
	$forkUrl = new moodle_url($CFG->wwwroot.'/local/ciae/fork.php', array('id' => $forked->id));
	redirect($forkUrl, 0);
}
else{
	$insert = $DB->insert_record('emarking_activities', $record);
	$forkUrl = new moodle_url($CFG->wwwroot.'/local/ciae/fork.php', array('id' => $insert));
	redirect($forkUrl, 0);
}