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
 * This file keeps track of upgrades to the emarking module
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations.
 * The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do. The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2013-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Crea un archivo PDF a partir de un quiz, agregando una hoja de respuestas de opción múltiple
 * 
 * @param unknown $cm
 * @param string $debug
 * @param string $context
 * @param string $course
 * @param string $logofilepath
 * @param boolean $answersheetsonly
 * @return void|NULL
 */
function emarking_create_quiz_pdf($cm, $debug = false, $context = null, $course = null, $answersheetsonly = false, $pbar = false)
{
    global $DB, $CFG, $OUTPUT;
    
    // Inclusión de librerías
    require_once ($CFG->dirroot . '/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php');
    require_once ($CFG->dirroot . '/mod/assign/feedback/editpdf/fpdi/fpdi.php');
    require_once ($CFG->libdir . '/pdflib.php');
    require_once ($CFG->dirroot . '/mod/quiz/locallib.php');
    require_once ($CFG->dirroot . '/mod/emarking/print/locallib.php');
    
    $filedir = $CFG->dataroot . "/temp/emarking/$context->id";
    emarking_initialize_directory($filedir, true);
    
    $fileimg = $CFG->dataroot . "/temp/emarking/$context->id/qr";
    emarking_initialize_directory($fileimg, true);
    
    $userimgdir = $CFG->dataroot . "/temp/emarking/$context->id/u";
    emarking_initialize_directory($userimgdir, true);
    
    $logofile = emarking_get_logo_file();
    $logofilepath = $logofile ? emarking_get_path_from_hash($filedir, $logofile->get_pathnamehash()) : null;
    
    $fullhtml = array();
    $numanswers = array();
    $attemptids = array();
    $images = array();
    $imageshtml = array();
    
    $users = emarking_get_enroled_students($course->id);
    
    if ($pbar) {
        echo $OUTPUT->heading(get_string('loadingquestions', 'mod_emarking'), 3);
        $progressbar = new progress_bar();
        $progressbar->create();
        $progressbar->update(0, count($users), get_string('processing', 'mod_emarking'));
    }
    
    $current = 0;
    foreach ($users as $user) {
        
        $current ++;
        if ($pbar) {
            $progressbar->update($current, count($users), "$user->firstname, $user->lastname");
        }
        // Get the quiz object
        $quizobj = quiz::create($cm->instance, $user->id);
        
        // Create the new attempt and initialize the question sessions
        $attemptnumber = 1;
        $lastattempt = null;
        $timenow = time(); // Update time now, in case the server is running really slowly.
        
        $attempts = quiz_get_user_attempts($quizobj->get_quizid(), $user->id, 'all');
        
        $numattempts = count($attempts);
        foreach ($attempts as $attempt) {
            
            $attemptobj = quiz_attempt::create($attempt->id);
            $slots = $attemptobj->get_slots();
            foreach ($slots as $slot) {
                $qattempt = $attemptobj->get_question_attempt($slot);
                $question = $qattempt->get_question();
                if ($question->get_type_name() === 'multianswer') {
                    $q = $question->subquestions[1];
                    $numanswers[$user->id][] = count($q->answers);
                } else 
                    if ($question->get_type_name() === 'multichoice') {
                        $numanswers[$user->id][] = count($question->answers);
                    }
                $attemptids[$user->id] = $attempt->id;
                $qhtml = $attemptobj->render_question($slot, false);
                $qhtml = emarking_clean_question_html($qhtml);
                $currentimages = emarking_extract_images_url($qhtml);
                $idx = 0;
                foreach ($currentimages[1] as $imageurl) {
                    if (! array_search($imageurl, $images)) {
                        $images[] = $imageurl;
                        $imageshtml[] = $currentimages[0][$idx];
                    }
                    $idx ++;
                }
                $fullhtml[$user->id][] = $qhtml;
            }
            
            // One attempt per user
            break;
        }
    }
    
    $save_to = $CFG->tempdir . '/emarking/printquiz/' . $cm->id . '/';
    emarking_initialize_directory($save_to, true);
    
    // Bajar las imágenes del HTML a dibujar
    $search = array();
    $replace = array();
    $replaceweb = array();
    $imagesize = array();
    $idx = 0;
    
    if ($pbar) {
        $progressbar->update_full(100, get_string('finished', 'mod_emarking'));
        echo $OUTPUT->heading(get_string('downloadingimages', 'mod_emarking'), 3);
        $progressbar = new progress_bar();
        $progressbar->create();
        $progressbar->update(0, count($images), get_string('processing', 'mod_emarking'));
    }
    
    foreach ($images as $image) {
        if ($pbar) {
            $imagefilename = explode("/", $image);
            $progressbar->update($idx + 1, count($images), $imagefilename[count($imagefilename)-1]);
        }
        // Si solamente incluiremos hojas de respuesta terminamos el ciclo
        if ($answersheetsonly)
            break;
        
        if (! list ($filename, $imageinfo) = emarking_get_file_from_url($image, $save_to)) {
            echo "Problem downloading file $image <hr>";
        } else {
            // Buscamos el src de la imagen
            $search[] = 'src="' . $image . '"';
            $replacehtml = ' src="' . $filename . '"';
            $replacehtmlxweb = ' src="' . $image . '"';
            // Si el html de la misma contiene ancho o alto, se deja tal cual
            $imghtml = $imageshtml[$idx];
            if (substr_count($imghtml, "width") + substr_count($imghtml, "height") == 0) {
                $width = $imageinfo[0];
                $height = $imageinfo[1];
                $ratio = floatval(10) / floatval($height);
                $height = 10;
                $width = (int) ($ratio * floatval($width));
                $sizehtml = 'width="' . $width . '" height="' . $height . '"';
                $replacehtml = $sizehtml . ' ' . $replacehtml;
                $replacehtmlxweb = $sizehtml . ' ' . $replacehtmlxweb;
            }
            $replace[] = $replacehtml;
            $replaceweb[] = $replacehtmlxweb;
            $imagesize[] = $imageinfo;
        }
        $idx ++;
    }
    
    
    if ($debug) {
        foreach ($fullhtml as $uid => $questions) {
            $index = 0;
            foreach ($questions as $question) {
                echo str_replace($search, $replaceweb, $fullhtml[$uid][$index]);
                $index++;
            }
        }
        return;
    }
    
    
    // Now we create the pdf file with the modified html
    $doc = new FPDI();
    $doc->setPrintHeader(false);
    $doc->setPrintFooter(false);
    $doc->SetFont('times', '', 12);
    
    // set margins
    $doc->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $doc->SetHeaderMargin(250);
    $doc->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    if($pbar) {
        $progressbar->update_full(100, get_string('finished', 'mod_emarking'));
        echo $OUTPUT->heading(get_string('creatingpdffile', 'mod_emarking'), 3);
        $progressbar = new progress_bar();
        $progressbar->create();
    }
    $current = 0;
    foreach ($fullhtml as $uid => $questions) {
        $current ++;
        $stinfo = $DB->get_record('user', array(
            'id' => $uid
        ));
        $stinfo->name = $stinfo->firstname . ' ' . $stinfo->lastname;
        $stinfo->picture = emarking_get_student_picture($stinfo, $userimgdir);
        $stinfo->idnumber = $uid . '-' . $attemptids[$uid];
        if ($pbar) {
            $progressbar->update($current, count($fullhtml), $stinfo->name);
        }
        $groups = groups_get_user_groups($course->id, $uid);
        if ($groups && isset($groups[0][0]) && $group = $DB->get_record('groups', array(
            'id' => $groups[0][0]
        ))) {
            $stinfo->group = $group->name;
        } else {
            $stinfo->group = '';
        }
        
        emarking_add_answer_sheet($doc, $filedir, $stinfo, $logofilepath, null, $fileimg, $course, $quizobj->get_quiz_name(), $numanswers[$uid], $attemptids[$uid]);
        
        // Una vez agregada la página de respuestas, si es todo lo que hay que hacer saltar al siguiente
        if ($answersheetsonly)
            continue;
        
        $doc->AddPage();
        emarking_draw_header($doc, $stinfo, $quizobj->get_quiz_name(), 2, $fileimg, $logofilepath, $course, null, false, 0);
        $doc->SetFont('times', '', 12);
        $doc->SetAutoPageBreak(true);
        $doc->SetXY(PDF_MARGIN_LEFT, 40);
        
        $index = 0;
        foreach ($questions as $question) {
            $prevy = $doc->getY();
            $fullhtml[$uid][$index] = str_replace($search, $replace, $fullhtml[$uid][$index]);
            $doc->writeHTML($fullhtml[$uid][$index]);
            
            $y = $doc->getY();
            $fmargin = $doc->getFooterMargin();
            $height = $doc->getPageHeight();
            $spaceleft = $height - $fmargin - $y;
            $questionsize = $y - $prevy; 
            
            if($spaceleft < 70) {
                $doc->AddPage();
            }
            $index ++;
        }
    }
    
    if ($pbar) {
        $progressbar->update_full(100, get_string('finished', 'mod_emarking'));
    }
    
    $qid = $quizobj->get_quizid();
    $pdfquizfilename = 'quiz-' . $qid . '-' . random_string() . '.pdf';
    
    $fs = get_file_storage();
    
    $filerecord = array(
        'component' => 'mod_emarking',
        'filearea' => 'pdfquiz',
        'contextid' => $context->id,
        'itemid' => $quizobj->get_quizid(),
        'filepath' => '/',
        'filename' => $pdfquizfilename
    );
    
    $doc->Output($filedir . '/' . $pdfquizfilename, 'F');
    
    $file = $fs->create_file_from_pathname($filerecord, $filedir . '/' . $pdfquizfilename);
    
    $downloadurl = moodle_url::make_file_url("$CFG->wwwroot/pluginfile.php", "/$context->id/mod_emarking/pdfquiz/$qid/$pdfquizfilename", null, true);
    
    return $downloadurl;
}

