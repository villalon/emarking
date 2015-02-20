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
 * @copyright 2015 Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname (dirname(dirname( __FILE__ ))) . '/config.php');
require_once ("lib.php");
require_once ($CFG->libdir . '/tablelib.php');
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/lib/externallib.php");
require_once($CFG->dirroot.'/lib/excellib.class.php');

global $USER, $OUTPUT, $DB, $CFG, $PAGE;

// Course module id
$cmid = required_param ( 'id', PARAM_INT );
// Page to show (when paginating)
$page = optional_param ( 'page', 0, PARAM_INT );
// Table sort
$tsort = optional_param ( 'tsort', '', PARAM_ALPHA );

$exportcsv = optional_param ( 'exportcsv', false, PARAM_BOOL );

// Rows per page
$perpage = 100;

// Validate course module
if (! $cm = get_coursemodule_from_id ( 'emarking', $cmid )) {
	print_error ( get_string ( 'invalidcoursemodule', 'mod_emarking' ) . " id: $cmid" );
}

// Validate eMarking activity  //TODO: validar draft si  está selccionado
if (! $emarking = $DB->get_record ( 'emarking', array ('id' => $cm->instance) )) {
	print_error ( get_string ( 'invalidid', 'mod_emarking' ) . " id: $cmid" );
}

// Validate course
if (! $course = $DB->get_record ( 'course', array ('id' => $emarking->course))) {
	print_error ( get_string ( 'invalidcourseid', 'mod_emarking'));
}

// Get the course module for the emarking, to build the emarking url
$urlemarking = new moodle_url ( '/mod/emarking/view.php', array ('id' => $cm->id));
$context = context_module::instance ( $cm->id );

// Check that user is logued in the course
require_login ( $course->id );
if (isguestuser ()) {
	die ();
}

// Check if user has an editingteacher role
$issupervisor = has_capability ( 'mod/emarking:supervisegrading', $context );
$usercangrade = has_capability ( 'mod/assign:grade', $context );

if ($issupervisor || is_siteadmin ( $USER )) {
	$emarking->anonymous = false;
}

// Download Excel if it is the case
if ($exportcsv && $usercangrade && $issupervisor) {
	emarking_download_excel($emarking);
	die ();
}

// Page navigation and URL settings
$PAGE->set_url ( $urlemarking );
$PAGE->set_context ( $context );
$PAGE->set_course ( $course );
$PAGE->set_pagelayout ( 'incourse' );
$PAGE->set_cm ( $cm );
$PAGE->set_heading ( $course->fullname );
$PAGE->navbar->add ( get_string ( 'emarking', 'mod_emarking' ) );
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');

// Show header and heading
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help ( 
		get_string ( 'emarking', 'mod_emarking' ), 
		'annotatesubmission', 'mod_emarking' );
echo $OUTPUT->heading(get_string_type($emarking->type),4);

// Navigation tabs
echo $OUTPUT->tabtree ( emarking_tabs ( $context, $cm, $emarking ), "mark" );

// Get rubric instance
list ( $gradingmanager, $gradingmethod ) = emarking_validate_rubric ( $context, true );

// User filter checking capabilities. If user can not grade, then she can not
// see other users
$userfilter = 'WHERE 1=1 ';
if (! $usercangrade) {
	$userfilter .= 'AND ue.userid = ' . $USER->id;
} else if($emarking->type == EMARKING_TYPE_MARKER_TRAINING && !is_siteadmin($USER->id) && !$issupervisor) {	
	$userfilter .= 'AND um.id = ' . $USER->id;
}

// Show export to Excel button if supervisor and there are students to export
if ($issupervisor && $emarking->type == EMARKING_TYPE_NORMAL) {
	$csvurl = new moodle_url ( 'view.php', array (
			'id' => $cm->id,
			'exportcsv' => true 
	) );
	echo $OUTPUT->single_button ( $csvurl, get_string('exporttoexcel', 'mod_emarking'));
}

