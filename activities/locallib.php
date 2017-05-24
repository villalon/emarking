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

function activities_show_result($data) {
	GLOBAL $CFG, $DB;
	
	$activityUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/activity.php', array (
			'id' => $data->id 
	) );
	$coursesOA = '<span>Curso: </span><br>';
	$coursesOA .= '<span>OAs:</span><br>';
	if( isset($activity->learningobjectives) && $activity->learningobjectives != null){
		$oaComplete = explode ( "-", $data->learningobjectives );
		foreach ( $oaComplete as $oaPerCourse ) {
			
			$firstSplit = explode ( "[", $oaPerCourse );
			$secondSplit = explode ( "]", $firstSplit [1] );
			$course = $firstSplit [0];
			
			$coursesOA .= '<span>Curso: ' . $firstSplit [0] . '° básico</span><br>';
			$coursesOA .= '<span>OAs: ' . $secondSplit [0] . '</span><br>';
		}
	}
	$userobject = $DB->get_record('user', array('id' => $data->userid));
	
	//Busca toda la información de la comunidad en esta actividad
	$communitysql = $DB->get_record('emarking_social', array('activityid' => $data->id));	
	if( !$communitysql ){	
		$communitysql=new stdClass ();
		$communitysql->activityid 			= $data->id;
		$communitysql->timecreated         	= time();
		$communitysql->data					= null;
		$DB->insert_record ( 'emarking_social', $communitysql );
		$average = 0;
	}
	$countvotes = 0;
	$countcomments = 0;
	$average = 0;
	if( isset($communitysql->data) && $communitysql->data != null ){
		$recordcleaned = emarking_activities_clean_string_to_json($communitysql->data);
		$decode = json_decode($recordcleaned);
		$social = $decode->data;
		$comments = $social->Comentarios;
		$votes = $social->Vote;
		$countvotes = count($votes);
		$countcomments = count($comments);
		if($countvotes > 0){
			$average = get_average($votes);
		}		
	}
	include ($CFG->dirroot. '/mod/emarking/activities/views/showresult.php');
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
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetTopMargin(40);
$pdf->SetRightMargin(10);
$pdf->SetLeftMargin(10);
// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();
$css='

<style>
   body {
        font-family: "Trebuchet MS", "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", Tahoma, sans-serif;
    }
	p {
		font-size: 12pt;
	}
	h3, p {
		margin-top: 1px;
		margin-bottom: 1px;
	}
	table {
    	border: 1px solid black;
	}
	td {
		width: 100%;
		border-bottom: 1px dotted #999;
		height: 10px;
	}
    </style>
   ';
$html =$css;

$pdf->writeHTML($css, true, false, false, false, '');

if(isset($sections->header) && $sections->header == 1) {
	$pdf->writeHTML('<h1>'.$activity->title.'</h1> ', true, false, false, false, '');
}
if(isset($sections->instructions)&&$sections->instructions==1){
	$pdf->writeHTML('<h3>Instrucciones</h3> ', true, false, false, false, '');
	$instructionshtml=emarking_activities_add_images_pdf($activity->instructions,$usercontext);
	$instructionshtml=emarking__activities_clean_html_to_print($instructionshtml);
	$pdf->writeHTML($instructionshtml, true, false, false, false, '');
}

if(isset($sections->planification)&&$sections->planification==1) {
	$planificationhtml=emarking_activities_add_images_pdf($activity->planification,$usercontext);
	$pdf->writeHTML('<h3>Planificación</h3>', true, false, false, false, '');
	$planificationhtml=emarking__activities_clean_html_to_print($planificationhtml);
	$pdf->writeHTML($planificationhtml, true, false, false, false, '');
}

if(isset($sections->writing)&&$sections->writing==1) {
	$writinghtml=emarking_activities_add_images_pdf($activity->writing,$usercontext);
	$writinghtml=emarking__activities_clean_html_to_print($writinghtml);

	$pdf->writeHTML('<h3>Escritura</h3>', true, false, false, false, '');
	$pdf->writeHTML($writinghtml, true, false, false, false, '');

	emarking_pdf_fill_writing_table($pdf);	
	$pdf->AddPage();
	
	emarking_pdf_fill_writing_table($pdf);	
	$pdf->AddPage();
}

