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
#oa .form-check label {
    padding-right: 10px;
    border-right: 1px solid #999;
}
@media (min-width: 600px) {
    #oa .form-check {
        display: inline;
        margin-left: 1em;
    }
}
#oa .input-group {
    width: auto;
}
#myTabContent {
    padding-top: 1em;
}
.oas {
    margin-left: 2em;
    margin-top: 1em;
    margin-right: 1em;
}
.label-curso {
    padding-top: 1em;
    margin-right: 1em;
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
#myTab li.nav-item {
    width: 33%;
    text-align: center;
}
#myTab i {
    display: block;
    font-size: 2em;
    text-align: center;
}
.card-header, .card-footer {
    background-color: #00547C;
    color: #fff;
}
.container-fluid.activity-header {
    padding-left: 0px;
    padding-right: 0px;
}
#activitySearchForm, .filtros {
    margin-bottom: 1em;
}
.filtros .btn {
    margin-right: 1em;
}
.filtros i {
    margin-left: 1em;
}

</style>
	<div class="card-columns">
		<?php			
			if ( count($results) > 0 ) {
				foreach ( $results as $result ) {
					activities_show_result($result);
				}
			} else {
				echo '<h3>No se encontraron resultados</h3>';
			}
		?>
	</div>	
