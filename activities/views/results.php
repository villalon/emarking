	<div class="card-columns">
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
