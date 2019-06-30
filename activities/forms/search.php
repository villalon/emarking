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
        if($genre->id === $genero) {
            $genrename = $genre->name;
        }
    }
    $filteractive = strlen($search) > 3 || $oa_curso > 0 || $genero > 0;
    ?>
<script type="text/javascript">
function validarGenero() {
   	if(document.getElementById("select_genre").selectedIndex > 0) {
   		document.getElementById("activitySearchForm").submit();      
   	}
}
function validarActivitySearch(form) {
	console.log('Validando');
	console.log('Texto:' + document.getElementById("text-search").value);
	console.log('Genero:' + document.getElementById("select_genre").selectedIndex);
	console.log('OA Curso:' + document.getElementById("oa").selectedIndex);
	// Validar si hay OAs seleccionados
	var inputElements = document.getElementsByClassName('oa-check');
	var checkedValue = '';
	for(var i=0; inputElements[i]; ++i){
	      if(inputElements[i].checked){
	           checkedValue += inputElements[i].value + ',';
	      }
	}
	console.log('OAs:' + checkedValue);
	console.log('Largo OAs' + checkedValue.length);
	if(document.getElementById("oa").selectedIndex > 0 || checkedValue.length < 1) {
		console.log('Valores inválidos para OAs');
		return false;
	}
	return true;
}
function validarTexto() {
	console.log('Texto:' + document.getElementById("text-search").value);
	if(document.getElementById('text-search').value.length < 3) {
		var el = document.getElementById('text-search');
		if(el) {
		    el.className += el.className ? ' is-invalid' : 'is-invalid';
		}
		console.log('No se aceptan texto de largo menor a 3');
		return false;
	}
	return true;
}
function validarOAs() {
	// Validar si hay OAs seleccionados
	var inputElements = document.getElementsByClassName('oa-check');
	var checkedValue = '';
	for(var i=0; inputElements[i]; ++i){
	      if(inputElements[i].checked){
	           checkedValue += inputElements[i].value + ',';
	      }
	}
	console.log('OAs:' + checkedValue);
	console.log('Largo OAs' + checkedValue.length);
	if(document.getElementById("select_oa").selectedIndex == 0 || checkedValue.length < 1) {
		var el = document.getElementById('select_oa');
		if(el) {
		    el.className += el.className ? ' is-invalid' : 'is-invalid';
		}
		console.log('Valores inválidos para OAs');
		return false;
	}
	document.getElementById("activitySearchForm").submit();
}
//Example starter JavaScript for disabling form submissions if there are invalid fields
(function() {
'use strict';
window.addEventListener('load', function() {
 // Fetch all the forms we want to apply custom Bootstrap validation styles to
 var forms = document.getElementsByClassName('needs-validation');
 // Loop over them and prevent submission
 var validation = Array.prototype.filter.call(forms, function(form) {
   form.addEventListener('submit', function(event) {
     if (form.checkValidity() === false) {
       event.preventDefault();
       event.stopPropagation();
     }
     form.classList.add('was-validated');
   }, false);
 });
}, false);
})();
</script>
	<form method="get" action="" class="pure-form needs-validation" id="activitySearchForm">
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
				<div class="form-group">
				<label for="text-search">Filtrar por palabra</label>
				<div class="input-group">
					<input class="form-control" type="text" id="text-search" name="search"
							value="<?= $search ?>" placeholder="Palabras a buscar">
					<div class="input-group-append">
						<button class="btn btn-outline-secondary" onClick='return validarTexto()'><i class="fa fa-search" aria-hidden="true"></i></button>
					</div>
					<div class="invalid-feedback">
          Ingrese una palabra de al menos tres letras
        			</div>
				</div>
				<small id="text-searchHelp" class="form-text text-muted">Sólo actividades que contengan las palabras (p.ej: carta).</small>
				</div>
			</div>
			<div class="tab-pane fade <?= $active_oa . " " . $show_oa ?>" id="oa" role="tabpanel"
				aria-labelledby="oa-tab">
					<div class="d-flex justify-content-between">
						<div class="form-group">
    					<label for="select_oa" class="label-curso">Curso</label>
    					<select id="select_oa" class="form-control" name="oa_curso" onChange="">
    						<option value="">Seleccione</option>
    						<?php for ($i=8;$i>=1;$i--) {
    						    $selected = "";
    						    if($oa_curso > 0 && $oa_curso == $i) { 
    						        $selected = "selected";
    						    }?>
       					 	<option value="<?= $i ?>" <?= $selected ?>><?= $i ?>°</option>
    			    		<?php } ?>
       					</select>
			    	<div class="invalid-feedback">
          Debe seleccionar un curso y al menos un OA
        			</div>
        			</div>
        			<div class="form-group oas">
   						OAs
			    	<?php for ($i=13;$i<23;$i++) {
			    	    $checked = "";
			    	    if(in_array($i, $oa)) {
			    	        $checked = "checked";
			    	    }
			    	    ?>
			    	    <div class="form-check">
							<input class="form-check-input oa-check" id="chkoa-<?= $i ?>" type="checkbox" class="oa-check" name="oa[]" value="<?= $i ?>" <?= $checked ?>>
							<label class="form-check-label" for="chkoa-<?= $i ?>"><?= $i ?></label>
						</div>
			    	<?php } ?>
			    	</div>
					    <button class="btn btn-default btn-lg" onClick="return validarOAs()"><i class="fa fa-search" aria-hidden="true"></i></button>					    
				</div>
					    <small id="select_oaHelp" class="form-text text-muted">Seleccione un curso y luego los OA que desea</small>
			</div>
			<div class="tab-pane fade <?= $active_genero ." ". $show_genero ?>" id="genero" role="tabpanel"
				aria-labelledby="genero-tab">
				<div class="form-group">
					<label for="select_genre">Filtrar por género</label>
						<select id="select_genre" name="genero" class="form-control" onChange="validarGenero()">
							<option value="">Seleccione un género</option>
						<?php
                            foreach ($genres as $genre) {
                                $selected = $genre->id === $genero ? 'selected' : '';
                                echo '<option value="' . $genre->id . '" ' . $selected . '>' . $genre->name . '</option>';
                            }
                        ?>
  					</select>
				<small id="generoHelp" class="form-text text-muted">Sólo actividades de un género específico.</small>
				</div>
			</div>
		</div>
	</form>
