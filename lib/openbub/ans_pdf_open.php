<?php


class BubPdf extends FPDI
{

/* BubPdf Documentation:

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

*/

	//Current column
	var $col=0;
	//Current question number
	var $qnum=0;
	//Total number of questions
	var $TotalQues=0;

	//different from total pages from method PageNo(), MyPageNum is per student
	var $MyPageNum=1;
	var $NumStudentsProcessed=0;
	var $MyFinalPageNum=0;

	// max width (number of digits) in the student id number
	var $MaxStudentCodeWidth = 10;
	
	//StudentCode bubbles cell width (and comb too)
	var $studentbubcellw=0.23; 
	
	//answer bubble cell width & if using extended bubble font
	var $ansbubcellw=.27;
	var $UseExtendedBubFont;
	
	// File path
	public $logofilepath = null;
	
	//All zones that get defined
	var $zonesArray;
	var $numExamZones = 0;
	var $ansWidth;
	var $ztlx;		// zone top left x coord
	var $ztly;		// zone top left y coord
	var $zbrx;		// zone bottom right x coord
	var $zbry;		// zone bottom right y coord
	var $zrows=0;		// number of rows (questions) in the zone
	var $zcols=0;		// number of cols (ans choices) in the zone
	var $ZoneArrayCapturing = TRUE;
	var $lastbuby;

	// Arrays for Answer Bubble Zones
	var $BubStyles = array();
	var $BubAnswersPossible = array();
	var $BubNumberOfQuestions = array();
	var $BubAlternateChoices = array();
	var $BubFillCorrectAnswers = array();
	var $BubCorrectAnsArray = array();
	
	// Arrays for Multiple Measures
	var $BubMmPoints = array();
	var $BubMmName = array();
	var $BubMmPointStart = array();

	// StudentCode bubbles removed whenever CorrectAnswers are provided
	var $CorrectAnswersProvided = FALSE;

	// InlineAnswerBubbles provided for younger students
	var $InlineAnswerBubbles = FALSE;
	var $LinkGroup = 0;					// integer, incrementing, or 0 if none
	var $LinkStart = 0; 				// boolean, 1 for true, 0 for false, 2 means already started

	// don't create a new zone if Y > ZoneYMax, because a PageBreak will happen anyway
	var $ZoneYMax = 9.85;
	var $BadZoneStarted = FALSE;

	// wide columns detected will cause a switch to fewer columns
	var $ForceThreeColumns = FALSE;
	var $ForceTwoColumns = FALSE;
	var $ForceOneColumn = FALSE;

	// Margin and Column width in the Bubble Area
	var $AreaTopMargin = 3;
	var $AreaLeftMargin = 0.75;
	var $AreaColumnWidth = 1.95;
	var $PrevAreaColumnWidth = 1.95;
	var $AreaBotMargin = 1;

	//when figuring out if q and a will fit for layout
	var $TryQuestion=TRUE;
	var $TryAnswers=TRUE;
	var $DistYCol;
	
	// While in Header, save location so Footer can print page numbers
	var $PageNumX;
	var $PageNumY;

	// whether or not to show the student_code bubbles and the exam barcode
	var $showOmrData = TRUE;

	// mode for printing questions only
	var $PlainExamQuestionsMode = FALSE;

	// mode for multiple-measures style bubbles
	var $MultipleMeasuresMode = FALSE;

	// Arrays for Answer Bubble Zones
	var $PassagesPrinted = array();

	// manage when a passage temporarily spans one column					
	var $TempTopMargin=0;
	var $PrePassageForce=1;
					
	// When printing an Exam, even numbered questions get this label for alternating answer types
	var $NumericEvenStartAns;
	var $AlphaEvenStartAns;
	
	//these are used in AddAnswerBubbles to figure out if we need extended font or not
	var $TempNumericEvenStartAns;
	var $TempQNum=0;

