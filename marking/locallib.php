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
 * @copyright 2012 Jorge Villalon <jorge.villalon@uai.cl>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * @param unknown $emarking
 * @param unknown $submission
 * @param unknown $studentanonymous
 * @param unknown $context
 * @return multitype:stdClass
 */
function emarking_get_all_pages($emarking, $submission, $draft, $studentanonymous, $context, $winwidth, $winheight) {
	global $DB, $CFG, $USER;

	$emarkingpages = array ();

	// Get criteria to filter pages
	$filterpages = false;
	$allowedpages = array ();

	// If user is supervisor, site admin or the student who owns the submission, we should not filter
	if (has_capability ( 'mod/emarking:supervisegrading', $context ) || is_siteadmin () || $USER->id == $submission->student) {
		$filterpages = false;
	} else if (
	// If it is another student (can't grade nor add instances) and peer visibility is allowed, we don't filter
	// but we force it as anonymous
	! has_capability ( 'mod/emarking:grade', $context ) && $emarking->peervisibility) {
		$filterpages = false;
		$studentanonymous = true;
	} else {
		// Remaining case is for markers
		$filterpages = true;

		$allowedpages = emarking_get_allowed_pages ( $emarking );
	}

	// In case there are no pages for this submission, we generate missing pages for those allowed
	if (! $pages = $DB->get_records ( 'emarking_page', array (
			'submission' => $submission->id
	), 'page ASC' )) {
		if ($emarking->totalpages > 0) {
			for($i = 0; $i < $emarking->totalpages; $i ++) {
				$emarkingpage = new stdClass ();
				$emarkingpage->url = $CFG->wwwroot . '/mod/emarking/pix/missing.png';
				$emarkingpage->width = 800;
				$emarkingpage->height = 1035;
				$emarkingpage->totalpages = $emarking->totalpages;
				$emarkingpage->pageno = $i + 1;
				$emarkingpage->comments = array();
				
				if ($filterpages) {
					$emarkingpage->showmarker = array_search ( $i + 1, $allowedpages ) !== false ? 1 : 0;
				} else {
					$emarkingpage->showmarker = 1;
				}

				$emarkingpages [] = $emarkingpage;
			}
		}
		return $emarkingpages;
	}
	
	$fs = get_file_storage ();
	$numfiles = max ( count ( $pages ), $emarking->totalpages );
	$pagecount = 0;

	foreach ( $pages as $page ) {
		$pagecount ++;

		$pagenumber = $page->page;

		while ( count ( $emarkingpages ) < $pagenumber - 1 ) {
			$emarkingpage = new stdClass ();
			$emarkingpage->url = $CFG->wwwroot . '/mod/emarking/pix/missing.png';
			$emarkingpage->width = 800;
			$emarkingpage->height = 1035;
			$emarkingpage->totalpages = $numfiles;
			$emarkingpage->pageno = count ( $emarkingpages ) + 1;
			$emarkingpage->comments = array();
				
			if ($filterpages) {
				$emarkingpage->showmarker = array_search ( count ( $emarkingpages ) + 1, $allowedpages ) !== false ? 1 : 0;
			} else {
				$emarkingpage->showmarker = 1;
			}
				
			$emarkingpages [] = $emarkingpage;
		}

		$fileid = $studentanonymous ? $page->fileanonymous : $page->file;
		if (! $file = $fs->get_file_by_id ( $fileid )) {
			$emarkingpage = new stdClass ();
			$emarkingpage->url = $CFG->wwwroot . '/mod/emarking/pix/missing.png';
			$emarkingpage->width = 800;
			$emarkingpage->height = 1035;
			$emarkingpage->totalpages = $numfiles;
			$emarkingpage->pageno = $pagenumber;
	        $emarkingpage->comments = array(); 
							
			if ($filterpages) {
				$emarkingpage->showmarker = array_search ( $pagenumber, $allowedpages ) !== false ? 1 : 0;
			} else {
				$emarkingpage->showmarker = 1;
			}
				
			$emarkingpages [] = $emarkingpage;
		}

		if ($imageinfo = $file->get_imageinfo ()) {
			$imgurl = file_encode_url ( $CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/mod_emarking/pages/' . $submission->emarking . '/' . $file->get_filename () );
			$emarkingpage = new stdClass ();
			$emarkingpage->url = $imgurl . "?r=" . random_string ( 15 );
			$emarkingpage->width = $imageinfo ['width'];
			$emarkingpage->height = $imageinfo ['height'];
			$emarkingpage->totalpages = $numfiles;
			$emarkingpage->pageno = $pagenumber;
				
			if ($filterpages) {
				$emarkingpage->showmarker = array_search ( $pagenumber, $allowedpages ) !== false ? 1 : 0;
			} else {
				$emarkingpage->showmarker = 1;
			}
				
	        $emarkingpage->comments = emarking_get_comments_page($pagenumber, $draft->id, $winwidth, $winheight); 
	        
			$emarkingpages [] = $emarkingpage;
		}
	}
	
	return $emarkingpages;
}

function emarking_get_comments_page($pageno, $draftid, $winwidth, $winheight) {
    global $DB;
    
    $sqlcomments = "SELECT
		aec.id,
		aec.posx,
		aec.posy,
		aec.rawtext,
		aec.textformat AS format,
		aec.width,
		aec.height,
		aec.colour,
		ep.page AS pageno,
		IFNULL(aec.bonus,0) AS bonus,
		grm.maxscore,
		aec.levelid,
		grl.score AS score,
		grl.definition AS leveldesc,
		IFNULL(aec.criterionid,0) AS criterionid,
		grc.description AS criteriondesc,
		u.id AS markerid,
		CONCAT(u.firstname,' ',u.lastname) AS markername,
		IFNULL(er.id, 0) AS regradeid,
		IFNULL(er.comment, '') AS regradecomment,
		IFNULL(er.motive,0) AS motive,
		IFNULL(er.accepted,0) AS regradeaccepted,
		IFNULL(er.markercomment, '') AS regrademarkercomment,
		IFNULL(er.levelid, 0) AS regradelevelid,
		IFNULL(er.markerid, 0) AS regrademarkerid,
		IFNULL(er.bonus, '') AS regradebonus,
		aec.timecreated
		FROM {emarking_comment} AS aec
		INNER JOIN {emarking_page} AS ep ON (aec.page = ep.id AND ep.page = :pageno AND aec.draft = :draft)
		INNER JOIN {emarking_draft} AS es ON (aec.draft = es.id)
		INNER JOIN {user} AS u ON (aec.markerid = u.id)
		LEFT JOIN {gradingform_rubric_levels} AS grl ON (aec.levelid = grl.id)
		LEFT JOIN {gradingform_rubric_criteria} AS grc ON (grl.criterionid = grc.id)
		LEFT JOIN (
			SELECT grl.criterionid,
			MAX(score) AS maxscore
			FROM {gradingform_rubric_levels} AS grl
			GROUP BY grl.criterionid
		) AS grm ON (grc.id = grm.criterionid)
		LEFT JOIN {emarking_regrade} AS er ON (er.criterion = grc.id AND er.draft = es.id)
        WHERE aec.levelid = 0 OR grl.id is not null
		ORDER BY aec.levelid DESC";
    $params = array('pageno'=>$pageno, 'draft'=>$draftid);
    
    $results = $DB->get_records_sql($sqlcomments, $params);
    
    $results = array_values($results);
    
    return $results;
}
/**
 * Gets a list of the pages allowed to be seen and interact for this user
 *
 * @param unknown $emarking
 * @return array of page numbers
 */
function emarking_get_allowed_pages($emarking) {
	global $DB, $USER;

	$allowedpages = array ();

	// We add page 0 so array_search returns only positive values for normal pages
	$allowedpages [] = 0;

	// If there is criteria assigned for this emarking activity
	if ($criteria = $DB->get_records ( 'emarking_page_criterion', array (
			'emarking' => $emarking->id
	) )) {
		// Organize pages per criterion
		$criteriapages = array ();
		foreach ( $criteria as $cr ) {
			if (! isset ( $criteriapages [$cr->criterion] ))
				$criteriapages [$cr->criterion] = array ();
			$criteriapages [$cr->criterion] [] = $cr->page;
		}
		$filteredbycriteria = true;

		// Get criteria the user is allowed to see
		$usercriteria = $DB->get_records ( 'emarking_marker_criterion', array (
				'emarking' => $emarking->id,
				'marker' => $USER->id
		) );

		// Add pages to allowed array if the user can see them
		foreach ( $usercriteria as $uc ) {
			if (isset ( $criteriapages [$uc->criterion] ))
				$allowedpages = array_merge ( $allowedpages, $criteriapages [$uc->criterion] );
		}
		// If there is no criteria assigned, all pages are allowed
	} else {
		// Get the maximum page number in the emarking activity
		if ($max = $DB->get_record_sql ( '
				SELECT MAX(page) AS pagenumber
				FROM {emarking_submission} AS s
				INNER JOIN {emarking_page} AS p ON (p.submission = s.id AND s.emarking = :emarking)', array (
						'emarking' => $emarking->id
				) )) {
					for($i = 1; $i <= $max->pagenumber; $i ++) {
						$allowedpages [] = $i;
					}
					// If no pages yet, we get the total pages from the activity if it is set
				} else if ($emarking->totalpages > 0) {
					for($i = 1; $i <= $emarking->totalpages; $i ++) {
						$allowedpages [] = $i;
					}
					// Finally we assume there are less than 50 pages
				} else {
					for($i = 1; $i <= 50; $i ++) {
						$allowedpages [] = $i;
					}
				}
	}

	// Sort the array
	asort ( $allowedpages );

	return $allowedpages;
}

