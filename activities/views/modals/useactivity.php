<div id="myModalUse" class="modal fade" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Utilizar actividad</h4>
			</div>
			<div class="modal-body">
		 <form role="form" action="newsubmission.php">
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