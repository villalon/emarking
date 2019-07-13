<div id="myModalUse" class="modal fade" role="dialog">
	<div class="modal-dialog">
 		<form role="form" action="newsubmission.php">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Usar actividad <?= $activity->title ?></h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
		 		<input type="hidden" value="<?php echo $activityid; ?>" name="id">
		 		<div class="form-group">
		 			<label for="select-course">Curso</label>
					<select class="form-control" id="select-course" name="course">
										<option>Seleccione el curso</option>
 									 <?php
							foreach ( $asteachercourses as $key => $asteachercourse ) {
								echo '<option value="' . $key . '"> ' . $asteachercourse . ' </option>';
							}
							?>
  					</select>
  				</div>
  				<div class="form-group">
  								<label for="submissiontype">Forma de subir los textos</label>
  								<select id="submissiontype" class="form-control" name="submissiontype">
  									<option value="">Seleccione una forma</option>
  									<option value="1">Profesor escanea</option>
  									<option value="2">Estudiantes suben archivo PDF</option>
  									<option value="3">Estudiantes escriben en línea</option>
  								</select>
  				</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
			<button type="submit" class="btn btn-primary">Usar</button>
		</div>
 		</form>
	</div>
</div>
