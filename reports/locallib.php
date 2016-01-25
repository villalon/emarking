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
 * @param unknown $divid
 * @param array $labels
 * @param array $data
 * @param unknown $title
 * @param string $xtitle
 * @param string $ytitle
 * @return multitype:string
 */
function emarking_get_google_chart($divid, array $labels, array $data, $title, $xtitle = NULL, $ytitle = NULL)
{
    // DIV for displaying
    $html = '<div id="'.$divid.'" style="width: 100%; height: 500px;"></div>';
    
    // Headers
    $labelsjs = "['".implode("', '", $labels)."']";
    
    // Data JS
    $datajs = "";
    for($i=0; $i<count($data); $i++) {
        $datajs .= "[";
        for($j=0;$j<count($data[$i]); $j++) {
            $datacell = $data[$i][$j];
            if($j == 0) {
                $datacell = "'".$datacell."'";
            }
            if($j<count($data[$i])-1) {
                $datacell = $datacell . ",";
            }
            $datajs .= $datacell;
        }
        $datajs .= "],";
    }
    
    // The required JS to display the chart
    $js = "
        google.setOnLoadCallback(drawChart$divid);

        // Chart function for $divid
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
    
    return array($html, $js);
}

/**
 * Navigation tabs for reports
 *
 * @param unknown $category
 *            The category object
 * @return multitype:tabobject array of tabobjects
 */
function emarking_reports_tabs($category)
{
    $tabs = array();

    // Statistics
    $statstab = new tabobject("statistics", new moodle_url("/mod/emarking/reports/print.php", array(
        "category" => $category->id
    )), get_string("statistics", 'mod_emarking'));

    // Print statistics
    $statstab->subtree[] = new tabobject("printstatistics", new moodle_url("/mod/emarking/reports/print.php", array(
        "category" => $category->id
    )), get_string("statistics", 'mod_emarking'));

    // Print statistics
    $statstab->subtree[] = new tabobject("printdetails", new moodle_url("/mod/emarking/reports/printdetails.php", array(
        "category" => $category->id
    )), get_string("printdetails", 'mod_emarking'));

    $tabs[] = $statstab;
    return $tabs;
}

function emarking_buttonstable($categoryid){
	$buttonsarray = array();
	
	// Creation of the activities button
	$activities = emarking_getActivities($categoryid);
	$activitiesbutton = html_writer::tag('button',$activities." ".get_string('totalactivies', 'emarking'), array('id' => 'activitiesbutton', 'class' => 'emarking-cost-button-style'));
	$buttonsarray[] = $activitiesbutton;
	
	// Creation of the emarkingcourses button
	$emarkingcourses = emarking_getemarkingcourses($categoryid);
	$emarkingcoursesbutton = html_writer::tag('button',$emarkingcourses." ".get_string('emarkingcourses', 'emarking'), array('id' => 'emarkingcourses', 'class' => 'emarking-cost-button-style'));
	$buttonsarray[] = $emarkingcoursesbutton;
	
	// Creation of the meanlengh button
	$originalpages = emarking_getoriginalpages($categoryid);
	$meantestlenghbutton = html_writer::tag('button',$originalpages." ".get_string('meantestlenght', 'emarking')  , array('id' => 'meantestleangh',  'class' => 'emarking-cost-button-style'));
	$buttonsarray[] = $meantestlenghbutton;
	
	// Creation of the totalprintedpages button
	$totalprintedpages = emarking_gettotalpages($categoryid);
	$totalprintedpagesbutton = html_writer::tag('button',$totalprintedpages." ".get_string('totalprintedpages', 'emarking') , array('id' => 'totalprintedpages', 'class' => 'emarking-cost-button-style'));
	$buttonsarray[] = $totalprintedpagesbutton;
	
	// Creation of the total cost.
	$printingcost =  emarking_getprintingcost($categoryid);
	$formatcost = number_format($printingcost);
	$totalprintingcostbutton = html_writer::tag('button','$'." ".$formatcost." ".get_string('totalprintingcost', 'emarking') , array('id' => 'totalprintingcost', 'class' =>'emarking-totalcost-button-style emarking-cost-button-style'));
	$buttonsarray[] = $totalprintingcostbutton;
	
	return $buttonsarray;
}

