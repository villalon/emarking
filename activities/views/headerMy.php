<?php 
$homeUrl=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/views/index.php');
$logoutUrl=new moodle_url($CFG->wwwroot.'/login/logout.php',array('sesskey'=>$USER->sesskey));
$searchUrl=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/views/search.php');
$createactivityUrl=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/views/create.php');
$myUrl=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/views/my.php');
$coursesUrl=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/views/my.php');
$loginUrl=new moodle_url($CFG->wwwroot.'/login/index.php');
if (isloggedin ()) {
	$image=new moodle_url($CFG->wwwroot.'/user/pix.php/'.$USER->id.'/f2.jpg');
}

?>
<meta charset="UTF-8">
<title>Escribiendo online</title>
<!-- CSS Font, Bootstrap, style de la página y auto-complete  -->
<link rel="stylesheet" href="../css/font-awesome.min.css">
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../auto-complete.css">
<!-- Fin CSS -->
<!-- Css traidos desde google, no sé cuales realmete se usan  -->
<link
	href='http://fonts.googleapis.com/css?family=Open+Sans:600italic,400,800,700,300'
	rel='stylesheet' type='text/css'>
<link
	href='http://fonts.googleapis.com/css?family=BenchNine:300,400,700'
	rel='stylesheet' type='text/css'>
<link rel="stylesheet"
	href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300">
<link rel="stylesheet"
	href="https://cdn.rawgit.com/yahoo/pure-release/v0.6.0/pure-min.css">
<!-- Fin CSS de google -->
<!-- Importar  Scripts Javascript -->
<script src="../js/modernizr.js"></script>

<!-- Fin Script Javascript -->
<!-- Scripts JQuery -->
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
<link rel="stylesheet"
	href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>

<!-- Script para filtro de genero -->
<header class="top-header">	
 <link rel="stylesheet" href="../css/bootstrap.min.css">   
   <div class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container"> 
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span> 
            </button>
           
            <a target="_blank" href="<?= $homeUrl ?>" class="navbar-brand">Escritura</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li><a href="#">Inicio</a></li>
                <li class=""><a href="<?= $searchUrl ?>" target="_blank">Actividades</a></li>
                 <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Información
                    <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="#">Sobre Nosotros</a></li>
                        <li><a href="#">Profesores</a></li>
                        <li><a href="#">Corectores</a></li>
                    </ul>
                 </li>              
             </ul>
            <ul class="nav navbar-nav navbar-right">
          <li>
        	<form class="navbar-form" role="search" method="post" action="search.php">
        	<div class="input-group">
            <input type="text" class="form-control" placeholder="Buscar Actividades" name="search">
            <input type="hidden" name="type" value="1">
            <div class="input-group-btn">
            <button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
            </div>
       		 </div>
       		 </form>
       		 </li>
            
             <?php if (isloggedin ()) {            	 
             	?>
             
           
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="glyphicon glyphicon-user"></span> 
                        <strong><?= $USER->username ?></strong>
                        <span class="glyphicon glyphicon-chevron-down"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                        
                            <div class="navbar-login">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <p class="text-center">
                                            <img src="<?= $image ?>" alt="Smiley face" height="90"	width="90">
                                        </p>
                                    </div>
                                    <div class="col-lg-8">
                                        <p class="text-left"><strong><?php echo $USER->firstname.' '.$USER->lastname; ?></strong></p>
                                        <p class="text-left small"><a href="<?= $myUrl ?>">Mi perfil</a></p>
                                        <p class="text-left small"><a href="my.php">Mis actividades</a></p>
                                        <p class="text-left small"><a href="<?= $createactivityUrl ?>">Crear Actividad</a></p>
                                        <p class="text-left small"><a href="<?= $coursesUrl ?>">Mis cursos</a></p>
                                        
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <div class="navbar-login navbar-login-session">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <p>
                                            <a href="<?= $logoutUrl ?>" class="btn btn-danger btn-block">Cerrar Sesion</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
            
            <?php }else{?>
           
                <li>
                  <a href="<?= $loginUrl ?>">
                        <strong>Entrar</strong>
                        
                    </a>
                    
               </li>
            
            <?php }?>
        </ul>
        </div>
        </div>
        </div>
        </header>