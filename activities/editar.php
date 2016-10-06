<?php
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/config.php');
//include simplehtml_form.php
require_once('forms/edit_activity.php');
require ('generos.php');

 //Código para setear contexto, url, layout
global $PAGE,$USER, $OUTPUT, $DB;
$forkid = required_param('id', PARAM_INT);

$PAGE->set_pagelayout('embedded');
require_login();
$PAGE->set_context(context_system::instance());
$url = new moodle_url($CFG->wwwroot.'/local/ciae/edit.php');
$PAGE->set_url($url);
	echo $OUTPUT->header();
	
$fork=$DB->get_record('emarking_activities',array('id'=>$forkid));
if($fork->userid != $USER->id){
		print_error('No tienes permiso para editar esta actividad.');
	
}
	
	
//Instantiate simplehtml_form 
$mform = new local_ciae_edit_activity();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
	

	if($fork->instructions != $fromform->instructions['text'] || 
	   $fork->teaching !=	$fromform->teaching['text'] ||
	   $fork->languageresources	!= $fromform->languageresources['text'] ||
	   $fork->rubricid != $fromform->rubricid){
		
	$fork->instructions				= $fromform->instructions['text'];
	$fork->teaching   				= $fromform->teaching['text'];
	$fork->languageresources 		= $fromform->languageresources['text'];
	$fork->timemodified				= time();
	$fork->rubricid 				= $fromform->rubricid;
	
	$DB->update_record('emarking_activities', $fork);
	}
		
	$url = new moodle_url($CFG->wwwroot.'/local/ciae/fork.php', array('id' => $forkid));
	redirect($url, 0);
  //In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
	
	$fork=$DB->get_record('emarking_activities',array('id'=>$forkid));
	$keyofgenre = array_search($fork->genre, $generos) + 1;
	$formData = new stdClass();
	
 	$formData->instructions['text']			= $fork->instructions;
	$formData->teaching['text']  			= $fork->teaching;
 	$formData->languageresources['text'] 	= $fork->languageresources;
 	$formData->rubricid 					= $fork->rubricid;
 	$formData->title 						= $fork->title;
 	$formData->description 					= $fork->description;
 	$formData->comunicativepurpose 			= $fork->comunicativepurpose;
 	$formData->genre 						= $keyofgenre;
 	$formData->audience 					= $fork->audience;
 	$formData->estimatedtime 				= $fork->estimatedtime;
 	$formData->id 							= $forkid;
 	$mform->set_data($formData);
 
  $mform->display();
}


//Código para setear contexto, url, layout
echo $OUTPUT->footer();

?>