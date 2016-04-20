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
 * @copyright 2016 Mihail Pozarski <mipozarski@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once($CFG->dirroot . "/mod/emarking/reports/locallib.php");
require_once($CFG->dirroot . "/mod/emarking/marking/locallib.php");
require_once($CFG->dirroot . '/lib/excellib.class.php');
require_once($CFG->dirroot . '/mod/emarking/reports/forms/cost_form.php');

global $DB, $CFG;
$categoryid = required_param('category', PARAM_INT);
$status = optional_param("status", 0, PARAM_INT);
// User must be logged in.
require_login();
if (isguestuser()) {
    die();
}
// Validate category.
if (! $category = $DB->get_record('course_categories', array(
    'id' => $categoryid))) {
    print_error(get_string('invalidcategoryid', 'mod_emarking'));
}

// We are in the category context.
$context = context_coursecat::instance($categoryid);

// And have viewcostreport capability.
if (! has_capability('mod/emarking:viewcostreport', $context)) {
    // TODO: Log invalid access to printreport.
    print_error(get_string("notallowed", "mod_emarking"));
}

// This page url.
$url = new moodle_url('/mod/emarking/reports/costcenter.php', array(
    'category' => $categoryid));
// Url that lead you to the category page.
$categoryurl = new moodle_url('/course/index.php', array(
    'categoryid' => $categoryid));

$pagetitle = get_string('costreport', 'mod_emarking');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add($category->name, $categoryurl);
$PAGE->navbar->add(get_string('printorders', 'mod_emarking'), $url);
$PAGE->navbar->add($pagetitle);
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));

if(emarking_get_activities($categoryid) == 0){
	print_error(get_string('nocostdata', 'mod_emarking'));	
}
//years or months in data
$yearormonth = emarking_years_or_months($categoryid);
$isyears = $yearormonth[0];
// Area chart data.
if($isyears == 1){
	$date = "YEAR";
} else{
	$date = "MONTH";
}

$parentcategory = $DB->get_record('course_categories', array('id' => $categoryid));
// Add the emarking cost form for categories.
$adduppercategoryform = new emarking_uppercategory_form(null,array(
		"category" => $categoryid
));

// If the form is cancelled redirects you to the report center.
if ($datas = $adduppercategoryform->get_data()) {
	// Redirect to the table with all the category costs.
	redirect(new moodle_url("/mod/emarking/reports/costcenter.php", array(
			"category" => $categoryid
	)));
}
$subcategories = emarking_get_subcategories($categoryid);
if(isset($subcategories)){
	// Add the emarking cost form for categories.
	$addsubcategoryform = new emarking_subcategory_form(null,array(
			"category" => $categoryid
	));
	// If the form is cancelled redirects you to the report center.
	if ($datas = $addsubcategoryform->get_data()) {
		// Redirect to the table with all the category costs.
		redirect(new moodle_url("/mod/emarking/reports/costcenter.php", array(
				"category" => $categoryid
		)));
	}
}
$activitiesmain = emarking_get_query(array("%/$categoryid/%",$categoryid),
		"idexam, COUNT(id) AS activities, printdate",
		"eexam.id AS idexam,e.id AS id,".$date."(FROM_UNIXTIME(eexam.printdate)) AS printdate",
		"(cc.path like ? OR cc.id = ?)",
		null, null, null,
		"printdate",
		"printdate ASC");
$activitiesmainchart = json_encode(emarking_array_by_date($isyears, $activitiesmain, get_string('activities', 'mod_emarking'), "printdate", "activities"));
$emarkingcoursesmain = emarking_get_query(array("%/$categoryid/%", $categoryid, EMARKING_EXAM_PRINTED, EMARKING_EXAM_SENT_TO_PRINT),
		"printdate, COUNT(course) AS coursecount",
		"e.course AS course,".$date."(FROM_UNIXTIME(MIN(eexam.printdate))) as printdate",
		"(cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)",
		"course",
		"printdate DESC",
		null,
		"printdate",
		null);
