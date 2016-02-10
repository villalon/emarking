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
 * @copyright 2014 onwards Jorge Villalon <villalon@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
class emarking_scalelevels_form extends moodleform {
    public function get_customdata() {
        return $this->_customdata;
    }
    /**
     * Defines forms elements
     */
    public function definition() {
        global $COURSE, $DB, $CFG;
        $criteria = $this->_customdata ['criteria'];
        $context = $this->_customdata ['context'];
        $cmid = $this->_customdata ['id'];
        $emarking = $this->_customdata ['emarking'];
        $scales = $this->_customdata ['scales'];
        $mform = $this->_form;
        // Add header.
        $mform->addElement('header', 'general', get_string('scalelevels', 'mod_emarking'));
        // Hide course module id.
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        // Hide action.
        $mform->addElement('hidden', 'action', 'updatescalelevels');
        $mform->setType('action', PARAM_ALPHA);
        $prev = 0;
        // Array of motives for regrading.
        foreach ($scales as $scale) {
            $mform->addElement('static', 'scaletitle-' . $scale->id, $scale->name);
            if ($prevdata = $DB->get_record("emarking_scale_levels",
                    array(
                        "emarking" => $emarking->id,
                        "scale" => $scale->id))) {
                $prevlevels = explode(',', $prevdata->levels);
            }
            $levels = explode(',', $scale->scale);
            for ($i = 0; $i < count($levels); $i ++) {
                $elementname = 'scalelevels-' . $scale->id . '-' . trim($levels [$i]);
                $mform->addElement('text', $elementname,
                        $levels [$i] . ":<span class='scalelevels'>&nbsp;$prev&nbsp;(%)&nbsp;" . get_string('to') . "</span>",
                        array(
                            'size' => '15'));
                $mform->setType($elementname, PARAM_SEQUENCE);
                $default = $prevdata ? $prevlevels [$i] : ($i + 1) * (100 / count($levels));
                $mform->setDefault($elementname, $default);
                $prev = $default;
            }
        }
        // Add action buttons.
        $this->add_action_buttons();
    }
}