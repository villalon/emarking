<!DOCTYPE html>
<?php
require_once (dirname (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) . '/config.php');
include 'header.php';
?>

	<section class="slider" id="home">
		<div class="container-fluid">
			<div class="row">
				<div id="carouselHacked" class="carousel slide carousel-fade"
					data-ride="carousel">
					<div class="header-backup"></div>
					<!-- Wrapper for slides -->
					<div class="carousel-inner" role="listbox">
						<div class="item active">
							<img src="../img/slide-one.jpg" alt="">
							<div class="carousel-caption">
								<h1>Título 1</h1>
								<p>(1)Esto es un texto de ejemplo</p>

							</div>
						</div>
						<div class="item">
							<img src="../img/slide-two.jpg" alt="">
							<div class="carousel-caption">
								<h1>Título 2</h1>
								<p>(2)Esto es un texto de ejemplo</p>

							</div>

						</div>
						<!-- Controls -->

					</div>
				</div>
			</div>
	</section>
	<!-- end of slider section -->

	<!-- buscar section -->
	<section class="about text-center" id="buscar">
		<div class="container">
			<div class="row">
				<h2>Buscar</h2>
				<div class="col-md-12 col-sm-6">
					<form method="get" action="buscar.php" class="pure-form"
						style="border-top: 1px solid #eee; border-bottom: 1px solid #eee; background: #fafafa; padding: 20px 10px; text-align: center">
						<div class="single-about-detail clearfix">

							<div class="about-details">

								<div class="form-group">
									<div class="col-xs-3">

									</div>
									<div class="col-xs-7">
										<input id="recursos" autofocus type="text" name="name"
											placeholder="Nombre" style="width: 100%; max-width: 600px">
									</div>
									<div class="col-xs-2">
										<input type="submit" value="Buscar">

									</div>
								</div>
							</div>
						</div>
					</form>

				</div>


			</div>
		</div>
	</section>
	<!-- end of buscar section -->








	<!-- team section -->
	<section class="team" id="nosotros">
		<div class="container">
			<div class="row">
				<div class="team-heading text-center">
					<h2>Sobre nosotros</h2>
					<h4>Lorem Ipsum es simplemente el texto de relleno de las
						imprentas y archivos de texto. Lorem Ipsum ha sido el texto de
						relleno estándar de las industrias desde el año 1500, cuando un
						impresor (N. del T. persona que se dedica a la imprenta)
						desconocido usó una galería de textos y los mezcló de tal manera
						que logró hacer un libro de textos especimen. No sólo sobrevivió
						500 años, sino que tambien ingresó como texto de relleno en
						documentos electrónicos, quedando esencialmente igual al original.
						Fue popularizado en los 60s con la creación de las hojas
						"Letraset", las cuales contenian pasajes de Lorem Ipsum, y más
						recientemente con software de autoedición, como por ejemplo Aldus
						PageMaker, el cual incluye versiones de Lorem Ipsum.</h4>
					<h4>Lorem Ipsum es simplemente el texto de relleno de las
						imprentas y archivos de texto. Lorem Ipsum ha sido el texto de
						relleno estándar de las industrias desde el año 1500, cuando un
						impresor (N. del T. persona que se dedica a la imprenta)
						desconocido usó una galería de textos y los mezcló de tal manera
						que logró hacer un libro de textos especimen. No sólo sobrevivió
						500 años, sino que tambien ingresó como texto de relleno en
						documentos electrónicos, quedando esencialmente igual al original.
						Fue popularizado en los 60s con la creación de las hojas
						"Letraset", las cuales contenian pasajes de Lorem Ipsum, y más
						recientemente con software de autoedición, como por ejemplo Aldus
						PageMaker, el cual incluye versiones de Lorem Ipsum.</h4>

				</div>



			</div>
		</div>
	</section>
	<!-- end of team section -->
	<!-- about section -->
	<section class="about text-center" id="profesor">
		<div class="container">
			<div class="row">
				<h2>Profesores</h2>

				<h4>
					Lorem Ipsum es simplemente el texto de relleno de las imprentas y
					archivos de texto. Lorem Ipsum ha sido el texto de relleno estándar
					de las industrias desde el año 1500, cuando un impresor (N. del T.
					persona que se dedica a la imprenta) desconocido usó una galería de
					textos y los mezcló de tal manera que logró hacer un libro de
					textos especimen. No sólo sobrevivió 500 años, sino que tambien
					ingresó como texto de relleno en documentos electrónicos, quedando
					esencialmente igual al original. Fue popularizado en los 60s con la
					creación de las hojas "Letraset", las cuales contenian pasajes de
					Lorem Ipsum, y más recientemente con software de autoedición, como
					por ejemplo Aldus PageMaker, el cual incluye versiones de Lorem
					Ipsum.
					</h4>
						<div class="col-md-4 col-sm-6">
							<div class="single-about-detail clearfix">
								<div class="about-img">
									<img class="img-responsive" src="../img/item1.jpg" alt="">
								</div>
								<div class="about-details">

									<h3>Luisa Fernandez</h3>
									<p>Lorem Ipsum is simply dummy text of the printing and
										typesetting industry. Lorem Ipsum has been the industry's
										standard dummy text ever since the 1500s, when an unknown
										printer.</p>
								</div>
							</div>
						</div>
						<div class="col-md-4 col-sm-6">
							<div class="single-about-detail">
								<div class="about-img">
									<img class="img-responsive" src="../img/item2.jpg" alt="">
								</div>
								<div class="about-details">


									<h3>Juan López</h3>
									<p>Lorem Ipsum is simply dummy text of the printing and
										typesetting industry. Lorem Ipsum has been the industry's
										standard dummy text ever since the 1500s, when an unknown
										printer.</p>
								</div>
							</div>
						</div>
						<div class="col-md-4 col-sm-6">
							<div class="single-about-detail">
								<div class="about-img">
									<img class="img-responsive" src="../img/item3.jpg" alt="">
								</div>
								<div class="about-details">

									<h3>Pedro Fuentes</h3>
									<p>Lorem Ipsum is simply dummy text of the printing and
										typesetting industry. Lorem Ipsum has been the industry's
										standard dummy text ever since the 1500s, when an unknown
										printer.</p>
								</div>
							</div>
						</div>
			</div>
		</div>
	</section>
	<!-- end of about section -->
	<!-- service section starts here -->
	<section class="service text-center" id="corrector">
		<div class="container">
			<div class="row">
				<br>
				<h2>¿Quieres ser corrector?</h2>
				<h4>Lorem Ipsum es simplemente el texto de relleno de las
					imprentas y archivos de texto. Lorem Ipsum ha sido el texto de
					relleno estándar de las industrias desde el año 1500, cuando un
					impresor (N. del T. persona que se dedica a la imprenta)
					desconocido usó una galería de textos y los mezcló de tal manera
					que logró hacer un libro de textos especimen. No sólo sobrevivió
					500 años, sino que tambien ingresó como texto de relleno en
					documentos electrónicos, quedando esencialmente igual al original.
					Fue popularizado en los 60s con la creación de las hojas
					"Letraset", las cuales contenian pasajes de Lorem Ipsum, y más
					recientemente con software de autoedición, como por ejemplo Aldus
					PageMaker, el cual incluye versiones de Lorem Ipsum.</h4>
				<div class="col-md-3 col-sm-6">
					<div class="single-service">
						<div class="single-service-img">
							<div class="service-img">
								<img class="heart img-responsive" src="../img/service1.png" alt="">
							</div>
						</div>
						<h3>Heart problem</h3>
					</div>
				</div>
				<div class="col-md-3 col-sm-6">
					<div class="single-service">
						<div class="single-service-img">
							<div class="service-img">
								<img class="brain img-responsive" src="../img/service2.png" alt="">
							</div>
						</div>
						<h3>brain problem</h3>
					</div>
				</div>
				<div class="col-md-3 col-sm-6">
					<div class="single-service">
						<div class="single-service-img">
							<div class="service-img">
								<img class="knee img-responsive" src="../img/service3.png" alt="">
							</div>
						</div>
						<h3>knee problem</h3>
					</div>
				</div>
				<div class="col-md-3 col-sm-6">
					<div class="single-service">
						<div class="single-service-img">
							<div class="service-img">
								<img class="bone img-responsive" src="../img/service4.png" alt="">
							</div>
						</div>
						<h3>human bones problem</h3>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- end of service section -->



