
<div class="results">
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
</div>