// Only when marking normally for a grade we can publish grades
if($emarking->type == EMARKING_TYPE_NORMAL) {
	echo "<form id='publishgrades' action='marking/publish.php' method='post'>";
	echo "<input type='hidden' name='id' value='$cm->id'>";
}
// Default variables for the number of criteria for this evaluation
// and minimum and maximum scores
$numcriteria = 0;
$rubricscores = array (
		'maxscore' => 0,
		'minscore' => 0 
);

// If there is a rubric defined we can get the controller and the parameters for this rubric
if ($gradingmethod && ($rubriccontroller = $gradingmanager->get_controller ( $gradingmethod ))) {
	if ($rubriccontroller instanceof gradingform_rubric_controller) {
		// Getting the number of criteria
		if ($rubriccriteria = $rubriccontroller->get_definition ()) {
			$numcriteria = count ( $rubriccriteria->rubric_criteria );
		}
		// Getting min and max scores
		$rubricscores = $rubriccontroller->get_min_max_score ();
	}
}

// Calculates the number of criteria assigned to current user
$numcriteriauser = $DB->count_records_sql ( "
		SELECT COUNT(DISTINCT criterion) 
		FROM {emarking_marker_criterion} 
		WHERE emarking=? AND marker=?", array (
		$emarking->id,
		$USER->id 
) );

// Check if activity is configured with separate groups to filter users
if ($cm->groupmode == SEPARATEGROUPS && $usercangrade && ! is_siteadmin ( $USER ) && ! $issupervisor) {
		$userfilter .= "
		AND u.id in (
			SELECT userid
			FROM {groups_members}
			WHERE groupid in (
				SELECT groupid
				FROM {groups_members} as gm
				INNER JOIN {groups} as g on (gm.groupid = g.id)
				WHERE gm.userid = $USER->id AND g.courseid = e.courseid
							)
					)";
}


// Get submissions with extra info to show
$sql = "
SELECT  u.*,
IFNULL(NM.submissionid,0) as submission,
IFNULL(NM.groupid,0) as groupid,
IFNULL(NM.draftid,0) as draft,
IFNULL(NM.status,0) as status,
IFNULL(NM.pages,0) as pages,
IFNULL(NM.comments,0) as comments,
CASE WHEN 0 = $numcriteria THEN 0 ELSE ROUND( IFNULL(NM.comments,0) / $numcriteria * 100, 0) END as pctmarked,
CASE WHEN 0 = $numcriteriauser THEN 0 ELSE ROUND( IFNULL(NM.commentsassigned,0) / $numcriteriauser * 100, 0) END as pctmarkeduser,
IFNULL(NM.grade,0) as grade,
IFNULL(NM.score,0) as score,
IFNULL(NM.bonus,0) as bonus,
IFNULL(NM.regrades,0) as regrades,
IFNULL(NM.generalfeedback,'') as feedback,
IFNULL(NM.timemodified, 0) as timemodified,
NM.grademax as grademax,
NM.grademin as grademin,
NM.sort,
NM.commentsassignedids,
NM.criteriaids,
NM.criteriascores,
IFNULL(um.lastname, '') as markerlast,
IFNULL(um.firstname, '') as markerfirst,
IFNULL(um.id, 0) as markerid
FROM {user_enrolments} ue
INNER JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
INNER JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
INNER JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.userid = ue.userid)
INNER JOIN {role} as r on (r.id = ra.roleid AND r.shortname = 'student')
INNER JOIN {user} u ON (ue.userid = u.id)
LEFT JOIN (
SELECT s.student,
d.id as draftid,
d.submissionid as submissionid,
d.groupid as groupid,
d.status,
d.timemodified,
d.grade,
d.generalfeedback,
d.teacher as marker,
count(distinct p.id) as pages,
count(distinct c.id) as comments,
count(distinct r.id) as regrades,
count(distinct mc.id) as commentsassigned,
IFNULL(GROUP_CONCAT(mc.id),'') as commentsassignedids,
IFNULL(GROUP_CONCAT(l.criterionid),'') as criteriaids,
IFNULL(GROUP_CONCAT(l.score + c.bonus),'') as criteriascores,
nm.course,
nm.id,
nm.grade as grademax,
nm.grademin as grademin,
round(sum(l.score),2) as score,
round(sum(c.bonus),2) as bonus,
d.sort
FROM {emarking} AS nm
INNER JOIN {emarking_draft} AS d ON (nm.id = ? AND d.emarkingid = nm.id)
INNER JOIN {emarking_submission} AS s ON (s.id = d.submissionid)
INNER JOIN {emarking_page} AS p ON (p.submission = d.submissionid)
LEFT JOIN {emarking_comment} as c on (c.page = p.id AND c.draft = d.id AND c.levelid > 0)
LEFT JOIN {gradingform_rubric_levels} as l ON (c.levelid = l.id)
LEFT JOIN {emarking_regrade} as r ON (r.draft = d.id AND r.criterion = l.criterionid AND r.accepted = 0)
LEFT JOIN {emarking_marker_criterion} AS mc ON (mc.criterion = l.criterionid AND mc.emarking = nm.id AND mc.marker=?)
GROUP BY d.id
) AS NM ON (u.id = NM.student AND e.courseid = NM.course)
LEFT JOIN {user} as um ON (NM.marker = um.id)
$userfilter
";

