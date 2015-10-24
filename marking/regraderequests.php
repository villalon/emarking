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

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
global $CFG,$OUTPUT, $PAGE, $DB;//To suppress eclipse warnings
require_once($CFG->dirroot.'/mod/emarking/locallib.php');

$cmid = required_param('id', PARAM_INT);

if(!$cm = get_coursemodule_from_id('emarking',$cmid)) {
	error('Invalid course module id');
}

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    error('You must specify a valid course ID');
}

if(!$emarking = $DB->get_record('emarking', array('id'=>$cm->instance))) {
	error('Invalid emarking id');
}

require_login($course, true);

$context = context_module::instance($cm->id);

require_capability ( 'mod/emarking:grade', $context );
require_capability ( 'mod/emarking:regrade', $context );

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking','mod_emarking'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_url(new moodle_url("/mod/emarking/marking/regraderequests.php?id=$cmid"));
$PAGE->navbar->add(get_string('regrades', 'mod_emarking'));	
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "regrades" );

$sql = "select 
			rg.*,
			u.id AS userid,
			u.firstname,
			u.lastname,
			c.description AS criterion,
			dr.id AS ids,
			dr.status AS status,
            T.maxscore,
            T.minscore,
        CASE WHEN b.score is null AND comment.bonus is null THEN T.minscore
            WHEN b.score is null THEN round(T.minscore + comment.bonus,2)
            WHEN comment.bonus is null THEN round(b.score,2)
			ELSE  round(b.score + comment.bonus,2) END
        AS currentscore,
		round(T.maxscore,2) AS maxscore,
        round(T.minscore,2) AS minscore,
		comment.bonus as currentbonus,
        ol.score as originalscore,
        rg.bonus as originalbonus,
        ol.definition as originaldefinition,
        b.definition as currentdefinition
from mdl_emarking AS e
inner join mdl_emarking_submission  AS s ON (s.emarking = :emarking AND s.emarking = e.id)
INNER JOIN mdl_emarking_draft AS dr ON (dr.submissionid = s.id AND dr.qualitycontrol=0)
INNER JOIN mdl_emarking_regrade AS rg ON (rg.draft = dr.id)
INNER JOIN mdl_gradingform_rubric_levels AS ol on (ol.id = rg.levelid)
INNER JOIN mdl_user  AS u on (s.student = u.id)
INNER JOIN (
			SELECT
			s.id AS emarkingid,
			a.id AS criterionid,
			MAX(l.score) AS maxscore,
            MIN(l.score) AS minscore
			FROM mdl_emarking AS s
			INNER JOIN mdl_course_modules  AS cm on (s.id = :emarkingid2 AND s.id = cm.instance)
			INNER JOIN mdl_context  AS c on (c.instanceid = cm.id)
			INNER JOIN mdl_grading_areas  AS ar on (ar.contextid = c.id)
			INNER JOIN mdl_grading_definitions  AS d on (ar.id = d.areaid)
			INNER JOIN mdl_gradingform_rubric_criteria  AS a on (d.id = a.definitionid)
			INNER JOIN mdl_gradingform_rubric_levels  AS l on (a.id = l.criterionid)
			GROUP BY s.id, criterionid
		) AS T ON (s.emarking = T.emarkingid AND T.criterionid = ol.criterionid)
INNER JOIN mdl_course  AS co ON (e.course = co.id)
INNER JOIN mdl_gradingform_rubric_criteria AS c on (ol.criterionid = c.id)
LEFT JOIN mdl_emarking_comment AS comment ON (comment.draft = dr.id AND comment.criterionid = c.id AND comment.levelid > 0)
LEFT JOIN mdl_gradingform_rubric_levels AS b on (b.id = comment.levelid)
LEFT JOIN mdl_emarking_page AS page ON (page.submission = s.id AND comment.page = page.id)
ORDER BY u.lastname ASC, c.sortorder";
$records = $DB->get_records_sql($sql,array("emarking"=>$emarking->id, "emarkingid2"=>$emarking->id));

if(count($records) == 0) {
	echo $OUTPUT->notification(get_string('noregraderequests', 'mod_emarking'), 'notifyproblem');
	echo $OUTPUT->footer();
	die();
}

$table = new html_table();
$table->head = array(
    get_string('student','grades') . '-' . get_string('criterion', 'mod_emarking'),
    get_string('motive', 'mod_emarking'),
    get_string('grade', 'mod_emarking'),
    get_string('regrade', 'mod_emarking'),
    '&nbsp;'
);

$data = array();
foreach($records as $record) {

    if($record->accepted) {
    	$statusicon = $OUTPUT->pix_icon("i/valid", get_string('replied', 'mod_emarking'));
    } else {
        $statusicon = $OUTPUT->pix_icon("i/flagged", get_string('sent', 'mod_emarking'));
    }

    $regradecomment = emarking_view_more(get_string("justification", "mod_emarking"), $record->comment, "comment", $record->id);
    $motive = emarking_get_regrade_type_string($record->motive) .
        '<br/>' . $regradecomment;
    
    // Student info
    $url = new moodle_url('/user/view.php',array('id'=>$record->userid,'course'=>$course->id));
    $studentcriterion = $OUTPUT->action_link($url, $record->firstname.' '.$record->lastname);
    $studentcriterion .= '<br/>' . $record->criterion;
    $studentcriterion .= '<br/>' .emarking_time_ago($record->timecreated, true);
    
    // Original grade
    $original = ' [' . round($record->originalscore, 2) . '/' . round($record->maxscore, 2) . ']';
    
    // After regrade
    $current = $record->accepted ? 
        '[' . round($record->currentscore, 2) . '/' . round($record->maxscore, 2) . '] '
        : '';
    $current .= '&nbsp;' . $statusicon;
    $current .= $record->accepted ? '<br/>' . $record->markercomment : '';
    $current .= '&nbsp;' .emarking_time_ago($record->timemodified, true);
    
    // Actions
    $urlsub = new moodle_url('/mod/emarking/marking/index.php',array('id'=>$record->ids));
    $actions = $OUTPUT->action_link($urlsub, get_string('annotatesubmission','mod_emarking'),
			new popup_action ( 'click', $urlsub, 'emarking' . $record->ids, array (
								'menubar' => 'no',
								'titlebar' => 'no',
								'status' => 'no',
								'toolbar' => 'no',
			                    'width' => 860,
			                    'height' => 600,
			)), array("class"=>"rowactions"));
    
    $array = array();
    $array[] = $studentcriterion;
    $array[] = $motive;
    $array[] = $original;
    $array[] = $current;
    $array[] = $actions;

    $data[] = $array;
}
$table->data = $data;

echo html_writer::table($table);
echo $OUTPUT->footer();
