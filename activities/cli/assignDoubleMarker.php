<?php
ini_set ( 'max_execution_time', 9999999999999999 );
define ( 'CLI_SCRIPT', true );

require (dirname ( dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) . '/config.php');

// Force a debugging mode regardless the settings in the site administration
@error_reporting ( E_ALL | E_STRICT ); // NOT FOR PRODUCTION SERVERS!
@ini_set ( 'display_errors', '1' ); // NOT FOR PRODUCTION SERVERS!
global $PAGE, $DB, $USER;
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
set_time_limit ( 0 );

$firstMarker = 531;
$secondMarker = 3;
$thirdMarker = 2;
$maxDia = 12;

$tests = $DB->get_records ( 'emarking_double_marking' );
$stage =1;
$stageCount=0;
$contadorMarker = 1;
$markerAnterior=0;
$markerArray = array();

foreach ( $tests as $test ) {
	list ( $cm, $emarking, $course, $context ) = emarking_get_cm_course_instance_by_id ( $test->emarkingid );
	
	$emarking_one = $test->emarkingid;
	$emarking_two = $test->secondemarkingid;
	
	$count_drafts = $DB->count_records ( 'emarking_draft', array (
			'emarkingid' => $emarking->id
	) );
	$percentage = $count_drafts * 3 / 10;

	$round = round ( $percentage );
	
	$sql = 'select *, rand() as rand from mdl_emarking_draft where emarkingid=? order by rand ASC';
	$drafts = $DB->get_records_sql ( $sql, array (
			$emarking->id 
	) );
	$contador = 0;
	
	
	foreach ( $drafts as $draft ) {
		
		$draftid = $draft->id;
		
		$data = new stdClass ();
		$data->emarking = $emarking_one;
		$data->draft = $draftid;
		$data->seconddraft = 0;
		$data->secondmarker = 0;
		$data->secondemarking = 0;
		$j = $contadorMarker;
		switch ($contadorMarker) {
			case 1 :
				$data->marker = $firstMarker;
				$contadorMarker ++;
				break;
			case 2 :
				$data->marker = $secondMarker;
				$contadorMarker ++;
				break;
			case 3 :
				$data->marker = $thirdMarker;
				$contadorMarker = 1;
				break;
		}

		if ($contador < $round) {
			list ( $secondcm, $secondemarking, $secondcourse, $secondcontext ) = emarking_get_cm_course_instance_by_id ( $emarking_two );
			$sqldos = 'select da.id as draftid
from mdl_emarking_draft as ed
inner join mdl_emarking_submission as es on (ed.submissionid=es.id)
inner join ( 
select ed.id as id, es.student as student ,es.emarking as emarking from mdl_emarking_submission  as es
inner join mdl_emarking_draft as ed on(es.id=ed.submissionid)) as da on (da.emarking=? AND da.student=es.student)
where ed.id=?';
			$seconddraft = $DB->get_record_sql ( $sqldos, array (
					$secondemarking->id,
					$draftid 
			) );
			
			switch ($contadorMarker) {
				case 1 :
					$data->secondmarker= $firstMarker;
					
					break;
				case 2 :
					$data->secondmarker= $secondMarker;
					
					break;
				case 3 :
					$data->secondmarker= $thirdMarker;
					
					break;
			}
				
			$data->seconddraft = $seconddraft->draftid;
			$data->secondemarking = $emarking_two;
			$contador ++;
		}
		
	
			
			
			if($stageCount % $maxDia == 0 AND $stageCount!=0){
				$stage++;
				$stageCount =0;
			}
			$data->stage=$stage;
			if (! $DB->get_record ( 'emarking_fondef_marking', array (
					'draft' => $draftid
			) )) {
			 $DB->insert_record ( 'emarking_fondef_marking', $data );
			}
			$stageCount++;
			if($data->secondemarking > 0 AND $stageCount % $maxDia != 0){
				$stageCount++;
			
			var_dump($data);
			
		}
		
	}
}
