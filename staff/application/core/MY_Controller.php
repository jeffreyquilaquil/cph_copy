<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
 
	public function __construct(){
		parent::__construct();
		
		if($this->config->item('devmode')===true)
			$this->db = $this->load->database('defaultdev', TRUE);
		else
			$this->db = $this->load->database('default', TRUE);
		$this->ptDB = $this->load->database('projectTracker', TRUE);
		
		session_start();
		$this->load->model('Databasemodel', 'dbmodel');
		$this->load->model('Textmodel', 'textM');
		$this->load->model('Commonmodel', 'commonM');
		$this->load->model('Emailmodel', 'emailM');
		
		$this->user = $this->getLoggedUser();
		$this->access = $this->getUserAccess();
		
		if($this->config->item('devmode')===true)
			error_reporting(E_ALL); ini_set('display_errors', 1);	
	}
	
	function getLoggedUser(){ 
		$uName = '';
		if(isset($_SESSION['u'])) $uName = $_SESSION['u'];
		
		if(!empty($uName)){
			$queryDB = $this->dbmodel->dbQuery('SELECT e.*, CONCAT(fname," ",lname) AS name, n.title, n.org, n.dept, n.grp, n.subgrp, n.orgLevel_fk, e.levelID_fk AS level FROM staffs e LEFT JOIN newPositions n ON e.position=n.posID WHERE e.username="'.$uName.'" OR md5(CONCAT(e.username,"","dv"))="'.$uName.'"');
			$row = $queryDB->row();	
			if(count($row)==0){
				$queryDB = $this->dbmodel->ptdbQuery('SELECT s.username, CONCAT(s.sFirst," ",s.sLast) AS name, s.email, "exec" AS access, 0 AS empID, 0 AS level FROM staff s WHERE username="'.$uName.'" AND active="Y"');
				$row = $queryDB->row();	
			}
			
			if(count($row)>0) return $row;
			else return false;	
		}else{
			return false;
		}
	}
	
	function getUserAccess(){
		$access = new stdClass;
		$access->accessFull = false;
		$access->accessHR = false;
		$access->accessFinance = false;
		$access->accessExec = false;
		$access->accessFullHR = false;
		$access->accessFullFinance = false;
		$access->accessFullHRFinance = false;
		
		if($this->user!=false){
			$access->myaccess = explode(',',$this->user->access);
			if(in_array('full', $access->myaccess)) $access->accessFull = true;
			if(in_array('hr', $access->myaccess)) $access->accessHR = true;
			if(in_array('finance', $access->myaccess)) $access->accessFinance = true;
			if(in_array('exec', $access->myaccess)) $access->accessExec = true;
			if(count(array_intersect($access->myaccess,array('full','hr')))>0) $access->accessFullHR = true;
			if(count(array_intersect($access->myaccess,array('full','finance')))>0) $access->accessFullFinance = true;
			if(count(array_intersect($access->myaccess,array('full','hr','finance')))>0) $access->accessFullHRFinance = true;	
		}
		
		return $access;
	}

	function checklogged($username, $pw){
		return $query = $this->dbmodel->dbQuery('SELECT empID, username, password FROM staffs WHERE active = 1 AND username = "'.$username.'" AND password = "'.md5($pw).'" LIMIT 1');
	}
}

?>