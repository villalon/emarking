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
 * @copyright 2012-2015 Jorge Villalon <jorge.villalon@uai.cl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot.'/mod/emarking/locallib.php');
require_once $CFG->dirroot.'/mod/emarking/forms/regrade_form.php';
require_once($CFG->dirroot.'/grade/grading/lib.php');

global $CFG,$OUTPUT, $PAGE, $DB;

$cmid = required_param('id', PARAM_INT);
$criterionid = optional_param('criterion',null,PARAM_INT);
$delete = optional_param('delete',false,PARAM_BOOL);

if(!$cm = get_coursemodule_from_id('emarking', $cmid)) {
	error('Invalid cm id');
}

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
	error('You must specify a valid course ID');
}

require_login($course, true);
if(isguestuser()) {
	die();
}

if(!$emarking = $DB->get_record('emarking', array('id'=>$cm->instance))) {
	error('You must specify a valid course module ID');
}

if($emarking->type != EMARKING_TYPE_NORMAL) {
    error('You can only have regrades in a normal emarking type');
}

if(!$gradeitemobj = $DB->get_record('grade_items', array('itemtype'=>'mod','itemmodule'=>'emarking','iteminstance'=>$cm->instance))) {
	error('You must specify a valid course module ID');
}

if($criterionid && !$criterion= $DB->get_record('gradingform_rubric_criteria',array('id'=>$criterionid))){
	print_error("No criterion");
}

$regrade = null;
if($criterionid) {
		if(!$emarkingsubmission = $DB->get_record('emarking_submission', array('emarking'=>$emarking->id, 'student'=>$USER->id))) {
			print_error('Fatal error! Couldn\'t find emarking submission');
		}
        if(!$emarkingdraft = $DB->get_record('emarking_draft', array('emarkingid'=>$emarking->id, 'submissionid'=>$emarkingsubmission->id))) {
			print_error('Fatal error! Couldn\'t find emarking draft');
		}
		$regrade = $DB->get_record('emarking_regrade',
			array('draft'=>$emarkingdraft->id,
					'criterion'=>$criterionid));
}

$gradeitem = $gradeitemobj->id;

$context = context_module::instance($cm->id);

$url = new moodle_url('/mod/emarking/marking/regrades.php', array('id'=>$cm->id,'criterion'=>$criterionid));
$cancelurl = new moodle_url('/mod/emarking/marking/regrades.php', array('id'=>$cm->id));

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking','mod_emarking'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_url($url);

$requestswithindate = emarking_is_regrade_requests_allowed($emarking);

if($criterionid && !$delete && $requestswithindate) {
	$mform = new emarking_justice_regrade_form($url, array("criterion"=>$criterion));

	if($regrade)
		$mform->set_data($regrade);

	if ($mform->is_cancelled()) {
		redirect($cancelurl);
	} else if ($data = $mform->get_data()) {
		$data->studentid = $USER->id;
		$data->moduleid = $cm->id;
		$data->modulename = 'emarking';

		if(!$regrade) {
			$regrade = new stdClass();
			$regrade->timecreated = time();
		}
		$regrade->student = $USER->id;
		$regrade->draft = $emarkingdraft->id;
		$regrade->motive = $data->motive;
		$regrade->comment = $data->comment;
		$regrade->criterion = $criterionid;
		$regrade->timemodified = time();

		if(isset($regrade->id)) {
			$DB->update_record('emarking_regrade', $regrade);
		} else {
			$regradeid = $DB->insert_record('emarking_regrade', $regrade);
			$regrade->id = $regradeid;
		}

		$emarkingsubmission->status = EMARKING_STATUS_REGRADING;
		$DB->update_record('emarking_submission', $emarkingsubmission);

		$successmessage = get_string('saved','mod_emarking');

	} else {
		//Form processing and displaying is done here
		echo $OUTPUT->header();
		echo $OUTPUT->heading(get_string('regrades','mod_emarking'));
		echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking),'regrade');

		$mform->display();
		echo $OUTPUT->footer();
		die();
	}
}

if($regrade && $delete && $requestswithindate) {
	$DB->delete_records('emarking_regrade', array(
			'draft'=>$emarkingdraft->id,
			'criterion'=>$criterionid));
	$successmessage=get_string('saved','mod_emarking');
}


// Get the grading manager, then method and finally controller
$gradingmanager = get_grading_manager($context, 'mod_emarking', 'attempt');
$gradingmethod = $gradingmanager->get_active_method();
$rubriccontroller = $gradingmanager->get_controller($gradingmethod);
$definition = $rubriccontroller->get_definition();

