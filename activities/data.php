
<?php
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/config.php');
GLOBAL $DB;
/*$data = Array(
		"VotosPositivos"=>array("Pedro","Juan"),
		"VotosNegativos"=>array("Diego"),
		"Comentarios"=>array(array("Pedro"=>"Me encantó","likes"=>array("Juanita","Mario")), "María"=>"La utilicé en mis clases y a mis alumnos les encantó"),
		"Visitas"=>array("Pedro","Juan","Diego","Belén","María","Catalina")		
		);

$total=Array("1"=>$data);
$json=json_encode($total, JSON_UNESCAPED_UNICODE);
var_dump($json);
$record = new stdClass();
$record->activityid 			= 6;
$record->timecreated         	= time();
$record->data		= $json;


$insert = $DB->insert_record('emarking_social', $record);

*/
$todo=$DB->get_record_sql("SELECT COLUMN_JSON(data) AS data FROM mdl_emarking_social where id=4");

$bodytag = str_replace('"[\\', "[", $todo->data);
$bodytag2 = str_replace('\\"', '"', $bodytag);
$bodytag3 = str_replace(']"', ']', $bodytag2);
$bodytag4 = str_replace('"[', '[', $bodytag3);

$mal=Array('"[\\','\\"',']"','"[');
$bien=Array("[",'"',']','[');
$manage = json_decode($bodytag4);
var_dump($manage);

//$sql=$DB->get_records_sql("SELECT COLUMN_JSON(data) as 'attribute' FROM mdl_emarking_resources WHERE COLUMN_GET(data, 'description' AS CHAR) like '%carta%' OR
		//COLUMN_GET(data, 'comunicativePurpose' AS CHAR) like '%carta%'OR COLUMN_GET(data, 'type' AS CHAR) like '%carta%' OR COLUMN_GET(data, 'title' AS CHAR) like '%carta%'");

//$sql=$DB->get_records_sql("SELECT COLUMN_JSON(data) as 'attribute' FROM mdl_emarking_resources WHERE COLUMN_GET(data, 'views' AS CHAR) like '%pedro%'");
/*$sql=$DB->get_records_sql("SELECT COLUMN_JSON(data) as 'attribute' FROM mdl_emarking_resources");

//var_dump($sql);
$mal=Array('"[\\','\\"',']"','"[');
$bien=Array("[",'"',']','[');
foreach($sql as $algo){
		
$replace=str_replace($mal, $bien, $algo->attribute);
$jsonsql=json_decode($replace);
if( isset($jsonsql->comments)){
	
	var_dump($jsonsql);
}

}*/
?>

  
