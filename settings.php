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
 * eMarking admin settings.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2012 Jorge Villalon
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
global $PAGE, $CFG;
// Cost settings.
$settings->add(
        new admin_setting_heading('emarking_costsettings', get_string('costconfiguration', 'mod_emarking'),
                get_string('costconfiguration_help', 'mod_emarking')));
// Default cost.
$settings->add(
        new admin_setting_configtext('emarking_defaultcost', get_string('defaultcost', 'mod_emarking'),
                get_string('defaultcost_cost', 'mod_emarking'), 0, PARAM_INT));
// Basic settings.
$settings->add(
        new admin_setting_heading('emarking_basicsettings', get_string('printsettings', 'mod_emarking'),
                get_string('printsettings_help', 'mod_emarking')));
// Minimum days allowed before sending an exam to print.
$choices = array();
for ($i = 0; $i < 100; $i ++) {
    $choices ["$i"] = "$i " . get_string("days");
}
$settings->add(
        new admin_setting_configselect('emarking_minimumdaysbeforeprinting',
                get_string('minimumdaysbeforeprinting', 'mod_emarking'),
                get_string('minimumdaysbeforeprinting_help', 'mod_emarking'), 0, $choices));
// Include or not the logo in the personalized header.
$settings->add(
        new admin_setting_configcheckbox('emarking_includelogo', get_string('includelogo', 'mod_emarking'),
                get_string('includelogo_help', 'mod_emarking'), 0, PARAM_BOOL));
// Logo file.
$settings->add(
        new admin_setting_configstoredfile('emarking_logo', get_string('logo', 'mod_emarking'),
                get_string('logodesc', 'mod_emarking'), 'logo', 1,
                array(
                    'maxfiles' => 1,
                    'accepted_types' => array(
                        'image'))));
// Include or not the student picture in the header.
$settings->add(
        new admin_setting_configcheckbox('emarking_includeuserpicture', get_string('includeuserpicture', 'mod_emarking'),
                get_string('includeuserpicture_help', 'mod_emarking'), 0, PARAM_BOOL));
// Path to user pictures.
$settings->add(
        new admin_setting_configtext('emarking_pathuserpicture', get_string('pathuserpicture', 'mod_emarking'),
                get_string('pathuserpicture_help', 'mod_emarking'), '', PARAM_PATH));
// Regular expression to identify parallel courses.
$settings->add(
        new admin_setting_configtext('emarking_parallelregex', get_string('parallelregex', 'mod_emarking'),
                get_string('parallelregex_help', 'mod_emarking'), '', PARAM_RAW));
// What enrolment methods to include when generating personalized exams.
$settings->add(
        new admin_setting_configtext('emarking_enrolincludes', get_string('enrolincludes', 'mod_emarking'),
                get_string('enrolincludes_help', 'mod_emarking'), 'manual,self', PARAM_PATH));
// Enable printing directly from eMarking to a remote printer using cups.
$settings->add(
        new admin_setting_configcheckbox('emarking_enableprinting', get_string('enableprinting', 'mod_emarking'),
                get_string('enableprinting_help', 'mod_emarking') . "<br/>" .
                         get_string('viewadminprints', 'mod_emarking', $CFG->wwwroot . "/mod/emarking/print/printers.php") .
                         get_string('viewpermitsprinters', 'mod_emarking', $CFG->wwwroot . "/mod/emarking/print/usersprinters.php"),
                        0, PARAM_BOOL));
// If printing should be done to a print server (cups server).
$settings->add(
        new admin_setting_configtext('emarking_printserver', get_string('printserver', 'mod_emarking'),
                get_string('printserver_help', 'mod_emarking'), '', PARAM_TEXT));
// Message for the digitized answers reminder.
$settings->add(
        new admin_setting_configtextarea('emarking_digitizedanswersmessage',
                new lang_string('digitizedanswersmessage', 'mod_emarking'),
                new lang_string('digitizedanswersmessage_desc', 'mod_emarking'), '', PARAM_RAW));
$settings->add(
        new admin_setting_configselect('emarking_daysbeforedigitizingreminder',
                get_string('daysbeforedigitizingreminder', 'mod_emarking'),
                get_string('daysbeforedigitizingreminder_help', 'mod_emarking'), 0, $choices));
// Advanced settings.
$settings->add(
        new admin_setting_heading('emarking_advancedsettings', get_string('settingsadvanced', 'mod_emarking'),
                get_string('settingsadvanced_help', 'mod_emarking')));
// NodeJs settings.
// Enable e-marking chat features.
$settings->add(
        new admin_setting_configtext('emarking_nodejspath', get_string('nodejspath', 'mod_emarking'),
                get_string('nodejspath_help', 'mod_emarking'), '', PARAM_URL));
// SMS communication.
// SMS settings.
$settings->add(
        new admin_setting_heading('emarking_smssettings', get_string('settingssecurity', 'mod_emarking'),
                get_string('settingssecurity_help', 'mod_emarking')));
$settings->add(
        new admin_setting_configcheckbox('emarking_usesms', get_string('usesms', 'mod_emarking'),
                get_string('usesms_help', 'mod_emarking'), 0, PARAM_BOOL));
$settings->add(
        new admin_setting_configtext('emarking_smsurl', get_string('smsurl', 'mod_emarking'),
                get_string('smsurl_help', 'mod_emarking'), '', PARAM_ALPHANUMEXT));
$settings->add(
        new admin_setting_configtext('emarking_smsuser', get_string('smsuser', 'mod_emarking'),
                get_string('smsuser_help', 'mod_emarking'), '', PARAM_ALPHANUMEXT));
$settings->add(
        new admin_setting_configpasswordunmask('emarking_smspassword', get_string('smspassword', 'mod_emarking'),
                get_string('smspassword_help', 'mod_emarking'), '', PARAM_ALPHANUMEXT));
$settings->add(
        new admin_setting_configtext('emarking_mobilephoneregex', get_string('mobilephoneregex', 'mod_emarking'),
                get_string('mobilephoneregex_help', 'mod_emarking'), '', PARAM_RAW));