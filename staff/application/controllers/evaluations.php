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
		$this->load->model('emailmodel');
		$this->load->model('commonmodel');
		$this->load->helper('form');
	}

	public function index(){
		$data['content'] = 'evaluations/index';
		$data['access'] = ($this->user->levelID_fk > 0 || $this->access->accessFullHR == true ? true : false);
		$data['tabs'] = ['Pending Self-Rating','In Progress','Pending HR','Done','Cancelled'];
		$data['evaluations'] = $this->evaluationsmodel->getStaffPerformanceEvaluation($this->user->empID, $this->user->dept, $this->user->is_supervisor);
		$data['evaluations']['headers'] = ['Evaluation ID',"Employee's Name",'Date Generated','Evaluation Date','Immediate Supervisor', 'Status', 'Actions'];
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
		$data['access'] = ($this->user->levelID_fk > 0 || $this->access->accessFullHR ? true : false);
		$data['tpage'] = 'evaluations';
		$data['column'] = 'withLeft';
		$data['questions'] = $this->evaluationsmodel->getQuestions($type, $careerType);
		$data['positions'] = $this->evaluationsmodel->getPositions();
		//dd($data['positions']);
		$this->load->view('includes/template', $data);
	}

	public function review($jobType = 0){
		$data['content'] = 'evaluations/reviewQuestions';
		$data['tpage'] = 'evaluations';
		$data['column'] = 'withLeft';
	
		if(!empty($_POST)){

			$this->databasemodel->updateQueryText('evalQuestions', 'hrStatus = 1', 'question_id = '.$_POST['questionId'].' LIMIT 1');
		}

		$data['jobDesc'] = $this->databasemodel->getSQLQueryResults('SELECT count(DISTINCT question_id) as "rowCount", title, job_type FROM evalQuestions LEFT JOIN newPositions ON posID = job_type WHERE hrStatus = 0 and question_type = 1 GROUP BY job_type ORDER BY title');

		if($jobType == 0 && !empty($data['jobDesc'])){
			$jobType = $data['jobDesc'][0]->job_type;
		}
		$data['question'] = $this->evaluationsmodel->getReviewQuestions($jobType);
		$data['question']['headers'] = ['ID','Objective Goals', 'Expectation', 'Output Format', 'Evaluation Question', 'Wt.', 'Uploaded by','Action'];
		$this->load->view('includes/template',$data);
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
			'hrStatus' => 0,
			'uploader' => $this->user->empID,
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
		print json_encode($result);
	}

	public function cancelEvaluation(){
		if(!empty($_POST)){
			$data['cancelReason'] = $_POST['cancelReason'];
			$data['canceller'] = $this->user->empID;
			$data['cancelDate'] = date('Y-m-d H:i:s');
			$data['status'] = 4;
			$this->commonmodel->addMyNotif($_POST['empId'], $this->user->name." cancelled your performance evaluation. Click <a href=evaluations/evaluationDetails/".$_POST['notifyId']." class='iframe'>here</a> to view details.",2,0,$this->user->empID);
			$this->databasemodel->updateQuery('staffEvaluationNotif',array('notifyId'=>$_POST['notifyId']),$data);
			echo "<script>
				parent.$.colorbox.close();
			</script>";
		}else{
			$data['content'] = "evaluations/cancelEvaluationForm";
			$data['empId'] = $this->uri->segment(3);
			$data['notifyId'] = $this->uri->segment(4);
			$data['name'] = $this->databasemodel->getSingleField('staffs','concat(fname," ",lname) as "name"','empId = '.$data['empId']);
			$this->load->view('includes/templatecolorbox', $data);
		}
	}

	public function saveEvaluation(){
		$data = $this->input->POST('data');
		$rows = [];
		for($i=0;$i < count($data['technical']['detailIdArr']);$i++){
			$dataArray = [
				'detail_id'=>$data['technical']['detailIdArr'][$i],
				'score' => $data['technical']['wtScoreArr'][$i],

				'remarks' => htmlentities((isset($data['technical']['remarksArr'][$i]) ? $data['technical']['remarksArr'][$i] : '')),
				'question_id' => $data['technical']['questionIdArr'][$i],
				'weight' => $data['technical']['wtArr'][$i],
				'rating' => $data['technical']['ratingArr'][$i],
				'emp_id' => $data['empId'],
				'staff_type' => $data['staffType'],
				'notifyId' => $data['notifyId'],
			];
			array_push($rows, $dataArray);
		}

		for($i=0; $i < count($data['behavioral']['detailIdArr']);$i++){
			$dataArray = [
				'detail_id'=>$data['behavioral']['detailIdArr'][$i],
				'score' => $data['behavioral']['wtScoreArr'][$i],
				'remarks' => htmlentities((isset($data['behavioral']['remarksArr'][$i]) ? $data['behavioral']['remarksArr'][$i] : '')),
				'question_id' => $data['behavioral']['questionIdArr'][$i],
				'weight' => $data['behavioral']['wtArr'][$i],
				'rating' => $data['behavioral']['ratingArr'][$i],
				'emp_id' => $data['empId'],
				'staff_type' => $data['staffType'],
				'notifyId' => $data['notifyId'],
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
			'evaluatorId' => $data['evaluator'],
			'staffType' => $data['staffType'],
			'notifyId' => $data['notifyId'],
		];


		 if($data['staffType'] == 2){

		 	 $this->emailModel->sendEvaluationEmail(1, $data['empId'], $data['evaluator'], $data['notifyId']);

		 	 $name = $this->databasemodel->getSingleField('staffs','concat(fname," ",lname) as "name"', 'empId = '.$data['empId']);
		 	 $this->commonmodel->addMyNotif($data['evaluator'], $name.' has entered its self-rating for his performance evaluation. Please <a href="performanceeval/1/'.$data['empId']."/".$data['empId']."/".$data['notifyId'].'" target="_blank">click here</a> to enter evaluator ratings.',2,1,0);
		 	 $this->databasemodel->updateQueryText('staffEvaluationNotif','status = 1','notifyId='.$data['notifyId']);

		 	// Self Rating Technical Questions
		 	$info2 += ['srtq'=> $data['empRating']['technical']];
		 	// Self Rating Behavioral Questions
		 	$info2 += ['srbq'=> $data['empRating']['behavioral']];
		 }

		 if($data['staffType'] == 1){
		 	$this->databasemodel->updateQueryText('staffEvaluationNotif','status = 2','notifyId='.$data['notifyId']);

		 	// Evaluator Technical Questions
		 	$info2 += ['etq' => $data['empRating']['technical']];
		 	// Evaluator Behavioral Questions
		 	$info2 += ['ebq' => $data['empRating']['behavioral']];
		 	// Evaluator Remarks
		 	$info2 += ['evalRemarks' => $data['evalRemarks']];
		 }
 		$this->evaluationsmodel->savePerformanceEvaluation($rows, $info2);
	}

	function saveEvaluationDate(){
		$evalDate = date('Y-m-d', strtotime($this->input->post('evalDate')));
		$evalName = $this->input->post('evaluatorName');

		$data = [
			'evalDate' => $evalDate,
			'empId' => $_POST['empID'],
			'evaluatorId' => $_POST['evaluator'],
		];

		$notifyId = $this->evaluationsmodel->saveEvaluationDate($data);
		$this->commonmodel->addMyNotif($data['empId'], $evalName.' has generated your performance evaluation. Please  <a href="performanceeval/2/'.$data['empId'].'/'.$data['evaluatorId'].'/'.$notifyId.'" target="_blank">click here</a> to enter your self-rating',2,1,$this->empID);

		$this->emailModel->sendEvaluationEmail(2, $data['empId'], $data['evaluatorId'], $notifyId);

	}

	public function evaluationDetails($notifId){
		$data['employee'] = $this->databasemodel->getSingleInfo('staffEvaluationNotif ses', 'ses.empId, concat(fname," ",lname) as name, title, dept, evalDate, supervisor, evaluatorId, ses.status, ses.cancelReason, ses.hrPrintDate, ses.hrUploadDate', 'notifyId='.$notifId, 'LEFT JOIN staffs on staffs.empId = ses.empId LEFT JOIN newPositions ON posID = position');
		$empId = $data['employee']->empId;
		if(!empty($_POST)){
			$data = array();
			if(!isset($_POST['printDate'])){
				$data['hrPrintDate'] = date('Y-m-d h:i:s');

			}

			if(is_uploaded_file($_FILES['fupload']['tmp_name'])){
				$data['hrUploadDate'] = date('Y-m-d h:i:s');
				$data['status'] = 3;
				$dir = "../../".UPLOADS."evaluations/";
				$fileDir = $dir.$empId."_eval_".strtotime(date('Y-m-d h:i:s')).'.pdf';
				move_uploaded_file($_FILES['fupload']['tmp_name'], $fileDir);
			}
			$this->databasemodel->updateQuery('staffEvaluationNotif','notifyId='.$notifId,$data);
			
			return false;
			echo '<script>parent.$.colorbox.close();</script>';
		}

		$data['content'] = "evaluations/perfEvalDetails";
		

		$data['evaluator'] = $this->databasemodel->getSingleInfo('staffs', 'concat(fname," ",lname) as name, title','empId='.$data['employee']->evaluatorId,'LEFT JOIN newPositions ON posID = position');

		$data['supervisor'] =  $this->databasemodel->getSingleInfo('staffs', 'concat(fname," ",lname) as name, title, supervisor','empId='.$data['employee']->supervisor,'LEFT JOIN newPositions ON posID = position');

		$data['supervisor2'] =  $this->databasemodel->getSingleInfo('staffs', 'concat(fname," ",lname) as name, title','empId='.$data['supervisor']->supervisor,'LEFT JOIN newPositions ON posID = position');

		$this->load->view('includes/templatecolorbox', $data);
	}

	/**
		PARAMS
		$staff_info = array(); //array of staff info who needs to be evaluated
		$evaluator_info = array(); //array of supervisor staff info who evaluated
		$evaluation_info = array(); //array of evaluation info
	**/
	//public function sendEvaluationEmail($evaluation_info, $staff_info, $supervisor_info){
	public function sendEmail($userType, $staff_id, $evaluator_id, $evaluation_id, $cc= ''){
		
		$this->emailmodel->sendEvaluationEmail($userType, $staff_id, $evaluator_id, $evaluation_id, $cc);

	}

	public function generateEvaluation($empID){
		$data['info'] = $this->databasemodel->getSingleInfo('staffs','fname, lname, dept, supervisor','empId = '.$empID, "left join newPositions on posID = position");
		$data['evaluator'] = $this->databasemodel->getQueryResults('staffs','empID, concat(fname," ",lname) as "name", title', 'staffs.active=1 and levelID_fk > 0' ,"left join newPositions on posID = position",'fname ASC');
		$data['empID'] = $empID;
		$data['content'] = 'evaluations/generateEvaluation';
		$this->load->view('includes/templatecolorbox', $data);
	}

	public function performanceEvaluationDetails(){
		$data['content'] = "evaluations/myPerformanceEvaluation";
		$data['tpage'] = "";

		$data['notifications'] = $this->evaluationsmodel->getMyPerformanceEvaluation($this->user->empID);
		$this->load->view('includes/template', $data);
	}

	public function evalPDF($staffType, $empID, $evalID, $notifId){
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');


		$pdf = new FPDI('P');
		// $pdf->SetAutoPageBreak('auto');
		$evaluationTitle = ['TECHNICAL GOALS AND OBJECTIVES', 'BEHAVIORAL GOALS AND OBJECTIVES'];
		$evaluatorTitles = ['Team Mate', 'Leaders and Clients', 'Immediate Supervisor'];

		$info = $this->databasemodel->getSingleInfo('staffs','concat(fname," ",lname) as name, title, startDate, DATE_ADD(startDate, INTERVAL 3 MONTH) as ninetiethDay, evalDate, position, srtq, srbq, etq, ebq, evalRemarks', 'staffs.empID = '.$empID.' AND notifyId = '.$notifId, 'LEFT JOIN newPositions np on np.posID = position LEFT JOIN staffEvaluationNotif sen ON sen.empId = staffs.empId');
		$evaluatorInfo = $this->databasemodel->getSingleInfo('staffs', 'concat(fname," ",lname) as name, title, supervisor', 'empID = '.$evalID, 'LEFT JOIN newPositions np on np.posID = position');
		$evaluatorInfo2 = $this->databasemodel->getSingleInfo('staffs', 'concat(fname," ",lname) as name, title, supervisor', 'empID = '.$evaluatorInfo->supervisor, 'LEFT JOIN newPositions np on np.posID = position');
		$textArr = ['Name of Employee', 'Immediate Supervisor', 'Position Title', 'Evaluator', 'Hire Date', "Evaluator's Position",'90th Day', 'Evaluation Date'];

	// For the 
		 $pdf->addPage();
		 $pdf->image('includes/images/logowithname.png',10,0,90,25,'PNG');
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
				$supervisorId = $evalID;
			#	$supervisor2Id = $evaluatorInfo->supervisor
				$posID = $info->position;
				unset($info->supervisor);
				unset($info->position);
			#	unset($infi)
			}

			$pdf->SetFont('Arial','',8);

			$y = 30;
			foreach ($info as $value) {
				$pdf->setXY(45,$y);
				$pdf->cell(61,5, $value,1,'','L',true);
				$y += 5;
			}

			$textArr = [$evaluatorInfo->name, $evaluatorInfo->name, $evaluatorInfo->title, date('Y-m-d', strtotime($info->evalDate))];
			$y = 30;
			foreach ($textArr as $value) {
				$pdf->setXY(141,$y);
				$pdf->cell(61,5, $value,1,'','L',true);
				$y += 5;
			}

			$pdf->setTextColor(255, 255, 255);
			$pdf->SetFillColor(128, 0, 0);

			$pdf->setXY(10,$y);
			$pdf->cell(96, 5, "Ratings",1,'','C', true);

			$pdf->setXY(106,$y);
			$pdf->cell(96, 5, "The minimum required for this evaluation is 100%", 1, '', 'C', true);
			$y += 5;

			$pdf->setTextColor(0,0,0);
			$pdf->SetFillColor(204, 255, 255);
			$pdf->SetFont('Arial','B',8);
			$colArr2 = ['Less than 100%', '100%', 'Above 100%'];
			$colArr3 = ['Did Not Meet Expectations', 'Meets Expectations', 'Exceeds Expectations'];
			for($x=0;$x<3;$x++){
				$pdf->setXY(10, $y);
				$pdf->cell(35, 5, $colArr2[$x],1,'','R',true);

				$pdf->setX(45);
				$pdf->cell(61, 5, $colArr3[$x],1,'','R',true);
				$y+=5;
			}

			$pdf->setXY(106, ($y-15));
			$pdf->cell(96, 5, 'Any grade below 100% is a failed evaluation.',1,'','C',true);

			$pdf->setXY(106, ($y-10));
			$pdf->cell(45.5, 10, 'OVERALL RATING',1,'','C',true);

			$pdf->setXY(151.5, ($y-10));
			// Edit this

			$overallRating = (int)( ( ( ($info->srtq * 0.2) + ($info->etq * 0.8) ) + ( ($info->srbq * 0.2) + ($info->ebq * 0.8) ) ) / 2);
			$pdf->cell(50.5,5,$overallRating.'%',1, '','C',true);

			if($overallRating < 100)
				$overallRatingText = 'Did Not Meet Expectation';
			if($overallRating  == 100)
				$overallRatingText = 'Meets Expectations';
			if($overallRating > 100)
				$overallRatingText = 'Exceeds Expectations';

			$pdf->setXY(151.6,($y-5));
			$pdf->cell(50.5,5,$overallRatingText, 1, '', 'C', false);
			
			$pdf->setTextColor(255, 255, 255);
			$pdf->SetFillColor(128, 0, 0);
			$pdf->SetFont('Arial','',8);

			$pdf->setXY(10, $y);
			$pdf->cell(96, 5, "Steps",1,'','C', true);
			$pdf->setXY(106, $y);
			$pdf->cell(96, 5, "Weighted Ratings",1,'','C', true);
			$y += 5;

			$pdf->setTextColor(0,0,0);
			$pdf->SetFillColor(204, 255, 255);
			$pdf->SetFont('Arial','',7);
			$pdf->rect(10,$y,96,20,'DF');
			$colArr = ['1. Employee gives his/her ratings in the self-ratings tab.', 
				"2. Evaluator gives his/her ratings in the evaluator's ratings tab.", 
				"3. Evaluator completes the summary tab.", 
				"4. Only the white cells in all tabs can be filled-in by the employee and evaluator."
			];
			for($x=0;$x<4;$x++){
				$pdf->setXY(10,($y + ($x*5)));
				$pdf->cell(96,5,$colArr[$x],0,'','L',false);
			}

			$pdf->SetFont('Arial','B',9);
			$pdf->setXY(106, $y);
			$pdf->cell(70,10,'Overall Technical Rating (50%)',1,'','C',true);
			$pdf->setXY(106, $y+10);
			$pdf->cell(70,10,'Overall Behavioral Rating (50%)',1,'','C',true);

			$pdf->setXY(176, $y);
			$pdf->cell(26, 10, (int)( ($info->srtq * 0.2) + ($info->etq * 0.8) ).'%', 1, '','C',true);
			$pdf->setXY(176, $y+10);
			$pdf->cell(26, 10, (int)( ($info->srbq * 0.2) + ($info->ebq * 0.8) ).'%',1,'','C',true);
			$y+=20;

			$pdf->setTextColor(255, 255, 255);
			$pdf->SetFillColor(128, 0, 0);
			$pdf->SetFont('Arial','',8);
			$pdf->setXY(10, $y);
			$pdf->cell(192, 5,'Performance Evaluation Details',1,'','C',true);
			$y+=5;

			$pdf->setTextColor(0,0,0);
			$pdf->SetFillColor(204, 255, 255);
			$pdf->SetFont('Arial','B',8);
			$colArr = ["Below 80%", "For termination of probationary employment.",
				'80% - 90%', 'For extension of probationary employment - 90 day evaluation results only.',
				'below 100%','For termination of probationary employment - 5th month and/or 2nd probationary evaluation only.',
				'100% and Above', 'For regularization.'
			];
			for($x=0;$x<8;$x++){
				if($x % 2 == 0){
					$pdf->setXY(10, $y);
					$pdf->cell(35,5,$colArr[$x],1,'','R',true);
				}else{
					$pdf->setXY(45, $y);
					$pdf->cell(157,5,$colArr[$x],1,'','L',true);
					$y+=5;
				}
			}

			$pdf->setTextColor(255, 255, 255);
			$pdf->SetFillColor(128, 0, 0);
			$pdf->SetFont('Arial','',8);
			$pdf->setXY(10, $y);
			$pdf->cell(96, 5, "Recommendation",1,'','C', true);
			$pdf->setXY(106, $y);
			$pdf->cell(96, 5, "Next Evaluation Date",1,'','C', true);
			$y+=5;

			$pdf->setTextColor(0,0,0);
			$pdf->SetFillColor(204, 255, 255);
			$pdf->SetFont('Arial','B',9);
			$pdf->SetXY(10, $y);
			$pdf->cell(96, 5, "For Regularization",1,'','C', false);
			$pdf->SetXY(106, $y);
			$pdf->cell(96, 5, "NA",1,'','C', false);
			$y+=5;

			$pdf->setTextColor(255, 255, 255);
			$pdf->SetFillColor(128, 0, 0);
			$pdf->SetFont('Arial','',8);
			$pdf->setXY(10, $y);
			$pdf->cell(192, 5, "Remarks",1,'','C', true);
			$y+=5;

			$pdf->setTextColor(0,0,0);
			$pdf->rect(10, $y, 192, 20);
			$pdf->setXY(10, $y);
			$pdf->multicell(192, 20, $info->evalRemarks,1,'C');
			$y+=20;

			$pdf->setTextColor(255, 255, 255);
			$pdf->setXY(10, $y);
			$pdf->cell(96, 5, "Evaluator Acknowledgement",1,'','C', true);
			$pdf->setXY(106, $y);
			$pdf->cell(96, 5, "Second Level Manager Acknowledgement",1,'','C', true);
			$y+=5;

			$pdf->setTextColor(0,0,0);
			$pdf->SetFillColor(204, 255, 255);
			$pdf->SetFont('Arial','',6.5);
			$pdf->setXY(10, $y);
			$pdf->cell(96, 5, "This is to certify that I have fairly evaluated and discussed the ratings to the employee.",1,'','C', true);
			$pdf->setXY(106, $y);
			$pdf->cell(96, 5, "This is to certify that I have reviewed and approved all evaluations provided.",1,'','C', true);
			$y+=5;

			$pdf->SetFont('Arial','B',8);
			$pdf->rect(10, $y, 96, 15);
			$pdf->setXY(10,$y+10);
			$pdf->cell(96, 5, $evaluatorInfo->name.' / '.date('F d, Y'),0,'','C');
			$pdf->rect(106, $y, 96, 15);
			$pdf->setXY(106,$y+10);
			$pdf->cell(96, 5, $evaluatorInfo2->name.' / '.date('F d, Y'),0,'','C');
			$y+=15;

			$pdf->setTextColor(255, 255, 255);
			$pdf->SetFillColor(128, 0, 0);
			$pdf->setXY(10,$y);
			$pdf->cell(192, 5, 'Employee Acknowledgement',1,'','C', true);
			$y+=5;

			$pdf->setTextColor(0,0,0);
			$pdf->SetFillColor(204, 255, 255);
			$pdf->SetFont('Arial','',8);
			$pdf->rect(10,$y,192,10,'DF');
			$pdf->setXY(10,$y);
			$pdf->multiCell(192,5,'I certify that the results of this evaluation have been discussed with me. I accept it as fair, and I acknowledge that I have been given the opportunity to discuss the results of the evaluation.',0,'C');
			$y+=10;

			$pdf->SetFont('Arial','B',8);
			$pdf->rect(10, $y, 192, 15);
			$pdf->setXY(10,$y+10);
			$pdf->cell(192, 5, $info->name.' / ' .date('F d, Y'),0,'','C');
			$y+=15;

			$pdf->SetFont('Arial','',8);
			$pdf->setXY(10,$y);
			$pdf->cell(192, 5, 'Employee Name and Signature / Date Signed',1,'','C', true);
			$y+=5;

			$pdf->setTextColor(255, 255, 255);
			$pdf->SetFillColor(128, 0, 0);
			$pdf->setXY(10,$y);
			$pdf->cell(192, 5, '',1,'','C', false);
			$pdf->setXY(10,$y+5);
			$pdf->cell(192, 5, 'IMPORTANT: The Employee and Evaluator must sign each page of this performance evaluation and supporting documents.',1,'','C', true);




// LIST OF QUESTIONS
		foreach($evaluationTitle as $eachTitle){		

			if($eachTitle == "TECHNICAL GOALS AND OBJECTIVES"){
				$questionType = 1;
				$job_type = $posID;
				$typeText = 'TECHNICAL';
				$ttlScore = $info->srtq;
				$evalTtlScore = $info->etq;
			}else{
				$questionType = 2;
				$job_type = 0;
				$typeText = 'BEHAVIORAL';
				$ttlScore = $info->srbq;
				$evalTtlScore = $info->ebq;			
			}

			$questions = $this->evaluationsmodel->getEvaluationScore($questionType, $job_type, $empID, $staffType, $notifId);

			$pdf->SetTitle('Evaluation Performance Form');
			$pdf->AddPage();
			// Header


			$pdf->image('includes/images/logowithname.png',10,0,90,25,'PNG');

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

			$pdf->SetFont('Arial','',8);

			$y = 30;
			$textArr = [$info->name, $info->title, $info->startDate, $info->ninetiethDay];
			foreach ($textArr as $value) {
				$pdf->setXY(45,$y);
				$pdf->cell(61,5, $value,1,'','L',true);
				$y += 5;
			}

			$evaluator = $evaluatorInfo->name;
			$evaluatorTitle = $evaluatorInfo->title;

			$textArr = [$evaluatorInfo->name, $evaluator, $evaluatorTitle, date('Y-m-d', strtotime($info->evalDate))];
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
			$pdf->cell(5,10,'#',1,'','C',true);

		 	$pdf->setXY(15,65);
		 	$pdf->cell(30,10,"Objective Goals",1,'','C',true);

		 	$pdf->setXY(45,65);
		 	$pdf->cell(35,10,"Expectation",1,'','C',true);

		 	$pdf->setXY(80,65);
		 	$text = ($eachTitle == "TECHNICAL GOALS AND OBJECTIVES" ? "Output Format" : "Evaluator");
		 	$pdf->cell(25,10,$text,1,'','C',true);

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

		 	// Page standards
		 	$pageHeight = 279.4; // (portrait size)
		 	$bottomMargin = 20; // millimeters

		 	// For the real questions
		 	$ii = 1;
		 	$y = $pdf->getY();		 	

			
			foreach($questions as $question){
				$fpdfCell = [];

				$h = $pdf->getY();
				$height = $h - $y;
				$height = ($height == 0 ? 60 : $height);
			if($ii == 5){
				$y -= 10;
			}
			#	array_push($fpdfCell, array(15, $y, $height, 30, $question->goals));		
				if(count($question->details) > 1){
					$ttlHeight = "";
					$y1 = $y;
					$iii = 1;	// to count the number of rows

					$pdf->setTextColor(0,0,0);
					$pdf->SetFillColor(204, 255,255);

					$expectationArr = [];
					$questionArr = [];
					$expectation1Text = "";
					foreach ($question->details as $row) {
						if(strcmp($expectation1Text, $row->expectation)){
							array_push($expectationArr, $row->expectation);
							array_push($questionArr, $row->question);
							$expectation1Text = $row->expectation;
						}
					}

					  if(count($expectationArr) == 1){
					  	$fpdfCell1 = [];
					  	if(strlen($expectationArr[0]) > strlen($questionArr[0])){
					  		array_push($fpdfCell1, array(105, $y, $height, 35, $questionArr[0]));
					  		array_push($fpdfCell1, array(45, $y, $height,35, $expectationArr[0]));
					  	}else{
					  		array_push($fpdfCell1, array(45, $y, $height,35, $expectationArr[0]));
					  		array_push($fpdfCell1, array(105, $y, $height, 35, $questionArr[0]));
					  	}

						foreach ($fpdfCell1 as $values) {
							
							$pdf->rect($values[0], $y ,$values[3],$height, 'DF');	
							$pdf->setXY($values[0], $y);
							$pdf->multiCell($values[3], 5, $values[4],0,'',false);
						}

					  	$height = ($height / count($question->details));
					
					  }

					foreach($question->details as $detailsRow){
						$text = ($detailsRow->score !=0 ? $detailsRow->score / $detailsRow->weight : 0);
						if(count($expectationArr) > 1){
							if(strlen($detailsRow->question) > strlen($detailsRow->expectation)){
								array_push($fpdfCell, array(45, $y, $height, 35, $detailsRow->expectation));
								array_push($fpdfCell, array(105, $y, $height, 35, $detailsRow->question));
							}else{
								array_push($fpdfCell, array(105, $y, $height, 35, $detailsRow->question));
								array_push($fpdfCell, array(45, $y, $height, 35, $detailsRow->expectation));
							}
						}

						if($eachTitle == "BEHAVIORAL GOALS AND OBJECTIVES"){
							$evaluatorText = $evaluatorTitles[$detailsRow->evaluator];
						}else{
							$evaluatorText = $detailsRow->evaluator;
						}
						$dy1 = $pdf->getY();
						
						array_unshift($fpdfCell, array(182, $y, $height, 10, $detailsRow->rating));
						array_unshift($fpdfCell, array(192, $y, $height, 10, $detailsRow->score));
						array_unshift($fpdfCell, array(80, $y, $height, 25, $evaluatorText));  
						array_unshift($fpdfCell, array(140, $y, $height, 7, $detailsRow->weight));
						array_unshift($fpdfCell, array(147, $y, $height, 35, html_entity_decode($detailsRow->remarks)));
						foreach ($fpdfCell as $value) {
					//	$pdf->rect(X, Y , W, H, 'DF / D / F');
							// These are filters to forcefully adjust cell's dimensions
							$value[2] = ($ii == 5 && $iii == 2 ? $value[2]+20 : $value[2]); 
							$value[2] = ($ii == 5 && $iii == 3 ? $value[2]-15 : $value[2]);

							$pdf->rect($value[0], $value[1] ,$value[3], $value[2], 'DF');
							$pdf->setXY($value[0], $y);
							$pdf->multiCell($value[3], 5, $value[4],0,'',false);
						}
						$iii++;
						$dy2 = $pdf->getY();
						$dHeight = $dy2 - $dy1;
						$ttlHeight += $height;
						$y = $pdf->getY() + 5;
						$fpdfCell = [];
						
					}
					 	$y += 5;	

					// Forcefully adjust cell height
					$ttlHeight = ($ii == 5 ? $ttlHeight+($height/2) : $ttlHeight);

					$pdf->setTextColor(0,0,0);
					$pdf->SetFillColor(204, 255,255);

					$pdf->rect(15, $y1, 30, $ttlHeight-$height, 'DF');
					$pdf->setXY(15, $y1);
					$pdf->multiCell(30, 5, $question->goals,0,'',false);

 					$pdf->setTextColor(255, 255,255);
					$pdf->SetFillColor(128, 0,0);

					$pdf->rect(10, $y1 ,5, $ttlHeight-$height, 'DF');
					$pdf->setXY(10, $y1);
					$pdf->multiCell(10, 5, $ii,0,'',false);

					$pdf->setY($y);
					if($y >=260){
						$pdf->SetFillColor(255, 255, 255);	
						$pdf->rect(9, $y, 194, $pageHeight - $y+1, 'F');
						$pdf->setY($y);
					 	$pdf->cell(192, 1, '', 'T', '', '');
					 	
						$pdf->addPage();
						$y = 10;
					}

				}else{

					if($eachTitle == "BEHAVIORAL GOALS AND OBJECTIVES"){
						$evaluatorText = $evaluatorTitles[$question->details[0]->evaluator];
					}else{
						$evaluatorText = $question->details[0]->evaluator;
					}

					$pdf->setTextColor(255, 255,255);
					$pdf->SetFillColor(128, 0,0);

					$pdf->rect(10, $y ,5, $height, 'DF');
					$pdf->setXY(10, $y);
					$pdf->multiCell(10, 5, $ii,0,'',false);

					$pdf->setTextColor(0,0,0);
					$pdf->SetFillColor(204, 255,255);

					array_push($fpdfCell, array(15, $y, $height, 30, $question->goals));	
					array_push($fpdfCell, array(182, $y, $height, 10, $question->details[0]->rating));
					array_push($fpdfCell, array(80, $y, $height, 25, $evaluatorText));
					array_push($fpdfCell, array(140, $y, $height, 7, $question->details[0]->weight));
					array_push($fpdfCell, array(147, $y, $height, 35, html_entity_decode($question->details[0]->remarks)));
					array_push($fpdfCell, array(192, $y, $height, 10, $question->details[0]->score));
					// The most number of characters should be last b/c it has the most # of text and it sets the height for the other cells
					if(strlen($question->details[0]->question) > strlen($question->details[0]->expectation)){
						array_push($fpdfCell, array(45, $y, $height, 35, $question->details[0]->expectation));
						array_push($fpdfCell, array(105, $y, $height, 35, $question->details[0]->question));
					}else{
						array_push($fpdfCell, array(105, $y, $height, 35, $question->details[0]->question));
						array_push($fpdfCell, array(45, $y, $height, 35, $question->details[0]->expectation));
					}

					foreach ($fpdfCell as $value) {
					//	$pdf->rect(X, Y , W, H, 'DF / D / F');
						$pdf->rect($value[0], $y ,$value[3], $height, 'DF');
						$pdf->setXY($value[0], $y);
						$pdf->multiCell($value[3], 5, $value[4],0,'',false);
					}
					
				}

				$y = $pdf->getY();

				$ii++;	

				if($y >=270){
				#	echo $y;
					$pdf->addPage();
					$y = 10;
					
				}

			}

			$pdf->SetFillColor(255, 255, 255);


		 	$pdf->SetX(9.5);
		 	$pdf->cell(80, 11, '', 'T', '', '', true);

			$pdf->setTextColor(255, 255,255);
			$pdf->SetFillColor(128, 0,0);
			$pdf->SetFont('Arial','B',10);
			$pdf->SetXY(55,$y);
			$pdf->cell(35,10,$typeText,1,'','C',true);

			$pdf->SetFont('Arial','B',8);

		 	$pdf->SetXY(88,($y));
		 	$pdf->cell(47,5,"Employee's Rating",1,'','C',true);
		 	$pdf->cell(10,5,$ttlScore."%",1,'','C',true);

		 	$pdf->SetXY(88,$y+5);
		 	$pdf->cell(47,5,"Employee's 20% Weighted Rating",1,'','C',true);
		 	$pdf->cell(10,5,($ttlScore * 0.2)."%",1,'','C',true);


		 	$pdf->SetXY(145,($y));
		 	$pdf->cell(47,5,"Evaluator's Rating",1,'','C',true);
		 	$pdf->cell(10,5,$evalTtlScore."%",1,'','C',true);

		 	$pdf->SetXY(145,$y+5);
		 	$pdf->cell(47,5,"Evaluator's 80% Weighted Rating",1,'','C',true);
		 	$pdf->cell(10,5,(int)($evalTtlScore * 0.8)."%",1,'','C',true);

		}
		$pdf->Output();
	}

}

?>