// Run the query on the database
$drafts = $DB->get_recordset_sql ( $sql, array (
		$course->id,
		$emarking->id,
		$USER->id
), $page * $perpage, $perpage );

$totalstudents = count($drafts);

$actionsheader = get_string ( 'actions', 'mod_emarking' );
$actionsheader .= $usercangrade ? '&nbsp;<input type="checkbox" id="select_all" title="' . get_string ( 'selectall', 'mod_emarking' ) . '">' : '';

$headers = array ();
$headers[] = get_string ( 'names', 'mod_emarking' );
if($emarking->type == 2)
	$headers[] = get_string ( 'marker', 'mod_emarking' );
$headers[] = get_string ( 'status', 'mod_emarking' );
$headers[] = get_string ( 'pctmarked', 'mod_emarking' );
$headers[] = get_string ( 'grade', 'mod_emarking' ) . ' ' . get_string ( 'between', 'mod_emarking', array (
				'min' => floatval ( $emarking->grademin ),
				'max' => floatval ( $emarking->grade )
		) );
$headers[] = get_string ( 'comment', 'mod_emarking' );
$headers[] = get_string ( 'lastmodification', 'mod_emarking' );
$headers[] = $actionsheader;
// Define flexible table (can be sorted in different ways)
$showpages = new flexible_table ( 'emarking-view-' . $cmid );
$showpages->define_headers($headers);
$columns = array();
$columns[] = 'lastname';
if($emarking->type==2)
	$columns[] = 'marker';
$columns[] = 'status';
$columns[] = 'pctmarked';
$columns[] = 'grade';
$columns[] = 'comment';
$columns[] = 'timemodified';
$columns[] = 'actions';
$showpages->define_columns ($columns);
$showpages->define_baseurl ( $urlemarking );
$defaulttsort = $emarking->anonymous ? null : 'lastname';
$showpages->sortable ( true, $defaulttsort, SORT_ASC );
if ($emarking->anonymous) {
	$showpages->no_sorting ( 'lastname' );
}
$showpages->no_sorting ( 'comment' );
$showpages->no_sorting ( 'actions' );
$showpages->pageable ( true );
$showpages->pagesize ( $perpage, $totalstudents );
$showpages->setup ();

// Decide on sorting depending on URL parameters and flexible table configuration
$orderby = $emarking->anonymous ? 'ORDER BY sort ASC' : 'ORDER BY u.lastname ASC';
if ($showpages->get_sql_sort ()) {
	$orderby = 'ORDER BY ' . $showpages->get_sql_sort ();
	$tsort = $showpages->get_sql_sort ();
}

