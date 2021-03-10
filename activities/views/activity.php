<?php
use core\event\user_loggedin;
?>
<script>
$(function() {
	var vote = '<?=$vote?>';
	if(vote > 0){
		for (i = 1; i <= vote; i++) { 
        	document.getElementById(i).className="btn btn-warning btn-sm";
        	document.getElementById(i).disabled = true;
        }
		document.getElementById(1).disabled = true;
        document.getElementById(2).disabled = true;
        document.getElementById(3).disabled = true;
        document.getElementById(4).disabled = true;
        document.getElementById(5).disabled = true;
		}
	
	  
	});
</script>
<style>
.activity ul {
    list-style: none;
    padding-left: 0px;
}
.activity .ficha-tecnica li i {
    font-size: 1.2em;
    margin-right: 5px;
}
#activityTab {
    margin-bottom: 1em;
}
.activity .btn {
    border-radius: 5px;
    color: #fff;
    margin-bottom: 1em;
}
</style>
<!-- BUSCADOR -->
<div class="activity">
		<div class="row">
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-body ficha-tecnica">
						<h4>Ficha técnica</h4>
						<p><?=$coursesOA?></p>
						<hr>
						<ul>
    						<li><i class="fa fa-book" aria-hidden="true" title="Género"></i><?php echo $genre->name; ?></li>
    					    <li><i class="fa fa-bullhorn" aria-hidden="true" title="Propóstico comunicativo"></i><?=$activity->comunicativepurpose?></li>
    					    <li><i class="fa fa-users" aria-hidden="true" title="Audiencia"></i><?= $activity->audience?></li>
    					    <li><i class="fa fa-clock-o" aria-hidden="true" title="Tiempo estimado"></i><?=$activity->estimatedtime?> horas</li>
    						<!-- <li><i class="fa fa-user" aria-hidden="true" title="Autor"></i><?php echo $userobject->firstname.' '.$userobject->lastname ?></li> -->
						</ul>
						<hr>
						<div class="activity_buttons">
						<ul>
						<?php if($userobject->id == $USER->id || has_capability('mod/emarking:manageactivities', context_system::instance())) { ?>
						<li><a href="<?= $CFG->wwwroot . '/mod/emarking/activities/createactivity.php?id='.$activity->id.'&step=1' ?>"><button type="button" class="btn btn-success">
							<i class="fa fa-paperclip" aria-hidden="true"></i> Editar actividad
						</button></a></li>
						<?php 
                        }
						if($useristeacher) {
						?>
						<li>
						<button type="button" class="btn  btn-success" data-toggle="modal"
							data-target="<?=$canuse?>" >
							<i class="fa fa-floppy-o" aria-hidden="true"></i> Usar
							Actividad
						</button></li>
						<li>
						<a href="<?=$forkingUrl?>"><button type="button" class="btn btn-primary">
							<i class="fa fa-floppy-o" aria-hidden="true"></i> Adaptar
							Actividad
						</button></a></li>
						<?php } ?>
                                                <?php if($userobject->id == $USER->id || has_capability('mod/emarking:manageactivities', context_system::instance())) { ?>
                                                <li><a href="<?= $CFG->wwwroot . '/mod/emarking/activities/deleteactivity.php?id='.$activity->id?>"><button type="button" class="btn btn-danger">
                                                        <i class="fa fa-trash" aria-hidden="true"></i> Borrar actividad
                                                </button></a></li>
                                                <?php
                                                }
