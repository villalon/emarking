counter = 1;
$(document).ready(function () {
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
		add_row();
		this.counter++;
	});
	$("table.rubric").on("click", ".ibtnDel", function (event) {
		$(this).closest("tr").remove();       
		this.counter -= 1
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
				add_row(result);
				this.counter++;
			}
		});
	});
});




function showinput(e){
	var tdid=e.id;
	var split = tdid.split("-");
	var row = split[1];
	var count = split[2];
	var inputid = 'leveltext-'+row+'-'+count;
	var spanid = 'level-'+row+'-'+count;
	var span =document.getElementById(spanid)
	var input =document.getElementById(inputid) 
	if(input.value.length > 0){
	input.value=span.innerHTML;
   }
	span.style.display='none';
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
		span.innerHTML="Click para editar";
	   }else{
		   span.innerHTML=input.value; 
	   }
	e.style.display='none';
	span.style.display='';
}

function add_row(result=null){
	var num = this.counter;
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
	var criterionid ='';
	var leveloneid='';
	var leveltwoid='';
	var levelthreeid='';
	var levelfourid='';
	
	if(result !=null){
		var criteria =obj.criteria;
		if(obj.bool === true)
		var criterionid =obj.criterionid;
		
		var textcriteria =criteria;
		var maxScore =obj.maxscore;
		if(obj.levels[1].length > 0){
		var levelone =obj.levels[1];
		var textone =levelone;
		if(obj.bool === true)
		var leveloneid=obj.levelids[1];
	
		}
		if(obj.levels[2].length > 0){
		var leveltwo =obj.levels[2];
		var texttwo =leveltwo;
		if(obj.bool === true)
		var leveltwoid=obj.levelids[2];
		}
		if(obj.levels[3].length > 0){
		var levelthree =obj.levels[3];
		var textthree =levelthree;
		if(obj.bool === true)
		var levelthreeid=obj.levelids[3];
		}
		if(obj.levels[4].length > 0){
		var levelfour =obj.levels[4];
		var textfour =levelfour;
		if(obj.bool === true)
		var levelfourid=obj.levelids[4];
		}
	}
	cols += '<td class="col-sm-2" id="td-0-'+num+'" onclick="showinput(this)" style="vertical-align: middle; cursor: pointer;">';
	cols +='<input id="leveltext-0-'+num+'" onblur="hideinput(this)"type="text" name="criteria['+num+']" class="form-control" style="display:none;" value="'+textcriteria+'"/>';
	cols +='<input type="hidden" name="criteriaid['+num+']" value="'+criterionid+'"/>';
	cols +='<span id="level-0-'+num+'">'+criteria+'</span></td>';

	cols +='<td class="col-sm-2" id="td-1-'+num+'" onclick="showinput(this)" style="vertical-align: middle; cursor: pointer;">';
	cols += '<textarea id="leveltext-1-'+num+'" onblur="hideinput(this)" name="level['+num+'][4]"  class="form-control" style="display:none;height: 157px; width: 100%;">'+textfour+'</textarea>';
	cols +='<input type="hidden" name="levelid['+num+'][4]" value="'+levelfourid+'"/>';
	cols +='<span id="level-1-'+num+'">'+levelfour+'</span></td>';

	cols +='<td class="col-sm-2" id="td-2-'+num+'" onclick="showinput(this)" style="vertical-align: middle; cursor: pointer;">';
	cols += '<textarea id="leveltext-2-'+num+'" onblur="hideinput(this)" name="level['+num+'][3]"  class="form-control" style="display:none;height: 157px; width: 100%;">'+textthree+'</textarea>';
	cols +='<input type="hidden" name="levelid['+num+'][3]" value="'+levelthreeid+'"/>';
	cols +='<span id="level-2-'+num+'">'+levelthree+'</span></td>';

	cols +='<td class="col-sm-2" id="td-3-'+num+'" onclick="showinput(this)" style="vertical-align: middle; cursor: pointer;">';
	cols += '<textarea id="leveltext-3-'+num+'" onblur="hideinput(this)" name="level['+num+'][2]"  class="form-control" style="display:none;height: 157px; width: 100%;">'+texttwo+'</textarea>';
	cols +='<input type="hidden" name="levelid['+num+'][2]" value="'+leveltwoid+'"/>';
	cols +='<span id="level-3-'+num+'">'+leveltwo+'</span></td>';

	cols +='<td class="col-sm-2" id="td-4-'+num+'" onclick="showinput(this)" style="vertical-align: middle; cursor: pointer;">';
	cols += '<textarea id="leveltext-4-'+num+'" onblur="hideinput(this)" name="level['+num+'][1]"  class="form-control" style="display:none;height: 157px; width: 100%;">'+textone+'</textarea>';
	cols +='<input type="hidden" name="levelid['+num+'][1]" value="'+leveloneid+'"/>';
	cols +='<span id="level-4-'+num+'">'+levelone+'</span></td>';

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
