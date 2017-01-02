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
 * @copyright 2016-onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once('forms/gradereport_form.php');
global $DB, $USER;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
// URLs for current page.
$url = new moodle_url('/mod/emarking/reports/feedback.php', array(
    'id' => $cm->id));
// First check that the user is logged in.
require_login($course->id);
if (isguestuser()) {
    die();
}
// Validate the user has grading capabilities.
require_capability('mod/emarking:grade', $context);
// Page settings (URL, breadcrumbs and title).
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add(get_string('feedbackreport', 'mod_emarking'));
// Require jquery for modal.
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
// Print eMarking tabs.
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "feedback");
$totalsubmissions = $DB->count_records_sql(
        "
                SELECT COUNT(DISTINCT s.id) AS total
                FROM {emarking_submission} s
                INNER JOIN {emarking_draft} d
                    ON (s.emarking = :emarking AND d.status >= " .
                 EMARKING_STATUS_PUBLISHED . " AND d.submissionid = s.id AND d.grade > 0 AND d.qualitycontrol=0)
                ", array(
                    'emarking' => $emarking->id));
if (! $totalsubmissions || $totalsubmissions == 0) {
    echo $OUTPUT->notification(get_string('nosubmissionspublished', 'mod_emarking'), 'notifyproblem');
    echo $OUTPUT->footer();
    die();
}

$sql = "SELECT 
	d.name rubricname,
	s.id emarkingid,
    a.id criterionid,
    a.description,
    l.id lid,
    l.definition,
    l.score,
    COUNT(c.id) students
    FROM {emarking} s
    INNER JOIN {grading_definitions} d ON (d.id = :definitionid AND s.id = :emarkingid)
    INNER JOIN {gradingform_rubric_criteria} a ON (d.id = a.definitionid)
    INNER JOIN {gradingform_rubric_levels} l ON (a.id = l.criterionid)
    LEFT JOIN {emarking_comment} c ON (c.levelid = l.id)
    GROUP BY s.id, a.id, l.id
    ORDER BY a.sortorder ASC, l.score ASC";
$emarkingstats = $DB->get_recordset_sql($sql, array('definitionid'=>2, 'emarkingid'=>1));
$definition = array();
foreach($emarkingstats as $stat) {
    if(!isset($definition[$stat->criterionid])) {
        $definition[$stat->criterionid] = array();
        $definition[$stat->criterionid]['name'] = $stat->description;
        $definition[$stat->criterionid]['total'] = 0;
    }
    $definition[$stat->criterionid][$stat->lid] = $stat;
    $definition[$stat->criterionid]['total'] += $stat->students;
    $rubricname = $stat->rubricname;
}
$data = array();
foreach($definition as $cid => $criterion) {
    $data[] = array($definition[$cid]['name'],
            html_writer::table(emarking_table_from_criterion($criterion, $cm)));
}
$table = new html_table();
$table->attributes ['class'] = 'criteria';
$table->id = 'rubric-criteria';
$table->data = $data;
$table->head = array('');
$table->colclasses = array(
        'description',
        'levels');
