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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Devuelve el path por defecto de archivos temporales de emarking.
 * Normalmente debiera ser moodledata\temp\emarking
 *
 * @param unknown $postfix
 *        	Postfijo (típicamente el id de assignment)
 * @return string El path al directorio temporal
 */
function emarking_get_temp_dir_path($postfix) {
	global $CFG;
	return $CFG->dataroot . "/temp/emarking/" . $postfix;
}

/**
 * Get students count from a course, for printing.
 *
 * @param unknown_type $courseid
 */
function emarking_get_students_count_for_printing($courseid) {
	global $DB;

	$query = 'SELECT count(u.id) as total
			FROM {user_enrolments} ue
			JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
			JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
			JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
			JOIN {user} u ON (ue.userid = u.id)
			GROUP BY e.courseid';

	// Se toman los resultados del query dentro de una variable.
	$rs = $DB->get_record_sql ($query, array($courseid));

	return $rs->total;
}

/**
 *
 *
 *
 * creates email to course manager, teacher and non-editingteacher, when a printing order has been created.
 *
 * @param unknown_type $exam
 * @param unknown_type $course
 */
function emarking_send_newprintorder_notification($exam, $course) {
	global $USER;

	$postsubject = $course->fullname . ' : ' . $exam->name . '. ' . get_string ( 'newprintorder', 'mod_emarking' ) . ' [' . $exam->id . ']';

	$examhasqr = $exam->headerqr ? get_string ( 'yes' ) : get_string ( 'no' );

	$pagestoprint = emarking_exam_total_pages_to_print ( $exam );

	// Create the email to be sent
	$posthtml = '';
	$posthtml .= '<table><tr><th colspan="2">' . get_string ( 'newprintorder', 'mod_emarking' ) . '</th></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'examid', 'mod_emarking' ) . '</td><td>' . $exam->id . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'fullnamecourse' ) . '</td><td>' . $course->fullname . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'shortnamecourse' ) . '</td><td>' . $course->shortname . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'requestedby', 'mod_emarking' ) . '</td><td>' . $USER->lastname . ' ' . $USER->firstname . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'examdate', 'mod_emarking' ) . '</td><td>' . date ( "d M Y - H:i", $exam->examdate ) . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'extrasheets', 'mod_emarking' ) . '</td><td>' . $exam->extrasheets . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'extraexams', 'mod_emarking' ) . '</td><td>' . $exam->extraexams . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'headerqr', 'mod_emarking' ) . '</td><td>' . $examhasqr . '</td></tr>';
	$posthtml .= '<tr><td>' . get_string ( 'totalpagesprint', 'mod_emarking' ) . '</td><td>' . $pagestoprint . '</td></tr>';
	$posthtml .= '</table>';
	$posthtml .= '';

	// Create the email to be sent
	$posttext = get_string ( 'newprintorder', 'mod_emarking' ) . '\n';
	$posttext .= get_string ( 'examid', 'mod_emarking' ) . ' : ' . $exam->id . '\n';
	$posttext .= get_string ( 'fullnamecourse' ) . ': ' . $course->fullname . '\n';
	$posttext .= get_string ( 'shortnamecourse' ) . ': ' . $course->shortname . '\n';
	$posttext .= get_string ( 'requestedby', 'mod_emarking' ) . ': ' . $USER->lastname . ' ' . $USER->firstname . '\n';
	$posttext .= get_string ( 'examdate', 'mod_emarking' ) . ': ' . date ( "d M Y - H:i", $exam->examdate ) . '\n';
	$posttext .= get_string ( 'extrasheets', 'mod_emarking' ) . ': ' . $exam->extrasheets . '\n';
	$posttext .= get_string ( 'extraexams', 'mod_emarking' ) . ': ' . $exam->extraexams . '\n';
	$posttext .= get_string ( 'headerqr', 'mod_emarking' ) . ': ' . $examhasqr . '\n';
	$posttext .= get_string ( 'totalpagesprint', 'mod_emarking' ) . ': ' . $pagestoprint . '\n';

	emarking_send_notification ( $exam, $course, $postsubject, $posttext, $posthtml );
}

/**
 * Extracts all pages in a big PDF file as separate PDF files, deleting the original PDF if successfull.
 *
 * @param unknown $newfile
 *        	PDF file to extract
 * @param unknown $tempdir
 *        	Temporary folder
 * @param string $doubleside
 *        	Extract every two pages (for both sides scanning)
 * @return number unknown number of pages extracted
 */
function emarking_pdf_count_pages($newfile, $tempdir, $doubleside = true) {
	global $CFG;

	require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php");
	require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi.php");

	$doc = new FPDI ();
	$files = $doc->setSourceFile ( $newfile );
	$doc->Close ();

	return $files;
}

/**
 * Creates a PDF form for the copy center to print
 * 
 * @param unknown $context
 * @param unknown $exam
 * @param unknown $userrequests
 * @param unknown $useraccepts
 * @param unknown $category
 * @param unknown $totalpages
 * @param unknown $course
 */
