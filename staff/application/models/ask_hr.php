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
			
			/*$sql = "Insert into $table (cs_post_empID_fk,cs_post_subject,cd_post_other_empID_fk,cs_post_date_submitted,cs_post_status) VALUES ('$data['cs_post_empID_fk']','$data['cs_post_subject']', $data['cd_post_other_empID_fk']', $data['cs_post_date_submitted']', $data['cs_post_status']' )";
			$this->db->query($sql);*/
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