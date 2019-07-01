<script src="js/rubric.js"></script>
<style>
.rubric td {
    width: 15%;
}
</style>
<div class="rubrics">
		<div class="row">
			<div class="col-md-12">
					<form action="" class="needs-validation" method="post" onsubmit="return validateform()" name="rubricCreator">
						<div class="form-group">
							<label for="rubricname">Nombre</label> <input type="text" class="form-control"
								name="rubricname" id="rubricname" value="<?=$rubricname?>">
							<small id="rubricnameHelp" class="form-text text-muted">Nombre de la rúbrica, p.ej: Evaluación de estructura y gramática.</small>
							<div class="invalid-feedback">
          Ingrese un nombre que contenga al menos tres letras
        					</div>
						</div>
						<div class="form-group">
							<label for="rubricdescription">Descripción</label>
							<textarea id="rubricdescription" class="form-control" rows="5" name="rubricdescription"><?=$rubricdescription?></textarea>
						</div>
						<table class="table rubric" id="table-rubric">
							<thead>
								<tr style="text-align: center;">
									<th>Criterio</th>
									<th>Nivel 4</th>
									<th>Nivel 3</th>
									<th>Nivel 2</th>
									<th>Nivel 1</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="6" style="text-align: left;">
											<input type="button" class="btn btn-md" id="addrow"
												value="Agregar criterio" />
									</td>
								</tr>
								<tr>
									<td colspan="6" style="text-align: right;">
											<button name="submit" type="submit"
												class="btn btn-primary btn-md" value="Guardar cambios" />
											Guardar cambios
									</td>
								</tr>
							</tfoot>
						</table>
					</form>
			</div>
		</div>
<?php 
if($id!=0){
foreach($rubric as $criteria){
$json=get_criteria($criteria->id,true);?>
<script type="text/javascript">
var json = '<?php echo $json;?>';
add_row(json)
this.counter++
</script>
<?php }}?>