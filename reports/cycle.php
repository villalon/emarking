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
*
* It can be reached from a block within a category or from an EMarking
* course module
*
* @package mod
* @subpackage emarking
* @copyright 2016 Benjamin Espinosa (beespinosa94@gmail.com)
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config.php");
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once(dirname(__FILE__) . '/locallib.php');


global $DB, $USER, $CFG, $OUTPUT, $COURSE;

$courseid = required_param("course", PARAM_INT);
$emarkingid = optional_param("emarking", -1, PARAM_INT);
$selectedcategory = optional_param("selectedcategory", "NULL", PARAM_RAW);
$selectedcourse = optional_param("selectedcourse", "NULL", PARAM_RAW);

$page = optional_param('page', 0, PARAM_INT);
$perpage = 4;

// EMarking tab I want to show, "0" means the summary tab.
$currenttab = optional_param("currenttab", 0, PARAM_INT);

// definition for this particular page of EMarking.
define('EMARKING_TO_PRINT',0);
define('EMARKING_PRINTED',5);
define('EMARKING_STATUS_GRADED',18);
define('EMARKING_STATUS_FINAL_PUBLISHED',45);
// First check that the user is logged in.
require_login();
if (isguestuser()) {
	die();
}

// Validate that the parameter corresponds to a course.
if (! $course = $DB->get_record("course", array(
		"id" => $courseid))) {
		print_error(get_string("invalidcourseid", "mod_emarking"));
}
// Validate that there are EMarking activities in this course.
if (!$isemarking = $DB->get_records("emarking", array(
		"course" => $courseid))) {
		print_error(get_string("invalidemarkingcourse", "mod_emarking"));
}

// Context from course for permissions later.
$context = context_course::instance($course->id);

// Capability access
if (! has_capability('mod/emarking:viewemarkingcycle', $context)) {
	print_error(get_string("notallowed", "mod_emarking"));
}

// URL for current page.
$url = new moodle_url("/mod/emarking/reports/cycle.php", array(
		"course" => $course->id, "emarking" => $emarkingid));

//Page definition.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string("emarking", "mod_emarking"));
$PAGE->set_pagelayout("incourse");
$PAGE->navbar->add(get_string("cycle", "mod_emarking"));

// User categories for FORM.
$categoryparameters = array($USER->id, $courseid);

echo $OUTPUT->header();

// Data of the current course.
$currentcategory = $DB-> get_record('course_categories', array('id' => $COURSE->category), 'name');
$curretcourse = $COURSE->shortname;

// Case first entry to page
if($selectedcategory == "NULL" && $selectedcourse == "NULL"){
	$selectedcategory = $currentcategory->name;
	$selectedcourse = $curretcourse;
}

echo $OUTPUT->tabtree(emarking_cycle_tabs($selectedcourse, $selectedcategory, $course), $currenttab);

$summarychartdata = json_encode([[0,0]]);