for ($i = 0; $i < count($data); $i ++) {
    $table->rowclasses [$i] = 'criterion' . ($i % 2 == 0 ? ' even' : ' odd');
}
echo $OUTPUT->heading($rubricname, 2);
echo html_writer::div(html_writer::table($table), 'gradingform_rubric');
$sql = "select rawtext
from mdl_emarking_comment
where draft in (select id from mdl_emarking_draft where emarkingid = :emarkingid)
and length(rawtext) > 0";
$comments = $DB->get_recordset_sql($sql, array('emarkingid'=>$emarking->id));
$words = array();
// add Elements
foreach($comments as $comment) {
    $s = preg_replace('/[^a-z\dáéíóúÁÉÍÓÚ]+/i', ' ', urldecode($comment->rawtext));
    $s = preg_split('/\s/', $s);
    foreach($s as $ss) {
        $token = core_text::strtolower($ss);
        if(!isset($words[$token]))
            $words[$token] = 0;
        $words[$token]++;
    }
}
$stopwords = array('algún','alguna','algunas','alguno','algunos','ambos','ampleamos','ante','antes','aquel','aquellas','aquellos','aqui','arriba','atras','bajo','bastante','bien','cada','cierta','ciertas','cierto','ciertos','como','con','conseguimos','conseguir','consigo','consigue','consiguen','consigues','cual','cuando','dentro','desde','donde','dos','el','ellas','ellos','empleais','emplean','emplear','empleas','empleo','en','encima','entonces','entre','era','eramos','eran','eras','eres','es','esta','estaba','estado','estais','estamos','estan','estoy','fin','fue','fueron','fui','fuimos','gueno','ha','hace','haceis','hacemos','hacen','hacer','haces','hago','incluso','intenta','intentais','intentamos','intentan','intentar','intentas','intento','ir','la','largo','las','lo','los','mientras','mio','modo','muchos','muy','nos','nosotros','otro','para','pero','podeis','podemos','poder','podria','podriais','podriamos','podrian','podrias','por','por qué','porque','primero','puede','pueden','puedo','quien','sabe','sabeis','sabemos','saben','saber','sabes','ser','si','siendo','sin','sobre','sois','solamente','solo','somos','soy','su','sus','también','teneis','tenemos','tener','tengo','tiempo','tiene','tienen','todo','trabaja','trabajais','trabajamos','trabajan','trabajar','trabajas','trabajo','tras','tuyo','ultimo','un','una','unas','uno','unos','usa','usais','usamos','usan','usar','usas','uso','va','vais','valor','vamos','van','vaya','verdad','verdadera','verdadero','vosotras','vosotros','voy','yo');
?>
<script src="<?php echo $CFG->wwwroot . '/mod/emarking/lib/jqcloud' ?>/jqcloud-1.0.4.min.js"></script>
<link rel="stylesheet" href="<?php echo $CFG->wwwroot . '/mod/emarking/lib/jqcloud' ?>/jqcloud.css">
<style type="text/css">
      div.jqcloud span.vertical {
        -webkit-writing-mode: vertical-rl;
        writing-mode: tb-rl;
      }
      .gradingform_rubric {
        max-width: 100%;
      }
      .gradingform_rubric .criteria {
        width: 95%;
      }
</style>
<script type="text/javascript">
      var word_list = [
<?php
arsort($words);
$total = 0;
foreach($words as $w => $f) {
    if(strlen($w) < 2 || array_search($w, $stopwords))
        continue;
    $total++;
    $vertical = '';
    if($total % 2 != 0) {
        // $vertical = ', html: {"class": "vertical"}';
    }
    $wurl = urlencode($w);
    $popupurl = $CFG->wwwroot . '/mod/emarking/reports/preview.php' . '?id=' . $cm->id . '&filter=tag&fids='.$wurl;
    echo "{text: \"$w\", weight:$f, link: \"$popupurl\"$vertical},";
}
?>
];
      $(function() {
	        $("#my_favorite_latin_words").jQCloud(word_list);
	  });
</script>
<div id="my_favorite_latin_words" style="width: 95%; height: 250px; border: 1px solid #ccc;"></div>
<?php echo $OUTPUT->footer();
function emarking_table_from_criterion($criterion, $cm) {
    global $OUTPUT;
    $levelstable = new html_table();
    $levelstable->attributes ['class'] = 'none';
    $levelstable->data = array();
    $levelstable->data [0] = array();
    $levelstable->data [1] = array();
    $levelstable->data [2] = array();
    $levelstable->size = array();
    $levelstable->colclasses = array();
    
    foreach ($criterion as $lid => $level) {
        if($lid === 'name') {
            $criterionname = $level;
            continue;
        }
        if($lid === 'total') {
            $total = $level;
            continue;
        }
        $popupurl = new moodle_url('/mod/emarking/reports/preview.php', array('id'=>$cm->id, 'filter'=>'level', 'fids'=>$lid));
        $percentage = $total > 0 ? round($level->students / $total * 100,0) : 0;
        $levelstable->data [0] [] = html_writer::div($level->definition
                , 'definition');
        $levelstable->data [1] [] = html_writer::div($percentage . '%');
        $levelstable->data [2] [] = $OUTPUT->action_link(
                $popupurl, $OUTPUT->pix_icon('t/preview', get_string('viewfeedback','mod_emarking')),
                new popup_action('click', $popupurl, 'emarking' . $cm->id, array(
                        'menubar' => 'no',
                        'titlebar' => 'no',
                        'status' => 'no',
                        'toolbar' => 'no',
                        'width' => 1024,
                        'height' => 600
        )));
        $levelstable->size [] = round(100 / (count($criterion) - 1), 1) . '%';
        $levelstable->colclasses [] = 'level';
    }
    $levelstable->rowclasses[0] = null;
    return $levelstable;
}
