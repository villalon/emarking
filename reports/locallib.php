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
}/**
 * EMarking time progression
 *
 * @param array $emarkingid
 *            The emarking ids
 * @return multitype: array 
 */
function emarking_time_progression($course, $fortable = null){
	global $DB;
	// EMarking cycle	
	$sqlemarking = "SELECT e.id AS id, e.name as name, eexam.timecreated AS printorder, eexam.printdate AS printdate, MIN(d.timecreated) AS digitalized,
							MIN(d.timecorrectionstarted) AS correctionstarted, MAX(d.timecorrectionended) AS corrected, MIN(d.timefirstpublished) AS firstpublished,
							MIN(d.timeregradingstarted) AS regradingstarted, MAX(d.timeregradingended) AS regraded, MAX(d.timelastpublished) AS lastpublished, 
							MAX(d.status) as maxstatus, MIN(d.status) as minstatus
							FROM mdl_emarking_exams AS eexam
                            INNER JOIN mdl_emarking AS e ON (e.id = eexam.emarking AND e.course= ?)
							LEFT JOIN mdl_emarking_draft AS d ON (e.id = d.emarkingid AND d.status != ?)
							GROUP BY e.id";
	// Gets the information of the above query.
	if ($emarkings = $DB->get_records_sql($sqlemarking,array($course, EMARKING_STATUS_ABSENT))) {
		$position=0;
		foreach($emarkings as $emarking){
			$time = time();
			
			if($emarking->printdate == 0){
				$status = EMARKING_TO_PRINT;
			
			}elseif(is_null($emarking->digitalized) && $emarking->printdate !== 0){
				$status = EMARKING_PRINTED;
			
			}elseif(is_null($emarking->correctionstarted) && $emarking->maxstatus == EMARKING_STATUS_SUBMITTED || $emarking->minstatus == EMARKING_STATUS_SUBMITTED){
				$status = EMARKING_STATUS_SUBMITTED;
			
			}elseif(is_null($emarking->firstpublished) && $emarking->maxstatus == EMARKING_STATUS_GRADING){
				$status = EMARKING_STATUS_GRADING;
				
			}elseif(is_null($emarking->regradingstarted) && $emarking->maxstatus ==EMARKING_STATUS_PUBLISHED){
				$status = EMARKING_STATUS_PUBLISHED;
			
			}elseif($emarking->maxstatus ==EMARKING_STATUS_REGRADING){
				$status = EMARKING_STATUS_REGRADING;
			
			}elseif($emarking->firstpublished < $emarking->regradingstarted && $emarking->minstatus == EMARKING_STATUS_GRADING || $emarking->maxstatus == EMARKING_STATUS_REGRADING_RESPONDED){
				$status = EMARKING_STATUS_REGRADING_RESPONDED;
			
			}elseif($emarking->minstatus ==EMARKING_STATUS_PUBLISHED && $emarking->lastpublished > $emarking->regraded && $emarking->regraded !== null){
				$status = EMARKING_STATUS_FINAL_PUBLISHED;
			}
			//echo "publi: $emarking->firstpublished regra: $emarking->regradingstarted maxstatus: $emarking->maxstatus minstatus: $emarking->minstatus status: $status";
			switch ($status) {
				
				case EMARKING_TO_PRINT:
					
					$emarkingarray[$position]= array(
						$emarking->name,
						(round(($time - $emarking->printorder)/86400)),
						0,0,0,0,0,0,0,0,0,
						(round(($time - $emarking->printorder)/86400))." Days"
					);
					$position++;
					break;
					
				case EMARKING_PRINTED:
					
					if(((time() - $emarking->printdate)/86400)>30){
						$time = ($emarking->printdate + 2592000);
					}
					
					$emarkingarray[$position]= array(
							$emarking->name,
							(round(($emarking->printdate - $emarking->printorder)/86400)),
							(round(($time - $emarking->printdate)/86400)),
							0,0,0,0,0,0,0,0,
							(round(($time - $emarking->printorder)/86400))." Days"
					);
					
					$position++;
					break;
					
				case EMARKING_STATUS_SUBMITTED:
					
					if(((time() - $emarking->digitalized)/86400)>30){
						$time = ($emarking->digitalized + 2592000);
					}
					
					$emarkingarray[$position]= array(
							$emarking->name,
							(round(($emarking->printdate - $emarking->printorder)/86400,1)),
							(round(($emarking->digitalized - $emarking->printdate)/86400,1)),
							(round(($time - $emarking->digitalized)/86400,1)),
							0,0,0,0,0,0,0,
							(round(($time - $emarking->printorder)/86400,1))." Days"
					);
					$position++;
					break;
					
				case EMARKING_STATUS_GRADING:
					
					if(((time() - $emarking->corrected)/86400)>15){
						$time = ($emarking->corrected + 1296000);
					}
					
					$emarkingarray[$position]= array(
							$emarking->name,
							(round(($emarking->printdate - $emarking->printorder)/86400,1)),
							(round(($emarking->digitalized - $emarking->printdate)/86400,1)),
							(round(($emarking->correctionstarted - $emarking->digitalized)/86400,1)),
							(round(($time - $emarking->correctionstarted)/86400,1)),
							0,0,0,0,0,0,
							(round(($time - $emarking->printorder)/86400,1))." Days"
					);
					$position++;
					break;
					
				case EMARKING_STATUS_PUBLISHED:
					
					if(((time() - $emarking->firstpublished)/86400)>10){
						$time = ($emarking->firstpublished + 864000);
					}
					
					$emarkingarray[$position]= array(
							$emarking->name,
							(round(($emarking->printdate - $emarking->printorder)/86400,1)),
							(round(($emarking->digitalized - $emarking->printdate)/86400,1)),
							(round(($emarking->correctionstarted - $emarking->digitalized)/86400,1)),
							(round(($emarking->corrected - $emarking->correctionstarted)/86400,1)),
							(round(($emarking->firstpublished - $emarking->corrected)/86400,1)),
							(round(($time - $emarking->firstpublished)/86400,1)),
							0,0,0,0,
							(round(($time - $emarking->printorder)/86400,1))." Days"
					);
					$position++;
					break;
					
				case EMARKING_STATUS_REGRADING:
					$emarkingarray[$position]= array(
							$emarking->name,
							(round(($emarking->printdate - $emarking->printorder)/86400,1)),
							(round(($emarking->digitalized - $emarking->printdate)/86400,1)),
							(round(($emarking->correctionstarted - $emarking->digitalized)/86400,1)),
							(round(($emarking->corrected - $emarking->correctionstarted)/86400,1)),
							(round(($emarking->firstpublished - $emarking->corrected)/86400,1)),
							(round(($emarking->regradingstarted - $emarking->firstpublished)/86400,1)),
							(round(($time - $emarking->regradingstarted)/86400,1)),
							0,0,0,
							(round(($time - $emarking->printorder)/86400,1))." Days"
					);
					$position++;
					break;
					
				case EMARKING_STATUS_REGRADING_RESPONDED:
					
					if(((time() - $emarking->regraded)/86400)>5){
						$time = ($emarking->regraded + 432000);
					}
					
					$emarkingarray[$position]= array(
							$emarking->name,
							(round(($emarking->printdate - $emarking->printorder)/86400,1)),
							(round(($emarking->digitalized - $emarking->printdate)/86400,1)),
							(round(($emarking->correctionstarted - $emarking->digitalized)/86400,1)),
							(round(($emarking->corrected - $emarking->correctionstarted)/86400,1)),
							(round(($emarking->firstpublished - $emarking->corrected)/86400,1)),
							(round(($emarking->regradingstarted - $emarking->firstpublished)/86400,1)),
							(round(($emarking->regraded - $emarking->regradingstarted)/86400,1)),
							(round(($time - $emarking->regraded)/86400,1)),
							0,0,
							(round(($time - $emarking->printorder)/86400,1))." Days"
					);
					$position++;
					break;
					
				case EMARKING_STATUS_FINAL_PUBLISHED:
					
					if(((time() - $emarking->lastpublished)/86400)>5){
						$time = ($emarking->lastpublished + 432000);
					}
					
					$emarkingarray[$position]= array(
					$emarking->name,
					(round(($emarking->printdate - $emarking->printorder)/86400,1)),
					(round(($emarking->digitalized - $emarking->printdate)/86400,1)),
					(round(($emarking->correctionstarted - $emarking->digitalized)/86400,1)),
					(round(($emarking->corrected - $emarking->correctionstarted)/86400,1)),
					(round(($emarking->firstpublished - $emarking->corrected)/86400,1)),
					(round(($emarking->regradingstarted - $emarking->firstpublished)/86400,1)),
					(round(($emarking->regraded - $emarking->regradingstarted)/86400,1)),
					(round(($emarking->lastpublished - $emarking->regraded)/86400,1)),
					(round(($time- $emarking->lastpublished)/86400,1)),
					0,
					(round(($time - $emarking->printorder)/86400,1))." Days"
					);
					$position++;
					break;
			}
			$status = null;
		}
		return $emarkingarray;
	}else{
		return 0;
	}
}
function emarking_cycle_tabs($selectedcourse, $selectedcategory, $course){
	global $DB;

	$getemarkingssql = 'SELECT e.id AS id,
				e.name AS name
				FROM {emarking} AS e
				INNER JOIN {course} AS c ON (e.course = c.id AND c.shortname = ?)';

	$getemarkings = $DB->get_records_sql($getemarkingssql, array($selectedcourse));

	$emarkingtabs = array();

	$emarkingtabs[] = new tabobject(0,
			new moodle_url("/mod/emarking/reports/cycle.php", array(
					"course" => $course->id, "emarking" => 0,
					"selectedcourse" => $selectedcourse,
					"selectedcategory" => $selectedcategory,
					"currenttab" => 0
			)),
			get_string('summary', 'mod_emarking'));

	$tabid = 1;
	foreach($getemarkings as $emarkings){

		$emarkingtabs[] = new tabobject($tabid,
				new moodle_url("/mod/emarking/reports/cycle.php", array(
						"course" => $course->id, "emarking" => $emarkings->id,
						"selectedcourse" => $selectedcourse,
						"selectedcategory" => $selectedcategory,
						"currenttab" => $tabid
				)),
				$emarkings->name);

		$tabid = $tabid + 1;
	}
	return $emarkingtabs;
}
/**
 * EMarking gantt chart data
 * 
 * @param int $emarkingid
 * 				The emarking id
 * @return multitype: array
 */
