<?php
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
//include simplehtml_form.php
global $PAGE,$USER,$CFG , $OUTPUT, $DB;
require_once ($CFG->dirroot. '/mod/emarking/activities/forms/edit_activity.php');
require_once ($CFG->dirroot. '/mod/emarking/activities/generos.php');
require_login ();

$activityid = required_param('activityid', PARAM_INT);
$context=context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');

$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/editactivity.php');
$PAGE->set_url($url);
$PAGE->set_title('escribiendo');
echo $OUTPUT->header ();
//print the header
include 'views/header.php';
$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));

if($activity->userid != $USER->id){
		print_error('No tienes permiso para editar esta actividad.');
	
}
?>
	<div class="container">
		<div class="row">
		<h2>Editar actividad</h2>
		<div class="col-md-12">

<?php
	
//Instantiate simplehtml_form 
$mform = new local_ciae_edit_activity();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
	$fs = get_file_storage ();
	file_save_draft_area_files ( $fromform->instructions ['itemid'], $context->id, 'mod_emarking', 'instructions', $fromform->instructions ['itemid'] );
	$files = $fs->get_area_files ( $context->id, 'mod_emarking', 'instructions', $fromform->instructions ['itemid'], 'itemid, filepath, filename', false );
	$usercontext = context_user::instance ( $USER->id );
	
	$urlAntigua = '/draftfile.php/' . $usercontext->id . '/user/draft/' . $fromform->instructions ['itemid'] . '/';
	$instructions = $fromform->instructions ['text'];
	$planification = $fromform->planification ['text'];
	$writing = $fromform->writing ['text'];
	$editing = $fromform->editing ['text'];
	
	$urlnueva = '/pluginfile.php/1/mod_emarking/instructions/' . $fromform->instructions ['itemid'] . '/';
	$instructions = str_replace ( $urlAntigua, $urlnueva, $instructions );
	$planification = str_replace ( $urlAntigua, $urlnueva, $planification );
	$writing = str_replace ( $urlAntigua, $urlnueva, $writing );
	$editing = str_replace ( $urlAntigua, $urlnueva, $editing );
	
	$activity->title = $fromform->title;
	$activity->description = $fromform->description;
	//$activity->learningobjectives = $oaCode;
	$activity->comunicativepurpose = $fromform->comunicativepurpose;
	//$activity->genre = $generos [$genero];
	$activity->audience = $fromform->audience;
	$activity->estimatedtime = $fromform->estimatedtime;
	$activity->instructions = $instructions;
	$activity->planification = $planification;
	$activity->writing = $writing;
	$activity->editing = $editing;
	$activity->teaching = $fromform->teaching ['text'];
	$activity->languageresources = $fromform->languageresources ['text'];
	$activity->timemodified = time ();
	$activity->userid = $USER->id;
	$activity->rubricid = $fromform->rubricid;
	
	$DB->update_record('emarking_activities', $activity);
	
		
	$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php', array('id' => $activityid));
	redirect($url, 0);
  //In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
	
	$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));
	$keyofgenre = array_search($activity->genre, $generos) + 1;
	$formData = new stdClass();
	
 	
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
 	
 	
 
 	//Set default data (if any)
 	if (empty($instructions->id)) {
 		$instructions = new stdClass();
 		$instructions->id = 0;
 	}
 	
 	
	$draftid_editor = file_get_submitted_draft_itemid ( 'instructions' );
	
	file_prepare_draft_area ( $draftid_editor, $context->id, 'mod_emarking', 'instructions', $instructions->id, null );
	
	$formData->instructions = array (
			'text' => $activity->instructions,
			'',
			'itemid' => $draftid_editor 
	);
	$formData->planification = array (
			'text' => $activity->planification,
			'',
			'itemid' => $draftid_editor 
	);
	$formData->writing = array (
			'text' => $activity->writing,
			'',
			'itemid' => $draftid_editor 
	);
	$formData->editing = array (
			'text' => $activity->editing,
			'',
			'itemid' => $draftid_editor 
	);
	
	$mform->set_data ( $formData );
 	
 	
 	$mform->set_data($formData);
 	
  $mform->display();
}


echo $OUTPUT->footer ();
echo" 	</div>			
	</div>";
//print the footer
include 'views/footer.html';