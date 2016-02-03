<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Commonmodel extends CI_Model {
	
    function __construct() {
        // Call the Model constructor
        parent::__construct();	
    }
	
	/***
		$ntype 0-other, 1-salary, 2-performance, 3-timeoff, 4-disciplinary, 5-actions
	***/
	function addMyNotif($empID, $ntexts, $ntype=0, $isNotif=0, $sID=''){
		$insArr['empID_fk'] = $empID;
		
		if($sID!='') $insArr['sID'] = $sID;
		else if($this->user==false) $insArr['sID'] = 0;
		else $insArr['sID'] = $this->user->empID;
				
		$insArr['ntexts'] = addslashes($ntexts);
		$insArr['dateissued'] = date('Y-m-d H:i:s');
		$insArr['ntype'] = $ntype;
		$insArr['isNotif'] = $isNotif;
		if($insArr['sID']==0 && $this->user!=false){
			$insArr['userSID'] = $this->user->username.'|'.$this->user->name;
		}
		
		$this->dbmodel->insertQuery('staffMyNotif', $insArr);
	}
		
	function photoResizer($source_image, $destination_filename, $width = 200, $height = 150, $quality = 70, $crop = true){
		if( ! $image_data = getimagesize( $source_image ) ){
			return false;
		}
		
		switch( $image_data['mime'] )
		{
			case 'image/gif':
				$get_func = 'imagecreatefromgif';
				$suffix = ".gif";
			break;
			case 'image/jpeg';
				$get_func = 'imagecreatefromjpeg';
				$suffix = ".jpg";
			break;
			case 'image/png':
				$get_func = 'imagecreatefrompng';
				$suffix = ".png";
			break;
		}
		
		$img_original = call_user_func( $get_func, $source_image );
		$old_width = $image_data[0];
		$old_height = $image_data[1];
		$new_width = $width;
		$new_height = $height;
		$src_x = 0;
		$src_y = 0;
		$current_ratio = round( $old_width / $old_height, 2 );
		$desired_ratio_after = round( $width / $height, 2 );
		$desired_ratio_before = round( $height / $width, 2 );
		/**
		 * If the crop option is left on, it will take an image and best fit it
		 * so it will always come out the exact specified size.
		 */
		if( $crop ){
			/**
			 * create empty image of the specified size
			 */
			$new_image = imagecreatetruecolor( $width, $height );
			/**
			 * Landscape Image
			 */
			if( $current_ratio > $desired_ratio_after ){
				$new_width = $old_width * $height / $old_height;
			}
			/**
			 * Nearly square ratio image.
			 */
			if( $current_ratio > $desired_ratio_before && $current_ratio < $desired_ratio_after ){
				if( $old_width > $old_height ){
					$new_height = max( $width, $height );
					$new_width = $old_width * $new_height / $old_height;
				}else{
					$new_height = $old_height * $width / $old_width;
				}
			}
			/**
			 * Portrait sized image
			 */
			if( $current_ratio < $desired_ratio_before  ){
				$new_height = $old_height * $width / $old_width;
			}
			/**
			 * Find out the ratio of the original photo to it's new, thumbnail-based size
			 * for both the width and the height. It's used to find out where to crop.
			 */
			$width_ratio = $old_width / $new_width;
			$height_ratio = $old_height / $new_height;
			/**
			 * Calculate where to crop based on the center of the image
			 */
			$src_x = floor( ( ( $new_width - $width ) / 2 ) * $width_ratio );
			$src_y = round( ( ( $new_height - $height ) / 2 ) * $height_ratio );
		}
		/**
		 * Don't crop the image, just resize it proportionally
		 */
		else{
			if( $old_width > $old_height ){
				$ratio = max( $old_width, $old_height ) / max( $width, $height );
			}else{
				$ratio = max( $old_width, $old_height ) / min( $width, $height );
			}
			$new_width = $old_width / $ratio;
			$new_height = $old_height / $ratio;
			$new_image = imagecreatetruecolor( $new_width, $new_height );
		}
				
		if($image_data['mime']=='image/png'){
			imagealphablending( $new_image, false );
			imagesavealpha( $new_image, true );
		}
		
		/**
		 * Where all the real magic happens
		 */
		imagecopyresampled( $new_image, $img_original, 0, 0, $src_x, $src_y, $new_width, $new_height, $old_width, $old_height );
		
		switch( $image_data['mime'] )
		{
			case 'image/gif':
				imagegif($tmp, $path);
			break;
			case 'image/jpeg';
				imagejpeg( $new_image, $destination_filename, $quality );				
			break;
			case 'image/png':
				imagepng( $new_image, $destination_filename, 0 );
			break;
		}
		
		imagedestroy( $new_image );
		imagedestroy( $img_original );
		/**
		 * Return true because it worked and we're happy. Let the dancing commence!
		 */
		return true;
	}
	
	function countResults($type){
		$cnt = 0;
		if($type=='cis'){
			$cnt = $this->dbmodel->getSingleField('staffCIS', 'COUNT(cisID) AS cnt', 'status=0');
		}else if($type=='coaching'){
			$condition = '';
			if($this->access->accessHR==false){
				$ids = '"",'; //empty value for staffs with no under yet
				$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);				
				foreach($myStaff AS $m):
					$ids .= $m->empID.',';
				endforeach;
				$condition .= ' AND empID_fk IN ('.rtrim($ids,',').')';
			}			
			$cnt = $this->dbmodel->getSingleField('staffCoaching', 'COUNT(coachID) AS cnt', 'status=0 AND coachedEval<="'.date('Y-m-d').'"'.$condition);
		}else if($type=='updateRequest'){
			$cnt = $this->dbmodel->getSingleField('staffUpdated', 'COUNT(updateID) AS cnt', 'status=0');
		}else if($type=='pendingCOE'){
			$cnt = $this->dbmodel->getSingleField('staffCOE', 'COUNT(coeID) AS cnt', 'status=0');
		}else if($type=='staffLeaves'){
			if($this->access->accessFull==true){
				$query = $this->dbmodel->dbQuery('SELECT COUNT(leaveID) AS cnt FROM staffLeaves LEFT JOIN staffs ON empID=empID_fk WHERE status=0 AND iscancelled=0 AND supervisor="'.$this->user->empID.'" LIMIT 1');
				$r = $query->row();
				$cnt = $r->cnt;
			}else{
				$ids = '"",'; //empty value for staffs with no under yet
				$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);				
				foreach($myStaff AS $m):
					$ids .= $m->empID.',';
				endforeach;
				
				$cnt = $this->dbmodel->getSingleField('staffLeaves', 'COUNT(leaveID) AS cnt', 'status=0 AND iscancelled=0 AND empID_fk IN ('.rtrim($ids,',').')');
			}
			
			if($this->access->accessHR==true){
				$cnt += $this->dbmodel->getSingleField('staffLeaves', 'COUNT(leaveID) AS cnt', '(status=1 OR status=2) AND ((iscancelled=0 AND hrapprover=0) OR iscancelled=3 OR iscancelled=4 OR matStatus=1 OR matStatus=4)');
			}
		}else if($type=='nte'){	
			//if HR
			if($this->access->accessHR==true){
				$cnt = $this->dbmodel->getSingleField('staffNTE', 'COUNT(nteID) AS cnt', '(status=1 AND (nteprinted="" OR (nteprinted!="" AND nteuploaded=""))) OR ((status=0 OR status=3) AND (carprinted="" OR (carprinted!="" AND caruploaded="")))', 'LEFT JOIN staffs ON empID=empID_fk');				
			}else{
				$ids = '"",'; //empty value for staffs with no under yet
				$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);				
				foreach($myStaff AS $m):
					$ids .= $m->empID.',';
				endforeach;
				
				$cnt = $this->dbmodel->getSingleField('staffNTE', 'COUNT(nteID) AS cnt', 'empID_fk IN ('.rtrim($ids,',').') AND ((status=1 AND nteprinted="") OR (status=0 AND carprinted="") OR (status=1 AND nteprinted!="" AND nteuploaded="") OR (status=0 AND carprinted!="" AND caruploaded=""))');
			}			
		}else if($type=='eval90th'){			
			$cnt = $this->dbmodel->getSingleField('staffEvaluation', 'COUNT(evalID) AS cnt', 'status=1 AND hrStatus>0');
		}else if($type=='unpublishedLogs'){
			$condUsers = '';
			if($this->config->item('timeCardTest')==true){ ///////////////TEST USERS ONLY REMOVE THIS IF LIVE TO ALL
				$testUsers = $this->commonM->getTestUsers();
				$condUsers = ' AND empID_fk IN ('.implode(',', $testUsers).')';
			}			
			$cnt = $this->dbmodel->getSingleField('tcStaffLogPublish', 
					'COUNT(slogID)', 
					'publishBy="" AND showStatus=1 AND slogDate!="'.date('Y-m-d').'"'.$condUsers, 
					'LEFT JOIN staffs ON empID=empID_fk');
		}else if($type=='timelogRequests'){
			$condition = '';
			if($this->access->accessFullHR==false){
				$ids = '"",'; //empty value for staffs with no under yet
				$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);				
				foreach($myStaff AS $m):
					$ids .= $m->empID.',';
				endforeach;
				$condition .= ' AND empID_fk IN ('.rtrim($ids,',').')';
			}
		
			$cnt = $this->dbmodel->getSingleField('tcTimelogUpdates', 'count(logDate)', 'status=1'.$condition, 'LEFT JOIN staffs ON empID=empID_fk');
		}else if( $type == 'medrequests' ){
			$cnt = $this->dbmodel->getSingleField('staffMedRequest', 'count(medrequestID)', 'status=0');
		}
		
		return $cnt;
	}
	
	function getStaffUnder($empID, $level){
		$query = $this->dbmodel->dbQuery('SELECT empID, CONCAT(fname," ",lname) AS name FROM staffs WHERE supervisor="'.$empID.'" OR supervisor IN (SELECT DISTINCT empID FROM staffs e WHERE levelID_fk!=0 AND levelID_fk<"'.$level.'" AND supervisor="'.$empID.'")');
		return $query->result();
	}
	
	function getSupervisors(){
		$query = $this->dbmodel->dbQuery('SELECT CONCAT(fname," ",lname) AS name, empID FROM staffs WHERE empID IN (SELECT DISTINCT supervisor FROM staffs WHERE supervisor != 0)');
		$a = array();
		foreach($query->result() AS $q):
			$a[$q->empID] = $q->name;
		endforeach;
		return $a;		
	}
	
	
	/******
		Check if staff is under me return false if not
		Accepts $username = username or empID
		if username is the same with login user and not an hr, full or finance it returns FALSE
	******/
	function checkStaffUnderMe($username){
		$valid = true;
		if(md5($username.'dv') != $this->session->userdata('u')){
			if($this->access->accessFullHRFinance==false){
				$query = $this->dbmodel->dbQuery('SELECT username FROM staffs WHERE (supervisor="'.$this->user->empID.'" OR supervisor IN (SELECT DISTINCT empID FROM staffs e WHERE levelID_fk!=0 AND levelID_fk<"'.$this->user->level.'" AND supervisor="'.$this->user->empID.'")) AND (username="'.$username.'" OR empID="'.$username.'")');
				$row = $query->row();
				if(!isset($row->username)) $valid = false;
			}			
		}	
		return $valid;
	}
		
	function checkStaffUnderMeByID($empID){
		$query = $this->dbmodel->dbQuery('SELECT empID FROM staffs WHERE (supervisor="'.$this->user->empID.'" OR supervisor IN (SELECT DISTINCT empID FROM staffs e WHERE levelID_fk!=0 AND levelID_fk<"'.$this->user->level.'" AND supervisor="'.$this->user->empID.'")) AND empID="'.$empID.'"');
		$r = $query->row();
		if(isset($r->empID))
			return true;
		else
			return false;		
	}
	
	function getStaffSupervisorsID($id){
		$cnt = 0;
		$supArr = array();		
		$sup = $this->dbmodel->getSingleField('staffs', 'supervisor', 'empID="'.$id.'"');	
		//get supervisors id until 2nd level manager
		while($sup !=0 && $cnt<2){
			$supArr[] = $sup;
			$sup = $this->dbmodel->getSingleField('staffs', 'supervisor', 'empID="'.$sup.'"');
			$cnt++;
		}
		return $supArr;
	}
	
	public function getImSupervisor($supervisor){
		$grow = $this->dbmodel->getSingleInfo('staffs', 'email, CONCAT(fname," ",lname) AS name, supervisor, (SELECT s.email FROM staffs s WHERE s.empID=staffs.supervisor LIMIT 1) AS supEmail', 'empID="'.$supervisor.'"');
		
		return $grow;		
	}
	
	public function getAllStaffs(){			
		if(isset($_POST['includeinactive'])) $condition = '';
		else $condition = 'staffs.active=1';

	
		if($this->user->access==''){
			$ids = '';
			$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);				
			foreach($myStaff AS $m):
				$ids .= $m->empID.',';
			endforeach;
			if($ids!=''){
				$condition .= ((!empty($condition))?' AND ':'').'empID IN ('.rtrim($ids,',').')';
			}
		}
										
		return $this->dbmodel->getQueryResults('staffs', 'empID, username, lname, fname, newPositions.title, shift, dept, (SELECT CONCAT(fname," ",lname) AS name FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS leader, staffHolidaySched', (($condition=="")?'1':$condition), 'LEFT JOIN newPositions ON posId=position', 'lname');
	}
	
	
	public function getSchedTimeArray(){
		$arr = array();
		$arr[0] = '';
		$query = $this->dbmodel->getQueryResults('tcCustomSchedTime', 'timeID, timeValue', 1, '', 'timeValue');
		foreach($query AS $q){
			$arr[$q->timeID] = $q->timeValue;
		}
		return $arr;
	}
	
	public function getSchedHourArray(){
		$arr = array();
		$arr[0] = '';
		$query = $this->dbmodel->getQueryResults('tcCustomSchedTime', 'timeID, timeHours', 1, '', 'timeValue');
		foreach($query AS $q){
			$arr[$q->timeID] = $q->timeHours;
		}
		return $arr;
	}
	
	function customTimeArrayByCat(){
		$timeArr = array();
		//$schedTimes = $this->dbmodel->getQueryResults('tcCustomSchedTime', '*', 1, '', 'timeValue');
		$schedTimes = $this->dbmodel->getQueryResults('tcCustomSchedTime', 'tcCustomSchedTime.*, SUBSTRING(timeValue, 1, 8) AS str', 'status=1');
		
		foreach ($schedTimes AS $key => $row) {
			$volume[$key]  = date('H:i:s', strtotime($row->str));
		}
		
		if(!empty($schedTimes) && !empty($volume))
			array_multisort($volume, SORT_ASC, $schedTimes);
			
		foreach($schedTimes AS $t):
			if($t->category==0)
				$timeArr[$t->timeID]['name'] = $t->timeName;
			else
				$timeArr[$t->category][$t->timeID] = $t->timeValue.'|'.$t->timeName.'|'.$t->timeHours.'|'.$t->timeID;
		endforeach;
		
		return $timeArr;
	}
	
	//returns an array of empID
	public function getTestUsers(){
		$users = array();
		
		$query = $this->dbmodel->getQueryResults('tcStaffSchedules', 'DISTINCT empID_fk', 'effectiveend="0000-00-00"');
		foreach($query AS $q)
			$users[] = $q->empID_fk;
			
		return $users;
	}
	
	
}

?>