function emarking_gantt_data($emarkingid){
global $DB;
	$emarkingdatasql = "SELECT e.id AS id,
			e.name AS name,
			ee.timecreated AS printorder,
			ee.printdate AS printdate,
			MIN(ed.timecreated) AS digitalized,
			MIN(ed.timecorrectionstarted) AS correctionstarted,
			MAX(ed.timecorrectionended) AS corrected,
			MIN(ed.timefirstpublished) AS firstpublished,
			MIN(ed.timeregradingstarted) AS regradingstarted,
			MAX(ed.timeregradingended) AS regraded,
			MAX(ed.timelastpublished) AS lastpublished,
			MAX(ed.status) as maxstatus,
			MIN(ed.status) as minstatus,
			count(ed.id) as draftnums
			FROM {emarking_exams} AS ee
            INNER JOIN {emarking} AS e ON (e.id = ee.emarking AND ee.id = ?)
			LEFT JOIN {emarking_draft} AS ed ON (e.id = ed.emarkingid AND ed.status != ?)";

	if($emarking = $DB->get_record_sql($emarkingdatasql, array($emarkingid, EMARKING_STATUS_ABSENT))){
		$time = time()*1000;
		if($emarking->printdate == 0){
			$status = EMARKING_TO_PRINT;
		
		}elseif(is_null($emarking->digitalized) && $emarking->printdate !== 0){
			$status = EMARKING_PRINTED;
			if(((time() - $emarking->printdate)/86400)>30){
				$time = ($emarking->printdate + 2592000)*1000;
			}
		
		}elseif(is_null($emarking->correctionstarted) && $emarking->maxstatus == EMARKING_STATUS_SUBMITTED || $emarking->minstatus == EMARKING_STATUS_SUBMITTED){
			$status = EMARKING_STATUS_SUBMITTED;
			if(((time() - $emarking->digitalized)/86400)>30){
				$time = ($emarking->digitalized + 2592000)*1000;
			}
		
		}elseif(is_null($emarking->firstpublished) && $emarking->maxstatus == EMARKING_STATUS_GRADING){
			$status = EMARKING_STATUS_GRADING;
			
		
		}elseif(is_null($emarking->regradingstarted) && $emarking->maxstatus ==EMARKING_STATUS_PUBLISHED){
			$status = EMARKING_STATUS_PUBLISHED;
			if(((time() - $emarking->firstpublished)/86400)>10){
				$time = ($emarking->firstpublished + 864000)*1000;
			}
		
		}elseif($emarking->maxstatus ==EMARKING_STATUS_REGRADING){
			$status = EMARKING_STATUS_REGRADING;
			
				
		}elseif($emarking->firstpublished < $emarking->regradingstarted && $emarking->minstatus == EMARKING_STATUS_GRADING  || $emarking->maxstatus == EMARKING_STATUS_REGRADING_RESPONDED){
			$status = EMARKING_STATUS_REGRADING_RESPONDED;
			if(((time() - $emarking->regraded)/86400)>5){
				$time = ($emarking->regraded + 432000)*1000;
			}
				
		}elseif($emarking->minstatus ==EMARKING_STATUS_PUBLISHED && $emarking->lastpublished > $emarking->regraded && $emarking->regraded !== null){
			$status = EMARKING_STATUS_FINAL_PUBLISHED;
			if(((time() - $emarking->lastpublished)/86400)>5){
				$time = ($emarking->lastpublished + 432000)*1000;
			}
		}
		//echo "publi: $emarking->firstpublished regra: $emarking->regradingstarted maxstatus: $emarking->maxstatus minstatus: $emarking->minstatus";
		$ganttarray = array();
		switch ($status) {
				
			case EMARKING_TO_PRINT:
				$ganttarray = array(
						array('1', 'enviado a imprimir', 'ImpresiÃ³n', $emarking->printorder*1000, $time, null, 100, null),
				);
				break;
		
			case EMARKING_PRINTED:
				$ganttarray = array(
				array('1', 'enviado a imprimir', 'ImpresiÃ³n', $emarking->printorder*1000, $emarking->printdate*1000, null, 100, null),
				array('2', 'impreso', 'ImpresiÃ³n', $emarking->printdate*1000, $time, null, 100, '1'),
				);
				break;
		
			case EMARKING_STATUS_SUBMITTED:
				$ganttarray = array(
						array('1', 'enviado a imprimir', 'ImpresiÃ³n', $emarking->printorder*1000, $emarking->printdate*1000, null, 100, null),
						array('2', 'impreso', 'ImpresiÃ³n', $emarking->printdate*1000, $emarking->digitalized*1000, null, 100, '1'),
						array('3', 'digitalizado', 'DigitalizaciÃ³n', $emarking->digitalized*1000, $time, null, 100, '2'),
				);
				break;
		
			case EMARKING_STATUS_GRADING:
				$ganttarray = array(
						array('1', 'enviado a imprimir', 'ImpresiÃ³n', $emarking->printorder*1000, $emarking->printdate*1000, null, 100, null),
						array('2', 'impreso', 'ImpresiÃ³n', $emarking->printdate*1000, $emarking->digitalized*1000, null, 100, '1'),
						array('3', 'digitalizado', 'DigitalizaciÃ³n', $emarking->digitalized*1000, $emarking->correctionstarted*1000, null, 100, '2'),
						array('4', 'en correccion', 'CorrecciÃ³n', $emarking->correctionstarted*1000, $time, null, 100, '3'),
				);
				break;
		
			case EMARKING_STATUS_PUBLISHED:
				$ganttarray = array(
						array('1', 'enviado a imprimir', 'ImpresiÃ³n', $emarking->printorder*1000, $emarking->printdate*1000, null, 100, null),
						array('2', 'impreso', 'ImpresiÃ³n', $emarking->printdate*1000, $emarking->digitalized*1000, null, 100, '1'),
						array('3', 'digitalizado', 'DigitalizaciÃ³n', $emarking->digitalized*1000, $emarking->correctionstarted*1000, null, 100, '2'),
						array('4', 'en correccion', 'CorrecciÃ³n', $emarking->correctionstarted*1000, $emarking->corrected*1000, null, 100, '3'),
						array('5', 'corregido', 'CorrecciÃ³n', $emarking->corrected*1000, $emarking->firstpublished*1000, null, 100, '4'),
						array('6', 'publicado', 'PublicaciÃ³n', $emarking->firstpublished*1000, $time, null, 100, '5'),
				);
				break;
		
			case EMARKING_STATUS_REGRADING:
				$ganttarray = array(
						array('1', 'enviado a imprimir', 'ImpresiÃ³n', $emarking->printorder*1000, $emarking->printdate*1000, null, 100, null),
						array('2', 'impreso', 'ImpresiÃ³n', $emarking->printdate*1000, $emarking->digitalized*1000, null, 100, '1'),
						array('3', 'digitalizado', 'DigitalizaciÃ³n', $emarking->digitalized*1000, $emarking->correctionstarted*1000, null, 100, '2'),
						array('4', 'en correccion', 'CorrecciÃ³n', $emarking->correctionstarted*1000, $emarking->corrected*1000, null, 100, '3'),
						array('5', 'corregido', 'CorrecciÃ³n', $emarking->corrected*1000, $emarking->firstpublished*1000, null, 100, '4'),
						array('6', 'publicado', 'PublicaciÃ³n', $emarking->firstpublished*1000, $emarking->regradingstarted*1000, null, 100, '5'),
						array('7', 'en recorreccion', 'RecorrecciÃ³n', $emarking->regradingstarted*1000, $time, null, 100, '6'),
				);
				break;
		
			case EMARKING_STATUS_REGRADING_RESPONDED:
				$ganttarray = array(
						array('1', 'enviado a imprimir', 'ImpresiÃ³n', $emarking->printorder*1000, $emarking->printdate*1000, null, 100, null),
						array('2', 'impreso', 'ImpresiÃ³n', $emarking->printdate*1000, $emarking->digitalized*1000, null, 100, '1'),
						array('3', 'digitalizado', 'DigitalizaciÃ³n', $emarking->digitalized*1000, $emarking->correctionstarted*1000, null, 100, '2'),
						array('4', 'en correccion', 'CorrecciÃ³n', $emarking->correctionstarted*1000, $emarking->corrected*1000, null, 100, '3'),
						array('5', 'corregido', 'CorrecciÃ³n', $emarking->corrected*1000, $emarking->firstpublished*1000, null, 100, '4'),
						array('6', 'publicado', 'PublicaciÃ³n', $emarking->firstpublished*1000, $emarking->regradingstarted*1000, null, 100, '5'),
						array('7', 'en recorreccion', 'RecorrecciÃ³n', $emarking->regradingstarted*1000, $emarking->regraded*1000, null, 100, '6'),
						array('8', 'recorregido', 'RecorrecciÃ³n', $emarking->regraded*1000, $time, null, 100, '7'),
				);
				break;
		
			case EMARKING_STATUS_FINAL_PUBLISHED:
				$ganttarray = array(
						array('1', 'enviado a imprimir', 'ImpresiÃ³n', $emarking->printorder*1000, $emarking->printdate*1000, null, 100, null),
						array('2', 'impreso', 'ImpresiÃ³n', $emarking->printdate*1000, $emarking->digitalized*1000, null, 100, '1'),
						array('3', 'digitalizado', 'DigitalizaciÃ³n', $emarking->digitalized*1000, $emarking->correctionstarted*1000, null, 100, '2'),
						array('4', 'en correccion', 'CorrecciÃ³n', $emarking->correctionstarted*1000, $emarking->corrected*1000, null, 100, '3'),
						array('5', 'corregido', 'CorrecciÃ³n', $emarking->corrected*1000, $emarking->firstpublished*1000, null, 100, '4'),
						array('6', 'publicado', 'PublicaciÃ³n', $emarking->firstpublished*1000, $emarking->regradingstarted*1000, null, 100, '5'),
						array('7', 'en recorreccion', 'RecorrecciÃ³n', $emarking->regradingstarted*1000, $emarking->regraded*1000, null, 100, '6'),
						array('8', 'recorregido', 'RecorrecciÃ³n', $emarking->regraded*1000, $emarking->lastpublished*1000, null, 100, '7'),
						array('9', 'publicado final', 'PublicaciÃ³n', $emarking->lastpublished*1000, $time, null, 100, '8')
				);
				break;
		}
		
		return $ganttarray;
	}
}
function emarking_area_chart($emarkingid){
	global $DB;
	
	$chartparameterssql = "SELECT COUNT(ed.id) AS quantity,
				FROM_UNIXTIME(MIN(ed.timecreated), '%Y-%m-%d') AS mindigitalized,
				FROM_UNIXTIME(MAX(ed.timecreated), '%Y-%m-%d') AS maxdigitalized,
				FROM_UNIXTIME(MAX(ed.timecorrectionended), '%Y-%m-%d') AS corrected,
				FROM_UNIXTIME(MAX(ed.timefirstpublished), '%Y-%m-%d') AS firstpublished,
				FROM_UNIXTIME(MAX(ed.timeregradingended), '%Y-%m-%d') AS regraded,
				FROM_UNIXTIME(MAX(ed.timelastpublished), '%Y-%m-%d') AS lastpublished
				FROM mdl_emarking_draft AS ed
				WHERE ed.emarkingid = ?";
	
	if($chartparameters = $DB->get_record_sql($chartparameterssql, array($emarkingid))){
		
		$date= $chartparameters->mindigitalized;
		$date = date('Y-m-d', strtotime(str_replace('-','/', $date)));
		$date =  date('Y-m-d', strtotime($date. ' - 1 days'));

		if(!is_null($chartparameters->lastpublished)){
			$enddate = $chartparameters->lastpublished;
		}elseif(!is_null($chartparameters->regraded)){
			$enddate = $chartparameters->regraded;
		}elseif(!is_null($chartparameters->firstpublished)){
			$enddate = $chartparameters->firstpublished;
		}elseif(!is_null($chartparameters->corrected)){
			$enddate = $chartparameters->corrected;
		}elseif(!is_null($chartparameters->maxdigitalized)){
			$enddate = $chartparameters->maxdigitalized;
		}else{
			return 0;
		}
		
		$enddate = date('Y-m-d', strtotime(str_replace('-','/', $enddate)));
		$enddate =  date('Y-m-d', strtotime($enddate. ' + 1 days'));
		
		$draftsdatasql = "SELECT ed.id AS draftid,
					FROM_UNIXTIME(ed.timecorrectionstarted, '%Y-%m-%d') AS correctionstarted,
					FROM_UNIXTIME(ed.timecorrectionended, '%Y-%m-%d') AS correctionended,
					FROM_UNIXTIME(ed.timefirstpublished, '%Y-%m-%d') AS firstpublished,
					FROM_UNIXTIME(ed.timelastpublished, '%Y-%m-%d') AS lastpublished,
					FROM_UNIXTIME(ed.timeregradingstarted, '%Y-%m-%d') AS regradingstarted,
					FROM_UNIXTIME(ed.timeregradingended, '%Y-%m-%d') AS regraded
					FROM mdl_emarking_draft AS ed
					WHERE ed.emarkingid = ?";
		
		if($draftsdata = $DB->get_records_sql($draftsdatasql, array($emarkingid))){
			
		 	$currentdata = array();
			foreach($draftsdata as $draftdates){
				$currentdata[$draftdates->draftid] = 'Digitalized';
			}
			$areachart = array();

			while($date < $enddate){
				if($date != null){
					foreach($draftsdata as $draftstatus){
						if($draftstatus->lastpublished == $date){
							$currentdata[$draftstatus->draftid] = 'finalpublished';
						}
						if($draftstatus->regraded == $date){
							$currentdata[$draftstatus->draftid] = 'regraded';
						}
						if($draftstatus->regradingstarted == $date){
							$currentdata[$draftstatus->draftid] = 'regrading';
						}
						if($draftstatus->firstpublished == $date){
							$currentdata[$draftstatus->draftid] = 'Publicated';
						}
						if($draftstatus->correctionended == $date){
							$currentdata[$draftstatus->draftid] = 'Graded';
						}
						if($draftstatus->correctionstarted == $date){
							$currentdata[$draftstatus->draftid] = 'Grading';
						}
					}
				}
				
				$datacount = [$date,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
				foreach($currentdata as $data){
					if($data == 'Digitalized'){
						$datacount[1] = $datacount[1] + 1;
					}
					if($data == 'Grading'){
						$datacount[3] = $datacount[3] + 1;
					}
					if($data == 'Graded'){
						$datacount[5] = $datacount[5] + 1;
					}
					if($data == 'Publicated'){
						$datacount[7] = $datacount[7] + 1;
					}
					if($data == 'finalpublished'){
						$datacount[9] = $datacount[9] + 1;
					}
					if($data == 'regrading'){
						$datacount[11] = $datacount[11] + 1;
					}
					if($data == 'regraded'){
						$datacount[13] = $datacount[13] + 1;
					}
				}
				for($i=2;$i<15;$i = $i + 2){
				$datacount[$i] = emarking_area_chart_tooltip($datacount);
				}
				array_push($areachart,$datacount);
				$datacount = array();
				$date =  date('Y-m-d', strtotime($date. ' + 1 days'));
			}	
			return $areachart;
		}
	}
}
function emarking_markers_corrections($emarkingid, $ismarkers = null){
	global $DB;
	$markerssql = "SELECT  comment,u.id, CONCAT(u.firstname,' ',u.lastname) as name, correctiontime
					FROM (SELECT c.id as comment, IF(r.id IS NULL,c.markerid,r.markerid) as marker, c.timecreated as correctiontime
						  FROM {emarking} AS e
						  INNER JOIN {emarking_submission} AS s ON (s.emarking = e.id AND emarking = ?)
						  INNER JOIN {emarking_draft} AS d ON (s.id = d.submissionid)
						  INNER JOIN {emarking_comment} AS c ON (c.draft = d.id)
					      LEFT JOIN mdl_emarking_regrade AS r ON (r.criterion = c.criterionid AND c.draft = r.draft)) as y
					INNER JOIN {user} AS u ON (y.marker = u.id)
					GROUP BY u.id
			 		ORDER BY name ASC";
	if($markers = $DB->get_records_sql($markerssql, array($emarkingid))){
		$arraymarkers = array();
		$contador = 1;
		$arraystacking = ['date'=>0];
		foreach($markers as $marker){
			$arraystacking[$marker->name] = 0;
			$arraystacking[$contador] = 0;
			$contador++;
		}
		if($ismarkers == 1){
			foreach($markers as $marker){
				$arraymarkers[] = $marker->name;
			}
			return $arraymarkers;
		}
		$markers = array_reverse($markers);
		$commentssql = "SELECT  comment,CONCAT(u.firstname,' ',u.lastname) as name, FROM_UNIXTIME(correctiontime, '%Y-%m-%d') as date
						FROM (SELECT c.id as comment, IF(r.id IS NULL,c.markerid,r.markerid) as marker, c.timecreated as correctiontime
							  FROM {emarking} AS e
							  INNER JOIN {emarking_submission} AS s ON (s.emarking = e.id AND emarking = ?)
							  INNER JOIN {emarking_draft} AS d ON (s.id = d.submissionid)
							  INNER JOIN {emarking_comment} AS c ON (c.draft = d.id)
						      LEFT JOIN {emarking_regrade} AS r ON (r.criterion = c.criterionid AND c.draft = r.draft)) as y
						INNER JOIN {user} AS u ON (y.marker = u.id)
						ORDER BY correctiontime, name ASC";
		if($comments = $DB->get_records_sql($commentssql, array($emarkingid))){
			
			$auxdate = 0;
			$auxcorrector = $comments[1]->name;
			$data = array();	
			foreach($comments as $comment){
				if($comment->date != $auxdate){
					$data[$comment->date] = array();
					$dates[] = $comment->date;
				}	
			}
			foreach($markers as $correctors){				
				$names[] = $correctors->name;
			}
			$names = array_unique($names);
			$dates = array_unique($dates);
			$size = count($comments);
			
			foreach($dates as $date){
				foreach($names as $name){
					$data[$date][$name] = 0 ;
				}
			}
		
			$auxdate = $comments[1]->date;
			$auxname = $comments[1]->name;
			
			foreach($dates as $date){
				$count = 0;
				foreach($comments as $correctors){
					if($date == $auxdate && $auxname == $correctors->name){
						$count++;
					}elseif($date == $auxdate && $auxname != $correctors->name){
                        $data[$date][$auxname] = $count;
                        $count = 1;
                        $auxname = $correctors->name;                       
					}elseif($date != $auxdate){
                        $data[$date][$auxname] = $count;
                        $count = 1;
                        $auxdate = $correctors->date;
                        $auxname = $correctors->name;                       
					}
				}
			}
			foreach($data as $key => $value){
				$markersdata[] = $value;
			}

			for($id = 0; $id < count($markersdata); $id++){
				foreach($names as $name){
					$returndata[$id][] = $markersdata[$id][$name];
				}
			}
			return $returndata;
		}
	}
}
function emarking_justice_perception($course){
	global $DB;

	$getemarkingssql = 'SELECT ee.id AS id
				FROM {emarking_exams} AS ee
				INNER JOIN {course} AS c ON (ee.course = c.id AND c.shortname = ?)';

	$getemarkings = $DB->get_records_sql($getemarkingssql, array($course));

	foreach($getemarkings as $id){
		$emarkingids[] = $id->id;
	}
	$emarkingids = implode(',',$emarkingids);

	$perceptiondatasql = "SELECT CONCAT (u.firstname, ' ', u.lastname)AS name,
					COUNT(er.accepted) AS regrades,
					AVG(ep.overall_fairness) AS justice_perception,
					(MAX(egh.timecreated) - MIN(egh.timecreated))/86400 AS correction_time
					FROM {emarking_perception} AS ep
					INNER JOIN {emarking_submission} AS es ON (ep.submission = es.id)
					INNER JOIN {emarking_exams} AS ee ON (es.emarking = ee.id AND ee.id IN(?))
					INNER JOIN {emarking_draft} AS ed ON (ed.emarkingid = ee.id)
					INNER JOIN {emarking_regrade} AS er ON (ed.id = er.draft)
					INNER JOIN {emarking_grade_history} AS egh ON (egh.draftid = ed.id)
					INNER JOIN {user} AS u ON (u.id = egh.marker)
					GROUP BY u.id";
	$perceptiondata = $DB->get_records_sql($perceptiondatasql, array($emarkingids));
	
	if(!empty($perceptiondata)){
		
		echo html_writer::tag('h4',get_string('justiceperceptiontable', 'emarking'),array('style' => 'width:100%;'));
		
		$tablehead = array(' ',get_string('regrades', 'mod_emarking'), get_string('justice_perception', 'mod_emarking'),
				get_string('daysincorrection', 'mod_emarking')
		);
		$tablerow = array();
			$tabledata = array();
		foreach($perceptiondata as $data){
			$tablerow[] = $data->name;
			$tablerow[] = $data->regrades;
			$tablerow[] = $data->justice_perception;
			$tablerow[] = $data->correction_time;
			$tabledata[] = $tablerow;
			$tablerow = array();
		}
	
		$table = new html_table();
		$table->head = $tablehead;
		$table->data = $tabledata;
		return html_writer::table($table);
	}
}
function emarking_time_progression_table($course){
	global $DB;
	// EMarking cycle
		$sqlemarking = "SELECT e.id AS id, e.name as name, eexam.timecreated AS printorder, eexam.printdate AS printdate, MIN(d.timecreated) AS digitalized,
							MIN(d.timecorrectionstarted) AS correctionstarted, MAX(d.timecorrectionended) AS corrected, MIN(d.timefirstpublished) AS firstpublished,
							MIN(d.timeregradingstarted) AS regradingstarted, MAX(d.timeregradingended) AS regraded, MAX(d.timelastpublished) AS lastpublished, 
							MAX(d.status) as maxstatus, MIN(d.status) as minstatus
							FROM mdl_emarking_exams AS eexam
                            INNER JOIN mdl_emarking AS e ON (e.id = eexam.emarking AND e.course= ?)
							LEFT JOIN mdl_emarking_draft AS d ON (e.id = d.emarkingid AND d.status != ?)
							GROUP BY e.id";
	// Gets the information of the above query.
	if ($emarkings = $DB->get_records_sql($sqlemarking,array($course, EMARKING_STATUS_ABSENT))) {
		// Headers for the table
		$emarkingarray[0] = ['','','','', get_string('dayshoursstrong', 'mod_emarking'),'','','',''];
		$emarkingarray[1] = [get_string('emarkingnamestrong', 'mod_emarking'),get_string('digitalized', 'mod_emarking'),
				get_string('incorrection', 'mod_emarking'),get_string('graded', 'mod_emarking'),
				get_string('published', 'mod_emarking'),get_string('inregrading', 'mod_emarking'),
				get_string('regraded', 'mod_emarking'),get_string('finalpublication', 'mod_emarking'),
				get_string('total', 'mod_emarking')];
		
		// 2 because the first 2 positions are taken by the headers.
		$position=2;
		
		// Variable used to full up empty array spaces.
		$emptycounter = 0 . get_string('cicletablehours', 'mod_emarking');
		
		foreach($emarkings as $emarking){
			
			// Definition of the time between statuses
			$timetoprint = floor(($emarking->printdate - $emarking->printorder)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
			if(floor(($emarking->printdate - $emarking->printorder)/ (60*60*24)) == 0){
				$timetoprint = round((($emarking->printdate - $emarking->printorder) - $timetoprint * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
			}
			
			$timeprinted = floor(($emarking->digitalized - $emarking->printdate)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
			if(floor(($emarking->digitalized - $emarking->printdate)/ (60*60*24)) == 0){
				$timeprinted = round((($emarking->digitalized - $emarking->printdate) - $timeprinted * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
			}
			
			$timedigitalized = floor(($emarking->correctionstarted - $emarking->digitalized)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
			if(floor(($emarking->correctionstarted - $emarking->digitalized)/ (60*60*24)) == 0){
				$timedigitalized = round((($emarking->correctionstarted - $emarking->digitalized) - $timedigitalized * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
			}
			
			$timecorrecting = floor(($emarking->corrected - $emarking->correctionstarted)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
			if(floor(($emarking->corrected - $emarking->correctionstarted)/ (60*60*24)) == 0){
				$timecorrecting = round((($emarking->corrected - $emarking->correctionstarted) - $timecorrecting * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
			}
			
			$timecorrected = floor(($emarking->firstpublished - $emarking->corrected)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
			if(floor(($emarking->firstpublished - $emarking->corrected)/ (60*60*24)) == 0){
				$timecorrected = round((($emarking->firstpublished - $emarking->corrected) - $timecorrected * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
			}
			
			$timepublished = floor(($emarking->regradingstarted - $emarking->firstpublished)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
			if(floor(($emarking->regradingstarted - $emarking->firstpublished)/ (60*60*24)) == 0){
				$timepublished = round((($emarking->regradingstarted - $emarking->firstpublished) - $timepublished * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
			}
			
			$timeregrading = floor(($emarking->regraded - $emarking->regradingstarted)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
			if(floor(($emarking->regraded - $emarking->regradingstarted)/ (60*60*24)) == 0){
				$timeregrading = round((($emarking->regraded - $emarking->regradingstarted) - $timeregrading * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
			}
			
			$timeregraded = floor(($emarking->lastpublished - $emarking->regraded)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
			if(floor(($emarking->lastpublished - $emarking->regraded)/ (60*60*24)) == 0){
				$timeregraded = round((($emarking->lastpublished - $emarking->regraded) - $timeregraded * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
			}
			
			// Saves time in a variable for easier convertion later
			$time = time();
	
			// Set the status of the emarking
			if($emarking->printdate == 0){
				$status = EMARKING_TO_PRINT;
				
			}elseif(is_null($emarking->digitalized) && $emarking->printdate !== 0){
				$status = EMARKING_PRINTED;
				
			}elseif(is_null($emarking->correctionstarted) && $emarking->maxstatus == EMARKING_STATUS_SUBMITTED || $emarking->minstatus == EMARKING_STATUS_SUBMITTED){
				$status = EMARKING_STATUS_SUBMITTED;
				
			}elseif(is_null($emarking->firstpublished) && $emarking->maxstatus == EMARKING_STATUS_GRADING){
				$status = EMARKING_STATUS_GRADING;
				
			}elseif(is_null($emarking->regradingstarted) && $emarking->maxstatus ==EMARKING_STATUS_PUBLISHED){
				$status = EMARKING_STATUS_PUBLISHED;
				
			}elseif($emarking->maxstatus ==EMARKING_STATUS_REGRADING){
				$status = EMARKING_STATUS_REGRADING;
				
			}elseif($emarking->firstpublished < $emarking->regradingstarted && $emarking->minstatus == EMARKING_STATUS_GRADING || $emarking->maxstatus == EMARKING_STATUS_REGRADING_RESPONDED){
				$status = EMARKING_STATUS_REGRADING_RESPONDED;
	
			}elseif($emarking->minstatus ==EMARKING_STATUS_PUBLISHED && $emarking->lastpublished > $emarking->regraded && $emarking->regraded !== null){
				$status = EMARKING_STATUS_FINAL_PUBLISHED;
				
			}
			//echo "publi: $emarking->firstpublished regra: $emarking->regradingstarted maxstatus: $emarking->maxstatus minstatus: $emarking->minstatus status: $status";
			switch ($status) {
				case EMARKING_TO_PRINT:
					
					$totaltime = floor(($time - $emarking->printorder)/ (60*60*24)) .get_string('cicletabledays', 'mod_emarking');
					$totaltime = $totaltime . round((($time - $emarking->printorder) - $totaltime * 60 * 60 * 24) / (60*60),1) .get_string('cicletablehours', 'mod_emarking');
					
					$timetoprint = floor(($time - $emarking->printorder)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
					if(floor(($time - $emarking->printorder)/ (60*60*24)) == 0){
						$timetoprint = round((($time - $emarking->printorder) - $timetoprint * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
					}

					$emarkingarray[$position]= array(
						$emarking->name,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$totaltime
					);
					$position++;
					break;
	
				case EMARKING_PRINTED:
					
					if(((time() - $emarking->printdate)/86400)>30){
						$time = ($emarking->printdate + 2592000);
					}
					
					$timeprinted = floor(($time - $emarking->printdate)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
					if(floor(($time - $emarking->printdate)/ (60*60*24)) == 0){
						$timeprinted = round((($time - $emarking->printdate) - $timeprinted * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
					}
					
					$totaltime = floor(($time - $emarking->printorder)/ (60*60*24)) .get_string('cicletabledays', 'mod_emarking');
					$totaltime = $totaltime . round((($time - $emarking->printorder) - $totaltime * 60 * 60 * 24) / (60*60),1) .get_string('cicletablehours', 'mod_emarking');
					
					
					$emarkingarray[$position]= array(
						$emarking->name,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$totaltime
					);
					$position++;
					break;
	
				case EMARKING_STATUS_SUBMITTED:
					
					if(((time() - $emarking->digitalized)/86400)>30){
						$time = ($emarking->digitalized + 2592000);
					}
					
					$timedigitalized = floor(($time - $emarking->digitalized)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
					if(floor(($time - $emarking->digitalized)/ (60*60*24)) == 0){
						$timedigitalized = round((($time - $emarking->digitalized) - $timedigitalized * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
					}
					
					$totaltime = floor(($time - $emarking->printorder)/ (60*60*24)) .get_string('cicletabledays', 'mod_emarking');
					$totaltime = $totaltime . round((($time - $emarking->printorder) - $totaltime * 60 * 60 * 24) / (60*60),1) .get_string('cicletablehours', 'mod_emarking');
					
					$emarkingarray[$position]= array(
						$emarking->name,
						$timedigitalized,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$totaltime
					);
					$position++;
					break;
	
				case EMARKING_STATUS_GRADING:
					
					if(((time() - $emarking->corrected)/86400)>15){
						$time = ($emarking->corrected + 1296000);
					}
					
					$timecorrecting = floor(($time - $emarking->correctionstarted)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
					if(floor(($time - $emarking->correctionstarted)/ (60*60*24)) == 0){
						$timecorrecting = round((($time - $emarking->correctionstarted) - $timecorrecting * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
					}
					
					$totaltime = floor(($time - $emarking->printorder)/ (60*60*24)) .get_string('cicletabledays', 'mod_emarking');
					$totaltime = $totaltime . round((($time - $emarking->printorder) - $totaltime * 60 * 60 * 24) / (60*60),1) .get_string('cicletablehours', 'mod_emarking');
					
					$emarkingarray[$position]= array(
						$emarking->name,
						$timedigitalized,
						$timecorrecting,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$totaltime
					);
					$position++;
					break;
	
				case EMARKING_STATUS_PUBLISHED:
					
					if(((time() - $emarking->firstpublished)/86400)>10){
						$time = ($emarking->firstpublished + 864000);
					}
					
					$timepublished = floor(($time - $emarking->firstpublished)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
					if(floor(($time - $emarking->firstpublished)/ (60*60*24)) == 0){
						$timepublished = round((($time - $emarking->firstpublished) - $timepublished * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
					}
					
					$totaltime = floor(($time - $emarking->printorder)/ (60*60*24)) .get_string('cicletabledays', 'mod_emarking');
					$totaltime = $totaltime . round((($time - $emarking->printorder) - $totaltime * 60 * 60 * 24) / (60*60),1) .get_string('cicletablehours', 'mod_emarking');
					
					$emarkingarray[$position]= array(
						$emarking->name,
						$timedigitalized,
						$timecorrecting,
						$timecorrected,
						$timepublished,
						$emptycounter,
						$emptycounter,
						$emptycounter,
						$totaltime
					);
					$position++;
					break;
	
				case EMARKING_STATUS_REGRADING:
					
					$timeregrading = floor(($time - $emarking->regradingstarted)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
					if(floor(($time - $emarking->regradingstarted)/ (60*60*24)) == 0){
						$timeregrading = round((($time - $emarking->regradingstarted) - $timeregrading * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
					}
					
					$totaltime = floor(($time - $emarking->printorder)/ (60*60*24)) .get_string('cicletabledays', 'mod_emarking');
					$totaltime = $totaltime . round((($time - $emarking->printorder) - $totaltime * 60 * 60 * 24) / (60*60),1) .get_string('cicletablehours', 'mod_emarking');
					
					$emarkingarray[$position]= array(
						$emarking->name,
						$timedigitalized,
						$timecorrecting,
						$timecorrected,
						$timepublished,
						$timeregrading,
						$emptycounter,
						$emptycounter,
						$totaltime
					);
					$position++;
					break;
	
				case EMARKING_STATUS_REGRADING_RESPONDED:
					
					if(((time() - $emarking->regraded)/86400)>5){
						$time = ($emarking->regraded + 432000);
					}
					$timeregraded = floor(($time - $emarking->regraded)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
					if(floor(($time - $emarking->regraded)/ (60*60*24)) == 0){
						$timeregraded = round((($time - $emarking->regraded) - $timeregraded * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
					}
					
					$totaltime = floor(($time - $emarking->printorder)/ (60*60*24)) .get_string('cicletabledays', 'mod_emarking');
					$totaltime = $totaltime . round((($time - $emarking->printorder) - $totaltime * 60 * 60 * 24) / (60*60),1) .get_string('cicletablehours', 'mod_emarking');
					
					$emarkingarray[$position]= array(
						$emarking->name,
						$timedigitalized,
						$timecorrecting,
						$timecorrected,
						$timepublished,
						$timeregrading,
						$timeregraded,
						$emptycounter,
						$totaltime
					);
					$position++;
					break;
	
				case EMARKING_STATUS_FINAL_PUBLISHED:
					
					if(((time() - $emarking->lastpublished)/86400)>5){
						$time = ($emarking->lastpublished + 432000);
					}
					
					$totaltime = floor(($time - $emarking->printorder)/ (60*60*24)) .get_string('cicletabledays', 'mod_emarking');
					$totaltime = $totaltime . round((($time - $emarking->printorder) - $totaltime * 60 * 60 * 24) / (60*60),1) .get_string('cicletablehours', 'mod_emarking');
					
					$timefinalpublished = floor(($time - $emarking->lastpublished)/ (60*60*24)).get_string('cicletabledays', 'mod_emarking');
					if(floor(($time - $emarking->lastpublished)/ (60*60*24)) == 0){
						$timefinalpublished = round((($time - $emarking->lastpublished) - $timefinalpublished * 60 * 60 * 24) / (60*60),1).get_string('cicletablehours', 'mod_emarking');
					}
					
					$emarkingarray[$position]= array(
					$emarking->name,
					$timedigitalized,
					$timecorrecting,
					$timecorrected,
					$timepublished,
					$timeregrading,
					$timeregraded,
					$timefinalpublished,
					$totaltime,
					);
					$position++;
					break;
			}
			$status = null;
		}
		return $emarkingarray;
	}else{
		return 0;
	}
}
function emarking_area_chart_tooltip($datacount){
	$tooltip = get_string('strongdate', 'mod_emarking')." : <strong>".$datacount[0]."</strong><br>".
			   get_string('digitalized', 'mod_emarking')." : ".$datacount[1]."<br>".
			   get_string('incorrection', 'mod_emarking')." : ".$datacount[3]."<br>".
			   get_string('graded', 'mod_emarking')." : ".$datacount[5]."<br>".
			   get_string('published', 'mod_emarking')." : ".$datacount[7]."<br>".
			   get_string('inregrading', 'mod_emarking')." : ".$datacount[9]."<br>".
			   get_string('regraded', 'mod_emarking')." : ".$datacount[11]."<br>".
			   get_string('finalpublication', 'mod_emarking')." : ".$datacount[13];
	
	return $tooltip;
}
function emarking_markers_chart_tooltip($markers, $corrections){
	$corrections = array_values($corrections);
	$lenght = count($corrections);
	$tooltip = get_string('strongdate', 'mod_emarking')." : <strong>".$corrections[0]."</strong><br>";
	$countcorrections = 1;
	foreach($markers as $marker){
		$tooltip .= $marker->name." : ".$corrections[$countcorrections]."<br>";
		$countcorrections = $countcorrections + 2;
	}
	return $tooltip;	
}