// If you are in the summary tab.
if($currenttab == 0){
	
	// Chart title
	echo html_writer::tag('h4',get_string('ciclechart', 'emarking'),array('style' => 'width:100%;'));
	
	// Alert for the user
	echo html_writer::start_tag('div', array('class' => 'alert alert-warning'));
	echo get_string('ciclechartalert', 'emarking');
	echo html_writer::end_tag('div');
  	
	// Div for summart chart.
  	echo html_writer::tag('div','', array('id' => 'summarychart','style' => 'height: 600px;'));
  	
  	//Table title
  	echo html_writer::tag('h4',get_string('cicletable', 'emarking'),array('style' => 'width:100%;'));
  	
  	// Alert for the user
  	echo html_writer::start_tag('div', array('class' => 'alert alert-warning'));
  	echo get_string('cicletablealert', 'emarking');
  	echo html_writer::end_tag('div');
  	// Emarkings days data to table.
  	
  	$tabledata = emarking_time_progression_table($course->id);
  	$headers = $tabledata[1];
  	unset($tabledata[0]);
  	unset($tabledata[1]);
  	$tabledata = array_values($tabledata);

  	
  	$temp = array();
  	for($row = 0; $row < count($tabledata); $row++){
  		for($col = 0; $col <= 8; $col++){
  			if($col != 0 ){
  				$temp[$col][$row] = $tabledata[$row][$col];
  			}else{
  				$temp[$col][$row] = "<b>".$tabledata[$row][$col]."</b>";
  			}
  		}
  	}
  	
  	$emarkingcount = count($tabledata);
  	
  	for($i = 0; $i <= count($temp); $i ++){
  		for($j = $perpage * $page; $j <= $perpage * $page + $perpage; $j ++){
  			if(isset($temp[$i][$j])){
  				$pagedata[$i][$j] = $temp[$i][$j];
  			}
  		}
  	}
  	for($id = 0; $id <= 8; $id ++){
  		array_unshift($pagedata[$id],"<b>".$headers[$id]."</b>");
  	}
  	
  	$table = new html_table();
  	$table->data = $pagedata;
  	echo html_writer::table($table);
  	
  	echo $OUTPUT->paging_bar($emarkingcount, $page, $perpage, $CFG->wwwroot.'/mod/emarking/reports/cycle.php?course='.$course->id);
  	
  	
  	echo emarking_justice_perception($selectedcourse);
  	
// If you are in a eMarking tab.  	
}else{
	// Gantt chart title
	echo html_writer::tag('h4',get_string('cicleganttchart', 'emarking'),array('style' => 'width:100%;'));
   	echo html_writer::div('','', array('id' => 'ganttchart','style' => 'height: 40%;'));
   	
   	echo html_writer::tag('h4',get_string('ciclestackedstatuses', 'emarking'),array('style' => 'width:100%;'));
   	echo html_writer::div('','', array('id' => 'areachart','style' => 'height: 40%;'));
   	
   	echo html_writer::tag('h4',get_string('ciclemarkerscorrections', 'emarking'),array('style' => 'width:100%;'));
   	echo html_writer::div('','', array('id' => 'markerschart','style' => 'height: 40%;'));
}

echo $OUTPUT->footer();

// Scripts for each google chart (must be pass to .js file).
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
  	google.charts.load('current', {'packages':['corechart', 'gantt']});
  	if (<?php echo $currenttab;?> == 0){
  	google.charts.setOnLoadCallback(drawStacked);
  	}
  	function drawStacked() {

  		// Loads the columns
    	var data = new google.visualization.DataTable();
  		data.addColumn('string', '<?php echo get_string("emarkingname", "mod_emarking"); ?>');
  		data.addColumn('number', '<?php echo get_string("dayssenttoprint", "mod_emarking"); ?>');
  		data.addColumn('number', '<?php echo get_string("printeddays", "mod_emarking"); ?>');
  		data.addColumn('number', '<?php echo get_string("digitalizeddays", "mod_emarking"); ?>');
  		data.addColumn('number', '<?php echo get_string("daysincorrection", "mod_emarking"); ?>');
  		data.addColumn('number', '<?php echo get_string("gradeddays", "mod_emarking"); ?>');
  		data.addColumn('number', '<?php echo get_string("publisheddays", "mod_emarking"); ?>');
  		data.addColumn('number', '<?php echo get_string("daysinregrading", "mod_emarking"); ?>');
  		data.addColumn('number', '<?php echo get_string("regradeddays", "mod_emarking"); ?>');
  		data.addColumn('number', '<?php echo get_string("finalpublicationdays", "mod_emarking"); ?>');
  		data.addColumn('number', '<?php echo get_string("totaldays", "mod_emarking"); ?>');
  		data.addColumn({type: 'string', role: 'annotation'});

  		// Loads the data
  		data.addRows(<?php echo json_encode(emarking_time_progression($course->id),null);?>);
  		
  		var view = new google.visualization.DataView(data);
  		view.setColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);
  		
  		var options = {
  			legendFontSize:11,
  			legend: { position: 'top', alignment: 'start ', maxLines: 3},
  		    chartArea: {width: '60%'},
  		    isStacked: true,
  		    bar: {groupWidth: "50%"},
  		    hAxis: {
  		    	title: '<?php echo get_string("days", "mod_emarking");?>',
  		        viewWindow: {min: 0},
  		    },
  		    vAxis: {
  		    	title: 'EMarking'
  		    }
  		};
  		var chart = new google.visualization.BarChart(document.getElementById('summarychart'));
  		chart.draw(view, options);
  	}
