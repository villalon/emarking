
<div class="results">
	<div class="container">		
		<div id="resultados" class="col-md-10" style="text-align: left;padding-bottom: 75px;">
			<?php			
				if ( count($results) > 0 ) {
					foreach ( $results as $result ) {
						activities_show_result($result);
					}
				} else {
					echo '<h3>No se encontraron resultados</h3>';
				}
			?>
			</div>	
			</div>
			</div>			
		</div>
	</div>
</div>