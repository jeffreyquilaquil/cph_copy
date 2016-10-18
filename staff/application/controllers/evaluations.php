<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* 		
*/
class Evaluations extends MY_Controller	
{
	var $widths;
	var $aligns;
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('evaluationsmodel');
		$this->load->helper('form');

	}

	public function index(){
		
		$data['content'] = 'evaluations/index';
		$data['tpage'] = 'evaluations';
		$data['column'] = 'withLeft';

		$this->load->view('includes/template', $data);
	}

	public function questionnaires($type=null, $careerType = 0){
		if(is_numeric($type)){
			$careerType = $type;
			$type = 'technicalQuestions';
		}

		$data['content'] = 'evaluations'.($type==null ? '/questionnaires' : '/'.$type);
		$data['tpage'] = 'evaluations';
		$data['column'] = 'withLeft';
		$data['questions'] = $this->evaluationsmodel->getQuestions($type, $careerType);
		$data['positions'] = $this->evaluationsmodel->getPositions();
		
		$this->load->view('includes/template', $data);
	}

	public function addQuestions(){

		$type = $this->input->post('questionType');

		$data[1]= array();

		if($type == 'technical'){
			$question_type = 1;
			$job_type = $this->input->post('posID');
			$details_array = array(
				'expectation' => htmlentities($this->input->post('txtExpectation')),
				'evaluator' => htmlentities($this->input->post('txtFormat')),
				'weight' => htmlentities($this->input->post('txtWeight')),
				'question' => htmlentities($this->input->post('txtQuestion')),
				);

			array_push($data[1], $details_array);
		}

		if($type == 'behavioral'){

			$question_type = 2;
			$job_type = 0;

			$expectation = explode('__', $this->input->post('txtExpectation'));
			$question = explode('__', $this->input->post('txtQuestion'));
			$evaluator = explode(',', $this->input->post('txtEvaluator'));
			$weight = explode(',', $this->input->post('txtWeight'));
			$nop = explode(',', $this->input->post('txtNop'));

			$expectation_count = count($expectation);
			$question_count = count($question);
			$evaluator_count = count($evaluator);

			for($i=0;$i<$evaluator_count;$i++){
				// if index($i) is less than expectation count, then
				// fetch the indexed value from the expectation array, else
				// then set the expectation value from the last value of the array to prevent index offset
				$expectation_val = ($i < $expectation_count ? $expectation[$i] : $expectation[$expectation_count - 1]);
				$question_val = ($i < $question_count ? $question[$i] : $question[$question_count - 1]);

				$details_array = array(
					'expectation' => htmlentities($expectation_val),
					'evaluator' => htmlentities($evaluator[$i]),
					'question' => htmlentities($question_val),
					'weight' => $weight[$i],
					'nop' => $nop[$i],
				);
				array_push($data[1], $details_array);
			}
		}

		$data[0] = array(
			'question_type' => $question_type,
			'job_type' => $job_type,
			'goals' => htmlentities($this->input->post('txtObjective')),
			'created' => date('Y-m-d'),
			);

	$result = $this->evaluationsmodel->saveQuestion($data);
	print json_encode($result);
	}

	public function updateQuestions(){
		$type = $this->input->post('questionType');
		$data[0] = array(
				'goals' => htmlentities($this->input->post('txtObjective')),
				'question_id' =>  $this->input->post('questionId'),
				'updated' => date('Y-m-d'),
			);

		$data[1] = array();
		if($type == 'technical'){
			$details_array = array(
				'expectation' => htmlentities($this->input->post('txtExpectation')),
				'evaluator' => htmlentities($this->input->post('txtFormat')),
				'question' => htmlentities($this->input->post('txtQuestion')),
				'weight' => $this->input->post('txtWeight'),
				'detail_id' => $this->input->post('detailsId')
				);
			array_push($data[1], $details_array);
		}

		if($type == 'behavioral'){

			$expectation = explode('__', $this->input->post('txtExpectation'));
			$question = explode('__', $this->input->post('txtQuestion'));
			$evaluator = explode(',', $this->input->post('txtEvaluator'));
			$weight = explode(',', $this->input->post('txtWeight'));
			$nop = explode(',', $this->input->post('txtNop'));
			$details_id = explode(',', $this->input->post('detailsId'));

			$evaluator_count = count($evaluator);
			$expectation_count = count($expectation);
			$question_count = count($question);
			// Refer the same function from above
			for($i=0; $i<$evaluator_count;$i++){
				$expectation_val = ($i < $expectation_count ? $expectation[$i] : $expectation[$expectation_count - 1]);
				$question_val = ($i < $question_count ? $question[$i] : $question[$question_count - 1]);

				// Push by chunks so that evaluations model will just loop and save .
				$details_array = array(
					'expectation' => htmlentities($expectation_val),
					'question' => htmlentities($question_val),
					'evaluator' => htmlentities($evaluator[$i]),
					'weight' => $weight[$i],
					'detail_id' => $details_id[$i],
					'nop' => $nop[$i],
					);
				array_push($data[1], $details_array);
			}
		}

		$result = $this->evaluationsmodel->updateQuestion($data);
	#	dd($data);
		print json_encode($result);
	}

	public function saveEvaluation(){
		$data = $this->input->POST('data');
		$rows = [];
		for($i=0;$i < count($data['technical']['detailIdArr']);$i++){
			$dataArray = [
				'detail_id'=>$data['technical']['detailIdArr'][$i],
				'score' => $data['technical']['wtScoreArr'][$i],
				'remarks' => $data['technical']['remarksArr'][$i],
				'question_id' => $data['technical']['questionIdArr'][$i],
				'emp_id' => $data['empId'],
				'staff_type' => $data['staffType'],
			];
			array_push($rows, $dataArray);
		}

		for($i=0; $i < count($data['behavioral']['detailIdArr']);$i++){
			$dataArray = [
				'detail_id'=>$data['behavioral']['detailIdArr'][$i],
				'score' => $data['behavioral']['wtScoreArr'][$i],
				'remarks' => $data['behavioral']['remarksArr'][$i],
				'question_id' => $data['behavioral']['questionIdArr'][$i],
				'emp_id' => $data['empId'],
				'staff_type' => $data['staffType'],
			];
			array_push($rows, $dataArray);
		}
		// the last parameter determines on what type the user is. 
		// If TL or rank and file

		// Pass data to save.
		// The first param is for the questions.
		// The second is for the performance evaluation
		$info2  = [
			'empId' => $data['empId'],
			'evaluator' => $data['evaluator'],
			'staffType' => $data['staffType'],
		];
		$this->evaluationsmodel->savePerformanceEvaluation($rows, $info2);

		 if($data['staffType'] == 2){
		 	$this->sendEvaluationEmail(1, $data['empId'], $data['evaluator']);
		 	#echo $data['staffType'];
		 }

		 if($data['staffType'] == 1){
		 	$this->evalPDF($data['empId'], $data['evaluator'], 2);
		 }
	}

	function saveEvaluationDate(){
		$evalDate = date('Y-m-d', strtotime($this->input->post('evalDate')));
		$data = [
			'evalDate' => $evalDate,
			'empId' => $this->input->post('empID'),
			'evaluatorId' => $this->input->post('evaluator'),
		];

		if($evalDate == date('Y-m-d')){
		#	$data['ansDate'] = date('Y-m-d');
			$this->sendEvaluationEmail(2, $data['empId'], $data['evaluatorId']);
		}
		$this->evaluationsmodel->saveEvaluationDate($data);
	}

