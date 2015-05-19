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
 * @copyright 2012 Jorge Villalon <jorge.villalon@uai.cl>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ('locallib.php');

global $DB, $CFG, $SCRIPT, $USER;

$categoryid = required_param ( 'category', PARAM_INT );
$status = optional_param ( 'status', 1, PARAM_INT );
$page = optional_param ( 'page', 0, PARAM_INT );
$perpage = 10;

// Validate status (print orders or history)
if ($status < 1 || $status > 2) {
	print_error ( 'Invalid status' );
}

if (! $category = $DB->get_record ( 'course_categories', array (
		'id' => $categoryid 
) )) {
	print_error ( get_string ( 'invalidcategoryid', 'mod_emarking' ) );
}

$context = context_coursecat::instance ( $categoryid );

require_login();

$url = new moodle_url ( '/mod/emarking/print/statistics.php', array (
		'category' => $categoryid 
) );
$ordersurl = new moodle_url ( '/mod/emarking/print/printorders.php', array (
		'category' => $categoryid,
		'status' => $status 
) );
$categoryurl = new moodle_url ( '/course/index.php', array (
		'categoryid' => $categoryid 
) );

if (! has_capability ( 'mod/emarking:printordersview', $context )) {
	// TODO: Log invalid access to printorders
	print_error ( 'Not allowed!' );
}

$pagetitle = $status == 1 ? get_string ( 'printorders', 'mod_emarking' ) : get_string ( 'records', 'mod_emarking' );

$PAGE->set_context ( $context );
$PAGE->set_url ( $url );
$PAGE->requires->js ( '/mod/emarking/js/printorders.js' );
$PAGE->set_pagelayout ( 'course' );
$PAGE->navbar->add ( $category->name, $categoryurl );
$PAGE->navbar->add ( get_string ( 'printorders', 'mod_emarking' ), $ordersurl );
$PAGE->navbar->add ( $pagetitle );
$PAGE->set_heading ( get_site ()->fullname );
$PAGE->set_title(get_string('emarking', 'mod_emarking'));

require_login ();
if (isguestuser ()) {
	die ();
}

// Creating tables and adding columns header
$examstable = new html_table ();

if ($status == 1) {
	$examstable->head = array (
			get_string ( 'examdate', 'mod_emarking' ),
			get_string ( 'examname', 'mod_emarking' ),
			get_string ( 'course' ),
			get_string ( 'details', 'mod_emarking' ),
			get_string ( 'requestedby', 'mod_emarking' ),
			get_string ( 'examdatesent', 'mod_emarking' ),
			ucfirst ( get_string ( 'pages', 'mod_emarking' ) ),
			get_string ( 'actions' ) 
	);
	$examstable->size = array (
			'10%',
			'10%',
			'10%',
			'5%',
			'5%',
			'10%',
			'5%',
			'7%' 
	);
	$examstable->align = array (
			'left',
			'center',
			'center',
			'center',
			'center',
			'center',
			'center',
			'right' 
	);
} else {
	$examstable->head = array (
			get_string ( 'examdate', 'mod_emarking' ),
			get_string ( 'examname', 'mod_emarking' ),
			get_string ( 'course' ),
			get_string ( 'details', 'mod_emarking' ),
			get_string ( 'requestedby', 'mod_emarking' ),
			get_string ( 'examdateprinted', 'mod_emarking' ),
			get_string ( 'actions' ),
			get_string ( 'printnotification', 'mod_emarking' ) 
	);
	$examstable->size = array (
			'5%',
			'10%',
			'10%',
			'5%',
			'5%',
			'10%',
			'7%',
			'5%' 
	);
	$examstable->align = array (
			'left',
			'center',
			'center',
			'center',
			'center',
			'center',
			'center',
			'center' 
	);
}

// Parameters for SQL calls
$statussql = $status == 1 ? EMARKING_EXAM_UPLOADED : EMARKING_EXAM_SENT_TO_PRINT . "," . EMARKING_EXAM_PRINTED;
$order = $status == 1 ? "e.examdate asc, c.shortname ASC" : "e.examdate desc, c.shortname ASC";
$ids_children = emarking_get_categories_childs ( $categoryid );
$params = array (
		$statussql 
);

$sqlcount = " SELECT count(*)
 FROM {emarking_exams} as e
INNER JOIN {course} as c ON (e.course = c.id)
WHERE c.category in ($ids_children) AND e.status in (?)";

// Get the count so we can use pagination
$examscount = $DB->count_records_sql ( $sqlcount, $params );

$sql = "SELECT e.*,
			c.id as courseid,
			c.fullname as coursefullname,
			u.id as userid,
			CONCAT(u.firstname, ' ', u.lastname) as userfullname,
			cc.name as category
		FROM {emarking_exams} as e
		INNER JOIN {course} as c ON (e.course = c.id)
		INNER JOIN {user} as u ON (e.requestedby = u.id)
		INNER JOIN {course_categories} as cc ON (cc.id = c.category)
		WHERE c.category in ($ids_children) AND e.status in ($statussql)
		ORDER BY " . $order;


	

// Getting all print orders

$exams = $DB->get_records_sql ( $sql, null, $page * $perpage, ($page + 1) * $perpage ); // status = 1 means still not downloaded

$currentdate = time ();
$current = 0;

?>
<style>
<!--
.examisok {
	border-left: 4px solid lightblue;
}

.examislate {
	border-left: 4px solid red;
}

