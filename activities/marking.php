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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   mod_emarking
 * @copyright 2017 Francisco Ralph fco.ralph@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
global $PAGE, $DB, $USER, $CFG;
$id = required_param('id', PARAM_INT);
$tab= required_param('tab', PARAM_INT);
$markingUrl=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/marking.php',array('id'=>$id,'tab'=>1));
$downloadUrl=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/marking.php',array('id'=>$id,'tab'=>2));
$uploadUrl=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/marking.php',array('id'=>$id,'tab'=>3));
$reportsUrl=new moodle_url($CFG->wwwroot.'/mod/emarking/activities/marking.php',array('id'=>$id,'tab'=>4));

$PAGE->set_context(context_system::instance());
$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/index.php');
$PAGE->set_url($url);
$PAGE->set_title('escribiendo');

//print the header
include 'views/header.php';
?>
<div class="container">
	<div class="row">
		<h2></h2>
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-body">
					<ul class="nav nav-tabs active_tab">
						
						
<?php 
switch ($tab) {
	case 1 :
?>
						<li class="active"><a href="<?= $markingUrl ?>">Corrección</a></li>
						<li><a href="<?= $downloadUrl ?>">Descargar</a></li>
						<li><a href="<?= $uploadUrl ?>">Digitalizar</a></li>
						<li><a href="<?= $reportsUrl ?>">Reportes</a></li>
						</ul>
						<?php 
		include include  $CFG->dirroot . '/mod/emarking/view.php';		
		break;
	case 2:
?>
						<li><a href="<?= $markingUrl ?>">Corrección</a></li>
						<li class="active"><a href="<?= $downloadUrl ?>">Descargar</a></li>
						<li><a href="<?= $uploadUrl ?>">Digitalizar</a></li>
						<li><a href="<?= $reportsUrl ?>">Reportes</a></li>
						</ul>
						<?php 
		include  $CFG->dirroot . '/mod/emarking/print/exam.php';
		break;
	case 3:
?>
						<li><a href="<?= $markingUrl ?>">Corrección</a></li>
						<li><a href="<?= $downloadUrl ?>">Descargar</a></li>
						<li class="active"><a href="<?= $uploadUrl ?>">Digitalizar</a></li>
						<li><a href="<?= $reportsUrl ?>">Reportes</a></li>
						</ul>
						<?php 
		include  $CFG->dirroot . '/mod/emarking/print/uploadanswers.php';
		break;
		case 4:
			?>
								<li><a href="<?= $markingUrl ?>">Corrección</a></li>
								<li><a href="<?= $downloadUrl ?>">Descargar</a></li>
								<li><a href="<?= $uploadUrl ?>">Digitalizar</a></li>
								<li class="active"><a href="<?= $reportsUrl ?>">Reportes</a></li>
								</ul>
								<?php 
				include  $CFG->dirroot . '/mod/emarking/reports/feedback.php';
				break;
}
?>						
						
						
					
				</div>
			</div>
		</div>
	</div>
</div>
<?php 
//print the footer
include 'views/footer.html';
