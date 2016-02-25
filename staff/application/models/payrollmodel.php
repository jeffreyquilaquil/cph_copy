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
			
			$queryItems = $this->dbmodel->getQueryResults('tcPayslipDetails', 'payValue, payType, payCDto, payCategory, payAmount', 'payslipID_fk="'.$payslipID.'"', 'LEFT JOIN tcPayslipItems ON payID=payItemID_fk');		
			if(count($queryItems)>0){				
				$down['earning'] = 0;
				$down['deduction'] = 0;
				$down['allowance'] = 0;
				$down['adjustment'] = 0;
				$down['advance'] = 0;
				$down['benefit'] = 0;
				$down['bonus'] = 0;
				$down['net'] = 0;
				
				$groupPerCat = array();	
				foreach($queryItems AS $q){
					if($q->payType=='credit'){
						$down['net'] += $q->payValue;
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
							|| ($cake->payStart>=$info->payPeriodStart && $cake->payStart<=$info->payPeriodEnd) || ($info->payPeriodStart>=$cake->payStart && $info->payPeriodStart<=$cake->payEnd)
						) $eat = true;
						else $eat = false;
					}
				}
				
				if($eat==true) $kaonNapud[] = $cake;
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
					$hr = $this->payrollM->getNumDays($info->startDate, $info->payPeriodEnd);
				}else if($info->endDate!="0000-00-00" && $info->endDate>=$info->payPeriodStart && $info->endDate<$info->payPeriodEnd){
					$hr = $this->payrollM->getNumDays($info->payPeriodStart, $info->endDate);
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
		if(empty($detailID)){
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
			if($type==1 || $type==2) $cond .= ' AND staffHolidaySched=0';
			else if($type==3) $cond .= ' AND staffHolidaySched=1';
			
			$hourDeduction = $this->dbmodel->getSingleField('tcStaffLogPublish LEFT JOIN tcAttendance ON dateToday=slogDate LEFT JOIN staffs ON empID=empID_fk', 'SUM(publishHO) AS hours', 
			'empID_fk="'.$empID.'" AND slogDate BETWEEN "'.$dateStart.'" AND "'.$dateEnd.'" AND publishHO!="" AND showStatus=1 '.$cond);
		}else if($type=='NDspecial' || $type=='NDregular'){
			if($type=='NDspecial') $holidayType = '2';
			else $holidayType = '1,3,4';
			
			$cond = ' AND (holidayType IN ('.$holidayType.') OR ((SELECT holidayType FROM tcAttendance WHERE dateToday=DATE_ADD(slogDate,INTERVAL 1 DAY)) IN ('.$holidayType.')))';
			if($type==1 || $type==2) $cond .= ' AND staffHolidaySched=0';
			else if($type==3) $cond .= ' AND staffHolidaySched=1';
			
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
		
		if(!empty($payStart) && !empty($payEnd) && $payStart!='0000-00-00' && $payEnd!='0000-00-00'){
			$first .= ' AND ((payStart="0000-00-00" AND payEnd="0000-00-00") OR ("'.$payStart.'" AND "'.$payEnd.'" BETWEEN payStart AND payEnd))';
			$second .= ' AND ((s.payStart="0000-00-00" AND s.payEnd="0000-00-00") OR ("'.$payStart.'" AND "'.$payEnd.'" BETWEEN s.payStart AND s.payEnd))';
		}
		
		$sql= 'SELECT payID, payID AS payID_fk, payCode, payName, payType, s.payPercent, s.payAmount AS 	prevAmount, payAmount, payPeriod, payStart, payEnd, payCDto, payCategory, mainItem, status, "0" AS empID_fk, "1" AS isMain  
				FROM tcPayslipItems AS s
				WHERE mainItem = 1 AND payID NOT IN (SELECT payID_fk FROM tcPayslipItemStaffs WHERE empID_fk="'.$empID.'" '.$condition.') '.$first.'
				UNION
				SELECT payStaffID AS payID, payID_fk, payCode, payName, payType, p.payPercent, p.payAmount AS prevAmount, s.payAmount, s.payPeriod, s.payStart, s.payEnd, p.payCDto, payCategory, p.mainItem, s.status, empID_fk, "0" AS isMain  
				FROM tcPayslipItemStaffs AS s
				LEFT JOIN tcPayslipItems AS p ON payID_fk=payID WHERE empID_fk="'.$empID.'" '.$second.' 
				ORDER BY status DESC, isMain DESC, payCategory, payAmount, payType, payName, payStart, payPeriod';
								
		$query = $this->dbmodel->dbQuery($sql);
					
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
		$dateToday = date('Y-m-d');
		
		$dateprev = date('Y-m-d', strtotime($dateToday.' -3 months'));
		$dateafter = date('Y-m-d', strtotime($dateToday.' +3 months'));
		
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
	public function staffLogStatusstaffLogStatus($payrollID, $status=''){
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
	
	public function generate13thmonth($periodFrom, $periodTo, $empIDs, $includeEndMonth=0){
		$dataEmp = explode(',', rtrim($empIDs, ','));
		$gID = array();
		
		foreach($dataEmp AS $emp){
			$queryPay = $this->payrollM->query13thMonth($emp, $periodFrom, $periodTo, $includeEndMonth);
			if(!empty($queryPay)){
				$pay = 0;
				$deduction = 0;				
				foreach($queryPay AS $ask){
					if(!empty($ask)){
						$pay += $ask->basePay;
						$deduction += (($ask->deduction!=NULL)?$ask->deduction:0);	
					}
				}
				
				$insArr['totalBasic'] = $pay;
				$insArr['totalDeduction'] = $deduction;
				$insArr['totalAmount'] = ($pay-$deduction)/12;
				$insArr['dateGenerated'] = date('Y-m-d H:i:s');
								
				///check if already exist update if exists else insert
				$monthID = $this->dbmodel->getSingleField('tc13thMonth', 'tcmonthID', 'empID_fk="'.$emp.'" AND periodFrom="'.$periodFrom.'" AND periodTo="'.$periodTo.'"');
				if(!empty($monthID)){
					$gID[] = $monthID;
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
			$basePay = ($this->textM->decryptText($info->sal))/2;
			
			$query = $this->dbmodel->getQueryResults('tcPayrolls', 'empID_fk, payDate, basePay, (SELECT SUM(payValue) FROM tcPayslipDetails LEFT JOIN tcPayslipItems ON payID=payItemID_fk WHERE payslipID_fk=payslipID AND payCategory=0 AND payType="debit") AS deduction', 
								'empID_fk="'.$empID.'" AND payDate BETWEEN "'.$periodFrom.'" AND "'.$periodTo.'" AND tcPayrolls.status!=3 AND pstatus=1',
								'LEFT JOIN tcPayslips ON payrollsID_fk=payrollsID', 'payDate');
								
			foreach($query AS $q){
				$computepay = ($q->basePay-$q->deduction) / 12;
				$q->pay = round($computepay,4);
				$payArr[$q->payDate] = $q;
			}
			
			$lastMonth = '';
			$dates = $this->payrollM->getArrayPeriodDates($periodFrom, $periodTo);
			foreach($dates AS $d){
				if(isset($payArr[$d])){
					$arrayMonths[$d] = $payArr[$d];
					$lastMonth = $d;
				}else if(!isset($payArr[$d]) && $includeEndMonth==1 && $lastMonth!='' && $d!=$lastMonth){
					$arrayMonths[$d] = (object) array('empID_fk'=>$empID, 'payDate'=>$d, 'basePay'=>$basePay, 'deduction'=>0, 'pay'=>($basePay/12));
				}else $arrayMonths[$d] = '';
			}
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
		$pdf->MultiCell(0, 5, 'That I, '.$data->full_name.', Filipino, of legal age, a resident of ________________ ________________________________________________, and formerly employed with Tate Publishing and Enterprises (Philippines), Inc., do by these presents acknowledge receipt of the sum of '.$data->amount_in_words.' ('.$data->amount_in_figure.'), Philippine Currency, from Tate Publishing and Enterprises (Philippines), Inc. in full payment and final settlement of my separation pay, 13th month pay, terminal benefits and accrued employment benefits due to me or which may be due to me from Tate Publishing and Enterprises (Philippines), Inc. under the law or under any existing agreement with respect thereto, as well as any and all claims of whatever kind and nature which I have or may have against Tate Publishing and Enterprises (Philippines), Inc., arising from my employment with and the termination of my employment with Tate Publishing and Enterprises (Philippines), Inc.', 0, 'J', false, $indent); 
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
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
			
		//SET DEFAULT FONT TO ARIAL BOLD size 12PT
		$pdf->SetFont('Arial','B',12);
		$pdf->setTextColor(0, 0, 0);
		
		//FOR THE YEAR
		$pdf->setXY(77, 35);
		$pdf->Write(0, date("Y")); 

		//FOR THE PERIOD
		//FROM
		$pdf->setXY(183, 35);
		$pdf->Write(0, '01');
		$pdf->setXY(191, 35);
		$pdf->Write(0, '01'); 
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
		$pdf->Write(0, strtoupper($staffInfo->fullName));

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
		$ts = $staffInfo->taxStatus;
		if($ts < 6){
			$pdf->setXY(62, 107);
		}
		else{
			$pdf->setXY(95, 107);
		}
		$pdf->Write(0, 'x');


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
		$personalExemption = 50000;	
		
		$salary = $payInfo->monthlyRate;
		$dailyRate = $this->payrollM->getDailyHourlyRate($salary, 'daily');
		$hourlyRate = $this->payrollM->getDailyHourlyRate($salary, 'hourly');
		$leaveAmount = $payInfo->addLeave * $dailyRate;		
		
		
		$payArr = array();
		foreach($dataMonth AS $m){
			$payArr[$m->payDate] = $m;
		}
		//echo '<pre>';
		//var_dump($payArr);
				
		$month13c = 0;
		foreach($dateArr AS $date){
			$payDate .= date('d-M-Y', strtotime($date))."\n";
			
			if(isset($payArr[$date])){
				$regTaken = ((isset($dataMonthItems[$payArr[$date]->payslipID]['regularTaken']))?$dataMonthItems[$payArr[$date]->payslipID]['regularTaken']:'0.00');
				$incomeTax = ((isset($dataMonthItems[$payArr[$date]->payslipID]['incomeTax']))?'-'.$dataMonthItems[$payArr[$date]->payslipID]['incomeTax']:'0.00');
				
				$totalSSS += ((isset($dataMonthItems[$payArr[$date]->payslipID]['sss']))?$dataMonthItems[$payArr[$date]->payslipID]['sss']:0);
				$totalPhilhealth += ((isset($dataMonthItems[$payArr[$date]->payslipID]['philhealth']))?$dataMonthItems[$payArr[$date]->payslipID]['philhealth']:0);
				$totalPagIbig += ((isset($dataMonthItems[$payArr[$date]->payslipID]['pagIbig']))?$dataMonthItems[$payArr[$date]->payslipID]['pagIbig']:0);
				
				//add all adjustments
				//"nightDiff", "overTime", "perfIncentive", "specialPHLHoliday", "regPHLHoliday", "	regUSHoliday", "regHoliday", "regHoursAdded", "nightDiffAdded", "nightDiffSpecialHoliday", "nightDiffRegHoliday"
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['nightDiff']))?$dataMonthItems[$payArr[$date]->payslipID]['nightDiff']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['overTime']))?$dataMonthItems[$payArr[$date]->payslipID]['overTime']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['perfIncentive']))?$dataMonthItems[$payArr[$date]->payslipID]['perfIncentive']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['specialPHLHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['specialPHLHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['regPHLHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['regPHLHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['regUSHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['regUSHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['regHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['regHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['regHoursAdded']))?$dataMonthItems[$payArr[$date]->payslipID]['regHoursAdded']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['nightDiffAdded']))?$dataMonthItems[$payArr[$date]->payslipID]['nightDiffAdded']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['nightDiffSpecialHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['nightDiffSpecialHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['nightDiffRegHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['nightDiffRegHoliday']:0;
				
				$gross .= $this->textM->convertNumFormat($payArr[$date]->earning)."\n";
				$basicSal .= $this->textM->convertNumFormat($payArr[$date]->basePay)."\n";
				$attendance .= $this->textM->convertNumFormat($regTaken)."\n";
				$adjustment .= $this->textM->convertNumFormat($payArr[$date]->adjustment)."\n";
				$taxIncome .= $this->textM->convertNumFormat($payArr[$date]->totalTaxable)."\n";
				$bonus .= $this->textM->convertNumFormat($payArr[$date]->bonus)."\n";
				$deminimis .= $this->textM->convertNumFormat($payArr[$date]->allowance)."\n";
				$taxWithheld .= $incomeTax."\n";
								
				//13th month computation = (basepay-deduction)/12 NO 13th month if end date before Jan 25
				
				if($staffInfo->endDate>=date('Y').'-01-25'){
					if( isset($payArra[$date]->deductions) ){
						$month13c = ($payArr[$date]->basePay - $payArr[$date]->deductions)/12;
					}
					
				}				
				
				$month13 .= $this->textM->convertNumFormat($month13c)."\n";
				$netPay .= $this->textM->convertNumFormat($payArr[$date]->net)."\n";
									
				$totalIncome += $payArr[$date]->earning;
				$totalSalary += $payArr[$date]->basePay;
				$totalDeduction += $regTaken;
				$totalTaxable += $payArr[$date]->totalTaxable;					
				$totalTaxWithheld += $incomeTax;	
				$totalBonus += $payArr[$date]->bonus;
				$totalAdjustment += $payArr[$date]->adjustment;
				$totalAllowance += $payArr[$date]->allowance;
				
				$total13th += $month13c;					
				$totalNet += $payArr[$date]->net;					
			}else{
				$gross .= "0.00\n";
				$basicSal .= "0.00\n";
				$attendance .= "0.00\n";
				$adjustment .= "0.00\n";
				$taxIncome .= "0.00\n";
				$bonus .= "0.00\n";
				$taxWithheld .= "0.00\n";
				$month13 .= "0.00\n";
				$deminimis .= "0.00\n";
				$netPay .= "0.00\n";
			}
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
		$dependents = $this->payrollM->getTaxStatus($staffInfo->taxstatus, 'num');
		if($dependents=='') $dependents = 0;
		else $personalExemption += ($dependents*25000);
		
		//MISC COMPUTATIONS
		$spp = $totalSSS+$totalPhilhealth+$totalPagIbig;
		$tnt = $this->textM->convertNumFormat($total13th+$totalAllowance+$spp);

		//FOR 21
		$pdf->setXY(94, 229);
		$pdf->Cell(49, 5, $this->textM->convertNumFormat($totalIncome),0,2,'R');

		//FOR 22
		$pdf->setXY(95, 236);
		$pdf->Cell(48, 5, $tnt, 0,2,'R');

		//FOR 23
		$pdf->setXY(95, 242);
		$pdf->Cell(48, 5, $this->formatNum($payInfo->taxFromCurrent), 0,2,'R');
		
		//FOR 24
		$pdf->setXY(95, 248);
		$pdf->Cell(48, 5, $this->formatNum($payInfo->taxFromPrevious), 0,2,'R');

		//FOR 25
		$n25 = $payInfo->taxFromCurrent + $payInfo->taxFromPrevious;
		$pdf->setXY(95, 254);
		$pdf->Cell(48, 5, $this->formatNum($n25), 0,2,'R');		

		//FOR 26
		$n26 = $dependents+$personalExemption;
		$pdf->setXY(95, 260);
		$pdf->Cell(48, 5, $this->formatNum($n26), 0,2,'R');

		//FOR 27. 27 is always 0
		$pdf->setXY(95, 266);
		$pdf->Cell(48, 5, $this->formatNum('0'), 0,2,'R');	
		
		//FOR 28
		$n28 = $n25-$n26;
		if( $n28 < 0 )
			$n28 = $this->formatNum('0');

		$pdf->setXY(95, 272);
		$pdf->Cell(48, 5, $n28, 0,2,'R');

		//FOR 29
		$pdf->setXY(95, 278);
		$pdf->Cell(48, 5, $this->formatNum($payInfo->taxDue) , 0,2,'R');

		//FOR 30A
		$pdf->setXY(95, 285);
		$pdf->Cell(48, 5, $this->formatNum($payInfo->taxWithheld) , 0,2,'R');

		//FOR 30B
		$pdf->setXY(95, 292);
		$pdf->Cell(48, 5, $this->formatNum($payInfo->taxWithheldFromPrevious) , 0,2,'R');		

		//FOR 31
		$n31 = $payInfo->taxWithheldFromPrevious+$payInfo->taxWithheld;
		$pdf->setXY(95, 298);
		$pdf->Cell(48, 5, $this->formatNum($n31) , 0,2,'R');

		//FOR 37
		$pdf->setXY(194, 100);
		$pdf->Cell(48, 5, $this->formatNum($total13th), 0,2,'R');		

		//FOF 38
		$pdf->setXY(194, 110);
		$pdf->Cell(48, 5, $this->formatNum($totalAllowance), 0,2,'R');

		//FOR 39
		$pdf->setXY(194, 122);
		$pdf->Cell(48, 5, $this->formatNum($spp), 0,2,'R');		

		//FOR 41
		$pdf->setXY(194, 147);
		$pdf->Cell(48, 5, $this->formatNum($tnt), 0,2,'R');			

		//FOR 42
		$tsal = $totalSalary - $totalDeduction - $spp;
		$pdf->setXY(194, 166);
		$pdf->Cell(48, 5, $this->formatNum($tsal), 0,2,'R');			
		
		//FOR 47A
		$pdf->setXY(194, 209);
		$pdf->Cell(48, 5, $this->formatNum($totalAdjustment), 0,2,'R');	

		//FOR 51
		$excs = '';
		if($total13th > 82000)
			$excs = $this->formatNum($total13th-82000);
		$pdf->setXY(194, 253);
		$pdf->Cell(48, 5, $excs, 0,2,'R');

		//FOR 55
		$n55 = $totalSalary+$totalAdjustment+$excs;
		$pdf->setXY(194, 297);
		$pdf->Cell(48, 5, $this->formatNum($n55), 0,2,'R');

		//OUTPUT PDF
		$pdf->Output('lastpay.pdf', 'I');

	}
	
	public function formatNum($d){
		return $this->textM->convertNumFormat($d);
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
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'lastpay.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
			
		$pdf->SetFont('Arial','',10);
		$pdf->setTextColor(0, 0, 0);
				
		$pdf->setXY(20, 46);
		$pdf->Write(0, $staffInfo->idNum); //employee id number
		$pdf->setXY(38, 44);
		$pdf->MultiCell(80, 4, $staffInfo->lname.', '.$staffInfo->fname,0,'C',false);  //employee name
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
		//echo '<pre>';
		//var_dump($payArr);
				
		$month13c = 0;
		foreach($dateArr AS $date){
			$payDate .= date('d-M-Y', strtotime($date))."\n";
			
			if(isset($payArr[$date])){
				$regTaken = ((isset($dataMonthItems[$payArr[$date]->payslipID]['regularTaken']))?$dataMonthItems[$payArr[$date]->payslipID]['regularTaken']:'0.00');
				$incomeTax = ((isset($dataMonthItems[$payArr[$date]->payslipID]['incomeTax']))?'-'.$dataMonthItems[$payArr[$date]->payslipID]['incomeTax']:'0.00');
				
				$totalSSS += ((isset($dataMonthItems[$payArr[$date]->payslipID]['sss']))?$dataMonthItems[$payArr[$date]->payslipID]['sss']:0);
				$totalPhilhealth += ((isset($dataMonthItems[$payArr[$date]->payslipID]['philhealth']))?$dataMonthItems[$payArr[$date]->payslipID]['philhealth']:0);
				$totalPagIbig += ((isset($dataMonthItems[$payArr[$date]->payslipID]['pagIbig']))?$dataMonthItems[$payArr[$date]->payslipID]['pagIbig']:0);
				
				//add all adjustments
				//"nightDiff", "overTime", "perfIncentive", "specialPHLHoliday", "regPHLHoliday", "	regUSHoliday", "regHoliday", "regHoursAdded", "nightDiffAdded", "nightDiffSpecialHoliday", "nightDiffRegHoliday"
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['nightDiff']))?$dataMonthItems[$payArr[$date]->payslipID]['nightDiff']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['overTime']))?$dataMonthItems[$payArr[$date]->payslipID]['overTime']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['perfIncentive']))?$dataMonthItems[$payArr[$date]->payslipID]['perfIncentive']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['specialPHLHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['specialPHLHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['regPHLHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['regPHLHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['regUSHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['regUSHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['regHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['regHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['regHoursAdded']))?$dataMonthItems[$payArr[$date]->payslipID]['regHoursAdded']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['nightDiffAdded']))?$dataMonthItems[$payArr[$date]->payslipID]['nightDiffAdded']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['nightDiffSpecialHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['nightDiffSpecialHoliday']:0;
				$payArr[$date]->adjustment += (isset($dataMonthItems[$payArr[$date]->payslipID]['nightDiffRegHoliday']))?$dataMonthItems[$payArr[$date]->payslipID]['nightDiffRegHoliday']:0;
				
				$gross .= $this->textM->convertNumFormat($payArr[$date]->earning)."\n";
				$basicSal .= $this->textM->convertNumFormat($payArr[$date]->basePay)."\n";
				$attendance .= $this->textM->convertNumFormat($regTaken)."\n";
				$adjustment .= $this->textM->convertNumFormat($payArr[$date]->adjustment)."\n";
				$taxIncome .= $this->textM->convertNumFormat($payArr[$date]->totalTaxable)."\n";
				$bonus .= $this->textM->convertNumFormat($payArr[$date]->bonus)."\n";
				$deminimis .= $this->textM->convertNumFormat($payArr[$date]->allowance)."\n";
				$taxWithheld .= $incomeTax."\n";
								
				//13th month computation = (basepay-deduction)/12 NO 13th month if end date before Jan 25
				
				if($staffInfo->endDate>=date('Y').'-01-25'){
					if( isset($payArra[$date]->deductions) ){
						$month13c = ($payArr[$date]->basePay - $payArr[$date]->deductions)/12;
					}
					
				}				
				
				$month13 .= $this->textM->convertNumFormat($month13c)."\n";
				$netPay .= $this->textM->convertNumFormat($payArr[$date]->net)."\n";
									
				$totalIncome += $payArr[$date]->earning;
				$totalSalary += $payArr[$date]->basePay;
				$totalDeduction += $regTaken;
				$totalTaxable += $payArr[$date]->totalTaxable;					
				$totalTaxWithheld += $incomeTax;	
				$totalBonus += $payArr[$date]->bonus;
				$totalAdjustment += $payArr[$date]->adjustment;
				$totalAllowance += $payArr[$date]->allowance;
				
				$total13th += $month13c;					
				$totalNet += $payArr[$date]->net;					
			}else{
				$gross .= "0.00\n";
				$basicSal .= "0.00\n";
				$attendance .= "0.00\n";
				$adjustment .= "0.00\n";
				$taxIncome .= "0.00\n";
				$bonus .= "0.00\n";
				$taxWithheld .= "0.00\n";
				$month13 .= "0.00\n";
				$deminimis .= "0.00\n";
				$netPay .= "0.00\n";
			}
		}
		
		$dependents = $this->payrollM->getTaxStatus($staffInfo->taxstatus, 'num');
		if($dependents=='') $dependents = 0;
		else $personalExemption += ($dependents*25000);
				
		$pdf->SetFont('Arial','',6.5);
		$pdf->setXY(11, 78); $pdf->MultiCell(18, 3.7, $payDate,0,'C',false);  //Payslip Date
		$pdf->setXY(29, 78); $pdf->MultiCell(18, 3.7, $gross,0,'C',false);  //Gross Income
		$pdf->setXY(46.5, 78); $pdf->MultiCell(18, 3.7, $basicSal,0,'C',false);  //Basic Salary
		$pdf->setXY(63.8, 78); $pdf->MultiCell(18, 3.7, $attendance,0,'C',false);  //Attendance Deduction
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
		$pdf->setXY(153, 168.5); $pdf->MultiCell(18, 3.7, $this->textM->convertNumFormat($total13th),0,'C',false);
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
		$pdf->setXY(128, 241.5); $pdf->MultiCell(78, 5, strtoupper($staffInfo->fname.' '.$staffInfo->lname),0,'C',false); //name 
		
		$pdf->Output('lastpay.pdf', 'I');		
	}	
	
}
?>
	