	// detect page/column breaks whenever a transaction is in progress
	var $TransactionInProgress = FALSE;
	var $TransactionBreakEncountered = FALSE;
	var $TransactionPageBreakEncountered=0;

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////                             	                                                                                                               /////////
	/////////		The following functions deal primarily with basic PDF printing: Headers, Footers, PageBreaks, Columns, BubbleZones, etc                /////////
	/////////                             	                                                                                                               /////////
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	function Header()
	{
	    parent::Header();return;
		 //Page header
		 global $exam_id, $examtitle, $grade, $subject, $instancedate, $teacher, $student_code, $student_name,$DBG,$authlib,$logofilepath;

			//$filepath=$_SERVER['PHP_SELF'];																	//figure out what $filepath is
			//for($i=0;$i<(count(explode("/", $filepath))-2);$i++) $ImgLoc .= "../";				//figure out where images are located
			
		 // If we have a logo file, we add it
			if($logofilepath) {
			 $this->Image($logofilepath,0.5,0.5,1.5);
			}

			if($DBG){
				 $this->SetXY(2.15,1); 
				 $this->SetFont('helvetica','I',6);
				 $this->SetTextColor(255,0,0);
				 $this->Cell(0,0,"DBG: BubPdf Header called for [$student_name], [$teacher], [$instancedate].");
				 $this->SetTextColor(0,0,0,255,0);
			}
			
			// MyPageNum
			if($this->MyFinalPageNum==0 || $this->MyFinalPageNum>=$this->PageNo()){
				// use PageNo() on the first answer sheet set only
				$this->MyPageNum = $this->PageNo();
			}else{
				// otherwise calculate based on number of students processed and the saved final page num from the first answer sheet
				$this->MyPageNum = $this->PageNo() - $this->MyFinalPageNum*($this->NumStudentsProcessed);
			}

			// StudentCode bubbles removed whenever CorrectAnswers are provided
			if($this->showOmrData && !$this->CorrectAnswersProvided && !$this->TransactionInProgress){
				// Student Name
				$this->SetXY(.5,2.4); 
				$this->SetFont('helvetica','B',10);
				$this->Cell(0.5,.25,"Name:",0,0); //height of cell should change if printed name font size changes
				// underlined font for Student ID line
				$this->SetFont('courier','U',14);
				$padding = "";
				$StudentLineLength = 35;
				if($student_name){
						for($i=0;$i<($StudentLineLength-strlen($student_name));$i++){
							$padding .= " ";
						}
						$this->Cell(4,0," ".$student_name.$padding,0,1); // student_name written on the line
				}else{
						for($i=0;$i<($StudentLineLength);$i++){
							$padding .= " ";
						}
						$this->Cell(4,0,$padding,0,1); // Blank line for them to fill in 
				}
				 
				 
				 $this->SetFont('helvetica','B',8);
				 // X Position for the Student ID Bubbles area
				 $IdPosX = 5.4;
				 
				 $this->SetXY($IdPosX, 0.55); 
				 // ID: 
				 $this->Cell(0,0,"ID:",0,0);
				 // underlined font for Student ID line
				 $this->SetFont('courier','U',9);
				 $SquareX = $IdPosX + 0.2; //how far comb starts from "ID:"
				/*if($student_code){
						for($i=0;$i<($StudentCodeLineLength-strlen($student_code));$i++){
							$padding .= " ";
						}
						$this->Cell(1.1,0,$student_code.$padding,0,1); // student_code written on the line
				 }else*/{
						
						// provide a "comb" over the student_code bubbles, so they know where each number goes
						$this->SetLineStyle(array('width' => 0.65 / $this->getScaleFactor(), 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(204, 204, 204)));
						$this->SetFont('courier','',11);
						$this->SetTextColor(204, 204, 204);
						for($i=0;$i<($this->MaxStudentCodeWidth+1);$i++){
							$CombDelta = $this->studentbubcellw*$i; //how wide comb sections are
							$this->Line($SquareX+$CombDelta, 0.55,$SquareX+$CombDelta, 0.7);
							// print the digits in their respective comb slot
							$this->SetXY($SquareX+$CombDelta+0.07, 0.53- 0.02); 
							//if($student_code[$i]!=="") $DigitPrint = $student_code[$i];		// left justified
							$j=$i+strlen($student_code)-10; 									// right justified 
							if(isset($student_code[$j])) $DigitPrint = $student_code[$j];			// right justified 
							else $DigitPrint = "";
							$this->Cell(1.1,0,$DigitPrint,0,1); 
						}
						//  blank line for them to fill in
						$this->Line($SquareX, 0.67,$SquareX+$CombDelta, 0.67);
				
						$this->SetTextColor(0, 0, 0);
				 }

				 $BubblesX = $IdPosX + 0.25;

				 $this->SetLeftMargin($BubblesX);
				 $this->SetXY($BubblesX, 0.85); 
				 
					// grab the top left zone coordinates
					$ztlx = $this->GetX()-0.04;
					$ztly = $this->GetY()-0.09; //adjust top of student bubble box

				 // now the bubbles, up to 10 digits
				 $this->SetFont("omrbubbles", "", 11);
				 // if student_code was provided, fill in the appropriate bubbles
				if($student_code){
						 $this->FilledInNumberRows($student_code); // fill in the bubbles for them
				}else{ // otherwise leave them blank
					for($k=0;$k<10;$k++){
						// empty bubbles
						$BubbleData="";
						for($i=0;$i<$this->MaxStudentCodeWidth;$i++){
							if ($i==($this->MaxStudentCodeWidth-1)) {
								$this->Cell($this->studentbubcellw, 0, strval($k), 0, 1);
								// grab the bottom zone coordinates of last cell
								$this->lastbuby = $this->GetY()+$this->studentbubcellw;
							} else $this->Cell($this->studentbubcellw, 0, strval($k), 0, 0);
						}
					}
				}

				// stick the zone in the array, if it's not already there
				$arrayIndex = 'student_code' . strval($this->MyPageNum);
				if(!isset($this->zonesArray[$arrayIndex])){

					 // Coordinates of student ID bubble zone
					$ztlx = $ztlx-.1;
					$RectWidth = ($this->studentbubcellw*$this->MaxStudentCodeWidth)+.2;
					$RectHeight = $this->lastbuby-$ztly-$this->studentbubcellw+.07; //adjust bottom of student bubble box
					$zbrx = $ztlx + $RectWidth;
					$zbry = $ztly + $RectHeight;
					if($DBG){
						 // Rectangle (show for debug only) of student ID bubble zone
						$this->SetLineStyle(array('width' => 0.01, 'dash' => "1,3", 'color' => array(0,0,255)));
						$this->Rect($ztlx, $ztly, $RectWidth, $RectHeight);
					}

					$this->zonesArray[$arrayIndex]['ztlx'] = $ztlx;
					$this->zonesArray[$arrayIndex]['zbrx'] = $zbrx;
					$this->zonesArray[$arrayIndex]['ztly'] = $ztly;
					$this->zonesArray[$arrayIndex]['zbry'] = $zbry;
					$this->zonesArray[$arrayIndex]['PageNum'] = $this->MyPageNum;

				}
			}

			 $this->SetLeftMargin(2.15);
			 $this->SetXY(2.15, 0.50); 
			 
			 //if plain questions, print the exam name up top to save space
			 if($this->PlainExamQuestionsMode) {
				// Arial Bold 15
				// Exam
				$this->SetFont('helvetica','B',14);
				$this->MultiCell(5.75,0,$examtitle,0,1);
				$this->SetFont('helvetica','',8);
				if($this->InlineAnswerBubbles){
					$this->Cell(5,0,"(ID: ".$exam_id."C)",0,1); 
					//TBD Don't print the "C" once Gravic fixes their bug. Teacher's should not see "C" once there is not a need to enter it into scan station.
				}else{
					$this->Cell(5,0,"(ID: ".$exam_id.")",0,1);
				}
			}
			
			 //print the teacher & grade info, etc.
			 // Arial 8
			 $this->SetFont('helvetica','',9);
			 // Grade, Subject, Instance Date
			 $ExamInfo = $grade;
			 if($subject) $ExamInfo .= ' / ' . $subject;
			 if($instancedate) $ExamInfo .= ' / ' . $instancedate;
			 $this->MultiCell(3.1,0, $ExamInfo ,0,1);

			 // grab X,Y so footer can print page number info
			 $this->PageNumX = $this->GetX();
			 $this->PageNumY = $this->GetY();
			 
			//if not plain questions, print the exam name lower since it is wider here
			if(!$this->PlainExamQuestionsMode) {
				$this->SetLeftMargin(.5);
				$this->SetXY(.5,1.5);
				// Arial Bold 15
				// Exam
				$this->SetFont('helvetica','B',14);
				$this->MultiCell(4.75,0,$examtitle,0,1);
				$this->SetFont('helvetica','',8);
				if($this->InlineAnswerBubbles){
					$this->Cell(4.75,0,"(ID: ".$exam_id."C)",0,0);
				}else{
					$this->Cell(4.75,0,"(ID: ".$exam_id.")",0,0);
				}
			} 
			
			// line at bottom of header
			if($this->PlainExamQuestionsMode) {
				$this->SetLineStyle(array('width' => 0.85 / $this->getScaleFactor(), 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(204,204,204)));
				$this->Line(0.5,$this->AreaTopMargin-.15,8,$this->AreaTopMargin-.15);
			}else{
				$this->SetLineStyle(array('width' => 0.85 / $this->getScaleFactor(), 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(204,204,204)));
				$this->Line(0.5,$this->AreaTopMargin-.15,8,$this->AreaTopMargin-.15);
			}
			$this->SetLineStyle(array('width' => 0.85 / $this->getScaleFactor(), 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));

			if($DBG){
				$TcPageNo = $this->PageNo();
				$MyFinalPage = $this->MyFinalPageNum;
				$NumStudentsProcessed = $this->NumStudentsProcessed;
			}

	}

	// supports up to a 10 digit student_code
	function FilledInNumberRows($student_code)
	{

		// split the array into each digit
		$id_array = str_split($student_code);
		
		// grab the x coordinate, etc
		$x = $this->GetX();
		$w = $this->GetCharWidth('0');
		
		for($k=0;$k<10;$k++){
			// grab the y coordinate
			$MultY = 0.7;
			$y = $this->GetY();
			$cy = $y + $w*$MultY;
			
			// empty bubbles
			$BubbleData="";
			for($i=0;$i<$this->MaxStudentCodeWidth;$i++){
				if ($i==($this->MaxStudentCodeWidth-1)) {
					$this->Cell($this->studentbubcellw, 0, strval($k), 0, 1);
								
					// grab bottom zone coordinates of last cell
					$this->lastbuby = $this->GetY()+$this->studentbubcellw;
				} else $this->Cell($this->studentbubcellw, 0, strval($k), 0, 0);
			} 
		
			/* example: $student_code=1870654, id_array=
				 [0] => 1
				 [1] => 8
				 [2] => 7
				 [3] => 0
				 [4] => 6
				 [5] => 5
				 [6] => 4
			*/	 

			// fill in the proper bubble(s)
			/* bubbles to the left
			for($i=0;$i<strlen($student_code);$i++){
				if($k==intval($id_array[$i])){
					$cx = $x + .068 + ($this->studentbubcellw*$i); // accounts for the space
					$this->Circle($cx,$cy,.0733,0,360,"F");
				}
			}
			*/
			// bubbles to the right
			for($i=0;$i<strlen($student_code);$i++){
				if($k==intval($id_array[$i])){
					$cx = $x + .068 + ($this->studentbubcellw*($i+10-strlen($student_code))); 
					$this->Circle($cx,$cy,.0733,0,360,"F");
				}
			}
		}
	}
	
