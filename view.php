<?php
require_once (dirname ( __FILE__ ) . '/../../config.php');
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

// Show header and heading
echo $OUTPUT->header ();
echo $OUTPUT->heading_with_help ( get_string ( 'emarking', 'mod_emarking' ), 'annotatesubmission', 'mod_emarking' );

// Navigation tabs
echo $OUTPUT->tabtree ( emarking_tabs ( $context, $cm, $emarking ), "mark" );

// Get rubric instance
list ( $gradingmanager, $gradingmethod ) = emarking_validate_rubric ( $context, true );

// User filter checking capabilities. If user can not grade, then she can not
// see other users
$userfilter = 'WHERE 1=1 ';
if (! $usercangrade) {
	$userfilter .= 'AND ue.userid = ' . $USER->id;
}

// Count total students enrolled for pagination
$totalstudents = $DB->count_records_sql ( "
		SELECT COUNT(DISTINCT u.id) AS total
		FROM {user_enrolments} ue
		INNER JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
		INNER JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
		INNER JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.userid = ue.userid)
		INNER JOIN {role} as r on (r.id = ra.roleid AND r.shortname = 'student')
		INNER JOIN {user} as u on (ra.userid = u.id)
		$userfilter
		", array (
				$course->id,
				$emarking->id
		) );

if ($issupervisor && $totalstudents > 0) {
	$csvurl = new moodle_url ( 'view.php', array (
			'id' => $cm->id,
			'exportcsv' => true 
	) );
	echo $OUTPUT->single_button ( $csvurl, get_string('exporttoexcel', 'mod_emarking'));
}

echo "<form id='publishgrades' action='publish.php' method='post'>";
echo "<input type='hidden' name='id' value='$cm->id'>";

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
if (($cm->groupmode == SEPARATEGROUPS || $emarking->experimentalgroups!=0) && $usercangrade && ! is_siteadmin ( $USER ) && ! $issupervisor) {
	if($emarking->experimentalgroups != 0){
		$userfilter .= "
		AND u.id in (
			SELECT d.student 
			FROM {emarking_draft} d, {emarking_experimental_groups} eeg
			WHERE d.groupid IN (
				SELECT groupid
				FROM {groups_members} as gm
				INNER JOIN {groups} as g on (gm.groupid = g.id)
				WHERE gm.userid = $USER->id AND g.courseid = e.courseid
			) AND d.groupid = eeg.groupid AND eeg.emarkingid = $emarking->id AND eeg.datestart <= UNIX_TIMESTAMP() AND eeg.dateend >=UNIX_TIMESTAMP()
		)";

	}else{
		$userfilter .= "
		AND u.id in (
			SELECT userid
			FROM {groups_members}
			WHERE groupid in (
				SELECT groupid
				FROM {groups_members} as gm
				INNER JOIN {groups} as g on (gm.groupid = g.id)
				WHERE gm.userid = $USER->id AND g.courseid = e.courseid))";
	}
}else{
	if($emarking->experimentalgroups == 2){
		$userfilter.= " AND NM.groupid <> 0 ";
	}
	
}

$firstselect = "u.*, 
				IFNULL(NM.submissionid,0) as submission, ";
if($emarking->experimentalgroups == 2){
	
	if($usercangrade && ! is_siteadmin ( $USER ) && ! $issupervisor){
		$submissiondraft = "INNER JOIN {emarking_draft} AS s ON (nm.id = ? AND s.emarkingid = nm.id AND s.groupid <> 0)
							INNER JOIN {emarking_experimental_groups} AS eeg ON (nm.id = eeg.emarkingid AND s.groupid = eeg.groupid AND eeg.datestart <= UNIX_TIMESTAMP() AND eeg.dateend >=UNIX_TIMESTAMP())
							INNER JOIN {groups_members} AS gm ON (gm.userid = $USER->id AND gm.groupid= s.groupid) 
							INNER JOIN {emarking_page} AS p ON (p.submission = s.id) ";
	}else{
		$submissiondraft = " INNER JOIN {emarking_draft} AS s ON (nm.id = ? AND s.emarkingid = nm.id AND s.groupid <> 0)
							INNER JOIN {emarking_page} AS p ON (p.submission = s.id) ";
	}
	$groupbydraft = " GROUP BY s.id ";
	$firstselect = "IFNULL(NM.submissionid,0) as submission,
					u.*, ";
	
}else{
	$submissiondraft = " INNER JOIN {emarking_draft} AS s ON (nm.id = ? AND s.emarkingid = nm.id)
						INNER JOIN {emarking_page} AS p ON (p.submission = s.id) ";
	$groupbydraft = " GROUP BY s.id, s.student ";
}

