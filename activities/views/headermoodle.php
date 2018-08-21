<?php
$homeUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/index.php' );
$searchUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/search.php' );
$createactivityUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/createactivity.php' );
$myUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/my.php' );
$coursesUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/my.php' );
$markingUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/marking.php');
$loginUrl = new moodle_url ( $CFG->wwwroot . '/login/index.php' );
if (isloggedin ()) {
	$logoutUrl = new moodle_url ( $CFG->wwwroot . '/login/logout.php', array (
			'sesskey' => $USER->sesskey
	) );
	$image = new moodle_url ( $CFG->wwwroot . '/user/pix.php/' . $USER->id . '/f2.jpg' );
}
if(isset($tab)){
	var_dump($url);
}
?>
<meta charset="UTF-8">
<title>Escribiendo online</title>
<!-- CSS Font, Bootstrap, style de la página y auto-complete  -->
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/style_escribiendo.css" rel="stylesheet">


<!-- Fin CSS -->
<!-- Importar  Scripts Javascript -->

<!-- Fin Script Javascript -->
<!-- Scripts JQuery -->
<link rel="stylesheet" type="text/css"
	href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css">




<!-- Script para filtro de genero -->
<header class="pageheader">

	<nav class="navbar navbar-default navbar-fixed-top pageheader">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed"
					data-toggle="collapse" data-target="#navbar" aria-expanded="false"
					aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span> <span
						class="icon-bar"></span> <span class="icon-bar"></span> <span
						class="icon-bar"></span>
				</button>
				<a class="navbar-brand logos_head" href="<?=$homeUrl?>"></a>
			</div>

			<div id="navbar" class="navbar-collapse collapse">

				<ul class="nav navbar-nav navbar-right">
					<li style="padding-top: 10px">
						<form class="navbar-form navbar-right" method="post"
							action="search.php">
							<input type="text" class="form-control"
								placeholder="Buscar actividades" name="search"> <input
								type="hidden" name="type" value="1">
							<input class="btn btn-md btn-primary" type="submit" value="Buscar">
						</form>
					</li>

					<li></li>

				</ul>
				<ul class="nav navbar-nav navbar-left">
					<li class="text_nav"><a href="<?=$homeUrl?>">Inicio</a></li>
					<li class="text_nav"><a href="#contact">Actividades</a></li>

					<li class="text_nav"><a href="#about">Nosotros</a></li>

					<li class="text_nav"><a href="#about">Proyecto</a></li>

				</ul>
				<ul class="nav navbar-nav navbar-right">
             <?php
													
													if (isloggedin ()) {
														
														?>
          
                   <li class="dropdown"><a class="dropdown-toggle"
						data-toggle="dropdown" href="#"><?=$USER->firstname;?> <span
							class="caret"></span></a>
						<ul class="dropdown-menu">
						
							<li><a href="<?= $myUrl ?>">Mis Cursos</a></li>
							<li><a href="<?= $myUrl ?>">Mis Actividades</a></li>
							<li><a href="<?= $createactivityUrl ?>">Crear Actividad</a></li>
							<li class="divider"></li>
							<li>
								<div class="navbar-login navbar-login-session">
									<div class="row">
										<div class="col-lg-12">
											<p>
												<a href="<?= $logoutUrl ?>" class="btn btn-danger btn-block">Cerrar
													Sesion</a>
											</p>
										</div>
									</div>
								</div>
							</li>
						</ul></li>
                
             
             <?php }else{?> 
              <li class="text_nac"><a href="<?=$loginUrl?>"><img
							src="img/header/ingreso_05.png"></a></li>
             <?php }?></ul>


			</div>


		</div>
		<!--/.nav-collapse -->
		</div>
	</nav>
</header>
