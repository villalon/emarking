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
 * This page shows a list of exams sent for printing. It can
 * be reached from a block within a category or from an eMarking
 * course module
 * 
 * @package mod
 * @subpackage emarking
 * @copyright 2012-2015 Jorge Villalon <jorge.villalon@uai.cl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__))))."/config.php");
require_once($CFG->dirroot."/mod/emarking/locallib.php");
require_once($CFG->dirroot."/mod/emarking/print/locallib.php");

global $DB, $USER, $CFG;

// Course module id if the user comes from an eMarking module
$cmid = optional_param("id", 0, PARAM_INT);
// Course id, if the user comes from a course
$courseid = optional_param("course", 0, PARAM_INT);
// Exam id in case an exam was just created
$examid = optional_param("examid", 0, PARAM_INT);
// If the user is downloading a print form
$downloadform = optional_param("downloadform", false, PARAM_BOOL);

// First check that the user is logged in
require_login();
if (isguestuser()) {
	die();
} 

// If the user comes from a course module, we get the course id from it
if($cmid > 0) {
    // Get the course module
	if(!$cm = get_coursemodule_from_id("emarking", $cmid)) {
		print_error(get_string("invalidid", "mod_emarking"));
	}

	// Get the emarking object
	if(!$emarking = $DB->get_record("emarking", array("id"=>$cm->instance))) {
		print_error(get_string("invalidid", "mod_emarking"));
	}

	$courseid = $cm->course;
}

// Validate that the parameter corresponds to a course
if(!$course = $DB->get_record("course", array("id"=>$courseid))) {
	print_error(get_string("invalidcourseid", "mod_emarking"));
}

// Both contexts, from course and category, for permissions later
$contextcourse = context_course::instance($course->id);
$contextcat = context_coursecat::instance($course->category);

// The context for the page is the course or the module
$context = $cmid > 0 ? context_module::instance($cm->id) : $context = $contextcourse;

// An exam id means either a new exam was sent, or a a download form
// was requested
if($examid) {
    $newexam = $DB->get_record("emarking_exams", array("id"=>$examid));
}

// If a download form was requested
if($examid && $downloadform) {
	$coursecat = $DB->get_record("course_categories", array("id"=>$course->category));
	$requestedbyuser = $DB->get_record("user", array("id"=>$newexam->requestedby));

	emarking_create_printform($context,
		$newexam,
		$USER,
		$requestedbyuser,
		$coursecat,
		$course);
	die();
}

// Ony users that can grade can see exams
require_capability ( "mod/emarking:grade", $context );

// URL for current page
$url = new moodle_url("/mod/emarking/print/exams.php", array("id"=>$cmid, "course"=>$course->id));
// URL for adding a new print order
$params = $cmid > 0 ? array("cm"=>$cm->id) : array("course"=>$course->id);
$urladd = new moodle_url("/mod/emarking/print/newprintorder.php", $params);

$PAGE->set_url($url);
$PAGE->requires->js("/mod/emarking/js/printorders.js");
$PAGE->set_context($context);
$PAGE->set_course($course);
if($cmid > 0) {
	$PAGE->set_cm($cm);
}
$PAGE->set_title(get_string("emarking", "mod_emarking"));
$PAGE->set_pagelayout("incourse");
$PAGE->navbar->add(get_string("myexams","mod_emarking"));

echo $OUTPUT->header();

// Heading and tabs if we are within a course module
if($cmid > 0) {
    echo $OUTPUT->heading($emarking->name);
	echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "myexams");
}

// If a new exam was recently added, show success message and instructions
if($examid) {
	echo $OUTPUT->notification(get_string("newprintordersuccessinstructions", "mod_emarking",$newexam),"notifysuccess");
	if($CFG->emarking_printsuccessinstructions)
	   echo $OUTPUT->notification($CFG->emarking_printsuccessinstructions,"notifysuccess");
}

// Retrieve all exams for this course
$exams = $DB->get_records("emarking_exams", array("course"=>$course->id), "examdate DESC");

// If there are no exams to show
if(count($exams) == 0) {
	echo $OUTPUT->notification(get_string("noprintorders", "mod_emarking"));
	echo $OUTPUT->single_button($urladd, get_string("newprintorder", "mod_emarking"), "get", array("class"=>"submitbutton"));
	echo $OUTPUT->footer();
	die();
}

// Create a new html table
$examstable = new html_table();

// Table header
$examstable->head = array(
		get_string("exam", "mod_emarking"),
		get_string("date"),
		get_string("details", "mod_emarking"),
		get_string("sent", "mod_emarking"),
		get_string("status", "mod_emarking"),
		get_string("multicourse", "mod_emarking"),
		get_string("actions", "mod_emarking")
);

