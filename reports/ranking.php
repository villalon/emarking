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

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
global $CFG, $OUTPUT, $PAGE, $DB;
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
$page = optional_param('page', 0, PARAM_INT);
require_login($course, true);
$url = new moodle_url("/mod/emarking/reports/ranking.php", array(
    'id' => $cm->id));
$context = context_module::instance($cm->id);
$usercangrade = has_capability('mod/assign:grade', $context);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_url($url);
if (! has_capability('mod/emarking:viewpeerstatistics', $context)) {
    redirect(new moodle_url("/mod/emarking/view.php?id=$cm->id"));
}
if (! has_capability('mod/assign:grade', $context) && ! $emarking->peervisibility) {
    redirect(new moodle_url("/mod/emarking/view.php?id=$cm->id"));
}
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), 'ranking');
// Get the grading manager, then method and finally controller.
list($gradingmanager, $gradingmethod, $definition) = emarking_validate_rubric($context);
$sqluserid = $usercangrade ? "$USER->id OR 1 = 1" : $USER->id;
// Query para obtener listado.
$sql = "select
u.id as studentid,
case
when u.id = $sqluserid then concat(u.lastname, ', ' ,u.firstname)
else 'NN'
end as student,
a.description,
a.id as criterionid,
IFNULL(ec.bonus, 0) + IFNULL(b.score, 0) as score,
s.grade as finalgrade
from {emarking_submission} as s
inner join {emarking} as sg on (s.emarking = :emarking AND s.emarking = sg.id)
inner join {course} as co on (sg.course = co.id)
inner join {user} as u on (s.student = u.id)
inner join  {emarking_page} as ep on (ep.submission = s.id)
inner join {emarking_comment} as ec on (ec.page = ep.id AND ec.levelid > 0)
inner join {gradingform_rubric_levels} AS b on (b.id = ec.levelid)
inner join {gradingform_rubric_criteria} AS a on (a.id = b.criterionid)
where s.status >= 20
group by s.id, a.id
order by s.grade, s.student, a.description";
$resultset = $DB->get_recordset_sql($sql, array(
    'emarking' => $emarking->id));
if (! $resultset->valid() || count($resultset) == 0) {
    echo $OUTPUT->notification(get_string('nosubmissionspublished', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}
// Procesamiento de la data, se definen headers dinámicos y data dinámica.
$nameheader = get_string('student', 'grades');
$headers = array(
    $nameheader);
$data = array();
$laststudent = 0;
$currentstudent = 0;
foreach ($resultset as $result) {
    $index = $result->finalgrade * 100000000 + $result->studentid;
    if ($laststudent != $index) {
        $laststudent = $index;
        $currentstudent ++;
    }
    if (! isset($data [$index])) {
        $data [$index] = array();
    }
    $data [$index] ["0a"] = $result->student;
    $data [$index] [$result->description] = $result->score * 1;
    if (! in_array($result->description, $headers)) {
        $headers [] = $result->description;
    }
}
$totalstudents = $currentstudent;
// Set all missing criteria as 0.
foreach ($data as $key => $value) {
    foreach ($headers as $header) {
        if (! isset($data [$key] [$header]) && $header != $nameheader) {
            $data [$key] [$header] = 0;
        }
    }
}
$newdata = array();
foreach ($data as $key => $values) {
    ksort($values);
    $data [$key] = array_values($values);
    $totalscore = 0;
    for ($i = 1; $i < count($data [$key]); $i ++) {
        $totalscore += floatval($data [$key] [$i]);
    }
    $newkey = $key + $totalscore * 1000000;
    $newdata [$newkey] = array_values($values);
}
ksort($newdata);
$data = array_merge(array(
    $headers), $newdata);
$height = $totalstudents * 45;
$current = 0;
$datajs = "[";
foreach ($data as $d) {
    if ($current == 0) {
        $criteria = array();
        foreach ($d as $criterion) {
            $criteria [] = preg_replace("/\r?\n/", "\\n", addslashes($criterion));
        }
        $datajs .= "['" . implode("','", $criteria) . "', {role: 'annotation'}],\n";
    } else {
        $datajs .= "['";
        for ($i = 0; $i < count($d); $i ++) {
            $datajs .= $i == 0 ? $d [$i] . "'," : $d [$i] . ", ";
        }
        $datajs .= " $current],\n";
    }
    $current ++;
}
$datajs .= "\n]";
?>
<?php echo $OUTPUT->heading(get_string('ranking', 'mod_emarking'), 3, 'charttitle'); ?>
<div id="chartRanking" style="width: 100%; text-align:center;"><?php echo $OUTPUT->pix_icon('i/loading', '')?></div>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
// TODO: Show friendly message when we couldn't load Google's library.
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawRanking);
function drawRanking() {
  var data = google.visualization.arrayToDataTable(<?php echo $datajs ?>);
  var options = {
    title: '<?php echo get_string('ranking', 'mod_emarking') ?>',
    isStacked: true,
    legend: 'top',
    fontSize: 12,
    height: <?php echo $height?>
  };
  var chart = new google.visualization.BarChart(document.getElementById('chartRanking'));
  chart.draw(data, options);
}
</script>
<?php
echo $OUTPUT->footer();