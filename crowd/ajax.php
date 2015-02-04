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

$context = context_module::instance($cm->id);
if(!has_capability('mod/emarking:grade',$context)){
    header('Content-type: application/json');
    echo json_encode(array("error"=>"Not logged in"));die();
}



$crowdmod = new emarking_crowd($cm,$context);
header('Content-type: application/json');
echo $crowdmod->ajax($action);