function emarking_draw_header($pdf, $stinfo, $examname, $pagenumber, $fileimgpath, $logofilepath, $course, $totalpages = null, $bottomqr = true, $isanswersheet = false, $attemptid = 0)
{
    global $CFG;
    
    $pdf->SetAutoPageBreak(false);
    
    // For the QR string and get the images
    $qrstring = "$stinfo->id-$course->id-$pagenumber";
    if ($isanswersheet && $attemptid > 0) {
        $qrstring .= '-' . $attemptid . '-BB';
    }
    list ($img, $imgrotated) = emarking_create_qr_image($fileimgpath, $qrstring, $stinfo, $pagenumber);
    
    if ($CFG->emarking_includelogo && $logofilepath) {
        $pdf->Image($logofilepath, 2, 8, 30);
    }
    
    $left = 38;
    $top = 8;
    $pdf->SetFont('times', '', 12);
    $pdf->SetXY($left, $top);
    $pdf->Write(1, core_text::strtoupper($examname));
    $pdf->SetFont('times', '', 9);
    $top += 5;
    $pdf->SetXY($left, $top);
    $pdf->Write(1, core_text::strtoupper($stinfo->name));
    $top += 4;
    if ($stinfo->idnumber && strlen($stinfo->idnumber) > 0) {
        $pdf->SetXY($left, $top);
        $pdf->Write(1, get_string('idnumber', 'mod_emarking') . ": " . $stinfo->idnumber);
        $top += 4;
    }
    $pdf->SetXY($left, $top);
    $pdf->Write(1, core_text::strtoupper($stinfo->group));
    $top += 4;
    $pdf->SetXY($left, $top);
    $pdf->Write(1, core_text::strtoupper($course->fullname));
    
    $pdf->Image($img, 176, 3, 34); // y antes era -2
    if ($bottomqr) {
        $pdf->Image($imgrotated, 0, $pdf->getPageHeight() - 35, 34);
    }
    unlink($img);
    unlink($imgrotated);
}

