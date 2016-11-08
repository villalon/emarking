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
	 if ($fs->file_exists($usercontext->id, 'mod_emarking', 'user', 4, '/', $pdffilename)) {
	 	$contents = $file->get_content();
	 }
	 $fileinfo = $fs->create_file_from_pathname($filerecord, $pathname);
return $itemid;
   
}
/**
 * Saves a new instance of the emarking into the database
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $emarking
 *        	An object from the form in mod_form.php
 * @param mod_emarking_mod_form $mform        	
 * @return int The id of the newly inserted emarking record
 */
function emarking_add_instance(stdClass $data,$itemid, mod_emarking_mod_form $mform = null) {
	global $DB, $CFG, $COURSE, $USER;
	$data->timecreated = time ();
	$id = $DB->insert_record ( 'emarking', $data );
	$data->id = $id;
	$course = $data->course;
	emarking_grade_item_update ( $data );
	
	foreach ( $data as $k => $v ) {
		$parts = explode ( '-', $k );
		if (count ( $parts ) > 1 && $parts [0] === 'marker') {
			$markerid = intval ( $parts [1] );
			$marker = new stdClass ();
			$marker->emarking = $id;
			$marker->marker = $markerid;
			$marker->qualitycontrol = 1;
			$DB->insert_record ( 'emarking_markers', $marker );
		}
	}
	// entregar id del curso
	$context = context_course::instance ( $course );
	$examid = 0;
	// If there's no previous exam to associate, and we are creating a new
	// EMarking, we need the PDF file.
	
	if ($data->exam == 0) {
		$examfiles = emarking_validate_exam_files_from_draft ($itemid);
		if (count ( $examfiles ) == 0) {
			throw new Exception ( 'Invalid PDF exam files' );
		}
		$numpages = $examfiles [0] ['numpages'];
	} else {
		$examid = $data->exam;
	}
	
	
	//$studentsnumber = emarking_get_students_count_for_printing ( $course );
	$studentsnumber=2;
	// A new exam object is created and its attributes filled from form data.
	if ($examid == 0) {
		$exam = new stdClass ();
		$exam->course = $course;
		$exam->courseshortname = $COURSE->shortname;
		$exam->name = "a";
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
		$exam->totalpages = 2;
		$exam->printingcost = emarking_get_category_cost ( $course );
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
	} else {
		$exam = $DB->get_record ( "emarking_exams", array (
				"id" => $examid 
		) );
		$exam->emarking = $id;
		$exam->timemodified = time ();
		$DB->update_record ( 'emarking_exams', $exam );
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
	return $id;
}
/**
 * Creates a copy of the emarking in the database.
 *
 * @param unknown $originalemarking        	
 * @return boolean|multitype:unknown NULL Ambigous <boolean, number>
 */
function emarking_copy_to_cm($originalemarking, $destinationcourse,$itemid) {
	global $CFG, $DB;
	require_once ($CFG->dirroot . "/course/lib.php");
	
	
	$emarkingmod = $DB->get_record ( 'modules', array (
			'name' => 'emarking' 
	) );
	$emarking = new stdClass ();
	$emarking = $originalemarking;
	$emarking->id = null;
	$emarking->course = $destinationcourse;
	$emarking->id = emarking_add_instance ( $emarking,$itemid );
	// Add coursemodule.
	$mod = new stdClass ();
	$mod->course = $destinationcourse;
	$mod->module = $emarkingmod->id;
	$mod->instance = $emarking->id;
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
			'id'=>$emarking->id,
			'cmid'=>$cmid,
			'sectionid'=>$sectionid 
	);
}
/**
 * Creates or updates grade item for the give emarking instance
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $emarking
 *        	instance object with extra cmidnumber and modname property
 * @param null $grades        	
 * @return int 0 if ok, error code otherwise
 */
function emarking_grade_item_update(stdClass $emarking, $grades = null) {
	global $CFG;
	require_once ($CFG->libdir . '/gradelib.php');
	
	if ($grades == null) {
		emarking_calculate_grades_users ( $emarking );
	}
	$params = array ();
	$params ['itemname'] = clean_param ( $emarking->name, PARAM_NOTAGS );
	$params ['gradetype'] = GRADE_TYPE_VALUE;
	$params ['grademax'] = $emarking->grade;
	$params ['grademin'] = $emarking->grademin;
	if ($grades === 'reset') {
		$params ['reset'] = true;
		$grades = null;
	}
	$ret = grade_update ( 'mod/emarking', $emarking->course, 'mod', 'emarking', $emarking->id, 0, $grades, $params );
	emarking_publish_all_grades ( $emarking );
	return $ret;
}
/**
 * Erraces all the content of a directory, then ir creates te if they don't exist.
 *
 * @param unknown $dir
 *            Directorio
 * @param unknown $delete
 *            Borrar archivos previamente
 */