function emarking_piebuttonstable($categoryid){
	$buttonsarray = array();

	// Creation of the activities button
	$activitiesforchart = json_encode(emarking_getActivitiesbydate($categoryid));
	$activitiesbutton = html_writer::tag('button',get_string('pieactivities', 'emarking'), array('id' => 'pieactivitiesbutton', 'class' => 'emarking-pie-cost-button-style', ));
	$buttonsarray[] = $activitiesbutton;

	// Creation of the emarkingcourses button
	$emarkingcoursesbutton = html_writer::tag('button',get_string('pieemarkingcourses', 'emarking'), array('id' => 'pieemarkingcourses', 'class' => 'emarking-pie-cost-button-style'));
	$buttonsarray[] = $emarkingcoursesbutton;

	// Creation of the meanlengh button
	$meantestlenghbutton = html_writer::tag('button',get_string('piemeanexamleanght', 'emarking')  , array('id' => 'piemeantestleangh',  'class' => 'emarking-pie-cost-button-style'));
	$buttonsarray[] = $meantestlenghbutton;

	// Creation of the totalprintedpages button
	$totalprintedpagesbutton = html_writer::tag('button',get_string('pietotalprintedpages', 'emarking') , array('id' => 'pietotalprintedpages', 'class' => 'emarking-pie-cost-button-style'));
	$buttonsarray[] = $totalprintedpagesbutton;

	// Creation of the total cost.
	$totalprintingcostbutton = html_writer::tag('button',get_string('pietotalcost', 'emarking') , array('id' => 'pietotalprintingcost', 'class' =>'emarking-pie-totalcost-button-style emarking-pie-cost-button-style'));
	$buttonsarray[] = $totalprintingcostbutton;

	return $buttonsarray;
}

function emarking_getcategorycosttabledata($category){
	global  $DB, $OUTPUT;
	// Gets the information of the above query
	$sqlactivities = "SELECT cc.id as id, cc.name as name, ecc.printingcost as printingcost, ecc.costcenter as costcenter
					  FROM {course_categories} AS cc
					  LEFT JOIN {emarking_category_cost} AS ecc ON (ecc.category = cc.id)";
	
	if($categorycost= $DB->get_records_sql($sqlactivities)){
		
		$arraycategorycost=array();
		
		foreach($categorycost as $categorycostdata){
			
			$arraycategorycost[$categorycostdata->id][0] = $categorycostdata->name;
		
			if(!$categorycostdata->costcenter == NULL){
				$arraycategorycost[$categorycostdata->id][1] = $categorycostdata->printingcost;
				$arraycategorycost[$categorycostdata->id][2] = $categorycostdata->costcenter;
				
			} else {
				$arraycategorycost[$categorycostdata->id][1] = 'NULL';
				$arraycategorycost[$categorycostdata->id][2] = 'NULL';
			}
			$editicon_table = new pix_icon("t/editstring", "edit");
			$arraycategorycost[$categorycostdata->id][3] = $deleteaction_boleta = $OUTPUT->action_icon(
					new moodle_url("/mod/emarking/reports/costconfig.php", array(
							"category" =>$categorycostdata->id
					)),
					$editicon_table);
			
			
		}
	}
	
	return $arraycategorycost;
	
	}

