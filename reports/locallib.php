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
 * @param int $category
 *            The category id
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
/**
 * Navigation tabs for cost configuration
 *
 * @param int $category
 *            The category id
 * @return multitype:tabobject array of tabobjects
 */
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
/**
 * Navigation tabs for reports
 *
 * @param string $string
 *            The text you want in the button
 * @param string $id
 * 			  The id you want the button to have
 * @param string $class
 * 			  The class you want the button to have           
 * @return button object
 */
function emarking_buttons_creator($string, $id = null, $class = null) {
	$button = html_writer::tag('button', $string,
			array(
					'id' => $id,
					'class' => $class));
	return $button;
	}
/**
 * Navigation tabs for reports
 *
 * @param array $head
 *            array with the headers of the table
 * @param 2 levels array $data
 * 			  array with each column as an array 
 * @param array $size
 * 			  array with the % of each column
 * @return table object
 */
function emarking_table_creator($head, $data, $size){
	$buttonstable = new html_table();
	$buttonstable->head = $head;
	$buttonstable->data = $data;
	$buttonstable->size = $size;	
	return html_writer::table($buttonstable);
}
function emarking_get_subcategories($category){
	global $DB;
	$arraysubcategory = array();
	$subcategoryquery = "SELECT cc.id as id, cc.name as name FROM {course_categories} as cc
						INNER JOIN {course} c ON (cc.id = c.category)
						INNER JOIN {emarking_exams} eexam ON (c.id = eexam.course)
						INNER JOIN {emarking} e ON (e.id = eexam.emarking)
					    WHERE ".$DB->sql_like('path', ':path')."GROUP BY cc.id";
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
    if ($activities = $DB->get_record_sql($sqlactivities, $activitiesparams)) {
		return $activities->activities;
    } else {
        return 0;
    }
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
    if ($originalpages = $DB->get_record_sql($sqloriginalpages, $originalpagesparams)) {
          return round((int) $originalpages->pages);
    }else {
          return 0;            
    }
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
    if ($pages = $DB->get_record_sql($sqlpage, $pageparams)) {
    	if ($pages->totalpages != null){
          return $pages->totalpages;
    	} 
          return 0;
	}
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
    if ($emarkingcourses = $DB->get_record_sql($sqlemarkingcourses, $emarkingcoursesparams)) {
            return $emarkingcourses->courses;
    } else {
        return 0;	
	}
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
    if ($students = $DB->get_record_sql($sqlstudents, $studentsparams)) {
		return $students->user;
    } else {
    return 0;
    }
}
function emarking_get_total_cost_for_table($category, $isyears) {
    global $DB, $CFG;
    $data = "MONTH";
    if($isyears == 1){
    	$data = "YEAR";
    }
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
                ".$data."(FROM_UNIXTIME(eexam.printdate)) as printdate,
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
    	if($isyears == 1){
    		$yearcount=1;
    		foreach ($totalpagesbydate as $costbydate) {
    			$arraytotalpagesbydate [$yearcount][0] = $costbydate->printdate;
    			$arraytotalpagesbydate [$yearcount][1] = '$' . " " . number_format((int) $costbydate->totalcost);
    			$yearcount++;
    		}
    	}
     	else{
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
     	}
    } else {
        $arraytotalpagesbydate = [];
    }
    return $arraytotalpagesbydate;
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
                eexam.printingcost*(eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
                GROUP BY eexam.id
                ORDER BY pages DESC) AS pagestotal";
    // Gets the information of the above query.
    if ($printingcost = $DB->get_record_sql($sqlprintingcost, $totalpritningcost)) {
    	return $printingcost->totalcost;
    } else{
    	return 0;
    }
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
	if ($activitiesbydate = $DB->get_record_sql($sqlactivitiesbydate, $activitiesbydateparams)) {
			if($activitiesbydate->isyears >= 2){
				$isyears=1;
			}else{
				$actualyear = $activitiesbydate->printyear;
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
function emarking_download_excel_monthly_cost($category, $totalcostdata) {
    global $DB;
    $headers = [
        get_string('costbydate', 'mod_emarking')];
    $excelfilename = clean_filename("Costes" . $category);
    emarking_save_data_to_excel($headers, $totalcostdata, $excelfilename, 2);
}
function emarking_get_query($params, $SELECT, $SUBSELECT = null, $SUBWHERE = null, $SUBGROUPBY = null, $SUBORDERBY = null, $WHERE = null, $GROUPBY = null, $ORDERBY = null) {
	global $DB;
	// Sql that counts all the resourses since the last time the app was used.
	$query = "SELECT ".$SELECT." FROM";
	if($SUBSELECT != null){
		$query = $query." (SELECT ".$SUBSELECT." 
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)";	
     if($SUBWHERE != null){
				$query = $query." WHERE 	".$SUBWHERE;
     }
	 if($SUBGROUPBY != null){
	 			$query = $query." GROUP BY ".$SUBGROUPBY;
	 }
	 if($SUBORDERBY != null){
	 			$query = $query." ORDER BY ".$SUBORDERBY;
	 }

	 			$query = $query." ) AS subquery";
	 }else{
	 	$query = $query." {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)";	
	 }
	 if($WHERE != null){
	 			$query= $query." WHERE ".$WHERE;
	 }
	 if($GROUPBY != null){
            	$query = $query. " GROUP BY ".$GROUPBY;
            }
     if($ORDERBY != null){
            $query = $query." ORDER BY ".$ORDERBY;
     }
	if ($result = $DB->get_records_sql($query, $params)){
		return $result;
	}else {
		return 0;
	}	
}
function emarking_array_column_chart($queryresult, $arraytitles, $queryvalue1, $queryvalue2){
	$array = array();
	$i = 1;
	$value1 = str_replace('"','',$queryvalue1);
	$value2 = str_replace('"','',$queryvalue2);
	if(!$queryresult){
		$array = [['nodata','nodata'],[0,0],[0,0]];
		return $array;
	}
	
	$array [0] = $arraytitles;
	foreach ($queryresult as $results) {
		if (! $results->$value1 == null) {
			$array [$i] [0] = $results->$value2;
			$array [$i] [1] = round((int) $results->$value1);
		} else {
			$array [$i] [0] = $results->$value2;
			$array [$i] [1] = 0;
		}
		$i ++;
	}
	
	return $array;
	}
function emarking_array_by_date($isyears, $queryresult, $secondarraytitle, $querydatevalue, $queryvalue){
	$array = array();
	$secondtitle = str_replace('"','',$secondarraytitle);
	$date = str_replace('"','',$querydatevalue);
	$value = str_replace('"','',$queryvalue);
	if(!$queryresult){
		$array = [
				['nodata','nodata'],[0,0],[0,0]];
		return $array;
	}
	if($isyears == 1){
		$array [0] = [
				get_string('year', 'mod_emarking'),
				$secondtitle			
		];
		$yearcount=1;
		foreach ($queryresult as $results) {
			$array [$yearcount] [0] = $results->$date;
			$array [$yearcount] [1] = (int) $results->$value;
			$yearcount++;
		}
	}
	if($isyears == 0){
	$array [0] = [
			get_string('month', 'mod_emarking'),
			$secondtitle
	];
	for ($contadormes = 1; $contadormes <= 12; $contadormes ++) {
		if (! isset($array [$contadormes])) {
			$array [$contadormes] = [
					date("F", mktime(0, 0, 0, $contadormes, 10)),
					0];
		}
	}
	foreach ($queryresult as $results) {
		$array [$results->$date] [0] = date("F", mktime(0, 0, 0, $results->$date, 10));
		$array [$results->$date] [1] = (int) $results->$value;
	}
	}
	return $array;
}