function emarking_create_printform($context, $exam, $userrequests, $useraccepts, $category, $totalpages, $course) {
	global $CFG;

	require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php");
	require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi.php");

	$cantsheets = $totalpages / ($exam->totalstudents + $exam->extraexams);
	$totalextraexams = $exam->totalstudents + $exam->extraexams;
	$canttotalpages = $cantsheets * $totalextraexams;

	$pdf = new FPDI ();
	$cp = $pdf->setSourceFile ( $CFG->dirroot . "/mod/emarking/img/printformtemplate.pdf" );
	for($i = 1; $i <= $cp; $i ++) {
		$pdf->AddPage (); // Agrega una nueva página
		if ($i <= $cp) {
			$tplIdx = $pdf->importPage ( $i ); // Se importan las páginas del documento pdf.
			$pdf->useTemplate ( $tplIdx, 0, 0, 0, 0, $adjustPageSize = true ); // se inserta como template el archivo pdf subido

			// Copia/Impresión/Plotteo
			$pdf->SetXY ( 32, 48.5 );
			$pdf->Write ( 1, "x" );
			// Fecha día
			$pdf->SetXY ( 153, 56 );
			$pdf->Write ( 1, core_text::strtoupper ( date ( 'd' ) ) );
			// Fecha mes
			$pdf->SetXY ( 163, 56 );
			$pdf->Write ( 1, core_text::strtoupper ( date ( 'm' ) ) );
			// Fecha año
			$pdf->SetXY ( 173, 56 );
			$pdf->Write ( 1, core_text::strtoupper ( date ( 'Y' ) ) );
			// Solicitante
			$pdf->SetXY ( 95, 69 );
			$pdf->Write ( 1, core_text::strtoupper ( $useraccepts->firstname . " " . $useraccepts->lastname ) );
			// Centro de Costo
			$pdf->SetXY ( 95, 75.5 );
			$pdf->Write ( 1, core_text::strtoupper ( $category->idnumber ) );
			// Campus UAI
			$pdf->SetXY ( 95, 80.8 );
			$pdf->Write ( 1, core_text::strtoupper ( "" ) );
			// Número originales
			$pdf->SetXY ( 35, 106.5 );
			$pdf->Write ( 1, core_text::strtoupper ( $cantsheets ) );
			// Número copias
			$pdf->SetXY ( 60, 106.5 );
			$pdf->Write ( 1, core_text::strtoupper ( "--" ) );
			// Número impresiones
			$pdf->SetXY ( 84, 106.5 );
			$pdf->Write ( 1, core_text::strtoupper ( $totalextraexams ) );
			// BN
			$pdf->SetXY ( 106, 106.5 );
			$pdf->Write ( 1, "x" );
			// Páginas totales
			$pdf->SetXY ( 135, 106.5 );
			$pdf->Write ( 1, core_text::strtoupper ( $canttotalpages ) );
			// Número impresiones Total
			$pdf->SetXY ( 84, 133.8 );
			$pdf->Write ( 1, core_text::strtoupper ( "" ) );
			// Páginas totales Total
			$pdf->SetXY ( 135, 133.8 );
			$pdf->Write ( 1, core_text::strtoupper ( "" ) );
			// Páginas totales Total
			$pdf->SetXY ( 43, 146 );
			$pdf->Write ( 1, core_text::strtoupper ( $course->fullname . " , " . $exam->name ) );
			// Recepcionado por Nombre
			$pdf->SetXY ( 30, 164.5 );
			$pdf->Write ( 1, core_text::strtoupper ( "" ) );
			// Recepcionado por RUT
			$pdf->SetXY ( 127, 164.5 );
			$pdf->Write ( 1, core_text::strtoupper ( "" ) );
		}
	}
	$pdf->Output ( "PrintForm" . $exam->id . ".pdf", "I" ); // se genera el nuevo pdf
}


/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2015 Jorge Villalon <villalon@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function emarking_upload_answers($emarking, $fileid, $course, $cm, progress_bar $progressbar = null) {
	global $CFG, $DB;

	$context = context_module::instance ( $cm->id );

	// Setup de directorios temporales
	$tempdir = emarking_get_temp_dir_path ( $emarking->id );

	if (! emarking_unzip ( $fileid, $tempdir . "/" )) {
		return array (
				false,
				get_string ( 'errorprocessingextraction', 'mod_emarking' ),
				0,
				0
		);
	}

	$numpages = emarking_count_files_in_dir ( $tempdir, ".png" );

	if ($numpages < 1) {
		die ( $tempdir );
		return array (
				false,
				get_string ( 'invalidpdfnopages', 'mod_emarking' ),
				0,
				0
		);
	}

	$totalDocumentsProcessed = 0;
	$totalDocumentsIgnored = 0;

	// Read full directory, then start processing
	$files = scandir ( $tempdir );

	$doubleside = false;

	$pdfFiles = array ();
	foreach ( $files as $fileInTemp ) {
		if (! is_dir ( $fileInTemp ) && strtolower ( substr ( $fileInTemp, - 4, 4 ) ) === ".png") {
			$pdfFiles [] = $fileInTemp;
			if (strtolower ( substr ( $fileInTemp, - 5, 5 ) ) === "b.png") {
				$doubleside = true;
			}
		}
	}

	$total = count ( $pdfFiles );

	// Process files
	for($current = 0; $current < $total; $current ++) {

		$file = $pdfFiles [$current];

		$filename = explode ( ".", $file );
		$parts = explode ( "-", $filename [0] );
		if (count ( $parts ) != 3) {
			if ($CFG->debug)
				echo "Ignoring $file as it has invalid name";
			$totalDocumentsIgnored ++;
			continue;
		}

		$studentid = $parts [0];
		$courseid = $parts [1];
		$pagenumber = $parts [2];

		if (! $student = $DB->get_record ( 'user', array (
				'id' => $studentid
		) )) {
			$totalDocumentsIgnored ++;
			continue;
		}

		if ($courseid != $course->id) {
			$totalDocumentsIgnored ++;
			continue;
		}

		if ($progressbar) {
			$progressbar->update ( $current, $total, $student->firstname . " " . $student->lastname );
		}

		// 1 pasa a 1 1 * 2 - 1 = 1
		// 1b pasa a 2 1 * 2
		// 2 pasa a 3 2 * 2 -1 = 3
		// 2b pasa a 4 2 * 2
		$anonymouspage = false;
		// First clean the page number if it's anonymous
		if (substr ( $pagenumber, - 2 ) === "_a") {
			$pagenumber = substr ( $pagenumber, 0, strlen ( $pagenumber ) - 2 );
			$anonymouspage = true;
		}

		if ($doubleside) {
			if (substr ( $pagenumber, - 1 ) === "b") { // Detecta b
				$pagenumber = intval ( $pagenumber ) * 2;
			} else {
				$pagenumber = intval ( $pagenumber ) * 2 - 1;
			}
		}

		if ($anonymouspage) {
			continue;
		}

		if (! is_numeric ( $pagenumber )) {
			if ($CFG->debug) {
				echo "Ignored file: $filename[0] page: $pagenumber student id: $studentid course id: $courseid";
			}
			$totalDocumentsIgnored ++;
			continue;
		}

		if (emarking_submit ( $emarking, $context, $tempdir, $file, $student, $pagenumber )) {
			$totalDocumentsProcessed ++;
		} else {
			return array (
					false,
					get_string ( 'invalidzipnoanonymous', 'mod_emarking' ),
					$totalDocumentsProcessed,
					$totalDocumentsIgnored
			);
		}
	}

	return array (
			true,
			get_string ( 'invalidpdfnopages', 'mod_emarking' ),
			$totalDocumentsProcessed,
			$totalDocumentsIgnored
	);
}

