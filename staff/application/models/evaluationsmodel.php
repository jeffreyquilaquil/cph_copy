<?php
if( ! defined('BASEPATH')) exit('No direct script access allowed');

class Evaluationsmodel extends CI_model{
	public function saveQuestion($data){
		$this->db->insert('evalQuestions', $data[0]);
		$lastId = $this->db->insert_id();

		foreach ($data[1] as $row) {
			$row['question_id'] = $lastId;
			$this->db->insert('evalQuestionsDetails', $row);
		}

	}

	public function updateQuestion($data){
		$this->load->model('databasemodel');

		$this->databasemodel->updateQuery('evalQuestions',  array("question_id" => $data[0]['question_id']), $data[0]);
		dd($data, false);

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
	}

	public function getQuestions($type, $jobType){
		$questions = [];
		$question_type = ($type == 'behavioralQuestions' ? 2 : 1);
		$this->load->model('databasemodel');

		$query = "SELECT * FROM evalQuestions WHERE question_type = {$question_type} AND job_type = {$jobType}";
		$questions = $this->databasemodel->getSQLQueryResults($query);
		for($i = 0; $i < count($questions); $i++){
			$query = "SELECT * FROM evalQuestionsDetails WHERE question_id = {$questions[$i]->question_id}";
			$questions[$i]->details = $this->databasemodel->getSQLQueryResults($query);
		}
		return $questions;
	}
}
?> 