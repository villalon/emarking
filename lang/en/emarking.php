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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2012 Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string["markerscanseewholerubric"]="Markers can see the entire rubric.";
$string["markerscanseeallpages"] = "Markers can see all pages.";
$string["markerscanseeselectedcriteria"] = "Markers can see only assigned criteria.";
$string["markerscanseenothing"] = "Pages are assigned to criteria but no markers are assigned to criteria. Only admins will be able to see pages.";
$string["markerscanseepageswithcriteria"] = "Markers can see only those pages assigned to the criteria they can mark.";
$string["criteria"] = "Criteria";

$string["nodejspath"] = "NodeJS path";
$string["nodejspath_help"] = "The full Node JS path including protocol, ipaddress and port. e.g: http://127.0.0.1:9091";

$string["er-4"] = "-4 (much worse than I deserved)";
$string["er-3"] = "-3";
$string["er-2"] = "-2";
$string["er-1"] = "-1";
$string["er0"] = "0 (about what I deserved)";
$string["er1"] = "1";
$string["er2"] = "2";
$string["er3"] = "3";
$string["er4"] = "4 (much more than I deserved)";
$string["of-4"] = "-4 (extremely unfair)";
$string["of-3"] = "-3";
$string["of-2"] = "-2";
$string["of-1"] = "-1";
$string["of0"] = "0 (neither fair nor unfair)";
$string["of1"] = "1";
$string["of2"] = "2";
$string["of3"] = "3";
$string["of4"] = "4 (extremely fair)";
$string ['justiceperceptionprocess'] = 'How would you rate the fairness of the marking of the evaluation?';
$string ['justiceperceptionexpectation'] = 'How does your grade for the first assignment compare to the mark you think you deserved to get for this assignment?';
$string ['thanksforjusticeperception'] = 'Thanks for expressing your opinion';

$string['mobilephoneregex'] = 'Mobile phone regex';
$string['mobilephoneregex_help'] = 'A regular expression to validate a correct mobile phone';
$string['invalidphonenumber'] = 'Invalid phone number, we expect a full international number (ex: +56912345678)';
$string['errorsendingemail'] = 'An error ocurred while sending the email';
$string['second'] = 'Second';

$string['processomr'] = 'Process OMR';

$string['signature'] = 'Signature';
$string['advanced'] = 'Advanced';
$string['photo'] = 'Photo';
$string['settingupprinting'] = 'Setting up printing';
$string['printing'] = 'Printing';
$string['tokenexpired'] = 'Security token has expired. Please get a new one.';
$string['otherenrolment'] = 'Other enrolment types.';
$string['sent'] = 'Sent';
$string['replied'] = 'Replied';

$string['usernotloggedin'] = 'User is not logged in';
$string['invalidsessionkey'] = 'Invalid session key';

$string['emarkingsecuritycode'] = 'eMarking security code';
$string['predefinedcomments'] = 'Predefined comments';
$string ['savechanges'] = 'Save changes';
$string ['changessaved'] = 'Changes saved';

$string['qualitycontrol'] = 'Quality Control';
$string['markersqualitycontrol'] = 'Quality Control markers';
$string['markersqualitycontrol_help'] = 'Quality Control markers are the ones that will mark and grade the QC exams which will be used to calculate inter-marker agreement.';
$string['enablequalitycontrol'] = 'Enable Quality Control';
$string['enablequalitycontrol_help'] = 'If QC in enabled, a set of QC exams will be assigned to the QC markers for extra marking and therefore calculate inter-marker agreement.';
$string['notenoughmarkersfortraining'] = 'Not enough markers for training, please enrol markers as non editing teachers for training.';
$string['notenoughmarkersforqualitycontrol'] = 'No markers were selected for quality control. Please select at least one marker as responsible for marking the control exams.';

$string['studentanonymous_markervisible'] = 'Student anonymous / Marker visible';
$string['studentanonymous_markeranonymous'] = 'Student anonymous / Marker anonymous';
$string['studentvisible_markervisible'] = 'Student visible / Marker visible';
$string['studentvisible_markeranonymous'] = 'Student visible / Marker anonymous';

$string ['markingtype'] = 'Marking type';
$string ['markingtype_help'] = "<h2>Marking types</h2><br>
		There are four types of marking sessions available in eMarking:
		<ul>
			<li><b>Normal</b>: Markers grade student exams normally according to a rubric. Exams can be marked more than once for quality control on inter-marker agreement.</li>
			<li><b>Markers training</b>: Exams do not belong to students in the course. All markers grade all exams and the process does not close until 100% agreement is reached between markers.</li>
			<li><b>Student training</b>: Exams do not belong to students in the course. Students grade exams as a way to practice for their own evaluations.</li>
			<li><b>Peer review</b>: Students grade their peers according to the groups configuration. If groups are configured as visible or separated each student in a group marks all exams from another group.</li>
		</ul>";
