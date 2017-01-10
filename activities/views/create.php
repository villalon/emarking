<!DOCTYPE html>
<?php
require_once (dirname (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) . '/config.php');
include ('header.php');
?>
<body>
<section class="createActivity">
	<div class="container">
		<div class="row">
		<h3></h3>
		<h2>Crear una actividad</h2>
		
		<div class="col-md-10">
		<?php  include ($CFG->dirroot. '/mod/emarking/activities/crear.php'); ?>
		</div>			
	</div>
</section><!-- FIN BUSCADOR -->

</body>
<?php include 'footer.php'; ?>
