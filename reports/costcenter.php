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
    print_error('Not allowed!');
}
// This page url.
$url = new moodle_url('/mod/emarking/reports/costcenter.php', array(
    'category' => $categoryid));
// Url that lead you to the category page.
$categoryurl = new moodle_url('/course/index.php', array(
    'categoryid' => $categoryid));
$pagetitle = get_string('costreport', 'mod_emarking');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('course');
$PAGE->navbar->add($category->name, $categoryurl);
$PAGE->navbar->add(get_string('printorders', 'mod_emarking'), $url);
$PAGE->navbar->add($pagetitle);
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
// Excel downloads.
if ($status == 1) {
    emarking_download_excel_course_ranking($categoryid);
}
if ($status == 2) {
    emarking_download_excel_teacher_ranking($categoryid);
}
if ($status == 3) {
    emarking_download_excel_monthly_cost($categoryid);
}
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle . ' ' . $category->name);
// Div that contain the buttons table.
echo html_writer::start_tag('div');
// Generation of the buttons table.
$buttonsarray = emarking_buttonstable($categoryid);
$buttonstable = new html_table();
$buttonstable->head = array(
    get_string('reportbuttonsheader', 'emarking'));
$buttonstable->data [] = $buttonsarray;
$buttonstable->size = [
    '25%',
    '25%',
    '25%',
    '25%',
    '25%'];
echo html_writer::table($buttonstable);
echo html_writer::end_tag('div');
// Google chart div.
echo html_writer::tag('div', '', array(
    'id' => 'areachartdiv',
    'style' => 'width:100%; height: 400px;'));
echo '<hr class="style-one">';
$subcategories = emarking_get_subcategories($categoryid);
if (! empty($subcategories)) {
    // Generation of the buttons table.
    $piebuttons = emarking_columnbuttonstable($categoryid);
    $piebuttonstable = new html_table();
    $piebuttonstable->data [] = $piebuttons;
    $piebuttonstable->size = [
        '25%',
        '25%',
        '25%',
        '25%',
        '25%'];
    echo html_writer::table($piebuttonstable);
    // Sub-category column chart.
    echo html_writer::tag('div', '', array(
        'id' => 'columnchartdiv'));
    echo '<hr class="style-one">';
}
// Rankings div.
echo html_writer::start_tag('div', array(
    'class' => 'emarking-left-table-ranking'));
// Generation of the ranking table.
$courseranking = emarking_get_total_pages_by_course($categoryid, 5);
$coursetable = new html_table();
$coursetable->head = array(
    get_string('courseranking', 'emarking'),
    'Number of pages');
$coursetable->data = $courseranking;
echo html_writer::table($coursetable);
// Excel export button.
$buttonurl = new moodle_url('/mod/emarking/reports/costcenter.php', array(
    'category' => $categoryid,
    'status' => 1));
echo $OUTPUT->single_button($buttonurl, get_string("downloadexcel", "mod_emarking"));
echo '<hr class="style-one">';
// Generation of the teachers Ranking.
$teacherranking = emarking_get_teacher_ranking($categoryid, 5);
$teachertable = new html_table();
$teachertable->head = array(
    get_string('teacherranking', 'emarking'),
    'Number of activities');
$teachertable->data = $teacherranking;
echo html_writer::table($teachertable);
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
$detailtable = new html_table();
$detailtable->data = [
    [
        $student]];
echo html_writer::table($detailtable);
echo '<hr class="style-one">';
// Get the monthly cost and gets it in a table.
$totalcostfortable = emarking_get_total_pages_for_table($categoryid);
$monthtable = new html_table();
$monthtable->head = [
    get_string("monthlycost", "mod_emarking")];
$monthtable->data = $totalcostfortable;
echo html_writer::table($monthtable);
// Excel export button.
$buttonurl = new moodle_url('/mod/emarking/reports/costcenter.php', array(
    'category' => $categoryid,
    'status' => 3));
