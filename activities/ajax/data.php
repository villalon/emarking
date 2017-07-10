<?php
define ( "AJAX_SCRIPT", true );

require_once (dirname ( dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) . '/config.php');
global $CFG, $DB, $OUTPUT, $USER;
require_once $CFG->dirroot . '/mod/emarking/activities/ajax/querylib.php';
$action = required_param ( "action", PARAM_TEXT );
$rubricid = optional_param ( "rubricid", 0, PARAM_INT );


// Callback para from webpage
$callback = optional_param ( "callback", null, PARAM_RAW_TRIMMED );

// Headers
header ( "Content-Type: text/javascript" );
header ( "Cache-Control: no-cache" );
header ( "Pragma: no-cache" );

switch ($action) {
	case 'getRubric' :
		$data = ajax_get_criteria($rubricid);
		$criteriasArray = array();
		foreach($data as $criteriondata){
			$criterion_levels = array();
			$criterion_definition=null;
			$criterionid = null;
			foreach ($criteriondata as $leveldata){
				$level = array();
				$level['id']=$leveldata->id;
				$level['score']=$leveldata->score;
				$level['definition']=$leveldata->definition;
				$criterion_definition = $leveldata->criterion;
				$criterionid = $leveldata->criterionid;
				
				$criterion_levels[]=$level;
			}
			$criteriasArray[]=array(
					"id" => $criterionid,
					"definition" => $criterion_definition,
					"levels" => $criterion_levels
			);
		}
		$jsonOutputs = array (
				"error" => "",
				"values" => $criteriasArray
		);
		
		break;
}


$jsonOutput = json_encode ( $jsonOutputs);
if ($callback){
	$jsonOutput = $callback . "(" . $jsonOutput . ");";
}
echo $jsonOutput;

