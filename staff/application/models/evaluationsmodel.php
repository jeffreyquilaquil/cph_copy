<?php
if( ! defined('BASEPATH')) exit('No direct script access allowed');

class Evaluationsmodel extends CI_model{

	function __construct(){
		parent::__construct();
		$this->load->model('databasemodel');
	}

	public function saveQuestion($data){
		$lastId = $this->databasemodel->insertQuery('evalQuestions' ,$data[0]);
		$data[0] += ['question_id'=>$lastId];

		// Add the Expectation, evaluator, Weight and Score Weight for a question
		// Because in Behavioral Type Questions, there are questions that have multiple expectations or evaluators 
		 $x = 0;
		 foreach ($data[1] as $row) {
		 	$data[1][$x] += ['question_id'=>$lastId];
		 	$row += ['question_id'=>$lastId];
		 	$id = $this->databasemodel->insertQuery('evalQuestionsDetails', $row);
		 	$data[1][$x] += ['detail_id'=>$id];
		 	$x++;
		}
	#return "pass";
		return $data;
	}

	public function updateQuestion($data){
		 $this->databasemodel->updateQuery('evalQuestions',  array("question_id" => $data[0]['question_id']), $data[0]);

		 foreach ($data[1] as $value) {
		 	$detail_id = $value['detail_id'];
		 	unset($value['detail_id']);

		 	if($detail_id != "add"){
		 		$this->databasemodel->updateQuery('evalQuestionsDetails', array("detail_id" => $detail_id), $value);
		 	}else{
		 		$value['question_id'] = $data[0]['question_id'];
		 		$this->db->insert("evalQuestionsDetails", $value);
		 	}
		 }

		return $data;
	}

	public function getQuestions($type, $jobType){
		$questions = [];
		$question_type = ($type == 'behavioralQuestions' ? 2 : 1);

		$query = "SELECT * FROM evalQuestions WHERE question_type = {$question_type} AND job_type = {$jobType}";
		$questions = $this->databasemodel->getSQLQueryResults($query);
		for($i = 0; $i < count($questions); $i++){
			$query = "SELECT * FROM evalQuestionsDetails WHERE question_id = {$questions[$i]->question_id}";
			$questions[$i]->details = $this->databasemodel->getSQLQueryResults($query);
		}
		return $questions;
	}

	public function getPositions(){
		// Fetch the list of positions so that it can be synced with the technical questions
		return $this->databasemodel->getQueryResults('newPositions', "posID, title",'title != ""','', 'title');
	}

	public function saveEvaluationDate($data){
		$data['evalDate'] = date('Y-m-d', strtotime($data['evalDate']));
		return $this->databasemodel->insertQuery("staffEvaluationNotif", $data);
	}

	public function getStaffEvaluation($staffId){
		$posID = $this->databasemodel->getSingleField('staffs','position', 'empID='.$staffId);
		$question['technical'] = $this->getQuestions("technicalQuestions", $posID);
		$question['behavioral'] = $this->getQuestions('behavioralQuestions', 0);

		return $question;
	}

	function savePerformanceEvaluation($data, $data2){
		if($data2['staffType'] == 2){
			$this->databasemodel->updateQueryText('staffEvaluationNotif', 'ansDate ="'.date('Y-m-d').'"', 'empId='.$data2['empId'].' AND evaluatorId='.$data2['evaluator']);

		}
		foreach ($data as $row) {
			$this->databasemodel->insertQuery('staffEvaluationScores', $row);
		//	print_r($row)."<br>";'
		}
		
	}

	function getEvaluationScore($questionType, $jobType, $empID, $staffType, $notifyId){
		$questions = [];

		$query = "SELECT question_id, goals FROM evalQuestions WHERE question_type = {$questionType} AND job_type = {$jobType}";
		$questions = $this->databasemodel->getSQLQueryResults($query);
		for($i = 0; $i < count($questions); $i++){
			$query = "SELECT eqd.detail_id, expectation, evaluator, ses.weight, score, question, remarks FROM evalQuestionsDetails eqd LEFT JOIN staffEvaluationScores ses ON ses.detail_id = eqd.detail_id WHERE eqd.question_id = {$questions[$i]->question_id} AND ses.staff_type = {$staffType} AND ses.emp_id = {$empID} AND notifyId = {$notifyId}";
			$questions[$i]->details = $this->databasemodel->getSQLQueryResults($query);
		}
		return $questions;
	}

