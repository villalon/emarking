<?php
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
//include simplehtml_form.php
require_once('forms/create_activity.php');
 //Código para setear contexto, url, layout
global $PAGE,$USER, $CFG, $OUTPUT, $DB;

$PAGE->set_pagelayout('embedded');
require_login();
$PAGE->set_context(context_system::instance());
$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/create.php');
$PAGE->set_url($url);
echo $OUTPUT->header();

//Instantiate simplehtml_form 
$mform = new local_ciae_create_activity();
 
//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
require ('generos.php');

$course=$fromform->codigoOA['course'];
$oaCode ="";
$oaCode .=$course.'[';
foreach($fromform->codigoOA['oa'] as $data){
	$oaCode .=$data.',';
}
$oaCode .=']';
$instructions=$fromform->instructions['text'];
$suggestion=$fromform->didacticSuggestions['text'];
$genero=(int)$fromform->genre-1;


//echo $fromform->C1;
$OAC1 ="";
if(isset($fromform->C1)){

	if(isset($fromform->CODC1)){
		foreach($fromform->CODC1 as $key =>$value){
			$porciones = explode("C1OA", $key);
			$OAC1 .=$porciones[1].",";
		}
		$OAC1= substr($OAC1, 0, -1);
		$OAC1 =$fromform->C1."[".$OAC1."]";
	}
	
}
$OAC2 ="";
if(isset($fromform->C2)){
	if(isset($fromform->CODC2)){
		foreach($fromform->CODC2 as $key =>$value){
			$porciones = explode("C2OA", $key);
			$OAC2 .=$porciones[1].",";
		}
		$OAC2= substr($OAC2, 0, -1);
		$OAC2 ="-".$fromform->C2."[".$OAC2."]";
	}
	
}
$OAC3 ="";
if(isset($fromform->C3)){
	if(isset($fromform->CODC3)){
		foreach($fromform->CODC3 as $key =>$value){
			$porciones = explode("C3OA", $key);
			$OAC3 .=$porciones[1].",";
		}
		$OAC3= substr($OAC3, 0, -1);
		$OAC3 ="-".$fromform->C3."[".$OAC3."]";
	}
}
$oaCode=$OAC1.$OAC2.$OAC3;

$record = new stdClass();
$record->title 					= $fromform->title;
$record->description         	= $fromform->description;
$record->learningobjectives		= $oaCode;
$record->comunicativepurpose    = $fromform->comunicativepurpose;
$record->genre 					= $generos[$genero];
$record->audience         		= $fromform->audience;
$record->estimatedtime 	    	= $fromform->estimatedtime;
$record->instructions			= $fromform->instructions['text'];
$record->teaching   			= $fromform->teaching['text'];
$record->languageresources 		= $fromform->languageresources['text'];
$record->timecreated			= time();
$record->userid 				= $USER->id;
$record->rubricid 				= $fromform->rubricid;


$insert = $DB->insert_record('emarking_activities', $record);

$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php', array('id' => $insert));
redirect($url, 0);
  //In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
  // or on the first display of the form.
 
  //Set default data (if any)

  //displays the form
  $mform->display();
}


//Código para setear contexto, url, layout
echo $OUTPUT->footer();

?>