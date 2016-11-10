<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payrollmodel extends CI_Model {
	function __construct() {
        // Call the Model constructor
        parent::__construct();			
    }
	
	
	/******
		$info is an array of empIDs (separated with comma), dateStart, dateEnd, payrollsID, payType
		
		First, insert to tcPayslips
		Then, insert to tcPayslipDetails
	******/
	public function generatepayroll($info){		
		$empArr = explode(',', rtrim($info['empIDs'], ','));
		$catArr = $this->textM->constantArr('payCategory');
		
		foreach($empArr AS $emp){
			$empInfo = $this->dbmodel->getSingleInfo('staffs', 'sal, taxstatus', 'empID="'.$emp.'"');
			$monthlyRate = $this->textM->decryptText($empInfo->sal);
			
			if(isset($down)) 
				unset($down);
								
			///INSERT TO tcPayslips			
			$payslipID = $this->dbmodel->getSingleField('tcPayslips', 'payslipID', 'payrollsID_fk="'.$info['payrollsID'].'" AND empID_fk="'.$emp.'" AND pstatus=1');
			if(empty($payslipID)){
				$payIns['empID_fk'] = $emp;
				$payIns['payrollsID_fk'] = $info['payrollsID'];
				$payIns['monthlyRate'] = str_replace(',', '', $monthlyRate);
				$payslipID = $this->dbmodel->insertQuery('tcPayslips', $payIns);
			}else{
				$down['monthlyRate'] = str_replace(',', '', $monthlyRate);
			}
			
			////INSERT Payslip details
			$this->payrollM->insertPayslipDetails($payslipID); //inserting payslip details EXCEPT tax
			
			///compute for taxable income and tax then INSERT TAX values
			$taxableIncome = $this->payrollM->getTaxableIncome($payslipID);
			$tax = $this->payrollM->getTax($payslipID, $taxableIncome, $info['payType'], $empInfo->taxstatus);
			$this->payrollM->insertPayEachDetail($payslipID, $this->dbmodel->getSingleField('tcPayslipItems', 'payID', 'payAmount="taxTable"'), $tax); ////inserting income tax 
					
			////UPDATING RECORDS and COMPUTING FOR NET
			$down['totalTaxable'] = $taxableIncome;
			
			$queryItems = $this->dbmodel->getQueryResults('tcPayslipDetails', 'payID, payValue, payType, payCDto, payCategory, payAmount', 'payslipID_fk="'.$payslipID.'"', 'LEFT JOIN tcPayslipItems ON payID=payItemID_fk');		
			if(count($queryItems)>0){				
				$down['earning'] = 0;
				$down['deduction'] = 0;
				$down['allowance'] = 0;
				$down['adjustment'] = 0;
				$down['advance'] = 0;
				$down['benefit'] = 0;
				$down['bonus'] = 0;
				$down['net'] = 0;

				//adjustments that doesn't need to be added to the earnings
				$notAllowedAdjustments = array(10,29,35,34,25,24,26,11,42,41,46);
				
				$groupPerCat = array();	
				foreach($queryItems AS $q){
					if($q->payType=='credit'){
						$down['net'] += $q->payValue;
						if($q->payID )
						$groupPerCat[$q->payCategory] = ((isset($groupPerCat[$q->payCategory]))?$groupPerCat[$q->payCategory]+$q->payValue:$q->payValue);
					}else{
						$down['net'] -= $q->payValue;
						$groupPerCat[$q->payCategory] = ((isset($groupPerCat[$q->payCategory]))?$groupPerCat[$q->payCategory]-$q->payValue:-$q->payValue);
					}				
					
					//this is for the base pay
					if($q->payAmount=='basePay')
						$down['basePay'] = $q->payValue;
				}
				
				///earning or gross = pay+adjustment+allowance+bonus+vacationpay
				$grossArr = array(0,1,3,4,5,7);
				foreach($groupPerCat AS $k=>$cat){
					if(in_array($k, $grossArr)){
						if(isset($down['earning'])) $down['earning'] += $cat;
						else $down['earning'] = $cat;
					}
					
					if($k!=0 && $k!==7){
						if(isset($down[$catArr[$k]])) $down[$catArr[$k]] += $cat;
						else $down[$catArr[$k]] = $cat;
					}
				}
			}
					
			$this->dbmodel->updateQuery('tcPayslips', array('payslipID'=>$payslipID, 'empID_fk'=>$emp, 'payrollsID_fk'=>$info['payrollsID'], 'pstatus'=>1), $down);
			
			//number generated
			$cntGenerated = $this->dbmodel->getSingleField('tcPayslips', 'COUNT(payslipID)', 'payrollsID_fk="'.$info['payrollsID'].'" AND pstatus=1');
			$this->dbmodel->updateQueryText('tcPayrolls', 'numGenerated="'.$cntGenerated.'"', 'payrollsID="'.$info['payrollsID'].'"');
			$this->payrollM->staffLogStatus($info['payrollsID']);
		}
	}
	
	/*****
		$info should have these "empID_fk, monthlyRate, payrollsID, payPeriodStart, payPeriodEnd, payType"
	*****/
	public function getPaymentItemsForPayroll($info){
		$kaonNapud = array();
		$dessertItems = $this->payrollM->getPaymentItems($info->empID_fk, 1, '', $info->payPeriodStart, $info->payPeriodEnd);
		// echo "<pre>";
		// var_dump($dessertItems);
			
		foreach($dessertItems AS $cake){
			$eat = true;
			
			if($cake->payPeriod=='per payroll' || $cake->payPeriod=='once' || $cake->payPeriod==$info->payType){								
				if($cake->payStart!='0000-00-00'){
					//if($cake->payPeriod=='once' && $cake->payStart<=$info->payPeriodStart && $cake->payStart>=$info->payPeriodEnd){
					if($cake->payPeriod=='once'){
						if($cake->payStart>=$info->payPeriodStart && $cake->payStart<=$info->payPeriodEnd){
							$eat = true;
						}else $eat = false;											
					}else if($cake->payPeriod!='once'){						
						if(($cake->payStart>=$info->payPeriodStart && $cake->payStart<=$info->payPeriodEnd) || ($cake->payEnd>=$info->payPeriodStart && $cake->payEnd<=$info->payPeriodEnd)
							|| ($cake->payStart>=$info->payPeriodStart && $cake->payStart<=$info->payPeriodEnd) || ($info->payPeriodStart>=$cake->payStart && $info->payPeriodStart<=$cake->payEnd) || $cake->payID_fk == 32 || $cake->payID_fk == 18
						){
							$eat = true; 
						}
						else $eat = false;
					}
				}
				
				if($eat==true){ 
					$kaonNapud[] = $cake; 
				}
			}
		}
		return $kaonNapud;		
	}
	
	////INSERT Payslip details
	public function insertPayslipDetails($payslipID){
		$info = $this->dbmodel->getSingleInfo('tcPayslips', 'empID_fk, monthlyRate, payrollsID, payPeriodStart, payPeriodEnd, payType, startDate, endDate', 'payslipID="'.$payslipID.'"', 'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk LEFT JOIN staffs ON empID=empID_fk');
		
		//update previous inserted items value to "0" first before updating to new value
		$this->dbmodel->updateQueryText('tcPayslipDetails', 'payValue=0, numHR=0', 'payslipID_fk='.$payslipID);
		
		$itemArr = $this->payrollM->getPaymentItemsForPayroll($info);
		
		///INSERT PAYSLIP DETAILS except tax because it is computed last
		foreach($itemArr AS $item){
			$hr = 0;
			$payValue = 0;
			$happen = true;
			
			$hourArr = array('nightdiff', 'taken', 'overtime', 'specialHoliday', 'regularHoliday', 'NDspecial', 'NDregular');			
			if(in_array($item->payAmount, $hourArr) || $item->prevAmount=='hourly'){
				$hourlyRate = $this->payrollM->getDailyHourlyRate($info->monthlyRate, 'hourly');
				
				if($item->prevAmount=='hourly'){
					$hr = $item->payAmount;
					$payValue = $hourlyRate*$hr;
					if($item->payPercent!=0) $payValue = ($payValue * $item->payPercent)/100;
				}else if($item->payAmount=='nightdiff'){
					$hr = $this->payrollM->getNumHours($info->empID_fk, $info->payPeriodStart, $info->payPeriodEnd, 'publishND');
					$payValue = ($hourlyRate*$hr) * 0.10;
				}else if($item->payAmount=='overtime'){ ///overtime hours is 30%
					$hr = $this->payrollM->getNumHours($info->empID_fk, $info->payPeriodStart, $info->payPeriodEnd, 'publishOT');
					$payValue = ($hourlyRate*$hr) * 0.30;
				}else if($item->payAmount=='regularHoliday' || $item->payAmount=='specialHoliday'){
					$hr = $this->payrollM->getNumHours($info->empID_fk, $info->payPeriodStart, $info->payPeriodEnd, $item->payPercent, 'publishHO');
					if($item->payAmount=='specialHoliday') $payValue = ($hourlyRate*$hr) * 0.30;
					else $payValue = $hourlyRate*$hr;
				}else if($item->payAmount=='NDspecial' || $item->payAmount=='NDregular'){
					$hr = $this->payrollM->getNumHours($info->empID_fk, $info->payPeriodStart, $info->payPeriodEnd, $item->payAmount, 'publishHOND');
					$payValue = ($hourlyRate*$hr) * 0.10; ///night diff 10%
					if($item->payAmount=='NDspecial') $payValue = $payValue * 0.30;
				}else{
					$hr = $this->payrollM->getNumHours($info->empID_fk, $info->payPeriodStart, $info->payPeriodEnd);
					$payValue = $hourlyRate*$hr;
				}
			}else if($item->prevAmount=='basePay' || $item->payCategory==3){ ////base pay and allowance computations
				$hr = 0;
				
				if($info->startDate>$info->payPeriodStart && $info->startDate<=$info->payPeriodEnd){

					//update 09-27-2016 weekends should be included for new hire
					$hr = $this->payrollM->getNumDays($info->startDate, $info->payPeriodEnd, false);
				}else if($info->endDate!="0000-00-00" && $info->endDate>=$info->payPeriodStart && $info->endDate<$info->payPeriodEnd){

					//weekends already subtracted
					$hr = $this->payrollM->getNumDays($info->payPeriodStart, $info->endDate);
					//check if the endDate has publish, if not then we can subtract to $hr;

					if( $hr > 1 ){
						$endDateDay = date('l', strtotime($info->endDate) );
			
						//don't pay weekends if endDate ends on Monday
						if( in_array($endDateDay, array('Monday') ) ){
							$hr = $hr - 2;
						}
						/*
						//don't include weekends to subtract
						//weekends are paid
						$endDate = $info->endDate;
						while( strtotime($endDate) >= strtotime($info->payPeriodStart) ){
							//check if on weekends, don't subtract since $hr has already weekends subtracted
							$endDateDay = date('l', strtotime($endDate) );
							if( !in_array($endDateDay, array('Saturday', 'Sunday') ) ){
								//$hasPublish = $this->dbmodel->getSingleField('tcStaffLogPublish', 'sLogDate', 'sLogDate = "'. $endDate .'" AND empID_fk = '. $info->empID_fk );
								//if( isset($hasPublish) AND empty($hasPublish) ){
									$hr--;
								//}
							}
							//decrement
							$endDateObj = new DateTime($endDate);
							$endDateObj->sub( new DateInterval('P1D') );

							$endDate = $endDateObj->format('Y-m-d');
						}*/	
					}
					
				}	
				
				if($item->prevAmount=='basePay'){					
					if($hr>0){
						$dailyRate = $this->payrollM->getDailyHourlyRate($info->monthlyRate, 'daily');
						$payValue = $dailyRate*$hr;
					}else $payValue = str_replace(',', '', ($info->monthlyRate/2));					
				}else if($item->payCategory==3){ ///for allowances					
					if($hr==0 && $item->payCode!='proRatedAllowance'){
						$payValue = $item->payAmount;
					}else{
						if($item->payCode=='proRatedAllowance'){ ///this is for pro rated allowance daily rate
							$allowanceDailyRate = $this->dbmodel->getSingleField('staffSettings', 'settingVal', 'settingName="allowanceDailyRate"');
							$payValue = $allowanceDailyRate * $hr;
						}else $happen = false;
					}
				}		
			}else if($item->payAmount=='philhealthTable'){
				$payValue = $this->dbmodel->getSingleField('philhealthTable', 'employeeShare', 'minRange<="'.$info->monthlyRate.'" AND maxRange>= "'.$info->monthlyRate.'"');
			}else if($item->payAmount=='sssTable'){
				$payValue = $this->dbmodel->getSingleField('sssTable', 'employeeShare', 'minRange<="'.$info->monthlyRate.'" AND maxRange>= "'.$info->monthlyRate.'"');
			}else if($item->payAmount=='taxTable'){
				$happen = false;
			}else $payValue = $item->payAmount;
			
			if($happen==true)
				$this->payrollM->insertPayEachDetail($payslipID, $item->payID_fk, $payValue, $hr);
		}
	}
	
	
	public function insertPayEachDetail($payslipID, $itemID, $payValue, $hr=0){
		$detailID = $this->dbmodel->getSingleField('tcPayslipDetails', 'detailID', 'payslipID_fk="'.$payslipID.'" AND payItemID_fk="'.$itemID.'"');
		$payValue = str_replace(',', '', $payValue);

		//or this is an additional pag-ibig contribution
		if(empty($detailID) || $itemID == 32){
			$insDetails['payslipID_fk'] = $payslipID;
			$insDetails['payItemID_fk'] = $itemID;				
			$insDetails['payValue'] = $payValue;			
			$insDetails['numHR'] = $hr;
			if($payValue>0)
				$this->dbmodel->insertQuery('tcPayslipDetails', $insDetails);
		}else{		
			$this->dbmodel->updateQuery('tcPayslipDetails', array('detailID'=>$detailID), array('payValue'=>$payValue, 'numHR'=>$hr));
		}
	}
	
	
	//Getting values of earnings, bonuses, allowances and deductions
	///bonus (non-taxable)
	///allowances (non-taxable)
	///deduction (taxable) for all deductions deduction and other deductions
	public function getComputedPayslipValue($type, $payslipID){
		$amount = 0;
		
		if($type=='earning'){ /// basic pay + incentive (taxable) + other pays like holiday
			$amount = $this->dbmodel->getSingleField('tcPayslips', 'basePay', 'payslipID="'.$payslipID.'"'); //base pay
			$earQuery = $this->dbmodel->getQueryResults('tcPayslipDetails', 'payValue, payType, payCDto', 'payslipID_fk="'.$payslipID.'" AND payCategory=0', 'LEFT JOIN tcPayslipItems ON payID=payItemID_fk');
		
			if(count($earQuery)>0){
				foreach($earQuery AS $e){
					if($e->payType=='credit') $amount += $e->payValue;
					else $amount -= $e->payValue;
				}
			}			
		}else{
			$condition = 'payslipID_fk="'.$payslipID.'"';
			if($type=='adjustment') $condition .= ' AND payCategory=1 ';
			else if($type=='advance') $condition .= ' AND payCategory=2 ';
			else if($type=='allowance') $condition .= ' AND payCategory=3 ';
			else if($type=='benefit') $condition .= ' AND payCategory=4 ';
			else if($type=='bonus') $condition .= ' AND payCategory=5 ';
			else if($type=='deduction') $condition .= ' AND payType="debit" ';
			else if($type=='vacationpay') $condition .= ' AND payCategory=7 ';
			
			$amount = $this->dbmodel->getSingleField('tcPayslipDetails LEFT JOIN tcPayslipItems ON payID=payItemID_fk', 'SUM(payValue) AS amount', $condition);
		}
		
		return $amount;
	}
	
	/*****
		$type = 'publishDeduct or publishND';
	*****/
	public function getNumHours($empID, $dateStart, $dateEnd, $type='publishDeduct', $publishType=''){
		if(is_numeric($type)){ //this is for the holiday
			$cond = ' AND (holidayType='.$type.' OR ((SELECT holidayType FROM tcAttendance WHERE dateToday=DATE_ADD(slogDate,INTERVAL 1 DAY))='.$type.'))';
			if($type==1) $cond .= ' AND staffHolidaySched=0';
			else if($type==3) $cond .= ' AND staffHolidaySched=1';
			else if( $type==2) $cond .= ' AND staffHolidaySched IN (0, 1)'; //special holiday should affect both shifts
			
			$hourDeduction = $this->dbmodel->getSingleField('tcStaffLogPublish LEFT JOIN tcAttendance ON dateToday=slogDate LEFT JOIN staffs ON empID=empID_fk', 'SUM(publishHO) AS hours', 
			'empID_fk="'.$empID.'" AND slogDate BETWEEN "'.$dateStart.'" AND "'.$dateEnd.'" AND publishHO!="" AND showStatus=1 '.$cond);
		}else if($type=='NDspecial' || $type=='NDregular'){
			if($type=='NDspecial') $holidayType = '2';
			else $holidayType = '1,3,4';
			
			$cond = ' AND (holidayType IN ('.$holidayType.') OR ((SELECT holidayType FROM tcAttendance WHERE dateToday=DATE_ADD(slogDate,INTERVAL 1 DAY)) IN ('.$holidayType.')))';
			if($type==1) $cond .= ' AND staffHolidaySched=0';
			else if($type==3) $cond .= ' AND staffHolidaySched=1';
			else if( $type==2) $cond .= ' AND staffHolidaySched IN (0, 1)'; //special holiday should affect both shifts
			
			$hourDeduction = $this->dbmodel->getSingleField('tcStaffLogPublish LEFT JOIN tcAttendance ON dateToday=slogDate LEFT JOIN staffs ON empID=empID_fk', 'SUM(publishHOND) AS hours', 
			'empID_fk="'.$empID.'" AND slogDate BETWEEN "'.$dateStart.'" AND "'.$dateEnd.'" AND publishHOND!=""  AND showStatus=1 '.$cond);
		}else{
			$hourDeduction = $this->dbmodel->getSingleField('tcStaffLogPublish', 'SUM('.$type.') AS hours', 'empID_fk="'.$empID.'" AND slogDate BETWEEN "'.$dateStart.'" AND "'.$dateEnd.'" AND showStatus=1');
		}
		
		if(empty($hourDeduction)) return 0;
		else return $hourDeduction;
	}
	
	/****
		$type = hour or pay
	****/
	public function getNightDiff($payslipID, $type='hour'){
		$val = 0;
		$info = $this->dbmodel->getSingleInfo('tcPayrolls', 'payPeriodStart, payPeriodEnd, empID_fk, monthlyRate', 'payslipID="'.$payslipID.'"', 'LEFT JOIN tcPayslips ON payrollsID=payrollsID_fk');
		$val = $this->payrollM->getNumHours($info->empID_fk, $info->payPeriodStart, $info->payPeriodEnd, 'publishND');
		
		if($type=='pay'){
			$hourlyRate = $this->payrollM->getDailyHourlyRate($info->monthlyRate, 'hourly');
			$val = ($hourlyRate * $val) * 0.10;
		}
		return $val;
	}
	
	
	/****** Taxable income computation is:
		Total Earnings is total of (abse salary + incentive + night diff + other pays like (holidays))
		Taxable is now the sum of all BASE + TAXABLE but MINUS income tax
		
		If Semi-monthly	
			Total earnings minus (SSS, pag-ibig and absences)
		If Monthly
			Total earnings minus (Philhealth and absences)
	******/
	public function getTaxableIncome($payslipID){
		$taxable = 0;
		$info = $this->dbmodel->getQueryResults('tcPayslipDetails', 'payItemID_fk, payValue, payType, payCDto', 'payslipID_fk="'.$payslipID.'" AND (payCDto = "taxable" OR payCDto="base") AND tcPayslipItems.payAmount!="taxTable"', 'LEFT JOIN tcPayslipItems ON payID=payItemID_fk LEFT JOIN tcPayslips ON payslipID= payslipID_fk');
		
		foreach($info AS $in){
			if($in->payType=='debit') $taxable -= $in->payValue;
			else $taxable += $in->payValue;
		}
		
		return $taxable;		
	}
	
	
	/*****
		Accepts, payslipID, amount of taxable income, payType[monthly, semi]
	*****/
	public function getTax($payslipID, $taxableIncome, $payType, $taxstatus){		
		$tax = 0;
		$prevTax = 0;
		$compute = true;
		$taxableIncome = str_replace(',', '', $taxableIncome);
		
		$info = $this->dbmodel->getSingleInfo('tcPayslips', 'payslipID, empID_fk, payPeriodStart, payPeriodEnd, payType', 'payslipID="'.$payslipID.'"', 'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk');
		
		if(count($info)>0){
			$taxMe = $this->dbmodel->getSingleInfo('tcPayslipItemStaffs', 'tcPayslipItemStaffs.payAmount, tcPayslipItemStaffs.status', 'tcPayslipItems.payAmount="taxTable" AND empID_fk="'.$info->empID_fk.'"', 'LEFT JOIN tcPayslipItems ON payID=payID_fk');
			if(count($taxMe)>0){
				if($taxMe->status==0 || $taxMe->payAmount!='taxTable'){
					$compute = false;
					if($taxMe->payAmount!='taxTable') $tax = $taxMe->payAmount;
				}
			}
			
			if($payType=='monthly' && $compute==true){
				$payStart = date('Y-m-26', strtotime($info->payPeriodStart.' -1 month'));
				$payEnd = date('Y-m-10', strtotime($info->payPeriodStart));
				
				$prevInfo = $this->dbmodel->getSingleInfo('tcPayslips', 'payslipID, totalTaxable', 
							'empID_fk="'.$info->empID_fk.'" AND payPeriodStart="'.$payStart.'" AND payPeriodEnd="'.$payEnd.'" AND payType="semi" AND status!=3 AND pstatus=1', 
							'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk');
										
				if(!empty($prevInfo)){ //get previous tax
					$prevTax = $this->dbmodel->getSingleField('tcPayslipDetails LEFT JOIN tcPayslipItems ON payID=payItemID_fk', 'payValue', 'payslipID_fk="'.$prevInfo->payslipID.'" AND payAmount="taxTable"');
					$taxableIncome += str_replace(',', '', $prevInfo->totalTaxable);
				}else $payType = 'semi';		
			}
		}
		
		if($compute==true){
			$tax = $this->payrollM->computeTax($payType, $taxableIncome, $taxstatus); 
			$tax -= $prevTax; ///minus tax for previous tax paid
		}
		
		return $tax;	
	}
	
	/*****
		$taxableIncome = base Pay minus deductions
		$taxType = monthly or semi
		$status = passed a number from taxstatus on staffs table
		
		monthly is from 11-25
		semi is from 26-10
	*****/
	public function computeTax($taxType, $taxableIncome, $status){
		$tax = 0;
		$taxableIncome = str_replace(',','',$taxableIncome);
		$status = ((!empty($status))?$this->payrollM->getTaxStatus($status):'');
		
		$info = $this->dbmodel->getSingleInfo('taxTable', 'excessPercent, baseTax, minRange', 'taxType="'.$taxType.'" AND status="'.$status.'" AND minRange<="'.$taxableIncome.'" AND maxRange>"'.$taxableIncome.'"');
		
		if(count($info)>0){
			$perc = (int)$info->excessPercent / 100; 
			$tax = ($taxableIncome - $info->minRange) * $perc; ///(taxable income - tax bracket) x tax %
			$tax = $tax + $info->baseTax; ////add tax base
		}
		
		//return round($tax, 2);
		return $tax;
	}
	
	/***
		$type = hourly or daily
	***/
	public function getDailyHourlyRate($monthlyRate, $type){
		$monthlyRate = str_replace(',', '', $monthlyRate);
		$rate = ($monthlyRate*12) / 365;

		if($type=='daily'){
			return round($rate, 2);
		}else{
			return round(($rate/8), 4);
		}
	}
	
	/**********
		Accepts timeIn, timeOut, schedIn, schedOut
		Computes night differential number of hours
	**********/
	public function getNightDiffTime($q, $holidayDate=''){
		$nightdiff = 0;
		$arr = array(0,1,2,3,4,5,6,22,23);
	
		$start = '0000-00-00 00:00:00';
		$end = '0000-00-00 00:00:00';
		
		//if no schedule change
		if($q->schedIn=='0000-00-00 00:00:00' && $q->timeIn!='0000-00-00 00:00:00' && $q->timeOut!='0000-00-00 00:00:00'){
			$start = date('Y-m-d H:00:00', strtotime($q->timeIn));
			$end = date('Y-m-d H:00:00', strtotime($q->timeOut));			
		}else if($q->timeIn!='0000-00-00 00:00:00' && $q->timeOut!='0000-00-00 00:00:00'){
			if(!empty($holidayDate)){
				$start = date('Y-m-d 00:00:00', strtotime($holidayDate));
				$end = date('Y-m-d 00:00:00', strtotime($holidayDate.' +1 day'));
			}else{
				$start = $q->timeIn;
				$end = $q->timeOut;
			}
		}
		
		$startLate = date('Y-m-d H:15:00', strtotime($start));	
		//while checks if included in array, belongs to the schedule and not late
		while($start<=$end){			
			if(in_array(date('G', strtotime($start)), $arr) && 
				$start >= $q->schedIn && $start <= $q->schedOut &&
				$start<date('Y-m-d H:15:00', strtotime($start))
			){
				$nightdiff++;
			}
			
			$start = date('Y-m-d H:00:00', ( strtotime($start.' +1 hour')));
		}
		
		//minus night diff for the complete 1 hour
		if($nightdiff>0) $nightdiff--;	
		
		///night diff minus 1 hour for 1 hour break
		if($nightdiff>4) $nightdiff--; 
		
		return $nightdiff;
	}
	
	/****
		$staffTaxStatus = is form table staffs taxstatus field
		Tax Statuses on tax table
			- Zero, SorM, SorM1, SorM2, SorM3, SorM4
			//stat is empty or num
	****/
	public function getTaxStatus($staffTaxStatus, $statType=''){
		$stat = 'Zero';
		$num = 0;
		if($staffTaxStatus==1 || $staffTaxStatus==6){ $stat = 'SorM'; $num=0; }
		else if($staffTaxStatus==2 || $staffTaxStatus==7){ $stat = 'SorM1'; $num=1; }
		else if($staffTaxStatus==3 || $staffTaxStatus==8){ $stat = 'SorM2'; $num=2; }
		else if($staffTaxStatus==4 || $staffTaxStatus==9){ $stat = 'SorM3'; $num=3; }
		else if($staffTaxStatus==5 || $staffTaxStatus==10){ $stat = 'SorM4'; $num=4;}	
		
		if($statType=='num') return $num;
		else return $stat;
	}	

	/******
		$activeOnly=1 show only active items
	******/
	public function getPaymentItems($empID, $activeOnly=0, $condition='', $payStart='', $payEnd=''){
		
		if($activeOnly==1) $condition .= ' AND s.status=1';
		
		$first = $condition;
		$second = $condition;

		//determine if semi or monthly
		$pst = '';
		$sst = '';
		if($payStart != ''){
			$pS = date('d', strtotime($payStart));

			if($pS > 25 || $pS < 11){
				//$pst = ' AND p.payPeriod IN ("once", "semi", "per payroll") ';
				//$p = ' AND payPeriod IN ("once", "semi", "per payroll") ';
				$pst = ' AND s.payPeriod IN ("once", "semi", "per payroll")';
				$sst = ' AND payPeriod IN ("once", "semi", "per payroll") ';

			}
			else{
				$pst = ' AND s.payPeriod IN ("once", "monthly", "per payroll")';
				$sst = ' AND payPeriod IN ("once", "monthly", "per payroll") ';
				//$pst = ' AND p.payPeriod IN ("once", "monthly", "per payroll")';
				//$p = ' AND payPeriod IN ("once", "monthly", "per payroll") ';
			}
		}
		$s_string = '';
		$p_string = '';
	
		
		//if(empty($payStart) && empty($payEnd) && $payStart =='0000-00-00' && $payEnd == '0000-00-00'){
		if(!empty($payStart) && !empty($payEnd) && $payStart !='0000-00-00' && $payEnd != '0000-00-00'){
			//$first .= ' AND ((p.payEnd = "0000-00-00") OR (s.payEnd >= "'.$payEnd.'")) ';
			//' AND ((payStart="0000-00-00" AND payEnd="0000-00-00") OR ("'.$payStart.'" AND "'.$payEnd.'" BETWEEN payStart AND payEnd)) ';
			//$second .=  ' AND ((p.payEnd = "0000-00-00") OR (s.payEnd >= "'.$payEnd.'")) ';

			//' AND ((s.payStart="0000-00-00" AND s.payEnd="0000-00-00") OR ("'.$payStart.'" AND "'.$payEnd.'" BETWEEN s.payStart AND s.payEnd)  AND s.payPeriod = "'.$pst.'") ';
			$s_string = 'AND ( 
							(payStart = "0000-00-00" AND payEnd = "0000-00-00") 
							OR ( UNIX_TIMESTAMP(payStart) <= UNIX_TIMESTAMP("'.$payEnd.'") 
									AND UNIX_TIMESTAMP(payEnd) >= UNIX_TIMESTAMP("'.$payEnd.'") 
								) 
						)';

			$p_string = 'AND ( 
							(s.payStart = "0000-00-00" AND s.payEnd = "0000-00-00") OR 
								( UNIX_TIMESTAMP(s.payStart) <= UNIX_TIMESTAMP("'.$payEnd.'") 
									AND UNIX_TIMESTAMP(s.PayEnd) >= UNIX_TIMESTAMP("'.$payEnd.'") 
								) 
						) ';
		} 
		
		/* $sql= 'SELECT payID, payID AS payID_fk, payCode, payName, payType, s.payPercent, s.payAmount AS 	prevAmount, payAmount, payPeriod, payStart, payEnd, payCDto, payCategory, mainItem, status, "0" AS empID_fk, "1" AS isMain  
				FROM tcPayslipItems AS s
				WHERE mainItem = 1 '.$p.' AND payID NOT IN (SELECT payID_fk FROM tcPayslipItemStaffs WHERE empID_fk="'.$empID.'" '.$condition.' ) '.$first.'
				UNION
				SELECT payStaffID AS payID, payID_fk, payCode, payName, payType, p.payPercent, p.payAmount AS prevAmount, s.payAmount, s.payPeriod, s.payStart, s.payEnd, p.payCDto, payCategory, p.mainItem, s.status, empID_fk, "0" AS isMain  
				FROM tcPayslipItemStaffs AS s
				LEFT JOIN tcPayslipItems AS p ON payID_fk=payID WHERE empID_fk="'.$empID.'" '.$condition.' '.$second.' '.$pst.'

				ORDER BY status DESC, isMain DESC, payCategory, payAmount, payType, payName, payStart, payPeriod';
				// SELECT payStaffID AS payID, payID_fk, payCode, payName, payType, p.payPercent, p.payAmount AS prevAmount, s.payAmount, s.payPeriod, s.payStart, s.payEnd, p.payCDto, payCategory, p.mainItem, s.status, empID_fk, "0" AS isMain  
				// FROM tcPayslipItemStaffs AS s
				// LEFT JOIN tcPayslipItems AS p ON payID_fk=payID WHERE (empID_fk = '.$empID.' AND payID_fk IN (32,18) AND s.payEnd = "0000-00-00" AND s.status = 1  AND s.payPeriod = "'.$pst.'") OR empID_fk="'.$empID.'" '.$second.' 
		*/
		$sql = '
		SELECT payID, payID AS payID_fk, payCode, payName, payType, s.payPercent, s.payAmount AS 	prevAmount, payAmount, payPeriod, payStart, payEnd, payCDto, payCategory, mainItem, status, "0" AS empID_fk, "1" AS isMain  
				FROM tcPayslipItems AS s
				WHERE mainItem = 1  '.$sst.' AND payID NOT IN (
					SELECT payID_fk FROM tcPayslipItemStaffs WHERE empID_fk="'.$empID.'"  
					  '. $s_string .'
					) '.$condition.' AND (payEnd = "0000-00-00" OR UNIX_TIMESTAMP(payEnd) >= UNIX_TIMESTAMP("'.$payEnd.'") )
		UNION

		SELECT payStaffID AS payID, payID_fk, payCode, payName, payType, p.payPercent, p.payAmount AS prevAmount, s.payAmount, s.payPeriod, s.payStart, s.payEnd, p.payCDto, payCategory, p.mainItem, s.status, empID_fk, "0" AS isMain 
				FROM tcPayslipItemStaffs as s 
				LEFT JOIN tcPayslipItems AS p 
					ON s.payID_fk = p.payID 
				WHERE empID_fk = '.$empID.'  '.$condition.' '.$pst.' '.$p_string;



		//echo $sql;
		//dd($sql);
		$query = $this->dbmodel->dbQuery($sql);
		// echo "<pre>";
		// var_dump($query->result());
		return $query->result(); 
	}
	
	/****
		$current should have (timeIn,schedIn,timeOut,schedOut)
	****/
	public function getHolidayHours($holidayDate, $current){
		$hr = 0;		
		$holidayStart = date('Y-m-d 00:00:00', strtotime($holidayDate));
		$holidayEnd = date('Y-m-d 24:00:00', strtotime($holidayDate));
		
		if($current->timeIn<=date('Y-m-d H:i:s', strtotime($current->schedIn.' +15 mins'))) $timeInShould = $current->schedIn;
		else $timeInShould = date('Y-m-d H:00:00', strtotime($current->timeIn.' +1 hour'));
		
		if($current->timeOut>=date('Y-m-d H:i:s', strtotime($current->schedOut.' -15 mins'))) $timeOutShould = $current->schedOut;
		else $timeOutShould = date('Y-m-d H:00:00', strtotime($current->timeOut.' +1 hour'));
						
		for($t=$timeInShould; $t<$timeOutShould; ){
			if($t>=$holidayStart && $t<=$holidayEnd)
				$hr++;
			
			$t = date('Y-m-d H:00:00', strtotime($t.' +1 hour'));
		}
		
		if($hr>4) $hr--; //minus 1 hour for break
		
		return $hr;
	}
	
	public function isHoliday($date){
		$holiday = false;
		//$holiday['phWork'] = 0;
		//$holiday['usWork'] = 0;
		
		$holidayType = $this->dbmodel->getSingleField('tcAttendance', 'holidayType', 'dateToday="'.$date.'"');
		if(!empty($holidayType) && $holidayType>0){
			$holiday['date'] = $date;
			$holiday['type'] = $holidayType;
		}else{
			$date = date('Y-m-d', strtotime($date.' +1 day'));
			$holidayType2 = $this->dbmodel->getSingleField('tcAttendance', 'holidayType', 'dateToday="'.$date.'"');
			if(!empty($holidayType2) && $holidayType2>0){
				$holiday['date'] = $date;
				$holiday['type'] = $holidayType2;
			}
		}

		//check who has work on this day
		$whoHasWork = $this->dbmodel->getSingleInfo('staffHolidays', 'phWork, usWork', 'holidayDate = "'.$date.'"');
		if( $whoHasWork ){
			$holiday['phWork'] = $whoHasWork->phWork;
			$holiday['usWork'] = $whoHasWork->usWork;
		}
		
		
		return $holiday;
	}
		
	public function pdfPayslip($empID, $payslipID){		
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
		$pdf = new FPDI();	
		
		$pdf->AddPage();	
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'pdfpayslip.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		
		$pdf->SetFont('Arial','',9);
		$pdf->setTextColor(0, 0, 0);
		
		$payInfo = $this->dbmodel->getSingleInfo('tcPayslips', 
				'payslipID, payrollsID, empID, monthlyRate, basePay, monthlyRate, earning, bonus, tcPayslips.allowance, adjustment, advance, benefit, deduction, totalTaxable, net, payPeriodStart, payPeriodEnd, payType, payDate, fname, lname, idNum, bdate, startDate, title, tcPayrolls.status, staffHolidaySched, levelName', 
				'payslipID="'.$payslipID.'" AND empID_fk="'.$empID.'"', 
				'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position LEFT JOIN orgLevel ON levelID=orgLevel_fk');
	
		if(!empty($payInfo)){
			$catArr = $this->textM->constantArr('payCategory');
			$dataPay = $this->dbmodel->getQueryResults('tcPayslipDetails', 'payID, payCode, payValue, payType, payName, payCategory, numHR, payAmount', 'payslipID_fk="'.$payslipID.'" AND payValue!="0.00"',
					'LEFT JOIN tcPayslipItems ON payID=payItemID_fk', 'payCategory, payAmount, payType');
			
			$dataSum = $this->dbmodel->getSingleInfo('tcPayslips', 'SUM(earning) AS earning, SUM(deduction) AS deduction, SUM(allowance) AS allowance, SUM(adjustment) AS adjustment, SUM(bonus) AS bonus, SUM(advance) AS advance, SUM(benefit) AS benefit, SUM(net) AS net', 'empID_fk="'.$empID.'" AND pstatus=1 AND status<3 AND YEAR(payPeriodEnd)="'.date('Y').'"',
			'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk');
														
			$pdf->setXY(20, 46);
			$pdf->Write(0,$payInfo->idNum); //employee no
			$pdf->setXY(38, 44);
			$pdf->MultiCell(80, 4, $payInfo->lname.', '.$payInfo->fname,0,'C',false); //employee name
			$pdf->setXY(117, 44);
			$pdf->MultiCell(57, 4, date('M d, Y', strtotime($payInfo->payPeriodStart)).' to '.date('M d, Y', strtotime($payInfo->payPeriodEnd)),0,'C',false);  //period
			$pdf->setXY(177, 46);
			$pdf->Write(0,date('M d, Y', strtotime($payInfo->payDate))); //pay date
			
			$pdf->SetFont('Arial','',7);
			$pdf->setXY(15, 53);
			$pdf->Write(0,$payInfo->levelName.' ('.(($payInfo->staffHolidaySched==0)?'PHL':'US').' Holiday Schedule) - '.$payInfo->title); //info between
			
			$earDisplay = "Monthly Rate: ".$payInfo->monthlyRate."\n";
			$earDisplay .= "Daily Rate: ".$this->payrollM->getDailyHourlyRate($payInfo->monthlyRate, 'daily')."\n";
			
			/////$arr = array('pay', 'adjustment', 'advance', 'allowance', 'benefit', 'bonus', 'deduction', 'vacation pay');
			
			$hourRate = $this->payrollM->getDailyHourlyRate($payInfo->monthlyRate, 'hourly');
			$rateDisplay = "\n\n\n";
			$hourDisplay = "\n\n\n";
			$amountDisplay = "\n\n\n";
			
			$otherArr = array();
			foreach($dataPay AS $pay){
				$otherArr[$pay->payCategory][] = $pay;
			}
			
			foreach($otherArr AS $o=>$other){
				if($o!=6){ //not equal to dedution
					$ear = '';
					$hr = '';
					$rate = '';
					$pay = '';
					foreach($other AS $o2){
						$ear .= $o2->payName."\n";
						$pay .= (($o2->payType=="debit")?'-':'').$this->textM->convertNumFormat($o2->payValue)."\n";
											
						if($o2->numHR>0){
							if($o2->payCode=='proRatedAllowance'){
								$allowanceDailyRate = $this->dbmodel->getSingleField('staffSettings', 'settingVal', 'settingName="allowanceDailyRate"');		
								$rate .= $this->textM->convertNumFormat($allowanceDailyRate);											
								$hr .= $o2->numHR.' days';
							}else{
								$rate .= (($o2->payType=="debit")?'-':'').$hourRate;
								if($o2->payCode=='basePay') $hr .= $o2->numHR.' days';
								else $hr .= $o2->numHR.' h';
							}
						}else if($o2->payAmount=='basePay'){
							$rate .= $hourRate;
							$hr .= '-';
						}
						$hr .= "\n";
						$rate .= "\n";
					}
					
					if(!empty($ear)){
						$earDisplay .= "\n".strtoupper($catArr[$o])."\n".$ear;
						$rateDisplay .= "\n".$rate."\n";
						$hourDisplay .= "\n".$hr."\n";
						$amountDisplay .= "\n".$pay."\n";
					}
				}									
			}
									
			$pdf->setXY(12, 68);
			$pdf->MultiCell(50, 4, $earDisplay,0,'L',false);  //earnings
			$pdf->setXY(61, 68);
			$pdf->MultiCell(18, 4, $rateDisplay,0,'R',false);  //hourly rate
			$pdf->setXY(79, 68);
			$pdf->MultiCell(18, 4, $hourDisplay,0,'R',false);  //Hours
			$pdf->setXY(98, 68);
			$pdf->MultiCell(18, 4, $amountDisplay,0,'R',false);  //Amounts
			
			
			//THIS IS FOR THE DEDUCTIONS
			$deduct = "";
			$curr = "";
			$ytd = "";
			
			$deductArr = array();
			foreach($otherArr[6] AS $nobya){
				$deductArr[$nobya->payID] = $nobya;
			}
			
			$deductItems = $this->payrollM->getPaymentItems($empID, 1, ' AND payCategory=6', $payInfo->payPeriodStart, $payInfo->payPeriodEnd);
			$deadArr = array();
			
			foreach($deductItems AS $ded){
				if(!in_array($ded->payID_fk, $deadArr)){
					$deduct .= $ded->payName."\n";
					if(isset($deductArr[$ded->payID_fk])){
						
						$curr .= $this->textM->convertNumFormat($deductArr[$ded->payID_fk]->payValue)."\n";
						array_push($deadArr, $ded->payID_fk);
					}else $curr .= "-\n";
									
					$ytdAmount = $this->payrollM->getTotalDeduction($empID, $ded->payID_fk, $payInfo->payPeriodEnd);
					$ytd .= $this->textM->convertNumFormat($ytdAmount)."\n";
				}
			}
			
			$pdf->setXY(117, 68);
			$pdf->MultiCell(50, 4, $deduct,0,'L',false);  //deductions
			$pdf->setXY(167.5, 68);
			$pdf->MultiCell(18, 4, $curr,0,'R',false);  //curr amounts
			$pdf->setXY(188, 68);
			$pdf->MultiCell(18, 4, $ytd,0,'R',false);  //YTD amounts
						
			$pdf->SetFont('Arial','',8);
			$detailsArr = array('earning'=>'EARNINGS', 'allowance'=>'ALLOWANCES', 'adjustment'=>'ADJUSTMENTS', 'bonus'=>'BONUSES', 'advance'=>'ADVANCES', 'benefit'=>'BENEFITS', 'deduction'=>'DEDUCTIONS');
			$deName = "";
			$deAmount = "";
			$deYTD = "";
			foreach($detailsArr AS $d=>$k){
				if($payInfo->$d!="0.00"){
					$deName .= $k."\n";
					$deAmount .= $this->textM->convertNumFormat($payInfo->$d)."\n";
					if(isset($dataSum->$d)) $deYTD .= $this->textM->convertNumFormat($dataSum->$d)."\n";
				}
			}
			$deName .= "- - - - - - - - - - - - - - - - - - - - -\nNET PAY";
			$deAmount .= "- - - - - - - - - - -\n".$this->textM->convertNumFormat($payInfo->net);
			$deYTD .= "- - - - - - - - - - -\n".$this->textM->convertNumFormat($dataSum->net);
						
			$pdf->setXY(13, 190);
			$pdf->MultiCell(50, 4, $deName,0,'L',false);  //deductions
			
			$pdf->setXY(65, 190);
			$pdf->MultiCell(25, 4, $deAmount,0,'R',false);  //curr amounts
			$pdf->setXY(90, 190);
			$pdf->MultiCell(25, 4, $deYTD,0,'R',false);  //ytd amounts
						
			$pdf->SetFont('Arial','',12);
			$pdf->setXY(132, 203);
			$pdf->MultiCell(62, 10, 'PHP*****'.$this->textM->convertNumFormat($payInfo->net),0,'C',false); //NET
			
			$pdf->SetFont('Arial','',9);
			$pdf->setXY(39, 238);
			$pdf->Write(0,$payInfo->idNum); //employee no		
			$pdf->setXY(28, 242);
			$pdf->Write(0,date('F d, Y', strtotime($payInfo->payDate))); //payable on
			$pdf->setXY(10, 255);
			$pdf->Write(0,$payInfo->lname.', '.$payInfo->fname); //to the order of
			$pdf->setXY(160, 243);
			$pdf->MultiCell(45, 10, 'PHP*****'.$this->textM->convertNumFormat($payInfo->net),0,'C',false); //NET
		}
		
		$pdf->Output('payslip.pdf', 'I');
	}
	
	
	/***
		Getting number of days for end dates
		$exWeekend will determine to exclude weekend or not
	***/
	public function getNumDays($dateStart, $dateEnd, $exWeekend=true){
		$days = 0;
		
		$diff = strtotime($dateEnd) - strtotime($dateStart);
		$days = floor($diff/(60*60*24));
		
		///if end date is Saturday or Sunday minus number of worked days
		if($exWeekend==true){
			$endDateDay = date('l', strtotime($dateEnd));
			if($endDateDay=='Saturday') $days -= 1;
			else if($endDateDay=='Sunday') $days -= 2;
		}
		
		///adding 1 because date start should be included
		return $days+1;
	}
	
	public function getTotalDeduction($empID, $itemID, $periodEnd){
		$val = $this->dbmodel->getSingleField('tcPayslipDetails LEFT JOIN tcPayslipItems ON payID=payItemID_fk LEFT JOIN tcPayslips ON payslipID = payslipID_fk LEFT JOIN tcPayrolls ON payrollsID = payrollsID_fk',
				'SUM(payValue)',
				'tcPayslips.empID_fk="'.$empID.'" AND payItemID_fk="'.$itemID.'" AND tcPayrolls.status!=3 AND tcPayslips.pstatus=1 AND tcPayslipItems.status=1 AND payPeriodEnd<="'.$periodEnd.'" AND YEAR(payPeriodEnd)="'.date('Y', strtotime($periodEnd)).'"');
		
		return $val;
	}
	
	
	/******
		Semi-Monthly is from 26th day of previous month to 10th day of current month
		Monthly is from 11th to 25th day of current month
			$type = 'semi' or 'monthly'			
			returns dates for previous and after 4 months
	******/
	public function getMonthlyPeriod($type){
		$arr = array();
		$dateToday = date('Y-01-01');
		
		$dateprev = date('Y-m-d', strtotime($dateToday));
		$dateafter = date('Y-m-d', strtotime($dateToday.' +12 months'));
		
		$d=$dateprev;
		if($type=='semi'){			
			while($d<=$dateafter){
				$arr[] = array(
					'start' => date('Y-m-26', strtotime($d.' -1 month')),
					'end' => date('Y-m-10', strtotime($d))
				);
				$d = date('Y-m-d', strtotime($d.' +1 month'));
			}
		}else{
			while($d<=$dateafter){
				$arr[] = array(
					'start' => date('Y-m-11', strtotime($d)),
					'end' => date('Y-m-25', strtotime($d))
				);
				$d = date('Y-m-d', strtotime($d.' +1 month'));
			}
		}
		
		return $arr;
	}
	
	/****
		Updating tcStaffLogPublish status
	****/
	public function staffLogStatus($payrollID, $status=''){
		if($status=='final'){
			$condition = 'tcStaffLogPublish.status=1,publishBy=CASE WHEN publishBy="" THEN "system" ELSE publishBy END
						,publishNote=CASE WHEN publishBy="" THEN "Published due to finalized payroll" ELSE publishNote END';
		}else $condition = 'tcStaffLogPublish.status=0';
		
		$this->dbmodel->dbQuery('UPDATE `tcStaffLogPublish` 
									LEFT JOIN tcPayslips ON tcPayslips.empID_fk=tcStaffLogPublish.empID_fk
									LEFT JOIN tcPayrolls ON tcPayslips.payrollsID_fk=payrollsID
									SET '.$condition.'
									WHERE slogDate BETWEEN payPeriodStart AND payPeriodEnd AND payrollsID='.$payrollID);
									
		$queryDates = $this->dbmodel->getSingleInfo('tcPayrolls', 'payPeriodStart, payPeriodEnd', 'payrollsID="'.$payrollID.'"');
		if(!empty($queryDates)){
			$dateStart = $queryDates->payPeriodStart;
			while($dateStart<=$queryDates->payPeriodEnd){
				$this->timeM->cntUpdateFinalizeAttendance($dateStart);
				$dateStart = date('Y-m-d', strtotime($dateStart.' +1 day'));
			}
		}
	}

	/** 
		get payslips items on time range
	**/
	public function getPayslipOnTimeRange( $empID, $from, $to, $add = false ){
		$data = array();
		$data['dateArr'] = $this->payrollM->getArrayPeriodDates($from, $to);	
		$data['dataMonth'] = $this->dbmodel->getQueryResults('tcPayslips', 'payslipID, payDate, basePay, totalTaxable, earning, deduction, net, bonus, adjustment, allowance', 
		'empID_fk="'.$empID.'" AND payDate BETWEEN "'.$from.'" AND "'.$to.'" AND status!=3 AND pstatus=1', 
		'LEFT JOIN tcPayrolls ON payrollsID_fk=payrollsID');
	
		$data['dataMonthItems'] = array();
		$payCode_str = 'AND payCode IN ("taxRefund","philhealth", "sss", "pagIbig", "incomeTax", "regularTaken", "regHoursDeducted", "regHoursAdded", "nightDiff", "overTime", "perfIncentive", "specialPHLHoliday", "regPHLHoliday", "	regUSHoliday", "regHoliday", "regHoursAdded", "nightDiffAdded", "nightDiffSpecialHoliday", "nightDiffRegHoliday")';
		if( $add == true ){
			$payCode_str = '';
		}


		if(count($data['dataMonth'])>0){
			$slipID = '';
			foreach($data['dataMonth'] AS $m){
				$slipID .= $m->payslipID.',';
			}
			if(!empty($slipID)){
				$queryItems = $this->dbmodel->getQueryResults('tcPayslipDetails', 'payslipID_fk, payCode, payValue, payType, payID, payName', 'payslipID_fk IN ('.rtrim($slipID, ',').') '. $payCode_str , 'LEFT JOIN tcPayslipItems ON payID=payItemID_fk');
				
				// echo "<pre>";
				// var_dump($queryItems);

				if(count($queryItems)>0){
					foreach($queryItems AS $item){
						$data['dataMonthItems'][$item->payslipID_fk][$item->payCode] = $item->payValue;
						$data['dataMonthMisc'][$item->payslipID_fk][$item->payCode.'_'.$item->payType.'_'.$item->payID.'_'.$item->payName] = $item->payValue;
					}
				}
			}
		}

		$total_month = array();
		//compute net

		foreach( $data['dataMonth'] as $key_m => $val_m ){
			foreach( $val_m as $key_n => $val_n ){
				if(!isset( $total_month[ $key_n ] ))
					$total_month[ $key_n ] = $val_n;
				else
					$total_month[ $key_n ] += $val_n;
			}
		}
		$allowances = array();
		$deductions = array();
		//compute deductions and allowances
		foreach( $data['dataMonthMisc'] as $key => $val ){
			foreach( $val as $key_ => $val_ ){
				//check if we have instance of the string
				if( (strpos( $key_, 'credit') !== FALSE) OR (strpos( $key_, 'debit') !== FALSE) ){
					$key_e = explode('_', $key_);
					
					if( $key_e[1] == 'credit'){
						if( !isset($allowances[ $key_e[3] ] ) )
							$allowances[ $key_e[3] ] = $val_;
						else
							$allowances[ $key_e[3] ] += $val_;
					}
					if( $key_e[1] == 'debit' ){
						if( !isset( $deductions[ $key_e[3] ] ) )
							$deductions[ $key_e[3] ] = $val_;
						else
							$deductions[ $key_e[3] ] += $val_;
					}	
 				}
				
 			}
 		}
		unset($allowances['Base Pay']);
		if( $add == true ){
			$data['total_month'] = $total_month;
			$data['allowances'] = $allowances;
			$data['deductions'] = $deductions;	
		}



		return $data;
	}
	
	public function generate13thmonth($periodFrom, $periodTo, $empIDs, $includeEndMonth=0){
		$dataEmp = explode(',', rtrim($empIDs, ','));
		$gID = array();
		
		foreach($dataEmp AS $emp){
			$queryPay = $this->payrollM->query13thMonth($emp, $periodFrom, $periodTo, $includeEndMonth);

			if(!empty($queryPay)){
				$pay = 0;
				$deduction = 0;				
				$insArr['totalBasic'] = $pay;
				$insArr['totalDeduction'] = $deduction;
				$insArr['totalAmount'] = 0;
				$insArr['includeEndMonth'] = $includeEndMonth;

				foreach($queryPay AS $ask){
					if(!empty($ask)){
						$pay += $ask->basePay;
						$deduction += ($ask->deduction-$ask->adj);
						$insArr['totalAmount'] += $ask->pay;
					}
				}
 
				$insArr['totalBasic'] = $pay;
				$insArr['totalDeduction'] = $deduction;
				//$insArr['totalAmount'] = ($pay-$deduction)/12;
				$insArr['dateGenerated'] = date('Y-m-d H:i:s');
								
				///check if already exist update if exists else insert
				$yearDate = date('Y');

				$monthID = $this->dbmodel->getSingleField('tc13thMonth', 'tcmonthID', 'empID_fk="'.$emp.'" AND YEAR(periodFrom) = "'.$yearDate.'" ');
				if(!empty($monthID)){
					$gID[] = $monthID;
					$insArr['periodFrom'] = $periodFrom;
					$insArr['periodTo'] = $periodTo;
					$this->dbmodel->updateQuery('tc13thMonth', array('tcmonthID'=>$monthID), $insArr);
				}else{
					$insArr['empID_fk'] = $emp;
					$insArr['periodFrom'] = $periodFrom;
					$insArr['periodTo'] = $periodTo;
					$gID[] = $this->dbmodel->insertQuery('tc13thMonth', $insArr);
				}
			}
		}
		
		return $gID;
	}
	
	public function getArrayPeriodDates($periodFrom, $periodTo){
		$dates = array();
		$payArr = array();
		$pfrom = $periodFrom;
		while($pfrom<=$periodTo){
			$dates[] = date('Y-m-15', strtotime($pfrom));
			$dates[] = date('Y-m-t', strtotime($pfrom));
			$pfrom = date('Y-m-d', strtotime($pfrom.' +1 month'));

		}
		return $dates;
	}
	
	public function query13thMonth($empID, $periodFrom, $periodTo, $includeEndMonth=0){
		$arrayMonths = array();
		
		$info = $this->dbmodel->getSingleInfo('staffs', 'empID, fname, lname, startDate, endDate, sal', 'empID="'.$empID.'"');
		if(count($info)>0){
			$basePay = ($this->textM->decryptText($info->sal));
			//for convert the string to float
			$basePay = (float) str_replace(',','',$basePay);
			$basePay = $basePay/2; 
			
			
			$query = $this->dbmodel->getQueryResults('tcPayrolls', 'empID_fk, payDate, payrollsID_fk, basePay, (SELECT SUM(payValue) FROM tcPayslipDetails LEFT JOIN tcPayslipItems ON payID=payItemID_fk WHERE payslipID_fk=payslipID AND payCode="regHoursAdded") AS "adj" , (SELECT SUM(payValue) FROM tcPayslipDetails LEFT JOIN tcPayslipItems ON payID=payItemID_fk WHERE payslipID_fk=payslipID AND payCategory=0 AND payType="debit") AS deduction', 
								'empID_fk="'.$empID.'" AND payDate BETWEEN "'.$periodFrom.'" AND "'.$periodTo.'" AND tcPayrolls.status!=3 AND pstatus=1',
								'LEFT JOIN tcPayslips ON payrollsID_fk=payrollsID', 'payDate');
								
			foreach($query AS $q){
				//get startdate of the employee
				$p = $this->dbmodel->getSingleInfo('staffs', 'startDate', 'empID = '.$empID);
				$diff = $this->commonM->dateDifference($p->startDate, date('Y-m-d'));

				$computepay = 0;
				if( $diff > 30 )
					$computepay = (($q->basePay+$q->adj)-$q->deduction ) / 12;

				$q->pay = round($computepay,4);
				$payArr[$q->payDate] = $q;
			}

			$lastMonth = '';
			$dates = $this->payrollM->getArrayPeriodDates($periodFrom, $periodTo);

			foreach($payArr as $key => $val){
				if( in_array($key, $dates) ){
					$arrayMonths[$key] = $val;
					$lastMonth = $key;
					//Unset the date on $dates for it to be used if the user is allowed to generate remaining months
					if($includeEndMonth == 1){
						if( $sKey = array_search($key, $dates) ){
							unset($dates[$sKey]);
						}
					}
				}
				else{
					$arrayMonths[$key] = '';
				}
			}

			if( $includeEndMonth == 1){
				foreach($dates as $dateK => $dateV){
					//check if the payroll is not yet generated
					$lp = $this->dbmodel->getSingleInfo('tcPayrolls LEFT JOIN tcPayslips ON payrollsID = payrollsID_fk', 'payrollsID', 'empID_fk = "'.$empID.'"AND payDate = "'.$dateV.'"');
					if(!$lp){
						if($dateV >= $info->startDate){
							$arrayMonths[$dateV] = (object) array('empID_fk'=>$empID, 'payDate'=>$dateV, 'basePay'=>$basePay, 'deduction'=>0, 'pay'=>($basePay/12));
						}
					}
				}
			}

			// echo "<pre>";
			// var_dump($dates);
			// var_dump($payArr);
			// exit();
			// foreach($dates AS $d){
			// 	if(isset($payArr[$d])){
			// 		$arrayMonths[$d] = $payArr[$d];
			// 		$lastMonth = $d;
			// 	}else if(!isset($payArr[$d]) && $includeEndMonth==1 && $lastMonth!='' && $d!=$lastMonth){
			// 		$arrayMonths[$d] = (object) array('empID_fk'=>$empID, 'payDate'=>$d, 'basePay'=>$basePay, 'deduction'=>0, 'pay'=>($basePay/12));
			// 	}else $arrayMonths[$d] = '';
			// }
			//exit();
		}

		return $arrayMonths;
	}
	
	
	public function pdf13thMonth($dataInfo, $dataMonth){
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
		$pdf = new FPDI();	
		
		$pdf->AddPage();	
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'13thmonth.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		
		$pdf->SetFont('Arial','',10);
		$pdf->setTextColor(0, 0, 0);
		
		$pdf->setXY(20, 46);
		$pdf->Write(0, $dataInfo->idNum); //employee id number
		$pdf->setXY(38, 44);
		$pdf->MultiCell(80, 4, $dataInfo->lname.', '.$dataInfo->fname,0,'C',false);  //employee name
		$pdf->setXY(117.5, 44);
		$pdf->MultiCell(57, 4, date('M', strtotime($dataInfo->periodFrom)).' - '.date('M Y', strtotime($dataInfo->periodTo)),0,'C',false);  //period
		
		
		$payDates = '';
		$basePay = '';
		$deduction = '';
		$pay = '';
		
		foreach($dataMonth AS $dates=>$me){
			$payDates .= date('d-M-Y', strtotime($dates))."\n";
			if(!empty($me)){				
				$basePay .= $this->textM->convertNumFormat($me->basePay)."\n";
				$deduction .= $this->textM->convertNumFormat($me->deduction)."\n";
				$pay .= $this->textM->convertNumFormat($me->pay)."\n";
			}else{				
				$basePay .= "0.00\n";
				$deduction .= "0.00\n";
				$pay .= "0.00\n";
			}
		}
		
		$pdf->setXY(11, 68);
		$pdf->MultiCell(48, 5.5, $payDates,0,'C',false); 
		$pdf->setXY(60.5, 68);
		$pdf->MultiCell(48, 5.5, $basePay,0,'C',false); 
		$pdf->setXY(109, 68);
		$pdf->MultiCell(48, 5.5, $deduction,0,'C',false);
		$pdf->setXY(158, 68);
		$pdf->MultiCell(48, 5.5, $pay,0,'C',false); 
		
		//TOTAL
		$pdf->setXY(60, 215);
		$pdf->MultiCell(48, 4, 'PHP '.$this->textM->convertNumFormat($dataInfo->totalBasic),0,'C',false);  //total basic pay
		$pdf->setXY(109, 215);
		$pdf->MultiCell(48, 4, 'PHP '.$this->textM->convertNumFormat($dataInfo->totalDeduction),0,'C',false);  //total deductions
		$pdf->SetFont('Arial','',11);
		$pdf->setXY(158, 215);
		$pdf->MultiCell(48, 4, 'PHP '.$this->textM->convertNumFormat($dataInfo->totalAmount),0,'C',false);  //total pay
		
		$pdf->SetFont('Arial','',10);
		$pdf->setXY(40, 239);
		$pdf->Write(0, $dataInfo->idNum); //employee id number
		$pdf->setXY(10, 256);
		$pdf->Write(0, $dataInfo->lname.', '.$dataInfo->fname); //employee name
		
		$pdf->SetFont('Arial','',11);
		$pdf->setXY(158.5, 249);
		$pdf->MultiCell(48, 4, 'PHP*****'.$this->textM->convertNumFormat($dataInfo->totalAmount),0,'C',false);  //13th month pay
				
		
		$pdf->Output('payslip.pdf', 'I');		
	}
	
	
	public function computeYearlyTaxDue($empID, $incomeTax){
		$tax = 0;
		$incomeTax = str_replace(',', '', $incomeTax);
		
		$dependents = $this->dbmodel->getSingleField('staffs', 'dependents', 'empID="'.$empID.'"');		
		if($dependents>4) $dependents=4;		
		$tax = $incomeTax - (50000) - ($dependents*25000); ///income tax minus personal exemption(50K) and minus (dependents x 25K)		
		
		$taxTable = $this->dbmodel->getSingleInfo('taxTable', 'excessPercent, baseTax, minRange', 'taxType="yearly" AND minRange<="'.$tax.'" AND maxRange>"'.$tax.'"');
		$tax = $tax - $taxTable->minRange; //minus tax bracket
		$tax = $tax * ($taxTable->excessPercent/100); //excess percentage
		$tax = $tax + $taxTable->baseTax; //add base tax		
		
		return $tax;		
	}
	
	
	public function getPeriodComputation($empID, $periodFrom, $periodTo){
		$dates = $this->payrollM->getArrayPeriodDates($periodFrom, $periodTo);		
		$salary = $this->dbmodel->getSingleField('staffs', 'sal', 'empID="'.$empID.'"');
		$salary = ($this->textM->decryptText($salary))/2;
		
		$info = $this->dbmodel->getQueryResults('tcPayslips', 'payDate, basePay, totalTaxable, earning, deduction, net', 
			'empID_fk="'.$empID.'" AND payDate BETWEEN "'.$periodFrom.'" AND "'.$periodTo.'" AND status!=3 AND pstatus=1', 
			'LEFT JOIN tcPayrolls ON payrollsID_fk=payrollsID');
					
		$dateArr = array();
		foreach($info AS $in){
			$dateArr[$in->payDate] = $in;
		}		
		
			
		echo '<pre>';
		print_r($dateArr);
		print_r($dates);
		echo '</pre>';
		exit;
		
	}
	
	//release claim waiver
	public function pdfReleaseClaim($data){

		$indent = 15;
		
		require_once('includes/fpdf/fpdf.php');
		//require_once('includes/fpdf/fpdi.php');		
		require_once('includes/fpdf/fpdf_ext.php');
		
		
		$pdf = new PDF_EXT();
		
		$pdf->AddPage();	
		$pdf->SetMargins(20, 20);
		$pdf->setTextColor(0, 0, 0);	
		
		$pdf->SetFont('Arial','B',9);		
		$pdf->setXY(0, 20);
		$pdf->MultiCell(0, 10, 'RELEASE WAIVER AND QUITCLAIM', 0, 'C', false);		
		$pdf->Ln(4);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(0, 4, 'KNOW ALL MEN BY THESE PRESENTS:', 0, 'L', false); 
		$pdf->Ln(4);
		$pdf->MultiCell(0, 5, 'That I, '.utf8_decode($data->full_name).', Filipino, of legal age, a resident of ________________ ________________________________________________, and formerly employed with Tate Publishing and Enterprises (Philippines), Inc., do by these presents acknowledge receipt of the sum of '.$data->amount_in_words.' ('.$data->amount_in_figure.'), Philippine Currency, from Tate Publishing and Enterprises (Philippines), Inc. in full payment and final settlement of my separation pay, 13th month pay, terminal benefits and accrued employment benefits due to me or which may be due to me from Tate Publishing and Enterprises (Philippines), Inc. under the law or under any existing agreement with respect thereto, as well as any and all claims of whatever kind and nature which I have or may have against Tate Publishing and Enterprises (Philippines), Inc., arising from my employment with and the termination of my employment with Tate Publishing and Enterprises (Philippines), Inc.', 0, 'J', false, $indent); 
		$pdf->Ln(4);
		$pdf->MultiCell(0, 5, 'In consideration of said payment, I do hereby quitclaim, release, discharge and waive any and all actions of whatever nature, expected, real or apparent, which I may have against Tate Publishing and Enterprises (Philippines), Inc., its directors, officers, employees, agents and clients by reason of or arising from my employment with the company. I will institute no action, whether civil, criminal, labor or administrative against Tate Publishing and Enterprises (Philippines), Inc., its directors, officers, employees, agents and clients. Any and all actions which I may have commenced either solely in my name or jointly with others before any office, board, bureau, court, or tribunal against Tate Publishing and Enterprises (Philippines), Inc., its directors, officers, employees, agents and clients are hereby deemed and considered voluntary withdrawn by me and I will no longer testify or continue to prosecute said action(s).', 0, 'J', false, $indent);
		$pdf->Ln(4);
		$pdf->MultiCell(0, 5, 'I declare that I have read this document and have fully understood its contents. I further declare that I voluntarily and willingly executed this Release, Waiver and Quitclaim with full knowledge of my rights under the law.', 0, 'J', false, $indent);
		$pdf->Ln(4);
		$pdf->MultiCell(0, 5, 'IN WITNESS WHEREOF, I have hereunto set my hand at ___________, this ___ day of ____________, 20___.', 0, 'L', false, $indent);
		$pdf->Ln(8);
		$pdf->SetX(120);
		$pdf->SetMargins(20, 20);
		$pdf->MultiCell(0, 5, '       '.$data->full_name.'         ', 'B', 'C', false);
		$pdf->SetX(120);
		$pdf->MultiCell(0, 5, '       Affiant         ', 0, 'C', false);
		$pdf->Ln(8);
		$pdf->MultiCell(0, 5, 'SIGNED IN THE PRESENCE OF', 0, 'C', false, $indent);
		$pdf->MultiCell(0, 5, '____________________   ___________________', 0, 'C', false, $indent);
		$pdf->Ln(10);
		$pdf->MultiCell(0, 5, 'SUBSCRIBED AND SWORN to before me ___ day of __________, 20___ affiant exhibiting to me her Tax Certificate', 0, 'L', false);
		$pdf->MultiCell(0, 5, 'No. ______________________ issued at ________________ on _______________.', 0, 'L', false);
		$pdf->Ln(8);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->MultiCell(0, 5, 'Doc. No. _______; ', 0, 'L', false);
		$pdf->MultiCell(0, 5, 'Page No._______;', 0, 'L', false);
		$pdf->MultiCell(0, 5, 'Book No._______;', 0, 'L', false);
		$pdf->MultiCell(0, 5, 'Series of 20______.', 0, 'L', false);
		$pdf->SetXY( ($x + 100), $y );
		$pdf->Cell(0, 5, 'NOTARY PUBLIC');
		
		
		$pdf->Output('release_waiver_and_quitclaim_'.$data->empID.'.pdf', 'I');	
	}

	public function pdfBIR($data){
		$payInfo = $data['payInfo'];
		$periodFrom = $data['periodFrom'];
		$periodTo = $data['periodTo'];
		$dataBracket = $data['dataBracket'];
		$staffInfo = $data['staffInfo'];
		$dateArr = $data['dateArr'];
		$dataMonth = $data['dataMonth'];
		$dataMonthItems = $data['dataMonthItems'];
		
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
		$pdf = new FPDI();	
		
		$pdf->AddPage();	
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'BIR2316.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 284.07783333333, 367.63325, true);
		$pdf->SetAutoPageBreak(true,1);
			
		//SET DEFAULT FONT TO ARIAL BOLD size 12PT
		$pdf->SetFont('Arial','B',12);
		$pdf->setTextColor(0, 0, 0);
		
		//FOR THE YEAR
		$pdf->setXY(77, 35);
		$pdf->Write(0, date("Y")); 
		//$pdf->Write(0, $pdf->w.' '.$pdf->h); 

		//FOR THE PERIOD
		//FROM

		$z = date('Y', strtotime($staffInfo->startDate) );
		$x = date('Y');

		$birStartDate = ( $z < $x )? '01-01' : date('m-d', strtotime($staffInfo->startDate)) ;
		$birStartDate = explode('-', $birStartDate);

		$pdf->setXY(183, 35);
		$pdf->Write(0, $birStartDate[0]);
		$pdf->setXY(191, 35);
		$pdf->Write(0, $birStartDate[1]); 
		//TO
		$pdf->setXY(227, 35);
		$pdf->Write(0, date('m', strtotime($staffInfo->endDate)));
		$pdf->setXY(235, 35);
		$pdf->Write(0, date('d', strtotime($staffInfo->endDate)));

		//TAXPAYER TIN
		$pdf->setXY(76,47);
		$tin = explode('-', $this->textM->decryptText($staffInfo->tin));
		$pdf->Write(0, $tin[0]);
		$pdf->setXY(94,47);
		$pdf->Write(0, $tin[1]);		
		$pdf->setXY(111,47);
		$pdf->Write(0, $tin[2]);
		$pdf->setXY(127,47);
		$pdf->Write(0, $tin[3]);

		//FOR EMPLOYEE'S NAME
		$pdf->setXY(43,57);
		$pdf->setFont('Arial', 'B', 8);
		$fullName = strtoupper(utf8_decode($staffInfo->fullName));
		$fullName = $this->textM->constantText('', $fullName);
		$pdf->Write(0, $fullName);

		//FOR RDO
		$pdf->setXY(129,57);
		$pdf->setFont('Arial','B',12);
		$pdf->Write(0, '081');		

		//FOR EMPLOYEE'S ADDRESS
		$pdf->setFont('Arial','B',8);
		$pdf->setXY(43,67);
		$pdf->Write(0, strtoupper($staffInfo->address));
		$pdf->setFont('Arial','B',10);
		$pdf->setXY(128, 67);
		$pdf->Write(0, $staffInfo->zip);

		//FOR EMPLOYEES TAX STATUS
		if( isset($staffInfo->taxStatus) ){
			$ts = $staffInfo->taxStatus;
			if($ts < 6){
				$pdf->setXY(62, 107);
			}
			else{
				$pdf->setXY(95, 107);
			}
			$pdf->Write(0, 'x');
		}

		//FOR EMPLOYEE'S BDATE
		$pdf->setXY(45,97);
		$pdf->Write(0, date('m', strtotime($staffInfo->bdate)) );
		$pdf->setXY(55,97);
		$pdf->Write(0, date('d', strtotime($staffInfo->bdate)) );
		$pdf->setXY(65,97);
		$pdf->Write(0, date('Y', strtotime($staffInfo->bdate)) );

		//FOR TATE'S INFORMATION
		//TIN
		$pdf->setXY(76,167);
		$pdf->Write(0, '423');
		$pdf->setXY(94,167);
		$pdf->Write(0, '687');
		$pdf->setXY(111,167);
		$pdf->Write(0, '498');
		$pdf->setXY(127,167);
		$pdf->Write(0, '0000');
		//TATE'S ADDRESS
		$pdf->setXY(45,176);
		$pdf->Write(0, 'TATE PUBLISHING AND ENTERPRISES (PHILIPPINES),');
		$pdf->setXY(45,186);
		$pdf->Write(0, 'SALINAS DRIVE LAHUG  CEBU CITY');
		//ZIP
		$pdf->setXY(128,186);
		$pdf->Write(0, '6000');



 
		/****************************************************************
		**															   **
		**			PAY LOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOP			   **										
		**															   **
		****************************************************************/
		$payDate = '';
		$gross = '';
		$basicSal = '';
		$attendance = '';
		$adjustment = '';
		$taxIncome = '';
		$bonus = '';
		$taxWithheld = '';
		$month13 = '';
		$deminimis = '';
		$netPay = '';
		
		$totalIncome = 0;
		$totalSalary = 0;
		$totalDeduction = 0;
		$totalTaxable = 0;
		$totalTaxWithheld = 0;
		$total13th = 0;
		$totalNet = 0;
		$totalSSS = 0;
		$totalPhilhealth = 0;
		$totalPagIbig = 0;
		$totalBonus = 0;
		$totalAdjustment = 0;
		$totalAllowance = 0;
		$personalExemption = $this->payrollM->computeTaxExemption($staffInfo->taxstatus);	
		
		$salary = $payInfo->monthlyRate;
		$dailyRate = $this->payrollM->getDailyHourlyRate($salary, 'daily');
		$hourlyRate = $this->payrollM->getDailyHourlyRate($salary, 'hourly');
		$leaveAmount = $payInfo->addLeave * $dailyRate;
		$addOnBonus = 0;
		$unusedLeave = 0;
		$sppDeduction = 0;
		
		if(!empty($payInfo->addOns)){
			$addArr = unserialize(stripslashes($payInfo->addOns));
			foreach($addArr AS $add){
				if(isset($add[0]) && isset($add[1]) ) {
					// if( $add[0] == 'Unpaid Bonuses' || $add[0] == 'Unpaid Performance Bonuses' || $add[0] == 'Unpaid Bonus' )
					// 	$addOnBonus += $add[1];
					// if( $rrr = $this->dbmodel->getSingleInfo('tcPayslipAddons', 'tcPayslipAddons_Name', '"'.$add[0].'" LIKE CONCAT("%", tcPayslipAddons_Name, "%")') ){
					// 	$sppDeduction += $add[1];
					// }
					if( $rrr = $this->dbmodel->getSingleInfo('tcPayslipAddons', 'tcPayslipAddons_Name, tcPayslipAddons_Type, tcPayslipAddons_itemType', '"'.$add[0].'" LIKE CONCAT("%", tcPayslipAddons_Name, "%")') ){
						if($rrr->tcPayslipAddons_itemType == 'bonus')
							$addOnBonus += $add[1];

						if($rrr->tcPayslipAddons_itemType == 'sppDeduction')
							$sppDeduction += $add[1];
					}
				}
			}
		}
		
		$payArr = array();
		foreach($dataMonth AS $m){
			$payArr[$m->payDate] = $m;
		}
				
		$month13c = 0;
		$data_items = $this->payrollM->_getTotalComputation( $payArr, $staffInfo, $dateArr, $dataMonth, $dataMonthItems, $payInfo->dateGenerated );
		//$this->textM->aaa($data_items);
		foreach( $data_items as $di_key => $di_val ){
			$$di_key = $di_val;
		}

		/****************************************
		*										*
		*			END OF PAY LOOP				*
		*									    *
		****************************************/
		/*
		* Now you can use variables for:
		* $gross
		* $totalSSS, $totalPhilhealth, $totalPagIbig, $total13th, $totalAllowance, $totalIncome
		*/


		//COMPTATION FOR DEPENDENTS
		// $dependents = $this->payrollM->getTaxStatus($staffInfo->taxstatus, 'num');
		// if($dependents=='') $dependents = 0;
		// else $personalExemption += ($dependents*25000);
		
		//MISC COMPUTATIONS
		$spp = $totalSSS+$totalPhilhealth+$totalPagIbig - $sppDeduction;
		$tsal = $totalSalary - $totalDeduction - $spp;

		$n55 = $tsal+$totalAdjustment;
		$n21 = $n55+$payInfo->add13th+$totalAllowance+$spp;

		$n37 = $payInfo->add13th;
		if($data['is_active'])
			$n37 = $total13th;

		$otherSalary = 0;
		list($totalDeminimis, $totalSalariesAndOtherForms) = $this->payrollM->getDeminimis($data['allowances']);

		$totalSalariesAndOtherForms += $leaveAmount;
		$totalSalariesAndOtherForms += $addOnBonus;
		$totalSalariesAndOtherForms += $unusedLeave;

		if( ($totalSalariesAndOtherForms + $n37) > 82000 ){
			$otherSalary = ($totalSalariesAndOtherForms + $n37) - 82000;
		}
		
		$tnt = $n37+$totalDeminimis+$spp+$totalSalariesAndOtherForms;
		$n23 = $tsal+$totalAdjustment;

		//FOR 21
		$pdf->setXY(94, 229);
		$pdf->Cell(49, 5, $this->textM->convertNumFormat(/*$n21*/$tnt+$n23),0,2,'R');

		//FOR 22
		$pdf->setXY(95, 236);
		$pdf->Cell(48, 5,  $this->textM->convertNumFormat($tnt), 0,2,'R');


		//FOR 23
		$pdf->setXY(95, 242);
		$pdf->Cell(48, 5, $this->formatNum($n23), 0,2,'R');
		
		//FOR 24
		$pdf->setXY(95, 248);
		$pdf->Cell(48, 5, $this->formatNum($payInfo->taxFromPrevious), 0,2,'R');

		//FOR 25
		$n25 = $n23 + $payInfo->taxFromPrevious;
		$pdf->setXY(95, 254);
		$pdf->Cell(48, 5, $this->formatNum($n25), 0,2,'R');		

		//FOR 26
		$n26 = $personalExemption;
		$pdf->setXY(95, 260);
		$pdf->Cell(48, 5, $this->formatNum($n26), 0,2,'R');

		//FOR 27. 27 is always 0
		$pdf->setXY(95, 266);
		$pdf->Cell(48, 5, $this->formatNum('0'), 0,2,'R');	
		
		//FOR 28
		$n28 = $n25-$n26;
		if( $n28 < 0 )
			$n28 = 0;

		$pdf->setXY(95, 272);
		$pdf->Cell(48, 5, $this->formatNum($n28), 0,2,'R');

		//FOR 29, 30A, 30B, and 31
		$n30a = $n30b = $n31 = 0;
		$n29 = $payInfo->taxDue;
		if($data['is_active']){
			if($n55 > $personalExemption){
				$deductedTax = $n55 - $personalExemption;//number 55 and 26 in BIR2316
				$n29 = $this->payrollM->getBIRTaxDue($deductedTax);
			}
		}

		if( $n29 > 0 && $payInfo->taxWithheldFromPrevious == 0 ){
			$n30a = $n31 = $n29;
		}
		if( $n29 > 0 && $payInfo->taxWithheldFromPrevious > 0 ){
			$n31 = $n29;
			$n30b = $payInfo->taxWithheldFromPrevious;
			$n30a = $n29 - $n30b;
		}

		$pdf->setXY(95, 278);
		$pdf->Cell(48, 5, $this->formatNum($n29) , 0,2,'R');

		//FOR 30A
		$pdf->setXY(95, 285);
		$pdf->Cell(48, 5, $this->formatNum($n30a) , 0,2,'R');

		//FOR 30B
		$pdf->setXY(95, 292);
		$pdf->Cell(48, 5, $this->formatNum($n30b) , 0,2,'R');		

		//FOR 31
		//$n31 = $payInfo->taxWithheldFromPrevious+$payInfo->taxWithheld;
		$pdf->setXY(95, 298);
		$pdf->Cell(48, 5, $this->formatNum($n31) , 0,2,'R');

		//FOR 37
		

		$pdf->setXY(194, 100);
		$pdf->Cell(48, 5, $this->formatNum($n37), 0,2,'R');		

		//FOR 38
		$pdf->setXY(194, 110);

		$pdf->Cell(48, 5, $this->formatNum($totalDeminimis), 0,2,'R');

		//FOR 39
		$pdf->setXY(194, 122);
		$pdf->Cell(48, 5, $this->formatNum($spp), 0,2,'R');		

		//FOR 40
		$pdf->setXY(194, 136);
		$pdf->Cell(48, 5, $this->formatNum($totalSalariesAndOtherForms), 0,2,'R');				

		//FOR 41

		$pdf->setXY(194, 147);
		$pdf->Cell(48, 5, $this->formatNum($tnt), 0,2,'R');			

		//FOR 42
		// echo $totalSalary."<br/>";
		// echo $totalDeduction."<br/>";
		// echo $spp."<br/>";
		$tsal = $totalSalary - $totalDeduction - $spp;
		$pdf->setXY(194, 166);
		$pdf->Cell(48, 5, $this->formatNum($tsal), 0,2,'R');			
		
		//FOR 47A
		$pdf->setXY(194, 209);
		$pdf->Cell(48, 5, $this->formatNum($totalAdjustment), 0,2,'R');	

		//FOR 51
		$excs = 0.00;
		if($total13th > 82000)
			$excs = $this->formatNum($otherSalary);
		$pdf->setXY(194, 253);
		$pdf->Cell(48, 5, $excs, 0,2,'R');

		//FOR 55
		
		$pdf->setXY(194, 297);
		$pdf->Cell(48, 5, $this->formatNum($n55), 0,2,'R');

		$utf8Name = $staffInfo->fname."  ".$staffInfo->lname;
		//FOR 56
		$pdf->setXY(65, 309);
		$pdf->Cell(48, 5, "Diana Rose T. Bartulin", 0,2,'R');

		//FOR 56
		$pdf->setXY(55, 317);
		$pdf->Cell(75, 5, utf8_decode($utf8Name), 0,2,'C');

		//FOR 56
		$pdf->setXY(55, 342);
		$pdf->Cell(75, 5, "Diana Rose T. Bartulin", 0,2,'C');

		//FOR 59
		$pdf->setXY(157, 352);
		//$pdf->Write(0, $staffInfo->fname." ".$staffs->mname." ".$staffInfo->lname);
		
		$pdf->Cell(68, 5, utf8_decode($utf8Name), 0,2,'C');

		//OUTPUT PDF
		$pdf->Output('lastpay.pdf', 'I');

	}

	//bir alpha list which is tied to pdfBIR
	public function getAlphaList( $dataQuery, $filename, $startDate, $endDate, $is_active = FALSE ){	
		require_once('includes/excel/PHPExcel/IOFactory.php');
		$fileType = 'Excel5';
		$fileName = 'includes/templates/bir_alphalist_.xls';

		// Read the file
		$objReader = PHPExcel_IOFactory::createReader($fileType);
		$objPHPExcel = $objReader->load($fileName);

		$sequence = 1;
		$cell_counter = 17;
		foreach( $dataQuery as $key => $val ){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$cell_counter, $sequence);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$cell_counter, $this->textM->decryptText( $val->tin ) );
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$cell_counter, strtoupper( $val->lname.', '.$val->fname.' '.$val->mname ) );
			
			$stDate = $val->startDate;
			if( date('Y-m-d', strtotime($stDate) ) < $startDate )
				$stDate = $startDate;

			$objPHPExcel->getActiveSheet()->setCellValue('D'.$cell_counter, $stDate );
			$eD = ($is_active) ? $endDate: $val->endDate; 
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$cell_counter, $eD);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$cell_counter, '6');

			/****************************************************************
			**															   **
			**			PAY LOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOP			   **										
			**															   **
			****************************************************************/
			$payDate = '';
			$gross = '';
			$basicSal = '';
			$attendance = '';
			$adjustment = '';
			$taxIncome = '';
			$bonus = '';
			$taxWithheld = '';
			$month13 = '';
			$deminimis = '';
			$netPay = '';
			
			$totalIncome = 0;
			$totalSalary = 0;
			$totalDeduction = 0;
			$totalTaxable = 0;
			$totalTaxWithheld = 0;
			$total13th = 0;
			$totalNet = 0;
			$totalSSS = 0;
			$totalPhilhealth = 0;
			$totalPagIbig = 0;
			$totalBonus = 0;
			$totalAdjustment = 0;
			$totalAllowance = 0;

			$payArr = array();
			foreach($val->dataMonth AS $m){
				$payArr[$m->payDate] = $m;
			}

			$staffInfo = $this->dbmodel->getSingleInfo('staffs', ' CONCAT(lname, ", ", fname, " ", mname) AS fullName, address, zip, empID, username, tin, 
				idNum, fname, lname, bdate, startDate, endDate, taxstatus, sal, leaveCredits, taxStatus', 'username = "'.$val->username.'"');
			
			$personalExemption = $this->payrollM->computeTaxExemption($staffInfo->taxstatus);

			$month13c = 0;

			$leaveAmount = 0;	
			$addOnBonus = 0;
			$unusedLeave = 0;
			$sppDeduction = 0;

			//for separated employee
			if(!$is_active){
				$payInfo = $this->dbmodel->getSingleInfo('tcLastPay', '*', 'empID_fk = '.$staffInfo->empID);
				$salary = $payInfo->monthlyRate;
				$dailyRate = $this->payrollM->getDailyHourlyRate($salary, 'daily');
				$hourlyRate = $this->payrollM->getDailyHourlyRate($salary, 'hourly');
				$leaveAmount = $payInfo->addLeave * $dailyRate;		
				
				
				if(!empty($payInfo->addOns)){
					$addArr = unserialize(stripslashes($payInfo->addOns));
					foreach($addArr AS $add){
						if(isset($add[0]) && isset($add[1])){
							if( $add[0] == 'Unpaid Bonuses' || $add[0] == 'Unpaid Performance Bonuses' || $add[0] == 'Unpaid Bonus')
								$addOnBonus += $add[1];
							if( $rrr = $this->dbmodel->getSingleInfo('tcPayslipAddons', 'tcPayslipAddons_Name', '"'.$add[0].'" LIKE CONCAT("%", tcPayslipAddons_Name, "%")') ){
								$sppDeduction += $add[1];
							}
						}
					}
				}
			}

			$data_items = $this->payrollM->_getTotalComputation( $payArr, $staffInfo, $val->dateArr, $val->dataMonth, $val->dataMonthItems, $payInfo->dateGenerated );
			foreach( $data_items as $di_key => $di_val ){
				$$di_key = $di_val;
			}

			
			//declare payinfo manually (add13 = total13th, )


			/****************************************
			*										*
			*			END OF PAY LOOP				*
			*									    *
			****************************************/
			/*
			* Now you can use variables for:
			* $gross
			* $totalSSS, $totalPhilhealth, $totalPagIbig, $total13th, $totalAllowance, $totalIncome
			*/

			// $spp = $totalSSS+$totalPhilhealth+$totalPagIbig;
			// $tnt = $this->textM->convertNumFormat($payInfo->add13th+$totalAllowance+$spp);
			// $tsal = $totalSalary - $totalDeduction - $spp;


			$spp = $totalSSS+$totalPhilhealth+$totalPagIbig;
			$tsal = $totalSalary - $totalDeduction - $spp;

			$n55 = $tsal+$totalAdjustment;
			$n21 = $n55+$payInfo->add13th+$totalAllowance+$spp;

			$n37 = $payInfo->add13th;
			if($data['is_active'])
				$n37 = $total13th;

			$totalDeminimis = 0;
			foreach ($val->allowances as $keey => $value) {
				$allowanceArray = $this->textM->constantArr('allowances');
				if(in_array($keey, $allowanceArray)){
					$totalDeminimis += $value;
				}
			}

			$totalDeminimis += $leaveAmount;
			$totalDeminimis += $addOnBonus;
			$totalDeminimis += $unusedLeave;
			
			$tnt = $this->textM->convertNumFormat($n37+$totalDeminimis+$spp);

			
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$cell_counter, $this->formatNum($totalIncome+$leaveAmount+$addOnBonus) );
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$cell_counter, $this->formatNum($total13th) );
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$cell_counter, $this->formatNum($totalDeminimis) );
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$cell_counter, $this->formatNum($spp) );
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$cell_counter, '' );
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $this->formatNum($tnt) );
			$objPHPExcel->getActiveSheet()->setCellValue('L'.$cell_counter, $this->formatNum($tsal) );

			$excs = '';
			if($total13th > 82000)
				$excs = $this->formatNum($total13th-82000);

			$objPHPExcel->getActiveSheet()->setCellValue('M'.$cell_counter, $this->formatNum($excs) );
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$cell_counter, $this->formatNum($totalAdjustment));
			$n55 = $tsal+$totalAdjustment;
			$objPHPExcel->getActiveSheet()->setCellValue('O'.$cell_counter, $this->formatNum($n55) );

			$taxstatus = $this->textM->constantArr('taxstatus');
			preg_match('#\((.*?)\)#', $taxstatus[$staffInfo->taxStatus], $match);

			$objPHPExcel->getActiveSheet()->setCellValue('P'.$cell_counter, $match[1]);

			$n26 = $dependents+$personalExemption;
			$objPHPExcel->getActiveSheet()->setCellValue('Q'.$cell_counter, $this->formatNum($n26));


			$objPHPExcel->getActiveSheet()->setCellValue('R'.$cell_counter, $this->formatNum(0));

			$n25 = ($tsal+$totalAdjustment) + $payInfo->taxFromPrevious;

			$n28 = $n25-$n26;
			if($n28 < 0)
				$n28 = 0;
			$objPHPExcel->getActiveSheet()->setCellValue('S'.$cell_counter, $this->formatNum($n28));

			$n30a = $n30b = $n31 = 0;
			$n29 = $payInfo->taxDue;

			if( $n29 > 0 && $payInfo->taxWithheldFromPrevious == 0 ){
				$n30a = $n31 = $n29;
			}
			if( $n29 > 0 && $payInfo->taxWithheldFromPrevious > 0 ){
				$n31 = $n29;
				$n30b = $payInfo->taxWithheldFromPrevious;
				$n30a = $n29 - $n30b;
			}

			$objPHPExcel->getActiveSheet()->setCellValue('T'.$cell_counter, $this->formatNum($n29) );

			$objPHPExcel->getActiveSheet()->setCellValue('U'.$cell_counter, $this->formatNum(0) );

			$objPHPExcel->getActiveSheet()->setCellValue('V'.$cell_counter, $this->formatNum($n31) );

			$objPHPExcel->getActiveSheet()->setCellValue('W'.$cell_counter, $this->formatNum(0) );

			$objPHPExcel->getActiveSheet()->setCellValue('X'.$cell_counter, $this->formatNum($n31) );
			$objPHPExcel->getActiveSheet()->setCellValue('Y'.$cell_counter, 'Y' );
			
			$cell_counter++;
			$sequence++;
		}
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
		ob_end_clean();
		// We'll be outputting an excel file
		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="'.$filename.'".xls"');
		$objWriter->save('php://output');
	}

	public function getAlphaListForAllEmployee($data, $outputFile){
		// echo "<pre>";
		// var_dump($data);
		require_once('includes/excel/PHPExcel/IOFactory.php');
		$fileType = 'Excel5';
		$fileName = 'includes/templates/alphalist_template.xls';

		// Read the file
		$objReader = PHPExcel_IOFactory::createReader($fileType);
		$objPHPExcel = $objReader->load($fileName);

		$sequence = 1;
		$cell_counter = 10;
		$data_items = $this->payrollM->getTotalComputationForAllEmployee($data);

		foreach ($data_items as $key => $value) {
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$cell_counter, $sequence);
			foreach ($value as $k => $v) {
				$objPHPExcel->getActiveSheet()->setCellValue($k.$cell_counter, $v );
			}
			$sequence++;
			$cell_counter++;
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
		ob_end_clean();
		// We'll be outputting an excel file
		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="'.$outputFile.'".xls"');
		$objWriter->save('php://output');
	}

	public function getAlphaListForEmployeeWithPrevious($data, $outputFile){
		require_once('includes/excel/PHPExcel/IOFactory.php');
		$fileType = 'Excel5';
		$fileName = 'includes/templates/alphalist_with_previous.xls';

		// Read the file
		$objReader = PHPExcel_IOFactory::createReader($fileType);
		$objPHPExcel = $objReader->load($fileName);

		$sequence = 1;
		$cell_counter = 12;
		$data_items = $this->payrollM->getTotalComputationForAllEmployee($data);

		foreach ($data_items as $key => $value) {
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$cell_counter, $sequence);
			$totalGross = 0;
			$forAA = 0;

			foreach ($value as $l => $v) {
				$k = $l;
				switch ($l) {
					case 'F': $totalGross += str_replace(',', '', $v);
					case 'G': $k = 'P'; break;
					case 'H': $k = 'Q'; break;
					case 'L': $k = 'T'; break;
					case 'M': $k = 'V'; break;
					case 'N': $k = 'W'; break;
					case 'R': $k = 'X'; break;
					case 'S': $k = 'Y'; break;
					case 'T': $k = 'Z'; break;
					case 'U': $k = 'W'; break;
					case 'V': $k = 'AC'; break;
					case 'W': $k = 'AD'; break;
					case 'AC': $k = 'AI'; break;
					case 'AE' : $k = 'AI'; break;
				}
				$objPHPExcel->getActiveSheet()->setCellValue($k.$cell_counter, $v );
			}
			$totalGross += $data[$key]->for21;

			$forAB = $data[$key]->for55 + str_replace(',', '', $value['U']);

			$objPHPExcel->getActiveSheet()->setCellValue('F'.$cell_counter, $totalGross );
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$cell_counter, $data[$key]->for37 );
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$cell_counter, $data[$key]->for38 );
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$cell_counter, $data[$key]->for39 );
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$cell_counter, $data[$key]->for40 );
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $data[$key]->for41 );
			$objPHPExcel->getActiveSheet()->setCellValue('L'.$cell_counter, $data[$key]->for42 );
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$cell_counter, $data[$key]->for47A );
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$cell_counter, $data[$key]->for51 );
			$objPHPExcel->getActiveSheet()->setCellValue('O'.$cell_counter, $data[$key]->for55 );
			$objPHPExcel->getActiveSheet()->setCellValue('AA'.$cell_counter, $value['U'] );
			$objPHPExcel->getActiveSheet()->setCellValue('AB'.$cell_counter, $forAB );
			$objPHPExcel->getActiveSheet()->setCellValue('AH'.$cell_counter, $data[$key]->for31 );

			//compute taxDue
			$forAG = 0;
			$deductedTax = 0;

			if($forAB > $value['W']){
				$deductedTax = $forAB - str_replace(',', '', $value['W']);
				$forAG = $this->payrollM->getBIRTaxDue($deductedTax);
			}

			$objPHPExcel->getActiveSheet()->setCellValue('AF'.$cell_counter, $deductedTax );
			$objPHPExcel->getActiveSheet()->setCellValue('AG'.$cell_counter, $forAG );

			$colLetter = 'AJ';
			$AE = str_replace(',', '', $value['AE']);
			$colVal = ( $forAG  - ($data[$key]->for31+ $AE) );

			$forAL = $AE + $colVal;

			if($colVal < 0){
				$forAL = $AE-$colVal;
				$colLetter = -1*($colVal);
			}

			$objPHPExcel->getActiveSheet()->setCellValue($colLetter.$cell_counter, $colVal );
			$objPHPExcel->getActiveSheet()->setCellValue('AL'.$cell_counter, $forAL );

			$sequence++;
			$cell_counter++;
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
		ob_end_clean();
		// We'll be outputting an excel file
		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="'.$outputFile.'".xls"');
		$objWriter->save('php://output');
	}

	public function pdfActiveBIR($info){
		$activeBIR = $this->payrollM->getTotalComputationForAllEmployee($info);
		$staffInfo = $info[0];

		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
		$pdf = new FPDI();	
		
		$pdf->AddPage();	
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'BIR2316.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 284.07783333333, 367.63325, true);
		$pdf->SetAutoPageBreak(true,1);
			
		//SET DEFAULT FONT TO ARIAL BOLD size 12PT
		$pdf->SetFont('Arial','B',12);
		$pdf->setTextColor(0, 0, 0);
		
		//FOR THE YEAR
		$pdf->setXY(77, 35);
		$pdf->Write(0, date("Y")); 
		//$pdf->Write(0, $pdf->w.' '.$pdf->h); 

		//FOR THE PERIOD
		//FROM

		$z = date('Y', strtotime($staffInfo->startDate) );
		$x = date('Y');

		$birStartDate = ( $z < $x )? '01-01' : date('m-d', strtotime($staffInfo->startDate)) ;
		$birStartDate = explode('-', $birStartDate);

		$pdf->setXY(183, 35);
		$pdf->Write(0, $birStartDate[0]);
		$pdf->setXY(191, 35);
		$pdf->Write(0, $birStartDate[1]); 
		//TO
		$pdf->setXY(227, 35);
		$pdf->Write(0, date('m', strtotime($staffInfo->endDate)));
		$pdf->setXY(235, 35);
		$pdf->Write(0, date('d', strtotime($staffInfo->endDate)));

		//TAXPAYER TIN
		$pdf->setXY(76,47);
		$tin = explode('-', $this->textM->decryptText($staffInfo->tin));
		$pdf->Write(0, $tin[0]);
		$pdf->setXY(94,47);
		$pdf->Write(0, $tin[1]);		
		$pdf->setXY(111,47);
		$pdf->Write(0, $tin[2]);
		$pdf->setXY(127,47);
		$pdf->Write(0, $tin[3]);

		//FOR EMPLOYEE'S NAME
		$pdf->setXY(43,57);
		$pdf->setFont('Arial', 'B', 8);
		$fullName = strtoupper(utf8_decode($staffInfo->fullName));
		$fullName = $this->textM->constantText('', $fullName);
		$pdf->Write(0, $fullName);

		//FOR RDO
		$pdf->setXY(129,57);
		$pdf->setFont('Arial','B',12);
		$pdf->Write(0, '081');		

		//FOR EMPLOYEE'S ADDRESS
		$pdf->setFont('Arial','B',8);
		$pdf->setXY(43,67);
		$pdf->Write(0, strtoupper($staffInfo->address));
		$pdf->setFont('Arial','B',10);
		$pdf->setXY(128, 67);
		$pdf->Write(0, $staffInfo->zip);

		//FOR EMPLOYEES TAX STATUS
		if( isset($staffInfo->taxStatus) ){
			$ts = $staffInfo->taxStatus;
			if($ts < 6){
				$pdf->setXY(62, 107);
			}
			else{
				$pdf->setXY(95, 107);
			}
			$pdf->Write(0, 'x');
		}

		//FOR EMPLOYEE'S BDATE
		$pdf->setXY(45,97);
		$pdf->Write(0, date('m', strtotime($staffInfo->bdate)) );
		$pdf->setXY(55,97);
		$pdf->Write(0, date('d', strtotime($staffInfo->bdate)) );
		$pdf->setXY(65,97);
		$pdf->Write(0, date('Y', strtotime($staffInfo->bdate)) );

		//FOR TATE'S INFORMATION
		//TIN
		$pdf->setXY(76,167);
		$pdf->Write(0, '423');
		$pdf->setXY(94,167);
		$pdf->Write(0, '687');
		$pdf->setXY(111,167);
		$pdf->Write(0, '498');
		$pdf->setXY(127,167);
		$pdf->Write(0, '0000');
		//TATE'S ADDRESS
		$pdf->setXY(45,176);
		$pdf->Write(0, 'TATE PUBLISHING AND ENTERPRISES (PHILIPPINES),');
		$pdf->setXY(45,186);
		$pdf->Write(0, 'SALINAS DRIVE LAHUG  CEBU CITY');
		//ZIP
		$pdf->setXY(128,186);
		$pdf->Write(0, '6000');

		//FOR 21
		$pdf->setXY(94, 229);
		$pdf->Cell(49, 5, $this->textM->convertNumFormat($activeBIR[0]['F']),0,2,'R');

		//FOR 22
		$pdf->setXY(95, 236);
		$pdf->Cell(48, 5, $this->textM->convertNumFormat($activeBIR[0]['N']), 0,2,'R');


		$n23 = $n25 = $activeBIR[0]['U'];
		$n24 = 0;	
		$n29 = $activeBIR[0]['AB'];

		if( $staffInfo->for55 > 0){
			//recompute tax due stuffs
			$n24 = $staffInfo->for55;
			$n25 = str_replace(',', '', $n23)+$n24;

			if($n25 > $staffInfo->taxExemption){
				$deductedTax = $n25 - $staffInfo->taxExemption;//number 55 and 26 in BIR2316
				$n29 = $this->payrollM->getBIRTaxDue($deductedTax);
			}
		}			

		//FOR 23
		$pdf->setXY(95, 242);
		$pdf->Cell(48, 5, $this->formatNum($n23), 0,2,'R');


		//FOR 24
		$pdf->setXY(95, 248);
		$pdf->Cell(48, 5, $this->formatNum($n24), 0,2,'R');

		//FOR 25
		$pdf->setXY(95, 254);
		$pdf->Cell(48, 5, $this->formatNum($n25), 0,2,'R');		
		//FOR 26
		$pdf->setXY(95, 260);
		$pdf->Cell(48, 5, $this->formatNum($activeBIR[0]['W']), 0,2,'R');

		//FOR 27. 27 is always 0
		$pdf->setXY(95, 266);
		$pdf->Cell(48, 5, $this->formatNum('0'), 0,2,'R');	
		
		//FOR 28
		$n25 = str_replace(',', '', $activeBIR[0]['U']);
		$n26 = str_replace(',', '', $activeBIR[0]['W']);

		$n28 = $n25 - $n26;

		if( $n28 < 0 )
			$n28 = 0;
		
		$pdf->setXY(95, 272);
		$pdf->Cell(48, 5, $this->formatNum($activeBIR[0]['AA']), 0,2,'R');

		// $n30a = $n30b = $n31 = 0;

		// if($n55 > $personalExemption){
		// 	$deductedTax = $n55 - $personalExemption;//number 55 and 26 in BIR2316
		// 	$n29 = $this->payrollM->getBIRTaxDue($deductedTax);
		// }

		// if( $n29 > 0 && $payInfo->taxWithheldFromPrevious == 0 ){
		// 	$n30a = $n31 = $n29;
		// }
		// if( $n29 > 0 && $payInfo->taxWithheldFromPrevious > 0 ){
		// 	$n31 = $n29;
		// 	$n30b = $payInfo->taxWithheldFromPrevious;
		// 	$n30a = $n29 - $n30b;
		// }


		//FOR 29, 30A, 30B, and 31
		$pdf->setXY(95, 278);
		$pdf->Cell(48, 5, $this->formatNum($n29) , 0,2,'R');


		$n30a = $activeBIR[0]['AC'];
		$n30b = $staffInfo->for31;
		$n31 = str_replace(',', '', $n30a)+$n30b;

		//FOR 30A
		$pdf->setXY(95, 285);
		$pdf->Cell(48, 5, $this->formatNum($n30a) , 0,2,'R');

		//FOR 30B
		$pdf->setXY(95, 292);
		$pdf->Cell(48, 5, $this->formatNum($staffInfo->for31) , 0,2,'R');		

		//FOR 31
		$pdf->setXY(95, 298);
		$pdf->Cell(48, 5, $this->formatNum($n31) , 0,2,'R');

		//FOR 37
		$pdf->setXY(194, 100);
		$pdf->Cell(48, 5, $this->formatNum($activeBIR[0]['G']), 0,2,'R');		

		//FOR 38
		$pdf->setXY(194, 110);

		$pdf->Cell(48, 5, $this->formatNum($activeBIR[0]['H']), 0,2,'R');

		//FOR 39
		$pdf->setXY(194, 122);
		$pdf->Cell(48, 5, $this->formatNum($activeBIR[0]['L']), 0,2,'R');		

		//FOR 40
		$pdf->setXY(194, 136);
		$pdf->Cell(48, 5, $this->formatNum($activeBIR[0]['M']), 0,2,'R');				

		//FOR 41

		$pdf->setXY(194, 147);
		$pdf->Cell(48, 5, $this->formatNum($activeBIR[0]['N']), 0,2,'R');			

		//FOR 42
		$pdf->setXY(194, 166);
		$pdf->Cell(48, 5, $this->formatNum($activeBIR[0]['R']), 0,2,'R');			
		
		//FOR 47A
		$pdf->setXY(194, 209);
		$pdf->Cell(48, 5, $this->formatNum($activeBIR[0]['T']), 0,2,'R');	

		//FOR 51
		$pdf->setXY(194, 253);
		$pdf->Cell(48, 5,  $this->formatNum($activeBIR[0]['S']), 0,2,'R');

		//FOR 55
		
		$pdf->setXY(194, 297);
		$pdf->Cell(48, 5, $this->formatNum($activeBIR[0]['U']), 0,2,'R');

		$utf8Name = $staffInfo->fname."  ".$staffInfo->lname;
		//FOR 56
		$pdf->setXY(65, 309);
		$pdf->Cell(48, 5, "Diana Rose T. Bartulin", 0,2,'R');

		//FOR 56
		$pdf->setXY(55, 317);
		$pdf->Cell(75, 5, utf8_decode($utf8Name), 0,2,'C');

		//FOR 56
		$pdf->setXY(55, 342);
		$pdf->Cell(75, 5, "Diana Rose T. Bartulin", 0,2,'C');

		//FOR 59
		$pdf->setXY(157, 352);
		//$pdf->Write(0, $staffInfo->fname." ".$staffs->mname." ".$staffInfo->lname);
		
		$pdf->Cell(68, 5, utf8_decode($utf8Name), 0,2,'C');

		$pdf->Output('bir2316.pdf', 'I');



	}

	public function taxSummary($data, $outputFile, $insertToDB = FALSE){
		require_once('includes/excel/PHPExcel/IOFactory.php');
		$fileType = 'Excel5';
		$fileName = 'includes/templates/taxSummary.xls';
		
		// echo "<pre>";
		// var_dump($data);
		// exit();

		// Read the file
		$objReader = PHPExcel_IOFactory::createReader($fileType);
		$objPHPExcel = $objReader->load($fileName);

		$cell_counter = 7;

		$objPHPExcel->getActiveSheet()->setCellValue('A2', $data->empID);
		$objPHPExcel->getActiveSheet()->mergeCells('C2:E2');
		$objPHPExcel->getActiveSheet()->setCellValue('C2', $data->fname.' '.$data->lname);
		$objPHPExcel->getActiveSheet()->mergeCells('G2:H2');
		$objPHPExcel->getActiveSheet()->setCellValue('G2', $data->dateRange);

		$payslipAddAdjustments = $this->textM->constantArr('payslipAddAdjustments');

		$totals = array(
			'grossIncome' => 0,
			'basePay' => 0,
			'otherCompensation' => 0,
			'regTaken' => 0,
			'sss' => 0,
			'pagIbig' => 0,
			'philhealth' => 0,
			'allowance' => 0,
			'incentives' => 0,
			'totalTaxable' => 0 
					);

		//get payment dates
		$payDates = $this->payrollM->getArrayPeriodDates(date('Y-01-01'), date('Y-12-t'));
		$dateArr = array();

		foreach ($data->dataMonth as $eK => $eV) {
			$regTaken = isset( $data->dataMonthItems[$eV->payslipID]["regularTaken"] )? $data->dataMonthItems[$eV->payslipID]["regularTaken"]:0;
			$regHoursAdded = isset( $data->dataMonthItems[$eV->payslipID]["regHoursAdded"] )? $data->dataMonthItems[$eV->payslipID]["regHoursAdded"]:0;
			$regHoursDeducted = isset( $data->dataMonthItems[$eV->payslipID]["regHoursDeducted"] )? $data->dataMonthItems[$eV->payslipID]["regHoursDeducted"]:0;
			$deduction = ($regTaken - $regHoursAdded + $regHoursDeducted);

			$month13 += ( ( $eV->basePay + $regHoursAdded )- $regTaken - $regHoursDeducted ) / 12;
			
			$earning = $eV->earning;

			$totals['sss'] += $sss = isset( $data->dataMonthItems[$eV->payslipID]["sss"] )? $data->dataMonthItems[$eV->payslipID]["sss"]:0;
			$totals['pagIbig'] += $pagIbig = isset( $data->dataMonthItems[$eV->payslipID]["pagIbig"] )? $data->dataMonthItems[$eV->payslipID]["pagIbig"]:0;
			$totals['philhealth'] += $philhealth = isset( $data->dataMonthItems[$eV->payslipID]["philhealth"] )? $data->dataMonthItems[$eV->payslipID]["philhealth"]:0;
			$unusedLeave = isset( $data->dataMonthItems[$eV->payslipID]["unusedLeave"] )? $data->dataMonthItems[$eV->payslipID]["unusedLeave"]:0;
			$totalIncomeTax = isset( $data->dataMonthItems[$eV->payslipID]["incomeTax"] )? $data->dataMonthItems[$eV->payslipID]["incomeTax"]:0;

			$incentives = 0;
			$allowanceKey = $this->textM->constantArr('otherAllowancesInKey');
			foreach ( $allowanceKey as $v ) {
				if(array_key_exists($v, $data->dataMonthItems[$eV->payslipID])){
					$incentives += $data->dataMonthItems[$eV->payslipID][$v];
				}
			}
			if(isset( $data->dataMonthItems[$eV->payslipID]["incomeTax"] )){
				$dateArr[$eV->payDate] = $data->dataMonthItems[$eV->payslipID]["incomeTax"];
			}
			else{
				$dateArr[$eV->payDate] = 0;
			}


			$spp = $pagIbig+$philhealth+$sss;

			//$tSalary = $eV->basePay;
			//$basicPay = $tSalary-$deduction-$spp;
			$otherCompensation = 0;

			foreach ($data->dataMonthItems[$eV->payslipID] as $mK => $mV) {
				if(in_array($mK, $payslipAddAdjustments)){
					$otherCompensation += $mV;
				}
			}

			$totals['grossIncome'] += $grossIncome = $eV->basePay + $otherCompensation + $eV->allowance - $regTaken;
			$totals['basePay'] += $eV->basePay;
			$totals['otherCompensation'] += $otherCompensation;
			$totals['regTaken'] += $regTaken;
			$totals['allowance'] += $eV->allowance;
			$totals['incentives'] += $incentives;
			$totals['totalTaxable'] += $eV->totalTaxable;

			if( $cell_counter  > 7 ){
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($cell_counter,1);
			}
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$cell_counter, date('F d, Y', strtotime($eV->payDate)));
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$cell_counter, $grossIncome);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$cell_counter, $eV->basePay);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$cell_counter, $otherCompensation);

			$objPHPExcel->getActiveSheet()->getStyle('E'.$cell_counter.':H'.$cell_counter)->applyFromArray( array( 'font' => array('color' => array('rgb' => 'ff0000') ) ) );
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$cell_counter, $regTaken*-1);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$cell_counter, $sss*-1);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$cell_counter, $pagIbig*-1);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$cell_counter, $philhealth*-1);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$cell_counter, $eV->allowance);
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$cell_counter, $incentives);
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $eV->totalTaxable);


			$cell_counter++;
		}

		$objPHPExcel->getActiveSheet()->setCellValue('B'.$cell_counter, $month13);			
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$cell_counter, $month13);

		$cell_counter += 1;

		//Totals
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$cell_counter, $totals['grossIncome']);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$cell_counter, $totals['basePay']);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$cell_counter, $totals['otherCompensation']);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$cell_counter, $totals['regTaken']*-1);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$cell_counter, $totals['sss']*-1);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$cell_counter, $totals['pagIbig']*-1);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$cell_counter, $totals['philhealth']*-1);
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$cell_counter, $totals['allowance']);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$cell_counter, $month13+$totals['incentives']);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $totals['totalTaxable']);

		$cell_counter += 1;

		$total13thAndIncentives = $month13+$totals['incentives'];
		$totalDifference = 0;
		//$adjustment for 13thmonth pay exceeding 82,000
		if( ($total13thAndIncentives) > 82000 ){
			$totalDifference = $total13thAndIncentives - 82000;
			$total13thAndIncentives = 82000;
		}

		$totals['totalTaxable'] += $totalDifference;

		$objPHPExcel->getActiveSheet()->setCellValue('D'.$cell_counter, $totalDifference);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$cell_counter, $totalDifference*-1);

		$cell_counter += 1;
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$cell_counter, $totals['grossIncome']);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$cell_counter, $totals['basePay']);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$cell_counter, $totals['otherCompensation'] + $totalDifference);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$cell_counter, $totals['regTaken']*-1);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$cell_counter, $totals['sss']*-1);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$cell_counter, $totals['pagIbig']*-1);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$cell_counter, $totals['philhealth']*-1);
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$cell_counter, $totals['allowance']);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$cell_counter, $total13thAndIncentives);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $totals['totalTaxable']);

		$cell_counter += 2;
		//for Taxable compensation from previous employer
		$taxFromPrevious = $data->for55? $data->for55: 0;
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $taxFromPrevious);

		//get totaltaxable
		$cell_counter += 1;
		$totalTaxable = $totals['totalTaxable']+$taxFromPrevious;
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter,$totalTaxable );
		
		//get tax excemption
		$cell_counter += 1;
		$taxExemption = $data->taxExemption;
		$objPHPExcel->getActiveSheet()->getStyle('K'.$cell_counter)->applyFromArray( array( 'font' => array('color' => array('rgb' => 'ff0000') ) ) );
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, '('.$this->formatNum($taxExemption).')');

		//get net taxable income (totaltax - taxexcemption)
		$cell_counter += 1;
		$netTaxable = $totalTaxable - $taxExemption;
		$range = $netTaxable;
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $range );

		//get tax bracket
		$cell_counter += 1;

		$s = 0;
		if($range > 0)
			$s = $range;
		
		$taxBracket = $this->dbmodel->getSingleInfo('taxTable', 'excessPercent, baseTax, minRange', 'taxType="yearly" AND "'.$s.'" BETWEEN minRange AND maxRange');
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $taxBracket->minRange);

		//get excess of tax base
		$cell_counter += 1;
		$excessTax = $s - $taxBracket->minRange;
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $excessTax);

		//get multiply
		$cell_counter += 1;
		$mulplyBy = $taxBracket->excessPercent/100;
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $mulplyBy);

		//get percent of excess 
		$cell_counter += 1;
		$percentOfExcess = $excessTax * $mulplyBy;
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $percentOfExcess);	

		//get add basic tax
		$cell_counter += 1;
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $taxBracket->baseTax);

		//get tax due
		$cell_counter += 1;
		$taxDue = $this->payrollM->getBIRTaxDue($netTaxable);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $taxDue);	

		
		$cell_counter += 4;
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $data->for30B);		

		$cell_counter += 1;

		foreach ($payDates as $d) {
			if( !array_key_exists($d, $dateArr)){
				$dateArr[$d] = 0;
			}
		}
		ksort($dateArr);
		$incomeTaxTotal = 0;
		foreach ($dateArr as $dateK => $dateV) {
			$incomeTaxTotal += $dateV;
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $dateV);
			$cell_counter++;
		}

		// $objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $totalDifference);

		// $cell_counter += 1;

		$incomeTaxTotal += $data->for30B;
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $incomeTaxTotal);

		$cell_counter += 1;
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $taxDue);

		$cell_counter += 1;
		$ITR = ($incomeTaxTotal) - $taxDue;
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$cell_counter, $ITR);

		$cell_counter += 4;
		$objPHPExcel->getActiveSheet()->mergeCells('H'.$cell_counter.':J'.$cell_counter.'');
		$objPHPExcel->getActiveSheet()->getStyle('H'.$cell_counter.':J'.$cell_counter.'')->applyFromArray( array( 'font' => array('bold' => true ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ) ) );
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$cell_counter, $data->fname.' '.$data->lname);

		if(!$insertToDB){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
			ob_end_clean();
			// We'll be outputting an excel file
			header('Content-type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename="'.$outputFile.'".xls"');
			$objWriter->save('php://output');
		}
		else{
			$insertArray = array(
									'empID_fk' => $data->empID,
									'totalTaxableIncome' => $totalTaxable,
									'taxDue' => ($taxDue)?$taxDue:0,
									'incomeTaxWithheld' => $incomeTaxTotal,
									'taxRefund' => $ITR,
									'taxSummaryDate' => $data->taxSummaryDate
								);
			if( $this->dbmodel->getSingleInfo('tcTaxSummary', 'taxSummaryDate', 'empID_fk = '.$data->empID) )
				$this->dbmodel->updateQuery('tcTaxSummary', 'empID_fk = '.$data->empID, $insertArray);
			else
				$this->dbmodel->insertQuery('tcTaxSummary', $insertArray);
		}
	}

	public function getTotalComputationForAllEmployee($info){

		$items = array();
		$payslipAddAdjustments = $this->textM->constantArr('payslipAddAdjustments');
		$payslipDeductAdjustments = $this->textM->constantArr('payslipDeductAdjustments');

		$empCount = 0;
	
		foreach ($info as $key => $value) {
			$items[$empCount]['B'] = $this->textM->decryptText( $value->tin );
			$items[$empCount]['C'] = $value->lname;
			$items[$empCount]['D'] = $value->fname;
			$items[$empCount]['E'] = $value->mname;
			
			$earning = 0;
			$month13 = 0;
			$sss = 0;
			$pagIbig = 0;
			$philhealth = 0;
			$totalSalary = 0;
			$totalDeduction = 0;
			$unusedLeave = 0;
			$leaveAmount = 0;
			$regHoursAdded = 0;
			$addOnBonus = 0;
			$regHoursDeducted = 0;
			$sppDeduction = 0;
			$totalAdjustment = 0;
			$totalIncomeTax = 0;

			//loop for earning
			foreach ($value->dataMonth as $eK => $eV) {
				$regTaken = isset( $value->dataMonthItems[$eV->payslipID]["regularTaken"] )? $value->dataMonthItems[$eV->payslipID]["regularTaken"]:0;
				$regHoursAdded = isset( $value->dataMonthItems[$eV->payslipID]["regHoursAdded"] )? $value->dataMonthItems[$eV->payslipID]["regHoursAdded"]:0;
				$regHoursDeducted = isset( $value->dataMonthItems[$eV->payslipID]["regHoursDeducted"] )? $value->dataMonthItems[$eV->payslipID]["regHoursDeducted"]:0;
				$totalDeduction += ($regTaken - $regHoursAdded + $regHoursDeducted);

				$month13 += ( ( $eV->basePay + $regHoursAdded )- $regTaken - $regHoursDeducted ) / 12;
				
				$earning += $eV->earning;

				$sss += isset( $value->dataMonthItems[$eV->payslipID]["sss"] )? $value->dataMonthItems[$eV->payslipID]["sss"]:0;
				$pagIbig += isset( $value->dataMonthItems[$eV->payslipID]["pagIbig"] )? $value->dataMonthItems[$eV->payslipID]["pagIbig"]:0;
				$philhealth += isset( $value->dataMonthItems[$eV->payslipID]["philhealth"] )? $value->dataMonthItems[$eV->payslipID]["philhealth"]:0;
				$unusedLeave += isset( $value->dataMonthItems[$eV->payslipID]["unusedLeave"] )? $value->dataMonthItems[$eV->payslipID]["unusedLeave"]:0;
				$totalIncomeTax += isset( $value->dataMonthItems[$eV->payslipID]["incomeTax"] )? $value->dataMonthItems[$eV->payslipID]["incomeTax"]:0;

				$totalSalary += $eV->basePay;
			}

			// echo "<pre>";
			// var_dump($value->dataMonthItems);
			

			foreach ($value->dataMonthItems as $mK => $mV) {
				foreach ($mV as $mmK => $mmV) {
					if(in_array($mmK, $payslipAddAdjustments)){
						$totalAdjustment += $mmV;
					}
					// elseif(in_array($mmK, $payslipDeductAdjustments)){
					// 	echo '<strong>'.$mmK.':</strong> '.$mmV.' -  Deduct <br/>';
					// 	$totalAdjustment -= $mmV;
					// }
				}
			}

			$earning += $month13;

			$spp = $sss + $pagIbig + $philhealth - ($sppDeduction);

			$otherSalary = 0;
			list($totalDeminimis, $totalSalariesAndOtherForms) = $this->payrollM->getDeminimis($value->allowances);
			$totalSalariesAndOtherForms += $leaveAmount;
			$totalSalariesAndOtherForms += $unusedLeave;

			if ( ($month13 + $totalSalariesAndOtherForms) > 82000){
				$otherSalary = ($month13 + $totalSalariesAndOtherForms) - 82000;
				$totalSalariesAndOtherForms = $totalSalariesAndOtherForms - $otherSalary;
			}

			$basicPay = $totalSalary-$totalDeduction-$spp;

			$totalNonTax = $month13 + $totalDeminimis + $spp + $totalSalariesAndOtherForms;

			$totalTaxableIncome = $basicPay + $totalAdjustment + $otherSalary;

			$items[$empCount]['F'] = $this->payrollM->formatNum($totalNonTax+$totalTaxableIncome);
			$items[$empCount]['G'] = $this->payrollM->formatNum($month13);
			
			$items[$empCount]['H'] = $this->payrollM->formatNum($totalDeminimis);
			$items[$empCount]['L'] = $this->payrollM->formatNum($spp);
			$items[$empCount]['M'] = $this->payrollM->formatNum($totalSalariesAndOtherForms);

			$items[$empCount]['N'] = $this->payrollM->formatNum($totalNonTax);

			//basicPay = totalSalary - totaldeduction - spp
			
			$items[$empCount]['R'] = $this->payrollM->formatNum($basicPay);
			$items[$empCount]['S'] = $this->payrollM->formatNum($otherSalary);
			$items[$empCount]['T'] = $this->payrollM->formatNum($totalAdjustment);

			
			$items[$empCount]['U'] = $this->payrollM->formatNum($totalTaxableIncome);	

			$taxstatus = $this->textM->constantArr('taxstatus')[$value->taxstatus];
			preg_match('#\((.*?)\)#', $taxstatus, $match);
			
			$items[$empCount]['V'] = $match[1];
			$items[$empCount]['W'] = $this->payrollM->formatNum($value->taxExemption);

			$netTaxable = $totalTaxableIncome-$value->taxExemption;
			if( $netTaxable < 0 )
				$netTaxable = 0;

			$items[$empCount]['AA'] = $this->payrollM->formatNum($netTaxable);

			$taxDue = 0;
			if($totalTaxableIncome > $value->taxExemption){
				$deductedTax = $totalTaxableIncome - $value->taxExemption;//number 55 and 26 in BIR2316
				$taxDue = $this->payrollM->getBIRTaxDue($deductedTax);
			}
			
			$items[$empCount]['AB'] = $this->payrollM->formatNum($taxDue);
			$items[$empCount]['AC'] = $this->payrollM->formatNum($totalIncomeTax);

			//if AB < AC = AB - AC else the other way around
			$yearEndAdjustment = $taxDue - $totalIncomeTax;
			$yColumn = 'AD';
			if( $yearEndAdjustment < 0){
				$yearEndAdjustment *= -1;
				$yColumn = 'AE';
			}

			$items[$empCount][$yColumn] = $this->payrollM->formatNum($yearEndAdjustment);
			$items[$empCount]['AF'] = $this->payrollM->formatNum($taxDue);

			$empCount++;
		}
		return $items;
	}


	
	public function formatNum($d){
		return $this->textM->convertNumFormat($d);
	}

	public function getDeminimis($allowances){

		$totalDeminimis = 0;
		$totalSalariesAndOtherForms = 0;

		foreach ($allowances as $key => $value) {
			$allowanceArray = $this->textM->constantArr('deminimisAllowance');
			$otherAllowanceArray = $this->textM->constantArr('otherAllowance');
			if(in_array($key, $allowanceArray)){
				$totalDeminimis += $value;
			}
			else if(in_array($key, $otherAllowanceArray)){
				$totalSalariesAndOtherForms += $value;
			}
		}
		return array($totalDeminimis, $totalSalariesAndOtherForms);
	}

	public function _getTotalComputation( $payArr, $staffInfo, $dateArr, $dataMonth, $dataMonthItems, $dateGenerated = '0000-00-00' ){
		$data = array();
		$data['gross'] = '';
		$data['basicSal'] = '';
		$data['attendance'] = '';
		$data['adjustment'] = '';
		$data['taxIncome'] = '';
		$data['bonus'] = '';
		$data['taxWithheld'] = '';
		$data['month13'] = '';
		$data['deminimis'] = '';
		$data['netPay'] = '';
		$data['payDate'] = '';
		$data['totalIncome'] = 0;
		$data['totalSalary'] = 0;
		$data['totalDeduction'] = 0;
		$data['totalTaxable'] = 0;
		$data['totalTaxWithheld'] = 0;
		$data['total13th'] = 0;
		$data['totalNet'] = 0;
		$data['totalSSS'] = 0;
		$data['totalPhilhealth'] = 0;
		$data['totalPagIbig'] = 0;
		$data['totalBonus'] = 0;
		$data['totalAdjustment'] = 0;
		$data['totalAllowance'] = 0;
		//$data['personalExemption'] = $this->payrollM->computeTaxExemption($staffInfo->taxstatus);
		$data['incomeTax'] = 0;
		$data['month13c'] = 0;
		$data['regTaken'] = 0;
		$data['unusedLeave'] = 0;

		$flag = 0;

		foreach($dateArr AS $date){
			$data['payDate'] .= date('d-M-Y', strtotime($date))."\n";
			
			if(isset($payArr[$date])){
				$data['regTaken'] = ((isset($dataMonthItems[$payArr[$date]->payslipID]['regularTaken']))?$dataMonthItems[$payArr[$date]->payslipID]['regularTaken']:'0.00');

				$data['regHoursAdded'] = ((isset($dataMonthItems[$payArr[$date]->payslipID]['regHoursAdded']))?$dataMonthItems[$payArr[$date]->payslipID]['regHoursAdded']:'0.00');

				$data['regHoursDeducted'] = ((isset($dataMonthItems[$payArr[$date]->payslipID]['regHoursDeducted']))?$dataMonthItems[$payArr[$date]->payslipID]['regHoursDeducted']:'0.00');				

				$data['refundCostofVaccines'] = ((isset($dataMonthItems[$payArr[$date]->payslipID]['refundCostofVaccines']))?$dataMonthItems[$payArr[$date]->payslipID]['refundCostofVaccines']:'0.00');

				$data['idReplacement'] = ((isset($dataMonthItems[$payArr[$date]->payslipID]['idReplacement']))?$dataMonthItems[$payArr[$date]->payslipID]['idReplacement']:'0.00');

				$data['payslipAdjustment'] = ((isset($dataMonthItems[$payArr[$date]->payslipID]['payslipAdjustment']))?$dataMonthItems[$payArr[$date]->payslipID]['payslipAdjustment']:'0.00');

				$data['incomeTax'] = ((isset($dataMonthItems[$payArr[$date]->payslipID]['incomeTax']))?'-'.$dataMonthItems[$payArr[$date]->payslipID]['incomeTax']:'0.00');
				
				$data['totalSSS'] += ((isset($dataMonthItems[$payArr[$date]->payslipID]['sss']))?$dataMonthItems[$payArr[$date]->payslipID]['sss']:0);
				$data['totalPhilhealth'] += ((isset($dataMonthItems[$payArr[$date]->payslipID]['philhealth']))?$dataMonthItems[$payArr[$date]->payslipID]['philhealth']:0);
				$data['totalPagIbig'] += ((isset($dataMonthItems[$payArr[$date]->payslipID]['pagIbig']))?$dataMonthItems[$payArr[$date]->payslipID]['pagIbig']:0);
				
				//add all adjustments
				//"nightDiff", "overTime", "perfIncentive", "specialPHLHoliday", "regPHLHoliday", "	regUSHoliday", "regHoliday", "regHoursAdded", "nightDiffAdded", "nightDiffSpecialHoliday", "nightDiffRegHoliday"
				// if( !$flag && $payArr[$date]->adjustment > 0 ){
				// 	$payArr[$date]->adjustment = 0;
				// 	$flag = 1;
				// }


				//This update does not affect previously computed last pay
				if( $dateGenerated != '0000-00-00' && $dateGenerated > '2016-07-10'){
					$data['regTaken'] = ($data['regTaken'] - $data['regHoursAdded']) + $data['regHoursDeducted'];
				}

				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['nightDiff']))?$dataMonthItems[$payArr[$date]->payslipID]['nightDiff']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['overTime']))?$dataMonthItems[$payArr[$date]->payslipID]['overTime']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['perfIncentive']))?$dataMonthItems[$payArr[$date]->payslipID]['perfIncentive']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['specialPHLHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['specialPHLHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['regPHLHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['regPHLHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['regUSHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['regUSHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['regHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['regHoliday']:0;
				
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['nightDiffAdded']))?$dataMonthItems[$payArr[$date]->payslipID]['nightDiffAdded']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['nightDiffSpecialHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['nightDiffSpecialHoliday']:0;
				
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['nightDiffRegHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['nightDiffRegHoliday']:0;

				$payArr[$date]->adjustment -= (isset($dataMonthItems[$payArr[$date]->payslipID]['regHoursAdded']))?$dataMonthItems[$payArr[$date]->payslipID]['regHoursAdded']:0;

				$payArr[$date]->adjustment -= (isset($dataMonthItems[$payArr[$date]->payslipID]['refundCostofVaccines']))?$dataMonthItems[$payArr[$date]->payslipID]['refundCostofVaccines']:0;
				

				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['idReplacement']))?$dataMonthItems[$payArr[$date]->payslipID]['idReplacement']:0;

				$payArr[$date]->adjustment -= (isset($dataMonthItems[$payArr[$date]->payslipID]['payslipAdjustment']))?$dataMonthItems[$payArr[$date]->payslipID]['payslipAdjustment']:0;


				$data['unusedLeave'] += (isset($dataMonthItems[$payArr[$date]->payslipID]['unusedLeave']))?$dataMonthItems[$payArr[$date]->payslipID]['unusedLeave']:0;



				if( isset($dataMonthItems[$payArr[$date]->payslipID]['taxRefund']) AND $dataMonthItems[$payArr[$date]->payslipID]['taxRefund'] > 0 ){
					$payArr[$date]->earning -= $dataMonthItems[$payArr[$date]->payslipID]['taxRefund'];
				}
								
				//13th month computation = (basepay-deduction)/12 NO 13th month if end date before Jan 25
				if( $staffInfo->active OR ($staffInfo->endDate >= date('Y').'-01-25' && $this->commonM->dateDifference($staffInfo->startDate,$staffInfo->endDate ) > 30) ){
					//if( isset($payArr[$date]->deduction) ){
					$data['month13c'] = ($payArr[$date]->basePay - $data['regTaken'] /*$payArr[$date]->deduction*/)/12;
					//}
				}		

				$data['gross'] .= $this->textM->convertNumFormat($payArr[$date]->earning + $data['month13c'])."\n";
				$data['basicSal'] .= $this->textM->convertNumFormat($payArr[$date]->basePay)."\n";

				//This formula applies only to future computations
				if( $dateGenerated != '0000-00-00' && $dateGenerated > '2016-07-10'){
					$data['attendance'] .= $this->textM->convertNumFormat($data['regTaken'])."\n";
				}
				else{
					$data['attendance'] .= $this->textM->convertNumFormat($data['regTaken'])."\n";
				}
				$data['adjustment'] .= $this->textM->convertNumFormat($payArr[$date]->adjustment)."\n";
				$data['taxIncome'] .= $this->textM->convertNumFormat($payArr[$date]->totalTaxable)."\n";
				$data['bonus'] .= $this->textM->convertNumFormat($payArr[$date]->bonus)."\n";
				$data['deminimis'] .= $this->textM->convertNumFormat($payArr[$date]->allowance)."\n";
				$data['taxWithheld'] .= $data['incomeTax']."\n";		

				$data['month13'] .= $this->textM->convertNumFormat($data['month13c'])."\n";

				$data['netPay'] .= $this->textM->convertNumFormat($payArr[$date]->net)."\n";
									
				$data['totalIncome'] += ($payArr[$date]->earning + $data['month13c']) - $data['refundCostofVaccines'] - $data['payslipAdjustment'] - $data['regHoursDeducted'] + $data['idReplacement'];
				//echo $payArr[$date]->earning.'-'.$data['totalIncome'].'<br/>';
				$data['totalSalary'] += $payArr[$date]->basePay;
				$data['totalDeduction'] += $data['regTaken'];
				$data['totalTaxable'] += $payArr[$date]->totalTaxable;					
				$data['totalTaxWithheld'] += $data['incomeTax'];	
				$data['totalBonus'] += $payArr[$date]->bonus;
				$data['totalAdjustment'] += $payArr[$date]->adjustment;
				$data['totalAllowance'] += $payArr[$date]->allowance;
				$data['total13th'] += $data['month13c'];					
				$data['totalNet'] += $payArr[$date]->net;					
			}else{
				$data['gross'] .= "0.00\n";
				$data['basicSal'] .= "0.00\n";
				$data['attendance'] .= "0.00\n";
				$data['adjustment'] .= "0.00\n";
				$data['taxIncome'] .= "0.00\n";
				$data['bonus'] .= "0.00\n";
				$data['taxWithheld'] .= "0.00\n";
				$data['month13'] .= "0.00\n";
				$data['deminimis'] .= "0.00\n";
				$data['netPay'] .= "0.00\n";
			}
		}
		return $data;
	}
	
	public function pdfLastPay($data){
		$payInfo = $data['payInfo'];
		$periodFrom = $data['periodFrom'];
		$periodTo = $data['periodTo'];
		$dataBracket = $data['dataBracket'];
		$staffInfo = $data['staffInfo'];
		$dateArr = $data['dateArr'];
		$dataMonth = $data['dataMonth'];
		$dataMonthItems = $data['dataMonthItems'];
		
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
		$pdf = new FPDI();	
		
		$pdf->AddPage();
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'lastpayV2.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
			
		$pdf->SetFont('Arial','',10);
		$pdf->setTextColor(0, 0, 0);
				
		$pdf->setXY(20, 46);
		$pdf->Write(0, $staffInfo->idNum); //employee id number
		$pdf->setXY(38, 44);
		$pdf->MultiCell(80, 4, utf8_decode($staffInfo->lname.', '.$staffInfo->fname),0,'C',false);  //employee name
		$pdf->setXY(117.5, 44);
		$pdf->MultiCell(44, 4, date('F d, Y', strtotime($staffInfo->startDate)),0,'C',false);  //start date
		$pdf->setXY(162, 44);
		$pdf->MultiCell(44, 4, (($staffInfo->endDate!='0000-00-00')?date('F d, Y', strtotime($staffInfo->endDate)):'Not yet determined'),0,'C',false);  //end date
	
		$payDate = '';
		$gross = '';
		$basicSal = '';
		$attendance = '';
		$adjustment = '';
		$taxIncome = '';
		$bonus = '';
		$taxWithheld = '';
		$month13 = '';
		$deminimis = '';
		$netPay = '';
		
		$totalIncome = 0;
		$totalSalary = 0;
		$totalDeduction = 0;
		$totalTaxable = 0;
		$totalTaxWithheld = 0;
		$total13th = 0;
		$totalNet = 0;
		$totalSSS = 0;
		$totalPhilhealth = 0;
		$totalPagIbig = 0;
		$totalBonus = 0;
		$totalAdjustment = 0;
		$totalAllowance = 0;
		$personalExemption = 50000;	
		
		$salary = $payInfo->monthlyRate;
		$dailyRate = $this->payrollM->getDailyHourlyRate($salary, 'daily');
		$hourlyRate = $this->payrollM->getDailyHourlyRate($salary, 'hourly');
		$leaveAmount = $payInfo->addLeave * $dailyRate;		
		
		
		$payArr = array();
		foreach($dataMonth AS $m){
			$payArr[$m->payDate] = $m;
		}
		$month13c = 0;
		$data_items = $this->payrollM->_getTotalComputation( $payArr, $staffInfo, $dateArr, $dataMonth, $dataMonthItems, $payInfo->dateGenerated );
		
		foreach( $data_items as $di_key => $di_val ){
			$$di_key = $di_val;
		}
		
		$dependents = $this->payrollM->getTaxStatus($staffInfo->taxstatus, 'num');
		if($dependents=='') $dependents = 0;
		else $personalExemption += ($dependents*25000);

		// $deminimis = 0;
		// foreach ($data['allowances'] as $keey => $value) {
		// 	$allowanceArray = $this->textM->constantArr('allowances');
		// 	if(in_array($keey, $allowanceArray)){
		// 		$deminimis .= $this->textM->convertNumFormat($value)."\n";
		// 	}
		// }
				
		$pdf->SetFont('Arial','',6.5);
		$pdf->setXY(11, 78); $pdf->MultiCell(18, 3.7, $payDate,0,'C',false);  //Payslip Date
		$pdf->setXY(29, 78); $pdf->MultiCell(18, 3.7, $gross,0,'C',false);  //Gross Income
		$pdf->setXY(46.5, 78); $pdf->MultiCell(18, 3.7, $basicSal,0,'C',false);  //Basic Salary
		$pdf->setXY(63.8, 78); $pdf->MultiCell(18, 3.7, $attendance,0,'C',false);  //Attendance Adjustments
		$pdf->setXY(82, 78); $pdf->MultiCell(18, 3.7, $adjustment,0,'C',false);  //Adjustment
		$pdf->setXY(100, 78); $pdf->MultiCell(18, 3.7, $taxIncome,0,'C',false);  //TAxable Income
		$pdf->setXY(117.5, 78); $pdf->MultiCell(18, 3.7, $bonus,0,'C',false);  //bonus
		$pdf->setXY(135.5, 78); $pdf->MultiCell(18, 3.7, $taxWithheld,0,'C',false);  //tax withheld
		$pdf->setXY(153, 78); $pdf->MultiCell(18, 3.7, $month13,0,'C',false);  //13th Month Pay
		$pdf->setXY(171, 78); $pdf->MultiCell(18, 3.7, $deminimis,0,'C',false);  //De minimis
		$pdf->setXY(188, 78); $pdf->MultiCell(18, 3.7, $netPay,0,'C',false);  //NET Pay
		
		$pdf->SetFont('Arial','B',7);
		$pdf->setXY(29, 168.5); $pdf->MultiCell(18, 3.7, $this->textM->convertNumFormat($totalIncome),0,'C',false);
		$pdf->setXY(46.5, 168.5); $pdf->MultiCell(18, 3.7, $this->textM->convertNumFormat($totalSalary),0,'C',false);
		$pdf->setXY(63.8, 168.5); $pdf->MultiCell(18, 3.7, $this->textM->convertNumFormat($totalDeduction),0,'C',false);
		$pdf->setXY(82, 168.5); $pdf->MultiCell(18, 3.7, $this->textM->convertNumFormat($totalAdjustment),0,'C',false);
		$pdf->setXY(100, 168.5); $pdf->MultiCell(18, 3.7, $this->textM->convertNumFormat($totalTaxable),0,'C',false);
		$pdf->setXY(117.5, 168.5); $pdf->MultiCell(18, 3.7, $this->textM->convertNumFormat($totalBonus),0,'C',false);
		$pdf->setXY(135.5, 168.5); $pdf->MultiCell(18, 3.7, $this->textM->convertNumFormat($totalTaxWithheld),0,'C',false);
		$pdf->setXY(153, 168.5); $pdf->MultiCell(18, 3.7, $this->textM->convertNumFormat($payInfo->add13th),0,'C',false);
		$pdf->setXY(171, 168.5); $pdf->MultiCell(18, 3.7, $this->textM->convertNumFormat($totalAllowance),0,'C',false);
		$pdf->setXY(188, 168.5); $pdf->MultiCell(18, 3.7, $this->textM->convertNumFormat($totalNet),0,'C',false);
		
		
		///COMPUTATION OF TAX DUE
		if(count($dataBracket)>0){
			$taxBracket = $dataBracket->minRange;
			$excessTax = $payInfo->taxNetTaxable-$dataBracket->minRange;
			$percenta = $dataBracket->excessPercent/100;
			$excessPer = $excessTax * $percenta;
			$baseTax = $dataBracket->baseTax;
		}		
		$leftTaxDue = "Taxable Compensation from Previous Employer\n";
		$leftTaxDue .= "Current Taxable Income\n";
		$leftTaxDue .= "Total Taxable Income\n";
		$leftTaxDue .= "LESS-EXEMPTION\n";
		$leftTaxDue .= "     Personal Exemption\n";
		$leftTaxDue .= "     Dependents\n";
		$leftTaxDue .= "NET Taxable Income\n";
		$leftTaxDue .= "Tax Bracket\n";
		$leftTaxDue .= "Excess of Tax Base\n";
		$leftTaxDue .= "Multiply By\n";
		$leftTaxDue .= "Percentage of Excess\n";
		$leftTaxDue .= "Add Basic Tax\n";
				
		$rightTaxDue = $this->textM->convertNumFormat($payInfo->taxFromPrevious)."\n";
		$rightTaxDue .= $this->textM->convertNumFormat($payInfo->taxFromCurrent)."\n";
		$rightTaxDue .= $this->textM->convertNumFormat($payInfo->taxFromPrevious + $payInfo->taxFromCurrent)."\n\n";
		$rightTaxDue .= $this->textM->convertNumFormat($personalExemption)."\n";
		$rightTaxDue .= $dependents."\n";
		$rightTaxDue .= $this->textM->convertNumFormat($payInfo->taxNetTaxable)."\n";
		$rightTaxDue .= ((isset($taxBracket))?$this->textM->convertNumFormat($taxBracket):'0.00')."\n";
		$rightTaxDue .= ((isset($excessTax))?'-'.$this->textM->convertNumFormat($excessTax):'0.00')."\n";
		$rightTaxDue .= ((isset($percenta))?$this->textM->convertNumFormat($percenta):'0.00')."\n";
		$rightTaxDue .= ((isset($excessPer))?$this->textM->convertNumFormat($excessPer):'0.00')."\n";
		$rightTaxDue .= ((isset($baseTax))?$this->textM->convertNumFormat($baseTax):'0.00')."\n";
				
		$pdf->SetFont('Arial','',7);
		$pdf->setXY(15, 183.5); $pdf->MultiCell(60, 3.6, $leftTaxDue,0,'L',false);
		$pdf->setXY(75, 183.5); $pdf->MultiCell(60, 3.6, $rightTaxDue,0,'L',false);		
		
		$pdf->SetFont('Arial','B',8);
		$pdf->setXY(15, 229); $pdf->Write(0, 'Tax Due for '.date('Y', strtotime($payInfo->dateTo))); //tax due
		$pdf->setXY(75, 229); $pdf->Write(0, $this->textM->convertNumFormat($payInfo->taxDue)); //tax due
		
		///WITHHOLDING TAX ALLOCATION
		$leftAlloc = "Income Tax Due for the Year\n";	
		$leftAlloc .= "Income Tax Withheld\n";
		$rightAlloc = $this->textM->convertNumFormat($payInfo->taxDue)."\n";
		$rightAlloc .= $this->textM->convertNumFormat($payInfo->taxWithheld)."\n";
		
		$pdf->SetFont('Arial','',7);
		$pdf->setXY(15, 244.5); $pdf->MultiCell(60, 4, $leftAlloc,0,'L',false);
		$pdf->setXY(75, 244.5); $pdf->MultiCell(60, 4, $rightAlloc,0,'L',false);
		
		$pdf->SetFont('Arial','B',8);
		$pdf->setXY(15, 257); $pdf->Write(0, 'Tax '.(($payInfo->taxRefund<0)?'Deficit':'Refund').' for the year '.date('Y', strtotime($payInfo->dateTo))); //tax due
		$pdf->setXY(75, 257); $pdf->Write(0, $this->textM->convertNumFormat($payInfo->taxRefund)); //tax due
		
		 
		//PAGE 2
		$pdf->AddPage();
		$tplIdx = $pdf->importPage(2);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);	
		
		///ADD ONS
		$leftAdd = '';
		$rightAdd = '';
		
		if($payInfo->add13th>0) $leftAdd .= "13th Month Pay\n";		
		$leftAdd .= "Unused Leave Credits\n";
		$leftAdd .= "Unpaid Salary\n";		
		
		if($payInfo->add13th>0) $rightAdd .= $this->textM->convertNumFormat($payInfo->add13th)."\n";
		$rightAdd .= $this->textM->convertNumFormat($leaveAmount)." (".$payInfo->addLeave." remaining leave credits x ".$dailyRate." daily rate)\n";
		$rightAdd .= $this->textM->convertNumFormat($payInfo->addUnpaid * $hourlyRate)." (".$payInfo->addUnpaid." hours x ".$hourlyRate.")\n";

		if(!empty($payInfo->addOns)){
			$addArr = unserialize(stripslashes($payInfo->addOns));
			foreach($addArr AS $add){
				if(isset($add[0]) && isset($add[1])){
					$leftAdd .= ucwords($add[0])."\n";
					$rightAdd .= $this->textM->convertNumFormat($add[1])."\n";
				}
			}
		}
		
		$pdf->SetFont('Arial','',8);
		$pdf->setXY(15, 20); $pdf->MultiCell(60, 4, $leftAdd,0,'L',false);
		$pdf->setXY(75, 20); $pdf->MultiCell(80, 4, $rightAdd,0,'L',false);
		
		$pdf->SetFont('Arial','B',8);
		$pdf->setXY(75, 43); $pdf->Write(0, $this->textM->convertNumFormat($payInfo->addTotal)); 
		
		///DEDUCTIONS
		$leftDeduct = "";
		$rightDeduct = "";		
		if($payInfo->deductMaxicare>0){
			$leftDeduct .= "Maxicare\n";
			$rightDeduct .= $this->textM->convertNumFormat($payInfo->deductMaxicare)."\n";
		}
		if($payInfo->deductArrears>0){
			$leftDeduct .= "Payment of Arrears\n";
			$rightDeduct .= $this->textM->convertNumFormat($payInfo->deductArrears)."\n";
		}
		if($payInfo->deductResti>0){
			$leftDeduct .= "Financial Restitutions\n";
			$rightDeduct .= $this->textM->convertNumFormat($payInfo->deductResti)."\n";
		}
		
		if(!empty($payInfo->addDeductions)){
			$dedArr = unserialize(stripslashes($payInfo->addDeductions));
			foreach($dedArr AS $ded){
				if(isset($ded[0]) && isset($ded[1])){
					$leftDeduct .= ucwords($ded[0])."\n";
					$rightDeduct .= $this->textM->convertNumFormat($ded[1])."\n";
				}
			}
		}
		
		$pdf->SetFont('Arial','',8);
		$pdf->setXY(15, 63); $pdf->MultiCell(60, 4, $leftDeduct,0,'L',false);
		$pdf->setXY(75, 63); $pdf->MultiCell(60, 4, $rightDeduct,0,'L',false);
		
		$pdf->SetFont('Arial','B',8);
		$pdf->setXY(75, 83.7); $pdf->Write(0, $this->textM->convertNumFormat($payInfo->deductTotal));
		
		///LAST PAY TOTALS
		$leftTotal = "Tax ".(($payInfo->taxRefund<0)?"Deficit":"Refund")." for the Year ".date('Y', strtotime($payInfo->dateTo))."\n";
		$leftTotal .= "Add Ons\n";
		$leftTotal .= "Deductions\n";
		$rightTotal = $this->textM->convertNumFormat($payInfo->taxRefund)."\n";
		$rightTotal .= $this->textM->convertNumFormat($payInfo->addTotal)."\n";
		$rightTotal .= (($payInfo->deductTotal>0)?'-':'').$this->textM->convertNumFormat($payInfo->deductTotal)."\n";
		
		$pdf->SetFont('Arial','',10);
		$pdf->setXY(15, 104); $pdf->MultiCell(60, 5, $leftTotal,0,'L',false);
		$pdf->setXY(75, 104); $pdf->MultiCell(60, 5, $rightTotal,0,'L',false);
		
		$pdf->SetFont('Arial','B',14);
		$pdf->setXY(75, 126); $pdf->Write(0, 'PHP '.$this->textM->convertNumFormat($payInfo->netLastPay)); ///NET LAST PAY

		////SUMMARY OF GOVERNMENT CONTRIBUTIONS
		$pdf->SetFont('Arial','',9);
		$pdf->setXY(15, 158); $pdf->Write(0, 'SSS');
		$pdf->setXY(75, 158); $pdf->Write(0, $this->textM->convertNumFormat($totalSSS));
		$pdf->setXY(15, 163); $pdf->Write(0, 'Philhealth');
		$pdf->setXY(75, 163); $pdf->Write(0, $this->textM->convertNumFormat($totalPhilhealth));
		$pdf->setXY(15, 168); $pdf->Write(0, 'Pag-ibig');
		$pdf->setXY(75, 168); $pdf->Write(0, $this->textM->convertNumFormat($totalPagIbig));
		
		
		$pdf->SetFont('Arial','B',11);
		$pdf->setXY(61, 216.2); $pdf->MultiCell(34, 5, 'PHP '.$this->textM->convertNumFormat($payInfo->netLastPay),0,'C',false); //received amount of
		$pdf->setXY(128, 241.5); $pdf->MultiCell(78, 5, utf8_decode(strtoupper($staffInfo->fname.' '.$staffInfo->lname)),0,'C',false); //name 
		
		$pdf->Output('lastpay.pdf', 'I');		
	}	

	public function computeTaxExemption($taxStatus){
		return $this->dbmodel->getSingleField('taxStatusExemption', 'taxExemption', 'taxStatus_fk = '.$taxStatus);
	}

	public function getBIRTaxDue($tax){
		$q = $this->dbmodel->getSingleField('birTaxTable', 'taxRate', "minRange < $tax AND maxRange > $tax");
		$s = eval("return $q;");
		return $s;
	}
	
	//return info used in distro list
	public function getEmployeePayrollDistro( $employee_info, $header_array_sequence, $include_last_pay = false ){
		$dataItems = $this->dbmodel->getQueryResults('tcPayslipDetails', 'payID, payCode, payValue, payType, payName, payCategory, numHR, payAmount', 'payslipID_fk="'.$employee_info->payslipID.'" AND payValue!="0.00"', 'LEFT JOIN tcPayslipItems ON payID=payItemID_fk', 'payCategory, payAmount, payType');

		$data_excel_array = array();

		$data_excel_array['name'] = $employee_info->lname.', '.$employee_info->fname;
		$data_excel_array['id_num'] = $employee_info->idNum;
		$data_excel_array['net'] = $employee_info->net;
		$data_excel_array['net_'] = $employee_info->net;
		$data_excel_array['earning'] = $employee_info->earning;
		$data_excel_array['earning_'] = $employee_info->earning;
		$data_excel_array['eCompensation'] = '-'.$employee_info->eCompensation;
		$data_excel_array['employerShare'] = '-'.$employee_info->employerShare;
		$data_excel_array['totalTaxable'] = $employee_info->totalTaxable;
		$data_excel_array['basePay'] = $employee_info->basePay;

		foreach( $dataItems as $item ){
			if( $item->payType == 'debit')	{
				$item->payValue = '-'.$item->payValue;
			}
			if( isset($header_array_sequence[ $item->payID ] ) ){
				$key = $item->payID;
				$data_excel_array[ $key ] += $item->payValue;
			}
			if( $item->numHR > 0 ){
				$key = $item->payID.'HR';
				$data_excel_array[ $key ] = $item->numHR;
			}
			//for tax refund
			//deduct tax refund from gross pay
			if( isset($item->payID) AND in_array($item->payID, array(43, 46, 37) ) ){
				$data_excel_array['earning'] = $data_excel_array['earning'] - $item->payValue;
				$data_excel_array['earning_'] = $data_excel_array['earning_'] - $item->payValue;
			}
		}
		if( !isset($data_excel_array['22HR']) ){
			unset($data_excel_array['22']);
		}
		if( isset($data_excel_array['22']) ){
			unset($data_excel_array['basePay']);
		}

		if( $include_last_pay == TRUE ){
			
			$last_pay_items = $this->dbmodel->getSingleInfo('tcLastPay', '*', 'empID_fk = '.$employee_info->empID_fk);
			if( count($last_pay_items) > 0 ){
				$data_excel_array['taxDue']	= $last_pay_items->taxDue;
				$data_excel_array['taxWithheld']	= $last_pay_items->taxWithheld;
				$data_excel_array['13thMonth']	= $last_pay_items->add13th;
				$data_excel_array['taxFromPrevious']	= $last_pay_items->taxFromPrevious;
				

			}
			
		}


		return $data_excel_array;
	}
}