$string ['type_normal'] = 'Normal';
$string ['type_markers_training'] = 'Markers training';
$string ['type_student_training'] = 'Student training';
$string ['type_peer_review'] = 'Peer review';
		
$string ['invalidcustommarks'] = 'Invalid custom marks, line(s): ';
$string ['exporttoexcel'] = 'Export to Excel';

$string ['comparativereport'] = 'Comparative';
$string ['comparativereport_help'] = 'Comparative';
$string ['youmustselectemarking'] = 'You must select an eMarking activity to compare to';
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

$string ['heartbeatenabled'] = 'Enable students tracking';
$string ['heartbeatenabled_help'] = 'Enables registering the time spent by students in front of the interface.';

$string ['downloadrubricpdf'] = 'Download pdf with rubric on it';
$string ['downloadrubricpdf_help'] = 'Students can download their test with the rubric in the last page';

$string ['linkrubric'] = 'Multicolor rubric';
$string ['linkrubric_help'] = "A multicolor rubric shows a different color for each criterion, both for marks and comments.";

$string ['collaborativefeatures'] = 'Markers collaboration';
$string ['collaborativefeatures_help'] = "Enables a chat, a wall and SOS for markers. The chat allows communication between markers. The wall allows supervisors (teachers or admins) to post messages, markers can only read them. The SOS allows markers to ask for help regarding a specific exam they are marking.";

$string ['experimentalgroups'] = 'Experimental Groups';
$string ['experimentalgroups_help'] = "Enable separete marking through the course groups.";

$string ['emarking:assignmarkers'] = 'Assign markers to criteria';
$string ['emarking:activatedelphiprocess'] = 'Activate delphi';
$string ['emarking:configuredelphiprocess'] = 'Configure delphi';
$string ['emarking:managedelphiprocess'] = 'Manage delphi';

$string ['emarking_webexperimental'] = 'eMarking Web experimental';
$string ['emarking_webexperimental_help'] = 'Enables the experimental interface';

$string ['enrolmanual'] = 'Manual enrolments';
$string ['enrolself'] = 'Self-enrolments';
$string ['enroldatabase'] = 'External database enrolments';
$string ['enrolmeta'] = 'Meta-link enrolments';

$string ['includestudentsinexam'] = 'Enrolment from which include students in personalized printing';
$string ['permarkercontribution'] = 'Contribution per marker';
$string ['notpublished'] = 'Not published';
$string ['markingstatusincludingabsents'] = 'Marking status (including absents)';
$string ['markingreport'] = 'Marking report';
$string ['markingreport_help'] = 'This report shows how complete is the marking process';

$string ['of'] = 'of';
$string ['missingpages'] = 'Some pages are missing';
$string ['transactionsuccessfull'] = 'Transaction successfull';
$string ['setasabsent'] = 'Absent';
$string ['setassubmitted'] = 'Set as submitted';
$string ['markers'] = 'Markers';
$string ['assignmarkerstocriteria'] = 'Add markers to criteria';

$string ['pctmarked'] = 'Marking progress';
$string ['saved'] = 'Saved';
$string ['downloadform'] = 'Download print form';
$string ['selectprinter'] = 'Select printer';
$string ['enableprinting'] = 'Enable printing';
$string ['enableprinting_help'] = 'Enables the use of cups (lp) for printing exams using a network printer (enables stappling exams)';
$string ['enableprintingrandom'] = 'enables printing randomly';
$string ['enableprintingrandom_help'] = 'enables printing randomly, based in a group created';
$string ['enableprintinglist'] = 'Allows printing a list of students';
$string ['enableprintinglist_help'] = 'allows printing of a list of students, this helps assistance in the classes';
$string ['printername'] = 'Printer name';
$string ['printername_help'] = 'Printer\'s name on cups configuration';
$string ['yourcodeis'] = 'Your security code is';

$string ['minimumdaysbeforeprinting'] = 'Days before exam for printing';
$string ['minimumdaysbeforeprinting_help'] = 'Teacher can send print orders with at least this number of days in advance, after that it will fail.';
$string ['showcoursesfrom'] = 'Show courses from';
$string ['donotinclude'] = 'Do not include';
$string ['parallelcourses'] = 'Parallel courses';
$string ['forcescale17'] = 'Force 1 to 7 scale';
$string ['configuration'] = 'Configuration';
$string ['overallfairnessrequired'] = 'Overall fairness is required';
$string ['expectationrealityrequired'] = 'Expectation is required';
$string ['choose'] = 'Choose';