$emarkingcoursesmainchart = json_encode(emarking_array_by_date($isyears, $emarkingcoursesmain, get_string('emarkingcourses', 'mod_emarking'), "printdate", "coursecount"));
$meanexamlenghtmain = emarking_get_query(array("%/$categoryid/%", $categoryid, EMARKING_EXAM_PRINTED, EMARKING_EXAM_SENT_TO_PRINT),
		"printdate, AVG(pages) AS avgpages",
		"eexam.id as id,".$date."(FROM_UNIXTIME(eexam.printdate)) as printdate,(eexam.totalpages+eexam.extrasheets) AS pages",
		"(cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)",
		"id",
		null, null,
		"printdate",
		null);
$meanexamlenghtmainchart = json_encode(emarking_array_by_date($isyears, $meanexamlenghtmain, get_string('meanexamleanght', 'mod_emarking'), "printdate", "avgpages"));
$totalpagesmain = emarking_get_query(array("%/$categoryid/%", $categoryid, EMARKING_EXAM_PRINTED, EMARKING_EXAM_SENT_TO_PRINT),
		"printdate,SUM(pages) AS totalpages",
		"c.id AS courseid, eexam.id AS examid,".$date."(FROM_UNIXTIME(eexam.printdate)) as printdate, (eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams) AS pages",
		"(cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)",
		"eexam.id",
		"pages DESC",
		null,
		"printdate",
		null);
$totalpagesmainchart = json_encode(emarking_array_by_date($isyears, $totalpagesmain, get_string('totalprintedpages', 'mod_emarking'), "printdate", "totalpages"));
$totalcostmain = emarking_get_query(array("%/$categoryid/%", $categoryid, EMARKING_EXAM_PRINTED, EMARKING_EXAM_SENT_TO_PRINT),
		"printdate, SUM(pages) AS totalcost",
		"c.id AS courseid, eexam.id AS examid,".$date."(FROM_UNIXTIME(eexam.printdate)) as printdate, eexam.printingcost*((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages",
		"(cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)",
		"eexam.id",
		"pages DESC",
		null,
		"printdate",
		null);
