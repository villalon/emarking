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
 * @copyright 2014 Jorge Villalon <villalon@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once($CFG->dirroot . '/mod/emarking/print/locallib.php');

global $DB, $USER;

// Get course module id
$cmid = required_param('id', PARAM_INT);

// Validate course module
if(!$cm = get_coursemodule_from_id('emarking', $cmid)) {
        print_error('Módulo inválido');
}

// Validate module
if(!$emarking = $DB->get_record('emarking', array('id'=>$cm->instance))) {
        print_error('Prueba inválida');
}

// Validate course
if(!$course = $DB->get_record('course', array('id'=>$emarking->course))) {
        print_error('Curso inválido');
}

if(!$exam = $DB->get_record('emarking_exams', array('emarking'=>$emarking->id))) {
    print_error('e-marking sin examen');
}

// URLs for current page
$url = new moodle_url('/mod/emarking/reports/justice.php', array('id'=>$cm->id));

$totalstudents = emarking_get_students_count_with_published_grades($emarking->id);

// Course context is used in reports
$context = context_module::instance($cm->id);

// Validate the user has grading capabilities
if(!has_capability ( 'mod/assign:grade', $context )) {
        print_error('No tiene permisos para ver reportes de justicia');
}

// First check that the user is logged in
require_login($course->id);
if (isguestuser()) {
        die();
}

// Page settings (URL, breadcrumbs and title)
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
// $PAGE->navbar->add(get_string('justicereport','mod_emarking'));

echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);

// Print eMarking tabs
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "justicereport" );

// Get rubric instance
list($gradingmanager, $gradingmethod) = emarking_validate_rubric($context, true, true);

// Get the rubric controller from the grading manager and method
$rubriccontroller = $gradingmanager->get_controller($gradingmethod);
$definition = $rubriccontroller->get_definition();

// Calculates the number of criteria for this evaluation
$numcriteria = 0;
if($rubriccriteria = $rubriccontroller->get_definition()) {
        $numcriteria = count($rubriccriteria->rubric_criteria);
}

$emarkingids = ''.$emarking->id;

$studentsanswered = $DB->get_records_sql("
                SELECT
	s.emarking,
	e.course,
    c.shortname,
    c.fullname,
	COUNT(DISTINCT s.student) AS total,
	COUNT(DISTINCT p.id) AS answered
FROM mdl_emarking_submission AS s
INNER JOIN mdl_emarking_draft AS d ON (d.qualitycontrol = 0 AND d.submissionid = s.id AND s.emarking in ($emarkingids) AND d.status >= :status)
INNER JOIN mdl_emarking AS e ON (s.emarking = e.id)
INNER JOIN mdl_course AS c ON (e.course = c.id)
LEFT JOIN mdl_emarking_perception AS p ON (p.submission = s.id)
GROUP BY s.emarking",
    array("status"=>EMARKING_STATUS_PUBLISHED)
                );

foreach($studentsanswered as $section) {
    $pending = ($section->total - $section->answered);
    $datatable[$section->emarking] = "['Status', 'Students'],
    ['Answered', $section->answered],
    ['Not yet', $pending]
    ";
}


if($emarking->justiceperception == EMARKING_JUSTICE_PER_CRITERION) {
    $sqljustice = "SELECT  'of' as name, pc.criterion, c.description, pc.overall_fairness AS level, COUNT(DISTINCT s.student) as total
FROM mdl_emarking_perception as p
INNER JOIN mdl_emarking_submission as s ON (p.submission = s.id)
INNER JOIN mdl_emarking_draft as d ON (d.submissionid = s.id AND d.qualitycontrol = 0 AND d.status >= :status)
INNER JOIN mdl_emarking_perception_criteria as pc ON (p.id = pc.perception)
INNER JOIN mdl_gradingform_rubric_criteria as c ON (pc.criterion = c.id)
WHERE s.emarking = :emarkingid
GROUP BY pc.criterion, pc.overall_fairness
        UNION ALL
        SELECT 'er' as name, pc.criterion, c.description, pc.expectation_reality AS level, COUNT(DISTINCT s.student) as total
FROM mdl_emarking_perception as p
INNER JOIN mdl_emarking_submission as s ON (p.submission = s.id)
INNER JOIN mdl_emarking_draft as d ON (d.submissionid = s.id AND d.qualitycontrol = 0 AND d.status >= :status2)
INNER JOIN mdl_emarking_perception_criteria as pc ON (p.id = pc.perception)
INNER JOIN mdl_gradingform_rubric_criteria as c ON (pc.criterion = c.id)
WHERE s.emarking = :emarkingid2
GROUP BY pc.criterion, pc.expectation_reality";
} else {
    $sqljustice = "SELECT 'of' as name, overall_fairness AS level, COUNT(DISTINCT s.student) as total
FROM mdl_emarking_perception as p
INNER JOIN mdl_emarking_submission as s ON (p.submission = s.id)
INNER JOIN mdl_emarking_draft as d ON (d.submissionid = s.id AND d.qualitycontrol = 0 AND d.status >= :status)
WHERE s.emarking = :emarkingid
GROUP BY overall_fairness
        UNION ALL
        SELECT 'er' as name, expectation_reality AS level, COUNT(DISTINCT s.student) as total
FROM mdl_emarking_perception as p
INNER JOIN mdl_emarking_submission as s ON (p.submission = s.id)
INNER JOIN mdl_emarking_draft as d ON (d.submissionid = s.id AND d.qualitycontrol = 0 AND d.status >= :status2)
WHERE s.emarking = :emarkingid2
GROUP BY expectation_reality";
}

$justiceperception = $DB->get_recordset_sql($sqljustice,
                array('status'=>EMARKING_STATUS_PUBLISHED, 'status2'=>EMARKING_STATUS_PUBLISHED, 'emarkingid'=>$emarking->id, 'emarkingid2'=>$emarking->id));

