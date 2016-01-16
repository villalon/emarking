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
$ordersurl = new moodle_url('/mod/emarking/reports/costcenter.php', array(
		'category' => $categoryid,
		
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
$PAGE->navbar->add(get_string('printorders', 'mod_emarking'), $ordersurl);
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
$buttonsarray = array();
// Creation of the activities button
$activities = emarking_getActivities($categoryid);
$activitiesbutton = html_writer::tag('button',$activities[0]." ".get_string('totalactivies', 'emarking'), array('id' => 'activitiesbutton', 'class' => 'cost-button-style'));
$buttonsarray[] = $activitiesbutton;
// Creation of the emarkingcourses button
$emarkingcourses = emarking_getemarkingcourses($categoryid);
$emarkingcoursesbutton = html_writer::tag('button',$emarkingcourses[0]." ".get_string('emarkingcourses', 'emarking'), array('id' => 'emarkingcourses', 'class' => 'cost-button-style'));
$buttonsarray[] = $emarkingcoursesbutton;
// Creation of the meanlengh button
$originalpages = emarking_getoriginalpages($categoryid);
if(!$activities[0] == 0){
$meantestlengh = $originalpages[0]/$activities[0];
} else{
	$meantestlengh = 0;
}
$meantestlenghbutton = html_writer::tag('button',$meantestlengh." ".get_string('meantestlenght', 'emarking')  , array('id' => 'meantestleangh',  'class' => 'cost-button-style'));
$buttonsarray[] = $meantestlenghbutton;
// Creation of the totalprintedpages button
$totalprintedpages = emarking_gettotalpages($categoryid);
$totalprintedpagesbutton = html_writer::tag('button',$totalprintedpages[0]." ".get_string('totalprintedpages', 'emarking') , array('id' => 'totalprintedpages', 'class' => 'cost-button-style'));
$buttonsarray[] = $totalprintedpagesbutton;
// Creation of the total cost.
$printingcost =  emarking_getprintingcost($categoryid);
$totalprintingcost = $printingcost[0];
$formatcost = number_format($totalprintingcost);
$totalprintingcostbutton = html_writer::tag('button','$'." ".$formatcost." ".get_string('totalprintingcost', 'emarking') , array('id' => 'totalprintingcost', 'class' =>'totalcost-button-style'));
$buttonsarray[] = $totalprintingcostbutton;

// Generation of the buttons table
$buttonstable = new html_table();
$buttonstable->head = array(get_string('reportbuttonsheader', 'emarking'));
$buttonstable->data[] = $buttonsarray;

echo html_writer::table($buttonstable);
echo html_writer::end_tag('div');
// Google chart div
//echo html_writer::start_tag('div',array( 'class' => 'row'));
//echo html_writer::start_tag('div',array( 'class' => 'chartrank', 'style' => 'float: left; width:75%'));
echo html_writer::tag('div', '', array('id' => 'chart_div', 'style' => 'width:100%; height: 400px;'));
echo html_writer::start_tag('div',array( 'class' => 'rankingdiv', 'style' => 'width: 100%;'));

echo html_writer::start_tag('div',array( 'class' => 'left-table-ranking'));

$courseranking=emarking_gettotalpagesbycourse($categoryid);
$coursetable = new html_table();
$coursetable->head = array(get_string('courseranking', 'emarking'),'Number of pages');
$coursetable->data = $courseranking;
$coursetable->size = [500];
$detailtable->attributes['class'] = 'left-table-ranking';
echo html_writer::table($coursetable);


// teachers Ranking


$teacherranking = emarking_getteacherranking($categoryid);
$teachertable = new html_table();
$teachertable->head = array(get_string('teacherranking', 'emarking'), 'Number of activities');
$teachertable->data = $teacherranking;
$coursetable->size = '50%';
$detailtable->attributes['class'] = '';
echo html_writer::table($teachertable);

$totalpagespiechart = emarking_gettotalpagespiechart($categoryid);
echo html_writer::tag('div', '', array('id' => 'piechartdiv'));

echo html_writer::end_tag('div');
//echo html_writer::end_tag('div');

//echo html_writer::end_tag('div');

echo html_writer::start_tag('div',array( 'style' => 'width: 50%; float: right;'));

// Table for showing detailed view
$students = emarking_getstudents($categoryid);
$student = html_writer::tag('span', $category->name."<br>"."Number of students:"." ".$students[0], array('id' => 'studentspan'));



$totalpagesfortable = emarking_gettotalpagesfortable($categoryid);
$monthtable = new html_table();
$monthtable->head = ['Detail information'];
$monthtable->data = $totalpagesfortable;
$tablemonth = html_writer::table($monthtable);

$detailtable = new html_table();
$detailtable->head = ['Facturacion emarking'];
$detailtable->data = [[$student],[$tablemonth]];
$detailtable->attributes['class'] = '';
echo html_writer::table($detailtable);

echo html_writer::end_tag('div');

echo html_writer::end_tag('div');



$activitiesforchart = emarking_getActivitiesbydate($categoryid);
$emarkingcoursesforchart = emarking_getemarkingcoursesbydate($categoryid);
$meantestlenghforchart = emarking_getoriginalpagesbydate($categoryid);
$totalpagesforchart = emarking_gettotalpagesbydate($categoryid);
$totalcostforchart = emarking_gettotalcostbydate($categoryid);

echo $OUTPUT->footer();

?>
<html>
  <head>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable(<?php echo json_encode($activitiesforchart); ?>);
        var data2 = google.visualization.arrayToDataTable(<?php echo json_encode($emarkingcoursesforchart ); ?>);
        var data3 = google.visualization.arrayToDataTable(<?php echo json_encode($meantestlenghforchart ); ?>);
        var data4 = google.visualization.arrayToDataTable(<?php echo json_encode($totalpagesforchart ); ?>);
        var data5 = google.visualization.arrayToDataTable(<?php echo json_encode($totalcostforchart ); ?>);
        var piedata = google.visualization.arrayToDataTable(<?php echo json_encode($totalpagespiechart ); ?>);
        var pieoptions = {
                title: 'Total pages'
              };
              
        var options = {
          title: 'Chart',
          hAxis: {title: 'Month',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0},
          legend:{position:'bottom'}
        };

        var piechart = new google.visualization.PieChart(document.getElementById('piechartdiv'));
        piechart.draw(piedata, pieoptions);
        
        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);

        document.getElementById("activitiesbutton").onclick = function() {selectHandler()};
        document.getElementById("emarkingcourses").onclick = function() {selectHandler2()};
        document.getElementById("meantestleangh").onclick = function() {selectHandler3()};
        document.getElementById("totalprintedpages").onclick = function() {selectHandler4()};
        document.getElementById("totalprintingcost").onclick = function() {selectHandler5()};

        function selectHandler(e) {
        	chart.draw(data, options);;
        }
        function selectHandler2(e) {
        	chart.draw(data2, options);;
        }
        function selectHandler3(e) {
        	chart.draw(data3, options);;
        }
        function selectHandler4(e) {
        	chart.draw(data4, options);;
        }
        function selectHandler5(e) {
        	chart.draw(data5, options);;
        }

      }
      
    </script>
    
  </head>
</html>

