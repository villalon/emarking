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
 * @copyright 2012 Jorge Villalon <jorge.villalon@uai.cl>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * 
 * @param unknown $submission
 */
function emarking_multi_publish_grade($submission) {
	global $CFG, $DB, $USER;

	require_once ($CFG->libdir . '/gradelib.php');

	if ($submission->status <= EMARKING_STATUS_ABSENT)
		return;

	// Copy final grade to gradebook
	$grade_item = grade_item::fetch ( array (
			'itemmodule' => 'emarking',
			'iteminstance' => $submission->emarking
	) );

	$feedback = $submission->generalfeedback ? $submission->generalfeedback : '';

	$grade_item->update_final_grade ( $submission->student, $submission->grade, 'editgrade', $feedback, FORMAT_HTML, $USER->id );

	if ($submission->status <= EMARKING_STATUS_RESPONDED) {
		$submission->status = EMARKING_STATUS_RESPONDED;
	}

	$submission->timemodified = time ();
	$DB->update_record ( 'emarking_draft', $submission );

	$realsubmission = $DB->get_record ( "emarking_submission", array (
			"id" => $submission->id
	) );
	$realsubmission->status = $submission->status;
	$realsubmission->timemodified = $submission->timemodified;
	$realsubmission->generalfeedback = $submission->generalfeedback;
	$realsubmission->grade = $submission->grade;
	$realsubmission->teacher = $submission->teacher;
	$DB->update_record ( 'emarking_submission', $realsubmission );
}

/**
 *
 * @param unknown $emarking
 * @param unknown $submission
 * @param unknown $anonymous
 * @param unknown $context
 * @return multitype:stdClass
 */