/**
 *
 * @param unknown $url            
 * @param unknown $pathname            
 * @return boolean
 */
function emarking_get_file_from_url($url, $pathname)
{
    // Calculate filename
    $parts = explode('/', $url);
    $filename = $parts[count($parts) - 1];
    
    $ispluginfile = false;
    $ispixfile = false;
    $index = 0;
    foreach ($parts as $part) {
        if ($part === 'pluginfile.php') {
            $ispluginfile = true;
            break;
        }
        if ($part === 'pix.php') {
            $ispixfile = true;
            break;
        }
        $index ++;
    }
    
    $fs = get_file_storage();
    
    // If the file is part of Moodle, we get it from the filesystem
    if ($ispluginfile) {
        $contextid = $parts[$index + 1];
        $component = $parts[$index + 2];
        $filearea = $parts[$index + 3];
        $number1 = $parts[$index + 4];
        $number2 = $parts[$index + 5];
        $itemid = $parts[$index + 6];
        $filepath = '/';
        if ($fs->file_exists($contextid, $component, $filearea, $itemid, $filepath, $filename)) {
            $file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);
            $file->copy_content_to($pathname . $filename);
            $imageinfo = getimagesize($pathname . $filename);
            return array(
                $pathname . $filename,
                $imageinfo
            );
        }
        return false;
    }
    
    // Open binary stream and read it
    $handle = fopen($url, "rb");
    $content = stream_get_contents($handle);
    fclose($handle);
    
    // Save the binary file
    $file = fopen($pathname . $filename, "wb+");
    fputs($file, $content);
    fclose($file);
    
    $imageinfo = getimagesize($pathname . $filename);
    return array(
        $pathname . $filename,
        $imageinfo
    );
}

