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
			 	<form action="pdfcreator.php" method="get">
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
  					<input type="hidden" name="id" value="<?= $activityid;?>">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
  				<button type="submit" class="btn btn-primary" >Descargar</button>
  				</div>
		  </form>
			</div>
			
		</div>

	</div>
</div>