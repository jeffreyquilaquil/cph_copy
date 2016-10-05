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
}
?> 