function emarking_get_all_pages($emarking, $submission, $anonymous, $context) {
	global $DB, $CFG, $USER;

	$emarkingpages = array ();

	// Get criteria to filter pages
	$filterpages = false;
	$allowedpages = array ();

	// If user is supervisor, site admin or the student who owns the submission, we should not filter
	if (has_capability ( 'mod/emarking:supervisegrading', $context ) || is_siteadmin () || $USER->id == $submission->student) {
		$filterpages = false;
	} else if (
	// If it is another student (can't grade nor add instances) and peer visibility is allowed, we don't filter
	// but we force it as anonymous
	! has_capability ( 'mod/emarking:grade', $context ) && $emarking->peervisibility) {
		$filterpages = false;
		$anonymous = true;
	} else {
		// Remaining case is for markers
		$filterpages = true;

		$allowedpages = emarking_get_allowed_pages ( $emarking );
	}

	// In case there are no pages for this submission, we generate missing pages for those allowed
	if (! $pages = $DB->get_records ( 'emarking_page', array (
			'submission' => $submission->id
	), 'page ASC' )) {
		if ($emarking->totalpages > 0) {
			for($i = 0; $i < $emarking->totalpages; $i ++) {
				$emarkingpage = new stdClass ();
				$emarkingpage->url = $CFG->wwwroot . '/mod/emarking/pix/missing.png';
				$emarkingpage->width = 800;
				$emarkingpage->height = 1035;
				$emarkingpage->totalpages = $emarking->totalpages;
				if ($filterpages) {
					$emarkingpage->showmarker = array_search ( $i + 1, $allowedpages ) !== false ? 1 : 0;
				} else {
					$emarkingpage->showmarker = 1;
				}

				$emarkingpages [] = $emarkingpage;
			}
		}
		return $emarkingpages;
	}

	$fs = get_file_storage ();
	$numfiles = max ( count ( $pages ), $emarking->totalpages );
	$pagecount = 0;

	foreach ( $pages as $page ) {
		$pagecount ++;

		$pagenumber = $page->page;

		while ( count ( $emarkingpages ) < $pagenumber - 1 ) {
			$emarkingpage = new stdClass ();
			$emarkingpage->url = $CFG->wwwroot . '/mod/emarking/pix/missing.png';
			$emarkingpage->width = 800;
			$emarkingpage->height = 1035;
			$emarkingpage->totalpages = $numfiles;
				
			if ($filterpages) {
				$emarkingpage->showmarker = array_search ( count ( $emarkingpages ) + 1, $allowedpages ) !== false ? 1 : 0;
			} else {
				$emarkingpage->showmarker = 1;
			}
				
			$emarkingpages [] = $emarkingpage;
		}

		$fileid = $anonymous ? $page->fileanonymous : $page->file;
		if (! $file = $fs->get_file_by_id ( $fileid )) {
			$emarkingpage = new stdClass ();
			$emarkingpage->url = $CFG->wwwroot . '/mod/emarking/pix/missing.png';
			$emarkingpage->width = 800;
			$emarkingpage->height = 1035;
			$emarkingpage->totalpages = $numfiles;
				
			if ($filterpages) {
				$emarkingpage->showmarker = array_search ( $pagenumber, $allowedpages ) !== false ? 1 : 0;
			} else {
				$emarkingpage->showmarker = 1;
			}
				
			$emarkingpages [] = $emarkingpage;
		}

		if ($imageinfo = $file->get_imageinfo ()) {
			$imgurl = file_encode_url ( $CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/mod_emarking/pages/' . $submission->emarking . '/' . $file->get_filename () );
			$emarkingpage = new stdClass ();
			$emarkingpage->url = $imgurl . "?r=" . random_string ( 15 );
			$emarkingpage->width = $imageinfo ['width'];
			$emarkingpage->height = $imageinfo ['height'];
			$emarkingpage->totalpages = $numfiles;
				
			if ($filterpages) {
				$emarkingpage->showmarker = array_search ( $pagenumber, $allowedpages ) !== false ? 1 : 0;
			} else {
				$emarkingpage->showmarker = 1;
			}
				
			$emarkingpages [] = $emarkingpage;
		}
	}
	return $emarkingpages;
}

/**
 * Gets a list of the pages allowed to be seen and interact for this user
 *
 * @param unknown $emarking
 * @return array of page numbers
 */
function emarking_get_allowed_pages($emarking) {
	global $DB, $USER;

	$allowedpages = array ();

	// We add page 0 so array_search returns only positive values for normal pages
	$allowedpages [] = 0;

	// If there is criteria assigned for this emarking activity
	if ($criteria = $DB->get_records ( 'emarking_page_criterion', array (
			'emarking' => $emarking->id
	) )) {
		// Organize pages per criterion
		$criteriapages = array ();
		foreach ( $criteria as $cr ) {
			if (! isset ( $criteriapages [$cr->criterion] ))
				$criteriapages [$cr->criterion] = array ();
			$criteriapages [$cr->criterion] [] = $cr->page;
		}
		$filteredbycriteria = true;

		// Get criteria the user is allowed to see
		$usercriteria = $DB->get_records ( 'emarking_marker_criterion', array (
				'emarking' => $emarking->id,
				'marker' => $USER->id
		) );

		// Add pages to allowed array if the user can see them
		foreach ( $usercriteria as $uc ) {
			if (isset ( $criteriapages [$uc->criterion] ))
				$allowedpages = array_merge ( $allowedpages, $criteriapages [$uc->criterion] );
		}
		// If there is no criteria assigned, all pages are allowed
	} else {
		// Get the maximum page number in the emarking activity
		if ($max = $DB->get_record_sql ( '
				SELECT MAX(page) AS pagenumber
				FROM {emarking_submission} AS s
				INNER JOIN {emarking_page} AS p ON (p.submission = s.id AND s.emarking = :emarking)', array (
						'emarking' => $emarking->id
				) )) {
					for($i = 1; $i <= $max->pagenumber; $i ++) {
						$allowedpages [] = $i;
					}
					// If no pages yet, we get the total pages from the activity if it is set
				} else if ($emarking->totalpages > 0) {
					for($i = 1; $i <= $emarking->totalpages; $i ++) {
						$allowedpages [] = $i;
					}
					// Finally we assume there are less than 50 pages
				} else {
					for($i = 1; $i <= 50; $i ++) {
						$allowedpages [] = $i;
					}
				}
	}

	// Sort the array
	asort ( $allowedpages );

	return $allowedpages;
}

/**
 *
 * @param unknown $submission
 */
function emarking_publish_grade($draft) {
	global $CFG, $DB, $USER;

	require_once ($CFG->libdir . '/gradelib.php');

	if(!$submission = $DB->get_record('emarking_submission', array('id'=>$draft->submissionid))) {
		return;
	}
	
	if(!$emarking = $DB->get_record('emarking', array('id'=>$submission->emarking))) {
		return;
	}

	if($emarking->type != EMARKING_TYPE_NORMAL)
		return;
	
	if ($submission->status <= EMARKING_STATUS_ABSENT)
		return;

	// Copy final grade to gradebook
	$grade_item = grade_item::fetch ( array (
			'itemmodule' => 'emarking',
			'iteminstance' => $submission->emarking
	) );

	$feedback = $draft->generalfeedback ? $draft->generalfeedback : '';

	$grade_item->update_final_grade ( $submission->student, $draft->grade, 'editgrade', $feedback, FORMAT_HTML, $USER->id );

	if ($draft->status <= EMARKING_STATUS_RESPONDED) {
		$draft->status = EMARKING_STATUS_RESPONDED;
	}

	$draft->timemodified = time();
	$DB->update_record ( 'emarking_draft', $draft );

	$submission->status = $draft->status;
	$submission->timemodified = $draft->timemodified;
	$submission->generalfeedback = $draft->generalfeedback;
	$submission->grade = $draft->grade;
	$submission->teacher = $draft->teacher;
	$DB->update_record ( 'emarking_submission', $submission );
}