<?php if ($filteractive) {
    ?>
<div class="container-fluid">
    <div class="row filtros">
     	<?php if($active_palabras === "active") {
     	    $link = '?';
     	    if($genero > 0) {
     	        $link .= 'genero=' . urlencode($genero) . '&';
     	    }
     	    if($oa_curso > 0) {
     	        $link .= 'oa_curso=' . $oa_curso . '&';
     	        foreach($oa as $oachk) {
     	            $link .= urlencode('oa[]').'='.$oachk . '&';
     	        }
     	    }
     	    $link = $CFG->wwwroot . '/mod/emarking/activities/search.php' . $link;
     	    ?>
         <button class="btn btn-primary" onClick="location.href='<?= $link ?>'"><?= $search ?><i class="fa fa-times" aria-hidden="true"></i></button>
         <?php
            }
            foreach($oa as $oachk) {
                $link = '?';
                if(strlen($search) >= 3) {
                    $link .= 'search=' . urlencode($search) . '&';
                }
                if($genero > 0) {
                    $link .= 'genero=' . urlencode($genero) . '&';
                }
                if($oa_curso > 0) {
                    $link .= 'oa_curso=' . $oa_curso . '&';
                    foreach($oa as $oachklink) {
                        if($oachk !== $oachklink) {
                            $link .= urlencode('oa[]').'='.$oachk . '&';
                        }
                    }
                }
                $link = $CFG->wwwroot . '/mod/emarking/activities/search.php' . $link;
                ?>
         <button class="btn btn-primary" onClick="location.href='<?= $link ?>'"><?= $oa_curso . "° " . $oachk ?><i class="fa fa-times" aria-hidden="true"></i></button>
         <?php }
         if($genero > 0) {
             $link = '?';
             if(strlen($search) >= 3) {
                 $link .= 'search=' . urlencode($search) . '&';
             }
             if($oa_curso > 0) {
                 $link .= 'oa_curso=' . $oa_curso . '&';
                 foreach($oa as $oachk) {
                     $link .= urlencode('oa[]').'='.$oachk . '&';
                 }
             }
             $link = $CFG->wwwroot . '/mod/emarking/activities/search.php' . $link;
             ?>
         <button class="btn btn-primary" onClick="location.href='<?= $link ?>'"><?= $genrename ?><i class="fa fa-times" aria-hidden="true"></i></button>
         <?php } ?>
    </div>
</div>
<?php } ?>