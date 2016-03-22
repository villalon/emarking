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
 * @copyright 2012 Jorge Villalon <jorge.villalon@uai.cl>
 * @copyright 2014 Nicolas Perez <niperez@alumnos.uai.cl>
 * @copyright 2014 Carlos Villarroel <cavillarroel@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config.php");
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
require_once('locallib.php');
global $DB, $CFG, $SCRIPT, $USER;
// Category with courses.
$categoryid = required_param('category', PARAM_INT);
// Status icon.
$statusicon = optional_param('status', 1, PARAM_INT);
// Page.
$page = optional_param('page', 0, PARAM_INT);
$perpage = 100;
// Exam id in case an exam required to be downloaded through a form.
$examid = optional_param("examid", 0, PARAM_INT);
// If the user is downloading a print form.
$downloadform = optional_param("downloadform", false, PARAM_BOOL);
emarking_verify_logo();
// Validate status (print orders or history).
if ($statusicon < 1 || $statusicon > 2) {
    print_error(get_string("invalidstatus", "mod_emarking"));
}
// Validate category.
if (! $category = $DB->get_record('course_categories', array(
    'id' => $categoryid))) {
    print_error(get_string('invalidcategoryid', 'mod_emarking'));
}
// We are in the category context.
$context = context_coursecat::instance($categoryid);
// User must be logged in.
require_login();
if (isguestuser()) {
    die();
}
// And have printordersview capability.
if (! has_capability('mod/emarking:printordersview', $context)) {
    // TODO: Log invalid access to printorders.
    print_error('Not allowed!');
}
// If the form is being downloaded.
if ($examid && $downloadform) {
    if (! $newexam = $DB->get_record("emarking_exams", array(
        "id" => $examid))) {
        print_error(get_string('invalidcategoryid', 'mod_emarking'));
    }
    if (! $course = $DB->get_record("course", array(
        "id" => $newexam->course))) {
        print_error(get_string('invalidcategoryid', 'mod_emarking'));
    }
    if (! $coursecat = $DB->get_record("course_categories", array(
        "id" => $course->category))) {
        print_error(get_string('invalidcategoryid', 'mod_emarking'));
    }
    $requestedbyuser = $DB->get_record("user", array(
        "id" => $newexam->requestedby));
    emarking_create_printform($context, $newexam, $USER, $requestedbyuser, $coursecat, $course);
    die();
}
$url = new moodle_url('/mod/emarking/print/printorders.php', array(
    'category' => $categoryid));
$ordersurl = new moodle_url('/mod/emarking/print/printorders.php',
        array(
            'category' => $categoryid,
            'status' => $statusicon));
$categoryurl = new moodle_url('/course/index.php', array(
    'categoryid' => $categoryid));
$pagetitle = $statusicon == 1 ? get_string('printorders', 'mod_emarking') : get_string('records', 'mod_emarking');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->requires->js('/mod/emarking/js/printorders.js');
$PAGE->set_pagelayout('course');
$PAGE->navbar->add($category->name, $categoryurl);
$PAGE->navbar->add(get_string('printorders', 'mod_emarking'), $ordersurl);
$PAGE->navbar->add($pagetitle);
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
// Require jquery for modal.
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
// Creating tables and adding columns header.
$examstable = new html_table();
$examstable->head = array(
    get_string('date'),
    get_string('exam', 'mod_emarking'),
    get_string('course'),
    get_string('details', 'mod_emarking'),
    get_string('requestedby', 'mod_emarking'),
    get_string('cost', 'mod_emarking'),
    $statusicon == 1 ? get_string('sent', "mod_emarking") : get_string('examdateprinted', 'mod_emarking'),
    $statusicon == 1 ? ucfirst(get_string('pages', 'mod_emarking')) : get_string('actions'),
    $statusicon == 1 ? get_string('actions') : get_string('printnotification', 'mod_emarking'));
$examstable->id = "fbody";
$examstable->size = array(
    '15%',
    '15%',
    '15%',
    '10%',
    '10%',
    '10%',
    '7%',
    '7%',
    '10%');
$examstable->align = array(
    'left',
    'center',
    'center',
    'center',
    'center',
    'center',
    'center',
    'center',
    'center');
