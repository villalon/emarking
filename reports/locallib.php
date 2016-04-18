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
 * @param unknown $divid
 * @param array $labels
 * @param array $data
 * @param unknown $title
 * @param string $xtitle
 * @param string $ytitle
 * @return multitype:string
 */
function emarking_get_google_chart($divid, array $labels, array $data, $title, $xtitle = null, $ytitle = null) {
    // DIV for displaying.
    $html = '<div id="' . $divid . '" style="width: 100%; height: 500px;"></div>';
    // Headers.
    $labelsjs = "['" . implode("', '", $labels) . "']";
    // Data JS.
    $datajs = "";
    for ($i = 0; $i < count($data); $i ++) {
        $datajs .= "[";
        for ($j = 0; $j < count($data [$i]); $j ++) {
            $datacell = $data [$i] [$j];
            if ($j == 0) {
                $datacell = "'" . $datacell . "'";
            }
            if ($j < count($data [$i]) - 1) {
                $datacell = $datacell . ",";
            }
            $datajs .= $datacell;
        }
        $datajs .= "],";
    }
    // The required JS to display the chart.
    $js = "
        google.setOnLoadCallback(drawChart$divid);
        // Chart function for $divid.
        function drawChart$divid() {
        var data = google.visualization.arrayToDataTable([
            $labelsjs,
            $datajs
            ]);
        var options = {
                        animation: {duration: 500},
                        title: '$title',
                        hAxis: {title: '$xtitle', titleTextStyle: {color: 'black'}, format:'#'},
                        vAxis: {title: '$ytitle', titleTextStyle: {color: 'black'}, format:'#'},
                        legend: 'top',
                        vAxes: {
                                0: {
                                    gridlines: {color: '#ddd'},
                                    format:'#'
                                   },
                                1: {
                                    gridlines: {color: '#ddd'},
                                    format:'#'
                                   },
                                },
                       series: {
                                0:{targetAxisIndex:0},
                                1:{targetAxisIndex:1},
                                2:{targetAxisIndex:1},
}
                      };
        var chart = new google.visualization.LineChart(document.getElementById('$divid'));
        chart.draw(data, options);
       }";
    return array(
        $html,
        $js);
}
/**
 * Navigation tabs for reports
 *
 * @param unknown $category
 *            The category object
 * @return multitype:tabobject array of tabobjects
 */
function emarking_reports_tabs($category) {
    $tabs = array();
    // Statistics.
    $statstab = new tabobject("statistics",
            new moodle_url("/mod/emarking/reports/print.php", array(
                "category" => $category->id)), get_string("statistics", 'mod_emarking'));
    // Print statistics.
    $statstab->subtree [] = new tabobject("printstatistics",
            new moodle_url("/mod/emarking/reports/print.php", array(
                "category" => $category->id)), get_string("statistics", 'mod_emarking'));
    // Print statistics.
    $statstab->subtree [] = new tabobject("printdetails",
            new moodle_url("/mod/emarking/reports/printdetails.php", array(
                "category" => $category->id)), get_string("printdetails", 'mod_emarking'));
    $tabs [] = $statstab;
    return $tabs;
}
function emarking_costconfig_tabs($category) {
    $tabs = array();
    // Print orders.
    $tabs [] = new tabobject(get_string("costconfigtab", 'mod_emarking'),
            new moodle_url("/mod/emarking/reports/costconfig.php", array(
                "category" => $category->id)), get_string("costconfigtab", 'mod_emarking'));
    // Print orders history.
    $tabs [] = new tabobject(get_string("costcategorytable", 'mod_emarking'),
            new moodle_url("/mod/emarking/reports/categorycosttable.php", array(
                "category" => $category->id)), get_string("costcategorytable", 'mod_emarking'));
    return $tabs;
}
function emarking_buttons_creator($string, $id = null, $class = null) {
	$button = html_writer::tag('button', $string,
			array(
					'id' => $id,
					'class' => $class));
	return $button;
	}
