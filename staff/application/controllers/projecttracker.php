<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Projecttracker extends CI_Controller {
 
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Manila");				
		$this->db = $this->load->database('default', TRUE);	
		$this->load->model('Staffmodel', 'staffM');	
	}

	public function getUserData(){
		$uName = $this->uri->segment(3); //visit account
				
		$query = $this->staffM->getSQLQueryArrayResults('SELECT regDate AS reg_date, empStatus AS emp_status, email AS comp_email, companyNumber AS phone_line, extn AS extension, skype AS skype_account, google AS google_account, "" AS job_r, office, username AS u, CONCAT(fname," ",lname) AS n, address AS ad, city AS c, country AS s, zip AS z, email, phone1 AS p1, phone2 AS p2, bdate AS bD, idNum AS py, dept AS eDeptName, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS supName, levelName AS postype, "" AS nt, startDate AS sD, endDate AS eD, accessEndDate AS eSD, if(fulltime=1, "Y", "N") AS fT, maritalStatus AS md, "0" AS ex, "Y" AS dd, "Y" AS li, "Y" AS hi, spouse AS sp, dependents AS dp, title, shift, leaveCredits AS leaveC, sss AS SSS, philhealth AS Philhealth, tin AS TIN, hdmf AS HDMF,gender FROM staffs LEFT JOIN newPositions ON position=posID LEFT JOIN orgLevel ON levelID_fk=levelID WHERE username="'.$uName.'"'); 
		
		$info = array();
		if(count($query)>0){
		foreach($query[0] AS $dex=>$q):
			$info[$dex] = $q;
		endforeach;
		}
		//var_dump($info);
		echo serialize($info);
	}
	
	public function getAllUsers(){	
		$query = $this->staffM->getQueryArrayResults('staffs', 'office, username, CONCAT(fname," ",lname) AS n, address AS ad, city AS c, country AS s, zip AS z, email, phone1 AS p1, phone2 AS p2, date_format(bdate, "%b %d, %y") AS bd, dept AS edeptname, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS supName, levelName AS postype, startDate AS sd, endDate AS eD, if(fulltime=1, "Y", "N") AS ft, maritalStatus AS md, "Y" AS dd, "Y" AS li, "Y" AS hi, spouse AS sp, dependents AS dp, title, shift, sss AS sss, tin, hdmf,gender, if(staffs.active=1, "Y", "N") AS active', 'office="PH-Cebu"', 'LEFT JOIN newPositions ON position=posID LEFT JOIN orgLevel ON levelID_fk=levelID');	

		$knees = array();
		foreach($query AS $q):
			$knees[] = (array)$q;
		endforeach;
				
		echo serialize($knees);
	}
	
	public function checkStaff(){
		$segment3 = $this->uri->segment(3); //visit account
		$empID = $this->staffM->getSingleField('staffs', 'empID', 'office="PH-Cebu" AND username="'.$segment3.'"');
		if(!empty($empID)){
			echo 'exist';
		}		
	}
	
	public function getUserNotes(){
		$cphUser = $this->uri->segment(3);
		
		$notes = array();
		$empID = $this->staffM->getSingleField('staffs', 'empID', 'username="'.$cphUser.'"');
		if($empID!=''){
			$myNotes = $this->staffM->getQueryResults('staffMyNotif', 'staffMyNotif.*, username, CONCAT(fname," ",lname) AS name', 'empID_fk="'.$empID.'"','LEFT JOIN staffs ON empID=sID', 'dateissued DESC');
					
			$noteType = $this->staffM->definevar('noteType');
			$types = array();
			foreach($noteType AS $k=>$n):
				$types[$n] = $k;
			endforeach;
			
			if(count($myNotes)>0){
				foreach($myNotes AS $m):
					$notes[] = array(
							'eNoteText'=>'<p><b>'.$m->name.'</b> ('.date('M d y h:i a', strtotime($m->timestamp)).') <br> </p><p>'.$m->ntexts.'</p>', 
							'username' => $m->username, 
							'eNoteStamp' => $m->timestamp,
							'category' => $types[$m->ntype]
						);
				endforeach;
			}
		}
		echo serialize($notes);
	}
}
?>