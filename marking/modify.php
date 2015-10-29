<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2015 Francisco García <frgarcia@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

// Check that user is logued in the course
require_login();
if (isguestuser()) {
	die();
}

// Course module id
$cmid = required_param('id', PARAM_INT);
// Criteria
$criterionid = required_param('criterionid', PARAM_INT);
// Comment id
$commentid = required_param('commentid', PARAM_INT);

// Validate course module
if (! $cm = get_coursemodule_from_id('emarking', $cmid)) {
	print_error(get_string('invalidcoursemodule', 'mod_emarking') . " id: $cmid");
}

$context = context_module::instance($cm->id);

//$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery();
$PAGE->set_url('/mod/emarking/marking/modify.php');
$PAGE->set_pagelayout('base');
$PAGE->set_context(context_system::instance());

$comment = $DB->get_record('emarking_comment',array('id'=>$commentid));
$levels = $DB->get_records('gradingform_rubric_levels',array('criterionid'=>$criterionid));

echo $OUTPUT->header();
echo "<h4>Modificando Criterio: 2</h4>";
echo "Seleccione la nueva ";
echo "<br>";

foreach($levels as $level){
	
	if($level->id == $comment->levelid){
		echo '<div style="position: relative;float:left;width:150px;height:150px;border:1px solid #000;background-color:#FF7878;border-color: #48D063">'
				.$level->definition.'<div style="position: absolute;bottom: 0;">
				<input type="radio" idlevel="'.$level->id.'" checked name="score" draftid="'.$comment->draft.'" commentid="'.$comment->id.'">'
				.floor($level->score).'puntos<br></div></div>';	
	}
	else{
		echo '<div style="position: relative;float:left;width:150px;height:150px;border:1px solid #000;background-color:#ffffff;border-color: #48D063">'
				.$level->definition.'<div style="position: absolute;bottom: 0;">
				<input type="radio" idlevel="'.$level->id.'" name="score" draftid="'.$comment->draft.'" commentid="'.$comment->id.'">'
				.floor($level->score).'puntos<br></div></div>';	
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
/*
 Data para ajax
 ids 	: draftid 			- $draft->id
 action : updcommnet		- "updcomment"
 cid 	: commentid 		- $comment->id
 comment: textcommnet 		- "delphi"
 levelid: levelid			- $level->id
 format	: 2, rubric mark	- "2"
 */

$ajaxurl = $CFG->wwwroot . "/mod/emarking/ajax/a.php";
$savebutton = "<input type='submit' value='Save' id='addregrade' url=".$ajaxurl.">";
$closebutton = "<input type='submit' value='Close Window'' onClick='window.close()'>";
echo $savebutton;
echo $closebutton;

echo $OUTPUT->footer();

?>
<script type="text/javascript" >

$(document).ready(function(){
	$("#addregrade").click(function() {
		var radiobutton = $("input[name='score']:checked");
		var updcomment = "updcomment";
		var draftid = radiobutton.attr("draftid");
		var commentid = radiobutton.attr("commentid");
		var delphi = "delphi";
		var idlevel = radiobutton.attr("idlevel");
		var ajaxurl = $("#addregrade").attr("url");
		//alert("draft id "+draftid + " commentid " + commentid + " idlevel "+ idlevel);
		$.ajax({
    		type: "GET",
        	url: ajaxurl,
        	data: {action : updcomment, ids : draftid, cid : commentid , comment : delphi, levelid : idlevel , format : 2},
        	success: function(data) {
         	}
		});
		location.reload();
	})
});
window.onunload = function() {
    if (window.opener && !window.opener.closed) {
        window.opener.popUpClosed();
    }
}
</script>