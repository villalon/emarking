<?php
use core\session\exception;

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
 * @copyright 2017 Hans Jeria <hansjeria@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
global $USER, $DB, $CFG;
require_once ($CFG->dirroot . "/mod/emarking/forms/upload_form.php");
require_once ($CFG->dirroot . "/repository/lib.php");
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
// Obtains basic data from cm id.
list ($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// Get the course module for the emarking, to build the emarking url.
$url = new moodle_url('/mod/emarking/print/uploadanswers.php', array(
    'id' => $cm->id
));
$urlemarking = new moodle_url('/mod/emarking/view.php', array(
    'id' => $cm->id
));
// Check that user is logged in and is not guest.
require_login($course->id);
if (isguestuser()) {
    die();
}
require_capability('mod/emarking:uploadexam', $context);
$action = optional_param('action', 'view', PARAM_ALPHA);
$digitizedanswerid = optional_param('did', 0, PARAM_INT);
$usercanmanageanswersfiles = has_capability('mod/emarking:uploadexam', $context) || is_siteadmin();
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
$PAGE->navbar->add(get_string('uploadanswers', 'mod_emarking'));
$mform = new mod_emarking_upload_form(null, array(
    'coursemoduleid' => $cm->id,
    'emarkingid' => $emarking->id
));
// If the user cancelled the form, redirect to activity.
if ($mform->is_cancelled()) {
    redirect($urlemarking);
    die();
}
if ($mform->get_data()) {
    // Save uploaded file in Moodle filesystem and check.
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_emarking', 'tmpupload');
    $itemid = $emarking->id;
    $filemimetypes = array(
        'dummy',
        'application/pdf',
        'application/zip'
    );
    $file = $mform->save_stored_file('assignment_file', $context->id, 'mod_emarking', 'tmpupload', $itemid, '/', emarking_clean_filename($mform->get_new_filename('assignment_file')));
    // Validate that file was correctly uploaded.
    if (!$file) {
        print_error('Could not upload file');
    }
    // Check that the file is a zip.
    if (!array_search($file->get_mimetype(), $filemimetypes)) {
        $fs->delete_area_files($context->id, 'mod_emarking', 'tmpupload');
        print_error(get_string('fileisnotzip', 'mod_emarking'));
    } else {
        $transaction = $DB->start_delegated_transaction();
        // Insert the record that associates a digitized file with a set of answers.
        $digitizedanswer = new stdClass();
        $digitizedanswer->file = $file->get_id();
        $digitizedanswer->emarking = $emarking->id;
        $digitizedanswer->status = EMARKING_DIGITIZED_ANSWER_UPLOADED;
        $digitizedanswer->totalpages = 0;
        $digitizedanswer->doubleside = isset($mform->get_data()->doubleside) ? 1 : 0;
        $digitizedanswer->ignorecourse = isset($mform->get_data()->ignorecourse) ? 1 : 0;
        $digitizedanswer->identifiedpages = 0;
        $digitizedanswer->timecreated = time();
        $digitizedanswer->timemodified = time();
        $digitizedanswer->id = $DB->insert_record('emarking_digitized_answers', $digitizedanswer);
        $filerecord = array(
            'contextid' => $context->id,
            'component' => 'mod_emarking',
            'filearea' => 'upload',
            'itemid' => $digitizedanswer->id,
            'filepath' => '/',
            'filename' => $file->get_filename(),
            'timecreated' => time(),
            'timemodified' => time()
        );
        $newfile = $fs->create_file_from_storedfile($filerecord, $file);
        $file->delete();
        if (!$newfile) {
            $e = new exception('Failed to create file in moodle filesystem');
            $DB->rollback_delegated_transaction($transaction, $e);
        } else {
            $digitizedanswer->file = $newfile->get_id();
            $DB->update_record('emarking_digitized_answers', $digitizedanswer);
            $DB->commit_delegated_transaction($transaction);
        }
        // Display confirmation page before moving to process.
        if (isset($CFG->emarking_pagelayouttype)&& $CFG->emarking_pagelayouttype== EMARKING_PAGES_LAYOUT_EMBEDDED) {
        	$url = new moodle_url('/mod/emarking/activities/marking.php', array(
        			'id' => $cm->id,
        			'tab'=>3
        	));
        	redirect($url, '', 0);
        	die();
        }
        redirect($url, get_string('uploadanswersuccessful', 'mod_emarking'), 3);
        die();
    }
}
$deletedsuccessfull = false;
if($action === 'delete') {
    if(!$DB->record_exists('emarking_digitized_answers', array('id'=>$digitizedanswerid))) {
        print_error('Invalid id for digitized answer to be deleted');
    }
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_emarking', 'upload', $digitizedanswerid);
    $DB->delete_records('emarking_digitized_answers', array('id'=>$digitizedanswerid));
    // Display confirmation page before moving to process.
    if (isset($CFG->emarking_pagelayouttype)&& $CFG->emarking_pagelayouttype== EMARKING_PAGES_LAYOUT_EMBEDDED) {
    	$url = new moodle_url('/mod/emarking/activities/marking.php', array(
    			'id' => $cm->id,
    			'tab'=>3
    	));
    	redirect($url, '', 0);
    	die();
    }
    redirect($url, get_string('transactionsuccessfull', 'mod_emarking'), 3);
    die();
} elseif($action === 'process') {
    if(! $digitizedanswer = $DB->get_record('emarking_digitized_answers',
            array('id'=>$digitizedanswerid))) {
        print_error('Invalid id for digitized answer to process');
    }
    $digitizedanswer->status = EMARKING_DIGITIZED_ANSWER_UPLOADED;
    $DB->update_record('emarking_digitized_answers', $digitizedanswer);
    // Display confirmation page before moving to process.
    if (isset($CFG->emarking_pagelayouttype)&& $CFG->emarking_pagelayouttype== EMARKING_PAGES_LAYOUT_EMBEDDED) {
    	$url = new moodle_url('/mod/emarking/activities/marking.php', array(
    			'id' => $cm->id,
    			'tab'=>3
    	));
    	redirect($url, '', 0);
    	die();
    }
    redirect($url, get_string('transactionsuccessfull', 'mod_emarking'), 3);
    die();
}
// Display form for uploading zip file.
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
if($CFG->emarking_pagelayouttype == EMARKING_PAGES_LAYOUT_STANDARD){
	echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), 'uploadanswers');
}
$totalbeingprocessed = 0;
$digitizedanswersfiles = emarking_get_digitized_answer_files($emarking);
if (count($digitizedanswersfiles) == 0) {
    echo $OUTPUT->notification(get_string('nodigitizedanswerfiles', 'mod_emarking'), 'notifymessage');
} else {
    $table = new html_table();
    $table->attributes['style'] = 'display:table;';
    $table->head = array(
        get_string('filename', 'repository'),
        get_string('size'),
        'Mime type',
        get_string('uploaded', 'hub'),
        get_string('status', 'mod_emarking'),
        core_text::strtotitle(get_string('pages', 'mod_emarking')),
        get_string('actions', 'mod_emarking')
    );
    foreach($digitizedanswersfiles as $file) {
        $actions = array();
        $deleteurl = new moodle_url('/mod/emarking/print/uploadanswers.php',
            array('id'=>$cm->id, 'action' => 'delete', 'did'=>$file->id));
        $processurl = new moodle_url('/mod/emarking/print/uploadanswers.php',
            array('id'=>$cm->id, 'action' => 'process', 'did'=>$file->id));
        if(has_capability('mod/emarking:uploadexam', $context)) {
            $downloadurl = moodle_url::make_pluginfile_url($context->id, 'mod_emarking', 'upload', $file->itemid, '/', $file->filename, true);
            $actions[] = $OUTPUT->action_icon(
                $downloadurl, new pix_icon(
                    'i/down',
                    'download',
                    null,
                    array(
                        'style' => 'width:1.5em;'
                )));
        }
        if (($file->status == EMARKING_DIGITIZED_ANSWER_ERROR_PROCESSING || $file->status <= EMARKING_DIGITIZED_ANSWER_UPLOADED)
            && $usercanmanageanswersfiles) {
            $actions[] = $OUTPUT->action_icon($deleteurl, new pix_icon('i/delete', 'delete', null, array(
                'style' => 'width:1.5em;'
            )));
        }
        if (($file->status != EMARKING_DIGITIZED_ANSWER_BEING_PROCESSED
            && $usercanmanageanswersfiles)
            || is_siteadmin()) {
            $actions[] = $OUTPUT->action_icon($processurl, new pix_icon('i/reload', 'reload', null, array(
                'style' => 'width:1.5em;'
            )));
            $totalbeingprocessed++;
        }
        $mimetype = $file->mimetype;
        $table->data[] = array(
            $file->filename,
            display_size($file->filesize)
                . ($file->doubleside ? '&nbsp;' . $OUTPUT->pix_icon('t/copy', get_string('doubleside', 'mod_emarking')) : '')
                . ($file->ignorecourse ? '&nbsp;' . $OUTPUT->pix_icon('t/block', get_string('ignorecourse', 'mod_emarking')) : ''),
            $mimetype,
            emarking_time_ago($file->timecreated),
            emarking_get_string_for_status_digitized($file->status),
            $file->totalpages . '/' . $file->identifiedpages,
            implode(' ', $actions)
        );
    }
    echo html_writer::table($table);
}
// Show orphan pages button
$orphanpages = emarking_get_digitized_answer_orphan_pages($context);
emarking_show_orphan_pages_link($context, $cm);
if(has_capability('mod/emarking:uploadexam', $context)) {
    $mform->display();
}
if($totalbeingprocessed > 0) {
	echo '<script>
setTimeout(function(){
	window.location.reload(1);
}, 5000);
		</script>';
}
echo $OUTPUT->footer();