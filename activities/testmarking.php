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
require_once ($CFG->dirroot . '/mod/emarking/lib.php');


$url = new moodle_url ( '/mod/emarking/activities/testmarking.php' );
require_login ();
if (isguestuser ()) {
	die ();
}
$stage = optional_param('stage', 1, PARAM_INT);
$systemcontext = context_system::instance ();
$PAGE->set_url ( $url );
$PAGE->set_context ( $systemcontext );
$PAGE->set_pagelayout ( 'embedded' );


echo $OUTPUT->header ();

?>
<div class="container" style="padding-top: 150px;">
	<div class="row">
		<h2>Corrección</h2>
		<div class="col-md-12">
<ul class="nav nav-tabs active_tab">

						<li  class="<?php if($stage==1) echo "active";?>"><a  href="testmarking.php?stage=1">Día 1</a></li>
						<li class="<?php if($stage==2) echo "active";?>"><a  href="testmarking.php?stage=2">Día 2</a></li>
						<li class="<?php if($stage==3) echo "active";?>"><a  href="testmarking.php?stage=3">Día 3</a></li>
						<li class="<?php if($stage==4) echo "active";?>"><a href="testmarking.php?stage=4">Día 4</a></li>
						<li class="<?php if($stage==5) echo "active";?>"><a  href="testmarking.php?stage=5">Día 5</a></li>
						<li class="<?php if($stage==6) echo "active";?>"><a href="testmarking.php?stage=6">Día 6</a></li>
						<li class="<?php if($stage==7) echo "active";?>"><a  href="testmarking.php?stage=7">Día 7</a></li>
						<li class="<?php if($stage==8) echo "active";?>"><a  href="testmarking.php?stage=8">Día 8</a></li>	
						</ul>


<?php
// Action on edit.

// Action actions on "list".
	$sql="Select * from mdl_emarking_fondef_marking where stage=$stage AND (marker =? or secondmarker = ?)";
	$tests = $DB->get_records_sql ( $sql,array($USER->id,$USER->id) );
	
	// Creating list.
	$table = new html_table ();
	$table->head = array (
			'Nombre',
			'Progreso'

	);
	$count=1;
	foreach ( $tests as $test ) {

		if($test->marker == $USER->id){
			$draftid=$test->draft;
		}
		else{
			$draftid=$test->seconddraft;
		}
		$draft=$DB->get_record("emarking_draft",array('id'=>$draftid));
$sqlcm="select cm.id,cm.instance
from mdl_course_modules as cm
INNER Join mdl_modules as m on (m.name='emarking' AND m.id=cm.module)
where cm.instance=?";
		$cm=$DB->get_record_sql($sqlcm,array($draft->emarkingid));
		$context = context_module::instance($cm->id);
		$numcriteria = emarking_activity_get_num_criteria ( $context );
		$numcomments=$DB->get_record_sql('select count(*) as count from mdl_emarking_comment where draft=? and textformat=?',	array($draftid, EMARKING_BUTTON_RUBRIC));
		$numcomments=$numcomments->count;

		if ($numcomments!= 0)
			$markingprogress = ($numcomments* 100) / ($numcriteria);
		else
			$markingprogress = 0;
			
			$popupurl = new moodle_url('/mod/emarking/marking/index.php', array(
					'id' => $draftid
			));
			
			$markactionlink = $OUTPUT->action_link($popupurl, 'Correccion '.$count, new popup_action('click', $popupurl, 'emarking' . $draftid, array(
					'menubar' => 'no',
					'titlebar' => 'no',
					'status' => 'no',
					'toolbar' => 'no',
					'width' => 860,
					'height' => 600
			)));
		
		
		$table->data [] = array (
				$markactionlink,
				emarking_get_progress_circle ( $markingprogress),

		);
	$count++;
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