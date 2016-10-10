<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* 		
*/
class Evaluations extends MY_Controller	
{
	
	
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
				);

			array_push($data[1], $details_array);
		}

		if($type == 'behavioral'){

			$question_type = 2;
			$job_type = 0;

			$expectation = explode('__', $this->input->post('txtExpectation'));
			$evaluator = explode(',', $this->input->post('txtEvaluator'));
			$weight = explode(',', $this->input->post('txtWeight'));

			$expectation_count = count($expectation);
			$evaluator_count = count($evaluator);

			for($i=0;$i<$evaluator_count;$i++){
				// if index($i) is less than expectation count, then
				// fetch the indexed value from the expectation array, else
				// then set the expectation value from the last value of the array to prevent index offset
				$expectation_val = ($i < $expectation_count ? $expectation[$i] : $expectation[$expectation_count - 1]);

				$details_array = array(
					'expectation' => htmlentities($expectation_val),
					'evaluator' => htmlentities($evaluator[$i]),
					'weight' => $weight[$i],
				);
				array_push($data[1], $details_array);
			}
		}

		$data[0] = array(
			'question_type' => $question_type,
			'job_type' => $job_type,
			'goals' => htmlentities($this->input->post('txtObjective')),
			'question' => htmlentities($this->input->post('txtEvaluation')),
			'created' => date('Y-m-d'),
			);

	$result = $this->evaluationsmodel->saveQuestion($data);
	print json_encode($result);
	}

	public function updateQuestions(){
		$type = $this->input->post('questionType');
		$data[0] = array(
				'goals' => htmlentities($this->input->post('txtObjective')),
				'question' => htmlentities($this->input->post('txtEvaluation')),
				'question_id' =>  $this->input->post('questionId'),
				'updated' => date('Y-m-d')
			);

		$data[1] = array();
		if($type == 'technical'){
			$details_array = array(
				'expectation' => htmlentities($this->input->post('txtExpectation')),
				'evaluator' => htmlentities($this->input->post('txtFormat')),
				'weight' => $this->input->post('txtWeight'),
				'detail_id' => $this->input->post('detailsId')
				);
			array_push($data[1], $details_array);
		}

		if($type == 'behavioral'){

			$expectation = explode('__', $this->input->post('txtExpectation'));
			$evaluator = explode(',', $this->input->post('txtEvaluator'));
			$weight = explode(',', $this->input->post('txtWeight'));
			$details_id = explode(',', $this->input->post('detailsId'));

			$evaluator_count = count($evaluator);
			$expectation_count = count($expectation);
			// Refer the same function from above
			for($i=0; $i<$evaluator_count;$i++){
				$expectation_val = ($i < $expectation_count ? $expectation[$i] : $expectation[$expectation_count - 1]);

				// Push by chunks so that evaluations model will just loop and save .
				$details_array = array(
					'expectation' => htmlentities($expectation_val),
					'evaluator' => htmlentities($evaluator[$i]),
					'weight' => $weight[$i],
					'detail_id' => $details_id[$i]
					);
				array_push($data[1], $details_array);
			}
		}

		$result = $this->evaluationsmodel->updateQuestion($data);
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
				'timestamp' => date('Y-m-d H:i:s'),
			];
			array_push($rows, $dataArray);
		}
		// the last parameter determines on what type the user is. 
		// If TL or rank and file
		#$this->evaluationsmodel->savePerformanceEvaluation($rows);
		dd($data, false);
		 if($data['staffType'] == 2){
		 	$this->sendEvaluationEmail(1, $data['empId']);
		 	#echo $data['staffType'];
		 }

		 if($data['staffType'] == 1){
		 	$this->evalPDF($data['empId']);
		 }
	}

