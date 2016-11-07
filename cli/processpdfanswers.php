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
 * This script processes a PDF file.
 * 
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Jorge Villalon (http://villalon.cl)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
error_reporting(E_ALL);
define('CLI_SCRIPT', true);

require (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
// Force a debugging mode regardless the settings in the site administration
@error_reporting(E_ALL | E_STRICT); // NOT FOR PRODUCTION SERVERS!
@ini_set('display_errors', '1');    // NOT FOR PRODUCTION SERVERS!
@ini_set('mysql.connect_timeout', 900);
@ini_set('default_socket_timeout', 900);
$CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
$CFG->debugdisplay = 1;             // NOT FOR PRODUCTION SERVERS!
require_once ($CFG->libdir . '/clilib.php'); // cli only functions
require_once ($CFG->dirroot . "/lib/pdflib.php");
require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi_bridge.php");
require_once ($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi.php");
require_once ($CFG->dirroot . "/mod/emarking/lib/phpqrcode/phpqrcode.php");
require_once ($CFG->dirroot . '/mod/emarking/lib.php');
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . '/mod/emarking/print/locallib.php');

// now get cli options
list($options, $unrecognized) = cli_get_params(array(
    'help' => false,
    'category' => 0
), array(
    'h' => 'help',
    'c' => 'category'
));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options ['help']) {
    $help = "Processes PDF files with answers.

Options:
-h, --help            Print out this help
-c, --category        Print out this only exams from course in this category
            
Example:
\$sudo -u www-data /usr/bin/php admin/cli/processpdfanswers.php --category 2
"; // TODO: localize - to be translated later when everything is finished
    
    echo $help;
    die();
}

cli_heading('EMarking processing PDF answers file'); // TODO: localize

emarking_process_digitized_answers();

exit(0); // 0 means success
