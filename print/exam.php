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
 * This page shows a list of exams sent for printing.
 * It can
 * be reached from a block within a category or from an eMarking
 * course module
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2012-2015 Jorge Villalon <villalon@gmail.com>
 * @copyright 2017 Hans Jeria <hansjeria@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . "/config.php");
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
global $DB, $USER, $CFG;
// Obtains basic data from cm id.
list ( $cm, $emarking, $course, $context ) = emarking_get_cm_course_instance ();
// First check that the user is logged in.
require_login ();
if (isguestuser ()) {
	die ();
}
$courseid = $cm->course;
list($issupervisor, $usercangrade) = emarking_get_grading_permissions($emarking, $context);
// URL for current page.
$url = new moodle_url ( "/mod/emarking/print/exam.php", array (
		"id" => $cm->id 
) );
$urlcourse = new moodle_url ( "/course/view.php", array (
		"id" => $courseid 
) );
// URL for adding a new print order.
$params = $cm->id > 0 ? array (
		"cm" => $cm->id 
) : array (
		"course" => $course->id 
);
$PAGE->set_url ( $url );
$PAGE->set_context ( $context );
$PAGE->set_course ( $course );
$PAGE->set_cm ( $cm );
$PAGE->set_title ( get_string ( "emarking", "mod_emarking" ) );
switch($CFG->emarking_pagelayouttype){
	case EMARKING_PAGES_LAYOUT_STANDARD:
		$PAGE->set_pagelayout('standard');
		break;
		
	case EMARKING_PAGES_LAYOUT_EMBEDDED:
		$PAGE->set_pagelayout('embedded');
		break;
}
$PAGE->navbar->add ( get_string ( "print", "mod_emarking" ) );
if (has_capability ( "mod/emarking:downloadexam", $context )) {
	$PAGE->requires->js ( "/mod/emarking/js/printorders.js" );
}
// If there are no exams to show.
$params = array (
		"course" => $course->id,
		"emarking" => $emarking->id
);
if (! $exam = $DB->get_record ( "emarking_exams", $params )) {
	if($emarking->uploadtype == EMARKING_UPLOAD_QR) {
		redirect ( new moodle_url ( "/course/modedit.php", array (
				"update" => $cm->id,
				"return" => "1"
		) ) );
		die ();
	}
}
echo $OUTPUT->header ();
// Heading and tabs if we are within a course module.
echo $OUTPUT->heading ( $emarking->name );
if($CFG->emarking_pagelayouttype == EMARKING_PAGES_LAYOUT_STANDARD){
	echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), 'myexams');
}
if(!$exam && $emarking->uploadtype != EMARKING_UPLOAD_QR) {
	echo $OUTPUT->notification('Files will be uploaded by students');
	echo $OUTPUT->footer();
	die();
}
list ( $canbedeleted, $multicourse ) = emarking_exam_get_parallels ( $exam );
// Create a new html table.
$examstable = new html_table ();
// Table header.
$examstable->head = array (
		get_string ( "examdetails", "mod_emarking" ),
		"&nbsp;" 
);
	
