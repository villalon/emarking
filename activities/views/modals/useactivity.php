<div id="myModalUse" class="modal fade" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Usar actividad</h4>
			</div>
				<div class="modal-body">
		 		<form role="form" action="newsubmission.php">
									<label>Curso </label><br> <select class="form-control" name="course" required>
										<option value="0">Seleccione el curso</option>
 									 <?php
							foreach ( $asteachercourses as $key => $asteachercourse ) {
								echo '<option value="' . $key . '"> ' . $asteachercourse . ' </option>';
							}
							?>
  								</select>
  								<input type="hidden" name="askMarking"
										value=1><input type="hidden"
										value="<?php echo $activityid; ?>" name="id">
  								<br> <label>Forma de subir los textos</label><br/><select id="submissiontype" class="form-control" name="submissiontype" onchange="showmsg();">
  												<option value="1">Profesor escanea</option>
  												<option value="2">Estudiantes suben su PDF</option>
  											</select><br><label><input type="checkbox" name="printteaching"
										value=1>&nbsp;Incluir didáctica en impresión</label>
  								<input type="hidden" name="changelog"
										value=1>

  								<div id="submissiontypemessage" style="display:none; color:#ff0000;">Los estudiantes deben guardar sus documentos en formato PDF para poder subirlos a la plataforma.</div>
								<div style="text-align: right">
								<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
								<script>
								function showmsg() {
									var submissiontype = document.getElementById('submissiontype');
									var submissiontypemsg = document.getElementById('submissiontypemessage');
					                if(!submissiontype || !submissiontypemsg) {
							             return;
							        }
						            var subtype = submissiontype.options[submissiontype.selectedIndex].value;
						            console.log(subtype);
						            // QR code.
							        if (subtype == '2') {
							        	submissiontypemsg.style.display = 'block';
						            } else {
							        	submissiontypemsg.style.display = 'none';
						            }
								}
								</script>
  				
			<?php
							if ($countcourses > 0) {
								?>
  				<button type="submit" class="btn btn-primary">Usar</button>
  			<?php }else { ?>
  				<button type="submit" class="btn btn-primary" disabled>Usar</button>
 			<?php }?></div>
						</form>
 			
		  </form>
			</div>
			
		</div>

	</div>
</div>