function emarking_get_subcategories($category){
	global $DB;
	$arraysubcategory = array();
	$subcategoryquery = "SELECT * FROM {course_categories} WHERE ".$DB->sql_like('path', ':path');
	if($subcategories = $DB->get_records_sql($subcategoryquery, array( "path" => "%/$category/%"))){
		foreach ($subcategories as $subcategory) {
			$arraysubcategory [$subcategory->id] = $subcategory->name;
		}
	}	
	return $arraysubcategory;
}
function emarking_get_category_cost_table_data($category) {
    global $DB, $OUTPUT;
    // Gets the information of the above query.
    $sqlactivities = "
            SELECT cc.id as id,
            cc.name as name,
            ecc.printingcost as printingcost,
            ecc.costcenter as costcenter
			FROM {course_categories} cc
			LEFT JOIN {emarking_category_cost} ecc ON (ecc.category = cc.id)";
    if ($categorycost = $DB->get_records_sql($sqlactivities)) {
        $arraycategorycost = array();
        foreach ($categorycost as $categorycostdata) {
            $arraycategorycost [$categorycostdata->id] [0] = $categorycostdata->name;
            if (! $categorycostdata->costcenter == null) {
                $arraycategorycost [$categorycostdata->id] [1] = $categorycostdata->printingcost;
                $arraycategorycost [$categorycostdata->id] [2] = $categorycostdata->costcenter;
            } else {
                $arraycategorycost [$categorycostdata->id] [1] = 'NULL';
                $arraycategorycost [$categorycostdata->id] [2] = 'NULL';
            }
            $editicontable = new pix_icon("t/editstring", "edit");
            $arraycategorycost [$categorycostdata->id] [3] = $OUTPUT->action_icon(
                    new moodle_url("/mod/emarking/reports/costconfig.php",
                            array(
                                "category" => $categorycostdata->id)), $editicontable);
        }
    }
    return $arraycategorycost;
}
function emarking_get_activities($category) {
    global $DB;
    $activitiesparams = array(
        "%/$category/%",
        $category);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlactivities = "SELECT count(e.id) AS activities
							   FROM {emarking} e
   							   INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
							   INNER JOIN {course} c ON (c.id = eexam.course)
							   INNER JOIN {course_categories} cc ON (cc.id = c.category)
						       WHERE (cc.path like ? OR cc.id = ?)";
    // Gets the information of the above query.
    if ($activities = $DB->get_records_sql($sqlactivities, $activitiesparams)) {
        foreach ($activities as $activity) {
            $totalactivity = $activity->activities;
        }
    } else {
        $totalactivity = 0;
    }
    return $totalactivity;
}
function emarking_get_teacher_ranking($category, $limit = null) {
    global $DB;
    $teacherrankingparams = array(
        "%/$category/%",
        $category);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlteacherranking = "
            SELECT u.id AS id,
            u.firstname AS firstname,
            u.lastname AS lastname,
            count(e.id) AS activities
			FROM {emarking} e
  			INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
			INNER JOIN {user} u ON (u.id = eexam.requestedby)
			INNER JOIN {course} c ON (c.id = eexam.course)
			INNER JOIN {course_categories} cc ON (cc.id = c.category)
			WHERE (cc.path like ? OR cc.id = ?)
    		GROUP BY id
   			ORDER BY activities DESC
			";
    if (! $limit == null) {
        $sqlteacherranking = $sqlteacherranking . "LIMIT $limit";
    }
    // Gets the information of the above query.
    $arrayteacherranking = array();
    if ($teacherranking = $DB->get_records_sql($sqlteacherranking, $teacherrankingparams)) {
        foreach ($teacherranking as $teachersrankings) {
            $arrayteacherranking [$teachersrankings->id] [] = $teachersrankings->firstname . " " . $teachersrankings->lastname;
            $arrayteacherranking [$teachersrankings->id] [] = $teachersrankings->activities;
        }
    }
    return $arrayteacherranking;
}
function emarking_get_original_pages($category) {
    global $DB;
    $originalpagesparams = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    // Sql that counts all the resourses since the last time the app was used.
    $sqloriginalpages = "SELECT AVG((eexam.totalpages+eexam.extrasheets)) AS pages
							   FROM {emarking} e
   							   INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                               INNER JOIN {course} c ON (c.id = eexam.course)
                               INNER JOIN {course_categories} cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)";
    // Gets the information of the above query.
    if ($originalpages = $DB->get_records_sql($sqloriginalpages, $originalpagesparams)) {
        foreach ($originalpages as $pages) {
            if (! $pages->pages == null) {
                $totaloriginalpages = round((int) $pages->pages);
            } else {
                $totaloriginalpages = 0;
            }
        }
    }
    return $totaloriginalpages;
}
function emarking_get_total_pages_by_course($category, $limit = null) {
    global $DB;
    $totalpagesbycourseparams = array(
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT,
        "%/$category/%",
        $category);
    // Sql that counts all the resourses since the last time the app was used.
    $sqltotalpagesbycourse = "
            SELECT courseid,
            coursename,
            SUM(pages) AS totalpages
            FROM (
                SELECT c.id AS courseid,
                c.fullname AS coursename,
                eexam.id AS examid,
                ((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE eexam.status IN (?,?) AND (cc.path like ? OR cc.id = ?)
                GROUP BY eexam.id
                ORDER BY pages DESC) AS pagestotal
            GROUP BY courseid
            ORDER BY totalpages DESC
			";
    if (! $limit == null) {
        $sqltotalpagesbycourse = $sqltotalpagesbycourse . "LIMIT $limit";
    }
    // Gets the information of the above query.
    $arraytotalpages = array();
    if ($totalpagesbycourse = $DB->get_records_sql($sqltotalpagesbycourse, $totalpagesbycourseparams)) {
        foreach ($totalpagesbycourse as $pagesbycourse) {
            if (! $pagesbycourse->totalpages == null) {
                $arraytotalpages [$pagesbycourse->courseid] [] = $pagesbycourse->coursename;
                $arraytotalpages [$pagesbycourse->courseid] [] = $pagesbycourse->totalpages;
            }
        }
    }
    return $arraytotalpages;
}
function emarking_get_total_pages($category) {
    global $DB;
    $pageparams = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlpage = "SELECT SUM(pages) AS totalpages
            FROM (SELECT c.id AS courseid,
                    eexam.id AS examid,
                    (eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams) AS pages
					FROM {emarking} e
   					INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                    INNER JOIN {course} c ON (c.id = eexam.course)
                    INNER JOIN {course_categories} cc ON (cc.id = c.category)
					WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
                    GROUP BY eexam.id
                    ORDER BY pages DESC) AS pagestotal";
    // Gets the information of the above query.
    if ($page = $DB->get_records_sql($sqlpage, $pageparams)) {
        foreach ($page as $pages) {
            if (! $pages->totalpages == null) {
                $totalpages = $pages->totalpages;
            } else {
                $totalpages = 0;
            }
        }
    }
    return $totalpages;
}
function emarking_get_emarking_courses($category) {
    global $DB;
    $emarkingcoursesparams = array(
        "%/$category/%",
        $category);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlemarkingcourses = "
            SELECT COUNT(course) AS courses
            FROM(
                SELECT e.course AS course
				FROM {emarking} e
				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = e.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?)
                GROUP BY e.course) courses";
    // Gets the information of the above query.
    if ($emarkingcourses = $DB->get_records_sql($sqlemarkingcourses, $emarkingcoursesparams)) {
        foreach ($emarkingcourses as $courses) {
            $totalemarkingcourses = $courses->courses;
        }
    }
    return $totalemarkingcourses;
}
function emarking_get_students($category) {
    global $DB;
    $studentsparams = array(
        '5',
        "%/$category/%",
        $category);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlstudents = "SELECT count(u.id) AS user
					FROM {user} u
					INNER JOIN {role_assignments} ra ON (ra.userid = u.id)
					INNER JOIN {context} ct ON (ct.id = ra.contextid)
					INNER JOIN {course} c ON (c.id = ct.instanceid)
					INNER JOIN {role} r ON (r.id = ra.roleid)
					INNER JOIN {course_categories} cc ON (cc.id = c.category)
					WHERE ra.roleid=? AND (cc.path like ? OR cc.id = ?)
	";
    // Gets the information of the above query.
    if ($students = $DB->get_records_sql($sqlstudents, $studentsparams)) {
        $arraystudents = array();
        foreach ($students as $student) {
            $arraystudents [] = $student->user;
        }
    }
    return $arraystudents;
}
function emarking_get_total_pages_to_print($category) {
    global $DB;
    $totalpagestoprintparams = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_UPLOADED);
    // Sql that counts all the resourses since the last time the app was used.
    $sqltotalpagestoprint = "
            SELECT SUM(pages) AS totalpages
            FROM (
                SELECT c.id AS courseid,
                eexam.id AS examid,
                (eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?) AND eexam.status = ?
                GROUP BY eexam.id
                ORDER BY pages DESC
				) AS pagestotal
            ORDER BY totalpages DESC";
    // Gets the information of the above query.
    if ($totalpagestoprint = $DB->get_records_sql($sqltotalpagestoprint, $totalpagestoprintparams)) {
        $arraypagestoprint = array();
        foreach ($totalpagestoprint as $pagestoprint) {
            $arraypagestoprint [] = $pagestoprint->totalpages;
        }
    }
    return $arraypagestoprint;
}
function emarking_get_activities_by_date($category, $isyear) {
    global $DB;
    $activitiesbydateparams = array(
        "%/$category/%",
        $category);
    if($isyear == 1){
    	// Sql that counts all the resourses since the last time the app was used.
    	$sqlactivitiesbydate = "
            	SELECT idexam,
            	COUNT(id) AS activities,
            	printyear,
    			COUNT(printyear) as isyears
            	FROM (
					SELECT eexam.id AS idexam,
                	e.id AS id,
                	YEAR(FROM_UNIXTIME(eexam.printdate)) AS printyear
					FROM {emarking} e
   					INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
					INNER JOIN {course} c ON (c.id = eexam.course)
					INNER JOIN {course_categories} cc ON (cc.id = c.category)
					WHERE (cc.path like ? OR cc.id = ?)
					) AS months
            	GROUP BY printyear
            	ORDER BY printyear ASC";
    	// Gets the information of the above query.
    	$arrayactivitiesbydate = array();
    	if ($activitiesbydate = $DB->get_records_sql($sqlactivitiesbydate, $activitiesbydateparams)) {
        	$arrayactivitiesbydate [0] = [
            	get_string('year', 'mod_emarking'),
            	get_string('activities', 'mod_emarking')];
        	$yearcount=1;
        	foreach ($activitiesbydate as $activitys) {
            	$arrayactivitiesbydate [$yearcount] [0] = $activitys->printyear;
            	$arrayactivitiesbydate [$yearcount] [1] = (int) $activitys->activities;
            	$yearcount++;
        	}
    	}
    }
    elseif($isyear == 0){
    	$sqlactivitiesbydate = "
            SELECT idexam,
            COUNT(id) AS activities,
            printmonth
            FROM (
				SELECT eexam.id AS idexam,
                e.id AS id,
                MONTH(FROM_UNIXTIME(eexam.printdate)) AS printmonth
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
				INNER JOIN {course} c ON (c.id = eexam.course)
				INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?)
				) AS months
            GROUP BY printmonth
            ORDER BY printmonth ASC";
    	// Gets the information of the above query.
    	$arrayactivitiesbydate = array();
    	if ($activitiesbydate = $DB->get_records_sql($sqlactivitiesbydate, $activitiesbydateparams)) {
    		$arrayactivitiesbydate [0] = [
    			get_string('month', 'mod_emarking'),
    			get_string('activities', 'mod_emarking')];
    		for ($contadormes = 1; $contadormes <= 12; $contadormes ++) {
    			if (! isset($arrayactivitiesbydate [$contadormes])) {
    				$arrayactivitiesbydate [$contadormes] = [
    					date("F", mktime(0, 0, 0, $contadormes, 10)),
    					0];
    			}
    		}
    		foreach ($activitiesbydate as $activitys) {
    			$arrayactivitiesbydate [$activitys->printmonth] [0] = date("F", mktime(0, 0, 0, $activitys->printmonth, 10));
    			$arrayactivitiesbydate [$activitys->printmonth] [1] = (int) $activitys->activities;
    		}
    	}	
   	}else {
    $arrayactivitiesbydate = [
            [
                'nodata',
                'nodata'],
            [
                0,
                0],
            [
                0,
                0]];
    }
    return $arrayactivitiesbydate;
}
function emarking_get_emarking_courses_by_date($category, $isyear) {
    global $DB;
    $emarkingcoursesbydateparams = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    if($isyear == 1){
    // Sql that counts all the resourses since the last time the app was used.
    $sqlemarkingcoursesbydate = "
            SELECT printyear,
    		COUNT(printyear) AS isyears,
            COUNT(course) AS coursecount
            FROM (
                SELECT e.course AS course,
                YEAR(FROM_UNIXTIME(MIN(eexam.printdate))) as printyear
                FROM {emarking} e
				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = e.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
                GROUP BY course
                ORDER BY printyear DESC) m
            GROUP BY printyear";
    // Gets the information of the above query.
    if ($emarkingcoursesbydate = $DB->get_records_sql($sqlemarkingcoursesbydate, $emarkingcoursesbydateparams)) {
        $arrayemarkingcoursesbydate = array();
        $arrayemarkingcoursesbydate [0] = [
            get_string('year', 'mod_emarking'),
            get_string('emarkingcourses', 'mod_emarking')];
        $yearcount=1;
        foreach ($emarkingcoursesbydate as $coursesbydate) {
        		$arrayemarkingcoursesbydate [$yearcount] [0] = $coursesbydate->printyear;
        		$arrayemarkingcoursesbydate [$yearcount] [1] = (int) $coursesbydate->coursecount;
        		$yearcount++;
        }
    }
    } elseif($isyear == 0){
      	  $sqlemarkingcoursesbydate = "
    	        SELECT printmonth,
    	        COUNT(course) AS coursecount
    	        FROM (
    	            SELECT e.course AS course,
    	            MONTH(FROM_UNIXTIME(MIN(eexam.printdate))) as printmonth
    	            FROM {emarking} e
					INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
            	    INNER JOIN {course} c ON (c.id = e.course)
     	            INNER JOIN {course_categories} cc ON (cc.id = c.category)
					WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
            	    GROUP BY course
            	    ORDER BY printmonth DESC) m
           		GROUP BY printmonth";
        	// Gets the information of the above query.
        	if ($emarkingcoursesbydate = $DB->get_records_sql($sqlemarkingcoursesbydate, $emarkingcoursesbydateparams)) {
        		$arrayemarkingcoursesbydate = array();
        		$arrayemarkingcoursesbydate [0] = [
        			get_string('month', 'mod_emarking'),
        			get_string('emarkingcourses', 'mod_emarking')];
        		for ($contadormes = 1; $contadormes <= 12; $contadormes ++) {
        			if (! isset($arrayemarkingcoursesbydate [$contadormes])) {
        				$arrayemarkingcoursesbydate [$contadormes] = [
        						date("F", mktime(0, 0, 0, $contadormes, 10)),
        						0];
        			}
        		}
        		foreach ($emarkingcoursesbydate as $coursesbydate) {
        			$arrayemarkingcoursesbydate [$coursesbydate->printmonth] [0] = date("F", mktime(0, 0, 0, $coursesbydate->printmonth, 10));
        			$arrayemarkingcoursesbydate [$coursesbydate->printmonth] [1] = (int) $coursesbydate->coursecount;
        		}
    		}
    	} else {
        $arrayemarkingcoursesbydate = [
            [
                'nodata',
                'nodata'],
            [
                0,
                0],
            [
                0,
                0]];
    }
    return $arrayemarkingcoursesbydate;
}
function emarking_get_original_pages_by_date($category, $isyear) {
    global $DB;
    $totaloriginalpagesbydateparams = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    if($isyear == 1){
    // Sql that counts all the resourses since the last time the app was used.
    $sqloriginalpagesbydate = "
            SELECT printyear,
    		COUNT(printyear) AS isyears,
            AVG(pages) AS avgpages
            FROM (
                SELECT eexam.id as id,
                YEAR(FROM_UNIXTIME(eexam.printdate)) as printyear,
                (eexam.totalpages+eexam.extrasheets) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
				GROUP BY id ) as pages
           GROUP BY printyear";
    // Gets the information of the above query.
    if ($originalpagesbydate = $DB->get_records_sql($sqloriginalpagesbydate, $totaloriginalpagesbydateparams)) {
        $arrayoriginalpagesbydate = array();
        $arrayoriginalpagesbydate [0] = [
            get_string('year', 'mod_emarking'),
            get_string('meanexamleanght', 'mod_emarking')];
        $yearcount=1;
        foreach ($originalpagesbydate as $pagesbydate) {

        		$arrayoriginalpagesbydate [$yearcount] [0] = $pagesbydate->printyear;
        		$arrayoriginalpagesbydate [$yearcount] [1] = (int) $pagesbydate->avgpages;
        		$yearcount++;
        }
    }
    }elseif($isyear == 0){
        	$sqloriginalpagesbydate = "
            SELECT printmonth,
            AVG(pages) AS avgpages
            FROM (
                SELECT eexam.id as id,
                MONTH(FROM_UNIXTIME(eexam.printdate)) as printmonth,
                (eexam.totalpages+eexam.extrasheets) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
				GROUP BY id ) as pages
           GROUP BY printmonth";
        	// Gets the information of the above query.
        	if ($originalpagesbydate = $DB->get_records_sql($sqloriginalpagesbydate, $totaloriginalpagesbydateparams)) {
        		$arrayoriginalpagesbydate = array();
        		$arrayoriginalpagesbydate [0] = [
        				get_string('month', 'mod_emarking'),
        				get_string('meanexamleanght', 'mod_emarking')];
        		for ($contadormes = 1; $contadormes <= 12; $contadormes ++) {
        			if (! isset($arrayoriginalpagesbydate [$contadormes])) {
        				$arrayoriginalpagesbydate [$contadormes] = [
        						date("F", mktime(0, 0, 0, $contadormes, 10)),
        						0];
        			}
        		}
        		foreach ($originalpagesbydate as $pagesbydate) {
        			$arrayoriginalpagesbydate [$pagesbydate->printmonth] [0] = date("F", mktime(0, 0, 0, $pagesbydate->printmonth, 10));
        			$arrayoriginalpagesbydate [$pagesbydate->printmonth] [1] = round((int) $pagesbydate->avgpages);
        		}
        	}
   		}else {
        $arrayoriginalpagesbydate = [
            [
                'nodata',
                'nodata'],
            [
                0,
                0],
            [
                0,
                0]];
    }
    return $arrayoriginalpagesbydate;
}
function emarking_get_total_pages_by_date($category, $isyear) {
    global $DB;
    $totalpagesbydateparams = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    if($isyear == 1){
    // Sql that counts all the resourses since the last time the app was used.
    $sqltotalpagesbydate = "
            SELECT printyear,
    		COUNT(printyear) AS isyears,
            SUM(pages) AS totalpages
            FROM (
                SELECT c.id AS courseid,
                eexam.id AS examid,
                YEAR(FROM_UNIXTIME(eexam.printdate)) as printyear,
                (eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams) AS pages
			    FROM {emarking} e
   			    INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
			    WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
                GROUP BY eexam.id
                ORDER BY pages DESC) AS pagestotal
            GROUP BY printyear";
    // Gets the information of the above query.
    if ($totalpagesbydate = $DB->get_records_sql($sqltotalpagesbydate, $totalpagesbydateparams)) {
        $arraytotalpagesbydate = array();
        $arraytotalpagesbydate [0] = [
            get_string('year', 'mod_emarking'),
            get_string('totalprintedpages', 'mod_emarking')];
        $yearcount=1;
        foreach ($totalpagesbydate as $pagesbydate) {
        		$arraytotalpagesbydate [$yearcount] [0] = $pagesbydate->printyear;
        		$arraytotalpagesbydate [$yearcount] [1] = (int) $pagesbydate->totalpages;
        		$yearcount++;
        	}
        }
    }elseif($isyear == 0){
        	$sqltotalpagesbydate = "
            SELECT printdate,
            SUM(pages) AS totalpages
            FROM (
                SELECT c.id AS courseid,
                eexam.id AS examid,
                MONTH(FROM_UNIXTIME(eexam.printdate)) as printdate,
                (eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams) AS pages
			    FROM {emarking} e
   			    INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
			    WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
                GROUP BY eexam.id
                ORDER BY pages DESC) AS pagestotal
            GROUP BY printdate";
        	// Gets the information of the above query.
        	if ($totalpagesbydate = $DB->get_records_sql($sqltotalpagesbydate, $totalpagesbydateparams)) {
        		$arraytotalpagesbydate = array();
        		$arraytotalpagesbydate [0] = [
        				get_string('month', 'mod_emarking'),
        				get_string('totalprintedpages', 'mod_emarking')];
        		for ($contadormes = 1; $contadormes <= 12; $contadormes ++) {
        			if (! isset($arraytotalpagesbydate [$contadormes])) {
        				$arraytotalpagesbydate [$contadormes] = [
        						date("F", mktime(0, 0, 0, $contadormes, 10)),
        						0];
        			}
        		}
        		foreach ($totalpagesbydate as $pagesbydate) {
        			$arraytotalpagesbydate [$pagesbydate->printdate] [0] = date("F", mktime(0, 0, 0, $pagesbydate->printdate, 10));
        			$arraytotalpagesbydate [$pagesbydate->printdate] [1] = (int) $pagesbydate->totalpages;
        		}
        	}	
        }
    return $arraytotalpagesbydate;
}
function emarking_get_total_pages_for_table($category) {
    global $DB, $CFG;
    $printingcost = emarking_get_printing_cost($category);
    $totalpagesbydateparams = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    // Sql that counts all the resourses since the last time the app was used.
    $sqltotalpagesbydate = "
            SELECT printdate,
            SUM(pages) AS totalcost
            FROM (
                SELECT c.id AS courseid,
                eexam.id AS examid,
                MONTH(FROM_UNIXTIME(eexam.printdate)) as printdate,
                eexam.printingcost*((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
                GROUP BY eexam.id
                ORDER BY pages DESC) AS pagestotal
            GROUP BY printdate";
    // Gets the information of the above query.
    if ($totalpagesbydate = $DB->get_records_sql($sqltotalpagesbydate, $totalpagesbydateparams)) {
        $arraytotalpagesbydate = array();
        for ($contadormes = 1; $contadormes <= 12; $contadormes ++) {
            if (! isset($arraytotalpagesbydate [$contadormes])) {
                $arraytotalpagesbydate [$contadormes] = [
                    date("F", mktime(0, 0, 0, $contadormes, 10)),
                    '$' . " " . "0"];
            }
        }
        foreach ($totalpagesbydate as $costbydate) {
            $arraytotalpagesbydate [$costbydate->printdate] [0] = date("F", mktime(0, 0, 0, $costbydate->printdate, 10));
            $arraytotalpagesbydate [$costbydate->printdate] [1] = '$' . " " . number_format((int) $costbydate->totalcost);
        }
    } else {
        $arraytotalpagesbydate = [
            '0'];
    }
    return $arraytotalpagesbydate;
}
function emarking_get_total_cost_piechart($category) {
    global $DB;
    $totalcostpiechartparams = array(
        "%/$category/%",
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    // Sql that counts all the resourses since the last time the app was used.
    $sqltotalcostpiechart = "
            SELECT categoryid,
            categoryname,
            SUM(pages) AS totalpages
            FROM (
                SELECT c.id AS courseid,
                eexam.id AS examid,
                cc.id as categoryid,
                cc.name as categoryname,
                eexam.printingcost*((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE cc.path like ? AND eexam.status IN (?,?)
                GROUP BY eexam.id
                ORDER BY pages DESC) AS pagestotal
            GROUP BY categoryid";
    // Gets the information of the above query.
    $arraytotalcost = array();
    $i = 1;
    if ($totalcostpiechart = $DB->get_records_sql($sqltotalcostpiechart, $totalcostpiechartparams)) {
        $arraytotalcost [0] = [
            get_string('category', 'mod_emarking'),
            get_string('totalcost', 'mod_emarking')];
        foreach ($totalcostpiechart as $cost) {
            if (! $cost->totalpages == null) {
                $arraytotalcost [$i] [0] = $cost->categoryname;
                $arraytotalcost [$i] [1] = (int) $cost->totalpages;
            } else {
                $arraytotalcost [$i] [0] = $cost->categoryname;
                $arraytotalcost [$i] [1] = 0;
            }
            $i ++;
        }
    }
    return $arraytotalcost;
}
function emarking_get_total_pages_piechart($category) {
    global $DB;
    $totalpagespiechartparams = array(
        "%/$category/%",
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    // Sql that counts all the resourses since the last time the app was used.
    $sqltotalpagespiechart = "
            SELECT categoryid,
            categoryname,
            SUM(pages) AS totalpages
            FROM (
                SELECT c.id AS courseid,
                eexam.id AS examid,
                cc.id as categoryid,
                cc.name as categoryname,
                ((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE cc.path like ?  AND eexam.status IN (?,?)
                GROUP BY eexam.id
                ORDER BY pages DESC) AS pagestotal
            GROUP BY categoryid";
    // Gets the information of the above query.
    $arraytotalpages = array();
    $i = 1;
    if ($totalpagespiechart = $DB->get_records_sql($sqltotalpagespiechart, $totalpagespiechartparams)) {
        $arraytotalpages [0] = [
            get_string('category', 'mod_emarking'),
            get_string('totalprintedpages', 'mod_emarking')];
        foreach ($totalpagespiechart as $pages) {
            if (! $pages->totalpages == null) {
                $arraytotalpages [$i] [0] = $pages->categoryname;
                $arraytotalpages [$i] [1] = (int) $pages->totalpages;
            } else {
                $arraytotalpages [$i] [0] = $pages->categoryname;
                $arraytotalpages [$i] [1] = 0;
            }
            $i ++;
        }
    }
    return $arraytotalpages;
}
function emarking_get_activities_piechart($category) {
    global $DB;
    $activitiespiechartparams = array(
        "%/$category/%");
    // Sql that counts all the resourses since the last time the app was used.
    $sqlactivitiespiechart = "
            SELECT cc.id as id,
            cc.name as name,
            COUNT(e.id) AS activities
			FROM {emarking} e
   			INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
            INNER JOIN {course} c ON (c.id = eexam.course)
            INNER JOIN {course_categories} cc ON (cc.id = c.category)
			WHERE cc.path like ?
            GROUP BY id";
    // Gets the information of the above query.
    $arrayactivities = array();
    $i = 1;
    if ($activitiespiechart = $DB->get_records_sql($sqlactivitiespiechart, $activitiespiechartparams)) {
        $arrayactivities [0] = [
            get_string('category', 'mod_emarking'),
            get_string('activities', 'mod_emarking')];
        foreach ($activitiespiechart as $activities) {
            if (! $activities->activities == null) {
                $arrayactivities [$i] [0] = $activities->name;
                $arrayactivities [$i] [1] = (int) $activities->activities;
            } else {
                $arrayactivities [$i] [0] = $activities->name;
                $arrayactivities [$i] [1] = 0;
            }
            $i ++;
        }
    }
    return $arrayactivities;
}
function emarking_get_emarking_courses_piechart($category) {
    global $DB;
    $emarkingcoursespiechartparams = array(
        "%/$category/%");
    // Sql that counts all the resourses since the last time the app was used.
    $sqlemarkingcoursespiechart = "
            SELECT id,
            name,
            COUNT(course) as countcourses
            FROM (
                SELECT e.course AS course,
                cc.name as name,
                cc.id as id
				FROM {emarking} e
				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = e.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE cc.path like ?
                GROUP BY course
                ORDER BY name desc) as m
           GROUP BY id";
    // Gets the information of the above query.
    $arrayemarkingcourses = array();
    $i = 1;
    if ($emarkingcoursespiechart = $DB->get_records_sql($sqlemarkingcoursespiechart, $emarkingcoursespiechartparams)) {
        $arrayemarkingcourses [0] = [
            get_string('category', 'mod_emarking'),
            get_string('emarkingcourses', 'mod_emarking')];
        foreach ($emarkingcoursespiechart as $courses) {
            if (! $courses->countcourses == null) {
                $arrayemarkingcourses [$i] [0] = $courses->name;
                $arrayemarkingcourses [$i] [1] = (int) $courses->countcourses;
            } else {
                $arrayemarkingcourses [$i] [0] = $courses->name;
                $arrayemarkingcourses [$i] [1] = 0;
            }
            $i ++;
        }
    }
    return $arrayemarkingcourses;
}
function emarking_get_mean_exam_lenght_piechart($category) {
    global $DB;
    $meanexamlenghtpiechartparams = array(
        "%/$category/%",
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlmeanexamlenghtpiechart = "
            SELECT cc.id as id,
            cc.name as name,
            AVG((eexam.totalpages+eexam.extrasheets)) AS pages
			FROM {emarking} e
   			INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
            INNER JOIN {course} c ON (c.id = eexam.course)
            INNER JOIN {course_categories} cc ON (cc.id = c.category)
			WHERE cc.path like ?  AND eexam.status IN (?,?)
			GROUP BY id";
    // Gets the information of the above query.
    $arraymeanlenght = array();
    $i = 1;
    $activities = emarking_get_activities_piechart($category);
    if ($meanexamlenghtpiechart = $DB->get_records_sql($sqlmeanexamlenghtpiechart, $meanexamlenghtpiechartparams)) {
        $arraymeanlenght [0] = [
            get_string('category', 'mod_emarking'),
            get_string('meanexamleanght', 'mod_emarking')];
        foreach ($meanexamlenghtpiechart as $lenght) {
            if (! $lenght->pages == null) {
                $arraymeanlenght [$i] [0] = $lenght->name;
                $arraymeanlenght [$i] [1] = round((int) $lenght->pages);
            } else {
                $arraymeanlenght [$i] [0] = $lenght->name;
                $arraymeanlenght [$i] [1] = 0;
            }
            $i ++;
        }
    }
    return $arraymeanlenght;
}
function emarking_get_printing_cost($category) {
    global $DB;
    $totalpritningcost = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlprintingcost = "
            SELECT SUM(pages) as totalcost
            FROM (
                SELECT c.id AS courseid,
                eexam.id AS examid,
                eexam.printingcost*((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
                GROUP BY eexam.id
                ORDER BY pages DESC) AS pagestotal";
    // Gets the information of the above query.
    $arrayprintingcost = array();
    if ($printingcost = $DB->get_records_sql($sqlprintingcost, $totalpritningcost)) {
        foreach ($printingcost as $cost) {
            $totalprintingcost = $cost->totalcost;
        }
    }
    return $totalprintingcost;
}
function emarking_get_total_cost_by_date($category, $isyear) {
    global $DB;
    $totalcostbydateparams = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    if($isyear == 1){
    // Sql that counts all the resourses since the last time the app was used.
    $sqltotalcostbydate = "
            SELECT printyear,
    		COUNT(printyear) as isyears,
            SUM(pages) AS totalcost
            FROM (
                SELECT c.id AS courseid,
                eexam.id AS examid,
                YEAR(FROM_UNIXTIME(eexam.printdate)) as printyear,
                eexam.printingcost*((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
                GROUP BY eexam.id
                ORDER BY pages DESC) AS pagestotal
            GROUP BY printyear
            ";
    // Gets the information of the above query.
    if ($totalcostbydate = $DB->get_records_sql($sqltotalcostbydate, $totalcostbydateparams)) {
        $arraytotalcostbydate = array();
        $arraytotalcostbydate [0] = [
            get_string('year', 'mod_emarking'),
            get_string('totalcost', 'mod_emarking')];
        $yearcount=1;
        foreach ($totalcostbydate as $costbydate) {
        		$arraytotalcostbydate [$yearcount] [0] = $costbydate->printyear;
        		$arraytotalcostbydate [$yearcount] [1] = (int) $costbydate->totalcost;
        		$yearcount++;
        	}
        }
    }elseif($isyear == 0){
        	$sqltotalcostbydate = "
        	    SELECT printdate,
        	    SUM(pages) AS totalcost
        	    FROM (
        	        SELECT c.id AS courseid,
         	     	eexam.id AS examid,
         	       	MONTH(FROM_UNIXTIME(eexam.printdate)) as printdate,
          	      	eexam.printingcost*((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
					FROM {emarking} e
   					INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                	INNER JOIN {course} c ON (c.id = eexam.course)
                	INNER JOIN {course_categories} cc ON (cc.id = c.category)
					WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
                	GROUP BY eexam.id
                	ORDER BY pages DESC) AS pagestotal
            	GROUP BY printdate
            	";
        	// Gets the information of the above query.
       		if ($totalcostbydate = $DB->get_records_sql($sqltotalcostbydate, $totalcostbydateparams)) {
        		$arraytotalcostbydate = array();
        		$arraytotalcostbydate [0] = [
        			get_string('month', 'mod_emarking'),
        			get_string('totalcost', 'mod_emarking')];
        		for ($contadormes = 1; $contadormes <= 12; $contadormes ++) {
        			if (! isset($arraytotalcostbydate [$contadormes])) {
        				$arraytotalcostbydate [$contadormes] = [
        					date("F", mktime(0, 0, 0, $contadormes, 10)),
        					0];
        			}
        		}
        		foreach ($totalcostbydate as $costbydate) {
        			$arraytotalcostbydate [$costbydate->printdate] [0] = date("F", mktime(0, 0, 0, $costbydate->printdate, 10));
        			$arraytotalcostbydate [$costbydate->printdate] [1] = (int) $costbydate->totalcost;
        		}
    		}
        }
    return $arraytotalcostbydate;
}
function emarking_years_or_months($category){
	global $DB;
	$isyears=0;
	$activitiesbydateparams = array(
			"%/$category/%",
			$category);
	// Sql that counts all the resourses since the last time the app was used.
	$sqlactivitiesbydate = "SELECT COUNT(printyear) as isyears,
							printyear
							FROM
								(SELECT YEAR(FROM_UNIXTIME(eexam.printdate)) AS printyear
								FROM {emarking} e
								INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                				INNER JOIN {course} c ON (c.id = e.course)
                				INNER JOIN {course_categories} cc ON (cc.id = c.category)
			    				WHERE (cc.path like ? OR cc.id = ?)
                				GROUP BY printyear) as y
								";
	// Gets the information of the above query.
	if ($activitiesbydate = $DB->get_records_sql($sqlactivitiesbydate, $activitiesbydateparams)) {
		foreach ($activitiesbydate as $activitys) {
			if($activitys->isyears >= 2){
				$isyears=1;
			}else{
				$actualyear = $activitys->printyear;
			}
		}
	}
	$emarkingcoursesbydateparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED,
			EMARKING_EXAM_SENT_TO_PRINT);
	// Sql that counts all the resourses since the last time the app was used.
	$sqlemarkingcoursesbydate = "
            SELECT count(printyear) AS isyears,
					printyear
            FROM (
                SELECT e.course AS course,
                YEAR(FROM_UNIXTIME(MIN(eexam.printdate))) as printyear
                FROM {emarking} e
				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = e.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
               ) m
               GROUP BY printyear";
	// Gets the information of the above query.
	if ($emarkingcoursesbydate = $DB->get_records_sql($sqlemarkingcoursesbydate, $emarkingcoursesbydateparams)) {
		foreach ($emarkingcoursesbydate as $coursesbydate) {
			if($coursesbydate->isyears >= 2){
				$isyears=1;
			}
		}	
	}
	if($isyears == 0){
	$yearormonth= array($isyears, $actualyear);
	}else{
	$yearormonth= array($isyears);
	}
	return $yearormonth;
}
function emarking_download_excel_teacher_ranking($category) {
    global $DB;
    $teacherrankingdata = emarking_get_teacher_ranking($category);
    $headers = [
        get_string('teachername', 'mod_emarking'),
        get_string('activities', 'mod_emarking')];
    $excelfilename = clean_filename("CourseRankCategory" . $category);
    emarking_save_data_to_excel($headers, $teacherrankingdata, $excelfilename, 2);
}
function emarking_download_excel_course_ranking($category) {
    global $DB;
    $courserankingdata = emarking_get_total_pages_by_course($category);
    $headers = [
        get_string('coursename', 'mod_emarking'),
        get_string('totalprintedpages', 'mod_emarking')];
    $excelfilename = clean_filename("CourseRankCategory" . $category);
    emarking_save_data_to_excel($headers, $courserankingdata, $excelfilename, 2);
}
function emarking_download_excel_monthly_cost($category) {
    global $DB;
    $totalcostdata = emarking_get_total_cost_by_date($category);
    $headers = [
        get_string('monthlycost', 'mod_emarking')];
    $excelfilename = clean_filename("MonthlyCost" . $category);
    emarking_save_data_to_excel($headers, $totalcostdata, $excelfilename, 2);
}