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
 * @copyright 2012-2015 Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// EMARKING TYPES WORKFLOW.
$string ['orsentexam'] = 'Use previously sent exam';
$string ['orsentexam_help'] = 'You can use the PDF from a previously sent exam.';
$string ['print'] = 'Print';
$string ['onscreenmarking'] = 'On Screen Marking';
$string ['scan'] = 'Scan';
$string ['enablescan'] = 'Enable scan';
$string ['scanisenabled'] = 'Scanning is enabled. Marking is done manually and student answers are digitized and uploaded to the system.';
$string ['scanwasenabled'] = 'Scan was enabled successfully';
$string ['osmisenabled'] = 'Scanning is enabled. Student answers are digitized and uploaded to the system for On Screen Marking using a rubric.';
$string ['enableosm'] = 'Enable OSM';
$string ['enableosm_help'] = 'You must enable scan before you can enable OSM';
$string ['emarkingwithnoexam'] = 'There is a configuration problem with your activity. Please notify the administrator.';
$string ['printsettings'] = 'Print settings';
$string ['printsettings_help'] = 'Help for print settings';
$string ['markingtypemandatory'] = 'You must select a marking type';
$string ['emarkingviewed'] = 'Feedback viewed';
$string ['updateemarkingtype'] = 'You are about to {$a->message} in {$a->name}. There are no risks in doing so, you can change this later in settings at any time.';
// REGRADES.
$string ['justification'] = 'Justification';
$string ['justification_help'] = 'You must justify your regrade request';
$string ['noregraderequests'] = 'There are no regrade requests';
$string ['regrade'] = 'Regrade';
$string ['regradingcomment'] = 'Regrading comment';
$string ['missasignedscore'] = 'Missasigned score';
$string ['unclearfeedback'] = 'Unclear feedback';
$string ['statementproblem'] = 'Question statement problem';
$string ['errorcarriedforward'] = 'Error carried forward';
$string ['correctalternativeanswer'] = 'Alternative answer which is correct';
$string ['other'] = 'Other';
$string ['regradespending'] = 'regrades';
$string ['regraderestrictdates'] = 'Restrict dates for regrading';
$string ['regraderestrictdates_help'] = 'Sets open and close dates for submitting regrade requests';
$string ['regradesopendate'] = 'Regrade requests open date';
$string ['regradesopendate_help'] = 'Date from which the students can submit regrading requests';
$string ['regradesclosedate'] = 'Regrade requests close date';
$string ['regradesclosedate_help'] = 'Date until the students can submit regrading requests';
$string ['mustseeexambeforeregrade'] = 'You must review your exam feedback before you can request a regrade.';
$string ['viewmore'] = 'View more';
$string ['cannotmodifyacceptedregrade'] = 'A regrade request that has been accepted can not be modified';
$string ['criterionrequired'] = 'You must select a criterion';
$string ['justificationrequired'] = 'You must justify your request';
// MARKERS AND PAGES OSM CONFIGURATION.
$string ['markerspercriteria'] = 'Markers';
$string ['pagespercriteria'] = 'Pages';
$string ['markerscanseewholerubric'] = 'Markers can see the entire rubric.';
$string ['markerscanseeallpages'] = 'Markers can see all pages.';
$string ['markerscanseeselectedcriteria'] = 'Markers can see only assigned criteria.';
$string ['markerscanseenothing'] = 'Pages are assigned to criteria but no markers are assigned to criteria. Only admins will be able to see pages.';
$string ['markerscanseepageswithcriteria'] = 'Markers can see only those pages assigned to the criteria they can mark.';
$string ['assignedmarkers'] = 'Assigned markers';
$string ['assignedoutcomes'] = 'Assigned outcomes';
$string ['nooutcomesassigned'] = 'There are no outcomes associated to this exam\'s rubric';
$string ['assignmarkerstocriteria'] = 'Add markers to criteria';
$string ['assignoutcomestocriteria'] = 'Add outcomes to criteria';
$string ['currentstatus'] = 'Current status';
$string ['noneditingteacherconfiguration'] = 'As a Non-editing teacher you can not change de settings.';
$string ['coursehasnooutcomes'] = 'The course has no outcomes associated. You must also associate outcomes to the emarking activity. You must associate at least one outcome in order to link them with the rubric.';
$string ['gotooutcomessettings'] = 'Go to outcome settings';
$string ['emarkinghasnooutcomes'] = 'The emarking activity has no outcomes associated. You must associate at least one outcome in order to link them with the rubric.';
$string ['gotoemarkingsettings'] = 'Go to emarking settings';
$string ['emarkingdst'] = 'Destination eMarking';
$string ['emarkingdst_help'] = 'Choose the eMarking activities that will copy their settings from the current activity';
$string ['override'] = 'Override rubric in destination eMarking';
$string ['override_help'] = 'Overrides any rubric configured in the destination eMarking activity and copies the current one';
$string ['overridemarkers'] = 'Override designated markers in destination eMarking';
$string ['overridemarkers_help'] = 'Overrides any designated marker in the destination eMarking activity by copying current markers';
$string ['noparallelemarkings'] = 'There are no eMarking exams in parallel courses';
$string ['scalelevels'] = 'Percentages per level';
$string ['studentachievement'] = 'Students %';
$string ['level'] = 'Achievement level';
$string ['outcomesnotconfigured'] = 'Outcomes have not yet been setup for this E-Marking activity';
// GENERAL.
$string ['criteria'] = 'Criteria';
$string ['deleterow'] = 'Delete row';
$string ['nodejspath'] = 'NodeJS path';
$string ['nodejspath_help'] = 'The full Node JS path including protocol, ipaddress and port. e.g: http://127.0.0.1:9091';
$string ['emarkinggraded'] = 'eMarking graded';
$string ['answerkey'] = 'Answer key';
// PERMISSIONS
$string['emarking:activatedelphiprocess'] = '';
$string['emarking:addinstance'] = '';
$string['emarking:assignmarkers'] = '';
$string['emarking:configuredelphiprocess'] = '';
$string['emarking:grade'] = '';
$string['emarking:manageanonymousmarking'] = '';
$string['emarking:managedelphiprocess'] = '';
$string['emarking:managespecificmarks'] = '';
$string['emarking:regrade'] = '';
$string['emarking:submit'] = '';
$string['emarking:supervisegrading'] = '';
$string['emarking:uploadexam'] = '';
$string['emarking:view'] = '';
$string['emarking:viewpeerstatistics'] = '';
// SMS SECURITY.
$string ['download'] = 'Download';
$string ['cancel'] = 'Cancel';
$string ['resendcode'] = 'Resend security code';
$string ['smsservertimeout'] = 'SMS service timeout. Please notify the administrator.';
$string ['smsservererror'] = 'SMS server communication error. Please try again later.';
// EXAMS.
$string ['examdetails'] = 'Exam details';
$string ['examalreadysent'] = 'The exam was already sent to print. It cannot be modified.';
$string ['examdate'] = 'Exam date';
$string ['examdate_help'] = 'Date and time in which the exam will be taken';
$string ['examdateinvalid'] = 'Invalid exam date, it should be at least {$a->mindays} working days in advance.';
$string ['examdateinvaliddayofweek'] = 'Invalid exam date, only from Monday to Fridays and Saturdays before 4pm.';
$string ['examdateprinted'] = 'Print date';
$string ['examdeleted'] = 'Exam deleted. Please wait while you are redirected';
$string ['examid'] = 'Exam id';
$string ['examinfo'] = 'Exam information';
$string ['examhasnopdf'] = 'El examen to tiene un archivo PDF asociado. Este error es grave, por favor notifique al administrador.';
$string ['examname'] = 'Exam name';
$string ['examname_help'] = 'Exam name e.g: Final examn, Mid-term.';
$string ['exam'] = 'Exam';
$string ['exams'] = 'Exams';
$string ['examnotavailable'] = 'Your exam is not available';
$string ['examstatusdownloaded'] = 'Downloaded';
$string ['examstatusprinted'] = 'Printed';
$string ['examstatussent'] = 'Sent for printing';
$string ['downloadexam'] = 'Download exam';
$string ['comment_help'] = 'Comment for the printing staff.';
// JUSTICE PERCEPTION.
$string ['er-4'] = '-4 (much worse than I deserved)';
$string ['er-3'] = '-3';
$string ['er-2'] = '-2';
$string ['er-1'] = '-1';
$string ['er0'] = '0 (about what I deserved)';
$string ['er1'] = '1';
$string ['er2'] = '2';
$string ['er3'] = '3';
$string ['er4'] = '4 (much more than I deserved)';
$string ['of-4'] = '-4 (extremely unfair)';
$string ['of-3'] = '-3';
$string ['of-2'] = '-2';
$string ['of-1'] = '-1';
$string ['of0'] = '0 (neither fair nor unfair)';
$string ['of1'] = '1';
$string ['of2'] = '2';
$string ['of3'] = '3';
$string ['of4'] = '4 (extremely fair)';
$string ['justiceperceptionprocess'] = 'How would you rate the fairness of the marking of the evaluation?';
$string ['justiceperceptionexpectation'] = 'How does your grade for the first assignment compare to the mark you think you deserved to get for this assignment?';
$string ['justiceperceptionprocesscriterion'] = 'How would you rate the fairness of the marking of this criterion?';
$string ['justiceperceptionexpectationcriterion'] = 'How does your score for this question compare to the score you think you deserved?';
$string ['thanksforjusticeperception'] = 'Thanks for expressing your opinion';
$string ['justicedisabled'] = 'Disabled';
$string ['justicepersubmission'] = 'Ask one opinion per exam';
$string ['justicepercriterion'] = 'Ask one opinion per criterion';
$string ['justice'] = 'Justice';
$string ['justiceperception'] = 'Ask students for their justice perception';
$string ['justiceperception_help'] = 'This options allows students to provide their perception of justice regarding the marking process (procedural justice) and the result (distributive justice). It can be set to ask for one opinion for the whole exam, or one opinion per criterion.';
$string ['agreementflexibility'] = 'Agreement flexibility';
$string ['agreementflexibility_help'] = 'Defines the maximum difference between the grades given by a marker and the average grade to be considered an outlier.';
$string ['agreementflexibility00'] = 'Strict (grades must be identical)';
$string ['agreementflexibility20'] = 'Flexible (allows differences up to 20%)';
$string ['agreementflexibility40'] = 'Relaxed (allows differences up to 40%)';
$string ['firststagedate'] = 'Max date for marking';
$string ['firststagedate_help'] = 'Maximum date for markers to grade all exams';
$string ['secondstagedate'] = 'Max date for agreement';
$string ['secondstagedate_help'] = 'Maximum date for markers to reach agreement';
$string ['mustseefeedbackbeforejustice'] = 'You must review your exam feedback before you can give your opinion.';
$string ['reviewpeersfeedback'] = 'Review peers';
// PREDEFINED COMMENTS.
$string ['datahasheaders'] = 'Ignore first row';
$string ['predefinedcomments'] = 'Predefined comments';
$string ['predefinedcomments_help'] = 'Paste a column from Excel (with or without a header) to import all the rows as predefined comments.';
$string ['onlyfirstcolumn'] = 'Only the first column will be imported. A sample of the data is shown below:';
$string ['onecolumnrequired'] = 'At least one column is required';
$string ['twolinesrequired'] = 'At least two lines are required';
$string ['mobilephoneregex'] = 'Mobile phone regex';
$string ['mobilephoneregex_help'] = 'A regular expression to validate a correct mobile phone';
$string ['invalidphonenumber'] = 'Invalid phone number, we expect a full international number (ex: +56912345678)';
$string ['errorsendingemail'] = 'An error ocurred while sending the email';
$string ['second'] = 'Second';
$string ['seconds'] = 'Seconds';
$string ['processomr'] = 'Process OMR';
$string ['signature'] = 'Signature';
$string ['advanced'] = 'Advanced';
$string ['photo'] = 'Photo';
$string ['settingupprinting'] = 'Setting up printing';
$string ['printing'] = 'Printing';
$string ['tokenexpired'] = 'Security token has expired. Please get a new one.';
$string ['otherenrolment'] = 'Other enrolment types.';
$string ['sent'] = 'Sent';
$string ['replied'] = 'Replied';
$string ['usernotloggedin'] = 'User is not logged in';
$string ['invalidsessionkey'] = 'Invalid session key';
$string ['emarkingsecuritycode'] = 'eMarking security code';
$string ['savechanges'] = 'Save changes';
$string ['changessaved'] = 'Changes saved';
$string ['qualitycontrol'] = 'Quality Control';
$string ['markersqualitycontrol'] = 'Quality Control markers';
$string ['markersqualitycontrol_help'] = 'Quality Control markers are the ones that will mark and grade the QC exams which will be used to calculate inter-marker agreement.';
$string ['enablequalitycontrol'] = 'Enable Quality Control';
$string ['enablequalitycontrol_help'] = 'If QC in enabled, a set of QC exams will be assigned to the QC markers for extra marking and therefore calculate inter-marker agreement.';
$string ['qualitycontroldescription'] = 'A set of exams will be assigned to the selected markers for extra marking and therefore calculate inter-marker agreement.';
// MARKERS TRAINING.
$string ['notenoughmarkersfortraining'] = 'Not enough markers for training, please enrol markers as non editing teachers for training.';
$string ['notenoughmarkersforqualitycontrol'] = 'No markers were selected for quality control. Please select at least one marker as responsible for marking the control exams.';
$string ['markerstrainingnotforstudents'] = 'This is a markers training activity. You have no access to its details.';
$string ['updatemark'] = 'Update mark';
// PEER REVIEW.
$string ['notenoughstudenstforpeerreview'] = 'Not enough students enrolled for a peer review session';
$string ['reassignpeers'] = 'Reassign peers';
// ANONYMOUS.
$string ['studentanonymous_markervisible'] = 'Student anonymous / Marker visible';
$string ['studentanonymous_markeranonymous'] = 'Student anonymous / Marker anonymous';
$string ['studentvisible_markervisible'] = 'Student visible / Marker visible';
$string ['studentvisible_markeranonymous'] = 'Student visible / Marker anonymous';
$string ['anonymous'] = 'Anonymous';
$string ['anonymous_help'] = 'Set to yes if you want the marking process to be blind. Student names and photos are hidden.';
$string ['anonymousstudent'] = 'Anonymous student';
$string ['yespeerisanonymous'] = 'Yes (Peer is anonymous)';
$string ['viewpeers'] = 'Students can review peers\' exams';
$string ['viewpeers_help'] = 'Students are allowed to see their peers\' exams in an anonymous way';
// EMARKING IMPORT RUBRIC.
$string ['rubriclevel'] = 'Rubric level';
$string ['importrubric'] = 'Import rubric';
$string ['pastefromexcel'] = 'Paste from Excel';
$string ['pastefromexcel_help'] = 'Select the desired cells in Excel, copy them  and then paste them in the text box';
$string ['rubricneeded'] = 'eMarking requires a rubric for marking, please create one by hand or import it from Excel';
$string ['rubricdraft'] = 'eMarking requires a ready rubric, the rubric is in status draft. Please complete rubric';
$string ['confirmimport'] = 'Below is the rubric that will be created, please check that all details are correct. NOTE: The rubric can be modified later in the editor.';
// E-MARKING TYPES.
$string ['markingtype'] = 'Marking type';
$string ['markingtype_help'] = '<h2>Marking types</h2><br>
		There are different types of marking procedures available in e-marking:
		<ul>
			<li><b>Print only</b>: Exams are sent for printing through the system, marking is done manually and grades can be optionally uploaded to gradebook.</li>
			<li><b>Print and scan</b>: Exams are sent for printing through the system, marking is done manually and student answers are digitized and uploaded to the system. Optionally grades can be set in the gradebook.</li>
            <li><b>On Screen Marking</b>: Exams are printed and answers digitized and marked on screen according to a rubric. Exams can be marked more than once for quality control on inter-marker agreement.</li>
			<li><b>Markers training</b>: Exams do not belong to students in the course. All markers grade all exams and the process does not close until 100% agreement is reached between markers.</li>
			<li><b>Student training</b>: Exams do not belong to students in the course. Students grade exams as a way to practice for their own evaluations.</li>
			<li><b>Peer review</b>: Students grade their peers according to the groups configuration. If groups are configured as visible or separated each student in a group marks all exams from another group.</li>
		</ul>';
