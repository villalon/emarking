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
 * This is a one-line short description of the file
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2014-2015 Nicolas Perez (niperez@alumnos.uai.cl)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

global $USER, $OUTPUT, $DB, $CFG, $PAGE;

require_once ($CFG->dirroot. '/mod/emarking/activities/forms/new_genre.php');

// Action var is needed to change the action wished to perfomr: list, create, edit, delete.
$action = optional_param('action', 'list', PARAM_TEXT);
$genreid = optional_param('genreid', 0, PARAM_INT);
// Emarking URL.

$url = new moodle_url('/mod/emarking/activities/genres.php');
require_login();
if (isguestuser()) {
    die();
}

$systemcontext = context_system::instance();
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('embedded');

echo $OUTPUT->header();
?>
<div class="container" style="padding-top: 150px;">
	<div class="row">
		<h2>Lista de géneros</h2>
		<div class="col-md-12">

<?php 
// Action action on delete.
if ($action == "delete") {
    // Getting record to delete.
    $DB->delete_records('emarking_activities_genres', array(
    		'id' => $genreid));
    echo $OUTPUT->notification(get_string('changessaved', 'mod_emarking'), 'notifysuccess');
    $action = "list";
}
// Action actions on "list".
if ($action == 'list') {
	// Form display.
	$genreform = new mod_emarking_activities_new_genre();
	if ($fromform=$genreform->get_data()) {
		$data= new stdClass();
		$data->name = $fromform->genre;
		$data->timecreated = time();
		$DB->insert_record('emarking_activities_genres', $data);
	} 
	
    // Create button url.
    $urlcreate = new moodle_url('/mod/emarking/marking/predefinedcomments.php',
            array(
                'id' => $cm->id,
                'action' => 'create'));
    $genres = $DB->get_records('emarking_activities_genres', null,'name ASC');
    // Creating list.
    $table = new html_table();
    $table->head = array(
        'Género',
        get_string('actions', 'mod_emarking'));
    foreach ($genres as $genre) {
        $deleteurlcomment = new moodle_url('',
                array(
                    'action' => 'delete',
                    'genreid' => $genre->id));
        $deleteigenre = new pix_icon('t/delete', get_string('delete'));
        $deleteactiongenre = $OUTPUT->action_icon($deleteurlcomment, $deleteigenre,
                new confirm_action(get_string('questiondeletecomment', 'mod_emarking')));
        
        $table->data [] = array(
            $genre->name,
        	$deleteactiongenre);
    }
   
        // Showing table.
        echo html_writer::table($table);
        // Action buttons.
        $genreform->add_action_buttons(false, get_string('submit'));
        $genreform->display();
    
}
echo $OUTPUT->footer();
?>
</div>
</div>
</div>
<?php 
$tab=1;
include 'views/header.php';
include 'views/footer.html';