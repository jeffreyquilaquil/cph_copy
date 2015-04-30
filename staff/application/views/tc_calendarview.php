<?php
	if($daynum<10) $daynum0 = '0'.$daynum;
	else $daynum0 = $daynum;
		
	//for holidays
	if(isset($calHoliday[$daynum0])) echo '<div class="dayholiday">'.$calHoliday[$daynum0]['holidayType'].': '.$calHoliday[$daynum0]['holidayName'].'</div>';
	
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
		$myday = date('Y-m-d', strtotime($year.'-'.$month.'-'.$daynum0));	
		foreach($calScheds AS $cal){
			$xx = strtolower(date('l', strtotime($year.'-'.$month.'-'.$daynum0)));
			
			if($cal->schedType!=0){ //first week
				if($cal->schedType==1) $fval = 'first';
				else if($cal->schedType==2) $fval = 'second';
				else if($cal->schedType==3) $fval = 'third';
				else if($cal->schedType==4) $fval = 'fourth';
				else $fval = 'last';
			
				if($cal->$xx !=0 && $myday==date('Y-m-d', strtotime($fval.' '.$xx.' of '.$year.'-'.$month)))
					$customschedtext = $custTime[$cal->$xx];						
			}else{			
				if($myday >= $cal->effectivestart && ($myday <= $cal->effectiveend || $cal->effectiveend=='0000-00-00')){
					if($cal->$xx!=0)
						$customschedtext = $custTime[$cal->$xx];
				}
			}
		}
		
		//custom time schedules
		foreach($calSchedTime AS $ctym){
			if($myday>=$ctym->effectivestart && $myday<=$ctym->effectiveend)
				$customschedtext = $ctym->timeValue;
		}
		
		if(!empty($customschedtext))
			echo '<div class="daysched tacenter" style="background-color:green; padding:3px; border-radius:3px;">'.$customschedtext.'</div>';
	}
?>