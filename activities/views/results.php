<?php 
require_once ('../locallib.php');
?>
<section class="barraResultado">
<hr>
	<div class="container">
		<div class="row">
			<div class="col-md-1">
			</div>
			<div class="col-md-8">
				<p><? count($results) ?> Resultado(s) </p>
				
			</div>
			<div class="col-md-2">
				<select class="form-control">
					<option>Ordenar por:</option>
 					<option>Comentarios</option>
  					<option>Likes</option>
  					<option>Vistos</option>
				</select>
			</div>
		</div>
	</div>
</section>
<section class="resultados">
	<div class="container">
		<div id="filtros" class="col-md-1" style="text-align: left">
			
		</div>
		<div id="resultados" class="col-md-10" style="text-align: left">
			<?php 
			if(count($results)!=0){
			foreach($results as $result){
				echo show_result($result);
			}
			}else{
				echo '<h3>No se encontraron resultados</h3>';
			}
			?>
			</div>	
			</div>
			</div>
			
		</div>
	</div>
</section>