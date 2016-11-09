<?php
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) )). '/config.php');
GLOBAL $USER, $CFG, $DB;
require_once ('locallib.php');
$rubrics = $DB->get_records('grading_definitions', array('usercreated'=>$USER->id));
var_dump($rubrics);
foreach($rubrics as $rubric){

	echo '<div class="panel panel-default">';
	echo '<div class="panel-body">';
	echo '<h3>'.$rubric->name.'</h3>';
	echo '<a href="$createActivity" style="text-align: right;">';
	echo $OUTPUT->pix_icon('i/edit', 'Crear una actividad');
	echo ' Editar rúbrica</a>';
	echo $rubric->description;
	echo show_rubric($rubric->id);
	echo '</div>';
	echo '</div>';
}