$(document).ready(function () {
	var counter = 1;
	$(document).ready(function () {
		$('#example').DataTable( {
			"paging":   false,
			"ordering": false,
			"info":     false,
			"language": {
				"sProcessing":     "Procesando...",
				"sLengthMenu":     "Mostrar _MENU_ registros",
				"sZeroRecords":    "No se encontraron resultados",
				"sEmptyTable":     "Ningún dato disponible en esta tabla",
				"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
				"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
				"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
				"sInfoPostFix":    "",
				"sSearch":         "Buscar:",
				"sUrl":            "",
				"sInfoThousands":  ",",
				"sLoadingRecords": "Cargando...",
				"oPaginate": {
					"sFirst":    "Primero",
					"sLast":     "Último",
					"sNext":     "Siguiente",
					"sPrevious": "Anterior"
				}
			}
		} );
	});
	$("#addrow").on("click", function () {
		add_row(counter);
		counter++;
	});
	$("table.rubric").on("click", ".ibtnDel", function (event) {
		$(this).closest("tr").remove();       
		counter -= 1
	});

	$("table.rubricSearch").on("click", ".ibtnAdd", function (event) {
		var rubricid= this.id;

		$.ajax({
			url:"ajax.php", //the page containing php script
			type: "POST", //request type
			data: {'id': rubricid,
				'action':'search'
			},
			success:function(result){
				add_row(counter,result);
				counter++;
			}
		});
	});


});




function showinput(e){
	var spanid=e.id;
	var split = spanid.split("-");
	var row = split[1];
	var count = split[2];
	var inputid = 'leveltext-'+row+'-'+count;
	var input =document.getElementById(inputid) 
	if(input.value.length > 0){
	input.value=e.innerHTML;
   }
	e.style.display='none';
	input.style.display='';
	input.focus();

}

function hideinput(e){
	var id=e.id;
	var input =document.getElementById(id);
	var split = id.split("-");
	var row = split[1];
	var count = split[2];
	var spanid = 'level-'+row+'-'+count;
	var span =document.getElementById(spanid) 
	
	if(input.value.length == 0){
		span.value="Click para editar";
	   }else{
		   span.innerHTML=input.value; 
	   }
	e.style.display='none';
	span.style.display='';
}

function add_row(counter, result=null){
	var newRow = $("<tr>");
	var cols = "";
	var obj = JSON.parse(result);
	var criteria ='Click para editar';
	var levelone ='Click para editar';
	var leveltwo ='Click para editar';
	var levelthree ='Click para editar';
	var levelfour ='Click para editar';
	var textcriteria ='';
	var textone ='';
	var texttwo ='';
	var textthree ='';
	var textfour ='';
	if(result !=null){
		var criteria =obj.criteria;
		var textcriteria =criteria;
		var maxScore =obj.maxscore;
		if(obj.levels[1].length > 0){
		var levelone =obj.levels[1];
		var textone =levelone;
		}
		if(obj.levels[2].length > 0){
		var leveltwo =obj.levels[2];
		var texttwo =leveltwo;
		}
		if(obj.levels[3].length > 0){
		var levelthree =obj.levels[3];
		var textthree =levelthree;
		}
		if(obj.levels[4].length > 0){
		var levelfour =obj.levels[4];
		var textfour =levelfour;
		}
	}
	
	cols += '<td class="col-sm-2" style="vertical-align: middle;">';
	cols +='<input id="leveltext-0-'+counter+'" onblur="hideinput(this)"type="text" name="criteria['+counter+']" class="form-control" style="display:none;" value="'+textcriteria+'"/>';
	cols +='<span id="level-0-'+counter+'" onclick="showinput(this)" style="cursor: pointer;">'+criteria+'</span></td>';

	cols +='<td class="col-sm-2" style="vertical-align: middle;">';
	cols += '<textarea id="leveltext-1-'+counter+'" onblur="hideinput(this)" name="level['+counter+'][4]"  class="form-control" style="display:none;height: 157px; width: 100%;">'+textfour+'</textarea>';
	cols +='<span id="level-1-'+counter+'" onclick="showinput(this)" style="cursor: pointer;">'+levelfour+'</span></td>';

	cols +='<td class="col-sm-2" style="vertical-align: middle;">';
	cols += '<textarea id="leveltext-2-'+counter+'" onblur="hideinput(this)" name="level['+counter+'][3]"  class="form-control" style="display:none;height: 157px; width: 100%;">'+textthree+'</textarea>';
	cols +='<span id="level-2-'+counter+'" onclick="showinput(this)" style="cursor: pointer;">'+levelthree+'</span></td>';

	cols +='<td class="col-sm-2" style="vertical-align: middle;">';
	cols += '<textarea id="leveltext-3-'+counter+'" onblur="hideinput(this)" name="level['+counter+'][2]"  class="form-control" style="display:none;height: 157px; width: 100%;">'+texttwo+'</textarea>';
	cols +='<span id="level-3-'+counter+'" onclick="showinput(this)" style="cursor: pointer;">'+leveltwo+'</span></td>';

	cols +='<td class="col-sm-2" style="vertical-align: middle;">';
	cols += '<textarea id="leveltext-4-'+counter+'" onblur="hideinput(this)" name="level['+counter+'][1]"  class="form-control" style="display:none;height: 157px; width: 100%;">'+textone+'</textarea>';
	cols +='<span id="level-4-'+counter+'" onclick="showinput(this)" style="cursor: pointer;">'+levelone+'</span></td>';

	cols += '<td class="col-sm-1" style="vertical-align: middle;"><input type="button" class="ibtnDel btn btn-md btn-danger "  value="Borrar"></td>';
	newRow.append(cols);
	$("table.rubric").append(newRow);
	
}
function validateform(){  
	var name=document.rubricCreator.rubricname.value; 
	if (name==null || name==""){  
	  alert("Debes ingresar un nombre a la rúbrica.");  
	  return false;  
	}  
	}  
