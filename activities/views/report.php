<?php
require_once (dirname (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) . '/config.php');
$id = required_param('id', PARAM_INT);
$markingUrl=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/views/view.php',array('id'=>$id));
$downloadUrl=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/views/download.php',array('id'=>$id));
$reportsUrl=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/views/report.php',array('id'=>$id));
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
 							 <li><a href="<?= $markingUrl ?>">Corrección</a></li>
 							 <li><a href="<?= $downloadUrl ?>">Descargar y digitalizar</a></li>
							 <li class="active"><a href="<?= $reportsUrl ?>">Reportes</a></li>
							</ul>
							
<?php 						
include  $CFG->dirroot . '/mod/emarking/reports/feedback.php';
?>
 
				</div>
					</div>
									
	</div></div></div>
	</section>
	<!-- FIN BUSCADOR -->
</body>
<?php include 'footer.php'; ?>