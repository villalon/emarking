<?php
define ( "AJAX_SCRIPT", true );

require_once (dirname ( dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) . '/config.php');
global $CFG, $DB, $OUTPUT, $USER;
require_once $CFG->dirroot . '/mod/emarking/activities/ajax/querylib.php';

$action = required_param ( "action", PARAM_TEXT );
$rubricid = optional_param ( "rubricid", 0, PARAM_INT );
$levelid = optional_param( "levelid", 0, PARAM_INT);
$criterionid = optional_param( "criterionid", 0, PARAM_INT);
$leveltext = optional_param( "leveltext",null, PARAM_TEXT );
$criteriontext = optional_param( "criteriontext",null, PARAM_TEXT );
$activityid = optional_param( "levelid", 0, PARAM_INT);

// Callback para from webpage
$callback = optional_param ( "callback", null, PARAM_RAW_TRIMMED );

// Headers
header ( "Content-Type: text/javascript" );
header ( "Cache-Control: no-cache" );
header ( "Pragma: no-cache" );

switch ($action) {
	case 'test':
		check_sortorder($rubricid);
		break;
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
	case 'updateCriterionText':
		$criterion = ajax_get_criterion($criterionid);
		$criterion->description = $criteriontext;
		if(ajax_update_criterion($criterion)){
			$jsonOutputs = array (
					"error" => "",
					"values" => "ok"
			);
		}
		break;
	case 'updateLevelText':
		$level = ajax_get_level($levelid);
		$level->definition = $leveltext;
		if(ajax_update_level($level)){
			$jsonOutputs = array (
					"error" => "",
					"values" => "ok"
			);
		}
		break;
	case 'createCriterion':
		$criteriasArray = array();
		$criterion_levels = array();
		$criteriondata = new stdClass();
		$criteriondata->rubricid = $rubricid;
		$criteriondata->description = "Click para editar";
		$criteriondata->sortorder = ajax_get_sort_order($rubricid);
		$newcriterionid = ajax_create_criterion($criteriondata); 
		for ($i=1; $i < 4; $i++){
			$leveldata = new stdClass();
			$leveldata->criterionid = $newcriterionid;
			$leveldata->score = $i;
			$leveldata->definition = "Click para editar";
				
			$level = array();
			$level['id']=ajax_create_level($leveldata);
			$level['score']=$leveldata->score;
			$level['definition']=$leveldata->definition;
			$criterion_levels[]=$level;
		}
		$criteriasArray[]=array(
				"id" => $newcriterionid,
				"definition" => $criteriondata->description,
				"levels" => $criterion_levels
		);
		$jsonOutputs = array (
				"error" => "",
				"values" => $criteriasArray
		);
		break;
	case'createLevel':
		$leveldata = new stdClass();
		$leveldata->criterionid = $criterionid;
		$leveldata->score = ajax_get_score($criterionid);
		$leveldata->definition = "Click para editar";
		
		$level_array = array();
		$level_array['id']=ajax_create_level($leveldata);
		$level_array['score']=$leveldata->score;
		$level_array['definition']=$leveldata->definition;
		$jsonOutputs = array (
				"error" => "",
				"values" => $level_array
		);
		break;
	case 'removeCriterion':
		$criterion_levels=ajax_get_criteria_levels($criterionid);
		$criterion = ajax_get_criterion($criterionid);
		foreach($criterion_levels as $level){
			$rubricid=$level->rubricid;
			ajax_remove_level($level->id);
		}
		ajax_remove_criterion($criterionid);
		ajax_update_criterion_sortorder($criterion->rubricid,$criterion->sortorder);
		
		$jsonOutputs = array (
				"error" => "",
				"values" => "ok"
		);
		break;
	case 'removeLevel':
		$level=ajax_get_level($levelid);
		ajax_remove_level($levelid);
		ajax_update_level_score($level->criterionid,$level->score);
			$jsonOutputs = array (
					"error" => "",
					"values" => "ok"
			);
		
		break;
	case 'moveCriterionDown':
		$criterion = ajax_get_criterion($criterionid);
		if($nextcriterion = ajax_get_next_criterion($criterion->rubricid,$criterion->sortorder)){
			$criterionsortorder = $criterion->sortorder;
			$nextcriterionsortorder = $nextcriterion->sortorder;
			
			$criterion->sortorder = $nextcriterionsortorder;
			$nextcriterion->sortorder = $criterionsortorder;
			
			ajax_update_criterion($criterion);
			ajax_update_criterion($nextcriterion);
			
		}
		$jsonOutputs = array (
				"error" => "",
				"values" => "ok"
		);
		break;
	case 'moveCriterionUp':
		$criterion = ajax_get_criterion($criterionid);
		if($beforecriterion = ajax_get_before_criterion($criterion->rubricid,$criterion->sortorder)){
			$criterionsortorder = $criterion->sortorder;
			$beforecriterionsortorder = $beforecriterion->sortorder;
			
			$criterion->sortorder = $beforecriterionsortorder;
			$beforecriterion->sortorder = $criterionsortorder;
			
			ajax_update_criterion($criterion);
			ajax_update_criterion($beforecriterion);
			
		}
		$jsonOutputs = array (
				"error" => "",
				"values" => "ok"
		);
		break;
			
}

$jsonOutput = json_encode ( $jsonOutputs);
if ($callback){
	$jsonOutput = $callback . "(" . $jsonOutput . ");";
}
echo $jsonOutput;

