<?php
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
//include simplehtml_form.php
require_once('forms/edit_activity.php');
require ('generos.php');

 //Código para setear contexto, url, layout
global $PAGE,$USER, $OUTPUT, $DB;
$activityid = required_param('activityid', PARAM_INT);
$context=context_system::instance();
$PAGE->set_context($context);
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
	$itemid=rand(1,32767);
	$fs = get_file_storage();
	file_save_draft_area_files($fromform->instructions ['itemid'], $context->id, 'mod_emarking', 'instructions', $itemid);
	$files = $fs->get_area_files(
			$context->id,
			'mod_emarking', 'instructions',
			$itemid,
			'itemid, filepath, filename',
			false);
	$usercontext = context_user::instance($USER->id);
	
	$urlAntigua='/draftfile.php/'.$usercontext->id.'/user/draft/'.$fromform->instructions ['itemid'] .'/';
	$text=$fromform->instructions['text'];
	var_dump($text);
	
	//$url = moodle_url::make_pluginfile_url($files->contextid, $files->component, $files->filearea, null, null, $files->filename);
	
	die();
	$oldfilename='';
	foreach($files as $file){
		$urlAntigua='/draftfile.php/'.$usercontext->id.'/user/draft/'.$fromform->instructions ['itemid'] .'/';
	if($oldfilename !=$file->get_filename()){
		$url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $fromform->instructions ['itemid'] , $file->get_filepath(), $file->get_filename());
		$urlnueva='/pluginfile.php/1/mod_emarking/'.$file->get_filearea().'/'.$fromform->instructions ['itemid'].'/';
		$text=str_replace($urlAntigua,$urlnueva,$text);
		$oldfilename=$file->get_filename();
		
	}
}
	
	die();
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
 	$formData->activityid 					= $activityid;
 	
 	$mform->set_data($formData);
 
 	//Set default data (if any)
 	if (empty($instructions->id)) {
 		$instructions = new object();
 		$instructions->id = 0;
 	}
 	
 	$draftid_editor = file_get_submitted_draft_itemid('instructions'); 
 	
 	file_prepare_draft_area($draftid_editor, $context->id, 'mod_emarking', 'instructions',
 			$instructions->id, null);
 	
 	$instructions->instructions = array('text'=>'', 'format'=>$instructions->format, 'itemid'=>$draftid_editor);
 	
 	
 	$mform->set_data($instructions);
 	
  $mform->display();
}


//Código para setear contexto, url, layout
echo $OUTPUT->footer();

?>