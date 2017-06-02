<script>
$(document).ready(function () {

	var type ='<?php echo $type;?>';
	var oa ='<?php echo $oa;?>';
	var pc ='<?php echo $pc;?>';
	var genre ='<?php echo $genre;?>';
	var checkbox13 ='<?php echo $chekbox13;?>';
	var checkbox14 ='<?php echo $chekbox14;?>';
	var checkbox15 ='<?php echo $chekbox15;?>';
	var checkbox16 ='<?php echo $chekbox16;?>';
	var checkbox17 ='<?php echo $chekbox17;?>';
	var checkbox18 ='<?php echo $chekbox18;?>';
	var checkbox19 ='<?php echo $chekbox19;?>';
	var checkbox20 ='<?php echo $chekbox20;?>';
	var checkbox21 ='<?php echo $chekbox21;?>';
	var checkbox22 ='<?php echo $chekbox22;?>';
	
	$("#select_pc").val(pc).change();
	$("#select_oa").val(oa).change();
	$("#select_genre").val(genre).change();
	if(checkbox13==1){
		$("#checkbox_13").prop('checked', true);
		}
	if(checkbox14==1){
		$("#checkbox_14").prop('checked', true);
		}
	if(checkbox15==1){
		$("#checkbox_15").prop('checked', true);
		}
	if(checkbox16==1){
		$("#checkbox_16").prop('checked', true);
		}
	if(checkbox17==1){
		$("#checkbox_17").prop('checked', true);
		}
	if(checkbox18==1){
		$("#checkbox_18").prop('checked', true);
		}
	if(checkbox19==1){
		$("#checkbox_19").prop('checked', true);
		}
	if(checkbox20==1){
		$("#checkbox_20").prop('checked', true);
		}
	if(checkbox21==1){
		$("#checkbox_21").prop('checked', true);
		}
	if(checkbox22==1){
		$("#checkbox_22").prop('checked', true);
		}
	
	if(type==1){
		$('#radio1').prop('checked', true);
	 		$('#oa').hide();
	 	    $('#pc').hide();
	 	    $('#genero').hide();
	 	    $('#general').show();
		}
	else if(type==2){
		$('#radio2').prop('checked', true);
	 		$('#oa').show();
	 	    $('#pc').hide();
	 	    $('#genero').hide();
	 	    $('#general').hide();
	}
	else if(type==3){
		$('#radio3').prop('checked', true);
	 		$('#oa').hide();
	 	    $('#pc').show();
	 	    $('#genero').hide();
	 	    $('#general').hide();
	}
	else {
		$('#radio4').prop('checked', true);
	 		$('#oa').hide();
	 	    $('#pc').hide();
	 	    $('#genero').show();
	 	    $('#general').hide();
		}
	
	$('#radio1').click(function () {
 		$('#oa').hide();
 	    $('#pc').hide();
 	    $('#genero').hide();
 	    $('#general').show();
  });
 	$('#radio2').click(function () {
 		$('#oa').show();
 	    $('#pc').hide();
 	    $('#genero').hide();
 	    $('#general').hide();
  });
 	$('#radio3').click(function () {
 		$('#oa').hide();
 	    $('#pc').show();
 	    $('#genero').hide();
 	    $('#general').hide();
  });
 	$('#radio4').click(function () {
 		$('#oa').hide();
 	    $('#pc').hide();
 	    $('#genero').show();
 	    $('#general').hide();
  });

});
</script>

<div class="search text-center">
	<h3></h3>
		<h3></h3>
			<h2></h2>
	<div class="container">
		<div class="panel panel-default">
			<div class="panel-body">
		<div class="row">
		
			<div class="col-md-1 col-sm-0">
			</div>
			<div class="col-md-11">
				<form method="get" action="" class="pure-form">
				<div class="row">
				<div class="form-group">
				<div class="col-md-8">
				<label><input type="radio" id="radio1" name="type" value="1"> Palabras claves</label>
       			 <label><input type="radio" id="radio2" name="type" value="2"> Objetivo de aprendizaje</label>
       			<!-- <label><input type="radio" id="radio3" name="type" value="3"> Propósito comunicativo</label>-->
        		<label><input type="radio" id="radio4" name="type" value="4"> Género</label>
        		
				</div>
				</div>
				</div>
				
				<div class="row" id="general">
				
					<div class="form-group">
						<div class="col-md-8">
						<?php if ($type === 1) {
							echo '<input type="text" class="form-control" name="search" value="'.$search.'">';
						}else{
							echo '<input class="form-control" type="text" name="search">';
						}?>
							
						</div>
						<div class="col-md-3">
							<button type="submit" class="btn btn-default btn-lg">Buscar</button>
						</div>
					</div>  
					</div> 
				<div class="row" id="oa" style="display:none;"> 
				<div class="form-group">
						<div class="col-md-3">
					 	<select id="select_oa" class="form-control" name="oa">
							<option value="">Seleccione un curso</option>
   						 <option>5°</option>
  							<option>6°</option>
   						 </select>
   						 </div>
   						 <div class="col-md-5">
   			<label>13 <input id="checkbox_13" type="checkbox" name="13" ></label>
   			<label>14 <input id="checkbox_14" type="checkbox"  name="14"></label>
   			<label>15 <input id="checkbox_15" type="checkbox" name="15"></label>
   			<label>16 <input id="checkbox_16" type="checkbox"  name="16"></label>
   			<label>17 <input id="checkbox_17" type="checkbox"  name="17"></label>
   			<label>18 <input id="checkbox_18" type="checkbox"  name="18"></label>
   			<label>19 <input id="checkbox_19" type="checkbox" name="19"></label>
   			<label>20 <input id="checkbox_20" type="checkbox"  name="20"></label>
   			<label>21 <input id="checkbox_21" type="checkbox" name="21"></label>
   			<label>22 <input id="checkbox_22" type="checkbox"  name="22"></label>
						</div>
						<div class="col-md-3">
							<button type="submit" class="btn btn-default btn-lg">Buscar</button>
						</div>
					</div>
				</div>
			<div class="row" id="pc" style="display:none;">
					<div class="form-group">
						<div class="col-md-8">
						 <select id="select_pc" name="pc" class="form-control">
						<option value="">Seleccione un propósito comunicativo</option>
   						<option>Argumentar</option>
  						<option>Informar</option>
  						<option>Narrar</option>
  						</select>
						</div>
						<div class="col-md-3">
							<button type="submit" class="btn btn-default btn-lg">Buscar</button>
						</div>
					</div>
					</div>
			<div class="row" id="genero" style="display:none;">
					<div class="form-group">
						<div class="col-md-8">
						 <select id="select_genre" name="genero" class="form-control">
						<option value="">Seleccione un género</option>
						<?php 
						$genres = $DB->get_records('emarking_activities_genres',null,'name ASC');
						$options ='';
						foreach($genres as $genre){
							$options .='<option value="'.$genre->id.'">'.$genre->name.'</option>';
							}					
							echo $options; 
						?>
  						</select>
						</div>
						<div class="col-md-3">
							<button type="submit" class="btn btn-default btn-lg">Buscar</button>
						</div>
					</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	</div>
	</div>
</div>
