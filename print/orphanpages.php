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
 * @copyright 2016-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
global $PAGE, $DB, $CFG, $OUTPUT;
require_once ($CFG->dirroot . "/repository/lib.php");
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
// Obtains basic data from cm id.
list ($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$action = optional_param('action', 'view', PARAM_ALPHA);
$page = optional_param('page', 0, PARAM_INT);
// Get the course module for the emarking, to build the emarking url.
$url = new moodle_url('/mod/emarking/print/orphanpages.php', array(
    'id' => $cm->id,
    'page' => $page
));
// Check that user is logged in and is not guest.
require_login($course->id);
if (isguestuser()) {
    die();
}
if (!$exam = $DB->get_record('emarking_exams', array(
    'emarking' => $emarking->id
))) {
    print_error('Invalid emarking activity. No exam found.');
}
$usercanupload = has_capability('mod/emarking:uploadexam', $context);
$perpage = 50;
// Set navigation parameters.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
if (isset($CFG->emarking_pagelayouttype)) {
switch($CFG->emarking_pagelayouttype){
	case EMARKING_PAGES_LAYOUT_STANDARD:
		$PAGE->set_pagelayout('standard');
		break;
		
	case EMARKING_PAGES_LAYOUT_EMBEDDED:
		$PAGE->set_pagelayout('embedded');
		break;
}
}
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('orphanpages', 'mod_emarking'));
// Require jquery for modal.
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
// Save uploaded file in Moodle filesystem and check.
$fs = get_file_storage();
if ($action === 'delete') {
    require_capability('mod/emarking:uploadexam', $context);
    $d = $_POST['d'];
    if (!is_array($d)) {
        print_error('Invalid parameters');
    }
    foreach($d as $fileidtodelete) {
        if (!is_number($fileidtodelete)) {
            continue;
        }
        $filetodelete = $fs->get_file_by_id($fileidtodelete);
        // Calculate anonymous file name from original file name.
        $anonymousfilename = emarking_get_anonymous_filename($filetodelete->get_filename());
        $filetodelete->delete();
        $anonymousfile = $fs->get_file($context->id, 'mod_emarking', 'orphanpages', $emarking->id, '/', $anonymousfilename);
        if ($anonymousfile) {
            $anonymousfile->delete();
        }
    }
    if (isset($CFG->emarking_pagelayouttype)&& $CFG->emarking_pagelayouttype== EMARKING_PAGES_LAYOUT_EMBEDDED) {
    	$url = new moodle_url('/mod/emarking/activities/marking.php', array(
    			'id' => $cm->id,
    			'tab'=>6
    	));
    }
    redirect($url, get_string('transactionsuccessfull', 'mod_emarking'), 3);
    die();
}
if ($action === 'rotate') {
    require_capability('mod/emarking:uploadexam', $context);
    $fileidtorotate = required_param('file', PARAM_INT);
    $newpath = emarking_rotate_image_file($fileidtorotate);
    if (isset($CFG->emarking_pagelayouttype)&& $CFG->emarking_pagelayouttype== EMARKING_PAGES_LAYOUT_EMBEDDED) {
    	$url = new moodle_url('/mod/emarking/activities/marking.php', array(
    			'id' => $cm->id,
    			'tab'=>6
    	));
    }
    redirect($url, get_string('transactionsuccessfull', 'mod_emarking'), 3);
    die();
}
// Display form for uploading zip file.
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
if($CFG->emarking_pagelayouttype == EMARKING_PAGES_LAYOUT_STANDARD){
    $tabname = "orphanpages";
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), $tabname);
}?>
<style>
<!--
.fixorphanpage {
	display: none;
	margin-top: 10px;
	position: absolute;
	background-color: #fafafa;
	padding: 5px;
	border: 1px solid #bbb;
	color: black !important;
	border-radius: 3px;
	box-shadow: 2px 2px 2px 2px grey;
}