/**
 *
 * @param unknown $submission
 */
function emarking_publish_grade($draft) {
	global $CFG, $DB, $USER;

	require_once ($CFG->libdir . '/gradelib.php');

	if(!$submission = $DB->get_record('emarking_submission', array('id'=>$draft->submissionid))) {
		throw new Exception("Invalid submission for draft");
	}
	
	if(!$emarking = $DB->get_record('emarking', array('id'=>$submission->emarking))) {
		throw new Exception("Invalid emarking in submission");
	}

	if($emarking->type != EMARKING_TYPE_NORMAL)
		throw new Exception("Invalid emarking type for publishing");
	    
	
	if ($draft->status <= EMARKING_STATUS_ABSENT)
		throw new Exception("Invalid draft status for publishing");
	    
	// Copy final grade to gradebook
	$grade_item = grade_item::fetch ( array (
			'itemmodule' => 'emarking',
			'iteminstance' => $submission->emarking
	) );

	$feedback = $draft->generalfeedback ? $draft->generalfeedback : '';

	$grade_item->update_final_grade ( $submission->student, $draft->grade, 'editgrade', $feedback, FORMAT_HTML, $USER->id );

	if ($draft->status <= EMARKING_STATUS_PUBLISHED) {
		$draft->status = EMARKING_STATUS_PUBLISHED;
	}

	$draft->timemodified = time();
	$DB->update_record ( 'emarking_draft', $draft );

	$submission->status = $draft->status;
	$submission->timemodified = $draft->timemodified;
	$submission->generalfeedback = $draft->generalfeedback;
	$submission->grade = $draft->grade;
	$submission->teacher = $draft->teacher;
	$DB->update_record ( 'emarking_submission', $submission );
}