function emarking_getActivities($category) {
	global  $DB;
	$activitiesparams = array(
			"%/$category/%",
			$category
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqlactivities = "SELECT count(e.id) AS activities
							   FROM {emarking} AS e
   							   INNER JOIN {emarking_exams} AS eexam ON (e.id = eexam.emarking)
							   INNER JOIN {course} AS c ON (c.id = eexam.course)
							   INNER JOIN {course_categories} as cc ON (cc.id = c.category)
						       WHERE (cc.path like ? OR cc.id = ?)";
	// Gets the information of the above query
	if($activities= $DB->get_records_sql($sqlactivities, $activitiesparams)){
		foreach($activities as $activity){
			$totalactivity = $activity->activities;
		}
	} else {
	$totalactivity = 0;
	}

	return $totalactivity;
}

function emarking_getteacherranking($category) {
	global  $DB;
	$teacherrankingparams = array(
			"%/$category/%",
			$category,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqlteacherranking = "SELECT u.id AS id, u.firstname AS firstname, u.lastname AS lastname, count(e.id) AS activities
								   FROM {emarking} AS e
  								   INNER JOIN {emarking_exams} AS eexam ON (e.id = eexam.emarking)
							  	   INNER JOIN {user} AS u ON (u.id = eexam.requestedby)
								   INNER JOIN {course} AS c ON (c.id = eexam.course)
								   INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
								   WHERE (cc.path like ? OR cc.id = ?)
    							   GROUP BY id
   								   ORDER BY activities DESC
    							   limit 5";
	// Gets the information of the above query
	$arrayteacherranking=array();
	if($teacherranking = $DB->get_records_sql($sqlteacherranking, $teacherrankingparams)){
		foreach($teacherranking as $teachersrankings){
			$arrayteacherranking[$teachersrankings->id][] = $teachersrankings->firstname." ".$teachersrankings->lastname;
			$arrayteacherranking[$teachersrankings->id][] = $teachersrankings->activities;
		}
	}

	return $arrayteacherranking;
}

function emarking_getoriginalpagesbycourse($category) {
	global  $DB;
	$originalpagesbycourseparams = array(
			"%/$category/%",
			$category,
			'EMARKING_EXAM_PRINTED',
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqloriginalpagesbycourse = "SELECT c.id as id, c.fullname as coursename, SUM(eexam.totalpages+eexam.extrasheets) AS pages
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?)
                               GROUP BY c.id
                               ORDER BY pages DESC
                               limit 5";
	// Gets the information of the above query
	$originalpagesbycourse = $DB->get_records_sql($sqloriginalpagesbycourse, $originalpagesbycourseparams);

	return $originalpagesbycourse;
}

function emarking_getoriginalpages($category) {
	global  $DB;
	$originalpagesparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqloriginalpages = "SELECT AVG((eexam.totalpages+eexam.extrasheets)) AS pages
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?)";
	// Gets the information of the above query
	if($originalpages = $DB->get_records_sql($sqloriginalpages, $originalpagesparams)){
		foreach($originalpages as $pages){
			if(!$pages->pages == NULL){
				$totaloriginalpages = round((int)$pages->pages);
			}
			else{
				$totaloriginalpages = 0;
			}
		}
	}

	return $totaloriginalpages;
}

function emarking_gettotalpagesbycourse($category) {
	global  $DB;
	$totalpagesbycourseparams = array(
			EMARKING_EXAM_PRINTED,
			"%/$category/%",
			$category
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqltotalpagesbycourse = "SELECT courseid, coursename, SUM(pages) AS totalpages FROM (SELECT c.id AS courseid, c.fullname AS coursename, eexam.id AS examid, ((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE eexam.status IN (?) AND (cc.path like ? OR cc.id = ?)
                               GROUP BY eexam.id
                               ORDER BY pages DESC) AS pagestotal
                               GROUP BY courseid
                               ORDER BY totalpages DESC
							   LIMIT 5";
	// Gets the information of the above query
	$arraytotalpages=array();
	if($totalpagesbycourse = $DB->get_records_sql($sqltotalpagesbycourse, $totalpagesbycourseparams)){
		foreach($totalpagesbycourse as $pagesbycourse){
			if(!$pagesbycourse->totalpages == NULL){
				$arraytotalpages[$pagesbycourse->courseid][] = $pagesbycourse->coursename;
				$arraytotalpages[$pagesbycourse->courseid][] = $pagesbycourse->totalpages;
			}
		}
	}
	return $arraytotalpages;
}

function emarking_gettotalpages($category) {
	global  $DB;
	$pageparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqlpage = "SELECT SUM(pages) AS totalpages FROM (SELECT c.id AS courseid, eexam.id AS examid, (eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams) AS pages
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status = ?
                               GROUP BY eexam.id
                               ORDER BY pages DESC) AS pagestotal";
	// Gets the information of the above query

	if($page = $DB->get_records_sql($sqlpage, $pageparams)){
		foreach($page as $pages){
			if(!$pages->totalpages == NULL){
				$totalpages = $pages->totalpages;
			} else{
				$totalpages = 0;
			}
		}
	}

	return $totalpages;
}

