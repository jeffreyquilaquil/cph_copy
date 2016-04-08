<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ask_hr extends CI_Model {
	
    function __construct() {
        // Call the Model constructor
        parent::__construct();		
		//$this->load->model('timecardmodel', 'timeM');
    }

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
			return $this->db->insert_id();
		}
   		

   }
  // function get_new_max_ID(){
   	// $sql=$this->db->query("SELECT MAX(id) as id FROM hr_cs_post");
    //	return $sql->row_array();
   //}
  function insertQuery($table, $array){
		date_default_timezone_set("Asia/Manila");
		if(count($array) > 0){
			$sql = "INSERT INTO $table (";
			$cols = '';
			$vals = '';
			foreach($array AS $k => $v){
				$cols .= '`'.$k.'`,';
				if($v=='NOW()')
					$vals .= $v.',';
				else
					$vals .= '"'.$v.'",';
			}
			$sql .= rtrim($cols,',').') VALUES ('.rtrim($vals,',').')';
			
			$this->db->query($sql);
			return $this->db->insert_id();
		}	
	}
}

?>