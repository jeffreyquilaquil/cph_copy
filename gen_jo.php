<?php
//ini_set('display_errors', 1); 
	require 'config.php';
	require_once('includes/labels.php');
	date_default_timezone_set("Asia/Manila");
	setlocale(LC_MONETARY, 'en_US');
		
	if(!isset($_SESSION['u']) || !in_array($_SESSION['u'], $authorized)){
		echo '<script>window.parent.location = "'.HOME_URL.'login.php";</script>';
		exit();
	}
	
	
	require_once('includes/fpdf/fpdf.php');
	require_once('includes/fpdf/fpdi.php');
	
	$applicant_details = $db->selectSingleQueryArray('applicants a', 'fname, lname, gender, title, g.startDate, offer', 'a.id = '.$_GET['appID'].' AND g.joID = '.$_GET['jo_id'], 'LEFT JOIN generatedJO g ON g.appID = a.id LEFT JOIN newPositions np ON a.position = np.posID');
	$prefix = ($applicant_details['gender'] == 'female' ) ? 'Ms.' : 'Mr.';
	
	ob_end_clean();
	$pdf = new FPDI();
	$pdf->AddPage();
	$pdf->setSourceFile('includes/forms/Job_Offer_2016.pdf');
	$tplIdx = $pdf->importPage(1);
	$pdf->useTemplate($tplIdx, null, null, 0, 0, true);

	$pdf->SetFont('Arial','',10);
	$pdf->setTextColor(0, 0, 0);

	$pdf->setXY(17.5, 45);
	$pdf->Write(0, date('F d, Y'));	

	$pdf->SetFont('Arial','B',10);
	$pdf->setXY(17.5, 52);
	$pdf->Write(0, $applicant_details['fname'].' '.$applicant_details['lname']);	

	$pdf->setXY(17.5, 64);
	$pdf->Write(0, 'Dear '.$prefix.' '.$applicant_details['lname'].',');

	$pdf->setXY(82, 88);
	$pdf->Write(4, $applicant_details['title']);

	$pdf->setXY(82, 96);
	$pdf->Write(0, date('F d, Y',strtotime($applicant_details['startDate'])));

	$pdf->setXY(82, 102);
	$pdf->Write(0, 'Php '.$applicant_details['offer']);

	$pdf->setXY(82, 106.5);
	$pdf->Write(4, 'Php 325.00 / month');
	$pdf->setXY(82, 112.5);
	$pdf->Write(4, 'Php 1,500.00 / month');
	$pdf->setXY(82, 118.5);
	$pdf->Write(4, 'Php 300.00 / month');
	$pdf->setXY(82, 124.5);
	$pdf->Write(4, 'Php 125.00 / month');
	$pdf->setXY(82, 130.5);
	$pdf->Write(4, 'Php 250.00 / month');
	$pdf->setXY(82, 137);
	
	$offer_to_int = str_replace(',', '', $applicant_details['offer']);
	$total = $offer_to_int + 2500;
	$pdf->Write(4, 'Php '.number_format($total, 2, '.', ',') .' (sum of A to F)');

	$pdf->AddPage();
	$tplIdx = $pdf->importPage(2);
	$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
				
	$pdf->SetFont('Arial','B',10);
	$pdf->setXY(17.5, 284);
	$pdf->Write(0, strtoupper($applicant_details['fname'].' '.$applicant_details['lname']));

	$job_offer_file = 'JobOffer'.$joInsID.'-'.$applicant_details['lname'].'_'.$applicant_details['fname'].'.pdf';
	
	$pdf->Output($job_offer_file, $_GET['method']);