$query = "SELECT
                a.id AS id,
                a.description AS description,
                round(b.score + comment.bonus,2) AS score,
                round(T.maxscore,2) AS maxscore,
                comment.rawtext AS feedback,
                rg.id AS regradeid,
                rg.markercomment AS markercomment,
                rg.accepted AS rgaccepted,
                rg.motive,
                rg.comment,
                comment.bonus
                FROM {emarking_submission}  AS s
                INNER JOIN {emarking_draft} AS dr ON (s.emarking = :emarkingid AND dr.submissionid = s.id)
                INNER JOIN {user}  AS u on (s.student = :userid AND s.student = u.id)
                INNER JOIN {emarking_page} AS page ON (page.submission = s.id)
                INNER JOIN {emarking_comment} AS comment ON (comment.page = page.id AND comment.draft = dr.id)
                INNER JOIN {gradingform_rubric_levels}  AS b on (b.id = comment.levelid)
                INNER JOIN {gradingform_rubric_criteria}  AS a on (a.id = b.criterionid)
                INNER JOIN (
                        SELECT
                        s.id AS emarkingid,
                        a.id AS criterionid,
                        MAX(l.score) AS maxscore
                        FROM {emarking} AS s
                        INNER JOIN {course_modules}  AS cm on (s.id = :emarkingid2 AND s.id = cm.instance)
                        INNER JOIN {context}  AS c on (c.instanceid = cm.id)
                        INNER JOIN {grading_areas}  AS ar on (ar.contextid = c.id)
                        INNER JOIN {grading_definitions}  AS d on (ar.id = d.areaid)
                        INNER JOIN {gradingform_rubric_criteria}  AS a on (d.id = a.definitionid)
                        INNER JOIN {gradingform_rubric_levels}  AS l on (a.id = l.criterionid)
                        GROUP BY s.id, criterionid
                ) AS T ON (s.emarking = T.emarkingid AND T.criterionid = b.criterionid)
                INNER JOIN {emarking}  AS sg ON (s.emarking = sg.id)
                INNER JOIN {course}  AS co ON (sg.course = co.id)
                LEFT JOIN {emarking_regrade} AS rg ON (rg.draft = dr.id AND a.id = rg.criterion)
                ORDER BY s.student,a.description";

$questions = $DB->get_records_sql($query,
		array('userid'=>$USER->id, 'emarkingid'=>$emarking->id, 'definition'=>$definition->id, 'emarkingid2'=>$emarking->id));

$table = new html_table();
$table->head = array(
		get_string('criterion', 'mod_emarking'),
		get_string('score', 'mod_emarking'),
		get_string('markingcomment', 'mod_emarking'),
		get_string('status', 'mod_emarking'),
		get_string('regradingcomment', 'mod_emarking'),
		get_string('actions', 'mod_emarking')
);
$data = array();
foreach($questions as $question){

	$urledit = new moodle_url('/mod/emarking/marking/regrades.php',array("id"=>$cm->id,"criterion"=>$question->id));
	$urldelete = new moodle_url('/mod/emarking/marking/regrades.php',array("id"=>$cm->id,"criterion"=>$question->id,'delete'=>'true'));

	$status = get_string("statusnotsent", "mod_emarking");
	if($question->regradeid!=null) {
		if($requestswithindate) {
			$linktext = $OUTPUT->action_link($urledit, null, null, null, new pix_icon('i/manual_item', get_string('edit')));
			$linktext .= '&nbsp;' . $OUTPUT->action_link($urldelete, null, null, null, new pix_icon('t/delete', get_string('delete')));
		} else {
			$linktext = '&nbsp;';
		}
		$status = $question->rgaccepted ? get_string("statusaccepted", "mod_emarking") : get_string("statussubmitted", "mod_emarking");
		$status .= '<br/>'. emarking_get_regrade_type_string($question->motive);
		$status .= '<br/>'. substr($question->comment, 0 , min(strlen($question->comment), 25));
		if(strlen($question->comment) > 25)
			$status .= '...';
	} elseif($requestswithindate) {
		$linktext = $OUTPUT->action_link($urledit, null, null, null, new pix_icon('t/add', 'Solicitar'));
	} else {
		$linktext = '&nbsp;';
	}

	$row = array();

	$row[] = $question->description;
	$row[] = round($question->score, 2).' / '. round($question->maxscore, 2);
	$row[] = $question->feedback;
	$row[] = $status;
	$row[] = $question->markercomment;
	$row[] = $linktext;

	$data[] = $row;
}
$table->data = $data;

//Form processing and displaying is done here
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking),'regrade');
if($criterionid)
	echo $OUTPUT->notification($successmessage,'notifysuccess');

$data = new stdClass();
$data->regradesclosedate = userdate($emarking->regradesclosedate);
if(!$requestswithindate)
	echo $OUTPUT->notification(get_string('regraderestricted', 'mod_emarking', $data),'notifyproblem');

echo html_writer::table($table);

echo $OUTPUT->footer();
