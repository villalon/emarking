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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2015 Francisco García <frgarcia@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/marking/locallib.php");

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

// Check that user is logued in the course
require_login();
if (isguestuser()) {
	die();
}

// Course module id
$cmid = required_param('id', PARAM_INT);

$markerid = optional_param('marker', 0, PARAM_INT);
$examid = optional_param('exam', 0, PARAM_INT);
$criterionid = optional_param('criterion', 0, PARAM_INT);

// Validate course module
if (! $cm = get_coursemodule_from_id('emarking', $cmid)) {
    print_error(get_string('invalidcoursemodule', 'mod_emarking') . " id: $cmid");
}

// Validate eMarking activity //TODO: validar draft si está selccionado
if (! $emarking = $DB->get_record('emarking', array(
    'id' => $cm->instance
))) {
    print_error(get_string('invalidid', 'mod_emarking') . " id: $cmid");
}

// Validate course
if (! $course = $DB->get_record('course', array(
		'id' => $emarking->course
))) {
	print_error(get_string('invalidcourseid', 'mod_emarking'));
}

// Get the course module for the emarking, to build the emarking url
$urlemarking = new moodle_url('/mod/emarking/marking/agreement.php', array(
		'id' => $cm->id
));
$context = context_module::instance($cm->id);

$filter = "AND ec.markerid = $USER->id ";
$markercolumn = get_string("yourmarking", "mod_emarking");

if(is_siteadmin($USER->id)) {
    $filter = "";    
}

if($examid != 0) {
    $filter .= "AND es.student = $examid";
} else if ($markerid != 0) {
    $filter = "AND ec.markerid = $markerid";
    $marker = $DB->get_record('user', array('id'=>$markerid));
    $markercolumn = $marker->firstname . " " . $marker->lastname;
} else if ($criterionid != 0) {
    $filter .= "AND ec.criterionid = $criterionid";
}

// Get rubric instance
list ($gradingmanager, $gradingmethod) = emarking_validate_rubric($context, true);

// As we have a rubric we can get the controller
$rubriccontroller = $gradingmanager->get_controller($gradingmethod);
if (! $rubriccontroller instanceof gradingform_rubric_controller) {
    print_error(get_string('invalidrubric', 'mod_emarking'));
}

$definition = $rubriccontroller->get_definition();

// var_dump($definition->rubric_criteria);die();

// Page navigation and URL settings
$PAGE->set_url($urlemarking);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));


$sqldata="SELECT ec.id AS commentid,
		ec.criterionid,
		ec.levelid,
        es.student,
        total.levelid AS agreement,
        case when ec.levelid = total.levelid then 1 else 0 end as sort,
        ec.markerid,
        grc.description,
        grl.definition,
        total.selections
FROM {emarking_comment} AS ec
INNER JOIN {emarking_draft} AS ed ON (ed.id = ec.draft AND ed.emarkingid = ?)
INNER JOIN {gradingform_rubric_criteria}  AS grc ON (grc.id = ec.criterionid)
INNER JOIN {emarking_submission} AS es ON (es.id = ed.submissionid)
INNER JOIN {gradingform_rubric_levels} as grl ON (grl.id = ec.levelid)
LEFT JOIN (SELECT MAX(ad.count) as selections,
					group_concat(ad.count) as counts,
                    ad.student,
					ad.criterionid,
					ad.levelid
			FROM (SELECT COUNT(ec.levelid) AS count,
                         ec.levelid,
                         es.student,
                         ec.criterionid
				  FROM mdl_emarking_comment AS ec 
				  INNER JOIN mdl_emarking_draft AS ed ON (ed.id = ec.draft AND ed.emarkingid = ?)
				  INNER JOIN mdl_emarking_submission AS es ON (es.id = ed.submissionid)	
				  WHERE ec.status=1
				  GROUP BY ec.levelid, es.student
                  ORDER BY es.student, ec.levelid DESC
		   ) AS ad
			GROUP BY ad.criterionid, ad.student
			ORDER BY ad.student, ad.criterionid) AS total ON (total.student=es.student AND ec.criterionid=total.criterionid)
WHERE ec.status = 1
$filter 
GROUP BY ec.criterionid, es.student
ORDER BY sort";

$params = array(
		$cm->instance,
		$cm->instance
);
$agreements = $DB->get_recordset_sql($sqldata, $params);

//TODO: si no tengo outliers, es decir no soy ayudante, no crear la tabla.
$firststagetable = new html_table();
$firststagetable->head = array(
    get_string("criterion", "mod_emarking"), 
    get_string("exam", "mod_emarking"),
    $markercolumn, 
    get_string("agreement", "mod_emarking"),
    get_string("status", "mod_emarking"));

foreach($agreements as $agree){
	$square = "";
	$squareagreement="";
	if($agree->levelid == $agree->agreement){
		$status = "OK";
	}else{
		$link = new moodle_url ('/mod/emarking/marking/modify.php', array(		
				'id' => $cm->id,
				'criterionid'=>$agree->criterionid,
				'commentid'=>$agree->commentid,
				'emarkingid'=>$emarking->id
		));
		
		$status= $OUTPUT->action_link($link, 'Modify', new popup_action ('click', $link));
	}
	
	foreach($definition->rubric_criteria[$agree->criterionid]['levels'] as $data) {
		if($data['id'] === $agree->levelid) {
		    $square .= html_writer::div($agree->selections, "agreement-yours-not-selected", array("title"=>$data['definition']));
		} else {
			$square .= html_writer::div($agree->selections, "agreement-yours-selected", array("title"=>$data['definition']));
		}
		if($data['id'] === $agree->agreement) {
		    $squareagreement .=  html_writer::div($agree->selections, "agreement-not-selected", array("title"=>$data['definition']));
		} else {
			$squareagreement .= html_writer::div($agree->selections, "agreement-selected", array("title"=>$data['definition']));
		}
	}

	$firststagetable->data[] = array(
			$agree->description,
			$agree->student,
			$square,
			$squareagreement, 
			$status
	);
}

// Show header
echo $OUTPUT->header();
//TODO: se debe agregar el avance de delphi al tabtree
echo emarking_tabs_markers_training($context, $cm, $emarking,100,0);

echo html_writer::table($firststagetable);

echo $OUTPUT->footer();

?>
<script type="text/javascript" >
function popUpClosed() {
    window.location.reload();
}
</script>