/**
 * Exports all grades and scores in an exam in Excel format
 *
 * @param unknown $emarking
 */
function emarking_download_excel($emarking) {
    global $DB;

    $csvsql = "
		SELECT cc.fullname AS course,
			e.name AS exam,
			u.id,
			u.idnumber,
			u.lastname,
			u.firstname,
			cr.description,
			IFNULL(l.score, 0) AS score,
			IFNULL(c.bonus, 0) AS bonus,
			IFNULL(l.score,0) + IFNULL(c.bonus,0) AS totalscore,
			d.grade
		FROM {emarking} AS e
		INNER JOIN {emarking_submission} AS s ON (e.id = :emarkingid AND e.id = s.emarking)
		INNER JOIN {emarking_draft} AS d ON (d.submissionid = s.id AND d.qualitycontrol=0)
        INNER JOIN {course} AS cc ON (cc.id = e.course)
		INNER JOIN {user} AS u ON (s.student = u.id)
		INNER JOIN {emarking_page} AS p ON (p.submission = s.id)
		INNER JOIN {emarking_comment} AS c ON (c.page = p.id AND d.id = c.draft)
		INNER JOIN {gradingform_rubric_levels} AS l ON (c.levelid = l.id)
		INNER JOIN {gradingform_rubric_criteria} AS cr ON (cr.id = l.criterionid)
		ORDER BY cc.fullname ASC, e.name ASC, u.lastname ASC, u.firstname ASC, cr.sortorder";

    // Get data and generate a list of questions
    $rows = $DB->get_recordset_sql ( $csvsql, array (
        'emarkingid' => $emarking->id
    ) );

    $questions = array ();
    foreach ( $rows as $row ) {
        if (array_search ( $row->description, $questions ) === FALSE)
            $questions [] = $row->description;
    }

    $current = 0;
    $laststudent = 0;
    $headers = array (
        '00course' => get_string ( 'course' ),
        '01exam' => get_string ( 'exam', 'mod_emarking' ),
        '02idnumber' => get_string ( 'idnumber' ),
        '03lastname' => get_string ( 'lastname' ),
        '04firstname' => get_string ( 'firstname' )
    );
    $tabledata = array ();
    $data = null;

    $rows = $DB->get_recordset_sql ( $csvsql, array (
        'emarkingid' => $emarking->id
    ) );

    $studentname = '';
    $lastrow = null;
    foreach ( $rows as $row ) {
        $index = 10 + array_search ( $row->description, $questions );
        $keyquestion = $index . "" . $row->description;
        if (! isset ( $headers [$keyquestion] )) {
            $headers [$keyquestion] = $row->description;
        }
        if ($laststudent != $row->id) {
            if ($laststudent > 0) {
                $tabledata [$studentname] = $data;
                $current ++;
            }
            $data = array (
                '00course' => $row->course,
                '01exam' => $row->exam,
                '02idnumber' => $row->idnumber,
                '03lastname' => $row->lastname,
                '04firstname' => $row->firstname,
                $keyquestion => $row->totalscore,
                '99grade' => $row->grade
            );
            $laststudent = intval ( $row->id );
            $studentname = $row->lastname . ',' . $row->firstname;
        } else {
            $data [$keyquestion] = $row->totalscore;
        }
        $lastrow = $row;
    }
    $studentname = $lastrow->lastname . ',' . $lastrow->firstname;
    $tabledata [$studentname] = $data;
    $headers ['99grade'] = get_string ( 'grade' );
    ksort ( $tabledata );

    $current = 0;
    $newtabledata = array ();
    foreach ( $tabledata as $data ) {
        foreach ( $questions as $q ) {
            $index = 10 + array_search ( $q, $questions );
            if (! isset ( $data [$index . "" . $q] )) {
                $data [$index . "" . $q] = '0.000';
            }
        }
        ksort ( $data );
        $current ++;
        $newtabledata [] = $data;
    }

    $tabledata = $newtabledata;

    $downloadfilename = clean_filename ( "$emarking->name.xls" );
    // Creating a workbook
    $workbook = new MoodleExcelWorkbook ( "-" );
    // Sending HTTP headers
    $workbook->send ( $downloadfilename );
    // Adding the worksheet
    $myxls = $workbook->add_worksheet ( get_string ( 'emarking', 'mod_emarking' ) );

    // Writing the headers in the first row
    $row = 0;
    $col = 0;
    foreach ( array_values ( $headers ) as $d ) {
        $myxls->write_string ( $row, $col, $d );
        $col ++;
    }
    // Writing the data
    $row = 1;
    foreach ( $tabledata as $data ) {
        $col = 0;
        foreach ( array_values ( $data ) as $d ) {
            if ($row > 0 && $col >= 5) {
                $myxls->write_number ( $row, $col, $d );
            } else {
                $myxls->write_string ( $row, $col, $d );
            }
            $col ++;
        }
        $row ++;
    }
    $workbook->close ();
}