// Get submissions with extra info to show
$sql .= $orderby;

// Run the query on the database
$drafts = $DB->get_recordset_sql ( $sql, array (
		$course->id,
		$emarking->id,
		$USER->id 
), $page * $perpage, $perpage );


$unpublishedsubmissions = 0;
// Prepare data for the table
foreach ( $drafts as $draft ) {
	
	
	// Student info
	$profileurl = new moodle_url ( '/user/view.php', array (
			'id' => $draft->id,
			'course' => $course->id 
	) );
	$userinfo = $emarking->anonymous ? get_string ( 'anonymousstudent', 'mod_emarking' ) : $OUTPUT->user_picture ( $draft ) . '&nbsp;<a href="' . $profileurl . '">' . $draft->firstname . ' ' . $draft->lastname . '</a>';
	
	// Draft status
	$pages = intval ( $draft->pages );
	$status = emarking_get_string_for_status ( $draft->status );
	
	// Add warning icon if there are missing pages in draft
	if ($emarking->totalpages > 0 && $emarking->totalpages > $pages) {
		$status .= '<br/>' . $OUTPUT->pix_icon ( 'i/risk_xss', get_string ( 'missingpages', 'mod_emarking' ) );
	}
	
	// Completion matrix
	$matrix = '';
	$markedcriteria = explode ( ",", $draft->criteriaids );
	$markedcriteriascores = explode ( ",", $draft->criteriascores );
	if (count ( $markedcriteria ) > 0 && $numcriteria > 0) {
		$matrix = "
				<div id='sub-$draft->draft' class='modal hide fade' aria-hidden='true' style='display:none;'>
	<div class='modal-header'>
		<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>Close</button>
		<h3>$emarking->name</h3>
		<h4>$userinfo</h4>
		</div><div class='modal-body'><table width='100%'>";
		$matrix .= "<tr><th>" . get_string ( 'criterion', 'mod_emarking' ) . "</th><th style='text-align:center'>" . get_string ( 'corrected', 'mod_emarking' ) . "</th></tr>";
		foreach ( $rubriccriteria->rubric_criteria as $criterion ) {
			$matrix .= "<tr><td>" . $criterion ['description'] . "</td><td style='text-align:center'>";
			$key = array_search ( $criterion ['id'], $markedcriteria );
			if ($key !== false) {
				$matrix .= $OUTPUT->pix_icon ( 'i/completion-manual-y', round ( $markedcriteriascores [$key], 1 ) . "pts" );
			} else {
				$matrix .= $OUTPUT->pix_icon ( 'i/completion-manual-n', null );
			}
			$matrix .= "</td></tr>";
		}
		$matrix .= "</table></div><div class='modal-footer'>
		<button class='btn' data-dismiss='modal' aria-hidden='true'>" . get_string ( 'close', 'mod_emarking' ) . "</button>
	</div></div>
				";
	}
	// Percentage of criteria already marked for this draft
	$pctmarkedtitle = ($numcriteria - $draft->comments) . " pending criteria";
	$pctmarked = "<a href='#' onclick='$(\"#sub-$draft->draft\").modal(\"show\");'>" . ($numcriteriauser > 0 ? $draft->pctmarkeduser . "% / " : '') . $draft->pctmarked . "%" . ($draft->regrades > 0 ? '<br/>' . $draft->regrades . ' ' . get_string ( 'regradespending', 'mod_emarking' ) : '') . "</a>" . $matrix;
	$pctmarked = $OUTPUT->box ( $pctmarked, 'generalbox', null, array (
			'title' => $pctmarkedtitle 
	) );
	
	// Grade
	$bonusinfo = $draft->bonus != 0 ? round ( $draft->bonus, 2 ) . " " : ' ';
	$bonusinfo = ($draft->bonus > 0 ? '+' : '') . $bonusinfo;
	$gradevalue = round ( floatval ( $draft->grade ), 2 );
	$finalgrade = $draft->status == EMARKING_STATUS_GRADING && $usercangrade ? $gradevalue . '<br/>' . get_string ( 'notpublished', 'mod_emarking' ) : $OUTPUT->heading ( $draft->status >= EMARKING_STATUS_RESPONDED ? $gradevalue : '-', 3 );
	$finalgrade = $OUTPUT->box ( $finalgrade, 'generalbox', null, array (
			'title' => round ( $draft->score, 2 ) . $bonusinfo . get_string ( 'of', 'mod_emarking' ) . " " . $rubricscores ['maxscore'] . " " . get_string ( 'points', 'grades' ) 
	) );
	
	// eMarking popup url
	$popup_url = new moodle_url ( '/mod/emarking/ajax/a.php', array (
			'ids' => $draft->draft,
			'action' => 'emarking'
	) );
	
	// Action buttons
	$actions = '<div width="100%" style="white-space:nowrap; margin-top:15px;">';
	
	// eMarking button
	if (($usercangrade && $draft->status >= EMARKING_STATUS_SUBMITTED && $numcriteria > 0) || $draft->status >= EMARKING_STATUS_RESPONDED) {
		$pixicon = $usercangrade ? new pix_icon ( 'i/manual_item', get_string ( 'annotatesubmission', 'mod_emarking' ) ) : new pix_icon ( 'i/preview', get_string ( 'viewsubmission', 'mod_emarking' ) );
		$actions .= $OUTPUT->action_link ( $popup_url, null, new popup_action ( 'click', $popup_url, 'emarking' . $draft->draft, array (
				'menubar' => 'no',
				'titlebar' => 'no',
				'status' => 'no',
				'toolbar' => 'no' 
		) ), null, $pixicon );
	}
	
	// Mark draft as absent/sent
	if ($emarking->type == EMARKING_TYPE_NORMAL && (is_siteadmin ( $USER ) || ($issupervisor && $usercangrade)) && $draft->status > EMARKING_STATUS_MISSING) {
		
		$newstatus = $draft->status >= EMARKING_STATUS_SUBMITTED ? EMARKING_STATUS_ABSENT : EMARKING_STATUS_SUBMITTED;
		
		$deletesubmissionurl = new moodle_url ( '/mod/emarking/marking/updatesubmission.php', array (
				'ids' => $draft->draft,
				'cm' => $cm->id,
				'status' => $newstatus 
		) );
		
		$pixicon = $draft->status >= EMARKING_STATUS_SUBMITTED ? new pix_icon ( 't/delete', get_string ( 'setasabsent', 'mod_emarking' ) ) : new pix_icon ( 'i/checkpermissions', get_string ( 'setassubmitted', 'mod_emarking' ) );
		
		$actions .= '&nbsp;&nbsp;' . $OUTPUT->action_link ( $deletesubmissionurl, null, null, null, $pixicon );
	}
	
	// Url for downloading PDF feedback
	$responseurl = new moodle_url ( '/pluginfile.php/' . $context->id . '/mod_emarking/response/' . $draft->id . '/response_' . $emarking->id . '_' . $draft->id . '.pdf' );
	
	// Download PDF button
	if ($emarking->type == EMARKING_TYPE_NORMAL && $draft->status >= EMARKING_STATUS_RESPONDED && ($draft->id == $USER->id || is_siteadmin ( $USER ) || $issupervisor)) {
		$actions .= '&nbsp;&nbsp;' . $OUTPUT->action_link ( $responseurl, null, null, null, new pix_icon ( 'f/pdf', get_string ( 'downloadfeedback', 'mod_emarking' ) ) );
	}
	
	// Checkbox for publishing grade
	if ($emarking->type == EMARKING_TYPE_NORMAL && $draft->status >= EMARKING_STATUS_SUBMITTED && $draft->status < EMARKING_STATUS_RESPONDED && $usercangrade) {
		$unpublishedsubmissions ++;
		$actions .= '&nbsp;&nbsp;<input type="checkbox" name="publish[]" value="' . $draft->draft . '">';
	}
	
	$actions .= '</div>';
	
	// Feedback
	$feedback = strlen ( $draft->feedback ) > 0 ? $draft->feedback : '';
	
	// Last modified
	$timemodified = $draft->timemodified > 0 ? date ( "d/m/y H:i", $draft->timemodified ) : '';
	// If there's a draft show total pages
	if ($draft->status >= EMARKING_STATUS_SUBMITTED) {
		$totalpages = $emarking->totalpages > 0 ? ' / ' . $emarking->totalpages . ' ' : ' ';
		$timemodified .= '<br/>' . $pages . $totalpages . get_string ( 'pages', 'mod_emarking' );
	}
	
	$data = array();
	$data[] = $userinfo;
	if($emarking->type==2)
		$data[] = $OUTPUT->action_link(new moodle_url('/user/view.php', array('id'=>$draft->markerid, 'course'=>$course->id)), 
				$draft->markerfirst . ' ' . $draft->markerlast);
	$data[] = $status;
	$data[] = $pctmarked;
	$data[] = $finalgrade;
	$data[] = $feedback;
	$data[] = $timemodified;
	$data[] = $actions;
	$showpages->add_data ($data);
}

