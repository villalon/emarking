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
.oa-chk label {
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
#activitySearchForm {
    margin-bottom: 1em;
}
.filtros .btn {
    margin-right: 1em;
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