$examstable->colclasses [1] = 'exams_examname';
// Parameters for SQL calls.
if ($statusicon == 1) {
    $statuses = array(
        EMARKING_EXAM_UPLOADED);
} else {
    $statuses = array(
        EMARKING_EXAM_SENT_TO_PRINT,
        EMARKING_EXAM_PRINTED);
}
list($statussql, $params) = $DB->get_in_or_equal($statuses);
$order = $statusicon == 1 ? "e.examdate asc, c.shortname ASC" : "e.examdate desc, c.shortname ASC";
list($childrensql, $childrenparams) = $DB->get_in_or_equal(emarking_get_categories_childs($categoryid));
$childrenparams = array($categoryid, $categoryid);
$sqlcount = " SELECT count(*)
 FROM {emarking_exams} as e
INNER JOIN {course} as c ON (e.course = c.id)
INNER JOIN {course_categories} as cc ON (cc.id = c.category)
WHERE (cc.path LIKE '%/$categoryid' OR cc.path LIKE '%/$categoryid/%') AND e.status {$statussql}";
// Get the count so we can use pagination.
$examscount = $DB->count_records_sql($sqlcount, $params);
$sql = "SELECT e.*,
			c.id as courseid,
			c.fullname as coursefullname,
			u.id as userid,
			CONCAT(u.firstname, ' ', u.lastname) as userfullname,
			cc.name as category,
			e.printingcost as cost
		FROM {emarking_exams} as e
		INNER JOIN {course} as c ON (e.course = c.id)
		INNER JOIN {user} as u ON (e.requestedby = u.id)
		INNER JOIN {course_categories} as cc ON (cc.id = c.category)
		WHERE (cc.path LIKE '%/$categoryid' OR cc.path LIKE '%/$categoryid/%') AND e.status {$statussql}
		ORDER BY " . $order;