$totalcostmainchart = json_encode(emarking_array_by_date($isyears, $totalcostmain, get_string('totalcost', 'mod_emarking'), "printdate", "totalcost"));
if(! empty($subcategories)){
	// Column chart variables.
	$activitiescolumn = emarking_get_query(array("%/$categoryid/%"),
			"cc.id as id, cc.name as name, COUNT(e.id) AS activities",
			null, null, null, null,
			"cc.path like ?",
			"id",
			null);
	$activitiescolumnchart = json_encode(emarking_array_column_chart($activitiescolumn, array(get_string('category', 'mod_emarking'),get_string('activities', 'mod_emarking')), "activities", "name"));
	$emarkingcoursescolumn = emarking_get_query(array("%/$categoryid/%"),
			"id, name, COUNT(course) as countcourses",
			"e.course AS course, cc.name as name, cc.id as id",
			"cc.path like ?",
			"course",
			"name desc",
			null,
			"id",
			null);
	$emarkingcoursescolumnchart = json_encode(emarking_array_column_chart($emarkingcoursescolumn, array(get_string('category', 'mod_emarking'), get_string('emarkingcourses', 'mod_emarking')), "countcourses", "name"));
	$meanexamlenghtcolumn = emarking_get_query(array("%/$categoryid/%", EMARKING_EXAM_PRINTED, EMARKING_EXAM_SENT_TO_PRINT),
			"cc.id as id, cc.name as name, AVG((eexam.totalpages+eexam.extrasheets)) AS pages",
			null, null, null, null,
			"cc.path like ?  AND eexam.status IN (?,?)",
			"id",
			null);
	$meanexamlenghtcolumnchart = json_encode(emarking_array_column_chart($meanexamlenghtcolumn, array(get_string('category', 'mod_emarking'), get_string('meanexamleanght', 'mod_emarking')), "pages", "name"));
	$totalpagescolumn = emarking_get_query(array("%/$categoryid/%", EMARKING_EXAM_PRINTED, EMARKING_EXAM_SENT_TO_PRINT),
			"categoryid, categoryname, SUM(pages) AS totalpages",
			"c.id AS courseid, eexam.id AS examid, cc.id as categoryid, cc.name as categoryname, ((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages",
			"cc.path like ?  AND eexam.status IN (?,?)",
			"eexam.id",
			"pages DESC",
			null,
			"categoryid",
			null);
	$totalpagescolumnchart = json_encode(emarking_array_column_chart($totalpagescolumn, array(get_string('category', 'mod_emarking'), get_string('totalprintedpages', 'mod_emarking')), "totalpages", "categoryname"));
	$totalcostcolumn = emarking_get_query(array("%/$categoryid/%", EMARKING_EXAM_PRINTED, EMARKING_EXAM_SENT_TO_PRINT),
			"categoryid, categoryname, SUM(pages) AS totalcost",
			"c.id AS courseid, eexam.id AS examid, cc.id as categoryid, cc.name as categoryname, eexam.printingcost*((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages",
			"cc.path like ? AND eexam.status IN (?,?)",
			"eexam.id",
			"pages DESC",
			null,
			"categoryid",
			null);
	$totalcostcolumnchart = json_encode(emarking_array_column_chart($totalcostcolumn, array(get_string('category', 'mod_emarking'), get_string('totalcost', 'mod_emarking')), "totalcost", "categoryname"));
}
// Excel downloads.
if ($status == 1) {
    emarking_download_excel_course_ranking($categoryid);
}
elseif ($status == 2) {
    emarking_download_excel_teacher_ranking($categoryid);
}
elseif ($status == 3) {
    emarking_download_excel_monthly_cost($categoryid, emarking_array_by_date($isyears, $totalcostmain, get_string('totalcost', 'mod_emarking'), "printdate", "totalcost"));
}
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle . ' ' . $category->name);
if(isset($subcategories)){
	echo html_writer::start_tag('div', array(
			'class' => 'emarking-left-table-ranking'));
	$addsubcategoryform->display();
	echo html_writer::end_tag('div');
}
if($parentcategory->parent != 0){
	echo html_writer::start_tag('div', array(
		'class' => 'emarking-left-table-ranking'));
	$adduppercategoryform->display();
	echo html_writer::end_tag('div');
}
// Div that contain the buttons table.
echo html_writer::start_tag('div');
// Generation of the buttons table.
$mainbuttons = array(
		emarking_buttons_creator(emarking_get_activities($categoryid). " " . get_string('totalactivies', 'emarking'), 'activitiesbutton', 'emarking-area-cost-button-style'),
		emarking_buttons_creator(emarking_get_emarking_courses($categoryid). " " . get_string('emarkingcourses', 'emarking'), 'emarkingcourses', 'emarking-area-cost-button-style'),
		emarking_buttons_creator(emarking_get_original_pages($categoryid). " " . get_string('emarkingcourses', 'emarking'), 'meantestlenght', 'emarking-area-cost-button-style'),
		emarking_buttons_creator(emarking_get_total_pages($categoryid). " " . get_string('totalprintedpages', 'emarking'), 'totalprintedpages', 'emarking-area-cost-button-style'),
		emarking_buttons_creator('$' . " " .number_format(emarking_get_total_pages($categoryid)). " " . get_string('totalprintingcost', 'emarking'), 'totalprintingcost', 'emarking-totalcost-button-style emarking-area-cost-button-style')
);

echo emarking_table_creator(array(get_string('reportbuttonsheader', 'emarking')),array($mainbuttons),array('20%','20%','20%','20%','20%'));