?>
<style>
.scol,.generaltable td {
	vertical-align: middle;
}
</style>
<?php if ($usercangrade) {?>
<script type="text/javascript">
$('#select_all').change(function() {
    var checkboxes = $('#publishgrades').find(':checkbox');
    if($(this).is(':checked')) {
        checkboxes.prop('checked', true);
        $('#select_all').prop('title','<?php echo get_string('selectnone', 'mod_emarking') ?>');
} else {
        checkboxes.prop('checked', false);
        $('#select_all').prop('title','<?php echo get_string('selectall', 'mod_emarking') ?>');
	}
});

function validatePublish() {
	var checkboxes = $('#publishgrades').find(':checkbox');
	var checked = 0;
	checkboxes.each(function () {
		if($(this).is(':checked')) {
			checked++;
		}
	});
	if(checked > 0) {
		return confirm('<?php echo get_string('areyousure','mod_emarking') ?>');
	} else {
		alert('<?php echo get_string('nosubmissionsselectedforpublishing','mod_emarking') ?>');
		return false;
	}
}
</script>
<?php
}

$showpages->print_html ();

if ($usercangrade && $unpublishedsubmissions > 0) {
	echo "<input style='float:right;' type='submit' onclick='return validatePublish();' value='" . get_string ( 'publishselectededgrades', 'mod_emarking' ) . "'>";
} else if ($unpublishedsubmissions == 0) {
	echo "<script>$('#select_all').hide();</script>";
}
echo "</form>";
// If the user can not grade, we show them
if (! $usercangrade && $CFG->emarking_enablejustice) {
	require_once $CFG->dirroot . '/mod/emarking/forms/justice_form.php';
	
	$submission = $DB->get_record ( 'emarking_submission', array (
			'emarking' => $emarking->id,
			'student' => $USER->id 
	) );
	$record = $submission ? $DB->get_record ( 'emarking_perception', array (
			"submission" => $submission->id 
	) ) : null;
	
	$mform = new justice_form ( $urlemarking, null, 'post' );
	$mform->set_data ( $record );
	if ($mform->get_data ()) {
		if (! $record) {
			$record = new stdClass ();
		}
		$record->submission = $submission->id;
		$record->overall_fairness = $mform->get_data ()->overall_fairness;
		$record->expectation_reality = $mform->get_data ()->expectation_reality;
		$record->timecreated = time ();
		if (isset ( $record->id )) {
			$DB->update_record ( 'emarking_perception', $record );
		} else {
			$record->id = $DB->insert_record ( 'emarking_perception', $record );
		}
		echo $OUTPUT->notification ( get_string ( 'thanksforjusticeperception', 'mod_emarking' ), 'notifysuccess' );
	}
	$mform->display ();
}
echo $OUTPUT->footer ();