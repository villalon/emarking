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
 * @copyright 2012 Jorge Villalon <jorge.villalon@uai.cl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot."/mod/emarking/locallib.php");

global $DB, $USER, $CFG;

// Obtain parameter from URL
$cmid = optional_param('id', 0, PARAM_INT);
$courseid = optional_param('course', 0, PARAM_INT);
$examid = optional_param('examid', 0, PARAM_INT);
$downloadform = optional_param('downloadform', false, PARAM_BOOL);

if($cmid > 0 && !$cm = get_coursemodule_from_id('emarking', $cmid)) {
	print_error(get_string('invalidid', 'mod_emarking'));
}

if($cmid > 0 && !$emarking = $DB->get_record('emarking', array('id'=>$cm->instance))) {
	print_error(get_string('invalidid', 'mod_emarking'));
}

if($cmid > 0) {
	$courseid = $cm->course;
}

// Validate that the parameter corresponds to a course
if(!$course = $DB->get_record('course', array('id'=>$courseid))) {
	print_error(get_string('invalidcourseid', 'mod_emarking'));
}

// The context for the page is a course
if($cmid > 0)
	$context = context_module::instance($cm->id);
else
	$context = context_course::instance($course->id);

// First check that the user is logged in
require_login();
if (isguestuser()) {
	die();
} 

$newexam = $DB->get_record('emarking_exams', array('id'=>$examid));

if($newexam && $downloadform) {
	$coursecat = $DB->get_record('course_categories', array('id'=>$course->category));
	$requestedbyuser = $DB->get_record('user', array('id'=>$newexam->requestedby));

	emarking_create_printform($context,
		$newexam,
		$USER,
		$requestedbyuser,
		$coursecat,
		emarking_exam_total_pages_to_print($newexam),
		$course);
	die();
}

require_capability ( 'mod/emarking:grade', $context );

// URL for current page and eMarking home
$url = new moodle_url('/mod/emarking/exams.php', array('id'=>$cmid, 'course'=>$course->id));
if($cmid > 0)
	$urladd = new moodle_url('/mod/emarking/newprintorder.php',array('cm'=>$cm->id));
else
	$urladd = new moodle_url('/mod/emarking/newprintorder.php',array('course'=>$course->id));

$PAGE->set_url($url);
$PAGE->requires->js('/mod/emarking/js/printorders.js');
$PAGE->set_context($context);
$PAGE->set_course($course);
if($cmid > 0)
	$PAGE->set_cm($cm);
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('myexams','mod_emarking'));

echo $OUTPUT->header();

// If a new exam was recently added, show success message and instructions
if($newexam) {
	echo $OUTPUT->notification(get_string('newprintordersuccessinstructions', 'mod_emarking',$newexam),'notifysuccess');
	echo $OUTPUT->notification($CFG->emarking_printsuccessinstructions,'notifysuccess');
}

echo $OUTPUT->heading_with_help(get_string('myexams', 'mod_emarking'), 'myexams', 'mod_emarking');

if($cmid > 0)
	echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "myexams" );


// Retrieve all exams for this course
$exams = $DB->get_records('emarking_exams', array('course'=>$course->id), 'examdate DESC');

if(count($exams) == 0) {
	echo $OUTPUT->notification(get_string('noprintorders', 'mod_emarking'));
	echo $OUTPUT->single_button($urladd, get_string('newprintorder', 'mod_emarking'));
	echo $OUTPUT->footer();
	die();
}

// Create a new html table
$examstable = new html_table();

// Table header
$examstable->head = array(
		get_string('examname', 'mod_emarking'),
		get_string('examdate', 'mod_emarking'),
		get_string('headerqr', 'mod_emarking'),
		get_string('examdatesent', 'mod_emarking'),
		get_string('status', 'mod_emarking'),
		get_string('multicourse', 'mod_emarking'),
		get_string('actions', 'mod_emarking')
);

// Now fill the table with exams data
foreach($exams as $exam) {
	$actions = '';
	$emarking ='';
	$headerqr = '';
	$statistics ='';

	list($canbedeleted, $multicourse) = emarking_exam_get_parallels($exam);

	if(has_capability('mod/emarking:downloadexam', $context)) {
		$actions .= '<a href="#">'.$OUTPUT->pix_icon('i/down', get_string('download'),null,array("examid"=>$exam->id,"class"=>"downloademarking")).'</a>&nbsp;&nbsp;';
	}

	// Check if exam can be deleted
	if($canbedeleted) {
		if($cmid > 0) {
			// Url for exam deletion
			$urldelete = new moodle_url('/mod/emarking/deleteexam.php',array('id'=>$exam->id,'cm'=>$cm->id));
			// Url for exam deletion
			$urledit = new moodle_url('/mod/emarking/newprintorder.php',array('id'=>$exam->id,'cm'=>$cm->id));
		} else {
			// Url for exam deletion
			$urldelete = new moodle_url('/mod/emarking/deleteexam.php',array('id'=>$exam->id,'course'=>$course->id));
			// Url for exam deletion
			$urledit = new moodle_url('/mod/emarking/newprintorder.php',array('id'=>$exam->id,'course'=>$course->id));
		}
		$actions .= '<a href="'.$urledit.'">'.$OUTPUT->pix_icon('t/edit', get_string('editorder', 'mod_emarking'));
		$actions .= '&nbsp;&nbsp;<a href="'.$urldelete.'">'.$OUTPUT->pix_icon('t/delete', get_string('cancelorder', 'mod_emarking'));
	}

	$emarking .= $exam->emarking ? get_string('yes') : get_string('no');

	$headerqr .= $exam->headerqr ? get_string('yes') : get_string('no');

	$examstatus = '';
	switch($exam->status) {
		case 1:
			$examstatus = get_string('examstatussent', 'mod_emarking');
			break;
		case 2:
			$examstatus = get_string('examstatusdownloaded', 'mod_emarking');
			break;
		case 3:
			$examstatus = get_string('examstatusprinted', 'mod_emarking');
			break;
	}

	$examstable->data[] = array(
			$exam->name,
			date("d/m/y H:i", $exam->examdate),
			$headerqr,
			date("d/m/y H:i", $exam->timecreated),
			$examstatus,
			$multicourse,
			$actions
	);
}

echo html_writer::table($examstable);

echo $OUTPUT->single_button($urladd, get_string('newprintorder', 'mod_emarking'));

$downloadurl = new moodle_url('/mod/emarking/download.php');

if($CFG->emarking_usesms) {
	$message = get_string('smsinstructions', 'mod_emarking', $USER);
} else {
	$message = get_string('emailinstructions', 'mod_emarking', $USER);
}

$multipdfs = $CFG->emarking_multiplepdfs;

?>
<script type="text/javascript">
	var wwwroot = '<?php echo $CFG->wwwroot ?>';
	var downloadurl = '<?php echo $downloadurl ?>';
	var sessionkey = '<?php echo sesskey() ?>';
	var multipdfs = '0';
</script>
<div id="loadingPanel"></div>
<!-- The panel DIV goes at the end to make sure it is loaded before javascript starts -->
<div id="panelContent">
	<div class="yui3-widget-bd">
		<form>
			<fieldset>
				<p>
					<label for="id"><?php echo $message ?></label><br /> 
					<input type="text" name="sms"
						id="sms" placeholder="">
					<select onchange="change(this.value);">
					  <option value="0">pdf unico</option>
					  <option value="1">pdf multiple</option>
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
