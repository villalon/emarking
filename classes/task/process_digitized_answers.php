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
 * @copyright 2012-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_emarking\task;
class process_digitized_answers extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens.
        return get_string('digitizedanswersprocessing', 'mod_emarking');
    }
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/emarking/lib.php');
        require_once($CFG->dirroot . '/mod/emarking/locallib.php');
        require_once($CFG->dirroot . '/mod/emarking/print/locallib.php');
        $digitizedanswerfiles = emarking_get_digitized_answer_files(NULL, EMARKING_DIGITIZED_ANSWER_UPLOADED);
        if(count($digitizedanswerfiles) == 0) {
            mtrace('No digitized answers files to process');
            return;
        }
        $totalfiles = 0;
        // Setup de directorios temporales.
        $tempdir = emarking_get_temp_dir_path(random_string());
        emarking_initialize_directory($tempdir, true);
        foreach($digitizedanswerfiles as $file) {
            if(!$zipfile = emarking_get_path_from_hash($tempdir, $file->hash)) {
                mtrace('Invalid file for processing ' . $file->id);
                continue;
            }
            if(!$emarking = $DB->get_record('emarking', array('id' => $file->emarking))) {
                mtrace('Invalid emarking activity ' . $file->emarking);
                continue;
            }
            if(!$course = $DB->get_record('course', array('id'=>$emarking->course))) {
                mtrace('Invalid course in emarking activity ' . $emarking->course);
                continue;
            }
            if(!$cm = get_coursemodule_from_instance('emarking', $emarking->id)) {
                mtrace('Invalid course module for emarking activity ' . $emarking->id);
                continue;
            }
            $file->status = EMARKING_DIGITIZED_ANSWER_BEING_PROCESSED;
            $DB->update_record('emarking_digitized_answers', $file);
            $totalfiles++;
            $msg = $totalfiles . ':'. $course->fullname . ':' . $emarking->name . ':' . $file->filename;
            // Process documents and obtain results.
            list($result, $errors, $totaldocumentsprocessed, $totaldocumentsignored) =
            emarking_upload_answers($emarking, $zipfile, $course,
                $cm, NULL);
            if($result) {
                $file->status = EMARKING_DIGITIZED_ANSWER_PROCESSED;
            } else {
                $file->status = EMARKING_DIGITIZED_ANSWER_ERROR_PROCESSING;
            }
            $msg .= emarking_get_string_for_status_digitized($file->status) . ' processed:' . $totaldocumentsprocessed . ' ignored:' . $totaldocumentsignored;
            $DB->update_record('emarking_digitized_answers', $file);
            mtrace($msg);
        }
        mtrace("A total of $totalfiles were processed.");
    }
}