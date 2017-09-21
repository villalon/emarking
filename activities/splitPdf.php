<?php
error_reporting(E_ALL);
ini_set('max_execution_time', 9999999999999999);
define('CLI_SCRIPT', true);

require (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');



// Force a debugging mode regardless the settings in the site administration
@error_reporting(E_ALL | E_STRICT); // NOT FOR PRODUCTION SERVERS!
@ini_set('display_errors', '1');    // NOT FOR PRODUCTION SERVERS!
global $PAGE, $DB, $USER, $CFG;

require_once ($CFG->dirroot . "/lib/pdflib.php");
require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi_bridge.php");
require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi.php");
set_time_limit(0);

function split_pdf($filename, $end_directory = false, $numPages, $curso,$forma)
{


	$end_directory = $end_directory ? $end_directory : './';
	$new_path = preg_replace('/[\/]+/', '/', $end_directory.'/'.$curso.'/'.$forma);
	var_dump($new_path);
	var_dump($filename);
	if (!is_dir($new_path))
	{
		// Will make directories under end directory that don't exist
		// Provided that end directory exists and has the right permissions
		mkdir($new_path, 0777, true);
	}
	
	$pdf = new FPDI();
	$pagecount = $pdf->setSourceFile($filename); // How many pages?
	
	// Split each page into a new PDF
	
	for ($i = 1; $i <= $pagecount; $i++) {
		$new_pdf = new FPDI();
		$new_pdf->setSourceFile($filename);
		
		for ($k = 0; $k < $numPages; $k++) {
		$page = $i + $k;
		$new_pdf->AddPage();
		$new_pdf->useTemplate($new_pdf->importPage($page));

		}
		$i = $i + $numPages - 1;
		
		try {
			$new_filename = $end_directory."/".$curso."/".$forma."/".$i.".pdf";
			var_dump($new_filename);
			$new_pdf->Output($new_filename, "F");
			
			echo "Page ".$i." split into ".$new_filename."<br />\n";
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
		
	}
	
}

$dir    = 'pdf';
$files = scandir($dir);

for ($i = 2; $i < count($files); $i++){
	$file = $files [$i];
	$porciones = explode(" ", $file);
	$forma = explode(".", $porciones[2]);
	var_dump($forma[0]);
	if($forma[0] == "A"){
		$pags = 8;
	}else{
		$pags = 10;
	}
	split_pdf("pdf/".$file, "/vagrant/opt/".$porciones[0], $pags, $porciones[1],"Forma ".$forma[0]);
	
}


// Create and check permissions on end directory!
//