// CSS classes for each column in the table
$examstable->colclasses = array(
    "exams_examname",
    null,    
    null,    
    null,    
    null,    
    null,    
    null
);

// Now fill the table with exams data
foreach($exams as $exam) {
	$actions = html_writer::start_tag("div", array("class"=>"printactions"));
	
	// Show download button if the user has capability for downloading within 
	// the category or if she is a teacher and has download capability for the 
	// course and teacher downloads are allowed in the system
	if (has_capability ( "mod/emarking:downloadexam", $contextcat )
	    || ($CFG->emarking_teachercandownload
	        && has_capability ( "mod/emarking:downloadexam", $contextcourse ))) {
		$actions .= html_writer::div($OUTPUT->pix_icon("i/down", get_string("download"), null,
		    array("examid"=>$exam->id,"class"=>"downloademarking")));
    }

	list($canbedeleted, $multicourse) = emarking_exam_get_parallels($exam);

    // Check if exam can be deleted
	if($canbedeleted) {
	    // Add exam id to URL params
	    $params["id"] = $exam->id;

		// Url for exam deletion and editing
		$urldelete = new moodle_url("/mod/emarking/print/deleteexam.php", $params);
		$urledit = new moodle_url("/mod/emarking/print/newprintorder.php", $params);

		// Edit icon
		$actions .= html_writer::div($OUTPUT->action_icon($urledit, 
		    new pix_icon("t/edit", get_string("editorder", "mod_emarking"), null,
		    array("examid"=>$exam->id,"class"=>"downloademarking"))));

		// Delete icon
		$actions .= html_writer::div($OUTPUT->action_icon($urldelete, 
		    new pix_icon("t/delete", get_string("cancelorder", "mod_emarking"), null,
		    array("examid"=>$exam->id,"class"=>"downloademarking"))));
	}
	
	$actions .= html_writer::end_tag("div");
	$details = html_writer::start_tag("div", array("class"=>"printdetails"));
	
	if($exam->headerqr) {
	    $details .= html_writer::div($OUTPUT->pix_icon("qr-icon", 
	        get_string("headerqr", "mod_emarking"),"mod_emarking"));
	}

	if($exam->printlist) {
	    $details .= html_writer::div($OUTPUT->pix_icon("i/grades", 
	        get_string("printlist", "mod_emarking")));
	}

	if($exam->printrandom) {
	    $details .= html_writer::div($OUTPUT->pix_icon("shuffle", 
	        get_string("printrandom", "mod_emarking"),"mod_emarking"));
	}
	

	$details.= emarking_enrolments_div($exam);
	
	$details .= html_writer::end_tag("div");
		
	$examstatus = "";
	switch($exam->status) {
		case 1:
			$examstatus = get_string("examstatussent", "mod_emarking");
			break;
		case 2:
			$examstatus = get_string("examstatusdownloaded", "mod_emarking");
			break;
		case 3:
			$examstatus = get_string("examstatusprinted", "mod_emarking");
			break;
	}

	$examstable->data[] = array(
			$exam->name,
			date("l jS F g:ia", $exam->examdate),
			$details,
			emarking_time_ago($exam->timecreated),
			$examstatus,
			$multicourse,
			$actions
	);
}

echo html_writer::table($examstable);

echo $OUTPUT->single_button($urladd, get_string("newprintorder", "mod_emarking"), 
    "get", array("class"=>"newprintorder"));

$downloadurl = new moodle_url("/mod/emarking/print/download.php");

if($CFG->emarking_usesms) {
	$message = get_string("smsinstructions", "mod_emarking", $USER);
} else {
	$message = get_string("emailinstructions", "mod_emarking", $USER);
}

$multipdfs = $CFG->emarking_multiplepdfs;

?>
<script type="text/javascript">
	var wwwroot = "<?php echo $CFG->wwwroot ?>";
	var downloadurl = "<?php echo $downloadurl ?>";
	var sessionkey = "<?php echo sesskey() ?>";
	var multipdfs = "0";
	var incourse = "1";
</script>
<div id="loadingPanel"></div>
<!-- The panel DIV goes at the end to make sure it is loaded before javascript starts -->
<div id="panelContent">
	<div class="yui3-widget-bd">
		<form style="width: 100%">
			<fieldset>
				<p>
					<label for="id"><?php echo $message ?></label><br /> 
					<input type="text" name="sms"
						id="sms" placeholder="">
					<select onchange="change(this.value);">
						<option value="0"><?php echo get_string("singlepdf", "mod_emarking") ?></option>
						<option value="1"><?php echo get_string("multiplepdfs", "mod_emarking") ?></option>
					</select>
				</p>
			</fieldset>
		</form>
	</div>
</div>
<?php

echo $OUTPUT->footer();

?>

<script type="text/javascript">
	function change(e){
			multipdfs = e;
		}
</script>