$string ['type_normal'] = 'On Screen Marking';
$string ['type_markers_training'] = 'Markers training';
$string ['type_student_training'] = 'Student training';
$string ['type_peer_review'] = 'Peer review';
$string ['type_print_only'] = 'Print only';
$string ['type_print_scan'] = 'Print and scan';
// EMARKING PRINTING.
$string ['digitizedanswersreminder'] = 'Digitized answers reminder';
$string ['daysbeforedigitizingreminder'] = 'Days before reminder';
$string ['daysbeforedigitizingreminder_help'] = 'Number of days to wait before sending the reminder message to teachers regarding the digitizing of the answers to her exam.';
$string ['digitizedanswersmessage'] = 'Digitized answers reminder message';
$string ['digitizedanswersmessage_desc'] = 'This message will be sent to teachers once the period after digitizing the answers expires.';
$string ['viewadminprints'] = '<a href="{$a}">Manage printers</a>';
$string ['viewpermitsprinters'] = '<br/><a href="{$a}">Manage printers permissions</a>';
$string ['aofb'] = '{$a->identified} of {$a->total}';
$string ['printserver'] = 'Print server IP number';
$string ['printserver_help'] = 'Tells Moodle to print an E-Marking evaluation to a cups server (leave blank if there is no print server).';
// EMARKING UPLOAD ANSWERS.
$string ['confirmprocess'] = 'Confirm process';
$string ['confirmprocessfile'] = 'You are about to process file {$a->file} as student submissions for assignment {$a->assignment}.<br> This will delete any previous submissions from students on that assignment. Are you sure?';
$string ['uploadanswers_help'] = 'In this page you can upload the digitized answers from your students. The format is a zip file containing two png files for each page a student has (one is the anonymous version). This file can be obtained using the eMarking desktop application that can be downloaded <a href="">here</a>';
$string ['uploadanswers'] = 'Upload answers';
$string ['uploadanswersuccessful'] = 'Upload answers successful';
// REPORTS.
$string ['gradereport'] = 'Grades report';
$string ['gradereport_help'] = 'This report shows basic statistics and a three graphs. It includes the grades from a particular eMarking activity but other activities from other courses can be added if the parallel courses settings are configured.<br/>
			<strong>Basic statistics:</strong>Shows the average, quartiles and ranges for the course.<br/>
			<strong>Average graph:</strong>Shows the average and standard deviation.<br/>
			<strong>Grades histogram:</strong>Shows the number of students per range.<br/>
			<strong>Approval rate:</strong>Shows the approval rate for the course.<br/>
			<strong>Criteria efficiency:</strong>Shows the average percentage of the maximum score obtained by the students.';
