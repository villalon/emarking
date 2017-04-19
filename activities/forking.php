<?php
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
GLOBAL $USER, $DB;

$activityid = required_param('id', PARAM_INT);
if(! $activity = $DB->get_record('emarking_activities',array('id'=>$activityid)) ){
	print_error("ID de Actividad invalido");
}

$activity->userid 			= $USER->id;
$activity->parent         	= $activityid;
$activity->timecreated 		= time();
$activity->status    		= 1;

if($forked = $DB->get_record('emarking_activities', array('userid' => $USER->id,'id' => $activityid))){
	$forkUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/createactivity.php', array('id' => $forked->id));
}
else if($forked = $DB->get_record('emarking_activities',array('userid' => $USER->id, 'parent' => $activityid))){
	$forkUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/createactivity.php', array('id' => $forked->id));
}
// ID = 2 is admin user, guest is id = 1
else if ($USER->id > 1){
	$insert = $DB->insert_record('emarking_activities', $activity);
	$forkUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/createactivity.php', array('id' => $insert));	
}else {
	// Redirect to activity if user id is invalid
	$forkUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php', array('id' => $activityid, 'message' => 1));
}
redirect($forkUrl, 0);
