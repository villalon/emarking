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
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2014 Jorge Villalon <villalon@gmail.com>
 * @copyright 2015 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @copyright 2015 Xiu-Fong Lin <xlin@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . '/config.php');
require_once ($CFG->dirroot . '/mod/emarking/locallib.php');
require_once ($CFG->dirroot . '/mod/emarking/reports/forms/gradereport_form.php');
require_once ($CFG->dirroot . '/mod/emarking/reports/statstable.php');

global $DB, $USER;

// Get course module id
$cmid = required_param ( 'id', PARAM_INT );

// Validate course module
if (! $cm = get_coursemodule_from_id ( 'emarking', $cmid )) {
	print_error ( get_string('invalidcoursemodule','mod_emarking'));
}

// Validate module
if (! $emarking = $DB->get_record ( 'emarking', array (
		'id' => $cm->instance 
) )) {
	print_error ( get_string('invalidexamid','mod_emarking') );
}
// Validate course
if (! $course = $DB->get_record ( 'course', array (
		'id' => $emarking->course 
) )) {
	print_error ( get_string('invalidcourseid','mod_emarking') );
}

// URLs for current page
$url = new moodle_url ( '/mod/emarking/gradereport.php', array (
		'id' => $cm->id 
) );

// Course context is used in reports
$context = context_module::instance ( $cm->id );

// Validate the user has grading capabilities
require_capability ( 'mod/emarking:grade', $context );

// First check that the user is logged in
require_login ( $course->id );
if (isguestuser ()) {
	die ();
}

// Page settings (URL, breadcrumbs and title)
$PAGE->set_context ( $context );
$PAGE->set_course ( $course );
$PAGE->set_cm ( $cm );
$PAGE->set_url ( $url );
$PAGE->set_pagelayout ( 'incourse' );
$PAGE->set_heading ( $course->fullname );
$PAGE->navbar->add ( get_string ( 'gradereport', 'grades' ) );

echo $OUTPUT->header ();
echo $OUTPUT->heading_with_help ( get_string ( 'gradereport', 'mod_emarking' ), get_string ( 'gradereport', 'mod_emarking' ), 'mod_emarking' );

// Print eMarking tabs.
echo $OUTPUT->tabtree ( emarking_tabs ( $context, $cm, $emarking ), get_string ( 'gradereport', 'mod_emarking' ) );

// Counts the total of exams.
$totalsubmissions = $DB->count_records_sql ( "
		SELECT COUNT(dr.id) AS total 
		FROM {emarking_draft} AS dr
		INNER JOIN {emarking_submission} AS e ON (e.emarking = :emarking AND e.id = dr.submissionid AND dr.qualitycontrol=0)
		WHERE dr.grade >= 0 AND dr.status >= :status", array (
		'emarking' => $emarking->id,
		'status' => EMARKING_STATUS_RESPONDED 
) );

// Check if there are any submissions to be shown.
if (! $totalsubmissions || $totalsubmissions == 0) {
	echo $OUTPUT->notification ( get_string ( 'nosubmissionsgraded', 'mod_emarking' ), 'notifyproblem' );
	echo $OUTPUT->footer ();
	die ();
}

// Initialization of the variable $emakingids, with the actual emarking id as the first one on the sequence.
$emarkingids = '' . $emarking->id;
//Initializatetion of the variable $emarkingidsfortable, its an array with all the parallels ids, this will be used in the stats table.
$emarkingidsfortable=array();
$emarkingidsfortable[0]=$emarking->id;
// check for parallel courses
if ($CFG->emarking_parallelregex) {
	$parallels = emarking_get_parallel_courses ( $course, $CFG->emarking_parallelregex );
} else {
	$parallels = false;
}
// Form that lets you choose if you want to add to the report the other courses
$emarkingsform = new emarking_gradereport_form ( null, array (
		'course' => $course,
		'cm' => $cm,
		'parallels' => $parallels,
		'id' => $emarkingids 
) );
$emarkingsform->display ();
// Get the IDs from the parallel courses
/*
 * This if bring all the parallel courses that were match in the regex and concatenate each other with a coma "," in between,
 * With this if the variable $parallels exists and count how manny id are inside the array to check if they are more than 0,
 * then it check if the the test exist and check their properties, if the evaluation of this match it brings their ids, and concatenates them.
 */
$totalemarkings = 1;
if ($parallels && count ( $parallels ) > 0) {
	foreach ( $parallels as $pcourse ) {
		$parallelids = '';
		if ($emarkingsform->get_data () && property_exists ( $emarkingsform->get_data (), "emarkingid_$pcourse->id" )) {
			eval ( "\$parallelids = \$emarkingsform->get_data()->emarkingid_$pcourse->id;" );
			if ($parallelids > 0) {
				$emarkingids .= ',' . $parallelids;
				$emarkingidsfortable[$totalemarkings]=$parallelids;
				$totalemarkings ++;
			}
		}
	}
}
// Print the stats table
echo get_stats_table($emarkingidsfortable,$totalemarkings);
$reportsdir = $CFG->wwwroot . '/mod/emarking/marking/emarkingreports';
?>
<script type="text/javascript" language="javascript"
	src="<?php echo $reportsdir ?>/emarkingreports.nocache.js"></script>
<div id='reports' cmid='<?php echo $cmid ?>'
	emarkingids='<?php echo $emarkingids?>'
	emarking='<?php echo $emarkingids?>' action='gradereport'
	url='<?php echo$CFG->wwwroot ?>/mod/emarking/ajax/reports.php'></div>

<?php
echo $OUTPUT->footer ();