/**
 * Exports all grades and scores in an exam in Excel format
 *
 * @param unknown $emarking
 */
function emarking_download_excel($emarking) {
    global $DB;

    $csvsql = "
		SELECT cc.fullname AS course,
			e.name AS exam,
			u.id,
			u.idnumber,
			u.lastname,
			u.firstname,
			cr.description,
			IFNULL(l.score, 0) AS score,
			IFNULL(c.bonus, 0) AS bonus,
			IFNULL(l.score,0) + IFNULL(c.bonus,0) AS totalscore,
			d.grade
		FROM {emarking} AS e
		INNER JOIN {emarking_submission} AS s ON (e.id = :emarkingid AND e.id = s.emarking)
		INNER JOIN {emarking_draft} AS d ON (d.submissionid = s.id AND d.qualitycontrol=0)
        INNER JOIN {course} AS cc ON (cc.id = e.course)
		INNER JOIN {user} AS u ON (s.student = u.id)
		INNER JOIN {emarking_page} AS p ON (p.submission = s.id)
		LEFT JOIN {emarking_comment} AS c ON (c.page = p.id AND d.id = c.draft)
		LEFT JOIN {gradingform_rubric_levels} AS l ON (c.levelid = l.id)
		LEFT JOIN {gradingform_rubric_criteria} AS cr ON (cr.id = l.criterionid)
		ORDER BY cc.fullname ASC, e.name ASC, u.lastname ASC, u.firstname ASC, cr.sortorder";

    // Get data and generate a list of questions
    $rows = $DB->get_recordset_sql ( $csvsql, array (
        'emarkingid' => $emarking->id
    ) );

    $questions = array ();
    foreach ( $rows as $row ) {
        if (array_search ( $row->description, $questions ) === FALSE)
            $questions [] = $row->description;
    }

    $current = 0;
    $laststudent = 0;
    $headers = array (
        '00course' => get_string ( 'course' ),
        '01exam' => get_string ( 'exam', 'mod_emarking' ),
        '02idnumber' => get_string ( 'idnumber' ),
        '03lastname' => get_string ( 'lastname' ),
        '04firstname' => get_string ( 'firstname' )
    );
    $tabledata = array ();
    $data = null;

    $rows = $DB->get_recordset_sql ( $csvsql, array (
        'emarkingid' => $emarking->id
    ) );

    $studentname = '';
    $lastrow = null;
    foreach ( $rows as $row ) {
        $index = 10 + array_search ( $row->description, $questions );
        $keyquestion = $index . "" . $row->description;
        if (! isset ( $headers [$keyquestion] )) {
            $headers [$keyquestion] = $row->description;
        }
        if ($laststudent != $row->id) {
            if ($laststudent > 0) {
                $tabledata [$studentname] = $data;
                $current ++;
            }
            $data = array (
                '00course' => $row->course,
                '01exam' => $row->exam,
                '02idnumber' => $row->idnumber,
                '03lastname' => $row->lastname,
                '04firstname' => $row->firstname,
                $keyquestion => $row->totalscore,
                '99grade' => $row->grade
            );
            $laststudent = intval ( $row->id );
            $studentname = $row->lastname . ',' . $row->firstname;
        } else {
            $data [$keyquestion] = $row->totalscore;
        }
        $lastrow = $row;
    }
    $studentname = $lastrow->lastname . ',' . $lastrow->firstname;
    $tabledata [$studentname] = $data;
    $headers ['99grade'] = get_string ( 'grade' );
    ksort ( $tabledata );

    $current = 0;
    $newtabledata = array ();
    foreach ( $tabledata as $data ) {
        foreach ( $questions as $q ) {
            $index = 10 + array_search ( $q, $questions );
            if (! isset ( $data [$index . "" . $q] )) {
                $data [$index . "" . $q] = '0.000';
            }
        }
        ksort ( $data );
        $current ++;
        $newtabledata [] = $data;
    }

    $tabledata = $newtabledata;

    $downloadfilename = clean_filename ( "$emarking->name.xls" );
    // Creating a workbook
    $workbook = new MoodleExcelWorkbook ( "-" );
    // Sending HTTP headers
    $workbook->send ( $downloadfilename );
    // Adding the worksheet
    $myxls = $workbook->add_worksheet ( get_string ( 'emarking', 'mod_emarking' ) );

    // Writing the headers in the first row
    $row = 0;
    $col = 0;
    foreach ( array_values ( $headers ) as $d ) {
        $myxls->write_string ( $row, $col, $d );
        $col ++;
    }
    // Writing the data
    $row = 1;
    foreach ( $tabledata as $data ) {
        $col = 0;
        foreach ( array_values ( $data ) as $d ) {
            if ($row > 0 && $col >= 5) {
                $myxls->write_number ( $row, $col, $d );
            } else {
                $myxls->write_string ( $row, $col, $d );
            }
            $col ++;
        }
        $row ++;
    }
    $workbook->close ();
}


