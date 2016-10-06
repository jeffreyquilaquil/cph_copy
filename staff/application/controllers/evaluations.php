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
		$this->evaluationsmodel->savePerformanceEvaluation($rows, 2);
	}
}