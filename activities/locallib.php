<?php 
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');

/**
 * Function to create the table for rubrics
 * 
 * @param string $id
 *            
 * @return the table with the rubric's data
 */
function show_rubric($id) {
	global $DB;
$sql="SELECT grl.id,
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
	
$rubric=$DB->get_records_sql($sql);
	
	
	foreach ($rubric as $data) {
	
		$tableData[$data->description][$data->definition]=$data->score;
	}
	
	$col=0;
	foreach ($tableData as $calc) {
	
		$actualcol=sizeof($calc);
		if($col < $actualcol){
			$col=$actualcol;
		}
	
	}
	$row=sizeof($table);
	$table ="";
	$table .='<table class="table table-bordered">';
	$table .='<thead>';
	$table .='<tr>';
	$table .='<td>';
	$table .='</td>';
	
	for ($i=1; $i <= $col; $i++) {
		$table .='<th>Nivel '.$i.'</th>';
	}
	
	     				   
	    $table .='</tr>';
	   	$table .='</thead>';
	   	$table .='<tbody>';
	
	 
	   	foreach ($tableData as $key => $value) {
	   		
	   		$table .='<tr>';   				    		
	   				    		$table .='<th>'.$key.'</th>';
	   				    		foreach ($value as $level => $score) {
	   				    			$table .='<th>'.$level.'</th>';
	   				    		}
	
	   				    		$table .='</tr>';
	   				    	}
	   				    	$table .='</tbody>';
	   				    	$table .='</table>';
	   				    		
	
 return $table;	
 
}

function show_result($data){
	GLOBAL $CFG;
	
	$activityUrl= new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php',array('id'=>$data->id));
	$oaComplete=explode("-",$data->learningobjectives);
	$coursesOA="";
	foreach($oaComplete as $oaPerCourse){
	
		$firstSplit=explode("[",$oaPerCourse);
		$secondSplit=explode("]",$firstSplit[1]);
		$course=$firstSplit[0];
	
		$coursesOA .='<p>Curso: '.$firstSplit[0].'° básico</p>';
		$coursesOA .='<p>OAs: '.$secondSplit[0].'</p>';
	}
	
	
	
	$show='<a href="'.$activityUrl.'">';
	$show.='<div id="resultados" class="col-md-12" style="text-align: left">';
	$show.='<div class="panel panel-default">';
	$show.='<div class="single-result-detail clearfix">';
	$show.='<div id="descripcion" class="panel-body">';
	$show.='<center><h3>'.$data->title.'</h3></center>';
	$show.='<div  class="col-md-4" style="text-align: left">';
	$show.=$coursesOA;
	$show.='<p>Propósito Comunicativo: Informar</p>';
	$show.='<p>Género: '.$data->genre.'</p>';
	$show.='<p>Audiencia: '.$data->audience.'</p>';
	$show.='<p>Tiempo estimado: 90 min.</p>';
	$show.='</div>';
	$show.='<div  class="col-md-5">';
	$show.='<p>'.$data->description.'</p>';
	$show.='</div>';
	$show.='<div  class="col-md-3" style="text-align: left">';
	$show.='<img src="img/premio.png" class="premio" height="40px" width="40px">';
	$show.='<p>55 Visitas</p>';
	$show.='<p>3 Comentarios</p>';
	$show.='<p>20 votos</p>';
	$show.='<span class="glyphicon glyphicon-star" aria-hidden="true"></span>';
	$show.='<span class="glyphicon glyphicon-star" aria-hidden="true"></span>';
	$show.='<span class="glyphicon glyphicon-star" aria-hidden="true"></span>';
	$show.='<span class="glyphicon glyphicon-star" aria-hidden="true"></span>';
	$show.='<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>';
	$show.='<p></p><p></p>';
		
	$show.='</div>';
	$show.='</div>';
	$show.='</div>';
	
	$show.='</div>';
	$show.='</div>';
	$show.='</a>';
	return $show;
	
}
/**
 * Saves a new instance of the emarking into the database
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $emarking
 *            An object from the form in mod_form.php
 * @param mod_emarking_mod_form $mform
 * @return int The id of the newly inserted emarking record
 */
