<script src="js/rubric.js"></script>
<div class="rubrics">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h3>Crear rúbrica</h3>
				<div class="container">
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
						<table class=" table rubric">
							<thead>
								<tr style="text-align: center;">
									<td>Criterio</td>
									<td>Nivel 4</td>
									<td>Nivel 3</td>
									<td>Nivel 2</td>
									<td>Nivel 1</td>
								</tr>
							</thead>
							<tbody>
								<tr>

								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="5" style="text-align: left;">
										<div align="right">
											<input type="button" class="btn btn-md" id="addrow"
												value="Crear criterio" />
											<button name="submit" type="submit"
												class="btn btn-primary btn-md" value="Crear rúbrica" />
											Crear rúbrica
										</div>
									</td>
								</tr>
								<tr>
								</tr>
							</tfoot>
						</table>
					</form>
				</div>
			</div>
		</div>
		<div class="row">
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