</script>
<script style="heigth:40%">
	if(<?php echo $currenttab;?> != 0){
  		google.charts.setOnLoadCallback(drawganttChart);
  	}
  		
    function drawganttChart() {

		var dataarray = <?php echo  json_encode(emarking_gantt_data($emarkingid));?>;
    	var arraylength = dataarray.length;
    	var startdate = 0;
		var enddate = 0;
		
    	for (var i = 0; i < arraylength; i++) {
    		startdate = dataarray[i][3]
    		enddate = dataarray[i][4]
			dataarray[i][3] = new Date(startdate);
			dataarray[i][4] = new Date(enddate);
    	}

    	// Load columns
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Task ID');
		data.addColumn('string', 'Task Name');
		data.addColumn('string', 'Resource');
		data.addColumn('date', 'Start Date');
		data.addColumn('date', 'End Date');
		data.addColumn('number', 'Duration');
		data.addColumn('number', 'Percent Complete');
		data.addColumn('string', 'Dependencies');

		// Load data
		data.addRows(dataarray);
		
		var options = {
			gantt: {
				trackHeight: 30
			}};

		var chart = new google.visualization.Gantt(document.getElementById('ganttchart'));

		chart.draw(data, options);
	}
</script>
<script style="heigth:30%">
	if(<?php echo $currenttab;?> != 0){
    	google.charts.setOnLoadCallback(drawareaChart);
    }
    
	function drawareaChart() {
 
		var data = new google.visualization.DataTable();
		
  		data.addColumn('string', '<?php echo get_string("date", "mod_emarking"); ?>');
  		data.addColumn('number', '<?php echo get_string("digitalized", "mod_emarking"); ?>');
  		data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
  		data.addColumn('number', '<?php echo get_string("incorrection", "mod_emarking"); ?>');
  		data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
  		data.addColumn('number', '<?php echo get_string("graded", "mod_emarking"); ?>');
  		data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
  		data.addColumn('number', '<?php echo get_string("published", "mod_emarking"); ?>');
  		data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
  		data.addColumn('number', '<?php echo get_string("inregrading", "mod_emarking"); ?>');
  		data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
  		data.addColumn('number', '<?php echo get_string("regraded", "mod_emarking"); ?>');
  		data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
  		data.addColumn('number', '<?php echo get_string("finalpublication", "mod_emarking"); ?>');
  		data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});
  		
  		data.addRows(<?php echo  json_encode(emarking_area_chart($emarkingid));?>);
  		
        var options = {
        	legend: { position: 'bottom', alignment: 'start ', maxLines: 3},
        	pointShape: 'star',
        	pointSize: 4,
        	tooltip: {isHtml: true},
        	isStacked: true,
        	vAxis: {minValue: 0}
        };

        var areachart = new google.visualization.AreaChart(document.getElementById('areachart'));
        areachart.draw(data, options);
	}
</script>
<script style="heigth:30%">

	if(<?php echo $currenttab;?> != 0){
    	google.charts.setOnLoadCallback(drawmarkersChart);
    }
	function drawmarkersChart() {
		var markers = <?php echo  json_encode(emarking_markers_corrections($emarkingid, 1));?>;
		var arraylength = markers.length;
		
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Date');
		for (var i = 0; i < arraylength; i++) {
    		data.addColumn('number', markers[i]);
    	
    	}
		
		data.addRows(<?php echo json_encode(emarking_markers_corrections($emarkingid));?>);
		var options = {
			legend: { position: 'bottom', alignment: 'start ', maxLines: 3},
			pointSize: 4,
			pointShape: 'star',
			tooltip: {isHtml: true},
			isStacked: true,
			vAxis: {minValue: 0}
		};
		var markerschart = new google.visualization.LineChart(document.getElementById('markerschart'));
		markerschart.draw(data, options);
	}
</script>
