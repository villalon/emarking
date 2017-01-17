<?php
require_once (dirname (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) . '/config.php');
?>
<!DOCTYPE html>
<?php include 'headerMy.php'; ?>

<!-- BODY -->
<body>
	<section class="perfil">
		<div class="container">
			<div class="row">
			<h2></h2>
				<div class="col-md-12">
						<div class="panel panel-default">
							<div class="panel-body">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#h">Corrección</a></li>
								<li><a data-toggle="tab" href="download">Descargar y Digitalizar</a></li>
								<li><a data-toggle="tab" href="#menu2">Reportes</a></li>
							</ul>
<?php 						
include  $CFG->dirroot . '/mod/emarking/view.php';
?>
 
				</div>
					</div>
									
	</div></div></div>
	</section>
	<!-- FIN BUSCADOR -->
</body>
<?php include 'footer.php'; ?>