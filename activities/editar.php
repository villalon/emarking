<?php
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
//include simplehtml_form.php
require_once('forms/edit_activity.php');
require ('generos.php');

 //Código para setear contexto, url, layout
global $PAGE,$USER, $OUTPUT, $DB;
$activityid = required_param('id', PARAM_INT);

$PAGE->set_pagelayout('embedded');
require_login();
$PAGE->set_context(context_system::instance());
$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/edit.php');
$PAGE->set_url($url);
	echo $OUTPUT->header();
	
$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));
if($activity->userid != $USER->id){
		print_error('No tienes permiso para editar esta actividad.');
	
}
	
	
//Instantiate simplehtml_form 
$mform = new local_ciae_edit_activity();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
	

	if($activity->instructions != $fromform->instructions['text'] || 
	   $activity->teaching !=	$fromform->teaching['text'] ||
	   $activity->languageresources	!= $fromform->languageresources['text'] ||
	   $activity->rubricid != $fromform->rubricid){
		
	$activity->instructions				= $fromform->instructions['text'];
	$activity->teaching   				= $fromform->teaching['text'];
	$activity->languageresources 		= $fromform->languageresources['text'];
	$activity->timemodified				= time();
	$activity->rubricid 				= $fromform->rubricid;
	
	$DB->update_record('emarking_activities', $activity);
	}
		
	$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php', array('id' => $activityid));
	redirect($url, 0);
  //In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
	
	$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));
	$keyofgenre = array_search($activity->genre, $generos) + 1;
	$formData = new stdClass();
	
 	$formData->instructions['text']			= $activity->instructions;
	$formData->teaching['text']  			= $activity->teaching;
 	$formData->languageresources['text'] 	= $activity->languageresources;
 	$formData->rubricid 					= $activity->rubricid;
 	$formData->title 						= $activity->title;
 	$formData->description 					= $activity->description;
 	$formData->comunicativepurpose 			= $activity->comunicativepurpose;
 	$formData->genre 						= $keyofgenre;
 	$formData->audience 					= $activity->audience;
 	$formData->estimatedtime 				= $activity->estimatedtime;
 	$formData->id 							= $activityid;
 	$mform->set_data($formData);
 
  $mform->display();
}


//Código para setear contexto, url, layout
echo $OUTPUT->footer();

?>