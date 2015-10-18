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
 * @copyright 2012-2015 Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once $CFG->dirroot . '/mod/emarking/forms/regrade_form.php';
require_once ($CFG->dirroot . '/grade/grading/lib.php');

global $CFG, $OUTPUT, $PAGE, $DB;

$cmid = required_param('id', PARAM_INT);
$criterionid = optional_param('criterion', null, PARAM_INT);
$delete = optional_param('delete', false, PARAM_BOOL);

if (! $cm = get_coursemodule_from_id('emarking', $cmid)) {
    print_error('Invalid cm id');
}

if (! $course = $DB->get_record('course', array(
    'id' => $cm->course
))) {
    print_error('You must specify a valid course ID');
}

require_login($course, true);
if (isguestuser()) {
    die();
}

if (! $emarking = $DB->get_record('emarking', array(
    'id' => $cm->instance
))) {
    print_error('You must specify a valid course module ID');
}

if ($emarking->type != EMARKING_TYPE_NORMAL) {
    print_error('You can only have regrades in a normal emarking type');
}

if (! $gradeitemobj = $DB->get_record('grade_items', array(
    'itemtype' => 'mod',
    'itemmodule' => 'emarking',
    'iteminstance' => $cm->instance
))) {
    print_error('You must specify a valid course module ID');
}

if ($criterionid && ! $criterion = $DB->get_record('gradingform_rubric_criteria', array(
    'id' => $criterionid
))) {
    print_error("No criterion");
}

$gradeitem = $gradeitemobj->id;

$context = context_module::instance($cm->id);

// Check if user has an editingteacher role
$issupervisor = has_capability('mod/emarking:supervisegrading', $context);
$usercangrade = has_capability('mod/assign:grade', $context);

$url = new moodle_url('/mod/emarking/marking/regrades.php', array(
    'id' => $cm->id,
    'criterion' => $criterionid
));
$cancelurl = new moodle_url('/mod/emarking/marking/regrades.php', array(
    'id' => $cm->id
));

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_url($url);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), 'regrade');

