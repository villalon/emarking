<!DOCTYPE html>





	<!-- fIN DEL header -->
	<!-- BUSCADOR -->
	<div class="profile">
		<div class="container">
			<div class="row">
				<h2></h2>
				<div class="col-md-1">
				</div>
				<div class="col-md-9">
					
						
						<div class="panel panel-default">
							<div class="panel-body">
					<h1>Mis Correcciones</h1>
					<?php 
					foreach ( $result as $instace ) {
							$emarkingurl=new moodle_url($CFG->wwwroot .'/mod/emarking/activities/marking.php', array(
									"id" => $instace->id,'tab'=>1));
							echo '<a href="'.$emarkingurl.'">'.$instace->name.'</a><br>';
							
						}
						?>
					</div>
					
					</div>
					
				</div>
				</div>
					</div>
									
	
	</div>
	<!-- FIN BUSCADOR -->
</body>