function emarking_initialize_directory($dir, $delete) {
	if ($delete) {
		// First erase all files.
		if (is_dir($dir)) {
			emarking_rrmdir($dir);
		}
	}
	// Si no existe carpeta para temporales se crea.
	if (!is_dir($dir)) {
		if (!mkdir($dir, 0777, true)) {
			print_error(get_string('initializedirfail', 'mod_emarking', $dir));
		}
	}
}

/**
 * Recursively remove a directory.
 * Enter description here ...
 *
 * @param unknown_type $dir
 */
function emarking_rrmdir($dir) {
	foreach(glob($dir . '/*') as $file) {
		if (is_dir($file)) {
			emarking_rrmdir($file);
		} else {
			unlink($file);
		}
	}
	rmdir($dir);
}
/**
 * Devuelve el path por defecto de archivos temporales de emarking.
 * Normalmente debiera ser moodledata\temp\emarking
 *
 * @param unknown $postfix
 *            Postfijo (típicamente el id de assignment)
 * @return string El path al directorio temporal
 */
function emarking_get_temp_dir_path($postfix) {
	global $CFG;
	return $CFG->dataroot . "/temp/emarking/" . $postfix;
}
/**
 * 
 * @param unknown $emarking
 * @param number $userid
 * @return void|boolean
 */
function emarking_calculate_grades_users($emarking, $userid = 0) {
    global $DB, $USER, $CFG;
    require_once($CFG->dirroot . '/grade/grading/lib.php');
    if (! $cm = get_coursemodule_from_instance('emarking', $emarking->id)) {
        return;
    }
    if ($emarking->type != EMARKING_TYPE_ON_SCREEN_MARKING && $emarking->type != EMARKING_TYPE_PEER_REVIEW) {
        return;
    }
    $context = context_module::instance($cm->id);
    // Get the grading manager, then method and finally controller.
    $gradingmanager = get_grading_manager($context, 'mod_emarking', 'attempt');
    $gradingmethod = $gradingmanager->get_active_method();
    $controller = $gradingmanager->get_controller($gradingmethod);
    $range = $controller->get_grade_range();
    $rubricscores = $controller->get_min_max_score();
    $totalrubricscore = $rubricscores ['maxscore'];
    $filter = 'WHERE 1=1';
    if ($userid > 0) {
        $filter = 'WHERE es.student = ' . $userid;
    }
    $studentscores = $DB->get_records_sql(
            "
			SELECT es.id,
			es.student,
	        d.id as draftid,
			sum(ifnull(rl.score,0)) as score,
			sum(ifnull(ec.bonus,0)) as bonus,
			sum(ifnull(rl.score,0)) + sum(ifnull(ec.bonus,0)) as totalscore
			FROM {emarking_submission} es
			INNER JOIN {emarking_page} ep ON (es.emarking = :emarking AND ep.submission = es.id)
			INNER JOIN {emarking_draft} d ON (d.submissionid = es.id AND d.qualitycontrol = 0)
			LEFT JOIN {emarking_comment} ec ON (ec.page = ep.id AND ec.levelid > 0 AND ec.draft = d.id)
			LEFT JOIN {gradingform_rubric_levels} rl ON (ec.levelid = rl.id)
			$filter
			AND d.status >= 10
	        AND rl.id IS NOT NULL
			GROUP BY es.emarking, es.id", array(
                'emarking' => $emarking->id));
    foreach ($studentscores as $studentscore) {
        $totalscore = min(floatval($studentscore->totalscore), $totalrubricscore);
        $finalgrade = emarking_calculate_grade($emarking, $totalscore, $totalrubricscore);
        $submission = $DB->get_record('emarking_submission', array(
            'id' => $studentscore->id));
        $submission->grade = $finalgrade;
        $DB->update_record('emarking_submission', $submission);
        $draft = $DB->get_record('emarking_draft', array(
            'id' => $studentscore->draftid));
        $draft->grade = $finalgrade;
        $DB->update_record('emarking_draft', $draft);
    }
    return true;
}
/**
 * Calculates the grade according to score
 * and corrects if there is a slope adjustment
 *
 * @param unknown $emarking
 * @param unknown $totalscore
 * @param unknown $totalrubricscore
 * @return Ambigous <number, mixed>
 */
function emarking_calculate_grade($emarking, $totalscore, $totalrubricscore) {
	if (isset($emarking->adjustslope) && $emarking->adjustslope) {
		$finalgrade = min($emarking->grade,
				((($emarking->adjustslopegrade - $emarking->grademin) / $emarking->adjustslopescore) * $totalscore) +
				$emarking->grademin);
	} else {
		$finalgrade = ((($emarking->grade - $emarking->grademin) / $totalrubricscore) * $totalscore) + $emarking->grademin;
	}
	return $finalgrade;
}
/**
 *
 * @param unknown $emarking
 * @return void|boolean
 */