	function Footer()
	{
	    parent::Footer();
	    return;
		global $DBG,$exam_id, $RosterSectionID, $instancedate, $authlib, $student_name, $teacher, $instancedate;
		
		if($DBG){
			$this->SetXY(1.0,10.6); 
			$this->SetFont('helvetica','I',6);
			$this->SetTextColor(255,0,0);
			$TcPageNo = $this->PageNo();
			$MyFinalPage = $this->MyFinalPageNum;
			$NumStudentsProcessed = $this->NumStudentsProcessed;
			$this->Cell(0,0,"DBG: BubPdf Footer called for [$student_name], [$teacher], [$instancedate]. MyPageNum=$this->MyPageNum. PageNo=$TcPageNo.");
			$this->SetTextColor(0,0,0,255,0);
		}

		// line at top of footer
		$this->SetLineStyle(array('width' => 0.85 / $this->getScaleFactor(), 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(204,204,204)));
		$this->Line(0.5, 10,8,10);
		$this->SetLineStyle(array('width' => 0.85 / $this->getScaleFactor(), 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
			
		//if we just finished printing the very first page, lets capture the bar code zones
		if($this->showOmrData && !$this->PlainExamQuestionsMode && !$this->TransactionInProgress && !$this->CorrectAnswersProvided){

			// calculate the contents of the barcode regions: the first contains the exam_id, which becomes a form identifier. The 2nd contains date, teacher, location, and page number.
			if($this->InlineAnswerBubbles){
				$BarcodeData1 = $exam_id . "C";
			}else{
				$BarcodeData1 = $exam_id;
			}

			$BarcodeData2 = $instancedate.'$'.$RosterSectionID; 

			$BarcodeData3 = $this->MyPageNum; 

			// perform a bunch of substitutions to shorten BarcodeData2 so the barcode doesn't become too long
			$patterns[0] = '/-(\d-)/';				//2 digit day
			$patterns[1] = '/-(\d\d-)/';			//remove dashes from day
			$patterns[2] = '/[- ]20(\d\d)/'; 		//2 digit year
			$patterns[3] = '/[- ]/'; 				//dashes and spaces become pluses
			$patterns[4] = '/End.of.Year/'; 		//shorten the date nicknames to 3 chars
			$patterns[5] = '/Summer/'; 				//shorten the date nicknames to 3 chars
			$patterns[6] = '/Winter/'; 				//shorten the date nicknames to 3 chars
			$patterns[7] = '/Spring/'; 				//shorten the date nicknames to 3 chars
			$patterns[8] = '/Fall/'; 				//shorten the date nicknames to 3 chars
			$replacements[0] = '0$1';
			$replacements[1] = '$1';
			$replacements[2] = '$1';
			$replacements[3] = '$';
			$replacements[4] = 'Eoy';
			$replacements[5] = 'Sum';
			$replacements[6] = 'Win';
			$replacements[7] = 'Spr';
			$replacements[8] = 'Fal';

			$BarcodeData2 =  preg_replace($patterns, $replacements, $BarcodeData2);	//shortening

			// the first barcode contains the exam_id
			$BarX = 0.6;
			$BarY = 10.1;

			$this->SetLeftMargin($BarX);
			
			//DO NOT WRITE ON BARCODES
			$this->SetXY($BarX-.1, $BarY-.22);
			$this->SetFont("helvetica", "", 8);
			$this->SetTextColor(150, 150, 150);
			$this->Cell(2,0,"DO NOT WRITE BELOW THIS LINE",0,0);
			$this->SetTextColor(0, 0, 0);
			
			if ($DBG==1) {
				if($BarcodeData2!="$$$"){
					$this->Cell(2,0," [$BarcodeData1, $BarcodeData2]",0,0);
				}else{
					$this->Cell(2,0," [$BarcodeData1]",0,0);
				}
			}

			//barcode of exam data
			$this->SetXY($BarX, $BarY); 
			//if(!$DBG) $this->SetFont("3of9_new", "", 30);
			$this->SetFont("3of9_new", "", 30);

			$this->Cell(1.8, 0, "*" . $BarcodeData1 . "*"); // 3 of 9 requires a beginning asterisk (*) and ending character asterisk (*) 

			if($exam_id && $RosterSectionID && $this->PageNo()==1){ // (first time only) submit $BarcodeData1 to the database
				$today = date("Y-m-d");
				$authlib->bsdb_connect();
				//uppercase the instance data, because 3of9 barcode seems to scan in that way
				$query = "UPDATE exam_instances SET inst_data='" . strtoupper($BarcodeData2) . "' WHERE RosterSectionID=$RosterSectionID AND ExamId='$exam_id' AND InstanceDate='$instancedate' LIMIT 1";
				@mysql_query($query);
				//$this->SetXY($BarX, $BarY+0.2); 	//DBG Only
				//$this->Cell(4.8, 0, $query); 		//DBG Only
			}

			// stick the zone in the array, if it's not already there
			$arrayIndex = 'exam_data' . strval($this->MyPageNum);

			if($DBG){
				$this->SetXY(5.0,10.6); 
				$this->SetFont('helvetica','I',6);
				$this->SetTextColor(255,0,0);
				$exam_dataIsSet = isset($this->zonesArray[$arrayIndex]);
				$this->Cell(0,0,"DBG: exam_data zone Footer called for MyPageNum=$this->MyPageNum. isset=$exam_dataIsSet");
				$this->SetTextColor(0,0,0,255,0);
				$this->SetFont("3of9_new", "", 30);
			}
			if(!isset($this->zonesArray[$arrayIndex])){

				// zone coordinates
				$ztlx = $BarX - 0.3;
				$ztly = $BarY - 0.22;
				$zbrx = $BarX + 1.6;
				$zbry = $BarY + 0.71;
				$RectWidth = $zbrx - $ztlx;
				$RectHeight = $zbry - $ztly;

				if($DBG){
					$this->SetLineStyle(array('width' => 0.01, 'dash' => "1,3", 'color' => array(0,0,255)));
					$this->Rect($ztlx, $ztly, $RectWidth, $RectHeight);
				}

				$this->zonesArray[$arrayIndex]['ztlx'] = $ztlx;
				$this->zonesArray[$arrayIndex]['zbrx'] = $zbrx;
				$this->zonesArray[$arrayIndex]['ztly'] = $ztly;
				$this->zonesArray[$arrayIndex]['zbry'] = $zbry;
				$this->zonesArray[$arrayIndex]['PageNum'] = $this->MyPageNum;

			}

			// the second barcode contains instance data
			$BarX = 2.7;
			$BarY = 10.1;

			//barcode of instance data
			$this->SetXY($BarX, $BarY); 
			//if(!$DBG) $this->SetFont("3of9_new", "", 30);
			$this->SetFont("3of9_new", "", 30);

			$this->Cell(3.8, 0, "*" . $BarcodeData2 . "*"); // 3 of 9 requires a beginning asterisk (*) and ending character asterisk (*) 

			// stick the zone in the array, if it's not already there
			$arrayIndex = 'inst_data' . strval($this->MyPageNum);
			if(!isset($this->zonesArray[$arrayIndex])){

				// zone coordinates
				$ztlx = $BarX - 0.4;
				$ztly = $BarY - 0.22;
				$zbrx = $BarX + 3.4;
				$zbry = $BarY + 0.71;
				$RectWidth = $zbrx - $ztlx;
				$RectHeight = $zbry - $ztly;

				if($DBG){
					$this->SetLineStyle(array('width' => 0.01, 'dash' => "1,3", 'color' => array(0,0,255)));
					$this->Rect($ztlx, $ztly, $RectWidth, $RectHeight);
				}

				$this->zonesArray[$arrayIndex]['ztlx'] = $ztlx;
				$this->zonesArray[$arrayIndex]['zbrx'] = $zbrx;
				$this->zonesArray[$arrayIndex]['ztly'] = $ztly;
				$this->zonesArray[$arrayIndex]['zbry'] = $zbry;
				$this->zonesArray[$arrayIndex]['PageNum'] = $this->MyPageNum;

			}

			// the third barcode contains pagenum
			$BarX = 6.6;
			$BarY = 10.1;

			//barcode of instance data
			$this->SetXY($BarX, $BarY); 
			//if(!$DBG) $this->SetFont("3of9_new", "", 30);
			$this->SetFont("3of9_new", "", 30);

			$this->Cell(1, 0, "*" . $BarcodeData3 . "*",0,0,"R"); // 3 of 9 requires a beginning asterisk (*) and ending character asterisk (*) 

			// stick the zone in the array, if it's not already there
			$arrayIndex = 'pagenum' . strval($this->MyPageNum);
			if(!isset($this->zonesArray[$arrayIndex])){

				// zone coordinates
				$ztlx = $BarX - 0.4;
				$ztly = $BarY - 0.22;
				$zbrx = $BarX + 1.6;
				$zbry = $BarY + 0.71;
				$RectWidth = $zbrx - $ztlx;
				$RectHeight = $zbry - $ztly;

				if($DBG){
					$this->SetLineStyle(array('width' => 0.01, 'dash' => "1,3", 'color' => array(0,0,255)));
					$this->Rect($ztlx, $ztly, $RectWidth, $RectHeight);
				}

				$this->zonesArray[$arrayIndex]['ztlx'] = $ztlx;
				$this->zonesArray[$arrayIndex]['zbrx'] = $zbrx;
				$this->zonesArray[$arrayIndex]['ztly'] = $ztly;
				$this->zonesArray[$arrayIndex]['zbry'] = $zbry;
				$this->zonesArray[$arrayIndex]['PageNum'] = $this->MyPageNum;
			}

		}
		
		// move pen back up into Header region, for the page number
		$this->SetLeftMargin(2.15);
		$this->SetXY($this->PageNumX, $this->PageNumY);

		//print the Answer sheet page num if not exam questions
		$this->SetFont('helvetica','',9);
		if (!$this->PlainExamQuestionsMode) {
			$this->MultiCell(3.1,0,$teacher,0,1);
			if($this->MyFinalPageNum>0){
				$this->Cell(0,0,"Answer Sheet Page ".$this->MyPageNum." of ".$this->MyFinalPageNum,0,0);
			}else{
				//TBD: Calculate MyFinalPageNum before printing. For now, eliminate it on the generic...
				$this->Cell(0,0,"Answer Sheet Page ".$this->MyPageNum,0,0);
			}
		} else {
			// JV $this->Cell(0,0,"Page ".$this->MyPageNum." of ".$this->getAliasNbPages(),0,0);
			$this->Cell(0,0,"Page ".$this->MyPageNum." of ",0,0);
		}
			
		// footer image
		/*
		$filepath=$_SERVER['PHP_SELF'];																	//figure out what $filepath is
		for($i=0;$i<(count(explode("/", $filepath))-2);$i++) $ImgLoc .= "../";				//figure out where images are located
		$this->Image($ImgLoc . images/bubblesoftlogo_print.jpg',6.50,10.3,1.5);			// bubblesoft logo in bottom right
		*/
	}

	//Method accepting or not automatic page break
	function AcceptPageBreak()
	{
		global $DBG,$student_code,$student_name;

		if(!$this->PlainExamQuestionsMode && !$this->MultipleMeasuresMode && !$this->InlineAnswerBubbles) BP_FinishOffExamBubbleZone($this);

		if($this->TransactionInProgress) $this->TransactionBreakEncountered=TRUE;

		// NOTE: $this->col is a 0 based variable!
		// allow up to 4 columns, but shrink to fewer in certain instances
		// wide columns detected will cause a switch to fewer columns
		if( ( ( $this->col == 2 ) && !$this->ForceThreeColumns ) || ( ( $this->col == 1 ) && !$this->ForceTwoColumns ) || ( ( $this->col == 0 ) && !$this->ForceOneColumn ) ) {
			//Go to next column
			BP_SetCol($this, $this->col+1);
			//Set ordinate to top
			return false; //Keep on page
		} else {
			$this->TransactionPageBreakEncountered++;
			//Go back to first column
			BP_SetCol($this, 0);
			return true; //Page break
		}

	}

	function setFilePath($filepath) {
	    $this->logofilepath = $filepath;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////                             	                                                                                                               /////////
/////////	                                       	The following are general Bubble Exam utility functions                                            /////////
/////////                             	                                                                                                               /////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function BP_SetCol($BubPdf, $col,$NoNewZone=FALSE)
{
	global $DBG;


	//Set position at a given column
	$BubPdf->col=$col;
	$x = $BubPdf->AreaLeftMargin + ( $col * $BubPdf->AreaColumnWidth );
	if($BubPdf->PlainExamQuestionsMode && $col==1) $x += 0.4; // Add a bit extra for this mode
	$BubPdf->SetLeftMargin($x);
	$BubPdf->SetX($x);

	// manage when a passage temporarily spans one column
	if($BubPdf->TempTopMargin){
		$BubPdf->SetY($BubPdf->TempTopMargin); 
		$vlinetop=$BubPdf->TempTopMargin;
		$BubPdf->TempTopMargin=0;
	}else{
		$BubPdf->SetY($BubPdf->AreaTopMargin); // Top Margin for Columns
		$vlinetop=$BubPdf->AreaTopMargin-.15;
	}
	
	//make a col divider line
	//if (!$BubPdf->PlainExamQuestionsMode && !$BubPdf->InlineAnswerBubbles && $col!=0) {
	if ($col!=0) {
		$BubPdf->SetLineStyle(array('width' => 0.85 / $BubPdf->getScaleFactor(), 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(204,204,204)));
		$BubPdf->Line($x-.32, $vlinetop,$x-.32,10);
		$BubPdf->SetLineStyle(array('width' => 0.85 / $BubPdf->getScaleFactor(), 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
	}

	if(!$BubPdf->PlainExamQuestionsMode && !$BubPdf->MultipleMeasuresMode && !$BubPdf->InlineAnswerBubbles && !$NoNewZone) BP_StartNewExamBubbleZone($BubPdf);

}

function BP_StartNewExamBubbleZone($BubPdf){
	global $DBG;

	if($BubPdf->ZoneArrayCapturing && !$BubPdf->TransactionInProgress){ // only need to capture zone information for the first student's answer sheet(s), because the others should be identical
	
		// don't create a new zone if Y > ZoneYMax, because a PageBreak will happen anyway
		if( $BubPdf->GetY() < $BubPdf->ZoneYMax ){
			// grab the top left zone coordinates, begin a new exam bubble zone entry
			$BubPdf->numExamZones++;
			if($BubPdf->MultipleMeasuresMode == TRUE){
				$BubPdf->ztlx = $BubPdf->GetX()+0.02;
				$BubPdf->ztly = $BubPdf->GetY()-0.07;
			}elseif($BubPdf->InlineAnswerBubbles){
				$BubPdf->ztlx = $BubPdf->GetX()-0.05;
				$BubPdf->ztly = $BubPdf->GetY()-0.07;
			}else{
				$BubPdf->ztlx = $BubPdf->GetX()+0.07;
				$BubPdf->ztly = $BubPdf->GetY()-0.07;
			}
		}else{
			$BubPdf->BadZoneStarted = TRUE;
		}

	}

}

function BP_FinishOffExamBubbleZone($BubPdf){
	global $DBG;

	if($BubPdf->ZoneArrayCapturing && !$BubPdf->TransactionInProgress){ // only need to capture zone information for the first student's answer sheet(s), because the others should be identical
	
		if($BubPdf->BadZoneStarted){
			$BubPdf->BadZoneStarted = FALSE;
		}else{
			// first finish off the zone coordinates

			if($BubPdf->MultipleMeasuresMode == TRUE){
				$BubPdf->zbry = $BubPdf->GetY() + 0.05;
				$BubPdf->zbrx = $BubPdf->GetX()+$BubPdf->ansWidth;
			}elseif($BubPdf->InlineAnswerBubbles){
				$BubPdf->zbrx = $BubPdf->GetX()+$BubPdf->ansWidth;
				$BubPdf->zbry = $BubPdf->GetY();
			}else{
				$BubPdf->zbry = $BubPdf->GetY() - 0.15;
				$BubPdf->zbrx = $BubPdf->GetX()+$BubPdf->ansWidth;
			}

			$RectWidth = $BubPdf->zbrx - $BubPdf->ztlx;
			$RectHeight = $BubPdf->zbry - $BubPdf->ztly;

			if($DBG){
				$BubPdf->SetLineStyle(array('width' => 0.01, 'dash' => "1,3", 'color' => array(0,0,255)));
				$BubPdf->Rect($BubPdf->ztlx, $BubPdf->ztly, $RectWidth, $RectHeight);
				$BubPdf->SetLineStyle(array('width' => 0.85 / $BubPdf->getScaleFactor(), 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
			}

			// stick the zone in the array, if it's not already there
			if(!isset($BubPdf->zonesArray[$BubPdf->numExamZones])){		// TBD: bug here, needs to reflect the question number for inline exams
				$BubPdf->zonesArray[$BubPdf->numExamZones]['ztlx'] = $BubPdf->ztlx;
				$BubPdf->zonesArray[$BubPdf->numExamZones]['zbrx'] = $BubPdf->zbrx;
				$BubPdf->zonesArray[$BubPdf->numExamZones]['ztly'] = $BubPdf->ztly;
				$BubPdf->zonesArray[$BubPdf->numExamZones]['zbry'] = $BubPdf->zbry;
				$BubPdf->zonesArray[$BubPdf->numExamZones]['PageNum'] = $BubPdf->MyPageNum;	
				$BubPdf->zonesArray[$BubPdf->numExamZones]['zcols'] = $BubPdf->zcols;
				$BubPdf->zonesArray[$BubPdf->numExamZones]['zrows'] = $BubPdf->zrows;
				if($BubPdf->LinkStart==1){ 		// New LinkGroup starting
					$BubPdf->zonesArray[$BubPdf->numExamZones]['LinkStart'] = 1;
					$BubPdf->zonesArray[$BubPdf->numExamZones]['LinkGroup'] = $BubPdf->LinkGroup;
				}elseif($BubPdf->LinkStart==2){ // LinkGroup continuing
					$BubPdf->zonesArray[$BubPdf->numExamZones]['LinkGroup'] = $BubPdf->LinkGroup;
				}
				$BubPdf->zrows=0;	// reset number of rows (questions) in the zone
			}
		 }
	}

}

function BP_CreateExam($BubPdf){
	global $DBG;

	
	if($BubPdf->MultipleMeasuresMode == TRUE){
		BP_PrintMultMeasBubbles($BubPdf);
		return $BubPdf->zonesArray;
	}elseif (!$BubPdf->InlineAnswerBubbles) {
		BP_PrintAnswerBubbles($BubPdf);
		return $BubPdf->zonesArray;
	}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////                             	                                                                                                               /////////
/////////                             		The following functions deal primarily with the printing of the answer bubbles                             /////////
/////////                             	                                                                                                               /////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function BP_NewExam(BubPdf $BubPdf, $CorrectAnswersProvided=FALSE)
{
	global $examtitle, $teacher, $exam_id, $grade, $subject, $instancedate;

	// StudentCode bubbles removed whenever CorrectAnswers are provided
	if($CorrectAnswersProvided) $BubPdf->CorrectAnswersProvided = TRUE;

	$BubPdf->getAliasNbPages(); //Defines an alias for the total number of pages. It will be substituted as the document is closed.
	$BubPdf->SetTopMargin($BubPdf->AreaTopMargin);
	$BubPdf->SetLeftMargin($BubPdf->AreaLeftMargin);
	$BubPdf->SetAutoPageBreak(TRUE, $BubPdf->AreaBotMargin); // bottom margin (if columns seem offset funny, this number could be to blame)

	// fill in some of the PDF meta data
	$BubPdf->SetTitle($examtitle);
	$BubPdf->SetAuthor($teacher);
	$BubPdf->SetSubject($exam_id);
	$BubPdf->SetKeywords("$grade $subject $instancedate");

}

function BP_StudentAnswerSheetStart($BubPdf){
	global $DBG;
	global $student_name;
	
	$BubPdf->AddPage();
	BP_SetCol($BubPdf, 0, TRUE);

	unset($BubPdf->PassagesPrinted);
	$BubPdf->PassagesPrinted=array();
}

function BP_StudentAnswerSheetComplete($BubPdf){
	global $DBG;
	global $student_name;

	if($BubPdf->MultipleMeasuresMode == TRUE){
		BP_PrintMultMeasBubbles($BubPdf);
	}elseif (!$BubPdf->InlineAnswerBubbles) {
		BP_PrintAnswerBubbles($BubPdf);
	}
	
	// Capture the final page number as soon as the first answer sheet is complete
	if($BubPdf->MyFinalPageNum==0) $BubPdf->MyFinalPageNum=$BubPdf->PageNo();

	// count another student's sheet set complete
	if($BubPdf->MyPageNum==$BubPdf->MyFinalPageNum){
		$BubPdf->NumStudentsProcessed++;
	}
	
	$BubPdf->ZoneArrayCapturing = FALSE;

}

function BP_AddAnswerBubbles($BubPdf, $AnswerStyle,$AnswersPossible,$NumberOfQuestions,$AlternateChoices,$FillCorrectAnswers=FALSE,$CorrectAnswers=0)
{
	global $DBG;

	// Fill the Arrays for Answer Bubble Zones
	array_push($BubPdf->BubStyles,$AnswerStyle);
	array_push($BubPdf->BubAnswersPossible,$AnswersPossible);
	array_push($BubPdf->BubNumberOfQuestions,$NumberOfQuestions);
	array_push($BubPdf->BubAlternateChoices,$AlternateChoices);
	array_push($BubPdf->BubFillCorrectAnswers,$FillCorrectAnswers);
	array_push($BubPdf->BubCorrectAnsArray,$CorrectAnswers);

	// Default to 4 columns, hopefully fitting about 80 answers per page
	// ForceOneColumn whenever some questions have more than 13 answer bubbles
	// ForceTwoColumns whenever some questions have more than 7 answer bubbles
	// ForceThreeColumns whenever some questions have more than 5 answer bubbles
	if($AnswersPossible>13){
		$BubPdf->ForceOneColumn = TRUE;
		// make the columns wider in this case
		$BubPdf->AreaColumnWidth = 7.0;
	}elseif(!$BubPdf->ForceOneColumn && $AnswersPossible>7){
		$BubPdf->ForceTwoColumns = TRUE;
		// make the columns wider in this case
		$BubPdf->AreaColumnWidth = 3.8;
	}elseif(!$BubPdf->ForceTwoColumns && $AnswersPossible>5){
		$BubPdf->ForceThreeColumns = TRUE;
		// make the columns wider in this case
		$BubPdf->AreaColumnWidth = 2.7;
	} 
	
	//figure out if we need extended bubble font or not
	if (($AnswerStyle=="1")||($AnswerStyle=="0")) {
		//if the first question, and alternating, even rows will always start on the same thing
		if ($AlternateChoices) 
			$BubPdf->TempNumericEvenStartAns=intval($AnswerStyle)+$AnswersPossible;
	
		//let's look at this block and see if any of the answer labels will use double digits
		//if any of the quetions in this block are over 9 choices, use ext font
		if ((intval($AnswersPossible)+intval($AnswerStyle)-1)>=10) 
			$BubPdf->UseExtendedBubFont = TRUE;
		elseif (($AlternateChoices)
			&&(((($BubPdf->TempQNum)%2)==0)||(intval($NumberOfQuestions)>2))
			&&((intval($AnswersPossible)+$BubPdf->TempNumericEvenStartAns-1)>=10)) {
			$BubPdf->UseExtendedBubFont = TRUE;
		} 
		
	} 
	
	//this makes the last # of this block
	$BubPdf->TempQNum=($BubPdf->TempQNum+$NumberOfQuestions); 
}

function BP_FillAnswerBubble($BubPdf, $position)
{
	global $DBG;

	//grab the width of one char
	$w = $BubPdf->GetCharWidth('0');
	
	// grab the x coordinate, etc
	$x = $BubPdf->GetX();
	$cx = $x + .105 + ($BubPdf->ansbubcellw*($position-1)); // accounts for the space
	
	// grab the y coordinate 
	if ($BubPdf->UseExtendedBubFont == TRUE) $MultY = .9; //extended bubble font seems to need extra v space
	else $MultY = 0.7;
	$y = $BubPdf->GetY();
	$cy = $y + $w*$MultY;
	
	$BubPdf->Circle($cx,$cy,.068,0,360,"F");
}

function BP_IncrementAnsChar(&$value)
{
	// this mapping corresponds with the omrextnd font, where 2 digit numbers follow the shifted keyboard numbers and shifted QWERTY...
	if($value=='!'){
		$value="@";
	}elseif($value=='9'){		
		$value=")";
	}elseif($value=='@'){
		$value="#";
	}elseif($value=='#'){
		$value="$";
	}elseif($value=='$'){
		$value="%";
	}elseif($value=='%'){
		$value="^";
	}elseif($value=='^'){
		$value="&";
	}elseif($value=='&'){
		$value="*";
	}elseif($value=='*'){
		$value="(";
	}elseif($value=='('){
		$value="Q";
	}elseif($value==')'){
		$value="!";
	}elseif($value=='Q'){
		$value="W";
	}elseif($value=='W'){
		$value="E";
	}elseif($value=='E'){
		$value="R";
	}elseif($value=='R'){
		$value="T";
	}elseif($value=='T'){
		$value="Y";
	}elseif($value=='Y'){
		$value="U";
	}elseif($value=='U'){
		$value="I";
	}elseif($value=='I'){
		$value="O";
	}elseif($value=='O'){
		$value="P";
	}else{
		$value++;
	}

}

function BP_PrintAnswerBubbles($BubPdf)
{
	global $DBG;

	// grab the first element from the arrays, then pop from the top until they're empty
	$NumberOfQuestions = array_shift($BubPdf->BubNumberOfQuestions);

	while($NumberOfQuestions){

		$AnswerStyle = strtolower( array_shift($BubPdf->BubStyles) ); // lowercase just to simlplify some of the if statements below
		$AnswersPossible = array_shift($BubPdf->BubAnswersPossible);
		$AlternateChoices = array_shift($BubPdf->BubAlternateChoices);
		$FillCorrectAnswers = array_shift($BubPdf->BubFillCorrectAnswers);
		$CorrectAnswers = array_shift($BubPdf->BubCorrectAnsArray);

		$BubPdf->zcols = $AnswersPossible;		// number of cols (ans choices) in the zone
		
		// TotalQues gets upped with each pop of the stack
		$BubPdf->TotalQues += $NumberOfQuestions;

		if($AlternateChoices) $NsdNumberingOn = TRUE; // NSD: Simple alternating rows for letters, every even numbered row starts same as #2
		else $NsdNumberingOn = FALSE;

		//Requirements
		//if( ($AnswersPossible>13)  && $AlternateChoices ) $AnswersPossible=13; // max 13 answer choices for alternating
		if( $AnswersPossible>2 && $AnswerStyle=="y" ) $AnswersPossible=2; // max 2 answer choices for Y/N
		if( $AnswersPossible>2 && $AnswerStyle=="t" ) $AnswersPossible=2; // max 2 answer choices for T/F

		/* inches per NumberOfQuestions: this is not quite accurate but is close.
			2.1   (10)
			1.92
			1.74
			1.56
			1.38
			1.20
			1.02
			0.84
			0.66  (2)
		*/
		$BubPdf->ansWidth = 0.105 + $BubPdf->ansbubcellw*($AnswersPossible);

		$PossibleAns = "";
		$PossibleAns2 = "";

		if($AnswerStyle=="y"){ 							// Yes-No answers
			$PossibleAns = "y n";						// 1 space between
			$PossibleAns2 = "y n";
		}elseif($AnswerStyle=="t"){ 					// True/False answers
			$PossibleAns = "t f";						// 1 space between
			$PossibleAns2 = "t f";
		}else{												// all others (e.g. A-B-C-D-E, 1-2-3-4)

			/* NSD: Comment out the old way, of trying not to repeat answers in adjacent rows
			// unless it's the first question, check to see if we're starting a new kindof alternating row, and in that case continuate the AnsChar
			if( (1+$BubPdf->qnum) % 2 == 1 )  $AnsChar = $AnswerStyle; // don't do for odd numbered questions, because they should start with "A" answer anyway
			elseif( $AlternateChoices && ($BubPdf->qnum!=0) && ($AnswersPossible!=$PrevAnswersPossible) ) {$AnsChar = $NewStartingAnswer;$AnsChar++;}
			else $AnsChar = $AnswerStyle;
			*/

			$AnsChar = $AnswerStyle;

			$PossibleAns .= $AnsChar;
			for($i=1;$i<$AnswersPossible;$i++){

				// omrextnd font seems to have its own builtin spacer
				//if( $BubPdf->UseExtendedBubFont ) 	
				$PossibleAns .= ' '; 		// 1 space after each bubble
				//else  						$PossibleAns .= '  '; 		// 2 spaces after each bubble

				if( $BubPdf->UseExtendedBubFont ){
					BP_IncrementAnsChar($AnsChar);
				}else{
					$AnsChar++;
				}
				$PossibleAns .= $AnsChar;
			}
			if(!isset($SavedPossibleAns) &&  $NsdNumberingOn && $AlternateChoices) $SavedPossibleAns = $AnsChar; // NSD: only save the first one, to be simple and consistent

			if($AlternateChoices){

				if($NsdNumberingOn  && $AlternateChoices) $AnsChar = $SavedPossibleAns; // NSD: to be simple and consistent
				
				if( $BubPdf->UseExtendedBubFont ){
					BP_IncrementAnsChar($AnsChar);
				}else{
					$AnsChar++;
				}

				$PossibleAns2 .= $AnsChar;
				for($i=1;$i<$AnswersPossible;$i++){

					// omrextnd font seems to have its own builtin spacer
					//if( $BubPdf->UseExtendedBubFont ) 	
					$PossibleAns2 .= ' '; 		// 1 space after each bubble
					//else  						$PossibleAns2 .= '  '; 		// 2 spaces after each bubble
					
					if( $BubPdf->UseExtendedBubFont ){
						BP_IncrementAnsChar($AnsChar);
					}else{
						$AnsChar++;
					}
					$PossibleAns2 .= $AnsChar;
					/* NSD: Comment out the old way,$SavedPossibleAns2 = $AnsChar;*/
				}
			}

		}

		if(!$BubPdf->PlainExamQuestionsMode) BP_StartNewExamBubbleZone($BubPdf);

		// NSD: Simple alternating rows, when the number is even, swap $PossibleAns and $PossibleAns2
		if(( (1+$BubPdf->qnum) % 2 == 0 ) &&  $NsdNumberingOn && $AlternateChoices ){
			$PossibleAnsTemp = $PossibleAns;
			$PossibleAns = $PossibleAns2;
			$PossibleAns2 = $PossibleAnsTemp;
		}

		$i=0; // contains the question number for this set only, to index the CorrectAnswers array
		while($BubPdf->qnum < $BubPdf->TotalQues){

			$BubPdf->qnum++;
			$BubPdf->SetFont('helvetica','',11);
			$BubPdf->Cell(.1,0,"$BubPdf->qnum. ",0,0,'R'); // the question number

			// use hall of famer omrextnd font for numerical alternating choices with greater than 9 possibilities
			if( $BubPdf->UseExtendedBubFont ) 	$BubPdf->SetFont("omrextnd", "", 11);
			else  						$BubPdf->SetFont("omrbubbles", "", 11);

			$i++;
			// Fill the correct answers if necessary, but only if was provided, and within range TBD: maybe this should somehow flag an error when the correct ans is out of range
			if($FillCorrectAnswers && $CorrectAnswers[$i] && ($CorrectAnswers[$i]<=$AnswersPossible) && ($CorrectAnswers[$i]>0) ) BP_FillAnswerBubble($BubPdf, $CorrectAnswers[$i]);

			//break up the line of answer options so we can put each bubble in a cell
			$answers=explode (" ",$PossibleAns);
			$answercount=count($answers);
			$n=0;
			foreach ($answers as $answer) {
				$n++; 
				//if it's the last one of the row, then give it a return carriage
				if ($n==$answercount) $BubPdf->Cell($BubPdf->ansbubcellw, 0, $answer,0,1);
				else $BubPdf->Cell($BubPdf->ansbubcellw, 0, $answer,0,0);
			}
			
			$BubPdf->zrows++;		// number of rows (questions) in the zone

			//blank line
			$BubPdf->SetFont('helvetica','',9); //this sets how much v space between rows
			$BubPdf->Cell($BubPdf->ansbubcellw,0," ",0,1);

			/* NSD: Comment out the old way, of trying not to repeat answers in adjacent rows
			$PrevAnswersPossible = $AnswersPossible; // save to test for when AlternateChoices depend on the previous question
			$NewStartingAnswer = $SavedPossibleAns;
			*/

			// alternating choices
			if($AlternateChoices && ($BubPdf->qnum < $BubPdf->TotalQues) ){

				$BubPdf->qnum++;
				$BubPdf->SetFont('helvetica','',11);
				$BubPdf->Cell(.1,0,"$BubPdf->qnum. ",0,0,'R'); // the question number
				// use hall of famer font for numerical alternating choices with greater than 9 possibilities
				if( $BubPdf->UseExtendedBubFont ) 	$BubPdf->SetFont("omrextnd", "", 11);
				else  						$BubPdf->SetFont("omrbubbles", "", 11);

				$i++;
				// Fill the correct answers if necessary, but only if was provided, and within range
				if($FillCorrectAnswers && $CorrectAnswers[$i] && ($CorrectAnswers[$i]<=$AnswersPossible) && ($CorrectAnswers[$i]>0) ) BP_FillAnswerBubble($BubPdf, $CorrectAnswers[$i]);

				//break up the line of answer options so we can put each bubble in a cell
				$answers=explode (" ",$PossibleAns2);
				$answercount=count($answers);
				$n=0;
				foreach ($answers as $answer) {
					$n++; 
					//if it's the last one of the row, then give it a return carriage
					if ($n==$answercount) $BubPdf->Cell($BubPdf->ansbubcellw, 0, $answer,0,1);
					else $BubPdf->Cell($BubPdf->ansbubcellw, 0, $answer,0,0);
				}
				
				$BubPdf->zrows++;		// number of rows (questions) in the zone

				//blank line
				$BubPdf->SetFont('helvetica','',9); //this sets how much v space between rows
				$BubPdf->Cell($BubPdf->ansbubcellw,0," ",0,1);

				/* NSD: Comment out the old way, of trying not to repeat answers in adjacent rows
				$PrevAnswersPossible = $AnswersPossible; // save to test for when AlternateChoices depend on the previous question
				$NewStartingAnswer = $SavedPossibleAns2;
				*/

			}

		}

		if(!$BubPdf->PlainExamQuestionsMode) BP_FinishOffExamBubbleZone($BubPdf);

		// grab the next set of data, or null to exit the loop
		$NumberOfQuestions = array_shift($BubPdf->BubNumberOfQuestions);
	}

	// empty out the question number holders for the next student (when the stack is empty)
	$BubPdf->TotalQues = 0;
	$BubPdf->qnum = 0;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////                             	                                                                                                               /////////
/////////	                             	The following functions deal primarily with Multiple Measures exams                                        /////////
/////////                             	                                                                                                               /////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function BP_AddMultMeasAnswerBubbles($points, $MultMeasName, $MultMeasPointStart)
{
	global $BubPdf;
	global $DBG; 

	//any one usage of AddMultMeasAnswerBubbles will force the code into this mode
	$BubPdf->MultipleMeasuresMode = TRUE;

	// Fill the Arrays for Answer Bubble Zones
	array_push($BubPdf->BubMmPoints,$points);
	array_push($BubPdf->BubMmName,$MultMeasName);
	array_push($BubPdf->BubMmPointStart,$MultMeasPointStart);

	$BubPdf->ForceTwoColumns = TRUE;
	// make the columns wider in this case
	$BubPdf->AreaColumnWidth = 3.8;
	

}

function BP_PrintMultMeasBubbles($BubPdf)
{
	global $DBG; 

	// grab the first element from the arrays, then pop from the top until they're empty
	$MultMeasName = array_shift($BubPdf->BubMmName);

	while($MultMeasName){

		$MultMeasPointStart = array_shift($BubPdf->BubMmPointStart); 
		$points = array_shift($BubPdf->BubMmPoints);
		
		$BubPdf->zcols = 10;		// number of cols (ans choices) in the zone
		
		// TotalQues gets upped with each pop of the stack
		$BubPdf->TotalQues += 1;

		while($BubPdf->qnum < $BubPdf->TotalQues){	
		
			//see how much space is left before the bottom margin
			$AreaRemaining=11-$BubPdf->GetY()-$BubPdf->AreaBotMargin;
			// don't start MM too far down the column.
			if( ($AreaRemaining < 1.35) && ($AreaRemaining > .01) && !($BubPdf->qnum==0) ) {
				/*DEBUG*/ if ($DBG) $BubPdf->Cell(0.3,0,"PBK_MM".$AreaRemaining,0,1);
				if ($BubPdf->col==1) {
					BP_SetCol($BubPdf, 0);
					$BubPdf->AddPage();
				} else {
					BP_SetCol($BubPdf, $BubPdf->col+1);
				}
			}
			
			$BubPdf->qnum++;
			$BubPdf->SetFont('helvetica','',11);
			$BubPdf->Cell(.01,.27,"$BubPdf->qnum.",0,0,"R"); 					// the question number right justified
			$BubPdf->SetFont('helvetica','B',11);
			$BubPdf->MultiCell(3.2,0,"$MultMeasName ",0,1); 				// the multiple measure name in bold (up to 3.2 inches wide)
			$BubPdf->SetFont('helvetica','',8);
			//$BubPdf->SetTextColor(200); //grey
			$BubPdf->SetTextColor(0); //grey
			$BubPdf->Cell(2,0,"(point range: $MultMeasPointStart-$points)",0,1); 	// the possible points (up to 2 inches wide)
			
			//blank line
			$BubPdf->SetFont('helvetica','',11);
			$BubPdf->SetTextColor(0, 0, 0); // black
			$BubPdf->Cell(.3,0," ",0,1);

			if(!$BubPdf->PlainExamQuestionsMode) BP_StartNewExamBubbleZone($BubPdf);

			$BubPdf->SetFont("omrbubbles", "", 11);

			//figure out the most significant digit 
			$LeftDigit = intval(substr(strval($points),0,1));

			//the shortened line starts with that digit and ends with zero
			$ShortLineRight = "";
			for($i=$LeftDigit;$i>0;$i--){
				$ShortLineRight .= strval($i) . " ";
			}
			$ShortLineRight .= "0";

			//the bubbles starts with 9 and ends before digit
			$ShortLineLeft = "";
			for($i=9;$i>$LeftDigit;$i--){
				$ShortLineLeft .= "-" . " "; // TBD: use a dash, unless we can find a blank bubble... (the blank bubble is ctrl-alt-shift-! in MsWord)
			}

			$maxcellMult=1;
			$bubblecellh=.2;

			// ShortLineLeft
			$x = $BubPdf->GetX(); // grab the x coordinate
			$y = $BubPdf->GetY(); // grab the y coordinate
			$BubPdf->SetX($x+0.087); // adjust the x coordinate
			$BubPdf->SetTextColor(0, 0, 0); // grey
			$BubPdf->Cell(2, $bubblecellh, $ShortLineLeft);
			$BubPdf->SetX($x); // reset the x coordinate
			$BubPdf->SetY($y); // reset the y coordinate

			$BubPdf->SetTextColor(0, 0, 0);
			$BubPdf->Cell(2, $bubblecellh, $ShortLineRight,0,0,"R");		// short line right justified
			$BubPdf->zrows++;		// number of rows (questions) in the zone
			$x = $BubPdf->GetX(); // grab the x coordinate
			$y = $BubPdf->GetY(); // grab the y coordinate
			$BubPdf->Cell(.01, $bubblecellh, " ",0,1); //this cell is just so we can set the xy for the rotated text
			$prevx = $BubPdf->GetX(); // grab the x coordinate
			if($points>9) {
				$BubPdf->Cell(2, $bubblecellh, "9 8 7 6 5 4 3 2 1 0",0,1,"R");
				$maxcellMult=2;
				$BubPdf->zrows++;		// number of rows (questions) in the zone
			}
			if($points>99) {
				$BubPdf->Cell(2, $bubblecellh, "9 8 7 6 5 4 3 2 1 0",0,1,"R");
				$maxcellMult=3;
				$BubPdf->zrows++;		// number of rows (questions) in the zone
			}
			if($points>999) {
				$BubPdf->Cell(2, $bubblecellh, "9 8 7 6 5 4 3 2 1 0",0,1,"R");
				$maxcellMult=4;
				$BubPdf->zrows++;		// number of rows (questions) in the zone
			}

			$finalx = $BubPdf->GetX(); // grab the x coordinate
			$finaly = $BubPdf->GetY(); // grab the y coordinate
			
			//-------------------------------------------------
			//rotated text
			$BubPdf->SetLeftMargin($x);
			$BubPdf->SetXY($x, $y-.4); //minus more from Y to set further away from bubbles
			$BubPdf->SetFont("Helvetica", "", 7);
			$BubPdf->SetTextColor(150);
			//Start Transformation
			$BubPdf->StartTransform();
			//Rotate 90 degrees counter-clockwise 
			$BubPdf->Rotate(-90,$x, $y);
			//$BubPdf->Cell($maxcellMult*$bubblecellh,0,'Min:',0,1);
			//$BubPdf->Cell(($maxcellMult*$bubblecellh)-$bubblecellh+.01,0,' ',0,0);
			//$BubPdf->Cell($bubblecellh-.01,0,$MultMeasPointStart,0,1,"C");
			$BubPdf->Cell($maxcellMult*$bubblecellh, 0, ' Max:',0,1);
			$numbers=str_split($points);
			foreach ($numbers as $number) {
				$BubPdf->Cell($bubblecellh,0,$number,0,0,"C");
			}
			//Stop Transformation
			$BubPdf->StopTransform(); 
			//-------------------------------------------------
			
			$BubPdf->SetLeftMargin($prevx);
			$BubPdf->SetXY($finalx, $finaly);
			$BubPdf->ansWidth = 2.05; // zone width for multiple measures

			if(!$BubPdf->PlainExamQuestionsMode) BP_FinishOffExamBubbleZone($BubPdf);

			$BubPdf->SetFont('helvetica','',11);
			$BubPdf->SetTextColor(0);
				
			//see how much space is left before the bottom margin
			$AreaRemaining=11-$BubPdf->GetY()-$BubPdf->AreaBotMargin;
			
			//blank line (only if not at very bottom of column to keep it from putting this at top of next col)
			$spacerheight=.3;
			if ($AreaRemaining>$spacerheight) $BubPdf->Cell(.1,$spacerheight,"",0,1);
		}


		// grab the next set of data, or null to exit the loop
		$MultMeasName = array_shift($BubPdf->BubMmName);
	}

	// empty out the question number holders for the next student (when the stack is empty)
	$BubPdf->TotalQues = 0;
	$BubPdf->qnum = 0;

}

?>
