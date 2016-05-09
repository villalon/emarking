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
 * This script allows you to reset any local user password.
 * 
 * @package mod
 * @subpackage emarking
 * @copyright 2016 Jorge Villalon (http://villalon.cl)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
error_reporting(E_ALL);
define('CLI_SCRIPT', true);

require (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
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
    'help' => false), array(
    'h' => 'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options ['help']) {
    $help = "Generates all PDFs pending for printing.

Options:
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php admin/cli/generatefilestoprint.php
"; // TODO: localize - to be translated later when everything is finished
    
    echo $help;
    die();
}

cli_heading('EMarking generate files to print'); // TODO: localize

$exams = $DB->get_records_sql(
        '
        SELECT e.*, c.fullname, c.shortname, c.id AS courseid
        FROM {emarking_exams} e
        INNER JOIN {course} c ON (e.course = c.id)
        WHERE status = :status AND emarking > 0
        ', array(
            'status' => EMARKING_EXAM_UPLOADED));
$ignore = array(2254,2255,2256);
echo "\nExams for printing:\n";
$i = 0;
foreach ($exams as $exam) {
    echo "[$exam->id] $exam->fullname $exam->name\n";
    echo "Printing exam $exam->id";
    $studentsforprinting = emarking_get_students_count_for_printing($exam->course, $exam);
    echo " for $studentsforprinting students ...";
    if($studentsforprinting <= 0 || in_array($exam->id, $ignore)) {
        echo " Skipping\n";
        continue;
    }
    $transaction = $DB->start_delegated_transaction();
    try {
        $result = emarking_download_exam($exam->id, false, false, NULL, false, false, false, false, true);
        if ($result) {
            echo "Success\n";
            $DB->commit_delegated_transaction($transaction);
        } else {
            echo "Logical error!\n";
            $e = new moodle_exception('Invalid PDF generation');
            $DB->rollback_delegated_transaction($transaction, $e);
        }
    } catch (Exception $e) {
        echo "Error!\n";
        $DB->rollback_delegated_transaction($transaction, $e);
    }
    $i ++;
}
echo ("\n\n");

echo "$i exams processed successfully\n";

exit(0); // 0 means success