$string ['regradespending'] = 'regrades';
$string ['regraderestricted'] = 'Regrade requests are not allowed anymore. The final date expire on {$a->regradesclosedate}.';
$string ['regraderestrictdates'] = 'Restrict dates for regrading';
$string ['regraderestrictdates_help'] = 'Sets open and close dates for submitting regrade requests';
$string ['regradesopendate'] = 'Regrade requests open date';
$string ['regradesopendate_help'] = 'Date from which the students can submit regrading requests';
$string ['regradesclosedate'] = 'Regrade requests close date';
$string ['regradesclosedate_help'] = 'Date until the students can submit regrading requests';
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
$string ['emarking:manageprinters'] = 'Manage printers';
$string ['enablemanageprinters'] = 'Enable management printers';
$string ['viewadminprints'] = 'Click <a href="{$a}">here</a> to manage printers';
$string ['viewpermitsprinters'] = '<br>Click <a href="{$a}">here</a> to manage permissions of the printers';
$string ['notenablemanageprinters'] = 'No enabled the option for printer management, more information <a href="{$a}">here</a>';
$string ['selectusers'] = 'Select user(s)';
$string ['selectprinters'] = 'Select printer(s)';
$string ['dontexistrelationship'] = 'The user-printer permisssion does not exist';
$string ['username'] = 'Username';
$string ['doyouwantdeleterelationship'] = 'Do you want delete the permission?';
$string ['return'] = 'Return';
$string ['notexistuserorprinter'] = 'There are no valid users or printers';
$string ['managepermissions'] = 'Manage permissions printers';
$string ['emptypermissions'] = 'There are no permissions';
$string ['addpermission'] = 'Add permission';

$string ['printdigitize'] = 'Print/Scan';
$string ['reports'] = 'Reports';
$string ['gradereport'] = 'Grades report';
$string ['gradereport_help'] = 'This report shows basic statistics and a three graphs. It includes the grades from a particular eMarking activity but other activities from other courses can be added if the parallel courses settings are configured.<br/>
			<strong>Basic statistics:</strong>Shows the average, quartiles and ranges for the course.<br/>
			<strong>Average graph:</strong>Shows the average and standard deviation.<br/>
			<strong>Grades histogram:</strong>Shows the number of students per range.<br/>
			<strong>Approval rate:</strong>Shows the approval rate for the course.<br/>
			<strong>Criteria efficiency:</strong>Shows the average percentage of the maximum score obtained by the students.';
$string ['annotatesubmission_help'] = 'eMarking allows to mark digitized exams using rubrics. In this page you can see the course list and their submissions (digitized answers). It also shows the exam status, that can be missing for a student with no answers, submitted if it has not been graded, responded when the marking is finished and regrading when a regrade request was made by a student.';
$string ['regrades_help'] = 'This page shows the regrade requests made by students.';
$string ['uploadanswers_help'] = 'In this page you can upload the digitized answers from your students. The format is a zip file containing two png files for each page a student has (one is the anonymous version). This file can be obtained using the eMarking desktop application that can be downloaded <a href="">here</a>';

$string ['gradescale'] = 'Grades scale';
$string ['rubricscores'] = 'Max score';

$string ['ranking'] = 'Ranking';

$string ['noregraderequests'] = 'There are no regrade requests';
$string ['regradedatecreated'] = 'Date created';
$string ['regradelastchange'] = 'Last change';

$string ['score'] = 'Score';
$string ['markingcomment'] = 'Marking comment';
$string ['regrade'] = 'Regrade';
$string ['regradingcomment'] = 'Regrading comment';

$string ['missasignedscore'] = 'Missasigned score';
$string ['unclearfeedback'] = 'Unclear feedback';
$string ['statementproblem'] = 'Question statement problem';
$string ['other'] = 'Other';

$string ['stdev'] = 'Deviation';
$string ['min'] = 'Minimum';
$string ['quartile1'] = '1st quartile';
$string ['median'] = 'Median';
$string ['quartile3'] = '3rd quartile';
$string ['max'] = 'Maximum';
$string ['lessthan'] = 'Less than {$a}';
$string ['between'] = '{$a->min} to {$a->max}';
$string ['greaterthan'] = 'More than {$a}';

