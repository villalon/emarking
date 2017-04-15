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
								name="rubricname" id="rubricname">
						</div>
						<div class="form-group">
							<label>Descripción</label>
							<textarea class="form-control" rows="5" name="rubricdescription"></textarea>
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
		      $bol=true;
		      $next=4;
		      foreach ($levels as $level){
		      	if($level->score == $level->max){
		      		$cols ='<tr">';
		      		$cols .= '<td class="col-sm-2" style="text-align: center;vertical-align: middle;">';
		      		$cols .= '<span>'.$level->criteria.'</span></td>';
		      		
		      }
		      
		      if($next!=$level->score){
		      	if($next > $level->score){
		      	$cols .= '<td class="col-sm-2" style="vertical-align: middle;">';
		      	$cols .= '<span ></span></td>';
	
		      	}
		      	
		      }
		      	$cols .= '<td class="col-sm-2" style="vertical-align: middle;">';
		      	$cols .= '<span >'.$level->definition.'</span></td>';
		      
		      
		      	$next=$level->score-1;
		      	
		      	
		      	
		      	if($level->score == 1){
		      		$cols .= '<td class="col-sm-1" style="vertical-align: middle;"><input type="button" id='.$level->criterionid.' class="ibtnAdd btn btn-md btn-success "  value="Agregar"></td>';
		      		$cols .='</tr>';
		      		echo $cols;
		      		$next=4;
		      	}	
	      }
								?>
        
					</tbody>

				</table>
			</div>

		</div>
	</div>
</div>
