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
 * @copyright 2012 Jorge Villalon <villalon@gmail.com>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
global $USER, $DB, $CFG;
require_once ($CFG->dirroot . "/repository/lib.php");
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
// Obtains basic data from cm id.
list ($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$delete = optional_param('delete', false, PARAM_BOOL);
$rotate = optional_param('rotate', false, PARAM_BOOL);
$page = optional_param('page', 0, PARAM_INT);
// Get the course module for the emarking, to build the emarking url.
$url = new moodle_url('/mod/emarking/print/orphanpages.php', array(
    'id' => $cm->id,
    'page' => $page
));
$urlemarking = new moodle_url('/mod/emarking/view.php', array(
    'id' => $cm->id
));
// Check that user is logged in and is not guest.
require_login($course->id);
if (isguestuser()) {
    die();
}
if(!$exam = $DB->get_record('emarking_exams', array('emarking'=>$emarking->id))) {
    print_error('Invalid emarking activity. No exam found.');
}
$usercanupload = has_capability('mod/emarking:uploadexam', $context);
$perpage = 50;
// Set navigation parameters.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('orphanpages', 'mod_emarking'));
// Require jquery for modal.
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
// Save uploaded file in Moodle filesystem and check.
$fs = get_file_storage();
if ($delete) {
    require_capability('mod/emarking:uploadexam', $context);
    $d = $_POST['d'];
    if (!is_array($d)) {
        print_error('Invalid parameters');
    }
    foreach($d as $fileidtodelete) {
        if(!is_number($fileidtodelete)) {
            continue;
        }
        $filetodelete = $fs->get_file_by_id($fileidtodelete);
        // Calculate anonymous file name from original file name.
        $filenameparts = explode(".", $filetodelete->get_filename());
        $anonymousfilename = $filenameparts[0] . "_a." . $filenameparts[1];
        $filetodelete->delete();
        if ($fs->file_exists($context->id, 'mod_emarking', 'orphanpages', $emarking->id, '/', $anonymousfilename)) {
            $anonymousfile = $fs->get_file($context->id, 'mod_emarking', 'orphanpages', $emarking->id, '/', $anonymousfilename);
            $anonymousfile->delete();
        }
    }
    redirect($url, get_string('transactionsuccessfull', 'mod_emarking'), 3);
    die();
}
if($rotate) {
    require_capability('mod/emarking:uploadexam', $context);
    $fileidtorotate = required_param('file', PARAM_INT);
    $newpath = emarking_rotate_image_file($fileidtorotate);
    redirect($url, get_string('transactionsuccessfull', 'mod_emarking'), 3);
    die();
}
// Display form for uploading zip file.
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), 'orphanpages');
?>
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
    if($usercanupload) {
    echo "<form method='post' id='orphanpages'>
    <input type='hidden' name='id' value='$cm->id'>
    <input type='hidden' name='page' value='$page'>
    <input type='hidden' name='delete' value='true'>
    ";
    }
    $options = array();
    $options[0] = get_string('choose');
    $totalpages = $exam->totalpages * ( 1 + $exam->usebackside);
    for($i=1;$i<=$totalpages;$i++) {
        $options[$i] = $i;
    }
    $table = new html_table();
    $table->attributes['style'] = 'display:table;';
    $table->head = array(
        get_string('filename', 'repository'),
        get_string('actions', 'mod_emarking')
    );
    if($usercanupload) {
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
            'id'=>$cm->id,
            'file'=>$file->get_id(),
            'rotate'=>true
        ));
        if($usercanupload) {
            $actions[] = $OUTPUT->action_icon($rotateurl, new pix_icon('i/return', get_string('rotatepage', 'mod_emarking')));
            $actions[] = $OUTPUT->pix_icon('i/edit', get_string('rotatepage', 'mod_emarking'), '',
                array('style'=>'cursor:pointer;', 'onclick'=>'fixorphanshow('.$file->get_id().')'));
        }
        if(isset($file->anonymous)) {
            $actions[] = $OUTPUT->action_icon(moodle_url::make_pluginfile_url($context->id, 'mod_emarking', 'orphanpages', $emarking->id, '/', $file->anonymous->get_filename()),
                new pix_icon('i/show', get_string('anonymousfile', 'mod_emarking')));
        }
        $actions[] = html_writer::div(
                get_string('student', 'grades')
                . '<br/>'
                . html_writer::tag('input', NULL, array('name'=>'student-'.$file->get_id(), 'type'=>'text', 'class'=>'studentname', 'tabindex'=>($shown * 2)))
                . '<br/>'
                . get_string('page', 'mod_emarking')
                . '<br/>'
                . html_writer::select($options, 'page-'.$file->get_id(), '', false, array('tabindex'=>($shown * 2 + 1)))
                . '<br/>'
                . html_writer::tag('button', get_string('cancel'), array('class'=>'btn', 'onclick'=>'return fixorphancancel('.$file->get_id().');'))                    
                . html_writer::tag('button', get_string('submit'), array('class'=>'btn', 'onclick'=>'return fixorphanhide('.$file->get_id().');'))
            ,
                'fixorphanpage',
                array('id'=>'fix-'.$file->get_id()))
        . html_writer::div('','',array('id'=>'content-'.$file->get_id()));
        $imgurl = moodle_url::make_pluginfile_url($context->id, 'mod_emarking', 'orphanpages', $emarking->id, '/', $file->get_filename());
        $imgurl .= '?r=' . random_string();
        $data = array(
            $OUTPUT->action_link($imgurl, html_writer::div(html_writer::img($imgurl, $file->get_filename()), '', array('style'=>'height:100px; overflow:hidden; max-width:600px;'))),
            implode(' ', $actions),
        );
        if($usercanupload) {
            $data[] = html_writer::checkbox('d[]', $file->get_id(), false, '');
        }
        $table->data[] = $data;
    }
    echo html_writer::table($table);
    echo $OUTPUT->paging_bar($numorphanpages, $page, $perpage, $url);
    if($usercanupload) {
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
var students = [
            	<?php
            	foreach($students as $student) {
            	    echo "'$student->lastname $student->firstname',";
            	}
            	?>
            	];
$('.studentname').each(function(index){
	console.log('an input');
	console.log(students);
});
$('.studentname').autocomplete({
	source: students
});
require(['core/ajax'], function(ajax) {
	var call = ajax.call([
	                      { methodname: 'core_get_string', args: { component: 'mod_wiki', stringid: 'pluginname' }}]);
    call.done(function(response) {
        console.log('yay' + response);
    }).fail(function(ex) {
        console.log(ex);
    });
});
function fixorphanhide(fileid) {
	$('#fix-'+fileid).hide();
	$('#content-'+fileid).text('Saving');
	$.
	return false;
}
function fixorphancancel(fileid) {
	$('#fix-'+fileid).hide();
	return false;
}
function fixorphanshow(fileid) {
	$('#fix-'+fileid).show();
	return false;
}
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
</script>
<?php
echo $OUTPUT->footer();