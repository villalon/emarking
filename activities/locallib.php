<?php
require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . '/config.php');

/**
 * Function to create the table for rubrics
 *
 * @param string $id        	
 *
 * @return the table with the rubric's data
 */
function show_rubric($id) {
	global $DB;
	$sql = "SELECT grl.id,
			 grc.id as grcid,
			 grl.score,
			 grl.definition,
			 grc.description,
			 grc.sortorder,
			 gd.name
	  FROM mdl_gradingform_rubric_levels as grl,
	 	   mdl_gradingform_rubric_criteria as grc,
    	   mdl_grading_definitions as gd
	  WHERE gd.id='$id' AND grc.definitionid=gd.id AND grc.id=grl.criterionid
	  ORDER BY grcid, grl.id";
	
	$rubric = $DB->get_records_sql ( $sql );
	
	foreach ( $rubric as $data ) {
		
		$tableData [$data->description] [$data->definition] = $data->score;
	}
	
	$col = 0;
	foreach ( $tableData as $calc ) {
		
		$actualcol = sizeof ( $calc );
		if ($col < $actualcol) {
			$col = $actualcol;
		}
	}
	$row = sizeof ( $table );
	$table = "";
	$table .= '<table class="table table-bordered">';
	$table .= '<thead>';
	$table .= '<tr>';
	$table .= '<td>';
	$table .= '</td>';
	
	for($i = 1; $i <= $col; $i ++) {
		$table .= '<th>Nivel ' . $i . '</th>';
	}
	
	$table .= '</tr>';
	$table .= '</thead>';
	$table .= '<tbody>';
	
	foreach ( $tableData as $key => $value ) {
		
		$table .= '<tr>';
		$table .= '<th>' . $key . '</th>';
		foreach ( $value as $level => $score ) {
			$table .= '<th>' . $level . '</th>';
		}
		
		$table .= '</tr>';
	}
	$table .= '</tbody>';
	$table .= '</table>';
	
	return $table;
}
function show_result($data) {
	GLOBAL $CFG;
	
	$activityUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/activity.php', array (
			'id' => $data->id 
	) );
	$oaComplete = explode ( "-", $data->learningobjectives );
	$coursesOA = "";
	foreach ( $oaComplete as $oaPerCourse ) {
		
		$firstSplit = explode ( "[", $oaPerCourse );
		$secondSplit = explode ( "]", $firstSplit [1] );
		$course = $firstSplit [0];
		
		$coursesOA .= '<p>Curso: ' . $firstSplit [0] . '° básico</p>';
		$coursesOA .= '<p>OAs: ' . $secondSplit [0] . '</p>';
	}
	
	$show = '<a href="' . $activityUrl . '">';
	$show .= '<div id="resultados" class="col-md-12" style="text-align: left">';
	$show .= '<div class="panel panel-default">';
	$show .= '<div class="single-result-detail clearfix">';
	$show .= '<div id="descripcion" class="panel-body">';
	$show .= '<center><h3>' . $data->title . '</h3></center>';
	$show .= '<div  class="col-md-4" style="text-align: left">';
	$show .= $coursesOA;
	$show .= '<p>Propósito Comunicativo: Informar</p>';
	$show .= '<p>Género: ' . $data->genre . '</p>';
	$show .= '<p>Audiencia: ' . $data->audience . '</p>';
	$show .= '<p>Tiempo estimado: 90 min.</p>';
	$show .= '</div>';
	$show .= '<div  class="col-md-5">';
	$show .= '<p>' . $data->description . '</p>';
	$show .= '</div>';
	$show .= '<div  class="col-md-3" style="text-align: left">';
	$show .= '<img src="img/premio.png" class="premio" height="40px" width="40px">';
	$show .= '<p>55 Visitas</p>';
	$show .= '<p>3 Comentarios</p>';
	$show .= '<p>20 votos</p>';
	$show .= '<span class="glyphicon glyphicon-star" aria-hidden="true"></span>';
	$show .= '<span class="glyphicon glyphicon-star" aria-hidden="true"></span>';
	$show .= '<span class="glyphicon glyphicon-star" aria-hidden="true"></span>';
	$show .= '<span class="glyphicon glyphicon-star" aria-hidden="true"></span>';
	$show .= '<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>';
	$show .= '<p></p><p></p>';
	
	$show .= '</div>';
	$show .= '</div>';
	$show .= '</div>';
	
	$show .= '</div>';
	$show .= '</div>';
	$show .= '</a>';
	return $show;
}
/**
 * Creates a pdf from selected activity.
 *
 * @param unknown $activityid        	
 * @return boolean|multitype:unknown NULL Ambigous <boolean, number>
 */
function get_pdf_activity($activity) {
	
	
	GLOBAL $USER,$CFG, $DB;
	require_once ($CFG->libdir . '/pdflib.php');
	require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
	$tempdir = emarking_get_temp_dir_path($activity->id);
	if (!file_exists($tempdir)) {
		emarking_initialize_directory($tempdir, true);
	}

$user_object = $DB->get_record('user', array('id'=>$activity->userid));

$usercontext=context_user::instance($USER->id);

$fs = get_file_storage();
// create new PDF document

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator($USER->firstname.' '.$USER->lastname);
$pdf->SetAuthor($user_object->firstname.' '.$user_object->lastname);
$pdf->SetTitle($activity->title);
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetFont('helvetica', '', 11);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 50);
$pdf->SetTopMargin(40);
// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

$pdf->writeHTML($activity->instructions, true, false, false, false, '');
$pdffilename=$activity->title.'.pdf';
	$pathname = $tempdir . '/' . $pdffilename;
	if (@file_exists($pathname)) {
		unlink($pathname);
	}
	$numpages = $pdf->getNumPages();
	 $pdf->Output($pathname, 'F');
	
