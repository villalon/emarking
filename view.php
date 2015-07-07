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
require_once (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once ("lib.php");
require_once ($CFG->libdir . '/tablelib.php');
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/marking/locallib.php");
require_once ($CFG->dirroot . "/lib/externallib.php");
require_once ($CFG->dirroot . '/lib/excellib.class.php');

global $USER, $OUTPUT, $DB, $CFG, $PAGE;

// Course module id
$cmid = required_param('id', PARAM_INT);
// Page to show (when paginating)
$page = optional_param('page', 0, PARAM_INT);
// Table sort
$tsort = optional_param('tsort', '', PARAM_ALPHA);

$exportcsv = optional_param('exportcsv', false, PARAM_BOOL);

// Rows per page
$perpage = 100;

// Validate course module
if (! $cm = get_coursemodule_from_id('emarking', $cmid)) {
    print_error(get_string('invalidcoursemodule', 'mod_emarking') . " id: $cmid");
}

// Validate eMarking activity //TODO: validar draft si está selccionado
if (! $emarking = $DB->get_record('emarking', array(
    'id' => $cm->instance
))) {
    print_error(get_string('invalidid', 'mod_emarking') . " id: $cmid");
}

// Validate course
if (! $course = $DB->get_record('course', array(
    'id' => $emarking->course
))) {
    print_error(get_string('invalidcourseid', 'mod_emarking'));
}

// Get the course module for the emarking, to build the emarking url
$urlemarking = new moodle_url('/mod/emarking/view.php', array(
    'id' => $cm->id
));
$context = context_module::instance($cm->id);

// Check that user is logued in the course
require_login($course->id);
if (isguestuser()) {
    die();
}

// Check if user has an editingteacher role
$issupervisor = has_capability('mod/emarking:supervisegrading', $context);
$usercangrade = has_capability('mod/assign:grade', $context);

if ($issupervisor || is_siteadmin($USER)) {
    $emarking->anonymous = 2;
}

// Download Excel if it is the case
if ($exportcsv && $usercangrade && $issupervisor) {
    emarking_download_excel($emarking);
    die();
}

// Get all user which can mark in this activity
$markers = get_enrolled_users($context, 'mod/emarking:grade');

// Page navigation and URL settings
$PAGE->set_url($urlemarking);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('emarking', 'mod_emarking'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

// Show header and heading
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);

// Navigation tabs
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "mark");

// Get rubric instance
list ($gradingmanager, $gradingmethod) = emarking_validate_rubric($context, true);

// User filter checking capabilities. If user can not grade, then she can not
// see other users
$userfilter = 'WHERE 1=1 ';
if (! $usercangrade) {
    $userfilter .= 'AND u.id = ' . $USER->id;
} else 
    if ($emarking->type == EMARKING_TYPE_MARKER_TRAINING && ! is_siteadmin($USER->id) && ! $issupervisor) {
        $userfilter .= 'AND um.id = ' . $USER->id;
    }

$qcfilter = ' AND d.qualitycontrol = 0';
if ($emarking->qualitycontrol && ($DB->count_records('emarking_markers', array(
    'emarking' => $emarking->id,
    'marker' => $USER->id,
    'qualitycontrol' => 1
)) > 0 || is_siteadmin($USER))) {
    $qcfilter = '';
}

// Show export to Excel button if supervisor and there are students to export
if ($issupervisor && $emarking->type == EMARKING_TYPE_NORMAL) {
    $csvurl = new moodle_url('view.php', array(
        'id' => $cm->id,
        'exportcsv' => true
    ));
    echo $OUTPUT->single_button($csvurl, get_string('exporttoexcel', 'mod_emarking'));
}

// Only when marking normally for a grade we can publish grades
if ($emarking->type == EMARKING_TYPE_NORMAL && has_capability("mod/emarking:supervisegrading", $context)) {
    echo "<form id='publishgrades' action='marking/publish.php' method='post'>";
    echo "<input type='hidden' name='id' value='$cm->id'>";
}
// Default variables for the number of criteria for this evaluation
// and minimum and maximum scores
$numcriteria = 0;
$rubricscores = array(
    'maxscore' => 0,
    'minscore' => 0
);

