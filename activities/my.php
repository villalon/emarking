<?php
require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . '/config.php');
GLOBAL $USER, $CFG, $PAGE, $DB;
require_once ('locallib.php');
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

<!DOCTYPE html>
<html lang="en">
<!-- Head -->
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<title>Lorem Ipsum</title>
<!-- CSS Font, Bootstrap, style de la página y auto-complete  -->
<link rel="stylesheet" href="css/font-awesome.min.css">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="auto-complete.css">
<!-- Fin CSS -->
<!-- Css traidos desde google, no sé cuales realmete se usan  -->
<link
	href='http://fonts.googleapis.com/css?family=Open+Sans:600italic,400,800,700,300'
	rel='stylesheet' type='text/css'>
<link
	href='http://fonts.googleapis.com/css?family=BenchNine:300,400,700'
	rel='stylesheet' type='text/css'>
<link rel="stylesheet"
	href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300">
<link rel="stylesheet"
	href="https://cdn.rawgit.com/yahoo/pure-release/v0.6.0/pure-min.css">
<!-- Fin CSS de google -->
<!-- Importar  Scripts Javascript -->
<script src="js/modernizr.js"></script>

<!-- Fin Script Javascript -->
<!-- Scripts JQuery -->
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
<script type="text/javascript" src="jquery-1.8.0.min.js"></script>
<link rel="stylesheet"
	href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
<script
	src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<!-- Script para filtro de genero -->

</head>

<!-- BODY -->
<body>
	<!-- Header  -->
	<header class="top-header">	
	<?php include 'header.php'; ?>
</header>
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
					foreach ( $coursesasteacher as $course ) {
						echo '<div class="panel panel-default">';
						echo '<div class="panel-body" >';
						$emarkingintances = $DB->get_records ( 'emarking', array (
								'course' => $course->id 
						) );
						echo '<h3>' . $course->fullname . '</h3>';
						
						foreach ( $emarkingintances as $instace ) {
							$exam=$DB->get_record('emarking_exams',array('emarking'=>$instace->id));
							echo '<div class="card">
							<div class="card-header" role="tab" id="heading' . $instace->id . '">
							<h5 class="mb-0">
							<a class="collapsed" data-toggle="collapse" data-parent="#accordion"
							href="#collapse' . $instace->id . '" aria-expanded="false" aria-controls="collapse' . $instace->id . '">
							' . $instace->name . '
							</a>
							</h5>
							</div>';
							echo '<div id="collapse' . $instace->id . '" class="collapse" role="tabpanel"
									aria-labelledby="heading' . $instace->id . '">';
							$buttontext = $exam->status < EMARKING_EXAM_BEING_PROCESSED ? get_string ( 'exam', 'mod_emarking' ) . ' ' . core_text::strtolower ( get_string ( 'examstatusbeingprocessed', 'mod_emarking' ) ) : get_string ( 'downloadexam', 'mod_emarking' );
    						$disabled = $exam->status < EMARKING_EXAM_BEING_PROCESSED ? 'disabled' : '';
   							$downloadexambutton = "<input type='button' class='downloademarking' examid ='$exam->id' value='" . $buttontext . "' $disabled>";
   							 echo $downloadexambutton; 
							echo '</div>';
						}
						
						echo '</div>';
						echo '</div>';
						echo '</div>';
						
						
					}
					?>
					
						</div>
						hola
					</div>
					hola
				</div>
				</div>
					</div>
									
	
	</section>
	<!-- FIN BUSCADOR -->
</body>
<?php include 'views/footer.php'; ?>