// CSS classes for each column in the table.
$examstable->colclasses = array (
		"exams_examname",
		null 
);
$examstable->data [] = array (
		"Nombre",
		//get_string ( "examname", "mod_emarking" ),
		$exam->name 
);
$details = html_writer::start_tag ( "div", array (
		"class" => "printdetails" 
) );
if ($exam->headerqr) {
	$details .= html_writer::div ( $OUTPUT->pix_icon ( "qr-icon", get_string ( "headerqr", "mod_emarking" ), "mod_emarking" ) );
}
if ($exam->printlist) {
	$details .= html_writer::div ( $OUTPUT->pix_icon ( "i/grades", get_string ( "printlist", "mod_emarking" ) ) );
}
if ($exam->printrandom) {
	$details .= html_writer::div ( $OUTPUT->pix_icon ( "shuffle", get_string ( "printrandom", "mod_emarking" ), "mod_emarking" ) );
}
$details .= emarking_enrolments_div ( $exam );
$details .= html_writer::end_tag ( "div" );
/* $examstable->data [] = array (
		get_string ( "examdate", "mod_emarking" ),
		date ( "l jS F Y, g:ia", usertime ( $exam->examdate ) ) 
); */
if ($usercangrade) {
	$examstatus = emarking_exam_status_string ( $exam );
	/*$examstable->data [] = array (
			get_string ( "comment", "mod_emarking" ),
			$exam->comment 
	);*/
	$examstable->data [] = array (
			get_string ( "status", "mod_emarking" ),
			$examstatus 
	);
	/*$examstable->data [] = array (
			get_string ( "details", "mod_emarking" ),
			$details 
	);*/
	$examstable->data [] = array (
			get_string ( "sent", "mod_emarking" ),
			emarking_time_ago ( $exam->timecreated ) 
	);
	$totalsheetsmessage = new stdClass();
	$totalsheetsmessage->originals = $exam->totalpages + $exam->extrasheets;
	$totalsheetsmessage->copies = $exam->totalstudents + $exam->extraexams;
	$totalsheetsmessage->totalsheets = $totalsheetsmessage->originals * $totalsheetsmessage->copies;
	$examstable->data [] = array (
			get_string ( 'totalpagesprint', 'mod_emarking'),
			get_string ( 'totalpagesprintdetails', 'mod_emarking', $totalsheetsmessage)
	);
	$user = $DB->get_record ( "user", array (
			"id" => $exam->requestedby 
	) );
	$examstable->data [] = array (
			get_string ( 'requestedby', 'mod_emarking' ),
			$user->firstname . ' ' . $user->lastname 
	);
	/*
	$examstable->data [] = array (
			get_string ( "multicourse", "mod_emarking" ),
			$multicourse ? $multicourse : get_string ( "no" ) 
	);*/
}
echo html_writer::table ( $examstable );
// Show download button if the user has capability for downloading within
// the category or if she is a teacher and has download capability for the
// course and teacher downloads are allowed in the system.
if (has_capability ( "mod/emarking:downloadexam", $context )) {
	$downloadurl = new moodle_url ( "/mod/emarking/print/download.php" );
	if ($CFG->emarking_usesms) {
		$message = get_string ( "smsinstructions", "mod_emarking", $USER );
	} else {
		$message = get_string ( "emailinstructions", "mod_emarking", $USER );
	}
	?>
<script type="text/javascript">
    var messages = {
		downloadexam: "<?php echo get_string("downloadexam", "mod_emarking") ?>",
		download: "<?php echo get_string("download", "mod_emarking") ?>",
		cancel: "<?php echo get_string("cancel", "mod_emarking") ?>",
		resendcode: "<?php echo get_string("resendcode", "mod_emarking") ?>",
		timeout: "<?php echo get_string("smsservertimeout", "mod_emarking") ?>",
		servererror: "<?php echo get_string("smsservererror", "mod_emarking") ?>"
    };
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
					<label for="id"><?php echo $message ?></label><br /> <input
						type="text" name="sms" id="sms" placeholder="">
				</p>
			</fieldset>
		</form>
	</div>
</div>
<?php
}
if (has_capability("mod/emarking:downloadexam", $context)) {
    $directdownload = isset($CFG->emarking_downloadsecurity) && $CFG->emarking_downloadsecurity == EMARKING_SECURITY_NO_VALIDATION;
    $buttontext = $exam->status < EMARKING_EXAM_BEING_PROCESSED ? get_string('exam', 'mod_emarking') . ' ' .
             core_text::strtolower(get_string('examstatusbeingprocessed', 'mod_emarking')) : get_string('downloadexam', 'mod_emarking');
    $disabled = $exam->status < EMARKING_EXAM_BEING_PROCESSED ? 'disabled' : '';
    if (! $directdownload) {
        $downloadexambutton = "<input type='button' class='downloademarking btn btn-default' examid ='$exam->id' value='" . $buttontext .
                 "' $disabled>";
        echo $downloadexambutton;
    } else {
        $directdownloadurl = new moodle_url('/mod/emarking/print/download.php', 
                array(
                    'sesskey' => sesskey(),
                    'multi' => 0,
                    'incourse' => 1,
                    'examid' => $exam->id));
        echo $OUTPUT->single_button($directdownloadurl, $buttontext, 'GET');
    }
}
if ($issupervisor) {
	// Active types tab.
	$urlscan = new moodle_url ( "/mod/emarking/print/enablefeatures.php", array (
			"id" => $cm->id,
			"type" => EMARKING_TYPE_PRINT_SCAN 
	) );
	$urlosm = new moodle_url ( "/mod/emarking/print/enablefeatures.php", array (
			"id" => $cm->id,
			"type" => EMARKING_TYPE_ON_SCREEN_MARKING 
	) );
	echo html_writer::start_tag ( 'div' );
	if ($emarking->type == EMARKING_TYPE_PRINT_ONLY) {
		echo $OUTPUT->single_button ( $urlscan, get_string ( "enablescan", "mod_emarking" ), 'GET', array('class'=>'form-submit') );
	} else if ($emarking->type == EMARKING_TYPE_PRINT_SCAN) {
		echo $OUTPUT->single_button ( $urlosm, get_string ( "enableosm", "mod_emarking" ), 'GET', array('class'=>'form-submit') );
	}
	echo html_writer::end_tag ( 'div' );
}
echo $OUTPUT->footer ();
