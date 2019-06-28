<script src="js/rubric.js"></script>
<style>
.rubric td {
    width: 15%;
}
</style>
<div class="rubrics">
		<div class="row">
			<div class="col-md-12">
				<h3>Crear rúbrica</h3>
					<form action="" method="post" onsubmit="return validateform()" name="rubricCreator">
						<div class="form-group">
							<label>Nombre:</label> <input type="text" class="form-control"
								name="rubricname" id="rubricname" value="<?=$rubricname?>">
						</div>
						<div class="form-group">
							<label>Descripción</label>
							<textarea class="form-control" rows="5" name="rubricdescription"><?=$rubricdescription?></textarea>
						</div>
						<br> <br>
						<table class="table rubric">
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
<!-- 		<div class="row">
			<div class="col-md-12">
				<h3>Buscador de criterios</h3>
				<table id="example" class="table rubricSearch">
					<thead>
						<tr style="text-align: center;">
							<td>Criterio</td>
							<td>Nivel 4</td>
							<td>Nivel 3</td>
							<td>Nivel 2</td>
							<td>Nivel 1</td>
							<td></td>
						</tr>
					</thead>
					<tbody>
						
		      <?php
		      add_row($levels,2);
		      ?>
        
					</tbody>

				</table>
			</div>

		</div>
	</div> -->

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