<?php
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
GLOBAL $USER, $CFG;
require_once ('locallib.php');
require_login();

$image=new moodle_url($CFG->wwwroot.'/user/pix.php/'.$USER->id.'/f1.jpg');
$createActivity=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/crear.php/');
$userData=$DB->get_record('user',array('id'=>$USER->id));
$countActivities=$DB->count_records_sql("select count(*) from {emarking_activities} where userid=?", array($USER->id));
$countRubrics=$DB->count_records_sql("select count(*) from {grading_definitions} where usercreated=?", array($USER->id));

$editProfileUrl= new moodle_url($CFG->wwwroot.'/user/edit.php/',array('id'=>$USER->id));

if($countRubrics == 1){
	$rubrics = $DB->get_record('grading_definitions', array('usercreated'=>$USER->id));
}elseif($countRubrics >= 1){
	
	$rubrics = $DB->get_records('grading_definitions', array('usercreated'=>$USER->id));
}

if($countActivities == 1){
	$activities = $DB->get_record('emarking_activities', array('userid'=>$USER->id));
}elseif($countActivities >= 1){

	$activities = $DB->get_records('emarking_activities', array('userid'=>$USER->id));
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
<script type="text/javascript" src="jquery-1.8.0.min.js"></script> 
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
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
					echo '<img src="'.$image.'" alt="Smiley face" height="100"	width="100">';
					echo '<h4>Miembro nivel 5</h4>';
					echo '<p>'.$countActivities.' Actividade(s) Publicada(s)</p>';
					echo '<p>'.$countRubrics.' Rúbricas Publicadas</p>';
					echo '<p>15 Opiniones</p>';
					echo '<p>25 Votos</p>';
				
					echo $userData->description;
					echo '<a href="'.$editProfileUrl.'">Editar perfil</a>';
					?>
					
					</div>
				</div>
			</div>
			<div class="col-md-9">
			<div class="panel panel-default">
					<div class="panel-body">
					<h2 class="title">Tu progreso</h2>
					<div class="col-md-6">
					<h4>Puntos totales</h4>
					<h3 class="importante">3.542</h3>
					</div>
					<div class="col-md-6">
					<h4>Categoría</h4>
					<h3 class="importante">Experta en confección de actividades</h3>
					</div>
					<br><br>
					<h4>Premios</h4>
					<img src="img/premio.jpg" alt="Smiley face" height="100"	width="70">
					<img src="img/premio.jpg" alt="Smiley face" height="100"	width="70">
					<img src="img/premio.jpg" alt="Smiley face" height="100"	width="70">
					<img src="img/premio.jpg" alt="Smiley face" height="100"	width="70">
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-body" >
					<h2 class="title">Tus Aportes</h2>
					

			



 <?php
 echo '<ul class="nav nav-tabs">';
  echo '<li class="active"><a data-toggle="tab" href="#home">Actividades ('.$countActivities.')</a></li>';
  echo '<li><a data-toggle="tab" href="#menu1">Rúbricas ('.$countRubrics.')</a></li>';
  echo '<li><a data-toggle="tab" href="#menu2">Opiniones (15)</a></li>';
 
  echo '</ul>';
 ?>


  <div class="tab-content">
    
	<div id="home" class="tab-pane fade in active">
					<h3 style="text-align: left;">Actividades</h3>
					
			
			<?php 
			echo '<a href="'.$createActivity.'" style="text-align: right;">';
			echo $OUTPUT->pix_icon('t/addfile', 'Crear una actividad');
			echo ' Crear una actividad</a>';
			if($countActivities == 1){
				echo show_result($activities);
				
			}
			elseif($countActivities > 1 ){
				
			foreach($activities as $activity){
				echo show_result($activity);
			}
			
			 
			}else{
				echo "<h3>Aún no has creado actividades...</h3>";
				
			}
				?>
			
			
			
			</div>
			<div id="menu1" class="tab-pane fade">
					<h3 style="text-align: left;">Rúbricas</h3>
					<?php 
				if($countRubrics == 1){
				
					$rubric = $DB->get_record('grading_definitions', array('usercreated'=>$USER->id));
					echo '<div class="panel panel-default">';
					echo '<div class="panel-body">';
					echo '<h3>'.$rubric->name.'</h3>';
					echo '<a href="$createActivity" style="text-align: right;">';
					echo $OUTPUT->pix_icon('i/edit', 'Crear una actividad');
					echo ' Editar rúbrica</a>';
					echo $rubric->description;
					echo show_rubric($rubric->id);
					echo '</div>';
					echo '</div>';
				}elseif($countRubrics >= 1){
				
					$rubrics = $DB->get_records('grading_definitions', array('usercreated'=>$USER->id));
					
				foreach($rubrics as $rubric){
				
					echo '<div class="panel panel-default">';
					echo '<div class="panel-body">';
					echo '<h3>'.$rubric->name.'</h3>';
					echo '<a href="$createActivity" style="text-align: right;">';
					echo $OUTPUT->pix_icon('i/edit', 'Crear una actividad');
					echo ' Editar rúbrica</a>';
					echo $rubric->description;
					echo show_rubric($rubric->id);
					echo '</div>';
					echo '</div>';
				
				}
				}
				?>
			
					</div>
					  <div id="menu2" class="tab-pane fade">
					<h3 style="text-align: left;">Opiniones</h3>
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="container">
								<div class="col-md-6 text-left descargas">
								<h3 class="title"><a href="#">Amigos por correspondencia</a></h3>
									<h4 style="margin-top: 0px; margin-bottom: 0px;"> Excelente actividad  
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star-empty" aria-hidden="true"	style="font-size: 20px;"></span>
									</h4>
									<p style="font-size: 18px;">Lorem ipsum dolor sit amet,
										consectetur adipiscing elit. Etiam eget commodo eros....
								</div>
							</div>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="container">
								<div class="col-md-6 text-left descargas">
								<h3 class="title"><a href="#">Amigos por correspondencia</a></h3>
									<h4 style="margin-top: 0px; margin-bottom: 0px;"> Excelente actividad  
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star-empty" aria-hidden="true"	style="font-size: 20px;"></span>
									</h4>
									<p style="font-size: 18px;">Lorem ipsum dolor sit amet,
										consectetur adipiscing elit. Etiam eget commodo eros....
								</div>
							</div>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="container">
								<div class="col-md-6 text-left descargas">
								<h3 class="title"><a href="#">Amigos por correspondencia</a></h3>
									<h4 style="margin-top: 0px; margin-bottom: 0px;"> Excelente actividad  
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star-empty" aria-hidden="true"	style="font-size: 20px;"></span>
									</h4>
									<p style="font-size: 18px;">Lorem ipsum dolor sit amet,
										consectetur adipiscing elit. Etiam eget commodo eros....
								</div>
							</div>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="container">
								<div class="col-md-6 text-left descargas">
								<h3 class="title"><a href="#">Amigos por correspondencia</a></h3>
									<h4 style="margin-top: 0px; margin-bottom: 0px;"> Excelente actividad  
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star" aria-hidden="true" style="font-size: 20px;"></span>
										<span class="glyphicon glyphicon-star-empty" aria-hidden="true"	style="font-size: 20px;"></span>
									</h4>
									<p style="font-size: 18px;">Lorem ipsum dolor sit amet,
										consectetur adipiscing elit. Etiam eget commodo eros....
								</div>
							</div>
						</div>
					</div>
					<ul class="pagination">
  <li class="active"><a href="#">1</a></li>
  <li ><a href="#">2</a></li>
  <li><a href="#">3</a></li>
  <li><a href="#">4</a></li>
</ul>
					</div>
				</div>
 </div>
		</div>
	</div>
</section><!-- FIN BUSCADOR -->
</body>
<?php include 'views/footer.php'; ?>