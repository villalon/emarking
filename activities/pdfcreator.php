<?php
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
require_once("$CFG->libdir/pdflib.php");
GLOBAL $USER, $DB;

$activityid = required_param('id', PARAM_INT);

$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));
$user_object = $DB->get_record('user', array('id'=>$activity->userid));
$usercontext=context_user::instance($USER->id);

// create new PDF document

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, 'mm', 'A4', true, 'UTF-8', false);

// set document information

// set document information
$pdf->SetCreator($USER->firstname.' '.$USER->lastname);
$pdf->SetAuthor($user_object->firstname.' '.$user_object->lastname);
$pdf->SetTitle($activity->title);
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetFont('helvetica', '', 11);
//SetMargins($left,$top,$right = -1,$keepmargins = false)
$pdf->SetMargins(25,40 , 25, true);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 50);
//$pdf->SetTopMargin(40);
// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

$pdf->writeHTML($activity->instructions, true, false, false, false, '');

$pdf->Output($activity->title.'.pdf', 'I');

	