$string ['stdev'] = 'Deviation';
$string ['min'] = 'Minimum';
$string ['quartile1'] = '1st quartile';
$string ['median'] = 'Median';
$string ['quartile3'] = '3rd quartile';
$string ['max'] = 'Maximum';
$string ['lessthan'] = 'Less than {$a}';
$string ['between'] = '{$a->min} to {$a->max}';
$string ['greaterthan'] = 'Greater than {$a}';
$string ['pagesperexam'] = 'Pages per exam';
$string ['printdetails'] = 'Print details';
$string ['apply'] = 'Apply';
$string ['statuspercriterion'] = 'Status per criterion';
// EMARKING COST REPORT.
$string ['period'] = 'Period';
$string ['subcategoryname'] = 'Subcategory name';
$string ['reports'] = 'Reports';
$string ['teacherrankingtitle'] = 'Ranking: Teacher name v/s activities';
$string ['courserankingtitle'] = 'Ranking: course name v/s printed pages';
$string ['costreport'] = 'Reports';
$string ['totalactivies'] = 'Number of activities';
$string ['emarkingcourses'] = 'Courses with emarking';
$string ['meantestlenght'] = 'Mean test pages';
$string ['totalprintedpages'] = 'Total printed pages';
$string ['reportbuttonsheader'] = 'eMarking costs';
$string ['secondarybuttonsheader'] = 'eMarking sub-category costs';
$string ['courseranking'] = 'Course name';
$string ['teacherranking'] = 'Teacher name';
$string ['printingcost'] = 'Cost for impresed page';
$string ['printingcost_help'] = 'The cost that have each page you print';
$string ['totalprintingcost'] = 'Total printing cost';
$string ['costsettings'] = 'Settings';
$string ['costconfigtab'] = 'Set category cost';
$string ['costcategorytable'] = 'View category costs';
$string ['editcost'] = 'Edit';
$string ['activities'] = 'Activities';
$string ['emarkingcourses'] = 'eMarking courses';
$string ['meanexamlength'] = 'Avg. exam lenght';
$string ['totalprintedpages'] = 'Printed pages';
$string ['totalcost'] = 'Total cost';
$string ['numericvalue_help'] = 'Must enter a numeric cost for each page';
$string ['numericvalue'] = 'Enter a numeric value';
$string ['validcostcenter'] = 'Must enter numeric value';
$string ['validcostcenter_help'] = 'Must enter valid cost center number';
$string ['categoryselection_help'] = 'Please select the category where you want to add/modify the cost';
$string ['categoryselection'] = 'Chose a category';
$string ['downloadexcel'] = 'Download excel';
$string ['costbyperiod'] = 'Cost by period';
$string ['categorynavegation'] = 'Category navegation';
$string ['category'] = 'Category';
$string ['categorycost'] = 'Category cost';
$string ['costcenter'] = 'Cost center';
$string ['costofonepage'] = 'Cost of printing one page';
$string ['costcenternumber'] = 'Cost Center number';
$string ['costremember'] = 'Remember that in the main chart there is information about courses that is not included in the sub-categories chart';
$string ['month'] = 'Month';
$string ['coursename'] = 'Course Name';
$string ['teachername'] = 'Teacher Name';
$string ['studentnumber'] = 'Number of Students';
$string ['categorychart'] = 'Category chart';
$string ['subcategorychart'] = 'Sub-category chart';
$string ['changeconfiguration'] = 'Change cost configuration';
$string ['cost'] = 'Cost per page';
$string ['exammodification'] = 'Enter new printing cost';
$string ['numericplease'] = 'Please enter a numeric value!';
$string ['costconfiguration'] = 'Cost Settings';
$string ['costconfiguration_help'] = 'For a correct cost analisis please enter a value diferent from 0';
$string ['defaultcost'] = 'Printing cost';
$string ['defaultcost_cost'] = 'Default cost in your sistem of printing a sigle page';
$string ['invalidcustommarks'] = 'Invalid custom marks, line(s): ';
$string ['exporttoexcel'] = 'Export to Excel';
$string ['exportgrades'] = 'Export grades';
$string ['exportagreement'] = 'Export agreement';
$string ['comparativereport'] = 'Comparative';
$string ['comparativereport_help'] = 'Comparative';
$string ['rubrcismustbeidentical'] = 'Rubrics must be identical to be able to compare them';
$string ['gradescheck'] = 'The minimum score may not be equal to or greater than the maximum score.';
$string ['adjustslope'] = 'Adjust grades slope';
$string ['adjustslope_help'] = 'Adjust how eMarking will calculate the final grade, according to a new grade that will match a specific score. The new grades are calculated linearly with a slope from 0 score for the minimum grade to the adjusted grade/score and then continue to the max grade if it can be obtained.';
$string ['adjustslopegrade'] = 'Grade for slope';
$string ['adjustslopegrade_help'] = 'The grade used to calculate the slope between adjusted grade and minimum grade';
$string ['adjustslopescore'] = 'Score for slope';
$string ['adjustslopescore_help'] = 'The score used to calculate the slope between adjusted score and 0';
$string ['adjustslopegrademustbegreaterthanmin'] = 'Grade for adjusting must be greater than the minimum grade';
$string ['adjustslopegrademustbelowerthanmax'] = 'Grade for adjusting must be lower than the maximum grade';
$string ['adjustslopescoregreaterthanzero'] = 'Score for adjusting must be greater than 0';
$string ['linkrubric'] = 'Multicolor rubric';
$string ['linkrubric_help'] = 'A multicolor rubric shows a different color for each criterion, both for marks and comments.';
$string ['collaborativefeatures'] = 'Markers collaboration';
$string ['collaborativefeatures_help'] = 'Enables a chat, a wall and SOS for markers. The chat allows communication between markers. The wall allows supervisors (teachers or admins) to post messages, markers can only read them. The SOS allows markers to ask for help regarding a specific exam they are marking.';
$string ['includeenrolments'] = 'Include students from';
$string ['enrolments'] = 'Enrolment methods';
$string ['enrolments_help'] = 'The students considered for the marking will be only those enroled in the selected enrolment methods.';
$string ['enrolmanual'] = 'Manual enrolments';
$string ['enrolself'] = 'Self-enrolments';
$string ['enroldatabase'] = 'External database enrolments';
$string ['enrolmeta'] = 'Meta-link enrolments';
$string ['enrolcohort'] = 'Cohort enrolments';
$string ['includestudentsinexam'] = 'Enrolment from which include students in personalized printing';
$string ['permarkercontribution'] = 'Contribution per marker';
$string ['permarkerscores'] = 'Scores per marker';
$string ['markingstatusincludingabsents'] = 'Marking status (including absents)';
$string ['markingreport'] = 'Marking report';
$string ['markingreport_help'] = 'This report shows how complete is the marking process';
$string ['of'] = 'of';
$string ['missingpages'] = 'Some pages are missing';
$string ['transactionsuccessfull'] = 'Transaction successfull';
$string ['setasabsent'] = 'Absent';
$string ['setassubmitted'] = 'Submitted';
$string ['markers'] = 'Markers';
$string ['saved'] = 'Saved';
$string ['downloadform'] = 'Download print form';
$string ['selectprinter'] = 'Select printer';
$string ['enableprinting'] = 'Enable printing from Moodle';
$string ['enableprinting_help'] = 'Enables cups (lp) for printing exams directly from Moodle using a network printer (certain printer enable stappling exams)';
$string ['printername'] = 'Printer name';
$string ['printername_help'] = 'Printer\'s name on cups configuration';
$string ['yourcodeis'] = 'Your security code is';
$string ['minimumdaysbeforeprinting'] = 'Minimum days before exam for printing';
$string ['minimumdaysbeforeprinting_help'] = 'Teachers can send print orders until this number of days before the exam date. If set to 0 days the date won\'t be verified.';
$string ['parallelcourses'] = 'Parallel courses';
$string ['configuration'] = 'Configuration';
$string ['overallfairnessrequired'] = 'Overall fairness is required';
$string ['expectationrealityrequired'] = 'Expectation is required';
$string ['choose'] = 'Choose';
$string ['markingduedate'] = 'Marking due date';
$string ['markingduedate_help'] = 'Defines a due date that will help notify markers and teachers on pending jobs';
$string ['enableduedate'] = 'Enable marking due date';
$string ['verifyregradedate'] = 'Verify that the opening is less than the closing';
$string ['emarkingprints'] = 'Emarking Printers';
$string ['adminprints'] = 'Manage printers';
$string ['permitsviewprinters'] = 'Permissions to view printers';
$string ['notallowedprintermanagement'] = 'Not allowed to access the printer management';
$string ['printerdoesnotexist'] = 'The printer does not exist';
$string ['ip'] = 'ip';
$string ['commandcups'] = 'Command cups';
$string ['insertiondate'] = 'Insertion date';
$string ['delete'] = 'Delete';
$string ['doyouwantdeleteprinter'] = 'Do you want delete this printer?';
$string ['edit'] = 'Edit';
$string ['doyouwanteditprinter'] = 'do you want edit this printer?';
$string ['addprinter'] = 'Add printer';
$string ['editprinter'] = 'Edit printer';
$string ['required'] = 'Required';
$string ['nameexist'] = 'The printer name already exists';
$string ['ipexist'] = 'The ip is associate to another printer';
$string ['ipproblem'] = 'The ip has no numeric characters';
$string ['emptyprinters'] = 'No printers in the system';
$string ['selectusers'] = 'Select user(s)';
$string ['selectprinters'] = 'Select printer(s)';
$string ['dontexistrelationship'] = 'The user-printer permisssion does not exist';
$string ['username'] = 'Username';
$string ['doyouwantdeleterelationship'] = 'Do you want delete the permission?';
$string ['managepermissions'] = 'Manage permissions printers';
$string ['emptypermissions'] = 'There are no permissions';
$string ['addpermission'] = 'Add permission';
$string ['annotatesubmission_help'] = 'eMarking allows to mark digitized exams using rubrics. In this page you can see the course list and their submissions (digitized answers). It also shows the exam status, that can be missing for a student with no answers, submitted if it has not been graded, responded when the marking is finished and regrading when a regrade request was made by a student.';
$string ['regrades_help'] = 'This page shows the regrade requests made by students.';
$string ['ranking'] = 'Ranking';
$string ['areyousure'] = 'Are you sure?';
$string ['actions'] = 'Actions';
$string ['annotatesubmission'] = 'Mark';
$string ['attempt'] = 'Attempt';
$string ['average'] = 'Average';
$string ['backcourse'] = 'Back to course';
$string ['categoryselect_help'] = 'Please select the category where you want to go';
$string ['categoryselect'] = 'Chose a category';
$string ['close'] = 'Close';
$string ['comment'] = 'Comment';
$string ['completerubric'] = 'Complete rubric';
$string ['copycenterinstructions'] = 'Copy center instructions';
$string ['corrected'] = 'Corrected';
$string ['createrubric'] = 'Create rubric';
$string ['criterion'] = 'Criterion';
$string ['criteriaefficiency'] = 'Criteria efficiency';
$string ['digitizedfile'] = 'Upload digitized answers file';
$string ['doubleside'] = 'Double Side';
$string ['downloadfeedback'] = 'PDF';
$string ['downloadsuccessfull'] = 'Download successfull';
$string ['email'] = 'Email';
$string ['emailinstructions'] = 'Enter the security code sent to the email: {$a->email}';
$string ['smsinstructions'] = 'Please enter the security code sent to the mobile number: {$a->phone2}';
$string ['emarking'] = 'eMarking';
$string ['enrolincludes'] = 'Default enrolment methods';
$string ['enrolincludes_help'] = 'The enrolment methods that will be selected when printing a new exam.';
$string ['errors'] = 'Errors';
$string ['errorprocessingextraction'] = 'Error processing extraction from ZIP';
$string ['errorsavingpdf'] = 'Error saving ZIP file';
$string ['extraexams'] = 'Extra exams';
$string ['extraexams_help'] = 'Extra exams with no student name on them.';
$string ['extrasheets'] = 'Extra sheets';
$string ['extrasheets_help'] = 'Extra sheets per exam.';
$string ['fatalerror'] = 'Fatal error';
$string ['fileisnotzip'] = 'File is not ZIP';
$string ['filerequiredpdf'] = 'A PDF file with the scanned tests is required';
$string ['filerequiredpdf_help'] = 'A pdf file with the scanned tests is required';
$string ['filerequiredzip'] = 'A ZIP file with the scanned tests is required';
$string ['filerequiredzip_help'] = 'A zip file with the scanned tests is required';
$string ['filerequiredtosendnewprintorder'] = 'A PDF file is required';
$string ['gotosubcategory'] = 'Go down';
$string ['gotouppercategory'] = 'Go up';
$string ['grade'] = 'Grade';
$string ['headerqr'] = 'Personalized header';
$string ['headerqr_help'] = 'The personalized header includes student information on every page.
		<div class="required">Warning<ul><li>The PDF must have a blank 3cm header to print the student name and QR code</li></ul></div>';