function emarking_getemarkingcourses($category) {
	global  $DB;
	$emarkingcoursesparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqlemarkingcourses = "SELECT COUNT(course) AS courses FROM(SELECT e.course AS course
							   FROM mdl_emarking AS e
							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = e.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) 
                               GROUP BY e.course) as courses";
	// Gets the information of the above query
	if($emarkingcourses = $DB->get_records_sql($sqlemarkingcourses, $emarkingcoursesparams)){
		foreach($emarkingcourses as $courses){
			$totalemarkingcourses = $courses->courses;
		}
	}

	return $totalemarkingcourses;
}

function emarking_getstudents($category) {
	global  $DB;
	$studentsparams = array(
			'5',
			"%/$category/%",
			$category
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqlstudents = "SELECT count(u.id) AS user
					FROM mdl_user AS u
					INNER JOIN mdl_role_assignments AS ra ON (ra.userid = u.id)
					INNER JOIN mdl_context AS ct ON (ct.id = ra.contextid)
					INNER JOIN mdl_course AS c ON (c.id = ct.instanceid)
					INNER JOIN mdl_role AS r ON (r.id = ra.roleid)
					INNER JOIN mdl_course_categories AS cc ON (cc.id = c.category)
					WHERE ra.roleid=? AND (cc.path like ? OR cc.id = ?)
	";
	// Gets the information of the above query
	if($students = $DB->get_records_sql($sqlstudents, $studentsparams));{

		$arraystudents=array();
		foreach($students as $student){
			$arraystudents[] = $student->user;
		}
	}
	return $arraystudents;
}

function emarking_gettotalpagestoprint($category) {
	global  $DB;
	$totalpagestoprintparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_SENT_TO_PRINT

	);
	// Sql that counts all the resourses since the last time the app was used
	$sqltotalpagestoprint = "SELECT SUM(pages) AS totalpages FROM (
								SELECT c.id AS courseid, eexam.id AS examid, (eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams) AS pages
							    FROM mdl_emarking AS e
   							    INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                                INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                                INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							    WHERE (cc.path like ? OR cc.id = ?) AND eexam.status = ?
                                GROUP BY eexam.id
                                ORDER BY pages DESC
							 ) AS pagestotal
                             ORDER BY totalpages DESC";
	// Gets the information of the above query


	if($totalpagestoprint = $DB->get_records_sql($sqltotalpagestoprint, $totalpagestoprintparams)){
		$arraypagestoprint=array();
		foreach($totalpagestoprint as $pagestoprint) {
			$arraypagestoprint[] = $pagestoprint->totalpages;
		}
	}

	return $arraypagestoprint;
}

