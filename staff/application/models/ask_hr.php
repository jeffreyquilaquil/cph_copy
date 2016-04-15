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

     function hrhelpdesk($fields, $table, $join='', $orderby=''){ // query with join
     	$arr = array();
		if($orderby!='') $orderby = 'ORDER BY '.$orderby. ' ASC';

		$query = $this->db->query("SELECT ".$fields." FROM ".$table." ".$join." ".$orderby);
		
		foreach($query->result() AS $q):
			$arr[] = $q;
		endforeach;
		return $arr;
     } // end of hrelpdesk function

     function getdata($field, $table, $where=''){ // query without join
     	$arr = array();
     	if($where!='')$where = 'WHERE '.$where;
     	$query = $this->db->query("SELECT ".$field." FROM ".$table." ".$where);
     	foreach ($query->result() as $value) {
     		$arr[] = $value;
     	}
     	return $arr;
     } // end hrinsedentform
   
} // end of class

?>