<?php

require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');

require_once("locallib.php");
GLOBAL $USER;

$activityid = required_param('id', PARAM_INT);
$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));

//$action = required_param('action', PARAM_TEXT);
$itemid=get_pdf_activity($activity);

$emarking = new stdClass();
$emarking->course = 2;
$emarking->name = $activity->title;
$emarking->intro = "";
$emarking->custommarks = "";
$emarking->markingduedate = time();
$emarking->type = 1;
$emarking->grade = 7.0;
$emarking->grademin = 1.0;
$emarking->keywords = "keyword1,keyword2,sentence1";
$emarking->exam=0;

emarking_copy_to_cm($emarking,2,$itemid);