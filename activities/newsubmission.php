<?php

require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
require_once($CFG->dirroot.'/mod/emarking/lib.php');
require_once("locallib.php");
require_once($CFG->dirroot.'/enrol/manual/locallib.php');
require_once($CFG->dirroot.'/lib/accesslib.php');
GLOBAL $USER;
$activityid = required_param('id', PARAM_INT);
$courseid = required_param('course', PARAM_INT);
$askMarking = optional_param('askMarking',0, PARAM_INT);
require_login($courseid);
$sections = new stdClass ();
$sections->instructions=1;
$sections->planification=1;
$sections->editing=1;
$sections->writing=1;

$pdf=get_pdf_activity($activityid,false,$sections);
$itemid=$pdf['itemid'];
$numpages=$pdf['numpages'];
$filedata=$pdf['filedata'];

$emarking = new stdClass();
$emarking->course = $courseid;
$emarking->name = $pdf['activitytitle'];;
$emarking->intro = "";
$emarking->custommarks = "";
$emarking->markingduedate = time();
$emarking->type = 1;
$emarking->grade = 7.0;
$emarking->grademin = 1.0;
$emarking->keywords = "keyword1,keyword2,sentence1";
$emarking->exam=0;
$emarking->uploadtype=EMARKING_UPLOAD_QR;

$data=emarking_create_activity_instance($emarking,$courseid,$itemid,$numpages,$filedata);
$contextmodule = context_module::instance($data['cmid']);
$coursecontext = context_course::instance($courseid, MUST_EXIST);


$gradingArea = new stdClass();
$gradingArea->contextid=$contextmodule->id;
$gradingArea->component='mod_emarking';
$gradingArea->areaname='attempt';
$gradingArea->activemethod='rubric';
$insert = $DB->insert_record('grading_areas', $gradingArea);
$rubric= $DB->get_record('grading_definitions',array('id'=>$pdf['rubricid']));
$rubricdefinition=$rubric->id;
$rubric->copiedfromid=$pdf['rubricid'];
$rubric->timecopied=time();
$rubric->areaid=$insert;
unset($rubric->id);
$insertRubric = $DB->insert_record('grading_definitions', $rubric);

$rubricCriterias= $DB->get_records('gradingform_rubric_criteria',array('definitionid'=>$rubricdefinition));

foreach ($rubricCriterias as $rubricCriteria){
	
	
	$rubricCriteria->definitionid=$insertRubric;
	$rubricCriteriaid=$rubricCriteria->id;
	
	unset($rubricCriteria->id);
	$insertRubricCriteria = $DB->insert_record('gradingform_rubric_criteria', $rubricCriteria);
	
	$rubricCriteriaLevels= $DB->get_records('gradingform_rubric_levels',array('criterionid'=>$rubricCriteriaid));
	foreach($rubricCriteriaLevels as $rubricCriteriaLevel){
		unset($rubricCriteriaLevel->id);
		
		$rubricCriteriaLevel->criterionid=$insertRubricCriteria;
		$insertRubricCriteriaLevels = $DB->insert_record('gradingform_rubric_levels', $rubricCriteriaLevel);
		
	}
	
}
if($askMarking==1){
	
	$canenrol = has_capability('enrol/manual:enrol', $coursecontext);
	$canunenrol = has_capability('enrol/manual:unenrol', $coursecontext);
	if (!$canenrol and !$canunenrol) {
		$forkUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php', array('id' => $activityid,'create'=>1));
		redirect($forkUrl, 0);
	}
	$instance = $DB->get_record('enrol', array('id'=>1, 'enrol'=>'manual'), '*', MUST_EXIST);
	
	$roleid = $instance->roleid;
	$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
	$timestart = $course->startdate;
	$roles = get_assignable_roles($coursecontext);
	if (!$enrol_manual = enrol_get_plugin('manual')) {
		throw new coding_exception('Can not instantiate enrol_manual');
	}
	$instancename = $enrol_manual->get_instance_name($instance);
	
	$enrol_manual->enrol_user($instance, 3, $roleid, $timestart, 0);
	 	$ra = new stdClass();
    	$ra->roleid       = 5;
    	$ra->contextid    = $coursecontext->id;
   		$ra->userid       = 3;
    	$ra->component    = '';
    	$ra->itemid       = 0;
    	$ra->timemodified = 0;
    	$ra->modifierid   = empty($USER->id) ? 0 : $USER->id;
    	$ra->sortorder    = 0;
    	$ra->id = $DB->insert_record('role_assignments', $ra);
    	 
	
}

$forkUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/marking.php', array('id' => $data['cmid'],'tab'=>1));
redirect($forkUrl, 0);
