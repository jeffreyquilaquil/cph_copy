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
	

	function getCustomSchedDetails($id) {
		$this->load->model('Staffmodel', 'staffM');
		$query = "SELECT * FROM  staffCustomSched WHERE custschedID =".$id;
		$query_result = $this->db->query($query);
		$customSchedDetails = $query_result->result();
		
		$weekarray = array();		
		$weekarray['sunday'] = $customSchedDetails[0]->sunday;
		$weekarray['monday'] = $customSchedDetails[0]->monday;
		$weekarray['tuesday'] = $customSchedDetails[0]->tuesday;
		$weekarray['wednesday'] = $customSchedDetails[0]->wednesday;
		$weekarray['thursday'] = $customSchedDetails[0]->thursday;
		$weekarray['friday'] = $customSchedDetails[0]->friday;
		$weekarray['saturday'] = $customSchedDetails[0]->saturday;
		$weekarray['schedType'] = $customSchedDetails[0]->schedType;
		return $weekarray;		
	
	}
	
}
?>