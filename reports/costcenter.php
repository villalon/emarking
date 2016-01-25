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
global $DB, $CFG, $SCRIPT, $USER;

$categoryid = required_param('category', PARAM_INT);


// Validate category
if (! $category = $DB->get_record('course_categories', array(
		'id' => $categoryid
))) {
	print_error(get_string('invalidcategoryid', 'mod_emarking'));
}
// We are in the category context
$context = context_coursecat::instance($categoryid);
// User must be logged in
require_login();
if (isguestuser()) {
	die();
}
// And have viewcostreport capability
if (! has_capability('mod/emarking:viewcostreport', $context)) {
	// TODO: Log invalid access to printreport
	print_error('Not allowed!');
}

$url = new moodle_url('/mod/emarking/reports/costcenter.php', array(
		'category' => $categoryid
));

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
echo html_writer::tag('div', '', array('id' => 'chart_div', 'style' => 'width:100%; height: 400px;'));
echo '<hr class="style-one">';
echo html_writer::start_tag('div',array( 'class' => 'rankingdiv', 'style' => 'width: 100%;'));
// Generation of the buttons table
$piebuttons = emarking_piebuttonstable($categoryid);
$piebuttonstable = new html_table();

$piebuttonstable->data[] = $piebuttons;
echo html_writer::table($piebuttonstable);

$totalpagespiechart = json_encode(emarking_gettotalcostpiechart($categoryid));
echo html_writer::tag('div', '', array('id' => 'piechartdiv'));
echo '<hr class="style-one">';
echo html_writer::start_tag('div',array( 'class' => 'emarking-left-table-ranking'));

$courseranking=emarking_gettotalpagesbycourse($categoryid);
$coursetable = new html_table();
$coursetable->head = array(get_string('courseranking', 'emarking'),'Number of pages');
$coursetable->data = $courseranking;
echo html_writer::table($coursetable);

// teachers Ranking
$teacherranking = emarking_getteacherranking($categoryid);
$teachertable = new html_table();
$teachertable->head = array(get_string('teacherranking', 'emarking'), 'Number of activities');
$teachertable->data = $teacherranking;
echo html_writer::table($teachertable);

echo html_writer::end_tag('div');
echo html_writer::start_tag('div',array( 'class' => 'emarking-center-table-ranking'));

// Table for showing detailed view
$students = emarking_getstudents($categoryid);
$student = html_writer::tag('span', $category->name."<br>"."Number of students:"." ".$students[0], array('id' => 'studentspan'));



$totalpagesfortable = emarking_gettotalpagesfortable($categoryid);
$monthtable = new html_table();
$monthtable->head = ['Detail information'];
$monthtable->data = $totalpagesfortable;
$tablemonth = html_writer::table($monthtable);
echo html_writer::end_tag('div');
echo html_writer::start_tag('div',array( 'class' => 'emarking-center-table-ranking'));
$detailtable = new html_table();
$detailtable->head = ['Facturacion emarking'];
$detailtable->data = [[$student],[$tablemonth]];
echo html_writer::table($detailtable);

echo html_writer::end_tag('div');

echo html_writer::end_tag('div');


//Area chart variables
$activitiesforchart = json_encode(emarking_getActivitiesbydate($categoryid));
$emarkingcoursesforchart = json_encode(emarking_getemarkingcoursesbydate($categoryid));
$meantestlenghforchart = json_encode(emarking_getoriginalpagesbydate($categoryid));
$totalpagesforchart = json_encode(emarking_gettotalpagesbydate($categoryid));
$totalcostforchart = json_encode(emarking_gettotalcostbydate($categoryid));

//Pie chart variables
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
        var data = google.visualization.arrayToDataTable(<?php echo $activitiesforchart; ?>);
        var piedata = google.visualization.arrayToDataTable(<?php echo $activitiespiechart; ?>);
        var options = {
          title: 'Chart',
          hAxis: {title: 'MONTH',  titleTextStyle: {color: '#3333'}},
          vAxis: {minValue: 0},
          legend:{position:'top'}
        };  
 	   var pieoptions = {
               title: 'Sub-categories chart',
               legend:{position:'top'}	   
             };     

        
        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);

        var piechart = new google.visualization.ColumnChart(document.getElementById('piechartdiv'));
        piechart.draw(piedata, pieoptions);
        
       function selectHandler(data) {
    	   var options = {
    		          title: 'Chart',
    		          hAxis: {title: 'MONTH',  titleTextStyle: {color: '#3333'}},
    		          vAxis: {minValue: 0},
    		          legend:{position:'top'}
    		        };
        	chart.draw(data, options);;
        }

       function pieHandler(data, pieoptions) {
      	   var pieoptions = {
                   title: 'Sub-categories chart',
                   legend:{position:'top'}    
                 }; 
        	piechart.draw(data, pieoptions);

        }
       
       $(".emarking-cost-button-style").click(function(){
           
           if( $(this).attr("id") == "activitiesbutton" )
           {
           	var datos = google.visualization.arrayToDataTable(<?php echo $activitiesforchart; ?>);
           }
           
           if( $(this).attr("id") == "emarkingcourses" )
           {
           	var datos = google.visualization.arrayToDataTable(<?php echo $emarkingcoursesforchart; ?>);
           }
           
           if( $(this).attr("id") == "meantestleangh" )
           {
        	   var datos = google.visualization.arrayToDataTable(<?php echo $meantestlenghforchart; ?>);
           }
           
           if( $(this).attr("id") == "totalprintedpages" )
           {
        	   var datos = google.visualization.arrayToDataTable(<?php echo $totalpagesforchart; ?>);
           }
           
           if( $(this).attr("id") == "totalprintingcost" )
           {
        	   var datos = google.visualization.arrayToDataTable(<?php echo $totalcostforchart; ?>);
           }
   		selectHandler(datos);
       	})

       $(".emarking-pie-cost-button-style").click(function(){
           if( $(this).attr("id") == "pieactivitiesbutton" )
           {
           	var datos = google.visualization.arrayToDataTable(<?php echo $activitiespiechart; ?>);
           }
           if( $(this).attr("id") == "pieemarkingcourses" )
           {
           	var datos = google.visualization.arrayToDataTable(<?php echo $emarkingcoursespiechart; ?>);
           }
           if( $(this).attr("id") == "piemeantestleangh" )
           {
        	   var datos = google.visualization.arrayToDataTable(<?php echo $meanexamlenghtpiechart; ?>);
           }
           if( $(this).attr("id") == "pietotalprintedpages" )
           {
        	   var datos = google.visualization.arrayToDataTable(<?php echo $totalpagespiechart; ?>);
           }
           if( $(this).attr("id") == "pietotalprintingcost" )
           {
        	   var datos = google.visualization.arrayToDataTable(<?php echo $totalcostpiechart; ?>);
           }
   		pieHandler(datos);
       	});
      } 


    </script>
    
  </head>
</html>