/**
 * Esta funcion copia el archivo solicitado mediante el Hash (lo busca en la base de datos) en la carpeta temporal especificada.
 *
 * @param String $tempdir
 *        	Carpeta a la cual queremos copiar el archivo
 * @param String $hash
 *        	hash del archivo en base de datos
 * @param String $prefix
 *        	???
 * @return mixed
 */
// exportado y cambiado
function emarking_get_path_from_hash($tempdir, $hash, $prefix = '', $create = true) {
	global $CFG;

	// Obtiene filesystem
	$fs = get_file_storage ();

	// Obtiene archivo gracias al hash
	if (! $file = $fs->get_file_by_hash ( $hash )) {
		return false;
	}

	// Se copia archivo desde Moodle a temporal
	$newfile = emarking_clean_filename ( $tempdir . '/' . $prefix . $file->get_filename () );

	$file->copy_content_to ( $newfile );

	return $newfile;
}

/**
 *
 *
 *
 * Send email with the downloading code.
 *
 * @param unknown_type $code
 * @param unknown_type $user
 * @param unknown_type $coursename
 * @param unknown_type $examname
 */
function emarking_send_email_code($code, $user, $coursename, $examname) {
	global $CFG;

	$posttext = 'Código de seguridad eMarking\n'; // TODO: Internacionalizar
	$posttext .= $coursename . ' ' . $examname . '\n';
	$posttext .= 'Su código: ' . $code . '';

	$thismessagehtml = '<html>';
	$thismessagehtml .= '<h3>Código de seguridad eMarking</h3>';
	$thismessagehtml .= $coursename . ' ' . $examname . '<br>';
	$thismessagehtml .= 'Su código:<br>' . $code . '<br>';
	$thismessagehtml .= '</html>';

	$subject = "Código de seguridad eMarking";

	$headers = "From: $CFG->supportname  \r\n" . "Reply-To: $CFG->noreplyaddress\r\n" . 'Content-Type: text/html; charset="utf-8"' . "\r\n" . 'X-Mailer: PHP/' . phpversion ();

	$eventdata = new stdClass ();
	$eventdata->component = 'mod_emarking';
	$eventdata->name = 'notification';
	$eventdata->userfrom = get_admin ();
	$eventdata->userto = $user;
	$eventdata->subject = $subject;
	$eventdata->fullmessage = $posttext;
	$eventdata->fullmessageformat = FORMAT_HTML;
	$eventdata->fullmessagehtml = $thismessagehtml;
	$eventdata->smallmessage = $subject;

	$eventdata->notification = 1;

	return message_send ( $eventdata );
}

/**
 * Gets course names for all courses that share the same exam file
 *
 * @param unknown $exam
 * @return multitype:boolean unknown
 */
function emarking_exam_get_parallels($exam) {
	global $DB;

	// Checking if exam is for multicourse
	$courses = array ();
	$canbedeleted = true;

	// Find all exams with the same PDF file
	$multi = $DB->get_records ( 'emarking_exams', array (
			'file' => $exam->file
	), 'course ASC' );
	foreach ( $multi as $mult ) {
		if ($mult->status >= EMARKING_EXAM_SENT_TO_PRINT) {
			$canbedeleted = false;
		}
		if ($mult->id != $exam->id) {
			$shortname = $DB->get_record ( 'course', array (
					'id' => $mult->course
			) );
			$courses [] = $shortname->shortname;
		}
	}
	$multicourse = implode ( ", ", $courses );

	return array (
			$canbedeleted,
			$multicourse
	);
}

/**
 * Creates the PDF version (downloadable) of the whole feedback produced by the teacher/tutor
 *
 * @param int $submissionid
 * @return boolean
 */
