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
require_once($CFG->dirroot."/mod/emarking/locallib.php");
require_once($CFG->dirroot."/mod/emarking/lib.php");
require_once($CFG->dirroot."/mod/emarking/marking/form.php");

global $USER, $OUTPUT, $DB, $CFG, $PAGE;

$cmid=required_param('cmid', PARAM_INT);

// $action var is needed to change the action wished to perfomr: list, create, edit, delete
$action=optional_param('action', 'list', PARAM_TEXT);

$commentid=optional_param('commentid', 0, PARAM_INT);

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
$urlemarking = new moodle_url('/mod/emarking/marking/predefinedcomments.php', array('cmid'=>$cm->id));
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
$PAGE->navbar->add(get_string('predefinedcomments', 'mod_emarking'));

echo $OUTPUT->header();
echo $OUTPUT->heading_with_help(get_string('emarking','mod_emarking'), 'annotatesubmission', 'mod_emarking');
//output of the tabtree
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "comment" );

// action action on delete
if($action =="delete"){
	//geting record to delete
	$DB->delete_records('emarking_predefined_comment', array('id'=>$commentid));
	
	echo $OUTPUT->notification(get_string('changessaved', 'mod_emarking'), 'notifysuccess');
	
	$action = "list";
}

// action action on edit
if($action=="edit") {
	//geting previous data, so we can reuse it
	if(!$editingcomment = $DB->get_record('emarking_predefined_comment', array('id'=>$commentid))) {
	    print_error(get_string('invalidid', 'mod_emarking'));
	}
	
    //Creating new form and giving the var it needs to pass
	$editcommentform = new EditCommentForm(null, array('text'=>$editingcomment->text, 'cmid'=>$cm->id, 'commentid'=>$commentid));
	
	//condition of form cancelation
	if ($editcommentform->is_cancelled()) {
		$action= "list";
			
	} elseif($fromform = $editcommentform->get_data()) {
	
		//setup of var record to update record in moodle DB
		$editingcomment->text = $fromform->comment['text'];
		$editingcomment->markerid = $USER->id;
	
		//updating the record
		$DB->update_record('emarking_predefined_comment', $editingcomment);
		
		echo $OUTPUT->notification(get_string('changessaved', 'mod_emarking'), 'notifysuccess');
		
		$action = "list";
	} else {
	    $editcommentform->display();
	}
}

//action actions on "list"
if($action=='list'){
    
    //Creating form
    $newcommentform = new CommentForm('predefinedcomments.php', array('cmid'=>$cmid));
    
    if($newcommentform->get_data()) {
        $newcomment = new stdClass();
        //Giving var record the necesary parameters
        $newcomment->text = $newcommentform->get_data()->comment['text'];
        $newcomment->emarkingid = $emarking->id;
        $newcomment->markerid = $USER->id;
    
        //Creating record in moodle DB
        $DB->insert_record('emarking_predefined_comment', $newcomment);
        
        echo $OUTPUT->notification(get_string('changessaved', 'mod_emarking'), 'notifysuccess');
    }
    
	// Create Button url
	$urlcreate = new moodle_url('/mod/emarking/marking/predefinedcomments.php', array('cmid'=>$cm->id, 'action'=>'create'));

	$predefinedcomments = $DB->get_records('emarking_predefined_comment', array('emarkingid'=> $emarking->id));
	
	//creating list
	$table = new html_table();
	$table->head = array(get_string('comment', 'mod_emarking'), get_string('creator', 'mod_emarking'),get_string('actions', 'mod_emarking'));
	foreach($predefinedcomments as $predefinedcomment){
		$deleteurl_comment = new moodle_url('', array('action'=>'delete', 'cmid'=>$cm->id, 'commentid'=>$predefinedcomment->id));
		$deleteicon_comment = new pix_icon('t/delete', get_string('delete'));
		$deleteaction_comment = $OUTPUT->action_icon($deleteurl_comment, $deleteicon_comment,new confirm_action(get_string('questiondeletecomment', 'mod_emarking')));

		$editurl_comment = new moodle_url('', array('action'=>'edit', 'cmid'=>$cm->id, 'commentid'=>$predefinedcomment->id));
		$editicon_comment = new pix_icon('i/edit', get_string('edit'));
		$editaction_comment = $OUTPUT->action_icon($editurl_comment, $editicon_comment);

		$creator_name= $DB->get_record('user', array('id'=>$predefinedcomment->markerid));
		
		$table->data[] = array($predefinedcomment->text, $creator_name->username, $editaction_comment.$deleteaction_comment);
	}
	//Showing table
	echo html_writer::table($table);

    //Form Display
    $newcommentform->display();
}


echo $OUTPUT->footer();