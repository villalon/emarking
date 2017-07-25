<?php 

defined ("MOODLE_INTERNAL") || die ();
global $CFG;

function ajax_get_criteria($rubricid){
	global $DB;
	check_sortorder($rubricid);
	$sql = "SELECT rc.id
		FROM {emarking_rubrics_criteria} rc
		INNER JOIN {emarking_rubrics} r ON (r.id = rc.rubricid )
		where r.id = ?
		Order by rc.sortorder";
	
	
	$data = $DB->get_records_sql ( $sql, array (
			$rubricid
	) );
	$rubric = array ();
	foreach ( $data as $criteria ) {
		$criteriaLevels = ajax_get_criteria_levels( $criteria->id);
		$rubric [] = $criteriaLevels;
	}
	return $rubric;
}

function check_sortorder($rubricid){
	global $DB;
	
	$criterias = $DB->get_records("emarking_rubrics_criteria", array("rubricid"=>$rubricid));
	$sortcount = 1;
	foreach($criterias as $criterion){
		if($criterion->sortorder == null){
			$criterion->sortorder = $sortcount;
			$sortcount++;
			$DB->update_record("emarking_rubrics_criteria",$criterion);
		}
	}
}

function ajax_get_criteria_levels($criteriaid) {
	global $DB;
$sql="SELECT rl.*, rc.description as criterion, i.max, rc.rubricid
FROM mdl_emarking_rubrics_levels as rl
INNER JOIN mdl_emarking_rubrics_criteria rc ON (rc.id = rl.criterionid )
LEFT JOIN (select criterionid, max(score) as max FROM mdl_emarking_rubrics_levels as rl group by criterionid) as i on (i.criterionid=rl.criterionid)
WHERE rl.criterionid=?
ORDER BY rl.criterionid ASC, rl.score DESC";

	return $DB->get_records_sql($sql, array($criteriaid));
}

function ajax_create_criterion($data){
	global $DB;
	return $DB->insert_record('emarking_rubrics_criteria', $data);
}

function ajax_get_criterion($criterionid){
	global $DB;
	return $DB->get_record("emarking_rubrics_criteria", array("id"=>$criterionid));
}

function ajax_update_criterion($criteriondata){
	global $DB;
	return $DB->update_record("emarking_rubrics_criteria",$criteriondata);
}
function ajax_update_criterion_sortorder($rubricid,$sortorder){
	global $DB;
	$sql = "SELECT * FROM {emarking_rubrics_criteria} where rubricid =? and sortorder > ?";
	$data = $DB->get_records_sql($sql, array($rubricid,$sortorder));
	foreach($data as $criterion){
	$criterion->sortorder = $criterion->sortorder - 1;
	ajax_update_criterion($criterion);
	}
}

function ajax_remove_criterion($criterionid){
	global $DB;
	return $DB->delete_records("emarking_rubrics_criteria", array("id"=>$criterionid));
}

function ajax_create_level($data){
	global $DB;
	return $DB->insert_record('emarking_rubrics_levels', $data);
}

function ajax_get_level($levelid){
	global $DB;
	return $DB->get_record("emarking_rubrics_levels", array("id"=>$levelid));
}

function ajax_update_level($leveldata){
	global $DB;
	return $DB->update_record("emarking_rubrics_levels",$leveldata);
}

function ajax_update_level_score($criterionid,$score){
	global $DB;
	$sql = "SELECT * FROM mdl_emarking_rubrics_levels WHERE criterionid=? and score > ?";
	$data = $DB->get_records_sql($sql, array($criterionid,$score));
	foreach($data as $level){
		$level->score = $level->score - 1;
		ajax_update_level($level);
	}
}

function ajax_remove_level($levelid){
	global $DB;
	return $DB->delete_records("emarking_rubrics_levels", array("id"=>$levelid));
}
function ajax_get_score($criterionid){
	global $DB;
	$count = $DB->count_records("emarking_rubrics_levels", array("criterionid"=>$criterionid));
	return $count + 1;
}
function ajax_get_sort_order($rubricid){
	global $DB;
	$count = $DB->count_records("emarking_rubrics_criteria", array("rubricid"=>$rubricid));
	return $count + 1;
}