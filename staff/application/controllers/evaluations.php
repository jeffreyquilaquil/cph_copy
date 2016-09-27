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
		
		$this->load->view('includes/template', $data);
	}

	public function addQuestions($type){

		$data[1]= array();

		if($type == 'technical'){
			$question_type = 1;
			$job_type = $this->uri->segment(10);
			$details_array = array(
				'expectation' => urldecode($this->uri->segment(6)),
				'evaluator' => urldecode($this->uri->segment(7)),
				'weight' => $this->uri->segment(8),
				'weight_score' => $this->uri->segment(9),
				);

			array_push($data[1], $details_array);
		}

		if($type == 'behavioral'){
			$question_type = 2;
			$job_type = 0;

			$expectation = explode('__', urldecode($this->uri->segment(6)));
			$evaluator = explode('__', $this->uri->segment(7));
			$weight = explode('__', $this->uri->segment(8));
			$weight_score = explode('__', $this->uri->segment(9));

			$expectation_count = count($expectation);
			$evaluator_count = count($evaluator);

			for($i=0;$i<$evaluator_count;$i++){
				$expectation_val = ($i < $expectation_count ? $expectation[$i] : $expectation[$expectation_count - 1]);

				$details_array = array(
					'expectation' => $expectation_val,
					'evaluator' => $evaluator[$i],
					'weight' => $weight[$i],
					'weight_score' => $weight_score[$i]
				);
				array_push($data[1], $details_array);
			}
		}

		$data[0] = array(
			'question_type' => $question_type,
			'job_type' => $job_type,
			'goals' => urldecode($this->uri->segment(4)),
			'question' => urldecode($this->uri->segment(5))
			);

		$this->evaluationsmodel->saveQuestion($data);
	}

	public function updateQuestions($type){
		$data[0] = array(
				'goals' => urldecode($this->uri->segment(4)),
				'question' => urldecode($this->uri->segment(5)),
				'question_id' =>  $this->uri->segment(12)
			);

		$data[1] = array();
		if($type == 'technical'){
			$details_array = array(
				'expectation' => urldecode($this->uri->segment(6)),
				'evaluator' => urldecode($this->uri->segment(7)),
				'weight' => $this->uri->segment(8),
				'weight_score' => $this->uri->segment(9),
				'detail_id' => $this->uri->segment(11)
				);
			array_push($data[1], $details_array);
		}

		if($type == 'behavioral'){

			$expectation = explode('__', urldecode($this->uri->segment(6)));
			$evaluator = explode('__', $this->uri->segment(7));
			$weight = explode('__', $this->uri->segment(8));
			$weight_score = explode('__', $this->uri->segment(9));
			$details_id = explode('__', $this->uri->segment(11));

			$evaluator_count = count($evaluator);
			$expectation_count = count($expectation);
			for($i=0; $i<$evaluator_count;$i++){
				$expectation_val = ($i < $expectation_count ? $expectation[$i] : $expectation[$expectation_count - 1]);

				$details_array = array(
					'expectation' => $expectation_val,
					'evaluator' => $evaluator[$i],
					'weight' => $weight[$i],
					'weight_score' => $weight_score[$i],
					'detail_id' => $details_id[$i]
					);

				array_push($data[1], $details_array);
			}
		}

		dd($data, false);

		$this->evaluationsmodel->updateQuestion($data);
	}
}