function emarking_getActivitiesbydate($category) {
	global  $DB;
	$activitiesbydateparams = array(
			"%/$category/%",
			$category
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqlactivitiesbydate = "SELECT idexam, Count(id) as activities, printdate FROM (
					  	SELECT eexam.id as idexam, e.id as id, MONTH(FROM_UNIXTIME(eexam.printdate)) as printdate
					  	FROM mdl_emarking AS e
   					  	INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
					    INNER JOIN mdl_course AS c ON (c.id = eexam.course)
					    INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
					    WHERE (cc.path like ? OR cc.id = ?)
					  ) AS months
                      GROUP BY printdate
                      ORDER BY printdate ASC";
	// Gets the information of the above query
	$arrayactivitiesbydate=array();
	if($activitiesbydate= $DB->get_records_sql($sqlactivitiesbydate, $activitiesbydateparams)){

		$arrayactivitiesbydate[0]=['Month','Activities'];
		for($contadormes = 1; $contadormes <= 12; $contadormes++){
			if(!isset($arrayactivitiesbydate[$contadormes])){
				$arrayactivitiesbydate[$contadormes] = [date("F", mktime(0, 0, 0, $contadormes, 10)),0];
			}
		}
		foreach($activitiesbydate as $activitys){
			$arrayactivitiesbydate[$activitys->printdate][0] = date("F", mktime(0, 0, 0, $activitys->printdate, 10));
			$arrayactivitiesbydate[$activitys->printdate][1] = (int)$activitys->activities;

		}

	} else {
		$arrayactivitiesbydate=[['nodata','nodata'],[0,0],[0,0]];

	}
	return $arrayactivitiesbydate;
}

function emarking_getemarkingcoursesbydate($category) {
	global  $DB;
	$emarkingcoursesbydateparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqlemarkingcoursesbydate = "SELECT COUNT(course) AS coursecount, printdate FROM (SELECT e.course AS course, MONTH(FROM_UNIXTIME(MIN(eexam.printdate))) as printdate
							   FROM mdl_emarking AS e
							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = e.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?)
                               GROUP BY course
                               ORDER BY printdate desc) as m
                               GROUP BY printdate";
	// Gets the information of the above query
	if($emarkingcoursesbydate = $DB->get_records_sql($sqlemarkingcoursesbydate, $emarkingcoursesbydateparams)){
		$arrayemarkingcoursesbydate=array();
		$arrayemarkingcoursesbydate[0]=['Month','Courses with emarking'];
		for($contadormes = 1; $contadormes <= 12; $contadormes++){
			if(!isset($arrayemarkingcoursesbydate[$contadormes])){
				$arrayemarkingcoursesbydate[$contadormes] = [date("F", mktime(0, 0, 0, $contadormes, 10)),0];
			}
		}
		foreach($emarkingcoursesbydate as $coursesbydate){
			$arrayemarkingcoursesbydate[$coursesbydate->printdate][0] = date("F", mktime(0, 0, 0, $coursesbydate->printdate, 10));
			$arrayemarkingcoursesbydate[$coursesbydate->printdate][1] = (int)$coursesbydate->coursecount;

		}
	} else {
		$arrayemarkingcoursesbydate=[['nodata','nodata'],[0,0],[0,0]];

	}

	return $arrayemarkingcoursesbydate;
}

function emarking_getoriginalpagesbydate($category) {
	global  $DB;
	$totaloriginalpagesbydateparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqloriginalpagesbydate = "SELECT printdate, AVG(pages) AS avgpages FROM (SELECT eexam.id as id, MONTH(FROM_UNIXTIME(eexam.printdate)) as printdate,(eexam.totalpages+eexam.extrasheets) AS pages
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?)
							   GROUP BY id ) as pages
                               GROUP BY printdate";
	// Gets the information of the above query

	if($originalpagesbydate = $DB->get_records_sql($sqloriginalpagesbydate, $totaloriginalpagesbydateparams)){
		$arrayoriginalpagesbydate=array();
		$arrayoriginalpagesbydate[0]=['Month','Mean lenght of tests'];
		for($contadormes = 1; $contadormes <= 12; $contadormes++){
			if(!isset($arrayoriginalpagesbydate[$contadormes])){
				$arrayoriginalpagesbydate[$contadormes] = [date("F", mktime(0, 0, 0, $contadormes, 10)),0];
			}
		}
		foreach($originalpagesbydate as $pagesbydate){

				$arrayoriginalpagesbydate[$pagesbydate->printdate][0] = date("F", mktime(0, 0, 0, $pagesbydate->printdate, 10));
				$arrayoriginalpagesbydate[$pagesbydate->printdate][1] = round((int)$pagesbydate->avgpages);
			}
		} else {
		$arrayoriginalpagesbydate=[['nodata','nodata'],[0,0],[0,0]];
	}

	return $arrayoriginalpagesbydate;
}