function emarking_publish_all_grades($emarking) {
	global $DB, $USER, $CFG;
	
	if($emarking->type != EMARKING_TYPE_NORMAL)
		return;

	$studentdrafts = $DB->get_records_sql(
			"SELECT d.* 
			FROM {emarking_draft} as d
			INNER JOIN {emarking_submission} as s ON (d.submissionid = s.id AND s.emarking = :emarking AND d.qualitycontrol = 0)", array (
			'emarking' => $emarking->id
	) );

	foreach ( $studentdrafts as $draft ) {
		if ($draft->status >= EMARKING_STATUS_RESPONDED)
			emarking_publish_grade ( $draft );
	}

	return true;
}

function emarking_calculate_grades_users($emarking, $userid = 0) {
	global $DB, $USER, $CFG;

	require_once ($CFG->dirroot . '/grade/grading/lib.php');

	if (! $cm = get_coursemodule_from_instance ( 'emarking', $emarking->id )) {
		return;
	}
	
	if($emarking->type != EMARKING_TYPE_NORMAL)
		return;

	$context = context_module::instance ( $cm->id );

	// Get the grading manager, then method and finally controller
	$gradingmanager = get_grading_manager ( $context, 'mod_emarking', 'attempt' );
	$gradingmethod = $gradingmanager->get_active_method ();
	$controller = $gradingmanager->get_controller ( $gradingmethod );
	$range = $controller->get_grade_range ();
	$rubricscores = $controller->get_min_max_score ();
	$totalrubricscore = $rubricscores ['maxscore'];

	$filter = 'WHERE 1=1';
	if ($userid > 0)
		$filter = 'WHERE es.student = ' . $userid;
	$studentscores = $DB->get_records_sql ( "
			SELECT es.id,
			es.student,
			sum(ifnull(rl.score,0)) as score,
			sum(ifnull(ec.bonus,0)) as bonus,
			sum(ifnull(rl.score,0)) + sum(ifnull(ec.bonus,0)) as totalscore
			FROM {emarking_submission} AS es
			INNER JOIN {emarking_page} AS ep ON (es.emarking = :emarking AND ep.submission = es.id)
			INNER JOIN {emarking_draft} AS d ON (d.submissionid = es.id AND d.qualitycontrol = 0)
			LEFT JOIN {emarking_comment} AS ec ON (ec.page = ep.id AND ec.levelid > 0 AND ec.draft = d.id)
			LEFT JOIN {gradingform_rubric_levels} AS rl ON (ec.levelid = rl.id)
			$filter
			AND d.status >= 10
			GROUP BY es.emarking, es.id", array (
					'emarking' => $emarking->id
			) );

	foreach ( $studentscores as $studentscore ) {
		$totalscore = min ( floatval ( $studentscore->totalscore ), $totalrubricscore );

		$finalgrade = emarking_calculate_grade ( $emarking, $totalscore, $totalrubricscore );

		$submission = $DB->get_record ( 'emarking_submission', array (
				'id' => $studentscore->id
		) );
		$submission->grade = $finalgrade;
		$DB->update_record ( 'emarking_submission', $submission );
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
	if (isset ( $emarking->adjustslope ) && $emarking->adjustslope) {
		$finalgrade = min ( $emarking->grade, ((($emarking->adjustslopegrade - $emarking->grademin) / $emarking->adjustslopescore) * $totalscore) + $emarking->grademin );
	} else {
		$finalgrade = ((($emarking->grade - $emarking->grademin) / $totalrubricscore) * $totalscore) + $emarking->grademin;
	}

	return $finalgrade;
}


