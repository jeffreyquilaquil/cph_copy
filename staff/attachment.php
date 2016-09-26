<?php
$username = decryptText(urldecode($_GET['u']));
$file_name = decryptText(urldecode($_GET['f']));
$timestamp = $_GET['t'];
if( !$username ){
    exit();
}
if( !$file_name ){ 
    exit();
}
//if( $timestamp <= strtotime("-1 week") ){
  //  exit();
//}
//decrypt
function decryptText($text){
    if(!empty($text)){
        $secret = base64_encode('hiCebuITteamIamDivi');
        $text = base64_decode($text);
        $text = str_replace($secret, '', $text);
        $text = base64_decode($text);
    }
    return $text;
}

//end decrypt
    

$filelocation = '/home/careerph/public_html/staff/uploads/staffs/' . $username .'/'. $file_name;
         include('application/config/mimes.php');

         //get file extension
         $file_extension = pathinfo( strtolower($file_name), PATHINFO_EXTENSION );
         //get mime equivalent of file extension
         $mime = $mimes[ $file_extension ];
         if( is_array($mime) ){
             $mime = $mimes[ $file_extension ][0];
         }
         header('Content-Type: '. $mime );
      header('Content-Disposition: inline; filename="'. $file_name .'"; ');
 header("Content-Length: ". filesize($filelocation) );
         $f = fopen($filelocation, 'rb');
         fpassthru($f);
         fclose($f);
 //        file_get_contents($filelocation);
         exit();

