<?php
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/config.php');
//include simplehtml_form.php
require_once('forms/edit_activity.php');
 //Código para setear contexto, url, layout
global $PAGE,$USER, $OUTPUT, $DB;
$forkid = required_param('id', PARAM_INT);
$PAGE->set_pagelayout('embedded');

	echo $OUTPUT->header();


//Instantiate simplehtml_form 
$mform = new local_ciae_edit_activity();
 var_dump($mform->get_data());
//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {


var_dump($fromform);

die('a');

$record = new stdClass();
$record->draftid				= $fromform->id;
$record->instructions			= $fromform->instructions['text'];
$record->teaching   			= $fromform->teaching['text'];
$record->languageresources 		= $fromform->languageresources['text'];
$record->timecreated			= time();
$record->rubricid 				= $fromform->rubricid;


//$insert = $DB->insert_record('emarking_activities', $record);

//$url = new moodle_url($CFG->wwwroot.'/local/ciae/activity.php', array('id' => $insert));
//redirect($url, 0);
  //In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
	$draft=$DB->get_record('emarking_activity_draft',array('id'=>$forkid));
	$activity=$DB->get_record('emarking_activities',array('id'=>$draft->activityid));
	$formData = new stdClass();
	
	if($draft->edited ==1 ){
	$editedActovity=$activity=$DB->get_record('emarking_edited_activities',array('draftid'=>$draft->id,'userid'=>$draft->userid));
	$activity->teaching=$editedActovity->teaching;
	$activity->instructions=$editedActovity->instructions;
	$activity->languageresources=$editedActovity->languageresources;
	$formData->editedid		= $editedActovity->id;
	//$activity->rubricid=$editedActovity->rubricid;
	}


 $formData->instructions['text']		= $activity->instructions;
 $formData->teaching['text']  			= $activity->teaching;
 $formData->languageresources['text'] 	= $activity->languageresources;
 $formData->rubricid 					= $activity->rubricid;
 $formData->forkid 						= $forkid;
 $mform->set_data($formData);
 
  $mform->display();
}


//Código para setear contexto, url, layout
echo $OUTPUT->footer();

?>