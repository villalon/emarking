<?php
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
require_once("$CFG->libdir/pdflib.php");
GLOBAL $USER;

$activityid = required_param('id', PARAM_INT);
$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));
$user_object = $DB->get_record('user', array('id'=>$activity->userid));
// create new PDF document

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, 'mm', 'A4', true, 'UTF-8', false);

// set document information
$pdf->SetCreator('CIAE');
$pdf->SetAuthor('Francisco Ralph');
$pdf->SetTitle('Perdidos en el cerro');
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetFont('helvetica', '', 11);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 50);
$pdf->SetTopMargin(40);
// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();
// Set some content to print
$html = '<p>hola</p>';

// Print text using writeHTMLCell()
//$pdf->writeHTMLCell(0, 0, '', '', $activity->instructions, 0, 1, 0, true, '', true);
$pdf->writeHTML($activity->instructions, true, false, false, false, '');
$pdf->AddPage();

$html = '<h3>Hoja de respuesta</h3>
<table cellpadding="7" cellspacing="1" border="1">
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
</table>';

//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
$pdf->writeHTML($html, true, false, false, false, '');
// ---------------------------------------------------------
// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('example_001.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+