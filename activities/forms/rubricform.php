<?php

$url = new moodle_url($CFG->wwwroot.'/mod/emagrking/activities/editRubric.php');
require_once('rubric.php');
require_once($CFG->dirroot.'/grade/grading/form/rubric/lib.php');
require_once($CFG->dirroot.'/grade/grading/lib.php');

$id = optional_param("id",0, PARAM_INT);

$area = $DB->get_record_sql('SELECT * FROM {grading_areas} WHERE id =  (SELECT max(id) FROM {grading_areas})');
$manager = get_grading_manager($area->id);
$controller = $manager->get_controller('rubric');

list($context, $course, $cm) = get_context_info_array($manager->get_context()->id);
//si existe voy a buscar la rúbrica


//si no existe

//Instantiate simplehtml_form
$mform = new local_ciae_rubric_form(null, array('areaid' => $area->id, 'context' => $context, 'allowdraft' => !$controller->has_active_instances()), 'post', '', array('class' => 'gradingform_rubric_editform'));
$data = $controller->get_definition_for_editing(true);
$returnurl = optional_param('returnurl', $manager->get_management_url(), PARAM_LOCALURL);
$data->returnurl = $returnurl;
$mform->set_data($data);



//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($mform->is_submitted() && $mform->is_validated() && !$mform->need_confirm_regrading($controller)) {

    // Everything ok, validated, re-grading confirmed if needed. Make changes to the rubric.
    $data = $mform->get_data();
    $controller->update_definition($data);

    // If we do not go back to management url and the minscore warning needs to be displayed, display it during redirection.
    $warning = null;
    if (!empty($data->returnurl)) {
        if (($scores = $controller->get_min_max_score()) && $scores['minscore'] <> 0) {
            $warning = get_string('zerolevelsabsent', 'gradingform_rubric').'<br>'.
                html_writer::link($manager->get_management_url(), get_string('back'));
        }
    }
    redirect($returnurl, $warning, null, \core\output\notification::NOTIFY_ERROR);
}

//Código para setear contexto, url, layout


echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();

?>