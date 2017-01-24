<!DOCTYPE html>
<?php
require_once (dirname (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) . '/config.php');
include 'headerMy.php';
?>
<body>
<section class="createActivity">
	<div class="container">
		<div class="row">
		<h3></h3>
		<h2>Editar actividad</h2>
		<div class="col-md-1"></div>
		<div class="col-md-9">
		<?php include '../editar.php'; ?>
		</div>			
	</div>
</section><!-- FIN BUSCADOR -->

</body>
<?php include 'footer.php'; ?>
