<?php
ini_set('max_execution_time', 9999999999999999);
define('CLI_SCRIPT', true);

require (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');



// Force a debugging mode regardless the settings in the site administration
@error_reporting(E_ALL | E_STRICT); // NOT FOR PRODUCTION SERVERS!
@ini_set('display_errors', '1');    // NOT FOR PRODUCTION SERVERS!
global $PAGE, $DB, $USER;

set_time_limit(0);

$firstMarker=33;
$secondMarker=32;
$thirdMarker=35;

$tests=$DB->get_records('emarking_double_marking');

foreach($tests as $test){
	
	$emarking_one=$test->emarkingid;
	$emarking_two=$test->secondemarkingid;
	
	$count_drafts=$DB->count_records('emarking_draft', array('emarkingid'=>$emarking_one));
	$percentage = $count_drafts * 3/10;
	$round=round($percentage);

$sql='select *, rand() as rand from mdl_emarking_draft where emarkingid=? order by rand ASC';
	$drafts = $DB->get_records_sql($sql,array($emarking_one));
	$contador=0;
	$contadorMarker=1;
	foreach($drafts as $draft){
		$draftid=$draft->id;
		
		$data = new stdClass ();
		$data->emarking = $emarking_one;
		$data->draft = $draftid;
		$data->seconddraft = '';
		$data->secondmarker = '';
		
		$j=$contadorMarker;
		switch($contadorMarker){
			case 1:
				$data->marker=$firstMarker;
				$contadorMarker++;
				break;
			case 2:
				$data->marker=$secondMarker;
				$contadorMarker++;
				break;
			case 3:
				$data->marker=$thirdMarker;
				$contadorMarker = 1;
				break;
		}
		
		
		
		if($contador <= $round){
$sqldos='select da.id as draftid
from mdl_emarking_draft as ed
inner join mdl_emarking_submission as es on (ed.submissionid=es.id)
inner join ( 
select ed.id as id, es.student as student ,es.emarking as emarking from mdl_emarking_submission  as es
inner join mdl_emarking_draft as ed on(es.id=ed.submissionid)) as da on (da.emarking=? AND da.student=es.student)
where ed.id=?';
$seconddraft = $DB->get_record_sql($sqldos,array($emarking_two,$draftid));
var_dump($seconddraft);
die();
$markerArray=array(1=>$firstMarker,2=>$secondMarker,3=>$thirdMarker);
unset($markerArray[$j]);

			$data->seconddraft = $seconddraft->draftid;
			$data->secondmarker = $markerArray[array_rand($markerArray)];
			$contador++;
		}
		
		if(!$DB->get_record('emarking_fondef_marking',array('draft'=>$draftid))){
		$DB->insert_record ( 'emarking_fondef_marking', $data );
		var_dump($data);
		}
	}
}