// If there is a rubric defined we can get the controller and the parameters for this rubric
if ($gradingmethod && ($rubriccontroller = $gradingmanager->get_controller($gradingmethod))) {
    if ($rubriccontroller instanceof gradingform_rubric_controller) {
        // Getting the number of criteria
        if ($rubriccriteria = $rubriccontroller->get_definition()) {
            $numcriteria = count($rubriccriteria->rubric_criteria);
        }
        // Getting min and max scores
        $rubricscores = $rubriccontroller->get_min_max_score();
    }
}

// Calculates the number of criteria assigned to current user
$numcriteriauser = $DB->count_records_sql("
		SELECT COUNT(DISTINCT criterion) 
		FROM {emarking_marker_criterion} 
		WHERE emarking=? AND marker=?", array(
    $emarking->id,
    $USER->id
));

// Check if activity is configured with separate groups to filter users
if ($cm->groupmode == SEPARATEGROUPS && $emarking->type == EMARKING_TYPE_NORMAL && $usercangrade && ! is_siteadmin($USER) && ! $issupervisor) {
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
$sqlstudents = "
SELECT  u.id,
u.picture,
u.imagealt,
u.firstname,
u.lastname,
u.middlename,
u.firstnamephonetic,
u.lastnamephonetic,
u.alternatename,
u.email,
IFNULL(NM.submissionid,0) as submission,
GROUP_CONCAT(IFNULL(NM.groupid,0) SEPARATOR '#') as groupid,
GROUP_CONCAT(IFNULL(NM.draftid,0) SEPARATOR '#') as draft,
GROUP_CONCAT(IFNULL(NM.status,0) SEPARATOR '#') as status,
GROUP_CONCAT(IFNULL(NM.pages,0) SEPARATOR '#') as pages,
GROUP_CONCAT(IFNULL(NM.comments,0) SEPARATOR '#') as comments,
GROUP_CONCAT(IFNULL(NM.qcs,0) SEPARATOR '#') as qcs,
GROUP_CONCAT(
CASE WHEN 0 = $numcriteria THEN 0 ELSE ROUND( IFNULL(NM.comments,0) / $numcriteria * 100, 0) END
SEPARATOR '#') as pctmarked,
GROUP_CONCAT(
CASE WHEN 0 = $numcriteriauser THEN 0 ELSE ROUND( IFNULL(NM.commentsassigned,0) / $numcriteriauser * 100, 0) END
SEPARATOR '#') as pctmarkeduser,
GROUP_CONCAT(IFNULL(NM.grade,0) SEPARATOR '#') as grade,
GROUP_CONCAT(IFNULL(NM.score,0) SEPARATOR '#') as score,
GROUP_CONCAT(IFNULL(NM.bonus,0) SEPARATOR '#') as bonus,
GROUP_CONCAT(IFNULL(NM.regrades,0) SEPARATOR '#') as regrades,
GROUP_CONCAT(IFNULL(NM.generalfeedback,'') SEPARATOR '#') as feedback,
GROUP_CONCAT(IFNULL(NM.timemodified, 0) SEPARATOR '#') as timemodified,
NM.grademax as grademax,
NM.grademin as grademin,
NM.sort,
NM.commentsassignedids,
GROUP_CONCAT(NM.criteriaids SEPARATOR '#') as criteriaids,
GROUP_CONCAT(NM.criteriascores SEPARATOR '#') as criteriascores,
GROUP_CONCAT(IFNULL(um.picture, '') SEPARATOR '#') as markerpicture,
GROUP_CONCAT(IFNULL(um.lastname, '') SEPARATOR '#') as markerlast,
GROUP_CONCAT(IFNULL(um.firstname, '') SEPARATOR '#') as markerfirst,
GROUP_CONCAT(IFNULL(um.id, 0) SEPARATOR '#') as markerid
FROM (
SELECT u.*, e.courseid FROM {user_enrolments} AS ue
INNER JOIN {enrol} AS e ON (e.id = ue.enrolid AND e.courseid = ?)
INNER JOIN {context} AS c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
INNER JOIN {role_assignments} AS ra ON (ra.contextid = c.id AND ra.userid = ue.userid)
INNER JOIN {role} AS r on (r.id = ra.roleid AND r.shortname = 'student')
INNER JOIN {user} AS u ON (ue.userid = u.id)
GROUP BY u.id) as u
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
d.qualitycontrol as qcs,
count(distinct p.id) as pages,
count(distinct l.id) as comments,
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
INNER JOIN {emarking_draft} AS d ON (nm.id = ? AND d.emarkingid = nm.id $qcfilter)
INNER JOIN {emarking_submission} AS s ON (s.id = d.submissionid)
INNER JOIN {emarking_page} AS p ON (p.submission = d.submissionid)
LEFT JOIN {emarking_comment} as c on (c.page = p.id AND c.draft = d.id AND c.levelid > 0)
LEFT JOIN {gradingform_rubric_levels} as l ON (c.levelid = l.id)
LEFT JOIN {emarking_regrade} as r ON (r.draft = d.id AND r.criterion = l.criterionid AND r.accepted = 0)
LEFT JOIN {emarking_marker_criterion} AS mc ON (mc.criterion = l.criterionid AND mc.emarking = nm.id AND mc.marker=?)
GROUP BY d.id
) AS NM ON (u.id = NM.student AND u.courseid = NM.course)
LEFT JOIN {user} as um ON (NM.marker = um.id)
$userfilter
GROUP BY u.id
";