function emarking_publish_all_grades($emarking) {
	global $DB, $USER, $CFG;
	
	if($emarking->type != EMARKING_TYPE_NORMAL)
		return;

	$studentdrafts = $DB->get_records_sql(
			"SELECT d.* 
			FROM {emarking_draft} as d
			INNER JOIN {emarking_submission} as s ON (d.submissionid = s.id AND s.emarking = :emarking AND d.qualitycontrol = 0)", array (
			'emarking' => $emarking->id
	) );

	foreach ( $studentdrafts as $draft ) {
		if ($draft->status >= EMARKING_STATUS_PUBLISHED)
			emarking_publish_grade ( $draft );
	}

	return true;
}

function emarking_calculate_grades_users($emarking, $userid = 0) {
	global $DB, $USER, $CFG;

	require_once ($CFG->dirroot . '/grade/grading/lib.php');

	if (! $cm = get_coursemodule_from_instance ( 'emarking', $emarking->id )) {
		return;
	}
	
	if($emarking->type != EMARKING_TYPE_NORMAL)
		return;

	$context = context_module::instance ( $cm->id );

	// Get the grading manager, then method and finally controller
	$gradingmanager = get_grading_manager ( $context, 'mod_emarking', 'attempt' );
	$gradingmethod = $gradingmanager->get_active_method ();
	$controller = $gradingmanager->get_controller ( $gradingmethod );
	$range = $controller->get_grade_range ();
	$rubricscores = $controller->get_min_max_score ();
	$totalrubricscore = $rubricscores ['maxscore'];

	$filter = 'WHERE 1=1';
	if ($userid > 0)
		$filter = 'WHERE es.student = ' . $userid;
	$studentscores = $DB->get_records_sql ( "
			SELECT es.id,
			es.student,
	        d.id as draftid,
			sum(ifnull(rl.score,0)) as score,
			sum(ifnull(ec.bonus,0)) as bonus,
			sum(ifnull(rl.score,0)) + sum(ifnull(ec.bonus,0)) as totalscore
			FROM {emarking_submission} AS es
			INNER JOIN {emarking_page} AS ep ON (es.emarking = :emarking AND ep.submission = es.id)
			INNER JOIN {emarking_draft} AS d ON (d.submissionid = es.id AND d.qualitycontrol = 0)
			LEFT JOIN {emarking_comment} AS ec ON (ec.page = ep.id AND ec.levelid > 0 AND ec.draft = d.id)
			LEFT JOIN {gradingform_rubric_levels} AS rl ON (ec.levelid = rl.id)
			$filter
			AND d.status >= 10
	        AND rl.id IS NOT NULL
			GROUP BY es.emarking, es.id", array (
					'emarking' => $emarking->id
			) );

	foreach ( $studentscores as $studentscore ) {
		$totalscore = min ( floatval ( $studentscore->totalscore ), $totalrubricscore );

		$finalgrade = emarking_calculate_grade ( $emarking, $totalscore, $totalrubricscore );

		$submission = $DB->get_record ( 'emarking_submission', array (
				'id' => $studentscore->id
		) );
		$submission->grade = $finalgrade;
		$DB->update_record ( 'emarking_submission', $submission );
		
		$draft = $DB->get_record ( 'emarking_draft', array (
				'id' => $studentscore->draftid
		) );
		$draft->grade = $finalgrade;
		$DB->update_record ( 'emarking_draft', $draft );
	}

	return true;
}

