<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ask_hr extends CI_Model {
	
    function __construct() {
        // Call the Model constructor
        parent::__construct();		
		
    } // end of contruction

   function askhr($table,$data){
   		date_default_timezone_set("Asia/Manila");
   				if(count($data) > 0){
			$sql = "INSERT INTO $table (";
			$cols = '';
			$vals = '';
			foreach($data AS $k => $v){
				$cols .= '`'.$k.'`,';
				if($v=='NOW()')
					$vals .= $v.',';
				else
					$vals .= '"'.$v.'",';
			}
			$sql .= rtrim($cols,',').') VALUES ('.rtrim($vals,',').')';
			
			$this->db->query($sql);
			$num = $this->db->insert_id();
			
			return $num;
		}
     } //end of askhr function

     function hrhelpdesk($table1, $table2, $condition1, $condition2, $field){
     	$this->db->select($field);    
		$this->db->from($table);
		$this->db->join($table2, $table.'.'.$condition1 .'='. $table2 .'.'. $condition2);
		
		$query = $this->db->get();
		foreach($query->result() AS $q):
			$arr[] = $q;
		endforeach;
		return $arr;
     }
   
} // end of class

?>