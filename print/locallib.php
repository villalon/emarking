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
 * Devuelve el path por defecto de archivos temporales de emarking.
 * Normalmente debiera ser moodledata\temp\emarking
 *
 * @param unknown $postfix
 *            Postfijo (típicamente el id de assignment)
 * @return string El path al directorio temporal
 */
function emarking_get_temp_dir_path($postfix) {
    global $CFG;
    return $CFG->dataroot . "/temp/emarking/" . $postfix;
}
/**
 * Imports the OMR fonts
 *
 * @param string $echo
 *            if echoes the result
 */
function emarking_import_omr_fonts($echo = false) {
    global $CFG;
    // The list of extensions a font in the tcpdf installation has.
    $fontfilesextensions = array(
        '.ctg.z',
        '.php',
        '.z');
    // The font files required for OMR.
    $fonts = array(
        '3of9_new' => '/mod/emarking/lib/omr/3OF9_NEW.TTF',
        'omrbubbles' => '/mod/emarking/lib/omr/OMRBubbles.ttf',
        'omrextnd' => '/mod/emarking/lib/omr/OMRextnd.ttf');
    // We delete previous fonts if any and then import it.
    foreach ($fonts as $fontname => $fontfile) {
        // Deleteing the previous fonts.
        foreach ($fontfilesextensions as $extension) {
            $fontfilename = $CFG->libdir . '/tcpdf/fonts/' . $fontname . $extension;
            if (file_exists($fontfilename)) {
                echo "Deleting $fontfilename<br/>";
                unlink($fontfilename);
            } else {
                echo "$fontfilename does not exist, it must be created<br/>";
            }
        }
        // Import the font.
        $ttfontname = TCPDF_FONTS::addTTFfont($CFG->dirroot . $fontfile, 'TrueType', 'ansi', 32);
        // Validate if import went well.
        if ($ttfontname !== $fontname) {
            echo "Fatal error importing font $fontname<br/>";
            return false;
        } else {
            echo "$fontname imported!<br/>";
        }
    }
    return true;
}
/**
 * Returns the HTML of a set of divs for the list of enrolments
 * configured for an exam
 *
 * @param unknown $exam
 * @return string
 */
function emarking_enrolments_div($exam) {
    global $OUTPUT;
    $output = "";
    $enrolments = explode(',', $exam->enrolments);
    foreach ($enrolments as $enrolment) {
        if ($enrolment === 'manual') {
            $output .= html_writer::start_tag("div");
            $output .= $OUTPUT->pix_icon('t/enrolusers', get_string('pluginname', 'enrol_manual'));
            $output .= html_writer::end_tag("div");
        } else if ($enrolment === 'self') {
            $output .= html_writer::start_tag("div");
            $output .= $OUTPUT->pix_icon('t/user', get_string('pluginname', 'enrol_self'));
            $output .= html_writer::end_tag("div");
        } else if ($enrolment === 'database') {
            $output .= html_writer::start_tag("div");
            $output .= $OUTPUT->pix_icon('i/db', get_string('pluginname', 'enrol_database'));
            $output .= html_writer::end_tag("div");
        } else if ($enrolment === 'meta') {
            $output .= html_writer::start_tag("div");
            $output .= $OUTPUT->pix_icon('e/inster_edit_link', get_string('pluginname', 'enrol_meta'));
            $output .= html_writer::end_tag("div");
        } else {
            $output .= html_writer::start_tag("div");
            $output .= $OUTPUT->pix_icon('i/cohort', get_string('otherenrolment', 'mod_emarking'));
            $output .= html_writer::end_tag("div");
        }
    }
    return $output;
}
/**
 * Returns the path for a student picture.
 * The path is the directory plus
 * two subdirs based on the last two digits of the user idnumber,
 * e.g: user idnumber 12345 will be stored in
 * $CFG->emarking_pathuserpicture/5/4/user12345.png
 * If the directory path is not configured or does not exist returns false
 *
 * @param unknown $studentidnumber
 * @return string|boolean false if user pictures are not configured or invalid idnumber (length < 2)
 */
function emarking_get_student_picture_path($studentidnumber) {
    global $CFG;
    // If the length of the idnumber is less than 2 returns false.
    if (strlen(trim($studentidnumber)) < 2) {
        return false;
    }
        // If the directory for user pictures is configured and exists.
    if (isset($CFG->emarking_pathuserpicture) && $CFG->emarking_pathuserpicture && is_dir($CFG->emarking_pathuserpicture)) {
        // Reverse the id number.
        $idstring = "" . $studentidnumber;
        $revid = strrev($idstring);
        // The path is the directory plus two subdirs based on the last two digits.
        // of the user idnumber, e.g: user idnumber 12345 will be stored in.
        // $CFG->emarking_pathuserpicture/5/4/user12345.png.
        $idpath = $CFG->emarking_pathuserpicture;
        $idpath .= "/" . substr($revid, 0, 1);
        $idpath .= "/" . substr($revid, 1, 1);
        return $idpath . "/user$idstring.png";
    }
    return false;
}
function emarking_get_student_picture($student, $userimgdir) {
    global $CFG, $DB;
    // Get the image file for student if user pictures are configured.
    if ($studentimage = emarking_get_student_picture_path($student->idnumber) && file_exists($studentimage)) {
        return $studentimage;
    }
        // If no picture was found in the pictures repo try to use the.
        // Moodle one or default on the anonymous.
    $usercontext = context_user::instance($student->id);
    $imgfile = $DB->get_record('files',
            array(
                'contextid' => $usercontext->id,
                'component' => 'user',
                'filearea' => 'icon',
                'filename' => 'f1.png'));
    if ($imgfile) {
        return emarking_get_path_from_hash($userimgdir, $imgfile->pathnamehash, "u" . $student->id, true);
    } else {
        return $CFG->dirroot . "/pix/u/f1.png";
    }
}
/**
 * Get students count from a course, for printing.
 *
 * @param unknown_type $courseid
 */
function emarking_get_students_count_for_printing($courseid, $exam = null) {
    global $DB;
    $sqlenrolments = "";
    if ($exam != null) {
        $parts = explode(',', $exam->enrolments);
        if (count($parts) > 0) {
            $enrolments = array();
            foreach ($parts as $part) {
                $enrolments [] = "'$part'";
            }
            $sqlenrolments = implode(',', $enrolments);
            $sqlenrolments = " AND e.enrol IN ($sqlenrolments)";
        }
    }
    $query = "SELECT count(u.id) as total
			FROM {user_enrolments} ue
			JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ? $sqlenrolments)
			JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
			JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
			JOIN {user} u ON (ue.userid = u.id)
			GROUP BY e.courseid";
    // Se toman los resultados del query dentro de una variable.
    $rs = $DB->get_record_sql($query, array(
        $courseid));
    return isset($rs->total) ? $rs->total : null;
}
/**
 * Get students count from a specific emarking activity.
 *
 * @param unknown_type $emarkingid
 */
function emarking_get_students_count_with_published_grades($emarkingid) {
    global $DB;
    $query = " SELECT COUNT(DISTINCT s.student) AS total
               FROM mdl_emarking_submission AS s
                INNER JOIN mdl_emarking_draft AS d ON (d.qualitycontrol = 0 AND d.submissionid = s.id)
                WHERE s.emarking = :emarkingid AND d.status >= :status";
    // Se toman los resultados del query dentro de una variable.
    $rs = $DB->get_record_sql($query, array(
        "emarkingid" => $emarkingid,
        "status" => EMARKING_STATUS_PUBLISHED));
    return isset($rs->total) ? $rs->total : null;
}
/**
 * creates email to course manager, teacher and non-editingteacher, when a printing order has been created.
 *
 * @param unknown_type $emarking
 * @param unknown_type $course
 */
function emarking_send_processanswers_notification($emarking, $course) {
    global $USER, $DB;
    $postsubject = $course->fullname . ' : ' . $emarking->name . '. ' . get_string('uploadanswersuccessful', 'mod_emarking') .
            ' [' . $emarking->id . ']';
    // Create the email to be sent.
    $posthtml = '';
    $posthtml .= '<table><tr><th colspan="2">' . get_string('uploadanswersuccessful', 'mod_emarking') . '</th></tr>';
    $posthtml .= '<tr><td>' . get_string('emarking', 'mod_emarking') . '</td><td>' . $emarking->name . '. [' . $emarking->id .
             ']</td></tr>';
    $posthtml .= '<tr><td>' . get_string('fullnamecourse') . '</td><td>' . $course->fullname . ' (' . $course->shortname . ')' .
             '</td></tr>';
    $posthtml .= '</table>';
    $posthtml .= '';
    // Create the email to be sent.
    $posttext = get_string('uploadanswersuccessful', 'mod_emarking') . '\n';
    $posttext .= get_string('emarking', 'mod_emarking') . ' : ' . $emarking->name . '. [' . $emarking->id . ']\n';
    $posttext .= get_string('fullnamecourse') . ' : ' . $course->fullname . ' (' . $course->shortname . ')' . '\n';
    // Get all users that should be notified.
    $users = get_enrolled_users(context_course::instance($course->id), "mod/emarking:receivenotification");
    foreach ($users as $user) {
        $eventdata = new stdClass();
        $eventdata->component = 'mod_emarking';
        $eventdata->name = 'notification';
        $eventdata->userfrom = $USER;
        $eventdata->userto = $user->id;
        $eventdata->subject = $postsubject;
        $eventdata->fullmessage = $posttext;
        $eventdata->fullmessageformat = FORMAT_HTML;
        $eventdata->fullmessagehtml = $posthtml;
        $eventdata->smallmessage = $postsubject;
        $eventdata->notification = 1;
        message_send($eventdata);
    }
    // Save the date of the digitization.
    $emarking->digitizingdate = time();
    $DB->update_record('emarking', $emarking);
}
/**
 * Sends an email to everyone with the receivedigitizingnotification capability (usually teachers)
 * indicating instructions for post digitizing steps
 *
 * @param string $cron
 * @param string $debug
 * @param string $debugsend
 * @param number $course
 */