function emarking_gettotalpagesbydate($category) {
	global  $DB;
	$totalpagesbydateparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqltotalpagesbydate = "SELECT printdate, SUM(pages) AS totalpages FROM (SELECT c.id AS courseid, eexam.id AS examid,MONTH(FROM_UNIXTIME(eexam.printdate)) as printdate, (eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams) AS pages
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?)
                               GROUP BY eexam.id
                               ORDER BY pages DESC) AS pagestotal
                               GROUP BY printdate";
	// Gets the information of the above query
	if($totalpagesbydate = $DB->get_records_sql($sqltotalpagesbydate, $totalpagesbydateparams)){
		$arraytotalpagesbydate=array();
		$arraytotalpagesbydate[0]=['Month','Total pages'];
		for($contadormes = 1; $contadormes <= 12; $contadormes++){
			if(!isset($arraytotalpagesbydate[$contadormes])){
				$arraytotalpagesbydate[$contadormes] = [date("F", mktime(0, 0, 0, $contadormes, 10)),0];
			}
		}
		foreach($totalpagesbydate as $pagesbydate){
			$arraytotalpagesbydate[$pagesbydate->printdate][0] = date("F", mktime(0, 0, 0, $pagesbydate->printdate, 10));
			$arraytotalpagesbydate[$pagesbydate->printdate][1] = (int)$pagesbydate->totalpages;
		}
	}

	return $arraytotalpagesbydate;
}

function emarking_gettotalpagesfortable($category) {
	global  $DB, $CFG;
	$printingcost= emarking_getprintingcost($category);
	$totalpagesbydateparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqltotalpagesbydate = "SELECT printdate, SUM(pages) AS totalcost FROM (SELECT c.id AS courseid, eexam.id AS examid,MONTH(FROM_UNIXTIME(eexam.printdate)) as printdate, eexam.printingcost*((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?)
                               GROUP BY eexam.id
                               ORDER BY pages DESC) AS pagestotal
                               GROUP BY printdate";
	// Gets the information of the above query
	if($totalpagesbydate = $DB->get_records_sql($sqltotalpagesbydate, $totalpagesbydateparams)){
		$arraytotalpagesbydate=array();
		for($contadormes = 1; $contadormes <= 12; $contadormes++){
			if(!isset($arraytotalpagesbydate[$contadormes])){
				$arraytotalpagesbydate[$contadormes] = [date("F", mktime(0, 0, 0, $contadormes, 10)),'$'." "."0"];
			}
		}
		foreach($totalpagesbydate as $costbydate){
			$arraytotalpagesbydate[$costbydate->printdate][0] = date("F", mktime(0, 0, 0, $costbydate->printdate, 10));
			$arraytotalpagesbydate[$costbydate->printdate][1] = '$'." ".number_format((int)$costbydate->totalcost);
		}

	} else {
		$arraytotalpagesbydate = ['0'];
	}

	return $arraytotalpagesbydate;
}

