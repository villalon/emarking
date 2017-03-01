
<?php

require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
require_once ($CFG->dirroot. '/mod/emarking/activities/locallib.php');
GLOBAL $DB;

$comentario=array(
		array(	"id"=>1,
				"userid"=>1,
				"username"=>"pepito",
				"timecreated"=>54621584681,
				"post"=>"Me encantó",
				"likes"=>array(
								"Juanita",
								"Mario"),
				"dislikes"=>array(
								  "Juanita",
				                  "Mario")),
		array(	"id"=>1,
				"userid"=>1,
				"post"=>"Me encantó",
				"username"=>"pepito",
				"timecreated"=>54621584681,
				"likes"=>array(
								"Juanita",
								"Mario"),
				"dislikes"=>array())
				);

$jsoncomentario=json_encode($comentario, JSON_UNESCAPED_UNICODE);



$data = Array(
		"Vote"=>array("all"=>array("user"=>"Pedro","nota"=>"5")),
		"Comentarios"=>$comment
			
		);

$total=Array("data"=>$data);
$json=json_encode($total, JSON_UNESCAPED_UNICODE);

$record = new stdClass();
$record->activityid 			= 1;
$record->timecreated         	= time();
$record->data		= $json;
//$insert = $DB->insert_record('emarking_social', $record);
 

$record = $DB->get_record('emarking_social', array('activityid'=>1));
$data=emarking_activities_clean_string_to_json($record->data);
$decode=json_decode($data);
$social=$decode->data;
var_dump($social->Comentarios);
die();




$data=$decode->data;
$comentarios=$data->Comentarios;
foreach($comentarios as $comentario){
	echo $comentario->id."<br>";
	echo $comentario->userid."<br>";
	echo $comentario->username."<br>";
	echo $comentario->timecreated."<br>";
	echo $comentario->post."<br>";
	var_dump(count($comentario->dislikes));
	var_dump(count($comentario->likes));
	
}

//$insert = $DB->insert_record('emarking_social', $record);

/*
$todo=$DB->get_record_sql("SELECT data AS data FROM mdl_emarking_social where id=2");
$manage = json_decode($todo->data);
$algo=$manage->data;
$comentarios=$algo->Comentarios;
var_dump($comentarios->0);



$mal=Array('"[\\','\\"',']"','"[');
$bien=Array("[",'"',']','[');

//var_dump($manage);



$mal=Array('"[\\','\\"',']"','"[');
$bien=Array("[",'"',']','[');

		
$replace=str_replace($mal, $bien, $todo->data);
$jsonsql=json_decode($replace);
var_dump($jsonsql);
if( isset($jsonsql->comments)){
	
	var_dump($jsonsql);


}
*/
  