function emarking_create_response_pdf($submission, $student, $context, $cmid) {
	global $CFG, $DB;

	require_once $CFG->libdir . '/pdflib.php';

	$fs = get_file_storage ();

	if (! $pages = $DB->get_records ( 'emarking_page', array (
			'submission' => $submission->id,
			'student' => $student->id
	), 'page ASC' )) {
		return false;
	}

	$emarking = $DB->get_record ( 'emarking', array (
			'id' => $submission->emarkingid
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
 * Creates the PDF version (downloadable) of the whole feedback produced by the teacher/tutor
 *
 * @param int $submissionid
 * @return boolean
 *
 */
function emarking_multi_create_response_pdf($submission, $student, $context, $cmid) {
	global $CFG, $DB;

	require_once $CFG->libdir . '/pdflib.php';

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
 * Creates a personalized exam file.
 *
 * @param unknown $examid
 * @return NULL
 */
function emarking_download_exam($examid, $multiplepdfs = false, $groupid = null, $pbar = null, $sendprintorder = false, $printername = null) {
	global $DB, $CFG, $USER, $OUTPUT;

	// Se obtiene el examen
	if (! $downloadexam = $DB->get_record ( 'emarking_exams', array (
			'id' => $examid
	) )) {
		return null;
	}

	// Contexto del curso para verificar permisos
	$context = context_course::instance ( $downloadexam->course );

	if (! has_capability ( 'mod/emarking:downloadexam', $context )) {
		return null;
	}

	// Verify that remote printing is enable, otherwise disable a printing order
	if ($sendprintorder && (! $CFG->emarking_enableprinting || $printername == null)) {
		return null;
	}

	$course = $DB->get_record ( 'course', array (
			'id' => $downloadexam->course
	) );
	$coursecat = $DB->get_record ( 'course_categories', array (
			'id' => $course->category
	) );

	if ($downloadexam->printrandom == 1) {
		$enrolincludes = 'manual,self,meta';
	} else {
		$enrolincludes = 'manual,self';
	}

	if ($CFG->emarking_enrolincludes && strlen ( $CFG->emarking_enrolincludes ) > 1) {
		$enrolincludes = $CFG->emarking_enrolincludes;
	}
	if (isset ( $downloadexam->enrolments ) && strlen ( $downloadexam->enrolments ) > 1) {
		$enrolincludes = $downloadexam->enrolments;
	}
	$enrolincludes = explode ( ",", $enrolincludes );

	// Get all the files uploaded as forms for this exam
	$fs = get_file_storage ();
	$files = $fs->get_area_files ( $context->id, 'mod_emarking', 'exams', $examid );

	// We filter only the PDFs
	$pdffileshash = array ();
	foreach ( $files as $filepdf ) {
		if ($filepdf->get_mimetype () === 'application/pdf') {
			$pdffileshash [] = array (
					'hash' => $filepdf->get_pathnamehash (),
					'filename' => $filepdf->get_filename ()
			);
		}
	}

	// Verify that at least we have a PDF
	if (count ( $pdffileshash ) < 1) {
		return null;
	}

	if ($downloadexam->headerqr == 1) {
		if ($groupid != null) {
			$filedir = $CFG->dataroot . "/temp/emarking/$context->id" . "/group_" . $groupid;
		} else {
			$filedir = $CFG->dataroot . "/temp/emarking/$context->id";
		}
		$fileimg = $CFG->dataroot . "/temp/emarking/$context->id/qr";
		$userimgdir = $CFG->dataroot . "/temp/emarking/$context->id/u";

		emarking_initialize_directory ( $filedir, true );
		emarking_initialize_directory ( $fileimg, true );
		emarking_initialize_directory ( $userimgdir, true );

		if ($groupid != null) {
			// Se toman los resultados del query dentro de una variable.
			$students = emarking_get_students_of_groups ( $downloadexam->course, $groupid );
		} else {
			// Se toman los resultados del query dentro de una variable.
			$students = emarking_get_students_for_printing ( $downloadexam->course );
		}

		$nombre = array ();

		$current = 0;
		// Los resultados del query se recorren mediante un foreach loop.
		foreach ( $students as $student ) {
			if (array_search ( $student->enrol, $enrolincludes ) === false) {
				continue;
			}
			$nombre [] = substr ( "$student->lastname, $student->firstname", 0, 65 );
			$rut [] = $student->idnumber;
			$moodleid [] = $student->id;
			// Get the image file for student
			$imgfound = false;
			if ($CFG->emarking_pathuserpicture && is_dir ( $CFG->emarking_pathuserpicture )) {
				$idstring = "" . $student->idnumber;
				$revid = strrev ( $idstring );
				$idpath = $CFG->emarking_pathuserpicture;
				$idpath .= "/" . substr ( $revid, 0, 1 );
				$idpath .= "/" . substr ( $revid, 1, 1 );
				if (file_exists ( $idpath . "/user$idstring.png" )) {
					$userimg [] = $idpath . "/user$idstring.png";
					$imgfound = true;
				}
			}
			if (! $imgfound) {
				$usercontext = context_user::instance ( $student->id );
				$imgfile = $DB->get_record ( 'files', array (
						'contextid' => $usercontext->id,
						'component' => 'user',
						'filearea' => 'icon',
						'filename' => 'f1.png'
				) );
				if ($imgfile)
					$userimg [] = emarking_get_path_from_hash ( $userimgdir, $imgfile->pathnamehash, "u" . $student->id, true );
				else
					$userimg [] = $CFG->dirroot . "/pix/u/f1.png";
			}
		}
		$numberstudents = count ( $nombre );

		for($i = $numberstudents; $i < $numberstudents + $downloadexam->extraexams; $i ++) {
			$nombre [$i] = '..............................................................................';
			$rut [$i] = '0';
			$moodleid [$i] = '0';
			$userimg [$i] = $CFG->dirroot . "/pix/u/f1.png";
		}

		$newfile = emarking_get_path_from_hash ( $filedir, $pdffileshash [$current] ['hash'] );
		$path = $filedir . "/" . str_replace ( ' ', '-', $pdffileshash [$current] ['filename'] );
		$hash = hash_file ( 'md5', $path );

		$logoisconfigured = false;
		if ($logofile = emarking_get_logo_file ()) {
			$logofilepath = emarking_get_path_from_hash ( $filedir, $logofile->get_pathnamehash () );
			$logoisconfigured = true;
		}

		$file1 = $filedir . "/" . emarking_clean_filename ( $course->shortname, true ) . "_" . emarking_clean_filename ( $downloadexam->name, true ) . ".pdf";

		$pdf = new FPDI ();
		$cp = $pdf->setSourceFile ( $path );
		if ($cp > 99) {
			print_error ( get_string ( 'page', 'mod_emarking' ) );
		}

		if ($multiplepdfs || $groupid != null) {
			$zip = new ZipArchive ();
			if ($groupid != null) {
				$file1 = $filedir . "/" . emarking_clean_filename ( $course->shortname, true ) . "_" . "GRUPO_" . $groupid . "_" . emarking_clean_filename ( $downloadexam->name, true ) . ".zip";
			} else {
				$file1 = $filedir . "/" . emarking_clean_filename ( $course->shortname, true ) . "_" . emarking_clean_filename ( $downloadexam->name, true ) . ".zip";
			}
				
			if ($zip->open ( $file1, ZipArchive::CREATE ) !== true) {
				return null;
			}
		}

		if ($sendprintorder) {
			if ($pbar != null) {
				$pbar->update ( 0, count ( $nombre ), '' );
			}
		}

		$jobs [] = array ();

		if ($downloadexam->printlist == 1) {
				
			$flag = 0;
			// lista de alumnos
			if ($flag == 0) {
				$pdf->SetAutoPageBreak ( false );
				$pdf->AddPage ();

				$left = 85;
				$top = 8;
				$pdf->SetFont ( 'Helvetica', 'B', 12 );
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( "LISTA DE ALUMNOS" ) );

				$left = 15;
				$top = 16;
				$pdf->SetFont ( 'Helvetica', '', 8 );
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( "Asignatura: " . $course->fullname ) );

				$left = 15;
				$top = 22;
				$pdf->SetFont ( 'Helvetica', '', 8 );
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( "N° Inscritos: " . count ( $nombre ) ) );

				// $year = date("Y");
				// $month= date("F");
				// $day= date("m");

				setlocale ( LC_ALL, "es_ES" );
				$left = 15;
				$top = 28;
				$pdf->SetFont ( 'Helvetica', '', 8 );
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( "Fecha: " . strftime ( "%A %d de %B del %Y" ) ) );

				$left = 15;
				$top = 36;
				$pdf->SetXY ( $left, $top );
				$pdf->Cell ( 10, 10, "N°", 1, 0, 'L' );
				$pdf->Cell ( 120, 10, "Nombres", 1, 0, 'L' );
				$pdf->Cell ( 50, 10, "Firmas", 1, 0, 'L' );

				$t = 0;
				$t2 = 46;
				for($a = 0; $a <= count ( $nombre ) - 1; $a ++) {
						
					if ($n == 24 || $n == 48 || $n == 72 || $n == 96 || $n == 120) {
						$pdf->AddPage ();
						$t = 0;
						$t2 = 8;
					}
						
					$top = $t2 + $t;
					$n = $a + 1;
					$pdf->SetFont ( 'Helvetica', '', 8 );
					$pdf->SetXY ( $left, $top );
					$pdf->Cell ( 10, 10, $n . ")", 1, 0, 'L' );
					$pdf->Cell ( 120, 10, core_text::strtoupper ( $nombre [$a] ), 1, 0, 'L' );
					$pdf->Cell ( 50, 10, "", 1, 0, 'L' );
					// $pdf->Write ( 1, core_text::strtoupper ( $nombre [$a] ) );
					$t = $t + 10;
				}
				$flag = 1;

				if ($multiplepdfs || $groupid != null) {
					if ($groupid != null) {
						$pdffile = $filedir . "/Lista_de_alumnos_" . "GRUPO_" . $groupid . ".pdf";
						$pdf->Output ( $pdffile, "F" ); // se genera el nuevo pdf
						$zip->addFile ( $pdffile, "GRUPO_" . $groupid . ".pdf" );
					} else {
						$pdffile = $filedir . "/Lista_de_alumnos_" . emarking_clean_filename ( $course->shortname, true ) . ".pdf";
						$pdf->Output ( $pdffile, "F" ); // se genera el nuevo pdf
						$zip->addFile ( $pdffile, "Lista_de_alumnos_" . emarking_clean_filename ( $course->shortname, true ) . ".pdf" );
					}
				}
				$printername = explode ( ',', $CFG->emarking_printername );
				if ($sendprintorder) {
					if ($printername [$_POST ["printername"]] != "Edificio-C-mesonSecretaria") {
						$command = "lp -d " . $printername [$_POST ["printername"]] . " -o StapleLocation=UpperLeft -o fit-to-page -o media=Letter " . $pdffile;
					} else {
						$command = "lp -d " . $printername [$_POST ["printername"]] . " -o StapleLocation=SinglePortrait -o PageSize=Letter -o Duplex=none " . $pdffile;
					}
						
					$printresult = exec ( $command );
					if ($CFG->debug) {
						echo "$command <br>";
						echo "$printresult <hr>";
					}
				}
			}
		}

		// Here we produce a PDF file for each student
		for($k = 0; $k <= count ( $nombre ) - 1; $k ++) {
				
			// If there are multiplepdfs we have to produce one per student
			if ($multiplepdfs || $sendprintorder || $groupid != null) {
				$pdf = new FPDI ();
			}
				
			if ($multiplepdfs || $sendprintorder || $groupid != null || count ( $pdffileshash ) > 1) {
				$current ++;
				if ($current > count ( $pdffileshash ) - 1)
					$current = 0;
				$newfile = emarking_get_path_from_hash ( $filedir, $pdffileshash [$current] ['hash'] );
				$path = $filedir . "/" . str_replace ( ' ', '-', $pdffileshash [$current] ['filename'] );
				$cp = $pdf->setSourceFile ( $path );
			}
				
			$pdf->SetAutoPageBreak ( false );
			for($i = 1; $i <= $cp + $downloadexam->extrasheets; $i = $i + 1) {
				$h = rand ( 1, 999999 );
				$img = $fileimg . "/qr" . $h . "_" . $rut [$k] . "_" . $i . "_" . $hash . ".png";
				$imgrotated = $fileimg . "/qr" . $h . "_" . $rut [$k] . "_" . $i . "_" . $hash . "r.png";
				// Se genera QR con id, curso y número de página
				$qrstring = "$moodleid[$k] - $downloadexam->course - $i";
				QRcode::png ( $qrstring, $img ); // se inserta QR
				QRcode::png ( $qrstring . " - R", $imgrotated ); // se inserta QR
				$gdimg = imagecreatefrompng ( $imgrotated );
				$rotated = imagerotate ( $gdimg, 180, 0 );
				imagepng ( $rotated, $imgrotated );
				$pdf->AddPage (); // Agrega una nueva página
				if ($i <= $cp) {
					$tplIdx = $pdf->importPage ( $i ); // Se importan las páginas del documento pdf.
					$pdf->useTemplate ( $tplIdx, 0, 0, 0, 0, $adjustPageSize = true ); // se inserta como template el archivo pdf subido
				}
				/*
				 * Ahora se escribe texto sobre las páginas ya importadas. Se fija la fuente, el tipo y el tamaño de la letra. Se señala el título. Se da el nombre, apellido y rut del alumno al cual pertenece la prueba. Se indica el curso correspondiente a la evaluación. Se introduce una imagen. Esta corresponde al QR que se genera con los datos
				*/

				if ($CFG->emarking_includelogo && $logoisconfigured) {
					$pdf->Image ( $logofilepath, 2, 8, 30 );
				}

				$left = 58;
				$top = 8;
				$pdf->SetFont ( 'Helvetica', '', 12 );
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( $downloadexam->name ) );
				$pdf->SetFont ( 'Helvetica', '', 9 );
				$top += 5;
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( get_string ( 'name' ) . ": " . $nombre [$k] ) );
				$top += 4;
				if ($rut [$k] && strlen ( $rut [$k] ) > 0) {
					$pdf->SetXY ( $left, $top );
					$pdf->Write ( 1, get_string ( 'idnumber', 'mod_emarking' ) . ": " . $rut [$k] );
					$top += 4;
				}
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( get_string ( 'course' ) . ": " . $course->fullname ) );
				$top += 4;
				if (file_exists ( $userimg [$k] )) {
					$pdf->Image ( $userimg [$k], 35, 8, 15, 15, "PNG", null, "T", true );
				}
				$totals = new stdClass ();
				$totals->identified = $i;
				$totals->total = $cp + $downloadexam->extrasheets;
				$pdf->SetXY ( $left, $top );
				$pdf->Write ( 1, core_text::strtoupper ( get_string ( 'page' ) . ": " . get_string ( 'aofb', 'mod_emarking', $totals ) ) );
				$pdf->Image ( $img, 176, 3, 34 ); // y antes era -2
				$pdf->Image ( $imgrotated, 0, $pdf->getPageHeight () - 35, 34 );
				unlink ( $img );
				unlink ( $imgrotated );
			}
				
			if ($multiplepdfs || $sendprintorder || $groupid != null) {

				$pdffile = $filedir . "/" . emarking_clean_filename ( $qrstring ) . ".pdf";

				if (file_exists ( $pdffile )) {
					$pdffile = $filedir . "/" . emarking_clean_filename ( $qrstring ) . "_" . $k . ".pdf";
					$pdf->Output ( $pdffile, "F" ); // se genera el nuevo pdf
					$zip->addFile ( $pdffile, emarking_clean_filename ( $qrstring ) . "_" . $k . ".pdf" );
				} else {
					$pdffile = $filedir . "/" . emarking_clean_filename ( $qrstring ) . ".pdf";
					$pdf->Output ( $pdffile, "F" ); // se genera el nuevo pdf
					$zip->addFile ( $pdffile, emarking_clean_filename ( $qrstring ) . ".pdf" );
				}

				$jobs [$k] ["param_1_pbar"] = $k + 1;
				$jobs [$k] ["param_2_pbar"] = count ( $nombre );
				$jobs [$k] ["param_3_pbar"] = 'Imprimiendo pruebas de ' . core_text::strtoupper ( $nombre [$k] );
				$jobs [$k] ["name_job"] = $pdffile;
			}
		}

		$printername = explode ( ',', $CFG->emarking_printername );

		if ($sendprintorder) {
			foreach ( $jobs as &$valor ) {
				if (! empty ( $valor )) {
					if ($pbar != null) {
						$pbar->update ( $valor ["param_1_pbar"], $valor ["param_2_pbar"], $valor ["param_3_pbar"] );
					}
						
					if ($printername [$_POST ["printername"]] != "Edificio-C-mesonSecretaria") {
						$command = "lp -d " . $printername [$_POST ["printername"]] . " -o StapleLocation=UpperLeft -o fit-to-page -o media=Letter " . $valor ["name_job"];
					} else {
						$command = "lp -d " . $printername [$_POST ["printername"]] . " -o StapleLocation=SinglePortrait -o PageSize=Letter -o Duplex=none " . $valor ["name_job"];
					}
						
					$printresult = exec ( $command );
					if ($CFG->debug) {
						echo "$command <br>";
						echo "$printresult <hr>";
					}
				}
			}
		}

		if ($multiplepdfs || $groupid != null) {
			// Generate Bat File
			$printerarray = array ();
			foreach ( explode ( ',', $CFG->emarking_printername ) as $printer ) {
				$printerarray [] = $printer;
			}
				
			$contenido = "@echo off\r\n";
			$contenido .= "TITLE Sistema de impresion\r\n";
			$contenido .= "color ff\r\n";
			$contenido .= "cls\r\n";
			$contenido .= ":MENUPPL\r\n";
			$contenido .= "cls\r\n";
			$contenido .= "echo #######################################################################\r\n";
			$contenido .= "echo #                     Sistema de impresion                            #\r\n";
			$contenido .= "echo #                                                                     #\r\n";
			$contenido .= "echo # @copyright 2014 Eduardo Miranda                                     #\r\n";
			$contenido .= "echo # Fecha Modificacion 23-04-2014                                       #\r\n";
			$contenido .= "echo #                                                                     #\r\n";
			$contenido .= "echo #   Para realizar la impresion debe seleccionar una de las impresoras #\r\n";
			$contenido .= "echo #   configuradas.                                                     #\r\n";
			$contenido .= "echo #                                                                     #\r\n";
			$contenido .= "echo #                                                                     #\r\n";
			$contenido .= "echo #######################################################################\r\n";
			$contenido .= "echo #   Seleccione una impresora:                                         #\r\n";
				
			$i = 0;
			while ( $i < count ( $printerarray ) ) {
				$contenido .= "echo #   " . $i . " - " . $printerarray [$i] . "                                                   #\r\n";
				$i ++;
			}
				
			$contenido .= "echo #   " . $i ++ . " - Cancelar                                                      #\r\n";
			$contenido .= "echo #                                                                     #\r\n";
			$contenido .= "echo #######################################################################\r\n";
			$contenido .= "set /p preg01= Que desea hacer? [";
				
			$i = 0;
			while ( $i <= count ( $printerarray ) ) {
				if ($i == count ( $printerarray )) {
					$contenido .= $i;
				} else {
					$contenido .= $i . ",";
				}

				$i ++;
			}
			$contenido .= "]\r\n";
				
			$i = 0;
			while ( $i < count ( $printerarray ) ) {

				$contenido .= "if %preg01%==" . $i . " goto MENU" . $i . "\r\n";
				$i ++;
			}
				
			$contenido .= "if %preg01%==" . $i ++ . " goto SALIR\r\n";
			$contenido .= "goto MENU\r\n";
			$contenido .= "pause\r\n";
				
			$i = 0;
			while ( $i < count ( $printerarray ) ) {

				$contenido .= ":MENU" . $i . "\r\n";
				$contenido .= "cls\r\n";
				$contenido .= "set N=%Random%%random%\r\n";
				$contenido .= "plink central.apuntes mkdir -m 0777 ~/pruebas/%N%\r\n";
				$contenido .= "pscp *.pdf central.apuntes:pruebas/%N%\r\n";
				$contenido .= "plink central.apuntes cp ~/pruebas/script_pruebas.sh ~/pruebas/%N%\r\n";
				$contenido .= "plink central.apuntes cd pruebas/%N%;./script_pruebas.sh " . $printerarray [$i] . "\r\n";
				$contenido .= "plink central.apuntes rm -dfr ~/pruebas/%N%\r\n";
				$contenido .= "EXIT\r\n";

				$i ++;
			}
				
			$contenido .= ":SALIR\r\n";
			$contenido .= "CLS\r\n";
			$contenido .= "ECHO Cancelando...\r\n";
			$contenido .= "EXIT\r\n";
				
			$random = rand ();
				
			mkdir ( $CFG->dataroot . '/temp/emarking/' . $random . '_bat/', 0777 );
			// chmod($random."_bat/", 0777);
				
			$fp = fopen ( $CFG->dataroot . "/temp/emarking/" . $random . "_bat/imprimir.bat", "x" );
			fwrite ( $fp, $contenido );
			fclose ( $fp );
			// Generate zip file
			$zip->addFile ( $CFG->dataroot . "/temp/emarking/" . $random . "_bat/imprimir.bat", "imprimir.bat" );
			$zip->close ();
			unlink ( $CFG->dataroot . "/temp/emarking/" . $random . "_bat/imprimir.bat" );
			rmdir ( $CFG->dataroot . "/temp/emarking/" . $random . "_bat" );
		} else if (! $sendprintorder) {
			$pdf->Output ( $file1, "F" ); // se genera el nuevo pdf
		}

		$downloadexam->status = EMARKING_EXAM_SENT_TO_PRINT;
		$downloadexam->printdate = time ();
		$DB->update_record ( 'emarking_exams', $downloadexam );

		if ($sendprintorder) {
			$pbar->update_full ( 100, 'Impresión completada exitosamente' );
			return $filedir;
		}

		if ($groupid != null) {
			unlink ( $file1 );
			return $filedir;
		} else {
			ob_start (); // modificación: ingreso de esta linea, ya que anterior revisión mostraba error en el archivo
				
			header ( 'Content-Description: File Transfer' );
			header ( 'Content-Type: application/x-download' );
			header ( 'Content-Disposition: attachment; filename=' . basename ( $file1 ) );
			header ( 'Content-Transfer-Encoding: binary' );
			header ( 'Expires: 0' );
			header ( 'Cache-Control: must-revalidate' );
			header ( 'Pragma: public' );
			ob_clean ();
			flush ();
				
			readfile ( $file1 );
			unlink ( $file1 ); // borra archivo temporal en moodledata
			exit ();
		}

		return false;
	} else {
		$students = emarking_get_students_for_printing ( $downloadexam->course );
		$filedir = $CFG->dataroot . "/temp/emarking/$context->id";
		emarking_initialize_directory ( $filedir, true );
		$printername = explode ( ',', $CFG->emarking_printername );
		$totalAlumn = 0;
		$pdffiles = array ();

		for($current = 0; $current < count ( $pdffileshash ); $current ++) {
			$newfile = emarking_get_path_from_hash ( $filedir, $pdffileshash [$current] ['hash'] );
			$path = $filedir . "/" . str_replace ( ' ', '-', $pdffileshash [$current] ['filename'] );
				
			$pdf = new FPDI ();
			$cp = $pdf->setSourceFile ( $path );
			if ($cp > 99) {
				print_error ( get_string ( 'page', 'mod_emarking' ) );
			}
				
			$pdf->SetAutoPageBreak ( false );
				
			$s = 1;
				
			while ( $s <= $cp + $downloadexam->extrasheets ) {
				$pdf->AddPage ();
				if ($s <= $cp) {
					$tplIdx = $pdf->importPage ( $s ); // Se importan las páginas del documento pdf.
					$pdf->useTemplate ( $tplIdx, 0, 0, 0, 0, $adjustPageSize = true ); // se inserta como template el archivo pdf subido
				}
				$s ++;
			}
				
			$pdffile = $filedir . "/" . $current . emarking_clean_filename ( $file->filename );
			$pdf->Output ( $pdffile, "F" );
			$pdffiles [] = $pdffile;
		}

		$totalAlumn = count ( $students );

		if ($pbar != null) {
			$pbar->update ( 0, $totalAlumn, '' );
		}

		for($k = 0; $k <= $totalAlumn + $downloadexam->extraexams - 1; $k ++) {
			$pdffile = $pdffiles [$k % count ( $pdffileshash )];
			if ($printername [$_POST ["printername"]] != "Edificio-C-mesonSecretaria") {
				$command = "lp -d " . $printername [$_POST ["printername"]] . " -o StapleLocation=UpperLeft -o fit-to-page -o media=Letter " . $pdffile;
			} else {
				$command = "lp -d " . $printername [$_POST ["printername"]] . " -o StapleLocation=SinglePortrait -o PageSize=Letter -o Duplex=none " . $pdffile;
			}
				
			// $printresult = exec ( $command );
			if ($CFG->debug) {
				echo "$command <br>";
				echo "$printresult <hr>";
			}
				
			if ($pbar != null) {
				$pbar->update ( $k, $totalAlumn, '' );
			}
		}

		$pbar->update_full ( 100, 'Impresión completada exitosamente' );

		return true;
		/*
		 * $downloadexam->status = EMARKING_EXAM_SENT_TO_PRINT; $downloadexam->printdate = time (); $DB->update_record ( 'emarking_exams', $downloadexam ); $downloadURL = $CFG->wwwroot . '/pluginfile.php/' . $file->contextid . '/mod_emarking/' . $file->filearea . '/' . $file->itemid . '/' . $file->filename; $startdownload = true; echo '<meta http-equiv="refresh" content="2;url=' . $downloadURL . '">'; return true;
		*/
	}
}