$string ['areyousure'] = 'Are you sure?';
$string ['actions'] = 'Actions';
$string ['annotatesubmission'] = 'Mark';
$string ['anonymous'] = 'Anonymous';
$string ['anonymous_help'] = 'Set to yes if you want the marking process to be blind. Student names and photos are hidden.';
$string ['anonymousstudent'] = 'Anonymous student';
$string ['aofb'] = '{$a->identified} of {$a->total}';
$string ['attempt'] = 'Attempt';
$string ['average'] = 'Average';
$string ['backcourse'] = 'Back to course';
$string ['cancelorder'] = 'Cancel print order';
$string ['checkdifferentpage'] = 'Check different page';
$string ['close'] = 'Close';
$string ['comment'] = 'Comment';
$string ['completerubric'] = 'Complete rubric';
$string ['confirmprocess'] = 'Confirm process';
$string ['confirmprocessfile'] = 'You are about to process file {$a->file} as student submissions for assignment {$a->assignment}.<br> This will delete any previous submissions from students on that assignment. Are you sure?';
$string ['confirmprocessfilemerge'] = 'You are about to process file {$a->file} as student submissions for assignment {$a->assignment}.<br> New pages will be merged with any previous submissions from students on that assignment. Are you sure?';
$string ['copycenterinstructions'] = 'Copy center instructions';
$string ['corrected'] = 'Corrected';
$string ['couldnotexecute'] = 'Could not execute command.';
$string ['createrubric'] = 'Create rubric';
$string ['criterion'] = 'Criterion';
$string ['criteriaefficiency'] = 'Criteria efficiency';
$string ['crowd'] = 'Crowd';
$string ['crowdexperiment'] = "Crowd Experiment";
$string ['crowdexperiment_help'] = "Enable initial crowd experiment (rtdelphi)";
$string ['crowdexperiment_rtm_secret'] = 'RTMarking Secret';
$string ['crowdexperiment_rtm_secret_help'] = 'Secret for RTMarking auth';
$string ['crowdexperiment_rtm_appid'] = 'RTMarking App-id';
$string ['crowdexperiment_rtm_appid_help'] = 'Appid for RTMarking auth';

$string ['decodeddata'] = 'Decoded data';
$string ['digitizedfile'] = 'Digitized answers file';
$string ['doubleside'] = 'Double Side';
$string ['doublesidescanning'] = 'Double side scanning';
$string ['doublesidescanning_help'] = 'Please check if both sides of the answer sheets were scanned.';
$string ['downloadfeedback'] = 'PDF';
$string ['downloadsuccessfull'] = 'Download successfull';
$string ['editorder'] = 'Edit print order';
$string ['email'] = 'Email';
$string ['emailinstructions'] = 'Enter the security code sent to the email: {$a->email}';
$string ['messageprovider:notification'] = 'Notification';
$string ['emarking'] = 'eMarking';
$string ['enablejustice'] = 'Enable justice perception';
$string ['enablejustice_help'] = 'Enables the option for students to express their perception of justice for their evaluations';
$string ['enrolincludes'] = 'Enrolment types for eMarking';
$string ['enrolincludes_help'] = 'The enrolment types that will be used to print the personalized headers in eMarking';
$string ['errors'] = 'Errors';
$string ['enrolincludes_help'] = 'The enrolment types that will be included when generating personalized headers in eMarking';
$string ['errorprocessingcrop'] = 'Error processing crop of QR';
$string ['errorprocessingextraction'] = 'Error processing extraction from ZIP';
$string ['errorsavingpdf'] = 'Error saving ZIP file';
$string ['examalreadysent'] = 'The exam was already sent to print. It cannot be modified.';
$string ['examdate'] = 'Exam date';
$string ['examdate_help'] = 'Date and time in which the exam will be taken';
$string ['examdateinvalid'] = 'Invalid exam date, it should be at least {$a->mindays} working days in advance.';
$string ['examdateinvaliddayofweek'] = 'Invalid exam date, only from Monday to Fridays and Saturdays before 4pm.';
$string ['examdateprinted'] = 'Print date';
$string ['examdatesent'] = 'Date sent';
$string ['examdeleteconfirm'] = 'You are about to delete {$a}. ¿Do you want to continue?';
$string ['examdeleted'] = 'Exam deleted. Please wait while you are redirected';
$string ['examid'] = 'Exam id';
$string ['examinfo'] = 'Exam information';
$string["examhasnopdf"] = "El examen to tiene un archivo PDF asociado. Este error es grave, por favor notifique al administrador.";
$string ['examname'] = 'Title';
$string ['examname_help'] = 'Exam title e.g: Final examn, Mid-term.';
$string ['exam'] = 'Exam';
$string ['exams'] = 'Exams';
$string ['examstatusdownloaded'] = 'Downloaded';
$string ['examstatusprinted'] = 'Printed';
$string ['examstatussent'] = 'Sent for printing';
$string ['experimental'] = 'Experimental';
$string ['experimental_help'] = 'Experimental features (use at your own risk)';
$string ['extractingpreview'] = 'Extracting pages';
$string ['extraexams'] = 'Extra exams';
$string ['extraexams_help'] = 'Extra exams with no student name on them.';
$string ['extrasheets'] = 'Extra sheets';
$string ['extrasheets_help'] = 'Extra sheets per exam.';
$string ['fatalerror'] = 'Fatal error';
$string ['fileisnotpdf'] = 'File is not PDF';
$string ['fileisnotzip'] = 'File is not ZIP';
$string ['filerequiredpdf'] = 'A PDF file with the scanned tests is required';
$string ['filerequiredpdf_help'] = 'A pdf file with the scanned tests is required';
$string ['filerequiredzip'] = 'A ZIP file with the scanned tests is required';
$string ['filerequiredzip_help'] = 'A zip file with the scanned tests is required';
$string ['filerequiredtosend'] = 'A ZIP file is required';
$string ['filerequiredtosendnewprintorder'] = 'A PDF file is required';
$string ['finalgrade'] = 'Final Grade';
$string ['grade'] = 'Grade';
$string ['headerqr'] = 'Personalized header';
$string ['headerqr_help'] = 'The personalized header includes student information on every page.
		<div class="required">Warning<ul><li>The PDF must have a blank 3cm header to print the student name and QR code</li></ul></div>';