/**
 * Creates the PDF version (downloadable) of the whole feedback produced by the teacher/tutor
 *
 * @param int $submissionid
 * @return boolean
 *
 */
function emarking_multi_create_response_pdf($submission, $student, $context, $cmid) {
	global $CFG, $DB;

	require_once $CFG->libdir . '/pdflib.php';
	require_once($CFG->dirroot . '/mod/emarking/print/locallib.php');
	
	$fs = get_file_storage ();

	if (! $pages = $DB->get_records ( 'emarking_page', array (
			'submission' => $submission->id,
			'student' => $student->id
	), 'page ASC' )) {
		return false;
	}

	$emarking = $DB->get_record ( 'emarking', array (
			'id' => $submission->emarking
	) );

	$numpages = count ( $pages );

	$sqlcomments = "SELECT ec.id,
			ec.posx,
			ec.posy,
			ec.rawtext,
			ec.pageno,
			grm.maxscore,
			ec.levelid,
			ec.width,
			ec.colour,
			ec.textformat,
			grl.score AS score,
			grl.definition AS leveldesc,
			grc.id AS criterionid,
			grc.description AS criteriondesc,
			u.id AS markerid, CONCAT(u.firstname,' ',u.lastname) AS markername
			FROM {emarking_comment} AS ec
			INNER JOIN {emarking_page} AS ep ON (ep.submission = :submission AND ec.page = ep.id)
			LEFT JOIN {user} AS u ON (ec.markerid = u.id)
			LEFT JOIN {gradingform_rubric_levels} AS grl ON (ec.levelid = grl.id)
			LEFT JOIN {gradingform_rubric_criteria} AS grc ON (grl.criterionid = grc.id)
			LEFT JOIN (
			SELECT grl.criterionid, max(score) AS maxscore
			FROM {gradingform_rubric_levels} AS grl
			GROUP BY grl.criterionid
			) AS grm ON (grc.id = grm.criterionid)
			WHERE ec.pageno > 0
			ORDER BY ec.pageno";
	$params = array (
			'submission' => $submission->id
	);
	$comments = $DB->get_records_sql ( $sqlcomments, $params );

	$commentsperpage = array ();

	foreach ( $comments as $comment ) {
		if (! isset ( $commentsperpage [$comment->pageno] )) {
			$commentsperpage [$comment->pageno] = array ();
		}

		$commentsperpage [$comment->pageno] [] = $comment;
	}

	// Parameters for PDF generation
	$iconsize = 5;

	$tempdir = emarking_get_temp_dir_path ( $emarking->id );
	if (! file_exists ( $tempdir )) {
		mkdir ( $tempdir );
	}

	// create new PDF document
	$pdf = new TCPDF ( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

	// set document information
	$pdf->SetCreator ( PDF_CREATOR );
	$pdf->SetAuthor ( $student->firstname . ' ' . $student->lastname );
	$pdf->SetTitle ( $emarking->name );
	$pdf->SetSubject ( 'Exam feedback' );
	$pdf->SetKeywords ( 'feedback, emarking' );
	$pdf->SetPrintHeader ( false );
	$pdf->SetPrintFooter ( false );

	// set default header data
	$pdf->SetHeaderData ( PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 036', PDF_HEADER_STRING );

	// set header and footer fonts
	$pdf->setHeaderFont ( Array (
			PDF_FONT_NAME_MAIN,
			'',
			PDF_FONT_SIZE_MAIN
	) );
	$pdf->setFooterFont ( Array (
			PDF_FONT_NAME_DATA,
			'',
			PDF_FONT_SIZE_DATA
	) );

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont ( PDF_FONT_MONOSPACED );

	// set margins
	$pdf->SetMargins ( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );
	$pdf->SetHeaderMargin ( PDF_MARGIN_HEADER );
	$pdf->SetFooterMargin ( PDF_MARGIN_FOOTER );

	// set auto page breaks
	$pdf->SetAutoPageBreak ( TRUE, PDF_MARGIN_BOTTOM );

	// set image scale factor
	$pdf->setImageScale ( PDF_IMAGE_SCALE_RATIO );

	// set some language-dependent strings (optional)
	if (@file_exists ( dirname ( __FILE__ ) . '/lang/eng.php' )) {
		require_once (dirname ( __FILE__ ) . '/lang/eng.php');
		$pdf->setLanguageArray ( $l );
	}

	// ---------------------------------------------------------

	// set font
	$pdf->SetFont ( 'times', '', 16 );

	foreach ( $pages as $page ) {
		// add a page
		$pdf->AddPage ();

		// get the current page break margin
		$bMargin = $pdf->getBreakMargin ();
		// get current auto-page-break mode
		$auto_page_break = $pdf->getAutoPageBreak ();
		// disable auto-page-break
		$pdf->SetAutoPageBreak ( false, 0 );
		// set bacground image
		$pngfile = $fs->get_file_by_id ( $page->file );
		$img_file = emarking_get_path_from_hash ( $tempdir, $pngfile->get_pathnamehash () );
		$pdf->Image ( $img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0 );
		// restore auto-page-break status
		// $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
		// set the starting point for the page content
		$pdf->setPageMark ();

		$widthratio = $pdf->getPageWidth () / 800;

		if (isset ( $commentsperpage [$page->page] )) {
			foreach ( $commentsperpage [$page->page] as $comment ) {

				$content = $comment->rawtext;

				if ($comment->textformat == 1) {
					// text annotation
					$pdf->Annotation ( $comment->posx * $widthratio, $comment->posy * $widthratio, 6, 6, $content, array (
							'Subtype' => 'Text',
							'StateModel' => 'Review',
							'State' => 'None',
							'Name' => 'Comment',
							'NM' => 'Comment' . $comment->id,
							'T' => $comment->markername,
							'Subj' => 'example',
							'C' => array (
									0,
									0,
									255
							)
					) );
				} elseif ($comment->textformat == 2) {
					$content = $comment->criteriondesc . ': ' . round ( $comment->score, 1 ) . '/' . round ( $comment->maxscore, 1 ) . "\n" . $comment->leveldesc . "\n" . get_string ( 'comment', 'mod_emarking' ) . ': ' . $content;
					// text annotation
					$pdf->Annotation ( $comment->posx * $widthratio, $comment->posy * $widthratio, 6, 6, $content, array (
							'Subtype' => 'Text',
							'StateModel' => 'Review',
							'State' => 'None',
							'Name' => 'Comment',
							'NM' => 'Mark' . $comment->id,
							'T' => $comment->markername,
							'Subj' => 'grade',
							'C' => array (
									255,
									255,
									0
							)
					) );
				} elseif ($comment->textformat == 3) {
					$pdf->Image ( $CFG->dirroot . "/mod/emarking/img/check.gif", $comment->posx * $widthratio, $comment->posy * $widthratio, $iconsize, $iconsize, '', '', '', false, 300, '', false, false, 0 );
				} elseif ($comment->textformat == 4) {
					$pdf->Image ( $CFG->dirroot . "/mod/emarking/img/crossed.gif", $comment->posx * $widthratio, $comment->posy * $widthratio, $iconsize, $iconsize, '', '', '', false, 300, '', false, false, 0 );
				}
			}
		}
	}
	// ---------------------------------------------------------

	// COGIDO PARA IMPRIMIR RÚBRICA
	if ($emarking->downloadrubricpdf) {

		$cm = new StdClass ();

		$rubricdesc = $DB->get_recordset_sql ( "SELECT
		d.name AS rubricname,
		a.id AS criterionid,
		a.description ,
		b.definition,
		b.id AS levelid,
		b.score,
		IFNULL(E.id,0) AS commentid,
		IFNULL(E.pageno,0) AS commentpage,
		E.rawtext AS commenttext,
		E.markerid AS markerid,
		IFNULL(E.textformat,2) AS commentformat,
		IFNULL(E.bonus,0) AS bonus,
		IFNULL(er.id,0) AS regradeid,
		IFNULL(er.motive,0) AS motive,
		er.comment AS regradecomment,
		IFNULL(er.markercomment, '') AS regrademarkercomment,
		IFNULL(er.accepted,0) AS regradeaccepted
		FROM {course_modules} AS c
		INNER JOIN {context} AS mc ON (c.id = :coursemodule AND c.id = mc.instanceid)
		INNER JOIN {grading_areas} AS ar ON (mc.id = ar.contextid)
		INNER JOIN {grading_definitions} AS d ON (ar.id = d.areaid)
		INNER JOIN {gradingform_rubric_criteria} AS a ON (d.id = a.definitionid)
		INNER JOIN {gradingform_rubric_levels} AS b ON (a.id = b.criterionid)
		LEFT JOIN (
		SELECT ec.*, es.id AS submissionid
		FROM {emarking_comment} AS ec
		INNER JOIN {emarking_page} AS ep ON (ec.page = ep.id)
		INNER JOIN {emarking_draft} AS es ON (es.id = :submission AND ep.submission = es.id)
		) AS E ON (E.levelid = b.id)
		LEFT JOIN {emarking_regrade} AS er ON (er.criterion = a.id AND er.submission = E.submissionid)
		ORDER BY a.sortorder ASC, b.score ASC", array (
				'coursemodule' => $cmid,
				'submission' => $submission->id
		) );

		$table = new html_table ();
		$data = array ();
		foreach ( $rubricdesc as $rd ) {
			if (! isset ( $data [$rd->criterionid] )) {
				$data [$rd->criterionid] = array (
						$rd->description,
						$rd->definition . " (" . round ( $rd->score, 2 ) . " ptos. )"
				);
			} else {
				array_push ( $data [$rd->criterionid], $rd->definition . " (" . round ( $rd->score, 2 ) . " ptos. )" );
			}
		}
		$table->data = $data;

		// add extra page with rubrics
		$pdf->AddPage ();
		$pdf->Write ( 0, 'Rúbrica', '', 0, 'L', true, 0, false, false, 0 );
		$pdf->SetFont ( 'helvetica', '', 8 );

		$tbl = html_writer::table ( $table );

		$pdf->writeHTML ( $tbl, true, false, false, false, '' );
	}
	// ---------------------------------------------------------

	$pdffilename = 'response_' . $emarking->id . '_' . $student->id . '.pdf';
	$pathname = $tempdir . '/' . $pdffilename;

	if (@file_exists ( $pathname )) {
		unlink ( $pathname );
	}

	// Close and output PDF document
	$pdf->Output ( $pathname, 'F' );

	// Copiar archivo desde temp a Área
	$file_record = array (
			'contextid' => $context->id,
			'component' => 'mod_emarking',
			'filearea' => 'response',
			'itemid' => $student->id,
			'filepath' => '/',
			'filename' => $pdffilename,
			'timecreated' => time (),
			'timemodified' => time (),
			'userid' => $student->id,
			'author' => $student->firstname . ' ' . $student->lastname,
			'license' => 'allrightsreserved'
	);

	// Si el archivo ya existía entonces lo borramos
	if ($fs->file_exists ( $context->id, 'mod_emarking', 'response', $student->id, '/', $pdffilename )) {
		$previousfile = $fs->get_file ( $context->id, 'mod_emarking', 'response', $student->id, '/', $pdffilename );
		$previousfile->delete ();
	}

	$fileinfo = $fs->create_file_from_pathname ( $file_record, $pathname );

	return true;
}

/**
 * 
 * @param number $userid
 * @param number $levelid
 * @param string $levelfeedback
 * @param object $submission
 * @param object $draft
 * @param string $emarking
 * @param string $context
 * @param string $generalfeedback
 * @param string $delete
 * @param number $cmid
 * @return multitype:boolean |NULL|multitype:number NULL
 */
function emarking_set_finalgrade(
		$userid = 0, 
		$levelid = 0, 
		$levelfeedback = '', 
		$submission = null, 
		$draft = null,
		$emarking = null, 
		$context = null, 
		$generalfeedback = null, 
		$delete = false, 
		$cmid = 0) {
	global $USER, $DB, $CFG;

	require_once ($CFG->dirroot . '/grade/grading/lib.php');

	// Validate parameters
	if ($userid == 0 || ($levelid == 0 && $cmid == 0) || $draft == null || $submission == null || $context == null) {
		return array (
				false,
				false,
				false
		);
	}

	if ($levelid > 0) {
		// Firstly get the rubric definition id and criterion id from the level
		$rubricinfo = $DB->get_record_sql ( "
				SELECT c.definitionid, l.definition, l.criterionid, l.score, c.description
				FROM {gradingform_rubric_levels} as l
				INNER JOIN {gradingform_rubric_criteria} as c on (l.criterionid = c.id)
				WHERE l.id = ?", array (
						$levelid
				) );
	} elseif ($cmid > 0) {
		// Firstly get the rubric definition id and criterion id from the level
		$rubricinfo = $DB->get_record_sql ( "
				SELECT
				d.id as definitionid
				FROM {course_modules} AS c
				inner join {context} AS mc on (c.id = ? AND c.id = mc.instanceid)
				inner join {grading_areas} AS ar on (mc.id = ar.contextid)
				inner join {grading_definitions} AS d on (ar.id = d.areaid)
				", array (
						$cmid
				) );
	} else {
		return null;
	}

	// Get the grading manager, then method and finally controller
	$gradingmanager = get_grading_manager ( $context, 'mod_emarking', 'attempt' );
	$gradingmethod = $gradingmanager->get_active_method ();
	$controller = $gradingmanager->get_controller ( $gradingmethod );
	$controller->set_grade_range ( array (
			"$emarking->grademin" => $emarking->grademin,
			"$emarking->grade" => $emarking->grade
	), true );
	$definition = $controller->get_definition ();

	// Get the grading instance we should already have
	$gradinginstancerecord = $DB->get_record ( 'grading_instances', array (
			'itemid' => $draft->id,
			'definitionid' => $definition->id
	) );

	// Use the last marking rater id to get the instance
	$raterid = $USER->id;
	$itemid = null;
	if ($gradinginstancerecord) {
		if ($gradinginstancerecord->raterid > 0) {
			$raterid = $gradinginstancerecord->raterid;
		}
		$itemid = $gradinginstancerecord->id;
	}

	// Get or create grading instance (in case submission has not been graded)
	$gradinginstance = $controller->get_or_create_instance ( $itemid, $raterid, $draft->id );

	$rubricscores = $controller->get_min_max_score ();

	// Get the fillings and replace the new one accordingly
	$fillings = $gradinginstance->get_rubric_filling ();

	if ($levelid > 0) {
		if ($delete) {
			if (! $minlevel = $DB->get_record_sql ( '
					SELECT id, score
					FROM {gradingform_rubric_levels}
					WHERE criterionid = ?
					ORDER BY score ASC LIMIT 1', array (
							$rubricinfo->criterionid
					) )) {
						return array (
								false,
								false,
								false
						);
					}
					$newfilling = array (
							"remark" => '',
							"levelid" => $minlevel->id
					);
		} else {
			$newfilling = array (
					"remark" => $levelfeedback,
					"levelid" => $levelid
			);
		}
		if (isset ( $fillings ['criteria'] [$rubricinfo->criterionid] ['levelid'] ) && isset ( $fillings ['criteria'] [$rubricinfo->criterionid] ['remark'] )) {
			$previouslvlid = $fillings ['criteria'] [$rubricinfo->criterionid] ['levelid'];
			$previouscomment = $fillings ['criteria'] [$rubricinfo->criterionid] ['remark'];
		} else {
			$previouslvlid = 0;
			$previouscomment = null;
		}
		$fillings ['criteria'] [$rubricinfo->criterionid] = $newfilling;
	} else {
		$previouslvlid = 0;
		$previouscomment = null;
	}

	$fillings ['raterid'] = $raterid;
	$gradinginstance->update ( $fillings );
	$rawgrade = $gradinginstance->get_grade ();

	$previousfeedback = '';
	$previousfeedback = $draft->generalfeedback == null ? '' : $draft->generalfeedback;

	if ($generalfeedback == null) {
		$generalfeedback = $previousfeedback;
	}

	$totalscore = emarking_get_totalscore ( $draft, $controller, $fillings );
	$finalgrade = emarking_calculate_grade ( $emarking, $totalscore, $rubricscores ['maxscore'] );

	$pendingregrades = $DB->count_records('emarking_regrade', array('draft'=>$draft->id, 'accepted'=>0));
	
	// Calculate grade for draft
	$draft->grade = $finalgrade + $gradebonus;
	$draft->generalfeedback = $generalfeedback;
	$draft->status = $pendingregrades == 0 ? EMARKING_STATUS_GRADING : EMARKING_STATUS_REGRADING;
	$draft->timemodified = time ();

	$DB->update_record ( 'emarking_draft', $draft );
	
	// Aggregate grade for submission
	$drafts = $DB->get_records ( "emarking_draft", array (
			"emarkingid" => $submission->emarking,
			"submissionid" => $submission->id
	) );
	
	$submission->generalfeedback = '';
	$submission->grade = 0;
	foreach($drafts as $d) {
		$submission->generalfeedback .= $d->generalfeedback;
		$submission->grade += $d->grade;
	}
	$submission->grade = $submission->grade / count($drafts);
	$submission->timemodified = time ();

	$DB->update_record ( 'emarking_submission', $submission );
	
	return array (
			$finalgrade + $gradebonus,
			$previouslvlid,
			$previouscomment
	);
}

