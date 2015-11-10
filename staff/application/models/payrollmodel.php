<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payrollmodel extends CI_Model {
	function __construct() {
        // Call the Model constructor
        parent::__construct();			
    }
	
	
	/******
		$info is an array of empID (separated with comma), dateStart, dateEnd, payrollsID, payType
		
		First, insert to tcPayslips
		Then, insert to tcPayslipDetails
	******/
	public function generatepayroll($info){		
		$empArr = explode(',', rtrim($info['empIDs'], ','));
		foreach($empArr AS $emp){
			$empInfo = $this->dbmodel->getSingleInfo('staffs', 'sal, taxstatus', 'empID="'.$emp.'"');
			$monthlyRate = $this->textM->decryptText($empInfo->sal);
											
			///INSERT TO tcPayslips			
			$payslipID = $this->dbmodel->getSingleField('tcPayslips', 'payslipID', 'payrollsID_fk="'.$info['payrollsID'].'" AND empID_fk="'.$emp.'"');
			if(empty($payslipID)){
				$payIns['empID_fk'] = $emp;
				$payIns['payrollsID_fk'] = $info['payrollsID'];
				$payIns['monthlyRate'] = str_replace(',', '', number_format($monthlyRate, 2));
				$payIns['basePay'] = str_replace(',', '', number_format(($monthlyRate/2), 2));
				$payslipID = $this->dbmodel->insertQuery('tcPayslips', $payIns);
			}
			
			////INSERT Payslip details
			$this->payrollM->insertPayslipDetails($payslipID);
			
			////for payslips records and inserting of income tax
			$tax = $this->payrollM->getTax($payslipID);
			$this->payrollM->insertPayEachDetail($payslipID, 4, $tax); ////inserting income tax 
			
			////UPDATING RECORDS
			$down['earnings'] = $this->payrollM->getComputedPayslipValue('earning', $payslipID);
			$down['bonuses'] = $this->payrollM->getComputedPayslipValue('bonus', $payslipID);
			$down['allowances'] = $this->payrollM->getComputedPayslipValue('allowance', $payslipID);
			$down['deductions'] = $this->payrollM->getComputedPayslipValue('deduction', $payslipID);
			$down['totalTaxable'] = $this->payrollM->getTaxableIncome($payslipID);

			//compute for NET
			$adjustmentAdd = $this->payrollM->getComputedPayslipValue('adjustmentAdd', $payslipID);
			$adjustmentDeduct = $this->payrollM->getComputedPayslipValue('adjustmentDeduct', $payslipID);
			$down['net'] = ($down['earnings'] + $down['bonuses'] + $down['allowances']) - $down['deductions'];
			$down['net'] = ($down['net'] + $adjustmentAdd) - $adjustmentDeduct;
			
			$this->dbmodel->updateQuery('tcPayslips', array('payslipID'=>$payslipID), $down);
			
			//number generated
			$cntGenerated = $this->dbmodel->getSingleField('tcPayslips', 'COUNT(payslipID)', 'payrollsID_fk="'.$info['payrollsID'].'"');
			$this->dbmodel->updateQueryText('tcPayrolls', 'numGenerated="'.$cntGenerated.'"', 'payrollsID="'.$info['payrollsID'].'"');
		}
	}
	
	////INSERT Payslip details
	public function insertPayslipDetails($payslipID){
		$info = $this->dbmodel->getSingleInfo('tcPayslips', 'empID_fk, monthlyRate, payrollsID, payPeriodStart, payPeriodEnd, payType', 'payslipID="'.$payslipID.'"', 'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk');
				
		$itemArr = array();
		$payItems = $this->dbmodel->getQueryResults('tcPayslipItems', 'itemID, itemPeriod, itemName, itemValue, tblReferred', 'itemPeriod!=""'); //all items
		foreach($payItems AS $i)
			$itemArr[$i->itemID] = $i;		
		
		$payItemStaff = $this->dbmodel->getQueryResults('tcPayslipItemStaffs', 'itemID, tcPayslipItemStaffs.itemPeriod, itemName, payValue AS itemValue, tblReferred', 'empID_fk="'.$info->empID_fk.'" AND (payStart="0000-00-00" OR "'.$info->payPeriodEnd.'" BETWEEN payStart AND payEnd)', 'LEFT JOIN tcPayslipItems ON itemID=itemID_fk'); //staff items
		foreach($payItemStaff AS $s)
			$itemArr[$s->itemID] = $s;
								
		///INSERT PAYSLIP DETAILS	
		//$payItems declared above
		foreach($itemArr AS $item){			
			if(($info->payType=='monthly' && $item->itemPeriod!='semi') || ($info->payType=='semi' && $item->itemPeriod!='monthly')){
				$payValue = 0;
				$hr = 0;
				if($item->itemValue!=0.00){
					$payValue = $item->itemValue;
				}else if($item->tblReferred=='philhealthTable'){
					$payValue = $this->dbmodel->getSingleField('philhealthTable', 'employeeShare', 'minRange<="'.$info->monthlyRate.'" AND maxRange>= "'.$info->monthlyRate.'"');
				}else if($item->tblReferred=='sssTable'){
					$payValue = $this->dbmodel->getSingleField('sssTable', 'employeeShare', 'minRange<="'.$info->monthlyRate.'" AND maxRange>= "'.$info->monthlyRate.'"');
				}else if($item->itemName=='Night Differential' || $item->itemName=='Regular Taken'){
					$hourlyRate = $this->payrollM->getDailyHourlyRate($info->monthlyRate, 'hourly');
					
					if($item->itemName=='Night Differential'){
						$hr = $this->payrollM->getNumHours($info->empID_fk, $info->payPeriodStart, $info->payPeriodEnd, 'publishND');
						$payValue = ($hourlyRate*$hr) * 0.10;
					}else{
						$hr = $this->payrollM->getNumHours($info->empID_fk, $info->payPeriodStart, $info->payPeriodEnd);
						$payValue = $hourlyRate*$hr;
					}
				}
				
				//if($payValue>0) 
				$this->payrollM->insertPayEachDetail($payslipID, $item->itemID, $payValue, $hr);
			}
		}
	}
	
	
	public function insertPayEachDetail($payslipID, $itemID, $payValue, $hr=0){
		$detailID = $this->dbmodel->getSingleField('tcPayslipDetails', 'detailID', 'payslipID_fk="'.$payslipID.'" AND itemID_fk="'.$itemID.'"');
		if(empty($detailID)){
			$insDetails['payslipID_fk'] = $payslipID;
			$insDetails['itemID_fk'] = $itemID;				
			$insDetails['payValue'] = $payValue;			
			$insDetails['itemHR'] = $hr;			
			$this->dbmodel->insertQuery('tcPayslipDetails', $insDetails);
		}else{		
			$this->dbmodel->updateQuery('tcPayslipDetails', array('detailID'=>$detailID), array('payValue'=>$payValue, 'itemHR'=>$hr));
		}
	}
	
	
	//Getting values of earnings, bonuses, allowances and deductions
	///bonus (non-taxable)
	///allowances (non-taxable)
	///deduction (taxable) for all deductions deduction and other deductions
	public function getComputedPayslipValue($type, $payslipID){
		$val = 0;
		if($type=='earning'){ /// basic pay + incentive (taxable) + other pays like holiday
			$basePay = $this->dbmodel->getSingleField('tcPayslips', 'basePay', 'payslipID="'.$payslipID.'"');
			$payIncentive = $this->dbmodel->getSingleField('tcPayslipDetails LEFT JOIN tcPayslipItems ON itemID=itemID_fk', 'SUM(payValue) AS incentive', 'payslipID_fk="'.$payslipID.'" AND (itemType=0 OR itemType=4)');
			
			$val = $basePay + $payIncentive;
		}else{
			$condition = 'payslipID_fk="'.$payslipID.'"';
			if($type=='bonus') $condition .= ' AND itemType=2';
			else if($type=='allowance') $condition .= ' AND itemType=1';
			else if($type=='deduction') $condition .= ' AND (itemType=3 OR itemType=7)';	
			else if($type=='adjustmentAdd') $condition .= ' AND itemType=5';		
			else if($type=='adjustmentDeduct') $condition .= ' AND itemType=6';		
		
			$val = $this->dbmodel->getSingleField('tcPayslipDetails LEFT JOIN tcPayslipItems ON itemID=itemID_fk', 'SUM(payValue) AS val', $condition);
		}
		
		return $val;
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
		Total Earnings is total of (basic salary + incentive + night diff + other pays like (holidays))
		If Semi-monthly	
			Total earnings minus (SSS, pag-ibig and absences)
		If Monthly
			Total earnings minus (Philhealth and absences)
	******/
	public function getTaxableIncome($payslipID){
		$info = $this->dbmodel->getSingleInfo('tcPayslips', 'empID_fk, monthlyRate, basePay, earnings, payPeriodEnd, payPeriodStart', 'payslipID="'.$payslipID.'"', 'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk');
		
		$earnings = $info->earnings;
		if($earnings==0.00) $earnings = $this->payrollM->getComputedPayslipValue('earning', $payslipID);		
		$deduction = $this->dbmodel->getSingleField('tcPayslipDetails', 'SUM(payValue)', 'payslipID_fk="'.$payslipID.'" AND itemID_fk IN (1,2,3,18)'); ///1-SSS, 2-Pag-ibig, 3-Philhealth, 18-Regular Taken
		
		return $earnings - $deduction;
	}
	
	
	public function getTax($payslipID){		
		$tax = 0;
		$prevTax = 0;
		
		$info = $this->dbmodel->getSingleInfo('tcPayslips', 'empID, totalTaxable, taxstatus, monthlyRate, basePay, earnings, payPeriodStart, payPeriodEnd, payType, payrollsID', 'payslipID="'.$payslipID.'"', 'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk LEFT JOIN staffs ON empID=empID_fk');
				
		$taxableIncome = $info->totalTaxable; ////THIS IS THE TOTAL TAXABLE INCOME	
		if($taxableIncome==0) $taxableIncome = $this->payrollM->getTaxableIncome($payslipID);
		$taxstatus = $this->payrollM->getTaxStatus($info->taxstatus);	

		if($info->payType=='monthly'){
			$payStart = date('Y-26-d', strtotime($info->payPeriodStart.' -1 month'));
			$payEnd = date('Y-10-d', strtotime($info->payPeriodStart));
			$prevpayslipID = $this->dbmodel->getSingleField('tcPayslips LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk', 'payslipID', 'empID_fk="'.$info->empID.'" AND payPeriodStart="'.$payStart.'" AND payPeriodEnd="'.$payEnd.'" AND payType="monthly" AND status=1');
			if(!empty($prevpayslipID)){
				$prevTax = $this->dbmodel->getSingleField('tcPayslipDetails', 'payValue', 'itemID_fk=4 AND payslipID_fk="'.$prevpayslipID.'"');
			}
		}
		$tax = $this->payrollM->computeTax($info->payType, $taxableIncome, $taxstatus, $prevTax);
		
		return $tax;
	}
	
	/*****
		$taxableIncome = base Pay minus deductions
		$taxType = monthly or semi
		$status = Single or Married and dependents
		$prevTax - previous tax (this is usual for monthly type) - required if monthly type
		
		monthly is from 11-25
		semi is from 26-10
	*****/
	public function computeTax($taxType, $taxableIncome, $status, $prevTax=0){
		$tax = 0;
		$taxableIncome = str_replace(',','',$taxableIncome);
		
		if($taxType=='monthly') $taxableIncome += $prevTax; ///if monthly add previous taxable income		
		
		$info = $this->dbmodel->getSingleInfo('taxTable', 'excessPercent, baseTax, minRange', 'taxType="'.$taxType.'" AND status="'.$status.'" AND minRange<="'.$taxableIncome.'" AND maxRange>"'.$taxableIncome.'"');
		
		if(count($info)>0){
			$perc = (int)$info->excessPercent / 100; 
			$tax = ($taxableIncome - $info->minRange) * $perc; ///(taxable income - tax bracket) x tax %
			$tax = $tax + $info->baseTax; ////add tax base
			
			if($taxType=='monthly'){ ///get tax from mid-month and deduct
				$prevTax = $this->computeTax('semi', $prevTax, $status);
				$tax = $tax - $prevTax;
			}
		}
		
		return round($tax, 2);
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
	
	public function getNightDiffTime($q){
		$nightdiff = 0;		
				
		if(date('Y-m-d', strtotime($q->schedIn)) == date('Y-m-d', strtotime($q->schedOut))){
			$start = date('Y-m-d 00:15:00', strtotime($q->slogDate));
			$end = date('Y-m-d 06:00:00', strtotime($q->slogDate));
		}else{
			$start = date('Y-m-d 22:15:00', strtotime($q->slogDate));
			$end = date('Y-m-d 06:00:00', strtotime($q->slogDate.' +1 day'));
		}
		
		while($start<$end){
			if($start>=$q->timeIn && $start<=$q->timeOut){
				$nightdiff++;
			}
			
			$start = date('Y-m-d H:15:s', strtotime($start.' +1 hour'));
		}
		
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
	
	
}
?>
	