function emarking_getsubcategories($category) {
	global  $DB;
	$subcategoriesparams = array(
			"%/$category/%"
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqlsubcategories = "SELECT id, name
							FROM mdl_course_categories AS cc
							WHERE cc.path like ?
                               ";
	// Gets the information of the above query
	if($subcategories = $DB->get_records_sql($sqlsubcategories, $subcategoriesparams)){

	}

	return $subcategories;
}

function emarking_gettotalcostpiechart($category) {
	global  $DB;
	$totalcostpiechartparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqltotalcostpiechart = "SELECT categoryid, categoryname, SUM(pages) AS totalpages FROM (SELECT c.id AS courseid, eexam.id AS examid, cc.id as categoryid, cc.name as categoryname, eexam.printingcost*((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?)
                               GROUP BY eexam.id
                               ORDER BY pages DESC) AS pagestotal
                               GROUP BY categoryid";
	// Gets the information of the above query
	$arraytotalcost = array();
	$i=1;
	if($totalcostpiechart = $DB->get_records_sql($sqltotalcostpiechart, $totalcostpiechartparams)){
		$arraytotalcost[0] = ['Category name','Total cost'];
		foreach($totalcostpiechart as $cost){
			if(!$cost->totalpages == NULL){
				$arraytotalcost[$i][0] = $cost->categoryname;
				$arraytotalcost[$i][1] = (int)$cost->totalpages;
			} else{
				$arraytotalcost[$i][0] = $cost->categoryname;
				$arraytotalcost[$i][1] = 0;
			}
			$i++;
		}
	}

	return $arraytotalcost;
}

function emarking_gettotalpagespiechart($category) {
	global  $DB;
	$totalpagespiechartparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqltotalpagespiechart = "SELECT categoryid, categoryname, SUM(pages) AS totalpages FROM (SELECT c.id AS courseid, eexam.id AS examid, cc.id as categoryid, cc.name as categoryname, ((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?)
                               GROUP BY eexam.id
                               ORDER BY pages DESC) AS pagestotal
                               GROUP BY categoryid";
	// Gets the information of the above query
	$arraytotalpages = array();
	$i=1;
	if($totalpagespiechart = $DB->get_records_sql($sqltotalpagespiechart, $totalpagespiechartparams)){
		$arraytotalpages[0] = ['Category name','Total pages'];
		foreach($totalpagespiechart as $pages){
			if(!$pages->totalpages == NULL){
				$arraytotalpages[$i][0] = $pages->categoryname;
				$arraytotalpages[$i][1] = (int)$pages->totalpages;
			} else{
				$arraytotalpages[$i][0] = $pages->categoryname;
				$arraytotalpages[$i][1] = 0;
			}
			$i++;
		}
	}

	return $arraytotalpages;
}

function emarking_getactivitiespiechart($category) {
	global  $DB;
	$activitiespiechartparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqlactivitiespiechart = "SELECT cc.id as id, cc.name as name, count(e.id) AS activities
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) 
                               GROUP BY id";
	// Gets the information of the above query
	$arrayactivities = array();
	$i=1;
	if($activitiespiechart = $DB->get_records_sql($sqlactivitiespiechart, $activitiespiechartparams)){
		$arrayactivities[0] = ['Category name','Activities'];
		foreach($activitiespiechart as $activities){
			if(!$activities->activities == NULL){
				$arrayactivities[$i][0] = $activities->name;
				$arrayactivities[$i][1] = (int)$activities->activities;
			} else{
				$arrayactivities[$i][0] = $activities->name;
				$arrayactivities[$i][1] = 0;
			}
			$i++;
		}
	}

	return $arrayactivities;
}

function emarking_getemarkingcoursespiechart($category) {
	global  $DB;
	$emarkingcoursespiechartparams = array(
			"%/$category/%",
			$category,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqlemarkingcoursespiechart = "SELECT id, name, COUNT(course) as countcourses FROM (SELECT e.course AS course, cc.name as name, cc.id as id 
							   FROM mdl_emarking AS e
							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = e.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?)
                               GROUP BY course
                               ORDER BY name desc) as m
                               GROUP BY id";
	// Gets the information of the above query
	$arrayemarkingcourses = array();
	$i=1;
	if($emarkingcoursespiechart = $DB->get_records_sql($sqlemarkingcoursespiechart, $emarkingcoursespiechartparams)){
		$arrayemarkingcourses[0] = ['Category name','eMarking courses'];
		foreach($emarkingcoursespiechart as $courses){
			if(!$courses->countcourses == NULL){
				$arrayemarkingcourses[$i][0] = $courses->name;
				$arrayemarkingcourses[$i][1] = (int)$courses->countcourses;
			} else{
				$arrayemarkingcourses[$i][0] = $courses->name;
				$arrayemarkingcourses[$i][1] = 0;
			}
			$i++;
		}
	}

	return $arrayemarkingcourses;
}