$string ['identifieddocuments'] = 'Documents identified';
$string ['idnotfound'] = '{$a->id} id not found';
$string ['idnumber'] = 'ID';
$string ['ignoreddocuments'] = 'Documents ignored';
$string ['includelogo'] = 'Include logo';
$string ['includelogo_help'] = 'Include a logo in each exam header. The logo image can be found in mod/emarking/img/logo.jpg';
$string ['includeuserpicture'] = 'Include user picture';
$string ['includeuserpicture_help'] = 'Includes the user picture in the exams headers';
$string ['initializedirfail'] = 'Could not initalize directory {$a}. Please check with the administrator.';
$string ['invalidaccess'] = 'Invalid access, trying to upload exam';
$string ['invalidcategoryid'] = 'Invalid category';
$string ['invalidcourse'] = 'Invalid course from assignment';
$string ['invalidcourseid'] = 'Invalid course Id';
$string ['invalidcoursemodule'] = 'Invalid course module';
$string ['invalidexamid'] = 'Invalid exam id';
$string ['invalidid'] = 'Invalid id';
$string ['invalididnumber'] = 'Invalid Id number';
$string ['invalidimage'] = 'Invalid information from Image';
$string ['invalidemarkingid'] = 'Invalid access, trying to upload exam';
$string ['invalidparametersforpage'] = 'Invalid parameters for page';
$string ['invalidpdfnopages'] = 'Invalid PDF file, it contains no pages.';
$string ['invalidsize'] = 'Invalid size from Image';
$string ['invalidstatus'] = 'Invalid status';
$string ['invalidtoken'] = 'Invalid token trying to download exam.';
$string ['invalidzipnoanonymous'] = 'Invalid ZIP file, it does not contain the anonymous version of answers. It is possible that it has been generated with an old version of the eMarking desktop tool.';
$string ["justice"] = "Justice";
$string ["justice.area.under.construction"] = "Dear Alpha Tester: This area is under construction! Thanks for helping us test our aplication.";
$string ["justice.back"] = "Go Back";
$string ["justice.download"] = "View";
$string ["justice.evaluations.actions"] = "Actions";
$string ["justice.evaluations.grade"] = "Grade";
$string ["justice.evaluations.marker"] = "Marker";
$string ["justice.evaluations.mean"] = "Mean";
$string ["justice.evaluations.name"] = "Evaluation";
$string ["justice.evaluations.status"] = "Status";
$string ["justice.exam.not.found"] = "Exam not found";
$string ["justice.feature.not.available.short"] = "Feature Unavailable";
$string ["justice.feature.not.available.yet"] = "This feature is not available yet.";
$string ["justice.feedback.already.given"] = "NOTICE! You have already given your opinion. You can update it below.";
$string ["justice.feedback.welcome"] = "Use this form when you are ready to accept your grade.";
$string ["justice.form.header"] = "My evaluations";
$string ["justice.graph.student.name"] = "Name";
$string ["justice.graph.test.performance"] = "Test performance";
$string ["justice.my.evaluations"] = "My evaluations";
$string ["justice.peercheck"] = "Review peers";
$string ["justice.question.unavailable"] = "No disponible";
$string ["justice.question.not.answered"] = "No Entregado";
$string ["justice.question.modify"] = "Modificar";
$string ["justice.regrade.request"] = "Request Regrade";
$string ["justice.similars.actions"] = "Actions";
$string ["justice.similars.grade"] = "Grade";
$string ["justice.similars.name"] = "Name";
$string ["justice.statistics"] = "Statistics";
$string ["justice.statistics.locked"] = "Before viewing statistics, you need to answer some questions.";
$string ["justice.status.grading"] = "Grading";
$string ["justice.status.pending"] = "Pending Acceptance";
$string ["justice.status.regrading"] = "Regrading";
$string ["justice.status.accepted"] = "Accepted";
$string ["justice.thank.you.for.your.feedback"] = "Thank you for your feedback.";
$string ["justice.question.instructions"] = "Considering a scale from -4 to 4 where -4 means very unfair and 4 means very fair, please answer the following questions regarding the evaluation:";
$string ["justice.question.first"] = "How would you rate the fairness of the marking process?";
$string ["justice.question.second"] = "How does your grade compare to what you think you deserved?";
$string ["justice.review"] = "Review";
$string ["justice.yourgrade"] = "Your grade";
$string ['justiceexperiment'] = 'Experiment in justice perception';
$string ['justiceexperiment_help'] = 'Show half the students evaluation statistics to have experimental and control groups for measuring justice perception';
$string ['justification'] = 'Justification';
$string ['justification_help'] = 'You must justify your regrade request';
$string ['lastmodification'] = 'Last modification';
$string ['logo'] = 'Logo for header';
$string ['logodesc'] = 'Logo to include in personalized exam headers';
$string ['marking'] = 'Marking';
$string ['merge'] = 'Merge submission';
$string ['merge_help'] = 'Adds new pages to current student submission.';
$string ['modulename'] = 'eMarking';
$string ['modulename_help'] = 'A name for the exam, e.g: Final exam';
$string ['modulenameplural'] = 'emarkings';
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
$string ['emarking:addinstance'] = 'Add a new module instance';
$string ['emarking:downloadexam'] = 'Download exams';
$string ['emarking:grade'] = 'Grades';
$string ['emarking:manageanonymousmarking'] = 'Manage anonymous marking';
$string ['emarking:managespecificmarks'] = 'Manage custom marks';
$string ['emarking:printordersview'] = 'View print orders';
$string ['emarking:receivenotification'] = 'Receive print order notifications';
$string ['emarking:regrade'] = 'Regrade';
$string ['emarking:reviewanswers'] = 'Review submissions';
$string ['emarking:submit'] = 'Submit exam to new module';
$string ['emarking:supervisegrading'] = 'Supervise grading process';
$string ['emarking:uploadexam'] = 'Upload exam';
$string ['emarking:view'] = 'View exams';
$string ['emarking:viewpeerstatistics'] = 'User can see other students statistics';
$string ['newprintorder'] = 'Send new exam for printing';
$string ['newprintorder_help'] = 'In order to send an exam paper for printing you need to provide a title for the exam (e.g: Final exam), the exact date when the exam will be held and a pdf file with the exam paper.<br/>
		<strong>eMarking header:</strong> If you check this option, exams will be printed with a personalized header for each student, including her picture if available. This header can be later automatically processed by the eMarking module, that helps in the process of marking, delivering marks and accepting regrade requests.<br/>
		<strong>Copy center instructions:</strong> Instructions to be sent to the copy center, such as printing extra sheets per student or extra exams.
		';