// Getting all print orders.
$exams = $DB->get_records_sql($sql, $params, $page * $perpage, ($page + 1) * $perpage); // Status = 1 means still not downloaded.
$currentdate = time();
$current = 0;
foreach ($exams as $exam) {
    // Url for the course.
    $urlcourse = new moodle_url('/course/view.php', array(
        'id' => $exam->course));
    // Url for the user profile of the person who requested the exam.
    $urlprofile = new moodle_url('/user/profile.php', array(
        'id' => $exam->userid));
    // Calculate the total pages and pages to print for this exam.
    $pagestoprint = emarking_exam_total_pages_to_print($exam);
    $actions = html_writer::start_tag("div", array(
        "class" => "printactions"));
    // Download exam link.
    $actions .= html_writer::div(
            $OUTPUT->pix_icon("i/down", get_string("download"), null,
                    array(
                        "examid" => $exam->id,
                        "class" => "downloademarking")));
    // Print directly.
    if ($CFG->emarking_enableprinting) {
        $urlprint = new moodle_url('/mod/emarking/print/printexam.php', array(
            'exam' => $exam->id));
        $actions .= html_writer::div(
                $OUTPUT->action_icon($urlprint, new pix_icon("t/print", get_string("printexam", "mod_emarking"))));
    }
    // Download print form.
    $urldownloadform = new moodle_url('/mod/emarking/print/printorders.php',
            array(
                'category' => $categoryid,
                'examid' => $exam->id,
                'downloadform' => 'true'));
    $actions .= html_writer::div(
            $OUTPUT->action_icon($urldownloadform, new pix_icon("i/report", get_string("downloadform", "mod_emarking"))));
    // Change cost configuration.
    $urlcost = new moodle_url('/mod/emarking/reports/exammodification.php',
            array(
                'exam' => $exam->id,
                'category' => $categoryid,
                'status' => $statusicon));
    $actions .= html_writer::end_tag("div");
    // Calculating date differences to identify exams that are late, are for today and so on.
    if (date("d/m/y", $exam->examdate) === date("d/m/y", $currentdate)) {
        $examstable->rowclasses [$current] = 'examtoday';
    } else if ($currentdate < $exam->examdate) {
        $examstable->rowclasses [$current] = 'examisok';
    } else {
        $examstable->rowclasses [$current] = 'examislate';
    }
    $notification = $exam->notified ? $OUTPUT->pix_icon('t/approve', get_string('printnotificationsent', 'mod_emarking')) :
            '<a href="' . $CFG->wwwroot . '/mod/emarking/print/sendprintnotification.php?id=' . $exam->id . '">' .
             $OUTPUT->pix_icon('i/email', get_string('printsendnotification', 'mod_emarking')) . '</a>';
    $enrolments = html_writer::start_tag("div", array(
        "class" => "printdetails"));
    $enrolments .= emarking_enrolments_div($exam);
    $enrolments .= html_writer::end_tag("div");
    $examstable->data [] = array(
        date("l jS F g:ia", $exam->examdate),
        $exam->name,
        $OUTPUT->action_link($urlcourse, $exam->coursefullname),
        $exam->category . '<br/>' . $enrolments,
        $OUTPUT->action_link($urlprofile, $exam->userfullname),
        '$' . number_format($exam->cost) .
                 $OUTPUT->action_icon($urlcost, new pix_icon("i/edit", get_string("downloadform", "mod_emarking"))),
                $statusicon == 1 ? emarking_time_ago($exam->timecreated) : emarking_time_ago($exam->printdate),
                $statusicon == 1 ? $pagestoprint : $actions,
                $statusicon == 1 ? $actions : $notification);
    $current ++;
}
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle . ' ' . $category->name);
$activetab = $statusicon == 1 ? 'printorders' : 'printordershistory';
echo $OUTPUT->tabtree(emarking_printoders_tabs($category), $activetab);
if (count($exams) > 0) {
    echo core_text::strtotitle(get_string("filter")) . "&nbsp;&nbsp;";
    echo html_writer::tag("input", null, array("id"=>"searchInput"));
    echo html_writer::table($examstable); // Print the table.
    echo $OUTPUT->paging_bar($examscount, $page, $perpage,
            $CFG->wwwroot . '/mod/emarking/print/printorders.php?category=' . $categoryid . '&status=' . $statusicon . '&page=');
} else {
    echo $OUTPUT->notification(get_string('noexamsforprinting', 'mod_emarking'), 'notifyproblem');
}
$downloadurl = new moodle_url('/mod/emarking/print/download.php');
if ($CFG->emarking_usesms) {
    $message = get_string('smsinstructions', 'mod_emarking', $USER);
} else {
    $message = get_string('emailinstructions', 'mod_emarking', $USER);
}
?>
<script type="text/javascript">
$("#searchInput").keyup(function () {
    //split the current value of searchInput
    var data = this.value.split(" ");
    //create a jquery object of the rows
    var jo = $("#fbody").find("tbody").find("tr");
    if (this.value == "") {
        jo.show();
        return;
    }
    //hide all the rows
    jo.hide();

    //Recusively filter the jquery object to get results.
    jo.filter(function (i, v) {
        var $t = $(this);
        for (var d = 0; d < data.length; ++d) {
            if ($t.is(":contains('" + data[d] + "')")) {
                return true;
            }
        }
        return false;
    })
    //show the rows that match.
    .show();
}).focus(function () {
    this.value = "";
    $(this).css({
        "color": "black"
    });
    $(this).unbind('focus');
}).css({
    "color": "#C0C0C0"
});
</script>
<script type="text/javascript">
    var messages = {
		downloadexam: "<?php echo get_string("downloadexam", "mod_emarking") ?>",
		download: "<?php echo get_string("download", "mod_emarking") ?>",
		cancel: "<?php echo get_string("cancel", "mod_emarking") ?>",
		resendcode: "<?php echo get_string("resendcode", "mod_emarking") ?>",
		timeout: "<?php echo get_string("smsservertimeout", "mod_emarking") ?>",
		servererror: "<?php echo get_string("smsservererror", "mod_emarking") ?>"
    };
	var wwwroot = "<?php echo $CFG->wwwroot ?>";
	var downloadurl = "<?php echo $downloadurl ?>";
	var sessionkey = "<?php echo sesskey() ?>";
	var multipdfs = "0";
	var incourse = "0";
</script>
<div id="loadingPanel"></div>
<!-- The panel DIV goes at the end to make sure it is loaded before javascript starts -->
<div id="panelContent">
	<div class="yui3-widget-bd">
		<form>
			<fieldset>
				<p>
					<label for="id"><?php echo $message ?></label><br /> <input
						type="text" name="sms" id="sms" placeholder=""> <select
						onchange="change(this.value);">
						<option value="0"><?php echo get_string("singlepdf", "mod_emarking") ?></option>
						<option value="1"><?php echo get_string("multiplepdfs", "mod_emarking") ?></option>
					</select>
				</p>
			</fieldset>
		</form>
	</div>
</div>
<?php
echo $OUTPUT->footer();
?>
<script type="text/javascript">
	function change(e){
			multipdfs = e;
		}
</script>