function emarking_publish_all_grades($emarking) {
	global $DB, $USER, $CFG;
	
		return;
	
}
function emarking_validate_exam_files_from_draft($itemid) {
	global $USER, $COURSE;
	// We get the draftid from the form.
	;
	
	$usercontext = context_user::instance($USER->id);
	$fs = get_file_storage();
	$files = $fs->get_area_files($usercontext->id, 'user', 'exam_files',$itemid);

	$tempdir = emarking_get_temp_dir_path($COURSE->id);
	emarking_initialize_directory($tempdir, true);
	$numpagesprevious = - 1;
	$exampdfs = array();
	
	foreach ($files as $uploadedfile) {
		if ($uploadedfile->is_directory() ||
				$uploadedfile->get_mimetype() !== 'application/pdf') {
					continue;
					
				}
				
				$filename = $uploadedfile->get_filename();
				$filename = emarking_clean_filename($filename);
				$newfilename = $tempdir . '/' . $filename;
				$pdffile = emarking_get_path_from_hash($tempdir, $uploadedfile->get_pathnamehash());
				// Executes pdftk burst to get all pages separated.
				$numpages = emarking_pdf_count_pages($newfilename, $tempdir, false);
				$exampdfs [] = array(
						'pathname' => $pdffile,
						'filename' => $filename,
						'numpages' => $numpages
				);
	}
	return $exampdfs;
}
/**
 * Replace "acentos", spaces from file names.
 * Evita problemas en Windows y Linux.
 *
 * @param unknown $filename
 *            El nombre original del archivo
 * @return unknown El nombre sin acentos, espacios.
 */
function emarking_clean_filename($filename, $slash = false) {
	$replace = array(
			' ',
			'á',
			'é',
			'í',
			'ó',
			'ú',
			'ñ',
			'Ñ',
			'Á',
			'É',
			'Í',
			'Ó',
			'Ú',
			'(',
			')',
			',',
			'&',
			'#'
	);
	$replacefor = array(
			'-',
			'a',
			'e',
			'i',
			'o',
			'u',
			'n',
			'N',
			'A',
			'E',
			'I',
			'O',
			'U',
			'-',
			'-',
			'-',
			'-',
			'-'
	);
	if ($slash) {
		$replace[] = '/';
		$replacefor[] = '-';
	}
	$newfile = str_replace($replace, $replacefor, $filename);
	return $newfile;
}
/**
 * Esta funcion copia el archivo solicitado mediante el Hash (lo busca en la base de datos) en la carpeta temporal especificada.
 *
 * @param String $tempdir
 *            Carpeta a la cual queremos copiar el archivo
 * @param String $hash
 *            hash del archivo en base de datos
 * @param String $prefix
 *            ???
 * @return mixed
 */
function emarking_get_path_from_hash($tempdir, $hash, $prefix = '', $create = true) {
	global $CFG;
	// Obtiene filesystem.
	$fs = get_file_storage();
	// Obtiene archivo gracias al hash.
	if (!$file = $fs->get_file_by_hash($hash)) {
		return false;
	}
	// Se copia archivo desde Moodle a temporal.
	$newfile = emarking_clean_filename($tempdir . '/' . $prefix . $file->get_filename());
	$file->copy_content_to($newfile);
	return $newfile;
}
/**
 * Extracts all pages in a big PDF file as separate PDF files, deleting the original PDF if successfull.
 *
 * @param unknown $newfile
 *            PDF file to extract
 * @param unknown $tempdir
 *            Temporary folder
 * @param string $doubleside
 *            Extract every two pages (for both sides scanning)
 * @return number unknown number of pages extracted
 */
function emarking_pdf_count_pages($newfile, $tempdir, $doubleside = true) {
	global $CFG;
	if ($CFG->version > 2015111600) {
		require_once ($CFG->dirroot . "/lib/pdflib.php");
		require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi_bridge.php");
	} else {
		require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php");
	}
	require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi.php");
	$doc = new FPDI();
	$files = $doc->setSourceFile($newfile);
	$doc->Close();
	return $files;
}
function emarking_get_category_cost($courseid) {
	global $DB, $CFG;
	$course = $DB->get_record('course', array(
			'id' => $courseid
	), 'id, category');
	$coursecategory = $course->category;
	$categorycost = null;
	$noinfloop = 0;
	while ($categorycost == null || $categorycost == 0) {
		$categorycostparams = array(
				$coursecategory
		);
		$sqlcategorycost = "SELECT cc.id, cc.name as name, ccc.printingcost AS cost, cc.parent as parent
        			  FROM mdl_course_categories as cc
			          LEFT JOIN mdl_emarking_category_cost AS ccc ON (cc.id = ccc.category)
        			  WHERE cc.id = ?";
		if ($categorycosts = $DB->get_records_sql($sqlcategorycost, $categorycostparams)) {
			foreach($categorycosts as $cost) {

				if ($cost->cost == null || $cost->cost == 0) {
					$coursecategory = $cost->parent;
					$noinfloop++;
				} else {
					$categorycost = $cost->cost;
					return $categorycost;
				}
				if ($cost->parent == 0) {
					$categorycost = $CFG->emarking_defaultcost;
					return $categorycost;
				}
			}
		}
	}
}
