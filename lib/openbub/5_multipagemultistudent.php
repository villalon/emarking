<?php

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

// Generic answer sheet, can be copied and filled in at exam time
BP_StudentAnswerSheetStart();
BP_AddAnswerBubbles('A',5, 60,TRUE,FALSE);	// 60 alternating questions with 5 choices
BP_AddAnswerBubbles('A',4, 60,TRUE,FALSE);	// 60 alternating questions with 4 choices
BP_StudentAnswerSheetComplete();

// answer sheet for Jose
$student_code="1870654129";
$student_name="Rosales, Jose";
BP_StudentAnswerSheetStart();
BP_AddAnswerBubbles('A',5, 60,TRUE,FALSE);	// 60 alternating questions with 5 choices
BP_AddAnswerBubbles('A',4, 60,TRUE,FALSE);	// 60 alternating questions with 4 choices
BP_StudentAnswerSheetComplete();

// answer sheet for Mary
$student_code="8675309";
$student_name="Cunningham, Mary";
BP_StudentAnswerSheetStart();
BP_AddAnswerBubbles('A',5, 60,TRUE,FALSE);	// 60 alternating questions with 5 choices
BP_AddAnswerBubbles('A',4, 60,TRUE,FALSE);	// 60 alternating questions with 4 choices
BP_StudentAnswerSheetComplete();

// the CreateExam call can be used to retrieve an array of the zone assignments
$myZones = BP_CreateExam();

//display the PDF of the Exam
$BubPdf->Output("exam_".$exam_id.".pdf"); // NOTE: "Save a Copy" in the acrobat plugin seems to ignore the name, though "File->Save As..." in the browser does not.

?>
