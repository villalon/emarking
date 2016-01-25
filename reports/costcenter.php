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
* @copyright 2016 Mihail Pozarski <mipozarski@alumnos.uai.cl>
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/reports/locallib.php");
require_once ($CFG->dirroot . "/mod/emarking/marking/locallib.php");
require_once ($CFG->dirroot . '/lib/excellib.class.php');
	global $DB, $CFG;

$categoryid = required_param('category', PARAM_INT);

// User must be logged in
require_login();
if (isguestuser()) {
	die();
}
// Validate category
if (!$category = $DB->get_record('course_categories', array(
		'id' => $categoryid
))) {
	print_error(get_string('invalidcategoryid', 'mod_emarking'));
}
// We are in the category context
$context = context_coursecat::instance($categoryid);
// And have viewcostreport capability
if (!has_capability('mod/emarking:viewcostreport', $context)) {
	// TODO: Log invalid access to printreport
	print_error('Not allowed!');
}
// This page url
$url = new moodle_url('/mod/emarking/reports/costcenter.php', array(
		'category' => $categoryid
));
// Url that lead you to the category page
$categoryurl = new moodle_url('/course/index.php', array(
		'categoryid' => $categoryid
));

$pagetitle = get_string('costreport', 'mod_emarking');
$PAGE->set_context($context);
$PAGE->set_url($url);
//$PAGE->requires->js('/mod/emarking/js/printorders.js');
$PAGE->set_pagelayout('course');
$PAGE->navbar->add($category->name, $categoryurl);
$PAGE->navbar->add(get_string('printorders', 'mod_emarking'), $url);
$PAGE->navbar->add($pagetitle);
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle . ' ' . $category->name);

// Div that contain the buttons table
echo html_writer::start_tag('div');

// Generation of the buttons table
$buttonsarray = emarking_buttonstable($categoryid);
$buttonstable = new html_table();
$buttonstable->head = array(get_string('reportbuttonsheader', 'emarking'));
$buttonstable->data[] = $buttonsarray;
echo html_writer::table($buttonstable);
echo html_writer::end_tag('div');

// Google chart div
echo html_writer::tag('div', '', array('id' => 'areachartdiv', 'style' => 'width:100%; height: 400px;'));

echo '<hr class="style-one">';

// Generation of the buttons table
$piebuttons = emarking_columnbuttonstable($categoryid);
$piebuttonstable = new html_table();
$piebuttonstable->data[] = $piebuttons;
echo html_writer::table($piebuttonstable);

// Sub-category column chart
echo html_writer::tag('div', '', array('id' => 'columnchartdiv'));
echo '<hr class="style-one">';

// Rankings div
echo html_writer::start_tag('div',array( 'class' => 'emarking-left-table-ranking'));

// Generation of the ranking table
$courseranking = emarking_gettotalpagesbycourse($categoryid);
$coursetable = new html_table();
$coursetable->head = array(get_string('courseranking', 'emarking'),'Number of pages');
$coursetable->data = $courseranking;
echo html_writer::table($coursetable);

// Generation of the teachers Ranking
$teacherranking = emarking_getteacherranking($categoryid);
$teachertable = new html_table();
$teachertable->head = array(get_string('teacherranking', 'emarking'), 'Number of activities');
$teachertable->data = $teacherranking;
echo html_writer::table($teachertable);

// End of ranking div
echo html_writer::end_tag('div');
// Start of the detailed view table div
echo html_writer::start_tag('div',array( 'class' => 'emarking-right-table-ranking'));

// Get the students in the category
$students = emarking_getstudents($categoryid);
$student = html_writer::tag('span', $category->name."<br>"."Number of students:"." ".$students[0], array('id' => 'studentspan'));

// Generates the detailed information table
$detailtable = new html_table();
$detailtable->head = ['Facturacion emarking'];
$detailtable->data = [[$student]];
echo html_writer::table($detailtable);

