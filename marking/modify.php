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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2015 Francisco García <frgarcia@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// Check that user is logued in the course.
require_login();
if (isguestuser()) {
    die();
}
// Criteria.
$criterionid = required_param('crid', PARAM_INT);
// Comment id.
$commentid = required_param('cid', PARAM_INT);
// Agreement level id.
$agreementlevelid = required_param('lid', PARAM_INT);
if (! $criterion = $DB->get_record('gradingform_rubric_criteria', array(
    "id" => $criterionid))) {
    print_error(get_string('invalidcriterionid', 'mod_emarking') . " id: $criterionid");
}
if (! $comment = $DB->get_record('emarking_comment', array(
    'id' => $commentid,
    'markerid' => $USER->id))) {
    print_error("Invalid comment id");
}
if (! $draft = $DB->get_record('emarking_draft', array(
    'id' => $comment->draft))) {
    print_error("Invalid comment id");
}
if (! $levels = $DB->get_records('gradingform_rubric_levels', array(
    'criterionid' => $criterionid))) {
    print_error("Invalid comment id");
}
$PAGE->requires->jquery();
$PAGE->set_url('/mod/emarking/marking/modify.php');
$PAGE->set_pagelayout('popup');
$PAGE->set_context($context);
echo $OUTPUT->header();
echo "<style>#page-mod-emarking-marking-modify #page-header {display:none;}</style>";
echo $OUTPUT->heading(
        get_string("updatemark", "mod_emarking") . ": $criterion->description " . get_string("exam", "mod_emarking") . " " .
                 $comment->draft);
$html = "<table style='margin-left: auto; margin-right: auto;'><tr>";
foreach ($levels as $level) {
    $class = $level->id == $agreementlevelid ? "agreement-modify-level-selected" : "agreement-modify-level";
    $checked = $level->id == $comment->levelid ? "checked" : "";
    $html .= '<td class="' . $class . '">' . $level->definition . '<br/>' . '<div><input type="radio" ' . $checked .
             ' name="score" idlevel=' . $level->id . '>' . floor($level->score) . ' puntos</div></td>';
}
$html .= '</tr></table><br/>';
echo $html;
$ajaxurl = $CFG->wwwroot .
         "/mod/emarking/ajax/a.php?action=updcomment&ids=$comment->draft&cid=$comment->id&comment=delphi&" +
         "posx=$comment->posx&posy=$comment->posy";
$savebutton = "<input type='submit' style='margin-left:auto;' value='Save' id='addregrade' url=" . $ajaxurl . ">";
$closebutton = "<input type='submit' style='margin-left:auto;' value='Cancel' id='cancel' onClick='window.close()'>";
echo $OUTPUT->box($savebutton . "&nbsp;&nbsp;&nbsp;" . $closebutton, null, null, array(
    "style" => "text-align:center;"));
echo $OUTPUT->footer();
?>
<script type="text/javascript">
$(document).ready(function(){
	$("#addregrade").click(function() {
		var radiobutton = $("input[name='score']:checked");
		var idlevel = radiobutton.attr("idlevel");
		var ajaxurl = $("#addregrade").attr("url") + "&levelid=" + idlevel;
		$.ajax({
    		type:"JSON",
        	url:ajaxurl,
        	data:{},
        	success:function(result) {
            	window.close();
         	},
            error:function(exception) {window.close();console.log(exception)}
		});
		$("#addregrade").prop('value', 'Saving');
		$("#addregrade").attr('disabled', 'disabled');
		$("#cancel").attr('disabled', 'disabled');
	})
});
window.onunload = function() {
    if (window.opener && !window.opener.closed) {
        window.opener.popUpClosed();
    }
}
</script>