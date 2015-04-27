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
		
}

?>