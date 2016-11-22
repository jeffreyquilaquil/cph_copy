<?php 
if( !defined('BASEPATH') ) exit('No direct script access allowed');
/**
* 
*/
class Exams extends CI_Controller
{
	
	public function __construct()
	{
		
		parent::__construct();
		$this->load->model('textmodel', 'textM');
		if($this->config->item('devmode')===true)
			$this->db = $this->load->database('defaultdev', TRUE);
		else
			$this->db = $this->load->database('default', TRUE);
		$this->load->helper('form');
		$this->load->helper('url');


	}

	public function index(){

		$data = $this->textM->questions();
		shuffle_assoc($data['questionnaires']);

		if( $this->input->post() ){
			//check login
			if( $this->input->post('login') ){
				$this->session->set_userdata('name', $this->input->post('username') );
			} else if( $this->input->post('submit') ){
				
				$answers = [];
				$cnt = count($data['questionnaires']);
				for( $x = 0; $x < $cnt; $x++ ){
					if( $this->input->post($x)  ){
						$answers[ $x ] = $this->input->post($x);
					}
				}
				$insert_array['name'] = $this->input->post('name');
				$insert_array['answers'] = json_encode( $answers );
				$insert_array['created_at'] = date('Y-m-d H:i:s');
				$insert_array['started_at'] = $this->input->post('started_at');

				$this->db->insert('exams', $insert_array);

				$this->session->unset_userdata('name');

				redirect( $this->config->base_url().'exams/success', 'location');
				return true;
			}


		} 

		if( !$this->session->userdata('name') ){
			$data['hide_nav'] = true;
			$data['content'] = 'exams/login';
			$this->load->view('exams/template', $data);
			return true;				
		}

		
		
		
		$data['content'] = 'exams/questions';
		$this->load->view('exams/template', $data);			

		
	}

	public function success(){

		$this->load->view('exams/template', ['content' => 'exams/success'] );
	}

	public function results(){
		
		$data['content'] = 'exams/results';
		$this->load->view('exams/template', $data);
	}
	
}