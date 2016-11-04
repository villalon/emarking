<?php
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
require_once("$CFG->libdir/pdflib.php");
GLOBAL $USER;

$activityid = required_param('id', PARAM_INT);
$action = required_param('action', PARAM_TEXT);
$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));
$user_object = $DB->get_record('user', array('id'=>$activity->userid));
$usercontext=context_user::instance($USER->id);

// create new PDF document

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, 'mm', 'A4', true, 'UTF-8', false);

// set document information
$pdf->SetCreator($USER->firstname.' '.$USER->lastname);
$pdf->SetAuthor($user->firstname.' '.$user->lastname);
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
$pdf->AddPage();

$html = '<h3>Hoja de escritura</h3>
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


if($action == 'download'){
	$pdf->Output($activity->title.'.pdf', 'I');
}
elseif($action == 'create'){
$pdfstring=$pdf->Output('', 'S');
$fs = get_file_storage();

// Prepare file record object
$fileinfo = array(
		'component' => 'user',     // usually = table name
		'filearea' => 'draft',     // usually = table name
		'itemid' => 2,               // usually = ID of row in table
		'contextid' => $usercontext->id, // ID of context
		'filepath' => '/',           // any path beginning and ending in /
		'filename' => $activity->title.'.pdf'); // any filename

// Get file
$file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
		$fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

// Read contents
if ($file) {
	$contents = $file->get_content();
} else {
	// file doesn't exist - do something
	$file = $fs->create_file_from_string($fileinfo, $pdfstring);
}
}
