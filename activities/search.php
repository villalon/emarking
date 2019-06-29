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
* @copyright 2017 CIAE Universidad de Chile
* @author 2017 Francisco Ralph fco.ralph@gmail.com
* @author 2017 Hans Jeria (hansjeria@gmail.com)
* @author 2019 Jorge Villalón (villalon@gmail.com)
* @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once (dirname (dirname ( dirname ( dirname ( __FILE__ ) ) ) ). '/config.php');
global $PAGE, $DB, $OUTPUT, $CFG;
require_once ($CFG->dirroot. '/mod/emarking/activities/locallib.php');

$oa_curso = optional_param('oa_curso', 0, PARAM_INT);
$oa = isset($_REQUEST['oa'])? $_REQUEST['oa'] : Array();
$genero = optional_param('genero', '', PARAM_TEXT);
$search = optional_param('search', '', PARAM_TEXT);

$PAGE->set_context(context_system::instance());
$url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/search.php');
$PAGE->set_url($url);
$PAGE->set_title('Actividades');
// Require jquery for modal.
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo $OUTPUT->header ();
echo $OUTPUT->heading ('Actividades');
$genres = $DB->get_records('emarking_activities_genres', null, 'name ASC');

// Se incluye formulario para busqueda
include_once $CFG->dirroot. '/mod/emarking/activities/forms/search.php';

$activitiessql = "SELECT ea.*, eag.name AS genrename, u.firstname, u.lastname
			FROM {emarking_activities} ea
            INNER JOIN {emarking_activities_genres} eag ON (ea.genre = eag.id)
            INNER JOIN {user} u ON (ea.userid = u.id)
			WHERE parent IS NULL AND status = 1 ";
$params = array();

if(strlen($search) > 3) {
		$search = $DB->sql_like_escape($search);
		$activitiessql .= "
				AND (title LIKE '%$search%' OR
				ea.description LIKE '%$search%' OR
				audience LIKE '%$search%' OR
				instructions LIKE '%$search%' OR
				teaching LIKE '%$search%' OR
				languageresources LIKE '%$search%')";
}

if($oa_curso > 0 && $oa_curso < 9) {
		$sqlwhere = "AND learningobjectives like '".$oa_curso."[%'";
		foreach($oa as $oaid){
			$sqlwhere .= " AND learningobjectives like '%$oaid%'";
		}
		$activitiessql .= $sqlwhere;
}

if($genero > 0) {
		$activitiessql .= 'AND genre = ?';
		$params[] = $genero;
}

$results = $DB->get_records_sql($activitiessql, $params);
// Display results search
include 'views/results.php';

echo $OUTPUT->footer ();
