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
 * @package mod
 * @subpackage emarking
 * @copyright 2012-2015 Jorge Villalón {@link http://www.uai.cl}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
global $CFG, $DB, $OUTPUT, $PAGE, $USER;
// Required and optional params for ajax interaction in emarking.
$draftid = required_param('id', PARAM_INT);
// A valid submission is required.
if (! $draft = $DB->get_record('emarking_draft', array(
    'id' => $draftid))) {
    print_error('Invalid draft');
}
// A valid submission is required.
if (! $submission = $DB->get_record('emarking_submission', array(
    'id' => $draft->submissionid))) {
    print_error('Invalid submission');
}
if (! $emarking = $DB->get_record("emarking", array(
    "id" => $draft->emarkingid))) {
    print_error('Invalid assignment');
}
// The course to which the assignment belongs.
if (! $course = $DB->get_record("course", array(
    "id" => $emarking->course))) {
    print_error('Invalid course');
}
// The marking process course module.
if (! $cm = get_coursemodule_from_instance("emarking", $emarking->id, $course->id)) {
    print_error('Invalid emarking course module');
}
// Now require login so full security is checked.
require_login($course->id, false, $cm);
$url = new moodle_url('/mod/emarking/marking/index.php', array(
    'id' => $draftid));
// Create the context within the course module.
$context = context_module::instance($cm->id);
// Event indicating that a user opened an exam.
$item = array(
    'context' => $context,
    'objectid' => $cm->id);
// Add to Moodle log so some auditing can be done.
\mod_emarking\event\emarking_viewed::create($item)->trigger();
// Read the module version.
$module = new stdClass();
list($lang, $langshort, $langspecific) = emarking_get_user_lang();
$langhtml = '<meta name="gwt:property" content="locale=' . $lang . '">';
$emarkingdir = $CFG->wwwroot . '/mod/emarking/marking/emarkingweb';
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<!-- The DOCTYPE declaration above will set the     -->
<!-- browser's rendering engine into                -->
<!-- "Standards Mode". Replacing this declaration   -->
<!-- with a "Quirks Mode" doctype is not supported. -->
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="shortcut icon" type="image/png"
	href="<?php echo $CFG->wwwroot ?>/mod/emarking/pix/icon.png" />
<?php echo $langhtml?>
<!--                                                               -->
<!-- Consider inlining CSS to reduce the number of requested files -->
<!--                                                               -->
<!--                                           -->
<!-- Any title is fine                         -->
<!--                                           -->
<title>eMarking</title>
<!--                                           -->
<!-- This script loads your compiled module.   -->
<!-- If you add any GWT meta tags, they must   -->
<!-- be added before this line.                -->
<script type="text/javascript"
	src="<?php echo $emarkingdir?>/emarkingweb.nocache.js"></script>
</head>
<!--                                           -->
<!-- The body can have arbitrary html, or      -->
<!-- you can leave the body empty if you want  -->
<!-- to create a completely dynamic UI.        -->
<!--                                           -->
<body style="padding-top: 0px;">
	<div id="emarking"
		moodleurl="<?php echo $CFG->wwwroot ?>/mod/emarking/ajax/a.php"></div>
</body>
</html>