echo html_writer::end_tag('div');
if($isyears==0){
$actualyear = $yearormonth[1];
echo html_writer::tag('center', get_string('year', 'emarking').":".$actualyear, array());
}
// Google chart div.
echo html_writer::tag('div', '', array(
    'id' => 'areachartdiv',
    'style' => 'width:100%; height: 400px;'));
echo html_writer::tag('hr','', array(
		'class' => 'style-one'
));
$subcategories = emarking_get_subcategories($categoryid);
if (! empty($subcategories)) {
	$secondarybuttons = array(
			emarking_buttons_creator(get_string('activities', 'emarking'), 'columnactivitiesbutton', 'emarking-column-cost-button-style'),
			emarking_buttons_creator(get_string('emarkingcourses', 'emarking'), 'columnemarkingcourses', 'emarking-column-cost-button-style'),
			emarking_buttons_creator(get_string('meanexamleanght', 'emarking'), 'columnmeantestlenght', 'emarking-column-cost-button-style'),
			emarking_buttons_creator(get_string('totalprintedpages', 'emarking'), 'columntotalprintedpages', 'emarking-column-cost-button-style'),
			emarking_buttons_creator(get_string('totalcost', 'emarking'), 'columntotalprintingcost', 'emarking-column-totalcost-button-style emarking-column-cost-button-style')
	);
    // Generation of the buttons table.
   echo emarking_table_creator(null,array($secondarybuttons),array('20%','20%','20%','20%','20%'));
    // Sub-category column chart.
    echo html_writer::tag('div', '', array(
        'id' => 'columnchartdiv'));
    echo html_writer::tag('hr','', array(
		'class' => 'style-one'
));
}
// Rankings div.
echo html_writer::start_tag('div', array(
    'class' => 'emarking-left-table-ranking'));
// Generation of the ranking table.
echo emarking_table_creator(array(get_string('courseranking', 'emarking'),get_string('pages', 'emarking')),emarking_get_total_pages_by_course($categoryid, 5),null);
// Excel export button.
$buttonurl = new moodle_url('/mod/emarking/reports/costcenter.php', array(
    'category' => $categoryid,
    'status' => 1));
echo $OUTPUT->single_button($buttonurl, get_string("downloadexcel", "mod_emarking"));
echo html_writer::tag('hr','', array(
		'class' => 'style-one'
));
// Generation of the teachers ranking table.
echo emarking_table_creator(array(get_string('teacherranking', 'emarking'),get_string('totalactivies', 'emarking')),emarking_get_teacher_ranking($categoryid, 5),null);
// Excel export button.
$buttonurl = new moodle_url('/mod/emarking/reports/costcenter.php', array(
    'category' => $categoryid,
    'status' => 2));
echo $OUTPUT->single_button($buttonurl, get_string("downloadexcel", "mod_emarking"));
// End of ranking div.
echo html_writer::end_tag('div');
// Start of the detailed view table div.
echo html_writer::start_tag('div', array(
    'class' => 'emarking-right-table-ranking'));
// Get the students in the category.
$students = emarking_get_students($categoryid);
$student = html_writer::tag('span', $category->name . "<br>" . get_string("studentnumber", "mod_emarking") . " " . $students [0],
        array(
            'id' => 'studentspan'));
// Generates the detailed information table.
echo emarking_table_creator(null,[[$student]],null);
echo html_writer::tag('hr','', array(
		'class' => 'style-one'
));
// Get the monthly cost and gets it in a table.
echo emarking_table_creator(array(get_string("costbydate", "mod_emarking")),emarking_get_total_cost_for_table($categoryid, $isyears),null);
// Excel export button.
$buttonurl = new moodle_url('/mod/emarking/reports/costcenter.php', array(
    'category' => $categoryid,
    'status' => 3));

