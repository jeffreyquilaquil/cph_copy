<?php 
	$this->load->view('includes/header_timecard'); 
	
	$dataArr['content'] = array();
	$today = strtotime($today);
	$year = date('Y', $today);
	$month = date('m', $today);
	$days_in_month = cal_days_in_month(0, $month, $year);	
	
	//loop for number of days
	for($daynum=1; $daynum<=$days_in_month; $daynum++){
		$daytext = '';	
		if($daynum<10) $daynum0 = '0'.$daynum;
		else $daynum0 = $daynum;
		
		$currentday = date('Y-m-d', strtotime($year.'-'.$month.'-'.$daynum0));	//current dayholiday
		$dayoftheweek = date('N', strtotime($currentday));	//day of the week 1 (for Monday) through 7 (for Sunday)
			
		//for holidays
		if(isset($calHoliday[$daynum0])) 
			$daytext .= '<div class="dayholiday">'.$calHoliday[$daynum0]['holidayType'].': '.$calHoliday[$daynum0]['holidayName'].'</div>';
		
		//if not holiday OR if holiday but work OR holiday but staff holiday is US or PHL
		if(!isset($calHoliday[$daynum0]) || 
			(isset($calHoliday[$daynum0]) && 
				($calHoliday[$daynum0]['holidayWork']==1 ) ||
				($calHoliday[$daynum0]['holidayWork']==0 &&
					(($this->user->staffHolidaySched==0 && $calHoliday[$daynum0]['holidayTypeNum']==3) ||
						($this->user->staffHolidaySched==1 && ($calHoliday[$daynum0]['holidayTypeNum']==1 || $calHoliday[$daynum0]['holidayTypeNum']==2))
					)
				)
			)
		){
			//custom schedules
			$customschedtext = '';	
			$customschedtoday = '';				
			foreach($calScheds AS $cal){
				$xx = strtolower(date('l', strtotime($year.'-'.$month.'-'.$daynum0)));
				
				if($cal->schedType!=0){ //first week
					if($cal->schedType==1) $fval = 'first';
					else if($cal->schedType==2) $fval = 'second';
					else if($cal->schedType==3) $fval = 'third';
					else if($cal->schedType==4) $fval = 'fourth';
					else $fval = 'last';
				
					if($cal->$xx !=0 && $currentday==date('Y-m-d', strtotime($fval.' '.$xx.' of '.$year.'-'.$month)))
						$customschedtext .= $custTime[$cal->$xx];						
				}else{			
					if($currentday >= $cal->effectivestart && ($currentday <= $cal->effectiveend || $cal->effectiveend=='0000-00-00')){
						if($cal->$xx!=0)
							$customschedtext .= $custTime[$cal->$xx];
					}
				}
			}
			 
			//custom time schedules
			//if will check if current date is in between eff start and eff date and exclude saturday and sunday if start and end if not equal else include saturday and sunday
			foreach($calSchedTime AS $ctym){
				if($currentday>=$ctym->effectivestart && $currentday<=$ctym->effectiveend && 
					(($ctym->effectivestart!=$ctym->effectiveend && $dayoftheweek<6) || $ctym->effectivestart==$ctym->effectiveend)
				)
					$customschedtoday = $ctym->timeValue;
			}
			
			//custom sched for today
			if(!empty($customschedtoday)) 
				$daytext .= '<div class="daysched" style="background-color:#2123e0;">'.$customschedtoday.'</div>';
			else if(!empty($customschedtext))
				$daytext .= '<div class="daysched" style="background-color:green;">'.$customschedtext.'</div>';
					
		}
		
		//this is for Staff leaves
		foreach($calLeaves AS $leaves){
			$leavetxt = '';
			$start = date('Y-m-d', strtotime($leaves->leaveStart));
			$end = date('Y-m-d', strtotime($leaves->leaveEnd));
			
			if($currentday>=$start && $currentday<=$end && $dayoftheweek<6){
				$leavetxt = '<div class="daysched" style="background-color:#6ed86e;">';
				if($leaves->iscancelled!=0 || $leaves->status==0 || $leaves->status==4){					
					$leavetxt = $daytext.$leavetxt;
					$leavetxt .= '<a href="'.$this->config->base_url().'staffleaves/'.$leaves->leaveID.'/" class="iframe">Leave Pending Approval</a>';
				}else{
					$leavetxt .= '<a href="'.$this->config->base_url().'staffleaves/'.$leaves->leaveID.'/" class="iframe">';
					if($leaves->status==1) $leavetxt .= ' Paid Day Off';
					else $leavetxt .= ' Unpaid Day Off';
					$leavetxt .= '<br/><strike>'.date('h:i a', strtotime($leaves->leaveStart)).' - '.date('h:i a', strtotime($leaves->leaveEnd)).'</strike></a>';
				}				
				$leavetxt .= '</div>';
				$daytext = $leavetxt;
			}					
		}
		
		//birthdays
		/* $bdtoday = '';
		foreach($birthdayQuery AS $bday){			
			if($daynum0==$bday->bdateNum)
				$bdtoday .= $bday->name.', ';
		}
		if(!empty($bdtoday)) 
			$daytext .= '<div class="daysched" style="background-color:#dc6900; text-align:left;">Birthday: '.rtrim($bdtoday,', ').'</div>'; */
		
		
		if(!empty($daytext)) $dataArr['content'][$daynum] = $daytext;
		
	}
	

	$this->load->view('tc_calendartemplate', $dataArr);
?>