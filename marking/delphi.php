<?php

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
$urlemarking = new moodle_url('/mod/emarking/marking/delphi.php', array(
		'id' => $cm->id
));
$context = context_module::instance($cm->id);

// Get rubric instance
list ($gradingmanager, $gradingmethod) = emarking_validate_rubric($context, true);

$urlprinters = new moodle_url("/mod/emarking/delphi/index.php");
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

$sql=" 
SELECT      STUDENTS.*,   
			ROUND((((ABS((CENTROID.avgscore - STUDENTS.score) / CENTROID.avgscore) - STUDENTS.agreementflexibility) / ABS(ABS((CENTROID.avgscore - STUDENTS.score) / CENTROID.avgscore) - STUDENTS.agreementflexibility)) + 1) / 2, 0) as outlier,   
            CENTROID.avgscore,   
            CENTROID.minscore,   
            CENTROID.maxscore     
			FROM ( SELECT  s.student as studentid,                        
				   d.id as draftid,
				   rl.score,
				   rl.criterionid, 
					MAX(c.markerid) as markerid,
					e.agreementflexibility
					FROM mdl_emarking AS e 
					INNER JOIN mdl_emarking_submission AS s ON (e.id = 1 AND e.id = s.emarking) 
					INNER JOIN mdl_emarking_draft AS d ON (d.submissionid = s.id)
					INNER JOIN mdl_emarking_comment AS c ON (c.draft = d.id)
					INNER JOIN mdl_gradingform_rubric_levels AS rl ON (c.levelid=rl.id)
					GROUP BY d.id, rl.criterionid     ) AS STUDENTS    
			INNER JOIN ( SELECT  s.student,
						AVG(rl.score * (1 - e.agreementflexibility)) as minscore, 
						AVG(rl.score) as avgscore, 
						AVG(rl.score * (1 + e.agreementflexibility)) as maxscore, 
						rl.criterionid
						FROM mdl_emarking AS e  
						INNER JOIN mdl_emarking_submission AS s ON (e.id = 1 AND e.id = s.emarking) 
						INNER JOIN mdl_emarking_draft AS d ON (d.submissionid = s.id) 
						INNER JOIN mdl_emarking_comment AS c ON (c.draft = d.id)
						INNER JOIN mdl_gradingform_rubric_levels AS rl ON (c.levelid=rl.id)
          GROUP BY s.student, rl.criterionid     ) AS CENTROID ON (STUDENTS.criterionid = CENTROID.criterionid AND STUDENTS.studentid = CENTROID.student)				
";
$max=1.2;
$min=0.8;
$sqlquerybystudent ="select student, sum(outlierpercentage)/sum(ndrafts) as percentage from ($sql  group by t1.criterionid,t1.student) as calc group by student";

$outliersbystudent=$DB->get_recordset_sql($sqlquerybystudent, array($max,$min,$cm->instance,$cm->instance));

$sqlquerybycriterion ="select criterionid,criterianame ,sum(outlierpercentage)/sum(ndrafts) as percentage from ($sql group by t1.criterionid,t1.student) as calc group by criterionid";

$outliersbycriterion=$DB->get_recordset_sql($sqlquerybycriterion, array($max,$min,$cm->instance,$cm->instance));

$sqlmarker="$sql group by t1.markerid";

$outliersbymarker=$DB->get_recordset_sql($sqlmarker, array($max,$min,$cm->instance,$cm->instance));

$sqldelphiprogress="select sum(percentage)/count(student) as delphiprogress from(  $sqlquerybystudent) as progress";
$delphiprogress=$DB->get_record_sql($sqldelphiprogress, array($max,$min,$cm->instance,$cm->instance));

// Show header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(emarking_tabs_markers_training($context, $cm, $emarking,100,floor((float)$delphiprogress->delphiprogress)), "second","first");

$k=0;
$firststagetable = new html_table();
$firststagetable->data[]=Array("<h4>Por Estudiante</h4>");
foreach($outliersbystudent as $outliers){
	$k++;
	$firststagetable->data[]=Array("Estudiante: ".$k.create_progress_graph(floor($outliers->percentage)));
}



$secondstagetable = new html_table();
$secondstagetable->data[]=Array("<h4>Por Criterio<h4>");
foreach($outliersbycriterion as $outliers){
	$secondstagetable->data[]=Array($outliers->criterianame.": ".create_progress_graph(floor($outliers->percentage)));
}


$thirdstagetable = new html_table();
$thirdstagetable->data[]=Array("<h4>Por Marker</h4>");
foreach($outliersbymarker as $outliers){

	$thirdstagetable->data[]=Array("Marker: ".$outliers->fakeid.": ".create_progress_graph(floor($outliers->outlierpercentage)));
}

$maintable=new html_table();
$maintable->data[]=Array(html_writer::table($firststagetable),html_writer::table($secondstagetable),html_writer::table($thirdstagetable));
echo html_writer::table($maintable);
echo $OUTPUT->footer();