// This function should be in myCron
	function sendEvaluationEmail($userType = 2, $empId = 538, $evaluator = 178){

		// User type 2 is Rank and File Employee
		// 1 is for the Supervisor
		$this->load->model('emailmodel');

		$subject = "90th day Performance Evaluation ";

		$fields = "concat(fname,' ',lname) as 'name', email";
		$info = $this->databasemodel->getSingleInfo('staffs', $fields,'empID = '.$empId);
		$to = $info->email;

		if($userType == 1){
			$info2 = $this->databasemodel->getSingleInfo('staffs', 'email', 'empID = '.$evaluator);
			$subject .= "for ".$info->name;
			$body = "Hi. Please give your Performance Evaluation for ".$info->name.". Just click the link below.";
			$to = $info2->email;
		}else{
			$body = "Hi ".$info->name.". This is your 90th day. Please click the link below to take your Self Performance Evaluation.";
		}

		$body .= "<br><a href='".$this->config->base_url()."performanceeval/".$userType."/".$empId."/".$evaluator."'>Click here</a>";
		
		$from = "CPH Evaluation";
		
		$to = 'jeffrey.quilaquil@tatepublishing.net';
		$this->emailmodel->sendEmail('careers.cebu@tatepublishing.net', $to, $subject, $body, 'CareerPH Auto-Emails' );
	}

	public function generateEvaluation($empID){
		$data['info'] = $this->databasemodel->getSingleInfo('staffs','fname, lname, dept, supervisor','empId = '.$empID, "left join newPositions on posID = position");
		$data['evaluator'] = $this->databasemodel->getQueryResults('staffs','empID, fname, lname, title',"dept = '".$data['info']->dept."' AND orgLevel_fk > 0","left join newPositions on posID = position");
		$data['empID'] = $empID;
		$data['content'] = 'evaluations/generateEvaluation';
		$this->load->view('includes/templatecolorbox', $data);
	}

	public function performanceEvaluationDetails(){

	}

	public function evalPDF($empID = 538, $evalID = 178, $staffType = 2){
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
		require_once('wordWrap.php');
		$pdf = new FPDI('P');

		$evaluationTitle = ['TECHNICAL GOALS AND OBJECTIVES', 'BEHAVIORAL GOALS AND OBJECTIVES'];
		
		$info = $this->databasemodel->getSingleInfo('staffs','concat(fname," ",lname) as name, title, startDate, DATE_ADD(startDate, INTERVAL 3 MONTH) as ninetiethDay, position, supervisor', 'empID = '.$empID, 'LEFT JOIN newPositions np on np.posID = position');
		$evaluatorInfo = $this->databasemodel->getSingleInfo('staffs', 'concat(fname," ",lname) as name, title', 'empID = '.$evalID.' LEFT JOIN newPositions np on np.posID = position');

		foreach($evaluationTitle as $eachTitle){		

			if($eachTitle == "TECHNICAL GOALS AND OBJECTIVES"){
				$questionType = 1;
				$job_type = $info->position;
			}else{
				$questionType = 2;
				$job_type = 0;
			}

			$questions = $this->evaluationsmodel->getEvaluationScore($questionType, $job_type, $empID, $staffType);
		#	dd($questions);
			$pdf->AddPage();
			// Header
			$pdf->setTextColor(0, 0, 0);
			$pdf->SetFont('Arial','B',15);
			$pdf->setXY(102, 0);
			$pdf->cell(35,30,'PERFORMANCE EVALUATION FORM');

			$pdf->SetFont('Arial','',9);
			$pdf->setXY(132,10);
			$pdf->cell(40,20,'Employee Self-Rating');

			// Start of Employee Information
			$pdf->SetFont('Arial','',9);
			$pdf->setTextColor(255, 255, 255);
			$pdf->SetFillColor(128, 0, 0);
			$pdf->setXY(10,25);
			$pdf->cell(192, 5, "Employee Information ",1,'','C', true);

			$pdf->setTextColor(0,0,0);
			$pdf->SetFillColor(204, 255, 255);

			// For the bold fields
			$pdf->SetFont('Arial','B',8);
			$y = 30;
			$textArr = ['Name of Employee', 'Immediate Supervisor', 'Position Title', 'Evaluator', 'Hire Date', "Evaluator's Position",'90th Day', 'Evaluation Date'];
			foreach($textArr as $value){
				if(array_search( $value, $textArr) % 2 == 0){
					$pdf->setXY(10, $y);
					
				}else{
					$pdf->setXY(106	, $y);
					$y+=5;
				}
				
				$pdf->cell(35,5, $value.':' ,1,'','R',true);
			}



			if(!isset($supervisorId)){
				$supervisorId = $info->supervisor;
				$posID = $info->position;
				unset($info->supervisor);
				unset($info->position);
			}

			$pdf->SetFont('Arial','',8);

			$y = 30;
			foreach ($info as $value) {
				$pdf->setXY(45,$y);
				$pdf->cell(61,5, $value,1,'','L',true);
				$y += 5;
			}

			if($staffType == 1){
				$evaluator = $evaluatorInfo->name;
				$evaluatorTitle = $evaluatorInfo->title;
			}else{
				$evaluator = $info->name;
				$evaluatorTitle = $info->title;
			}

			$textArr = [$evaluatorInfo->name, $evaluator, $evaluatorTitle, date('Y-m-d')];
			$y = 30;
			foreach ($textArr as $value) {
				$pdf->setXY(141,$y);
				$pdf->cell(61,5, $value,1,'','L',true);
				$y += 5;
			}

			$pdf->setTextColor(255, 255, 255);
			$pdf->SetFillColor(128, 0, 0);
			$pdf->setXY(10,55);
			$pdf->cell(192, 5, $eachTitle,1,'','C', true);

			$pdf->setXY(10,60);
			$pdf->SetFont('Arial','',8);
			$pdf->cell(192, 5, "RATINGS: 0=Did not Meet Expectations  1=Meets Expectations  2=Exceeds Expectations",1,'','C',true);

			// Question Headers
			$pdf->setXY(10,65);
			$pdf->cell(5,10,"#",1,'','C',true);

		 	$pdf->setXY(15,65);
		 	$pdf->cell(30,10,"Objective Goals",1,'','C',true);

		 	$pdf->setXY(45,65);
		 	$pdf->cell(30,10,"Expectation",1,'','C',true);

		 	$pdf->setXY(75,65);
		 	$text = ($eachTitle == "TECHNICAL GOALS AND OBJECTIVES" ? "Output Format" : "Evaluator");
		 	$pdf->cell(30,10,$text,1,'','C',true);

		 	$pdf->setXY(105,65);
		 	$pdf->cell(35,10,"Evaluation Question",1,'','C',true);

			$pdf->setXY(140,65);
		 	$pdf->cell(7,10,"Wt.",1,'','C',true);

		 	$pdf->setXY(147,65);
		 	$pdf->cell(35,10,"Employee's Remarks",1,"",'C',true);

		 	$pdf->setXY(182,65);
		 	$pdf->MultiCell(10,5,"Emp Rtg",1,'C',true);

		 	$pdf->setXY(192,65);
		 	$pdf->MultiCell(10,5,"Wtd. Score",1,'C',true);

		 	// For the real questions
		 	$i = 1;
		 	$y = $pdf->getY();
		 	$x = $pdf->getX(); 

		 	echo "overall";dd($questions);
		 	foreach($questions as $question){
		 		
				$fpdfCell = [];
				$ttlScore = 0;

   				$pdf->setTextColor(0, 0, 0);
				$pdf->SetFillColor(204, 255, 255);

	#		 	$pdf->SetXY(105,$y);
	#			$pdf->MultiCell(35,5,html_entity_decode($question->question),1,'C',true);

			 	$H = $pdf->GetY();
			 	$height= $H-$y;

			 	$pdf->SetXY(45, $y);
			 	$pdf->setTextColor(0, 0, 0);
					
				if(count($question->details) > 1){
					

					$heightDiff = $height / count($question->details);
					#die($heightDiff);
					foreach($question->details as $detailRow){
						$pdf->SetXY(105, ($y + $heightDiff),'DF');
						$pdf->MultiCell(35,5,html_entity_decode($details->question),1,'C',true);

						$pdf->SetXY(45, ($y + $heightDiff));
						$pdf->Rect(45,$y, 30, $heightDiff,'DF');
						$pdf->MultiCell(30,5,$detailRow->expectation,'','C');

						$ttlScore += $detailRow->score;
					}
				}else{
					// $cell[0]=[X,Y,height,width,text];

					array_push($fpdfCell, array(45, $y, $height, 30, $question->details[0]->expectation));
					array_push($fpdfCell, array(75, $y, $height, 30, $question->details[0]->evaluator));
					array_push($fpdfCell, array(140, $y, $height, 7, $question->details[0]->weight));
					array_push($fpdfCell, array(147, $y, $height, 35, $question->details[0]->remarks));
					array_push($fpdfCell, array(182, $y, $height, 10, ($question->details[0]->score / $question->details[0]->weight)));
					array_push($fpdfCell, array(192, $y, $height, 10, $question->details[0]->score));
					$ttlScore += $question->details[0]->score;
				}

				// foreach($fpdfCell as $cell){
				// 	$pdf->SetXY($cell[0],$cell[1]);
				// 	$pdf->Rect($cell[0],$cell[1], $cell[3], $cell[2], 'DF');
				// 	$pdf->MultiCell($cell[3], 5, $cell[4],'','C');
				// }

			  	$pdf->SetXY(15,$y);
	 	  	 	$pdf->MultiCell(30,$height,$question->goals,1,'C',true);

			// Numbering
			 	$pdf->SetXY(10,$y);
   				$pdf->setTextColor(255, 255, 255);
			 	$pdf->SetFillColor(128, 0, 0);
			 	$pdf->cell(5,$height,$i,1,"",'C',true);
			 	$i++;
			 	$y=$H;

		 	  // set page constants
				$page_height = 279.4; // mm (portrait letter)
				$bottom_margin = 20; // mm

				// mm until end of page (less bottom margin of 20mm)
				$space_left = $page_height - $pdf->GetY(); // space left on page
				$space_left -= $bottom_margin; // less the bottom margin

				// test if height of cell is greater than space left
				if ( $height >= $space_left) {                    

				   	$pdf->AddPage(); // page break.
				    $pdf->Cell(100,5,'','B',2); // this creates a blank row for formatting reasons
				}			
			#	var_dump($question);
			}


		 	$pdf->SetXY(147,$y);
		 	$pdf->cell(45,5,"Employee's Rating",1,'','C',true);
		 	$pdf->cell(10,5,$ttlScore."%",1,'','C',true);

		 	$pdf->SetXY(147,($y+5));
		 	$pdf->cell(45,5,"Employee's 20% Weighted Rating",1,'','C',true);
		 	$pdf->cell(10,5,($ttlScore * 0.2)."%",1,'','C',true);
		 #	dd($question,false);
		}
		$pdf->Output();
	}
}