/**
 * Extra los URLs de imagenes desde un string HTML
 * 
 * @param String $html            
 * @return multitype:
 */
function emarking_extract_images_url($html)
{
    $images = array();
    $number = preg_match_all('/<img [^>]*src\s*=\s*"([^"]*)"[^>]*>/', $html, $images);
    return $images;
}

/**
 * Limpia el HTML producido por una pregunta de un quiz
 *
 * @param String $html            
 * @return String
 */
function emarking_clean_question_html($html)
{
    $question = get_string('question', 'mod_quiz');
    $html = preg_replace('/[\n\r]/', '', $html);
    $html = preg_replace('/<div class="state">(.*?)<\/div>/', '', $html);
    $html = preg_replace('/<div class="grade">(.*?)<\/div>/', '', $html);
    $html = preg_replace('/<div class="questionflag">(.*?)<\/div>/', '', $html);
    $html = preg_replace('/<h4 class="accesshide">(.*?)<\/h4>/', '', $html);
    $html = preg_replace('/<input type="hidden"(.*?)>/', '', $html);
    $html = preg_replace('/<input type="radio"(.*?)>/', '<input type="radio">', $html);
    $html = preg_replace('/checked="checked"/', '', $html);
    $html = preg_replace('/alt="[^"]*"/', '', $html);
    $html = preg_replace('/title="[^"]*"/', '', $html);
    $html = preg_replace('/class="texrender"/', '', $html);
    $html = preg_replace('/<script type="math\/tex">(.*?)<\/script>/', '', $html);
    $html = preg_replace('/class\s*=\s*"(.*?)"/', '', $html);
    $html = preg_replace('/style\s*=\s*"(.*?)"/', '', $html);
    $html = preg_replace('/<span\s*>/', '', $html);
    $html = preg_replace('/<\/span>/', '', $html);
    $html = preg_replace('/<label(.*?)>/', '', $html);
    $html = preg_replace('/<\/label>/', '', $html);
    $html = preg_replace('/<a\s+href\s*=\s*"(.*?)">(.*?)<\/a>/', '$2', $html);
    $html = preg_replace('/id\s*=\s*"(.*?)"/', '', $html);
    $html = preg_replace('/<br>/', '', $html);
    $html = preg_replace('/<p(\s*?)>/', '', $html);
    $html = preg_replace('/(<div\s*>)+/', '<div>', $html);
    $html = preg_replace('/(<\/div>)+/', '</div>', $html);
    $html = preg_replace('/<div>Sele(.*?)<\/div>/', '', $html);
    $html = preg_replace('/<div(.*?)><h3(.*?)>'.$question.'\s+(.*?)<\/h3><\/div>/', '<br/><p><b>$3</b></p>', $html);
    $html = preg_replace('/<tbody\s*>/', '', $html);
    $html = preg_replace('/<\/tbody>/', '', $html);
    $html = preg_replace('/<td(.*?)>(.*?)<\/p><\/td>/', '<td style="text-align:center;" align="center">$2</td>', $html);
    $html = preg_replace('/frame="border"/', '', $html);
    $html = preg_replace('/border="\d+"/', 'border="1"', $html);
    $html = preg_replace('/<table(.*?)>/', '<br/><table$1>', $html);
    $html = preg_replace('/<table(.*?)>/', '<br/><table$1>', $html);
    $html = preg_replace('/<div>(<input.*?)<\/div>/', '<br/>$1', $html);
    return $html;
}

