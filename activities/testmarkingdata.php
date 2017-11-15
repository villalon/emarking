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

$url = new moodle_url ( '/mod/emarking/activities/testmarking.php' );
require_login ();
if (isguestuser ()) {
	die ();
}
$categoryid = optional_param ( 'categoryid', 0, PARAM_INT );
$systemcontext = context_system::instance ();
$PAGE->set_url ( $url );
$PAGE->set_context ( $systemcontext );
$PAGE->set_pagelayout ( 'embedded' );

echo $OUTPUT->header ();
include 'views/header.php';
$totalniveles = 3;
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
					'Criterio 1',
					'Criterio 2' 
			
			);
			$tableCorreccion = new html_table ();
			$tableCorreccion->head = array (
					'Corrector 1',
					'Corrector 2',
					'Criterio ',
					'Level 1',
					'level 2' 
			
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
			foreach ( $markers as $marker ) {
				
				$sql = "select sum(total.commentcount) as comments, sum(total.criteriacount) as criterias from 
(select ec.id,count(ec.levelid) as commentcount, cc.count as criteriacount from mdl_emarking_comment as ec
inner join mdl_gradingform_rubric_criteria as grc on (ec.criterionid =grc.id)
left join( select definitionid, count(*) as count from mdl_gradingform_rubric_criteria as grc group by definitionid) as cc on (cc.definitionid =grc.definitionid)
where ec.draft in (select if(marker=?,draft,seconddraft) as draft
from mdl_emarking_fondef_marking
where marker =? Or secondmarker =?) AND ec.textformat = ?
group by ec.draft) as total";
				$count = $DB->get_record_sql ( $sql, array (
						$marker->userid,
						$marker->userid,
						$marker->userid,
						2 
				) );
				
				$user = $DB->get_record ( 'user', array (
						"id" => $marker->userid 
				) );
				
				$percentage = $count->comments * 100 / $count->criterias;
				$table->data [] = array (
						$user->firstname . " " . $user->lastname,
						emarking_get_progress_circle ( $percentage ) 
				
				);
				
				for($i = 0; $i < count ( $markersArray ); $i ++) {
					$kappaFirstCriterialArray = Array ();
					$kappaSecondCriteriaArray = Array ();
					
					if ($markersArray [$i] != $marker->userid) {
						$sql = "select if(marker=?,draft,seconddraft) as mydrafts, if(marker!=?,draft,seconddraft) as otherdrafts
from mdl_emarking_fondef_marking
where marker in(?,?) AND secondmarker in (?,?)";
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
								$kappaSC 
						
						);
					}
				}
			}
			
			foreach ( $doblecorreccionArray as $data ) {
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
				
				$tableCorreccion->data [] = array (
						$firstMarker->firstname . " " . $firstMarker->lastname,
						$secondMarker->firstname . " " . $secondMarker->lastname,
						$data->criterion,
						$markactionlink,
						$markactionlinkS
				);
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

include 'views/footer.html';