$actionsheader = get_string ( 'actions', 'mod_emarking' );
$actionsheader .= $usercangrade ? '&nbsp;<input type="checkbox" id="select_all" title="' . get_string ( 'selectall', 'mod_emarking' ) . '">' : '';

// Define flexible table (can be sorted in different ways)
$showpages = new flexible_table ( 'emarking-view-' . $cmid );
$showpages->define_headers ( array (
		get_string ( 'names', 'mod_emarking' ),
		get_string ( 'status', 'mod_emarking' ),
		get_string ( 'pctmarked', 'mod_emarking' ),
		get_string ( 'grade', 'mod_emarking' ) . ' ' . get_string ( 'between', 'mod_emarking', array (
				'min' => floatval ( $emarking->grademin ),
				'max' => floatval ( $emarking->grade ) 
		) ),
		get_string ( 'comment', 'mod_emarking' ),
		get_string ( 'lastmodification', 'mod_emarking' ),
		'Acuerdo (%)',
		$actionsheader 
) );
$showpages->define_columns ( array (
		'lastname',
		'status',
		'pctmarked',
		'grade',
		'comment',
		'timemodified',
		'agreement',
		'actions' 
) );
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
$sql = "
SELECT  $firstselect
		IFNULL(NM.groupid,0) as groupid, 
		IFNULL(NM.realsubmissionid,0) as realsubmission,
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
		gi.grademax as grademax,
		gi.grademin as grademin,
		NM.sort,
		NM.commentsassignedids,
		NM.criteriaids,
		NM.criteriascores
FROM {user_enrolments} ue
INNER JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
INNER JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
INNER JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.userid = ue.userid)
INNER JOIN {role} as r on (r.id = ra.roleid AND r.shortname = 'student')
INNER JOIN {user} u ON (ue.userid = u.id)
LEFT JOIN (
	SELECT s.student, 
		s.id as submissionid, 
		s.submissionid as realsubmissionid, 
		s.groupid as groupid,
		s.status, 
		s.timemodified,
		s.grade,
		s.generalfeedback, 
		count(distinct p.id) as pages,
		count(distinct c.id) as comments,
		count(distinct r.id) as regrades,
		count(distinct mc.id) as commentsassigned,
		IFNULL(GROUP_CONCAT(mc.id),'') as commentsassignedids,
		IFNULL(GROUP_CONCAT(l.criterionid),'') as criteriaids,
		IFNULL(GROUP_CONCAT(l.score + c.bonus),'') as criteriascores,
		nm.course, 
		nm.id,
		round(sum(l.score),2) as score,
		round(sum(c.bonus),2) as bonus,
		s.sort
	FROM {emarking} AS nm
	$submissiondraft
	LEFT JOIN {emarking_comment} as c on (c.page = p.id AND c.levelid > 0)
	LEFT JOIN {gradingform_rubric_levels} as l ON (c.levelid = l.id)
	LEFT JOIN {emarking_regrade} as r ON (r.submission = s.id AND r.criterion = l.criterionid AND r.accepted = 0)
	LEFT JOIN {emarking_marker_criterion} AS mc ON (mc.criterion = l.criterionid AND mc.emarking = nm.id AND mc.marker=?)
	$groupbydraft
) AS NM ON (u.id = NM.student AND e.courseid = NM.course)
LEFT JOIN {grade_items} AS gi ON (gi.iteminstance = NM.id AND gi.itemmodule = 'emarking' AND gi.itemtype = 'mod')
$userfilter
$orderby";


//die(print_r($sql));

