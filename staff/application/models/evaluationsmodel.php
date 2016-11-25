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
		$technicalFilter = ($type != 'behavioralQuestions' ? 'AND hrStatus = 1' : '');;

		$query = "SELECT * FROM evalQuestions WHERE question_type = {$question_type} AND job_type = {$jobType} ".$technicalFilter."";
		$questions = $this->databasemodel->getSQLQueryResults($query);
		for($i = 0; $i < count($questions); $i++){
			$query = "SELECT * FROM evalQuestionsDetails WHERE question_id = {$questions[$i]->question_id} ";
			$questions[$i]->details = $this->databasemodel->getSQLQueryResults($query);
		}
		return $questions;
	}

	public function getReviewQuestions($jobType){
		$questionList = [];
		$questions = $this->databasemodel->getQueryResults('evalQuestions','evalQuestions.question_id, goals, created ,concat(fname," ",lname) as "name", question, expectation, evaluator, weight','hrStatus = 0 AND job_type = '.$jobType, 'LEFT JOIN staffs on empId = uploader LEFT JOIN evalQuestionsDetails ON evalQuestionsDetails.question_id = evalQuestions.question_id');
		foreach($questions as $row){
			$data =[
				'ID' => $row->question_id,
				'Objective Goals' => $row->goals,
				'Expectation' => $row->expectation,
				'Output Format' => $row->evaluator,
				'Evaluation Question' => $row->question,
				'Wt.' => $row->weight,
				'Uploaded by' => $row->name,
				'Action' => '<input type="button" value="Approve" data-val="1" class="btnclass btnApprove" data-Id="'.$row->question_id.'">',
			];	
			array_push($questionList, $data);
		}
		return $questionList;
	}

	public function getPositions(){
		// Fetch the list of positions so that it can be synced with the technical questions
		return $this->databasemodel->getQueryResults('newPositions', "posID, title",'title != ""','', 'title');
	}

	public function saveEvaluationDate($data){
		$data['genDate'] = date('Y-m-d');
		return $this->databasemodel->insertQuery("staffEvaluationNotif", $data);
	}

	public function getStaffEvaluation($staffId){
		$posID = $this->databasemodel->getSingleField('staffs','position', 'empID='.$staffId);
		$question['technical'] = $this->getQuestions("technicalQuestions", $posID);
		$question['behavioral'] = $this->getQuestions('behavioralQuestions', 0);

		return $question;
	}

	function savePerformanceEvaluation($data, $data2){
		$notifyId = $data2['notifyId'];
		unset($data2['notifyId']);
		unset($data2['staffType']);
		$this->databasemodel->updateQuery('staffEvaluationNotif',array('notifyId'=>$notifyId), $data2);
		die();
		if($data2['staffType'] == 2){
			foreach ($data as $row) {
				$this->databasemodel->insertQuery('staffEvaluationScores', $row);
			//	print_r($row)."<br>";'
			}
		}else{
			foreach($data as $row){
				$this->databasemodel->updateQueryText('staffEvaluationScores', 'evalRating = "'.$row['rating'].'", evalRemarks="'.$row['remarks'].'"', 'detail_id = "'.$row['detail_id'].'" AND notifyId = "'.$row['notifyId'].'"');
			}
		}
	}

	function getEvaluationScore($questionType, $jobType, $empID, $staffType, $notifyId){
		$questions = [];
		$query = "SELECT question_id, goals FROM evalQuestions WHERE question_type = {$questionType} AND job_type = {$jobType}";
		$questions = $this->databasemodel->getSQLQueryResults($query);
		for($i = 0; $i < count($questions); $i++){
			$query = "SELECT eqd.detail_id, expectation, evaluator, ses.weight, score, question, remarks, rating FROM evalQuestionsDetails eqd LEFT JOIN staffEvaluationScores ses ON ses.detail_id = eqd.detail_id WHERE eqd.question_id = {$questions[$i]->question_id} AND ses.staff_type = {$staffType} AND notifyId = {$notifyId}";
			$questions[$i]->details = $this->databasemodel->getSQLQueryResults($query);
		}
		return $questions;
	}

	function getMyPerformanceEvaluation($empID){

		return $this->databasemodel->getQueryResults('staffEvaluationNotif', '*, (SELECT concat(fname," ",lname) FROM staffs WHERE empID = evaluatorID) as evaluatorName', 'empId ='.$empID, '');
	}

	function getStaffPerformanceEvaluation($empId, $dept, $isSupervisor){
		$evaluations = [];
		$allStatus =[];
		
		if($dept == 'Human Resources'){
			foreach(range(0,4) as $status){
				
				$data = [];
				$value = $this->databasemodel->getQueryResults('staffEvaluationNotif ses', 'notifyId, ses.empid, concat(fname," ",lname) as "name", genDate,evalDate, supervisor,evaluatorId, status, hrUploadDate', 'status = '.$status, 'LEFT JOIN staffs ON staffs.empid = ses.empId');
				array_push($allStatus, $value);
			}
		}else if($isSupervisor == 1){
	
			foreach(range(0,4) as $status){
				$value = $this->databasemodel->getQueryResults('staffEvaluationNotif ses', 'notifyId, ses.empid, concat(fname," ",lname) as "name", genDate,evalDate, supervisor,evaluatorId, status, hrUploadDate', 'status = '.$status.' AND staffs.supervisor = '.$empId, 'LEFT JOIN staffs ON staffs.empid = ses.empId');
				array_push($allStatus, $value);
			}
		}
		foreach($allStatus as $status){
			$dataRow = [];
			$actionButton = "";
			$statusText = "";
			foreach($status as $info){
				$supervisorInfo = $this->databasemodel->getSingleInfo('staffs','concat(fname," ",lname) as name','empid = '.$info->supervisor);
				$editImageBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACoAAAAhCAIAAAAgZq7PAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAQ0SURBVFhH7ZZtUxpXFID7/8q6gPL+IhIapARFRoPVim0lxmrKIOhEk9S3iE2MaKtpTSSDRquQFDGmCdYojVq1RkJURuo4jrM97L1dFsQEmpn4Jc+cD/eec/Y+7HL3wifUezO7sMThfIpicNyHs9nxvvq50CKH4DD6KlsbLmRHbvrd3R1piblAo1/d2ITp39sRgiQZN8TSWiKfPTnoq1s6QVBwTscr/AwGZQ0OUlrIiCHc3mncmjVZ6eGmkbJ1yIMyDd132OJEqX8ElXLi3frgYhhWh4e8FYniFEUZLtnZ7ktt3biQI+/Qex8FYXW+Wnd8fIxTFFVsaWC7IQQavTfwOy7nwtv092cCsLSivAbPaaqar7PFpitXx3yzBD8fxobLjqOjI9yXHafqg4t/woqqL6x4TtPU1c+IIcR6Ey5QlPGyAyV7RydQxnbTbe247bw90ueZHpzwTz4Jzf2xzH6KwKl6nvIcKS/CE5reux5GDMFXnceF/3geXslXa6FEcHl8pZrdzITrHv5wiMz6VzsxaJ1fXsFzipqcfcpeBeKirR3XUpmaX7D1j1a2dJLyDJ/A2NiK+2gy62uv9gg1OjyhqNDyS/bxQkqVPIVaWW7B5VOodqTsEhQy05e4TJNZz5MqrF0DaPxmZydPJGUvsb7+l0xvanWPoobTGH7oZ1+FAl4iXKbJoH++sg73Gov/g6YlVlvyepIbWFganpipcX4PpVgs5nv2ArWd5OXGFvwYJK+lgxBJcZkmg76+M3GiNXXdQVP0UqGAA3/g4W8waLw5pLF8CwPHDz+htpMcHBwQ/ALmWhSwK3GZJoNe8nmZRFcq1hpgPDL1CF0GxzuvqFh43kBK5KILFSKtAY5h+623HbSgz8sXMGIUcDO4TJOuh7dTVWEZnnwMGwem8LRJmWo88DQSiaCG7Dk8PORk0BfgMk26XlpyUf9103cdrvtTvsLKOrgAbgLyFucN71wI9WRJdHePk/pzDMEt1OAyTbq+yt6e+PtAcAihBLpbXG6Ur2y+Ud9xC42zJLy6xhajEBSX4TJNut56rZfdTQhE9T1DvQ/8ilLzduQ1bqLZ399/vBDue+Bzjf26urmFsxS1uf3aOeiBjZmv1rGXQlFUWYf7aFL0a9tvhGotHLdMt9xUzSHgaSTGPKVGqDMKtEauTEVwuUwPCoLH5yrUhECclk8Lsz2xpRhS9BUN9jF/EAao9d50AOV98yFn/89F5m9kpWZ9na22va+xx93jmRmeCbrH/R13vV9d69NZmzW1jaYrbV2/TESjUfjpY5TsuP7jGFoTkdRPP1tSlde82otbOwegz+XJ+Z9TGmKdEW8jLi9PoiQlCq5c9WJ1A5dpknrxhQqwwv1pa+qX15Pf5f8GXrzdvQTxeBynTpDUm2Fvdw/6Q2E8/yCkfPcfno/6M+Sj/gw5Uz1F/QvesptB1LgvDAAAAABJRU5ErkJggg==';
				$pdfImageBase64 = "data:image/png;base64,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";

				switch($info->status){
					case 0:
						$statusText = 'Pending Self-rating. <a href="'.$this->config->base_url().'evaluations/sendEmail/2/'.$info->empid.'/'.$info->evaluatorId.'/'.$info->notifyId.'" target="_blank">Click here</a> to send reminder to employee to enter self-rating';
						$actionButton = '<a href="'.$this->config->base_url().'evaluations/cancelEvaluation/'.$info->empid.'/'.$info->notifyId.'" class="iframe"><input type="button" value="CANCEL" class="btnclass"></a>';
					break;
					case 1:
						$statusText = 'Employee ratings locked in. <a href="'.$this->config->base_url().'performanceeval/1/'.$info->empid.'/'.$info->evaluatorId.'/'.$info->notifyId.'" target="_blank">Click here</a> to enter evaluator settings';
						$actionButton = '<a href="'.$this->config->base_url().'evaluations/cancelEvaluation/'.$info->empid.'/'.$info->notifyId.'" class="iframe"><input type="button" value="CANCEL" class="btnclass"></a>';
					break;
					case 2:
						$statusText = 'Pending Evaluation Form for Printings';
						if($this->user->dept == "Human Resources" || $this->user->access == "full"){
							$actionButton = '<a href="'.$this->config->base_url().'evaluations/evaluationDetails/'.$info->notifyId.'" class="iframe"><img src="'.$editImageBase64.'"></a>';
							$actionButton .= '<a href="'.$this->config->base_url().'evaluations/evalPDF/2/'.$info->empid.'/'.$info->evaluatorId.'/'.$info->notifyId.'" target="_blank"><img src="'.$pdfImageBase64.'"></a>';
						}
						
					break;
					case 3:
						$statusText = "Ratings: Meets Expectations.";
						$actionButton = '<a href="../../'.UPLOADS.'evaluations/'.$info->empid.'_eval_'.strtotime($info->hrUploadDate).'" target="_blank"><img src="'.$pdfImageBase64.'"></a>';
					break;
					case 4:
						$statusText = "Cancelled";
						$actionButton = '<a href="'.$this->config->base_url().'evaluations/evaluationDetails/'.$info->notifyId.'" class="iframe"><img src="'.$editImageBase64.'"></a>';
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
		return $evaluations;
	}
}
?> 
