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
// Mensaje que se muestra si hace clic en "Adoptar Actividad" y no esta aún logeado
$message = optional_param ('message', 0 , PARAM_INT);
$teacherroleid = 3;

$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

if (! $activity = $DB->get_record ( 'emarking_activities', array ('id' => $activityid))) {
	print_error("ID de Actividad invalido");
}

$PAGE->set_context(context_system::instance());
$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php', array('id' => $activityid));
$PAGE->set_url($url);
$PAGE->set_title('escribiendo');

$PAGE->set_context ( context_system::instance () );

$forkingUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/forking.php', array (
		'id' => $activityid
) );
$rubricUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/rubric.php', array (
		'activityid' => $activityid
) );
$editUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/editactivity.php', array (
		'activityid' => $activityid
) );

if (isloggedin ()) {
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

$userobject = $DB->get_record ( 'user', array (
		'id' => $activity->userid
) );
$sql="SELECT rl.*, rc.description as criteria, r.id as rubricid, r.name, r.description,rc.description as criteriondescription, i.max
FROM mdl_emarking_rubrics_levels as rl
INNER JOIN mdl_emarking_rubrics_criteria rc ON (rc.id = rl.criterionid )
INNER JOIN mdl_emarking_rubrics r ON (r.id = rc.rubricid )
LEFT JOIN (select criterionid, max(score) as max FROM mdl_emarking_rubrics_levels as rl group by criterionid) as i on (i.criterionid=rl.criterionid)
where r.id=?
ORDER BY rl.criterionid ASC, rl.score DESC";
$rubric = $DB->get_records_sql ( $sql, array (
							  		$activity->rubricid
							  ) );

$disabled="disabled";
$canuse="#myModalUse";
if(isset($rubric)&& $rubric!=null){
	foreach ( $rubric as $data ) {
		
	$disabled=null;
	$table [$data->criteriondescription] [$data->score] = $data->definition;
	$rubricdescription = $data->description;
	$rubricname = $data->name;
	$maxlevel=$data->max;
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
$coursesOA = '<span>Curso: </span><br>';
$coursesOA .= '<span>OAs:</span><br>';
if(isset($activity->learningobjectives)&&$activity->learningobjectives!=null){
$oaComplete = explode ( "-", $activity->learningobjectives );

foreach ( $oaComplete as $oaPerCourse ) {

	$firstSplit = explode ( "[", $oaPerCourse );
	$secondSplit = explode ( "]", $firstSplit [1] );
	$course = $firstSplit [0];

	$coursesOA = '<span>Curso: ' . $firstSplit [0] . '° básico</span><br>';
	$coursesOA .= '<span>OAs: ' . $secondSplit [0] . '</span><br>';
}
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
$countVotes=0;
$vote=0;
if(isset($communitysql->data)&& $communitysql->data!=null){
$recordcleaned=emarking_activities_clean_string_to_json($communitysql->data);
$decode=json_decode($recordcleaned);
$social=$decode->data;
$comments=$social->Comentarios;
$votes=$social->Vote;

if (isset ( $votes )) {
	$countVotes=count ( $votes );
		if ($countVotes == 1) {
			if ($vote=if_user_has_voted ( $votes [0], $USER->id ));			
		} else {
			if ($vote= if_user_has_voted ( $votes, $USER->id ));
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

	$comentario->date = $dias[date('w',$comentario->timecreated)]." ".date('d',$comentario->timecreated)." de ".$meses[date('n',$comentario->timecreated)-1]. " del ".date('Y',$comentario->timecreated) ;

	echo json_encode($comentario, JSON_UNESCAPED_UNICODE);
	die();
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

//print the header
include 'views/header.php';

// Display activity information
include 'views/activity.php';

//print the footer
include 'views/footer.html';
