<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Actionmodel extends CI_Model {

	var $ptDB;
	var $db;
	
    function __construct() {
        // Call the Model constructor
        parent::__construct();
		
		$this->load->model('Staffmodel', 'staffM');	
		$this->load->model('Textdefinemodel', 'txtM');	
		$this->db = $this->load->database('default', TRUE);
		$this->ptDB = $this->load->database('projectTracker', TRUE);		
    }
	
	public function whatIsMyAction($type, $contents=''){
		if($type=='sendEmail'){
			$this->commonM->addMyNotif($this->user->empID, $contents, 5, 0);
		}
	}
	
}

?>