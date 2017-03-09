
<!-- BUSCADOR -->
<div class="activity">
	<div class="container">
		<div class="row">
			<h2></h2>
			<div class="col-md-3">

				<div class="panel panel-default">
					<div class="panel-body">
						<h3>Resumen</h3>

						<p>Título: <?=ucfirst(strtolower($activity->title))?></p>
						<p>Descipción: <?php echo $activity->description;?></p>
					<?php echo $coursesOA; ?>
					<p>Propósito comunicativo: <?php echo $activity->comunicativepurpose; ?></p>
						<p>Género: <?php echo $activity->genre; ?></p>
						<p>Audiencia: <?php echo $activity->audience; ?></p>
						<p>Tiempo estimado: <?php echo $activity->estimatedtime; ?> minutos</p>
						<p>Creado por: <?php echo $user_object->firstname.' '.$user_object->lastname ?> </p>




					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-body">
						<h3>Calificación</h3>
						<div class="rating-block" style="text-align: center;">
							<h2 id="average" class="bold padding-bottom-7">
								<?=$average?> <small>/ 5</small> <small id="countVotes"
									style="font-size: 13px; color: black;"><?=$countVotes?> voto(s)</small>
							</h2>
							<button id="1" type="button"
								class="btn btn-default btn-grey btn-sm" aria-label="Left Align"
								value="1" onclick="rating(this.value)">
								<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							</button>
							<button id="2" type="button"
								class="btn btn-default btn-grey btn-sm" aria-label="Left Align"
								value="2" onclick="rating(this.value)">
								<span class="glyphicon glyphicon-star" aria-hidden="true"> </span>
							</button>
							<button id="3" type="button"
								class="btn btn-default btn-grey btn-sm" aria-label="Left Align"
								value="3" onclick="rating(this.value)">
								<span class="glyphicon glyphicon-star" aria-hidden="true"> </span>
							</button>
							<button id="4" type="button"
								class="btn btn-default btn-grey btn-sm" aria-label="Left Align"
								value="4" onclick="rating(this.value)">
								<span class="glyphicon glyphicon-star" aria-hidden="true"> </span>
							</button>
							<button id="5" type="button"
								class="btn btn-default btn-grey btn-sm" aria-label="Left Align"
								value="5" onclick="rating(this.value)">
								<span class="glyphicon glyphicon-star" aria-hidden="true"> </span>
							</button>

						</div>
					</div>
				</div>
			</div>
			<div class="col-md-9">
				<div class="panel panel-default">
					<div class="panel-body">
						<h2 class="title"> <?=ucfirst(strtolower($activity->title))?> </h2>
						<button type="button" class="btn btn-success" data-toggle="modal"
							data-target="#myModal">
							<span class="glyphicon glyphicon-cloud-download"></span>
							Descargar Actividad
						</button>
							 
							 <?php
								
								if ($activity->userid == $USER->id) {
									echo '<a href="' . $editUrl . '" class="btn btn-primary" role="button">
										<span class="glyphicon glyphicon-edit"></span> Editar Actividad</a> ';
									echo '<button type="button" class="btn btn-warning" data-toggle="modal"
								data-target="#myModalUse">
									   <span class="glyphicon glyphicon-floppy-disk"></span> Utilizar Actividad</button>';
								} else {
									echo '<a href="' . $forkingUrl . '" class="btn btn-primary" role="button">
									<span class="glyphicon glyphicon-floppy-disk"></span> Guardar Actividad</a>';
								}
								?>
								<br>
						<br>

						<!-- Aqui agregue el cambio para las tabs -->
						<ul class="nav nav-tabs active_tab">
							<li class="active"><a data-toggle="tab" href="#home">Para el
									estudiante</a></li>
							<li><a data-toggle="tab" href="#menu1">Didáctica</a></li>
							<li><a data-toggle="tab" href="#menu2">Evaluación</a></li>
						</ul>

						<div class="tab-content">
							<div id="home" class="tab-pane fade in active">


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
						<h2 class="title">Comentarios</h2>
						<?php include 'social.php';?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Modal -->
<?php
include "modals/downloadactivity.php";
include "modals/useactivity.php";
?>
<script>

	 function rating (mount) {
		 
	      $.ajax({
	        url:"ajax.php", //the page containing php script
	        type: "POST", //request type
	        data: {'id':'<?=$activity->id?>',
		        'userid':'<?=$USER->id?>',
		        'rating':mount,
		        'action':'rating'
		        	 },
	        success:function(result){
		        
		        for (i = 1; i <= mount; i++) { 
		        	document.getElementById(i).className="btn btn-warning btn-sm";
		        }
		        
		        document.getElementById("average").innerHTML=result+ " <small>/ 5</small>";
	       }
	     });
}
</script>