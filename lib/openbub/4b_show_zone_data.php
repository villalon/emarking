<?php

$DBG="zones";  // no pdf, just a print out of when zones are created, and all zone dimensions

require_once('ans_pdf_open.php'); // for more documentation, see the top of this file

// Variables to be assigned
$exam= "Cumulative Assessment 10-B";
$grade= "Grade 4";
$teacher= "Mr. Smithman";
$subject= "Language Arts";
$instancedate= "Fall 2009";
$exam_id="786B";

// Create a new BubPdf object. 
$BubPdf=new BubPdf('P', 'in', 'LETTER', true);

// NewExam sets the margins, etc
BP_NewExam($CorrectAnswersProvided=FALSE);

// Generic answer sheet
BP_StudentAnswerSheetStart();

// 12 questions
BP_AddAnswerBubbles('A',5, 12,FALSE,FALSE);
// 6 questions, True, False
BP_AddAnswerBubbles('T',2, 6,FALSE,FALSE);
// 10 questions, alternating
BP_AddAnswerBubbles('A',4, 10,TRUE,FALSE);
// 10 questions, yes/no
BP_AddAnswerBubbles('Y',2, 10,FALSE,FALSE);
// 20 questions, numerical
BP_AddAnswerBubbles('1',5, 20,FALSE,FALSE);
// 22 questions, alternating
BP_AddAnswerBubbles('A',4, 22,TRUE,FALSE);

BP_StudentAnswerSheetComplete();

// Student specific answer sheet
$student_code="1870654129";
$student_name="Rosales, Jose";

BP_StudentAnswerSheetStart();

// 12 questions
BP_AddAnswerBubbles('A',5, 12,FALSE,FALSE);
// 6 questions, True, False
BP_AddAnswerBubbles('T',2, 6,FALSE,FALSE);
// 10 questions, alternating
BP_AddAnswerBubbles('A',4, 10,TRUE,FALSE);
// 10 questions, yes/no
BP_AddAnswerBubbles('Y',2, 10,FALSE,FALSE);
// 20 questions, numerical
BP_AddAnswerBubbles('1',5, 20,FALSE,FALSE);
// 22 questions, alternating
BP_AddAnswerBubbles('A',4, 22,TRUE,FALSE);

BP_StudentAnswerSheetComplete();

// the CreateExam call can be used to retrieve an array of the zone assignments
$myZones = BP_CreateExam();

// for debug, print out the zones
echo "<H1>Example of zone data that would be sent to the scanning software:</H1><pre>";
print_r($myZones);
echo "</pre>";

?>