$string ['newprintordersuccess'] = 'The print order was successfully submitted.';
$string ['newprintordersuccessinstructions'] = 'Your exam {$a->name} was successfully sent for printing.';
$string ['noemarkings'] = 'No submissions left';
$string ['nopagestoprocess'] = 'Error. No pages to process, please upload the answers again.';
$string ['noprintorders'] = 'No print orders for this course';
$string ['nosubmissionsgraded'] = 'No submissions graded';
$string ['nosubmissionsselectedforpublishing'] = 'No submissions selected for publishing grades';
$string ['nocomment'] = 'No general comment';
$string ['noexamsforprinting'] = 'There are no exams for printing';
$string ['notcorrected'] = 'Not corrected';
$string ['page'] = 'Page';
$string ['pages'] = 'pages';
$string ['assignpagestocriteria'] = 'Add pages to criteria';
$string ['pagedecodingfailed'] = 'Decoding QR from page {$a} failed';
$string ['pagedecodingsuccess'] = 'Decoding QR from page {$a} successfull';
$string ['pagenumber'] = 'Page number';
$string ['parallelregex'] = 'Regex for parallels';
$string ['parallelregex_help'] = 'Regular expression to extract unit of study code in course shortnames so exams from parallel course can be compared.';
$string ['pathuserpicture'] = 'Path to users pictures directory';
$string ['pathuserpicture_help'] = 'Absolute path to directory containing users pictures in PNG format labeled userXXX.png with XXX being the user id';
$string ['pdffile'] = 'PDF file(s)';
$string ['pdffile_help'] = 'You can upload several PDF files if you want to have different forms for each student';
$string ['pluginadministration'] = 'eMarking administration';
$string ['pluginname'] = 'eMarking';
$string ['previewheading'] = 'Preview QR decoding';
$string ['previewtitle'] = 'Preview QR';
$string ['printsuccessinstructions'] = 'Instructions for successfull printing';
$string ['printsuccessinstructionsdesc'] = 'A personalized message to show teachers and admin staff once a successfull print order was sent. For example to pick up the prints from a copy center or to download by themselves.';
$string ['printdoublesided'] = 'Double sided';
$string ['printexam'] = 'Print exam';
$string ['printrandom'] = 'Print random';
$string ['printrandominvalid'] = 'must create a group for using this feature';
$string ['printrandom_help'] = 'Print random, based in a group of course';
$string ['printlist'] = 'Print students list';
$string ['printlist_help'] = 'Adds a student list';
$string ['printnotification'] = 'Notification';
$string ['printnotificationsent'] = 'Print notification sent';
$string ['printorders'] = 'Prints orders';
$string ['printsendnotification'] = 'Send print notification';
$string ['problem'] = 'Problem';
$string ['processanswers'] = 'Process answers';
$string ['processtitle'] = 'Upload answers';
$string ['publishselectededgrades'] = 'Publish selected grades';
$string ['publishtitle'] = 'Publish grades';
$string ['publishedgrades'] = 'Published grades';
$string ['publishinggrade'] = 'Publishing grade';
$string ['publishinggrades'] = 'Publishing grades';
$string ['publishinggradesfinished'] = 'Publishing grades finished';
$string ['qrdecoding'] = 'QR decoding';
$string ['qrdecodingfinished'] = 'QR decoding finished';
$string ['qrdecodingloadingtoram'] = 'Preparing pages {$a->floor} to {$a->ceil} for decoding. Total pages: {$a->total}';
$string ['qrdecodingprocessing'] = 'Processing page {$a->current}. Preparing pages again at: {$a->ceil}. Total pages: {$a->total}';
$string ['qrerror'] = 'QR encoded information error';
$string ['qrimage'] = 'QR image';
$string ['qrnotidentified'] = 'QR could not be identified';
$string ['qrprocessingtitle'] = 'Answers processing software';
$string ['qrprocessing'] = 'Download answers processing software';
$string ['records'] = 'History';
$string ['regrades'] = 'Regrade';
$string ['regraderequest'] = 'Request regrading';
$string ['requestedby'] = 'Requested by';
$string ['results'] = 'Results';
$string ['rubricneeded'] = 'eMarking requires a rubric for marking, please create one';
$string ['rubricdraft'] = 'eMarking requires a ready rubric, the rubric is in status draft. Please complete rubric';
$string ['selectall'] = 'Select all';
$string ['selectnone'] = 'Select none';
$string ['separategroups'] = 'Separate groups';
$string ['settings'] = 'Settings';
$string ['settingsadvanced'] = 'Advanced settings';
$string ['settingsadvanced_help'] = 'Advanced configuration for eMarking';
$string ['settingsbasic'] = 'Basic settings';
$string ['settingsbasic_help'] = 'Basic settings for eMarking functioning';
$string ['settingslogo'] = 'Header settings';
$string ['settingslogo_help'] = 'Settings for the personalized header';
$string ['settingssms'] = 'SMS settings';
$string ['settingssms_help'] = 'SMS settings to use an SMS service for validating exam downloads using two steps';
$string ['smsinstructions'] = 'Please enter the security code sent to the mobile number: {$a->phone2}';
$string ['smspassword'] = 'SMS provider password';
$string ['smspassword_help'] = 'Password of the SMS sending provider';
$string ['smsserverproblem'] = 'Error connecting to SMS';
$string ['smsurl'] = 'SMS provider URL';
$string ['smsurl_help'] = 'URL of the SMS sending provider';
$string ['smsuser'] = 'SMS provider user';
$string ['smsuser_help'] = 'User of the SMS sending provider';
$string ['smssent'] = 'Security code sent to your mobile phone';
$string ['specificmarks'] = 'Custom marks';
$string ['specificmarks_help'] = 'Custom marks, one per line separating code and description by a # (e.g: Sp#Spelling error<br/>Gr#Grammar error)';
$string ['statistics'] = 'Statistics';
$string ['statisticstotals'] = 'Accumulated';
$string ['status'] = 'Status';
$string ['statusaccepted'] = 'Accepted';
$string ['statusabsent'] = 'Absent';
$string ['statusgrading'] = 'Grading';
$string ['statusgradingfinished'] = 'Ready for publishing';
$string ['statusmissing'] = 'Missing';
$string ['statusnotsent'] = 'Not sent';
$string ['statusregrading'] = 'Regrading';
$string ['statuspublished'] = 'Published';
$string ['statussubmitted'] = 'Submitted';
$string ['statuserror'] = 'Error';
$string ['submission'] = 'Manual submission';
$string ['teachercandownload'] = 'Show teachers download exam link';
$string ['teachercandownload_help'] = 'Show teachers a  exam link for the exams they send. It still requires to configure the capability for the teacher role.';
$string ['totalexams'] = 'Total exams';
$string ['totalpages'] = 'Expected pages';
$string ['totalpages_help'] = 'Indicates the number of pages expected for each student. It does not limit the pages that can be uploaded, it allows to associate pages to rubric criteria and shows visual warnings when there are missing pages for a student.';
$string ['totalpagesprint'] = 'Total Pages to Print';
$string ['uploadanswers'] = 'Upload answers';
$string ['uploaderrorsmanual'] = 'Upload errors manually';
$string ['uploadexamfile'] = 'ZIP file';
$string ['uploadinganswersheets'] = 'Uploading student answer sheets';
$string ['uploadanswersuccessful'] = 'Upload answers successful';
$string ['usesms'] = 'Use SMS';
$string ['usesms_help'] = 'Use SMS messaging  instead of sending email for eMarking security codes';
$string ['viewpeers'] = 'Students view peers\' exams';
$string ['viewpeers_help'] = 'Students are allowed to see their peers\' exams in an anonymous way';
$string ['viewsubmission'] = 'View submission';
$string ['visualizeandprocess'] = 'Visualizar errores y procesar nuevamente';
$string ['formnewcomment'] = 'Comment text';
$string ['writecomment'] = 'Write a Comment';
$string ['createcomment'] = 'Create Comment';
$string ['formeditcomment'] = 'Edit Comment:';
$string ['editcomment'] = 'Edit Comment';
$string ['createnewcomment'] = 'Create New Comment';
$string ['adjustments'] = 'Adjustments';
$string ['questioneditcomment'] = 'Do you want to edit the comment?';
$string ['questiondeletecomment'] = 'Do you want to delete the comment?';
$string ['creator'] = 'Creator';
$string ['building'] = 'Building';
$string ['details'] = 'Details';
$string ['originals'] = 'Originals';
$string ['copies'] = 'Copies';
$string ['teacher'] = 'Teacher';

