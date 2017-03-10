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
 * @package   mod_emarking
 * @copyright 2017 Francisco Ralph fco.ralph@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
require_once ($CFG->dirroot. '/mod/emarking/activities/generos.php');
require_once ($CFG->dirroot. '/mod/emarking/activities/locallib.php');
global $PAGE, $DB, $USER, $CFG;
$activityid = required_param ( 'id', PARAM_INT );

$PAGE->set_context(context_system::instance());
$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php', array('id'=>$activityid));
$PAGE->set_url($url);
$PAGE->set_title('escribiendo');
$teacherroleid = 3;
$logged = false;
$PAGE->set_context ( context_system::instance () );
// Id of the exam to be deleted.


$forkingUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/forking.php', array (
		'id' => $activityid
) );
$editUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/editactivity.php', array (
		'activityid' => $activityid
) );


if (isloggedin ()) {
	$logged = true;
	$courses = enrol_get_all_users_courses ( $USER->id );
	$countcourses = count ( $courses );
	foreach ( $courses as $course ) {
		$context = context_course::instance ( $course->id );
		$roles = get_user_roles ( $context, $USER->id, true );
		foreach ( $roles as $rol ) {
			if ($rol->roleid == $teacherroleid) {
				$asteachercourses [$course->id] = $course->fullname;
			}
		}
	}
}
$activity = $DB->get_record ( 'emarking_activities', array (
		'id' => $activityid
) );
$userobject = $DB->get_record ( 'user', array (
		'id' => $activity->userid
) );

$rubric = $DB->get_records_sql ( "SELECT grl.id,
									 gd.description as des,
									 grc.id as grcid,
									 grl.score,
									 grl.definition,
									 grc.description,
									 grc.sortorder,
									 gd.name as name
							  FROM {gradingform_rubric_levels} as grl,
	 							   {gradingform_rubric_criteria} as grc,
    							   {grading_definitions} as gd
							  WHERE gd.id=? AND grc.definitionid=gd.id AND grc.id=grl.criterionid
							  ORDER BY grcid, grl.id", array (
							  		$activity->rubricid
							  ) );
if(isset($rubric)&& $rubric!=null){
foreach ( $rubric as $data ) {

	$table [$data->description] [$data->definition] = $data->score;
	$rubricdescription = $data->des;
	$rubricname = $data->name;
}
$col = 0;
foreach ( $table as $calc ) {

	$actualcol = sizeof ( $calc );
	if ($col < $actualcol) {
		$col = $actualcol;
	}
}
$row = sizeof ( $table );
}
$oaComplete = explode ( "-", $activity->learningobjectives );
$coursesOA = "";

foreach ( $oaComplete as $oaPerCourse ) {

	$firstSplit = explode ( "[", $oaPerCourse );
	$secondSplit = explode ( "]", $firstSplit [1] );
	$course = $firstSplit [0];

	$coursesOA .= '<span>Curso: ' . $firstSplit [0] . '° básico</span><br>';
	$coursesOA .= '<span>OAs: ' . $secondSplit [0] . '</span><br>';
}

//Busca toda la información de la comunidad en esta actividad
$communitysql = $DB->get_record('emarking_social', array('activityid'=>$activityid));

if(!$communitysql){
	
	$communitysql=new stdClass ();
	$communitysql->activityid 			= $activityid;
	$communitysql->timecreated         	= time();
	$communitysql->data					= null;				
	$DB->insert_record ( 'emarking_social', $communitysql );
	$average=0;
}
$average=0;
if(isset($communitysql->data)&& $communitysql->data!=null){
$recordcleaned=emarking_activities_clean_string_to_json($communitysql->data);
$decode=json_decode($recordcleaned);
$social=$decode->data;
$comments=$social->Comentarios;
$votes=$social->Vote;
$vote=0;
if (isset ( $votes )) {
		if ($countVotes=count ( $votes ) == 1) {
			if (if_user_has_voted ( $votes [0], $USER->id )) {
				$vote = $votes [0]->rating;
			}
		} else {
			if (if_user_has_voted ( $votes, $USER->id )) {
				$vote = $votes->rating;
			}
		}
$average=get_average($votes);
}
$votesjson=json_encode($votes, JSON_UNESCAPED_UNICODE);
if(isset($_POST['submit'])) {
	$comentario = new stdClass ();
	$comentario->userid=$USER->id;
	$comentario->username=$USER->firstname.' '.$USER->lastname;
	$comentario->timecreated=time();
	$comentario->post=$_POST['comment'];
	$comentario->likes=array();
	$comentario->dislikes=array();
	$comments[]=$comentario;
	$commentjson=json_encode($comments, JSON_UNESCAPED_UNICODE);
	$newdata = Array(
			"Vote"=>$votes,
			"Comentarios"=>$commentjson
				
			);
	
	$dataarray=Array("data"=>$newdata);

	$datajson=json_encode($dataarray, JSON_UNESCAPED_UNICODE);
	$communitysql->data=$datajson;

	$DB->update_record('emarking_social', $communitysql);
	header("Refresh:0");
}
}else{
	if(isset($_POST['submit'])) {
		$comentario=array(
				array(	"userid"=>$USER->id,
						"username"=>$USER->firstname.' '.$USER->lastname,
						"timecreated"=>time(),
						"post"=>$_POST['comment'],
						"likes"=>array(),
						"dislikes"=>array())
		);
		
	$commentjson=json_encode($comentario, JSON_UNESCAPED_UNICODE);
	$data = Array(
				"Vote"=>null,
				"Comentarios"=>$commentjson
					
				);
		
		$dataarray=Array("data"=>$data);
		$datajson=json_encode($dataarray, JSON_UNESCAPED_UNICODE);
		$communitysql->data=$datajson;
		$DB->update_record('emarking_social', $communitysql);
		header("Refresh:0");
		
  }
}
$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
//print the header
include 'views/header.php';


include 'views/activity.php';

//print the footer
include 'views/footer.html';
