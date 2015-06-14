<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Textdefinemodel extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }
		
	function getEmps($all, $id, $n){
		$emptxt = '';
		if(isset($all[$id])){
			$emptxt .= '<ul class="ul_'.$n.' emp_'.$id.' '.(($n>2)?'uldisp':'').'">';
			foreach($all[$id] AS $a):
				if(isset($all[$a[0]])){
					$emptxt .= '<li class="li_'.$n.'" style="cursor:pointer" onClick="toggleDisplay('.$a[0].')">';
				}else{
					$emptxt .= '<li class="li_'.$n.' li_none">';
				}
				
				$emptxt .= '<u>'.$a[2].'</u>, <i><a href="'.$this->config->base_url().'staffinfo/'.$a[3].'/" target="_blank">'.$a[1].'</a></i>';
				
				
				if(isset($all[$a[0]]))
					$emptxt .= '<div id="pointer_'.$a[0].'" class="tpointer" style="float:right; background-color:#ccc; padding:0 5px;">+</div>';
					
				$emptxt .= '</li>';
				if(isset($all[$a[0]])){
					$emptxt .= $this->getEmps($all, $a[0], ($n+1));
				}
			endforeach;	
			$emptxt .= '</ul>';
		}
		
		return $emptxt;
	}
	
	function encryptText($text){
		if(!empty($text)){
			$secret = base64_encode(THEENCRYPTOR);
			$text = base64_encode($secret.base64_encode($text));
		}
		return $text;
	}
	
	function decryptText($text){
		if(!empty($text)){
			$secret = base64_encode(THEENCRYPTOR);
			$text = base64_decode($text);
			$text = str_replace($secret, '', $text);
			$text = base64_decode($text);
		}
		return $text;
	}
	
	//if salType==1 no Php
	function convertDecryptedText($fld, $text, $salType=0){
		if(in_array($fld, $this->config->item('encText'))){
			$text = $this->decryptText($text);
		}
		
		if($fld=='sal' || $fld=='allowance'){
			if($salType==1) $text = $this->convertNumFormat($text);
			else $text = 'Php '.$this->convertNumFormat($text);
		}
		
		return $text;
	}
	
	public function convertNumFormat($n){
		return number_format((int)str_replace(',','',$n),2);
	}
	
	function convertTimeToMinHours($tdiff){
		$tText = '';
		$hours = floor($tdiff/3600);
		$remainder = $tdiff - ($hours * 3600);
		$minutes = floor($remainder/60);
		$remainder = $remainder - ($minutes * 60);
		$seconds = $remainder;

		if($hours>0 && $hours==1) $tText .= $hours.' hour ';
		else if($hours>0 && $hours>1) $tText .= $hours.' hours ';

		if($minutes==1) $tText .= $minutes.' minute ';
		else if($minutes>1) $tText .= $minutes.' minutes ';
		
		if($seconds==1) $tText .= $seconds.' second ';
		else if($seconds>1) $tText .= $seconds.' seconds ';

		return $tText;
	}
		
}

?>