$itemid=rand(1,32767);
	 $filerecord = array(
	 		'contextid' => $usercontext->id,
	 		'component' => 'user',
	 		'filearea' => 'exam_files',
	 		'itemid' => $itemid,
	 		'filepath' => '/',
	 		'filename' => $pdffilename,
	 		'timecreated' => time(),
	 		'timemodified' => time(),
	 		'author' =>'pepito',
	 		'license' => 'allrightsreserved'
	 );
	 // Si el archivo ya existía entonces lo borramos.
	 if ($fs->file_exists($usercontext->id, 'mod_emarking', 'user', $itemid, '/', $pdffilename)) {
	 	$contents = $file->get_content();
	 }
	 $fileinfo = $fs->create_file_from_pathname($filerecord, $pathname);

	 $filedata [] = array(
	 		'pathname' => $pathname,
	 		'filename' => $pdffilename
	 );
	 
return array (
		'itemid'=>$itemid,
		'numpages'=>$numpages,
		'filedata'=>$filedata
			);
   
}
/**
 * Creates a new instance of emarking, with the data obteined in 
 * the bank of activities.
 *
 * @param object data
 *        	An object from data of the new instance emarking
 * @param inst $destinationcourse
 * 			The course where the instance will be create        	
 * @return int $itemid
 * 			The id of the pdf for emarking
 */
function emarking_create_activity_instance(stdClass $data,$destinationcourse,$itemid,$numpages,$filedata) {
	global $DB, $CFG, $COURSE, $USER;
	require_once ($CFG->dirroot . "/course/lib.php");
	require_once ($CFG->dirroot . "/mod/emarking/lib.php");
	require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
	
	$emarkingmod = $DB->get_record ( 'modules', array (
			'name' => 'emarking'
	) );

	$data->id = null;
	$data->course = $destinationcourse;
	
	$data->timecreated = time ();
	$id = $DB->insert_record ( 'emarking', $data );
	$data->id = $id;
	$course = $data->course;
	emarking_grade_item_update ( $data );
	

	// entregar id del curso
	$context = context_course::instance ( $course );
	
	$examfiles = $filedata;

	// If there's no previous exam to associate, and we are creating a new
	// EMarking, we need the PDF file.
	
	$studentsnumber = emarking_get_students_count_for_printing ( $course );
	
	// A new exam object is created and its attributes filled from form data.
	
		$exam = new stdClass ();
		$exam->course = $course;
		$exam->courseshortname = $COURSE->shortname;
		$exam->name = $data->name;
		$exam->examdate = time();
		$exam->emarking = $id;
		$exam->headerqr = 1;
		$exam->printrandom = 0;
		$exam->printlist = 0;
		$exam->extrasheets = 0;
		$exam->extraexams = 0;
		$exam->usebackside = 0;
		if ($examid == 0) {
			$exam->timecreated = time ();
		}
		$exam->timemodified = time ();
		$exam->requestedby = $USER->id;
		$exam->totalstudents = $studentsnumber;
		$exam->comment = "comment";
		// Get the enrolments as a comma separated values.
	
		$exam->enrolments = "manual";
		$exam->printdate = 0;
		$exam->status = 10;
		// Calculate total pages for exam.
		$exam->totalpages = $numpages;
		$exam->printingcost = 0;
		$exam->id = $DB->insert_record ( 'emarking_exams', $exam );
		$fs = get_file_storage ();
		foreach ( $examfiles as $exampdf ) {
			
			// Save the submitted file to check if it's a PDF.
			$filerecord = array (
					'component' => 'mod_emarking',
					'filearea' => 'exams',
					'contextid' => $context->id,
					'itemid' => $exam->id,
					'filepath' => '/',
					'filename' => $exampdf ['filename'] 
			);
			$file = $fs->create_file_from_pathname ( $filerecord, $exampdf ['pathname'] );
		}
		// Update exam object to store the PDF's file id.
		$exam->file = $file->get_id ();
		if (! $DB->update_record ( 'emarking_exams', $exam )) {
			$fs->delete_area_files ( $contextid, 'emarking', 'exams', $exam->id );
			print_error ( get_string ( 'errorsavingpdf', 'mod_emarking' ) );
		}
	
	$headerqr = 1;
	setcookie ( "emarking_headerqr", $headerqr, time () + 3600 * 24 * 365 * 10, '/' );
	$defaultexam = new stdClass ();
	$defaultexam->headerqr = $exam->headerqr;
	$defaultexam->printrandom = $exam->printrandom;
	$defaultexam->printlist = $exam->printlist;
	$defaultexam->extrasheets = $exam->extrasheets;
	$defaultexam->extraexams = $exam->extraexams;
	$defaultexam->usebackside = $exam->usebackside;
	$defaultexam->enrolments = $exam->enrolments;
	setcookie ( "emarking_exam_defaults", json_encode ( $defaultexam ), time () + 3600 * 24 * 365 * 10, '/' );
	
	$emarking->id= $id;
	$mod = new stdClass ();
	$mod->course = $destinationcourse;
	$mod->module = $emarkingmod->id;
	$mod->instance = $data->id;
	$mod->section = 0;
	$mod->visible = 0; // Hide the forum.
	$mod->visibleold = 0; // Hide the forum.
	$mod->groupmode = 0;
	$mod->grade = 100;
	if (! $cmid = add_course_module ( $mod )) {
		return false;
	}
	$sectionid = course_add_cm_to_section ( $mod->course, $cmid, 0 );
	return array (
			'id'=>$data->id,
			'cmid'=>$cmid,
			'sectionid'=>$sectionid
	);
}


