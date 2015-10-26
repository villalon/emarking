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

// Course module id
$cmid = required_param('id', PARAM_INT);



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

// Get rubric instance
list ($gradingmanager, $gradingmethod) = emarking_validate_rubric($context, true);


// Page navigation and URL settings
$PAGE->set_url($urlemarking);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));


$sqldata="
		SELECT    ec.id AS commentid, 
		ec.criterionid,
			ec.levelid,
         es.student,
         total.levelid as agreement,
         ec.markerid,
         grc.description,
         grl.definition,
         levels.grupo
FROM mdl_emarking_comment AS ec
INNER JOIN mdl_emarking_draft AS ed ON (ed.id=ec.draft AND ed.emarkingid=?)
INNER JOIN mdl_gradingform_rubric_criteria  AS grc ON (grc.id=ec.criterionid)
INNER JOIN mdl_emarking_submission AS es ON (es.id=ed.submissionid)
INNER JOIN mdl_gradingform_rubric_levels as grl ON (grl.id=ec.levelid)
LEFT JOIN (select  GROUP_CONCAT(id) as grupo, criterionid from mdl_gradingform_rubric_levels group by criterionid) as levels  on(levels.criterionid=ec.criterionid)
LEFT JOIN (
			SELECT max(ad.count),
					ad.levelid,
                    ad.student,
					ad.criterionid,
					ad.draft
			FROM (SELECT COUNT(ec.levelid) AS count,
						 ec.draft,
                         ec.criterionid,
                         ec.levelid,
                         ec.markerid,
                         ed.emarkingid,
                         es.student
				  FROM mdl_emarking_comment AS ec 
				  INNER JOIN mdl_emarking_draft AS ed ON (ed.id=ec.draft AND ed.emarkingid=?)
				  INNER JOIN mdl_emarking_submission AS es ON (es.id=ed.submissionid)
		
		where ec.status=1
				  GROUP BY ec.levelid, es.student
                  ORDER BY count DESC) AS ad
			GROUP BY ad.criterionid, ad.student) AS total ON (total.student=es.student AND ec.criterionid=total.criterionid)
            where ec.markerid=? AND ec.status=1
            GROUP BY ec.criterionid, es.student
		
		";
$params = array(
		$cm->instance,$cm->instance,$USER->id
);
$agreements = $DB->get_recordset_sql($sqldata, $params);
$firststagetable = new html_table();
$firststagetable->head=Array("Pregunta","Estudiante","Tú selección", "Acuerdo", "Status");

foreach($agreements as $agree){
$cuadrados="";
$cuadradosagreement="";
	if($agree->levelid == $agree->agreement){
		$status="OK";
	}else{
		$link = new moodle_url ('/mod/emarking/marking/modify.php', array(
		'id' => $cm->id,
		'criterionid'=>$agree->criterionid,
		'commentid'=>$agree->commentid
));
		
		$status= $OUTPUT->action_link($link, 'Modify', new popup_action ('click', $link));
	}
	$grupo =explode(",", $agree->grupo);
	
	foreach($grupo as $data){
		if($data==$agree->levelid){
			$cuadrados .='<div style="float:left;width:20px;height:20px;border:2px solid #000;background-color:#F3F36F;border-color: #48D063"><center>'.$data.'</center></div>';
		}else{
		$cuadrados .='<div style="float:left;width:20px;height:20px;border:2px solid #000;background-color:#ffffff;border-color: #48D063"><center>'.$data.'</center></div>';
		}
		if($data==$agree->agreement){
			$cuadradosagreement .='<div style="float:left;width:20px;height:20px;border:2px solid #000;background-color:#FF7878;border-color: #48D063"><center>'.$data.'</center></div>';
		}else{
			$cuadradosagreement .='<div style="float:left;width:20px;height:20px;border:2px solid #000;background-color:#ffffff;border-color: #48D063"><center>'.$data.'</center></div>';
		}
	}
	
	
	

	$firststagetable->data[]=Array($agree->description,$agree->student,$cuadrados,$cuadradosagreement, $status);
	

}
// Show header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(emarking_tabs_markers_training($context, $cm, $emarking,100,0), "second","first");
echo html_writer::table($firststagetable);

echo $OUTPUT->footer();