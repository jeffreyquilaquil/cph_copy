<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Schedulemodel extends CI_Model {
	var $db;
	
    function __construct() {
        // Call the Model constructor
        parent::__construct();
		
		$this->db = $this->load->database('default', TRUE);
    }	

	function customTimeDisplay($time, $day, $v='', $edit=true){
		$valentine = '<select id="'.$day.'" class="schedSelect everyday padding5px '.(($v!='' && $v==0)?'bggray':'').'" '.(($edit==false)?'disabled':'').'>
					<option value=""></option>';					
					foreach($time AS $t=>$t2):
						$valentine .= '<optgroup label="'.$t2['name'].'">';						
						foreach($t2 AS $k=>$t):
							if($k!='name'){
								$ex = explode('|', $t);
								$valentine .= '<option value="'.$k.'" '.(($k==$v)?'selected="selected"':'').'>'.$ex[0].'</option>';
							}
						endforeach;
						$valentine .= '</optgroup>';
					endforeach;					
		$valentine .= '</select>';
		return $valentine;
	}	
	
}
?>