.fixorphanpage button {
	float: right;
}
-->
</style>
<?php
// Show orphan pages button
$orphanpages = emarking_get_digitized_answer_orphan_pages($context);
$numorphanpages = count($orphanpages);
if ($numorphanpages == 0) {
    echo $OUTPUT->notification(get_string('noorphanpages', 'mod_emarking'), 'notifymessage');
} else {
    echo $OUTPUT->paging_bar($numorphanpages, $page, $perpage, $url);
    if ($usercanupload) {
        echo "<form method='post' id='orphanpages'>
    <input type='hidden' name='id' value='$cm->id'>
    <input type='hidden' name='page' value='$page'>
    <input type='hidden' name='delete' value='true'>
    <input type='hidden' name='action' value='delete'>
    ";
    }
    $options = array();
    $options[0] = get_string('choose');
    $totalpages = ($exam->totalpages + 5) * (1 + $exam->usebackside);
    for($i = 1; $i <= $totalpages; $i++) {
        $options[$i] = $i;
    }
    $table = new html_table();
    $table->attributes['style'] = 'display:table;';
    $table->head = array(
        get_string('filename', 'repository'),
        get_string('actions', 'mod_emarking')
    );
    if ($usercanupload) {
        $table->head[] = "<input type='checkbox' id='select_all' title=\"" . get_string('selectall', 'mod_emarking') . "\">";
    }
    $shown = 0;
    foreach($orphanpages as $file) {
        $shown++;
        if (floor($shown / $perpage) != ($page)) {
            continue;
        }
        $actions = array();
        $deleteurl = new moodle_url('/mod/emarking/print/orphanpages.php', array(
            'id' => $cm->id,
            'delete' => $file->get_id()
        ));
        $rotateurl = new moodle_url('/mod/emarking/print/orphanpages.php', array(
            'id' => $cm->id,
            'file' => $file->get_id(),
            'rotate' => true
        ));
        if ($usercanupload) {
            $actions[] = $OUTPUT->action_icon($rotateurl, new pix_icon('i/return', get_string('rotatepage', 'mod_emarking')));
            $actions[] = $OUTPUT->pix_icon('i/edit', get_string('rotatepage', 'mod_emarking'), '', array(
                'style' => 'cursor:pointer;',
                'onclick' => 'showfixform(' . $file->get_id() . ')'
            ));
        }
        if (isset($file->anonymous)) {
            $actions[] = $OUTPUT->action_icon(moodle_url::make_pluginfile_url($context->id, 'mod_emarking', 'orphanpages', $emarking->id, '/', $file->anonymous->get_filename()), new pix_icon('i/show', get_string('anonymousfile', 'mod_emarking')));
        }
        $actions[] = html_writer::div(html_writer::div(get_string('student', 'grades'), NULL, array(
            'id' => 'error-student-' . $file->get_id()
        )) . html_writer::tag('input', NULL, array(
            'name' => 'student-' . $file->get_id(),
            'type' => 'text',
            'class' => 'studentname',
            'tabindex' => ($shown * 2),
            'fileid' => $file->get_id()
        )) . '<br/>' . html_writer::div(get_string('page', 'mod_emarking'), NULL, array(
            'id' => 'error-pagenumber-' . $file->get_id()
        )) . html_writer::select($options, 'page-' . $file->get_id(), '', false, array(
            'tabindex' => ($shown * 2 + 1),
            'id' => 'page-' . $file->get_id()
        )) . '<br/>' . html_writer::tag('button', get_string('cancel'), array(
            'class' => 'btn',
            'onclick' => 'return cancelchanges(' . $file->get_id() . ');'
        )) . html_writer::tag('button', get_string('submit'), array(
            'class' => 'btn',
            'onclick' => 'return savechanges(' . $file->get_id() . ');'
        )) . html_writer::tag('input', NULL, array(
            'type' => 'hidden',
            'name' => 'studentid-' . $file->get_id(),
            'id' => 's' . $file->get_id()
        )), 'fixorphanpage', array(
            'id' => 'fix-' . $file->get_id()
        )) . html_writer::div('', '', array(
            'id' => 'content-' . $file->get_id()
        ));
        $imgurl = moodle_url::make_pluginfile_url($context->id, 'mod_emarking', 'orphanpages', $emarking->id, '/', $file->get_filename());
        $imgurl .= '?r=' . random_string();
        $data = array(
            $OUTPUT->action_link($imgurl, html_writer::div(html_writer::img($imgurl, $file->get_filename(), array('width'=>'600px')), '', array(
                'style' => 'height:100px; overflow:scroll; max-width:620px;'
            ))),
            implode(' ', $actions)
        );
        if ($usercanupload) {
            $data[] = html_writer::checkbox('d[]', $file->get_id(), false, '');
        }
        $table->data[] = $data;
    }
    echo html_writer::table($table);
    echo $OUTPUT->paging_bar($numorphanpages, $page, $perpage, $url);
    if ($usercanupload) {
        echo html_writer::start_tag('input', array(
            'type' => 'submit',
            'value' => get_string('deleteselectedpages', 'mod_emarking'),
            'style' => 'float:right;'
        ));
        echo "</form>";
    }
}
$students = get_enrolled_users($context, 'mod/emarking:submit');
?>
<script type="text/javascript">
// Course module id.
var cmid = <?php echo $cm->id ?>;
// List of enroled students for autocomplete.
var students = [
            	<?php
            foreach($students as $student) {
                echo "{ value:$student->id,label:'$student->lastname $student->firstname'},";
            }
            ?>
            	];
