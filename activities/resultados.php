<?php 
require_once ('locallib.php');
?>
<section class="barraResultado">
<hr>
	<div class="container">
		<div class="row">
			<div class="col-md-1">
			</div>
			<div class="col-md-8">
				<p><?php echo count($results);?> Resultado(s) </p>
				
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
		<div id="filtros" class="col-md-3" style="text-align: left">
			<div class="panel panel-default">
				<div class="panel-body">	
					<h1 style="text-align: left">Filtros</h1>
					<h3 style="text-align: left">Tipo de recurso</h3>
					<ul>
						<li>
						<div class="checkbox">
						<input type="checkbox" name="rubrica" value="1"> Actividad
						</div>
						</li>
						<li>
						<div class="checkbox">
						<input type="checkbox" name="rubrica" value="1"> Rúbrica
						</div>
						</li>
					</ul>
					<h3 style="text-align: left">Géneros</h3>
					<ul id="genero">
						<?php
						// recorre el arreglo que contiene todos los generos, por cada uno crea un checkbox y una lista
						for($i=0;$i<count($generos);$i++){
							echo'<li>
							<div class="checkbox">
							<input type="checkbox" name="'.$generos[$i].'"> '.$generos[$i].'
							</div>
							</li>';
						}
						?>
					</ul>
					<div id="loadMore">Ver más...</div>
					<div id="showLess">Ver menos...</div>	
					<h3 style="text-align: left"></h3>
					
					<h3 style="text-align: left">Calificación</h3>
					<ul id="calificacion">
						<li>
						<div class="checkbox">
							<input type="checkbox" name="unaEstrella" value="1">
							<span class="glyphicon glyphicon-star" aria-hidden="true" ></span>
							<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
						</div>
						</li>
						<li>
						<div class="checkbox">
							<input type="checkbox" name="dosEstrella" value="1">
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
						</div>
						</li>
						<li>
						<div class="checkbox">
							<input type="checkbox" name="tresEstrella" value="1">
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
						</div>
						</li>
						<li>
						<div class="checkbox">
							<input type="checkbox" name="cuatroEstrella" value="1">
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
						</div>
						</li>
						<li>
						<div class="checkbox">
							<input type="checkbox" name="cincoEstrella" value="1">
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
							<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
						</div>
						</li>
					</ul>					
				</div>
			</div>
		</div>
		<div id="resultados" class="col-md-9" style="text-align: left">
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