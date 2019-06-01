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
require_once $CFG->dirroot . '/mod/emarking/lib.php';
// Marking settings.
$settings->add(
        new admin_setting_heading('emarking_markingsettings', get_string('markingsettings', 'mod_emarking'),
                get_string('markingsettings_help', 'mod_emarking')));
// Marking buttons.
$buttonchoices = array(
        EMARKING_BUTTON_RUBRIC => get_string('buttonrubric', 'mod_emarking'),
        EMARKING_BUTTON_COMMENT => get_string('buttoncomment', 'mod_emarking'),
        EMARKING_BUTTON_TICK => get_string('buttontick', 'mod_emarking'),
        EMARKING_BUTTON_CROSS => get_string('buttoncross', 'mod_emarking'),
        EMARKING_BUTTON_PEN => get_string('buttonpen', 'mod_emarking'),
        EMARKING_BUTTON_HIGHLIGHT => get_string('buttonhighlight', 'mod_emarking'),
        EMARKING_BUTTON_QUESTION => get_string('buttonquestion', 'mod_emarking')
);
$settings->add(
        new admin_setting_configmultiselect('emarking_markingbuttonsenabled',
                get_string('markingbuttonsenabled', 'mod_emarking'),
                get_string('markingbuttonsenabled_help', 'mod_emarking'), array_keys($buttonchoices), $buttonchoices));
$yesno = array(
        0 => get_string('no'),
        1 => get_string('yes')
);
$settings->add(
        new admin_setting_configselect('emarking_coloredrubricforced', 
                get_string('coloredrubricforced','mod_emarking'), 
                get_string('coloredrubricforced_help','mod_emarking'), 0, $yesno));
$settings->add(
		new admin_setting_configselect('emarking_formativefeedbackonly',
				get_string('formativefeedbackonly','mod_emarking'),
				get_string('formativefeedbackonly_help','mod_emarking'), 0, $yesno));
// Rubric levels sorting.
$sortingoptions = array(
		1 => get_string('sortlevelsasc1', 'gradingform_rubric'),
		2 => get_string('sortlevelsasc0', 'gradingform_rubric')
);
$settings->add(
		new admin_setting_configselect('emarking_rubriclevelsorting',
				get_string('sortlevelsasc','gradingform_rubric'),
				get_string('rubriclevelsorting_help','mod_emarking'), 1, $sortingoptions));
// Print settings.
$settings->add(
        new admin_setting_heading('emarking_basicsettings', get_string('printsettings', 'mod_emarking'),
                get_string('printsettings_help', 'mod_emarking')));
// Enabled EMarking types
$types = emarking_types_array();
$settings->add(
        new admin_setting_configmultiselect('emarking_enabledtypes',
                get_string('enabledtypes', 'mod_emarking'),
                get_string('enabledtypes_help', 'mod_emarking'), array_keys($types), $types));
// Enabled EMarking upload types
$types = emarking_uploadtypes_array();
$settings->add(
        new admin_setting_configmultiselect('emarking_enableduploadtypes',
                get_string('enableduploadtypes', 'mod_emarking'),
                get_string('enableduploadtypes_help', 'mod_emarking'), array_keys($types), $types));
// Minimum days allowed before sending an exam to print.
$choices = array();
for ($i = 0; $i < 100; $i ++) {
    $choices ["$i"] = "$i " . get_string("days");
}
$settings->add(
        new admin_setting_configselect('emarking_minimumdaysbeforeprinting',
                get_string('minimumdaysbeforeprinting', 'mod_emarking'),
                get_string('minimumdaysbeforeprinting_help', 'mod_emarking'), 0, $choices));
// Include or not a second QR at the bottom of the page for extra recognition.
$settings->add(
		new admin_setting_configcheckbox('emarking_bottomqr', get_string('bottomqr', 'mod_emarking'),
				get_string('bottomqr_help', 'mod_emarking'), 1, PARAM_BOOL));
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
// Enabling the upload of a zip file with digitized answers already processed.
$settings->add(
        new admin_setting_configcheckbox('emarking_enabledigitizedzipfile', get_string('enabledigitizedzipfile', 'mod_emarking'),
                get_string('enabledigitizedzipfile_help', 'mod_emarking'), 0, PARAM_BOOL));
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
// Default cost center.
$settings->add(
        new admin_setting_configtext('emarking_defaultcost', get_string('defaultcost', 'mod_emarking'),
                get_string('defaultcost_cost', 'mod_emarking'), 0, PARAM_INT));
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

//Select type of layout for emarking
$choices = array(
        EMARKING_PAGES_LAYOUT_STANDARD => get_string('page_layout_standard', 'mod_emarking'),
        EMARKING_PAGES_LAYOUT_EMBEDDED => get_string('page_layout_embedded', 'mod_emarking')
);
$settings->add(
        new admin_setting_configselect('emarking_pagelayouttype',
                get_string('pagelayouttype', 'mod_emarking'),
                get_string('pagelayouttype_help', 'mod_emarking'), 0, $choices));
// Security settings.
$settings->add(
        new admin_setting_heading('emarking_securitysettings', get_string('settingssecurity', 'mod_emarking'),
                get_string('settingssecurity_help', 'mod_emarking')));
// Minimum days allowed before sending an exam to print.
$choices = array(
        EMARKING_SECURITY_NO_VALIDATION => get_string('security_novalidation', 'mod_emarking'),
        EMARKING_SECURITY_TOKEN_EMAIL => get_string('security_tokenemail', 'mod_emarking'),
        EMARKING_SECURITY_TOKEN_SMS => get_string('security_tokensms', 'mod_emarking')
);
$settings->add(
        new admin_setting_configselect('emarking_downloadsecurity',
                get_string('downloadsecurity', 'mod_emarking'),
                get_string('downloadsecurity_help', 'mod_emarking'), 0, $choices));
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