$fairnessdata = array();
for($i=-4; $i<=4; $i++) {
    $fairnessdata[$i] = 0;
}

$criteria = array();
foreach($definition->rubric_criteria as $criterion) {
    $criteria[$criterion["id"]] = $criterion["description"];
}

$datajusticeof = array();
$datajusticeer = array();
if($emarking->justiceperception == EMARKING_JUSTICE_PER_CRITERION) {
    foreach($definition->rubric_criteria as $criterion) {
        $datajusticeer[$criterion["id"]] = $fairnessdata;
        $datajusticeof[$criterion["id"]] = $fairnessdata;
    }
} else {
    $datajusticeof[0] = $fairnessdata;
    $datajusticeer[0] = $fairnessdata;
}

foreach($justiceperception as $justice) {
    if($emarking->justiceperception == EMARKING_JUSTICE_PER_CRITERION) {
        if($justice->name === 'of') {
            $datajusticeof[$justice->criterion][$justice->level] = $justice->total;
        } else {
            $datajusticeer[$justice->criterion][$justice->level] = $justice->total;
        }
    } else {
        if($justice->name === 'of') {
            $datajusticeof[0][$justice->level] = $justice->total;
        } else {
            $datajusticeer[0][$justice->level] = $justice->total;
        }
    }
}

    if($emarking->justiceperception == EMARKING_JUSTICE_PER_CRITERION) {
        foreach($criteria as $cid => $criterion) {
            $datatablejustice[$cid] = "[ ['Level', 'Overall fairness', 'Expectation vs reality' ],";
        }
    } else {
        $datatablejustice = "[ ['Level', 'Overall fairness', 'Expectation vs reality' ],";
    }
for($i=-4;$i<=4;$i++) {
    if($emarking->justiceperception == EMARKING_JUSTICE_PER_CRITERION) {
            foreach($criteria as $cid => $criterion) {
                $datatablejustice[$cid] .= "    ['$i', ".$datajusticeof[$cid][$i].", ".$datajusticeer[$cid][$i]."],";
            }
    } else {
        $datatablejustice .= "    ['$i', ".$datajusticeof[0][$i].", ".$datajusticeer[0][$i]."],";
    }
}

if($emarking->justiceperception == EMARKING_JUSTICE_PER_CRITERION) {
            foreach($criteria as $cid => $criterion) {
                $datatablejustice[$cid] .= "]";
            }
    } else {
        $datatablejustice .= "]";
    }

    foreach(explode(",", $emarkingids) as $eid) {
        echo "<div id='statusdonut$eid' style='width: 100%; height: 300px;'></div>";
    }

    if($emarking->justiceperception == EMARKING_JUSTICE_PER_CRITERION) {
        foreach($criteria as $cid => $criterion) {
            echo '<div id="criteriabarchart-'.$cid.'" style="width: 100%; height: 500px;"></div>';
        }
    } else {
        echo '<div id="criteriabarchart" style="width: 100%; height: 500px;"></div>';
    }
?>
<script
        type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
          // TODO: Show friendly message when we couldn't load Google's library
      google.load("visualization", "1", {packages:["corechart"]});

      <?php foreach(explode(",", $emarkingids) as $eid) { ?>
      google.setOnLoadCallback(drawStatusDonut<?php  echo $eid ?>);
      
        function drawStatusDonut<?php echo $eid ?>() {
                var data = google.visualization.arrayToDataTable(
                                [
                                <?php echo $datatable[$eid]; ?>
                        ]);

                var options = {
                        title: 'Justice perception answers',
                        pieHole: 0,
                        legend: {position: 'bottom'},
                    };

                var chart = new google.visualization.PieChart(document.getElementById('statusdonut<?php echo $eid ?>'));
                chart.draw(data, options);
    }
    <?php } ?>
        
    google.setOnLoadCallback(drawHistogram);
    // Grades histogram
    function drawHistogram() {

        <?php
        if($emarking->justiceperception == EMARKING_JUSTICE_PER_CRITERION) {
            foreach($criteria as $cid => $criterion) {
                echo 'var data'.$cid.' = google.visualization.arrayToDataTable('.$datatablejustice[$cid].');';
            }
        } else {
                echo 'var data = google.visualization.arrayToDataTable('.$datatablejustice.');';
        }
        ?>
      

        <?php
                if($emarking->justiceperception == EMARKING_JUSTICE_PER_CRITERION) {
                    foreach($criteria as $cid => $criterion) {
                        echo "var options$cid = {
                      animation: {duration: 500},
                      title: '$criterion',
                      hAxis: {title: 'Level', titleTextStyle: {color: 'black'}},
                      vAxis: {format:'#'},
                      legend: 'top'
                    };";
                    }
                } else {
                        echo "var options = {
                      animation: {duration: 500},
                      title: 'Justice perception for exam',
                      hAxis: {title: 'Level', titleTextStyle: {color: 'black'}},
                      vAxis: {format:'#'},
                      legend: 'top'
                    };";
                }
                ?>

                

      <?php
    	        if($emarking->justiceperception == EMARKING_JUSTICE_PER_CRITERION) {
    	            foreach($criteria as $cid => $criterion) {
    	                echo "var chart$cid = new google.visualization.ColumnChart(document.getElementById('criteriabarchart-$cid'));";
                        echo "chart$cid.draw(data$cid, options$cid);";
    	            }
    	        } else {
    	            echo "var chart = new google.visualization.ColumnChart(document.getElementById('criteriabarchart'));";
                    echo "chart.draw(data, options);";
    	        }
    ?>
    	      
      
      


    }
    
</script>
<?php 

echo $OUTPUT->footer();