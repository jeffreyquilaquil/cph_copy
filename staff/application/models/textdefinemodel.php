<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Textdefinemodel extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }
	
	function definevar($con){
		$a = array();
		if(strtolower($con)=='maritalstatus'){
			$a = array(
					'Single' => 'Single',
					'Married' => 'Married',
					'Widowed' => 'Widowed',
					'Separated' => 'Separated',
					'Divorced' => 'Divorced'
				);
		}else if($con=='yesno'){
			$a = array(
					'No' => 'No',
					'Yes' => 'Yes'
				);
		}else if($con=='yesno01' || $con=='active'){
			$a = array(
					'0' => 'No',
					'1' => 'Yes'
				);
		}else if($con=='empStatus'){
			$a = array(
						'probationary' => 'Probationary', 
						'regular' => 'Regular',
						'part-time' => 'Part-Time'
					);
		}else if($con=='gender'){
			$a = array(
						'M' => 'Male', 
						'F' => 'Female'
					);
		}else if($con=='sanctionawol'){
			$a = array(
						'1' => '1-4 Days Suspension', 
						'2' => '5-10 Days Suspension',
						'3' => 'Termination'
					);
		}else if($con=='sanctiontardiness'){
			$a = array(
						'1' => 'Verbal Warning', 
						'2' => 'Written Warning',
						'3' => '1 – 4 Days Suspension',
						'4' => '5 - 10 Days Suspension',
						'5' => 'Termination'
					);
		}else if($con=='leaveType'){
			$a = array(
						'1' => 'Vacation Leave',
						'2' => 'Sick Leave',
						'3' => 'Emergency Leave',
						'4' => 'Offsetting',
						'5' => 'Paternity Leave',
						'6' => 'Maternity Leave',
						'7' => 'Solo Parent Leave',
						'8' => 'Special Leave for Women'
					);
		}else if($con=='leaveStatus'){
			$a = array(
						'0' => 'pending approval',
						'1' => 'approved w/ pay',
						'2' => 'approved w/o pay',
						'3' => 'disapproved',
						'4' => 'additional information required',
						'5' => 'deleted'
					);						
		}else if($con=='noteType'){
			$a = array(
						'other'=>0,
						'salary'=>1,
						'performance'=>2,
						'timeoff'=>3,
						'disciplinary'=>4,
						'actions'=>5
					);
		}else if($con=='office'){
			$a = array(
						'PH-Cebu'=>'PH-Cebu',
						'US-OKC'=>'US-OKC'
					);
		}else if($con=='terminationType'){
			$a = array(
						'0'=>'',
						'1'=>'Voluntary (Resignation)',
						'2'=>'Involuntary (Just Cause - AWOL)',
						'3'=>'Involuntary (End of Probationary Employment)',
						'4'=>'Involuntary (Just Cause)'
					);
		}else if($con=='areaofimprovement'){
			$a = array(
					0 => 'Work Quality/Productivity/Performance',
					1 => 'Attendance/Dependability',
					2 => 'Safety or Work Environment',
					3 => 'Conduct or Behavior (Interpersonal Skills)'
				);
		}else if($con=='coachingrecommendations'){
			$a = array(
					0 => 'Eligible for Regularization',
					1 => 'End of Probationary Employment',
					2 => 'NTE for Poor Performance',
					3 => 'Eligible for continued employment',
					4 => 'Follow-up with another Coaching',
					5 => 'Extension of Probationary Period',
					6 => 'Recommended Transfer',
					7 => 'Other'
				);
		}else if($con=='taxstatus'){
			$a = array(
					0 => '',
					1 => 'Single with No Dependents (S)',
					2 => 'Single with 1 Qualified Dependent (S1)',
					3 => 'Single with 2 Qualified Dependents (S2)',
					4 => 'Single with 3 Qualified Dependents (S3)',
					5 => 'Single with 4 Qualified Dependents (S4)',
					6 => 'Married',
					7 => 'Married with 1 Qualified Dependent (M1)',
					8 => 'Married with 2 Qualified Dependents (M2)',
					9 => 'Married with 3 Qualified Dependents (M3)',
					10 => 'Married with 4 Qualified Dependents (M4)'
				);
		}
		
		return $a;
	}
	
	function defineField($f){
		$v = '';
		if($f=='fname') $v = 'First Name';
		else if($f=='lname') $v = 'Last Name';
		else if($f=='lname') $v = 'Last Name';
		else if($f=='mname') $v = 'Middle Name';
		else if($f=='suffix') $v = 'Name Suffix';
		else if($f=='username') $v = 'Username';
		else if($f=='email') $v = 'Company E-mail';
		else if($f=='pemail') $v = 'Personal E-mail'; 
		else if($f=='address' || $f=='address1') $v = 'Address';
		else if($f=='city') $v = 'City';
		else if($f=='country') $v = 'Country';
		else if($f=='zip') $v = 'Zipcode';
		else if($f=='phone') $v = 'Phone Number';
		else if($f=='phone1') $v = 'Phone 1';
		else if($f=='phone2') $v = 'Phone 2';
		else if($f=='bdate') $v = 'Birthday';
		else if($f=='gender') $v = 'Gender';
		else if($f=='maritalStatus') $v = 'Marital Status';
		else if($f=='spouse') $v = 'Spouse';
		else if($f=='dependents') $v = 'Dependents';
		else if($f=='sss') $v = 'SSS';
		else if($f=='tin') $v = 'TIN';
		else if($f=='philhealth') $v = 'Philhealth';
		else if($f=='hdmf') $v = 'HDMF';
		else if($f=='office') $v = 'Office Branch';
		else if($f=='shift') $v = 'Shift Sched';
		else if($f=='startDate') $v = 'Start Date';
		else if($f=='idNum') $v = 'Payroll ID';
		else if($f=='supervisor') $v = 'Supervisor';
		else if($f=='department') $v = 'Department';
		else if($f=='grp') $v = 'Group'; 
		else if($f=='dept') $v = 'Department';
		else if($f=='title' || $f=='position') $v = 'Position Title';
		else if($f=='skype') $v = 'Skype Account';
		else if($f=='google') $v = 'Google Account';
		else if($f=='endDate') $v = 'Separation Date';
		else if($f=='accessEndDate') $v = 'Access End Date';
		else if($f=='fulltime') $v = 'Full-Time';
		else if($f=='empStatus') $v = 'Employee Status';
		else if($f=='regDate') $v = 'Regularization Date'; 
		else if($f=='separationDate') $v = 'SeparationDate Date';  
		else if($f=='evalDate') $v = 'Evaluation Date';  
		else if($f=='levelName' || $f=='levelID_fk') $v = 'Org Level';
		else if($f=='sal' || $f=='salary') $v = 'Salary';
		else if($f=='active') $v = 'Is Active';
		else if($f=='leaveCredits') $v = 'Leave Credits';
		else if($f=='allowance') $v = 'Monthly Allowance';
		else if($f=='bankAccnt') $v = 'Payroll Bank Account Number';
		else if($f=='hmoNumber') $v = 'HMO Policy Number';
		else if($f=='terminationType') $v = 'Termination Reason';
		else if($f=='taxstatus') $v = 'Tax Status';
		
		return $v;
	}
	
	function getEmps($all, $id, $n){
	$emptxt = '';
	if(isset($all[$id])){
		$emptxt .= '<ul class="ul_'.$n.' emp_'.$id.' hidden">';
		foreach($all[$id] AS $a):
			if(isset($all[$a[0]])){
				$emptxt .= '<li class="li_'.$n.'" style="cursor:pointer" onClick="toggleDisplay('.$a[0].')">';
			}else{
				$emptxt .= '<li class="li_'.$n.' li_none">';
			}
			
			$emptxt .= '<u>'.$a[2].'</u>, <i><a href="'.$this->config->base_url().'staffinfo/'.$a[3].'/" target="_blank">'.$a[1].'</a></i>';
			
			
			if(isset($all[$a[0]]))
				$emptxt .= '<div id="pointer_'.$a[0].'" class="tpointer" style="float:right; background-color:#ccc; padding:0 5px;">+</div>';
				
			$emptxt .= '</li>';
			if(isset($all[$a[0]])){
				$emptxt .= $this->getEmps($all, $a[0], ($n+1));
			}
		endforeach;	
		$emptxt .= '</ul>';
	}
	
	return $emptxt;
}
		
}

?>