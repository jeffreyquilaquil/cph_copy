<?php 
ini_set('display_errors', 1);
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
$d = time();
$save_path_sig = $full_path.'/signature_resized'.$d.'.jpg';
$save_path_id = $full_path.'/tmp_id_resized'.$d.'.jpg';
	
$signature_file = resize_image( $signature_file, 100, 70, $save_path_sig, 'jpg');
$tmp_id_file = resize_image( $tmp_id_file, 105, 150, $save_path_id, 'jpg' );


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
			$pdf->Image($tmp_id_file, 36.5, 97);
			
			$pdf->Image($signature_file, 80, 123);
			
			
		//	$pdf->Output($full_path. '/' .$tmp_id_filename.'.pdf', 'F');
            $pdf->Output($tmp_id_filename.'.pdf', 'I');



function resize_image($file, $w, $h, $save_path, $img_type ) {
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    $dir = pathinfo( $save_info ); 
    switch( $img_type ){
        case 'jpg':
            $src = imagecreatefromjpeg($file); break;
        case 'png': 
            $src = imagecreatefrompng($file); break;
    }
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    switch( $img_type ){
        case 'jpg':
            imagejpeg( $dst, $save_path, 100); break;
        case 'png': 
            imagepng( $dst, $save_path, 100); break;
    }
    
    return $save_path;
    
}
