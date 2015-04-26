BUBPDF - README
============================================================

Name: BUBPDF
Version: 1.0
Release date: 2009-12-17
Author:	Steve Petri 
	
Copyright (c) 2008-2009:
	Steve Petri
	www.pranatechdesigns.com
	
URLs:
	http://www.pranatechdesigns.com/bubblesoft/OpenSource/
	http://www.sourceforge.net/projects/bubpdf
	
Description:
	BUBPDF is a PHP class for generating bubble style PDF exam answer sheets on-the-fly, requiring only the LGPL TCPDF library to function. It is compatible with many flatbed and form feed scanners, and with the Gravic Remark Office OMR 7 scanning software. http://www.gravic.com/remark/officeomr/index.html
	Contact info@pranatechdesigns.com for other compatible modules.
	
Main Features:
		1. Answer Bubble Sheets, to be filled in by the students. Can have blank bubbles for the student ID (e.g. for late roster additions), or student ID filled in automatically
		2. Correct Answer Bubble Sheets, as an answer key
		3. Multiple Measures Exam Answer Sheets

Installation 
	1. copy the folder on your Web server
	2. install the included TCPDF library into a tcpdf folder, or upgrade to the latest version from http://www.tcpdf.org
	3. install 3 necessary fonts into TCPDF: omrextnd, omrbubbles, and 3of9_new. http://www.gravic.com/remark/officeomr/downloads.html#fonts

Source Code Documentation:
	(see the source code and provided samples)
	

License
	Copyright (C) 2008-2009  Steve Petri - Pranatech Designs, Inc
	
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 2.1 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.
	
	You should have received a copy of the GNU Lesser General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
	
	See LICENSE.TXT file for more information.
	
============================================================

BubPdf Documentation:

	Class for BubbleSoft Exams, which extends the open source TCPDF class library.
	TCPDF project (http://www.tcpdf.org) has been originally derived in 2002 from the Public Domain FPDF class by Olivier Plathey (http://www.fpdf.org), but now is almost entirely rewritten.
	This is a PHP class for generating PDF documents without requiring external extensions.

	For creating PDFs of:
	
		1. Answer Bubble Sheets, to be filled in by the students. Can have blank bubbles for the student ID (e.g. for late roster additions), or student ID filled in automatically
		2. Correct Answer Bubble Sheets, as an answer key
		3. Multiple Measures Exam Answer Sheets

	Must be called with a minimum of 7 calls:
	-----------------------------------------

			// Global Variables to be assigned
			$exam= "Cumulative Assessment 10-B";
			$grade= "Grade 4";
			$teacher= "Mr. Smithman";
			$subject= "Kimball";
			$instancedate= "Fall 2009";
			$exam_id="784983762";
			$student_code="1870654";					// [OPTIONAL - leave blank to let the student fill this in]
			$student_name="Rosales, Jose";			// [OPTIONAL - leave blank to let the student fill this in]

			// Create a new BubPdf object. Note: These parameters must be used: ('P', 'in', 'LETTER', true).
	1.		$BubPdf=new BubPdf('P', 'in', 'LETTER', true);

			// [OPTIONAL] hide the PDF viewer's toolbars like so:
			if($_GET['hidetoolbars']){	$preferences = array( 'HideToolbar' => true, 'HideMenubar' => true, 'HideWindowUI' => true); $BubPdf->setViewerPreferences($preferences); }

			// BP_NewExam sets the margins, etc
	2.		BP_NewExam($CorrectAnswersProvided=FALSE);
			// Note: if CorrectAnswersProvided==TRUE, then the student ID bubbles will not show up

			// adds a new page and sets the column to 0
	3. 		BP_StudentAnswerSheetStart();

			// [OPTIONAL] to create an answer key, correct answers are provided in an array, preceded by TRUE bool which enables it
			$CorrectAnswers[1]=1;
			$CorrectAnswers[2]=3;
			$CorrectAnswers[3]=4;

			// BP_AddAnswerBubbles allows you to select answer style, answers possible, number of questions, bool alternate choices, bool whether to fill in correct answers, correct answers array
	4.		BP_AddAnswerBubbles('A',4, 10,TRUE,TRUE,$CorrectAnswers);
			// answer style can be any uppercase letter (A-Y) as long as it won't wrap; 0; 1; or Y for Yes/No type answers; T for True/False type answers
			// answers possible can be only 2 for Y/N or T/F
			// number of questions - up to three digit numbers have been tested with the margins
			// bool alternate choices - TRUE of FALSE, whether you want every other row to have different choices, e.g. A-B-C-D, E-F-G-H
			// bool whether to fill in correct answers  - TRUE of FALSE, whether you are providing correct answers
			// correct answers array - one based

			// [OPTIONAL] can be called multiple times, don't need to have answers provided
			BP_AddAnswerBubbles('q',5, 7,TRUE);

			// [OPTIONAL] For Multiple Measures style exams, use this call instead (it can also be called multiple times)
			BP_AddMultMeasAnswerBubbles($points, $MultMeasName, $MultMeasPointStart)

			// Begins page formatting of the output, calculates page numbers.  Resets the question number to 1, so that you can loop BP_AddAnswerBubbles() for different answer layouts / students.
	5.		BP_StudentAnswerSheetComplete();

			// the BP_CreateExam call can be used to retrieve an array of the zone assignments
	6.		$myZones = BP_CreateExam();

			// [OPTIONAL] do something with the zone information
			if($MODE==="scan_software") pass_zone_info_to_scan_software($myZones);

			//display the PDF of the Exam
	7.		$BubPdf->Output("exam_".$exam_id.".pdf"); // NOTE: "Save a Copy" in the acrobat plugin seems to ignore the name, though "File->Save As..." in the browser does not.
			// [OPTIONAL] $BubPdf->Output("exam_".$exam_id.".pdf", "F"); // can be output to file


	IMPORTANT: No other text output can be sent to the browser (e.g. header information, debug info, etc) 

	Columns:
		// Allow up to 4 columns, but shrink to fewer in certain instances (wide columns detected will cause a switch to fewer columns)
		// fitting about 80 answers per page
		// Force One Column whenever some questions have more than 13 answer bubbles
		// Force Two Columns whenever some questions have more than 7 answer bubbles
		// Force Three Columns whenever some questions have more than 5 answer bubbles

	Notes:
		// supports up to a 10 digit student_code
		// allows 1.0 inch for Exam ID, Right Justified
		// if student_code was provided, BubPdf will fill in the appropriate bubbles, and otherwise leave them blank
		// all Layout is in inches
		// bool alternate choices TRUE will be turned FALSE for Y/N answer style and T/F answer style
		// to create an answer key, correct answers are provided in a (one based) array, preceded by TRUE bool which enables it
		// StudentCode bubbles removed whenever CorrectAnswers are provided
	
	Debugging:
		$DBG=1;  // will print little dashed blue rectangles around all defined bubble zones
		$DBG="zones";  // no pdf, just a print out of when zones are created, and all zone dimensions

