<?php
function pre($arr){
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
}

function is_admin(){
	global $db;
	$level = $db->selectSingleQuery("pt_users", "level", "username = '{$_SESSION['u']}'");
	
	if($level==1)
		return TRUE;
	else
		return FALSE;
}

function get_error(){
	global $error, $update;
	if(is_array($error) && sizeof($error) > 0){
		$style = "<style>";
		foreach($error AS $k => $v){
			if($k == "password")
				$style .= "input[name=re$k],";
			$style .= "input[name=$k],";
			$style .= "textarea[name=$k],";
			$style .= "select[name=$k],";
			$style .= "radio[name=$k],";
		}
		$style = substr($style,0,-1);
		$style .= "{border-color: #FF0000;box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset, 0 0 8px rgba(102, 175, 233, 0.6);outline: 0 none;}</style>";
		$err = implode("</p><p>", $error);
		$error_msg = "$style<div class='error'><p>$err</p></div>";
	}
	if(is_array($update) && sizeof($update) > 0){
		$update = implode("</p><p>", $update);
		$update_msg = "<div class='success'><p>$update</p></div>";
	}
	return $error_msg.$update_msg;
}

function nicetime($date)
{
    if(empty($date)) {
        return "No date provided";
    }
    
    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths         = array("60","60","24","7","4.35","12","10");
    
    $now             = time();
    $unix_date         = strtotime($date);
    
       // check validity of date
    if(empty($unix_date)) {    
        return "Bad date";
    }

    // is it future date or past date
    if($now > $unix_date) {    
        $difference     = $now - $unix_date;
        $tense         = "ago";
        
    } else {
        $difference     = $unix_date - $now;
        $tense         = "from now";
    }
    
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }
    
    $difference = round($difference);
    
    if($difference != 1) {
        $periods[$j].= "s";
    }
    
    return "$difference $periods[$j] {$tense}";
}

function get_profile_pic($uid, $name="", $full_image=FALSE){
	$dir = $full_image? "uploads/profiles/$uid/":"uploads/profiles/$uid/thumbnail";
	$size = $full_image? "height='400' width='400'":"height='80' width='80'";
	$profile_pic = get_file($dir);
	if($profile_pic === FALSE){
		return "<img class='profile-pic' src='images/default_profile_pic.jpg' $size />";
	}
	else{
		$p_dir = urlencode("uploads/profiles/$uid/{$profile_pic[0]}");
		$name = urlencode($name);
		return "<a href='profile_image.php?name=$name&dir=$p_dir&&iframe=true&width=80%&height=100%' rel='launcher'><img class='profile-pic' src='$dir/{$profile_pic[0]}' $size /></a>";
	}
}

function get_uploaded_file_icon($dir,$size=NULL){
	$size = is_null($size)? "" : "_".$size;
	$file = get_file($dir);
	if($file && sizeof($file) != 0 && !empty($file[0])){
		return "<a href='$dir/{$file[0]}'><img src='img/attach_document$size.png'></a>";
	}
	return false;
}

function get_file($dir){
	if(is_dir($dir)){
		$files = array();
		$content  = opendir($dir);
		while (false !== ($file = readdir($content))) {
			if($file === "." || $file === ".." || strpos($file, ".") === FALSE)
				continue;
		    $files[] = $file;
		}
		return $files;
	}
	else{
		return FALSE;
	}
}

function is_good_email($email, $id=NULL){
	global $db;
	$email = trim($email);
	if(empty($email)){
		return "Email empty.";
	}
	else if(filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE){
		return "Invalid Email.";
	}
	return TRUE;
}

function get_ajax_loader($element, $image='img/loading.gif'){
	if(!empty($image)){
		$img = "<img src='$image' /> ";
	}
	return <<<EOF
	$("$element").html("<div style='text-align:center;'>{$img}Loading...</div>");
EOF;
}

function checkDuplicateApplicant($fname, $lname, $bdate) {		
	global $db;
	$where = "fname like '%".$fname."%' AND lname like '%".$lname."%' AND bdate = '".$bdate."'";
	$querythis = $db->selectSingleQuery("applicants", "DATE(date_created)" ,$where);	
	$try = strtotime("+3 months",strtotime($querythis));		
	$validDate = date("Y-m-d",$try);		
	$today = date("Y-m-d");		
	if($validDate > $today)		
		return "<b>
		We have received your application last ".ucfirst(date("F d, Y",strtotime($querythis))).". <br/>
		You still have pending application, you may re-apply on ".ucfirst(date("F d, Y",strtotime($validDate)))." and onwards.</b>";
	return TRUE;
			
}

function sendEmail( $from, $to, $subject, $body, $fromName='' ){
	$url = 'https://pt.tatepublishing.net/api.php?method=sendGenericEmail';
	  /*
	   * from = sender's email
	   * fromName = sender's name
	   * BCC = cc's
	   * replyTo = reply to email address
	   * sendTo = recipient email address
	   * subject = email subject
	   * body = email body
	   */
	 

	/* $subject = $subject.' to-'.$to;
	$to = 'ludivina.marinas@tatepublishing.net'; */
	$fields = array(
	'from' => $from,
	'sendTo' => $to,
	'subject' => $subject,
	'body' => $body,
	);

	if( !empty($fromName) ){
	$fields['fromName'] = $fromName;
	}
	//build the urlencoded data
	$postvars='';
	$sep='';
	foreach($fields as $key=>$value) { 
	   $postvars.= $sep.urlencode($key).'='.urlencode($value); 
	   $sep='&'; 
	}
	//open connection
	$ch = curl_init();
	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS,$postvars);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	
	//execute post
	$result = curl_exec($ch);

	//close connection
	curl_close($ch);
}

function addStatusNote($appID, $type, $testStatus, $positionID, $reason){
	global $db;
	$insArr = array(
					'appID' => $appID,
					'type' => $type,
					'testStatus' => $testStatus,
					'positionID' => $positionID,
					'reason' => $reason,
					'examiner' => $_SESSION['u']
				);
				
	$db->insertQuery('processStatusData', $insArr);
}
?>
