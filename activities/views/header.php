<?php
$homeUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/index.php' );
$searchUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/search.php' );
$createactivityUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/createactivity.php' );
$myUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/my.php' );
$coursesUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/my.php' );
$loginUrl = new moodle_url ( $CFG->wwwroot . '/login/index.php' );
if (isloggedin ()) {
	$logoutUrl = new moodle_url ( $CFG->wwwroot . '/login/logout.php', array (
			'sesskey' => $USER->sesskey 
	) );
	$image = new moodle_url ( $CFG->wwwroot . '/user/pix.php/' . $USER->id . '/f2.jpg' );
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
<?php 
$fixed="";
$fix=false;
if(!isset($tab)){
	echo '<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>';
}else{
	$fixed="navbar-fixed-top";
	$fix=true;
}
?>
<script>
$(function() {
	var width=$( window ).width();
	var height=$( window ).height();
	var sticky = $('.pageheader');
	var txt = $('.txt');
	var logos = $('.navbar-brand');
	var search = $('.search');
	var fix = "<?php echo $fix; ?>";
	 if(fix != true){

	 if(width < 990){
   	  txt.removeClass('text_nav');
   	  txt.addClass('text_nav_1100');
	      }
	$(window).scroll(function(){
		  
		      scroll = $(window).scrollTop();
		  if (scroll >= 100){ 
			  sticky.addClass('navbar-fixed-top');
			  logos.removeClass('logos_head');
			  logos.addClass('logos_head_fixed');
			  search.hide();
			  if(width < 990){
			  txt.addClass('text_nav_fixed_1100');
			  txt.removeClass('text_nav_1100')
		  		}
			  }
		  else {
			  sticky.removeClass('navbar-fixed-top')
			  logos.addClass('logos_head');
			  logos.removeClass('logos_head_fixed');
			  search.show();
			  if(width < 990){
			  txt.removeClass('text_nav_fixed_1100')
			  txt.addClass('text_nav_1100')
		  }
			  };
		});
	  }
	}); 
</script>

<!-- Script para filtro de genero -->
<header class="pageheader">

	<nav class="navbar navbar-default <?= $fixed ?> pageheader">
		<div class="container">
		<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
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
		</div>
		<div class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
			<div id="navbar" class="navbar-collapse collapse">
				<div class="row">
				<ul class="nav navbar-nav navbar-right search">
					<li style="padding-top: 10px">
						<form class="navbar-form navbar-right" method="post"
							action="search.php">
							<input type="text" class="form-control"
								placeholder="Buscar actividades" name="search" size="40"> <input
								type="hidden" name="type" value="1">
							<button class="btn btn-md btn-primary" type="submit">Buscar</button>
						</form>
					</li>

					<li></li>

				</ul></div>
				<div class="row">
				<ul class="nav navbar-nav navbar-left text_nav">
					<li class="txt text_nav"><a href="<?=$homeUrl?>">Inicio</a></li>
					<li class="txt text_nav"><a href="<?=$searchUrl?>">Actividades</a></li>
					<li class="txt text_nav"><a href="#contact">Foro</a></li>

				</ul>
				<ul class="nav navbar-nav navbar-right">
             <?php
													
													if (isloggedin ()) {
														
														?>
          
                   <li class="dropdown txt text_nav"><a class="dropdown-toggle"
						data-toggle="dropdown" href="#"><?=$USER->firstname;?> <span
							class="caret"></span></a>
						<ul class="dropdown-menu">
							<!--- <li><a href="<?= $myUrl ?>">Mi perfil</a></li> --->
							<li><a href="<?= $myUrl ?>">Mis cursos</a></li>
							<li><a href="<?= $myUrl ?>">Mis actividades</a></li>
							<li><a href="<?= $createactivityUrl ?>">Crear actividad</a></li>
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
		</div>
	</nav>
	
</header>