function emarking_add_instance(stdClass $data, mod_emarking_mod_form $mform = null) {
	global $DB, $CFG, $COURSE, $USER;
	$data->timecreated = time();
	$id = $DB->insert_record('emarking', $data);
	$data->id = $id;
	$course=$data->course;
	emarking_grade_item_update($data);
	
	foreach ($data as $k => $v) {
		$parts = explode('-', $k);
		if (count($parts) > 1 && $parts [0] === 'marker') {
			$markerid = intval($parts [1]);
			$marker = new stdClass();
			$marker->emarking = $id;
			$marker->marker = $markerid;
			$marker->qualitycontrol = 1;
			$DB->insert_record('emarking_markers', $marker);
		}
	}
	//entregar id del curso
	$context = context_course::instance($course);
	$examid = 0;
	// If there's no previous exam to associate, and we are creating a new
	// EMarking, we need the PDF file.
	if ($data->exam == 0) {
		$examfiles = emarking_validate_exam_files_from_draft();
		if(count($examfiles) == 0) {
			throw new Exception('Invalid PDF exam files');
		}
		$numpages = $examfiles[0]['numpages'];
	} else {
		$examid = $data->exam;
	}
	$studentsnumber = emarking_get_students_count_for_printing($course);
	// A new exam object is created and its attributes filled from form data.
	if ($examid == 0) {
		$exam = new stdClass();
		$exam->course = $course;
		$exam->courseshortname = $COURSE->shortname;
		$exam->name = $mform->get_data()->name;
		$exam->examdate = $mform->get_data()->examdate;
		$exam->emarking = $id;
		$exam->headerqr = isset($mform->get_data()->headerqr) ? 1 : 0;
		$exam->printrandom = isset($mform->get_data()->printrandom) ? 1 : 0;
		$exam->printlist = isset($mform->get_data()->printlist) ? 1 : 0;
		$exam->extrasheets = $mform->get_data()->extrasheets;
		$exam->extraexams = $mform->get_data()->extraexams;
		$exam->usebackside = isset($mform->get_data()->printdoublesided) ? 1 : 0;
		if ($examid == 0) {
			$exam->timecreated = time();
		}
		$exam->timemodified = time();
		$exam->requestedby = $USER->id;
		$exam->totalstudents = $studentsnumber;
		$exam->comment = $mform->get_data()->comment;
		// Get the enrolments as a comma separated values.
		$enrollist = array();
		if (! empty($mform->get_data()->enrolments)) {
			$enrolments = $mform->get_data()->enrolments;
			foreach ($enrolments as $key => $enrolment) {
				if (! empty($enrolment)) {
					$enrollist [] = $key;
				}
			}
		}
		$exam->enrolments = implode(",", $enrollist);
		$exam->printdate = 0;
		$exam->status = EMARKING_EXAM_UPLOADED;
		// Calculate total pages for exam.
		$exam->totalpages = $numpages;
		$exam->printingcost = emarking_get_category_cost($course);
		$exam->id = $DB->insert_record('emarking_exams', $exam);
		$fs = get_file_storage();
		foreach ($examfiles as $exampdf) {
			// Save the submitted file to check if it's a PDF.
			$filerecord = array(
					'component' => 'mod_emarking',
					'filearea' => 'exams',
					'contextid' => $context->id,
					'itemid' => $exam->id,
					'filepath' => '/',
					'filename' => $exampdf ['filename']);
			$file = $fs->create_file_from_pathname($filerecord, $exampdf ['pathname']);
		}
		// Update exam object to store the PDF's file id.
		$exam->file = $file->get_id();
		if (! $DB->update_record('emarking_exams', $exam)) {
			$fs->delete_area_files($contextid, 'emarking', 'exams', $exam->id);
			print_error(get_string('errorsavingpdf', 'mod_emarking'));
		}
		
	} else {
		$exam = $DB->get_record("emarking_exams", array(
				"id" => $examid));
		$exam->emarking = $id;
		$exam->timemodified = time();
		$DB->update_record('emarking_exams', $exam);
	}
	$headerqr = isset($mform->get_data()->headerqr) ? 1 : 0;
	setcookie("emarking_headerqr", $headerqr, time() + 3600 * 24 * 365 * 10, '/');
	$defaultexam = new stdClass();
	$defaultexam->headerqr = $exam->headerqr;
	$defaultexam->printrandom = $exam->printrandom;
	$defaultexam->printlist = $exam->printlist;
	$defaultexam->extrasheets = $exam->extrasheets;
	$defaultexam->extraexams = $exam->extraexams;
	$defaultexam->usebackside = $exam->usebackside;
	$defaultexam->enrolments = $exam->enrolments;
	setcookie("emarking_exam_defaults", json_encode($defaultexam), time() + 3600 * 24 * 365 * 10, '/');
	return $id;
}
/**
 * Creates a copy of the emarking in the database.
 *
 * @param unknown $originalemarking
 * @return boolean|multitype:unknown NULL Ambigous <boolean, number>
 */
function emarking_copy_to_cm($originalemarking, $destinationcourse) {
	global $CFG, $DB;
	require_once ($CFG->dirroot . "/course/lib.php");
	require_once ($CFG->dirroot . "/mod/emarking/mod_form.php");
	$emarkingmod = $DB->get_record('modules', array(
			'name' => 'emarking'));
	$emarking = new stdClass();
	$emarking = $originalemarking;
	$emarking->id = null;
	$emarking->course = $destinationcourse;
	$emarking->id = emarking_add_instance($emarking);
	// Add coursemodule.
	$mod = new stdClass();
	$mod->course = $destinationcourse;
	$mod->module = $emarkingmod->id;
	$mod->instance = $emarking->id;
	$mod->section = 0;
	$mod->visible = 0; // Hide the forum.
	$mod->visibleold = 0; // Hide the forum.
	$mod->groupmode = 0;
	$mod->grade = 100;
	if (! $cmid = add_course_module($mod)) {
		return false;
	}
	$sectionid = course_add_cm_to_section($mod->course, $cmid, 0);
	return array(
			$emarking->id,
			$cmid,
			$sectionid);
}
/**
 * Creates or updates grade item for the give emarking instance
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $emarking
 *            instance object with extra cmidnumber and modname property
 * @param null $grades
 * @return int 0 if ok, error code otherwise
 */
function emarking_grade_item_update(stdClass $emarking, $grades = null) {
	global $CFG;
	require_once ($CFG->libdir . '/gradelib.php');
	require_once ($CFG->dirroot . '/mod/emarking/marking/locallib.php');
	if ($grades == null) {
		emarking_calculate_grades_users($emarking);
	}
	$params = array();
	$params ['itemname'] = clean_param($emarking->name, PARAM_NOTAGS);
	$params ['gradetype'] = GRADE_TYPE_VALUE;
	$params ['grademax'] = $emarking->grade;
	$params ['grademin'] = $emarking->grademin;
	if ($grades === 'reset') {
		$params ['reset'] = true;
		$grades = null;
	}
	$ret = grade_update('mod/emarking', $emarking->course, 'mod', 'emarking', $emarking->id, 0, $grades, $params);
	emarking_publish_all_grades($emarking);
	return $ret;
}