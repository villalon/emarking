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
$css=new moodle_url($CFG->wwwroot.'/mod/emarking/activity/css/style_escribiendo.css');

?>
<meta charset="UTF-8">
<title>Escribiendo online</title>
<!-- CSS Font, Bootstrap, style de la página y auto-complete  -->
<link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="../css/style_escribiendo.css" rel="stylesheet" type="text/css">

<!-- Fin CSS -->
<!-- Importar  Scripts Javascript -->

<!-- Fin Script Javascript -->
<!-- Scripts JQuery -->

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="../js/bootstrap.js"></script>

<!-- Script para filtro de genero -->
<header class="header">	
  
   <div class="navbar navbar-default" role="navigation">
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