echo $OUTPUT->single_button($buttonurl, get_string("downloadexcel", "mod_emarking"));
echo html_writer::end_tag('div');
// Area chart data.
$activitiesforchart = json_encode(emarking_get_activities_by_date($categoryid));
$emarkingcoursesforchart = json_encode(emarking_get_emarking_courses_by_date($categoryid));
$meantestlenghforchart = json_encode(emarking_get_original_pages_by_date($categoryid));
$totalpagesforchart = json_encode(emarking_get_total_pages_by_date($categoryid));
$totalcostforchart = json_encode(emarking_get_total_cost_by_date($categoryid));
// Column chart variables.
$activitiespiechart = json_encode(emarking_get_activities_piechart($categoryid));
$emarkingcoursespiechart = json_encode(emarking_get_emarking_courses_piechart($categoryid));
$meanexamlenghtpiechart = json_encode(emarking_ge_tmean_exam_lenght_piechart($categoryid));
$totalpagespiechart = json_encode(emarking_get_total_pages_piechart($categoryid));
$totalcostpiechart = json_encode(emarking_get_total_cost_piechart($categoryid));
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
        var areadata = google.visualization.arrayToDataTable(<?php echo $activitiesforchart; ?>);
        // Options for the area chart.
        var areaoptions = {
          title: '<?php echo get_string("categorychart", "mod_emarking");?>',
          hAxis: {title: 'MONTH',  titleTextStyle: {color: '#3333'}},
          vAxis: {minValue: 0},
          legend: {position:'top'}
        };
        // Initialize the column chart.
        var areachart = new google.visualization.AreaChart(document.getElementById('areachartdiv'));
        areachart.draw(areadata, areaoptions);
        // Funtion to reload the data of the area chart.
        function areaChartHandler(data) {
    		var areaoptions = {
    		          title: '<?php echo get_string("categorychart", "mod_emarking");?>',
    		          hAxis: {title: 'MONTH',  titleTextStyle: {color: '#3333'}},
    		          vAxis: {minValue: 0},
    		          legend: {position:'top'}
    		        };
    		areachart.draw(data, areaoptions);;
        }
       $(".emarking-area-cost-button-style").click(function(){
           if( $(this).attr("id") == "activitiesbutton" )
           {
           		var data = google.visualization.arrayToDataTable(<?php echo $activitiesforchart; ?>);
           }
           if( $(this).attr("id") == "emarkingcourses" )
           {
		   		var data = google.visualization.arrayToDataTable(<?php echo $emarkingcoursesforchart; ?>);
           }
           if( $(this).attr("id") == "meantestleangh" )
           {
        		var data = google.visualization.arrayToDataTable(<?php echo $meantestlenghforchart; ?>);
           }
           if( $(this).attr("id") == "totalprintedpages" )
           {
        		var data = google.visualization.arrayToDataTable(<?php echo $totalpagesforchart; ?>);
           }
           if( $(this).attr("id") == "totalprintingcost" )
           {
        		var data = google.visualization.arrayToDataTable(<?php echo $totalcostforchart; ?>);
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
    	  var columndata = google.visualization.arrayToDataTable(<?php echo $activitiespiechart; ?>);
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
                		var data = google.visualization.arrayToDataTable(<?php echo $activitiespiechart; ?>);
                }
                if( $(this).attr("id") == "columnemarkingcourses" )
                {
                		var data = google.visualization.arrayToDataTable(<?php echo $emarkingcoursespiechart; ?>);
                }
                if( $(this).attr("id") == "columnmeantestleangh" )
                {
             	    var data = google.visualization.arrayToDataTable(<?php echo $meanexamlenghtpiechart; ?>);
                }
                if( $(this).attr("id") == "columntotalprintedpages" )
                {
             	    var data = google.visualization.arrayToDataTable(<?php echo $totalpagespiechart; ?>);
                }
                if( $(this).attr("id") == "columntotalprintingcost" )
                {
             	    var data = google.visualization.arrayToDataTable(<?php echo $totalcostpiechart; ?>);
                }
              columnChartHandler(data);
            	});
      }
    </script>
</head>
</html>