<style>
.card ul {
    list-style: none;
    padding-left: 0px;
    margin-bottom: 0px;
}
.card i {
    margin-right: 5px;
}
.card-footer div {
    display: inline;
}
.card-footer .author {
    float: right;
}
#oa label {
    margin-right: 5px;
    padding-left: 10px;
    border-left: 1px solid #999;
}
#myTabContent {
    padding-top: 1em;
}
#select_oa {
    display:inline;
    width: auto;
}
.card:hover {
    -webkit-box-shadow: 5px 5px 5px 0px rgba(0,0,0,0.50);
    -moz-box-shadow: 5px 5px 5px 0px rgba(0,0,0,0.50);
    box-shadow: 5px 5px 5px 0px rgba(0,0,0,0.50);
}
td.descripcion {
    padding-top: 10px;
}
</style>
<div class="container">
	<form method="get" action="" class="pure-form">
		<ul class="nav nav-tabs" id="myTab" role="tablist">
			<li class="nav-item"><a class="nav-link active" id="keywords-tab"
				data-toggle="tab" href="#keywords" role="tab" aria-controls="home"
				aria-selected="true">Palabras clave</a></li>
			<li class="nav-item"><a class="nav-link" id="oa-tab"
				data-toggle="tab" href="#oa" role="tab" aria-controls="oa"
				aria-selected="false">Objetivos de aprendizaje</a></li>
			<li class="nav-item"><a class="nav-link" id="genero-tab"
				data-toggle="tab" href="#genero" role="tab" aria-controls="genero"
				aria-selected="false">Género</a></li>
		</ul>
		<div class="tab-content" id="myTabContent">
			<div class="tab-pane fade show active" id="keywords" role="tabpanel"
				aria-labelledby="keywords-tab">
				<div class="form-group">
					<div class="col-md-12">
						<input class="form-control" type="text" name="search"
							value="<?= $search ?>">
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="oa" role="tabpanel"
				aria-labelledby="oa-tab">
				<div class="form-group">
					<select id="select_oa" class="form-control" name="oa_curso">
						<option value="">Seleccione un curso</option>
						<?php for ($i=8;$i>=1;$i--) { ?>
   					 	<option value="<?= $i ?>"><?= $i ?>°</option>
			    		<?php } ?>
   					</select>
			    	<?php for ($i=13;$i<23;$i++) { ?>
						<label><?= $i ?></label><input type="checkbox" name="oa"
						value="<?= $i ?>">
			    	<?php } ?>
				</div>
			</div>
			<div class="tab-pane fade" id="genero" role="tabpanel"
				aria-labelledby="genero-tab">
				<div class="form-group">
					<div class="col-md-8">
						<select id="select_genre" name="genero" class="form-control">
							<option value="">Seleccione un género</option>
						<?php
    foreach ($genres as $genre) {
        $selected = $genre->id == $genero ? 'selected' : '';
        echo '<option value="' . $genre->id . '" ' . $selected . '>' . $genre->name . '</option>';
    }
    ?>
  					</select>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<button type="submit" class="btn btn-default btn-lg">Buscar</button>
			</div>
		</div>
	</form>
</div>