// This function should be in myCron
	function sendEvaluationEmail($userType = 2, $empId = 538){
		// User type 2 is Rank and File Employee
		// 1 is for the Supervisor
		$this->load->model('emailmodel');

		$subject = "90th day Performance Evaluation ";

		$fields = "concat(fname,' ',lname) as 'name', email, supervisor";
		$info = $this->databasemodel->getSingleInfo('staffs', $fields,'empID = '.$empId);
		$to = $info->email;

		if($userType == 1){
			$info2 = $this->databasemodel->getSingleInfo('staffs', 'email', 'empID = '.$info->supervisor);
			$subject .= "for ".$info->name;
			$body = "Hi. Please give your Performance Evaluation for ".$info->name.". Just click the link below.";
			$to = $info2->email;
		}else{
			$body = "Hi ".$info->name.". This is your 90th day. Please click the link below to take your Self Performance Evaluation.";
		}

		$body .= "<br><a href='".$this->config->base_url()."/performanceeval/".$userType."/".$empId."'>Click here</a>";
		
		$from = "CPH Evaluation";
		
		$to = 'jeffrey.quilaquil@tatepublishing.net';
	#	$this->emailmodel->sendEmail('careers.cebu@tatepublishing.net', $to, $subject, $body, 'CareerPH Auto-Emails' );
	}

	public function evalPDF(){
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');

		$empID = $this->uri->segment(1);
		$empID = 538;

		$pdf = new FPDI('P');
		$pdf->AddPage();
#		$pdf->setSourceFile(PDFTEMPLATES_DIR, 'Evaluation.pdf');

#		$tplIdx = $pdf->importPage(1);
#		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);

		
	#	$pdf->setTextColor(0, 0, 0);

#		$pdf->setXY(47, 38.7);
#		$pdf->Write(0, date('l, F d, Y', strtotime($row->dateissued)));	

		// Header
		$pdf->SetFont('Arial','B',16);
		$pdf->setXY(90, 0);
		$pdf->cell(35,30,'PERFORMANCE EVALUATION FORM');

		$pdf->SetFont('Arial','',9);
		$pdf->setXY(135,10);
		$pdf->cell(40,20,'Employee Self-Rating');

		// Start of Technical
		$pdf->SetFont('Arial','',9);
		$pdf->setTextColor(255, 255, 255);
		$pdf->SetFillColor(128, 0, 0);
		$pdf->setXY(15,25);
		$pdf->cell(180, 5, "Employee Information ",1,'','C', true);

		$pdf->setTextColor(0,0,0);
		$pdf->SetFillColor(204, 255, 255);

		// For the bold fields
		$pdf->SetFont('Arial','B',8);
		$pdf->setXY(15,30);
		$pdf->cell(35,5, 'Name of Employee: ',1,'','R',true);
		$pdf->setXY(110,30);
		$pdf->cell(35,5, 'Immediate Supervisor: ',1,'','R',true);

		$pdf->setXY(15,35);
		$pdf->cell(35,5, 'Position Title: ',1,'','R',true);
		$pdf->setXY(110,35);
		$pdf->cell(35,5, 'Evaluator: ',1,'','R',true);

		$pdf->setXY(15,40);
		$pdf->cell(35,5, 'Hire Date: ',1,'','R',true);
		$pdf->setXY(110,40);
		$pdf->cell(35,5, "Evaluator's Position: ",1,'','R',true);

		$pdf->setXY(15,45);
		$pdf->cell(35,5, '90th Day: ',1,'','R',true);
		$pdf->setXY(110,45);
		$pdf->cell(35,5, 'Evaluation Date: ',1,'','R',true);

		$info = $this->databasemodel->getSingleInfo('staffs','concat(fname," ",lname) as name, title, startDate, supervisor', 'empID = '.$empID, 'LEFT JOIN newPositions np on np.posID = position');
		#$evaluatorInfo = $this->databasemodel->getSingleInfo('staffs', 'concat(fname," ",lname) as name, np.title', 'empID = '.$info->supervisor,'LEFT JOIN newPositions np on np.posID = position');


		$pdf->SetFont('Arial','',8);

		$pdf->setXY(50,30);
		$pdf->cell(60,5, 'Name of Employee',1,'','L',true);
		$ninethDay = date('d-m-Y',strtotime($info->startDate) + strtotime("3 months"));

		$textArr = [$info->name, $info->title, $info->startDate, $ninethDay];

		$y = 30;
		foreach ($textArr as $value) {
			$pdf->setXY(50,$y);
			$pdf->cell(60,5, $value,1,'','L',true);
			$y += 5;
		}

		$pdf->Output();
	}
}