<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/emarking/locallib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/emarking/form.php');


global $CFG,$OUTPUT, $PAGE, $DB,$USER;//To suppress eclipse warnings

/*
 *
* Bootstraping moodle.
*
*/
$cmid = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('emarking',$cmid);
$action = optional_param('action','default',PARAM_ALPHANUMEXT);

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
	error('You must specify a valid course ID');
}

if(!$emarking = $DB->get_record('emarking', array('id'=>$cm->instance))) {
	error('You must specify a valid emarking ID');
}
require_login($course, true);

$experimentalgroups = $emarking->experimentalgroups;
$context = context_module::instance($cm->id);
$pagelayout = 'incourse';
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
require_capability ( 'mod/emarking:grade', $context );



$PAGE->set_title("Experimental Groups");
$PAGE->set_pagelayout($pagelayout);
$PAGE->set_heading("Experimental Groups");
$PAGE->set_url(new moodle_url("/mod/emarking/experimentalgroups.php?id=$cmid"));

//Set a variable for output buffering.
$o = "";


// Print eMarking tabs
$o.=$OUTPUT->tabtree(emarking_tabs($context,$cm, $emarking), "experimentalgroups" );


if($action == "edit"){
	$groupId = required_param('groupid', PARAM_INT);
	$expGroup = $DB->get_record("emarking_experimental_groups", array("id"=>$groupId));
	$group = $DB->get_record("groups", array("id"=>$expGroup->groupid));
	$members =  $DB->get_records("groups_members", array("groupid"=>$group->id));
	
	$mform = new EditExperimentalGroupForm(null, array('id'=>$cmid, 'groupid'=>$groupId));
	
	//Form processing and displaying is done here
	if ($mform->is_cancelled()) {
		//Handle form cancel operation, if cancel button is present on form
		$action = "default";
	} else if ($fromform = $mform->get_data()) {
		$expGroupEdit = new stdClass();
		$expGroupEdit->id = $fromform->groupid;
		$expGroupEdit->datestart = $fromform->datestart;
		$expGroupEdit->dateend = $fromform->dateend;
		if(isset($fromform->linkrubric)){
			$expGroupEdit->linkrubric = 1;
		}else{
			$expGroupEdit->linkrubric = 0;
		}
		$DB->update_record("emarking_experimental_groups", $expGroupEdit);
		$action = "default";
	} else {
		// this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
		// or on the first display of the form.
	
		//Set default data (if any)
		$mform->set_data(array("datestart"=>$expGroup->datestart, "dateend"=>$expGroup->dateend, "linkrubric"=>$expGroup->linkrubric));
		//displays the form		
		ob_start();
		$mform->display();
		$o .= ob_get_contents();
		ob_end_clean();
	}
}

if($action == "overlap"){	
	if($experimentalgroups == 1){
		$emarking->experimentalgroups = 2;
		$groupmembers = $DB->get_records_sql("SELECT * FROM {groups_members} WHERE groupid IN (SELECT id FROM {groups} WHERE courseid = $COURSE->id)");
		foreach ($groupmembers as $gm){
			if($submission = $DB->get_record("emarking_submission", array("student"=>$gm->userid, "emarking"=> $emarking->id))){
				$draft = new stdClass();
				$draft->submissionid = $submission->id;
				$draft->emarkingid = $emarking->id;
				$draft->student = $submission->student;
				$draft->groupid = $gm->groupid;
				$draft->grade = 1;
				$draft->status = $submission->status>EMARKING_STATUS_SUBMITTED?EMARKING_STATUS_SUBMITTED:$submission->status;
				$draft->teacher = $USER->id;
				$draft->sort = 0; //TODO: WTF
				$draft->timecreated = time();
				$draft->timemodified = time();
				$newdraftid = $DB->insert_record("emarking_draft", $draft, true);
					
				if($smpages = $DB->get_records("emarking_page", array("submission"=>$submission->id, "student"=>$submission->student))){
					foreach ($smpages as $smpage){
						$page = $smpage;
						$page->submission = $newdraftid;
						$page->timecreated = time();
						$page->timemodified = time();
						$DB->insert_record("emarking_page", $page);
					}
				}
			}
		}
	}else{
		$emarking->experimentalgroups = 1;
		$drafts = $DB->get_records_sql("SELECT * FROM {emarking_draft} WHERE emarkingid = $emarking->id AND groupid <> 0");
		foreach ($drafts as $draft){
			$DB->delete_records("emarking_page", array("submission"=>$draft->id, "student"=>$draft->student));
			$DB->delete_records("emarking_draft", array("id"=>$draft->id));
		}
	}

	$DB->update_record("emarking", $emarking);
	$experimentalgroups = $emarking->experimentalgroups;
	$action = "default";
}

if($action == "default"){
	
	if($groups = $DB->get_records("emarking_experimental_groups", array("emarkingid"=>$emarking->id))){
	
		$o.= "Hay ".count($groups)." grupos definidos en el curso";
		$urloverlap = new moodle_url ( 'experimentalgroups.php', array (
				'id' => $cm->id,
				'action' =>'overlap',
		) );
		$o.= $OUTPUT->single_button ( $urloverlap, $experimentalgroups==1?"Overlap Students":"No Overlap Students");

		$table = new html_table();
		$table->head = array("Group Name", "Start Date", "End Date", "Link Rubric", "Edit");
		foreach($groups as $group){
			$realGroup = $DB->get_record("groups", array("id"=>$group->groupid));
			$editUrl = new moodle_url('experimentalgroups.php', array('id'=>$cm->id,'action'=>'edit', 'groupid'=>$group->id));
			$editIcon = new pix_icon('t/editstring', "Edit");
			$edit = $OUTPUT->action_icon($editUrl, $editIcon);
			
			$table->data[] = array($realGroup->name, date("d-M-Y" ,$group->datestart), date("d-M-Y" ,$group->dateend), $group->linkrubric, $edit);
		}
		$o.= html_writer::table($table);
		
		
		$draftNoOverlap = $DB->count_records("emarking_draft", array("emarkingid"=>$emarking->id, "groupid"=>0));
		$draftOverlap = $DB->count_records_sql("SELECT COUNT(*) FROM {emarking_draft} WHERE emarkingid = $emarking->id AND groupid <> 0");
		
		$o.= "Pruebas Sin Overlap: ".$draftNoOverlap."</br>";

		$o.= "Pruebas en grupos experimentales: ".$draftOverlap ."</br>";
	
	
	}else{
	
		$o.= "No hay grupos definidos en el curso";
	}
	
}

//
echo $OUTPUT->header();
echo $o;
echo $OUTPUT->footer();
