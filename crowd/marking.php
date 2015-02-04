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
use Uai\CrowdMarking\Entity\MarkerTask;
use Uai\CrowdMarking\MarkerTaskService;

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.php';
require_once dirname(dirname(__FILE__)).'/locallib.php';

global $CFG,$OUTPUT, $PAGE, $DB,$USER;//To suppress eclipse warnings

require_once 'crowdlib.php';
//require_once(dirname(__FILE__).'/lib.php');

/*
*
* Bootstraping moodle.
*
*/
$cmid = required_param('cmid', PARAM_INT);
$cm = get_coursemodule_from_id('emarking',$cmid);
$action = optional_param('act','default',PARAM_ALPHANUMEXT);

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    error('You must specify a valid course ID');
}

if(!$emarking = $DB->get_record('emarking', array('id'=>$cm->instance))) {
	error('You must specify a valid emarking ID');
}
require_login($course, true);

$gradeitem = optional_param('gradeitem','0',PARAM_INT);


$context = context_module::instance($cm->id);
$pagelayout = 'incourse';
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
require_capability ( 'mod/emarking:grade', $context );



$PAGE->set_title("CrowdMarking");
$PAGE->set_pagelayout($pagelayout);
$PAGE->set_heading("CrowdMarking");
$PAGE->set_url(new moodle_url("/mod/emarking/crowd/marking.php?cmid=$cmid"));

//Autoloading using psr-0 standard http://www.php-fig.org/psr/psr-0/

//Set a variable for output buffering.
$o = "";


// Print eMarking tabs
$o.=$OUTPUT->tabtree(emarking_tabs($context,$cm, $emarking), "crowd" );

$crowdmod = new emarking_crowd($cm,$context);


$o.=$crowdmod->view($action);


//
echo $OUTPUT->header();
echo $o;
echo $OUTPUT->footer();
