<link href="css/style_escribiendo.css" rel="stylesheet">
<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This is a one-line short description of the file
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2014-2015 Nicolas Perez (niperez@alumnos.uai.cl)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . '/config.php');

global $USER, $OUTPUT, $DB, $CFG, $PAGE, $USER;
require_once ($CFG->dirroot . '/mod/emarking/activities/locallib.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
$stage = optional_param('stage', 1, PARAM_INT);
$url = new moodle_url ( '/mod/emarking/activities/testmarking.php' );
require_login ();
if (isguestuser ()) {
	die ();
}
$categoryid = optional_param ( 'categoryid', 3, PARAM_INT );
$systemcontext = context_system::instance ();
$PAGE->set_url ( $url );
$PAGE->set_context ( $systemcontext );
$PAGE->set_pagelayout ( 'standard' );

echo $OUTPUT->header ();

$totalniveles = 3;
$stagesql="stage > 0";
if($stage!=99){
	$stagesql="stage =$stage";
}
?>
<div class="container" style="">
	<div class="row">
		<h2>Corrección</h2>
		<div class="col-md-12">
		<?php
		
		$categories = coursecat::make_categories_list ();
		$newarray = array ();
		$newarray [0] = "";
		foreach ( $categories as $key => $value ) {
			$selected = "";
			$newarray [$key] = $value;
			$params = array (
					"value" => $key,
					$selected 
			);
		}
		$span = html_writer::start_span ( '' ) . 'Filtrar por categorias ' . html_writer::end_span ();
		$select = html_writer::select ( $newarray, 'categoryid', $categoryid, true, array (
				"onchange" => "this.form.submit()" 
		) );
		$form = html_writer::tag ( 'form', $span . $select, array (
				"method" => "get" 
		) );
		echo $form;
		
		?>
		<ul class="nav nav-tabs active_tab">

						<li  class="<?php if($stage==1) echo "active";?>"><a  href="testmarkingdata.php?stage=1&categoryid=3">Día 1</a></li>
						<li class="<?php if($stage==2) echo "active";?>"><a  href="testmarkingdata.php?stage=2&categoryid=3">Día 2</a></li>
						<li class="<?php if($stage==3) echo "active";?>"><a  href="testmarkingdata.php?stage=3&categoryid=3">Día 3</a></li>
						<li class="<?php if($stage==4) echo "active";?>"><a href="testmarkingdata.php?stage=4&categoryid=3">Día 4</a></li>
						<li class="<?php if($stage==5) echo "active";?>"><a  href="testmarkingdata.php?stage=5&categoryid=3">Día 5</a></li>
						<li class="<?php if($stage==6) echo "active";?>"><a href="testmarkingdata.php?stage=6&categoryid=3">Día 6</a></li>
						<li class="<?php if($stage==7) echo "active";?>"><a  href="testmarkingdata.php?stage=7&categoryid=3">Día 7</a></li>
						<li class="<?php if($stage==8) echo "active";?>"><a  href="testmarkingdata.php?stage=8&categoryid=3">Día 8</a></li>
						<li class="<?php if($stage==99) echo "active";?>"><a  href="testmarkingdata.php?stage=99&categoryid=3">Consolidado</a></li>	
						</ul>
		<?php
		if ($categoryid != 0) {
			$table = new html_table ();
			$table->head = array (
					'Nombre',
					'Progreso' 
			
			);
			$tableKappa = new html_table ();
			$tableKappa->head = array (
					'Corrector 1',
					'Corrector 2',
					'Texto informativo',
					'Po',
					'Texto narrativo', 
					'Po'			
			);
			$tableCorreccion = new html_table ();
			$tableCorreccion->head = array (
					'',
					'Corrector 1',
					'Corrector 2',
					'Texto ',
					'Level 1',
					'level 2',
					''
			
			);
			
			$categorycontext = context_coursecat::instance ( $categoryid );
			$sql = "select ra.userid as userid 
from mdl_role_assignments as ra
Inner join mdl_role as r on(r.shortname=? AND ra.roleid=r.id)
where ra.contextid = ?";
			$markers = $DB->get_records_sql ( $sql, array (
					"corrector",
					$categorycontext->id 
			) );
			
			$countmarkers = count ( $markers );
			
			$markersArray = array ();
			foreach ( $markers as $marker ) {
				$markersArray [] = $marker->userid;
			}
			$doblecorreccionArray=Array();
			$agregados=array();
			$kappaagregados=array();
			foreach ( $markers as $marker ) {
				
				
				
				
				$sql = "select count(ec.levelid) as count from mdl_emarking_comment as ec
where ec.draft in (select if(marker=?,draft,seconddraft) as draft
from {emarking_fondef_marking}
where $stagesql AND (marker =? Or secondmarker =?)) AND ec.textformat = ?";
				$commentsCount = $DB->get_record_sql ( $sql, array (
						$marker->userid,
						$marker->userid,
						$marker->userid,
						2 
				) );
				
				$sql="select count(*) as count from mdl_emarking_fondef_marking where $stagesql AND (marker =? Or secondmarker =?)";
				$draftsCount = $DB->get_record_sql ( $sql, array (
						$marker->userid,
						$marker->userid
				) );
				$criteriaCount=$draftsCount->count * 2;
				$user = $DB->get_record ( 'user', array (
						"id" => $marker->userid 
				) );
				
				$percentage = $commentsCount->count * 100 / $criteriaCount;
				$table->data [] = array (
						$user->firstname . " " . $user->lastname,
						emarking_get_progress_circle ( $percentage ) 
				
				);
				
				for($i = 0; $i < count ( $markersArray ); $i ++) {
					$kappaFirstCriterialArray = Array ();
					$kappaSecondCriteriaArray = Array ();
					
					if ($markersArray [$i] != $marker->userid) {
						$str =$marker->userid."#".$markersArray [$i];
						if(!in_array($str, $kappaagregados)){
						
						$sql = "select if(marker=?,draft,seconddraft) as mydrafts, if(marker!=?,draft,seconddraft) as otherdrafts
from mdl_emarking_fondef_marking
where marker in(?,?) AND secondmarker in (?,?) AND $stagesql";
						$drafts = $DB->get_records_sql ( $sql, array (
								$marker->userid,
								$marker->userid,
								$marker->userid,
								$markersArray [$i],
								$marker->userid,
								$markersArray [$i]
						) );
						
						
						for($k = 0; $k < $totalniveles; $k ++) {
							for($h = 0; $h < $totalniveles; $h ++) {
								$kappaFirstCriterialArray [$k] [$h] = 0;
								$kappaSecondCriteriaArray [$k] [$h] = 0;
							}
						}
						
						foreach ( $drafts as $draft ) {
							$sql = "select ec.id,ec.criterionid,ec.levelid, ec.draft
from mdl_emarking_comment as ec
where ec.draft = ? and ec.textformat = 2 ORDER BY criterionid";
							$mydrafts = $DB->get_records_sql ( $sql, array (
									$draft->mydrafts 
							) );
							
							$otherdrafts = $DB->get_records_sql ( $sql, array (
									$draft->otherdrafts 
							) );
							$mydrafts = array_values ( $mydrafts );
							$otherdrafts = array_values ( $otherdrafts );
							
							if (count ( $mydrafts ) == count ( $otherdrafts )) {
								for($j = 0; $j < count ( $mydrafts ); $j ++) {
									
									$mycriteria = $DB->get_record ( "gradingform_rubric_criteria", array (
											"id" => $mydrafts [$j]->criterionid 
									) );
									$othercriteria = $DB->get_record ( "gradingform_rubric_criteria", array (
											"id" => $otherdrafts [$j]->criterionid 
									) );
									if ($mycriteria->description == $othercriteria->description) {
										$mylevel = $DB->get_record ( "gradingform_rubric_levels", array (
												"id" => $mydrafts [$j]->levelid 
										) );
										$otherlevel = $DB->get_record ( "gradingform_rubric_levels", array (
												"id" => $otherdrafts [$j]->levelid 
										) );
										$mylevelScore = ( int ) round ( $mylevel->score );
										$otherlevelScore = ( int ) round ( $otherlevel->score );
										
										if ($mylevelScore != $otherlevelScore) {
											
											$firstdraft = $mydrafts [$j]->draft;
											$seconddraft = $otherdrafts [$j]->draft;
											$doblecorreccion = new stdClass ();
											$doblecorreccion->draft = $firstdraft;
											$doblecorreccion->marker = $marker->userid;
											$doblecorreccion->secondmarker = $markersArray [$i];
											$doblecorreccion->seconddraft = $seconddraft;
											$doblecorreccion->level = $mylevelScore;
											$doblecorreccion->secondlevel = $otherlevelScore;
										}
										if ($j == 0) {
											$kappaFirstCriterialArray [$mylevelScore] [$otherlevelScore] = $kappaFirstCriterialArray [$mylevelScore] [$otherlevelScore] + 1;
											if ($mylevelScore != $otherlevelScore) {
												$doblecorreccion->criterion = 1;
											}
										} else {
											$kappaSecondCriteriaArray [$mylevelScore] [$otherlevelScore] = $kappaSecondCriteriaArray [$mylevelScore] [$otherlevelScore] + 1;
											if ($mylevelScore != $otherlevelScore) {
												$doblecorreccion->criterion = 2;
											}
										}
										if ($mylevelScore != $otherlevelScore) {
											$doblecorreccionArray [] = $doblecorreccion;
										}
									}
								}
							}
						}
						$totalPC = 0;
						$totalDigonalPC = 0;
						$totalfilasPC = Array ();
						$totalColumnasPC = Array ();
						
						$totalSC = 0;
						$totalDigonalSC = 0;
						$totalfilasSC = Array ();
						$totalColumnasSC = Array ();
						
						for($k = 0; $k < $totalniveles; $k ++) {
							$totalfilaPC = 0;
							$totalcolumnaPC = 0;
							
							$totalfilaSC = 0;
							$totalcolumnaSC = 0;
							
							for($h = 0; $h < $totalniveles; $h ++) {
								
								$totalPC = $totalPC + $kappaFirstCriterialArray [$k] [$h];
								
								$totalfilaPC = $totalfilaPC + $kappaFirstCriterialArray [$k] [$h];
								$totalcolumnaPC = $totalcolumnaPC + $kappaFirstCriterialArray [$h] [$k];
								
								$totalSC = $totalSC + $kappaSecondCriteriaArray [$k] [$h];
								$totalfilaSC = $totalfilaSC + $kappaSecondCriteriaArray [$k] [$h];
								$totalcolumnaSC = $totalcolumnaSC + $kappaSecondCriteriaArray [$h] [$k];
								
								if ($k == $h) {
									$totalDigonalPC = $totalDigonalPC + $kappaFirstCriterialArray [$k] [$h];
									
									$totalDigonalSC = $totalDigonalSC + $kappaSecondCriteriaArray [$k] [$h];
								}
							}
							$totalfilasPC [] = $totalfilaPC;
							$totalColumnasPC [] = $totalcolumnaPC;
							
							$totalfilasSC [] = $totalfilaSC;
							$totalColumnasSC [] = $totalcolumnaSC;
						}
						
						$kappaPC = 0;
						$kappaSC = 0;
						
						if ($totalPC != 0) {
							$poPC = $totalDigonalPC / $totalPC;
							$pePC = 0;
							
							for($k = 0; $k < count ( $totalfilasPC ); $k ++) {
								if ($totalfilasPC [$k] != 0 && $totalColumnasPC [$k] != 0) {
									$pePC = $pePC + ($totalfilasPC [$k] / $totalPC) * ($totalColumnasPC [$k] / $totalPC);
								}
							}
							
							if ($pePC != 1) {
								$kappaPC = ($poPC - $pePC) / (1 - $pePC);
								$kappaPC = round ( $kappaPC, 4 );
							}
						}
						if ($totalSC != 0) {
							$poSC = $totalDigonalSC / $totalSC;
							$peSC = 0;
							
							for($k = 0; $k < count ( $totalfilasPC ); $k ++) {
								
								if ($totalfilasSC [$k] != 0 && $totalColumnasSC [$k] != 0) {
									$peSC = $peSC + ($totalfilasSC [$k] / $totalSC) * ($totalColumnasSC [$k] / $totalSC);
									
								}
							}
							
							if ($peSC != 1) {
								$kappaSC = ($poSC - $peSC) / (1 - $peSC);
								$kappaSC = round ( $kappaSC, 4 );
							}
						}
						
						$firstMarker = $DB->get_record ( 'user', array (
								"id" => $marker->userid 
						) );
						$secondMarker = $DB->get_record ( 'user', array (
								"id" => $markersArray [$i] 
						) );
						
						$tableKappa->data [] = array (
								$firstMarker->firstname . " " . $firstMarker->lastname,
								$secondMarker->firstname . " " . $secondMarker->lastname,
								$kappaPC,
								round($poPC,1),
								$kappaSC,
								round($poSC,1)
						
						);
						$kappaagregados[]=$marker->userid."#".$markersArray [$i];
						$kappaagregados[]=$markersArray [$i]."#".$marker->userid;
						}
					}
				}
			}
			$contador=1;
			foreach ( $doblecorreccionArray as $data ) {
				$comb =$data->marker ."#".$data->secondmarker."#".$data->draft."#". $data->seconddraft."#".$data->criterion;
				if(!in_array($comb, $agregados)){
				$firstMarker = $DB->get_record ( 'user', array (
						"id" => $data->marker 
				) );
				$secondMarker = $DB->get_record ( 'user', array (
						"id" => $data->secondmarker 
				) );
				
				$level = $data->level + 1;
				$Slevel = $data->secondlevel + 1;
				$popupurl = new moodle_url('/mod/emarking/marking/index.php', array(
						'id' => $data->draft
				));
				
				$markactionlink = $OUTPUT->action_link($popupurl, "Nivel " . $level, new popup_action('click', $popupurl, 'emarking' . $data->draft, array(
						'menubar' => 'no',
						'titlebar' => 'no',
						'status' => 'no',
						'toolbar' => 'no',
						'width' => 860,
						'height' => 600
				)));
				$popupurlS = new moodle_url('/mod/emarking/marking/index.php', array(
						'id' => $data->seconddraft
				));
				
				$markactionlinkS = $OUTPUT->action_link($popupurl, "Nivel " . $Slevel, new popup_action('click', $popupurlS, 'emarking' . $data->seconddraft, array(
						'menubar' => 'no',
						'titlebar' => 'no',
						'status' => 'no',
						'toolbar' => 'no',
						'width' => 860,
						'height' => 600
				)));
				$warning="";
				$resta=abs($data->level - $data->secondlevel);
				if($resta > 1){ 
					$warning="Alerta";
				}
				if($data->criterion==1){
					$criterio="Informativo";
				}else{
					$criterio="Narrativo";
				}
				
				$tableCorreccion->data [] = array (
						$contador,
						$firstMarker->firstname . " " . $firstMarker->lastname,
						$secondMarker->firstname . " " . $secondMarker->lastname,
						$criterio,
						$markactionlink,
						$markactionlinkS,
						$warning
				);
				$agregados[]=$data->marker ."#".$data->secondmarker."#".$data->draft."#". $data->seconddraft."#".$data->criterion;
				$agregados[]=$data->secondmarker."#".$data->marker."#".$data->seconddraft."#". $data->draft."#".$data->criterion;
				$contador++;
				}
				
			}
			
			// Showing table.
			echo html_writer::tag ( "h3", "Avance por corrector" );
			echo html_writer::table ( $table );
			echo html_writer::tag ( "h3", "Kappa" );
			echo html_writer::table ( $tableKappa );
			echo html_writer::tag ( "h3", "Detalle" );
			echo html_writer::table ( $tableCorreccion );
			echo $OUTPUT->footer ();
		}
		?>
</div>
	</div>
</div>
<?php

