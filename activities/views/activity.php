<?php
require_once (dirname(dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) )) . '/config.php');
require_once ($CFG->dirroot. '/mod/emarking/activities/generos.php'); 
GLOBAL $USER, $CFG, $PAGE;
$teacherroleid = 3;
$logged = false;
$PAGE->set_context ( context_system::instance () );
// Id of the exam to be deleted.
$activityid = required_param ( 'id', PARAM_INT );

$forkingUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/forking.php', array (
		'id' => $activityid 
) );
$editUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/views/edit.php', array (
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
$user_object = $DB->get_record ( 'user', array (
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

$oaComplete = explode ( "-", $activity->learningobjectives );
$coursesOA = "";
foreach ( $oaComplete as $oaPerCourse ) {
	
	$firstSplit = explode ( "[", $oaPerCourse );
	$secondSplit = explode ( "]", $firstSplit [1] );
	$course = $firstSplit [0];
	
	$coursesOA .= '<p>Curso: ' . $firstSplit [0] . '° básico</p>';
	$coursesOA .= '<p>OAs: ' . $secondSplit [0] . '</p>';
}

?>
<!DOCTYPE html>
<?php include 'header.php'; ?>

<!-- BODY -->
<body>

	<!-- BUSCADOR -->
	<div class="activity">
		<div class="container">
			<div class="row">
				<h2></h2>
				<div class="col-md-3">
					
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
						<div class="panel-body">
							<h2 class="title"> <?php echo $activity->title ?> </h2>
							 <button type="button" class="btn btn-success" data-toggle="modal"
								data-target="#myModal"><span class="glyphicon glyphicon-cloud-download"></span> Descargar Actividad</button>
							 
							 <?php	
								
						if ($activity->userid == $USER->id) {
							echo '<a href="' . $editUrl . '" class="btn btn-primary" role="button">
										<span class="glyphicon glyphicon-edit"></span> Editar Actividad</a> ';
							echo '<button type="button" class="btn btn-warning" data-toggle="modal"
								data-target="#myModalUse">
									   <span class="glyphicon glyphicon-floppy-disk"></span> Utilizar Actividad</button>';
						}else{
							echo '<a href="' . $forkingUrl . '" class="btn btn-primary" role="button">
									<span class="glyphicon glyphicon-floppy-disk"></span> Guardar Actividad</a>';
						}
							?>
								<br><br>
							

							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#home">Instrucciones</a></li>
								<li><a data-toggle="tab" href="#menu1">Didáctica</a></li>
								<li><a data-toggle="tab" href="#menu2">Evaluación</a></li>
							</ul>

							<div class="tab-content">
								<div id="home" class="tab-pane fade in active">
									<h3 style="text-align: left;">Instrucciones para el estudiante</h3>
 
									<div class="panel panel-default">
										<div class="panel-body">
											<h4 style="text-align: left;">Instrucciones</h4>
				<?php
				echo $activity->instructions;
				?>
			</div>
									</div>
									<div class="panel panel-default">
										<div class="panel-body">
											<h4 style="text-align: left;">Planificación</h4>
				<?php
				echo $activity->planification;
				?>
			</div>
									</div>
									<div class="panel panel-default">
										<div class="panel-body">
											<h4 style="text-align: left;">Escritura</h4>
				<?php
				echo $activity->writing;
				?>
			</div>
									</div>
									<div class="panel panel-default">
										<div class="panel-body">
											<h4 style="text-align: left;">Revisión y edición</h4>
				<?php
				echo $activity->editing;
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
													for($i = 1; $i <= $col; $i ++) {
														echo "<th>Nivel $i</th>";
													}
													?>
     				   
     					</tr>
										</thead>
										<tbody>

   				    	<?php
												foreach ( $table as $key => $value ) {
													echo "<tr>";
													
													echo "<th>$key</th>";
													foreach ( $value as $level => $score ) {
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
	
	</div>
	<!-- FIN BUSCADOR -->
	<section>
		<div class="container">
			<div class="row">
				<h2></h2>
				<div class="panel panel-default">
					<div class="panel-body">
						<h2 class="title">Social</h2>
					</div>
				</div>
			</div>
		</div>
	</section>
</body>
<?php include 'footer.php'; ?>
 

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">¿Qué deseas agregar al pdf?</h4>
			</div>
			
			<div class="modal-body">
			 	<form action="../pdfcreator.php" method="get">
 					<div class="form-check">
   			 			<label class="form-check-label">
      						<input type="checkbox" class="form-check-input" name="instructions" value="1" checked>
     						 Instrucciones para el estudiante
   						</label>
  					</div>
  					<div class="form-check">
   			 			<label class="form-check-label">
      						<input type="checkbox" class="form-check-input" name="planification" value="1" checked>
     						 Planificación
   						</label>
  					</div>
  					<div class="form-check">
   			 			<label class="form-check-label">
      						<input type="checkbox" class="form-check-input" name="writing" value="1" checked>
     						 Escritura
   						</label>
  					</div>
  					<div class="form-check">
   			 			<label class="form-check-label">
      						<input type="checkbox" class="form-check-input" name="editing" value="1" checked>
     						 Revisión y edición
   						</label>
  					</div>
  					<div class="form-check">
   			 			<label class="form-check-label">
      						<input type="checkbox" class="form-check-input" name="teaching" value="1" checked>
     						 Sugerencias didácticas
   						</label>
  					</div>
  					<div class="form-check">
   			 			<label class="form-check-label">
      						<input type="checkbox" class="form-check-input" name="resources" value="1" checked>
     						 Recursos de la lengua
   						</label>
  					</div>
  					<div class="form-check">
   			 			<label class="form-check-label">
      						<input type="checkbox" class="form-check-input" name="rubric" value="1" checked>
     						 Evaluación
   						</label>
  					</div>
  					<div style="text-align: right">
  					<input type="hidden" name="id" value="<?php echo $activityid;?>">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
  				<button type="submit" class="btn btn-primary" >Descargar</button>
  				</div>
		  </form>
			</div>
			
		</div>

	</div>
</div>
<!-- Modal -->
<div id="myModalUse" class="modal fade" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Utilizar actividad</h4>
			</div>
			<div class="modal-body">
		 <form role="form" action="../newsubmission.php">
									<br> <br> <select class="form-control" name="course">
										<option>Seleccione el curso</option>
 									 <?php
							foreach ( $asteachercourses as $key => $asteachercourse ) {
								echo '<option value="' . $key . '"> ' . $asteachercourse . ' </option>';
							}
							?>
  								</select>
  								<br> <label><input type="checkbox" name="askMarking"
										value=1>Corrección experta</label> <input type="hidden"
										value="<?php echo $activityid; ?>" name="id"> <br>
								<div style="text-align: right">
								<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
  				
			<?php
							if ($countcourses > 0) {
								?>
  				<button type="submit" class="btn btn-primary">Utilizar</button>
  			<?php }else { ?>
  				<button type="submit" class="btn btn-primary" disabled>Utilizar</button>
 			<?php }?></div>
						</form>
 			
		  </form>
			</div>
			
		</div>

	</div>
</div>