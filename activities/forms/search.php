<?php
    $active_palabras = "";
    $active_oa = "";
    $active_genero = "";
    $show_palabras = "";
    $show_oa = "";
    $show_genero = "";
    if(strlen($search) > 3) {
        $active_palabras = "active";
        $show_palabras = "show";
    } elseif($oa_curso > 0) {
        $active_oa = "active";
        $show_oa = "show";
    } elseif($genero > 0) {
        $active_genero = "active";
        $show_genero = "show";
    } else {
        $active_palabras = "active";
        $show_palabras = "show";
    }
    $genrename = "";
    foreach ($genres as $genre) {
        if($genre->id = $genero) {
            $genrename = $genre->name;
        }
    }
    ?>
<script type="text/javascript">
function onGenreChange() {
    if(document.getElementById("select_genre").selectedIndex > 0) {
    	document.getElementById("activitySearchForm").submit();      
    }
}
 </script>
	<form method="get" action="" class="pure-form" id="activitySearchForm">
		<ul class="nav nav-tabs" id="myTab" role="tablist">
			<li class="nav-item"><a class="nav-link <?= $active_palabras ?>" id="keywords-tab"
				data-toggle="tab" href="#keywords" role="tab" aria-controls="home"
				aria-selected="true"><i class="fa fa-search" aria-hidden="true"></i>Palabras clave</a></li>
			<li class="nav-item"><a class="nav-link <?= $active_oa ?>" id="oa-tab"
				data-toggle="tab" href="#oa" role="tab" aria-controls="oa"
				aria-selected="false"><i class="fa fa-bullseye" aria-hidden="true"></i>Objetivos de aprendizaje</a></li>
			<li class="nav-item"><a class="nav-link <?= $active_genero ?>" id="genero-tab"
				data-toggle="tab" href="#genero" role="tab" aria-controls="genero"
				aria-selected="false"><i class="fa fa-book" aria-hidden="true"></i>Género</a></li>
		</ul>
		<div class="tab-content" id="myTabContent">
			<div class="tab-pane fade <?= $active_palabras . " " . $show_palabras ?>" id="keywords" role="tabpanel"
				aria-labelledby="keywords-tab">
				<div class="input-group">
						<input class="form-control" type="text" name="search"
							value="<?= $search ?>">
					<div class="input-group-append">
						<button class="btn btn-outline-secondary"><i class="fa fa-search" aria-hidden="true"></i></button>
					</div>
				</div>
			</div>
			<div class="tab-pane fade <?= $active_oa . " " . $show_oa ?>" id="oa" role="tabpanel"
				aria-labelledby="oa-tab">
				<div class="container-fluid">
				<div class="row">
				<div class="col-md-3">
					<label>Curso</label>
					<select id="select_oa" class="form-control" name="oa_curso" onChange="onOAchange()">
						<option value="">Seleccione</option>
						<?php for ($i=8;$i>=1;$i--) {
						    $selected = "";
						    if($oa_curso > 0 && $oa_curso == $i) { 
						        $selected = "selected";
						    }?>
   					 	<option value="<?= $i ?>" <?= $selected ?>><?= $i ?>°</option>
			    		<?php } ?>
   					</select>
   				</div>
   				<div class="col-md-7 pt-1 oa-chk">
   				OA
			    	<?php for ($i=13;$i<23;$i++) {
			    	    $checked = "";
			    	    if(in_array($i, $oa)) {
			    	        $checked = "checked";
			    	    }
			    	    ?>
						<label><?= $i ?></label>
						<input type="checkbox" name="oa[]"
						value="<?= $i ?>" <?= $checked ?>>
			    	<?php } ?>
						<label>&nbsp;</label>
			    </div>
			    <div class="col-md-2 text-right">
				<button type="submit" class="btn btn-default btn-lg">Buscar</button>
				</div>
				</div>
				</div>
			</div>
			<div class="tab-pane fade <?= $active_genero ." ". $show_genero ?>" id="genero" role="tabpanel"
				aria-labelledby="genero-tab">
				<div class="input-group">
					<div class="col-md-8">
						<select id="select_genre" name="genero" class="form-control" onChange="onGenreChange()">
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
	</form>
 <div class="row filtros">
 	<?php if($active_palabras === "active") { ?>
     <button class="btn btn-primary"><?= $search ?><i class="fa fa-times" aria-hidden="true"></i></button>
     <?php
        }
        foreach($oa as $oachk) { ?>
     <button class="btn btn-primary"><?= $oa_curso . "-" . $oachk ?><i class="fa fa-times" aria-hidden="true"></i></button>
     <?php }
     if($genero > 0) { ?>
     <button class="btn btn-primary"><?= $genrename ?><i class="fa fa-times" aria-hidden="true"></i></button>
     <?php } ?>
 </div>
	