emarking_get_category_cost(8);
echo $OUTPUT->single_button($buttonurl, get_string("downloadexcel", "mod_emarking"));
echo html_writer::end_tag('div');
echo $OUTPUT->footer();
?>
<html>
<head>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        // Initial data for the area  chart.
        var areadata = google.visualization.arrayToDataTable(<?php echo $activitiesmainchart; ?>);
        // Options for the area chart.
        var areaoptions = {
          title: '<?php echo get_string("categorychart", "mod_emarking");?>',
          hAxis: {title: 'Time',  titleTextStyle: {color: '#3333'}},
          vAxis: {title: 'Activity', minValue: 0},
          legend: {position:'top'}
        };
        // Initialize the column chart.
        var areachart = new google.visualization.AreaChart(document.getElementById('areachartdiv'));
        areachart.draw(areadata, areaoptions);
        // Funtion to reload the data of the area chart.
        function areaChartHandler(data) {
    		var areaoptions = {
    		          title: '<?php echo get_string("categorychart", "mod_emarking");?>',
    		          hAxis: {title: 'Time',  titleTextStyle: {color: '#3333'}},
    		          vAxis: {title: 'Activity', minValue: 0},
    		          legend: {position:'top'}
    		        };
    		areachart.draw(data, areaoptions);;
        }
       $(".emarking-area-cost-button-style").click(function(){
           if( $(this).attr("id") == "activitiesbutton" )
           {
           		var data = google.visualization.arrayToDataTable(<?php echo $activitiesmainchart; ?>);
           }
           if( $(this).attr("id") == "emarkingcourses" )
           {
		   		var data = google.visualization.arrayToDataTable(<?php echo $emarkingcoursesmainchart; ?>);
           }
           if( $(this).attr("id") == "meantestlenght" )
           {
        		var data = google.visualization.arrayToDataTable(<?php echo $meanexamlenghtmainchart; ?>);
           }
           if( $(this).attr("id") == "totalprintedpages" )
           {
        		var data = google.visualization.arrayToDataTable(<?php echo $totalpagesmainchart; ?>);
           }
           if( $(this).attr("id") == "totalprintingcost" )
           {
        		var data = google.visualization.arrayToDataTable(<?php echo $totalcostmainchart; ?>);
           }
           areaChartHandler(data);
       	})
      }
    </script>
<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
    	// Initial data for the column chart.
    	  var columndata = google.visualization.arrayToDataTable(<?php echo $activitiescolumnchart; ?>);
    	// Options for the column chart.
    	   var columnoptions = {
                  title: '<?php echo get_string("subcategorychart", "mod_emarking");?>',
                  legend: {position:'top'}
                };
    	// Initialize the column chart.
           var columnchart = new google.visualization.ColumnChart(document.getElementById('columnchartdiv'));
           columnchart.draw(columndata, columnoptions);
        // Funtion to reload the data of the column chart.
        	function columnChartHandler(data, columnoptions) {
         	var columnoptions = {
            	title: '<?php echo get_string("subcategorychart", "mod_emarking");?>',
            	legend: {position:'top'}
           		};
         	columnchart.draw(data, columnoptions);
           }
        	$(".emarking-column-cost-button-style").click(function(){
                if( $(this).attr("id") == "columnactivitiesbutton" )
                {
                		var data = google.visualization.arrayToDataTable(<?php echo $activitiescolumnchart; ?>);
                }
                if( $(this).attr("id") == "columnemarkingcourses" )
                {
                		var data = google.visualization.arrayToDataTable(<?php echo $emarkingcoursescolumnchart; ?>);
                }
                if( $(this).attr("id") == "columnmeantestlenght" )
                {
             	    var data = google.visualization.arrayToDataTable(<?php echo $meanexamlenghtcolumnchart; ?>);
                }
                if( $(this).attr("id") == "columntotalprintedpages" )
                {
             	    var data = google.visualization.arrayToDataTable(<?php echo $totalpagescolumnchart; ?>);
                }
                if( $(this).attr("id") == "columntotalprintingcost" )
                {
             	    var data = google.visualization.arrayToDataTable(<?php echo $totalcostcolumnchart; ?>);
                }
              columnChartHandler(data);
            	});
      }
    </script>
</head>
</html>