$string ['headerqrrequired'] = 'The personalized header is required for On Screen Marking.';
$string ['identifieddocuments'] = 'Documents identified';
$string ['idnumber'] = 'ID';
$string ['ignoreddocuments'] = 'Documents ignored';
$string ['includelogo'] = 'Include logo';
$string ['includelogo_help'] = 'Includes a logo in the exam header.';
$string ['includeuserpicture'] = 'Include user picture';
$string ['includeuserpicture_help'] = 'Includes the user picture in the exams headers';
$string ['invalidaccess'] = 'Invalid access, trying to upload exam';
$string ['invalidcategoryid'] = 'Invalid category';
$string ['invalidcourse'] = 'Invalid course from assignment';
$string ['invalidcourseid'] = 'Invalid course Id';
$string ['invalidcoursemodule'] = 'Invalid course module';
$string ['invalidexamid'] = 'Invalid exam id';
$string ['invalidfilenotpdf'] = 'Invalid file, it is not a pdf';
$string ['invalidid'] = 'Invalid id';
$string ['invalididnumber'] = 'Invalid Id number';
$string ['invalidemarkingid'] = 'Invalid access, trying to upload exam';
$string ['invalidpdfnopages'] = 'Invalid PDF file, it contains no pages.';
$string ['invalidpdfnumpagesforms'] = 'Invalid PDF files, they must have the same number of pages.';
$string ['invalidstatus'] = 'Invalid status';
$string ['invalidtoken'] = 'Invalid token trying to download exam.';
$string ['invalidzipnoanonymous'] = 'Invalid ZIP file, it does not contain the anonymous version of answers. It is possible that it has been generated with an old version of the eMarking desktop tool.';
$string ['lastmodification'] = 'Last modification';
$string ['logo'] = 'Logo for header';
$string ['logodesc'] = 'Logo to include in personalized exam headers';
$string ['marking'] = 'Marking';
$string ['modulename'] = 'E-Marking';
$string ['modulename_help'] = 'The E-Marking module allows:<br/>
    <strong>Printing</strong>
    <ul>
    <li>Print exams using personalized sheets including students\' name, a logo and a QR code for scanning later.</li>
    <li>Add a students list for attendance.</li>
    <li>Print an exam for several courses (1).</li>
    </ul>
    <strong>Scanning</strong>
    <ul>
    <li>Digitize students\' answers and grade exams using basic feedback or On Screen Marking.</li>
    </ul>
    <strong>On Screen Marking</strong>
    <ul>
    <li>Mark students\' answers using rubrics, custom marks and predefined comments to provide better feedback. Several markers can collaborate and reuse each others comments.</li>
    <li>Mark anonymously so markers won\'t be biased if they know the student.</li>
    <li>Double mark a sample of the exams for quality control.</li>
    <li>Help markers collaborate through a chat, having supervisor\'s messages on a wall and asking for help when they see an answer they don\'t feel confident to grade (1).</li>
    <li>Train markers on interpreting a rubric using selected answers and forcing them to reach consensus.</li>
    <li>Supervise the marking process and obtain grade reports per student, per rubric criteria and per marker.</li>
    </ul>
    <strong>Feedback</strong>
    <ul>
    <li>Students can see their exams, grades and feedback from anywhere in the world and request regrades.</li>
    <li>Collect students\' justice perception regarding the marking process and their grades.</li>
    <li>Students can see the course ranking and anonymously see their peers\' exams to better understand what they did good or wrong.</li>
    </ul>
    (1): Requires extra server configuration.';