/**
 * Calculates the grade according to score
 * and corrects if there is a slope adjustment
 *
 * @param unknown $emarking
 * @param unknown $totalscore
 * @param unknown $totalrubricscore
 * @return Ambigous <number, mixed>
 */
function emarking_calculate_grade($emarking, $totalscore, $totalrubricscore) {
	if (isset ( $emarking->adjustslope ) && $emarking->adjustslope) {
		$finalgrade = min ( $emarking->grade, ((($emarking->adjustslopegrade - $emarking->grademin) / $emarking->adjustslopescore) * $totalscore) + $emarking->grademin );
	} else {
		$finalgrade = ((($emarking->grade - $emarking->grademin) / $totalrubricscore) * $totalscore) + $emarking->grademin;
	}

	return $finalgrade;
}

/**
 * 
 * @param number $userid
 * @param number $levelid
 * @param string $levelfeedback
 * @param object $submission
 * @param object $draft
 * @param string $emarking
 * @param string $context
 * @param string $generalfeedback
 * @param string $delete
 * @param number $cmid
 * @return multitype:boolean |NULL|multitype:number NULL
 */
function emarking_set_finalgrade(
		$userid = 0, 
		$levelid = 0, 
		$levelfeedback = '', 
		$submission = null, 
		$draft = null,
		$emarking = null, 
		$context = null, 
		$generalfeedback = null, 
		$delete = false, 
		$cmid = 0) {
	global $USER, $DB, $CFG;

	require_once ($CFG->dirroot . '/grade/grading/lib.php');

	// Validate parameters
	if ($userid == 0 || ($levelid == 0 && $cmid == 0) || $draft == null || $submission == null || $context == null) {
		return array (
				false,
				false,
				false
		);
	}

	if ($levelid > 0) {
		// Firstly get the rubric definition id and criterion id from the level
		$rubricinfo = $DB->get_record_sql ( "
				SELECT c.definitionid, l.definition, l.criterionid, l.score, c.description
				FROM {gradingform_rubric_levels} as l
				INNER JOIN {gradingform_rubric_criteria} as c on (l.criterionid = c.id)
				WHERE l.id = ?", array (
						$levelid
				) );
	} elseif ($cmid > 0) {
		// Firstly get the rubric definition id and criterion id from the level
		$rubricinfo = $DB->get_record_sql ( "
				SELECT
				d.id as definitionid
				FROM {course_modules} AS c
				inner join {context} AS mc on (c.id = ? AND c.id = mc.instanceid)
				inner join {grading_areas} AS ar on (mc.id = ar.contextid)
				inner join {grading_definitions} AS d on (ar.id = d.areaid)
				", array (
						$cmid
				) );
	} else {
		return null;
	}

	// Get the grading manager, then method and finally controller
	$gradingmanager = get_grading_manager ( $context, 'mod_emarking', 'attempt' );
	$gradingmethod = $gradingmanager->get_active_method ();
	$controller = $gradingmanager->get_controller ( $gradingmethod );
	$controller->set_grade_range ( array (
			"$emarking->grademin" => $emarking->grademin,
			"$emarking->grade" => $emarking->grade
	), true );
	$definition = $controller->get_definition ();

	// Get the grading instance we should already have
	$gradinginstancerecord = $DB->get_record ( 'grading_instances', array (
			'itemid' => $draft->id,
			'definitionid' => $definition->id
	) );

	// Use the last marking rater id to get the instance
	$raterid = $USER->id;
	$itemid = null;
	if ($gradinginstancerecord) {
		if ($gradinginstancerecord->raterid > 0) {
			$raterid = $gradinginstancerecord->raterid;
		}
		$itemid = $gradinginstancerecord->id;
	}

	// Get or create grading instance (in case submission has not been graded)
	$gradinginstance = $controller->get_or_create_instance ( $itemid, $raterid, $draft->id );

	$rubricscores = $controller->get_min_max_score ();

	// Get the fillings and replace the new one accordingly
	$fillings = $gradinginstance->get_rubric_filling ();

	if ($levelid > 0) {
		if ($delete) {
			if (! $minlevel = $DB->get_record_sql ( '
					SELECT id, score
					FROM {gradingform_rubric_levels}
					WHERE criterionid = ?
					ORDER BY score ASC LIMIT 1', array (
							$rubricinfo->criterionid
					) )) {
						return array (
								false,
								false,
								false
						);
					}
					$newfilling = array (
							"remark" => '',
							"levelid" => $minlevel->id
					);
		} else {
			$newfilling = array (
					"remark" => $levelfeedback,
					"levelid" => $levelid
			);
		}
		if (isset ( $fillings ['criteria'] [$rubricinfo->criterionid] ['levelid'] ) && isset ( $fillings ['criteria'] [$rubricinfo->criterionid] ['remark'] )) {
			$previouslvlid = $fillings ['criteria'] [$rubricinfo->criterionid] ['levelid'];
			$previouscomment = $fillings ['criteria'] [$rubricinfo->criterionid] ['remark'];
		} else {
			$previouslvlid = 0;
			$previouscomment = null;
		}
		$fillings ['criteria'] [$rubricinfo->criterionid] = $newfilling;
	} else {
		$previouslvlid = 0;
		$previouscomment = null;
	}

	$fillings ['raterid'] = $raterid;
	$gradinginstance->update ( $fillings );
	$rawgrade = $gradinginstance->get_grade ();

	$previousfeedback = '';
	$previousfeedback = $draft->generalfeedback == null ? '' : $draft->generalfeedback;

	if ($generalfeedback == null) {
		$generalfeedback = $previousfeedback;
	}

	$totalscore = emarking_get_totalscore ( $draft, $controller, $fillings );
	$finalgrade = emarking_calculate_grade ( $emarking, $totalscore, $rubricscores ['maxscore'] );

	$pendingregrades = $DB->count_records('emarking_regrade', array('draft'=>$draft->id, 'accepted'=>0));
	
	// Calculate grade for draft
	$draft->grade = $finalgrade;
	$draft->generalfeedback = $generalfeedback;
	$draft->status = $pendingregrades == 0 ? EMARKING_STATUS_GRADING : EMARKING_STATUS_REGRADING;
	$draft->timemodified = time ();

	$DB->update_record ( 'emarking_draft', $draft );
	
	// Adds an entry in the grades history
	$grade_history = new stdClass();
	$grade_history->draftid = $draft->id;
	$grade_history->grade = $finalgrade;
	$grade_history->score = $totalscore;
	$grade_history->bonus = 0;
	$grade_history->marker = $USER->id;
	$grade_history->timecreated = time();
	$grade_history->timemodified = time();
	
	$DB->insert_record('emarking_grade_history', $grade_history);
	
	// Aggregate grade for submission
	$drafts = $DB->get_records ( "emarking_draft", array (
			"emarkingid" => $submission->emarking,
			"submissionid" => $submission->id
	) );
	
	$submission->generalfeedback = '';
	$submission->grade = 0;
	foreach($drafts as $d) {
		$submission->generalfeedback .= $d->generalfeedback;
		$submission->grade += $d->grade;
	}
	$submission->grade = $submission->grade / count($drafts);
	$submission->timemodified = time ();

	$DB->update_record ( 'emarking_submission', $submission );
	
	return array (
			$finalgrade,
			$previouslvlid,
			$previouscomment
	);
}
/**
 * Creates an especial array with the navigation tabs for emarking markers training mode
 *
 * @param unknown $context
 *            The course context to validate capabilit
 * @param unknown $cm
 *            The course module (emarking activity)
 * @return multitype:tabobject
 */
