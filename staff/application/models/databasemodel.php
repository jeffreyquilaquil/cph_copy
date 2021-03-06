<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Databasemodel extends CI_Model {
	
    function __construct() {
        // Call the Model constructor
        parent::__construct();	
    }
	
	//PT DATABASE QUERIES	
	function ptdbQuery($sql){
		return $this->ptDB->query($sql);
	}		
	
	function getPTQueryResults($table, $fields, $where=1, $join='', $orderby=''){
		if($orderby!='') $orderby = 'ORDER BY '.$orderby;
		$query = $this->ptDB->query("SELECT ".$fields." FROM ".$table." ".$join." WHERE ".$where." ".$orderby);
		return $query->result();
	}
	
	function getPTSQLQueryResults($sql){
		$query = $this->ptDB->query($sql);
		return $query->result();
	}
	
	
	//CPH DATABASE QUERIES
	function dbQuery($sql){
		return $this->db->query($sql);
	}
	
	function getSingleField($table, $field, $where=''){
		if( $where != '')
			$where = "WHERE ".$where;

		$query = $this->db->query('SELECT '.$field.' FROM '.$table.' '.$where.' LIMIT 1');
		$f = '';
		foreach($query->row() AS $r){
			$f = $r;
		}
		return $f;
	}
			
	function getSingleInfo($table, $fields, $where=1, $join='', $orderby='', $display=false){
		if($orderby!='') $orderby = 'ORDER BY '.$orderby;
		$sql = "SELECT ".$fields." FROM ".$table." ".$join." WHERE ".$where." ".$orderby." LIMIT 1";
		if($display===true) echo $sql;
		
		$query = $this->db->query($sql);
		return $query->row();
	}
	
	function getQueryResults($table, $fields, $where=1, $join='', $orderby='', $trace=false){
		if($orderby!='') $orderby = 'ORDER BY '.$orderby; 
		$sql = "SELECT ".$fields." FROM ".$table." ".$join." WHERE ".$where." ".$orderby;
		if($trace==true) echo $sql;
		
		$query = $this->db->query($sql);
		return $query->result();
	}
	
	function getSQLQueryResults($sql){
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getResultArray($table, $fields, $where=1, $join='', $orderby='', $trace=false){
		if($orderby!='') $orderby = 'ORDER BY '.$orderby; 
		$sql = "SELECT ".$fields." FROM ".$table." ".$join." WHERE ".$where." ".$orderby;
		if($trace==true) echo $sql;
		
		$query = $this->db->query($sql);
		return $query->result_array();
	}
	
	function getQueryArrayResults($table, $fields, $where=1, $join='', $orderby=''){
		$arr = array();
		if($orderby!='') $orderby = 'ORDER BY '.$orderby;
		$query = $this->db->query("SELECT ".$fields." FROM ".$table." ".$join." WHERE ".$where." ".$orderby);
		foreach($query->result() AS $q):
			$arr[] = $q;
		endforeach;
		return $arr;
	}
	
	function getSQLQueryArrayResults($sql){
		$arr = array();
		$query = $this->db->query($sql);
		foreach($query->result() AS $q):
			$arr[] = $q;
		endforeach;
		return $arr;
	}
	
	function insertQuery($table, $array, $batch = false){
		date_default_timezone_set("Asia/Manila");
		if(count($array) > 0){
			/*$sql = "INSERT INTO $table (";
			$cols = '';
			$vals = '';
			foreach($array AS $k => $v){
				$cols .= '`'.$k.'`,';
				if($v=='NOW()')
					$vals .= $v.',';
				else
					$vals .= $this->db->escape($v).',';
			}
			$sql .= rtrim($cols,',').') VALUES ('.rtrim($vals,',').')';
			
			$this->db->query($sql);*/
			if(!$batch){
				$this->db->insert($table, $array);
			}else{
				$this->db->insert_batch($table, $array);
			}
			

			return $this->db->insert_id();
		}	
	}
	
	function insertQueryUpdateExist($table, $pKey, $arrayExist, $arrayInsertUpdate){
		date_default_timezone_set("Asia/Manila");
		$where = '';
		foreach($arrayExist AS $k=>$w){
			if(!empty($where)) $where .= ' AND ';
			$where .= '`'.$k.'`="'.$w.'"';
		}
	
		$id = $this->getSingleField($table, $pKey, $where);
		if(empty($id)) $id = $this->insertQuery($table, $arrayInsertUpdate);
		else $this->updateQuery($table, $arrayExist, $arrayInsertUpdate);	

		return $id;
	}
		
	function updateQuery($table, $where = array(), $data = array()) {
		$this->db->where($where);
		$this->db->update($table, $data);
	}

	function updateBatch($table, $data, $where){
		$this->db->update_batch($table, $data, $where);
	}
	
	function updateQueryText($table, $set, $where){
		$this->dbQuery('UPDATE '.$table.' SET '.$set.' WHERE '.$where.'');
	}
	
	function updateConcat($table, $where=1, $field, $fieldvalue){
		$this->dbQuery('UPDATE '.$table.' SET '.$field.'=CONCAT('.$field.',"'.addslashes($fieldvalue).'") WHERE '.$where.'');
	}	
}

?>