$string ['modulenameplural'] = 'E-Markings';
$string ['motive'] = 'Motive';
$string ['motive_help'] = 'Please indicate the motive for your requesting a regrade in this criterion';
$string ['multicourse'] = 'Multicourse';
$string ['multicourse_help'] = 'Aquí puede seleccionar otros cursos para los que también se enviará la orden de impresión';
$string ['singlepdf'] = 'Single PDF with all students';
$string ['multiplepdfs'] = 'Multiple pdfs in a zip file';
$string ['multiplepdfs_help'] = 'If selected eMarking generates a zip file containing a personalized version for the exam for each student, otherwise a single large pdf file will be generated.';
$string ['myexams'] = 'My exams';
$string ['myexams_help'] = 'This page shows all the exam papers that have been sent for printing in this course. You can edit and cancel a submission as long as it has not been downloaded from the copy center.';
$string ['names'] = 'First Name/Last Name';
$string ['emailsent'] = 'Security code sent to your email';
$string ['newprintorder'] = 'Send new exam for printing';
$string ['newprintorder_help'] = 'In order to send an exam paper for printing you need to provide a title for the exam (e.g: Final exam), the exact date when the exam will be held and a pdf file with the exam paper.<br/>
		<strong>eMarking header:</strong> If you check this option, exams will be printed with a personalized header for each student, including her picture if available. This header can be later automatically processed by the eMarking module, that helps in the process of marking, delivering marks and accepting regrade requests.<br/>
		<strong>Copy center instructions:</strong> Instructions to be sent to the copy center, such as printing extra sheets per student or extra exams.
		';