/**
 * Erraces all the content of a directory, then ir creates te if they don't exist.
 *
 * @param unknown $dir
 *        	Directorio
 * @param unknown $delete
 *        	Borrar archivos previamente
 */
function emarking_initialize_directory($dir, $delete) {
	if ($delete) {
		// First erase all files
		if (is_dir ( $dir )) {
			emarking_rrmdir ( $dir );
		}
	}

	// Si no existe carpeta para temporales se crea
	if (! is_dir ( $dir )) {
		if (! mkdir ( $dir, 0777, true )) {
			print_error ( get_string ( 'initializedirfail', 'mod_emarking', $dir ) );
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
	foreach ( glob ( $dir . '/*' ) as $file ) {
		if (is_dir ( $file ))
			emarking_rrmdir ( $file );
		else
			unlink ( $file );
	}
	rmdir ( $dir );
}

/**
 * Sends an sms message using UAI's service with infobip.com.
 * Returns true if successful, false otherwise.
 *
 * @param string $message
 *        	the message to be sent
 * @param string $number
 *        	the mobile number
 */
function emarking_send_sms($message, $number) {
	global $CFG;

	$postUrl = $CFG->emarking_smsurl;

	$xmlString = "<SMS>
	<authentification>
	<username>$CFG->emarking_smsuser</username>
	<password>$CFG->emarking_smspassword</password>
	</authentification>
	<message>
	<sender>Webcursos</sender>
	<text>$message</text>
	<recipients>
	<gsm>$number</gsm>
	</recipients>
	</message>

	</SMS>";

	// previamente formateado en XML
	$fields = "XML=" . urlencode ( $xmlString );

	// Se require cURL
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $postUrl );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

	// Respuesta del POST
	$response = curl_exec ( $ch );
	curl_close ( $ch );

	if (! $response) {
		return false;
	}

	try {
		$xml = new SimpleXmlElement ( $response );
	} catch ( exception $e ) {
		return false;
	}

	if ($xml && $xml->status == 1) {
		return true;
	}

	return false;
}