$string ['gradestats'] = 'Grade stats by course';
$string ['gradehistogram'] = 'Grades histogram by course';
$string ['gradehistogramtotal'] = 'Grades histogram aggregated';
$string ['courseaproval'] = 'Pass ratio';
$string ['course'] = 'Course';
$string ['range'] = 'Range';
$string ['lessthan3'] = 'Less than 3';
$string ['between3and4'] = '3 to 4';
$string ['morethan4'] = 'More than 4';

$string ['advacebycriteria'] = 'Advance per criterion';
$string ['pointsassignedbymarker'] = 'Points assigned per markers';
$string ['advancebymarker'] = 'Advance per marker';
$string ['marker'] = 'Marker';
$string ['grades'] = 'Grades';

/**
 * Events
 */
$string ['eventemarkinggraded'] = 'Emarking';
$string ['eventsortpagesswitched'] = 'Sort pages';
$string ['eventrotatepageswitched'] = 'Rotate page';
$string ['eventaddcommentadded'] = 'Add coment';
$string ['eventaddregradeadded'] = 'Add Regrade';
$string ['eventupdcommentupdated'] = 'Up the comment';
$string ['eventdeletecommentdeleted'] = 'Delete comment';
$string ['eventaddmarkadded'] = 'Add Mark';
$string ['eventregradegraded'] = 'Regrade';
$string ['eventdeletemarkdeleted'] = 'Delete Mark';
$string ['eventhmarkingended'] = 'Finish Emarking';
$string ['eventinvalidaccessgranted'] = 'Invalid access, trying to upload exam';
$string ['eventsuccessfullydownloaded'] = 'Download successfull';
$string ['eventinvalidtokengranted'] = 'Invalid token trying to download exam.';
$string ['eventunauthorizedccessgranted'] = 'WARNING: An unauthorized access to emarking Ajax inteface';
$string ['eventmarkersconfigcalled'] = 'The markers config was called';
$string ['eventmarkersassigned'] = 'Markers have been assigned';
$string ['eventemarkingcalled'] = 'Emarking Called';


