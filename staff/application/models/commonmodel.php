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

	public function uploadFile($file, $target, $fileName){
		//extract extension
		$ext = explode('.', $file['signed_doc']['name']);
		//create the new filename
		$newFileName = $fileName.'.'.end($ext);
		$complete = '';

		// if( $ext == 'pdf'){
			move_uploaded_file($file['signed_doc']['tmp_name'], $target.$newFileName);
			$complete = $newFileName;
			return $complete;
		// }
		// else{
		// 	return FALSE;
		// }
		exit();
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
	
	//own means for supervisor
	function countResults($type, $own = false){
		$cnt = 0;
		if($type=='cis'){
			$condition = '';
			if( $own === true ){
				
				$condition = 'AND preparedby ='.$this->user->empID;
			}

			$cnt = $this->dbmodel->getSingleField('staffCIS', 'COUNT(cisID) AS cnt', 'status=0 '.$condition);
		}
		else if($type=='coaching'){
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
		}else if($type=='evalNotif'){
			$cnt = $this->dbmodel->getSingleField('staffEvaluationNotif', 'COUNT(notifyId) AS cnt', 'status = 0 OR status = 1 AND empid ='.$this->user->empID);
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
		}else if($type=='incidentreport'){
			 $cnt = $this->dbmodel->getSingleField('staffReportViolation', 'count(reportID)', 'status>=1 AND status<10', 'dateSubmitted DESC');
		} else if( $type == 'medrequests' ){
 			$cnt = $this->dbmodel->getSingleField('staffMedRequest', 'count(medrequestID)', 'status=0');
		} else if( $type == 'hdmf_loans' ){
			$cnt = $this->dbmodel->getSingleField('staff_hdmf_loan', 'count(hdmf_loan_id)', 'hdmf_loan_status=0');
		} elseif( $type == 'kudos'){
			$kWhere = 'kudosRequestStatus = 1 AND kudosReceiverSupID = '.$this->user->empID;
			if($this->access->accessHR){
				$kWhere .= ' OR kudosRequestStatus = 2';
			}

			$cnt = $this->dbmodel->getSingleField('kudosRequest', 'COUNT(kudosRequestID)', $kWhere );
//			$cnt = $this->dbmodel->getSingleField('kudosRequest', 'COUNT(kudosRequestID)', 'kudosRequestStatus = 1 AND kudosReceiverSupID = '.$this->user->empID);
		} elseif($type == 'notifStatus'){
			// SELECT COUNT( (SELECT reply_empUser FROM hr_cs_msg WHERE cs_msg_postID_fk = cs_post_id AND reply_empUser != 'bpodutan' ORDER BY cs_msg_date_submitted DESC LIMIT 1) ) AS cnt FROM hr_cs_post WHERE cs_post_empID_fk = 468
			$cnt = $this->dbmodel->getSingleField('hr_cs_post', ' COUNT( (SELECT reply_empUser FROM hr_cs_msg WHERE cs_msg_postID_fk = cs_post_id AND reply_empUser != "'.$this->user->username.'" ORDER BY cs_msg_date_submitted DESC LIMIT 1) ) AS cnt ', 'cs_post_status < 3 AND cs_post_empID_fk ='.$this->user->empID );
			//$cnt = $this->dbmodel->getSingleField('hr_cs_post', 'COUNT(notifStatus)', 'notifStatus = 0 AND cs_post_empID_fk= '.$this->user->empID);
		} elseif($type == 'hr_accounting'){
			//$cnt = $this->dbmodel->getSingleField('hr_cs_post', ' COUNT( (SELECT reply_empUser FROM hr_cs_msg WHERE cs_msg_postID_fk = cs_post_id AND reply_empUser != "'.$this->user->username.'" AND cs_msg_type != 2 ORDER BY cs_msg_date_submitted DESC LIMIT 1) ) AS cnt ', 'cs_post_agent ='.$this->user->empID.' AND cs_post_status = 1' );
			//getQueryResults($table, $fields, $where=1, $join='', $orderby='', $trace=false){
			// $cnt_dummy = $this->dbmodel->getQueryResults('hr_cs_post', ' (SELECT reply_empUser FROM hr_cs_msg WHERE cs_msg_postID_fk = cs_post_id  AND cs_msg_type != 2 ORDER BY cs_msg_date_submitted DESC LIMIT 1)  AS cnt ', 'cs_post_agent ='.$this->user->empID.' AND cs_post_status = 1' );
			
			// foreach( $cnt_dummy as $c ){
				
			// 	if( $c->cnt != $this->user->username ){
			// 		$cnt++;
			// 	}
			// }
		 	$string = '';
		 	// dd($this->user, false);
		 	// dd($this->access);
			if( $this->access->accessMainHR == true ){
				$string = ' AND report_related = 0';
			} else if( $this->access->accessMainFinance == true ){
				$string = ' AND report_related = 1';
			}

			$cnt_dummy = $this->dbmodel->getQueryResults('hr_cs_post INNER JOIN staffs ON staffs.empID = hr_cs_post.cs_post_empID_fk LEFT JOIN hr_cs_msg ON hr_cs_msg.cs_msg_postID_fk = hr_cs_post.cs_post_id LEFT JOIN assign_category ON assign_category.categorys = hr_cs_post.assign_category', 'COUNT(cs_post_id) AS cnt', 'cs_post_status = 0 '.$string);
			$cnt = $cnt_dummy[0]->cnt;
		}
		return $cnt;
	}
	
	function getStaffUnder($empID, $level){
		$query = '';
		///tweak for design team
		if($level==2 OR $level==3 ){
			$myDesign = $this->dbmodel->getSingleInfo('staffs', 'supervisor, grp', 'empID="'.$empID.'"', 'LEFT JOIN newPositions ON posID=position');
			if($myDesign->grp=='Design'){
				$supervisors = '';
				$supQuery = $this->dbmodel->getQueryResults('staffs', 'DISTINCT empID', 'empID IN (SELECT DISTINCT supervisor FROM staffs WHERE supervisor!=0) AND dept="Production" AND grp!="Editing"', 'LEFT JOIN newPositions ON posID=position');
				if(count($supQuery)>0){
					foreach($supQuery AS $sup)
						$supervisors .= $sup->empID.',';
				}
				
				$query = $this->dbmodel->dbQuery('SELECT empID, username, CONCAT(fname," ",lname) AS name FROM staffs WHERE supervisor IN ('.rtrim($supervisors, ',').')');
			}
		}
		
		
		if(empty($query)){
			$query = $this->dbmodel->dbQuery('SELECT empID, username, CONCAT(fname," ",lname) AS name FROM staffs WHERE supervisor="'.$empID.'" OR supervisor IN (SELECT DISTINCT empID FROM staffs e WHERE levelID_fk!=0 AND levelID_fk<"'.$level.'" AND supervisor="'.$empID.'")');
		}
		
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
		$valid = false;
		//dd($username, false);
		//if(md5($username.'dv') != $this->session->userdata('u')){

		if($this->access->accessFullHRFinance){
			return true;
		}

		$underMe = $this->getStaffUnder($this->user->empID, $this->user->level);
		
		foreach( $underMe as $staff ){
			if( is_numeric($username) ){
				if( $staff->empID == $username ){
					$valid = true;
					break;
				}	
			} else{
				if( $staff->username == $username ){
					$valid = true;
					break;
				}
			}	

		}
				//$query = $this->dbmodel->dbQuery('SELECT username FROM staffs WHERE (supervisor="'.$this->user->empID.'" OR supervisor IN (SELECT DISTINCT empID FROM staffs e WHERE levelID_fk!=0 AND levelID_fk<"'.$this->user->level.'" AND supervisor="'.$this->user->empID.'")) AND (username="'.$username.'" OR empID="'.$username.'")');
				//$row = $query->row();
				//if(!isset($row->username)) $valid = false;
			//}			
		//}	

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

	public function _getAllStaff($key, $condition = ''){

		$tmp = array();
		$all_staff = $this->dbmodel->getQueryResults('staffs', 'empID, username, lname, fname, CONCAT(fname, " ", lname) AS "name", newPositions.title, shift, dept, (SELECT CONCAT(fname," ",lname) AS name FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS leader, staffHolidaySched', (($condition=="")?'1':$condition), 'LEFT JOIN newPositions ON posId=position', 'lname');
		foreach($all_staff as $staff){

			$tmp[ $staff->$key ] = $staff;
		} 
		return $tmp;
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
	
	//compute leave credits minus the incremental leave
	public function computeLastLeave( $start_date, $leave_credits ){
		
		$current_date = date('Y-m-d');		
		$start_date = date('Y-m-d', strtotime($start_date) );
		
		$current_date = date_create($current_date);
		$start_date = date_create( $start_date );
		
		$diff = date_diff($current_date, $start_date);
		$incremental_leave = $diff->format('%y');
		
		if( $incremental_leave >= $leave_credits ){
			return 0;
		} else {
			return $leave_credits - $incremental_leave;
		}
		
	}

	//compute difference of two dates and format
	//////////////////////////////////////////////////////////////////////
	//PARA: Date Should In YYYY-MM-DD Format
	//RESULT FORMAT:
	// '%y Year %m Month %d Day %h Hours %i Minute %s Seconds'        =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
	// '%y Year %m Month %d Day'                                    =>  1 Year 3 Month 14 Days
	// '%m Month %d Day'                                            =>  3 Month 14 Day
	// '%d Day %h Hours'                                            =>  14 Day 11 Hours
	// '%d Day'                                                        =>  14 Days
	// '%h Hours %i Minute %s Seconds'                                =>  11 Hours 49 Minute 36 Seconds
	// '%i Minute %s Seconds'                                        =>  49 Minute 36 Seconds
	// '%h Hours                                                    =>  11 Hours
	// '%a Days                                                        =>  468 Days
	//////////////////////////////////////////////////////////////////////
	public function dateDifference( $date1, $date2, $format = '%a'){

		$date1 = date('Y-m-d H:i:s', strtotime($date1) );
		$date2 = date('Y-m-d H:i:s', strtotime($date2) );

		$date1 = date_create( $date1 );
		$date2 = date_create( $date2 );	

		$diff = date_diff( $date1, $date2 );
		
		$return =  $diff->format( $format );
		return $return;//date('H:i:s', strtotime($return));
	}
	
	// public function dateDifferenceDays( $date1, $date2, $format = '%a'){

	// 	$date1 = date('Y-m-d H:i:s', strtotime($date1) );
	// 	$date2 = date('Y-m-d H:i:s', strtotime($date2) );

	// 	$date1 = date_create( $date1 );
	// 	$date2 = date_create( $date2 );	

	// 	$diff = date_diff( $date1, $date2 );
		
	// 	$return =  $diff->format( $format );
	// 	return $return;//date('m', strtotime($return));
	// }

	public function slugify($text)
	{
	  // replace non letter or digits by -
	  $text = preg_replace('~[^\pL\d]+~u', '_', $text);

	  // transliterate
	  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	  // remove unwanted characters
	  $text = preg_replace('~[^-\w]+~', '', $text);

	  // trim
	  $text = trim($text, '_');

	  // remove duplicate -
	  $text = preg_replace('~-+~', '_', $text);

	  // lowercase
	  $text = strtolower($text);

	  if (empty($text)) {
	    return 'n-a';
	  }

	  return $text;
	}

	//check for the correct answers
	public function check_answers( $key, $answers, $return = 'score' ){
		
		$cnt = count($key);
		$results = [];
		$scores = 0;
		$check = false;
		if( $return == 'score' ){
			for ($x=0; $x < $cnt; $x++) { 
				if( isset($answers[$x]) AND $key[$x] == $answers[$x] ){
					$scores++;
				}
			}
			return $scores;	
		} else if( $return == 'check' ){
			if( $key == $answers ){
				$check = true;
			}
			return $check;
		}
		
	}
	
}

?>