$sqldrafts = "
SELECT  es.student as id,
		'' as picture,
		'' as imagealt,
		'' as firstname,
		'' as lastname,
		'' as middlename,
		'' as firstnamephonetic,
		'' as lastnamephonetic,
		'' as alternatename,
		'' as email,
IFNULL(NM.submissionid,0) as submission,
GROUP_CONCAT(IFNULL(NM.groupid,0) SEPARATOR '#') as groupid,
GROUP_CONCAT(IFNULL(NM.draftid,0) SEPARATOR '#') as draft,
GROUP_CONCAT(IFNULL(NM.status,0) SEPARATOR '#') as status,
GROUP_CONCAT(IFNULL(NM.pages,0) SEPARATOR '#') as pages,
GROUP_CONCAT(IFNULL(NM.comments,0) SEPARATOR '#') as comments,
GROUP_CONCAT(IFNULL(NM.qcs,0) SEPARATOR '#') as qcs,
GROUP_CONCAT(
CASE WHEN 0 = $numcriteria THEN 0 ELSE ROUND( IFNULL(NM.comments,0) / $numcriteria * 100, 0) END
SEPARATOR '#') as pctmarked,
GROUP_CONCAT(
CASE WHEN 0 = $numcriteriauser THEN 0 ELSE ROUND( IFNULL(NM.commentsassigned,0) / $numcriteriauser * 100, 0) END
SEPARATOR '#') as pctmarkeduser,
GROUP_CONCAT(IFNULL(NM.grade,0) SEPARATOR '#') as grade,
GROUP_CONCAT(IFNULL(NM.score,0) SEPARATOR '#') as score,
GROUP_CONCAT(IFNULL(NM.bonus,0) SEPARATOR '#') as bonus,
GROUP_CONCAT(IFNULL(NM.regrades,0) SEPARATOR '#') as regrades,
GROUP_CONCAT(IFNULL(NM.generalfeedback,'') SEPARATOR '#') as feedback,
GROUP_CONCAT(IFNULL(NM.timemodified, 0) SEPARATOR '#') as timemodified,
NM.grademax as grademax,
NM.grademin as grademin,
NM.sort,
NM.commentsassignedids,
GROUP_CONCAT(NM.criteriaids SEPARATOR '#') as criteriaids,
GROUP_CONCAT(NM.criteriascores SEPARATOR '#') as criteriascores,
GROUP_CONCAT(IFNULL(um.picture, '') SEPARATOR '#') as markerpicture,
GROUP_CONCAT(IFNULL(um.lastname, '') SEPARATOR '#') as markerlast,
GROUP_CONCAT(IFNULL(um.firstname, '') SEPARATOR '#') as markerfirst,
GROUP_CONCAT(IFNULL(um.id, 0) SEPARATOR '#') as markerid
FROM {emarking_submission} AS es
INNER JOIN {emarking} AS e ON (e.id = ? AND es.emarking = e.id)
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
d.qualitycontrol as qcs,
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
INNER JOIN {emarking_draft} AS d ON (nm.id = ? AND d.emarkingid = nm.id $qcfilter)
INNER JOIN {emarking_submission} AS s ON (s.id = d.submissionid)
INNER JOIN {emarking_page} AS p ON (p.submission = d.submissionid)
LEFT JOIN {emarking_comment} as c on (c.page = p.id AND c.draft = d.id AND c.levelid > 0)
LEFT JOIN {gradingform_rubric_levels} as l ON (c.levelid = l.id)
LEFT JOIN {emarking_regrade} as r ON (r.draft = d.id AND r.criterion = l.criterionid AND r.accepted = 0)
LEFT JOIN {emarking_marker_criterion} AS mc ON (mc.criterion = l.criterionid AND mc.emarking = nm.id AND mc.marker=?)
GROUP BY d.id
) AS NM ON (es.student = NM.student AND es.id = NM.submissionid)
LEFT JOIN {user} as um ON (NM.marker = um.id)
$userfilter
GROUP BY es.student
";