$string ['nocostdata'] = 'There is not all the cost data, make sure you have eMarking activities send to print or printed and students enroled to courses';
$string ['nototalcost'] = 'There is not all the cost data, make sure your eMarking activities have a printing cost associated';
$string ['nocourseranking'] = 'There is not all the cost data for course ranking, make sure you have eMarking activities send to print or printed';
$string ['noteacherranking'] = 'There is not all the cost data for teacher ranking, make sure you have eMarking activities';
$string ['nostudent'] = 'There are no students in this category, make sure you enrol students to the courses.';
$string ['nodata'] = 'No data';
$string ['nopagestoprocess'] = 'Error. No pages to process, please upload the answers again.';
$string ['noprintorders'] = 'No print orders for this course';
$string ['nosubmissionsgraded'] = 'No submissions graded';
$string ['nosubmissionspublished'] = 'No grades published';
$string ['nosubmissionsselectedforpublishing'] = 'No submissions selected for publishing grades';
$string ['noexamsforprinting'] = 'There are no exams for printing';
$string ['notallowed'] = 'Not allowed!';
$string ['notallowedcostreport'] = 'Not allowed to see cost reports';
$string ['notcorrected'] = 'Not corrected';
$string ['page'] = 'Page';
$string ['pages'] = 'pages';
$string ['assignpagestocriteria'] = 'Assign pages to criteria';
$string ['parallelregex'] = 'Regex for parallels';
$string ['parallelregex_help'] = 'Regular expression to extract unit of study code in course shortnames so exams from parallel course can be compared.';
$string ['pathuserpicture'] = 'Path to users pictures directory';
$string ['pathuserpicture_help'] = 'Absolute path to directory containing users pictures in PNG format labeled userXXX.png with XXX being the user id';
$string ['pdffile'] = 'Exam PDF file(s)';
$string ['pdffile_help'] = 'You can upload several PDF files if you want to have different forms for each student';
$string ['pdffileupdate'] = 'Update exam PDF file(s)';
$string ['pluginadministration'] = 'eMarking administration';
$string ['pluginname'] = 'eMarking';
$string ['printdoublesided'] = 'Double sided';
$string ['printdoublesided_help'] = 'When selected e-marking will try to print the exam using both sides of the paper sheets. If CUPS (network printing) is not configured, instructions are indicated for the person who downloads.';
$string ['printexam'] = 'Print exam';
$string ['printsendnotification'] = 'Send print notification';
$string ['printrandom'] = 'Print random';
$string ['printrandominvalid'] = 'must create a group for using this feature';
$string ['printrandom_help'] = 'Print random, based in a group of course';
$string ['printlist'] = 'Print students list';
$string ['printlist_help'] = 'Adds a student list';
$string ['printnotification'] = 'Notification';
$string ['printnotificationsent'] = 'Print notification sent';
$string ['printorders'] = 'Print orders';
$string ['processtitle'] = 'Upload answers';
$string ['publishselectededgrades'] = 'Publish selected grades';
$string ['publishtitle'] = 'Publish grades';
$string ['publishedgrades'] = 'Published grades';
$string ['publishinggrade'] = 'Publishing grade';
$string ['publishinggradesfinished'] = 'Publishing grades finished';
$string ['qrdecodingfinished'] = 'QR decoding finished';
$string ['qrprocessingtitle'] = 'Answers processing software';
$string ['qrprocessing'] = 'Download answers processing software';
$string ['records'] = 'History';
$string ['regrades'] = 'Regrade';
$string ['regraderequest'] = 'Request regrading';
$string ['requestedby'] = 'Requested by';
$string ['results'] = 'Results';
$string ['selectcategory'] = 'Select category';
$string ['selectall'] = 'Select all';
$string ['selectnone'] = 'Select none';
$string ['settings'] = 'Settings';
$string ['settingsadvanced'] = 'Advanced settings';
$string ['settingsadvanced_help'] = 'Advanced configuration for eMarking';
$string ['settingssecurity'] = 'Security settings';
$string ['settingssecurity_help'] = 'You can configure extra security using SMS. It will use Twilio.com services to enable validating exam downloads using two steps.';
$string ['smspassword'] = 'Twilio.com auth token';
$string ['smspassword_help'] = 'The auth token for the account in Twilio.com';
$string ['smsserverproblem'] = 'Error connecting to Twilio.com';
$string ['smsurl'] = 'Twilio.com phone number';
$string ['smsurl_help'] = 'The Twilio.com phone number that is used as the sender for the messages.';
$string ['smsuser'] = 'Twilio.com account id';
$string ['smsuser_help'] = 'The account id from Twilio.com';
$string ['smssent'] = 'Security code sent to your mobile phone';
$string ['specificmarks'] = 'Custom marks';
$string ['specificmarks_help'] = 'Custom marks, one per line separating code and description by a # (e.g: Sp#Spelling error<br/>Gr#Grammar error)';
$string ['statistics'] = 'Statistics';
$string ['statisticstotals'] = 'Accumulated';
$string ['status'] = 'Status';
$string ['statusaccepted'] = 'Accepted';
$string ['statusabsent'] = 'Absent';
$string ['statusgrading'] = 'Grading';
$string ['statusgradingfinished'] = 'Marked';
$string ['statusmissing'] = 'Missing';
$string ['statusnotsent'] = 'Not sent';
$string ['statusregrading'] = 'Regrading';
$string ['statusregradingresponded'] = 'Regrading responded';
$string ['statuspublished'] = 'Published';
$string ['statussubmitted'] = 'Uploaded';
$string ['statuserror'] = 'Error';
$string ['totalexams'] = 'Total exams';
$string ['totalpagesprint'] = 'Total Pages to Print';
$string ['uploadexamfile'] = 'ZIP file';
$string ['uploadinganswersheets'] = 'Uploading student answer sheets';
$string ['usesms'] = 'Use Twilio.com to send SMS';
$string ['usesms_help'] = 'Use SMS messaging  instead of sending email for eMarking security codes';
$string ['viewsubmission'] = 'View exam';
$string ['formnewcomment'] = 'Comment text';
$string ['writecomment'] = 'Write a Comment';
$string ['createcomment'] = 'Create Comment';
$string ['formeditcomment'] = 'Edit Comment:';
$string ['editcomment'] = 'Edit Comment';
$string ['adjustments'] = 'Adjustments';
$string ['questiondeletecomment'] = 'Do you want to delete the comment?';
$string ['creator'] = 'Creator';
$string ['details'] = 'Details';
$string ['originals'] = 'Originals';
$string ['copies'] = 'Copies';
$string ['teacher'] = 'Teacher';
$string ['gradehistogram'] = 'Grades histogram by course';
$string ['gradehistogramtotal'] = 'Grades histogram aggregated';
$string ['courseaproval'] = 'Pass ratio';
$string ['range'] = 'Range';
$string ['marker'] = 'Marker';
$string ['year'] = 'Year';
// Events.
$string ['eventemarkinggraded'] = 'Emarking';
$string ['eventrotatepageswitched'] = 'Rotate page';
$string ['eventaddcommentadded'] = 'Add coment';
$string ['eventaddregradeadded'] = 'Add Regrade';
$string ['eventdeletecommentdeleted'] = 'Delete comment';
$string ['eventaddmarkadded'] = 'Add Mark';
$string ['eventdeletemarkdeleted'] = 'Delete Mark';
$string ['eventinvalidaccessgranted'] = 'Invalid access, trying to upload exam';
$string ['eventsuccessfullydownloaded'] = 'Download successfull';
$string ['eventinvalidtokengranted'] = 'Invalid token trying to download exam.';
$string ['unauthorizedccess'] = 'WARNING: Unauthorized access to eMarking Ajax inteface';
$string ['eventmarkersconfigcalled'] = 'The markers config was called';
$string ['eventmarkersassigned'] = 'Markers have been assigned';
$string ['eventemarkingcalled'] = 'Emarking Called';
// Delphi's strings.
$string ['marking_progress'] = 'Marking progress';
$string ['delphi_stage_one'] = 'Marking';
$string ['marking_deadline'] = 'Finishes in';
$string ['stage_general_progress'] = 'general progress';
$string ['delphi_stage_two'] = 'Discussion';
$string ['marking_completed'] = 'Well done, just wait for the others markers for the stage two';
$string ['stage'] = 'Stage';
$string ['agreement'] = 'Agreement';
$string ['yourmarking'] = 'Your marking';