function emarking_send_digitizing_notification($cron = true, $debug = false, $debugsend = false, $course = 0) {
    global $USER, $DB;
    if ($course) {
        $emarkingactivities = $DB->get_records_sql(
                '
            SELECT *
            FROM {emarking}
            WHERE digitizingnotified = 0 AND digitizingdate > 0');
    } else {
        $emarkingactivities = $DB->get_records_sql(
                '
            SELECT *
            FROM {emarking}
            WHERE digitizingnotified = 0 AND digitizingdate > 0 AND course = :courseid',
                array(
                    'courseid' => $course));
    }
    if (! $emarkingactivities) {
        return;
    }
    foreach ($emarkingactivities as $emarking) {
        if ($emarking->digitizingnotified > 0) {
            break;
        }
        $postsubject = $course->fullname . ' : ' . $emarking->name . '. ' . get_string('digitizedanswersreminder', 'mod_emarking');
        // Create the email to be sent.
        $posthtml = '';
        $posthtml .= '<h3>' . get_string('digitizedanswersreminder', 'mod_emarking') . '</h3>';
        $posthtml .= '<p>';
        $posthtml .= $CFG->emarking_digitizedanswersmessage;
        $posthtml .= '</p>';
        $posthtml .= '';
        // Create the email to be sent.
        $posttext = get_string('digitizedanswersreminder', 'mod_emarking') . '\n';
        $posttext .= $CFG->emarking_digitizedanswersmessage . '\n';
        $posttext .= '\n';
        // Get all users that should be notified.
        $users = get_enrolled_users(context_course::instance($course), "mod/emarking:receivedigitizingnotification");
        foreach ($users as $user) {
            $eventdata = new stdClass();
            $eventdata->component = 'mod_emarking';
            $eventdata->name = 'notification';
            $eventdata->userfrom = $USER;
            $eventdata->userto = $user->id;
            $eventdata->subject = $postsubject;
            $eventdata->fullmessage = $posttext;
            $eventdata->fullmessageformat = FORMAT_HTML;
            $eventdata->fullmessagehtml = $posthtml;
            $eventdata->smallmessage = $postsubject;
            $eventdata->notification = 1;
            message_send($eventdata);
        }
        // Save the date of the digitization.
        $emarking->digitizingnotified = 1;
        $DB->update_record('emarking', $emarking);
    }
}
/**
 * creates email to course manager, teacher and non-editingteacher, when a printing order has been created.
 *
 * @param unknown_type $exam
 * @param unknown_type $course
 */
function emarking_send_newprintorder_notification($exam, $course, $title = null) {
    global $USER;
    $postsubject = $course->fullname . ' : ' . $exam->name . '. ' . get_string('newprintorder', 'mod_emarking') . ' [' . $exam->id .
             ']';
    if ($title) {
        $postsubject = $course->fullname . ' : ' . $exam->name . '. ' . $title . ' [' . $exam->id . ']';
    }
    $examhasqr = $exam->headerqr ? get_string('yes') : get_string('no');
    $pagestoprint = emarking_exam_total_pages_to_print($exam);
    $originals = $exam->totalpages + $exam->extrasheets;
    $copies = $exam->totalstudents + $exam->extraexams;
    $totalsheets = $originals * $copies;
    $teachers = get_enrolled_users(context_course::instance($course->id), 'mod/emarking:receivenotification');
    $teachersnames = array();
    foreach ($teachers as $teacher) {
        $teachersnames [] = $teacher->firstname . ' ' . $teacher->lastname;
    }
    $teacherstring = implode(',', $teachersnames);
    if (! $title) {
        $title = get_string('newprintorder', 'mod_emarking');
    }
    // Create the email to be sent.
    $posthtml = '';
    $posthtml .= '<table><tr><th colspan="2">' . $title . '</th></tr>';
    $posthtml .= '<tr><td>' . get_string('examid', 'mod_emarking') . '</td><td>' . $exam->id . '</td></tr>';
    $posthtml .= '<tr><td>' . get_string('fullnamecourse') . '</td><td>' . $course->fullname . ' (' . $course->shortname . ')' .
             '</td></tr>';
    $posthtml .= '<tr><td>' . get_string('teacher', 'mod_emarking') . '</td><td>' . $teacherstring . '</td></tr>';
    $posthtml .= '<tr><td>' . get_string('requestedby', 'mod_emarking') . '</td><td>' . $USER->firstname . ' ' . $USER->lastname .
             '</td></tr>';
    $posthtml .= '<tr><td>' . get_string('examdate', 'mod_emarking') . '</td><td>' . date("d M Y - H:i", $exam->examdate) .
             '</td></tr>';
    $posthtml .= '<tr><td>' . get_string('comment', 'mod_emarking') . '</td><td>' . $exam->comment . '</td></tr>';
    $posthtml .= '<tr><td>' . get_string('headerqr', 'mod_emarking') . '</td><td>' . $examhasqr . '</td></tr>';
    $posthtml .= '<tr><td>' . get_string('doubleside', 'mod_emarking') . '</td><td>' .
             ($exam->usebackside ? get_string('yes') : get_string('no')) . '</td></tr>';
    $posthtml .= '<tr><td>' . get_string('printlist', 'mod_emarking') . '</td><td>' .
             ($exam->printlist ? get_string('yes') : get_string('no')) . '</td></tr>';
    $posthtml .= '<tr><td>' . get_string('originals', 'mod_emarking') . '</td><td>' . $originals . '</td></tr>';
    $posthtml .= '<tr><td>' . get_string('copies', 'mod_emarking') . '</td><td>' . $copies . '</td></tr>';
    $posthtml .= '<tr><td>' . get_string('totalpagesprint', 'mod_emarking') . '</td><td>' . $totalsheets . '</td></tr>';
    $posthtml .= '</table>';
    $posthtml .= '';
    // Create the email to be sent.
    $posttext = $title . '\n';
    $posttext .= get_string('examid', 'mod_emarking') . ' : ' . $exam->id . '\n';
    $posttext .= get_string('fullnamecourse') . ' : ' . $course->fullname . ' (' . $course->shortname . ')' . '\n';
    $posttext .= get_string('teacher', 'mod_emarking') . ' : ' . $teacherstring . '\n';
    $posttext .= get_string('requestedby', 'mod_emarking') . ': ' . $USER->firstname . ' ' . $USER->lastname . '\n';
    $posttext .= get_string('examdate', 'mod_emarking') . ': ' . date("d M Y - H:i", $exam->examdate) . '\n';
    $posttext .= get_string('comment', 'mod_emarking') . ': ' . $exam->comment . '\n';
    $posttext .= get_string('headerqr', 'mod_emarking') . ': ' . $examhasqr . '\n';
    $posttext .= get_string('doubleside', 'mod_emarking') . ' : ' . ($exam->usebackside ? get_string('yes') : get_string('no')) .
             '\n';
    $posttext .= get_string('printlist', 'mod_emarking') . ' : ' . ($exam->printlist ? get_string('yes') : get_string('no')) . '\n';
    $posttext .= get_string('originals', 'mod_emarking') . ' : ' . $originals . '\n';
    $posttext .= get_string('copies', 'mod_emarking') . ' : ' . $copies . '\n';
    $posttext .= get_string('totalpagesprint', 'mod_emarking') . ': ' . $totalsheets . '\n';
    emarking_send_notification($exam, $course, $postsubject, $posttext, $posthtml);
}
/**
 * creates email to course manager, teacher and non-editingteacher, when a printing order has been downloaded.
 *
 * @param unknown_type $exam
 * @param unknown_type $course
 */
function emarking_send_examdownloaded_notification($exam, $course) {
    global $USER;
    emarking_send_newprintorder_notification($exam, $course, get_string("examstatusdownloaded", "mod_emarking"));
}
/**
 * creates email to course manager, teacher and non-editingteacher, when a printing order has been downloaded.
 *
 * @param unknown_type $exam
 * @param unknown_type $course
 */
function emarking_send_examprinted_notification($exam, $course) {
    global $USER;
    emarking_send_newprintorder_notification($exam, $course, get_string("examstatusprinted", "mod_emarking"));
}
/**
 * Extracts all pages in a big PDF file as separate PDF files, deleting the original PDF if successfull.
 *
 * @param unknown $newfile
 *            PDF file to extract
 * @param unknown $tempdir
 *            Temporary folder
 * @param string $doubleside
 *            Extract every two pages (for both sides scanning)
 * @return number unknown number of pages extracted
 */
function emarking_pdf_count_pages($newfile, $tempdir, $doubleside = true) {
    global $CFG;
    if ($CFG->version > 2015111600) {
        require_once($CFG->dirroot . "/lib/pdflib.php");
        require_once($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi_bridge.php");
    } else {
        require_once($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php");
    }
    require_once($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi.php");
    $doc = new FPDI();
    $files = $doc->setSourceFile($newfile);
    $doc->Close();
    return $files;
}
/**
 * Creates a PDF form for the copy center to print
 *
 * @param unknown $context
 * @param unknown $exam
 * @param unknown $userrequests
 * @param unknown $useraccepts
 * @param unknown $category
 * @param unknown $totalpages
 * @param unknown $course
 */
function emarking_create_printform($context, $exam, $userrequests, $useraccepts, $category, $course) {
    global $CFG;
    if ($CFG->version > 2015111600) {
        require_once($CFG->dirroot . "/lib/pdflib.php");
        require_once($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi_bridge.php");
    } else {
        require_once($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php");
    }
    require_once($CFG->dirroot . "/mod/assign/feedback/editpdf/fpdi/fpdi.php");
    $originalsheets = $exam->totalpages + $exam->extrasheets;
    $copies = $exam->totalstudents + $exam->extraexams;
    $totalpages = emarking_exam_total_pages_to_print($exam);
    $pdf = new FPDI();
    $pdf->setSourceFile($CFG->dirroot . "/mod/emarking/img/printformtemplate.pdf");
    // Adds the form page from the template.
    $pdf->AddPage();
    $tplidx = $pdf->importPage(1);
    $pdf->useTemplate($tplidx, 0, 0, 0, 0, true);
    // Copy / Printing.
    $pdf->SetXY(32, 48.5);
    $pdf->Write(1, "x");
    // Date.
    $pdf->SetXY(153, 56);
    $pdf->Write(1, core_text::strtoupper(date('d')));
    $pdf->SetXY(163, 56);
    $pdf->Write(1, core_text::strtoupper(date('m')));
    $pdf->SetXY(173, 56);
    $pdf->Write(1, core_text::strtoupper(date('Y')));
    // Requested by.
    $pdf->SetXY(95, 69);
    $pdf->Write(1, core_text::strtoupper($useraccepts->firstname . " " . $useraccepts->lastname));
    // Cost center.
    $pdf->SetXY(95, 75.5);
    $pdf->Write(1, core_text::strtoupper($category->idnumber));
    // UAI campus.
    $pdf->SetXY(95, 80.8);
    $pdf->Write(1, core_text::strtoupper(""));
    // Originals.
    $pdf->SetXY(35, 106.5);
    $pdf->Write(1, core_text::strtoupper($originalsheets));
    // Copies.
    $pdf->SetXY(60, 106.5);
    $pdf->Write(1, core_text::strtoupper("--"));
    // Number of printings.
    $pdf->SetXY(84, 106.5);
    $pdf->Write(1, core_text::strtoupper($copies));
    // Black and white.
    $pdf->SetXY(106, 106.5);
    $pdf->Write(1, "x");
    // Total pages.
    $pdf->SetXY(135, 106.5);
    $pdf->Write(1, core_text::strtoupper($totalpages));
    // Number of printings Total.
    $pdf->SetXY(84, 133.8);
    $pdf->Write(1, core_text::strtoupper(""));
    // Total pages Total.
    $pdf->SetXY(135, 133.8);
    $pdf->Write(1, core_text::strtoupper(""));
    // PÃ¡ginas totales Total.
    $pdf->SetXY(43, 146);
    $pdf->Write(1, core_text::strtoupper($course->fullname . " , " . $exam->name));
    // Recepcionado por Nombre.
    $pdf->SetXY(30, 164.5);
    $pdf->Write(1, core_text::strtoupper(""));
    // Recepcionado por RUT.
    $pdf->SetXY(127, 164.5);
    $pdf->Write(1, core_text::strtoupper(""));
    $pdf->Output("PrintForm" . $exam->id . ".pdf", "I"); // Se genera el nuevo pdf.
}
/**
 * 
 * @param unknown $emarking
 * @return boolean
 */
function emarking_assign_peers($emarking) {
    global $DB;
    $students = $DB->get_records_sql(
            "
        SELECT s.student as id, MAX(s.sort) as sort
        FROM {emarking} e
        INNER JOIN {emarking_submission} s ON (e.id = :emarking AND s.emarking = e.id)
        INNER JOIN {emarking_draft} d ON (d.submissionid = s.id)
        GROUP BY s.student
        ORDER BY s.sort", array(
                "emarking" => $emarking->id));
    $assign = array();
    foreach ($students as $student) {
        $assign [] = $student->id;
    }
    $final = array();
    $numstudents = count($assign);
    $diff = rand(1, max(array(
        1,
        $numstudents - 1)));
    for ($i = 0; $i < $numstudents; $i ++) {
        $j = ($i + $diff) % $numstudents;
        $final [$assign [$i]] = $assign [$j];
    }
    $drafts = $DB->get_records_sql(
            "
        SELECT d.*, s.student
        FROM {emarking} e
        INNER JOIN {emarking_submission} s ON (e.id = :emarking AND s.emarking = e.id)
        INNER JOIN {emarking_draft} d ON (d.submissionid = s.id)", array(
                "emarking" => $emarking->id));
    foreach ($drafts as $draft) {
        $draft->teacher = $final [$draft->student];
        unset($draft->student);
        $DB->update_record("emarking_draft", $draft);
    }
    return true;
}
/**
 *
 * @param unknown $emarking
 * @param unknown $student
 * @param unknown $context
 * @return Ambigous <mixed, stdClass, false, boolean>|stdClass
 */
function emarking_get_or_create_submission($emarking, $student, $context) {
    global $DB, $USER;
    if ($submission = $DB->get_record('emarking_submission',
            array(
                'emarking' => $emarking->id,
                'student' => $student->id))) {
        return $submission;
    }
    $tran = $DB->start_delegated_transaction();
    $submission = new stdClass();
    $submission->emarking = $emarking->id;
    $submission->student = $student->id;
    $submission->status = EMARKING_STATUS_SUBMITTED;
    $submission->timecreated = time();
    $submission->timemodified = time();
    $submission->teacher = $USER->id;
    $submission->generalfeedback = null;
    $submission->grade = $emarking->grademin;
    $submission->sort = rand(1, 9999999);
    $submission->answerkey = 0;
    $submission->id = $DB->insert_record('emarking_submission', $submission);
    // Normal marking - One draft default.
    if ($emarking->type == EMARKING_TYPE_NORMAL || $emarking->type == EMARKING_TYPE_PRINT_SCAN ||
             $emarking->type == EMARKING_TYPE_PEER_REVIEW) {
        $draft = new stdClass();
        $draft->emarkingid = $emarking->id;
        $draft->submissionid = $submission->id;
        $draft->groupid = 0;
        $draft->timecreated = time();
        $draft->timemodified = time();
        $draft->grade = $emarking->grademin;
        $draft->sort = rand(1, 9999999);
        $draft->qualitycontrol = 0;
        $draft->teacher = 0;
        $draft->generalfeedback = null;
        $draft->status = EMARKING_STATUS_SUBMITTED;
        if ($emarking->type == EMARKING_TYPE_PEER_REVIEW) {
            $draft->teacher = - 1;
            $draft->qualitycontrol = 1;
        }
        $draft->id = $DB->insert_record('emarking_draft', $draft);
        if ($emarking->qualitycontrol) {
            $qcdrafts = $DB->count_records('emarking_draft',
                    array(
                        'emarkingid' => $emarking->id,
                        'qualitycontrol' => 1));
            $totalstudents = emarking_get_students_count_for_printing($emarking->course);
            if (ceil($totalstudents / 4) > $qcdrafts) {
                $draft->qualitycontrol = 1;
                $DB->insert_record('emarking_draft', $draft);
            }
        }
        // Markers training - One draft per marker.
    } else if ($emarking->type == EMARKING_TYPE_MARKER_TRAINING) {
        // Get all users with permission to grade in emarking.
        $markers = get_enrolled_users($context, 'mod/emarking:grade');
        foreach ($markers as $marker) {
            if (has_capability('mod/emarking:supervisegrading', $context, $marker)) {
                continue;
            }
            $draft = new stdClass();
            $draft->emarkingid = $emarking->id;
            $draft->submissionid = $submission->id;
            $draft->groupid = 0;
            $draft->timecreated = time();
            $draft->timemodified = time();
            $draft->grade = $emarking->grademin;
            $draft->sort = rand(1, 9999999);
            $draft->teacher = $marker->id;
            $draft->generalfeedback = null;
            $draft->status = EMARKING_STATUS_SUBMITTED;
            $DB->insert_record('emarking_draft', $draft);
        }
        // Students training.
    } else if ($emarking->type == EMARKING_TYPE_STUDENT_TRAINING) {
        // Get all users with permission to grade in emarking.
        $students = get_enrolled_users($context, 'mod/emarking:submit');
        foreach ($students as $student) {
            $draft = new stdClass();
            $draft->emarkingid = $emarking->id;
            $draft->submissionid = $submission->id;
            $draft->groupid = 0;
            $draft->timecreated = time();
            $draft->timemodified = time();
            $draft->grade = $emarking->grademin;
            $draft->sort = rand(1, 9999999);
            $draft->teacher = $student->id;
            $draft->generalfeedback = null;
            $draft->status = EMARKING_STATUS_SUBMITTED;
            $DB->insert_record('emarking_draft', $draft);
        }
    } else {
        $e = new moodle_exception("Invalid emarking type");
        $tran->rollback($e);
        throw $e;
    }
    $DB->commit_delegated_transaction($tran);
    return $submission;
}
/**
 * Draws a table with a list of students in the $pdf document
 *
 * @param unknown $pdf
 *            PDF document to print the list in
 * @param unknown $logofilepath
 *            the logo
 * @param unknown $downloadexam
 *            the exam
 * @param unknown $course
 *            the course
 * @param unknown $studentinfo
 *            the student info including name and idnumber
 */
function emarking_draw_student_list($pdf, $logofilepath, $downloadexam, $course, $studentinfo) {
    global $CFG;
    // Pages should be added automatically while the list grows.
    $pdf->SetAutoPageBreak(true);
    $pdf->AddPage();
    // If we have a logo we draw it.
    $left = 10;
    if ($CFG->emarking_includelogo && $logofilepath) {
        $pdf->Image($logofilepath, $left, 6, 30);
        $left += 40;
    }
    // We position to the right of the logo and write exam name.
    $top = 7;
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetXY($left, $top);
    $pdf->Write(1, core_text::strtoupper($downloadexam->name));
    // Write course name.
    $top += 6;
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->SetXY($left, $top);
    $pdf->Write(1, core_text::strtoupper(get_string('course') . ': ' . $course->fullname));
    $teachers = get_enrolled_users(context_course::instance($course->id), 'mod/emarking:supervisegrading');
    $teachersnames = array();
    foreach ($teachers as $teacher) {
        $teachersnames [] = $teacher->firstname . ' ' . $teacher->lastname;
    }
    $teacherstring = implode(',', $teachersnames);
    // Write number of students.
    $top += 4;
    $pdf->SetXY($left, $top);
    $pdf->Write(1, core_text::strtoupper(get_string('teacher', 'mod_emarking') . ': ' . $teacherstring));
    // Write date.
    $top += 4;
    $pdf->SetXY($left, $top);
    $pdf->Write(1, core_text::strtoupper(get_string('date') . ': ' . date("l jS F g:ia", $downloadexam->examdate)));
    // Write number of students.
    $top += 4;
    $pdf->SetXY($left, $top);
    $pdf->Write(1, core_text::strtoupper(get_string('students') . ': ' . count($studentinfo)));
    // Write the table header.
    $left = 10;
    $top += 8;
    $pdf->SetXY($left, $top);
    $pdf->Cell(10, 10, "N°", 1, 0, 'C');
    $pdf->Cell(20, 10, core_text::strtoupper(get_string('idnumber')), 1, 0, 'C');
    $pdf->Cell(20, 10, core_text::strtoupper(get_string('photo', 'mod_emarking')), 1, 0, 'C');
    $pdf->Cell(90, 10, core_text::strtoupper(get_string('name')), 1, 0, 'C');
    $pdf->Cell(50, 10, core_text::strtoupper(get_string('signature', 'mod_emarking')), 1, 0, 'C');
    $pdf->Ln();
    // Write each student.
    $current = 0;
    foreach ($studentinfo as $stlist) {
        if (! $stlist->id && $downloadexam->extraexams > 0) {
            error_log(print_r($stlist, true));
            continue;
        }
        $current ++;
        $pdf->Cell(10, 10, $current, 1, 0, 'C');
        $pdf->Cell(20, 10, $stlist->idnumber, 1, 0, 'C');
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Image($stlist->picture, $x + 5, $y, 10, 10, "PNG", null, "T", true);
        $pdf->SetXY($x, $y);
        $pdf->Cell(20, 10, "", 1, 0, 'L');
        $pdf->Cell(90, 10, core_text::strtoupper($stlist->name), 1, 0, 'L');
        $pdf->Cell(50, 10, "", 1, 0, 'L');
        $pdf->Ln();
    }
}
/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2015 Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function emarking_upload_answers($emarking, $fileid, $course, $cm, progress_bar $progressbar = null) {
    global $CFG, $DB;
    $context = context_module::instance($cm->id);
    // Setup de directorios temporales.
    $tempdir = emarking_get_temp_dir_path($emarking->id);
    if (! emarking_unzip($fileid, $tempdir . "/")) {
        return array(
            false,
            get_string('errorprocessingextraction', 'mod_emarking'),
            0,
            0);
    }
    $totaldocumentsprocessed = 0;
    $totaldocumentsignored = 0;
    // Read full directory, then start processing.
    $files = scandir($tempdir);
    $doubleside = false;
    $pdffiles = array();
    foreach ($files as $fileintemp) {
        if (! is_dir($fileintemp) && strtolower(substr($fileintemp, - 4, 4)) === ".png") {
            $pdffiles [] = $fileintemp;
            if (strtolower(substr($fileintemp, - 5, 5)) === "b.png") {
                $doubleside = true;
            }
        }
    }
    $total = count($pdffiles);
    if ($total == 0) {
        return array(
            false,
            get_string('nopagestoprocess', 'mod_emarking'),
            0,
            0);
    }
    // Process files.
    for ($current = 0; $current < $total; $current ++) {
        $file = $pdffiles [$current];
        $filename = explode(".", $file);
        $updatemessage = $filename;
        if ($progressbar) {
            $progressbar->update($current, $total, $updatemessage);
        }
        $parts = explode("-", $filename [0]);
        if (count($parts) != 3) {
            if ($CFG->debug) {
                echo "Ignoring $file as it has invalid name";
            }
            $totaldocumentsignored ++;
            continue;
        }
        $studentid = $parts [0];
        $courseid = $parts [1];
        $pagenumber = $parts [2];
        // Now we process the files according to the emarking type.
        if ($emarking->type == EMARKING_TYPE_NORMAL || $emarking->type == EMARKING_TYPE_PEER_REVIEW) {
            if (! $student = $DB->get_record('user', array(
                'id' => $studentid))) {
                $totaldocumentsignored ++;
                continue;
            }
            if ($student->deleted) {
                $totaldocumentsignored ++;
                continue;
            }
            if ($courseid != $course->id) {
                $totaldocumentsignored ++;
                continue;
            }
            if (! is_enrolled($context, $student, "mod/emarking:submit")) {
                $totaldocumentsignored ++;
                continue;
            }
        } else if ($emarking->type == EMARKING_TYPE_MARKER_TRAINING) {
            $student = new stdClass();
            $student->id = 0;
        } else {
            $student = new stdClass();
            $student->id = $studentid;
        }
        // 1 pasa a 1 1 * 2 - 1 = 1.
        // 1b pasa a 2 1 * 2.
        // 2 pasa a 3 2 * 2 -1 = 3.
        // 2b pasa a 4 2 * 2.
        $anonymouspage = false;
        // First clean the page number if it's anonymous.
        if (substr($pagenumber, - 2) === "_a") {
            $pagenumber = substr($pagenumber, 0, strlen($pagenumber) - 2);
            $anonymouspage = true;
        }
        if ($doubleside) {
            if (substr($pagenumber, - 1) === "b") { // Detecta b.
                $pagenumber = intval($pagenumber) * 2;
            } else {
                $pagenumber = intval($pagenumber) * 2 - 1;
            }
        }
        if ($anonymouspage) {
            continue;
        }
        if (! is_numeric($pagenumber)) {
            if ($CFG->debug) {
                echo "Ignored file: $filename[0] page: $pagenumber student id: $studentid course id: $courseid";
            }
            $totaldocumentsignored ++;
            continue;
        }
        if (emarking_submit($emarking, $context, $tempdir, $file, $student, $pagenumber)) {
            $totaldocumentsprocessed ++;
        } else {
            return array(
                false,
                get_string('invalidzipnoanonymous', 'mod_emarking'),
                $totaldocumentsprocessed,
                $totaldocumentsignored);
        }
    }
    if ($emarking->type == EMARKING_TYPE_PEER_REVIEW) {
        if (!emarking_assign_peers($emarking, 10)) {
            echo "Error assigning peers";
        }
    }
    emarking_send_processanswers_notification($emarking, $course);
    return array(
        true,
        get_string('invalidpdfnopages', 'mod_emarking'),
        $totaldocumentsprocessed,
        $totaldocumentsignored);
}
/**
 * Uploads a PDF file as a student's submission for a specific assignment
 *
 * @param object $emarking
 *            the assignment object from dbrecord
 * @param unknown_type $context
 *            the coursemodule
 * @param unknown_type $course
 *            the course object
 * @param unknown_type $path
 * @param unknown_type $filename
 * @param unknown_type $student
 * @param unknown_type $numpages
 * @param unknown_type $merge
 * @return boolean
 */
function emarking_submit($emarking, $context, $path, $filename, $student, $pagenumber = 0) {
    global $DB, $USER, $CFG;
    // All libraries for grading.
    require_once("$CFG->dirroot/grade/grading/lib.php");
    require_once($CFG->dirroot . '/grade/lib.php');
    require_once("$CFG->dirroot/grade/grading/form/rubric/lib.php");
    // Calculate anonymous file name from original file name.
    $filenameparts = explode(".", $filename);
    $anonymousfilename = $filenameparts [0] . "_a." . $filenameparts [1];
    // Verify that both image files (anonymous and original) exist.
    if (! file_exists($path . "/" . $filename) || ! file_exists($path . "/" . $anonymousfilename)) {
        throw new Exception("Invalid path and/or filename $path $filename");
    }
    if (! $student) {
        throw new Exception("Invalid student to submit page");
    }
    // Filesystem.
    $fs = get_file_storage();
    $userid = isset($student->firstname) ? $student->id : $USER->id;
    $author = isset($student->firstname) ? $student->firstname . ' ' . $student->lastname :
        $USER->firstname . ' ' . $USER->lastname;
    // Copy file from temp folder to Moodle's filesystem.
    $filerecord = array(
        'contextid' => $context->id,
        'component' => 'mod_emarking',
        'filearea' => 'pages',
        'itemid' => $emarking->id,
        'filepath' => '/',
        'filename' => $filename,
        'timecreated' => time(),
        'timemodified' => time(),
        'userid' => $userid,
        'author' => $author,
        'license' => 'allrightsreserved');
    // If the file already exists we delete it.
    if ($fs->file_exists($context->id, 'mod_emarking', 'pages', $emarking->id, '/', $filename)) {
        $previousfile = $fs->get_file($context->id, 'mod_emarking', 'pages', $emarking->id, '/', $filename);
        $previousfile->delete();
    }
    // Info for the new file.
    $fileinfo = $fs->create_file_from_pathname($filerecord, $path . '/' . $filename);
    // Now copying the anonymous version of the file.
    $filerecord ['filename'] = $anonymousfilename;
    // Check if anoymous file exists and delete it.
    if ($fs->file_exists($context->id, 'mod_emarking', 'pages', $emarking->id, '/', $anonymousfilename)) {
        $previousfile = $fs->get_file($context->id, 'mod_emarking', 'pages', $emarking->id, '/', $anonymousfilename);
        $previousfile->delete();
    }
    $fileinfoanonymous = $fs->create_file_from_pathname($filerecord, $path . '/' . $anonymousfilename);
    $submission = emarking_get_or_create_submission($emarking, $student, $context);
    // Get the page from previous uploads. If exists update it, if not insert a new page.
    $page = $DB->get_record('emarking_page',
            array(
                'submission' => $submission->id,
                'student' => $student->id,
                'page' => $pagenumber));
    if ($page != null) {
        $page->file = $fileinfo->get_id();
        $page->fileanonymous = $fileinfoanonymous->get_id();
        $page->timemodified = time();
        $page->teacher = $USER->id;
        $DB->update_record('emarking_page', $page);
    } else {
        $page = new stdClass();
        $page->student = $student->id;
        $page->page = $pagenumber;
        $page->file = $fileinfo->get_id();
        $page->fileanonymous = $fileinfoanonymous->get_id();
        $page->submission = $submission->id;
        $page->timecreated = time();
        $page->timemodified = time();
        $page->teacher = $USER->id;
        $page->id = $DB->insert_record('emarking_page', $page);
    }
    // Update submission info.
    $submission->teacher = $page->teacher;
    $submission->timemodified = $page->timemodified;
    $DB->update_record('emarking_submission', $submission);
    return true;
}
/**
 * Esta funcion copia el archivo solicitado mediante el Hash (lo busca en la base de datos) en la carpeta temporal especificada.
 *
 * @param String $tempdir
 *            Carpeta a la cual queremos copiar el archivo
 * @param String $hash
 *            hash del archivo en base de datos
 * @param String $prefix
 *            ???
 * @return mixed
 */
function emarking_get_path_from_hash($tempdir, $hash, $prefix = '', $create = true) {
    global $CFG;
    // Obtiene filesystem.
    $fs = get_file_storage();
    // Obtiene archivo gracias al hash.
    if (! $file = $fs->get_file_by_hash($hash)) {
        return false;
    }
    // Se copia archivo desde Moodle a temporal.
    $newfile = emarking_clean_filename($tempdir . '/' . $prefix . $file->get_filename());
    $file->copy_content_to($newfile);
    return $newfile;
}
/**
 * Counts files in dir using an optional suffix
 *
 * @param unknown $dir
 *            Folder to count files from
 * @param string $suffix
 *            File extension to filter
 */
function emarking_count_files_in_dir($dir, $suffix = ".pdf") {
    return count(emarking_get_files_list($dir, $suffix));
}
/**
 * Gets a list of files filtered by extension from a folder
 *
 * @param unknown $dir
 *            Folder
 * @param string $suffix
 *            Extension to filter
 * @return multitype:unknown Array of filenames
 */
function emarking_get_files_list($dir, $suffix = ".pdf") {
    $files = scandir($dir);
    $cleanfiles = array();
    foreach ($files as $filename) {
        if (! is_dir($filename) && substr($filename, - 4, 4) === $suffix) {
            $cleanfiles [] = $filename;
        }
    }
    return $cleanfiles;
}
/**
 * Calculates the total number of pages an exam will have for printing statistics
 * according to extra sheets, extra exams and if it has a personalized header and
 * if it uses the backside
 *
 * @param unknown $exam
 *            the exam object
 * @param unknown $numpages
 *            total pages in document
 * @return number total pages to print
 */
function emarking_exam_total_pages_to_print($exam) {
    if (! $exam) {
        return 0;
    }
    $total = $exam->totalpages + $exam->extrasheets;
    if ($exam->totalstudents > 0) {
        $total = $total * ($exam->totalstudents + $exam->extraexams);
    }
    if ($exam->usebackside) {
        $total = round($total / 2);
    }
    return $total;
}
/**
 * Send email with the downloading code.
 *
 * @param unknown_type $code
 * @param unknown_type $user
 * @param unknown_type $coursename
 * @param unknown_type $examname
 */
function emarking_send_email_code($code, $user, $coursename, $examname) {
    global $CFG;
    $posttext = get_string('emarkingsecuritycode', 'mod_emarking') . '\n';
    $posttext .= $coursename . ' ' . $examname . '\n';
    $posttext .= get_string('yourcodeis', 'mod_emarking') . ': ' . $code . '';
    $thismessagehtml = '<html>';
    $thismessagehtml .= '<h3>' . get_string('emarkingsecuritycode', 'mod_emarking') . '</h3>';
    $thismessagehtml .= $coursename . ' ' . $examname . '<br>';
    $thismessagehtml .= get_string('yourcodeis', 'mod_emarking') . ':<br>' . $code . '<br>';
    $thismessagehtml .= '</html>';
    $subject = get_string('emarkingsecuritycode', 'mod_emarking');
    $headers = "From: $CFG->supportname  \r\n" . "Reply-To: $CFG->noreplyaddress\r\n" . 'Content-Type: text/html; charset="utf-8"' .
             "\r\n" . 'X-Mailer: PHP/' . phpversion();
    $eventdata = new stdClass();
    $eventdata->component = 'mod_emarking';
    $eventdata->name = 'notification';
    $eventdata->userfrom = get_admin();
    $eventdata->userto = $user;
    $eventdata->subject = $subject;
    $eventdata->fullmessage = $posttext;
    $eventdata->fullmessageformat = FORMAT_HTML;
    $eventdata->fullmessagehtml = $thismessagehtml;
    $eventdata->smallmessage = $subject;
    $eventdata->notification = 1;
    return message_send($eventdata);
}
/**
 * Gets course names for all courses that share the same exam file
 *
 * @param unknown $exam
 * @return multitype:boolean unknown
 */
function emarking_exam_get_parallels($exam) {
    global $DB;
    // Checking if exam is for multicourse.
    $courses = array();
    $canbedeleted = true;
    // Find all exams with the same PDF file.
    $multi = $DB->get_records('emarking_exams', array(
        'file' => $exam->file), 'courseshortname ASC');
    foreach ($multi as $mult) {
        if ($mult->status >= EMARKING_EXAM_SENT_TO_PRINT) {
            $canbedeleted = false;
        }
        if ($mult->id != $exam->id) {
            $shortname = $DB->get_record('course', array(
                'id' => $mult->course));
            list($academicperiod, $campus, $coursecode, $section, $term, $year) = emarking_parse_shortname($shortname->shortname);
            $courses [] = html_writer::span("$campus-$section", null,
                    array(
                        "title" => "$shortname->fullname"));
        }
    }
    sort($courses, SORT_NATURAL | SORT_FLAG_CASE);
    $multicourse = implode("<br/>", $courses);
    return array(
        $canbedeleted,
        $multicourse);
}
/**
 * Creates the PDF version (downloadable) of the whole feedback produced by the teacher/tutor
 *
 * @param unknown $draft
 * @param unknown $student
 * @param unknown $context
 * @param unknown $cmid
 * @return boolean
 */
function emarking_create_response_pdf($draft, $student, $context, $cmid) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/pdflib.php');
    $fs = get_file_storage();
    if (! $submission = $DB->get_record('emarking_submission', array(
        'id' => $draft->submissionid))) {
        return false;
    }
    if (! $pages = $DB->get_records('emarking_page',
            array(
                'submission' => $submission->id,
                'student' => $student->id), 'page ASC')) {
        return false;
    }
    if (! $emarking = $DB->get_record('emarking', array(
        'id' => $submission->emarking))) {
        return false;
    }
    $numpages = count($pages);
    $sqlcomments = "SELECT ec.id,
			ec.posx,
			ec.posy,
			ec.rawtext,
			ec.pageno,
			grm.maxscore,
			ec.levelid,
			ec.width,
			ec.colour,
			ec.textformat,
			grl.score AS score,
			grl.definition AS leveldesc,
			grc.id AS criterionid,
			grc.description AS criteriondesc,
			u.id AS markerid, CONCAT(u.firstname,' ',u.lastname) AS markername
			FROM {emarking_comment} ec
			INNER JOIN {emarking_page} ep ON (ec.draft = :draft AND ec.page = ep.id)
			LEFT JOIN {user} u ON (ec.markerid = u.id)
			LEFT JOIN {gradingform_rubric_levels} grl ON (ec.levelid = grl.id)
			LEFT JOIN {gradingform_rubric_criteria} grc ON (grl.criterionid = grc.id)
			LEFT JOIN (
			SELECT grl.criterionid, max(score) AS maxscore
			FROM {gradingform_rubric_levels} grl
			GROUP BY grl.criterionid
			) AS grm ON (grc.id = grm.criterionid)
			WHERE ec.pageno > 0
			ORDER BY ec.pageno";
    $params = array(
        'draft' => $draft->id);
    $comments = $DB->get_records_sql($sqlcomments, $params);
    $commentsperpage = array();
    foreach ($comments as $comment) {
        if (! isset($commentsperpage [$comment->pageno])) {
            $commentsperpage [$comment->pageno] = array();
        }
        $commentsperpage [$comment->pageno] [] = $comment;
    }
    // Parameters for PDF generation.
    $iconsize = 5;
    $tempdir = emarking_get_temp_dir_path($emarking->id);
    if (! file_exists($tempdir)) {
        mkdir($tempdir);
    }
    // Create new PDF document.
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // Set document information.
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($student->firstname . ' ' . $student->lastname);
    $pdf->SetTitle($emarking->name);
    $pdf->SetSubject('Exam feedback');
    $pdf->SetKeywords('feedback, emarking');
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    // Set default header data.
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 036', PDF_HEADER_STRING);
    // Set header and footer fonts.
    $pdf->setHeaderFont(Array(
        PDF_FONT_NAME_MAIN,
        '',
        PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(
        PDF_FONT_NAME_DATA,
        '',
        PDF_FONT_SIZE_DATA));
    // Set default monospaced font.
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    // Set margins.
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    // Set auto page breaks.
    $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    // Set image scale factor.
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    // Set some language-dependent strings (optional).
    if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
        require_once(dirname(__FILE__) . '/lang/eng.php');
        $pdf->setLanguageArray($l);
    }
    // Set font.
    $pdf->SetFont('times', '', 16);
    foreach ($pages as $page) {
        // Add a page.
        $pdf->AddPage();
        // Get the current page break margin.
        $bmargin = $pdf->getBreakMargin();
        // Get current auto-page-break mode.
        $autopagebreak = $pdf->getAutoPageBreak();
        // Disable auto-page-break.
        $pdf->SetAutoPageBreak(false, 0);
        // Set bacground image.
        $pngfile = $fs->get_file_by_id($page->file);
        $imgfile = emarking_get_path_from_hash($tempdir, $pngfile->get_pathnamehash());
        $pdf->Image($imgfile, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
        // Restore auto-page-break status.
        // Set the starting point for the page content.
        $pdf->setPageMark();
        $dimensions = $pdf->getPageDimensions();
        if (isset($commentsperpage [$page->page])) {
            foreach ($commentsperpage [$page->page] as $comment) {
                $content = $comment->rawtext;
                $posx = (int) (((float) $comment->posx) * $dimensions ['w']);
                $posy = (int) (((float) $comment->posy) * $dimensions ['h']);
                if ($comment->textformat == 1) {
                    // Text annotation.
                    $pdf->Annotation($posx, $posy, 6, 6, $content,
                            array(
                                'Subtype' => 'Text',
                                'StateModel' => 'Review',
                                'State' => 'None',
                                'Name' => 'Comment',
                                'NM' => 'Comment' . $comment->id,
                                'T' => $comment->markername,
                                'Subj' => 'example',
                                'C' => array(
                                    0,
                                    0,
                                    255)));
                } else if ($comment->textformat == 2) {
                    $content = $comment->criteriondesc . ': ' . round($comment->score, 1) . '/' . round($comment->maxscore, 1) .
                            "\n" . $comment->leveldesc . "\n" . get_string('comment', 'mod_emarking') . ': ' . $content;
                    // Text annotation.
                    $pdf->Annotation($posx, $posy, 6, 6, $content,
                            array(
                                'Subtype' => 'Text',
                                'StateModel' => 'Review',
                                'State' => 'None',
                                'Name' => 'Comment',
                                'NM' => 'Mark' . $comment->id,
                                'T' => $comment->markername,
                                'Subj' => 'grade',
                                'C' => array(
                                    255,
                                    255,
                                    0)));
                } else if ($comment->textformat == 3) {
                    $pdf->Image($CFG->dirroot . "/mod/emarking/img/check.gif", $posx, $posy, $iconsize, $iconsize, '', '', '',
                            false, 300, '', false, false, 0);
                } else if ($comment->textformat == 4) {
                    $pdf->Image($CFG->dirroot . "/mod/emarking/img/crossed.gif", $posx, $posy, $iconsize, $iconsize, '', '', '',
                            false, 300, '', false, false, 0);
                }
            }
        }
    }
    // Print rubric.
    if ($emarking->downloadrubricpdf) {
        $cm = new StdClass();
        $rubricdesc = $DB->get_recordset_sql(
                "SELECT
		d.name AS rubricname,
		a.id AS criterionid,
		a.description ,
		b.definition,
		b.id AS levelid,
		b.score,
		IFNULL(E.id,0) AS commentid,
		IFNULL(E.pageno,0) AS commentpage,
		E.rawtext AS commenttext,
		E.markerid AS markerid,
		IFNULL(E.textformat,2) AS commentformat,
		IFNULL(E.bonus,0) AS bonus,
		IFNULL(er.id,0) AS regradeid,
		IFNULL(er.motive,0) AS motive,
		er.comment AS regradecomment,
		IFNULL(er.markercomment, '') AS regrademarkercomment,
		IFNULL(er.accepted,0) AS regradeaccepted
		FROM {course_modules} c
		INNER JOIN {context} mc ON (c.id = :coursemodule AND c.id = mc.instanceid)
		INNER JOIN {grading_areas} ar ON (mc.id = ar.contextid)
		INNER JOIN {grading_definitions} d ON (ar.id = d.areaid)
		INNER JOIN {gradingform_rubric_criteria} a ON (d.id = a.definitionid)
		INNER JOIN {gradingform_rubric_levels} b ON (a.id = b.criterionid)
		LEFT JOIN (
		SELECT ec.*, d.id AS draftid
		FROM {emarking_comment} ec
		INNER JOIN {emarking_draft} d ON (d.id = :draft AND ec.draft = d.id)
		) E ON (E.levelid = b.id)
		LEFT JOIN {emarking_regrade} er ON (er.criterion = a.id AND er.draft = E.draftid)
		ORDER BY a.sortorder ASC, b.score ASC",
                array(
                    'coursemodule' => $cmid,
                    'draft' => $draft->id));
        $table = new html_table();
        $data = array();
        foreach ($rubricdesc as $rd) {
            if (! isset($data [$rd->criterionid])) {
                $data [$rd->criterionid] = array(
                    $rd->description,
                    $rd->definition . " (" . round($rd->score, 2) . " ptos. )");
            } else {
                array_push($data [$rd->criterionid], $rd->definition . " (" . round($rd->score, 2) . " ptos. )");
            }
        }
        $table->data = $data;
        // Add extra page with rubric.
        $pdf->AddPage();
        $pdf->Write(0, 'Rúbrica', '', 0, 'L', true, 0, false, false, 0);
        $pdf->SetFont('helvetica', '', 8);
        $tbl = html_writer::table($table);
        $pdf->writeHTML($tbl, true, false, false, false, '');
    }
    $pdffilename = 'response_' . $emarking->id . '_' . $draft->id . '.pdf';
    $pathname = $tempdir . '/' . $pdffilename;
    if (@file_exists($pathname)) {
        unlink($pathname);
    }
    // Close and output PDF document.
    $pdf->Output($pathname, 'F');
    // Copiar archivo desde temp a Area.
    $filerecord = array(
        'contextid' => $context->id,
        'component' => 'mod_emarking',
        'filearea' => 'response',
        'itemid' => $student->id,
        'filepath' => '/',
        'filename' => $pdffilename,
        'timecreated' => time(),
        'timemodified' => time(),
        'userid' => $student->id,
        'author' => $student->firstname . ' ' . $student->lastname,
        'license' => 'allrightsreserved');
    // Si el archivo ya existía entonces lo borramos.
    if ($fs->file_exists($context->id, 'mod_emarking', 'response', $student->id, '/', $pdffilename)) {
        $previousfile = $fs->get_file($context->id, 'mod_emarking', 'response', $student->id, '/', $pdffilename);
        $previousfile->delete();
    }
    $fileinfo = $fs->create_file_from_pathname($filerecord, $pathname);
    return true;
}
/**
 * Creates a personalized exam file.
 *
 * @param unknown $examid
 * @return NULL
 */
function emarking_download_exam($examid, $multiplepdfs = false, $groupid = null, progress_bar $pbar = null,
        $sendprintorder = false, $idprinter = null, $printanswersheet = false, $debugprinting = false) {
    global $DB, $CFG, $USER, $OUTPUT;
    require_once($CFG->dirroot . '/mod/emarking/lib/openbub/ans_pdf_open.php');
    // Validate emarking exam object.
    if (! $downloadexam = $DB->get_record('emarking_exams', array(
        'id' => $examid))) {
        throw new Exception(get_string("invalidexamid", "mod_emarking"));
    }
    // Contexto del curso para verificar permisos.
    $context = context_course::instance($downloadexam->course);
    if (! has_capability('mod/emarking:downloadexam', $context)) {
        throw new Exception(get_string("invalidaccess", "mod_emarking"));
    }
    // Verify that remote printing is enable, otherwise disable a printing order.
    if ($sendprintorder && (! $CFG->emarking_enableprinting || $idprinter == null)) {
        throw new Exception('Printing is not enabled or printername was absent ' . $idprinter);
    }
    // Validate course.
    if (! $course = $DB->get_record('course', array(
        'id' => $downloadexam->course))) {
        throw new Exception(get_string("invalidcourse", "mod_emarking"));
    }
    // Validate course category.
    if (! $coursecat = $DB->get_record('course_categories', array(
        'id' => $course->category))) {
        throw new Exception(get_string("invalidcategoryid", "mod_emarking"));
    }
    // We tell the user we are setting up the printing.
    if ($pbar) {
        $pbar->update(0, 1, get_string('settingupprinting', 'mod_emarking'));
    }
    // Default value for enrols that will be included.
    if ($CFG->emarking_enrolincludes && strlen($CFG->emarking_enrolincludes) > 1) {
        $enrolincludes = $CFG->emarking_enrolincludes;
    }
    // If the exam sets enrolments, we use those.
    if (isset($downloadexam->enrolments) && strlen($downloadexam->enrolments) > 1) {
        $enrolincludes = $downloadexam->enrolments;
    }
    // Convert enrolments to array.
    $enrolincludes = explode(",", $enrolincludes);
    // Produce all PDFs first separatedly.
    $filedir = $CFG->dataroot . "/temp/emarking/$context->id";
    $fileimg = $filedir . "/qr";
    $userimgdir = $filedir . "/u";
    $pdfdir = $filedir . "/pdf";
    emarking_initialize_directory($filedir, true);
    emarking_initialize_directory($fileimg, true);
    emarking_initialize_directory($userimgdir, true);
    emarking_initialize_directory($pdfdir, true);
    // Get all the files uploaded as forms for this exam.
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_emarking', 'exams', $examid);
    // We filter only the PDFs.
    $pdffileshash = array();
    foreach ($files as $filepdf) {
        if ($filepdf->get_mimetype() === 'application/pdf') {
            $pdffileshash [] = array(
                'hash' => $filepdf->get_pathnamehash(),
                'filename' => $filepdf->get_filename(),
                'path' => emarking_get_path_from_hash($filedir, $filepdf->get_pathnamehash()));
        }
    }
    // Verify that at least we have a PDF.
    if (count($pdffileshash) < 1) {
        throw new Exception(get_string("examhasnopdf", "mod_emarking"));
    }
    $students = emarking_get_students_for_printing($downloadexam->course);
    $studentinfo = array();
    $currenttemplate = 0;
    // Fill studentnames with student info (name, idnumber, id and picture).
    foreach ($students as $student) {
        $studentenrolments = explode(",", $student->enrol);
        // Verifies that the student is enrolled through a valid enrolment and that we haven't added her yet.
        if (count(array_intersect($studentenrolments, $enrolincludes)) == 0 || isset($studentinfo [$student->id])) {
            continue;
        }
        // We create a student info object.
        $studentobj = new stdClass();
        $studentobj->name = substr("$student->lastname, $student->firstname", 0, 65);
        $studentobj->idnumber = $student->idnumber;
        $studentobj->id = $student->id;
        $studentobj->picture = emarking_get_student_picture($student, $userimgdir);
        // Store student info in hash so every student is stored once.
        $studentinfo [$student->id] = $studentobj;
    }
    // We validate the number of students as we are filtering by enrolment.
    // type after getting the data.
    $numberstudents = count($studentinfo);
    if ($numberstudents == 0) {
        throw new Exception('No students to print/create the exam');
    }
    // Add the extra students to the list.
    for ($i = $numberstudents; $i < $numberstudents + $downloadexam->extraexams; $i ++) {
        $studentobj = new stdClass();
        $studentobj->name = '..............................................................................';
        $studentobj->idnumber = 0;
        $studentobj->id = 0;
        $studentobj->picture = $CFG->dirroot . "/pix/u/f1.png";
        $studentinfo [] = $studentobj;
    }
    // Check if there is a logo file.
    $logofilepath = emarking_get_logo_file($filedir);
    // If asked to do so we create a PDF witht the students list.
    if ($downloadexam->printlist == 1) {
        $pdf = new FPDI();
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        emarking_draw_student_list($pdf, $logofilepath, $downloadexam, $course, $studentinfo);
        $studentlistpdffile = $pdfdir . "/000-studentslist.pdf";
        $pdf->Output($studentlistpdffile, "F"); // Se genera el nuevo pdf.
        $pdf = null;
    }
    // Here we produce a PDF file for each student.
    $currentstudent = 0;
    foreach ($studentinfo as $stinfo) {
        // If we have a progress bar, we notify the new PDF being created.
        if ($pbar) {
            $pbar->update($currentstudent + 1, count($studentinfo), $stinfo->name);
        }
        // We create the PDF file.
        $pdf = new FPDI();
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        // We use the next form available from the list of PDF forms sent.
        if ($currenttemplate >= count($pdffileshash) - 1) {
            $currenttemplate = 0;
        } else {
            $currenttemplate ++;
        }
        // Load the PDF from the filesystem as template.
        $path = $pdffileshash [$currenttemplate] ['path'];
        $originalpdfpages = $pdf->setSourceFile($path);
        $pdf->SetAutoPageBreak(false);
        // Add all pages in the template, adding the header if it corresponds.
        for ($pagenumber = 1; $pagenumber <= $originalpdfpages + $downloadexam->extrasheets; $pagenumber ++) {
            // Adding a page.
            $pdf->AddPage();
            // If the page is not an extra page, we import the page from the template.
            if ($pagenumber <= $originalpdfpages) {
                $template = $pdf->importPage($pagenumber);
                $pdf->useTemplate($template, 0, 0, 0, 0, true);
            }
            // If we have a personalized header, we add it.
            if ($downloadexam->headerqr) {
                emarking_draw_header($pdf, $stinfo, $downloadexam->name, $pagenumber, $fileimg, $logofilepath, $course,
                        $originalpdfpages + $downloadexam->extrasheets);
            }
        }
        // The filename will be the student id - course id - page number.
        $qrstringtmp = $stinfo->id > 0 ? "$stinfo->id-$course->id-$pagenumber" : "NN$currentstudent+1-$course->id-$pagenumber";
        // Create the PDF file for the student.
        $pdffile = $pdfdir . "/" . $qrstringtmp . ".pdf";
        $pdf->Output($pdffile, "F");
        // Store the exam file for printing later.
        $stinfo->examfile = $pdffile;
        $stinfo->number = $currentstudent + 1;
        $stinfo->pdffilename = $qrstringtmp;
        $currentstudent ++;
    }
    $sqlprinter = "SELECT id, name, command
			FROM {emarking_printers}
			WHERE id = ?";
    $printerinfo = $DB->get_record_sql($sqlprinter, array(
        $idprinter));
    // If we have to print directly.
    $debugprintingmsg = '';
    if ($sendprintorder) {
        // Check if we have to print the students list.
        if ($downloadexam->printlist == 1) {
            $printresult = emarking_print_file($printerinfo->name, $printerinfo->command, $studentlistpdffile, $debugprinting);
            if (! $printresult) {
                $debugprintingmsg .= 'Problems printing ' . $studentlistpdffile . '<hr>';
            } else {
                $debugprintingmsg .= $printresult . '<hr>';
            }
        }
        // Print each student.
        $currentstudent = 0;
        foreach ($studentinfo as $stinfo) {
            $currentstudent ++;
            if ($pbar != null) {
                $pbar->update($currentstudent, count($studentinfo), get_string('printing', 'mod_emarking') . ' ' . $stinfo->name);
            }
            if (! isset($stinfo->examfile) || ! file_exists($stinfo->examfile)) {
                continue;
            }
            $printresult = emarking_print_file($printerinfo->name, $printerinfo->command, $stinfo->examfile, $debugprinting);
            if (! $printresult) {
                $debugprintingmsg .= 'Problems printing ' . $stinfo->examfile . '<hr>';
            } else {
                $debugprintingmsg .= $printresult . '<hr>';
            }
        }
        if ($CFG->debug || $debugprinting) {
            echo $debugprintingmsg;
        }
        // Notify everyone that the exam was printed.
        emarking_send_examprinted_notification($downloadexam, $course);
        $downloadexam->status = EMARKING_EXAM_PRINTED;
        $downloadexam->printdate = time();
        $DB->update_record('emarking_exams', $downloadexam);
        // Add to Moodle log so some auditing can be done.
        \mod_emarking\event\exam_downloaded::create_from_exam($downloadexam, $context)->trigger();
        return true;
    }
    $examfilename = emarking_clean_filename($course->shortname, true) . "_" . emarking_clean_filename($downloadexam->name, true);
    $zipdebugmsg = '';
    if ($multiplepdfs) {
        $zip = new ZipArchive();
        $zipfilename = $filedir . "/" . $examfilename . ".zip";
        if ($zip->open($zipfilename, ZipArchive::CREATE) !== true) {
            throw new Exception('Could not create zip file');
        }
        // Check if we have to print the students list.
        if ($downloadexam->printlist == 1) {
            $zip->addFile($studentlistpdffile);
        }
        // Add every student PDF to zip file.
        $currentstudent = 0;
        foreach ($studentinfo as $stinfo) {
            $currentstudent ++;
            if ($pbar != null) {
                $pbar->update($currentstudent, count($studentinfo), get_string('printing', 'mod_emarking') . ' ' . $stinfo->name);
            }
            if (! isset($stinfo->examfile) || ! file_exists($stinfo->examfile)) {
                continue;
            }
            if (! $zip->addFile($stinfo->examfile, $stinfo->pdffilename . '.pdf')) {
                $zipdebugmsg .= "Problems adding $stinfo->examfile to ZIP file using name $stinfo->pdffilename <hr>";
            }
        }
        $zip->close();
        if ($CFG->debug || $debugprinting) {
            echo $zipdebugmsg;
        }
        // Notify everyone that the exam was downloaded.
        emarking_send_examdownloaded_notification($downloadexam, $course);
        $downloadexam->status = EMARKING_EXAM_SENT_TO_PRINT;
        $downloadexam->printdate = time();
        $DB->update_record('emarking_exams', $downloadexam);
        // Add to Moodle log so some auditing can be done.
        \mod_emarking\event\exam_downloaded::create_from_exam($downloadexam, $context)->trigger();
        // Read zip file from disk and send to the browser.
        $filename = basename($zipfilename);
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=" . $examfilename . ".zip");
        header("Content-Length: " . filesize($zipfilename));
        readfile($zipfilename);
        exit();
    }
    // We create the final big PDF file.
    $pdf = new FPDI();
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    // We import the students list if required.
    if ($downloadexam->printlist) {
        emarking_import_pdf_into_pdf($pdf, $studentlistpdffile);
    }
    // Add every student PDF to zip file.
    $currentstudent = 0;
    foreach ($studentinfo as $stinfo) {
        $currentstudent ++;
        if (! isset($stinfo->examfile) || ! file_exists($stinfo->examfile)) {
            continue;
        }
        emarking_import_pdf_into_pdf($pdf, $stinfo->examfile);
    }
    // Notify everyone that the exam was downloaded.
    emarking_send_examdownloaded_notification($downloadexam, $course);
    $downloadexam->status = EMARKING_EXAM_SENT_TO_PRINT;
    $downloadexam->printdate = time();
    $DB->update_record('emarking_exams', $downloadexam);
    // Add to Moodle log so some auditing can be done.
    \mod_emarking\event\exam_downloaded::create_from_exam($downloadexam, $context)->trigger();
    $pdf->Output($examfilename . '.pdf', 'D');
}
function emarking_import_pdf_into_pdf(FPDI $pdf, $pdftoimport) {
    $originalpdfpages = $pdf->setSourceFile($pdftoimport);
    $pdf->SetAutoPageBreak(false);
    // Add all pages in the template, adding the header if it corresponds.
    for ($pagenumber = 1; $pagenumber <= $originalpdfpages; $pagenumber ++) {
        // Adding a page.
        $pdf->AddPage();
        $template = $pdf->importPage($pagenumber);
        $pdf->useTemplate($template, 0, 0, 0, 0, true);
    }
}
function emarking_print_file($printername, $command, $file, $debugprinting) {
    global $CFG;
    if (! $printername) {
        return null;
    }
    $server = '';
    if (isset($CFG->emarking_printserver) && strlen($CFG->emarking_printserver) > 3) {
        $server = '-h ' . $CFG->emarking_printserver;
    }
    $command = explode("#", $command);
    $cups = $command [0] . $printername . ' ' . $server . ' ' . $command [1] . " " . $file;
    $printresult = null;
    if (! $debugprinting) {
        $printresult = exec($cups);
    }
    if ($CFG->debug || $debugprinting) {
        $printresult .= "$cups <br>";
    }
    return $printresult;
}
/**
 * Draws the personalized header in a PDF
 *
 * @param unknown $pdf
 * @param unknown $stinfo
 * @param unknown $examname
 * @param unknown $pagenumber
 * @param unknown $fileimgpath
 * @param unknown $logofilepath
 * @param unknown $course
 * @param string $totalpages
 * @param string $bottomqr
 * @param string $isanswersheet
 * @param number $attemptid
 */
function emarking_draw_header($pdf, $stinfo, $examname, $pagenumber, $fileimgpath, $logofilepath, $course, $totalpages = null,
        $bottomqr = true, $isanswersheet = false, $attemptid = 0) {
    global $CFG;
    $pdf->SetAutoPageBreak(false);
    // If we have a log, it is drawn on the top left part.
    if ($CFG->emarking_includelogo && $logofilepath) {
        $pdf->Image($logofilepath, 2, 8, 30);
    }
    // Exam name.
    $left = 58;
    $top = 8;
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->SetXY($left, $top);
    $pdf->Write(1, core_text::strtoupper($examname));
    // Student name.
    $pdf->SetFont('Helvetica', '', 9);
    $top += 5;
    $pdf->SetXY($left, $top);
    $pdf->Write(1, core_text::strtoupper(get_string('name') . ": " . $stinfo->name));
    $top += 4;
    // Student idnumber.
    if ($stinfo->idnumber && strlen($stinfo->idnumber) > 0) {
        $pdf->SetXY($left, $top);
        $pdf->Write(1, get_string('idnumber', 'mod_emarking') . ": " . $stinfo->idnumber);
        $top += 4;
    }
    // Course name.
    $pdf->SetXY($left, $top);
    $pdf->Write(1, core_text::strtoupper(get_string('course') . ": " . $course->fullname));
    $top += 4;
    if (file_exists($stinfo->picture)) {
        $pdf->Image($stinfo->picture, 35, 8, 15, 15, "PNG", null, "T", true);
    }
    // Page number and total pages.
    if ($totalpages) {
        $totals = new stdClass();
        $totals->identified = $pagenumber;
        $totals->total = $totalpages;
        $pdf->SetXY($left, $top);
        $pdf->Write(1, core_text::strtoupper(get_string('page') . ": " . get_string('aofb', 'mod_emarking', $totals)));
    }
    // Generate the QR images.
    $qrstring = "$stinfo->id-$course->id-$pagenumber";
    // If the page is an answer sheets (has bubbles), add the attemptid.
    if ($isanswersheet && $attemptid > 0) {
        $qrstring .= '-' . $attemptid . '-BB';
    }
    list($img, $imgrotated) = emarking_create_qr_image($fileimgpath, $qrstring, $stinfo, $pagenumber);
    $pdf->Image($img, 176, 3, 34);
    if ($bottomqr) {
        $pdf->Image($imgrotated, 0, $pdf->getPageHeight() - 35, 34);
    }
    // Delete QR images.
    unlink($img);
    unlink($imgrotated);
}
/**
 * Creates a QR image based on a string
 *
 * @param unknown $fileimg
 * @param unknown $qrstring
 * @param unknown $stinfo
 * @param unknown $i
 * @return multitype:string
 */
function emarking_create_qr_image($fileimg, $qrstring, $stinfo, $i) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/emarking/lib/phpqrcode/phpqrcode.php');
    $h = random_string(15);
    $hash = random_string(15);
    $img = $fileimg . "/qr" . $h . "_" . $stinfo->idnumber . "_" . $i . "_" . $hash . ".png";
    $imgrotated = $fileimg . "/qr" . $h . "_" . $stinfo->idnumber . "_" . $i . "_" . $hash . "r.png";
    // The image is generated based on the string.
    QRcode::png($qrstring, $img);
    // Same image but rotated.
    QRcode::png($qrstring . "-R", $imgrotated);
    $gdimg = imagecreatefrompng($imgrotated);
    $rotated = imagerotate($gdimg, 180, 0);
    imagepng($rotated, $imgrotated);
    return array(
        $img,
        $imgrotated);
}
/**
 * Creates a QR image based on a string
 *
 * @param unknown $qrstring
 * @return string
 */
function emarking_create_qr_from_string($qrstring) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/emarking/lib/phpqrcode/phpqrcode.php');
    $path = emarking_get_temp_dir_path("attendance");
    if (! file_exists($path)) {
        mkdir($path, 0777, true);
    }
    $hash = random_string(15);
    $time = time();
    $img = $path . "/qr" . $time . $hash . ".png";
    // The image is generated based on the string.
    QRcode::png($qrstring, $img);
    return $img;
}
/**
 * Erraces all the content of a directory, then ir creates te if they don't exist.
 *
 * @param unknown $dir
 *            Directorio
 * @param unknown $delete
 *            Borrar archivos previamente
 */
function emarking_initialize_directory($dir, $delete) {
    if ($delete) {
        // First erase all files.
        if (is_dir($dir)) {
            emarking_rrmdir($dir);
        }
    }
    // Si no existe carpeta para temporales se crea.
    if (! is_dir($dir)) {
        if (! mkdir($dir, 0777, true)) {
            print_error(get_string('initializedirfail', 'mod_emarking', $dir));
        }
    }
}
/**
 * Recursively remove a directory.
 * Enter description here ...
 *
 * @param unknown_type $dir
 */
function emarking_rrmdir($dir) {
    foreach (glob($dir . '/*') as $file) {
        if (is_dir($file)) {
            emarking_rrmdir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dir);
}
/**
 * Sends an sms message using UAI's service with infobip.com.
 * Returns true if successful, false otherwise.
 *
 * @param string $message
 *            the message to be sent
 * @param string $number
 *            the mobile number
 */
function emarking_send_sms($message, $number) {
    global $CFG;
    // This line loads the library.
    require($CFG->dirroot . '/mod/emarking/lib/twilio/Services/Twilio.php');
    $accountsid = $CFG->emarking_smsuser;
    $authtoken = $CFG->emarking_smspassword;
    $client = new Services_Twilio($accountsid, $authtoken);
    $obj = $client->account->messages->create(
            array(
                'To' => "$number",
                'From' => "+" . $CFG->emarking_smsurl,
                'Body' => "$message"));
    return $obj->status === "queued";
}
/**
 * Replace "acentos", spaces from file names.
 * Evita problemas en Windows y Linux.
 *
 * @param unknown $filename
 *            El nombre original del archivo
 * @return unknown El nombre sin acentos, espacios.
 */
function emarking_clean_filename($filename, $slash = false) {
    $replace = array(
        ' ',
        'á',
        'é',
        'í',
        'ó',
        'ú',
        'ñ',
        'Ñ',
        'Á',
        'É',
        'Í',
        'Ó',
        'Ú',
        '(',
        ')',
        ',');
    $replacefor = array(
        '-',
        'a',
        'e',
        'i',
        'o',
        'u',
        'n',
        'N',
        'A',
        'E',
        'I',
        'O',
        'U',
        '-',
        '-',
        '-');
    if ($slash) {
        $replace [] = '/';
        $replacefor [] = '-';
    }
    $newfile = str_replace($replace, $replacefor, $filename);
    return $newfile;
}
// Gets an ipv4 address in dotted format and returns true if the format
// is acceptable.
function emarking_validate_ipv4_address($ipv4) {
    $valid = true;
    $pattern = '/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/';
    if (preg_match($pattern, $ipv4, $parts)) {
        if ($parts [1] > 0 && $parts [1] <= 255 && $parts [2] >= 0 && $parts [2] <= 255 && $parts [3] >= 0 && $parts [3] <= 255 &&
                 $parts [4] >= 0 && $parts [4] <= 255) {
            $valid = false;
        }
    }
    return $valid;
}
// Gets an ipv6 in hex format and returns true if the format is acceptable.
function emarking_validate_ipv6_address($ipv6) {
    $flag = true;
    // Uncompressed form.
    if (strpos($ipv6, '::') === false) {
        $pattern = '/^([a-f0-9]{1,4}\:){7}([a-f0-9]{1,4})$/i';
        if (preg_match($pattern, $ipv6)) {
            $flag = false;
        }
    } else if (substr_count($ipv6, '::') == 1) {
        $pattern = '/^([a-f0-9]{1,4}::?){1,}([a-f0-9]{1,4})$/i';
        if (preg_match($pattern, $ipv6)) {
            $flag = false;
        }
    }
    return $flag;
}