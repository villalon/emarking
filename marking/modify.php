<?php
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

// Course module id
$cmid = required_param('id', PARAM_INT);
// Criteria
$criterionid = required_param('criterionid', PARAM_INT);

$commentid = required_param('commentid', PARAM_INT);
// Validate course module
if (! $cm = get_coursemodule_from_id('emarking', $cmid)) {
	print_error(get_string('invalidcoursemodule', 'mod_emarking') . " id: $cmid");
}

$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/emarking/marking/modify.php');
$PAGE->set_pagelayout('base');
$PAGE->set_context(context_system::instance());

$comment=$DB->get_record('emarking_comment',array('id'=>$commentid));
$levels=$DB->get_records('gradingform_rubric_levels',array('criterionid'=>$criterionid));

echo $OUTPUT->header();
echo "<h4>Modificando Criterio: 2</h4>";
echo "Seleccione la nueva ";
echo "<br>";
foreach($levels as $level){
	
	if($level->id==$comment->levelid){
	echo '<div style="position: relative;float:left;width:150px;height:150px;border:1px solid #000;background-color:#FF7878;border-color: #48D063">'.$level->definition.'<div style="position: absolute;bottom: 0;">'.floor($level->score).' puntos</div></div>';	
	}
	else{
	echo '<div style="position: relative;float:left;width:150px;height:150px;border:1px solid #000;background-color:#ffffff;border-color: #48D063">'.$level->definition.'<div style="position: absolute;bottom: 0;">'.floor($level->score).' puntos</div></div>';
	}

}
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";

echo $OUTPUT->single_button("", 'Guardar');
echo $OUTPUT->footer();