// Use the top check box to select/deselect all checkboxes in the table.
$('#select_all').change(function() {
    var checkboxes = $('#orphanpages').find(':checkbox');
    if($(this).is(':checked')) {
        checkboxes.prop('checked', true);
        $('#select_all').prop('title','<?php echo get_string('selectnone', 'mod_emarking') ?>');
    } else {
        checkboxes.prop('checked', false);
        $('#select_all').prop('title','<?php echo get_string('selectall', 'mod_emarking') ?>');
	}
});
// Autocomplete setup.
$('.studentname').autocomplete({
	source: students,
	focus: function(event, ui) {
		// prevent autocomplete from updating the textbox
		event.preventDefault();
		// manually update the textbox
		$(this).val(ui.item.label);
	},
	select: function(event, ui) {
		// prevent autocomplete from updating the textbox
		event.preventDefault();
		// manually update the textbox and hidden field
		$(this).val(ui.item.label);
		var fileid = $(this).attr('fileid');
		$("#s"+fileid).val(ui.item.value);
	}	
});
// Saves the changes made to a specific file in the table.
function savechanges(fileid) {
	// Loads the libraries required (Ajax, Moodle config and strings).
	require(['core/ajax','core/config','core/str'], function(ajax, mdlcfg, str) {
		var studentid = parseInt($("#s"+fileid).val());
		console.log(studentid);
		console.log(fileid);
		var pagenumber = parseInt($("#page-"+fileid).val());
		var invalididmsg = '';
		str.get_string('invalidid','mod_emarking').done(function(s) {
			invalididmsg = s;
		});
		// Validates student id.
		if(studentid < 1) {
			$('#error-student-'+fileid).text('Invalid student');
			console.log(invalididmsg);
			return false;
		}
		// Validates page number.
		if(pagenumber < 1) {
			console.log('Invalid page number');
			return false;
		}
		// Hides the form.
		$('#fix-'+fileid).hide();
		// Shows a loading icon or saving message.
		$('#content-'+fileid).text('Saving');
		// Uses ajax to fix the page.
		var ajaxrequest = ajax.call([
			{
				methodname: 'mod_emarking_fix_page',
				args: {
					pages : [
								{
									cmid: cmid,
									fileid: fileid,
									studentid: studentid,
									pagenumber: pagenumber
								}
							]
					  }
			}]);
		ajaxrequest[0].done(function(response) {
	    	$('#content-'+fileid).text('Transaction successful');
			var tr = $('#content-'+fileid).closest('tr');
			tr.css('background-color','#009688');
			tr.fadeOut(800, function() {
				tr.remove();
			});
	    }).fail(function(ex) {
	    	$('#content-'+fileid).text('ERROR! Please try again later.');
	    	if(mdlcfg.developerdebug) {
	    		console.log(ex);
			}
	    });
	});
	return false;
}
// If the user presses the cancel button.
function cancelchanges(fileid) {
	$('#fix-'+fileid).hide();
	return false;
}
// If the user presses the fix icon we show the form to fix a page.
function showfixform(fileid) {
	$('#fix-'+fileid).show();
	return false;
}
</script>
<?php
echo $OUTPUT->footer();