// Run the query on the database
$emarkingpages = $DB->get_records_sql ( $sql, array (
		$course->id,
		$emarking->id,
		$USER->id 
), $page * $perpage, $perpage );


$unpublishedsubmissions = 0;
// Prepare data for the table
foreach ( $emarkingpages as $pageinfo ) {
	
	
	// Student info
	$profileurl = new moodle_url ( '/user/view.php', array (
			'id' => $pageinfo->id,
			'course' => $course->id 
	) );
	$userinfo = $emarking->anonymous ? get_string ( 'anonymousstudent', 'mod_emarking' ) : $OUTPUT->user_picture ( $pageinfo ) . '&nbsp;<a href="' . $profileurl . '">' . $pageinfo->firstname . ' ' . $pageinfo->lastname . '</a>';
	
	// Submission status
	$pages = intval ( $pageinfo->pages );
	$status = emarking_get_string_for_status ( $pageinfo->status );
	
	// Add warning icon if there are missing pages in submission
	if ($emarking->totalpages > 0 && $emarking->totalpages > $pages) {
		$status .= '<br/>' . $OUTPUT->pix_icon ( 'i/risk_xss', get_string ( 'missingpages', 'mod_emarking' ) );
	}
	
	// Completion matrix
	$matrix = '';
	$markedcriteria = explode ( ",", $pageinfo->criteriaids );
	$markedcriteriascores = explode ( ",", $pageinfo->criteriascores );
	if (count ( $markedcriteria ) > 0 && $numcriteria > 0) {
		$matrix = "<div id='sub-$pageinfo->submission' class='modal hide fade' aria-hidden='true' style='display:none;'>
	<div class='modal-header'>
		<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>�</button>
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
	</div></div>";
	}
	// Percentage of criteria already marked for this submission
	$pctmarkedtitle = ($numcriteria - $pageinfo->comments) . " pending criteria";
	$pctmarked = "<a href='#' onclick='$(\"#sub-$pageinfo->submission\").modal(\"show\");'>" . ($numcriteriauser > 0 ? $pageinfo->pctmarkeduser . "% / " : '') . $pageinfo->pctmarked . "%" . ($pageinfo->regrades > 0 ? '<br/>' . $pageinfo->regrades . ' ' . get_string ( 'regradespending', 'mod_emarking' ) : '') . "</a>" . $matrix;
	$pctmarked = $OUTPUT->box ( $pctmarked, 'generalbox', null, array (
			'title' => $pctmarkedtitle 
	) );
	
	// Grade
	$bonusinfo = $pageinfo->bonus != 0 ? round ( $pageinfo->bonus, 2 ) . " " : ' ';
	$bonusinfo = ($pageinfo->bonus > 0 ? '+' : '') . $bonusinfo;
	$gradevalue = round ( floatval ( $pageinfo->grade ), 2 );
	$finalgrade = $pageinfo->status == EMARKING_STATUS_GRADING && $usercangrade ? $gradevalue . '<br/>' . get_string ( 'notpublished', 'mod_emarking' ) : $OUTPUT->heading ( $pageinfo->status >= EMARKING_STATUS_RESPONDED ? $gradevalue : '-', 3 );
	$finalgrade = $OUTPUT->box ( $finalgrade, 'generalbox', null, array (
			'title' => round ( $pageinfo->score, 2 ) . $bonusinfo . get_string ( 'of', 'mod_emarking' ) . " " . $rubricscores ['maxscore'] . " " . get_string ( 'points', 'grades' ) 
	) );
	
	// eMarking popup url
	$popup_url = new moodle_url ( '/mod/emarking/ajax/a.php', array (
			'ids' => $pageinfo->submission,
			'action' => 'emarking'
	) );
	
	// Action buttons
	$actions = '<div width="100%" style="white-space:nowrap; margin-top:15px;">';
	
	// eMarking button
	if (($usercangrade && $pageinfo->status >= EMARKING_STATUS_SUBMITTED && $numcriteria > 0) || $pageinfo->status >= EMARKING_STATUS_RESPONDED) {
		$pixicon = $usercangrade ? new pix_icon ( 'i/manual_item', get_string ( 'annotatesubmission', 'mod_emarking' ) ) : new pix_icon ( 'i/preview', get_string ( 'viewsubmission', 'mod_emarking' ) );
		$actions .= $OUTPUT->action_link ( $popup_url, null, new popup_action ( 'click', $popup_url, 'emarking' . $pageinfo->submission, array (
				'menubar' => 'no',
				'titlebar' => 'no',
				'status' => 'no',
				'toolbar' => 'no' 
		) ), null, $pixicon );
	}
	
	// Mark submission as absent/sent
	if ((is_siteadmin ( $USER ) || ($issupervisor && $usercangrade)) && $pageinfo->status > EMARKING_STATUS_MISSING) {
		
		$newstatus = $pageinfo->status >= EMARKING_STATUS_SUBMITTED ? EMARKING_STATUS_ABSENT : EMARKING_STATUS_SUBMITTED;
		
		$deletesubmissionurl = new moodle_url ( '/mod/emarking/updatesubmission.php', array (
				'ids' => $pageinfo->submission,
				'cm' => $cm->id,
				'status' => $newstatus 
		) );
		
		$pixicon = $pageinfo->status >= EMARKING_STATUS_SUBMITTED ? new pix_icon ( 't/delete', get_string ( 'setasabsent', 'mod_emarking' ) ) : new pix_icon ( 'i/checkpermissions', get_string ( 'setassubmitted', 'mod_emarking' ) );
		
		$actions .= '&nbsp;&nbsp;' . $OUTPUT->action_link ( $deletesubmissionurl, null, null, null, $pixicon );
	}
	
	// Url for downloading PDF feedback
	$responseurl = new moodle_url ( '/pluginfile.php/' . $context->id . '/mod_emarking/response/' . $pageinfo->id . '/response_' . $emarking->id . '_' . $pageinfo->id . '.pdf' );
	
	// Download PDF button
	if ($pageinfo->status >= EMARKING_STATUS_RESPONDED && ($pageinfo->id == $USER->id || is_siteadmin ( $USER ) || $issupervisor)) {
		$actions .= '&nbsp;&nbsp;' . $OUTPUT->action_link ( $responseurl, null, null, null, new pix_icon ( 'f/pdf', get_string ( 'downloadfeedback', 'mod_emarking' ) ) );
	}
	
	if ($pageinfo->status >= EMARKING_STATUS_SUBMITTED && $pageinfo->status < EMARKING_STATUS_RESPONDED && $usercangrade) {
		$unpublishedsubmissions ++;
		$actions .= '&nbsp;&nbsp;<input type="checkbox" name="publish[]" value="' . $pageinfo->submission . '">';
	}
	
	$actions .= '</div>';
	
	// Feedback
	$feedback = strlen ( $pageinfo->feedback ) > 0 ? $pageinfo->feedback : '';
	
	// Last modified
	$timemodified = $pageinfo->timemodified > 0 ? date ( "d/m/y H:i", $pageinfo->timemodified ) : '';
	// If there's a submission show total pages
	if ($pageinfo->status >= EMARKING_STATUS_SUBMITTED) {
		$totalpages = $emarking->totalpages > 0 ? ' / ' . $emarking->totalpages . ' ' : ' ';
		$timemodified .= '<br/>' . $pages . $totalpages . get_string ( 'pages', 'mod_emarking' );
	}
	
	$agreeAssignment = $DB->get_record_sql("SELECT d.submissionid, STDDEV(d.grade)*2/6 as dispersion, count(d.id) as conteo
			FROM mdl_emarking_draft d
			WHERE d.groupid <> 0 AND d.submissionid = $pageinfo->realsubmission AND d.grade <> 1
			GROUP BY d.submissionid");
	if($agreeAssignment){
		$agreeLevelAvg = $agreeAssignment->dispersion;
	}else{
		$agreeLevelAvg = 0;
	}
	
	$showpages->add_data ( array (
			$userinfo,
			$status,
			$pctmarked,
			$finalgrade,
			$feedback,
			$timemodified,
			round((1-$agreeLevelAvg)*100,2)."%",
			$actions 
	) );
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
?>
<?php

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