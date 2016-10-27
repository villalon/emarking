<?php
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) )). '/config.php');
require_once ('generos.php');
GLOBAL $USER, $CFG,$PAGE;
$teacherroleid = 3;
$logged = false;
$PAGE->set_context(context_system::instance());
// Id of the exam to be deleted.
$activityid = required_param('id', PARAM_INT);
$forkingUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/forking.php', array('id' => $activityid));
$editUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/edit.php', array('id' => $activityid));
$pdfUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/pdfcreator.php', array('id' => $activityid));

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
$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));
$user_object = $DB->get_record('user', array('id'=>$activity->userid));

$rubric=$DB->get_records_sql("SELECT grl.id,
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
							  ORDER BY grcid, grl.id",
							  array($activity->rubricid));


foreach ($rubric as $data) {
	
	$table[$data->description][$data->definition]=$data->score;
	$rubricdescription=$data->des;
	$rubricname=$data->name;
}
$col=0;
foreach ($table as $calc) {
	
	$actualcol=sizeof($calc);
	if($col < $actualcol){
		$col=$actualcol;
	}
	
}
$row=sizeof($table);

$oaComplete=explode("-",$activity->learningobjectives);
$coursesOA="";
foreach($oaComplete as $oaPerCourse){

	$firstSplit=explode("[",$oaPerCourse);	
	$secondSplit=explode("]",$firstSplit[1]);
	$course=$firstSplit[0];
	
	$coursesOA .='<p>Curso: '.$firstSplit[0].'° básico</p>';
	$coursesOA .='<p>OAs: '.$secondSplit[0].'</p>';
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
  				<center>
  				<?php
  				if($activity->userid == $USER->id){
  					echo '<a href="'.$editUrl.'" class="btn btn-primary" role="button">Editar Actividad</a>';
  				}else{
  					echo '<a href="'.$forkingUrl.'" class="btn btn-primary" role="button">Utilizar Actividad</a>';
  				}
  				?>
  				
 				</center>
						
					</div>
			</div>
				<div class="panel panel-default">
					<div class="panel-body">
					<h3>Resumen</h3>
					
					<p>Título: <?php echo $activity->title; ?></p>
					<p>Descipción: <?php echo $activity->description;?></p>
					<?php echo $coursesOA; ?>
					<p>Propósito comunicativo: <?php echo $activity->comunicativepurpose; ?></p>
					<p>Género: <?php echo $activity->genre; ?></p>
					<p>Audiencia: <?php echo $activity->audience; ?></p>
					<p>Tiempo estimado: <?php echo $activity->estimatedtime; ?> minutos</p>
					<p>Creado por: <?php echo $user_object->firstname.' '.$user_object->lastname ?> </p>


					

					</div>
				</div>
			</div>
			<div class="col-md-9">
				<div class="panel panel-default">
					<div class="panel-body" >
					<h2 class="title"> <?php echo $activity->title ?> </h2>
					
 
  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#home">Instrucciones</a></li>
    <li><a data-toggle="tab" href="#menu1">Didáctica</a></li>
    <li><a data-toggle="tab" href="#menu2">Evaluación</a></li>
  </ul>

  <div class="tab-content">
	<div id="home" class="tab-pane fade in active">
		<h3 style="text-align: left;">Instrucciones para el estudiante</h3>
			 <a href="<?php echo $pdfUrl;?>" target="_blank"> Descargar pdf</a>
		<div class="panel panel-default">
			<div class="panel-body">	
				<?php 
				echo $activity->instructions;
				?>
			</div>
		</div>
	</div>


	<div id="menu1" class="tab-pane fade">
		<h3 style="text-align: left;">Didáctica</h3>
		
		<div class="panel panel-default">
			<div class="panel-body">
				<h4 style="text-align: left;">Sugerencias</h4>	
				<?php 
				echo $activity->teaching;
				?>

			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-body">
				<h4 style="text-align: left;">Recursos de la lengua</h4>	
				<?php 
				echo $activity->languageresources;
				?>

			</div>
		</div>

				
	</div>

	 <div id="menu2" class="tab-pane fade">
	 <h3 style="text-align: left;">Evaluación</h3>
	<h4 style="text-align: left;"><?php echo $rubricname?></h4>
	<?php echo $rubricdescription; ?>
			<table class="table table-bordered">
 					<thead>
     					<tr>
     				    <td></td>
     				    <?php 
     				    for ($i=1; $i <= $col; $i++) { 
     				    	echo "<th>Nivel $i</th>";
     				    }
     				    ?>
     				   
     					</tr>
   					</thead>
   					<tbody>

   				    	<?php 
   				    	foreach ($table as $key => $value) {
   				    		echo "<tr>";
   				    		   				    		
   				    		echo "<th>$key</th>";
   				    		foreach ($value as $level => $score) {
   				    			echo "<th>$level</th>";
   				    		}

   				    		echo "</tr>";
   				    	}

   				    	?>
   				    	

   				    </tbody>
   			</table>

					
	</div>
				</div>
 </div>
		</div>
		</div> 
	</div>
</section><!-- FIN BUSCADOR -->
<section >
	<div class="container">
		<div class="row">
			<h2></h2>
			<div class="panel panel-default">
				<div class="panel-body" >
					<h2 class="title">Social</h2>
					</div>
				</div>
			</div>
	</div>
</section>
</body>
<!-- footer starts here -->
<footer class="footer clearfix">
	<div class="container">
		<div class="row">
			<div class="col-xs-6 footer-para">
				<p>&copy; All right reserved</p>
			</div>
			<div class="col-xs-6 text-right">
				<a href=""><i class="fa fa-facebook"></i></a> <a href=""><i
					class="fa fa-twitter"></i></a>
			</div>
		</div>
	</div>
</footer>
