<?php

require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');

require_once("locallib.php");
GLOBAL $USER;

$activityid = required_param('id', PARAM_INT);
$courseid = required_param('course', PARAM_INT);
$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));

//$action = required_param('action', PARAM_TEXT);
$itemid=get_pdf_activity($activity);

$emarking = new stdClass();
$emarking->course = $courseid;
$emarking->name = $activity->title;
$emarking->intro = "";
$emarking->custommarks = "";
$emarking->markingduedate = time();
$emarking->type = 1;
$emarking->grade = 7.0;
$emarking->grademin = 1.0;
$emarking->keywords = "keyword1,keyword2,sentence1";
$emarking->exam=0;
//var_dump($activity);
$data=emarking_copy_to_cm($emarking,$courseid,$itemid);
//var_dump($data);
$contextmodule = context_module::instance($data['cmid']);

$gradingArea = new stdClass();
$gradingArea->contextid=$contextmodule->id;
$gradingArea->component='mod_emarking';
$gradingArea->areaname='attempt';
$gradingArea->activemethod='rubric';
$insert = $DB->insert_record('grading_areas', $gradingArea);
$rubric= $DB->get_record('grading_definitions',array('id'=>$activity->rubricid));
$rubricdefinition=$rubric->id;
$rubric->copiedfromid=$activity->rubricid;
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

$forkUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php', array('id' => $activityid,'create'=>1));
redirect($forkUrl, 0);