.examtoday {
	border-left: 4px solid green;
}
-->
</style>
<?php
foreach ( $exams as $exam ) {
	
	// Url for the course
	$urlcourse = new moodle_url ( '/course/view.php', array (
			'id' => $exam->course 
	) );
	// Url for the user profile of the person who requested the exam
	$urlprofile = new moodle_url ( '/user/profile.php', array (
			'id' => $exam->userid 
	) );
	
	// Calculate the total pages and pages to print for this exam
	$totalpages = $exam->totalpages;
	$pagestoprint = ($exam->totalpages + $exam->extrasheets) * ($exam->totalstudents + $exam->extraexams);
	
	if ($exam->usebackside) {
		$pagestoprint = ceil ( ($exam->totalpages + $exam->extrasheets) / 2 ) * ($exam->totalstudents + $exam->extraexams);
	}
	
	$actions = '<table><tr>';
	// Download exam link
	$actions .= '<td style="border:solid 0px #fff; max-width:18px;"><a href="#">' . $OUTPUT->pix_icon ( 'i/down', get_string ( 'download' ), null, array (
			"examid" => $exam->id,
			"class" => "downloademarking" 
	) ) . '</a></td>';
	
	// Print directly
	if ($CFG->emarking_enableprinting) {
		$actions .= '<td style="border:solid 0px #fff; max-width:18px;">' . $OUTPUT->action_icon ( new moodle_url ( '/mod/emarking/print/printexam.php', array (
				'exam' => $exam->id 
		) ), new pix_icon ( 't/print', get_string ( 'printexam', 'mod_emarking' ) ) ) . '</a></td>';
	}
	
	// Download print form
	$actions .= '<td style="border:solid 0px #fff; max-width:18px;">' . $OUTPUT->action_icon ( new moodle_url ( '/mod/emarking/print/exams.php', array (
			'course' => $exam->course,
			'examid' => $exam->id,
			'downloadform' => 'true' 
	) ), new pix_icon ( 'i/report', get_string ( 'downloadform', 'mod_emarking' ) ) ) . '</a></td>';
	
	$actions .= '</tr></table>';
	
	// Calculating date differences to identify exams that are late, are for today and so on
	if (date ( "d/m/y", $exam->examdate ) === date ( "d/m/y", $currentdate )) {
		$examstable->rowclasses [$current] = 'examtoday';
	} else if ($currentdate < $exam->examdate) {
		$examstable->rowclasses [$current] = 'examisok';
	} else {
		$examstable->rowclasses [$current] = 'examislate';
	}
	
	if ($status == 1) {
		$examstable->data [] = array (
				date ( "d M H:i", $exam->examdate ),
				$exam->name,
				$OUTPUT->action_link ( $urlcourse, $exam->coursefullname ),
				$exam->category . '<br/>' . $exam->enrolments,
				$OUTPUT->action_link ( $urlprofile, $exam->userfullname ),
				date ( "d M H:i", $exam->timecreated ),
				$pagestoprint,
				$actions 
		);
	} else {
		
		$notification = $exam->notified ? $OUTPUT->pix_icon ( 't/approve', get_string ( 'printnotificationsent', 'mod_emarking' ) ) : '<a href="' . $CFG->wwwroot . '/mod/emarking/print/sendprintnotification.php?id=' . $exam->id . '">' . $OUTPUT->pix_icon ( 'i/email', get_string ( 'printsendnotification', 'mod_emarking' ) ) . '</a>';
		
		$examstable->data [] = array (
				date ( "d M H:i", $exam->examdate ),
				$exam->name,
				$OUTPUT->action_link ( $urlcourse, $exam->coursefullname ),
				$exam->category . '<br/>' . $exam->enrolments,
		    	$OUTPUT->action_link ( $urlprofile, $exam->userfullname ),
				date ( "d M H:i", $exam->printdate ),
				$actions,
				$notification 
		);
	}
	
	$current ++;
}

echo $OUTPUT->header ();

$activetab = $status == 1 ? 'printorders' : 'printordershistory';
echo $OUTPUT->tabtree ( emarking_printoders_tabs ( $category ), $activetab );

echo $OUTPUT->heading ( $pagetitle . ' ' . $category->name );

if (count ( $exams ) > 0) {
	echo html_writer::table ( $examstable ); // print the table
	echo $OUTPUT->paging_bar ( $examscount, $page, $perpage, $CFG->wwwroot . '/mod/emarking/print/printorders.php?category=' . $categoryid . '&status=' . $status . '&page=' );
} else {
	echo $OUTPUT->notification ( get_string ( 'noexamsforprinting', 'mod_emarking' ), 'notifyproblem' );
}

$downloadurl = new moodle_url ( '/mod/emarking/print/download.php' );

if ($CFG->emarking_usesms) {
	$message = get_string ( 'smsinstructions', 'mod_emarking', $USER );
} else {
	$message = get_string ( 'emailinstructions', 'mod_emarking', $USER );
}

$multipdfs = $CFG->emarking_multiplepdfs;

?>
<script type="text/javascript">
	var wwwroot = '<?php echo $CFG->wwwroot ?>';
	var downloadurl = '<?php echo $downloadurl ?>';
	var sessionkey = '<?php echo sesskey() ?>';
	var multipdfs = '0';
	var incourse = '0';
</script>
<div id="loadingPanel"></div>
<!-- The panel DIV goes at the end to make sure it is loaded before javascript starts -->
<div id="panelContent">
	<div class="yui3-widget-bd">
		<form>
			<fieldset>
				<p>
					<label for="id"><?php echo $message ?></label><br /> <input
						type="text" name="sms" id="sms" placeholder=""> <select
						onchange="change(this.value);">
						<option value="0"><?php echo get_string('singlepdf', 'mod_emarking') ?></option>
						<option value="1"><?php echo get_string('multiplepdfs', 'mod_emarking') ?></option>
					</select>
				</p>
			</fieldset>
		</form>
	</div>
</div>
<?php

echo $OUTPUT->footer ();

?>

<script type="text/javascript">
	function change(e){
			multipdfs = e;
		}
</script>