if(isset($sections->editing) && $sections->editing==1){

$editinghtml=emarking_activities_add_images_pdf($activity->editing,$usercontext);
$editinghtml ='<h3>Revisión y edición</h3>';
$editinghtml.=emarking__activities_clean_html_to_print($editinghtml);
$pdf->writeHTML($editinghtml, true, false, false, false, '');
}
// $pdf->writeHTML($html, true, false, false, false, '');
if(isset($sections->teaching) && $sections->teaching==1){

$teachinghtml=emarking_activities_add_images_pdf($activity->teaching,$usercontext);
$pdf->writeHTML('<h3>Sugerencias didácticas</h3>', true, false, false, false, '');
$teachinghtml=emarking__activities_clean_html_to_print($teachinghtml);
$pdf->writeHTML($teachinghtml, true, false, false, false, '');
}

if(isset($sections->resources) && $sections->resources==1){

$languageresourceshtml=emarking_activities_add_images_pdf($activity->languageresources,$usercontext);
$pdf->writeHTML('<h3>Recursos del lenguaje</h3>', true, false, false, false, '');
$languageresourceshtml=emarking__activities_clean_html_to_print($languageresourceshtml);
$pdf->writeHTML($languageresourceshtml, true, false, false, false, '');
}

if(isset($sections->rubric)&&$sections->rubric==1){
$pdf->AddPage();
$rubrichtml=show_rubric($activity->rubricid);
$pdf->writeHTML('<h3>Evaluación</h3>', true, false, false, false, '');
$rubrichtml=emarking__activities_clean_html_to_print($rubrichtml);
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
function emarking_pdf_fill_writing_table(TCPDF $pdf) {
	$footermargin = $pdf->getFooterMargin();
	$pdf->SetAutoPageBreak(false);
	$linewidth = emarking_pdf_linewidth($pdf);
	$lineheight = 8;
	$spaceleft = emarking_pdf_spaceleft($pdf);
	
	$rows = $spaceleft / ($lineheight + 1);
	for($i=0; $i<$rows; $i++) {
		if($i==0)
			$pdf->Cell($linewidth, $lineheight,' ',
					array(
							'TRL'=>
							array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
							'B'=>
							array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(200, 200, 200))
					)
					,1);
			elseif($i<$rows-1)
			$pdf->Cell($linewidth, $lineheight,' ',
					array(
							'RL'=>
							array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
							'B'=>
							array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(200, 200, 200))
					)
					,1);
			else
				$pdf->Cell($linewidth, $lineheight,' ',
						array(
								'BRL'=>
								array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))
						)
						,1);
	}
	$pdf->SetAutoPageBreak(true, $footermargin);
}

