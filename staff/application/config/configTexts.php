<?php
	$config['txt_fname'] = 'First Name';
	$config['txt_lname'] = 'Last Name';
	$config['txt_mname'] = 'Middle Name';
	$config['txt_suffix'] = 'Name Suffix';
	$config['txt_username'] = 'Username';
	$config['txt_email'] = 'Company E-mail';
	$config['txt_pemail'] = 'Personal E-mail'; 
	$config['txt_address'] = 'Address';
	$config['txt_address1'] = 'Address';
	$config['txt_city'] = 'City';
	$config['txt_country'] = 'Country';
	$config['txt_zip'] = 'Zipcode';
	$config['txt_phone'] = 'Phone Number';
	$config['txt_phone1'] = 'Phone 1';
	$config['txt_phone2'] = 'Phone 2';
	$config['txt_bdate'] = 'Birthday';
	$config['txt_gender'] = 'Gender';
	$config['txt_maritalStatus'] = 'Marital Status';
	$config['txt_spouse'] = 'Spouse';
	$config['txt_dependents'] = 'Dependents';
	$config['txt_sss'] = 'SSS';
	$config['txt_tin'] = 'TIN';
	$config['txt_philhealth'] = 'Philhealth';
	$config['txt_hdmf'] = 'HDMF';
	$config['txt_office'] = 'Office Branch';
	$config['txt_shift'] = 'Shift Sched';
	$config['txt_startDate'] = 'Start Date';
	$config['txt_idNum'] = 'Payroll ID';
	$config['txt_supervisor'] = 'Supervisor';
	$config['txt_department'] = 'Department';
	$config['txt_grp'] = 'Group';
	$config['txt_dept'] = 'Department';
	$config['txt_title'] = 'Position Title';
	$config['txt_position'] = 'Position Title';
	$config['txt_skype'] = 'Skype Account';
	$config['txt_google'] = 'Google Account';
	$config['txt_endDate'] = 'Separation Date';
	$config['txt_accessEndDate'] = 'Access End Date';
	$config['txt_fulltime'] = 'Full-Time';
	$config['txt_empStatus'] = 'Employee Status';
	$config['txt_regDate'] = 'Regularization Date';
	$config['txt_separationDate'] = 'SeparationDate Date';
	$config['txt_evalDate'] = 'Evaluation Date';
	$config['txt_levelName'] = 'Org Level';
	$config['txt_levelID_fk'] = 'Org Level';
	$config['txt_sal'] = 'Salary';
	$config['txt_salary'] = 'Salary';
	$config['txt_active'] = 'Is Active';
	$config['txt_leaveCredits'] = 'Leave Credits';
	$config['txt_allowance'] = 'Monthly Allowance';
	$config['txt_bankAccnt'] = 'Payroll Bank Account Number';
	$config['txt_hmoNumber'] = 'HMO Policy Number';
	$config['txt_terminationType'] = 'Termination Reason';
	$config['txt_taxstatus'] = 'Tax Status';
	
	$config['maritalStatus'] = array(
					'Single' => 'Single',
					'Married' => 'Married',
					'Widowed' => 'Widowed',
					'Separated' => 'Separated',
					'Divorced' => 'Divorced'
				);
	$config['yesno'] = array(
					'No' => 'No',
					'Yes' => 'Yes'
				);				
	$config['yesno01'] = array(
					'0' => 'No',
					'1' => 'Yes'
				);
	$config['active'] = array(
					'0' => 'No',
					'1' => 'Yes'
				);
	$config['empStatus'] = array(
						'probationary' => 'Probationary', 
						'regular' => 'Regular',
						'part-time' => 'Part-Time'
					);			
	$config['gender'] = array(
						'M' => 'Male', 
						'F' => 'Female'
					);			
	$config['sanctionawol'] = array(
						'1' => '1-4 Days Suspension', 
						'2' => '5-10 Days Suspension',
						'3' => 'Termination'
					);			
	$config['sanctiontardiness'] = array(
						'1' => 'Verbal Warning', 
						'2' => 'Written Warning',
						'3' => '1 - 4 Days Suspension',
						'4' => '5 - 10 Days Suspension',
						'5' => 'Termination'
					);
	$config['leaveType'] = array(
						'1' => 'Vacation Leave',
						'2' => 'Sick Leave',
						'3' => 'Emergency Leave',
						'4' => 'Offsetting',
						'5' => 'Paternity Leave',
						'6' => 'Maternity Leave',
						'7' => 'Solo Parent Leave',
						'8' => 'Special Leave for Women'
					);
	$config['leaveStatus'] = array(
						'0' => 'pending approval',
						'1' => 'approved w/ pay',
						'2' => 'approved w/o pay',
						'3' => 'disapproved',
						'4' => 'additional information required',
						'5' => 'deleted'
					);	
	$config['noteType'] = array(
						'other'=>0,
						'salary'=>1,
						'performance'=>2,
						'timeoff'=>3,
						'disciplinary'=>4,
						'actions'=>5
					);
	$config['office'] = array(
						'PH-Cebu'=>'PH-Cebu',
						'US-OKC'=>'US-OKC'
					);				
	$config['terminationType'] = array(
						'0'=>'',
						'1'=>'Voluntary (Resignation)',
						'2'=>'Involuntary (Just Cause - AWOL)',
						'3'=>'Involuntary (End of Probationary Employment)',
						'4'=>'Involuntary (Just Cause)',
						'5'=>'Terminated for Just Cause: No Show'
					);				
	$config['areaofimprovement'] = array(
					0 => 'Work Quality/Productivity/Performance',
					1 => 'Attendance/Dependability',
					2 => 'Safety or Work Environment',
					3 => 'Conduct or Behavior (Interpersonal Skills)'
				);
	$config['coachingrecommendations'] = array(
					0 => 'Eligible for Regularization',
					1 => 'End of Probationary Employment',
					2 => 'NTE for Poor Performance',
					3 => 'Eligible for continued employment',
					4 => 'Follow-up with another Coaching',
					5 => 'Extension of Probationary Period',
					6 => 'Recommended Transfer',
					7 => 'Other'
				);
	$config['taxstatus'] = array(
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
	$config['requiredTest'] = array(
				'iq' => 'IQ Test',
				'typing' => 'Typing Test',
				'written' => 'Written Comprehension Test',
				'pmEmail' => 'PM Email Test',
				'pressRelease' => 'Press Release Writing Test',
				'design' => 'Design Test',
				'editing' => 'Copy Editing Test',
				'it' => 'IT Test',									
				'sales' => 'Sales Quiz',				
				'acqEmail' => 'Acquisitions Email Test',			
				'pcfTest' => 'PCF Test',		
				'editingTest' => 'Editing Test',			
				'sampleAudio' => 'Sample Audio Recording',		
				'illustrations' => 'Illustrations Test'			
			);
	$config['nteStat'] = array(
				'0' => 'CAR Generated',		
				'1' => 'NTE Generated',		
				'2' => 'Cancelled',		
				'3' => 'Satisfactory'		
			);				
	$config['schedType'] = array(
				'0' => '',		
				'1' => 'First',		
				'2' => 'Second',		
				'3' => 'Third',		
				'4' => 'Fourth',		
				'5' => 'Last'		
			);
?>