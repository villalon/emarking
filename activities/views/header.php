<?php
//$homeUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/index.php' );
//$searchUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/views/search.php' );
//$createactivityUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/views/create.php' );
//$myUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/views/my.php' );
//$coursesUrl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/views/my.php' );
//$loginUrl = new moodle_url ( $CFG->wwwroot . '/login/index.php' );
/*if (isloggedin ()) {
	$logoutUrl = new moodle_url ( $CFG->wwwroot . '/login/logout.php', array (
			'sesskey' => $USER->sesskey
	) );
	$image = new moodle_url ( $CFG->wwwroot . '/user/pix.php/' . $USER->id . '/f2.jpg' );
}*/
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
<link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="js/bootstrap.js"></script>

<!-- Script para filtro de genero -->
<header class="header">

<nav class="navbar navbar-default navbar-fixed-top header">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand logos_head" href="#"></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          
          
               <ul class="nav navbar-nav navbar-right" >
                 <li><form class="navbar-form navbar-right" method="post" action="search.php">
                 <input type="text" class="form-control" placeholder="Buscar actividades" name="search">
                  <input type="hidden" name="type" value="1">
                 <button class="btn btn-md btn-primary" type="submit" >Buscar </button>
            </form></li>
               </ul>
              
              
              
               <ul class="nav navbar-nav navbar-right">
                 <li class="text_nav"><a href="<?= $homeUrl?>">Inicio</a></li>
                 <li class="dropdown text_nav">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Nosotros <span class="caret"></span></a>
               <ul class="dropdown-menu">
                <li><a href="#">Quienes Somos</a></li>
                <li><a href="#">Profesores</a></li>
                <li><a href="#">Correctores</a></li>
                <li><a href="#">Alumnos</a></li>
              </ul>
            </li>
            
              <li class="text_nav"><a href="#about">Proyectos</a></li>
              <li class="text_nav"><a href="#contact">Actividades</a></li>
            
              </ul>

          </div>

          
        </div><!--/.nav-collapse -->
      </div>
    </nav>
      </header>