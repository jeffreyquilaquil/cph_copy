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
		foreach($empArr AS $emp){
			$empInfo = $this->dbmodel->getSingleInfo('staffs', 'sal, taxstatus', 'empID="'.$emp.'"');
			$monthlyRate = $this->textM->decryptText($empInfo->sal);
								
			///INSERT TO tcPayslips			
			$payslipID = $this->dbmodel->getSingleField('tcPayslips', 'payslipID', 'payrollsID_fk="'.$info['payrollsID'].'" AND empID_fk="'.$emp.'" AND pstatus=1');
			if(empty($payslipID)){
				$payIns['empID_fk'] = $emp;
				$payIns['payrollsID_fk'] = $info['payrollsID'];
				$payIns['monthlyRate'] = str_replace(',', '', $monthlyRate);
				$payIns['basePay'] = str_replace(',', '', ($payIns['monthlyRate']/2));
				$payslipID = $this->dbmodel->insertQuery('tcPayslips', $payIns);
				$down['basePay'] = $payIns['basePay'];
			}else{
				$down['monthlyRate'] = str_replace(',', '', $monthlyRate);
				$down['basePay'] = str_replace(',', '', ($down['monthlyRate']/2));
			}
			
			////INSERT Payslip details
			$this->payrollM->insertPayslipDetails($payslipID); //inserting payslip details EXCEPT tax
			
			///compute for taxable income and tax then INSERT TAX values
			$taxableIncome = $this->payrollM->getTaxableIncome($payslipID);
			$tax = $this->payrollM->getTax($payslipID, $taxableIncome, $info['payType'], $empInfo->taxstatus);
			$this->payrollM->insertPayEachDetail($payslipID, $this->dbmodel->getSingleField('tcPayslipItems', 'payID', 'payAmount="taxTable"'), $tax); ////inserting income tax 
					
			////UPDATING RECORDS and COMPUTING FOR NET
			$down['totalTaxable'] = $taxableIncome;
			
			$queryItems = $this->dbmodel->getQueryResults('tcPayslipDetails', 'payValue, payType, payCDto, payCategory', 'payslipID_fk="'.$payslipID.'"', 'LEFT JOIN tcPayslipItems ON payID=payItemID_fk');		
			if(count($queryItems)>0){ 
				$catArr = $this->textM->constantArr('payCategory');
				$down['net'] = $down['basePay'];
				
				$groupPerCat = array();	
				$groupPerCat[0] = $down['basePay'];
				foreach($queryItems AS $q){
					if($q->payType=='credit'){
						$down['net'] += $q->payValue;
						$groupPerCat[$q->payCategory] = ((isset($groupPerCat[$q->payCategory]))?$groupPerCat[$q->payCategory]+$q->payValue:$q->payValue);
					}else{
						$down['net'] -= $q->payValue;
						$groupPerCat[$q->payCategory] = ((isset($groupPerCat[$q->payCategory]))?$groupPerCat[$q->payCategory]-$q->payValue:-$q->payValue);
					}				
					
				}
				
				$down['earning'] = 0;
				foreach($groupPerCat AS $k=>$cat){
					if($k==0 || $k==7){
						if(isset($down['earning'])) $down['earning'] += $cat;
						else $down['earning'] = $cat;
					}else $down[$catArr[$k]] = $cat;
				}
			}
			
			$this->dbmodel->updateQuery('tcPayslips', array('payslipID'=>$payslipID), $down);
			
			//number generated
			$cntGenerated = $this->dbmodel->getSingleField('tcPayslips', 'COUNT(payslipID)', 'payrollsID_fk="'.$info['payrollsID'].'" AND pstatus=1');
			$this->dbmodel->updateQueryText('tcPayrolls', 'numGenerated="'.$cntGenerated.'"', 'payrollsID="'.$info['payrollsID'].'"');
		}
	}
	
	/*****
		$info should have these "empID_fk, monthlyRate, payrollsID, payPeriodStart, payPeriodEnd, payType"
	*****/
	public function getPaymentItemsForPayroll($info){
		$kaonNapud = array();
		$dessertItems = $this->payrollM->getPaymentItems($info->empID_fk, 1);
			
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
		$info = $this->dbmodel->getSingleInfo('tcPayslips', 'empID_fk, monthlyRate, payrollsID, payPeriodStart, payPeriodEnd, payType', 'payslipID="'.$payslipID.'"', 'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk');
		
		//update previous inserted items value to "0" first before updating to new value
		$this->dbmodel->updateQueryText('tcPayslipDetails', 'payValue=0, numHR=0', 'payslipID_fk='.$payslipID);
		
		$itemArr = $this->payrollM->getPaymentItemsForPayroll($info);
		///INSERT PAYSLIP DETAILS except tax because it is computed last
		foreach($itemArr AS $item){
			$hr = 0;
			$payValue = 0;
			$happen = true;
			
			if($item->payAmount=='nightdiff' || $item->payAmount=='taken' || $item->payAmount=='overtime'){
				$hourlyRate = $this->payrollM->getDailyHourlyRate($info->monthlyRate, 'hourly');
				
				if($item->payAmount=='nightdiff'){
					$hr = $this->payrollM->getNumHours($info->empID_fk, $info->payPeriodStart, $info->payPeriodEnd, 'publishND');
					$payValue = ($hourlyRate*$hr) * 0.10;
				}else if($item->payAmount=='overtime'){ ///overtime hours is 30%
					$hr = $this->payrollM->getNumHours($info->empID_fk, $info->payPeriodStart, $info->payPeriodEnd, 'publishOT');
					$payValue = ($hourlyRate*$hr) * 0.30;
				}else{
					$hr = $this->payrollM->getNumHours($info->empID_fk, $info->payPeriodStart, $info->payPeriodEnd);
					$payValue = $hourlyRate*$hr;
				}
			}else if($item->payAmount=='philhealthTable'){
				$payValue = $this->dbmodel->getSingleField('philhealthTable', 'employeeShare', 'minRange<="'.$info->monthlyRate.'" AND maxRange>= "'.$info->monthlyRate.'"');
			}else if($item->payAmount=='sssTable'){
				$payValue = $this->dbmodel->getSingleField('sssTable', 'employeeShare', 'minRange<="'.$info->monthlyRate.'" AND maxRange>= "'.$info->monthlyRate.'"');
			}else if($item->payAmount=='taxTable') $happen = false;
			else $payValue = $item->payAmount;
			
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
	public function getNumHours($empID, $dateStart, $dateEnd, $type='publishDeduct'){
		$hourDeduction = $this->dbmodel->getSingleField('tcStaffLogPublish', 'SUM('.$type.') AS hours', 'empID_fk="'.$empID.'" AND slogDate BETWEEN "'.$dateStart.'" AND "'.$dateEnd.'"');
		
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
		$basePay = 0;
		$taxable = 0;
		$info = $this->dbmodel->getQueryResults('tcPayslipDetails', 'payItemID_fk, payValue, payType, basePay, payCDto', 'payslipID_fk="'.$payslipID.'" AND (payCDto = "taxable" OR payCDto="base") AND tcPayslipItems.payAmount!="taxTable"', 'LEFT JOIN tcPayslipItems ON payID=payItemID_fk LEFT JOIN tcPayslips ON payslipID= payslipID_fk');
		
		foreach($info AS $in){
			$basePay = $in->basePay;
			if($in->payType=='debit') $taxable -= $in->payValue;
			else $taxable += $in->payValue;
		}
		
		return $basePay + $taxable;		
	}
	
	
	/*****
		Accepts, payslipID, amount of taxable income, payType[monthly, semi]
	*****/
	public function getTax($payslipID, $taxableIncome, $payType, $taxstatus){		
		$tax = 0;
		$prevTax = 0;
		$taxableIncome = str_replace(',', '', $taxableIncome);
		
		if($payType=='monthly'){
			$info = $this->dbmodel->getSingleInfo('tcPayslips', 'payslipID, empID_fk, payPeriodStart, payPeriodEnd, payType', 'payslipID="'.$payslipID.'"', 'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk');
			if(count($info)>0){
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
		
		$tax = $this->payrollM->computeTax($payType, $taxableIncome, $taxstatus); 
		$tax -= $prevTax; ///minus tax for previous tax paid
		
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
		$status = $this->payrollM->getTaxStatus($status);	
		
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
	public function getNightDiffTime($q){
		$nightdiff = 0;
		$arr = array(0,1,2,3,4,5,6,22,23);
	
		$start = '0000-00-00 00:00:00';
		$end = '0000-00-00 00:00:00';
		
		//if no schedule change
		if($q->schedIn=='0000-00-00 00:00:00' && $q->timeIn!='0000-00-00 00:00:00' && $q->timeOut!='0000-00-00 00:00:00'){
			$start = date('Y-m-d H:00:00', strtotime($q->timeIn));
			$end = date('Y-m-d H:00:00', strtotime($q->timeOut));			
		}else if($q->timeIn!='0000-00-00 00:00:00' && $q->timeOut!='0000-00-00 00:00:00'){
			$start = $q->timeIn;
			$end = $q->timeOut;
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
	****/
	public function getTaxStatus($staffTaxStatus){
		$stat = 'Zero';
		if($staffTaxStatus==1 || $staffTaxStatus==6) $stat = 'SorM';
		else if($staffTaxStatus==2 || $staffTaxStatus==7) $stat = 'SorM1';
		else if($staffTaxStatus==3 || $staffTaxStatus==8) $stat = 'SorM2';
		else if($staffTaxStatus==4 || $staffTaxStatus==9) $stat = 'SorM3';
		else if($staffTaxStatus==5 || $staffTaxStatus==10) $stat = 'SorM4';		
			
		return $stat;
	}	

	/******
		$activeOnly=1 show only active items
	******/
	public function getPaymentItems($empID, $activeOnly=0){
		$condition = '';
		if($activeOnly==1) $condition = ' AND s.status=1';
		
		$sql= 'SELECT payID, payID AS payID_fk, payName, payType, payAmount, payPeriod, payStart, payEnd, payCDto, payCategory, mainItem, status, "0" AS empID_fk, "1" AS isMain  
					FROM tcPayslipItems AS s
					WHERE mainItem = 1 AND payID NOT IN (SELECT payID_fk FROM tcPayslipItemStaffs WHERE empID_fk="'.$empID.'" '.$condition.') '.$condition.'
					UNION
					SELECT payStaffID AS payID, payID_fk, payName, payType, s.payAmount, s.payPeriod, s.payStart, s.payEnd, p.payCDto, payCategory, p.mainItem, s.status, empID_fk, "0" AS isMain  
					FROM tcPayslipItemStaffs AS s
					LEFT JOIN tcPayslipItems AS p ON payID_fk=payID WHERE empID_fk="'.$empID.'" '.$condition.' 
					ORDER BY status DESC, isMain DESC, payStart, payPeriod, payCategory, payName';
		$query = $this->dbmodel->dbQuery($sql);
			
		return $query->result(); 
	}
	
	
}
?>
	