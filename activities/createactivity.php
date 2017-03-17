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

/**
 *
 * @package   mod_emarking
 * @copyright 2017 Francisco Ralph fco.ralph@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
global $PAGE, $DB, $USER, $CFG, $OUTPUT;

require_once ('forms/create_activity.php');

require_login ();
$PAGE->set_pagelayout ( 'embedded' );
$context = context_system::instance ();
$PAGE->set_context ( $context );
$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/create.php');
$PAGE->set_url($url);
$PAGE->set_title('escribiendo');

echo $OUTPUT->header ();
include 'views/headermoodle.php';
//print the header

?>

<div class="container">
<div class="row">

<h2>Crear una actividad</h2>

<div class="col-md-12">
<?php 
$mform = new local_ciae_create_activity ();

// Form processing and displaying is done here
if ($mform->is_cancelled ()) {
	// Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data ()) {
	 include ($CFG->dirroot. '/mod/emarking/activities/generos.php');
	$genero = ( int ) $fromform->genre - 1;
	
	$OAC1 = "";
	if (isset ( $fromform->C1 )) {
		
		if (isset ( $fromform->CODC1 )) {
			foreach ( $fromform->CODC1 as $key => $value ) {
				$porciones = explode ( "C1OA", $key );
				$OAC1 .= $porciones [1] . ",";
			}
			$OAC1 = substr ( $OAC1, 0, - 1 );
			$OAC1 = $fromform->C1 . "[" . $OAC1 . "]";
		}
	}
	$OAC2 = "";
	if (isset ( $fromform->C2 )) {
		if (isset ( $fromform->CODC2 )) {
			foreach ( $fromform->CODC2 as $key => $value ) {
				$porciones = explode ( "C2OA", $key );
				$OAC2 .= $porciones [1] . ",";
			}
			$OAC2 = substr ( $OAC2, 0, - 1 );
			$OAC2 = "-" . $fromform->C2 . "[" . $OAC2 . "]";
		}
	}
	$OAC3 = "";
	if (isset ( $fromform->C3 )) {
		if (isset ( $fromform->CODC3 )) {
			foreach ( $fromform->CODC3 as $key => $value ) {
				$porciones = explode ( "C3OA", $key );
				$OAC3 .= $porciones [1] . ",";
			}
			$OAC3 = substr ( $OAC3, 0, - 1 );
			$OAC3 = "-" . $fromform->C3 . "[" . $OAC3 . "]";
		}
	}
	$oaCode = $OAC1 . $OAC2 . $OAC3;
	
	$fs = get_file_storage ();
	file_save_draft_area_files ( $fromform->instructions ['itemid'], $context->id, 'mod_emarking', 'instructions', $fromform->instructions ['itemid'] );
	$files = $fs->get_area_files ( $context->id, 'mod_emarking', 'instructions', $fromform->instructions ['itemid'], 'itemid, filepath, filename', false );
	$usercontext = context_user::instance ( $USER->id );
	
	$urlAntigua = '/draftfile.php/' . $usercontext->id . '/user/draft/' . $fromform->instructions ['itemid'] . '/';
	$instructions = $fromform->instructions ['text'];
	$planification = $fromform->planification ['text'];
	$writing = $fromform->writing ['text'];
	$editing = $fromform->editing ['text'];
	$teaching= $fromform->teaching ['text'];
	$lenguageresources= $fromform->languageresources ['text'];
	
	//changing url of image
	$urlnueva = '/pluginfile.php/1/mod_emarking/instructions/' . $fromform->instructions ['itemid'] . '/';
	$instructions = str_replace ( $urlAntigua, $urlnueva, $instructions );
	$planification = str_replace ( $urlAntigua, $urlnueva, $planification );
	$writing = str_replace ( $urlAntigua, $urlnueva, $writing );
	$editing = str_replace ( $urlAntigua, $urlnueva, $editing );
	$teaching = str_replace ( $urlAntigua, $urlnueva, $teaching );
	$lenguageresources = str_replace ( $urlAntigua, $urlnueva, $lenguageresources );
	
	//cleaning html text
	$instructions = emarking_activities_clean_html_text($instructions);
	$planification = emarking_activities_clean_html_text($planification);
	$writing = emarking_activities_clean_html_text($writing);
	$editing = emarking_activities_clean_html_text($editing);
	$teaching = emarking_activities_clean_html_text($teaching);
	$lenguageresources = emarking_activities_clean_html_text($lenguageresources);
	
	$record = new stdClass ();
	$record->title = $fromform->title;
	$record->description = $fromform->description;
	$record->learningobjectives = $oaCode;
	$record->comunicativepurpose = $fromform->comunicativepurpose;
	$record->genre = $generos [$genero];
	$record->audience = $fromform->audience;
	$record->estimatedtime = $fromform->estimatedtime;
	$record->instructions = $instructions;
	$record->planification = $planification;
	$record->writing = $writing;
	$record->editing = $editing;
	$record->teaching = $teaching;
	$record->languageresources = $lenguageresources;
	$record->timecreated = time ();
	$record->userid = $USER->id;
	$record->rubricid = $fromform->rubricid;
	$record->status = 1;
	$instertnewactivity = $DB->insert_record ( 'emarking_activities', $record );
	
	$socialrecord=new stdClass ();
	$socialrecord->activityid 			= $instertnewactivity;
	$socialrecord->timecreated         	= time();
	$socialrecord->data					= null;				
	$DB->insert_record ( 'emarking_social', $socialrecord );
	$url = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/activity.php', array (
			'id' => $instertnewactivity 
	) );
	redirect ( $url, 0 );
	// In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
	// this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
	// or on the first display of the form.
	
	// Set default data (if any)
	if (empty ( $instructions->id )) {
		$instructions = new stdClass ();
		$instructions->id = 0;
	}
	
	$draftid_editor = file_get_submitted_draft_itemid ( 'instructions' );
	
	file_prepare_draft_area ( $draftid_editor, $context->id, 'mod_emarking', 'instructions', $instructions->id, null );
	
	$instructions->instructions = array (
			'text' => '',
			'',
			'itemid' => $draftid_editor 
	);
	$instructions->planification = array (
			'text' => '',
			'',
			'itemid' => $draftid_editor 
	);
	$instructions->writing = array (
			'text' => '',
			'',
			'itemid' => $draftid_editor 
	);
	$instructions->editing = array (
			'text' => '',
			'',
			'itemid' => $draftid_editor 
	);
	$instructions->teaching = array (
			'text' => '',
			'',
			'itemid' => $draftid_editor
	);
	$instructions->languageresources = array (
			'text' => '',
			'',
			'itemid' => $draftid_editor
	);
	
	$mform->set_data ( $instructions );
	// displays the form
	$mform->display ();
}

echo $OUTPUT->footer ();
echo" 	</div>			
	</div>";
//print the footer

include 'views/footer.html';
