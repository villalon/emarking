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
 * @param unknown $submission
 */
function emarking_publish_grade($submission) {
	global $CFG, $DB, $USER;

	require_once ($CFG->libdir . '/gradelib.php');

	if ($submission->status <= EMARKING_STATUS_ABSENT)
		return;

	// Copy final grade to gradebook
	$grade_item = grade_item::fetch ( array (
			'itemmodule' => 'emarking',
			'iteminstance' => $submission->emarkingid
	) );

	$feedback = $submission->generalfeedback ? $submission->generalfeedback : '';

	$grade_item->update_final_grade ( $submission->student, $submission->grade, 'editgrade', $feedback, FORMAT_HTML, $USER->id );

	if ($submission->status <= EMARKING_STATUS_RESPONDED) {
		$submission->status = EMARKING_STATUS_RESPONDED;
	}

	$submission->timemodified = time ();
	$DB->update_record ( 'emarking_draft', $submission );

	$realsubmission = $DB->get_record ( "emarking_submission", array (
			"id" => $submission->submissionid
	) );
	$realsubmission->status = $submission->status;
	$realsubmission->timemodified = $submission->timemodified;
	$realsubmission->generalfeedback = $submission->generalfeedback;
	$realsubmission->grade = $submission->grade;
	$realsubmission->teacher = $submission->teacher;
	$DB->update_record ( 'emarking_submission', $realsubmission );
}

function emarking_publish_all_grades($emarking) {
	global $DB, $USER, $CFG;

	$studentsubmissions = $DB->get_records ( "emarking_submission", array (
			'emarking' => $emarking->id
	) );

	foreach ( $studentsubmissions as $submission ) {
		if ($submission->status >= EMARKING_STATUS_RESPONDED)
			emarking_publish_grade ( $submission );
	}

	return true;
}

function emarking_calculate_grades_users($emarking, $userid = 0) {
	global $DB, $USER, $CFG;

	require_once ($CFG->dirroot . '/grade/grading/lib.php');

	if (! $cm = get_coursemodule_from_instance ( 'emarking', $emarking->id )) {
		return;
	}

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
			LEFT JOIN {emarking_comment} AS ec ON (ec.page = ep.id AND ec.levelid > 0)
			LEFT JOIN {gradingform_rubric_levels} AS rl ON (ec.levelid = rl.id)
			$filter
			AND es.status >= 10
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