?>

						</ul>
					</div>
					<h4>Calificación</h4>
					<div class="stars">
					<?php for ($i=1;$i <= 5;$i++){
					       if($i <= $average){
						      echo '<i class="fa fa-star" aria-hidden="true"> </i>';
					       } else {
						      echo '<i class="fa fa-star-o" aria-hidden="true"></i>';
					       }
				        }
				echo "&nbsp;$average/5";
				?></div>
    				<div class="creative-commons">
    					<hr>
    					<a rel="license" href="http://creativecommons.org/licenses/by/4.0/">
    					<img alt="Licencia Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/88x31.png" /></a>
    					<p>Esta obra está bajo una <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Licencia Creative Commons Atribución 4.0 Internacional</a></p>
    				</div>
					</div>
				</div>
			</div>
			<div class="col-md-9">
				<div class="panel panel-default">
					<div class="panel-body">
					<?php 
						if ($message) {
					?>
						<div class="alert alert-danger">
						  <strong>Atención!</strong> Usuario invalido para Adoptar la Actividad. Favor revisar si ha iniciado sesión correctamente.
						</div>
						
					<?php
						}								
						if(isset($disabled) && $disabled!=null && $usercaneditrubric){
					?>
						<div class="alert alert-warning">
						  <strong>Atención!</strong> Es necesario crear una rúbrica para que esta actividad pueda ser utilizada. <a href="<?=$rubricUrl?>">Crear Rúbrica</a> o <a href="<?=$importrubricUrl?>">Importar Rúbrica</a>
						</div>
					<?php }?>
						<h2 class="title_result"><?=$activity->title ?></h2>
						<p><?=$activity->description?></p>
						<!-- Aqui agregue el cambio para las tabs -->
						<ul class="nav nav-tabs active_tab" id="activityTab" role="tablist">
							<li class="nav-item"><a class="nav-link active" id="didactica-tab" data-toggle="tab" href="#teaching">Didáctica</a></li>
							<li class="nav-item"><a class="nav-link" id="estudiante-tab" data-toggle="tab" href="#tostudent">Para el
									estudiante</a></li>
							<li class="nav-item"><a class="nav-link" id="evaluacion-tab" data-toggle="tab" href="#evaluation">Evaluación</a></li>
						</ul>

						<div class="tab-content">
							<div id="tostudent" class="tab-pane fade">
						<div class="activity_buttons">
						<a href="<?=$printpdfUrl?>"><button type="button" class="btn btn-info">
							<i class="fa fa-print" aria-hidden="true"></i> Descargar instrucciones
						</button></a>
						</div>
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


					<div id="teaching" class="tab-pane fade  in active">
						<div class="activity_buttons">
						<a href="<?=$printteachingpdfUrl?>"><button type="button" class="btn btn-info">
							<i class="fa fa-print" aria-hidden="true"></i> Descargar didáctica
						</button></a>
						</div>
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
										<h4 style="text-align: left;">Contenidos complementarios</h4>	
				<?php
				echo $activity->languageresources;
				?>

			</div>
								</div>


							</div>

							<div id="evaluation" class="tab-pane fade">
	<?php if(isset($rubric)&& $rubric!=null){?>
								<h4 style="text-align: left;"><?php echo $rubricname?></h4>
						<?php if(isset($disabled) && $disabled!=null && $usercaneditrubric){
						?>
						<a href="<?=$rubricUrl?>"><button type="button" class="btn btn-warning">
							<i class="fa fa-paperclip" aria-hidden="true"></i> Crear Rúbrica
						</button></a>
						<?php
						} elseif ($usercaneditrubric) {
						?>
						<a href="<?=$importrubricUrl?>"><button type="button" class="btn btn-warning">
							<i class="fa fa-paperclip" aria-hidden="true"></i> Importar Rúbrica
						</button></a>
						<a href="<?=$rubricUrl?>"><button type="button" class="btn btn-warning">
							<i class="fa fa-paperclip" aria-hidden="true"></i> Editar Rúbrica
						</button></a>
						<?php
						}
						echo $rubricdescription; ?>
			<table class="table table-bordered">
									<thead>
										<tr>
											<td></td>
<?php
$maxlevel = 3;
$i = $maxlevel;
$niveles = array(
	"",
	"Principiante",
	"Intermedio",
	"Avanzado",
);
while($i>0) {
	echo "<th>". $niveles[$i] . "</th>";
	$i--;
}

													?>
     				   
     					</tr>
									</thead>
									<tbody>

<?php 
   				    	
foreach ( $table as $key => $value ) {
echo "<tr>";
echo "<th>$key</th>";
if (!array_key_exists(1, $value) && $maxlevel >= 1)
	$value[1]="";
if (!array_key_exists(2, $value) && $maxlevel >= 2)
	$value[2]="";
if (!array_key_exists(3, $value) && $maxlevel >= 3)
	$value[3]="";
if (!array_key_exists(4, $value) && $maxlevel >= 4)
	$value[4]="";
krsort($value);
foreach ( $value as $score => $level ) {
	if($score > $maxlevel) {
		continue;
	}
echo "<td>$level</td>";
}
echo "</tr>";
}
												
?>
   				    	

   				    </tbody>
								</table>

<?php }?>
							</div>
							
						</div>
					</div>
				</div>
			</div>
	</div>
	<!-- FIN BUSCADOR -->
	<section>
			<div class="row">
				<div class="col-md-12 panel panel-default">
					<div class="panel-body">
						<hr>
						<h4 class="title">Comentarios</h4>
						<?php include 'social.php';?>
					</div>
				</div>
			</div>
	</section>

	<!-- Modal -->
<?php
include "modals/downloadactivity.php";
include "modals/useactivity.php";
include "modals/cantuseactivity.php";
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
		        document.getElementById(1).disabled = true;
		        document.getElementById(2).disabled = true;
		        document.getElementById(3).disabled = true;
		        document.getElementById(4).disabled = true;
		        document.getElementById(5).disabled = true;
		        
		        var countvotes = parseInt('<?=$countVotes?>') + 1;
		        document.getElementById("average").innerHTML=result+ " <small>/ 5 </small>" + 
		        '<small id="countVotes"	style="font-size: 13px; color: black;"> '+ countvotes + " voto(s)";
	       }
	     });
}
</script>