if ($emarking->type == EMARKING_TYPE_NORMAL) {
    $params = array(
        $course->id,
        $emarking->id,
        $USER->id
    );
    
    // Run the query on the database
    $drafts = $DB->get_recordset_sql($sqlstudents, $params, $page * $perpage, $perpage);
} else {
    $params = array(
        $emarking->id,
        $emarking->id,
        $USER->id
    );
    
    // Run the query on the database
    $drafts = $DB->get_recordset_sql($sqldrafts, $params, $page * $perpage, $perpage);
}

$totalstudents = count($drafts);

$actionsheader = get_string('actions', 'mod_emarking');
if(has_capability("mod/emarking:supervisegrading", $context)) {
    $actionsheader .= $usercangrade ? '&nbsp;<input type="checkbox" id="select_all" title="' . get_string('selectall', 'mod_emarking') . '">' : '';
}

$headers = array();
$headers[] = get_string('names', 'mod_emarking');
if ($emarking->type == 2)
    $headers[] = get_string('marker', 'mod_emarking');
$headers[] = get_string('status', 'mod_emarking');
$headers[] = get_string('grade', 'mod_emarking');
$headers[] = get_string('lastmodification', 'mod_emarking');
$headers[] = $actionsheader;

$columns = array();
$columns[] = 'lastname';
if ($emarking->type == 2)
    $columns[] = 'marker';
$columns[] = 'status';
$columns[] = 'grade';
$columns[] = 'timemodified';
$columns[] = 'actions';

// Define flexible table (can be sorted in different ways)
$showpages = new flexible_table('emarking-view-' . $cmid);
$showpages->define_headers($headers);
$showpages->define_columns($columns);
$showpages->define_baseurl($urlemarking);

$defaulttsort = $emarking->anonymous < 2 ? null : 'lastname';
$showpages->sortable(true, $defaulttsort, SORT_ASC);
if ($emarking->anonymous < 2) {
    $showpages->no_sorting('lastname');
}
$showpages->no_sorting('comment');
$showpages->no_sorting('actions');
$showpages->pageable(true);
$showpages->pagesize($perpage, $totalstudents);
$showpages->setup();

// Decide on sorting depending on URL parameters and flexible table configuration
$orderby = $emarking->anonymous < 2 ? 'ORDER BY sort ASC' : 'ORDER BY u.lastname ASC';
if ($showpages->get_sql_sort()) {
    $orderby = 'ORDER BY ' . $showpages->get_sql_sort();
    $tsort = $showpages->get_sql_sort();
}

// Get submissions with extra info to show
$sqlstudents .= $orderby;
$sqldrafts .= $orderby;

if ($emarking->type == EMARKING_TYPE_NORMAL) {
    $params = array(
        $course->id,
        $emarking->id,
        $USER->id
    );
    // Run the query on the database
    $drafts = $DB->get_recordset_sql($sqlstudents, $params, $page * $perpage, $perpage);
} else {
    // Run the query on the database
    $drafts = $DB->get_recordset_sql($sqldrafts, array(
        $emarking->id,
        $emarking->id,
        $USER->id
    ), $page * $perpage, $perpage);
}

