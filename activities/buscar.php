<?php
require_once (dirname(dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
require_once ('generos.php');
GLOBAL $USER, $CFG;
$teacherroleid = 3;
$logged = false;

if (isloggedin ()) {
	$logged = true;
	$courses = enrol_get_all_users_courses ( $USER->id );
	$countcourses = count ( $courses );
	foreach ( $courses as $course ) {
		$context = context_course::instance ( $course->id );
		$roles = get_user_roles ( $context, $USER->id, true );
		foreach ( $roles as $rol ) {
			if ($rol->roleid == $teacherroleid) {
				$asteachercourses [$course->id] = $course->fullname;
			}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- Head --> 
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<title>Lorem Ipsum</title>
<!-- CSS Font, Bootstrap, style de la página y auto-complete  --> 
<link rel="stylesheet" href="css/font-awesome.min.css">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="auto-complete.css">
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
<script src="js/modernizr.js"></script>

<!-- Fin Script Javascript -->
<!-- Scripts JQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>

    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<!-- Script para filtro de genero -->
<script>
$(document).ready(function () {
    size_li = $("#genero li").size();
    x=3;
    $('#genero li:lt('+x+')').show();
    $('#loadMore').click(function () {
        x= size_li;
        $('#genero li:lt('+x+')').show();
        $('#genero').show().siblings('#loadMore').hide();
        $('#genero').show().siblings('#showLess').show();
    });
    $('#showLess').click(function () {
        x= 3;
        $('#genero li').not(':lt('+x+')').hide();
        $('#genero').show().siblings('#loadMore').show();
        $('#genero').show().siblings('#showLess').hide();
    });
   
});
</script>
<!-- Script del slider para filtro de Tiempo estimadoión -->
<script type="text/javascript">
$(function() {
    $( "#slider-range" ).slider({
      range: true,
      min: 0,
      max: 180,
      values: [1,90],
      slide: function( event, ui ) {
        $( "#amount" ).val(  ui.values[ 0 ] + " - " + ui.values[ 1 ] );
      }
    });
    $( "#amount" ).val(  $( "#slider-range" ).slider( "values", 0 ) +
      " - " + $( "#slider-range" ).slider( "values", 1 ) );
  });
</script>
<!-- Fin scripts JQuery -->
</head>
<!-- Fin de HEAD -->

<!-- BODY -->
<body>
<!-- Header  -->
<header class="top-header">	
<?php include 'header.php'; ?>
</header>
<!-- fIN DEL header -->

<!-- BUSCADOR -->
<?php include_once 'forms/search.php';?>
<!-- FIN BUSCADOR -->

<!-- Barra de información sobre el resultado, incluye un ordenador del mismo -->

<!-- FIN BARRA DE INFORMACIÓN DE RESULTADO -->	

<!-- RESULTADOS -->
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

	switch ($_POST['type']){
	case 1:
		$search=$_POST['search'];
		$sql="SELECT *
			  FROM {emarking_activities}
			  WHERE parent IS NULL AND 
			  		(title like '%$search%' OR 
					description like '%$search%' OR
					audience like '%$search%' OR
					instructions like '%$search%' OR
					teaching like '%$search%' OR
					languageresources like '%$search%')";
		$results = $DB->get_records_sql($sql);
		break;
	case 2;
	
		break;
	case 3:
		$results=$DB->get_records('emarking_activities',array('comunicativepurpose'=>$_POST['pc'],'parent'=>null));
		break;
	case 4:
		$results=$DB->get_records('emarking_activities',array('genre'=>$_POST['genero'],'parent'=>null));
		
		break;
	}
	
include_once 'resultados.php';
}

?>
<!-- FIN RESULTADOS -->	
</body>
<!-- footer starts here -->
<footer class="footer clearfix">
	<div class="container">
		<div class="row">
			<div class="col-xs-6 footer-para">
				<p>&copy; All right reserved</p>
			</div>
			<div class="col-xs-6 text-right">
				<a href=""><i class="fa fa-facebook"></i></a> <a href=""><i
					class="fa fa-twitter"></i></a>
			</div>
		</div>
	</div>
</footer>
<script src="js/bootstrap.min.js"></script>