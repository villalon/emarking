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
 * Page to show marking tasks in a kanban style
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2014 Jorge Villalón
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once("../lib.php");
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once($CFG->dirroot . "/lib/externallib.php");
global $USER, $OUTPUT, $DB, $CFG, $PAGE;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$criterionid = optional_param('criterion', 0, PARAM_INT);
$criterion = null;
if ($criterionid > 0 && ! $criterion = $DB->get_record('gradingform_rubric_criteria', array(
    'id' => $criterionid))) {
    print_error(get_string('invalidcourseid', 'mod_emarking'));
}
// Get the course module for the emarking, to build the emarking url.
$urlemarking = new moodle_url('/mod/emarking/kanban.php', array(
    'id' => $cm->id,
    'criterion' => $criterionid));
// Check that user is logued in the course.
require_login($course->id);
if (isguestuser()) {
    die();
}
// Check if user has an editingteacher role.
require_capability('mod/emarking:grade', $context);
if (has_capability('mod/emarking:supervisegrading', $context) || is_siteadmin($USER)) {
    $emarking->anonymous = false;
}
// Page navigation and URL settings.
$PAGE->set_url($urlemarking);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_cm($cm);
$PAGE->set_heading($emarking->name);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('emarking', 'mod_emarking'));
// Show header and heading.
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help(get_string('emarking', 'mod_emarking'), 'annotatesubmission', 'mod_emarking');
// Navigation tabs.
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "mark");
list($gradingmanager, $gradingmethod, $definition) = emarking_validate_rubric($context);
// User filter checking capabilities. If user can not grade, then she can not
// see other users.
$userfilter = 'WHERE 1=1 ';
if (! has_capability('mod/emarking:grade', $context)) {
    $userfilter .= 'AND ue.userid = ' . $USER->id;
}
// As we have a rubric we can get the controller.
$rubriccontroller = $gradingmanager->get_controller($gradingmethod);
if (! $rubriccontroller instanceof gradingform_rubric_controller) {
    print_error(get_string('invalidrubric', 'mod_emarking'));
}
// Calculates the number of criteria for this evaluation.
$numcriteria = 0;
$rubricscores = $rubriccontroller->get_min_max_score();
$levels = array();
if ($rubriccriteria = $rubriccontroller->get_definition()) {
    foreach ($rubriccriteria->rubric_criteria as $criterion) {
        if ($criterion ['id'] == $criterionid) {
            foreach ($criterion ['levels'] as $lvl) {
                $levels [] = intval($lvl ['id']);
            }
        }
    }
}
$levels = implode(",", $levels);
// Calculates the number of criteria assigned to current user.
$criteriafilter = "
	SELECT u.*,
		CASE WHEN S.comments = 0 AND S.status < 20 THEN 5
			WHEN S.comments = 0 AND S.status >= 20 THEN 20
			WHEN S.comments > 0 THEN 30 END AS status,
		S.submission
	FROM (SELECT
	s.id as submission,
	s.student,
	COUNT(DISTINCT ec.id) AS comments,
	s.sort,
	s.status
	FROM {emarking_submission} s
	INNER JOIN {emarking_draft} d ON (d.submissionid = s.id AND d.qualitycontrol = 0)
	INNER JOIN {emarking_page} p ON (s.emarking = $emarking->id AND p.submission = s.id)
	LEFT JOIN {emarking_comment} ec ON (ec.page = p.id AND ec.levelid IN ($levels))
	GROUP BY s.id) S
	INNER JOIN {user} u ON (S.student = u.id)";
// Check if activity is configured with separate groups to filter users.
if ($cm->groupmode == SEPARATEGROUPS && $usercangrade && ! is_siteadmin($USER) && ! $useristeacher) {
    $userfilter .= "
AND u.id in (SELECT userid
		FROM {groups_members}
WHERE groupid in (SELECT groupid
FROM {groups_members} gm
INNER JOIN {groups} g on (gm.groupid = g.id)
WHERE gm.userid = $USER->id AND g.courseid = e.courseid))";
}
// Define flexible table (can be sorted in different ways).
$showpages = new flexible_table('emarking-kanban-' . $cm->id);
$showpages->define_headers(
        array(
            get_string('notcorrected', 'mod_emarking'),
            get_string('marking', 'mod_emarking'),
            get_string('corrected', 'mod_emarking')));
$showpages->define_columns(array(
    'notcorrected',
    'marking',
    'corrected'));
$showpages->define_baseurl($urlemarking);
$defaulttsort = $emarking->anonymous ? null : 'status';
$showpages->sortable(false);
$showpages->pageable(false);
$showpages->setup();
// Decide on sorting depending on URL parameters and flexible table configuration.
$orderby = $emarking->anonymous ? 'ORDER BY sort ASC' : 'ORDER BY u.lastname ASC';
// Get submissions with extra info to show.
$sql = $criterionid == 0 ? "
SELECT u.*,
		IFNULL(s.id,0) as submission,
		IFNULL(s.status,0) as status,
		s.sort
FROM {emarking_submission} s
	INNER JOIN {user} u ON (s.emarking = ? AND s.student = u.id)
$userfilter
$orderby" : $criteriafilter . $userfilter . $orderby;
// Run the query on the database.
$emarkingpages = $DB->get_records_sql($sql, array(
    $emarking->id));
$notcorrected = "";
$marking = "";
$corrected = "";
// Prepare data for the table.
foreach ($emarkingpages as $pageinfo) {
    // Student info.
    $userinfo = $emarking->anonymous ? get_string('anonymousstudent', 'mod_emarking') : $pageinfo->firstname . ' ' .
             $pageinfo->lastname . '</a>';
    // EMarking popup url.
    $popupurl = new moodle_url('/mod/emarking/marking/index.php', array(
        'id' => $pageinfo->submission));
    $actions = $OUTPUT->action_link($popupurl, $userinfo,
            new popup_action('click', $popupurl, 'emarking' . $pageinfo->submission,
                    array(
                        'menubar' => 'no',
                        'titlebar' => 'no',
                        'status' => 'no',
                        'toolbar' => 'no')));
    if ($pageinfo->status < EMARKING_STATUS_GRADING) {
        $notcorrected .= $actions . "<br/>";
    } else if ($pageinfo->status == EMARKING_STATUS_GRADING) {
        $marking .= $actions . "<br/>";
    } else {
        $corrected .= $actions . "<br/>";
    }
}
$showpages->add_data(array(
    $notcorrected,
    $marking,
    $corrected));
?>
<style>
.scol, .generaltable td {
	vertical-align: middle;
}
</style>
<?php
$showpages->print_html();
echo $OUTPUT->footer();