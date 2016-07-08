<?php 

$_POST = array(
	'startdate' => 'July 08, 2016',
	'bdate' => 'Janury 01, 2000',
	'sss' => '111111111',
	'philhealth' => '11111111111111',
	'hdmf' => '111111111111111',
	'tin' => '1111111111111111111',
	'e_contact_person' => 'Contact Person',
	'e_contact_address' => 'Contact Address',
	'e_contact_number' => 'Contact Number',
	'e_contact_relationship' => 'Relationship',
	'fname' => 'Marjune',
	'lname' => 'Abellana',
	'username' => 'jmonares'
	);


$tmp_id_filename = 'tmp_id_'.$_POST['username'];
$full_path = '/home/careerph/public_html/staff/uploads/staffs/'. $_POST['username'];

$signature_file =  $full_path.'/signature.JPG';
	$tmp_id_file = $full_path.'/tmp_id_jmonares.JPG';

	
	$sig_dimension = getimagesize($signature_file);



			require_once('includes/fpdf/fpdf.php');
			require_once('includes/fpdf/fpdi.php');
			$pdf = new FPDI();
			$pdf->AddPage();
			$pdf->setSourceFile('includes/forms/temp_id_template.pdf');
			$tplIdx = $pdf->importPage(1);
			$pdf->useTemplate($tplIdx, null, null, 0, 0, true);

			$pdf->SetFont('Arial','',6);
			$pdf->setTextColor(0, 0, 0);
			
			$pdf->setXY(42, 34);
			$pdf->Write(0, $_POST['startdate']);
			//hbd
			$pdf->setXY(41, 38.5);		
			$pdf->Write(0, $_POST['bdate'] );
			
			$pdf->setXY(41, 42.5);		
			$pdf->Write(0, $_POST['sss'] );
			
			$pdf->setXY(41, 47);		
			$pdf->Write(0, $_POST['philhealth'] );
			
			$pdf->setXY(42, 51);		
			$pdf->Write(0, $_POST['hdmf'] );
			
			$pdf->setXY(40, 55.5);		
			$pdf->Write(0, $_POST['tin'] );
			
			$pdf->setXY(80, 34);		
			$pdf->Write(0, $_POST['e_contact_person'] );
			
			$pdf->setXY(82, 38.5);		
			$pdf->Write(0, $_POST['e_contact_address'] );
			
			$pdf->setXY(84, 42.5);		
			$pdf->Write(0, $_POST['e_contact_number'] );
			
			$pdf->setXY(85, 47);		
			$pdf->Write(0, $_POST['e_contact_relationship'] );
			
			$pdf->SetFont('Arial','B',9);
			//$pdf->setTextColor(255, 255, 255);
			$pdf->setTextColor(0, 0, 0);	
		    $full_name = $_POST['fname'].' '.$_POST['lname'];	
			$full_name = strtoupper( $full_name );
			$pdf->setXY(67, 118);		
			$pdf->Write(0, $full_name );
			
			//picture
			$pdf->Image($tmp_id_file, 36, 96, -88, -110);
			
			$pdf->Image($signature_file, 70, 125);
			
			
		//	$pdf->Output($full_path. '/' .$tmp_id_filename.'.pdf', 'F');
			$pdf->Output($tmp_id_filename.'.pdf', 'I');