<?php
include 'footer.php';
?>

	<!-- script tags
	============================================================= -->
	<script src="js/auto-complete.js"></script>
	<script>
		var tipo = new autoComplete({
			selector : '#tipo',
			minChars : 1,
			source : function(term, suggest) {
				term = term.toLowerCase();
				var choices = [ 'Marcadores', 'Rúbricas', 'Tareas' ];
				var suggestions = [];
				for (i = 0; i < choices.length; i++)
					if (~choices[i].toLowerCase().indexOf(term))
						suggestions.push(choices[i]);
				suggest(suggestions);
			}
		});
		var recursos = new autoComplete({
			selector : '#recursos',
			minChars : 1,
			source : function(term, suggest) {
				term = term.toLowerCase();
				var choices = [ 'Los Mayas', 'Acto fin de año',
						'La contaminación', 'Día internacional de la Mujer',
						'LE05 OA 01', 'LE05 OA 02', 'LE05 OA 03', 'LE05 OA 05',
						'LE05 OA 06', 'El tren',
						'Propósito comunicativo: Informar',
						'Propósito comunicativo: Opinar',
						'Propósito comunicativo: Narrar', 'Cuento', 'Carta',
						'Noticia' ];
				var suggestions = [];
				for (i = 0; i < choices.length; i++)
					if (~choices[i].toLowerCase().indexOf(term))
						suggestions.push(choices[i]);
				suggest(suggestions);
			}

		});
	</script>
	
</body>
</html>