$unpublishedsubmissions = 0;
// Prepare data for the table
foreach ($drafts as $draft) {
    
    // Student info
    $profileurl = new moodle_url('/user/view.php', array(
        'id' => $draft->id,
        'course' => $course->id
    ));
    
    if ($emarking->type == EMARKING_TYPE_NORMAL) {
        $userinfo = $emarking->anonymous < 2 && $USER->id != $draft->id ? get_string('anonymousstudent', 'mod_emarking') : $OUTPUT->user_picture($draft) . '&nbsp;<a href="' . $profileurl . '">' . $draft->firstname . ' ' . $draft->lastname . '</a>';
    } else {
        $userinfo = get_string('exam', 'mod_emarking') . ' ' . $draft->submission;
    }
    
    $draftids = explode('#', $draft->draft);
    $draftqcs = explode('#', $draft->qcs);
    $draftstatuses = explode('#', $draft->status);
    $draftspages = explode('#', $draft->pages);
    $draftscomments = explode('#', $draft->comments);
    $draftspctmarked = explode('#', $draft->pctmarked);
    $draftspctmarkeduser = explode('#', $draft->pctmarkeduser);
    $draftscriteriaids = explode('#', $draft->criteriaids);
    $draftscriteriascores = explode('#', $draft->criteriascores);
    $draftsregrades = explode('#', $draft->regrades);
    $draftsbonus = explode('#', $draft->bonus);
    $draftsgrade = explode('#', $draft->grade);
    $draftsscore = explode('#', $draft->score);

    // Status and marking progress
    $pctmarked = '';
    $current = 0;
    foreach ($draftids as $d) {
        
        $pages = intval($draftspages[$current]);
        $status = $draftqcs[$current]== 0 ? 
            emarking_get_submission_icon_status($draftstatuses[$current]) : 
            $OUTPUT->pix_icon('i/completion-auto-y', get_string("qualitycontrol", "mod_emarking"));
        
        // Add warning icon if there are missing pages in draft
        if ($emarking->totalpages > 0 
            && $emarking->totalpages > $pages 
            && $draftstatuses[$current] > EMARKING_STATUS_MISSING) {
            $status .= $OUTPUT->pix_icon('i/risk_xss', get_string('missingpages', 'mod_emarking'));
        }
        // Completion matrix
        $matrix = '';
        $markedcriteria = explode(",", $draftscriteriaids[$current]);
        $markedcriteriascores = explode(",", $draftscriteriascores[$current]);
        if (count($markedcriteria) > 0 && $numcriteria > 0) {
            $matrix = "<div id='sub-$d' style='display:none;' title='$draft->firstname $draft->lastname '>
		<table width='100%'>";
            $matrix .= "<tr><th>" . get_string('criterion', 'mod_emarking') . "</th><th style='text-align:center'>" . get_string('corrected', 'mod_emarking') . "</th></tr>";
            foreach ($rubriccriteria->rubric_criteria as $criterion) {
                $matrix .= "<tr><td>" . $criterion['description'] . "</td><td style='text-align:center'>";
                $key = array_search($criterion['id'], $markedcriteria);
                if ($key !== false) {
                    $matrix .= $OUTPUT->pix_icon('i/completion-manual-y', round($markedcriteriascores[$key], 1) . "pts");
                } else {
                    $matrix .= $OUTPUT->pix_icon('i/completion-manual-n', null);
                }
                $matrix .= "</td></tr>";
            }
            $matrix .= "</table></div>";
        }
        // Percentage of criteria already marked for this draft
        $pctmarkedtitle = ($numcriteria - $draftscomments[$current]) . " pending criteria";
        $matrixlink = "<a style='cursor:pointer;' onclick='$(\"#sub-$d\").dialog({modal:true,buttons:{Ok: function(){\$(this).dialog(\"close\");}}});'>" . ($numcriteriauser > 0 ? $draftspctmarkeduser[$current] . "% / " : '') . $draftspctmarked[$current] . "%" . ($draftsregrades[$current] > 0 ? '<br/>' . $draftsregrades[$current] . ' ' . get_string('regradespending', 'mod_emarking') : '') . "</a>" . $matrix;
        if($draftstatuses[$current] > EMARKING_STATUS_MISSING) {
            $pctmarked .= $OUTPUT->box($status . '&nbsp;' . $matrixlink, 'generalbox', null, array(
                'title' => $pctmarkedtitle
            ));
        } else {
            $pctmarked .= $OUTPUT->box($status, 'generalbox', null, array(
                'title' => $pctmarkedtitle
            ));
        }
        $current ++;
    }
    
    $finalgrade = '';
    $current = 0;
    foreach ($draftids as $d) {
        // Grade
        $bonusinfo = $draftsbonus[$current] != 0 ? round($draftsbonus[$current], 2) . " " : ' ';
        $bonusinfo = ($draftsbonus[$current] > 0 ? '+' : '') . $bonusinfo;
        $gradevalue = round(floatval($draftsgrade[$current]), 2);
        $thisfinalgrade = '-';
        if ($draftstatuses[$current] >= EMARKING_STATUS_GRADING) {
            $thisfinalgrade = $gradevalue;
            if ($draftstatuses[$current] >= EMARKING_STATUS_PUBLISHED) {
                $thisfinalgrade = '<strong>' . $thisfinalgrade . '</strong>';
            }
        } else if($draftstatuses[$current] <= EMARKING_STATUS_MISSING) {
            $thisfinalgrade = "";
        }
        $finalgrade .= $OUTPUT->box($thisfinalgrade, 'generalbox', null, array(
            'title' => round($draftsscore[$current], 2) . $bonusinfo . get_string('of', 'mod_emarking') . " " . $rubricscores['maxscore'] . " " . get_string('points', 'grades')
        ));
        $current ++;
    }
    
    
    // Action buttons
    $actions = "";
    
    $current = 0;
    foreach ($draftids as $d) {
        
        // Action buttons
        $actions .= html_writer::start_div('printactions');
        $thisstatus = $draftstatuses[$current];
        $thisid = $d;
        
        // eMarking popup url
        $popup_url = new moodle_url('/mod/emarking/marking/index.php', array(
            'id' => $thisid
        ));
        
        // eMarking button
        if (($usercangrade && $thisstatus >= EMARKING_STATUS_SUBMITTED && $numcriteria > 0) || $thisstatus >= EMARKING_STATUS_PUBLISHED) {
            $pixicon = $usercangrade ? new pix_icon('i/manual_item', get_string('annotatesubmission', 'mod_emarking')) : new pix_icon('i/preview', get_string('viewsubmission', 'mod_emarking'));
            $actions .= html_writer::div($OUTPUT->action_link($popup_url, null, 
                new popup_action('click', $popup_url, 'emarking' . $thisid, array(
                'menubar' => 'no',
                'titlebar' => 'no',
                'status' => 'no',
                'toolbar' => 'no',
                'width' => 860,
                'height' => 600
            )), null, $pixicon));
        }
        
        // Mark draft as absent/sent
        if ($emarking->type == EMARKING_TYPE_NORMAL && $draftqcs[$current] == 0 && (is_siteadmin($USER) || ($issupervisor && $usercangrade)) && $thisstatus > EMARKING_STATUS_MISSING) {
            
            $newstatus = $thisstatus >= EMARKING_STATUS_SUBMITTED ? EMARKING_STATUS_ABSENT : EMARKING_STATUS_SUBMITTED;
            
            $deletesubmissionurl = new moodle_url('/mod/emarking/marking/updatesubmission.php', array(
                'ids' => $thisid,
                'cm' => $cm->id,
                'status' => $newstatus
            ));
            
            $pixicon = $thisstatus >= EMARKING_STATUS_SUBMITTED ? new pix_icon('t/delete', get_string('setasabsent', 'mod_emarking')) : new pix_icon('i/checkpermissions', get_string('setassubmitted', 'mod_emarking'));
            
            $actions .= html_writer::div($OUTPUT->action_link($deletesubmissionurl, null, null, null, $pixicon));
        }
        
        // Url for downloading PDF feedback
        $responseurl = new moodle_url('/pluginfile.php/' . $context->id . '/mod_emarking/response/' . $draft->id . '/response_' . $emarking->id . '_' . $thisid . '.pdf');
        
        // Download PDF button
        if ($emarking->type == EMARKING_TYPE_NORMAL && $thisstatus >= EMARKING_STATUS_PUBLISHED && $draftqcs[$current] == 0 && ($thisid == $USER->id || is_siteadmin($USER) || $issupervisor)) {
            $actions .= html_writer::div($OUTPUT->action_link($responseurl, null, null, null, new pix_icon('f/pdf', get_string('downloadfeedback', 'mod_emarking'))));
        }
        
        // Checkbox for publishing grade
        if ($emarking->type == EMARKING_TYPE_NORMAL && $draftqcs[$current] == 0 
            && $thisstatus >= EMARKING_STATUS_SUBMITTED && $thisstatus < EMARKING_STATUS_PUBLISHED && $usercangrade
            && has_capability("mod/emarking:supervisegrading", $context)) {
            $unpublishedsubmissions ++;
            $actions .= html_writer::div('<input type="checkbox" name="publish[]" value="' . $thisid . '">');
        }
        
        $actions .= html_writer::end_div();
        $current ++;
    }
    
    
    // Feedback
    $feedbacks = explode('#', $draft->feedback);
    $feedback = '';
    foreach ($feedbacks as $f) {
        $feedback = strlen($f) > 0 ? $f : '';
    }
    
    // Last modified
    $timesmodified = explode('#', $draft->timemodified);
    $timemodified = '';
    foreach ($timesmodified as $t) {
        $timemodified .= html_writer::start_div();
        $timemodified .= $t > 0 ? core_text::strtolower(emarking_time_ago($t)) : '';
        $timemodified .= html_writer::end_div();
    }
    // If there's a draft show total pages
    if ($draft->status >= EMARKING_STATUS_SUBMITTED) {
        $totalpages = $emarking->totalpages > 0 ? ' / ' . $emarking->totalpages . ' ' : ' ';
    }
    
    // Markers pictures
    if ($emarking->type == EMARKING_TYPE_MARKER_TRAINING) {
        $markersids = explode('#', $draft->markerid);
        $markersstring = '';
        foreach ($markersids as $mids) {
            foreach ($markers as $marker) {
                if ($mids == $marker->id) {
                    $markersstring .= $OUTPUT->user_picture($marker) . '&nbsp;';
                }
            }
        }
    }
    
    $data = array();
    $data[] = $userinfo;
    if ($emarking->type == EMARKING_TYPE_MARKER_TRAINING) {
        $data[] = $markersstring;
    }
    $data[] = $pctmarked;
    $data[] = $finalgrade;
    $data[] = $timemodified;
    $data[] = $actions;
    $showpages->add_data($data);
}

