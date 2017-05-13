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
 * @copyright 2017 Hans Jeria (hansjeria@gmail.com)
 *@license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . '/formslib.php');
class evaluatefeedback_form extends moodleform {
	public function definition() {
		global $DB, $USER, $OUTPUT;
		$mform = $this->_form;
		$instance = $this->_customdata;
		$submissionid = $instance['submissionid'];
		
		$getmarkertime = 'SELECT sess.id, sess.userid, (sess.endtime - sess.starttime) AS time
				FROM {emarking_session} AS sess INNER JOIN {emarking_draft} AS draft ON (sess.draftid = draft.id)
				INNER JOIN {emarking_submission} AS sub ON (sub.id = draft.submissionid)
				WHERE sub.id = ?';
		$sessions = $DB->get_records_sql($getmarkertime, array($submissionid));
		$markers = array();
		$totaltime = 0;
		$usertime = 0;
		foreach ($sessions as $session) {
			if ($session->userid == $USER->id) {
				$usertime += $session->time;
			}else {
				$totaltime += $session->time;
				if (!in_array($session->userid, $markers)) {
					$markers [] = $session->userid;
				}
			}		
		}
		
		$valuesarray = array(
				'null' => get_string('choosevalue', 'mod_emarking'),
				'1' => 1,
				'2' => 2,
				'3' => 3,
				'4' => 4,
				'5' => 5
		);
		$mform->addElement('header', 'general', get_string('formevaluatedfeedback', 'mod_emarking'));
		// Informative table
		$mform->addElement('html', '<table class="table table-striped table-hover table-responsive">');
		$mform->addElement('html', '<tr> 
				<th>'.get_string('indicator', 'mod_emarking').'</th>
				<th>'.get_string('value', 'mod_emarking').'</th>
				</tr>'
		);
		$mform->addElement('html', '<tr>
				<td>'.get_string('countofmarkers', 'mod_emarking').'</td>
				<td>'.$OUTPUT->pix_icon('i/cohort', 'markers')."&nbsp;".count($markers).'</td>
				</tr>'
		);
		$mform->addElement('html', '<tr>
				<td>'.get_string('timeincorrection', 'mod_emarking').'</td>
				<td>'.$OUTPUT->pix_icon('i/duration', 'time')."&nbsp;".gmdate('H:i', $totaltime).'</td>
				</tr>'
		);
		$mform->addElement('html', '<tr>
				<td>'.get_string('timestudentview', 'mod_emarking').'</td>
				<td>'.$OUTPUT->pix_icon('i/duration', 'time')."&nbsp;".gmdate('H:i', $usertime).'</td>
				</tr>'
		);
		$mform->addElement('html', '</table>');
		// Table for evaluate feedback
		$mform->addElement('html', '<h5>'.get_string('criteriontoevaluate', 'mod_emarking').'</h5>');
		$mform->addElement('html', '<table class="table table-striped table-hover table-responsive">');
		$mform->addElement('html', '<tr>
				<th>'.get_string('criterion', 'mod_emarking').'</th>
				<th style="text-align:right;" >'.get_string('evaluation', 'mod_emarking').'&nbsp&nbsp&nbsp&nbsp</th>
				</tr>'
		);
		// First criterion
		$mform->addElement('html', '<tr><td style="text-justify: inter-word;">'.get_string('complexitycriterion', 'mod_emarking').'</td><td>');
		$mform->addElement('select', 'complexity', '', $valuesarray);
		$mform->addRule('complexity', null, 'required', null, 'client');
		$mform->addElement('html', '</td></tr>');
		// Second criterion
		$mform->addElement('html', '<tr><td style="text-justify: inter-word;">'.get_string('relevantcriterion', 'mod_emarking').'</td><td>');
		$mform->addElement('select', 'relevant','', $valuesarray);
		$mform->addRule('relevant', null, 'required', null, 'client');
		$mform->addElement('html', '</td></tr>');
		// Third criterion
		$mform->addElement('html', '<tr><td style="text-justify: inter-word;">'.get_string('personalizationcriterion', 'mod_emarking').'</td><td>');
		$mform->addElement('select', 'personalization', '', $valuesarray);
		$mform->addRule('personalization', null, 'required', null, 'client');
		$mform->addElement('html', '</td></tr>');
		$mform->addElement('html', '</table>');
		// Optional comment
		$mform->addElement('textarea', 'optionalcomment', get_string('optionalcomment', 'mod_emarking'),
				array(
						'rows' => 3,
						'cols' => 90,
						'class' => 'smalltext'));
		$mform->addRule('optionalcomment', get_string('maximumchars', '', 1000), 'maxlength', 1000, 'client');
		$mform->setType('optionalcomment', PARAM_TEXT);
		
		$mform->addElement('hidden', 'id', $instance['id']);
		$mform->setType('id', PARAM_INT);
		$this->add_action_buttons(false);
		
	}
	public function validation($data, $files) {			
	}
}