	function getMyPerformanceEvaluation($empID){

		return $this->databasemodel->getQueryResults('staffEvaluationNotif', '*, (SELECT concat(fname," ",lname) FROM staffs WHERE empID = evaluatorID) as evaluatorName', 'empId ='+$empID, '');
	}

	function getStaffPerformanceEvaluation($empId, $dept, $isSupervisor){
		$evaluations = [];
		if($dept == 'IT'){
			foreach(range(0,4) as $status){
				$dataRow = [];
				$data = [];
				$value = $this->databasemodel->getQueryResults('staffEvaluationNotif ses', 'notifyId, ses.empid, concat(fname," ",lname) as "name", genDate,evalDate, supervisor,evaluatorId', 'status = '.$status, 'LEFT JOIN staffs ON staffs.empid = ses.empId');
				foreach($value as $info){
					$supervisorInfo = $this->databasemodel->getSingleInfo('staffs','concat(fname," ",lname) as name','empid = '.$info->supervisor);
					$actionButton = "";

					switch($status){
						case 0:
							$statusText = 'Pending Self-rating. <a href="'.$this->config->base_url().'evaluations/sendEvaluationEmail/2/'.$info->empid.'/'.$info->evaluatorId.'/'.$info->notifyId.'" target="_blank">Click here</a> to send reminder to employee to enter self-rating';
							$actionButton = '<a href="'.$this->config->base_url().'evaluations/cancelEvaluation/'.$info->empid.'/'.$info->notifyId.'" class="iframe"><input type="button" value="CANCEL"></a>';
						break;
						case 1:
							$statusText = 'Employee ratings locked in. <a href="'.$this->config->base_url().'evaluations/sendEvaluationEmail/1/'.$info->empid.'/'.$info->evaluatorId.'/'.$info->notifyId.'" target="_blank">Click here</a> to enter evaluator settings';
							$actionButton = '<a href="'.$this->config->base_url().'evaluations/cancelEvaluation/'.$info->empid.'/'.$info->notifyId.'" class="iframe"><input type="button" value="CANCEL"></a>';
						break;
						case 2:
							$statusText = 'Pending Evaluation Form for Printing';
							$actionButton = '<a href="'.$this->config->base_url().'evaluations/evaluationDetails/'.$info->notifyId.'" class="iframe"><img src="#"></a>';
						break;
						case 3:
							$statusText = "Done";
							$actionButton = '<a href="'.$this->config->base_url().'evaluations/evalPDF/2/'.$info->empid.'/'.$info->evaluatorId.'/'.$info->notifyId.'" target="_blank"><img src="#"></a>';
						break;
						case 4:
							$statusText = "Cancelled";
							$actionButton = '<a href="'.$this->config->base_url().'evaluations/evaluationDetails/'.$info->notifyId.'" class="iframe"><img src="#"></a>';
						break;
					}

					$data = [
						'Evaluation ID' => $info->notifyId,
						'Employee ID' => $info->empid,
						"Employee's Name" => $info->name,
						"Evaluator ID" => $info->evaluatorId,
						'Date Generated' => $info->genDate,
						'Evaluation Date' => $info->evalDate,
						'Immediate Supervisor' => $supervisorInfo->name,
						'Notify ID' => $info->notifyId,
						'Status' => $statusText,
						'Action' => $actionButton,
					];
					array_push($dataRow, $data);
				}
				array_push($evaluations, $dataRow);
			}
		}else if($isSupervisor == 1){
			$raf = $this->databasemodel->getQueryResults('staffs', 'empId', 'supervisor='.$empId);
			foreach($raf as $employee){
				$value = $this->databasemodel->getQueryResults('staffEvaluationNotif', '*','empId ='.$employee->empId);
				array_push($evaluations, $value);
			}
		}
		return $evaluations;
	}
}
?> 
