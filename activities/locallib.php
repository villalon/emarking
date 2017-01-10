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
	
	$activityUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/views/activity.php', array (
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
	$show .= '<img src="../img/premio.png" class="premio" height="40px" width="40px">';
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
function get_pdf_activity($activityid,$download = false,$sections = null) {
	GLOBAL $USER,$CFG, $DB;
	require_once ($CFG->libdir . '/pdflib.php');
	require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
	
	
$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));
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
$pdf->SetFont('times', '', 12);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 50);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetTopMargin(40);
$pdf->SetRightMargin(25);
$pdf->SetLeftMargin(25);
// Add a page
// This method has several options, check the source code documentation for more information.

if($sections->instructions==1){
$pdf->AddPage();
$pdf->writeHTML('<h1>Instrucciones</h1> ', true, false, false, false, '');
$instructionshtml=emarking_activities_add_images_pdf($activity->instructions,$usercontext);
$pdf->writeHTML($instructionshtml, true, false, false, false, '');

}

if($sections->planification==1){
$pdf->AddPage();
$planificationhtml=emarking_activities_add_images_pdf($activity->planification,$usercontext);
$pdf->writeHTML('<h1>Planificación</h1>', true, false, false, false, '');
$pdf->writeHTML($planificationhtml, true, false, false, false, '');
}

if($sections->writing==1){
$pdf->AddPage();
$writinghtml=emarking_activities_add_images_pdf($activity->writing,$usercontext);
$pdf->writeHTML('<h1>Escritura</h1>', true, false, false, false, '');
$pdf->writeHTML($writinghtml, true, false, false, false, '');
}

if($sections->editing==1){
$pdf->AddPage();
$editinghtml=emarking_activities_add_images_pdf($activity->editing,$usercontext);
$pdf->writeHTML('<h1>Revisión y edición</h1>', true, false, false, false, '');
$pdf->writeHTML($editinghtml, true, false, false, false, '');
}

if($sections->teaching==1){
$pdf->AddPage();
$teachinghtml=emarking_activities_add_images_pdf($activity->teaching,$usercontext);
$pdf->writeHTML('<h1>Sugerencias didácticas</h1>', true, false, false, false, '');
$pdf->writeHTML($teachinghtml, true, false, false, false, '');
}

if($sections->resources==1){
$pdf->AddPage();
$languageresourceshtml=emarking_activities_add_images_pdf($activity->languageresources,$usercontext);
$pdf->writeHTML('<h1>Recursos del lenguaje</h1>', true, false, false, false, '');
$pdf->writeHTML($languageresourceshtml, true, false, false, false, '');
}

if($sections->rubric==1){
$pdf->AddPage();

$rubrichtml=show_rubric($activity->rubricid);
$pdf->writeHTML('<h1>Evaluación</h1>', true, false, false, false, '');
$pdf->writeHTML($rubrichtml, true, false, false, false, '');
}

if($download==true){
	$pdf->Output($activity->title.'.pdf', 'D');
	
} else{
	$tempdir = emarking_get_temp_dir_path($activity->id);
	if (!file_exists($tempdir)) {
		emarking_initialize_directory($tempdir, true);
	}
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
		'filedata'=>$filedata,
		'activitytitle'=>$activity->title,
		'rubricid'=>$activity->rubricid
			);
}
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
		$exam->timecreated = time ();
		$exam->timemodified = 0;
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
function emarking_activities_add_images_pdf($html,$context){
	global $DB, $CFG, $OUTPUT;

	// Inclusión de librerías
	require_once ($CFG->dirroot . '/mod/emarking/orm/locallib.php');
	require_once ($CFG->dirroot . '/mod/emarking/print/locallib.php');
	$filedir = $CFG->dataroot . "/temp/emarking/$context->id";
	emarking_initialize_directory($filedir, false);

	$fileimg = $CFG->dataroot . "/temp/emarking/$context->id/images";
	emarking_initialize_directory($fileimg, false);


	$fullhtml = array();
	$numanswers = array();
	$attemptids = array();
	$images = array();
	$imageshtml = array();

				$currentimages = emarking_extract_images_url($html);
				$idx = 0;
				foreach ($currentimages[1] as $imageurl) {
					if (! array_search($imageurl, $images)) {
						$images[] = $imageurl;
						$imageshtml[] = $currentimages[0][$idx];
					}
					$idx ++;
				}
				
	// Bajar las imágenes del HTML a dibujar
	$search = array();
	$replace = array();
	$replaceweb = array();
	$imagesize = array();
	$idx = 0;
			
	foreach ($images as $image) {
		
			if (! list ($filename, $imageinfo) = emarking_activities_get_file_from_url($image, $fileimg)) {
				echo "Problem downloading file $image <hr>";
			} else {
				// Buscamos el src de la imagen
				$search[] = 'src="' . $image . '"';
				$replacehtml = ' src="' . $filename . '"';
				$replacehtmlxweb = ' src="' . $image . '"';
				// Si el html de la misma contiene ancho o alto, se deja tal cual
				$imghtml = $imageshtml[$idx];
				if (substr_count($imghtml, "width") + substr_count($imghtml, "height") == 0) {
					$width = $imageinfo[0];
					$height = $imageinfo[1];
					$ratio = floatval(10) / floatval($height);
					$height = 10;
					$width = (int) ($ratio * floatval($width));
					$sizehtml = 'width="' . $width . '" height="' . $height . '"';
					$replacehtml = $sizehtml . ' ' . $replacehtml;
					$replacehtmlxweb = $sizehtml . ' ' . $replacehtmlxweb;
				}
				$replace[] = $replacehtml;
				$replaceweb[] = $replacehtmlxweb;
				$imagesize[] = $imageinfo;
			}
			$idx ++;
	}
	$fullhtml = str_replace($search, $replace, $html);
	return $fullhtml;
}

/**
 *
 * @param unknown $url
 * @param unknown $pathname
 * @return boolean
 */
function emarking_activities_get_file_from_url($url, $pathname)
{
	// Calculate filename
	$parts = explode('/', $url);
	$filename = $parts[count($parts) - 1];
	 
	$ispluginfile = false;
	$ispixfile = false;
	$index = 0;
	foreach ($parts as $part) {
		if ($part === 'pluginfile.php') {
			$ispluginfile = true;
			break;
		}
		if ($part === 'pix.php') {
			$ispixfile = true;
			break;
		}
		$index ++;
	}

	$fs = get_file_storage();

	// If the file is part of Moodle, we get it from the filesystem
	if ($ispluginfile) {
		$contextid = $parts[$index + 1];
		$component = $parts[$index + 2];
		$filearea = $parts[$index + 3];
		$itemid = $parts[$index + 4];
		$filepath = '/';
		if ($fs->file_exists($contextid, $component, $filearea, $itemid, $filepath, $filename)) {
			$file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);
		
			$file->copy_content_to($pathname . $filename);		
			$imageinfo = getimagesize($pathname . $filename);
			return array(
					$pathname . $filename,
					$imageinfo
			);
		}
		return false;
	}

	// Open binary stream and read it
	$handle = fopen($url, "rb");
	$content = stream_get_contents($handle);
	fclose($handle);

	// Save the binary file
	$file = fopen($pathname . $filename, "wb+");
	fputs($file, $content);
	fclose($file);

	$imageinfo = getimagesize($pathname . $filename);
	return array(
			$pathname . $filename,
			$imageinfo
	);
}