function emarking_tabs_markers_training($context, $cm, $emarking,$generalprogress,$delphiprogress){

	global $CFG;
	global $USER;
	global $OUTPUT;


	//tab's icons
	$timeicon = $OUTPUT->pix_icon('i/scheduled', null);
	$scalesicon = $OUTPUT->pix_icon('i/scales', null);

	//array for tabs data
	$tabs = array();

	$firststagetable = new html_table();
	
	$firststagetable->data[] = array(
	    get_string('stage', 'mod_emarking'),
		$timeicon . " " . get_string('marking_deadline', 'mod_emarking'),
	    $scalesicon . " " . get_string('stage_general_progress', 'mod_emarking'));
	
	if($generalprogress >= 100) {
	   $firststagetable->data[] = array(
	      get_string('delphi_stage_one', 'mod_emarking'),
		  "&nbsp;",
		  $OUTPUT->pix_icon('i/grade_correct', ""));
	} else {
	    $firststagetable->data[] = array(
	        get_string('delphi_stage_one', 'mod_emarking'),
	        emarking_time_difference($emarking->firststagedate, time(), false),
	        emarking_create_progress_graph($generalprogress));
	}
	
	$firststagetable->data[] = array(
	    get_string('delphi_stage_two', 'mod_emarking'),
		emarking_time_difference($emarking->secondstagedate, time(), false),
		emarking_create_progress_graph($delphiprogress));

	return html_writer::table($firststagetable);

}

/**
 * Creates progreph graph of delphi or marking in tabs
 *
 * @param unknown $progress
 *            Marking progress or delphi's progress
 * @return string
 */
function emarking_create_progress_graph($progress){

	$width="width:$progress%; height: 20px; line-height: 20px; border-radius: 3px 0px 0px 3px;";
	$strong=html_writer::span($progress."%",'bar',array("style"=>$width));
	$graph= html_writer::div($strong,'graph',array("style"=>"border-radius:3px;"));
	$graphcont= html_writer::div($graph,'graphcont');
	$rating= html_writer::div($graphcont,'rating');

	return $rating;

}
/**
 * Countdown to especific deadline
 *
 * @param unknown $deadline
 *            Deadline in unixtime
 * @return string
 */
function emarking_stage_countdown($deadline){
	$remaining=$deadline - time();
	if($remaining < 0)
		$remaining=0;

	$days_remaining = floor($remaining / 86400);
	$hours_remaining = floor(($remaining % 86400) / 3600);
	$minutes_remaining = floor((($remaining % 86400) % 3600)/ 60);

	$countdown=$days_remaining." ".get_string('days')." ".$hours_remaining." ".get_string('hours')." ".$minutes_remaining." ".get_string('minutes');

	return $countdown;
}