?>
<style>
.scol, .generaltable td {
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

$showpages->print_html();

if ($usercangrade && $unpublishedsubmissions > 0) {
    echo "<input style='float:right;' type='submit' onclick='return validatePublish();' value='" . get_string('publishselectededgrades', 'mod_emarking') . "'>";
} else 
    if ($unpublishedsubmissions == 0) {
        echo "<script>$('#select_all').hide();</script>";
    }
echo "</form>";

// If the user can not grade, we show them
if (! $usercangrade) {
    require_once $CFG->dirroot . '/mod/emarking/forms/justice_form.php';
    
    $submission = $DB->get_record('emarking_submission', array(
        'emarking' => $emarking->id,
        'student' => $USER->id
    ));
    $record = $submission ? $DB->get_record('emarking_perception', array(
        "submission" => $submission->id
    )) : null;
    
    $mform = new justice_form($urlemarking, null, 'post');
    $mform->set_data($record);
    if ($mform->get_data()) {
        if (! $record) {
            $record = new stdClass();
        }
        $record->submission = $submission->id;
        $record->overall_fairness = $mform->get_data()->overall_fairness;
        $record->expectation_reality = $mform->get_data()->expectation_reality;
        $record->timecreated = time();
        if (isset($record->id)) {
            $DB->update_record('emarking_perception', $record);
        } else {
            $record->id = $DB->insert_record('emarking_perception', $record);
        }
        echo $OUTPUT->notification(get_string('thanksforjusticeperception', 'mod_emarking'), 'notifysuccess');
    }
    $mform->display();
}
echo $OUTPUT->footer();