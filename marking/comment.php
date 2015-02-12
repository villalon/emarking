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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_emarking
 * @copyright  Nicolas Perez (niperez@alumnos.uai.cl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot."/mod/emarking/locallib.php");
require_once($CFG->dirroot."/mod/emarking/lib.php");
require_once($CFG->dirroot."/mod/emarking/form.php");

global $USER, $OUTPUT, $DB, $CFG, $PAGE;

$cmid=required_param('id', PARAM_INT);
if(empty($cmid)){
	$cmid = $_POST["id"];
}

// $action var is needed to change the action wished to perfomr: list, create, edit, delete
$action=optional_param('action', 'list', PARAM_TEXT);

$deleteid=optional_param('commentid', 0, PARAM_INT);

// Validate course module
if(!$cm = get_coursemodule_from_id('emarking', $cmid)) {
	print_error ( get_string('invalidcoursemodule','mod_emarking' ) . " id: $cmid" );
}

// Validate eMarking activity
if(!$emarking = $DB->get_record('emarking', array('id'=>$cm->instance))) {
	print_error ( get_string('invalidid','mod_emarking' ) . " id: $cmid" );
}

// Validate course
if(!$course = $DB->get_record('course', array('id'=>$emarking->course))) {
	print_error(get_string('invalidcourseid', 'mod_emarking'));
}

// Emarking URL
$urlemarking = new moodle_url('/mod/emarking/marking/comment.php', array('id'=>$cm->id));
$context = context_module::instance($cm->id);

require_login($course->id);
if (isguestuser()) {
	die();
}

$PAGE->set_url($urlemarking);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('emarking','mod_emarking'));
$PAGE->navbar->add(get_string('comment', 'mod_emarking'));

echo $OUTPUT->header();
echo $OUTPUT->heading_with_help(get_string('emarking','mod_emarking'), 'annotatesubmission', 'mod_emarking');
//output of the tabtree
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "comment" );

//Form viewer
$view_form=1;

// action actions on create
if($action=="create"){
	echo $OUTPUT->heading(get_string('createcomment', 'mod_emarking'));
	
	//Creating form
	$newcommentform = new CommentForm();

	//Form Display
	$newcommentform->display();
}

if($action == "created"){
	//Recration of the form
	$newcommentform = new CommentForm();
	
	//Form result
	if ($newcommentform->is_cancelled()) {
		$action="list";
	}	elseif ($fromform = $newcommentform->get_data()) {
		$record = new stdClass();
		//Giving var record the necesary parameters
		$record->text = $fromform->comment["text"];
		$record->emarkingid = $emarking->id;
		$record->markerid = $USER->id;

		//Creating record in moodle DB
		$DB->insert_record('emarking_predefined_comment', $record);
		$action = "list";

	}
}

// action action on delete
if($action =="delete"){
	//geting record to delete
	$DB->delete_records('emarking_predefined_comment', array('id'=>$deleteid));
	$action = "list";
}
// action action on edit
if($action=="edit"){
	echo $OUTPUT->heading(get_string('editcomment', 'mod_emarking'));
	//getting record to edit
	$comment_edition=$DB->get_record('emarking_predefined_comment', array('id'=>$deleteid));

	//creating a var which contains the message to adit
	$comment_var = $comment_edition->text;

	//Creating new form and giving the var it needs to pass
	$newcommentform = new EditCommentForm(null,array('text'=>$comment_var, 'id'=>$cm->id, 'deleteid'=>$deleteid));

	//Dislay of the form
	$newcommentform->display();

	
}

if($action =="edited"){
	
	//Creating new form and giving the var it needs to pass
	$newcommentform = new EditCommentForm(null,array('text'=>"", 'id'=>$cm->id, 'deleteid'=>$deleteid));
	
	//condition of form cancelation
	if ($newcommentform->is_cancelled()) {
		$action= "list";
			
	} elseif($fromform = $newcommentform->get_data()) {
		$record = new stdClass();
		//geting previous data, so we can reuse it
		$comment_setup=$DB->get_record('emarking_predefined_comment', array('id'=>$fromform->delete));
	
		//setup of var record to update record in moodle DB
		$record->id = $comment_setup->id;
		$record->emarkingid = $comment_setup->emarkingid;
		$record->text = $fromform->comment['text'];
		$record->markerid = $USER->id;
	
		//updating the record
		$DB->update_record('emarking_predefined_comment', $record);
	
		$action = "list";
	}
}
//action actions on "list"
if($action=='list'){

	// Create Button url
	$urlcreate = new moodle_url('/mod/emarking/marking/comment.php', array('id'=>$cm->id, 'action'=>'create'));
	// Create new comment button
	echo $OUTPUT->single_button($urlcreate, get_string('createnewcomment', 'mod_emarking'));

	$predefinedcomments = $DB->get_records('emarking_predefined_comment', array('emarkingid'=> $emarking->id));
	
	//creating list
	$table = new html_table();
	$table->head = array(get_string('comment', 'mod_emarking'), get_string('creator', 'mod_emarking'),get_string('adjustments', 'mod_emarking'));
	foreach($predefinedcomments as $predefinedcomment){
		$deleteurl_comment = new moodle_url('', array('action'=>'delete', 'id'=>$cm->id, 'sesskey'=>sesskey(), 'commentid'=>$predefinedcomment->id));
		$deleteicon_comment = new pix_icon('t/delete', 'Delete');
		$deleteaction_comment = $OUTPUT->action_icon($deleteurl_comment, $deleteicon_comment,new confirm_action(get_string('questiondeletecomment', 'mod_emarking')));

		$editurl_comment = new moodle_url('', array('action'=>'edit', 'id'=>$cm->id, 'sesskey'=>sesskey(), 'commentid'=>$predefinedcomment->id));
		$editicon_comment = new pix_icon('i/edit', 'Edit');
		$editaction_comment = $OUTPUT->action_icon($editurl_comment, $editicon_comment, new confirm_action(get_string('questioneditcomment', 'mod_emarking')));

		$creator_name= $DB->get_record('user', array('id'=>$predefinedcomment->markerid));
		
		$table->data[] = array($predefinedcomment->text, $creator_name->username, $editaction_comment.$deleteaction_comment);

	}
	//Showing table
	echo html_writer::table($table);
	// Create new comment button
	echo $OUTPUT->single_button($urlcreate, get_string('createnewcomment', 'mod_emarking'));
}
echo $OUTPUT->footer();