if (! $emarkingsubmission = $DB->get_record('emarking_submission', array(
    'emarking' => $emarking->id,
    'student' => $USER->id
))) {
    echo $OUTPUT->notification(get_string('examnotavailable', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}

if (! $emarkingdraft = $DB->get_record('emarking_draft', array(
    'emarkingid' => $emarking->id,
    'submissionid' => $emarkingsubmission->id,
    'qualitycontrol' => 0
))) {
    print_error('Fatal error! Couldn\'t find emarking draft');
}

if(!$emarkingsubmission->seenbystudent) {
    echo $OUTPUT->notification(get_string('mustseeexambeforeregrade', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}

$regrade = null;
$emarkingcomment = null;
if ($criterionid) {
    $regrade = $DB->get_record('emarking_regrade', array(
        'draft' => $emarkingdraft->id,
        'criterion' => $criterionid
    ));
    $emarkingcomment = $DB->get_record_sql('
		SELECT ec.*
		FROM {emarking_comment} AS ec
		WHERE ec.levelid in (
            SELECT id FROM {gradingform_rubric_levels} as l
            WHERE l.criterionid = :criterionid) AND ec.draft = :draft',
        array('criterionid'=>$criterionid, 'draft'=>$emarkingdraft->id));
}

$requestswithindate = emarking_is_regrade_requests_allowed($emarking);

if ($criterionid && ! $delete && $requestswithindate) {
    $mform = new emarking_justice_regrade_form($url, array(
        "criterion" => $criterion
    ));
    
    if ($regrade)
        $mform->set_data($regrade);
    
    if ($mform->is_cancelled()) {
        redirect($cancelurl);
    } else 
        if ($data = $mform->get_data()) {
            
            if (! $requestswithindate) {
                print_error('Fatal error! Requesting regrade outside allowed dates');
            }
            $data->studentid = $USER->id;
            $data->moduleid = $cm->id;
            $data->modulename = 'emarking';
            
            if (! $regrade) {
                $regrade = new stdClass();
                $regrade->timecreated = time();
                if($emarkingcomment) {
                    $regrade->levelid = $emarkingcomment->levelid;
                    $regrade->markerid = $emarkingcomment->markerid;
                    $regrade->bonus = $emarkingcomment->bonus;
                }
            
            }
            $regrade->student = $USER->id;
            $regrade->draft = $emarkingdraft->id;
            $regrade->motive = $data->motive;
            $regrade->comment = $data->comment;
            $regrade->criterion = $criterionid;
            $regrade->timemodified = time();
            
            if (isset($regrade->id)) {
                $DB->update_record('emarking_regrade', $regrade);
            } else {
                $regradeid = $DB->insert_record('emarking_regrade', $regrade);
                $regrade->id = $regradeid;
            }
            
            $emarkingsubmission->status = EMARKING_STATUS_REGRADING;
            $DB->update_record('emarking_submission', $emarkingsubmission);
            
            $successmessage = get_string('saved', 'mod_emarking');
        } else {
            // Form processing and displaying is done here
            $mform->display();
            echo $OUTPUT->footer();
            die();
        }
}

if ($regrade && $delete && $requestswithindate) {
    $DB->delete_records('emarking_regrade', array(
        'draft' => $emarkingdraft->id,
        'criterion' => $criterionid
    ));
    $successmessage = get_string('saved', 'mod_emarking');
}

// Get the grading manager, then method and finally controller
$gradingmanager = get_grading_manager($context, 'mod_emarking', 'attempt');
$gradingmethod = $gradingmanager->get_active_method();
$rubriccontroller = $gradingmanager->get_controller($gradingmethod);
$definition = $rubriccontroller->get_definition();

// Get the grading instance we should already have
$gradinginstancerecord = $DB->get_record ( 'grading_instances', array (
    'itemid' => $emarkingdraft->id,
    'definitionid' => $definition->id
) );

$query = "select
		c.id AS id,
		c.description AS description,
        CASE WHEN b.score is null AND comment.bonus is null THEN T.minscore
            WHEN b.score is null THEN round(T.minscore + comment.bonus,2)
            WHEN comment.bonus is null THEN round(b.score,2)
			ELSE  round(b.score + comment.bonus,2) END
        AS score,
		round(T.maxscore,2) AS maxscore,
        round(T.minscore,2) AS minscore,
		rf.remark AS feedback,
		rg.id AS regradeid,
		rg.markercomment AS markercomment,
		rg.accepted AS rgaccepted,
		rg.motive,
		rg.comment,
		comment.bonus,
        ol.score as originalscore,
        rg.bonus as originalbonus,
        ol.definition as originaldefinition,
        b.score as currentscore,
        b.definition as currentdefinition 
from mdl_gradingform_rubric_criteria AS c
inner join mdl_emarking_submission  AS s ON (s.emarking = :emarkingid AND s.student = :userid AND c.definitionid = :definitionid)
INNER JOIN mdl_emarking_draft AS dr ON (dr.submissionid = s.id AND dr.qualitycontrol=0)
INNER JOIN mdl_user  AS u on (s.student = u.id)
		INNER JOIN (
			SELECT
			s.id AS emarkingid,
			a.id AS criterionid,
			MAX(l.score) AS maxscore,
            MIN(l.score) AS minscore
			FROM mdl_emarking AS s
			INNER JOIN mdl_course_modules  AS cm on (s.id = 5 AND s.id = cm.instance)
			INNER JOIN mdl_context  AS c on (c.instanceid = cm.id)
			INNER JOIN mdl_grading_areas  AS ar on (ar.contextid = c.id)
			INNER JOIN mdl_grading_definitions  AS d on (ar.id = d.areaid)
			INNER JOIN mdl_gradingform_rubric_criteria  AS a on (d.id = a.definitionid)
			INNER JOIN mdl_gradingform_rubric_levels  AS l on (a.id = l.criterionid)
			GROUP BY s.id, criterionid
		) AS T ON (s.emarking = T.emarkingid AND T.criterionid = c.id)
		INNER JOIN mdl_emarking  AS sg ON (s.emarking = sg.id)
		INNER JOIN mdl_course  AS co ON (sg.course = co.id)
        LEFT JOIN mdl_gradingform_rubric_fillings as rf ON (rf.criterionid = c.id AND rf.instanceid = 24)
        LEFT JOIN mdl_gradingform_rubric_levels as b ON (b.criterionid = c.id AND b.id = rf.levelid)
		LEFT JOIN mdl_emarking_comment AS comment ON (comment.draft = dr.id AND comment.levelid = b.id)
		LEFT JOIN mdl_emarking_page AS page ON (page.submission = s.id AND comment.page = page.id)
		LEFT JOIN mdl_emarking_regrade AS rg ON (rg.draft = dr.id AND c.id = rg.criterion)
        LEFT JOIN mdl_gradingform_rubric_levels AS ol on (ol.id = rg.levelid)
        ORDER BY s.student,c.sortorder";

$questions = $DB->get_records_sql($query, array(
    'emarkingid' => $emarking->id,
    'userid' => $USER->id,
    'definitionid' => $definition->id,
));

$table = new html_table();
$table->head = array(
    get_string('criterion', 'mod_emarking'),
    get_string('marking', 'mod_emarking'),
    get_string('regraderequest', 'mod_emarking'),
    get_string('regrade', 'mod_emarking'),
    get_string('actions', 'mod_emarking')
);
$data = array();
foreach ($questions as $question) {
    
    $urledit = new moodle_url('/mod/emarking/marking/regrades.php', array(
        "id" => $cm->id,
        "criterion" => $question->id
    ));
    $urldelete = new moodle_url('/mod/emarking/marking/regrades.php', array(
        "id" => $cm->id,
        "criterion" => $question->id,
        'delete' => 'true'
    ));
    
    $linktext = "";
    $statusicon = get_string("statusnotsent", "mod_emarking");
    if ($question->regradeid != null) {
        if ($requestswithindate && ! $question->rgaccepted) {
            $linktext = $OUTPUT->action_link($urledit, null, null, null, new pix_icon('i/manual_item', get_string('edit')));
            $linktext .= '&nbsp;' . $OUTPUT->action_link($urldelete, null, null, null, new pix_icon('t/delete', get_string('delete')));
        } else {
            $linktext = '&nbsp;';
        }
        if ($question->rgaccepted) {
            $statusicon = $OUTPUT->pix_icon("i/valid", get_string('replied', 'mod_emarking'));
        } else {
            $statusicon = $OUTPUT->pix_icon("i/flagged", get_string('sent', 'mod_emarking'));
        }
        $statusicon .= '<br/>' . emarking_get_regrade_type_string($question->motive);
        $statusicon .= '<br/>' . emarking_view_more(get_string("regradingcomment", "mod_emarking"), $question->comment, "cc", $question->regradeid);
    } elseif ($requestswithindate && $emarkingdraft->status >= EMARKING_STATUS_PUBLISHED) {
        $linktext = $OUTPUT->action_link($urledit, null, null, null, new pix_icon('t/add', 'Solicitar'));
    } else {
        $linktext = '&nbsp;';
    }
    
    $originalinfo = round($question->originalscore, 2) . ' / ' . round($question->maxscore, 2) . ' : ' . $question->originaldefinition;
    if($question->feedback && core_text::strlen($question->feedback) > 0) {
        $originalinfo .= '<br/>' . $question->feedback;
    }
    $currentinfo = round($question->score, 2) . ' / ' . round($question->maxscore, 2) . ' : ' . $question->currentdefinition;
    if($question->markercomment && core_text::strlen($question->markercomment) > 0) {
        $currentinfo .= '<br/>' . $question->markercomment;
    }
    
    $row = array();
    
    $row[] = $question->description;
    $row[] = $question->rgaccepted ? $originalinfo : $currentinfo;
    $row[] = $statusicon;
    $row[] = $question->rgaccepted ? $currentinfo : '';
    $row[] = $linktext;
    
    $data[] = $row;
}
$table->data = $data;

// Form processing and displaying is done here
if ($criterionid)
    echo $OUTPUT->notification($successmessage, 'notifysuccess');

$data = new stdClass();
$data->regradesclosedate = userdate($emarking->regradesclosedate);
if (! $requestswithindate) {
    echo $OUTPUT->notification(get_string('regraderestricted', 'mod_emarking', $data), 'notifyproblem');
}

echo html_writer::table($table);

echo $OUTPUT->footer();
