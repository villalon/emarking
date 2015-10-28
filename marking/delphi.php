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

global $CFG, $DB, $OUTPUT, $PAGE;

// Check that user is logued in the course
require_login();
if (isguestuser()) {
	die();
}

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
$urlemarking = new moodle_url('/mod/emarking/marking/delphi.php', array(
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

// If there is a rubric defined we can get the controller and the parameters for this rubric
if ($gradingmethod && ($rubriccontroller = $gradingmanager->get_controller($gradingmethod))) {
	
	if ($rubriccontroller instanceof gradingform_rubric_controller) {
		// Getting the number of criteria
		if ($rubriccriteria = $rubriccontroller->get_definition()) {
			$numcriteria = count($rubriccriteria->rubric_criteria);
		}
		// Getting min and max scores
		$rubricscores = $rubriccontroller->get_min_max_score();
	}
}

$sqlagreement="
SELECT ec.criterionid, GROUP_CONCAT(ec.levelid SEPARATOR '') AS selection, 
		GROUP_CONCAT(total.levelid SEPARATOR '') AS agreement,
		es.student,
		total.levelid as agree
FROM {emarking_comment} AS ec
INNER JOIN {emarking_draft} AS ed ON (ed.id = ec.draft AND ed.emarkingid = ?)
INNER JOIN {emarking_submission} AS es ON (es.id = ed.submissionid)
LEFT JOIN (SELECT max(ad.count),
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
				  FROM {emarking_comment} AS ec 
				  INNER JOIN {emarking_draft} AS ed ON (ed.id = ec.draft AND ed.emarkingid = ?)
				  INNER JOIN {emarking_submission} AS es ON (es.id = ed.submissionid)
				  WHERE ec.status = 1
				  GROUP BY ec.levelid, es.student
                  ORDER BY count DESC) AS ad
			GROUP BY ad.criterionid, ad.student
		) AS total ON (total.student = es.student AND ec.criterionid = total.criterionid)
WHERE ec.status = 1
GROUP BY ec.criterionid, es.student";
		
$params = array(
		$cm->instance,$cm->instance
);

$agreements = $DB->get_recordset_sql($sqlagreement, $params);
$sumbycriteria = array();
$sumbystudent =array();
$str="1!=1";

if ($agreements->valid()) {
	foreach($agreements as $agreement){
		
		if(!array_key_exists($agreement->criterionid, $sumbycriteria)){
			$sumbycriteria[$agreement->criterionid] = 0;
			$sum = 0;
		}else{
			$sum = $sumbycriteria[$agreement->criterionid];
		}
		if(!array_key_exists($agreement->student, $sumbystudent)){
			$sumbystudent[$agreement->student] = 0;
			$plus = 0;
			
		}else{
			$plus = $sumbystudent[$agreement->student];
		}
		
		$algo= $DB->get_records_sql("SELECT  @s:=@s+1 AS val, id 
				FROM {gradingform_rubric_levels}, (SELECT @s:= 0) AS s 
				WHERE criterionid=?", 
				array(
						$agreement->criterionid
		));
		
		foreach($algo as $info){		
				$agreement->selection=str_replace($info->id,$info->val,$agreement->selection);
				$agreement->agreement=str_replace($info->id,$info->val,$agreement->agreement);
		}

		$res = array_diff_assoc(str_split($agreement->selection), str_split($agreement->agreement));
		$hammingdistance = count($res);
		$sum = $sum+$hammingdistance;
		$plus = $plus+$hammingdistance;

		$sumbycriteria[$agreement->criterionid] = $sum;
		$sumbystudent[$agreement->student] = $plus;

		$str .= " OR (ec.criterionid=$agreement->criterionid AND ec.levelid!=$agreement->agree AND es.student=$agreement->student)";
	}
}
//var_dump($str);
$sqlcountperstudent = "SELECT es.student, COUNT(es.student) as count
		FROM {emarking_comment} AS ec 
		INNER JOIN {emarking_draft} AS ed ON (ed.id = ec.draft AND ed.emarkingid = ?)
		INNER JOIN {emarking_submission} AS es ON (es.id = ed.submissionid)
		WHERE ec.status = 1
		GROUP BY es.student";

$sqlcountpercriteria = "SELECT ec.criterionid, COUNT(ec.criterionid) AS count, grc.description AS criterianame 
		FROM {emarking_comment} AS ec 
		INNER JOIN {emarking_draft} AS ed ON (ed.id = ec.draft AND ed.emarkingid = ?)
		INNER JOIN {gradingform_rubric_criteria}  AS grc ON (grc.id = ec.criterionid)
		GROUP BY ec.criterionid";

$sqlcountpermarker="SELECT ec.markerid, COUNT(ec.markerid) as count, algo.final AS otro
				  FROM {emarking_comment} AS ec 
				  INNER JOIN {emarking_draft} AS ed ON (ed.id = ec.draft AND ed.emarkingid = ?)
				  INNER JOIN {emarking_submission} AS es ON (es.id = ed.submissionid)
				  LEFT JOIN (SELECT count(*) AS final,ec.markerid
							FROM {emarking_comment} AS ec 
							INNER JOIN {emarking_draft} AS ed ON (ed.id = ec.draft AND ed.emarkingid = ?)
							INNER JOIN {emarking_submission} AS es ON (es.id = ed.submissionid) 
               				GROUP BY ec.markerid) as algo on (algo.markerid = ec.markerid)
                  WHERE ($str) AND ec.status=1
                  GROUP BY ec.markerid";

$param = array(
		$cm->instance
);

// Show header
echo $OUTPUT->header();

$outlierxstudents = $DB->get_records_sql($sqlcountperstudent, $param);

$firststagetable = new html_table();
$firststagetable->data[] = array($OUTPUT->heading("Por prueba", 5));

$avg=0;
$numberstudent=0;
foreach ($outlierxstudents as $outlier){
	$numberstudent++;
	$value = (1-($sumbystudent[$outlier->student]/$outlier->count))*100;
	$avg = $avg+$value;
	$examurl = new moodle_url("/mod/emarking/marking/agreement.php", array("id"=>$cm->id, "exam"=>$outlier->student));
	$firststagetable->data[] = array($OUTPUT->action_link($examurl, get_string("exam","mod_emarking"). ": ".$numberstudent.emarking_create_progress_graph(floor($value))));
	
}
$avg=$avg/$numberstudent;
$secondstagetable = new html_table();
$secondstagetable->data[] = array($OUTPUT->heading("Por Criterio", 5));
$outlierxcriteria = $DB->get_records_sql($sqlcountpercriteria, $param);

foreach ($outlierxcriteria as $outlier){
	$value = (1-($sumbycriteria[$outlier->criterionid]/$outlier->count))*100;
	$criterionurl = new moodle_url("/mod/emarking/marking/agreement.php", array("id"=>$cm->id, "criterion"=>$outlier->criterionid));
	$secondstagetable->data[] = array($OUTPUT->action_link($criterionurl, $outlier->criterianame.": ".emarking_create_progress_graph(floor($value))));
}

$param = array(
		$cm->instance,
		$cm->instance
);
$thirdstagetable = new html_table();
$thirdstagetable->data[] = array($OUTPUT->heading("Por Corrector", 5));
$outlierxmarker = $DB->get_records_sql($sqlcountpermarker, $param);

$numbermarker = 0;
foreach ($outlierxmarker as $outlier){
	$numbermarker++;
	$value = (1-($outlier->count/$outlier->otro))*100;
	$markerurl = new moodle_url("/mod/emarking/marking/agreement.php", array("id"=>$cm->id, "marker"=>$outlier->markerid));
	$thirdstagetable->data[] = array($OUTPUT->action_link($markerurl, get_string("marker", "mod_emarking").": ".$numbermarker.": ".emarking_create_progress_graph(floor($value))));
}

// Get the course module for the emarking, to build the emarking url
$urlagreement = new moodle_url('/mod/emarking/marking/agreement.php', array(
		'id' => $cm->id
));

echo emarking_tabs_markers_training(
		$context, 
		$cm, 
		$emarking,
		100,
		floor($avg));

echo "<h4>Porcentajes de acuerdo</h4>";

$maintable = new html_table();
$maintable->data[] = array(
		html_writer::table($firststagetable),
		html_writer::table($secondstagetable),
		html_writer::table($thirdstagetable)
);
echo html_writer::table($maintable);

echo $OUTPUT->footer();
