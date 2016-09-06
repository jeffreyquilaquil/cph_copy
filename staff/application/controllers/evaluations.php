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
	}

	//
	public function index(){
		
		//$which = $this->uri->segment();
		$data['content'] = 'evaluations/index';
		$data['tpage'] = 'evaluations';
		$data['column'] = 'withLeft';

		
		
		$this->load->view('includes/template', $data);
	}

	public function questionnaires(){

		$data['content'] = 'evaluations/questionnaires';
		$data['tpage'] = 'evaluations';
		$data['column'] = 'withLeft';

		$this->load->view('includes/template', $data);
	} 

	

}