// Get the monthly cost and gets it in a table
$totalcostfortable = emarking_gettotalpagesfortable($categoryid);
$monthtable = new html_table();
$monthtable->head = ['Detail information'];
$monthtable->data = $totalcostfortable;
echo html_writer::table($monthtable);

echo html_writer::end_tag('div');

// Area chart data
$activitiesforchart = json_encode(emarking_getActivitiesbydate($categoryid));
$emarkingcoursesforchart = json_encode(emarking_getemarkingcoursesbydate($categoryid));
$meantestlenghforchart = json_encode(emarking_getoriginalpagesbydate($categoryid));
$totalpagesforchart = json_encode(emarking_gettotalpagesbydate($categoryid));
$totalcostforchart = json_encode(emarking_gettotalcostbydate($categoryid));

// Column chart variables
$activitiespiechart = json_encode(emarking_getactivitiespiechart($categoryid));
$emarkingcoursespiechart = json_encode(emarking_getemarkingcoursespiechart($categoryid));
$meanexamlenghtpiechart = json_encode(emarking_getmeanexamlenghtpiechart($categoryid));
$totalpagespiechart = json_encode(emarking_gettotalpagespiechart($categoryid));
$totalcostpiechart = json_encode(emarking_gettotalcostpiechart($categoryid));



echo $OUTPUT->footer();

?>
<html>
  <head>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      
      function drawChart() {
          
        // Initial data for the area and column chart
        var areadata = google.visualization.arrayToDataTable(<?php echo $activitiesforchart; ?>);
        var columndata = google.visualization.arrayToDataTable(<?php echo $activitiespiechart; ?>);
        
        // Options for the area chart
        var areaoptions = {
          title: 'Chart',
          hAxis: {title: 'MONTH',  titleTextStyle: {color: '#3333'}},
          vAxis: {minValue: 0},
          legend: {position:'top'}
        };
          
        // Options for the column chart
 	   var columnoptions = {
               title: 'Sub-categories chart',
               legend: {position:'top'}	   
             };     

        // Initialize the column chart
        var areachart = new google.visualization.AreaChart(document.getElementById('areachartdiv'));
        areachart.draw(areadata, areaoptions);

     	// Initialize the column chart
        var columnchart = new google.visualization.ColumnChart(document.getElementById('columnchartdiv'));
        columnchart.draw(columndata, columnoptions);

        // Funtion to reload the data of the area chart
        function areaChartHandler(data) {
    		var areaoptions = {
    		          title: 'Chart',
    		          hAxis: {title: 'MONTH',  titleTextStyle: {color: '#3333'}},
    		          vAxis: {minValue: 0},
    		          legend: {position:'top'}
    		        };
    		areachart.draw(data, areaoptions);;
        }

        // Funtion to reload the data of the column chart
        function columnChartHandler(data, columnoptions) {
      		var columnoptions = {
                   title: 'Sub-categories chart',
                   legend: {position:'top'}    
                 }; 
      		columnchart.draw(data, columnoptions);
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

       $(".emarking-column-cost-button-style").click(function(){
           if( $(this).attr("id") == "pieactivitiesbutton" )
           {
           		var data = google.visualization.arrayToDataTable(<?php echo $activitiespiechart; ?>);
           }
           if( $(this).attr("id") == "pieemarkingcourses" )
           {
           		var data = google.visualization.arrayToDataTable(<?php echo $emarkingcoursespiechart; ?>);
           }
           if( $(this).attr("id") == "piemeantestleangh" )
           {
        	    var data = google.visualization.arrayToDataTable(<?php echo $meanexamlenghtpiechart; ?>);
           }
           if( $(this).attr("id") == "pietotalprintedpages" )
           {
        	    var data = google.visualization.arrayToDataTable(<?php echo $totalpagespiechart; ?>);
           }
           if( $(this).attr("id") == "pietotalprintingcost" )
           {
        	    var data = google.visualization.arrayToDataTable(<?php echo $totalcostpiechart; ?>);
           }
         columnChartHandler(data);
       	});
      } 
    </script>    
  </head>
</html>

