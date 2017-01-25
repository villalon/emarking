<!DOCTYPE html>
<?php
require_once (dirname(dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) )) . '/config.php');
GLOBAL $USER, $CFG, $PAGE, $DB;
require_once ($CFG->dirroot. '/mod/emarking/activities/locallib.php');
require_once ($CFG->dirroot . "/mod/emarking/lib.php");require_login ();
$PAGE->set_context ( context_system::instance () );
$image = new moodle_url ( $CFG->wwwroot . '/user/pix.php/' . $USER->id . '/f1.jpg' );
$createActivity = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/crear.php/' );
$userData = $DB->get_record ( 'user', array (
		'id' => $USER->id 
) );
$countActivities = $DB->count_records_sql ( "select count(*) from {emarking_activities} where userid=?", array (
		$USER->id 
) );
$countRubrics = $DB->count_records_sql ( "select count(*) from {grading_definitions} where usercreated=?", array (
		$USER->id 
) );

$editProfileUrl = new moodle_url ( $CFG->wwwroot . '/user/edit.php/', array (
		'id' => $USER->id 
) );

if ($countRubrics == 1) {
	$rubrics = $DB->get_record ( 'grading_definitions', array (
			'usercreated' => $USER->id 
	) );
} elseif ($countRubrics >= 1) {
	
	$rubrics = $DB->get_records ( 'grading_definitions', array (
			'usercreated' => $USER->id 
	) );
}

if ($countActivities == 1) {
	$activities = $DB->get_record ( 'emarking_activities', array (
			'userid' => $USER->id 
	) );
} elseif ($countActivities >= 1) {
	
	$activities = $DB->get_records ( 'emarking_activities', array (
			'userid' => $USER->id 
	) );
}
$usercourses = enrol_get_users_courses ( $USER->id );

foreach ( $usercourses as $usercourse ) {
	
	$coursecontext = context_course::instance ( $usercourse->id );
	
	if (has_capability ( 'moodle/course:update', $coursecontext )) {
		$coursesasteacher [] = $usercourse;
	}
}

?>


	<?php include 'header.php'; ?>

	<!-- fIN DEL header -->
	<!-- BUSCADOR -->
	<section class="perfil">
		<div class="container">
			<div class="row">
				<h2></h2>
				<div class="col-md-3">
					<div class="panel panel-default">
						<div class="panel-body">
					<?php
					echo "<h3>$USER->firstname $USER->lastname</h3>";
					echo '<img src="' . $image . '" alt="Smiley face" height="100"	width="100">';
					echo '<h4>Miembro nivel 5</h4>';
					echo '<p>' . $countActivities . ' Actividade(s) Publicada(s)</p>';
					echo '<p>' . $countRubrics . ' Rúbricas Publicadas</p>';
					echo '<p>15 Opiniones</p>';
					echo '<p>25 Votos</p>';
					
					echo $userData->description;
					echo '<a href="' . $editProfileUrl . '">Editar perfil</a>';
					?>
					
					</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-body">
							<h2 class="title">Tu progreso</h2>
							<h4>Puntos totales</h4>
							<h3 class="importante">3.542</h3>
							<h4>Categoría</h4>
							<h3 class="importante">Experta en confección de actividades</h3>
							<h4>Premios</h4>
							<img src="img/premio.jpg" alt="Smiley face" height="100"
								width="70">

						</div>
					</div>
				</div>
				<div class="col-md-9">
					
						
						<div class="panel panel-default">
							<div class="panel-body">
					<h1>Tus Cursos</h1>
					<?php
					
					if(isset($coursesasteacher)){
					foreach ( $coursesasteacher as $course ) {
						
						
						echo '<div class="panel panel-default">';
						echo '<div class="panel-body" >';
$sql="select cm.id as coursemodule, e.*
from mdl_course_modules as cm
INNER JOIN mdl_emarking as e ON e.id=cm.instance
RIGHT JOIN mdl_modules as m ON cm.module=(select id from mdl_modules where name = ?)
where e.course =? 
GROUP BY cm.id";
$emarkingintances = $DB->get_records_sql($sql,array(get_string('pluginname', 'mod_emarking'),$course->id));


			
						echo '<h3>' . $course->fullname . '</h3>';
						
						foreach ( $emarkingintances as $instace ) {
							$emarkingurl=new moodle_url($CFG->wwwroot .'/mod/emarking/activities/views/view.php', array(
									"id" => $instace->coursemodule));
							echo '<a href="'.$emarkingurl.'">'.$instace->name.'</a><br>';
							
						}
						
						echo '</div>';
						echo '</div>';
					
						
					}
						
					}
					else{
						echo '<h1>No tienes cursos</h1>';
					}
					?>
					
						</div>
					
					</div>
					
				</div>
				</div>
					</div>
									
	
	</section>
	<!-- FIN BUSCADOR -->
</body>
<?php include 'footer.php'; ?>