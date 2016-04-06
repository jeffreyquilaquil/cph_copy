<?php
class recaptcha{
	
	private $site_key;
	private $secret_key;
	
	function __construct($site_key, $secret_key){
		$this->site_key = $site_key;
		$this->secret_key = $secret_key;
	}
	
	function is_valid($response, $remote_ip, $return_as_array = FALSE){
		$remote_ip = is_null($remote_ip)?"":"&remoteip=".$_SERVER['REMOTE_ADDR'];
		// Get cURL resource
		$curl = curl_init();
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify?secret='.$this->secret_key.'&response='.$response.$remote_ip,
		    CURLOPT_USERAGENT => 'Recaptcha Verification'
		));
		// Send the request & save response to $resp
		$resp_json = curl_exec($curl);
		// Close request to clear up some resources
		curl_close($curl);
		
		$resp_array = json_decode($resp_json, true);
		
		if($return_as_array){
			return $resp_array;
		}
		else{
			if(!empty($resp_array['success'])){
				return TRUE;
			}
			else 
				return FALSE;
		}
	}
	
	function display($attr=""){
		echo <<<EOF
		<script src='https://www.google.com/recaptcha/api.js'></script>
		<div $attr class="g-recaptcha" data-sitekey="{$this->site_key}"></div>
EOF;
	}
}

?>