function emarking_pdf_spaceleft(TCPDF $pdf) {
	$currentY = $pdf->GetY();
	$fmargin = $pdf->getFooterMargin();
	$height = $pdf->getPageHeight();
	$spaceleft = $height - $fmargin - $currentY;
	return $spaceleft;
}
function emarking_pdf_linewidth(TCPDF $pdf) {
	$lmargin = $pdf->getMargins()['left'];
	$rmargin = $pdf->getMargins()['right'];
	$width = $pdf->getPageWidth();
	$linewidth = $width - $lmargin - $rmargin;
	return $linewidth;
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
	$mod->visible = 1; // Hide the forum.
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
/**
 * Limpia el HTML producido por una pregunta de un quiz
 *
 * @param String $html
 * @return String
 */
function emarking__activities_clean_html_to_print($html)
{
	$html = preg_replace ( '!\s+!', ' ', $html );
	$html = preg_replace('/<tbody\s*>/', '', $html);
	$html = preg_replace('/<\/tbody>/', '', $html);
	$html = preg_replace('/<td(.*?)>/', '<td>', $html);
	$html = preg_replace('/border="\d+"/', '', $html);
	$html = preg_replace('/<table(.*?)>/', '<br/><table border="1">', $html);
	$html = preg_replace('/<div>(<input.*?)<\/div>/', '<br/>$1', $html);

	return $html;
}
/**
 * Limpia el texto de una actividad
 *
 * @param String $html
 * @return String
 */
function emarking_activities_clean_html_text($html)
{
	$html = preg_replace ( '!\s+!', ' ', $html );
	$html = preg_replace ( '/<p(.*?)>/', '<p align="justify">', $html);
	$html = preg_replace ( '/<span(.*?)>/', '<span>', $html);
	$html = preg_replace('/<table(.*?)>/', '<table class="table table-bordered">', $html);
	$html = preg_replace ( '/<td(.*?)>/', '<td>', $html);
	$html = preg_replace ( '/<tbody(.*?)>/', '', $html );
	$html = preg_replace ( '/<td> <\/td>/', '', $html);
	$html = preg_replace ( '/<h1(.*?)>/', '<h1>', $html );
	$html = preg_replace ( '/<h2(.*?)>/', '<h2>', $html );
	$html = preg_replace ( '/<h3(.*?)>/', '<h3>', $html );
	$html = preg_replace ( '/<h4(.*?)>/', '<h4>', $html );
	$html = preg_replace ( '/<h5(.*?)>/', '<h5>', $html );
	
	return $html;
}
/**
 * Limpia una cadena de string para ser transformado en json
 *
 * @param String $html
 * @return String
 */
function emarking_activities_clean_string_to_json($string) {
	$bodytag = str_replace ( '"[\\', "[", $string );
	$bodytag2 = str_replace ( '\\"', '"', $bodytag );
	$bodytag3 = str_replace ( ']"', ']', $bodytag2 );
	$bodytag4 = str_replace ( '"[', '[', $bodytag3 );
	
	return $bodytag4;
}
function rating($userid, $activityid, $stars) {
	global $DB;
	$communitysql = $DB->get_record ( 'emarking_social', array (
			'activityid' => $activityid 
	) );
	
	if (isset ( $communitysql->data ) && $communitysql->data != null) {
		
		$recordcleaned = emarking_activities_clean_string_to_json ( $communitysql->data );
		$decode = json_decode ( $recordcleaned );
		$social = $decode->data;
		$comments = $social->Comentarios;
		$commentsjson = json_encode ( $comments, JSON_UNESCAPED_UNICODE );
		$votes = $social->Vote;
		if (! isset ( $votes )) {
			
			$votes = array (
					array (
							'userid' => $userid,
							'rating' => $stars 
					) 
			);
			$votesjson = json_encode ( $votes, JSON_UNESCAPED_UNICODE );
			$data = Array (
					"Vote" => $votesjson,
					"Comentarios" => $commentsjson 
			)
			;
			$communitysql->data = $data;
			$dataarray = Array (
					"data" => $data 
			);
			$datajson = json_encode ( $dataarray, JSON_UNESCAPED_UNICODE );
			$communitysql->data = $datajson;
			
			$DB->update_record ( 'emarking_social', $communitysql );
			
			return get_average ( $votes );
		} else {
			
			if (if_user_has_voted ( $votes, $userid )) {
				
				$rating = new stdClass ();
				$rating->userid = $userid;
				$rating->rating = $stars;
				$votes [] = $rating;
				$votesjson = json_encode ( $votes, JSON_UNESCAPED_UNICODE );
				$newdata = Array (
						"Vote" => $votes,
						"Comentarios" => $commentsjson 
				);
				
				$dataarray = Array (
						"data" => $newdata 
				);
				
				$datajson = json_encode ( $dataarray, JSON_UNESCAPED_UNICODE );
				$communitysql->data = $datajson;
				
				$DB->update_record ( 'emarking_social', $communitysql );
				return get_average ( $votes );
			}
		}
	} else {
		$votes = array (
				array (
						'userid' => $userid,
						'rating' => $stars 
				) 
		);
		$votesjson = json_encode ( $votes, JSON_UNESCAPED_UNICODE );
		$data = Array (
				"Vote" => $votesjson,
				"Comentarios" => null 
		)
		;
		$dataarray = Array (
				"data" => $data 
		);
		$datajson = json_encode ( $dataarray, JSON_UNESCAPED_UNICODE );
		$communitysql->data = $datajson;
		$voteObject = new stdClass ();
		$voteObject->userid=$userid;
		$voteObject->rating=$stars;
		$arrayVote[]=$voteObject;
		$DB->update_record ( 'emarking_social', $communitysql );

		return get_average ( $arrayVote );
	}
}

function if_user_has_voted($array, $userid) {
	foreach ( $array as $object ) {
		if (isset ( $object->userid ) && $object->userid == $userid){
			return $object->rating;
		}
	}
	return true;
}
function get_average($array) {
	$sum = 0;
	$count = 0;
	foreach ( $array as $object ) {
		$sum = $sum + ( int ) $object->rating;
		$count ++;
	}
	$average = $sum / $count;
	return round($average);
}

function get_criteria($id,$bool=false){
	GLOBAL $DB,$USER;
$sql="SELECT rl.*, rc.description as criterion, i.max
FROM mdl_emarking_rubrics_levels as rl
INNER JOIN mdl_emarking_rubrics_criteria rc ON (rc.id = rl.criterionid )
LEFT JOIN (select criterionid, max(score) as max FROM mdl_emarking_rubrics_levels as rl group by criterionid) as i on (i.criterionid=rl.criterionid)
WHERE rl.criterionid=?
ORDER BY rl.criterionid ASC, rl.score DESC";
$result = $DB->get_records_sql($sql, array($id));
$criteriaarray=Array();
$levelarray=Array();
$levelidarray=Array();

foreach ($result as $level){
	$criteriaarray['criteria']=$level->criterion;
	$levelarray[$level->score]=$level->definition;
	$criteriaarray['maxscore']=$level->max;
	if($bool){
		$criteriaarray['criterionid']=$level->criterionid;
		$levelidarray[$level->score]=$level->id;
	}
}
if (!array_key_exists(1, $levelarray))
	$levelarray[1]="";
if (!array_key_exists(2, $levelarray))
	$levelarray[2]="";
if (!array_key_exists(3, $levelarray))
	$levelarray[3]="";
if (!array_key_exists(4, $levelarray))
	$levelarray[4]="";
krsort($levelarray);
$criteriaarray['levels']=$levelarray;
if($bool){
$criteriaarray['levelids']=$levelidarray;
}
$criteriaarray['bool']=$bool;
$tojson=json_encode($criteriaarray);
return $tojson;

}

function insert_rubric($data,$activityid){
	GLOBAL $DB, $USER, $CFG;
	$rubric = new stdClass ();
	$rubric->name = $data ['rubricname'];
	$rubric->description = $data ['rubricdescription'];
	$rubric->usercreated = $USER->id;
	$rubric->timecreated = time ();
	$rubricid = $DB->insert_record ( 'emarking_rubrics', $rubric );
	if(isset($data ['criteria'])&& $criterias = $data ['criteria']){
	foreach ( $criterias as $key => $criteria ) {

		$crit = new stdClass ();
		$crit->rubricid = $rubricid;
		$crit->description = $criteria;
		$inssertcriteria = $DB->insert_record ( 'emarking_rubrics_criteria', $crit );
		if(isset($data ['level'])&& $levels = $data ['level']){
		foreach ( $levels [$key] as $score => $level ) {
			if($level!=null){
				$lev = new stdClass ();
				$lev->criterionid=$inssertcriteria;
				$lev->score=$score;
				$lev->definition=$level;
				$DB->insert_record ( 'emarking_rubrics_levels', $lev );
			}
		}
		}
	}
	}
	$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));
	$activity->rubricid=$rubricid;
	$DB->update_record('emarking_activities', $activity);
	$activityUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php', array('id' => $activity->id));
	redirect($activityUrl, 0);
}
function update_rubric($id,$data){
	Global $USER,$DB;
	$rubric = new stdClass ();
	$rubric->id=$id;
	$rubric->name = $data['rubricname'];
	$rubric->description = $data ['rubricdescription'];
	$rubric->usermodified = $USER->id;
	$rubric->timemodified = time ();
	$DB->update_record('emarking_rubrics', $rubric);
	$criterias = $data['criteria'];
	$criteriaid=$data['criteriaid'];
	$levels=$data['level'];
	$levelsid=$data['levelid'];
	//elimino criterios borrados de la rúbrica
	$criteriosDB=$DB->get_records('emarking_rubrics_criteria', array('rubricid'=>$rubric->id));
	foreach($criteriosDB as $criterioDB){
		if (!in_array($criterioDB->id, $criteriaid)) {
			$DB->delete_records('emarking_rubrics_criteria', array('id'=>$criterioDB->id));
		}
	}
	
	for ($i = 1; $i <= count($criterias); $i++) {
		$criteriaRecord = new stdClass ();
		$criteriaRecord->rubricid=$rubric->id;
		$criteriaRecord->description=$criterias[$i];
		//si existe un criterio con ese id, se hace update en la BD
		if($criteriaid[$i]!=null&&$criteriaRecord->id=$criteriaid[$i]){
		$DB->update_record('emarking_rubrics_criteria', $criteriaRecord);
		}//No existe criterio por lo tanto se crea
		else{
			$criteriaRecord->id = $DB->insert_record ( 'emarking_rubrics_criteria', $criteriaRecord );
		}
		
		
		for ($k = 1; $k <= 4; $k++) {
			$levelRecord = new stdClass ();
			$levelRecord->criterionid=$criteriaRecord->id;
			$levelRecord->score=$k;
			$levelRecord->definition=$levels[$i][$k];
			if($levelsid[$i][$k]!=null&&$levelRecord->id =$levelsid[$i][$k]){
			$DB->update_record('emarking_rubrics_levels', $levelRecord);
			}else{
			$DB->insert_record ( 'emarking_rubrics_levels', $levelRecord );
			}
		}
	}
}
function add_row($data,$type){
	$bol=true;
	$next=4;
	foreach ($data as $level){
		if($level->score == $level->max){
			$cols ='<tr">';
			$cols .= '<td class="col-sm-2" style="text-align: center;vertical-align: middle;">';
			$cols .= '<span>'.$level->criteria.'</span></td>';
		}
	
		if($next!=$level->score){
			if($next > $level->score){
				$cols .= '<td class="col-sm-2" style="vertical-align: middle;">';
				$cols .= '<span ></span></td>';
	
			}
			 
		}
		$cols .= '<td class="col-sm-2" style="vertical-align: middle;">';
		$cols .= '<span >'.$level->definition.'</span></td>';
	
	
		$next=$level->score-1;
		 
		 
		 
		if($level->score == 1){
			if($type==1){
			$cols .= '<td class="col-sm-1" style="vertical-align: middle;"><input type="button" class="ibtnDel btn btn-md btn-danger "  value="Borrar"></td>';
			}
			if($type==2){
		      		$cols .= '<td class="col-sm-1" style="vertical-align: middle;"><input type="button" id='.$level->criterionid.' class="ibtnAdd btn btn-md btn-success "  value="Agregar"></td>';
			}
			$cols .='</tr>';
			echo $cols;
			$next=4;
		}
	}
}
function add_new_activity_basic($fromform){
	global $DB,$USER, $CFG;
	require ($CFG->dirroot. '/mod/emarking/activities/generos.php');
	$genero = ( int ) $fromform->genre - 1;
	
	$oaCode=clean_oa_code($fromform);
	
	$record = new stdClass ();
	$record->title = $fromform->title;
	$record->description = $fromform->description;
	$record->learningobjectives = $oaCode;
	$record->comunicativepurpose = $fromform->comunicativepurpose;
	$record->genre = $generos [$genero];
	$record->audience = $fromform->audience;
	$record->estimatedtime = $fromform->estimatedtime;
	$record->timecreated = time ();
	$record->userid = $USER->id;
	$instertnewactivity = $DB->insert_record ( 'emarking_activities', $record );
	
	$socialrecord=new stdClass ();
	$socialrecord->activityid 			= $instertnewactivity;
	$socialrecord->timecreated         	= time();
	$socialrecord->data					= null;
	$DB->insert_record ( 'emarking_social', $socialrecord );
	
	return $instertnewactivity;
}
function edit_activity_basic($fromform,$activityid){
	global $DB,$CFG,$USER;
	require ($CFG->dirroot. '/mod/emarking/activities/generos.php');
	$genero = ( int ) $fromform->genre - 1;
	$oaCode=clean_oa_code($fromform);
	
	$record=$DB->get_record('emarking_activities',array('id'=>$activityid));
	$record->title = $fromform->title;
	$record->description = $fromform->description;
	$record->learningobjectives = $oaCode;
	$record->comunicativepurpose = $fromform->comunicativepurpose;
	$record->genre = $generos [$genero];
	$record->audience = $fromform->audience;
	$record->estimatedtime = $fromform->estimatedtime;
	$DB->update_record('emarking_activities', $record);
	
}
function add_new_activity_instructions($fromform,$activityid,$context){
	global $DB, $USER;
	
	change_draft_area($fromform->instructions ['itemid'],$context->id,'instructions');
	
	$instructions = $fromform->instructions ['text'];
	$planification = $fromform->planification ['text'];
	$writing = $fromform->writing ['text'];
	$editing = $fromform->editing ['text'];
	
	//changing url of images 
	$instructions = change_images_url($instructions,$fromform->instructions ['itemid']);
	$planification = change_images_url($planification,$fromform->instructions ['itemid']);
	$writing = change_images_url($writing,$fromform->instructions ['itemid']);
	$editing = change_images_url($editing,$fromform->instructions ['itemid']);
	
	//cleaning html text
	$instructions = emarking_activities_clean_html_text($instructions);
	$planification = emarking_activities_clean_html_text($planification);
	$writing = emarking_activities_clean_html_text($writing);
	$editing = emarking_activities_clean_html_text($editing);
	
	$record=$DB->get_record('emarking_activities',array('id'=>$activityid));
	$record->instructions = $instructions;
	$record->planification = $planification;
	$record->writing = $writing;
	$record->editing = $editing;
	$DB->update_record('emarking_activities', $record);
}
function add_new_activity_teaching($fromform,$activityid,$context){
	global $DB,$USER;
	change_draft_area($fromform->teaching ['itemid'],$context->id,'instructions');
	$teaching= $fromform->teaching ['text'];
	$lenguageresources= $fromform->languageresources ['text'];
	
	//changing url of images
	$teaching = change_images_url($teaching,$fromform->teaching ['itemid']);
	$lenguageresources = change_images_url($lenguageresources,$fromform->teaching ['itemid']);
	
	//cleaning html text
	$teaching = emarking_activities_clean_html_text($teaching);
	$lenguageresources = emarking_activities_clean_html_text($lenguageresources);
	$record=$DB->get_record('emarking_activities',array('id'=>$activityid));
	$record->teaching = $teaching;
	$record->languageresources = $lenguageresources;
	$DB->update_record('emarking_activities', $record);
	
}
function change_draft_area($itemid,$contextid,$area){
	
	$fs = get_file_storage ();
	file_save_draft_area_files ( $itemid, $contextid, 'mod_emarking', $area, $itemid );
	$files = $fs->get_area_files ( $contextid, 'mod_emarking', $area, $itemid, 'itemid, filepath, filename', false );
	
}
function change_images_url($obj,$itemid){
	global $USER;
	
	$usercontext = context_user::instance ( $USER->id );
	$urlAntigua = '/draftfile.php/' . $usercontext->id . '/user/draft/' . $itemid . '/';
	$urlnueva = '/pluginfile.php/1/mod_emarking/instructions/' . $itemid . '/';
	$obj = str_replace ( $urlAntigua, $urlnueva, $obj );
	return $obj;
}
function clean_oa_code($fromform){
	$OAC1 = "";
	if (isset ( $fromform->C1 )) {
	
		if (isset ( $fromform->CODC1 )) {
			foreach ( $fromform->CODC1 as $key => $value ) {
				$porciones = explode ( "C1OA", $key );
				$OAC1 .= $porciones [1] . ",";
			}
			$OAC1 = substr ( $OAC1, 0, - 1 );
			$OAC1 = $fromform->C1 . "[" . $OAC1 . "]";
		}
	}
	$OAC2 = "";
	if (isset ( $fromform->C2 )) {
		if (isset ( $fromform->CODC2 )) {
			foreach ( $fromform->CODC2 as $key => $value ) {
				$porciones = explode ( "C2OA", $key );
				$OAC2 .= $porciones [1] . ",";
			}
			$OAC2 = substr ( $OAC2, 0, - 1 );
			$OAC2 = "-" . $fromform->C2 . "[" . $OAC2 . "]";
		}
	}
	$OAC3 = "";
	if (isset ( $fromform->C3 )) {
		if (isset ( $fromform->CODC3 )) {
			foreach ( $fromform->CODC3 as $key => $value ) {
				$porciones = explode ( "C3OA", $key );
				$OAC3 .= $porciones [1] . ",";
			}
			$OAC3 = substr ( $OAC3, 0, - 1 );
			$OAC3 = "-" . $fromform->C3 . "[" . $OAC3 . "]";
		}
	}
	$oaCode = $OAC1 . $OAC2 . $OAC3;
	return $oaCode;
}

function emarking_activity_send_notification($cm,$userto) {
	global $CFG;
	
	$postsubject = 'Corrección asignada';
	$url= new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/marking.php', 
			array('id'=>$cm,
					'tab'=>1
			));
	// Create the email to be sent.
	$posthtml = '';
	$posthtml='<p>Estimado corrector se le ha asignado una actividad por corregir, para seguir el estado de esta actividad porfavor seguir este link <a href="'.$url.'">'.$url.'</a></p>';
	$posthtml .= '<p>Se le recuerda que tiene como plazo máximo 14 días</p>';
	// Create the email to be sent.
	$posttext = '';
		$eventdata = new stdClass();
		$eventdata->component = 'mod_emarking';
		$eventdata->name = 'notification';
		$eventdata->userfrom = $fromuser;
		$eventdata->userto = $userto;
		$eventdata->subject = $postsubject;
		$eventdata->fullmessage = $posttext;
		$eventdata->fullmessageformat = FORMAT_HTML;
		$eventdata->fullmessagehtml = $thismessagehtml;
		$eventdata->smallmessage = $postsubject;
		$eventdata->notification = 1;
		message_send($eventdata);
	
}