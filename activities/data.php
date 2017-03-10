
<?php

require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
require_once ($CFG->dirroot. '/mod/emarking/activities/locallib.php');
GLOBAL $DB;
$string='<p style="text-align: justify;"></p><h5><p style="text-align: justify;"></p><p style="text-align: justify;"><span>4.<span><span style="font-size:12.0pt;line-height:107%;
font-family:&quot;Times New Roman&quot;,serif;
color:#333333"></span></span></span></p><p style="margin-top:7.5pt;margin-right:0cm;margin-bottom:7.5pt;margin-left:36.0pt;text-indent:-18.0pt;line-height:115%;background:white"><span>1.<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<span style="font-size:12.0pt;line-height:115%;
font-family:&quot;Times New Roman&quot;,serif;
color:#333333">Antes de escribir tu relato de
experiencia personal, puedes planificar tu texto guiándote en las siguientes
preguntas que te ayudarán a ordenar tus ideas.<br>
&nbsp;</span></span></span></p>

<br><br><table border="1">
 <tbody><tr><td>
  </td><td width="305" valign="top" style="width:228.9pt;border:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:16.3pt">
  <p align="center" style="margin-bottom:0cm;margin-bottom:.0001pt;
  text-align:center;line-height:115%"><b><span style="font-size:12.0pt;
  line-height:115%;font-family:&quot;Times New Roman&quot;,serif">Preguntas</span></b><span></span></p>
  </td>
  <td width="305" valign="top" style="width:228.9pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:16.3pt">
  <p align="center" style="margin-bottom:0cm;margin-bottom:.0001pt;
  text-align:center;line-height:115%"><b><span style="font-size:12.0pt;
  line-height:115%;font-family:&quot;Times New Roman&quot;,serif">Completa aquí</span></b><span></span></p>
  </td>
 </tr>
 <tr><td>
  </td><td width="305" valign="top" style="width:228.9pt;border:solid windowtext 1.0pt;
  border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:17.2pt">
  <p style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  115%"><span>&nbsp;
  &nbsp; &nbsp;¿A quién irá dirigido el relato?</span></p>
  </td>
  <td width="305" valign="top" style="width:228.9pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:17.2pt">
  <p style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  115%"><span>&nbsp;</span></p>
  </td>
 </tr>
 <tr><td>
  </td><td width="305" valign="top" style="width:228.9pt;border:solid windowtext 1.0pt;
  border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:16.3pt">
  <p style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  115%"><span>&nbsp;
  &nbsp; &nbsp;¿Dónde ocurrirá este relato?</span></p>
  </td>
  <td width="305" valign="top" style="width:228.9pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:16.3pt">
  <p style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  115%"><span>&nbsp;</span></p>
  </td>
 </tr>
 <tr><td>
  </td><td width="305" valign="top" style="width:228.9pt;border:solid windowtext 1.0pt;
  border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:17.2pt">
  <p style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  115%"><span>&nbsp;
  &nbsp; ¿Quiénes participarán en la historia?</span></p>
  </td>
  <td width="305" valign="top" style="width:228.9pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:17.2pt">
  <p style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  115%"><span>&nbsp;</span></p>
  </td>
 </tr>
 <tr><td>
  </td><td width="305" valign="top" style="width:228.9pt;border:solid windowtext 1.0pt;
  border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:17.2pt">
  <p style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  115%"><span>&nbsp;
  &nbsp; ¿Qué hechos interesantes ocurrieron?</span></p>
  </td>
  <td width="305" valign="top" style="width:228.9pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:17.2pt">
  <p style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  115%"><span>&nbsp;</span></p>
  </td>
 </tr>
 <tr><td>
  </td><td width="305" valign="top" style="width:228.9pt;border:solid windowtext 1.0pt;
  border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:17.2pt">
  <p style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  115%"><span>&nbsp;
  &nbsp; &nbsp;¿Qué título tendrá tu historia?</span></p>
  </td>
  <td width="305" valign="top" style="width:228.9pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:17.2pt">
  <p style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  115%"><span>&nbsp;</span></p>
  </td>
 </tr>
 <tr><td>
  </td><td width="305" valign="top" style="width:228.9pt;border:solid windowtext 1.0pt;
  border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:17.2pt">
  <p style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  115%"><span>&nbsp;
  &nbsp; &nbsp;¿Qué conectores temporales crees que podrías utilizar en tu
  narración?</span></p>
  </td>
  <td width="305" valign="top" style="width:228.9pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:17.2pt">
  <p style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  115%"><span>&nbsp;</span></p>
  </td>
 </tr>
</tbody></table>

<span><br>
<br>
<span>
<br></span></span></h5><p style="text-align: justify;"></p>';
$output = preg_replace('!\s+!', ' ', $string);
$html = preg_replace('/<p(.*?)>/', '<p>', $output,-1,$count);
$html = preg_replace('/<span(.*?)>/', '<span>', $html,-1,$count);
$html = preg_replace('/<td(.*?)>/', '<td>', $html,-1,$count);
$html = preg_replace('/<tbody(.*?)>/', '', $html,-1,$count);
$html = preg_replace('/<td> <\/td>/', '', $html,-1,$count);
var_dump($html);
//$html = preg_replace('/<span[\s\s+](.*?)>/', '<span>', $output,-1,$count);

//$string = emarking_activities_clean_html_text($string);
//echo $count;
/*;
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

$votes=array(
		    array(
		    		'userid'=>1,
		    		'rating'=>5),
			array(
					'userid'=>2,
					'rating'=>1
)
			);
$jsonvotes=json_encode($votes, JSON_UNESCAPED_UNICODE);

$data = Array(
		"Vote"=>null,
		"Comentarios"=>$jsoncomentario
			
		);
var_dump($data);
/*
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
*/
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
  
