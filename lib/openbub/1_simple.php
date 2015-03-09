<?php

require_once('ans_pdf_open.php'); // for more documentation, see the top of this file

// Variables to be assigned
$exam= "Cumulative Assessment 10-B";
$grade= "Grade 4";
$teacher= "Mr. Smithman";
$subject= "Language Arts";
$instancedate= "Fall 2009";
$exam_id="786B";
$student_code="1870654129";
$student_name="Rosales, Jose";

// Create a new BubPdf object. 
$BubPdf=new BubPdf('P', 'in', 'LETTER', true);

// NewExam sets the margins, etc
BP_NewExam($CorrectAnswersProvided=FALSE);

BP_StudentAnswerSheetStart();

// A simple 12 question exam
BP_AddAnswerBubbles('A',5, 12,FALSE,FALSE);

BP_StudentAnswerSheetComplete();

// the CreateExam call can be used to retrieve an array of the zone assignments
$myZones = BP_CreateExam();

//display the PDF of the Exam
$BubPdf->Output("exam_".$exam_id.".pdf"); // NOTE: "Save a Copy" in the acrobat plugin seems to ignore the name, though "File->Save As..." in the browser does not.

?>