function emarking_getmeanexamlenghtpiechart($category) {
	global  $DB;
	$meanexamlenghtpiechartparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqlmeanexamlenghtpiechart = "SELECT cc.id as id, cc.name as name, AVG((eexam.totalpages+eexam.extrasheets)) AS pages
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?)
							   GROUP BY id";
	// Gets the information of the above query
	$arraymeanlenght = array();
	$i=1;
	$activities = emarking_getactivitiespiechart($category);
	if($meanexamlenghtpiechart = $DB->get_records_sql($sqlmeanexamlenghtpiechart, $meanexamlenghtpiechartparams)){
		$arraymeanlenght[0] = ['Category name','exam mean lenght'];
		foreach($meanexamlenghtpiechart as $lenght){
			if(!$lenght->pages == NULL){
				$arraymeanlenght[$i][0] = $lenght->name;
				$arraymeanlenght[$i][1] = round((int)$lenght->pages);
			} else{
				$arraymeanlenght[$i][0] = $lenght->name;
				$arraymeanlenght[$i][1] = 0;
			}
			$i++;
		}
	}

	return $arraymeanlenght;
}

function emarking_getprintingcost($category) {
	global  $DB;
	$totalpritningcost = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqlprintingcost = "SELECT SUM(pages) as totalcost FROM (SELECT c.id AS courseid, eexam.id AS examid, eexam.printingcost*((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?)
                               GROUP BY eexam.id
                               ORDER BY pages DESC) AS pagestotal";

	// Gets the information of the above query
	$arrayprintingcost = array();
	if($printingcost = $DB->get_records_sql($sqlprintingcost, $totalpritningcost)){
		foreach($printingcost as $cost){
			$totalprintingcost = $cost->totalcost;
		}
	}

	return $totalprintingcost;
}

function emarking_gettotalcostbydate($category) {
	global  $DB;
	$totalcostbydateparams = array(
			"%/$category/%",
			$category,
			EMARKING_EXAM_PRINTED,
	);
	// Sql that counts all the resourses since the last time the app was used
	$sqltotalcostbydate = "SELECT printdate, SUM(pages) AS totalcost FROM (SELECT c.id AS courseid, eexam.id AS examid,MONTH(FROM_UNIXTIME(eexam.printdate)) as printdate, eexam.printingcost*((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
							   FROM mdl_emarking AS e
   							   INNER JOIN mdl_emarking_exams AS eexam ON (e.id = eexam.emarking)
                               INNER JOIN mdl_course AS c ON (c.id = eexam.course)
                               INNER JOIN mdl_course_categories as cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?)
                               GROUP BY eexam.id
                               ORDER BY pages DESC) AS pagestotal
                               GROUP BY printdate
                               ";
	// Gets the information of the above query
	if($totalcostbydate = $DB->get_records_sql($sqltotalcostbydate, $totalcostbydateparams)){
		$arraytotalcostbydate=array();
		$arraytotalcostbydate[0]=['Month','Total cost'];
		for($contadormes = 1; $contadormes <= 12; $contadormes++){
			if(!isset($arraytotalcostbydate[$contadormes])){
				$arraytotalcostbydate[$contadormes] = [date("F", mktime(0, 0, 0, $contadormes, 10)),0];
			}
		}
		foreach($totalcostbydate as $costbydate){
			$arraytotalcostbydate[$costbydate->printdate][0] = date("F", mktime(0, 0, 0, $costbydate->printdate, 10));
			$arraytotalcostbydate[$costbydate->printdate][1] = (int)$costbydate->totalcost;
		}
	}

	return $arraytotalcostbydate;
}
