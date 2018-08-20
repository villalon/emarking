<?php

define('AJAX_SCRIPT', true);
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
require_once("$CFG->libdir/pdflib.php");
require_once ($CFG->dirroot . '/mod/emarking/activities/locallib.php');
GLOBAL $USER, $DB;

/*require_login();
if (isguestuser()) {
	die();
}*/

$activityid = required_param('id', PARAM_INT);
if ( !$activity = $DB->get_record('emarking_activities', array('id' => $activityid))) {
	die();
}

$sections = new stdClass ();
$sections->instructions = optional_param('instructions', 0,PARAM_INT);
$sections->planification = optional_param('planification', 0,PARAM_INT);
$sections->editing = optional_param('editing', 0,PARAM_INT);
$sections->writing = optional_param('writing', 0,PARAM_INT);
$sections->teaching = optional_param('teaching', 0,PARAM_INT);
$sections->resources = optional_param('resources', 0,PARAM_INT);
$sections->rubric = optional_param('rubric', 0,PARAM_INT);
$sections->header = optional_param('header', 0,PARAM_INT);

emarking_get_pdf_activity($activity, true, $sections);