/**
 *
 * @param string $answers            
 * @return BubPdf
 */
function emarking_create_omr_answer_sheet($answers = null)
{
    global $CFG;
    
    require_once ($CFG->libdir . '/tcpdf/tcpdf.php'); // for more documentation, see the top of this file
    require_once ($CFG->dirroot . '/mod/emarking/lib/openbub/ans_pdf_open.php'); // for more documentation, see the top of this file
                                                                                 
    // Create a new BubPdf object.
    $BubPdf = new BubPdf('P', 'in', 'LETTER', true);
    $BubPdf->SetPrintHeader(false);
    $BubPdf->SetPrintFooter(false);
    
    // NewExam sets the margins, etc
    BP_NewExam($BubPdf, $CorrectAnswersProvided = TRUE);
    
    BP_StudentAnswerSheetStart($BubPdf);
    
    // A simple 12 question exam
    foreach ($answers as $options) {
        BP_AddAnswerBubbles($BubPdf, 'A', $options, 1, FALSE, FALSE);
    }
    
    BP_StudentAnswerSheetComplete($BubPdf);
    
    // the CreateExam call can be used to retrieve an array of the zone assignments
    $myZones = BP_CreateExam($BubPdf);
    
    return $BubPdf;
}

/**
 *
 * @param unknown $pdf            
 * @param unknown $filedir            
 * @param unknown $stinfo            
 * @param unknown $logofilepath            
 * @param unknown $path            
 * @param unknown $fileimg            
 * @param unknown $course            
 * @param unknown $examname            
 * @param unknown $answers            
 * @param unknown $attemptid            
 */
function emarking_add_answer_sheet($pdf, $filedir, $stinfo, $logofilepath, $path, $fileimg, $course, $examname, $answers, $attemptid)
{
    global $CFG;
    
    require_once ($CFG->dirroot . '/mod/assign/feedback/editpdf/fpdi/fpdi2tcpdf_bridge.php');
    require_once ($CFG->dirroot . '/mod/assign/feedback/editpdf/fpdi/fpdi.php');
    
    if ($answers == null)
        return;
    
    $answerspdffilename = $filedir . '/answer' . random_string(15) . '.pdf';
    $answerspdf = emarking_create_omr_answer_sheet($answers);
    $answerspdf->Output($answerspdffilename, 'F');
    
    $pdf->setSourceFile($answerspdffilename);
    $tplidx = $pdf->ImportPage(1);
    $s = $pdf->getTemplatesize($tplidx);
    $pdf->AddPage('P', array(
        $s['w'],
        $s['h']
    ));
    $pdf->useTemplate($tplidx);
    if ($path) {
        $pdf->setSourceFile($path);
    }
    
    $top = 50;
    $left = 10;
    $width = $s['w'] - 20;
    $height = $s['h'] - 65;
    
    // Corners
    $style = array(
        'width' => 0.25,
        'cap' => 'butt',
        'join' => 'miter',
        'dash' => 0,
        'color' => array(
            0,
            0,
            0
        )
    );
    $pdf->Circle($left, $top, 9, 0, 360, 'F', $style, array(
        0,
        0,
        0
    ));
    $pdf->Circle($left, $top, 4, 0, 360, 'F', $style, array(
        255,
        255,
        255
    ));
    
    $pdf->Circle($left + $width, $top, 9, 0, 360, 'F', $style, array(
        0,
        0,
        0
    ));
    $pdf->Circle($left + $width, $top, 4, 0, 360, 'F', $style, array(
        255,
        255,
        255
    ));
    
    $pdf->Circle($left, $top + $height, 9, 0, 360, 'F', $style, array(
        0,
        0,
        0
    ));
    $pdf->Circle($left, $top + $height, 4, 0, 360, 'F', $style, array(
        255,
        255,
        255
    ));
    
    $pdf->Circle($left + $width, $top + $height, 9, 0, 360, 'F', $style, array(
        0,
        0,
        0
    ));
    $pdf->Circle($left + $width, $top + $height, 4, 0, 360, 'F', $style, array(
        255,
        255,
        255
    ));
    
    emarking_draw_header($pdf, $stinfo, $examname, 1, $fileimg, $logofilepath, $course, null, false, true, $attemptid);
}

/**
 * Lista de todos los estudiantes enrolados en un curso
 *
 * @param int $courseid            
 * @return unknown
 */
