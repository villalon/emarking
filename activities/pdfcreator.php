<?php
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
require_once("$CFG->libdir/pdflib.php");
require_once ($CFG->dirroot . '/mod/emarking/activities/locallib.php');
GLOBAL $USER, $DB;

$activityid = required_param('id', PARAM_INT);
$instructions = optional_param('instructions', 0,PARAM_INT);
$planification = optional_param('planification', 0,PARAM_INT);
$editing = optional_param('editing', 0,PARAM_INT);
$writing = optional_param('writing', 0,PARAM_INT);
$teaching = optional_param('teaching', 0,PARAM_INT);
$resources = optional_param('resources', 0,PARAM_INT);
$rubric = optional_param('rubric', 0,PARAM_INT);

$sections = new stdClass ();
$sections->instructions=$instructions;
$sections->planification=$planification;
$sections->editing=$editing;
$sections->writing=$writing;
$sections->teaching=$teaching;
$sections->resources=$resources;
$sections->rubric=$rubric;

get_pdf_activity($activityid,true,$sections);