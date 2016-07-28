<?php 
ini_set('display_errors', 1);
$username = $_GET['username'];

if( !isset($username) OR empty($username) ){
	echo 'Please provide the username.';
	exit();
}

require 'config.php';

if(!isset($_SESSION['u']) || !in_array($_SESSION['u'], $authorized)){
	header("Location: login.php");
	exit();
}

$hire = $db->selectSingleQueryArray('staffs', '*' , 'username = "'.$username.'"');




$tmp_id_filename = 'tmp_id_'.$username;
$full_path = '/home/careerph/public_html/staff/uploads/staffs/'. $username;


if( file_exists( $full_path.'/signature.PNG' ) ){
    $signature_file =  $full_path.'/signature.PNG';
} else if( file_exists($full_path.'/signature.png') ){
    $signature_file =  $full_path.'/signature.png';
}

if( file_exists( $full_path.'/tmp_id_'.$username.'.JPG') ){
    $tmp_id_file = $full_path.'/tmp_id_'.$username.'.JPG';
} else if( file_exists( $full_path.'/tmp_id_'.$username.'.jpg') ){
    $tmp_id_file = $full_path.'/tmp_id_'.$username.'.jpg';
}    
$d = time();
$save_path_sig = $full_path.'/signature_resized.png';
$save_path_id = $full_path.'/tmp_id_resized.jpg';
	
$signature_file = resize_image( $signature_file, 100, 70, $save_path_sig, 'png');
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
			$pdf->Write(0, $hire['startDate']);
			//hbd
			$pdf->setXY(41, 38.5);		
			$pdf->Write(0, $hire['bdate'] );
			
			$pdf->setXY(41, 42.5);		
			$pdf->Write(0, decryptText($hire['sss']) );
			
			$pdf->setXY(41, 47);		
			$pdf->Write(0, decryptText($hire['philhealth']) );
			
			$pdf->setXY(42, 51);		
			$pdf->Write(0, decryptText($hire['hdmf']) );
			
			$pdf->setXY(40, 55.5);		
			$pdf->Write(0, decryptText($hire['tin']) );
			
			$pdf->setXY(80, 34);		
			$pdf->Write(0, $hire['emergency_person'] );
			
			$pdf->setXY(82, 37.5);		
			$pdf->MultiCell(44,1.8, $hire['emergency_address'],0,'L',false );
			
			$pdf->setXY(84, 43);		
			$pdf->Write(0, $hire['emergency_number'] );
			
			$pdf->setXY(87, 47);		
			$pdf->Write(0, $hire['emergency_relationship'] );
			
			$pdf->SetFont('Arial','B',9);
			//$pdf->setTextColor(255, 255, 255);
			$pdf->setTextColor(0, 0, 0);	
		    $full_name = $hire['fname'].' '.$hire['lname'];	
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
            $src = imagecreatefromjpeg($file);
            $dst = imagecreatetruecolor($newwidth, $newheight);
            break;
        case 'png': 
            $dst = imagecreatetruecolor($newwidth, $newheight);
            imagealphablending( $dst, false);
            imagesavealpha( $dst, true);
            $transparent = imagecolorallocatealpha( $dst, 255, 255, 255, 127);
            imagefilledrectangle( $dst, 0, 0, $newwidth, $newheight, $transparent);
            $src = imagecreatefrompng($file); 
            break;
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    switch( $img_type ){
        case 'jpg':
            imagejpeg( $dst, $save_path, 100); break;
        case 'png': 
            imagepng( $dst, $save_path, 9); break;
    }
    
    return $save_path;
    
}
