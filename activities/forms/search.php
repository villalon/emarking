
<?php
$options ='';
foreach($generos as $genero){
	$options .='<option value="'.$genero.'">'.$genero.'</option>';		
	
}

?>
<script>
$(document).ready(function () {

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
				<form method="post" action="" class="pure-form">
				<div class="row">
				<div class="form-group">
				<div class="col-md-8">
				<label><input type="radio" id="radio1" name="type" value="1" checked> Palabras claves</label>
       			 <label><input type="radio" id="radio2" name="type" value="2"> Objetivo de aprendizaje</label>
       			 <label><input type="radio" id="radio3" name="type" value="3"> Propósito comunicativo</label>
        		<label><input type="radio" id="radio4" name="type" value="4"> Género</label>
        		
				</div>
				</div>
				</div>
				
				<div class="row" id="general">
				
					<div class="form-group">
						<div class="col-md-8">
						<?php if ($_SERVER['REQUEST_METHOD'] == 'POST' & $_POST['type']==1){
					echo '<input type="text" class="form-control" name="search" value="'.$_POST['search'].'">';
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
					 	<select class="form-control" name="oa">
							<option value="">Seleccione un curso</option>
   						 <option>5°</option>
  							<option>6°</option>
   						 </select>
   						 </div>
   						 <div class="col-md-5">
   			<label>13 <input type="checkbox" name="13" ></label>
   			<label>14 <input type="checkbox"  name="14"></label>
   			<label>15 <input type="checkbox" name="15"></label>
   			<label>16 <input type="checkbox"  name="16"></label>
   			<label>17 <input type="checkbox"  name="17"></label>
   			<label>18 <input type="checkbox"  name="18"></label>
   			<label>19 <input type="checkbox" name="19"></label>
   			<label>20 <input type="checkbox"  name="20"></label>
   			<label>21 <input type="checkbox" name="21"></label>
   			<label>22 <input type="checkbox"  name="22"></label>
						</div>
						<div class="col-md-3">
							<button type="submit" class="btn btn-default btn-lg">Buscar</button>
						</div>
					</div>
				</div>
			<div class="row" id="pc" style="display:none;">
					<div class="form-group">
						<div class="col-md-8">
						 <select name="pc" class="form-control">
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
						 <select name="genero" class="form-control">
						<option value="">Seleccione un género</option>
						<?php echo $options; ?>
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
