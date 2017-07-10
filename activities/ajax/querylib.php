<?php 

defined ("MOODLE_INTERNAL") || die ();
global $CFG;

function ajax_get_criteria($rubricid){
	global $DB;
	$sql = "SELECT rc.id
		FROM {emarking_rubrics_criteria} rc
		INNER JOIN {emarking_rubrics} r ON (r.id = rc.rubricid )
		where r.id = ?";
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

function ajax_get_criteria_levels($criteriaid) {
	global $DB;
$sql="SELECT rl.*, rc.description as criterion, i.max
FROM mdl_emarking_rubrics_levels as rl
INNER JOIN mdl_emarking_rubrics_criteria rc ON (rc.id = rl.criterionid )
LEFT JOIN (select criterionid, max(score) as max FROM mdl_emarking_rubrics_levels as rl group by criterionid) as i on (i.criterionid=rl.criterionid)
WHERE rl.criterionid=?
ORDER BY rl.criterionid ASC, rl.score DESC";

	$data = $DB->get_records_sql($sql, array($criteriaid));
	
	return $data;
}
