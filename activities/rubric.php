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
 * Displays information about all the assignment modules in the requested course
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
global $PAGE,$USER, $OUTPUT, $DB;
require_login();


$PAGE->set_context(context_system::instance());
$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/rubric.php');
$PAGE->set_url($url);
$PAGE->set_pagelayout('embedded');
$strplural = get_string("modulenameplural", "assign");
$PAGE->set_title($strplural);
$PAGE->navbar->add($strplural);



$action = optional_param('action',"create", PARAM_TEXT);
//print the header
include 'views/header.php'; 

switch($action) {
    case "create":
    ?>
    <div class="container">
		<div class="row">
		<h3></h3>
		<h2>Crear una rúbrica</h2>
		<div class="col-md-2"></div>
		<div class="col-md-7">
		<?php include 'forms/rubricform.php'; ?>
		</div>			
	</div>
	<?php 
        break;
    case "update":
        break;
    case "delete":
        break;
    default:
        break;
}

//print the footer
include 'views/footer.html';