function emarking_get_enroled_students($courseid)
{
    global $DB;
    
    $query = emarking_get_enroled_students_sql();
    
    // Se toman los resultados del query dentro de una variable.
    $users = $DB->get_records_sql($query, array(
        $courseid
    ));
    
    return $users;
}

/**
 * SQL que lista todos los estudiantes enrolados en un curso
 *
 * @return String
 */
function emarking_get_enroled_students_sql()
{
    $query = 'SELECT u.*
			FROM {user_enrolments} ue
			JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
			JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
			JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
			JOIN {user} u ON (ue.userid = u.id)
			GROUP BY u.id';
    
    return $query;
}

/**
 * SQL que lista todos los estudiantes enrolados en un curso
 *
 * @return String
 */
function emarking_get_count_enroled_students_sql($courseid = 0)
{
    
    // De pasarse un curso, se filtra por su id
    $sqlcourse = $courseid ? 'e.courseid = ? AND ' : '';
    
    $query = "SELECT cc.*, count(*) as enroledstudents
			FROM {user_enrolments} ue
			JOIN {enrol} e ON (e.id = ue.enrolid)
			JOIN {context} c ON ($sqlcourse c.contextlevel = 50 AND c.instanceid = e.courseid)
			JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
			JOIN {course} cc ON (e.courseid = cc.id)
			GROUP BY cc.id";
    
    return $query;
}

function emarking_finish_user_attempt($attemptid)
{
    global $DB;
    
    $timenow = time();
    $transaction = $DB->start_delegated_transaction();
    $attemptobj = quiz_attempt::create($attemptid);
    $attemptobj->process_finish($timenow, ! false);
    $transaction->allow_commit();
}

function emarking_insert_user_answers($choices, $user, $attemptid)
{
    global $DB;
    
    $timenow = time();
    
    $transaction = $DB->start_delegated_transaction();
    $attemptobj = quiz_attempt::create($attemptid);
    
    $quizobj = $attemptobj->get_quizobj();
    $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
    $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
    
    $answers = array();
    
    $current = 0;
    foreach ($attemptobj->get_slots('all') as $k => $slot) {
        $qa = $attemptobj->get_question_attempt($slot);
        $currentchoice = 0;
        foreach ($qa->get_question()->get_order($qa) as $index => $questionid) {
            $qans = $qa->get_question()->answers[$questionid];
            if ($currentchoice == $choices[$current]) {
                $answers[$slot] = array(
                    'answer' => clean_param($qans->answer, PARAM_NOTAGS)
                );
            }
            $currentchoice ++;
        }
        $current ++;
    }
    
    // Don't log - we will end with a redirect to a page that is logged.
    try {
        $attemptobj->process_submitted_actions($timenow, false, $answers);
    } catch (question_out_of_sequence_exception $e) {
        print_error('submissionoutofsequencefriendlymessage', 'question', $attemptobj->attempt_url(null, $thispage));
    } catch (Exception $e) {
        // This sucks, if we display our own custom error message, there is no way
        // to display the original stack trace.
        $debuginfo = '';
        if (! empty($e->debuginfo)) {
            $debuginfo = $e->debuginfo;
        }
        print_error('errorprocessingresponses', 'question', $attemptobj->attempt_url(null, $thispage), $e->getMessage(), $debuginfo);
    }
    
    // Send the user to the review page.
    $DB->commit_delegated_transaction($transaction);
}

function emarking_add_user_attempt($cm, $user)
{
    global $DB;
    
    // Get the quiz object
    $quizobj = quiz::create($cm->instance, $user->id);
    
    // TODO get to know what usage by activity means
    $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
    $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
    
    // Create the new attempt and initialize the question sessions
    $attemptnumber = 1;
    $lastattempt = null;
    $timenow = time(); // Update time now, in case the server is running really slowly.
    
    $attempt = quiz_create_attempt($quizobj, $attemptnumber, $lastattempt, $timenow, false, $user->id);
    $attempt = quiz_start_new_attempt($quizobj, $quba, $attempt, $attemptnumber, $timenow);
    
    $transaction = $DB->start_delegated_transaction();
    $attempt = quiz_attempt_save_started($quizobj, $quba, $attempt);
    $DB->commit_delegated_transaction($transaction);
}