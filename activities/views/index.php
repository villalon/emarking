<?php
$activities = emarking_get_random_activities();
?>
<!-- Carousel -->
<div class="row">
	<div id="carouselExampleControls" class="carousel slide"
		data-ride="carousel" style="width:100%">
		<div class="carousel-inner">
			<div class="carousel-item active">
				<img class="d-block w-100" src="img/slide1.png" alt="First slide">
			</div>
			<div class="carousel-item">
				<img class="d-block w-100" src="img/slide02.png" alt="Second slide">
			</div>
		</div>
		<a class="carousel-control-prev" href="#carouselExampleControls"
			role="button" data-slide="prev"> <span
			class="carousel-control-prev-icon" aria-hidden="true"></span> <span
			class="sr-only">Previous</span>
		</a> <a class="carousel-control-next" href="#carouselExampleControls"
			role="button" data-slide="next"> <span
			class="carousel-control-next-icon" aria-hidden="true"></span> <span
			class="sr-only">Next</span>
		</a>
	</div>
</div>
<!-- Carousel End -->
<!-- Actividades -->
<div class="row row_actividades">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 tit_actv"></div>
</div>
<div class="row row_actividades">
	<div class="col-xs-12 col-sm-5 col-md-4 col-lg-4 col_height" style="background-color: #085B7F;">
		<?php if(count($activities) > 0) { ?>
		<div class="subtit_actv h4">Género: <?=$activities[0]['genre']?></div>
		<div class="h2tit_actv h2">
			<a class="h2tit_actv" href="<?=$activities[0]['link']?>"> <?=$activities[0]['title']?></a>
		</div>
		<hr>
		<br>
		<p class="text-justify text_actv"><?=$activities[0]['description']?></p>
		<?php } ?>
	</div>
	<div class="col-xs-12 col-sm-2 col-md-4 col-lg-4 img1_actv"></div>
	<div class="col-xs-12 col-sm-5 col-md-4 col-lg-4 col_height" style="background-color: #097C5E;">
	<?php if(count($activities) > 1) { ?>
		<div class="subtit_actv h4">Género: <?=$activities[1]['genre']?></div>
		<div class="h2tit_actv h2">
			<a class="h2tit_actv" href="<?=$activities[1]['link']?>"> <?=$activities[1]['title']?></a>
		</div>
		<hr>
		<br>
		<p class="text-justify text_actv"><?=$activities[1]['description']?></p>
		<?php } ?>
		</div>
</div>
<div class="row row_actividades">
	<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 img2_actv"></div>
	<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 col_height"
		style="background-color: #7C3709;">
					<?php if(count($activities) > 2) { ?>
					<h4 class="subtit_actv">Género: <?=$activities[2]['genre']?></h4>
		<h2 class="h2tit_actv">
			<a class="h2tit_actv" href="<?=$activities[2]['link']?>"> <?=$activities[2]['title']?></a>
		</h2>
		<hr>
		<br>
		<p class="text-justify text_actv"><?=$activities[2]['description']?></p>
					<?php } ?>
				</div>
	<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 col_height"
		style="background-color: #757A0A;">
					<?php if(count($activities) > 3) { ?>
					<h4 class="subtit_actv">Género: <?=$activities[3]['genre']?></h4>
		<h2 class="h2tit_actv">
			<a class="h2tit_actv" href="<?=$activities[3]['link']?>"> <?=$activities[3]['title']?></a>
		</h2>
		<hr>
		<br>
		<p class="text-justify text_actv"><?=$activities[3]['description']?></p>
					<?php } ?>
				</div>
	<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 img2_actv"></div>
</div>
<!-- Actividades END -->

<!-- Caracteristicas -->
<div class="row row_caract">
		<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3" align="center">
			<img src="img/caract_01_01.png">
			<h4>Crea tu Perfil</h4>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3" align="center">
			<img src="img/caract_01_03.png">
			<h4>Crea Actividades</h4>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3" align="center">
			<img src="img/caract_01_05.png">
			<h4>Participa en el Foro</h4>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3" align="center">
			<img src="img/caract_01_08.png">
			<h4>Conviértete en Corrector</h4>
		</div>
</div>
<!-- Caracteristicas End -->
<!-- Quienes Somos -->
<div class="row row_info">
	<div class="col-xs-12 col-sm-3 col-md-6 col-lg-6 tit_blue">
		<h2>Quiénes Somos</h2>
		<br>
		<p class="text_grey1" align="justify">Escribiendo.online es una
			comunidad de aprendizaje comprometida con la enseñanza de la
			escritura en las aulas chilenas. Sabemos que aprender a escribir es
			un proceso complejo en el que los estudiantes necesitan ser guiados
			por sus profesores. Pero también sabemos lo difícil y lento que puede
			resultar revisar y retroalimentar la escritura de los estudiantes.</p>
		<p class="text_grey1" align="justify">Por esto hemos creado esta
			plataforma colaborativa en línea que permite a los profesores
			compartir actividades, videos y pautas de evaluación de la escritura,
			así como corregir y retroalimentar en línea los textos de sus
			estudiantes o recibir una evaluación experta de los textos de sus
			alumnos.</p>
	</div>

	<div class="col-xs-12 col-sm-9 col-md-6 col-lg-6 tit_blue"
		align="center">
		<iframe width="560" height="315"
			src="https://www.youtube.com/embed/CAKj1hkuJIo" frameborder="0"
			allow="autoplay; encrypted-media" allowfullscreen></iframe>
	</div>
</div>
<!-- Quienes somos End -->
