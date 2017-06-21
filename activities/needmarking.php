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

global $USER, $OUTPUT, $DB, $CFG, $PAGE;
require_once ($CFG->dirroot . '/mod/emarking/activities/locallib.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ($CFG->dirroot . '/mod/emarking/activities/forms/assign_marker.php');


$url = new moodle_url ( '/mod/emarking/activities/needmarking.php' );
require_login ();
if (isguestuser ()) {
	die ();
}
$systemcontext = context_system::instance ();
$PAGE->set_url ( $url );
$PAGE->set_context ( $systemcontext );
$PAGE->set_pagelayout ( 'embedded' );


echo $OUTPUT->header ();

?>
<div class="container" style="padding-top: 150px;">
	<div class="row">
		<h2>Asignación de correctores</h2>
		<div class="col-md-12">

<?php


	$markers = $DB->get_records ( 'emarking_markers', array('marker'=>$USER->id), 'marker ASC' );
	// Creating list.
	$table = new html_table ();
	$table->head = array (
			'Activitdad',
			'Progreso',
			'Fecha asignación'
	);
	foreach ( $markers as $marker ) {
		$editurlassignation = new moodle_url ( '', array (
				'action' => 'edit',
				'id' => $marker->id,
				'marker'=> $marker->marker
		) );
		$editiconassignation = new pix_icon ( 'i/edit', get_string ( 'edit' ) );
		$editactionassignation = $OUTPUT->action_icon ( $editurlassignation, $editiconassignation );
		list ( $cm, $emarking, $course, $context ) = emarking_get_cm_course_instance_by_id ( $marker->emarking );
		$numcriteria = emarking_activity_get_num_criteria ( $context );
		$numdrafts = $DB->count_records ( 'emarking_draft', array (
				'emarkingid' => $emarking->id 
		) );
		$numcriteriacomments = emarking_activity_get_num_criteria_comments ( $emarking->id );
		$totalcriteriadrats = $numdrafts * $numcriteria;
		if ($totalcriteriadrats != 0)
			$markingprogress = ($numcriteriacomments * 100) / ($totalcriteriadrats);
		else
			$markingprogress = 0;
		
		$emarkingurl = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/marking.php', array (
				"id" => $marker->emarking,
				'tab' => 1 
		) );
		$emarkikngLink = '<a href="' . $emarkingurl . '">' . $emarking->name . '</a>';
		$username = '';
		if ($marker->marker != 0) {
			$markerobj = $DB->get_record ( 'user', array (
					'id' => $marker->marker 
			) );
			$username = $markerobj->firstname . ' ' . $markerobj->lastname;
		}
		$date='';
		if($marker->timeassignation){
			$date=date("d/m/y",$marker->timeassignation);
		}
		$table->data [] = array (
				$emarkikngLink,
				emarking_get_progress_circle ( $markingprogress ),
				$date

		);
	}
	
	// Showing table.
	echo html_writer::table ( $table );

echo $OUTPUT->footer ();
?>
</div>
	</div>
</div>
<?php
$tab=1;
include 'views/header.php';
include 'views/footer.html';