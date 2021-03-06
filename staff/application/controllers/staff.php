<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Staff extends MY_Controller {
 
	public function __construct(){
		parent::__construct();
		
		$this->load->model('Staffmodel', 'staffM');		
		$this->load->model('Schedulemodel', 'schedM');
	}

		
	public function index(){
		$data['content'] = 'index';	
		
		if(!empty($_POST)){
			if($_POST['submitType']=='announcement'){
				$this->dbmodel->insertQuery('staffAnnouncements', array('announcement'=>addslashes($_POST['aVal']), 'createdBy'=>$this->user->empID));
				$this->commonM->addMyNotif($this->user->empID, 'You created new announcement.', 5);
				exit;
			}else if($_POST['submitType']=='updateAnn'){
				$aID = $this->dbmodel->getSingleField('staffAnnouncements', 'aID', '1 ORDER BY timestamp DESC');
				
				$this->dbmodel->updateQuery('staffAnnouncements', array('aID'=>$aID), array('announcement'=>addslashes($_POST['aVal']), 'updatedBy'=>$this->user->empID.'|'.date('Y-m-d H:i:s')));
				$this->commonM->addMyNotif($this->user->empID, 'You updated announcement.', 5);
				exit;
			}else if($_POST['submitType']=='uploadProfile' && !empty($_FILES['pfile'])){				
				$n = array_reverse(explode('.', $_FILES['pfile']['name']));	
				if($n[0]=='jpg'){
					$dir = UPLOAD_DIR.$this->user->username;
					if (!file_exists($dir)) {
						mkdir($dir, 0755, true);
						chmod($dir.'/', 0777);
					}
					move_uploaded_file($_FILES['pfile']['tmp_name'], $dir.'/'.$this->user->username.'.'.$n[0]);	
					$this->commonM->addMyNotif($this->user->empID, 'You updated your profile picture.', 5);
				}else{
					echo '<script>alert("Invalid file type. Please upload .jpg file only");</script>';
				}
			}else if($_POST['submitType']=='uploadPER'){							
				$dir = UPLOAD_DIR.$this->user->username;
				if (!file_exists($dir)) {
					# $data['dir'] doesn't have value 
					mkdir($dir, 0755, true);
					chmod($dir.'/', 0777);
				}
				
				$stark = array_reverse(explode('.', $_FILES['pfile']['name']));	
				$filename = str_replace(' ','',$_POST['pTypeName']).'_'.date('YmdHis').'.'.$stark[0];
				if(move_uploaded_file($_FILES['pfile']['tmp_name'], $dir.'/'.$filename)){					
					//insert to staffUploads	
					$up['empID_fk'] = $this->user->empID;
					$up['uploadedBy'] = $this->user->empID;
					$up['docName'] = $_POST['pTypeName'];
					$up['fileName'] = $filename;
					$up['dateUploaded'] = date('Y-m-d H:i:s');					
					$this->dbmodel->insertQuery('staffUploads', $up);
					
					//insert to staffPerEmpStatus
					$emp['perID_fk'] = $_POST['perTypeID'];
					$emp['empID_fk'] = $this->user->empID;
					$emp['perType'] = 0;
					$emp['perValue'] = addslashes($this->user->fname.' uploaded '.$_POST['pTypeName'].' file. Click <a href="'.$this->config->base_url().$dir.'/'.$filename.'">here</a> to view the file.');
					$emp['forVerification'] = 1;
					$emp['adder'] = $this->user->username;
					$emp['dateAdded'] = date('Y-m-d H:i:s');
					$this->dbmodel->insertQuery('staffPerEmpStatus', $emp);
					
					//for notification
					$this->commonM->addMyNotif($this->user->empID, 'Uploaded '.$_POST['pTypeName'].' file. Click <a href="'.$this->config->base_url().$dir.'/'.$filename.'">here</a> to view the file.', 5);
					echo '<script>alert("File has been uploaded. This is still waiting for HR verification."); location.href="'.$_SERVER['REQUEST_URI'].'"; </script>';
					exit;
				}else{
					echo '<script>alert("Unable to upload file. Please upload less than 2MB."); location.href="'.$_SERVER['REQUEST_URI'].'";</script>';
					exit;
				}
			} else if( isset($_POST['submitType']) AND $_POST['submitType'] == 'leaderFeedback' ){
				$insert_array = array();
				$insert_array['supervisor'] = $this->input->post('sel_leader');
				$insert_array['from_whom'] = $this->user->empID;
				$insert_array['feedback'] = $this->input->post('txt_leaderFeedback');
				$insert_array['date_submitted'] = date('Y-m-d H:i:s');
				
				$insert_id = $this->dbmodel->insertQuery('staffLeaderFeedback', $insert_array);
				if( isset($insert_id) AND !empty($insert_id) ){
					$data['confirm'] = 'Your feedback has been noticed. Thank you for noticing this notice.';
				}
			}
			//Valentines Day Special
			elseif ($_POST['submitType'] == 'sendvalentines') {
				$empID = $this->dbmodel->getSingleField('staffs', 'empID', 'username = "'.$_POST['to'].'"');
				$message = "This personal message is from: ".$_POST['from']."<br/>";
				$message .= $_POST['message'];
				if($empID){
					$message .= '
						<br/>
						<img src="'.$this->config->base_url().'includes/images/'.$_POST['card'].'.jpg" />
					';
					$this->commonM->addMyNotif($empID, $message, 6, 1);
                    echo "Message Sent";
                    //echo '<script>location.reload();</script>';
				}
				else{
					echo "User not found";
				}
				exit;
			}
		}
		
		if($this->user!=false){
			$data['row'] = $this->user;
			$data['announcement'] = '';	
				
			$anQuery = $this->dbmodel->getSingleInfo('staffAnnouncements', 'announcement', 1, '', 'timestamp DESC');
			if(isset($anQuery->announcement))
				$data['announcement'] = stripslashes($anQuery->announcement);
		
			//PER EMP STATUS
			if($this->user->perStatus<100){
				$data['requirements'] = $this->dbmodel->getQueryResults('staffPerRequirements', 'perID, perName, perDescShort');
				$data['perUploaded'] = $this->dbmodel->getQueryResults('staffPerEmpStatus', 'perID_fk', 'empID_fk="'.$this->user->empID.'" AND perType=1');
				$data['perForVerify'] = $this->dbmodel->getQueryResults('staffPerEmpStatus', 'perID_fk', 'empID_fk="'.$this->user->empID.'" AND perType=0 AND forVerification=1');
			}
			
			//feedback logic
			$data['view_feedback'] = true;
			
			//get all leaders
			$leaders = $this->dbmodel->getQueryArrayResults('staffs', 'empID, CONCAT(fname, " ", lname) AS "name"', 'is_supervisor = 1 AND active = 1 AND office = "PH-Cebu" ORDER BY fname');
			$data['leaders'] = $leaders;
		
			//end of feedback logic
		}
		
		$this->load->view('includes/template', $data);
	}
	
	public function hello(){
		if($this->uri->segment(2)=='empID' && isset($_POST['empID'])){			
			$sarap = $this->dbmodel->getSingleInfo('staffs', 'empID, username', 'empID="'.$_POST['empID'].'"');
		}else if(isset($_POST['username']) && $_POST['username'] !=''){
			$sarap = $this->dbmodel->getSingleInfo('staffs', 'empID, username', 'username="'.$_POST['username'].'"');
		}else{
			$sarap = $this->dbmodel->getSingleInfo('staffs', 'empID, username', 'username="'.$this->uri->segment(2).'"');
		}		
	
		if(isset($sarap->empID)){
			$this->session->set_userdata('uid',$sarap->empID);
			$this->session->set_userdata('u',md5($sarap->username.'dv'));
			
			//session_start();
			$_SESSION['u'] = $sarap->username; 
			
			header("Location:".$_SERVER['HTTP_REFERER']);
			exit;
		}else{
			echo '<script> alert("No staff found."); location.href="'.$this->config->base_url().'"; </script>';
		}
	}
		
	public function login(){
		$data = array();
		if($this->user!=false){
			header("Location:".$this->config->base_url());
			exit;
		}else if(!empty($_POST)){	
			$username = $this->input->post('username');
			$pw = $this->input->post('password');
			
			if(empty($username) || empty($pw)){
				$data['error'] = 'All fields are required.';
			}else{
				$query = $this->checklogged($username, $pw);
				$row = $query->row();				
				if($query->num_rows()==0){
					$data['error'] = 'Unable to login. Check your login details.';
				}else if($row->password != md5($pw)){
					$data['error'] = 'Invalid password.';
				}else if($row->active==0 || $row->active==2){
					$data['error'] = 'Your account has been deactivated. Please contact HR.';
				}else{
					$this->session->set_userdata('uid', $row->empID);			
					$this->session->set_userdata('u', md5($row->username.'dv'));
					$this->session->set_userdata('popupnotification', true);
					
					if($row->username=='lmarinas' || $row->username=='lmarinastest' || $row->username=='mabellana' || $row->username=='flariosa')					
						$this->session->set_userdata('testing', true);
					
					session_start();
					$_SESSION['u'] = $row->username; 
					
					//insert login details
					$logData['type'] = 0; //0-login; 1-logout
					$logData['username'] = $row->username;					
					$logData['IP'] = $this->input->ip_address();
					$logData['userAgent'] = $this->input->user_agent();
					$logData['timestamp'] = date('Y-m-d H:i:s');
					$this->dbmodel->insertQuery('staffLogAccess', $logData);
					
					if(md5($row->username) == $row->password){
						header('Location:'.$this->config->base_url().'changepassword/required/');
					}else{
						if($this->input->post('urlRequest') == '/staff/login/') header('Location:'.$this->config->base_url());
						else header('Location:'.$this->config->item('career_url').$this->input->post('urlRequest'));
					}
					exit;
				}
			}					
		}		
		
		$this->load->view('includes/template', $data);	
	}
	
	public function logout(){
		//insert logut details
		if(!empty($_SESSION['u'])){
			$logData['type'] = 1; //0-login; 1-logout
			$logData['username'] = $_SESSION['u'];					
			$logData['IP'] = $this->input->ip_address();
			$logData['userAgent'] = $this->input->user_agent();
			$logData['timestamp'] = date('Y-m-d H:i:s');
			$this->dbmodel->insertQuery('staffLogAccess', $logData);
		}
		
		$this->session->sess_destroy();
		session_unset(); 
		session_destroy(); 
		header('Location:'.$this->config->base_url());
		exit;
	}
	
	public function forgotpassword(){
		$data['content'] = 'forgotpassword';
		
		if($this->uri->segment(2)!=''){
			$username = $this->dbmodel->getSingleField('staffs', 'username', 'md5(username)="'.$this->uri->segment(2).'"');
			if($username!=''){
				$this->dbmodel->updateQuery('staffs', array('username'=>$username), array('password'=>md5($username)));
				echo '<script>alert("Password reset the same as your username."); window.location.href="'.$this->config->base_url().'"</script>';
				exit;
			}
		}
		
		if(!empty($_POST)){
			$username = '';
			$type = '';
			if(!empty($_POST['username'])){
				$type = 'username';
				$info = $this->dbmodel->getSingleInfo('staffs', 'username, email, fname', 'active=1 AND username="'.trim($_POST['username']).'"');
			}else if(!empty($_POST['email'])){
				$type = 'email';
				$info = $this->dbmodel->getSingleInfo('staffs', 'username, email, fname', 'active=1 AND email="'.trim($_POST['email']).'"');
			}
			
			if(count($info)==0){
				echo ucfirst($type).' "'.$_POST[$type].'" is not found.';
			}else{
				$this->emailM->emailForgotPassword($info->email, $info->fname, $info->username);
				echo 'Request to reset password sent. Please check your email address '.$info->email.'.';
			}
			exit;
		}
		
		$this->load->view('includes/templatecolorbox', $data);		
	}
	
	public function changepassword(){
		$data['content'] = 'changepassword';
		$segment2 = $this->uri->segment(2);
		if($this->user!=false){
			$data['error'] = '';
			$data['updated'] = false;
			if(!empty($_POST)){ 	
				if($this->user->password!=md5($_POST['curpassword']))
					$data['error'] = 'Current password is not correct.<br/>';				
				if($_POST['newpassword'] != $_POST['confirmpassword'])
					$data['error'] .= 'New and confirm passwords does not match.';
				else if($this->user->username==$_POST['newpassword'])
					$data['error'] .= 'Password should not be your username.';
				
				if(empty($data['error'])){	
					$this->dbmodel->updateQuery('staffs', array('empID'=>$this->user->empID), array('password'=>md5($_POST['newpassword'])));
					$data['updated'] = true;
					unset($_POST);
					
					if($segment2=='required'){
						header('Location:'.$this->config->base_url());
						exit;
					}
				}else{
					$_POST = array();
				}
			}			
		}
		if($segment2=='required')
			$this->load->view('includes/template', $data);	
		else
			$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function manageStaff(){
		$data['content'] = 'manageStaff';
		// $active = ' AND staffs.active = 1 ';
		///$this->textM->aaa($this->user, false);
		//$this->textM->aaa($this->access, true);
		if($this->user!=false){		
			if($this->user->access=='' AND $this->user->level==0){
				$data['access'] = false;
			}else{	
				$condition = 'staffs.office="PH-Cebu"';
										
				if($this->access->accessFullHRFinance==false AND $this->access->accessMedPerson == false){
					$ids = '"",'; //empty value for staffs with no under yet
					$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);						
					foreach($myStaff AS $m):
						$ids .= $m->empID.',';
					endforeach;
					
					//get coaching staffs
					$cstaffs = $this->dbmodel->getQueryResults('staffs', 'empID', 'coach="'.$this->user->empID.'"');				
					foreach($cstaffs AS $c){
						$ids .= $c->empID.',';
					}
					
					if($condition!='') $condition .= ' AND ';
					$condition .= 'empID IN ('.rtrim($ids,',').') /*fullHR*/';							
				}
								
				$flds = 'CONCAT(lname,", ",fname) AS name, ';
				if(isset($_POST['flds']) || (isset($_POST['submitType']) && $_POST['submitType']=='Generate Employee Report')){				
					if(isset($_POST['flds'])){
						foreach($_POST['flds'] AS $p):
							if($p=='title') $flds .= 'newPositions.title, ';
							else if($p=='active') $flds .= 'staffs.active, ';
							else if($p=='address') $flds .= 'address, city, country, zip, ';
							else if($p=='phone') $flds .= 'phone1, phone2, ';
							else if($p=='supervisor') $flds .= '(SELECT CONCAT(fname," ",lname) AS n FROM staffs ss WHERE ss.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS supervisor, ';
							else if($p == 'gov_record' ) $flds .= 'sss, tin, philhealth, hdmf, ';
							else if($p == 'hr_record' ) $flds .= ' hmoNumber, bankAccnt, ';
							else $flds .= $p.', ';
						endforeach;
					}

					if(isset($_POST['includeinactive']) AND !isset($_POST['includefloat']) ){
						$active .= ' AND staffs.active IN (0, 1)';
					} else if(!isset($_POST['includeinactive']) AND isset($_POST['includefloat'])){
						$active .= ' AND staffs.active IN (1, 2)';
					} else if(isset($_POST['includeinactive']) AND isset($_POST['includefloat'])) {
						$active .= ' AND staffs.active IN (0, 1, 2) ';
					} else {
						$active .= ' AND staffs.active IN (1) ';
					}
						
					$condition .= $active;

					if($_POST['submitType']=='Generate Employee Report'){
						$narr = array('lname', 'fname');
						$flds .= 'staffs.fname, ';
						$flds .= 'staffs.lname, ';						
						
						if(!isset($_POST['flds']) || !in_array('supervisor', $_POST['flds'])){
							array_push($narr,"supervisor");
							$flds .= '(SELECT CONCAT(fname," ",lname) AS n FROM staffs ss WHERE ss.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS supervisor, ';
						}
						if(!isset($_POST['flds']) || !in_array('startDate', $_POST['flds'])){
							array_push($narr,"startDate");
							$flds .= 'staffs.startDate, ';
						}
						if( in_array('gov_record', $_POST['flds']) ){
							if(($key = array_search('gov_record', $_POST['flds'])) !== false) {
								unset($_POST['flds'][$key]);
							}							
							array_push($narr, 'tin');
							array_push($narr, 'sss');
							array_push($narr, 'hdmf');
							array_push($narr, 'philhealth');
						}
						if( in_array('hr_record', $_POST['flds']) ){
							if(($key = array_search('hr_record', $_POST['flds'])) !== false) {
								unset($_POST['flds'][$key]);
							}
							array_push($narr, 'hmoNumber');
							array_push($narr, 'bankAccnt');
						}
						
						if(!isset($_POST['flds']))
							$_POST['flds'] = $narr;
						else
							$_POST['flds'] = array_merge($narr, $_POST['flds']);
						
						
					}
					
					$flds = rtrim($flds,', ');
					$data['fvalue'] = $_POST['flds'];
					
				}else{
					$flds = $flds.'email, newPositions.title, dept';
					$data['fvalue'] = array('email', 'title', 'dept');
					
					// if(isset($_POST['includeinactive']) && $_POST['includeinactive']=='on') $condition .= ' AND staffs.active IN (0, 1) ';
					// else if( isset($_POST['includefloat']) && $_POST['includefloat'] == 'on' ) $condition .= '';
					// else $condition .= 'AND staffs.active=1';

					if(isset($_POST['includeinactive']) AND !isset($_POST['includefloat']) ){
						$active .= ' AND staffs.active IN (0, 1)';
					} else if(isset($_POST['includeinactive']) AND !isset($_POST['includeinactive'])){
						$active .= ' AND staffs.active IN (1, 2)';
					} else if(isset($_POST['includeinactive']) AND isset($_POST['includeinactive'])) {
						$active .= ' AND staffs.active IN (0, 1, 2) ';
					} else {
						$active .= ' AND staffs.active IN (1) ';
					}

					
				}
			
			
				$data['query'] = $this->dbmodel->getQueryResults('staffs', 'empID, username, supervisor, '.$flds, $condition, 'LEFT JOIN newPositions ON posId=position LEFT JOIN orgLevel ON levelID=levelID_fk', 'lname');

				//dd($data['query']);
								
				if(isset($_POST) && !empty($_POST['submitType']) && $_POST['submitType']=='Generate Employee Report'){					
					header("Content-Type: application/xls");    
					header("Content-Disposition: attachment; filename=staffs.xls");  
					header("Pragma: no-cache"); 
					header("Expires: 0");
					
					$txt = '';
					$txt = "\r\n";
					$tab = "\t";
					$cntNewYork = count($data['fvalue']);
					for($i=0;$i<$cntNewYork;$i++){
						$txt .= strtoupper($this->textM->constantText('txt_'.$data['fvalue'][$i]))."\t";						
					}
					
					$txt .= "\r\n";
					
					$cntUS = count($data['fvalue']);
					foreach($data['query'] AS $q):
						for($j=0;$j<$cntUS;$j++){
							if( in_array($data['fvalue'][$j], array('sal', 'tin', 'hdmf', 'philhealth','sss', 'hmoNumber', 'bankAccnt') ) )
								$txt .= $this->textM->convertDecryptedText($data['fvalue'][$j],$q->$data['fvalue'][$j]);
							else if($data['fvalue'][$j]=='phone') $txt .= $q->phone1.((!empty($q->phone2))?','.$q->phone2:'');
							else if($data['fvalue'][$j]=='active') $txt .= $this->textM->constantArr('active')[ $q->active ];
							else $txt .= trim($q->$data['fvalue'][$j]);
							$txt .= $tab;
						}
						$txt .= "\r\n";
					endforeach;
					
					echo $txt;
					exit;
				}				
			}
		}		
		//$this->output->enable_profiler(true);
		$this->load->view('includes/template', $data);			
	}
			
	public function myinfo(){
		$this->staffpage('myinfo');		
	}
	
	public function staffinfo(){
		if(md5($this->uri->segment(2).'dv')==$this->session->userdata('u')){
			header('Location:'.$this->config->base_url().'myinfo/');
			exit;
		}			
		$this->staffpage('staffinfo');			
	}
	
    public function staffpage($page){
		$data['content'] = 'staffinfo'; 
				
		if($this->user!=false){			
			$data['column'] = 'withLeft';
			$data['current'] = $page;
			
			if($page=='myinfo'){
				$data['backlink'] = 'myinfo/';
				$uname = $this->user->username;
			}else{
				$data['backlink'] = 'staffinfo/'.$this->uri->segment(2).'/';
				$uname = $this->uri->segment(2);
			}
			
			$data['row'] = $this->dbmodel->getSingleInfo('staffs', 'staffs.*, CONCAT(staffs.fname," ",staffs.lname) AS name, title AS title2, position AS title, dept AS department, (SELECT CONCAT(fname," ",lname) AS sname FROM staffs e WHERE e.empID=staffs.supervisor AND staffs.supervisor!=0) AS supName, levelName', 'username="'.$uname.'"', 'LEFT JOIN newPositions ON posID=position LEFT JOIN orgLevel ON levelID=levelID_fk');
			
			if(count($data['row']) > 0){				
				if(!empty($_POST)){			
				//$this->textM->aaa($_POST);
					if($_POST['submitType']=='pdetails' || $_POST['submitType']=='jdetails' || $_POST['submitType']=='cdetails'){
						$orig = (array)$data['row'];
						
						$what2update = $this->staffM->compareResults($_POST, $orig);					
						if(count($what2update) >0){
							if($this->access->accessFullHR==false){
									$upNote = 'You requested an update for:<br/>';
									foreach($what2update AS $k=>$val):
										$r['empID_fk'] = $_POST['empID'];
										$r['fieldname'] = $k;											
										$r['fieldvalue'] = $val;											
										$r['daterequested'] = date('Y-m-d H:i:s');
										$r['isJob'] = 0;
										
										if(($_POST['submitType']=='jdetails' && !in_array($k, array('levelID_fk','terminationType'))) || ($_POST['submitType']=='cdetails' && $k=='sal')) 
											$r['isJob'] = 1;
											
										if($k=='endDate') $r['notes'] = 'Requested by: '.$this->user->name;
										
										if($k!='empStatus')
											$this->dbmodel->insertQuery('staffUpdated', $r);
										
										if($k=='title'){
											$o = $orig['title2'];
										}else if($k=='supervisor'){
											$o = $orig['supName'];
										}else if($k=='levelID_fk'){
											$o = $orig['levelName'];
										}else if($k=='terminationType' || ($k=='taxstatus' && $orig[$k]!='')){
											$o = $this->staffM->infoTextVal($k, $orig[$k]);
										}else{
											$o = $this->textM->convertDecryptedText($k, $orig[$k]);
											if($o=='') $o = 'none';
										}
										
										$upNote .= $this->textM->constantText('txt_'.$k).' from <i>'.$o.'</i> to <u>'.$this->staffM->infoTextVal($k, $val).'</u><br/>';			
									endforeach;
									$upNote .= 'This needs HR approval. Please upload documents on Personal File on My Info page to support the request.';
									$this->commonM->addMyNotif($_POST['empID'], $upNote);								
							}else{

								$empID = $_POST['empID'];
								$submitType = $_POST['submitType'];
								
								unset($_POST['empID']);
								unset($_POST['submitType']);
																
								if(isset($_POST['endDate']) && $_POST['endDate']=='Not yet set') $_POST['endDate'] = '0000-00-00';
								if(isset($_POST['title'])){ $_POST['position'] = $_POST['title']; unset($_POST['title']); }
												
								if(isset($_POST['bdate']) && $_POST['bdate']!='') $_POST['bdate'] = date('Y-m-d', strtotime($_POST['bdate']));
								if(isset($_POST['startDate']) && $_POST['startDate']!='') $_POST['startDate'] = date('Y-m-d', strtotime($_POST['startDate']));
								if(isset($_POST['endDate']) && $_POST['endDate']!=''){
									$_POST['endDate'] = date('Y-m-d', strtotime($_POST['endDate']));
									$this->schedM->endSchedule($empID, $_POST['endDate']); ///end schedule for payroll
								}
								if(isset($_POST['accessEndDate']) && $_POST['accessEndDate']!='') $_POST['accessEndDate'] = date('Y-m-d', strtotime($_POST['accessEndDate']));
								if(isset($_POST['floatStartDate']) && $_POST['floatStartDate']!='') $_POST['floatStartDate'] = date('Y-m-d', strtotime($_POST['floatStartDate']));
								if(isset($_POST['regDate']) && $_POST['regDate']!='') $_POST['regDate'] = date('Y-m-d', strtotime($_POST['regDate']));
								
								$encArr = $this->textM->constantArr('encText');
								foreach($encArr AS $en){
									if(isset($_POST[$en])) 
										$_POST[$en] = $this->textM->encryptText($_POST[$en]);
								}
						
								//UPDATE STAFFS TABLE
								$this->dbmodel->updateQuery('staffs', array('empID'=>$empID), $_POST);
								
								//UPDATE PTDB TABLE
								if(isset($what2update['title'])){
									$nposdata = $this->dbmodel->getSingleInfo('newPositions', 'title, dt', 'posID="'.$what2update['title'].'"');
									$this->dbmodel->ptdbQuery('UPDATE eData SET title="'.$nposdata->title.'", dt="'.$nposdata->dt.'" WHERE u="'.$data['row']->username.'"');
								}
										
								//send email notification if access or end date is set
								if(isset($what2update['endDate']) || isset($what2update['accessEndDate'])){
									$this->emailM->emailSeparationNotice($empID);
								}

								if( isset($what2update['regDate']) ){
									$this->emailM->emailRegularization($empID);
								}
								
								//cancel coaching if effective separation date is set on or before today. Note CANCELLED DUE TO TERMINATION
								if(isset($what2update['endDate']) && $what2update['endDate']<=date('Y-m-d')){
									$coaching = $this->dbmodel->getQueryResults('staffCoaching', 'coachID', 'empID_fk="'.$empID.'" AND status!=1 AND status!=4');
									if(count($coaching)>0){
										foreach($coaching AS $c){
											$this->dbmodel->updateQuery('staffCoaching', array('coachID'=>$c->coachID), array('status'=>4, 'canceldata'=>'CANCELLED DUE TO TERMINATION<br/><i>careerPH '.date('Y-m-d h:i a').'</i>'));
										}
									}						
								}
								
								//deactivate PT and careerPH access
								if(isset($what2update['active'])){
									if($this->config->item('devmode')==false){
										if($what2update['active']==1) $this->dbmodel->ptdbQuery('UPDATE staff SET active="Y" WHERE username = "'.$data['row']->username.'"');
										else $this->dbmodel->ptdbQuery('UPDATE staff SET active="N" WHERE username = "'.$data['row']->username.'"');
									}
									
									$this->emailM->sendDeActivateEmail($what2update['active'], $data['row']->name);									
								}
								
								if($submitType=='jdetails') $upNote = 'Job details';
								else if($submitType=='cdetails') $upNote = 'Compensation details';
								else $upNote = 'Personal details';
								$upNote .= ' has been updated to:<br/>';
																	
								foreach($what2update AS $k=>$val):
									if($k=='title'){
										$o = $orig['title2'];
									}else if($k=='supervisor'){
										$o = $orig['supName'];
									}else if($k=='levelID_fk'){
										$o = $orig['levelName'];
									}else if(in_array($k, $this->textM->constantArr('encText'))){
										$o = $this->textM->decryptText($orig[$k]);
									}else if($k=='staffHolidaySched'){
										$schedLoc = $this->textM->constantArr($k);
										$o = $schedLoc[$orig[$k]];
									}else if($k=='shiftSched'){
										$shf = $this->textM->constantArr($k);
										$o = $shf[$orig[$k]];
									}else{
										$o = $orig[$k];
										if($o=='') $o = 'none';
									}
									
									$upNote .= $this->textM->constantText('txt_'.$k).' from <i>'.$o.'</i> to <u>'.$this->staffM->infoTextVal($k, $val).'</u><br/>';
								endforeach;
								$this->commonM->addMyNotif($empID, $upNote, 0, 1);
							}
						}
						exit;
					}else if($_POST['submitType']=='addnote'){
						$this->commonM->addMyNotif($_POST['empID_fk'], $_POST['ntexts'], $_POST['ntype']);
					}else if($_POST['submitType']=='uploadPF'){	
						$err = '';
						
						$upFile = $_FILES['pfilei'];
						$cntUp = count($upFile['name']);
						
						if(empty($upFile['name'][0])){
							$err = 'No file uploaded.';
						}else{
							$moreThan100 = 0;
							for($n=0; $n<$cntUp; $n++){
								if(strlen($upFile['name'][$n])>100)
									$moreThan100 = 1;
							}
							if($moreThan100==1)
								$err = 'Filename is too long. Please upload filename less than 100 characters.';
						} 		
						
						if($err!=''){
							echo '<script>alert("'.$err.'"); window.location.href="'.$this->config->base_url().$data['backlink'].'";</script>';
						}else{		
							$fnotes = '';
							for($ff=0; $ff<$cntUp; $ff++){
								$filename = $upFile['name'][$ff];
								$dd = $this->dbmodel->getQueryArrayResults('staffUploads', 'fileName', 'empID_fk='.$data['row']->empID.' AND isDeleted=0');
								$ddArr = array();
								$cntCalifornia = count($dd);
								for($d=0; $d<$cntCalifornia; $d++){
									$ddArr[] = $dd[$d]->fileName;
								}
								
								if(in_array($filename, $ddArr)){
									$filename = date('YmdHis').'_'.$filename;
								}
							
								$dir = UPLOAD_DIR.$data['row']->username;
								if (!file_exists($dir)) {
									# $data['dir'] doesn't have value 
									mkdir($dir, 0755, true);
									chmod($dir.'/', 0777);
								}
								
								if(move_uploaded_file($upFile['tmp_name'][$ff], $dir.'/'.$filename)){
									$pIns['empID_fk'] = $data['row']->empID;
									$pIns['uploadedBy'] = $this->user->empID;
									$pIns['fileName'] = $filename;
									$pIns['dateUploaded'] = date('Y-m-d H:i:s');
									if(isset($_POST['typeVal'])) $pIns['fileType'] = $_POST['typeVal'];
									$this->dbmodel->insertQuery('staffUploads', $pIns);
									
									//for notification
									$ciframe = '';								
									$ext = strtolower(pathinfo($upFile['name'][$ff], PATHINFO_EXTENSION));
									if(in_array($ext, array('jpg', 'png', 'gif', 'pdf')))
										$ciframe = 'class="iframe"';
																	
									$fnotes .= '<li><a href="'.$this->config->base_url().$dir.'/'.$filename.'" '.$ciframe.'>'.$filename.'</a></li>';
								}else{
									echo '<script>alert("Unable to upload file. Please upload less than 2MB."); location.href="'.$_SERVER['REQUEST_URI'].'";</script>';
									exit;
								}															
							}
							
							//add notifications	
							if(!empty($fnotes)){
								if($data['row']->empID==$this->user->empID){
									$this->commonM->addMyNotif($this->user->empID, 'Uploaded file/s: <ul>'.$fnotes.'</ul>');
								}else{
									$ttxt = $this->user->name.' <ul>'.$fnotes.'</ul>';
									$this->commonM->addMyNotif($data['row']->empID, $ttxt, 0, 1);
								}
							}														
						}
					}else if($_POST['submitType']=='uploadPrevPay'){
						$err = '';
						
						$upFile = $_FILES['pfilei'];
						$cntUp = count($upFile['name']);
												
						if(empty($upFile['name'][0])){
							$err = 'No file uploaded.';
						}else{
							$moreThan100 = 0;
							for($n=0; $n<$cntUp; $n++){
								if(strlen($upFile['name'][$n])>100)
									$moreThan100 = 1;
							}
							if($moreThan100==1)
								$err = 'Filename is too long. Please upload filename less than 100 characters.';
						} 		
						
						if($err!=''){
							echo '<script>alert("'.$err.'"); window.location.href="'.$this->config->base_url().$data['backlink'].'";</script>';
						}else{		
							$fnotes = '';
							$dir = UPLOAD_DIR.$data['row']->username.'/payslips/';
							if (!file_exists($dir)) {
								mkdir($dir, 0755, true);
								chmod($dir, 0777);
							}
								
							for($ff=0; $ff<$cntUp; $ff++){
								$filename = $upFile['name'][$ff];
								
								if(!move_uploaded_file($upFile['tmp_name'][$ff], $dir.$filename)){
									echo '<script>alert("Unable to upload file. Please upload less than 2MB."); location.href="'.$_SERVER['REQUEST_URI'].'";</script>';
									exit;
								}															
							}														
						}						
					}else if($_POST['submitType']=='delFile'){
						$this->dbmodel->updateQuery('staffUploads', array('upID'=>$_POST['upID']), array('isDeleted' => 1, 'deletedBy'=>$this->user->empID, 'dateDeleted'=>date('Y-m-d H:i:s')));

						//add notifications
						if($data['row']->empID==$this->user->empID){
							$this->commonM->addMyNotif($this->user->empID, 'You deleted the file '.$_POST['fileName'], 5);
						}else{
							$ttxt = $this->user->name.' deleted the file '.$_POST['fileName'];
							$this->commonM->addMyNotif($data['row']->empID, $ttxt, 0, 1);
						}
						
						exit;
					}else if($_POST['submitType']=='cancelRequest'){					
						$this->commonM->addMyNotif($data['row']->empID, 'Cancelled update field request:<br/>'.$this->textM->constantText('txt_'.$_POST['fld']).' - '.$this->staffM->infoTextVal($_POST['fld'], $_POST['fname']), 5);
						$this->dbmodel->updateQuery('staffUpdated', array('updateID'=>$_POST['updateID']), array('status'=>5));
						exit;
					}else if($_POST['submitType']=='uLeaveC'){
						$ins['empID_fk'] = $this->user->empID;
						$ins['notes'] = $_POST['note'];
						$ins['daterequested'] = date('Y-m-d H:i:s');
						$ins['isJob'] = 2;
						$this->dbmodel->insertQuery('staffUpdated', $ins);
						$this->commonM->addMyNotif($this->user->empID, 'You requested to recheck your leave credits.<br/>Your note:'.$_POST['note'], 5);
						exit;
					}else if($_POST['submitType']=='editleavecredits'){
						$this->dbmodel->updateQuery('staffs', array('empID'=>$_POST['empID']), array('leaveCredits'=>$_POST['newleavecredits']));
						$this->commonM->addMyNotif($_POST['empID'], $this->user->name.' updated your leave credits from '.$_POST['oldleavecredit'].' to '.$_POST['newleavecredits'].'.', 0, 1);
						
						if($this->user->empID!=$_POST['empID']){
							$this->commonM->addMyNotif($this->user->empID, 'You updated leave credits of '.$_POST['empName'].' from '.$_POST['oldleavecredit'].' to '.$_POST['newleavecredits'].'.', 5, 0);
						}
						exit;
					}else if($_POST['submitType']=='uploadPTpicture'){
						require_once('includes/S3.php');
						$file = $_FILES ['PTpicture']['tmp_name'];
						$bucket = 'staffthumbnails';  
						$s3 = new S3(AWSACCESSKEY, AWSSECRETKEY);
						
						$this->commonM->photoResizer($file, $file, $width = 150, $height = 150, $quality = 70, false);
														   
						$input = S3::inputFile($file);
						$new_name = $data['row']->username.'.jpg';
						
						if(S3::getObjectInfo($bucket, $new_name))
							S3::deleteObject($bucket, $new_name);
						S3::putObject($input, $bucket, $new_name, S3::ACL_PUBLIC_READ);	
								
						header("Cache-Control: no-cache, must-revalidate"); 
						header('Location:'.$_SERVER['REQUEST_URI']);
						exit;
					}else if($_POST['submitType']=='editUploadName'){
						$this->dbmodel->updateQuery('staffUploads', array('upID'=>$_POST['upID']), array('docName'=>$_POST['docName']));
						exit;
					}
				} //end of POST not empty
			
				$data['leaveTypeArr'] = $this->textM->constantArr('leaveType');
				$data['leaveStatusArr'] = $this->textM->constantArr('leaveStatus');
				$data['timeoff'] = $this->dbmodel->getQueryResults('staffLeaves', '*', 'empID_fk="'.$data['row']->empID.'" AND status!=5','', 'date_requested DESC');
				$data['disciplinary'] = $this->dbmodel->getQueryResults('staffNTE', 'staffNTE.*, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE issuer=empID AND issuer!=0) AS issuerName', 'empID_fk="'.$data['row']->empID.'" AND status!=2','', 'timestamp DESC');
				$data['perfTrackRecords'] = $this->dbmodel->getQueryResults('staffCoaching', 'coachID, coachedBy, empID_fk, coachedDate, coachedEval, status, selfRating, supervisorsRating, finalRating, dateSupAcknowledged, date2ndMacknowledged, dateEmpAcknowledge, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=coachedBy LIMIT 1) AS coachedByName, dateGenerated, HRoptionStatus, fname AS name, supervisor', 'status!=4 AND empID_fk="'.$data['row']->empID.'"','LEFT JOIN staffs ON empID=empID_fk', 'dateGenerated DESC');
								
				$data['pfUploaded'] = $this->dbmodel->getQueryResults('staffUploads', 'upID, docName, fileName, dateUploaded', 'empID_fk="'.$data['row']->empID.'" AND isDeleted=0 AND (fileType!="disciplinary" OR fileType!="performance")','', 'dateUploaded DESC');
				$data['disciplinaryUploaded'] = $this->dbmodel->getQueryResults('staffUploads', 'upID, docName, fileName, dateUploaded', 'empID_fk="'.$data['row']->empID.'" AND isDeleted=0 AND fileType="disciplinary"','', 'dateUploaded DESC');
				$data['performanceUploaded'] = $this->dbmodel->getQueryResults('staffUploads', 'upID, docName, fileName, dateUploaded', 'empID_fk="'.$data['row']->empID.'" AND isDeleted=0 AND fileType="performance"','', 'dateUploaded DESC');
				$data['nteUploadedFiles'] = $this->dbmodel->getQueryResults('staffNTE', 'nteuploaded, caruploaded', 'empID_fk="'.$data['row']->empID.'"');								
				$data['coachingUploadedFiles'] = $this->dbmodel->getQueryResults('staffCoaching', 'coachID, HRstatusData, HRoptionStatus', 'empID_fk="'.$data['row']->empID.'" AND HRoptionStatus>=2');
				$data['coachedNames'] = $this->dbmodel->getQueryResults('staffs', 'empID, CONCAT(fname," ",lname) as name', 'coach="'.$data['row']->empID.'" AND active=1');
								
				$data['isUnderMe'] = $this->commonM->checkStaffUnderMe($data['row']->username);
				
				$data['dataPayslips'] = array();
				$data['payslipDIR'] = UPLOAD_DIR.$data['row']->username.'/payslips/';
				if (is_dir($data['payslipDIR'])) {
					$fileArr = array();
					if ($dh = opendir($data['payslipDIR'])) {
						while(($filename = readdir($dh)) !== false){
							if($filename!='.' && $filename!='..')
								$fileArr[] = $filename;
						}
						closedir($dh);
					}
										
					foreach($fileArr AS $f){						
						$katski = array_reverse(explode('_', $f));
						$katski2 = explode('.', $katski[0]);
						$katski3 = explode('-', $katski2[0]);
						$data['dataPayslips'][$katski3[2].'-'.$katski3[0].'-'.$katski3[1]] = $f;
					}
					
					if(count($data['dataPayslips'])>0){
						foreach ($data['dataPayslips'] as $key => $part) {
							$sort[$key] = strtotime($key);
						}
						array_multisort($sort, SORT_DESC, $data['dataPayslips']);
					}				
				}
										
				if($page=='myinfo'){
					$data['updatedVal'] = $this->dbmodel->getQueryResults('staffUpdated', '*', 'empID_fk="'.$data['row']->empID.'" AND status=0', 'timestamp');
				}
			}				
			
		}
		$this->load->view('includes/template', $data);	
	}

	public function staffupdated(){
		$data['content'] = 'staffupdated';
		$data['edit'] = $this->uri->segment(2);
		$data['updateID'] = $this->uri->segment(3);
		$data['success'] = false;
							
		if($this->access->accessFullHR==false){
			$data['access'] = false;
		}else if($this->user!=false){		
			if(!empty($_POST)){				
				if($_POST['submitType']=='Update'){
					if($_POST['fieldN']=='title'){
						//update position and org level
						$orgLevel = $this->dbmodel->getSingleField('newPositions', 'orgLevel_fk', 'posID="'.$_POST['fieldV'].'"');
						$this->dbmodel->updateQuery('staffs', array('empID'=>$_POST['empID']), array('position' => $_POST['fieldV'], 'levelID_fk'=>$orgLevel));
						
						//UPDATE PTDB TABLE
						$username = $this->dbmodel->getSingleField('staffs', 'username', 'empID="'.$_POST['empID'].'"');
						$nposdata = $this->dbmodel->getSingleInfo('newPositions', 'title, dt', 'posID="'.$_POST['fieldV'].'"');
						$this->dbmodel->ptdbQuery('UPDATE eData SET title="'.$nposdata->title.'", dt="'.$nposdata->dt.'" WHERE u="'.$username.'"');
						
					}else $this->dbmodel->updateQuery('staffs', array('empID'=>$_POST['empID']), array($_POST['fieldN'] => $_POST['fieldV']));
					
					$addNote = '['.date('Y-m-d H:i').'] '.$this->user->username.': <i>request approved and changed</i><br/>';
					$this->dbmodel->updateConcat('staffUpdated', 'updateID="'.$_POST['updateID'].'"', 'notes', $addNote);
					$this->dbmodel->updateQuery('staffUpdated', array('updateID'=>$_POST['updateID']), array('status'=>1));
															
					$ntext = 'Approved update request.<br/>Information details has been updated to:<br/>';
					$ntext .= $this->textM->constantText('txt_'.$_POST['fieldN']).' - ';
					$ntext .= $this->staffM->infoTextVal($_POST['fieldN'], $_POST['fieldV']);
											
					$this->commonM->addMyNotif($_POST['empID'], $ntext, 0, 1);
										
					//send separation notice email if access end date or end date is set					
					if($_POST['fieldN']=='endDate' || $_POST['fieldN']=='accessEndDate'){
						$this->emailM->emailSeparationNotice($_POST['empID']);							
					}							

					//cancel coaching if effective separation date is set on or before today. Add note CANCELLED DUE TO TERMINATION
					if($_POST['fieldN']=='endDate' && $_POST['fieldV']<=date('Y-m-d')){
						$coaching = $this->dbmodel->getQueryResults('staffCoaching', 'coachID', 'empID_fk="'.$_POST['empID'].'" AND status!=1 AND status!=4');
						if(count($coaching)>0){
							foreach($coaching AS $c){
								$this->dbmodel->updateQuery('staffCoaching', array('coachID'=>$c->coachID), array('status'=>4, 'canceldata'=>'CANCELLED DUE TO TERMINATION<br/><i>careerPH '.date('Y-m-d h:i a').'</i>'));
							}
						}						
					}
					
					//this is for timecard and scheduling
					if($_POST['fieldN']=='endDate' && !empty($_POST['fieldV']) && $_POST['fieldV']!="0000-00-00")
						$this->schedM->endSchedule($_POST['empID'], $_POST['endDate']); ///end schedule for payroll
					
					exit;
				}else if($_POST['submitType']=='addnote'){								
					$this->dbmodel->updateQuery('staffUpdated', array('updateID'=>$_POST['updateID']), array('notes'=>'['.date('Y-m-d H:i:s').'] '.$this->user->username.': <i>'.$_POST['notes'].'</i>'));
					$data['success'] = true;
				}else if($_POST['submitType']=='disapprove'){
					$this->dbmodel->updateQuery('staffUpdated', array('updateID'=>$_POST['updateID']), array('status'=>2,'notes'=>'['.date('Y-m-d H:i:s').'] '.$this->user->username.': request disapproved <br/><i>'.$_POST['notes'].'</i>'));
					$data['success'] = true;
					
					$ntext = $this->user->name.' disapproved your personal details update request: '.$this->textM->constantText('txt_'.$_POST['fieldN']).' - '.$this->textM->convertDecryptedText($_POST['fieldN'], $_POST['fieldV']).'<br/>Reason: '.$_POST['notes'];
					$this->commonM->addMyNotif($_POST['empID_fk'], $ntext, 0, 1);
				}else if($_POST['submitType']=='sendEmail' || $_POST['submitType']=='sendEmailClose'){
					$this->emailM->sendEmail( 'hr.cebu@tatepublishing.net', $_POST['email'], $_POST['subject'], nl2br($_POST['message']), 'CAREERPH');
					
					$forevermore = $this->user->name.' sent you an email';
					if($_POST['submitType']=='sendEmailClose'){
						$this->dbmodel->updateQuery('staffUpdated', array('updateID'=>$_POST['updateID']), array('status'=>4,'notes'=>$note.'Closed'));
						$forevermore .= ' and closed your recheck leave request';
					}					
					$this->commonM->addMyNotif($_POST['empID'], $forevermore.'.<br/>Message: <br/>'.$_POST['message'], 0, 1);
					exit;
				}else if($_POST['submitType']=='requestclose'){
					$this->commonM->addMyNotif($_POST['empID'], $this->user->name.' closed your recheck leave request.<br/>Note: '.$_POST['note'], 0, 1);
					$this->dbmodel->updateQuery('staffUpdated', array('updateID'=>$_POST['updateID']), array('status'=>4,'notes'=>$note.'Closed<br/>Note:'.$_POST['note']));
					exit;
				}
			}		
			if($data['edit']==''){
				$data['row'] = $this->dbmodel->getQueryResults('staffUpdated', 'staffUpdated.*, staffs.*, (SELECT CONCAT(fname," ",lname) AS name FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS supervisor, (SELECT title FROM newPositions WHERE newPositions.posID=staffs.position AND position!=0 LIMIT 1) AS title, levelName', 'status=0 AND isJob<2', 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN orgLevel ON levelID=levelID_fk');
				$data['rowLeave'] = $this->dbmodel->getQueryResults('staffUpdated', 'staffUpdated.*, staffs.*', 'status=0 AND isJob=2', 'LEFT JOIN staffs ON empID=empID_fk');
			}else if($data['edit']=='disapprove'){
				$data['row'] = $this->dbmodel->getSingleInfo('staffUpdated', '*', 'updateID='.$data['updateID']);
			}else if($data['edit']=='sendemailleave'){
				$data['email'] = $this->dbmodel->getSingleField('staffs', 'email', 'empID="'.$this->uri->segment(4).'"');
			}
		}
		
		if($data['edit']=='') $this->load->view('includes/template', $data);
		else $this->load->view('includes/templatecolorbox', $data);
	}
	
	public function issueNTE(){		
		$data['content'] = 'issueNTE';
		if($this->user!=false){
			$data['ntegenerated']=false;
			$empID = $this->uri->segment(2);
			
			$data['row'] = $this->dbmodel->getSingleInfo('staffs', 'empID, username, fname, CONCAT(fname," ",lname) AS name, email, pemail, supervisor, (SELECT CONCAT(fname," ",lname) AS sname FROM staffs e WHERE e.empID=staffs.supervisor AND supervisor!=0 ) AS sName', 'empID="'.$empID.'"');
			
			//check if you are allowed to issue nte
			if($this->user->access=='' && $this->commonM->checkStaffUnderMe($data['row']->username)==false){
				$data['access'] = false;
			}
			
			if(isset($_POST['submitType']) && $_POST['submitType']=='issueNTE'){				
				$offensedates = '';
				if($_POST['type']=='AWOL'){	
					$ins['offenselevel'] = $_POST['offenselevelawol'];
					foreach($_POST['offensedates'] AS $d):
						if(!empty($d))
							$offensedates .= date('Y-m-d',strtotime($d)).'|';
					endforeach;		
				}else if($_POST['type']=='tardiness'){
					$ins['offenselevel'] = $_POST['offenseleveltardy'];
					for($i=0; $i<6; $i++){
						if($_POST['tdates'][$i]!=''){
							$offensedates .= date('Y-m-d H:i', strtotime($_POST['tdates'][$i].' '.$_POST['ttime'][$i])).'|';
						}
					}
				}
				
				$ins['offensedates'] = rtrim($offensedates,'|');
				$ins['type'] = $_POST['type'];
				$ins['empID_fk'] = $_POST['empID_fk'];
				$ins['dateissued'] = date('Y-m-d H:i:s');
				$ins['issuer'] = $this->user->empID;				
								
				$insid = $this->dbmodel->insertQuery('staffNTE', $ins);
				$data['ntegenerated']=true;
				$data['insid'] = $insid;
				$this->commonM->addMyNotif($_POST['empID_fk'], $this->user->name.' issued an NTE to you.  Please check your disciplinary records or click <a href="'.$this->config->base_url().'ntepdf/'.$insid.'/" class="iframe">here</a> to view the NTE file.', 4, 1);
				$this->commonM->addMyNotif($this->user->empID, 'You issued an NTE to '.$data['row']->name.'. Click <a href="'.$this->config->base_url().'detailsNTE/'.$insid.'/" class="iframe">here</a> to view NTE details page or <a href="'.$this->config->base_url().'ntepdf/'.$insid.'/" class="iframe">here</a> to view PDF the file. ', 5, 0);
				
				$dateplus5 = date('l F d, Y', strtotime('+7 day'));
				$nextMonday = date('l F d, Y', strtotime('next monday', strtotime($dateplus5)));
				
				$to = $data['row']->email.','.$data['row']->pemail;
				$subject = 'A Notice to Explain For '.$data['row']->name;
				$body = '<p>Hello '.trim($data['row']->fname).',</p>
					<p>This is an automatic notification sent to inform you a Notice to Explain is generated for you by '.$this->user->name.'. See details below:
						<ul>
							<li>Date of Issuance: '.date('F d, Y').'</li>
							<li>Offense Number: '.$ins['offenselevel'].'</li>
							<li>'.ucfirst($_POST['type']).' Dates:
								<ul>';
						$inQ = explode('|', $ins['offensedates']);
						foreach($inQ AS $in):
							if($_POST['type']=='tardiness')
								$body .= '<li>'.date('F d, Y H:i', strtotime($in)).'</li>';
							else
								$body .= '<li>'.date('F d, Y', strtotime($in)).'</li>';
						endforeach;
						$body .= '</ul>
							</li>
						</ul>
					</p>	
					<p><i>Click <a href="'.$this->config->base_url().'ntepdf/'.$insid.'/"><b>here</b></a> to view the file.</i></p>';
				if($ins['offenselevel']<3){
					if($_POST['type']=='tardiness') $data['sanctionArr'] = $this->textM->constantArr('sanctiontardiness');
					else $data['sanctionArr'] = $this->textM->constantArr('sanctionawol');				
					
					$body .= '<p>Note that the Code of Conduct prescribes a sanction of '.$data['sanctionArr'][$ins['offenselevel']].' to a '.$this->textM->ordinal($ins['offenselevel']).' offense of '.$_POST['type'].'.</p>					
						<p>You are hereby requested to send an explanation. Click <a href="'.$this->config->base_url().'detailsNTE/'.$insid.'/"><b>here</b></a> to submit your explanation. Please explain why this happened and why no sanction should be imposed upon you. You are given until '.$dateplus5.' (5 days) to write your explanation. Failure to do so will be considered an admission of fault and your waiver of your right to be heard.</p>
						<p style="color:red;">This shall be the first and only reminder for you to provide your explanation to the said NTE.</p>
						<p>A <b>Corrective Action Report</b> and <b>Notice of Disciplinary Action</b> will naturally follow the Notice of Decision whether or not an explanation is received from you. The management may give a lighter sanction depending on the validity of the explanation that you provided, or in consideration of the frequency in which the offense is repeated and of your overall conduct and performance at work.</p>';
				}else{
					$body .= '<p>Note that 3 or more offenses of '.$_POST['type'].' is already TERMINABLE as prescribed by our Code of Conduct.</p>
						<p>You are hereby requested to send an explanation. Click <a href="'.$this->config->base_url().'detailsNTE/'.$insid.'/"><b>here</b></a> to submit your explanation. Please explain why this happened and why no sanction should be imposed upon you. You are given until '.$dateplus5.' to write your explanation.</p>
						<p>On '.$nextMonday.' you are invited for an administrative hearing at the Admin Office where you can further justify your explanation. Failure to send an explanation or attend the administrative hearing shall be considered an admission of fault and your waiver of your right to be heard.</p>
						<p style="color:red;">This shall be the first and only reminder for you to provide your explanation to the said NTE.</p>
						<p>A <b>Corrective Action Report</b> and <b>Notice of Disciplinary Action</b> will naturally follow the Notice of Decision whether or not an explanation is received from you.</p>';
				}
				$body .= '<p>If you have questions or concerns about this NTE, plase discuss with '.$this->user->name.'.</p>
					<p>The hard copy will be routed to you for your signature.</p>
					<p><br/></p>
					<p>Yours truly,</p>
					<p><b>The Human Resources Department</b></p>
				';
				
				$this->emailM->sendEmail( 'hr.cebu@tatepublishing.net', $to, $subject, $body, 'The Human Resources Department');
				
			}
			
			$data['prevNTE'] = $this->dbmodel->getQueryResults('staffNTE', 'nteID, empID_fk, type, offenselevel, offensedates, dateissued, issuer, status, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=issuer) AS issuedBy, sanction, suspensiondates', 'empID_fk="'.$empID.'" AND (dateissued >= "'.date('Y-m-', strtotime('-6 months')).'" OR (type="AWOL" AND dateissued>="'.date('Y-m-', strtotime('-1 year')).'"))', '', 'dateissued ASC');
			$data['nteStat'] = $this->textM->constantArr('nteStat');
			
		}
		$this->load->view('includes/templatecolorbox', $data);
	}
			
	public function ntepdf(){
		if($this->uri->segment(2)!=''){
            $nteID = $this->uri->segment(2);
            /*
             * remove staff.title - unknown column in db
             * */
			$row = $this->dbmodel->getSingleInfo('staffNTE', 'staffNTE.*, CONCAT(fname," ",lname) AS name, username, idNum, supervisor, dept, grp, title, startDate', 'nteID="'.$nteID.'"', 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
			
			if(count($row)==0){
				echo 'No NTE record.';
				exit;
			}
			
			if($row->issuer!=0){
				$sName = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, username', 'empID="'.$row->issuer.'"');			
			}
			
			$nntype = '';
			$nntext = '';
			$nnexp = '';
			$dateFormat = 'l, F d, Y';
			if($row->type=='AWOL'){
				$nntype = 'Absence Without Official Leave (AWOL)';
				$nntext = 'You have been recorded as AWOL on the dates listed below. AWOL is being recorded as absent without a documented, fully signed and processed leave form on file:';
				$nnexp = 'Offense Against Attendance And Schedule Adherence - Unauthorized or unexcused absence (i.e. no valid reason and permission of the superior) - Level 2';				
			}else if($row->type=='tardiness'){
				$dateFormat = 'F d, Y H:i';
				$nntype = 'Tardiness or Undertime';
				$nntext = 'You have been recorded as tardy on the dates listed below:';
				$nnexp = 'Offense Against Attendance And Schedule Adherence - Tardiness and Undertime (i.e. Employees who will be late in reporting to work or late in returning to work from their scheduled break) - Level 1';
			}
						
			require_once('includes/fpdf/fpdf.php');
			require_once('includes/fpdf/fpdi.php');
			
			$pdf = new FPDI();
			$pdf->AddPage();
			$pdf->setSourceFile(PDFTEMPLATES_DIR.'NTE_V2.pdf');
			
			if($row->status==1 || $this->uri->segment(4)=='nform'){ //if NTE form		
				$tplIdx = $pdf->importPage(1);
				$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
				
				$pdf->SetFont('Helvetica','B',9);
				$pdf->setTextColor(0, 0, 0);
				
				$pdf->setXY(47, 38.7);
				$pdf->Write(0, date('l, F d, Y', strtotime($row->dateissued)));	
				
				$pdf->setXY(47, 42.8);
				$pdf->Write(0, $row->name);	

				$pdf->setXY(47, 47);
				$pdf->Write(0, $row->title);
				
				if(isset($sName->name)){
					$pdf->setXY(47, 55);
					$pdf->Write(0, $sName->name);	
				}
				
				$pdf->setXY(47, 51);
				$pdf->Write(0, 'The Human Resource Department');	
				
				$pdf->setTextColor(255, 0, 0);
				$pdf->setXY(29, 92);		
				$pdf->Write(0, $this->textM->ordinal($row->offenselevel).' Offense');			
				$pdf->setXY(29, 152);
				$pdf->Write(0, $this->textM->ordinal($row->offenselevel).' Offense');	
				
				$pdf->setTextColor(0, 0, 0);
				$pdf->SetFont('Helvetica','',9);
								
				$pdf->setXY(30, 97);
				$pdf->Write(0, $nntype);				
				$pdf->setXY(30, 101.5);
				$pdf->MultiCell(150, 4, $nntext ,0,'L',false);				
				$pdf->setXY(30, 155);
				$pdf->MultiCell(150, 4, $nnexp ,0,'L',false);	
				
				
				$dd = explode('|',$row->offensedates);
				
				$pdf->SetFont('Helvetica','',9);
				if(isset($dd[0])){
					$pdf->setXY(40, 118);
					$pdf->Write(0, date($dateFormat, strtotime($dd[0])));
				}
				if(isset($dd[1])){
					$pdf->setXY(40, 123);
					$pdf->Write(0, date($dateFormat, strtotime($dd[1])));
				}
				if(isset($dd[2])){
					$pdf->setXY(40, 128);
					$pdf->Write(0, date($dateFormat, strtotime($dd[2])));
				}
				if(isset($dd[3])){
					$pdf->setXY(110, 118);
					$pdf->Write(0, date($dateFormat, strtotime($dd[3])));
				}
				if(isset($dd[4])){
					$pdf->setXY(110, 123);
					$pdf->Write(0, date($dateFormat, strtotime($dd[4])));
				}
				if(isset($dd[5])){
					$pdf->setXY(110, 128);
					$pdf->Write(0, date($dateFormat, strtotime($dd[5])));
				}
				
				//issued by
				$pdf->SetFont('Helvetica','B',9);
				$pdf->setXY(30, 215);
				$pdf->MultiCell(63, 4, strtoupper($sName->name),0,'C',false);			
				$pdf->SetFont('Helvetica','',9);
				$pdf->setXY(110, 215);
				$pdf->MultiCell(63, 4, date('F d, Y', strtotime($row->dateissued)),0,'C',false);		
			
				
				//received by
				$pdf->SetFont('Helvetica','B',9);
				$pdf->setXY(30, 236.5);
				$pdf->MultiCell(63, 4, strtoupper($row->name),0,'C',false);	
				$pdf->SetFont('Helvetica','',9);
				$pdf->setXY(110, 236.5);
				$pdf->MultiCell(63, 4, date('F d, Y', strtotime($row->dateissued)),0,'C',false);	
			}
			
			//if CAR form
			if($this->uri->segment(4)!='nform' && ($row->status==0 || $row->status==3)){
				$firstlevelmngr = $this->dbmodel->getSingleInfo('staffs', 'username, CONCAT(fname," ",lname) AS eName, title, supervisor', 'empID="'.$row->supervisor.'"', 'LEFT JOIN newPositions ON posID=position');
				if($row->type=='tardiness') $sanctionArr = $this->textM->constantArr('sanctiontardiness');
				else $sanctionArr = $this->textM->constantArr('sanctionawol');

				$secondlevelmngr = '';
				if(isset($firstlevelmngr->supervisor))
					$secondlevelmngr = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS eName, title', 'empID="'.$firstlevelmngr->supervisor.'"', 'LEFT JOIN newPositions ON posID=position');
									
				$thru = $this->dbmodel->getSingleInfo('staffs', 'username, CONCAT(fname," ",lname) AS name', 'empID="'.$row->carissuer.'"');
				
				if(!empty($row->sanction)) $sanction = $row->sanction;
				else if($row->offenselevel>3) $sanction = 'Termination';
				else $sanction = $sanctionArr[$row->offenselevel];

				$nlevel = $row->offenselevel + 1;
				if($nlevel>3) $nextsanction = 'Termination';
				else $nextsanction = $sanctionArr[$nlevel];
			
				
				$tplIdx = $pdf->importPage(2);
				$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
				
				$pdf->SetFont('Arial','B',16);
				$pdf->setXY(138, 24);
				$pdf->Write(0, date('Y'));
				
				$pdf->SetFont('Arial','B',10);
				$pdf->setTextColor(0, 0, 0);
				
				$pdf->setXY(48, 33);
				$pdf->Write(0, $row->name);	
				$pdf->setXY(41, 37);
				$pdf->Write(0, $row->dept);	
				$pdf->setXY(32, 41.5);
				$pdf->Write(0, $row->grp);		
				$pdf->setXY(42, 45);
				$pdf->Write(0, $row->idNum);	
				
				$pdf->setXY(128, 33);
				$pdf->Write(0, date('l, F d, Y', strtotime($row->startDate)));	
				$pdf->setXY(132, 35);
				$pdf->MultiCell(150, 4, $row->title ,0,'L',false);
				if(isset($firstlevelmngr->eName) && isset($firstlevelmngr->title)){
					$pdf->setXY(139, 41.5);
					$pdf->Write(0, $firstlevelmngr->eName);	
					$pdf->setXY(147, 45.2);
					$pdf->Write(0, $firstlevelmngr->title);	
				}
				
				$pdf->setXY(25, 63);
				if($row->status==3) $pdf->Write(0, 'None. Response satisfactory.');
				else $pdf->Write(0, ucwords($sanction));
				
				
				
				$pdf->setTextColor(255, 0, 0);
				$pdf->setXY(20, 78);		
				$pdf->Write(0, '('.$this->textM->ordinal($row->offenselevel).' Offense)');
				$pdf->setXY(20, 97);
				$pdf->Write(0, '('.$this->textM->ordinal($row->offenselevel).' Offense)');
				
				$pdf->setTextColor(0, 0, 0);
				$pdf->SetFont('Arial','',9);
				
				$pdf->setXY(20, 80);
				$pdf->MultiCell(170, 4, $nnexp ,0,'L',false);	
				$pdf->setXY(20, 103);
				$pdf->Write(0, $nntype);				
				$pdf->setXY(20, 105);
				$pdf->MultiCell(150, 4, $nntext ,0,'L',false);		
				
				$dawol = explode('|', $row->offensedates);
				
				if(isset($dawol[0]) && !empty($dawol[0])){
					$pdf->setXY(30, 117);
					$pdf->Write(0, date($dateFormat,strtotime($dawol[0])));	
				}
				if(isset($dawol[1]) && !empty($dawol[1])){
					$pdf->setXY(30, 121);
					$pdf->Write(0, date($dateFormat,strtotime($dawol[1])));	
				}
				if(isset($dawol[2]) && !empty($dawol[2])){
					$pdf->setXY(30, 125);
					$pdf->Write(0, date($dateFormat,strtotime($dawol[2])));	
				}
				if(isset($dawol[3]) && !empty($dawol[3])){
					$pdf->setXY(100, 117);
					$pdf->Write(0, date($dateFormat,strtotime($dawol[3])));	
				}
				if(isset($dawol[4]) && !empty($dawol[4])){
					$pdf->setXY(100, 121);
					$pdf->Write(0, date($dateFormat,strtotime($dawol[4])));	
				}
				if(isset($dawol[5]) && !empty($dawol[5])){
					$pdf->setXY(100, 125);
					$pdf->Write(0, date($dateFormat,strtotime($dawol[5])));	
				}
				
				if($row->status!=3){
					$pdf->setXY(20, 140);
					$pdf->MultiCell(175, 4, $row->planImp ,0,'L',false);
				}
				
				if($row->status==3){
					$pdf->setXY(20, 157);
					$pdf->MultiCell(175, 4, 'NOTE: '.$row->planImp ,0,'L',false);
				}else{
					$consequences = 'Any subsequent case of '.$nntype.' within the next six months will be '.$this->textM->ordinal($nlevel).' case of excessive '.$nntype.' and merits '.strtoupper($nextsanction);
					$pdf->setXY(20, 157);
					$pdf->MultiCell(175, 4, $consequences ,0,'L',false);
				}

				$pdf->SetFont('Arial','B',10);
				
				if(isset($firstlevelmngr->eName)){
					$pdf->setXY(20, 194.5);	
					$pdf->MultiCell(77, 4,strtoupper($firstlevelmngr->eName),0,'C',false);
				}			
				$pdf->setXY(135, 194.5);
				$pdf->MultiCell(50, 4,date('F d, Y',strtotime($row->cardate)),0,'C',false);
				
				if(isset($secondlevelmngr->eName)){
					$pdf->setXY(20, 221);
					$pdf->MultiCell(77, 4,strtoupper($secondlevelmngr->eName),0,'C',false);				
				}
				$pdf->setXY(135, 221);
				$pdf->MultiCell(50, 4,date('F d, Y',strtotime($row->cardate)),0,'C',false);		
								
				
				$pdf->AddPage();
				$tplIdx = $pdf->importPage(3);
				$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
				$pdf->SetFont('Arial','B',11);
				$pdf->setTextColor(0, 0, 0);
				
				$pdf->setXY(32, 43);
				$pdf->Write(0, date('F d, Y', strtotime($row->cardate)));	
				$pdf->setXY(28, 49);
				$pdf->Write(0, $row->name);	
				if(isset($firstlevelmngr->eName)){
					$pdf->setXY(33, 55.5);
					$pdf->Write(0, $firstlevelmngr->eName);	
				}
				$pdf->setXY(32, 62);
				$pdf->Write(0, $thru->name);		
						
				$dateplus5 = date('l Y-m-d', strtotime('+5 day', strtotime($row->dateissued)));
				$nextMonday = date('l F d, Y', strtotime('next monday', strtotime($dateplus5)));
				$notice = '';
				
				if($sanction == 'Termination'){
					$notice .= "On ".date('l F d, Y', strtotime($row->dateissued)).", you were given an NTE for ".strtoupper($row->type).". You were given 5 (five) days to respond and you were invited for an administrative hearing on ".$nextMonday.". ";
				}				
				
				if(!empty($row->response) && $row->satisfactory==1){
					$notice .= "The response you provided on ".date('l F d, Y', strtotime($row->responsedate))." was found to be considerable and satisfactory.";
				}else if(!empty($row->response) && $row->satisfactory==0){
					$notice .= "The response you provided on ".date('l F d, Y', strtotime($row->responsedate))." was found to be unsatisfactory.";
				}else{
					$notice .= "As of ".date('l F d, Y', strtotime($row->cardate)).", no response is received from you.";
				}
				
				if($row->status!=3)
					$notice .= "\n\nThe gravity of misconduct committed by you is such that it warrants ".$sanctionArr[$row->offenselevel];
				
				if(strpos($row->sanction,'Suspension') !== false){
					$sdatesQ = explode('|', $row->suspensiondates);
					$sdate = '';
					$scount = 0;
					foreach($sdatesQ AS $s):
						$sdate .= date('F d, Y', strtotime($s)).', ';
						$scount++;
					endforeach;
					
					if($scount==1)
						$dsanc = "1 Day Suspension";
					else
						$dsanc = $scount." Days Suspension";
				}else{
					$dsanc = " ".$sanction;
				}	
				
				if($row->proceedsanc==1){
					if (strpos($row->sanction,'Suspension') !== false) {
						$notice .= " and the sanction that has been decided is ".$dsanc.". The suspension date is on: ".rtrim($sdate, ', ').". Please be aware that you do not have a work schedule on this date and you are not expected and are prohibited to be in the office during a suspension date.";
					}else if($row->sanction == 'Termination'){
						$notice .= " and we regret to inform you that your continued unauthorized absence despite repeated reminder and reprimand has amounted to a gross, habitual, and deliberate neglect of duty and of the company’s code of conduct.\n\nYou were invited for an administrative hearing on ".$nextMonday." and below is what transpired during the administrative hearing:\n\n\"".$row->reasonsanction."\"\n\nFor this reason the company has decided to TERMINATE your services effective on ".date('l F d, Y', strtotime($row->suspensiondates)).". Your last day of employment is on ".date('l F d, Y', strtotime('-1 day',strtotime($row->suspensiondates))).".";
					}
				}else{
					$notice .= " however the management has decided to take on a more lenient view on this matter and instead give you a sanction of ".$dsanc." after consideration of the below reason:\n\n";
					$notice .= '"'.$row->reasonsanction.'"';
					
					if (strpos($row->sanction,'Suspension') !== false){
						if($row->sanction == '1 Day Suspension' ){
							$notice .= "\n\nThe suspension date is on: ".date('F d, Y', strtotime($row->suspensiondates)).". Please be aware that you do not have a work schedule on this date and you are not expected and are prohibited to be in the office during a suspension date.";
						}else{
							$notice .= "\n\nThe suspension dates are as follows: ".rtrim($sdate, ', ').". Please be aware that you do not have a work schedule on these dates and you are not expected and are prohibited to be in the office during these suspension dates.";			
						}
					}
				}
				
				if($row->sanction == 'Termination'){
					$notice .= "\n\n\nThe decision to terminate your employment was not made lightly and was purely based on the account that your repetitive unauthorized absence despite repeated reminder and reprimand has amounted to a gross, habitual, and deliberate neglect of duty and of the company’s code of conduct. Note that an employee who is terminated from employment for a just cause is not entitled to payment of separation benefits as provided by Section 7, Rule I, Book VI, of the Omnibus Rules Implementing the Labor Code. You will, however, be given your last payslip including pay for worked days prior to the effective date of separation, unused leave credits, 13th month pay pro-rated amount, and any allowances or incentives you may have earned prior to the effective date of separation. Please contact hr.cebu@tatepublishing.net, 09173015686, or 0323182586 should you have any concern about this matter. You have seven (7) calendar days to dispute this decision after which time, this decision will be considered final and will be recorded in your permanent file.";
				}else{
					$notice .= "\n\n\nYou are further advised in your own interest to be cautious and not to repeat such an act in the future. It is your responsibility to perform your job duties efficiently and effectively on a consistent and ongoing basis. Failure to show improvement in your job performance or behavior and/or any future violations of the same or similar nature will subject you to further disciplinary action, up to and including termination of employment. Signing this form does not necessarily indicate that you agree with this notice of disciplinary action. If you are dissatisfied with this disciplinary decision, you may appeal in writing within five (5) working days of the decision being given. Otherwise, the disciplinary decision will be considered final and added to your personnel file.";
				}
				
				$pdf->SetFont('Arial','',11);
				$pdf->setXY(20, 80);
				$pdf->MultiCell(175, 4, $notice ,0,'L',false);
				
				$pdf->SetFont('Arial','B',10);
				$pdf->setXY(20, 195.5);
				$pdf->MultiCell(77, 4,strtoupper($thru->name),0,'C',false);
				$pdf->setXY(135, 195.5);
				$pdf->MultiCell(50, 4,date('F d, Y', strtotime($row->cardate)),0,'C',false);
				
				$pdf->setXY(20, 222);
				$pdf->MultiCell(77, 4,strtoupper($row->name),0,'C',false);
				$pdf->setXY(135, 222);
				$pdf->MultiCell(50, 4,date('F d, Y', strtotime($row->cardate)),0,'C',false);				
			}
				
			if($this->uri->segment(3)!='')
				$pdf->Output('NTE_'.$nteID.'.pdf', $this->uri->segment(3));	
			else
				$pdf->Output('NTE_'.$nteID.'.pdf', 'I');		
		}else{
			echo 'No NTE ID.';
		}
	}
	
	public function nteissued(){
		$data['content'] = 'nteissued';
		
		if($this->user!=false){
			if($this->user->access=='' && $this->user->level==0){
				$data['access'] = false;
 			}else{
				if(!empty($_POST)){
					if($_POST['submitType']=='nteprinted'){
						$reqInfo = $this->dbmodel->getSingleInfo('staffs', 'fname, email, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=empID_fk) AS name, status', 'nteID="'.$_POST['nteID'].'"', 'LEFT JOIN staffNTE ON issuer=empID');
						//update database	
						if($reqInfo->status==1)
							$this->dbmodel->updateQuery('staffNTE', array('nteID'=>$_POST['nteID']), array('nteprinted'=>$this->user->username.'|'.date('Y-m-d H:i:s'))); 
						else	
							$this->dbmodel->updateQuery('staffNTE', array('nteID'=>$_POST['nteID']), array('carprinted'=>$this->user->username.'|'.date('Y-m-d H:i:s'))); 
						
						//send email to requestor to get printed document from HR						
						$emBody = '<p>Hi '.$reqInfo->fname.',</p>
								<p>Please be informed that the '.(($reqInfo->status==1)?'NTE':'CAR').' for '.$reqInfo->name.' has been printed. Please collect the document from HR and have it signed by '.$reqInfo->name.'. <span style="color:red;">Note that you will receive a daily reminder until this document is returned to HR with complete signatures (yours and employee\'s).</span></p>
								<p>&nbsp;</p>
								<p>Yours truly,<br/>
								<b>The Human Resources Department</b></p>';												
						$this->emailM->sendEmail('hr.cebu@tatepublishing.net', $reqInfo->email, 'NTE document has been printed', $emBody, 'The Human Resources Department');	
					}else if($_POST['submitType']=='signedNTE'){
						$nteD = $this->dbmodel->getSingleInfo('staffNTE', 'nteID, status', 'nteID="'.$_POST['nteID'].'"');
						
						//upload signed document to server
						if($_FILES['signedFile']['name']!=''){
							$katski = array_reverse(explode('.', $_FILES['signedFile']['name']));
							$fname = $_POST['nteID'].'_'.(($nteD->status==1)?'NTE':'CAR').'_'.date('YmdHis').'.'.$katski[0];
							move_uploaded_file($_FILES['signedFile']['tmp_name'], UPLOADS.'NTE/'.$fname);

							//update database
							if($nteD->status==1)
								$this->dbmodel->updateQuery('staffNTE', array('nteID'=>$_POST['nteID']), array('nteuploaded'=>$this->user->username.'|'.date('Y-m-d H:i:s').'|'.$fname)); 
							else
								$this->dbmodel->updateQuery('staffNTE', array('nteID'=>$_POST['nteID']), array('caruploaded'=>$this->user->username.'|'.date('Y-m-d H:i:s').'|'.$fname));								
						}
						
						
					}
				}
			
				if($this->access->accessFullHR==true){
					$data['pendingPrint'] = $this->dbmodel->getQueryResults('staffNTE', 'nteID, type, offenselevel, dateissued, status, sanction, username, CONCAT(fname," ",lname) AS name, (SELECT CONCAT(fname," ",lname) AS iname FROM staffs e WHERE e.empID=staffNTE.issuer LIMIT 1) AS issuerName', '(status=1 AND nteprinted="") OR ((status=0 OR status=3) AND carprinted="")', 'LEFT JOIN staffs ON empID=empID_fk', 'dateissued DESC');
					$data['pendingUpload'] = $this->dbmodel->getQueryResults('staffNTE', 'nteID, type, offenselevel, dateissued, status, sanction, carprinted, nteprinted, username, CONCAT(fname," ",lname) AS name, (SELECT CONCAT(fname," ",lname) AS iname FROM staffs e WHERE e.empID=staffNTE.issuer LIMIT 1) AS issuerName', '(status=1 AND nteprinted!="" AND nteuploaded="") OR ((status=0 OR status=3) AND carprinted!="" AND caruploaded="")', 'LEFT JOIN staffs ON empID=empID_fk', 'dateissued DESC');
				}
				
				$condition = '';
				if($this->access->accessFullHR==false){
					$ids = '"",'; //empty value for staffs with no under yet
					$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);				
					foreach($myStaff AS $m):
						$ids .= $m->empID.',';
					endforeach;
					if($ids!='')
						$condition .= ' AND empID_fk IN ('.rtrim($ids,',').')';
				}
				
				$data['allActive'] = $this->dbmodel->getQueryResults('staffNTE', 'nteID, type, offenselevel, dateissued, status, sanction, username, CONCAT(fname," ",lname) AS name, (SELECT CONCAT(fname," ",lname) AS iname FROM staffs e WHERE e.empID=staffNTE.issuer LIMIT 1) AS issuerName', 'status!=2'.$condition, 'LEFT JOIN staffs ON empID=empID_fk', 'dateissued DESC');
				
				//OFFENSES
				$offArr = $this->dbmodel->getQueryResults('staffOffenses', 'offenseID, offense');
				foreach($offArr AS $of)
					$data['dataOffense'][$of->offenseID] = $of->offense;
				
			}
		}
		$this->load->view('includes/template', $data);
	}
	
	public function detailsNTE(){
		$data['content'] = 'detailsNTE';
		if($this->user!=false){				
			$nteID = $this->uri->segment(2);
			
			$data['row'] = $this->dbmodel->getSingleInfo('staffNTE', 'staffNTE.*, CONCAT(fname," ",lname) AS name, email, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=issuer AND issuer!=0) AS issuerName, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=carissuer AND carissuer!=0) AS carName', 'nteID="'.$nteID.'"', 'LEFT JOIN staffs ON empID=empID_fk'); 
			
			if($data['row']->type=='tardiness' || is_numeric($data['row']->type)) $data['sanctionArr'] = $this->textM->constantArr('sanctiontardiness');
			else $data['sanctionArr'] = $this->textM->constantArr('sanctionawol');
			
			//OFFENSES
			if(is_numeric($data['row']->type)){
				$data['dataOffense'] = $this->dbmodel->getSingleInfo('staffOffenses', 'offense, category, level', 'offenseID="'.$data['row']->type.'"');
			}
			$data['warningStatusArr'] = $this->textM->constantArr('writtenWarningStatus');
			
			if(!empty($_POST)){
				if($_POST['submitType']=='aknowledge'){
					$this->dbmodel->updateQuery('staffNTE', array('nteID'=>$nteID), array('response' => addslashes($_POST['response']), 'responsedate'=>date('Y-m-d H:i:s')));
					$this->commonM->addMyNotif($this->user->empID, 'You acknowledged the NTE issued to you. Click <a href="'.$this->config->base_url().'detailsNTE/'.$nteID.'/" class="iframe">here</a> to view details.', 5);
					
					
					//send email to nte requestor if there is response
					$issuerEmail = $this->dbmodel->getSingleField('staffs', 'email', 'empID="'.$data['row']->issuer.'"');
					$ebody = '<p>Hi,</p>
							<p>Please be informed that '.$data['row']->name.' has responded to the NTE with the below explanation:</p>
							<p><b>"'.((empty($_POST['response']))?'None':nl2br($_POST['response'])).'"</b><p>
							<p>Your next step as the NTE requestor will be to evaluate the merit of the explanation whether or not you find that the explanation is sufficient, you must document your decision with a Corrective Action Report. Click <b><a href="'.$this->config->base_url().'detailsNTE/'.$nteID.'/">here</a></b> to generate the CAR. <span style="color:red;">Remember that you will receive a daily email reminder to generate the CAR from the date the explanation was received until the CAR is generated.</span></p>
							<p>&nbsp;</p>
							<p>Thanks!</p>';
					$this->emailM->sendEmail('careers.cebu@tatepublishing.net', $issuerEmail, 'NTE Response of '.$data['row']->name, $ebody, 'CareerPH');
					
					header('Location:'.$_SERVER['REQUEST_URI']);
					exit;
				}else if($_POST['submitType']=='cancel'){
					$this->dbmodel->updateQuery('staffNTE', array('nteID'=>$this->uri->segment(2)), array('status' => '2', 'canceldata'=>$this->user->empID.'|'.date('Y-m-d H:i').'|'.$_POST['cancelv']));
					
					//add notifications
					$this->commonM->addMyNotif($data['row']->empID_fk, $this->user->name.' cancelled the NTE issued to you. Click <a href="'.$this->config->base_url().'detailsNTE/'.$data['row']->nteID.'/" class="iframe">here</a> to view info.', 4, 1);
					$this->commonM->addMyNotif($this->user->empID, 'You cancelled the NTE you issued to '.$data['row']->name.'. Click <a href="'.$this->config->base_url().'detailsNTE/'.$data['row']->nteID.'/" class="iframe">here</a> to view info.',5);
					
					if($this->user->username != $data['row']->issuer){
						$carID = $this->dbmodel->getSingleField('staffs', 'empID', 'username="'.$data['row']->issuer.'"');
						$this->commonM->addMyNotif($carID, $this->user->name.' cancelled the NTE you issued to '.$data['row']->name.'. Click <a href="'.$this->config->base_url().'ntepdf/'.$data['row']->nteID.'/" class="iframe">here</a> to view info.', 0, 1);
					}
					exit;
				}else if($_POST['submitType']=='issuecar'){				
					if($_POST['satisfactory']==1){
						$upArr['status'] = '3';						
					}else{
						$upArr['status'] = '0';						
						$upArr['sanction'] = $_POST['sanction'];
						$upArr['proceedsanc'] = $_POST['psanction'];
					}
					$upArr['satisfactory'] = $_POST['satisfactory'];
					$upArr['carissuer'] = $this->user->empID;
					$upArr['cardate'] = date('Y-m-d H:i:s');
					
					if(!empty($_POST['response'])){
						$upArr['response'] = $_POST['response'];
						$upArr['responsedate'] = date('Y-m-d H:i:s');
					}
						
					if($upArr['sanction']=='Termination'){
						$upArr['reasonsanction'] = $_POST['whyterminate'];
						$upArr['suspensiondates'] = date('Y-m-d',strtotime($_POST['endDate']));
					}else{
						$sudates = '';
						$numdates = 0;
						foreach($_POST['sdates'] AS $s):
							if($s!=''){
								$sudates .= date('Y-m-d',strtotime($s)).'|';
								$numdates++;
							}
						endforeach;
						
						$upArr['suspensiondates'] = rtrim($sudates, '|');					
						$upArr['reasonsanction'] = $_POST['reasonsanction'];
						$upArr['planImp'] = $_POST['planImp'];
					}
											
					$this->dbmodel->updateQuery('staffNTE', array('nteID'=>$_POST['nteID']), $upArr);
					
					if($upArr['status']==3){
						$ntx = $this->user->name.' approved your NTE response. No CAR generated.';
						$ntxu = 'You approved the NTE response of '.$data['row']->name.'. No CAR generated.';
					}else{
						$ntx = $this->user->name.' issued a Corrective Action Report to you.';
						$ntxu = 'You issued a Corrective Action Report to '.$data['row']->name.'.';
						
						//send email to employee
						$emBody = '<p>Hi,</p>
								<p>Please be informed that '.$this->user->name.' has generated a CAR for you. The corrective action is '.$_POST['sanction'].'. Please check <b><a href="'.$this->config->base_url().'detailsNTE/'.$_POST['nteID'].'/">here</a></b> to see the details of the CAR. Should you have any concern about this CAR, please discuss and clarify with '.$data['row']->issuerName.' before signing the document.</p>
								<p>&nbsp;</p>
								<p>Thanks!</p>';
						$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $data['row']->email, 'CAR has been generated', $emBody, 'CAREERPH');
					}
					
					$this->commonM->addMyNotif($data['row']->empID_fk, $ntx.' Please check your disciplinary records or click <a href="'.$this->config->base_url().'detailsNTE/'.$_POST['nteID'].'/" class="iframe">here</a> to view the NTE details.', 4, 1);
					$this->commonM->addMyNotif($this->user->empID, $ntxu.' Click <a href="'.$this->config->base_url().'detailsNTE/'.$_POST['nteID'].'/" class="iframe">here</a> to view the NTE details.', 5);
					
					header('Location:'.$_SERVER['REQUEST_URI']);
					exit;
				}
			}
						
			//check if you are allowed to issue nte
			// if($this->user->access=='' && $this->commonM->checkStaffUnderMe($data['row']->username)==false){
			// 	$data['access'] = false;
			// }
		}
	
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function adminsettings(){
		$data['content'] = 'adminsettings';
		
		if($this->access->accessFull==false){
			$data['access'] = false;
		}else if($this->user!=false){	
			$id = $this->uri->segment(2);
			$data['updated'] = '';
			
			if(!empty($_POST)){
				$atext = '';
				if($_POST['submitType']=='accesstype'){
					$actext = '';
					if(isset($_POST['access'])){
						$cntNevada = count($_POST['access']);
						for($i=0; $i<$cntNevada; $i++){
							$actext .= $_POST['access'][$i].',';
						}
						$this->dbmodel->updateQuery('staffs', array('empID'=>$id), array('access' => rtrim($actext,',')));
					}
					if( isset($_POST['exclude_schedule']) ){
						$this->dbmodel->updateQuery('staffs', array('empID'=>$id), array('exclude_schedule' => $_POST['exclude_schedule']) );
					}					
					
					$data['updated'] = 'Access type successfully submitted.';
					$atext = 'Updated access type to '.rtrim($actext,',');
				}
				$this->commonM->addMyNotif($this->user->empID, $atext, 5);
			}
			
			$data['row'] = $this->dbmodel->getSingleInfo('staffs', 'access, CONCAT(fname," ",lname) AS name, exclude_schedule', 'empID="'.$id.'"');
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function detailsnotifications(){	
		$data['content'] = 'detailsnotifications';	
		if($this->user!=false){			
			if(!empty($_POST)){
				$this->dbmodel->updateQuery('staffMyNotif', array('notifID'=>$_POST['notifID']), array('isNotif'=>0));
				exit;
			}
			
			$data['row'] = $this->dbmodel->getQueryResults('staffMyNotif', 'staffMyNotif.*, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=sID AND sID!=0) AS nName', 'isNotif=1 AND empID_fk="'.$this->user->empID.'"', '', 'nstatus DESC, notifID DESC');

		}
	
		$this->load->view('includes/templatecolorbox', $data);
	}

	public function sendValentinesGreetings(){
		$data['content'] = 'sendvalentinesgreetings';
		$queryDept = $this->dbmodel->ptdbQuery('SELECT * FROM eDept');
		$query = $queryDept->result();
		$data['PTDeptArr'] = array();
		$data['arraystaff'] = $this->dbmodel->getResultArray('staffs', 'username, CONCAT(fname, " ", lname) AS staffName', 'active = 1', '', 'fname ASC');

		foreach($query AS $q){
			$data['PTDeptArr'][$q->eDeptKey] = $q->eDeptName;
		}
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function fileleave(){
		$data['content'] = 'fileleave';
		
		if($this->user!=false){
			$data['submitted'] = false;	
			$data['segment2'] = $this->uri->segment(2);
			if($this->user->empStatus=='probationary' && $data['segment2']==''){
				header('Location:'.$this->config->base_url().'fileleave/leave/');
				exit;
			} 
			
			if($data['segment2']=='offset'){
				$data['allowedOffset'] = $this->dbmodel->getSingleInfo('staffs', 'offsetHrs', 'empID="'.$this->user->empID.'"');
				$data['numOffset'] = $this->dbmodel->getQueryResults('staffLeaves', 'totalHours', 'empID_fk="'.$this->user->empID.'" AND leaveStart LIKE "'.date('Y-m').'%" AND iscancelled=0 AND status!=3 AND leaveType=4');
			}else{
				$data['numLeaves'] = $this->dbmodel->getQueryResults('staffLeaves', 'leaveID', 'empID_fk="'.$this->user->empID.'" AND leaveStart LIKE "'.date('Y-m').'%" AND iscancelled=0 AND status!=3');
			}
									
			if(!empty($_POST)){			
				if($_POST['submitType']=='chooseFromUploaded'){
					$upFiles = $this->dbmodel->getQueryResults('staffUploads', 'upID, docName, fileName', 'empID_fk="'.$this->user->empID.'" AND isDeleted=0');
					if(count($upFiles)==0){
						echo 'No uploaded files.';
					}else{
						echo '<b>Uploaded files:</b><br/>';
						foreach($upFiles AS $u):
							echo '<input type="checkbox" class="upFile" onClick="addSupVal(\'upDocDV++'.$u->fileName.'\', this);"/> <a href="'.$this->config->base_url().UPLOAD_DIR.$this->user->username.'/'.$u->fileName.'">'.(($u->docName!='')?$u->docName:$u->fileName).'</a><br/>';
						endforeach;
					}		
					exit;
				}
				
				//check for duplicate insert leave data
				$dupQuery = $this->dbmodel->getSingleInfo('staffLeaves','leaveID', 'empID_fk="'.$this->user->empID.'" AND date_requested LIKE "'.date('Y-m-d').'%" AND leaveType="'.$_POST['leaveType'].'" AND reason LIKE "%'.addslashes($_POST['reason']).'%" AND leaveStart="'.date('Y-m-d H:i:s',strtotime($_POST['leaveStart'])).'" AND leaveEnd="'.date('Y-m-d H:i:s',strtotime($_POST['leaveEnd'])).'" AND code="'.$_POST['code'].'" AND totalHours="'.$_POST['totalHours'].'" AND status=0 AND iscancelled = 0');
				
				if(count($dupQuery)>0){
					echo 'There is a duplicate entry of this leave. Click <a href="'.$this->config->base_url().'staffleaves/'.$dupQuery->leaveID.'/">here</a> to view duplicate leave entry or check "Time Off Details" on My HR Info page if filed leave exists or inform IT if no duplicate entry and you can\'t file leave.';
					exit;
				}
				
				$data['errortxt'] = '';										
				if(empty($_POST['reason'])) $data['errortxt'] .= 'Reason of absence is empty<br/>';
								
				if($_POST['submitType']=='offset'){
					$_POST['leaveType'] = '4'; //for offset leave type
					if(empty($_POST['leaveStart'])) $data['errortxt'] .= 'Start Date and Time of Absence is empty<br/>';
					if(empty($_POST['leaveEnd'])) $data['errortxt'] .= 'End Date and Time of Absence is empty<br/>';
					if(strtotime($_POST['leaveStart']) > strtotime($_POST['leaveEnd'])){
						$data['errortxt'] .= 'End of Leave is less than start of leave.<br/>';
					}elseif(strtotime($_POST['leaveStart']) == strtotime($_POST['leaveEnd'])){
						$data['errortxt'] .= 'The start and end of your leave is the same. Please correct.<br/>';
					}
					
					$offdates = '';
					$cnthrs = 0;
					$offdatecheck = true;
					$cntLuisiana = count($_POST['offdate']);
					for($u=0; $u<$cntLuisiana; $u++){
						$start = $_POST['offdate'][$u]['start'];
						$end = $_POST['offdate'][$u]['end'];
					
						if(!empty($start) && !empty($end)){
							$offdates .= date('Y-m-d H:i', strtotime($start)).','.date('Y-m-d H:i', strtotime($end)).'|';
							$countnumhours = round((strtotime(date('Y-m-d H:i', strtotime($end))) - strtotime(date('Y-m-d H:i', strtotime($start))))/3600, 1);
							if($countnumhours==9) $cnthrs += 8;
							else $cnthrs += $countnumhours;
						}
						
						if((!empty($start) && empty($end)) || (empty($start) && !empty($end)))
							$offdatecheck = false;						
					}
					
					$data['numOffset'] = $this->dbmodel->getQueryResults('staffLeaves', 'totalHours', 'empID_fk="'.$this->user->empID.'" AND leaveStart LIKE "'.date('Y-m', strtotime($_POST['leaveStart'])).'%" AND iscancelled=0 AND status!=3 AND leaveType=4');
					
					$tambal = 0;
					foreach($data['numOffset'] AS $noffset):
						$tambal += $noffset->totalHours;
					endforeach;
					
					if(($tambal+$_POST['totalHours'])> $data['allowedOffset']->offsetHrs ) $data['errortxt'] .= 'You cannot file offset leave more than '.$data['allowedOffset']->offsetHrs.' hours in a month.<br/>';
					
					if($offdatecheck==false) $data['errortxt'] .= 'Check your schedule of work to compensate<br/>';
					if(empty($offdates)) $data['errortxt'] .= 'Schedule of work to compensate absence is empty<br/>';
					
				}else{					
					if(empty($_POST['leaveStart'])) $data['errortxt'] .= 'Start Date and Time of Absence is empty<br/>';
					if(empty($_POST['leaveEnd'])) $data['errortxt'] .= 'End Date and Time of Absence is empty<br/>';
					
					if(isset($_POST['code']) && !empty($_POST['code'])){
						$code = $this->dbmodel->getSingleInfo('staffCodes', 'codeID, generatedBy', 'code="'.$_POST['code'].'" AND usedBy=0 AND (forWhom=0 OR forWhom='.$this->user->empID.') AND status=1');
						if(count($code)==0) $data['errortxt'] = 'Code is invalid or already used.<br/>';
					}
					
					$dtoday = date('F d, Y H:i');					
					if(!empty($_POST['leaveStart']) && !empty($_POST['leaveEnd'])){
						if(strtotime($_POST['leaveStart']) > strtotime($_POST['leaveEnd'])){
							$data['errortxt'] .= 'End of Leave is less than start of leave.<br/>';
						}elseif(strtotime($_POST['leaveStart']) == strtotime($_POST['leaveEnd'])){
							$data['errortxt'] .= 'The start and end of your leave is the same. Please correct.<br/>';
						}else{							
							if($_POST['leaveType']==1){ //if vacation leave								
								$vdate = date('F d, Y H:i', strtotime('+13 days'));
								if(strtotime($_POST['leaveStart']) < strtotime($vdate) && (!isset($code) || (isset($code) && count($code)==0))){
									$data['errortxt'] .= 'You cannot file vacation leave less than 2 weeks. Enter valid code to bypass this condition.<br/>';
								}
								if($_POST['totalHours']>40){
									$data['errortxt'] .= 'The maximum number of days per leave application is five (5) days or forty (40) hours.';
								}
							}else if($_POST['leaveType']==2 || $_POST['leaveType']==3){
								if(strtotime(date('Y-m-d', strtotime($_POST['leaveStart']))) >= strtotime(date('Y-m-d')) && (!isset($code) || (isset($code) && count($code)==0)))
									$data['errortxt'] .= 'Invalid Start of Leave. Sick/Emergency leave cannot be filed in advance. Enter valid code to bypass this condition.<br/>';
							}
						}
					}
					$numsD = 0;
					if($_POST['leaveType']==2 || $_POST['leaveType']==3 || ($_POST['leaveType']==5 && $_POST['fromUploaded']=='')){	
						if(isset($_FILES['supDocs'])){
							foreach($_FILES['supDocs']['name'] AS $s):
								if($s!='') $numsD++;
							endforeach;
						}
						
						if(($_POST['leaveStart']!='' && strtotime($_POST['leaveStart']) > strtotime($dtoday) && $numsD==0) || 
							(!isset($_POST['noDocs']) && $numsD==0) ){
							$data['errortxt'] .= 'No supporting documents submitted.<br/>';
						}
					}
					
					if($_POST['leaveType']==5){
						$today = date('Y-m-d');
						$startDay = date('m-d', strtotime($this->user->startDate));

						$validYear = date('Y-').$startDay;
						if($today<$validYear)
							$validYear = date('Y-', strtotime('-1 year')).$startDay;
						
						$patHours = $this->dbmodel->getQueryArrayResults('staffLeaves', 'totalHours', 'leaveType=5 AND status=1 AND iscancelled=0 AND reason="'.$_POST['reason'].'" AND empID_fk="'.$this->user->empID.'" AND leaveStart>"'.$validYear.'"');
											
						$phours = 0;
						foreach($patHours AS $p):
							$phours += $p->totalHours;
						endforeach;
						if($phours>56){ //8hours x 7days
							$data['errortxt'] .= 'Paternity leave is valid for 7 days in a year per child. You already used '.($phours/8).' days.<br/>';
						}else if(($phours+$_POST['totalHours']) > 56){
							$data['errortxt'] .= 'Paternity leave is valid for 7 days in a year per child. You already used '.($phours/8).' days. You only have '.((56-$phours)/8).' day/s or '.(56-$phours).' hours remaining leaves.<br/>';
						}
					}
					
				}
								
				if(empty($_POST['totalHours'])){ $data['errortxt'] .= 'Total Number of Hours is empty<br/>'; }
				else if(!ctype_digit ($_POST['totalHours'])){ $data['errortxt'] .= 'Total Number of Hours is invalid<br/>'; }
				else if($_POST['totalHours']%4!=0){ $data['errortxt'] .= 'Number of hours should be divisible by 4 or 8.'; }
				else if(isset($cnthrs) && $_POST['totalHours']!=$cnthrs){ $data['errortxt'] .= 'Number of offset hours does not match with hours of absence.'; }
				
				if($data['errortxt']!='' && (($_POST['leaveType']==2 || $_POST['leaveType']==3 || $_POST['leaveType']==5) && $numsD>0 || ($_POST['leaveType']==5 && $_POST['fromUploaded']!=''))){
					$data['errortxt'] .= 'Select supporting documents again.<br/>';
				}
									
				//process if no errors
				if(empty($data['errortxt']) && count($dupQuery)==0){
					$insArr['empID_fk'] = $this->user->empID;
					$insArr['date_requested'] = date('Y-m-d H:i:s');
					$insArr['reason'] = addslashes($_POST['reason']);
					$insArr['totalHours'] = $_POST['totalHours'];
					$insArr['notesforHR'] = $_POST['notesforHR'];
					$insArr['leaveStart'] = date('Y-m-d H:i', strtotime($_POST['leaveStart']));
					$insArr['leaveEnd'] = date('Y-m-d H:i', strtotime($_POST['leaveEnd']));
					if($_POST['leaveType']==4){
						$insArr['offsetdates'] = $offdates;
						$insArr['leaveType'] = 4;
					}else{
						$insArr['leaveType'] = $_POST['leaveType'];
					}
					
					//update code data if filing leave using codes
					if(isset($_POST['code']) && !empty($_POST['code']) && isset($code) && count($code)>0){
						$insArr['code'] = $_POST['code'];
						$this->dbmodel->updateQuery('staffCodes', array('codeID'=>$code->codeID), array('usedBy'=>$this->user->empID, 'dateUsed'=>date('Y-m-d H:i:s'), 'type'=>'Leave', 'status'=>2));
						$this->commonM->addMyNotif($code->generatedBy, $this->user->name.' used your generated code <b>'.$_POST['code'].'</b> for filing leave.', 0, 1);
					}
					
					if(isset($_POST['fromUploaded']) && !empty($_POST['fromUploaded']))
						$insArr['supDocs'] = $_POST['fromUploaded'];
									
					$insID = $this->dbmodel->insertQuery('staffLeaves', $insArr);
					$this->staffM->updatePublishLog($insID); ///UPDATING tcStaffLogPublish
					
					if(isset($_FILES) && !empty($_FILES['supDocs'])){	
						$insDoc = '';
						for($x=0; $x<5; $x++){
							if($_FILES['supDocs']['name'][$x]!=''){
								$katski = array_reverse(explode('.', $_FILES['supDocs']['name'][$x]));
								$fname = $insID.'_'.$this->user->empID.'_'.date('YmdHis').'_'.$x.'.'.$katski[0];
								$insDoc .= $fname.'|';
								move_uploaded_file($_FILES['supDocs']['tmp_name'][$x], UPLOADS.'/leaves/'.$fname);	
							}
						}	
						if($insDoc!=''){							
							$this->dbmodel->updateConcat('staffLeaves', 'leaveID="'.$insID.'"', 'supDocs', $insDoc);
						}
					}
									
					$leaveTypeArr = $this->textM->constantArr('leaveType');
					//add notes to employee
					$this->commonM->addMyNotif($this->user->empID, 'Filed <b>'.$leaveTypeArr[$_POST['leaveType']].'</b> for '.date('F d, Y h:i a', strtotime($_POST['leaveStart'])).' to '.date('F d, Y h:i a', strtotime($_POST['leaveEnd'])).'. Click <a class="iframe" href="'.$this->config->base_url().'staffleaves/'.$insID.'/">here</a> for details.', 3);
					
					//add note to supervisors					
					$ntexts = 'Filed <b>'.$leaveTypeArr[$_POST['leaveType']].'</b> for '.date('F d, Y h:i a', strtotime($_POST['leaveStart'])).' to '.date('F d, Y h:i a', strtotime($_POST['leaveEnd'])).'. Check Manage Staff > Staff Leaves page or click <a href="'.$this->config->base_url().'staffleaves/'.$insID.'/" class="iframe">here</a> to view leave details and approve.';
						
					$superID = $this->commonM->getStaffSupervisorsID($this->user->empID);
					$cntAlaska = count($superID);
					for($s=0; $s<$cntAlaska; $s++){
						$this->commonM->addMyNotif($superID[$s], $ntexts, 0, 1);
					}
					//send email to immediate supervisor
					$supEmail = $this->dbmodel->getSingleField('staffs', 'email', 'empID="'.$this->user->supervisor.'" AND "'.$this->user->supervisor.'"!=0');
					if(!empty($supEmail)){
						$msg = '<p>Hi,</p>';
						$msg .= '<p>'.$this->user->name.' filed '.$leaveTypeArr[$_POST['leaveType']].'. Login to <a href="'.$this->config->base_url().'">careerPH</a> to approve leave request.</p>';
						$msg .= '<p>Thanks!</p>';
						$this->emailM->sendEmail('careers.cebu@tatepublishing.net', $supEmail, $this->user->name.' filed '.$leaveTypeArr[$_POST['leaveType']], $msg, 'CareerPH' );
					}
										
					$data['submitted'] = true;
					unset($_POST);
				}					
			}
		}
		//$this->output->enable_profiler(true);
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function leavepdf(){
		if($this->uri->segment(2)!=''){
			$leave = $this->dbmodel->getSingleInfo('staffLeaves', 'staffLeaves.*, CONCAT(fname," ",lname) AS name, username', 'leaveID="'.$this->uri->segment(2).'"', 'LEFT JOIN staffs ON empID_fk=empID');
			$leave->reason = stripslashes($leave->reason);
			$leave->notesforHR = stripslashes($leave->notesforHR);
			if($leave->leaveType==4){
				$this->staffM->createOffsetpdf($leave);
			}else{
				$this->staffM->createLeavepdf($leave);
			}		
		}else{
			echo 'No Leave ID.';
		}
	}
	
	function staffleaves(){ 
		$segment2 = $this->uri->segment(2);

		$this->load->library('calendar');
		$data['content'] = 'staffleaves';
			
		if($this->user!=false){
			if($this->user->access=='' && $this->user->level==0 && $segment2==''){
				$data['access'] = false;
			}else{
				$data['leaveTypeArr'] = $this->textM->constantArr('leaveType');
				$data['leaveStatusArr'] = $this->textM->constantArr('leaveStatus');
				$data['statusMaternityArr'] = $this->textM->constantArr('statusMaternityLeave');
				if($segment2 != ''){
					$data['content'] = 'staffleavesedit';				
									
					$data['row'] = $this->dbmodel->getSingleInfo('staffLeaves', 'staffLeaves.*, username, fname, CONCAT(fname," ",lname) AS name, email, dept, supervisor, startDate, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor LIMIT 1) AS supName,(SELECT email FROM staffs e WHERE e.empID=staffs.supervisor LIMIT 1) AS supEmail, leaveCredits, empStatus', 'leaveID="'.$segment2.'"', 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');

					//check if we have leave
					if( empty($data['row']->leaveID) ){
						show_404();
						exit();
					}
					
					$data['leaveHistory'] = $this->dbmodel->getQueryResults('staffLeaves', 'leaveID, leaveType, leaveStart, leaveEnd, status, iscancelled, isrefiled, totalHours', 'empID_fk="'.$data['row']->empID_fk.'" AND leaveID!="'.$segment2.'" AND status!=5 AND (leaveStart LIKE "'.date('Y-m-').'%" OR leaveEnd LIKE "'.date('Y-m-').'%")');
					$data['dir_leave'] = UPLOADS.'leaves/';		
										
					if($this->user->access=='' && $this->user->level==0 && $this->user->empID != $data['row']->empID_fk)
						$data['access'] = false;
				
					if(!empty($_POST)){
						$updateArr = array();
						$actby = '';
						if($_POST['submitType']=='svisor'){
							$updateArr['status'] = $_POST['approve'];
							$updateArr['approverID'] = $this->user->empID;
							$updateArr['remarks'] = $_POST['remarks'];						
							$updateArr['dateApproved'] = date('Y-m-d');	
							if($data['row']->leaveType!=4 && $data['row']->leaveType!=5)
								$updateArr['leaveCreditsUsed'] = $_POST['leaveCreditsUsed'];	
																
							$usernote = '<b>'.ucfirst(strtolower($data['leaveStatusArr'][$updateArr['status']])).'</b> your leave request.';
							$actby = '<b>'.ucfirst(strtolower($data['leaveStatusArr'][$updateArr['status']])).'</b> '.$data['row']->name.'\'s leave request.';
							if($updateArr['status']!=3){
								$usernote .= ' This is waiting for HR approval.';
							
								$hrnote = '<b>'.ucfirst(strtolower($data['leaveStatusArr'][$updateArr['status']])).'</b> '.$data['row']->name.'\'s leave request. This is waiting for your approval. Click <a href="'.$this->config->base_url().'staffleaves/'.$data['row']->leaveID.'/" class="iframe">here</a> to take HR action.';	
							}
						}else if($_POST['submitType']=='hr'){	
							if($_POST['status']==4){ //additional info required
								$updateArr['iscancelled'] = 4;
								$addInfo = '<b>'.strtoupper($this->user->username).'</b><br/>
									Sent: '.date('M d, Y h:i A').'<br/>
									To: '.ltrim($_POST['toEmail'],',').'<br/>
									Subject: '.$_POST['subjectEmail'].'<br/><br/>
									'.$_POST['message'].'<br/><br/>
									============================================================================================<br/>
								';
								$this->emailM->sendEmail('careers.cebu@tatepublishing.net', $_POST['toEmail'], $_POST['subjectEmail'], $_POST['message'], 'CareerPH' );
								$this->dbmodel->updateConcat('staffLeaves', 'leaveID="'.$data['row']->leaveID.'"', 'addInfo', $addInfo);
							}else{
								if($_POST['status']!=$_POST['oldstatus']){
									$updateArr['status'] = $_POST['status'];
								}
									
								$updateArr['hrapprover'] = $this->user->empID;
								$updateArr['hrremarks'] = $_POST['remarks'];						
								$updateArr['hrdateapproved'] = date('Y-m-d');
								if($data['row']->leaveType!=4 && $data['row']->leaveType!=5)
									$updateArr['leaveCreditsUsed'] = $_POST['leaveCreditsUsed'];
																
								$usernote = 'Updated your '.strtolower($data['leaveTypeArr'][$data['row']->leaveType]).' request';							
								if($_POST['status']!=$_POST['oldstatus']){
									$usernote .= ' from <b>'.$data['leaveStatusArr'][$_POST['oldstatus']].'</b> to <b>'.$data['leaveStatusArr'][$_POST['status']].'</b>';
									$approvernote = 'Updated '.$data['row']->name.'\'s leave request from <b>'.$data['leaveStatusArr'][$_POST['oldstatus']].'</b> to <b>'.$data['leaveStatusArr'][$_POST['status']].'</b>';
									$updateArr['hrremarks'] .= ' - <i>updated '.$data['leaveStatusArr'][$_POST['oldstatus']].' to '.$data['leaveStatusArr'][$_POST['status']].'</i>';
								}							
								$usernote .= '.';
								
								$approvernote = 'Updated '.$data['row']->name.'\'s '.strtolower($data['leaveTypeArr'][$data['row']->leaveType]).' request. ';
								$actby = 'Updated '.$data['row']->name.'\'s '.strtolower($data['leaveTypeArr'][$data['row']->leaveType]).' request. ';
																		
								if($data['row']->leaveType!=4 && $data['row']->leaveType!=5){
									if($data['row']->leaveCredits>0 || ($data['row']->leaveCredits==0 && date('m-d', strtotime($row->leaveStart)) < date('m-d', strtotime($row->startDate))))
									$this->dbmodel->updateQuery('staffs', array('empID'=>$data['row']->empID_fk), array('leaveCredits'=>$_POST['remaining']));
								}
								
								//for approval with previous stat additional info 
								if($_POST['status']!=4 && $data['row']->iscancelled==4){
									$updateArr['iscancelled'] = 0;	
								}
								
								//send email to accounting if approved without pay
								/* if(isset($_POST['status']) && $_POST['status']==2){
									$eMsg = '<p>Hi,</p>
											<p>This is an automatic email to inform you that the leave request of employee '.$data['row']->name.' is Approved without Pay for the following dates:</p>
											<p>
												Start of Leave: '.date('F d, Y h:i a', strtotime($data['row']->leaveStart)).'<br/>
												End of Leave: '.date('F d, Y h:i a', strtotime($data['row']->leaveEnd)).'<br/>
												Total Number of Hours: '.$data['row']->totalHours.'
											</p>
											<p>Thank you.</p>
											<p><br/></p>
											<p>Best Regards,</p>
											<p>'.$this->user->name.'</p>
										';
									$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'accounting.cebu@tatepublishing.net', 'Leave Approved Without Pay', $eMsg, 'CareerPH Auto-Email');
								} */
								
								$uEmail = '<p>Hi '.$data['row']->fname.',</p>
										<p>HR updated your '.strtolower($data['leaveTypeArr'][$data['row']->leaveType]).' request. Click <a href="'.$this->config->base_url().'staffleaves/'.$data['row']->leaveID.'/">here</a> to view leave details.</p><p><br/></p><p>Thanks!</p><p>CAREERPH</p>';
								$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $data['row']->email, 'Update on Leave Request', $uEmail, 'CareerPH Auto-Email');
							}
						}else if($_POST['submitType']=='cancel'){
							$updateArr['cancelReasons'] = $_POST['cancelReasons'];
							$updateArr['datecancelled'] = date('Y-m-d H:i:s');
							
							if($data['row']->approverID==0){
								$updateArr['iscancelled'] = 1;	
								$canceldata = '^_^Cancelled: '.$this->user->username.'|'.date('Y-m-d H:i:s');	
							}else{
								$updateArr['iscancelled'] = 2;
								$approvernote = $this->user->name.' <b>cancelled</b> the leave requested. Please approve cancel request.';
								$canceldata = '^_^Pending Cancelled for IS: '.$this->user->username.'|'.date('Y-m-d H:i:s');	
							}
							$actby = '<b>Cancelled</b> leave request. ';
						}else if($_POST['submitType']=='cancelApprover'){							
							if($_POST['capprover']==1){
								$usernote = $this->user->name.' <b>approved</b> your cancel request';	
								if($data['row']->hrapprover	!= 0){
									$usernote .= ' and forwarded to HR for changing the schedule and leave credits.';
									$updateArr['iscancelled'] = 3;	
									$canceldata = '^_^Pending Cancelled for HR: '.$this->user->username.'|'.date('Y-m-d H:i:s');	
								}else{
									$updateArr['iscancelled'] = 1;	
									$canceldata = '^_^Cancelled Approved: '.$this->user->username.'|'.date('Y-m-d H:i:s');	
								}
								
								$usernote .= '.';
								$actby = 'Approved '.$data['row']->name.'\'s cancel leave request. ';
							}else{
								$updateArr['iscancelled'] = 0;	
								$usernote = '<b>Disapproved</b> your cancel request.<br/> Reason: '.$_POST['disnote'].'<br/>';
								$updateArr['datecancelled'] = '0000-00-00 00:00:00';
								$canceldata = '^_^Disapproved cancel request: '.$this->user->username.'|'.date('Y-m-d H:i:s').'<br/><i>Note: '.$_POST['disnote'].'</i>';
								$actby = 'Disapproved '.$data['row']->name.'\'s cancel leave request. ';
							}
						}else if($_POST['submitType']=='cancelHRapprove'){							
							$updateArr['iscancelled'] = 1;	
							$canceldata = '^_^Cancel request approved by HR: '.$this->user->username.'|'.date('Y-m-d H:i:s');
							
							if($_POST['leaveCredits'] != 0){
								$updateArr['leaveCreditsUsed'] = 0;
								$this->dbmodel->updateQuery('staffs', array('empID'=>$data['row']->empID_fk), array('leaveCredits'=>$_POST['leaveCredits']));
								$usernote .= 'Approved your cancel request. Your leave credits is back to '.$_POST['leaveCredits'].'.';
							}
							$actby = 'Approved '.$data['row']->name.'\'s cancel leave request. ';
							
							//send email to accounting if cancel leave without pay
							/* if($data['row']->status==2){
								$cancelMsg = '<p>Hi Accounting,</p>
											<p>This is an automatic email to inform you that the leave request of employee '.$data['row']->name.' for the below dates has been cancelled.</p>
											<p>Start of Leave: '.date('F d, Y h:i a', strtotime($data['row']->leaveStart)).'<br/>
											End of Leave: '.date('F d, Y h:i a', strtotime($data['row']->leaveEnd)).'<br/>
											Total Number of Hours: '.$data['row']->totalHours.'</p>
											<p>Thank you.</p><br/>CareerPH';
								$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'accounting.cebu@tatepublishing', 'CareerPH - Cancelled Leave Without Pay', $cancelMsg, 'CareerPH Auto-Email');
							} */	
						}else if($_POST['submitType']=='resubmit'){
							$updateArr['status'] = 0;	
							$actby = 'Resubmit your leave request. ';
						}else if($_POST['submitType']=='uploadSD'){	
							$katski = array_reverse(explode('.', $_FILES['supDocs']['name']));
							$fname = $data['row']->leaveID.'_'.$this->user->empID.'_'.date('YmdHis').'.'.$katski[0]; 
							move_uploaded_file($_FILES['supDocs']['tmp_name'], $data['dir_leave'].$fname);	
							
							$this->dbmodel->dbQuery('UPDATE staffLeaves SET supDocs=CONCAT(supDocs,"'.$fname.'|'.'") WHERE leaveID="'.$data['row']->leaveID.'"');
						}else if($_POST['submitType']=='removeDoc'){							
							$sDoc = str_replace($_POST['fname'].'|', '', $data['row']->supDocs);
							$this->dbmodel->updateQuery('staffLeaves', array('leaveID'=>$data['row']->leaveID), array('supDocs'=>$sDoc));
							exit;
						}else if($_POST['submitType']=='hrAdditionalRemarks'){
							$this->dbmodel->updateConcat('staffLeaves', 'leaveID="'.$segment2.'"', 'hrAddRemarks', $this->user->username.' '.date('Y-m-d h:i a').'>>'.$_POST['remarks'].'||');
							exit;
						}else if($_POST['submitType']=='maternityresume'){
							$updateArr['matStatus'] = 1;
							$updateArr['matDateRequested'] = date('Y-m-d H:i:s');
							$updateArr['matDateResume'] = date('Y-m-d', strtotime($_POST['dateResume']));
							$updateArr['matFile'] = $data['row']->leaveID.'_maternity_'.date('YmdHis').'.'.$this->textM->getFileExtn($_FILES['filecert']['name']);
							
							$mNote = '<b>REQUESTED TO SHORTEN MATERNITY LEAVE</b><hr/> Date Requested: '.date('F d, Y').'<br/>File: <a href="'.$this->config->base_url().$data['dir_leave'].$updateArr['matFile'].'">Fit to Work Certificate</a>';
							$updateArr['matHistory'] = $data['row']->matHistory.'<div class="leaveMatNote">'.$mNote.'</div>';
							
							move_uploaded_file($_FILES['filecert']['tmp_name'], $data['dir_leave'].$updateArr['matFile']);
							$actby = 'Requested to shorten maternity leave.';
						}else if($_POST['submitType']=='submitShortenLeave'){								
							$mNote = '';
							if($_POST['stype']=='HRApproval') $mNote .= '<b>HR APPROVAL</b><hr/>';
							else $mNote .= '<b>IMMEDIATE SUPERVISOR APPROVAL</b><hr/>';							
							$mNote .= '<b>'.strtoupper($this->user->name).'</b> - <i>'.date('Y-m-d H:i:s').'</i><br/>';
							
							if($_POST['stype']=='HRApproval'){
								if($_POST['approveCert']==1){
									$updateArr['matStatus'] = 2; //HR approved and for supervisor approval
									$mNote .= '<u>Approved Fit To Work Certificate</u><br/>';
									$usernote = 'Validated your fit to work certificate. This is waiting for your immediate supervisor\'s approval.';
									$approvernote = 'Validated fit to work certificate of '.$data['row']->name.'. Please approve shorten leave request.';
								}else{
									$updateArr['matStatus'] = 3; //HR disapproved
									$mNote .= '<u>Disapproved Fit To Work Certificate</u><br/>';
									$usernote = 'Disapproved your shorten leave request.<br/>Note from HR: '.$_POST['approveNote'];
									
									//send email
									$sBody = 'Hi,<br/><br/>Your shorten maternity leave request has been disapproved by HR for reason:<br/><i>"'.nl2br($_POST['approveNote']).'"</i><br/><br/>Your payrollHero have not been changed. Note that you can request again by providing valid fit to work certificate.<br/><br/><br/>CareerPH';
									$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $data['row']->email, 'Update on Shorten Leave Request', $sBody, 'CareerPH Auto-Email');
								}
								
								$actby = 'Validated fit to work certificate uploaded by '.$data['row']->name;
							}else if($_POST['stype']=='ISApproval'){
								if($_POST['approveCert']==1){
									$updateArr['matStatus'] = 4; //IS Approved for HR for final status
									$mNote .= '<u>Approved request to shorten maternity leave</u><br/>';
									$hrnote = 'Approved request to shorten maternity leave of '.$data['row']->name.'. Please update payrollHero schedule.';
									$usernote = 'Approved your shorten leave request.';
								}else{
									$updateArr['matStatus'] = 5; //IS disapproved request
									$mNote .= '<u>Disapproved request to shorten maternity leave</u><br/>';	
									$usernote = 'Disapproved your shorten leave request.<br/>Note from your immediate supervisor: '.$_POST['approveNote'];									
									
									//send email
									$sBody2 = 'Hi,<br/><br/>Your shorten maternity leave request has been disapproved by your immediate supervisor for reason:<br/><i>"'.nl2br($_POST['approveNote']).'"</i><br/><br/>Your payrollHero have not been changed. Note that you can request again.<br/><br/><br/>CareerPH';
									$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $data['row']->email, 'Update on Shorten Leave Request', $sBody2, 'CareerPH Auto-Email');
								}
								
								$actby = 'Updated shorten maternity leave request of '.$data['row']->name.'.';
							}
							
							if($_POST['dateResume']!=$data['row']->matDateResume){
								$updateArr['matDateResume'] = $_POST['dateResume'];
								$mNote .= 'Changed intended date to resume from '.$data['row']->matDateResume.' to <b>'.$_POST['dateResume'].'</b><br/>';
							}
							
							if(!empty($_POST['approveNote'])){
								$mNote .= '<i>NOTE:</i><br/>'.$_POST['approveNote'];
							}
							
							$updateArr['matHistory'] = $data['row']->matHistory.'<div class="leaveMatNote">'.$mNote.'</div>';
						}else if($_POST['submitType']=='schedUpdated'){
							$mNote = '<b>PAYROLLHERO UPDATED</b><hr/><b>'.strtoupper($this->user->name).'</b> - <i>'.date('Y-m-d H:i:s').'</i>';
							$updateArr['matHistory'] = $data['row']->matHistory.'<div class="leaveMatNote">'.$mNote.'</div>';
							$updateArr['matStatus'] = 6;
							$actby = 'Updated payrollHero schedule of '.$data['row']->name.'.';
							
							//send email to employee
							//send email
							$sBody3 = 'Hi,<br/><br/>Your shorten maternity leave request has been approved and your payrollHero schedule has been updated. You can now return to work on <b>'.date('l, F d, Y', strtotime($data['row']->matDateResume)).'</b> the approved returned date.<br/><br/>See you!<br/><br/><br/>CareerPH';
							$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $data['row']->email, 'Update on Shorten Leave Request', $sBody3, 'CareerPH Auto-Email');
						}else if($_POST['submitType']=='refileleave'){
							$updateArr['isrefiled'] = 2;
							$updateArr['refileReason'] = $_POST['refileReason'];
							$updateArr['dateRefiled'] = date('Y-m-d H:i:s');
							$updateArr['refileDocs'] = '';
													
							for($c=0; $c<5; $c++){
								if(!empty($_FILES['refileDocs']['name'][$c])){									
									$n = array_reverse(explode('.', $_FILES['refileDocs']['name'][$c]));
									$fname = $data['row']->leaveID.'_refile_'.$c.'_'.date('YmdHis').'.'.$n[0];
									
									move_uploaded_file($_FILES['refileDocs']['tmp_name'][$c], $data['dir_leave'].$fname);	
									$updateArr['refileDocs'] .= $fname.'|';
								}
							}
							
							$approvernote = $this->user->name.' <b>refiled</b> the leave requested. Please approve this request.';							
							$actby = '<b>Refiled</b> leave request. ';
							$refiledata = 'Pending Refiled Approval for IS<br/>By: '.$this->user->username.'|'.date('Y-m-d H:i:s');
						}else if($_POST['submitType']=='refileapproval'){
							if($_POST['submitWho']=='supervisor'){
								if($_POST['ISapproval']==1){
									$updateArr['isrefiled'] = 3;
									$refiledata = 'Pending Refiling Approval for HR';
									$usernote = $this->user->name.' <b>approved</b> your refiling request and forwarded to HR for changing the schedule and leave credits';
									$actby = 'Approved '.$data['row']->name.'\'s refiling leave request. ';
								}else{
									$updateArr['isrefiled'] = 0;
									$refiledata = 'Refiling Disapproved ';
									$usernote = $this->user->name.' <b>disapproved</b> your refiling request. Reason: '.$_POST['refileIMnote'];
									
									$actby = 'Disapproved '.$data['row']->name.'\'s refiling leave request. ';
								}
								
								if(!empty($_POST['refileIMnote'])) $refiledata .= '<br/><i>Note:</i> '.$_POST['refileIMnote'];
								$refiledata .= '<br/>By: '.$this->user->username.' '.date('Y-m-d H:i:s');
							}else{
								if($_POST['changeStatus']==1){
									$updateArr['isrefiled'] = 1;
									$updateArr['status'] = 1;
									$updateArr['leaveCreditsUsed'] = $_POST['leaveCreditsUsed'];
									$refiledata = 'HR Approved Refiling Request';
									$refiledata .= '<br/>Changed status from without pay to <b>WITH PAY</b>';
									$refiledata .= '<br/>Leave credit deducted '.$_POST['leaveCreditsUsed'].' from '.$data['row']->leaveCredits.' and now '.$_POST['leaveCredits'];
									if(!empty($_POST['noterefile'])) $refiledata .= '<br/><i>Note:</i> '.$_POST['noterefile'].'<br/>';
									$refiledata .= '<br/>By: '.$this->user->username.' '.date('Y-m-d H:i:s');
									
									$actby = 'Approved '.$data['row']->name.'\'s refiling leave request. ';
									$this->dbmodel->updateQuery('staffs', array('empID'=>$data['row']->empID_fk), array('leaveCredits'=>$_POST['leaveCredits']));
									$usernote = $this->user->name.' approved your refiling request. This leave is "With Pay" and your leave credits is now '.$_POST['leaveCredits'].'.';
									
									//send email to accounting
									/* $refiledMsg = '<p>Hi Accounting,</p>
											<p>This is an automatic email to inform you that the leave request of employee '.$data['row']->name.' for the below dates has been refiled and now WITH PAY.</p>
											<p>Start of Leave: '.date('F d, Y h:i a', strtotime($data['row']->leaveStart)).'<br/>
											End of Leave: '.date('F d, Y h:i a', strtotime($data['row']->leaveEnd)).'<br/>
											Total Number of Hours: '.$data['row']->totalHours.'</p>
											<p>Thank you.</p><br/>CareerPH';
									$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'accounting.cebu@tatepublishing', 'CareerPH - Refiled Leave Without Pay to With Pay', $refiledMsg, 'CareerPH Auto-Email'); */
								}else{
									$updateArr['isrefiled'] = 0;
									$refiledata = 'HR Disapproved Refiling Request';
									$refiledata .= '<br/><i>Note:</i> '.$_POST['noterefile'];
									$refiledata .= '<br/>By: '.$this->user->username.' '.date('Y-m-d H:i:s');
									
									$actby = 'Disapproved '.$data['row']->name.'\'s refiling leave request. ';
									$usernote = $this->user->name.' disapproved your refiling request. Reason: '.$_POST['noterefile'];
								}
							}
						}else if($_POST['submitType']=='updatestartendleave'){ //this is for maternity leave changing of leave start and end dates							
							$updateArr['leaveStart'] = date('Y-m-d H:i:s', strtotime($_POST['leaveStart']));
							$updateArr['leaveEnd'] = date('Y-m-d H:i:s', strtotime($_POST['leaveEnd']));
							
							$leaveUpdate['pStart'] = $data['row']->leaveStart;
							$leaveUpdate['pEnd'] = $data['row']->leaveEnd;
							$leaveUpdate['nStart'] = $updateArr['leaveStart'];
							$leaveUpdate['nEnd'] = $updateArr['leaveEnd'];
							$leaveUpdate['updatedBy'] = $this->user->username;
							$leaveUpdate['dateUpdated'] = date('Y-m-d H:i:s');
														
							if(!empty($data['row']->updateData)) $udata = json_decode($data['row']->updateData);
							else $udata = array();
							
							array_push($udata, $leaveUpdate);
							$updateArr['updateData'] = json_encode($leaveUpdate);

							$newleavedates = 'maternity leave dates from '.date('F d, Y h:i a', strtotime($data['row']->leaveStart)).' - '.date('F d, Y h:i a', strtotime($data['row']->leaveEnd)).' to '.date('F d, Y h:i a', strtotime($updateArr['leaveStart'])).' - '.date('F d, Y h:i a', strtotime($updateArr['leaveEnd'])).'. ';
							$actby = 'Updated '.$data['row']->name.'\'s '.$newleavedates;
							$usernote = $this->user->name.' updated your '.$newleavedates;
						}			
						
						$addnote = ' Click <a href="'.$this->config->base_url().'staffleaves/'.$data['row']->leaveID.'/" class="iframe">here</a> to view leave details.';
												
						//ADDING NOTES
						if(!empty($usernote)) $this->commonM->addMyNotif($data['row']->empID_fk, $usernote.$addnote, 0, 1);							
						if(isset($approvernote) && !empty($approvernote)) $this->commonM->addMyNotif($data['row']->approverID, $approvernote.$addnote, 0, 1);	
						if(isset($actby) && !empty($actby)) $this->commonM->addMyNotif($this->user->empID, $actby.$addnote, 5, 0);
						if(isset($hrnote) && !empty($hrnote)){
							$hrStaffs = $this->staffM->getHRStaffID();
							$cntCanada = count($hrStaffs);
							for($hr=0; $hr<$cntCanada; $hr++){
								if($hrStaffs[$hr] != $this->user->empID)
									$this->commonM->addMyNotif($hrStaffs[$hr], $hrnote, 0, 1);
							}
						}
						
						if(isset($canceldata) && !empty($canceldata)){
							$this->dbmodel->dbQuery('UPDATE staffLeaves SET canceldata=CONCAT(canceldata,"'.$canceldata.'") WHERE leaveID="'.$data['row']->leaveID.'"');
						}
						
						if(isset($refiledata)){
							if(!empty($data['row']->refiledata)) $rdata = json_decode($data['row']->refiledata);
							else $rdata = array();
							
							array_push($rdata, $refiledata);
							$updateArr['refiledata'] = json_encode($rdata);
						}
												
						if(count($updateArr)>0){
							$this->dbmodel->updateQuery('staffLeaves', array('leaveID'=>$data['row']->leaveID), $updateArr);							
							$this->staffM->updatePublishLog($data['row']->leaveID); ///UPDATE STAFF LOGS
							
							if(isset($_POST['submitType']) && $_POST['submitType']=='maternityresume')							
								header('Location:'.$_SERVER['REQUEST_URI'].'updated/maternityresume/');
							else
								header('Location:'.$_SERVER['REQUEST_URI'].'updated/');
						}else{
							header('Location:'.$_SERVER['REQUEST_URI']);
						}
						exit; 
					}
					
					$data['leaveReset'] = false;
					$data['current'] = $data['row']->leaveCredits;
					if($data['row']->leaveCredits==0 && $data['row']->empStatus=='regular' && date('m-d', strtotime($data['row']->leaveStart)) >= date('m-d', strtotime($data['row']->startDate))){
						$data['leaveReset'] = true;	
						$diff = abs(strtotime(date('Y-m-d')) - strtotime($data['row']->startDate));
						$years = floor($diff / (365*60*60*24));
						$current = 11+$years;
						$data['current'] = 11+$years;
						
						$quer = $this->dbmodel->getQueryResults('staffLeaves', 'leaveCreditsUsed, status', 'empID_fk="'.$data['row']->empID_fk.'" AND status = 1 AND leaveCreditsUsed>0 AND hrapprover!=0 AND leaveStart>"'.date('Y').'-'.date('m-d', strtotime($data['row']->startDate)).'"');
						
						foreach($quer AS $qq):
							$data['current'] -= $qq->leaveCreditsUsed;
						endforeach;
					}
				}else{	
					$condition = '';
					if($this->user->access==''){
						$ids = '"",'; //empty value for staffs with no under yet
						$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);				
						foreach($myStaff AS $m):
							$ids .= $m->empID.',';
						endforeach;
						if($ids!='')
							$condition .= ' AND empID_fk IN ('.rtrim($ids,',').')';
					}
					$dateToday = date('Y-m-d');
					$data['tquery'] = $this->dbmodel->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'status!=3 AND iscancelled=0 AND ((leaveStart <= "'.$dateToday.'" AND leaveEnd >= "'.$dateToday.'") OR (leaveType=4 AND offsetdates LIKE "%'.$dateToday.'%"))'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position', 'date_requested DESC');
					$data['imquery'] = $this->dbmodel->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', '((status=0 AND iscancelled=0) OR matStatus=2)'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position', 'date_requested DESC');
					$data['imcancelledquery'] = $this->dbmodel->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'iscancelled=2'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position', 'date_requested DESC');
					$data['imrefiledquery'] = $this->dbmodel->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'isrefiled=2'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position', 'date_requested DESC');
					$data['hrquery'] = $this->dbmodel->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', '(status=1 OR status=2) AND ((iscancelled=0 AND hrapprover=0) OR iscancelled=3 OR iscancelled=4 OR matStatus=1 OR matStatus=4 OR isrefiled=3)'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position', 'date_requested DESC');
					
					//for all leaves
					$data['allpending'] = $this->dbmodel->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', '((status=0 AND iscancelled=0) OR iscancelled>1 OR isrefiled>1)'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position', 'date_requested DESC');
					$data['allapproved'] = $this->dbmodel->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'status=1 AND iscancelled=0'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position', 'date_requested DESC');
					$data['allapprovedNopay'] = $this->dbmodel->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'status=2 AND iscancelled=0'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position', 'date_requested DESC');
					$data['alldisapproved'] = $this->dbmodel->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'status=3 AND iscancelled=0'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position', 'date_requested DESC');
					$data['allcancelled'] = $this->dbmodel->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'iscancelled=1'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position', 'date_requested DESC');					
				}
			}
		}
		
		if($segment2!='')
			$this->load->view('includes/templatecolorbox', $data);
		else
			$this->load->view('includes/template', $data);		
	}
	
	function upsignature(){
		$data['content'] = 'uploadsignature';
		if($this->user!=false){
			$data['errortxt'] = '';
			$data['dir'] = UPLOAD_DIR.$this->user->username;
						
			if(isset($_FILES) && !empty($_FILES)){			
				if(empty($_FILES['fileToUpload']['name'])){
					$data['errortxt'] = 'No signature uploaded.';
				}else if($_FILES['fileToUpload']['type']!='image/png'){
					$data['errortxt'] = 'Upload only .png file.';
				}else{
					if (!file_exists($data['dir'])) {
						mkdir($data['dir'], 0755, true);
						chmod($data['dir'].'/', 0777);
					}
					if(file_exists($data['dir'].'/signature.png')){
						rename($data['dir'].'/signature.png', $data['dir'].'/signature_'.date('Y-m-d_H:i:s').'.png');
					}
					move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $data['dir'].'/signature.png');	

					$this->commonM->photoResizer($data['dir'].'/signature.png', $data['dir'].'/signature.png', $width = 100, $height = 100, $quality = 70, false);	
					
					if(isset($_POST['page']) && !empty($_POST['page'])){
						header("Cache-Control: no-cache, must-revalidate"); 
						header('Location:'.$_POST['page']);
						exit;
					}else if($this->uri->segment(2)!=''){
						header("Cache-Control: no-cache, must-revalidate"); 
						header('Location:'.str_replace('-','/',$this->uri->segment(2)));
						exit;
					}
				}				
			}			
		}		
		$this->load->view('includes/templatecolorbox', $data);
	}
		
	public function generatecis(){	
		$data['content'] = 'generatecis';
		$data['updated'] = false;
		$data['holidaySched_array']	= $this->textM->constantArr('staffHolidaySched');

		if($this->user!=false){
			$id = $this->uri->segment(2);	
				
			$data['row'] = $this->dbmodel->getSingleInfo('staffs', 'username, CONCAT(fname," ",lname) as name, title, staffHolidaySched, position, office, shift, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0) AS supName, org, dept, grp, subgrp, endDate, empStatus, sal', 'empID="'.$id.'" AND position!=0', 'LEFT JOIN newPositions ON posID=position');
									
			if(!empty($_POST)){
				//$this->textM->aaa($_POST);
				$updatetext = array();
				$updateArr = array();
				if( !empty($_POST['staffHolidaySched']) ){
					$updateArr['staffHolidaySched'] = $_POST['staffHolidaySched'];
					$updatetext['staffHolidaySched'] = array(
							'c' => $data['holidaySched_array'][ $data['row']->staffHolidaySched ],
							'n' => $data['holidaySched_array'][ $_POST['staffHolidaySched'] ]
						);
				}
				if(!empty($_POST['position'])){
					$p = explode('|',$_POST['position']);
					$updateArr['position'] = $p[0];
					$updatetext['position'] = array(
										'c' => $data['row']->title,
										'n' => $p[1]
									);					
				}
				
				if(!empty($_POST['office'])){					
					$updateArr['office'] = $_POST['office'];
					$updatetext['office'] = array(
										'c' => $data['row']->office,
										'n' => $_POST['office']
									);
				}
				
				if(!empty($_POST['shift'])){					
					$updateArr['shift'] = $_POST['shift'];
					$updatetext['shift'] = array(
										'c' => $data['row']->shift,
										'n' => $_POST['shift']
									);
				}
								
				if(!empty($_POST['supervisor'])){
					$s = explode('|',$_POST['supervisor']);
					$updateArr['supervisor'] = $s[0];
					$updatetext['supervisor'] = array(
										'c' => $data['row']->supName,
										'n' => $s[1]
									);
				}
				
				if(!empty($_POST['salary'])){
					$updateArr['sal'] = $_POST['salary'];
					$updatetext['salary'] = array(
										'c' => $this->textM->decryptText($data['row']->sal),
										'n' => $_POST['salary'],
										'com' => $_POST['justification']
									);
				}
								
				if(!empty($_POST['separationDate'])){
					$updateArr['endDate'] = $_POST['separationDate'];
					$updatetext['separationDate'] = array(
										'c' => $data['row']->endDate,
										'n' => $_POST['separationDate']
									);
				}
				
				if(!empty($_POST['evalDate']) && !empty($_POST['regDate'])){
					$updateArr['empStatus'] = 'regular';
					$updateArr['regDate'] = date('Y-m-d',strtotime($_POST['regDate']));
					$updatetext['empStatus'] = array(
										'c' => $data['row']->empStatus,
										'n' => 'regular',
										'evalDate' => date('Y-m-d',strtotime($_POST['evalDate'])),
										'regDate' => date('Y-m-d',strtotime($_POST['regDate']))
									);
				}
				
				$insCIS = array(
							'empID_fk' => $id,
							'datefiled' => date('Y-m-d H:i:s'),
							'effectivedate' => date('Y-m-d', strtotime($_POST['effectiveDate'])),
							'changes' => json_encode($updatetext),
							'dbchanges' => json_encode($updateArr),
							'preparedby' => $this->user->empID
						);
				
				$insid = $this->dbmodel->insertQuery('staffCIS', $insCIS);
				if($this->uri->segment(3)!=''){
					$wonka = $this->dbmodel->getSingleInfo('staffUpdated', 'empID_fk, fieldname, fieldvalue, CONCAT(fname," ",lname) AS name', 'updateID="'.$this->uri->segment(3).'"', 'LEFT JOIN staffs ON empID=empID_fk');
					if(count($wonka)>0){
						$this->dbmodel->updateQuery('staffUpdated', array('updateID'=>$this->uri->segment(3)), array('status'=>3));
											
						$wfval = $this->staffM->infoTextVal($wonka->fieldname, $wonka->fieldvalue);
						$this->commonM->addMyNotif($wonka->empID_fk, $this->user->name.' generated CIS for your update request:<br/>'.$this->textM->constantText('txt_'.$wonka->fieldname).' - '.$wfval.'<br/>Claim the printed copy of the CIS from '.$this->user->fname.', sign and submit it to HR so they can proceed with the changes.', 0, 1);
						$this->commonM->addMyNotif($this->user->empID, 'You generated a CIS for '.$wonka->name.'. Update requests:<br/>'.$this->textM->constantText('txt_'.$wonka->fieldname).' - '.$wfval.'<br/>Print the CIS and let '.$this->user->fname.'sign and submit it to HR so they can proceed with the changes.', 5);
					}
				}else{
					$this->commonM->addMyNotif($this->user->empID, 'Generated CIS for '.$data['row']->name.'. Click <a href="'.$this->config->base_url().'cispdf/'.$insid.'/" class="iframe">here</a> to view file.', 5);
				}
				header('Location:'.$this->config->base_url().'cispdf/'.$insid.'/');	
				echo '<script> parent.$.fn.colorbox.close(); </script>';
				exit;
			}
			
			//check if you are allowed to issue nte
			if($this->user->access=='' && $this->commonM->checkStaffUnderMe($data['row']->username)==false){
				$data['access'] = false;
			}else{
				if(count($data['row'])>0){
					$data['departments'] = $this->dbmodel->getQueryResults('newPositions', 'posID, title, org, dept, grp, subgrp, active', '1', '', 'title');
					$data['supervisorsArr'] = $this->dbmodel->getQueryResults('staffs', 'empID, CONCAT(fname," ",lname) AS name', 'levelID_fk>0', '', 'fname');
				}
				
				if($this->uri->segment(3)!=''){
					$data['wonka'] = $this->dbmodel->getSingleInfo('staffUpdated', 'empID_fk, fieldname, fieldvalue, CONCAT(fname," ",lname) AS name', 'updateID="'.$this->uri->segment(3).'"', 'LEFT JOIN staffs ON empID=empID_fk');
				}
			}			
		}
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function updatecis(){
		$data['content'] = 'generatecis';
		$data['updated'] = true;
		$data['holidaySched_array']	= $this->textM->constantArr('staffHolidaySched');
		if($this->user!=false){
			$cisID = $this->uri->segment(2);
			$data['row'] = $this->dbmodel->getSingleInfo('staffCIS','staffCIS.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs ss WHERE ss.empID=staffCIS.preparedby) AS prepName', 'cisID="'.$cisID.'"', 'LEFT JOIN staffs ON empID=empID_fk');
			
			if(!empty($_POST)){
				if($_POST['submitType']=='approve'){
					$upArr['reason'] = $_POST['reason'];
					$upArr['effectivedate'] = date('Y-m-d',strtotime($_POST['effectivedate']));
					
					if($_POST['effectivedate']<=date('Y-m-d')){
						$upArr['status'] = 3;
						$chtext = '';
						
						$changes = json_decode($data['row']->dbchanges);
						
						

						if(isset($changes->title)) unset($changes->title);
											
						if(isset($changes->position)){
							$changes->levelID_fk = $this->dbmodel->getSingleField('newPositions', 'orgLevel_fk', 'posID="'.$changes->position.'"');	
							
							$nposdata = $this->dbmodel->getSingleInfo('newPositions', 'title, dt', 'posID="'.$changes->position.'"');
							$this->dbmodel->ptdbQuery('UPDATE eData SET title="'.$nposdata->title.'", dt="'.$nposdata->dt.'" WHERE u="'.$data['row']->username.'"');
						}
						
						if(isset($changes->sal))
							$changes->sal = $this->textM->encryptText($changes->sal);
												
						$this->dbmodel->updateQuery('staffs', array('empID'=>$data['row']->empID_fk), $changes);
						
						if(isset($changes->supervisor)){
							$this->commonM->addMyNotif($changes->supervisor, 'You are the new immediate supervisor of <a href="'.$this->config->base_url().'staffinfo/'.$data['row']->username.'/">'.$data['row']->name.'</a>.', 0, 1);
						}
																		
						$chQuery = json_decode($data['row']->changes);
						foreach($chQuery AS $k=>$c):
							if($k!='salary'){
								$chtext .= 'Previous '.$this->textM->constantText('txt_'.$k).': '.$c->c.'<br/>';
								$chtext .= 'New '.$this->textM->constantText('txt_'.$k).': <b>'.$c->n.'</b><br/>';	
							}
						endforeach;
						
						$this->commonM->addMyNotif($data['row']->empID_fk, 'The CIS generated by '.$data['row']->prepName.' has been reflected to your employee details. Click <a href="'.$this->config->base_url().'cispdf/'.$cisID.'/" class="iframe">here</a> to check details.<br/>'.$chtext, 0, 1);
						$this->commonM->addMyNotif($data['row']->preparedby, 'The CIS you generated for <a href="'.$this->config->base_url().'staffinfo/'.$data['row']->username.'/">'.$data['row']->name.'</a> has been reflected to his/her employee details. Click <a href="'.$this->config->base_url().'cispdf/'.$cisID.'/" class="iframe">here</a> to check details.<br/>'.$chtext, 0, 1);						
					}else{						
						$upArr['status'] = 1;
						
						$this->commonM->addMyNotif($data['row']->preparedby, 'The CIS you requested for '.$data['row']->name.' has been approved by '.$this->user->name.' but changes will reflect on '.date('F d, Y',strtotime($_POST['effectivedate'])).'. Click <a href="'.$this->config->base_url().'updatecis/'.$cisID.'/" class="iframe">here</a> for details.', 0, 1);												
					}
					$hraction = 'Approved the CIS generated by '.$data['row']->prepName.' for '.$data['row']->name.'.';
				}else if($_POST['submitType']=='disapprove'){					
					$upArr['status'] = 2;	
					$upArr['reason'] = $_POST['reason'];
					
					$hraction = 'Disapproved the CIS generated by '.$data['row']->prepName.' for '.$data['row']->name.'.';
				} else if($_POST['submitType'] == 'cancel'){
					$upArr['status'] = 4;	
					$upArr['reason'] = $_POST['reason'];
					
					$hraction = $data['row']->prepName.' cancelled the generated CIS for '.$data['row']->name.'.';
				}else if($_POST['submitType']=='signedCIS' && !empty($_FILES['signedFile'])){					
					if($_FILES['signedFile']['name']!=''){
						$dir = UPLOADS.'CIS/';	
						$n = array_reverse(explode('.', $_FILES['signedFile']['name']));
						move_uploaded_file($_FILES['signedFile']['tmp_name'], $dir.'CIS_'.$cisID.'.'.$n[0]);	
						$upArr['signedDoc'] = 'CIS_'.$cisID.'.'.$n[0];
						$hraction = 'Uploaded signed CIS document.';
					}
				}
				
				if(isset($hraction)){
					$this->commonM->addMyNotif($this->user->empID, $hraction.' Click <a href="'.$this->config->base_url().'updatecis/'.$cisID.'/" class="iframe">here</a> to see details.', 5);
				}
				
				if(count($upArr)>0){
					$upArr['updatedby'] = $this->user->name.'|'.date('Y-m-d H:i:s');
					$this->dbmodel->updateQuery('staffCIS', array('cisID'=>$cisID), $upArr);
					header('Location:'.$_SERVER['REQUEST_URI']);
					exit;
				}
			}
						
		}
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function cispdf(){
		$id = $this->uri->segment(2);
		
		$row = $this->dbmodel->getSingleInfo('staffCIS', 'staffCIS.*, CONCAT(fname," ",lname) AS name, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=preparedby AND preparedby!=0) AS prepby, supervisor', 'cisID="'.$id.'"', 'LEFT JOIN staffs ON empID=empID_fk');
		
		//$row->changes = stripslashes($row->changes);
		//$row->dbchanges = stripslashes($row->dbchanges);
		//$this->textM->aaa($row);
		if(count($row)>0){
			$isupname = '';
			$nsupname = '';
			$isup = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, supervisor', 'empID="'.$row->supervisor.'"');
			if(count($isup)>0){
				$isupname = $isup->name;
				$nsup = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name', 'empID="'.$isup->supervisor.'"');
				if(isset($nsup->name)) $nsupname = $nsup->name;				
			}
			
			if($this->uri->segment(3)=='') $tt = 'I';
			else $tt = $this->uri->segment(3);
					
			$this->staffM->createCISpdf($row, $isupname, $nsupname, $tt);
		}else{
			echo 'Sorry, no record for this CIS.';
		}
	}

	public function performanceeval(){
		$this->load->model('evaluationsmodel');
		$data['content'] = 'performanceEvaluation';
		$data['uType'] = $this->uri->segment(2);
		$data['questions'] = $this->evaluationsmodel->getStaffEvaluation($this->uri->segment(3));
		$data['empId'] = $this->uri->segment(3);
		$data['evaluator'] = $this->uri->segment(4);
		$data['notifyId'] = $this->uri->segment(5);
		$data['status'] = $this->databasemodel->getSingleField('staffEvaluationNotif', 'status', 'notifyId = '.$data['notifyId']);
		$this->load->view('includes/templatecolorbox', $data);	
	}
		
	public function staffcis(){
		$data['content'] = 'staffcis';
		
				
		if($this->user!=false){
			
			if( $this->user->levelID_fk > 0 AND $this->access->myaccess[0] == "" ){
				
				$data['supervisor'] = $this->dbmodel->getQueryResults('staffCIS', 'staffCIS.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS supName, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=preparedby) AS prepby, updatedby', 'status=0 AND preparedby IN ('.$this->user->empID.')', 'LEFT JOIN staffs ON empID=empID_fk');
			} else if( $this->access->accessFullHR == true ){
				$data['pending'] = $this->dbmodel->getQueryResults('staffCIS', 'staffCIS.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS supName, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=preparedby) AS prepby, updatedby', 'status=0', 'LEFT JOIN staffs ON empID=empID_fk');
				$data['approved'] = $this->dbmodel->getQueryResults('staffCIS', 'staffCIS.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS supName, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=preparedby) AS prepby, updatedby', 'status=1', 'LEFT JOIN staffs ON empID=empID_fk');
				$data['done'] = $this->dbmodel->getQueryResults('staffCIS', 'staffCIS.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS supName, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=preparedby) AS prepby, updatedby', 'status=3', 'LEFT JOIN staffs ON empID=empID_fk');
				$data['disapproved'] = $this->dbmodel->getQueryResults('staffCIS', 'staffCIS.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS supName, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=preparedby) AS prepby, updatedby', 'status=2', 'LEFT JOIN staffs ON empID=empID_fk');
				$data['cancelled'] = $this->dbmodel->getQueryResults('staffCIS', 'staffCIS.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS supName, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=preparedby) AS prepby, updatedby', 'status=4', 'LEFT JOIN staffs ON empID=empID_fk');
			} else {			
				$data['access'] = false;
			}
		}
		$this->load->view('includes/template', $data);			
	}
	
	public function requestcoe(){
		$data['content'] = 'requestcoe';
		
		if($this->user!=false){
			if($this->uri->segment(2)!=''){
				if($this->access->accessFullHR==false){
					$data['access'] = false;
				}else{				
					$coeID = $this->uri->segment(2);
					$data['toupdate'] = true;
					$data['row'] = $this->dbmodel->getSingleInfo('staffCOE', 'staffCOE.*, CONCAT(fname," ",lname) AS name, newPositions.title, startDate, endDate, sal, allowance, fname, username, empStatus, notesforHR', 'coeID="'.$coeID.'"', 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
					if($data['row']->dateissued!='0000-00-00'){
						$this->generatecoe($coeID);
					}
					
					if(!empty($_POST)){
						if($_POST['submitType']=='generate'){							
							if(!empty($_POST['editpurpose'])){
								$this->dbmodel->updateConcat('staffCOE', 'coeID="'.$coeID.'"', 'notesforHR', '<br/>Purpose edited to: '.$_POST['editpurpose']);
							}						
							$this->dbmodel->updateQuery('staffCOE', array('coeID'=>$coeID), array('issuedby'=>$this->user->empID, 'dateissued'=>date('Y-m-d'), 'status'=>'1', 'purposeEdited'=>$_POST['editpurpose']));
							$this->generatecoe($coeID);
							$this->commonM->addMyNotif($data['row']->empID_fk, $this->user->name.' generated the COE you requested. Click <a href="'.$this->config->base_url().'requestcoe/'.$coeID.'/" class="iframe">here</a> to view the file.', 0, 1);
							$this->commonM->addMyNotif($this->user->empID, 'You generated COE for '.$data['row']->name.'. Click <a href="'.$this->config->base_url().'requestcoe/'.$coeID.'/" class="iframe">here</a> to view the file.', 5);
						}else if($_POST['submitType']=='cancelRequest'){
							$this->dbmodel->updateQuery('staffCOE', array('coeID'=>$_POST['coeID']), array('status'=>2));
							$this->commonM->addMyNotif($data['row']->empID_fk, 'Cancelled your COE request last '.date('F d, Y', strtotime($data['row']->daterequested)).' for '.$data['row']->purpose.'.', 0, 1);
							$this->commonM->addMyNotif($this->user->empID, 'Cancelled COE request of '.$data['row']->name, 5);
							exit;
						}
					}				
				}				
			}else{
				$data['toupdate'] = false;
				$data['row'] = $this->user;
				$data['prevRequests'] = $this->dbmodel->getQueryResults('staffCOE', 'staffCOE.*', 'empID_fk="'.$this->user->empID.'" AND status=1');
				if(isset($_POST) && !empty($_POST) && $_POST['submitType']=='request'){	
					$id = $this->dbmodel->insertQuery('staffCOE', array('empID_fk'=>$this->user->empID, 'purpose'=>$_POST['purpose'],'notesforHR'=>$_POST['notesforHR'], 'daterequested'=>date('Y-m-d H:i:s')));
					$this->commonM->addMyNotif($this->user->empID, 'You requested for a Certificate of Employment.', 5);
					
					$body = '<p>Hi,</p>
						<p>This is an automatic notification that employee '.$this->user->name.' has requested for a COE. Please click <a href="'.$this->config->base_url().'requestcoe/'.$id.'/">here</a> to generate the COE.</p>
						<p>For HR, pleave validate information in the COE. In the event of any information discrepancy, please update PT.</p>
						<p style="color:red;">Refrain from manually editing the COE.</p>
						<p>Once validated, printed, and signed, click "Send email" button on Manage COE page to send and email to employee to claim their employment certificate in HR Office.</p>
						<p>Authorized signatories are only HR Personnel and Director of Operations.</p>
						<p><br/></p>
						<p>Thanks!</p>';
						
					$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', 'Request for COE', $body, 'CareerPH Auto-Email');
					$data['inserted'] = true;
				}
			}
		}
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function generatecoe($id){	
		$row = $this->dbmodel->getSingleInfo('staffCOE', 'coeID, dateissued, purpose, purposeEdited, empID, CONCAT(fname, " ",lname) AS name, newPositions.title, startDate, endDate, sal AS salary, allowance, empStatus', 'coeID="'.$id.'"', 'LEFT JOIN staffs ON empID_fk=empID LEFT JOIN newPositions ON posID=position');
		
		$this->staffM->genCOEpdf($row);
		
	}
	
	public function managecoe(){
		$data['content'] = 'managecoe';
		
		if($this->user!=false){
			$data['pending'] = $this->dbmodel->getQueryResults('staffCOE', 'staffCOE.*, CONCAT(fname," ",lname) AS name, username', 'status=0', 'LEFT JOIN staffs ON empID=empID_fk');
			$data['printed'] = $this->dbmodel->getQueryResults('staffCOE', 'staffCOE.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=staffCOE.issuedby) AS genName', 'status=1', 'LEFT JOIN staffs ON empID=empID_fk');
		}
		$this->load->view('includes/template', $data);	
	}
	
	public function sendEmail(){
		$data['content'] = 'sendemail';
		
		if($this->user!=false){
			$segment2 = $this->uri->segment(2);
			$segment3 = $this->uri->segment(3);
							
			$data['sent'] = false;
			if(!empty($_POST) && isset($_POST['submitType'])){		
				$fromName = $_POST['fromName'];
				$from = $_POST['from'];
				$to = ltrim($_POST['to'],',');
				$subject = $_POST['subject'];
				$message = $_POST['message'];	
			
								
				if($segment2=='addinfoleavesubmitted'){		
					$addInfo = '<b>'.strtoupper($this->user->username).'</b><br/>
									Sent: '.date('M d, Y h:i A').'<br/>
									To: '.$to.'<br/>
									Subject: '.$subject.'<br/><br/>
									'.$message.'<br/><br/>
									============================================================================================<br/>
								';
					$this->dbmodel->updateConcat('staffLeaves', 'leaveID="'.$segment3.'"', 'addInfo', $addInfo);	
				}			
				
				$message .= '<br/><br/>---- <i style="font-size:11px;">Message sent from CareerPH</i> ----';				
				$this->emailM->sendEmail($from, $to, $subject, $message, $fromName);
				
				$ntexts = 'From: '.$from.'<br/>
							To: '.$to.'<br/>
							Subject: '.$subject.'<br/>
							'.$message;
				$this->commonM->addMyNotif($this->user->empID, $ntexts, 5);
				
				//search if staff exist then add notification
				$toemails = explode(',', $to);
				$cntto = count($toemails);
				for($e=0; $e<$cntto; $e++){
					$staffID = $this->dbmodel->getSingleField('staffs', 'empID', 'email="'.trim($toemails[$e]).'" OR pemail="'.trim($toemails[$e]).'"');
					if(!empty($staffID)) 
						$this->commonM->addMyNotif($staffID, $ntexts, 0, 1, $this->user->empID);
				}

				//add record on timelog request update
				if($segment2=='timelogrequest'){
					$ins['empID_fk'] = $segment3;
					$ins['logDate'] = $this->uri->segment(4);
					$ins['message'] = addslashes('<div class="divMessageReq">'.$ntexts.'</div>');
					$ins['status'] = 0;
					$ins['updatedBy'] = $this->user->username; 
					$ins['dateRequested'] = date('Y-m-d H:i:s');
					$ins['dateUpdated'] = $ins['dateRequested'];					
					$this->dbmodel->insertQuery('tcTimelogUpdates', $ins);	
				}
								
				$data['sent'] = true;
			}else{			
				$data['subject'] = '';
				$data['to'] = '';
				$data['message'] = '';
				
				if($segment2=='addinfoleavesubmitted'){
					$lEmpID = $this->dbmodel->getSingleField('staffLeaves', 'empID_fk', 'leaveID="'.$segment3.'"');
					$supID = $this->dbmodel->getSingleField('staffs', 'supervisor', 'empID="'.$lEmpID.'"');
					$supEmail = $this->dbmodel->getSingleField('staffs', 'email', 'empID="'.$supID.'"');
					$data['subject'] = 'Additional Information for Leave Application #'.$segment3.' Submitted';
					$data['to'] = $supEmail.',hr.cebu@tatepublishing.net';
					$data['message'] = '';				
				}else if($segment2=='followupcoaching'){
					$data['to'] = 'hr.cebu@tatepublishing.net';
					$data['subject'] = 'Follow up on Signed Coaching Form of Coach ID #'.$segment3;	
					$data['message'] = 'Hi HR, please upload signed coaching form so I can proceed evaluating employee\'s performance.';
				}else if($segment2=='payslipinquiry'){
					$data['to'] = 'accounting.cebu@tatepublishing.net';
					$data['subject'] = 'Inquiry for Payslip ID #'.$segment3;	
					$data['message'] = 'Hi Accounting,<br/><br/><i>Please refer to payslip link <a href="'.$this->config->base_url().'timecard/'.$this->uri->segment(4).'/payslipdetail/'.$segment3.'/">'.$this->config->base_url().'timecard/'.$this->uri->segment(4).'/payslipdetail/'.$segment3.'/</a></i><br/><br/>';
				}else if($segment2=='timelogrequest' && !empty($_POST)){
					$data['row'] = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, fname, lname, email', 'empID="'.$segment3.'"');
					$data['to'] = $data['row']->email;					
					$data['subject'] = 'Update on your timelog request for '.$this->uri->segment(4);
					
					$data['message'] = '<p>Hi '.$data['row']->fname.',</p>
							<p>This is in response to your timelog request.<p>
							<p style="color:red">[WRITE MESSAGE HERE...]</p>
							<p>&nbsp;</p>
							<p>Thanks!</p>
							<hr/>
							<p><b>Your request message:</b><br/><i>'.$_POST['message'].'</i></p><p>Click <a href="'.$this->config->base_url().'timecard/'.$segment3.'/viewlogdetails/?d='.$this->uri->segment(4).'">here</a> to visit timelog for this day.</p>';
				}else if($segment2!=''){
					$data['row'] = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, fname, lname, email', 'empID="'.$segment2.'"');
					$data['to'] = $data['row']->email;
					if($this->uri->segment(3)=='acknowledgecoaching'){
						$data['subject'] = 'Please acknowledge coaching evaluation';	
						$data['message'] = '<p>Hi '.$data['row']->fname.',</p>
							<p>Please acknowledge and input coaching score discussed to you by your supervisor. Click <a href="'.$this->config->base_url().'coachingEvaluation/'.$this->uri->segment(4).'/">here</a> to acknowledge.<p>
							<p>&nbsp;</p>
							<p>Thanks!</p>';
					}
				}
			}
			
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function testpage(){
		$data['content'] = 'test';	
		if(!empty($_POST)){
			$quer = $this->dbmodel->ptdbQuery('UPDATE eData SET title="'.$_POST['title'].'" WHERE eKey="'.$_POST['eKey'].'"');
		}
		
		if($this->uri->segment(2)=='show'){
			$username = '';
			$data['query'] = $this->dbmodel->getQueryResults('staffs', 'empID, username, title', '1', 'LEFT JOIN newPositions ON position=posID');
			foreach($data['query'] AS $q){
				$username .= '"'.$q->username.'",';
			}
			$quer = $this->dbmodel->ptdbQuery('SELECT eKey, u, title FROM eData WHERE u IN ('.rtrim($username,',').')');
			$data['query1'] = $quer->result();
		}
		
		
		$this->load->view('includes/templatenone', $data);	
	}
	
	public function supportingdocs(){
		$data['content'] = 'supportingdocs';
		
		if($this->user!=false && $this->uri->segment(2)!=''){
			$data['docs'] = $this->dbmodel->getQueryResults('staffUploads', 'staffUploads.*, (SELECT CONCAT(fname," ",lname) FROM staffs WHERE uploadedBy=empID) AS uploader, username', 'empID_fk="'.$this->uri->segment(2).'" AND isDeleted=0','LEFT JOIN staffs ON empID=empID_fk', 'dateUploaded DESC');
			
			if($this->uri->segment(3)=='paternityleave'){
				if(count($data['docs'])>0){
					$prom = '';
					foreach($data['docs'] AS $d):
						$prom .= '<input type="checkbox" name="sDoc[]" value="'.$d->upID.'"/> '.$d->fileName.'<br/>';
					endforeach;
					echo $prom;
				}else{
					echo 'No supporting documents uploaded.';
				}
				exit;
			}
		}
		$this->load->view('includes/templatecolorbox', $data);
		
	}
		
	public function uploadFiles(){
		$data['content'] = 'uploadFiles';
		
		if(isset($_FILES) && !empty($_FILES['fileToUpload'])){
			$n = array_reverse(explode('.', $_FILES['fileToUpload']['name']));			
			move_uploaded_file($_FILES['fileToUpload']['tmp_name'], UPLOADS.'others/'.date('YmdHis').'.'.$n[0]);
		}
		
		if(!empty($_POST)){
			if($_POST['submitType']=='delete'){
				unlink($_POST['fname']);
				$this->commonM->addMyNotif($this->user->empID, 'You deleted this file '.$_POST['fname'], 5);
			}
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
		
	public function generatecode(){
		$data['content'] = 'generatecode';
		
		if($this->user!=false){
			if(!empty($_POST) AND !empty($_POST['forWhom']) AND isset($_POST['forWhom']) ){
				if($_POST['submitType']=='gencode'){
					$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
					$res = "";
					for ($i = 0; $i < 10; $i++) {
						$res .= $chars[mt_rand(0, strlen($chars)-1)];
					}
					$insArr['code'] = $res;
					$insArr['generatedBy'] = $this->user->empID;
					$insArr['dategenerated'] = date('Y-m-d H:i:s');
					$insArr['forWhom'] = $_POST['forWhom'];
					$insArr['why'] = addslashes($_POST['why']);
					$this->dbmodel->insertQuery('staffCodes', $insArr);
					
					//send email to staff
					$to = $this->dbmodel->getSingleField('staffs', 'email', 'empID="'.$insArr['forWhom'].'"');
					if(!empty($to)){
						$ebody = '<p>Hi,</p>
								<p>'.$this->user->fname.' generated code for you. See details below:</p>
								<p>Code: <b>'.$res.'</b></p>
								<p>Purpose: '.$_POST['why'].'</p>
								<p><i>Please take note that generated code is valid for 24 hours and can only be used once.</i></p>
								<p><br/></p>
								<p>Thanks!</p>
								<p>CareerPH</p>
							';
						$this->emailM->sendEmail('careers.cebu@tatepublishing.net', $to, 'Code has been generated for you.', $ebody, 'CareerPH', $this->user->email);
						//add notification
						$this->commonM->addMyNotif($insArr['forWhom'], 'Generated code for you. See details below:<br/>Code: <b>'.$res.'</b><br/>Purpose: '.addslashes($_POST['why']), 0, 1);
					}
					
					echo $res;
					exit;
				}
			}
			
			$condition = '';
			if($this->access->accessFullHR==false){
				$ids = '"",'; //empty value for staffs with no under yet
				$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);				
				foreach($myStaff AS $m):
					$ids .= $m->empID.',';
				endforeach;
				if($ids!='')
					$condition .= ' AND empID IN ('.rtrim($ids,',').')';
			}
			$data['staffs'] = $this->dbmodel->getQueryResults('staffs', 'empID, lname, fname', 'active=1'.$condition, '', 'lname');
			$data['codes'] = $this->dbmodel->getQueryResults('staffCodes', 'staffCodes.*, (SELECT CONCAT(fname," ",lname) FROM staffs WHERE usedBy!=0 AND empID=usedBy) AS useByName, (SELECT CONCAT(fname," ",lname) FROM staffs WHERE forWhom!=0 AND empID=forWhom) AS forWhomName', 'generatedBy="'.$this->user->empID.'"', '', 'dategenerated DESC');
			
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function getStaffEmails(){	
		$query = $this->dbmodel->getQueryResults('staffs', 'empID, username, CONCAT(fname," ",lname) AS name, email', 'active=1 AND empID!="'.$this->user->empID.'"');
		$disp = '<button id="cboxClose" type="button" style="top:27px; right:8px;" onClick="$(\'.staffEmails\').addClass(\'hidden\'); $(\'#filter\').val(\'\');">close</button>';
		$disp .= '<table class="tableInfo" style="text-align:left;">';
		foreach($query AS $q):
			$disp .= '<tr class="cpointer"><td onClick="sendEmailOpen('.$q->empID.')">'.$q->name.'</td></tr>';
		endforeach;
		$disp .= '</table>';
		echo $disp;		
	}
	
	public function getAllStaffsForSearch(){
		$query = $this->dbmodel->getQueryResults('staffs', 'empID, username, CONCAT(fname," ",lname) AS name', 'office!="US-OKC" AND empID!="'.$this->user->empID.'"');
		$disp = '<button id="cboxClose" type="button" style="top:27px; right:8px;" onClick="$(\'.staffSearch\').addClass(\'hidden\'); $(\'#filter2\').val(\'\');">close</button>';
		$disp .= '<table class="tableInfo" style="text-align:left;">';
		foreach($query AS $q):
			$disp .= '<tr class="cpointer"><td onClick="visitStaffPage(\''.$q->username.'\')">'.$q->name.'</td></tr>';
		endforeach;
		$disp .= '</table>';
		echo $disp;	
	}
				
	public function notes(){
		$data['content'] = 'notes';
		if($this->user!=false)
			$data['myID'] = $this->user->empID;
		else
			$data['myID'] = 0;
		
		$data['empID'] = $this->uri->segment(2);
		$empID = $data['empID'];
		$username = $this->dbmodel->getSingleField('staffs', 'username', 'empID="'.$empID.'"');
				
		$data['myNotes'] = $this->staffM->mergeMyNotes($empID, $username);		
		
		$this->load->view('includes/templatenone', $data);
	}
	
	public function others(){
		$data['content'] = 'others';
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function addnewposition(){
		$data['content'] = 'addnewposition';
		$data['page'] = 'add';
		
		$pID = $this->uri->segment(2);
		if($pID!=''){
			$data['page'] = 'edit';
			$data['row'] = $this->dbmodel->getSingleInfo('newPositions', '*', 'posID="'.$pID.'"');
			$data['depts'] = $this->dbmodel->getSQLQueryArrayResults('SELECT DISTINCT dept FROM newPositions WHERE org="'.$data['row']->org.'"');
			$data['grps'] = $this->dbmodel->getSQLQueryArrayResults('SELECT DISTINCT grp FROM newPositions WHERE dept="'.$data['row']->dept.'"');
			$data['subgrps'] = $this->dbmodel->getSQLQueryArrayResults('SELECT DISTINCT subgrp FROM newPositions WHERE grp="'.$data['row']->grp.'"');
		}
		
		if(!empty($_POST)){			
			if(isset($_POST['rtest'])){ //for required test
				$_POST['requiredTest'] = '';
				foreach($_POST['rtest'] AS $p):
					$_POST['requiredTest'] .= $p.',';
				endforeach;
				$_POST['requiredTest'] = rtrim($_POST['requiredTest'], ',');
				unset($_POST['rtest']);
			}
			
			if(isset($_POST['rskill'])){ //for required skills
				$_POST['requiredSkills'] = '';
				foreach($_POST['rskill'] AS $p):
					$_POST['requiredSkills'] .= $p.'|';
				endforeach;
				$_POST['requiredSkills'] = rtrim($_POST['requiredSkills'], '|');
				unset($_POST['rskill']);
			}
		
			if($_POST['submitType']=='grpdepts'){
				$query = $this->dbmodel->getSQLQueryArrayResults('SELECT DISTINCT '.$_POST['newtype'].' FROM newPositions WHERE '.$_POST['oldtype'].'="'.$_POST['tval'].'"');
				echo '<option></option>';
				foreach($query AS $q):
					echo '<option value="'.$q->$_POST['newtype'].'">'.$q->$_POST['newtype'].'</option>';
				endforeach;
				exit;
			}else if($_POST['submitType']=='addposition'){
				unset($_POST['submitType']);
				$_POST['desc'] = addslashes($_POST['desc']);
				$_POST['user'] = $this->user->username;
				$_POST['date_created'] = date('Y-m-d H:i:s');
				$this->dbmodel->insertQuery('newPositions', $_POST);
				$this->commonM->addMyNotif($this->user->empID, 'Added new position "<b>'.$_POST['title'].'</b>" for '.$_POST['org'].'> '.$_POST['dept'].'> '.$_POST['grp'].'> '.$_POST['subgrp'].'.', 5);
				$data['added'] = $_POST['title'];
			}else if($_POST['submitType']=='editposition'){
				unset($_POST['submitType']);
				$_POST['desc'] = addslashes($_POST['desc']);
				$this->dbmodel->updateQuery('newPositions', array('posID'=>$pID), $_POST);				
				$this->dbmodel->updateConcat('newPositions', 'posID="'.$pID.'"', 'editData', $this->user->username.'|'.date('Y-m-d H:i:s').'-^_^-');
				
				//add notification
				$this->commonM->addMyNotif($this->user->empID, 'Edited position "<b>'.$_POST['title'].'</b>" for '.$_POST['org'].'> '.$_POST['dept'].'> '.$_POST['grp'].'> '.$_POST['subgrp'].'.', 5);
				$data['edited'] = $_POST['title'];
			}
		}
		
	
		$data['org'] = $this->dbmodel->getSQLQueryArrayResults('SELECT DISTINCT org FROM newPositions');
		$data['orgLevel'] = $this->dbmodel->getSQLQueryArrayResults('SELECT * FROM orgLevel');
		$data['requiredTestArr'] = $this->textM->constantArr('requiredTest');		
		$data['requiredSkillsArr'] = $this->dbmodel->getQueryResults('applicantSkills', '*');
		
		//PT OLD DEPARTMENTS
		$queryDept = $this->dbmodel->ptdbQuery('SELECT * FROM eDept');
		$query = $queryDept->result();
		$data['PTDeptArr'] = array();
		foreach($query AS $q){
			$data['PTDeptArr'][$q->eDeptKey] = $q->eDeptName;
		}
				
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function allpositions(){
		$data['content'] = 'allpositions';
		$id = $this->uri->segment(2);
		
		$queryDept = $this->dbmodel->ptdbQuery('SELECT * FROM eDept');
		$query = $queryDept->result();
		$data['PTDeptArr'] = array();
		foreach($query AS $q){
			$data['PTDeptArr'][$q->eDeptKey] = $q->eDeptName;
		}
		
		if($id!=''){
			$data['page'] = 'details';
			$data['row'] = $this->dbmodel->getSingleInfo('newPositions', '*, levelName', 'posID="'.$id.'"', 'LEFT JOIN orgLevel ON levelID=orgLevel_fk');
			$data['txt'] = $this->textM->constantArr('requiredTest');
			$allskills = $this->dbmodel->getQueryResults('applicantSkills', '*');
			$sArr = array();
			foreach($allskills AS $a):
				$sArr[$a->skillID] = $a->skillName;
			endforeach;
			$data['skills'] = $sArr;
			$this->load->view('includes/templatecolorbox', $data);
		}else{
			$data['page'] = 'all';
			$data['positions'] = $this->dbmodel->getQueryResults('newPositions', 'posID, title, `desc`, active, orgLevel_fk, levelName, org, dept, dt, grp, subgrp', '1', 'LEFT JOIN orgLevel ON orgLevel_fk=levelID', 'org, dept, grp, subgrp, title');
			$this->load->view('includes/template', $data);
		}
	}
	
	public function generatecoaching(){
		$data['content'] = 'generatecoaching';
		
		if($this->user!=false){
			$id = $this->uri->segment(2);
			
			if(!empty($_POST)){
				if($_POST['submitType']=='generateC'){	
					unset($_POST['submitType']);
					$coached = $_POST;
										
					$insArr['generatedBy'] = $this->user->empID;
					$insArr['dateGenerated'] = date('Y-m-d H:i:s');
					
					$insArr['empID_fk'] = $id;
					$insArr['coachedBy'] = $coached['whocoached'];
					$insArr['coachedDate'] = date('Y-m-d', strtotime($coached['coachedDate']));
					$insArr['coachedPeriod'] = $coached['coachedPeriod'];
					$insArr['coachedEval'] = date('Y-m-d',strtotime($coached['coachedEval']));
					$insArr['coachedImprovement'] = $coached['areaofimprovement'];
					
					$insArr['coachedAspectExpected'] = '';
					for($ae=1; $ae<=4; $ae++){
						if(isset($coached['aspectExpected'.$ae]))
							$insArr['coachedAspectExpected'] .= $coached['aspectExpected'.$ae].'--^_^--';
					}
					$insArr['coachedAspectExpected'] = addslashes(rtrim($insArr['coachedAspectExpected'], '--^_^--'));
					
					$insArr['coachedSupport'] = '';
					for($s=1; $s<=4; $s++){
						if(isset($coached['support'.$s]))
							$insArr['coachedSupport'] .= $coached['support'.$s].'--^_^--';
					}
					$insArr['coachedSupport'] = addslashes(rtrim($insArr['coachedSupport'], '--^_^--'));
										
					$insID = $this->dbmodel->insertQuery('staffCoaching', $insArr);
					
					$empInfo = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, supervisor', 'empID="'.$id.'"');
					if($empInfo->supervisor!=0){
						$sup2ndlevel = $this->dbmodel->getSingleField('staffs', 'supervisor', 'empID="'.$empInfo->supervisor.'"');
					}
										
					
					if($this->user->empID!=$empInfo->supervisor){
						$this->commonM->addMyNotif($this->user->empID, 'Generated coaching form for '.$empInfo->name.'. Click <a href="'.$this->config->base_url().'coachingform/acknowledgment/'.$insID.'/" class="iframe">here</a> to view details.', 5, 0); //for coaching generator
					}
					
					$ntext = 'Coaching form has been generated to '.$empInfo->name.'. Click <a href="'.$this->config->base_url().'coachingform/acknowledgment/'.$insID.'/" class="iframe">here</a> to view details.';
					$this->commonM->addMyNotif($id, $ntext, 2, 1); //for employee
					if($empInfo->supervisor!=0) $this->commonM->addMyNotif($empInfo->supervisor, $ntext, 0, 1); //for immediate supervisor
					if(isset($sup2ndlevel) && $sup2ndlevel!=0) $this->commonM->addMyNotif($sup2ndlevel, $ntext, 0, 1); //for 2nd level supervisor
					
					//for reviewer
					if($this->user->empID!=$insArr['coachedBy'] && $insArr['coachedBy']!=$empInfo->supervisor){
						$this->commonM->addMyNotif($insArr['coachedBy'], 'You are the reviewer of the coaching form generated by '.$this->user->fname .' for '.$empInfo->name.'. Click <a href="'.$this->config->base_url().'coachingform/acknowledgment/'.$insID.'/" class="iframe">here</a> to view details.', 0, 1);
					}
					
					//for HR
					$hrnote = 'Coaching form has been generated to '.$empInfo->name.'. Click <a href="'.$this->config->base_url().'coachingform/hroptions/'.$insID.'/" class="iframe">here</a> to view details and print the coaching form.';
					$hrStaffs = $this->staffM->getHRStaffID();
					for($hr=0; $hr<count($hrStaffs); $hr++){
						if($hrStaffs[$hr] != $this->user->empID)
							$this->commonM->addMyNotif($hrStaffs[$hr], $hrnote, 0, 1, 0);
					}
					
					echo $insID;
					exit;
				}
			}
			
			$data['row'] = $this->dbmodel->getSingleInfo('staffs', 'empID, username, fname, lname, CONCAT(fname," ",lname) AS name, supervisor, coach', 'empID="'.$id.'"');
			
			$data['supervisors'] = $this->dbmodel->getQueryResults('staffs', 'empID, CONCAT(fname," ",lname) AS name, title', 'staffs.active=1', 'LEFT JOIN newPositions ON posID=position', 'fname ASC');
			$data['areaofimprovementArr'] = $this->textM->constantArr('areaofimprovement');
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function coachingform(){
		$type = $this->uri->segment(2);
		$id = $this->uri->segment(3);
		
		if($type=='hroptions' && $this->access->accessFullHR==false)
			$data['access'] = false;
		
		$row = $this->dbmodel->getSingleInfo('staffCoaching', 'staffCoaching.*, username, CONCAT(fname," ",lname) AS name, title, dept, supervisor, position, (SELECT CONCAT(fname," ",lname) AS rname FROM staffs WHERE empID=coachedBy) AS reviewer, startDate', 'coachID="'.$id.'"', 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
		
		if(!empty($_POST)){
			if($_POST['submitType']=='hroption' || $_POST['submitType']=='hroptionevaluation'){
				if($_POST['submitType']=='hroption')
					$optionVal = 'coaching';
				else
					$optionVal = 'evaluation';
								
				$this->dbmodel->updateQuery('staffCoaching', array('coachID'=>$id), array('HRoptionStatus'=>$_POST['status'], 'HRstatusData'=>$row->HRstatusData.$_POST['status'].'|'.$this->user->username.'|'.date('Y-m-d H:i:s').'-^_^-'));
				
				$this->commonM->addMyNotif($row->generatedBy, 'Please claim from HR printed copy of the '.$optionVal.' form you generated/conducted for '.$row->name.' and let employee and supervisors signed it. After signing, return it to HR for filing.');
				
				
				$genEmail = $this->dbmodel->getSingleField('staffs', 'email', 'empID="'.$row->generatedBy.'"');
				$bEmail = '<p>Hi,</p>';
				$bEmail .= '<p>Please claim from HR printed copy of the '.$optionVal.' form you generated/conducted for '.$row->name.'.</p>';
				$bEmail .= '<p>Thanks!</p>';
				$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $genEmail, ucfirst($optionVal).' Form Printed for '.$row->name, $bEmail, 'CAREERPH');
				
				//send message to HR
				$hrEmail = 'Hello HR,<br/><br/>Please be informed that the '.$optionVal.' form of '.$row->name.' is printed. Coach has been informed to collect the prints from HR. Please use this ticket to monitor that the fully signed '.$optionVal.' form is signed and on file. Click <a href="'.$this->config->base_url().'coachingform/hroptions/'.$row->coachID.'/">here</a> to view coaching details.<br/><br/>Thanks!';
				$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', ucfirst($optionVal).' Form Printed for '.$row->name, $hrEmail, 'CAREERPH');
				
				
			}else if($_POST['submitType']=='uploadCF'){				
				if($_FILES['cffile']['name']!=''){
					$dir = UPLOADS.'coaching/';	
					$n = array_reverse(explode('.', $_FILES['cffile']['name']));
					
					if($n[0]!='pdf'){
						echo '<script>alert(\'Error: File should be a pdf.\'); window.location.href="'.$this->config->item('career_uri').'";</script>';
					}else{					
						move_uploaded_file($_FILES['cffile']['tmp_name'], $dir.'coachingform_'.$id.'.'.$n[0]);
						$this->dbmodel->updateQuery('staffCoaching', array('coachID'=>$id), array('HRoptionStatus'=>2, 'HRstatusData'=>$row->HRstatusData.'2|'.$this->user->username.'|'.date('Y-m-d H:i:s').'-^_^-'));
						echo '<script>window.location.href="'.$this->config->item('career_uri').'";</script>';
					}
				}
			}else if($_POST['submitType']=='uploadEF'){				
				if($_FILES['cffile']['name']!=''){
					$dir = UPLOADS.'coaching/';	
					$n = array_reverse(explode('.', $_FILES['cffile']['name']));
					
					if($n[0]!='pdf'){
						echo '<script>alert(\'Error: File should be a pdf.\'); window.location.href="'.$this->config->item('career_uri').'";</script>';
					}else{					
						move_uploaded_file($_FILES['cffile']['tmp_name'], $dir.'coachingevaluation_'.$id.'.'.$n[0]);
						$this->dbmodel->updateQuery('staffCoaching', array('coachID'=>$id), array('HRoptionStatus'=>4, 'HRstatusData'=>$row->HRstatusData.'4|'.$this->user->username.'|'.date('Y-m-d H:i:s').'-^_^-'));
						echo '<script>window.location.href="'.$this->config->item('career_uri').'";</script>';
					}
				}
			}else if($_POST['submitType']=='coachingCancel'){
				$this->dbmodel->updateQuery('staffCoaching', array('coachID'=>$id), array('status'=>4, 'canceldata'=>$_POST['reason'].' <br/><i>'.$this->user->username.' '.date('Y-m-d h:i a').'</i>'));				
			}
		}
				
		
		if(count($row)>0){
			$sup = $this->dbmodel->getSingleInfo('staffs', 'username AS supUsername, CONCAT(fname," ",lname) AS supName, title AS supTitle, supervisor AS supSupervisor', 'empID="'.$row->supervisor.'"', 'LEFT JOIN newPositions ON posID=position' );
			
			if(count($sup)>0){				
				$row = (object) array_merge((array) $row, (array) $sup);
				
				if(isset($sup->supSupervisor) && $sup->supSupervisor!=0){					
					$sup2nd = $this->dbmodel->getSingleInfo('staffs', 'username AS sup2ndUsername, CONCAT(fname," ",lname) AS sup2ndName, title AS sup2ndTitle', 'empID="'.$sup->supSupervisor.'"', 'LEFT JOIN newPositions ON posID=position' );
					if(count($sup2nd) > 0)
						$row = (object) array_merge((array) $row, (array) $sup2nd);
				}
			}
			
			if($type=='acknowledgment' || $type=='evaluate' || $type=='hroptions'){
				$data['type'] = $type;
				$data['content'] = 'coachingoptions';
				$data['row'] = $row;
				$this->load->view('includes/templatecolorbox', $data);
			}else{			
				$this->staffM->createCoachingPDF($row, $type);
			}
		}else{
			echo 'Sorry, no record for this coaching.';
		}		
	}
	
	public function staffcoaching(){
		$data['content'] = 'staffcoaching';
				
		if($this->user!=false){		
			if($this->user->access=='' && $this->user->level==0){
				$data['access'] = false;
			}else{			
				$condition = '';
				if($this->user->access==''){
					$ids = '"",'; //empty value for staffs with no under yet
					$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);				
					foreach($myStaff AS $m):
						$ids .= $m->empID.',';
					endforeach;
					
					//get coaching staffs
					$cstaffs = $this->dbmodel->getQueryResults('staffs', 'empID', 'coach="'.$this->user->empID.'"');				
					foreach($cstaffs AS $c){
						$ids .= $c->empID.',';
					}
					
					if($ids!='')
						$condition .= ' AND empID_fk IN ('.rtrim($ids,',').')';
				}
		
				$data['forprinting'] = $this->dbmodel->getQueryResults('staffCoaching', 'staffCoaching.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS coachedByName, supervisor', 'active=1 AND (status=0 AND (HRoptionStatus=0 || HRoptionStatus=1)) OR (status=1 AND HRoptionStatus>=2 AND HRoptionStatus<4) OR (status=3 AND HRoptionStatus<4)', 'LEFT JOIN staffs ON empID=empID_fk');
				$data['inprogress'] = $this->dbmodel->getQueryResults('staffCoaching', 'staffCoaching.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS coachedByName, supervisor', 'active=1 AND status=0 AND coachedEval>"'.date('Y-m-d').'"'.$condition, 'LEFT JOIN staffs ON empID=empID_fk');
				$data['pending'] = $this->dbmodel->getQueryResults('staffCoaching', 'staffCoaching.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS coachedByName, supervisor', 'active=1 AND (status=0 OR status=2) AND coachedEval<="'.date('Y-m-d').'"'.$condition, 'LEFT JOIN staffs ON empID=empID_fk');
				$data['done'] = $this->dbmodel->getQueryResults('staffCoaching', 'staffCoaching.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS coachedByName, supervisor', 'active=1 AND (status=1 OR status=3)'.$condition, 'LEFT JOIN staffs ON empID=empID_fk');
				$data['cancelled'] = $this->dbmodel->getQueryResults('staffCoaching', 'staffCoaching.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS coachedByName, supervisor', 'active=1 AND status=4'.$condition, 'LEFT JOIN staffs ON empID=empID_fk');
			}
		}
		
		$this->load->view('includes/template', $data);			
	}
	
	public function coachingEvaluation(){
		$data['content'] = 'coachingEvaluation';
		$id = $this->uri->segment(2);
		
		if($this->user!=false && !empty($id)){
			$data['row'] = $this->dbmodel->getSingleInfo('staffCoaching', 'staffCoaching.*, CONCAT(fname," ",lname) AS name, email, supervisor', 'coachID="'.$id.'"', 'LEFT JOIN staffs ON empID=empID_fk');
			
			if($data['row']->status==1){
				header('Location:'.$this->config->base_url().'coachingform/acknowledgment/'.$id.'/');
				exit;
			}
			
			if($this->user->empID!=$data['row']->empID_fk && $this->user->empID!=$data['row']->coachedBy && $this->access->accessFullHR==false && $this->commonM->checkStaffUnderMe($data['row']->empID_fk)===false){
				$data['access'] = false;
			}
			
			if(!empty($_POST)){
				if($_POST['submitType']=='self'){
					$this->dbmodel->updateQuery('staffCoaching', array('coachID'=>$id), array('selfRating'=>ltrim($_POST['selfRating'], '|'), 'selfRatingNotes'=>ltrim($_POST['selfRatingNotes'], '+++')));
					//send email to coach
					$coachEmail = $this->dbmodel->getSingleField('staffs', 'email', 'empID="'.$data['row']->coachedBy.'"');
					$eBody = '<p>Hi,<p>';
					$eBody .= '<p>'.$data['row']->name.'\'s coaching period started on '.date('F d, Y', strtotime($data['row']->coachedDate)).' and the performance evaluation is due on '.date('F d, Y',strtotime($data['row']->coachedEval)).'. '.$data['row']->name.' has already submitted his/her self-rating. It is now your turn to give your performance evaluation. Click <a href="'.$this->config->base_url().'coachingEvaluation/'.$data['row']->coachID.'/" class="iframe">here</a> to conduct evaluation.<p>';				
					$eBody .= '<p>Thanks!<p>';				
					$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $coachEmail, 'Coaching performance evaluation', $eBody, 'CAREERPH');
					
					//notes
					$this->commonM->addMyNotif($this->user->empID, 'Rated coaching evaluation. Click <a href="'.$this->config->base_url().'coachingEvaluation/'.$id.'/" class="iframe">here</a> for details.', 5);			
				}else if($_POST['submitType']=='coach'){
					$upArr['supervisorsRating'] = ltrim($_POST['supervisorsRating'], '|');
					$upArr['supervisorsRatingNotes'] = ltrim($_POST['supervisorsRatingNotes'], '+++');
					$upArr['status'] = $_POST['status'];
					if($_POST['status']==3){
						$upArr['finalRating'] = $_POST['finalRating'];
						$upArr['evaluatedBy'] = $this->user->empID;
						$upArr['dateEvaluated'] = date('Y-m-d');
						
						$eBody = '<p>Hi,<p>';
						$eBody .= '<p>This is an automatic email notification to inform you that your Coach/Immediate Supervisor has provided his/her ratings. Please acknowledge that your immediate supervisor has discussed the evaluation with you by logging in to careerph/staff to acknowledge.<p>';				
						$eBody .= '<p>Thanks!<p>';						
						$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $data['row']->email, 'Aknowledge performance evaluation', $eBody, 'CAREERPH');
					}
					$this->dbmodel->updateQuery('staffCoaching', array('coachID'=>$id), $upArr);
					
					//notes
					$this->commonM->addMyNotif($this->user->empID, 'Rated coaching evaluation. Click <a href="'.$this->config->base_url().'coachingEvaluation/'.$id.'/" class="iframe">here</a> for details.', 5);
				}else if($_POST['submitType']=='acknowledge'){
					$this->dbmodel->updateQuery('staffCoaching', array('coachID'=>$id), array('status'=>1));
				}

				exit;			
			}
			
			if($this->user->empID==$data['row']->empID_fk)
				$data['pageType'] = 'self';
			else	
				$data['pageType'] = 'coach';
		}else{
			$data['access'] = false;
		}
		
		$this->load->view('includes/templatecolorbox', $data);	
	}
	
	public function organizationalchart(){
		$data['content'] = 'organizationalchart';
		$all = '';
		
		$supervisors = $this->dbmodel->getQueryResults('staffs', 'DISTINCT supervisor');
		foreach($supervisors AS $s):
			$emps = $this->dbmodel->getQueryResults('staffs', 'empID, CONCAT(fname," ",lname) AS name, title, username', 'supervisor="'.$s->supervisor.'" AND staffs.active=1 AND office="PH-Cebu"', 'LEFT JOIN newPositions ON posID=position', 'title ASC');
			foreach($emps AS $e):
				$all[$s->supervisor][] = array($e->empID, $e->name, $e->title, $e->username);
			endforeach;			
		endforeach;
		
		
		$data['upper'] = $this->dbmodel->getQueryResults('staffs', 'empID, CONCAT(fname," ",lname) AS name, title, supervisor', 'levelID_fk="5" AND staffs.active=1', 'LEFT JOIN newPositions ON posID=position', 'lname ASC');
				
		$data['all'] = $all;		
		$this->load->view('includes/template', $data);	
	}
	
	public function referafriend(){
		$data['content'] = 'referafriend';
			
		if(!empty($_POST)){
			$insArr['empID_fk'] = $this->user->empID;
			$insArr['lastName'] = $_POST['lastName'];
			$insArr['firstName'] = $_POST['firstName'];
			$insArr['emails'] = implode(',',$_POST['email']);
			$insArr['contacts'] = implode(',',$_POST['number']);
			$insArr['dateReferred'] = date('Y-m-d H:i:s');
			$insArr['timestamp'] = date('Y-m-d H:i:s');
			$this->dbmodel->insertQuery('staffReferFriend', $insArr);
			
			//send email
			$ebody = '<p>Hello '.$insArr['firstName'].' '.$insArr['lastName'].',</p>
					<p>A good day to you!</p>
					<p>Your friend '.$this->user->name.' currently works at Tate Publishing and Enterprises (Philippines) and is inviting you to apply for any the positions that are currently open in the company:</p>';
				
				$oQuery = $this->dbmodel->dbQuery('SELECT title FROM jobReqData LEFT JOIN newPositions ON posID=positionID WHERE status = 0 GROUP BY positionID ORDER BY title');
			$openPos = '<ul>';
			foreach($oQuery->result() AS $op){
				$openPos .= '<li>'.$op->title.'</li>';
			}
			
			$openPos .= '</ul>';
			$ebody .= $openPos;
			
			$ebody .= '<p>To apply, please submit this form on this link: <a href="'.$this->config->item('career_url').'?refername='.$this->user->name.'&id='.$this->user->empID.'">'.str_replace(' ','%20',$this->config->item('career_url').'?refername='.$this->user->name.'&id='.$this->user->empID).'</a></p>';
			$ebody .= '<p><br/><b style="color:red;">IMPORTANT :</b><br/>
						Don\'t forget to select "Referred by a Tate Employee" in the filed <i style="color:#a52a2a;">"Where did you hear about Tate Publishing?"</i> and write '.$this->user->name.' in the field that follows.</p>';
			$ebody .= '<p>We look forward to processing your application soon!</p>
						<p>Thank you very much!</p>';			
			
			$this->emailM->sendEmail('careers.cebu@tatepublishing.net', $insArr['emails'], $this->user->name.' invites you to apply in Tate Publishing', $ebody, 'CareerPH at Tate Publishing');
			
			$this->commonM->addMyNotif($this->user->empID, 'Send email invitation to apply to '.$insArr['emails'], 5, 0);
			$data['submitted'] = '<br/><br/><h3>Email sent to your friend '.$insArr['firstName'].' '.$insArr['lastName'].'</h3>';	
			
		}
		
		$this->load->view('includes/templatecolorbox', $data);	
	}
	
	public function setcoach(){
		$data['content'] = 'setcoach';
		$id = $this->uri->segment(2);
		if($this->user!=false){
			
			if(!empty($_POST)){
				if($_POST['submitType']=='Submit'){
					$ids = str_replace('++', ',', $_POST['ids']);
					$ids = str_replace(',,', ',', $ids);
					$ids = trim($ids, ',');		
					
					//update coach and is_coach
					if(!empty($ids)){
						$this->dbmodel->dbQuery('UPDATE staffs SET coach="'.$id.'" WHERE empID IN ('.$ids.')');
						$this->dbmodel->dbQuery('UPDATE staffs SET is_coach="1" WHERE empID="'.$id.'"');
					}
					
					$this->commonM->addMyNotif($this->user->empID, 'You added "'.$id.'" AS coach to '.$ids.'. <br/><i>-CareerPH notes only</i>' , 5);
				}else if($_POST['submitType']=='remove'){
					$this->dbmodel->dbQuery('UPDATE staffs SET coach="0" WHERE empID="'.$_POST['id'].'"');
					exit;
				}				
			}
			
			$data['coach'] = $this->dbmodel->getSingleInfo('staffs', 'fname, lname, supervisor, levelID_fk', 'empID="'.$id.'"');
			$data['coachedStaffs'] = $this->dbmodel->getQueryResults('staffs', 'empID, fname, lname', 'coach="'.$id.'"');
			$data['staffs'] = $this->commonM->getAllStaffs();
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function probationmanagement(){
		$data['content'] = 'probationmanagement';
		
		if($this->access->accessFullHR== true OR $this->access->accessMedPerson || $this->user->empID == 474) 
		{
			if(!empty($_POST)){
				if($_POST['submitType']=='printedEval'){
					$this->dbmodel->updateQuery('staffEvaluation', array('evalID'=>$_POST['id']), array('hrStatus'=>2));
					$this->dbmodel->updateConcat('staffEvaluation', 'evalID="'.$_POST['id'].'"', 'hrStatusData', $this->user->username.' - Printed form - '.date('Y-m-d H:i:s').'|');
					
					$eval = $this->dbmodel->getSingleInfo('staffEvaluation', '(SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=empID_fk) AS name, (SELECT email FROM staffs WHERE empID=reviewerEmpID) AS reviewerEmail', 'evalID="'.$_POST['id'].'"');
					//send email to reviewer				
					$emBody = '<p>Hi,</p>
							<p>Please be informed that the evaluation form of '.$eval->name.' has been printed. Please collect the document from HR have it signed by '.$eval->name.' and return signed document to HR for filing.</p>
							<p>&nbsp;</p>
							<p>Yours truly,<br/>
							<b>The Human Resources Department</b></p>';												
					$this->emailM->sendEmail('hr.cebu@tatepublishing.net', $eval->reviewerEmail, 'Evaluation form has been printed', $emBody, 'The Human Resources Department');
				}else if($_POST['submitType']=='uploadEval'){
					if(!empty($_FILES['evalDoc']['name'])){
						$fextn = $this->textM->getFileExtn($_FILES['evalDoc']['name']);
						$filename = 'evaluationForm_'.$_POST['evalID'].'_'.$_POST['empID'].'.'.$fextn;
						
						move_uploaded_file($_FILES['evalDoc']['tmp_name'], UPLOADS.'evaluations/'.$filename);
						
						$this->dbmodel->updateQuery('staffEvaluation', array('evalID'=>$_POST['evalID']), array('hrStatus'=>0));
						$this->dbmodel->updateConcat('staffEvaluation', 'evalID="'.$_POST['evalID'].'"', 'hrStatusData', $this->user->username.' - Uploaded form - '.date('Y-m-d H:i:s').'|');
					}
				}
			}
			
			$data['queryProbationary'] = $this->dbmodel->getQueryResults('staffs', 'username, empID, CONCAT(fname," ",lname) AS name, email, title, startDate, (SELECT CONCAT(fname," ",lname) FROM staffs s WHERE s.empID=staffs.supervisor) AS isName, perStatus', 'empStatus="probationary" AND staffs.active=1', 'LEFT JOIN newPositions ON position=posID');
			$data['queryRegular'] = $this->dbmodel->getQueryResults('staffs', 'username, empID, CONCAT(fname," ",lname) AS name, email, title, startDate, (SELECT CONCAT(fname," ",lname) FROM staffs s WHERE s.empID=staffs.supervisor) AS isName, perStatus', 'empStatus="regular" AND staffs.active=1', 'LEFT JOIN newPositions ON position=posID');
			
			$data['queryEval'] = $this->dbmodel->getQueryResults('staffEvaluation', 'evalID, empID_fk, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=empID_fk) AS name, finalRating, (SELECT CONCAT(fname," ",lname) AS M FROM staffs WHERE empID=reviewerEmpID) AS reviewerName, hrStatus', '1');
		} else {
			$data['access'] = false;
		}
				
		$this->load->view('includes/template', $data);
	}
	
	public function probperstatus(){
		$data['content'] = 'probperstatus';
		$id = $this->uri->segment(2);
		if(is_numeric($id) && $this->access->accessFullHR===true || $this->user->empID == 474){
			$data['row'] = $this->dbmodel->getSingleInfo('staffs', 'empID, username, fname, lname, CONCAT(fname," ",lname) AS name, perStatus ', 'empID="'.$id.'"');
			$data['queryPerStatus'] = $this->dbmodel->getQueryResults('staffPerRequirements', '*');
			$data['queryHistory'] = $this->dbmodel->getQueryResults('staffPerEmpStatus s', '*', 's.empID_fk="'.$id.'"', 'LEFT JOIN tcPrevious2316 t ON s.empID_fk = t.empID_fk');
			
			$data['arrHistory'] = array();
			foreach($data['queryHistory'] AS $hq){
				if($hq->perType==0){
					$data['arrHistory']['remark'][$hq->perID_fk][] = $hq->perValue;
				}else{
					$gunting = explode('+++', $hq->perValue);
					$data['arrHistory']['action'][$hq->perID_fk]['text'] = '';
					if(isset($gunting[1]) && !empty($gunting[1]))$data['arrHistory']['action'][$hq->perID_fk]['text'] .= $gunting[1].' - ';
					if(isset($gunting[2]) && !empty($gunting[2]))$data['arrHistory']['action'][$hq->perID_fk]['text'] .= $gunting[2].' - ';
					
					$data['arrHistory']['action'][$hq->perID_fk]['text'] .= 'Validated by <b>'.$hq->adder.'</b> on '.date('m/d/Y h:i A', strtotime($hq->dateAdded));
					$data['arrHistory']['action'][$hq->perID_fk]['naVal'] = $hq->naVal;
					$data['arrHistory']['action'][$hq->perID_fk]['tcPrevious2316_ID'] = $hq->tcPrevious2316_ID;
				}
			}
			
			if(!empty($_POST)){
				if($_POST['submitType']=='addRemarks'){
					$insArr['perType'] = 0; //1 for remarks
					$insArr['perValue'] = $_POST['remarks'];
					
				}else if($_POST['submitType']=='validate'){					
					$insArr['perType'] = 1; //1 for action
					$insArr['perValue'] = $_POST['perName'].' validated.';
					if(isset($_POST['naVal'])) $insArr['naVal'] = $_POST['naVal'];

					if( $_POST['perID'] == 6){
						
						$insertBIR = array(
											'empID_fk' => $id,
											'for21' => $_POST['bir21'],
											'for30B' => $_POST['bir30b'],
											'for31' => $_POST['bir31'],
											'for37' => $_POST['bir37'],
											'for38' => $_POST['bir38'],
											'for39' => $_POST['bir39'],
											'for40' => $_POST['bir40'],
											'for41' => $_POST['bir41'],
											'for42' => $_POST['bir42'],
											'for47A' => $_POST['bir47a'],
											'for51' => $_POST['bir51'],
											'for55' => $_POST['bir55'],
										);
						$this->dbmodel->insertQuery('tcPrevious2316', $insertBIR);
						if( isset($_POST['hasPrev']) && $_POST['hasPrev'] ){
							exit();
						}
					}
										
					if(!empty($_FILES['fileupload']['name'])){
						$fextn = $this->textM->getFileExtn($_FILES['fileupload']['name']);
						$filename = str_replace(' ', '_', $_POST['perName']).'_'.date('YmdHis').'.'.$fextn;
						
						move_uploaded_file($_FILES['fileupload']['tmp_name'], UPLOAD_DIR.$data['row']->username.'/'.$filename);							
						//$insArr['perValue'] .= '<br/>File uploaded <a href="'.$this->config->base_url().UPLOAD_DIR.$data['row']->username.'/'.$filename.'">'.$_POST['perName'].'</a>';	
						$insArr['perValue'] .= '<br/>File uploaded <a href="'.$this->config->base_url().'attachment.php?u='.urlencode($this->textM->encryptText('staffs/'.$data['row']->username)).'&f='.urlencode($this->textM->encryptText($filename)).'">'.$_POST['perName'].'</a>';

						//add data to staffUploads table
						$upArr['empID_fk'] = $id;
						$upArr['uploadedBy'] = $this->user->empID;
						$upArr['docName'] = $_POST['perName'].' File';
						$upArr['fileName'] = $filename;
						$upArr['dateUploaded'] = date('Y-m-d H:i:s');
						$this->dbmodel->insertQuery('staffUploads', $upArr);
						
						//add notification
						$this->commonM->addMyNotif($this->user->empID, 'You validated and uploaded <a href="'.$this->config->base_url().UPLOAD_DIR.$data['row']->username.'/'.$filename.'">'.$upArr['docName'].'</a> of '.$data['row']->name, 5);					
					}else{						
						$this->commonM->addMyNotif($this->user->empID, 'You validated '.$_POST['perName'].' of '.$data['row']->name, 5);
					}					
					
					$insArr['perValue'] .= '+++'.$_POST['remarks'];
					if(!empty($_POST['filelink'])) $insArr['perValue'] .= '+++File link is '.$_POST['filelink'];
					
					//update staffs perStatus
					$perce = 100/count($data['queryPerStatus']);
					$jacson = 1;
					if(isset($data['arrHistory']['action'])) 
						$jacson += count($data['arrHistory']['action']);
					$perce = $perce * $jacson;
					$this->dbmodel->updateQuery('staffs', array('empID'=>$id), array('perStatus'=>$perce));
				}
				
				if(isset($insArr)){
					$insArr['empID_fk'] = $id;
					$insArr['perID_fk'] = $_POST['perID'];
					$insArr['perValue'] = addslashes($insArr['perValue']);
					$insArr['adder'] = $this->user->username;
					$insArr['dateAdded'] = date('Y-m-d H:i:s');
					
					$this->dbmodel->insertQuery('staffPerEmpStatus', $insArr);					
					
					header('Location: '.$_SERVER['REQUEST_URI']);
					exit;
				}
			}			
		}else{
			$data['access'] = false;
		}
				
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function probaddrequirement(){
		$data['content'] = 'probaddrequirement';
		
		if(!empty($_POST)){			
			$_POST['addEmpID'] = $this->user->empID;
			$this->dbmodel->insertQuery('staffPerRequirements', $_POST);
			
			$data['added'] = $_POST['perName'];
			unset($_POST);
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	function referralmanagement(){
		$data['content'] = 'referralmanagement';
		
		if($this->access->accessFullHR==false) $data['access'] = false;
		else{
			$data['queryReferrals'] = $this->dbmodel->getQueryResults('staffReferFriend', 'staffReferFriend.*, (SELECT CONCAT(lname,", ",fname) AS name FROM staffs WHERE empID=empID_fk) AS referrer', 'refStatus=1');
			
			$data['queryTop'] = $this->dbmodel->getSQLQueryResults('SELECT empID, CONCAT(fname," ",lname) AS name, (SELECT SUM(bonus) FROM staffReferralBonus WHERE empID_fk = empID AND mrbID_fk=0) AS bonus, (SELECT COUNT(id) FROM applicants WHERE referrerID=empID) AS cnt FROM staffs WHERE active=1 HAVING cnt>=5');
			
			$data['queryMRB'] = $this->dbmodel->getQueryResults('staffMRB', 'staffMRB.*, fname, lname', '1', 'LEFT JOIN staffs ON empID=empID_fk');
		
		}
		
		$this->load->view('includes/template', $data);
	}
	
	function validatereferrrals(){		
		$data['content'] = 'validatereferrrals';
		
		$id = $this->uri->segment(2);
		if(!is_numeric($id) || $this->access->accessFullHR==false) $data['access'] = false;
		else{
			$data['row'] = $this->dbmodel->getSingleInfo('staffs', 'empID, username, fname, lname, CONCAT(fname," ",lname) AS name, perStatus', 'empID="'.$id.'"');
			$data['queryReferrals'] = $this->dbmodel->getQueryResults('applicants', 'id, fname, lname, email, mnumber, title, status, process, processStat, processText, processType, (SELECT mrbID_fk FROM staffReferralBonus WHERE appID_fk=id) AS mrbID', 
			'referrerID="'.$id.'" AND processStat=1', 
			'LEFT JOIN newPositions ON posID=position LEFT JOIN recruitmentProcess ON processID=process');
			
			$data['queryInvalid'] = $this->dbmodel->getQueryResults('applicants', 'id, fname, lname, email, mnumber, title, status, process, processStat, processText, processType', 
			'referrerID="'.$id.'" AND processStat=0', 
			'LEFT JOIN newPositions ON posID=position LEFT JOIN recruitmentProcess ON processID=process');
		}
		
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	function referralrelease(){
		$data['content'] = 'referralrelease';
		$id = $this->uri->segment(2);
		if(!is_numeric($id) || $this->access->accessFullHR==false) $data['access'] = false;
		else{
			if(!empty($_POST)){				
				$queryBonus = $this->dbmodel->getQueryResults('staffReferralBonus', 'bonusID, bonus', 'mrbID_fk=0 AND empID_fk="'.$id.'"');
				$bonus = 0;
				$bonusID = array();
				foreach($queryBonus AS $q){
					$bonusID[] = $q->bonusID;
					$bonus += $q->bonus;
				}
				
				$insArr['empID_fk'] = $id;
				$insArr['releasedAmount'] = $bonus;
				$insArr['dateReleased'] = date('Y-m-d', strtotime($_POST['dateReleased']));
				$insArr['bonusIDs'] = implode(',', $bonusID);
				$insArr['releasedNote'] = $_POST['releasedNote'];
				
				
				$mrbID = $this->dbmodel->insertQuery('staffMRB', $insArr); //insert
				$this->dbmodel->updateQueryText('staffReferralBonus', 'mrbID_fk="'.$mrbID.'"', 'bonusID	IN ('.$insArr['bonusIDs'].')'); //update referral table
				$data['posted'] = true;
			}
			
			$data['row'] = $this->dbmodel->getSingleInfo('staffs', 'empID, username, fname, lname, CONCAT(fname," ",lname) AS name, perStatus', 'empID="'.$id.'"');
		}
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	function referrralreleasedetails(){
		$data['content'] = 'referrralreleasedetails';
		$id = $this->uri->segment(2);
		
		if(!is_numeric($id) || $this->access->accessFullHR==false) $data['access'] = false;
		else{
			$data['mrb'] = $this->dbmodel->getSingleInfo('staffMRB','staffMRB.*, (SELECT CONCAT(fname," ",lname) AS name FROM staffs WHERE empID=empID_fk) AS name', 'mrbID="'.$id.'"');
						 
			if(!empty($data['mrb']->bonusIDs)){
				$data['applicants'] = $this->dbmodel->getQueryResults('applicants', 'id, CONCAT(fname," ",lname) AS name', 'bonusID IN ('.$data['mrb']->bonusIDs.')', 'LEFT JOIN staffReferralBonus ON appID_fk = id');				
			}
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	
	function evaluationself(){
		$data['content'] = 'evaluationself';
		
		if($this->user!=false){
			if(!empty($_POST)){
				if($_POST['submitType']=='submitSelfEval'){
					unset($_POST['submitType']);
					
					$insArr = $_POST;
					$arrPerf = array('dedicationToExcellence', 'proactiveness', 'teamwork', 'communication', 'reliability', 'professionalism','flexibility');
					
					foreach($insArr AS $k=>$i){
						if(in_array($k, $arrPerf)){
							$insArr[$k] = implode(',', $i);
						}
					}
					
					$insArr['empID_fk'] = $this->user->empID;
					$insArr['reviewFrom'] = date('Y-m-d', strtotime($insArr['reviewFrom']));
					$insArr['reviewTo'] = date('Y-m-d', strtotime($insArr['reviewTo']));
					$insArr['dateSelfEvaluated'] = date('Y-m-d H:i:s');
					
					$this->dbmodel->insertQuery('staffEvaluation', $insArr);					
					
					$this->commonM->addMyNotif($this->user->empID, 'You submitted your self-evaluation.', 5);
					$data['inserted'] = true;
				}				
			}
			
			$data['queryEvaluations'] = $this->dbmodel->getQueryResults('staffEvaluation', 'evalID, status, evalRating, finalRating, dateSelfEvaluated, reviewerEmpID, reviewFrom, reviewTo, (SELECT CONCAT(fname," ",lname) FROM staffs WHERE empID=reviewerEmpID AND reviewerEmpID!=0) AS reviewer', 'empID_fk="'.$this->user->empID.'" AND status<2', '', 'evalID DESC');
			
		}
		
		$this->load->view('includes/templatecolorbox', $data);	
	}
	
	function evaluationsupervisor(){
		$data['content'] = 'evaluationsupervisor';
		$id = $this->uri->segment(2);
		
		if(!is_numeric($id) || ($this->access->accessFullHR==false && $this->commonM->checkStaffUnderMe($id)==false) ){
			$data['access'] = false;
		}else{
			$data['evalData'] = $this->dbmodel->getSingleInfo('staffEvaluation', '*', 'reviewType="90th" AND empID_fk="'.$id.'"');
			$data['row'] = $this->dbmodel->getSingleInfo('staffs', 'empID, username, fname, lname, empStatus, CONCAT(fname," ",lname) AS name, perStatus, title, dept, supervisor, startDate', 'empID="'.$id.'"', 'LEFT JOIN newPositions ON posID=position');
			if(isset($data['row']->supervisor) && $data['row']->supervisor!=0){
				$data['firstsup'] = $this->dbmodel->getSingleInfo('staffs', 'empID, CONCAT(lname,", ",fname) AS name, title, supervisor', 'empID="'.$data['row']->supervisor.'"', 'LEFT JOIN newPositions ON posID=position');
				
				if(isset($data['firstsup']->supervisor) && $data['firstsup']->supervisor!=0){
					$data['secondsup'] = $this->dbmodel->getSingleInfo('staffs', 'empID, CONCAT(lname,", ",fname) AS name, title, supervisor', 'empID="'.$data['firstsup']->supervisor.'"', 'LEFT JOIN newPositions ON posID=position');
				}				
			}
			
			if(!empty($_POST)){
				unset($_POST['submitType']);
				$upArr = $_POST;			
				
				$arrPerf = array('dedicationToExcellence', 'proactiveness', 'teamwork', 'communication', 'reliability', 'professionalism','flexibility');
					
				foreach($upArr AS $k=>$ir){
					if(in_array($k, $arrPerf)){
						$imparr  = implode(",", $ir);
						$upArr[$k] = $data['evalData']->$k.'+++'.$imparr;
					}
				}
				 
				$arrOther = array('achievements', 'strengths', 'areasOfImprovement', 'goals', 'evalRating');
				foreach($arrOther AS $a){
					$upArr[$a] = $data['evalData']->$a.'+++'.$upArr[$a];
				}
				
				$upArr['status'] = 1;
				$upArr['reviewDate'] = date('Y-m-d', strtotime($upArr['reviewDate']));
				$upArr['dateSupEvaluated'] = date('Y-m-d H:i:s');
				
				if(!empty($upArr['nextReviewDate'])) $upArr['nextReviewDate'] = date('Y-m-d', strtotime($upArr['nextReviewDate']));				
				if(!empty($upArr['effectiveDate'])) $upArr['effectiveDate'] = date('Y-m-d', strtotime($upArr['effectiveDate']));
								
				if(!empty($upArr['effectiveSeparationDate'])) 
					$upArr['effectiveDate'] = date('Y-m-d', strtotime($upArr['effectiveSeparationDate']));
												
				unset($upArr['effectiveSeparationDate']);
				$this->dbmodel->updateQuery('staffEvaluation', array('evalID'=>$data['evalData']->evalID), $upArr);
				
				$this->commonM->addMyNotif($this->user->empID, 'You submitted evalution of '.$data['row']->name.'.', 5);
				$data['submitted'] = true;
			}
						
			$data['querySupervisor'] = $this->dbmodel->getQueryResults('staffs', 'empID, CONCAT(fname," ",lname) AS name', 'active=1 AND is_supervisor=1','fname');					
		}
		
		$this->load->view('includes/templatecolorbox', $data);	
	}
	
	public function evaluationpdf(){
		$id = $this->uri->segment(2);
		
		if(!is_numeric($id)){
			echo 'Invalid';
			exit;
		}else{
			$evalData = $this->dbmodel->getSingleInfo('staffEvaluation', 'staffEvaluation.*, (SELECT CONCAT(fname," ",lname) AS name FROM staffs WHERE empID=reviewerEmpID) AS reviewer', ' evalID="'.$id.'"');
			
			if($evalData->status==0){
				echo 'Invalid.';
				exit;
			}
			
			$row = $this->dbmodel->getSingleInfo('staffs', 'empID, username, fname, lname, empStatus, CONCAT(fname," ",lname) AS name, perStatus, title, dept, supervisor, startDate', 'empID="'.$evalData->empID_fk.'"', 'LEFT JOIN newPositions ON posID=position');
			if(isset($row->supervisor) && $row->supervisor!=0){
				$row->firstsup = $this->dbmodel->getSingleInfo('staffs', 'empID, CONCAT(lname,", ",fname) AS name, title, supervisor', 'empID="'.$row->supervisor.'"', 'LEFT JOIN newPositions ON posID=position');
				
				if(isset($row->firstsup->supervisor) && $row->firstsup->supervisor!=0){
					$row->secondsup = $this->dbmodel->getSingleInfo('staffs', 'empID, CONCAT(lname,", ",fname) AS name, title, supervisor', 'empID="'.$row->firstsup->supervisor.'"', 'LEFT JOIN newPositions ON posID=position');
				}		
			}
			
			$this->staffM->evaluationpdf($evalData, $row);
		}
	}
	
	public function reportviolation(){
		$data['content'] = 'reportviolation';
		
		if($this->user==false){
			$data['access'] = false;
		}else if(!empty($_POST)){
			$docs = '';
			$dir = 'uploads/violationreported/';
						
			$_POST['dateSubmitted'] = date('Y-m-d H:i:s');
			$_POST['empID_fk'] = $this->user->empID;
			$_POST['when'] = date('Y-m-d', strtotime($_POST['when']));
			$_POST['what'] = $_POST['what'];
			$_POST['whatISaction'] = $_POST['whatISaction'];
			$_POST['proof'] = $_POST['proof'];
			$_POST['otherdetails'] = $_POST['otherdetails'];
			$_POST['whatViolation'] = implode(',', $_POST['whatViolation']);
			$_POST['whyExcludeIS'] = $_POST['whyExcludeIS'];
			$_POST['anonymousEmail'] = $this->user->email;
			$_POST['anonymousName'] = $this->user->name;

			unset($_POST['donotccIS']);
			
			if(isset($_POST['submitanonymous'])){
				$_POST['anonymous'] = 1;
				unset($_POST['submitanonymous']);
			}else unset($_POST['alias']);
			
			
			$dfiles = $_FILES['docs'];
			for($m=0; $m<5; $m++){
				if(!empty($dfiles['name'][$m])){
					$fname = 'doc_0'.$m.'_'.date('ymdHis').'.'.$this->textM->getFileExtn($dfiles['name'][$m]);
					$docs .= $fname.'|';
					
					move_uploaded_file($dfiles['tmp_name'][$m], $dir.$fname);	
				}
			}
			$_POST['docs'] = $docs;
			
			$data['reportID'] = $this->dbmodel->insertQuery('staffReportViolation', $_POST);
			
			//record notifications on supervisor and employee
			$this->commonM->addMyNotif($this->user->empID, 'Submitted an incident report. Please check <a href="'.$this->config->base_url().'incidentreportaction/details/'.$data['reportID'].'/" class="iframe">here</a> for details.', 5);
			if(empty($_POST['whyExcludeIS']) && $this->user->supervisor!=0){
				$this->commonM->addMyNotif($this->user->supervisor, 'Submitted an incident report. Please check <a href="'.$this->config->base_url().'incidentreportaction/details/'.$data['reportID'].'/" class="iframe">here</a> for details.', 0, 1);
			}
			$data['submitted'] = true;			
		}
		
		$data['offensesData'] = $this->dbmodel->getQueryResults('staffOffenses', 'offenseID, offense');
		
		$this->load->view('includes/templatecolorbox', $data);	
	}
	
	public function incidentreports(){
		$data['content'] = 'incidentreports_';
		// $data['content'] = 'incidentreports';
		
		if( isset($_POST['hrAction']) ){
			$forNoMerit = '';
			if($_POST['reportType'] == 'noMerit'){
				$q = $this->dbmodel->getSingleInfo('staffReportViolation', "dateSubmitted, anonymousEmail, anonymousName", 'reportID = '.$_POST['reportID']);
				$dataE['IRRequestor'] = array('dateSubmitted' => $q->dateSubmitted,'requestoremail' =>$q->anonymousEmail, 'requestorname' => $q->anonymousName);
			}
			//$this->dbmodel->updateQueryText('staffReportViolation', 'hrAction="'.$_POST['reportType'].'" ' , 'reportID='.$_POST['reportID']);
			//get supervisor
			$r = $this->dbmodel->getSingleInfo('staffs s', "CONCAT(s.fname,' ', s.lname) as employeeName, CONCAT(sp.fname,' ', sp.lname) AS supervisorName, s.email, sp.email AS supEmail", 's.empID='.$_POST['empID'], 'LEFT JOIN staffs sp ON sp.empID = s.supervisor');
			
			$dataE['reportType'] = $_POST['reportType'];
			$dataE['reportID'] = $_POST['reportID'];
			$dataE['employeeName'] = $r->employeeName;
			$dataE['supEmail'] = $r->supEmail;
			$dataE['supervisorName'] = $r->supervisorName;

			$this->emailM->sendEmailIncidentReport($dataE);
		}
		if($this->user!=false){		
			if($this->access->accessFullHR==false){
				$data['access'] = false;
			}else{	
				$data['reportStatus'] = $this->textM->constantArr('incidentRepStatus');
				$data['reportData'] = $this->dbmodel->getQueryResults('staffReportViolation', 'reportID, docs,empID_fk, alias, supervisor,dateSubmitted, status, hrAction, CONCAT(fname," ",lname) AS name', 'status!=0', 'LEFT JOIN staffs ON empID=empID_fk ', 'dateSubmitted DESC');
			}
		}
		
		$this->load->view('includes/template', $data);	
	}
	
	
	public function incidentreportaction(){
		$data['content'] = 'incidentreportaction_';
		//$data['content'] = 'incidentreportaction';
		if($this->user==false){
			$data['access'] = false;
		}else{
			$id = $this->uri->segment(3);
			$data['type'] = $this->uri->segment(2);
			$data['reportStatus'] = $this->textM->constantArr('incidentRepStatus');
			
			if(!empty($_POST)){
				if($_POST['submitType']=='changeStatus'){
					$continue = TRUE;
					$isUploaded = '';

					if($_POST['status'] == 3){
						$isUploaded = $this->commonM->uploadFile($_FILES, 'uploads/staffs/violationreported/', 'IR-'.$id.'-'.date('Y-m-d'));
						if( $isUploaded ){
							$isUploaded = ', docs="'.$isUploaded.'"';
						}

						else{
							$continue = FALSE;
							echo "<script>alert('Please upload a PDF File.');</script>";
						}
					}

					if($continue){
						$insArr['status'] = $_POST['status'];
						$insArr['statusNote'] = $_POST['statusNote'];
						$insArr['staffReportViolation_fk'] = $id;
						$insArr['updatedBy'] = $this->user->username;
						$insArr['dateUpdated'] = date('Y-m-d H:i:s');
						$this->dbmodel->insertQuery('staffReportViolationHistory', $insArr);
						
						if(is_numeric($insArr['status'])){
							$this->dbmodel->updateQueryText('staffReportViolation', 'status="'.$insArr['status'].'"'.$isUploaded, 'reportID="'.$id.'"');
							//$this->dbmodel->updateQueryText('staffReportViolation', 'status="'.$insArr['status'].'"', 'reportID="'.$id.'"');
						}
					}
					$this->dbmodel->insertQuery('staffReportViolationHistory', $insArr);
					$data['actionsaved'] = true;
				}else if($_POST['submitType']=='editwhere'){
					$this->dbmodel->updateQueryText('staffReportViolation', '`where`="'.$_POST['where'].'"', 'reportID="'.$id.'"');
					
					$insArr['status'] = 'Edited';
					$insArr['statusNote'] = 'Where did the incident take place FROM '.$_POST['prevwhere'].' TO '.$_POST['where'];
					$insArr['staffReportViolation_fk'] = $id;
					$insArr['updatedBy'] = $this->user->username;
					$insArr['dateUpdated'] = date('Y-m-d H:i:s');
					$this->dbmodel->insertQuery('staffReportViolationHistory', $insArr);
				}
			}

			$data['dir'] = 'uploads/violationreported/';
			$data['details'] = $this->dbmodel->getSingleInfo('staffReportViolation', 'staffReportViolation.*, CONCAT(fname," ",lname) AS name', 'reportID="'.$id.'"', 'LEFT JOIN staffs ON empID=empID_fk');
			if(isset($data['details']->whatViolation)){
				$data['offensesData'] = $this->dbmodel->getQueryResults('staffOffenses', '*', 'offenseID IN ('.$data['details']->whatViolation.')');
			}			
			$data['statusHistory'] = $this->dbmodel->getQueryResults('staffReportViolationHistory', '*', 'staffReportViolation_fk="'.$id.'"');
			
		}
		
		$this->load->view('includes/templatecolorbox', $data);	
	}
	
	public function incidentreportform(){
		if($this->user==false){
			$data['access'] = false;
		}else{
			$id = $this->uri->segment(2);
			$details = $this->dbmodel->getSingleInfo('staffReportViolation', 'staffReportViolation.*, idNum, supervisor, CONCAT(fname," ",lname) AS name, (SELECT CONCAT(fname," ",lname) FROM staffs s WHERE s.empID=staffs.supervisor) AS supervisorName', 'reportID="'.$id.'"', 'LEFT JOIN staffs ON empID=empID_fk');
			
			$this->staffM->pdfincidentreport($details);
		}	
	}
	
	public function previouspayslips(){
		$empID = $this->uri->segment(2);		
		
		$data['content'] = 'previouspayslips';
		if($this->user!=false){
			if(!empty($_POST)){
				if($_POST['submitType']=='uploadPay' && !empty($_FILES['pfile'])){
					$username = $this->dbmodel->getSingleField('staffs', 'username', 'empID="'.$empID.'"');
					$cntend = count($_FILES['pfile']['name']);
										
					$notUploaded = '';
					for($x=0; $x<$cntend; $x++){
						if($_FILES['pfile']['name'][$x]!=''){
							$extn = array_reverse(explode('.', $_FILES['pfile']['name'][$x]));
							/* $paydate = str_replace($username.'-', '', $_FILES['pfile']['name'][$x]);
							$paydate = str_replace('.'.$extn[0], '', $paydate); */
														
							$payEx = array_reverse(explode('_', $extn[1]));
							$payEx2 = array_reverse(explode('-', $payEx[0]));
							$paydate = $payEx2[0].'-'.$payEx2[2].'-'.$payEx2[1];
														
							if(date('Y-m-d', strtotime($paydate)) == $paydate){
								$fname = $this->textM->getRandText(15).''.strtotime(date('Y-m-d H:i:s')).'.'.$extn[0];							
								move_uploaded_file($_FILES['pfile']['tmp_name'][$x], UPLOADS.'/prevPayslips/'.$fname);
																
								$insArr['empID_fk'] = $empID;
								$insArr['paydate'] = $paydate;
								$insArr['filename'] = $fname;
								$insArr['dateUploaded'] = date('Y-m-d H:i:s');
								$this->dbmodel->insertQuery('staffPrevPayslips', $insArr);
							}else{
								$notUploaded .= $_FILES['pfile']['name'][$x].'<br/>';
							}
						}
					}
					
					$data['notUploaded'] = $notUploaded;
				}
			}
			
			if($this->access->accessFullHRFinance==false && $empID!=$this->user->empID) $data['access'] = false;
			else{
				$data['staffInfo'] = $this->dbmodel->getSingleInfo('staffs', 'empID, idNum, fname, lname, title', 'empID="'.$empID.'"', 'LEFT JOIN newPositions ON posID=position');
				if(count($data['staffInfo'])==0) $data['access'] = false;
				
				$data['dataPayslips'] = $this->dbmodel->getQueryResults('staffPrevPayslips', '*', 'empID_fk="'.$empID.'"', '', 'paydate DESC');
			}
		}
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function generatewrittenwarning() {
		$data['content'] = 'writtenwarninggenerate';
		
		if($this->user!=false){
			$id = $this->uri->segment(2);
			$data['row'] = $this->dbmodel->getSingleInfo('staffs', 'empID, username, fname, lname, CONCAT(fname," ",lname) AS name, email, supervisor', 'empID="'.$id.'"');
						
			if(!empty($_POST)){				
				if($_POST['submitType']=='getOffdetails'){
					$level = $this->dbmodel->getSingleField('staffNTE', 'COUNT(nteID)', 'empID_fk="'.$id.'" AND status!=2 AND type="'.$_POST['offenseID'].'"');
					$level = (int)$level+1;
					
					echo $level;
					exit;
				}else if($_POST['submitType']=='submitWarning'){					
					$insArr['empID_fk'] = $id;
					if($_POST['offenseLevel']<2){
						$insArr['status'] = 4; //this is for the written warning
						$insArr['wrStatus'] = 1; //this is for the written warning
					} 
					
					$insArr['issuer'] = $this->user->empID;
					$insArr['dateissued'] = date('Y-m-d H:i:s');
					$insArr['wrDateGenerated'] = date('Y-m-d H:i:s');					
					$insArr['offensedates'] = date('Y-m-d', strtotime($_POST['dateIncident']));
					$insArr['wrDetails'] = addslashes($_POST['details']);
					$insArr['type'] = $_POST['offenseID_fk'];
					$insArr['offenselevel'] = $_POST['offenseLevel'];					
					$insID = $this->dbmodel->insertQuery('staffNTE', $insArr);
					
					
					$offDetail = $this->dbmodel->getSingleInfo('staffOffenses', 'offense, level', 'offenseID="'.$_POST['offenseID_fk'].'"');					
					$emDetail = '<p><b>Details of the incident:</b><br/>'.$_POST['details'].'</p>';
					$emDetail .= '<p><b>COC violation committed:</b><br/>'.$offDetail->offense.'</p>';
					$emDetail .= '<p><b>Offense Level:</b><br/>Level '.$offDetail->level.'</p>';
					
					$this->emailM->sendWrittenWarningEmail($data['row'], $emDetail, $insID);
					$data['submitted'] = true;
				}
			}
			
			
					
							
			$data['violationList'] = $this->dbmodel->getQueryResults('staffOffenses', 'offenseID,offense,level,category', 'level=1');	
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function writtenwarning(){
		$data['content'] = 'writtenwarning';
		
		if($this->user!=false){
			$nteID = $this->uri->segment(2);
			$data['type'] = $this->uri->segment(3);
			
			if(!empty($_POST)){
				$updateArr = array();
				if($_POST['submitType']=='requestchange'){
					$upArr['wrStatus'] = 4;
					$upArr['wrEdited'] = addslashes($_POST['wrEdited']);
					$this->dbmodel->updateQuery('staffNTE', array('nteID'=>$nteID), $upArr);
					$this->emailM->sendRequestEditToSup($nteID);
					echo 'Submitted';
					exit;
				}else if($_POST['submitType']=='accept'){
					$updateArr['wrStatus'] = 2;
					$updateArr['wrEdited'] = addslashes($_POST['wrEdited']);
					$echoAfter = 'Submitted';
				}else if($_POST['submitType']=='respond'){
					$upArr['wrStatus'] = 3;
					$upArr['wrResponse'] = addslashes($_POST['wrResponse']);	
					$this->dbmodel->updateQuery('staffNTE', array('nteID'=>$nteID), $upArr);
					$this->emailM->sendRequestEditToSup($nteID, $type='respond');
					echo 'Submitted';
					exit;
				}else if($_POST['submitType']=='deliberate'){
					$updateArr['wrDeliberation'] = addslashes($_POST['wrDeliberation']);
					if($_POST['subType']==0){
						$updateArr['wrStatus'] = 5;
						$data['demerit'] = true;
					}else{
						$updateArr['wrStatus'] = 6;
						$data['demerit'] = false;
					}
				}else if($_POST['submitType']=='merit' || $_POST['submitType']=='nomerit'){
					if($_POST['submitType']=='merit') $updateArr['wrStatus'] = 6;
					else $updateArr['wrStatus'] = 5;
					$updateArr['wrDeliberation'] = addslashes($_POST['wrDeliberation']);
					$echoAfter = 'Submitted';
				}
				
				if(!empty($updateArr)){					
					$this->dbmodel->updateQuery('staffNTE', array('nteID'=>$nteID), $updateArr);
					if(!empty($echoAfter)){
						echo $echoAfter;
						exit;
					}
				}
			}
						
			$data['info'] = $this->dbmodel->getSingleInfo('staffNTE', 'wrDetails, wrEdited, wrDeliberation, empID_fk, fname, issuer, CONCAT(fname," ",lname) AS name, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=issuer) AS supName, (SELECT email FROM staffs WHERE empID=issuer) AS supEmail', 'nteID="'.$nteID.'"', 'LEFT JOIN staffs ON empID=empID_fk');
									
			if($data['type']=='download'){	///download and send email to supervisor to pick up document
				//send email to requestor to get printed document from HR						
				$emBody = '<p>Hi '.$data['info']->supName.',</p>
						<p>Please be informed that the written warning document that you generated for '.$data['info']->fname.' has been printed. Please collect the document from HR and have it signed by '.$data['info']->name.'.</p>
						<p>&nbsp;</p>
						<p>Yours truly,<br/>
						<b>The Human Resources Department</b></p>';												
				$this->emailM->sendEmail('hr.cebu@tatepublishing.net', $data['info']->supEmail, 'Written warning document has been printed', $emBody, 'The Human Resources Department');
				
				header('Location:'.$this->config->base_url().'writtenmanagement/'.$nteID.'/pdf/D');
				exit;
			}else if($this->access->accessFullHR==false){
				if(($data['type']=='requestchange' || $data['type']=='notsign') && $data['info']->empID_fk!=$this->user->empID) $data['access'] = false;
				else if($data['type']=='accept' && $data['info']->issuer!=$this->user->empID) $data['access'] = false;
			}		
		}		
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function writtenmanagement(){
		$data['content'] = 'writtenmanagement';
		$data['contentpage'] = '';
		$id = $this->uri->segment(2);
		
		if($this->user!=false){
			if($this->access->accessFullHR==false) $data['access'] = false;
			
			$data['wrStatusArr'] = $this->textM->constantArr('writtenWarningStatus');
			
			if(!empty($id) && is_numeric($id)){
				$data['info'] = $this->dbmodel->getSingleInfo('staffNTE', 'nteID, empID_fk, type, offenselevel, category, wrDetails, wrEdited, wrResponse, wrDeliberation, dateissued, wrStatus, CONCAT(lname,", ",fname) AS name, fname, lname, username, offense, level, issuer, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS supName', 'status=4 AND wrStatus>0 AND nteID="'.$id.'"', 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN staffOffenses ON offenseID=type');
				
				if($data['info']->empID_fk==$this->user->empID || $data['info']->issuer==$this->user->empID) $data['access'] = true;
				
				if($this->uri->segment(3)=='pdf'){
					if($this->uri->segment(4)=='') $type = 'I';
					else $type = $this->uri->segment(4);
					$this->staffM->pdfwrittenwarning($data['info'], $type);
					exit;
				}else{
					$data['contentpage'] = 'details';				
				}				
			}else{
				$data['dataQuery'] = $this->dbmodel->getQueryResults('staffNTE', 'nteID, empID_fk, type, offenselevel, dateissued, wrStatus, CONCAT(lname,", ",fname) AS name, username, offense, level', 'status=4 AND wrStatus>0', 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN staffOffenses ON offenseID=type');
			}		
		}
		
		if($data['contentpage']=='details') $this->load->view('includes/templatecolorbox', $data);
		else $this->load->view('includes/template', $data);
	}
	
	public function medrequest(){
		$data['content'] = 'medrequest';
		$data['submitted'] = false;		
        $data['disabled'] = '';
        $data['status_labels'] = $this->textM->constantArr('medrequest');

		//check if we have session data
		if($this->user!=false){
			$med_id = $this->uri->segment(2); 
			if( isset($med_id) AND !empty($med_id) ){
				//get data by id in staffMedRequest
				$med_request_info = $this->dbmodel->getSingleInfo('staffMedRequest', 'medrequestID,supporting_docs_url, prescription_date, requested_amount, empID, idNum, CONCAT(fname," ", lname) AS "name", status, medperson_remarks, accounting_remarks, approved_amount, status_accounting, payStaffID_fk, date_submitted', 'medrequestID = '.$med_id, 'LEFT JOIN staffs ON empID = empID_fk');
				
				//if it is approved full data
				if( $med_request_info->status_accounting == 2 ){
					$payslip_details = $this->dbmodel->getSingleInfo('tcPayslipItemStaffs', 'tcPayslipItemStaffs.*, tcPayslipItems.payName', 'payStaffID = '. $med_request_info->payStaffID_fk, 'LEFT JOIN tcPayslipItems ON payID = payID_fk');
					
					$data['payslip_details'] = $payslip_details;
				}		
				
				//get history of request by id
				$data['med_request_history'] = $this->dbmodel->getQueryArrayResults('staffMedRequest', 'medrequestID, prescription_date, requested_amount, supporting_docs_url, status, medperson_remarks, accounting_remarks, approved_amount, status_accounting, date_submitted', 'empID_fk = '. $med_request_info->empID);				
				
				$data['header'] = 'Medicine Reimbursement Request of '. $med_request_info->name;
				$data['employee_info'] = $med_request_info;
				$data['pageview_type'] = 'approval';
				$data['med_id'] = $med_id;
				
				$data_config['dataItemInfo'] = $this->dbmodel->getSingleInfo('tcPayslipItems', '*', 'payID="33"');
				$data_config['dynamic_call'] = true;
				//$data_config['pageType'] = 'empUpdate';
				$data_config['dataItemInfo']->payAmount = $med_request_info->approved_amount;
                $data['payroll_item_html'] = $this->load->view('v_timecard/v_manange_paymentitems', $data_config, true );
				$data['disabled'] = 'disabled';
				
				if( $this->user->empID == $med_request_info->empID ){
					$data['self'] = true;
				}
				
                //not pulling?
			} else {
                $data['employee_info'] = $this->dbmodel->getSingleInfo('staffs', 'empID, idNum, CONCAT(fname," ", lname) AS "name"', 'empID = '.$this->user->empID);
                $data['pageview_type'] = 'file';
                $data['header'] = 'Request a Medicine Reimbursement';
			}		
			//if we have post data then save
            if( isset($_POST) AND !empty($_POST) ){
                //med request submission
                if( $this->isSetNotEmpty($_POST['from_page']) AND $_POST['from_page'] == 'file' ) {
                    $insert_array['empID_fk'] = $this->input->post('empID');
                    $insert_array['prescription_date'] = date('Y-m-d H:i:s', strtotime( $this->input->post('prescription_date') ) );
                    $insert_array['requested_amount'] = $this->input->post('requested_amount');
                    $insert_array['date_submitted'] = date('Y-m-d H:i:s');

					if( $this->isSetNotEmpty($_FILES) ){
						$files = $_FILES;					
						/* config data */
						$upload_config['upload_path'] = FCPATH .'uploads/medrequest';
						$upload_config['allowed_types'] = 'gif|jpg|png|pdf';
						$upload_config['max_size']	= '2048';
						$upload_config['overwrite']	= 'FALSE';						
						$this->load->library('upload');			
						
						//check how many to upload
						$upload_count = count( $_FILES['supporting_docs']['name'] );
						for( $x = 0; $x < $upload_count; $x++ ){
							$_FILES['to_upload']['name'] = $files['supporting_docs']['name'][$x];
							$_FILES['to_upload']['type'] = $files['supporting_docs']['type'][$x];
							$_FILES['to_upload']['tmp_name'] = $files['supporting_docs']['tmp_name'][$x];
							$_FILES['to_upload']['error'] = $files['supporting_docs']['error'][$x];
							$_FILES['to_upload']['size'] = $files['supporting_docs']['size'][$x];
							
							$ext = pathinfo( $_FILES['to_upload']['name'], PATHINFO_EXTENSION );
							$uniq_id = uniqid();
							$upload_config['file_name'] = $this->user->empID .'_'. $uniq_id .'_'. $x .'.'.$ext;
							
							$this->upload->initialize( $upload_config );
							
							if( ! $this->upload->do_upload('to_upload') ){
								$error_data[$x] = $this->upload->display_errors();
							} else {
								$upload_data[$x] = $this->upload->data();
							}
						}
						
						//if we have error, throw it to views
                        if( isset($error_data) AND !empty($error_data) ){
                            $data['error'] = '';
							foreach( $error_data as $key1 => $val1 ){
								$data['error'] .= $val1 ."\n";
							}						
						}					
						if( isset($upload_data) AND !empty($upload_data) ){
							foreach( $upload_data as $key => $val ){
								$docs_url[] = $val['full_path'];
							}
							$insert_array['supporting_docs_url'] = json_encode( $docs_url );
							$insert_array['supporting_docs_url'] = addslashes($insert_array['supporting_docs_url']);
						}					
					} // end of file upload
				
				
					//if we have all data then insert
					if( isset($insert_array['supporting_docs_url']) ){
						$insert_id = $this->dbmodel->insertQuery('staffMedRequest', $insert_array);
						
						//send notification to staff with med_person access						
						//there can be multiple medical personnel
						$med_person_id = $this->dbmodel->getQueryArrayResults('staffs', 'empID', 'access LIKE "%med_person%"');											
						$ntexts = 'Requested a medical reimbursement. Check Manage Staff > Medical Reimbursement Management page or click <a href="'. $this->config->base_url() .'medrequest/'.$insert_id.'/" class="iframe">here</a> to view the medical reimbursement details and approve.';
						foreach( $med_person_id as $key => $val ){
							$this->commonM->addMyNotif($val->empID, $ntexts, 5, 1);
						}
			
						$data['submitted'] = true;
						$data['confirm_msg'] = 'Your medicine reimbursement request has been submitted.';
					}
				} //end of med request submission
				
				//medical personnel approval
				if( $this->isSetNotEmpty($_POST['from_page']) AND $_POST['from_page'] == 'med_person' ){
					if( $this->isSetNotEmpty($_POST['medrequestID']) ){
						//update the status
						$update_array['status'] = $this->input->post('status_medperson');
						$update_array['medperson_remarks'] = $this->input->post('remarks_med_person');
						if( $update_array['status'] == 1 ){
								$update_array['approved_amount'] = $this->input->post('approved_amount');						
                        } 						
                        //if disapproved, automatically disapproved accounting
                        if( $update_array['status'] == 3 ){
                            $update_array['status_accounting'] = 4;
                        } else {
						    $update_array['status_accounting'] = $update_array['status'];
                        }


						$this->dbmodel->updateQuery('staffMedRequest', array('medrequestID' => $this->input->post('medrequestID') ), $update_array);
						//then insert history
						$history_array['medrequestID_fk'] = $this->input->post('medrequestID');
						$history_array['updatedBy'] = $this->user->username;
						$history_array['dateUpdate'] = date('Y-m-d H:i:s');
						$history_array['remarks'] = $this->input->post('remarks_med_person');
						$history_array['currentStatus'] = $update_array['status'];
						$this->dbmodel->insertQuery('staffMedRequest_history', $history_array);
						
						//add notification to accounting that medicine requisition has been approved
						$finance_id = $this->dbmodel->getQueryArrayResults('staffs', 'empID', 'access LIKE "%finance%"');						

						$ntexts = $data['status_labels'][ $update_array['status'] ] .' the medicine reimbursement request of '. $data['employee_info']->name.'. Check Manage Staff > Medicine Reimbursement Management page or click <a href="'. $this->config->base_url() .'medrequest/'. $this->input->post('medrequestID') .'/" class="iframe">here</a> to view the medicine reimbursement details and approve.';
						foreach( $finance_id as $key => $val ){
							$this->commonM->addMyNotif($val->empID, $ntexts, 5, 1);
						}
						$data['submitted'] = true;
						$data['confirm_msg'] = 'Medicine Reimbursement Request of '. $data['employee_info']->name .' has updated to '. $data['status_labels'][ $update_array['status'] ];
					}
				} //end med person update
				
				//accounting approval
				if( $this->isSetNotEmpty($_POST['from_page']) AND $_POST['from_page'] == 'full_finance' ){					
				
					if( $this->isSetNotEmpty($_POST['medrequestID']) ){
						//update the status
						$update_array['status_accounting'] = $this->input->post('status_accounting');
						$update_array['accounting_remarks'] = $this->input->post('remarks_accounting');						
						$this->dbmodel->updateQuery('staffMedRequest', array('medrequestID' => $this->input->post('medrequestID') ), $update_array);
						//then insert history
						$history_array['medrequestID_fk'] = $this->input->post('medrequestID');
						$history_array['updatedBy'] = $this->user->username;
						$history_array['dateUpdate'] = date('Y-m-d H:i:s');
						$history_array['remarks'] = $this->input->post('remarks_accounting');
						$history_array['currentStatus'] = $update_array['status_accounting'];
						$this->dbmodel->insertQuery('staffMedRequest_history', $history_array);
						
						if( $_POST['status_accounting'] == 2 ){
							//add item to payslip and filter out discrepancies
							$payslip_array = $_POST;
							if($payslip_array['payAmount']=='specific amount') $payslip_array['payAmount'] = $payslip_array['inputPayAmount'];
							if($payslip_array['payAmount']=='hourly' && isset($payslip_array['payAmountHourly'])) $payslip_array['payAmount'] = $payslip_array['payAmountHourly'];
							if(!empty($payslip_array['payStart'])) $payslip_array['payStart'] = date('Y-m-d', strtotime($payslip_array['payStart']));
							if(!empty($payslip_array['payEnd'])) $payslip_array['payEnd'] = date('Y-m-d', strtotime($payslip_array['payEnd']));
							if($payslip_array['payAmount']=='regularHoliday') $payslip_array['payPercent'] = $payslip_array['selectPayPercent'];
							if($payslip_array['payAmount']=='specialHoliday') $payslip_array['payPercent'] = 2;
							if($payslip_array['payPeriod']=='once' && !empty($payslip_array['payStartOnce'])){
								$payslip_array['payStart'] = date('Y-m-d', strtotime($payslip_array['payStartOnce']));
								$payslip_array['payEnd'] = $payslip_array['payStart'];
							}
							$payslip_array['empID_fk'] = $payslip_array['empID'];
							$payslip_array['payID_fk'] = $payslip_array['payID'];
							
							unset($payslip_array['empID']);
							unset($payslip_array['emp_id']);
							unset($payslip_array['emp_name']);
							unset($payslip_array['prescription_date']);
							unset($payslip_array['requested_amount']);
							unset($payslip_array['from_page']);
							unset($payslip_array['status_accounting']);
							unset($payslip_array['remarks_accounting']);
							unset($payslip_array['submit']);
							unset($payslip_array['submitType']);
							unset($payslip_array['payID']);
							unset($payslip_array['inputPayAmount']);
							unset($payslip_array['payAmountHourly']);
							unset($payslip_array['payStartOnce']);
							unset($payslip_array['selectPayPercent']);
							unset($payslip_array['medrequestID']);
							unset($payslip_array['payName']);
							unset($payslip_array['mainItem']);
							unset($payslip_array['payCategory']);
							unset($payslip_array['payType']);
							unset($payslip_array['payCDto']);
							
						}
						
						
						if( isset($payslip_array) AND !empty($payslip_array) AND $_POST['status_accounting'] == 2 ){
							$insID = $this->dbmodel->insertQuery('tcPayslipItemStaffs', $payslip_array);
							//update the med
							$update_array = array('payStaffID_fk' => $insID);
							$this->dbmodel->updateQuery('staffMedRequest', array('medrequestID' => $this->input->post('medrequestID') ), $update_array);
						}
						
						
						//add notification to user that medicine requisition has been approved						
						$ntexts = $data['status_labels'][ $this->input->post('status_accounting') ] . ' your medicine reimbursement request. Click <a href="'. $this->config->base_url() .'medrequest/'.$this->input->post('medrequestID').'/" class="iframe">here</a> to view the medicine reimbursement details.';
 						$this->commonM->addMyNotif($data['employee_info']->empID, $ntexts, 5, 1);
						
						$data['submitted'] = true;
						$data['confirm_msg'] = 'Medicine Reimbursement Request of '. $data['employee_info']->name .' has updated to '. $data['status_labels'][ $this->input->post('status_accounting') ];
 					}
				}//end of accounting approval
			} //end of post
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function medrequests(){
		$data['content'] = 'medrequests';
		
		$data['data_query_all'] = $this->dbmodel->getQueryArrayResults('staffMedRequest', '*', 1, 'LEFT JOIN staffs ON empID = empID_fk');
		$data['data_query_medical'] = $this->dbmodel->getQueryArrayResults('staffMedRequest', '*', 'status = 0', 'LEFT JOIN staffs ON empID = empID_fk');
		$data['data_disapproved_medical'] = $this->dbmodel->getQueryArrayResults('staffMedRequest', '*', 'status = 3', 'LEFT JOIN staffs ON empID = empID_fk');
		$data['data_query_accounting'] = $this->dbmodel->getQueryArrayResults('staffMedRequest', '*', 'status = 1 AND status_accounting NOT IN (2, 3, 4)', 'LEFT JOIN staffs ON empID = empID_fk');
		
		$data['data_approved_accounting'] = $this->dbmodel->getQueryArrayResults('staffMedRequest', '*', 'status_accounting = 2 AND status NOT IN (0)', 'LEFT JOIN staffs ON empID = empID_fk');
		$data['data_disapproved_accounting'] = $this->dbmodel->getQueryArrayResults('staffMedRequest', '*', 'status_accounting IN (3,4) OR status IN (3, 4)', 'LEFT JOIN staffs ON empID = empID_fk');
		
		$data['cnt_all'] = count($data['data_query_all']);
		$data['cnt_medical'] = count($data['data_query_medical']);
		$data['cnt_accounting'] = count($data['data_query_accounting']);
		$data['cnt_approved_accounting'] = count($data['data_approved_accounting']);
		$data['cnt_disapproved_accounting'] = count($data['data_disapproved_accounting']);
		$data['cnt_disapproved_medical'] = count($data['data_disapproved_medical']);
		
		//var_dump($data['data_query']);
		$this->load->view('includes/template', $data);	
	}

	public function kudosrequest(){
		$data['id'] = $this->uri->segment(2);
		if($data['id'] == 'evaluation'){
			$data['requestID'] = $this->uri->segment(3);
			$data['kudosRequestStatus'] = $this->uri->segment(4);

			$evaluation = $this->dbmodel->getResultArray('kudosRequest', ' kudosReceiverID ,CONCAT(fname, " ", lname) AS requestor, kudosReason, kudosAmount', 'kudosRequestID = '.$data['requestID'], 'LEFT JOIN staffs ON kudosRequestorID = empID');

			$data['evaluationContent'] = $evaluation;

			$data['kudosEvaluation'] = $this->dbmodel->getSingleField('kudosRequest','kudosEvaluation', 'kudosRequestID = '.$data['requestID'] );
			if( $data['kudosEvaluation'] != '' )
				$data['kudosEvaluation'] = substr($data['kudosEvaluation'], 0, -1);

			$data['content'] = 'kudosrequestevaluation';
		}elseif( $data['id'] == 'viewrequest' ){
			$data['requestID'] = $this->uri->segment(3);
			$data['kudosRequestStatus'] = $this->uri->segment(4);

			$evaluation = $this->dbmodel->getResultArray('kudosRequest', ' reasonForDisapproving, kudosReceiverID ,CONCAT(fname, " ", lname) AS requestor, kudosReason, kudosAmount', 'kudosRequestID = '.$data['requestID'], 'LEFT JOIN staffs ON kudosRequestorID = empID');

			$data['evaluationContent'] = $evaluation;

			$data['content'] = 'kudosview';
		}
		else{
			$data['content'] = 'kudosrequest';
			$data['supervisor'] = $this->dbmodel->getSingleField('staffs', 'supervisor', 'empID = '.$data['id']);
		}

		$this->load->view('includes/templatecolorbox', $data);
	}

	public function submitKudosRequest(){
		$p = $this->input->post();

		if(isset($p['submitType']) && $p['submitType'] == 'updateStatus'){
			unset($p['submitType']);
			
			$r['kudosRequestID'] = $p['kudosRequestID'];
			unset($p['kudosRequestID']);

			if( $p['kudosRequestStatus'] > 3 ){
				$p['dateApproved'] = date('Y-m-d H:i:s');
			}

			if( $p['kudosRequestStatus'] == 4 ){
				//insert to tcPayslipItemStaffs
				//payStaffID	empID_fk	payID_fk 	payAmount	payPeriod	payStart	payEnd	status
				$insertPay['empID_fk'] = $p['empID_fk'];
				$insertPay['payID_fk'] = $p['payID_fk'];
				$insertPay['payAmount'] = $p['payAmount'];
				$insertPay['payPeriod'] = $p['payPeriod'];
				$insertPay['payStart'] = $p['payStart'];
				$insertPay['payEnd'] = $p['payEnd'];
				$insertPay['status'] = $p['status'];

				//get the payperiod date
				$p['payPeriod'] = $p['payStart'];
				//unset those stuffs
				unset($p['empID_fk']);
				unset($p['payID_fk']);
				unset($p['payAmount']);
				unset($p['payStart']);
				unset($p['payEnd']);
				unset($p['status']);

				$insID = $this->dbmodel->insertQuery('tcPayslipItemStaffs', $insertPay);
			}

			$this->dbmodel->updateQuery('kudosRequest', $r, $p);

			//send auto-email for approved kudos
			if( $p['kudosRequestStatus'] == 4){
				$this->emailM->sendKudosRequestEmail($r['kudosRequestID'], TRUE, $insertPay['payStart']);
			}elseif( $p['kudosRequestStatus'] == 5 ){
				$this->emailM->sendKudosRequestEmail($r['kudosRequestID'], FALSE);
			}
			echo "Request Updated";
		}else{
			$p['dateRequested'] = date("Y-m-d H:i:s");
			//$p['kudosReason'] = mysql_real_escape_string($p['kudosReason']);

			$this->db->insert('kudosRequest', $p);
			$insertID = $this->db->insert_id();
			//insert notification for kudos
			$staffName = $this->dbmodel->getSingleField('staffs', 'CONCAT(fname," ",lname) AS staffName', 'empID = '.$p['kudosReceiverID']);

			$message = 'A Kudos Request has been given to '.$staffName.'<br/>Click <a href="'.$this->config->base_url().'kudosRequest/evaluation/'.$insertID.'/1">here</a> to view the Kudos Request';
			$this->commonM->addMyNotif($p['kudosReceiverSupID'], $message, 6, 1);
		}
	}

	public function kudosrequestM(){
		if( $this->user ){
			$data['content'] = 'kudosrequestM';
		
			$condition = '';

			$all = '';
			$and = '';

			if(!$this->access->accessFull && !$this->access->accessFinance){
				$condition = 'kudosReceiverSupID = '.$this->user->empID;
				$all = $condition;
			}

			if( $all != '')
				$and = " AND ";

			$data['cnt_is'] = $this->dbmodel->getSingleField('kudosRequest', 'COUNT(kudosRequestID)', $condition.$and.' kudosRequestStatus = 1');
			$data['cnt_hr'] = $this->dbmodel->getSingleField('kudosRequest', 'COUNT(kudosRequestID)', ' kudosRequestStatus = 2');
			$data['cnt_acc'] = $this->dbmodel->getSingleField('kudosRequest', 'COUNT(kudosRequestID)', ' kudosRequestStatus = 3');


			$data['cnt_all'] = $this->dbmodel->getSingleField('kudosRequest', 'COUNT(kudosRequestID)', $all);
			$data['cnt_approved'] = $this->dbmodel->getSingleField('kudosRequest', 'COUNT(kudosRequestID)', $all.$and." kudosRequestStatus = 4");
			$data['cnt_disapproved'] = $this->dbmodel->getSingleField('kudosRequest', 'COUNT(kudosRequestID)', $all.$and." kudosRequestStatus = 5");

			$p = $this->dbmodel->getSQLQueryResults("SELECT kudosReceiverSupID, payPeriod, kudosRequestID, kudosReason, kudosAmount, kudosRequestStatus, dateRequested, dateApproved, reasonForDisapproving, statusName,CONCAT(r.fname,' ' , r.lname) AS requestorName, CONCAT(s.fname,' ', s.lname) AS staffName FROM kudosRequest LEFT JOIN staffs s ON s.empID = kudosReceiverID LEFT JOIN staffs r ON r.empID = kudosRequestorID LEFT JOIN kudosRequestStatusLabels ON kudosRequestStatus = statusID ORDER BY kudosRequestID ");
			
			//$p = $this->dbmodel->getSQLQueryResults('kudosRequest', 'kudosRequestID, kudosReason, kudosAmount, kudosRequestStatus,statusName,CONCAT(r.fname," " , r.lname) AS requestorName, CONCAT(s.fname," ", s.lname) AS staffName', 1,'LEFT JOIN staffs s ON s.empID = kudosReceiverID LEFT JOIN staffs r ON r.empID = kudosRequestorID LEFT JOIN kudosRequestStatusLabels ON kudosRequestStatus = statusID','kudosRequestID');
			$newData = array();

			//Rearrange data base on statuses
			foreach ($p as $key => $value) {
				$newData[$value->kudosRequestStatus][] = $value;
			}
			$data['results'] = $newData;

			$this->load->view('includes/template', $data);	
		}
	}
	
	function test(){
		
		// $today = date_create(date('Y-m-d'));
		// dd($today, false);
		// $twodays = date_add($today, date_interval_create_from_date_string('2 days') );
		// $twodays = date_format( $twodays, 'Y-m-d' );
		// dd($twodays);
		// $info->name = 'Marjune';
		// $info->gender = 'M';
		// $info->endDate = '2016-06-15';
		// $info->supEmail = 'marjune.abellana@tatepublishing.net';
		// $this->emailM->emailSeparationDateAdvanceNotice( $info );
		$date13 = date('Y-m-d');
		$endDateObj = new DateTime( $date13 );
		$endDateObj->add( new DateInterval( 'P90D') );

		//check if endDate is Tuesday
		$endDate = $endDateObj->format('l');
		dd($endDateObj->format('Y-m-d'), false);
		//dd($endDate);
		while( $endDate != 'Tuesday' ){

			
			$endDateObj->add( new DateInterval('P1D') );
			$endDate = $endDateObj->format('l');

			dd($endDate, false);
		}
		$releaseDate = $endDateObj->format('Y-m-d');
		dd($releaseDate);
		//$arrayOfDataThatIsNew['releaseDate'] = $releaseDate;
}
	public function reports(){
		$data['content'] = 'reports';
		$which_report = $this->uri->segment(2);
		
		
		if($this->user!=false){
			if($this->access->accessFullHRFinance==false){
				$data['access'] = false;
			} else if( $which_report == 'upward_feedback' ){
				$data['which_report'] = $which_report;
					
				$feedbacks = $this->dbmodel->getSQLQueryArrayResults('SELECT date_submitted, feedback, (SELECT CONCAT(fname, " ", lname) FROM staffs WHERE empID = from_whom) AS "respondent", (SELECT CONCAT(fname, " ", lname) FROM staffs WHERE staffs.empID = staffLeaderFeedback.supervisor) AS "supervisor" FROM staffLeaderFeedback ORDER BY date_submitted DESC');
				$data['feedbacks'] = $feedbacks;
				
			} else {
				if(!empty($_POST)){
					if($_POST['submitType']=='genLeaveCodes'){
						$start = date('Y-m-d', strtotime($_POST['dateFrom']));
						$end = date('Y-m-d', strtotime($_POST['dateTo']));
												
						$queryCode = $this->dbmodel->getQueryResults('staffCodes', '*', 'dategenerated BETWEEN "'.$start.'" AND "'.$end.'"');
						$this->staffM->excelGenerateLeaveCodes($queryCode, $start, $end);						
					}

					if( $_POST['submitType'] == 'genAttendanceReports' ){
						$data['report_start'] = date('Y-m-d', strtotime($_POST['dateFrom']) );
						$data['report_end'] = date('Y-m-d', strtotime($_POST['dateTo']) );
						$this->timeM->getAttendanceReport( $data );
						exit();
					}
				}
				//current calc
				$current_calc = $this->dbmodel->getSingleInfo('staffCompensationReports', '*', 'for_month LIKE "%'.date('Y-m', strtotime('-1 months') ).'%"');
				$data['average_wage_rankfile'] = $this->textM->decryptText( $current_calc->total_wage_rankfile ) / $current_calc->total_rankfile;
				$data['average_wage_supervisor'] = $this->textM->decryptText( $current_calc->total_wage_supervisor ) / $current_calc->total_supervisor;
				
				$past_calc = $this->dbmodel->getSQLQueryArrayResults('SELECT * FROM staffCompensationReports ORDER BY for_month DESC');
				$data['past_calc'] = $past_calc;
				
			}
		}
		
		$this->load->view('includes/template', $data);
	}
	public function alphalist(){
		$data['content'] = 'alphalist';
		
		$this->load->view('includes/templatecolorbox', $data);
	}


	public function hdmf(){
		$data['content'] = 'hdmf_loan';
		$data['loan_purpose_array'] = $this->textM->constantArr('hdmf_loan_purpose');
		$this->load->library('form_validation');
		$all_staff = $this->dbmodel->getQueryArrayResults('staffs', 'empID, CONCAT(fname," ",lname) AS "name"', 'office = "PH-Cebu" AND active = 1 ORDER BY lname');
		$all_staffs = array();
		
		foreach($all_staff as $key => $val ){
			$all_staffs[$val->empID] = $val->name;
		}
		$data['all_staff'] = $all_staffs;

		
		if( $this->input->post('loan_id') ) {			
			//upload
			if( isset($_FILES) AND !empty($_FILES) ){
				$upload_config['upload_path'] = FCPATH .'uploads/staffs/'.$this->input->post('username');
				$upload_config['allowed_types'] = 'gif|jpg|png|pdf';
				$upload_config['max_size']	= '2048';
				$upload_config['overwrite']	= 'FALSE';								

				$ext = pathinfo( $_FILES['loan_voucher']['name'], PATHINFO_EXTENSION );				
				$upload_config['file_name'] = 'HDMF_loan_voucher.'.$ext;
				$this->load->library('upload', $upload_config);					
				//upload
				if( $this->upload->do_upload('loan_voucher') ){
					//update the table 
					$upload_data = $this->upload->data();
					$this->dbmodel->updateQuery('staff_hdmf_loan', array('hdmf_loan_id' => $this->input->post('loan_id')), array('hdmf_loan_voucher_url' => $upload_data['full_path']));
					$data['msg'] = 'Voucher has been uploaded.';
				} else {
					$data['upload_error'] = $this->upload->display_errors();
				}
			}
		} 
		//for accounting
		if( $this->input->post('empID_fk') ){
			//add item to payslip and filter out discrepancies

			

			$payslip_array = $_POST;
			if($payslip_array['payAmount']=='specific amount') $payslip_array['payAmount'] = $payslip_array['inputPayAmount'];
			if($payslip_array['payAmount']=='hourly' && isset($payslip_array['payAmountHourly'])) $payslip_array['payAmount'] = $payslip_array['payAmountHourly'];
			if(!empty($payslip_array['payStart'])) $payslip_array['payStart'] = date('Y-m-d', strtotime($payslip_array['payStart']));
			if(!empty($payslip_array['payEnd'])) $payslip_array['payEnd'] = date('Y-m-d', strtotime($payslip_array['payEnd']));
			if($payslip_array['payAmount']=='regularHoliday') $payslip_array['payPercent'] = $payslip_array['selectPayPercent'];
			if($payslip_array['payAmount']=='specialHoliday') $payslip_array['payPercent'] = 2;
			if($payslip_array['payPeriod']=='once' && !empty($payslip_array['payStartOnce'])){
				$payslip_array['payStart'] = date('Y-m-d', strtotime($payslip_array['payStartOnce']));
				$payslip_array['payEnd'] = $payslip_array['payStart'];
			}
			$payslip_array['empID_fk'] = $payslip_array['empID_fk'];
			$payslip_array['payID_fk'] = $payslip_array['payID'];
			
			unset($payslip_array['empID']);
			unset($payslip_array['submit']);
			unset($payslip_array['submitType']);
			unset($payslip_array['payID']);
			unset($payslip_array['inputPayAmount']);
			unset($payslip_array['payAmountHourly']);
			unset($payslip_array['payStartOnce']);
			unset($payslip_array['selectPayPercent']);		
			
			if( isset($payslip_array) AND !empty($payslip_array) ){
				$insID = $this->dbmodel->insertQuery('tcPayslipItemStaffs', $payslip_array);
				//add notification to user that medicine requisition has been approved	
				if( isset($insID) AND !empty($insID) ){
					$ntexts = 'Salary deduction for Pag-IBIG loan has updated';
					$this->commonM->addMyNotif( $this->input->post('empID'), $ntexts, 5, 1);
				}

				$data['content'] = 'v_timecard/v_mypayrollsetting';
				$data['dataAddItems'] = $this->dbmodel->getQueryResults('tcPayslipItems', '*', 'mainItem=0', '', 'payName'); //additional items
				$data['dataMyItems'] = $this->payrollM->getPaymentItems($payslip_array['empID_fk']);				
			}
		}


		$id = $this->uri->segment(2);
		if( isset($id) AND !empty($id) ){

			$info = $this->dbmodel->getSingleInfo('staff_hdmf_loan', 'staff_hdmf_loan.*, username', 'hdmf_loan_id = '.$id, 'LEFT JOIN staffs ON empID = empID_fk');
			//prep the data
			$data['loan_id'] = $info->hdmf_loan_id;
			$data['loan_type'] =  $info->hdmf_loan_type;
			$data['loan_amt'] =  $info->hdmf_loan_amt;
			$data['loan_purpose'] =  $info->hdmf_loan_purpose;
			$data['birth_place'] =  $info->hdmf_loan_birth_place;
			$data['mo_maiden_name'] =  $info->hdmf_loan_mo_maiden_name;
			$data['employer_1'] =  json_decode(stripslashes($info->hdmf_loan_employer_1), true);
			$data['employer_2'] =  json_decode(stripslashes($info->hdmf_loan_employer_2), true);
			$data['witness_2'] =  $this->input->post('witness_1');
			$data['witness_2'] =  $this->input->post('witness_2');
			$data['date_submitted'] = $info->hdmf_loan_date_submitted;
			$data['empID'] = $info->empID_fk;
			$data['username'] = $info->username;
			$data['witness_1'] = $info->hdmf_loan_witness_1;
			$data['witness_2'] = $info->hdmf_loan_witness_2;
			

			if( $this->input->get('a') == 'upload' ){
				$data['upload'] = true;	
			} else if( $this->input->get('a') == 'accounting' ) {
				$data['accounting'] = true;
				$data_config['dataItemInfo'] = $this->dbmodel->getSingleInfo('tcPayslipItems', '*', 'payID="19"');
				$data_config['dynamic_call'] = true;
				$data_config['pageType'] = 'empUpdate';				
                $data['payroll_item_html'] = $this->load->view('v_timecard/v_manange_paymentitems', $data_config, true );
			} else {
				$this->staffM->hdmf_loan( $info->empID_fk, $data );
			}

		} else {

			
			$rules = array(
					array('field' => 'loan_type', 'label' => '`Loan Type`', 'rules' => 'required'),
					array('field' => 'loan_amt', 'label' => '`Loan Amount`', 'rules' => 'required'),
					array('field' => 'loan_purpose' , 'label' => '`Loan Purpose`', 'rules' => 'required|callback_select_check["loan purpose"]'),
					array('field' => 'birth_place' , 'label' => '`Birth Place`', 'rules' => 'required|xss_clean'),
					array('field' => 'mo_maiden_name' , 'label' => '`Mother\'s Maiden Name`', 'rules' => 'required|xss_clean'),
					array('field' => 'witness_1' , 'label' => 'First Witness', 'rules' => 'required|callback_select_check["first witness"]'),
					array('field' => 'witness_2' , 'label' => 'Second Witness', 'rules' => 'required|callback_select_check["second witness"]'),
					array('field' => 'employer_1[0]' , 'label' => 'Employer First', 'rules' => 'xss_clean'),
					array('field' => 'employer_2[0]' , 'label' => 'Employer Second', 'rules' => 'xss_clean'),
					array('field' => 'employer_1[1]' , 'label' => 'Employer First', 'rules' => 'xss_clean'),
					array('field' => 'employer_2[1]' , 'label' => 'Employer Second', 'rules' => 'xss_clean'),
					array('field' => 'employer_1[2]' , 'label' => 'Employer First', 'rules' => 'xss_clean'),
					array('field' => 'employer_2[2]' , 'label' => 'Employer Second', 'rules' => 'xss_clean'),
					array('field' => 'employer_1[3]' , 'label' => 'Employer First', 'rules' => 'xss_clean'),
					array('field' => 'employer_2[3]' , 'label' => 'Employer Second', 'rules' => 'xss_clean'),
				);
			$this->form_validation->set_rules( $rules );
			$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
			if( isset($_POST) AND !empty($_POST) ){
				
				if( $this->form_validation->run() == TRUE ){
					//insert the records to db
					$insert_array = array(
						'empID_fk' => $this->user->empID, 
						'hdmf_loan_type' => $this->input->post('loan_type'),
						'hdmf_loan_amt' => $this->input->post('loan_amt'),
						'hdmf_loan_purpose' => $this->input->post('loan_purpose'),
						'hdmf_loan_birth_place' => $this->input->post('birth_place'),
						'hdmf_loan_mo_maiden_name' => $this->input->post('mo_maiden_name'),
						'hdmf_loan_employer_1' => addslashes(json_encode($this->input->post('employer_1'))),
						'hdmf_loan_employer_2' => addslashes(json_encode($this->input->post('employer_2'))),
						'hdmf_loan_date_submitted' => date('Y-m-d H:i:s'),
						'hdmf_loan_witness_1' => $this->input->post('witness_1'),
						'hdmf_loan_witness_2' => $this->input->post('witness_2'),
					);
					$insert_id = $this->dbmodel->insertQuery('staff_hdmf_loan', $insert_array);

					//add notification to accounting for hdmf loan application
					$finance_id = $this->dbmodel->getQueryArrayResults('staffs', 'empID', 'access LIKE "%finance%" OR access LIKE "%hr%"');						
					$staff_name = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname, " ", lname) AS name', 'empID = '. $this->user->empID );
					$ntexts = $staff_name->name.' has submitted HDMF loan application.';
					foreach( $finance_id as $key => $val ){
						$this->commonM->addMyNotif($val->empID, $ntexts, 5, 1);
					}
					$data['msg'] = 'Application has been submitted. You can view the form <a href="'.$this->config->base_url().'hdmf/'.$insert_id.'" target="_blank">here</a>';

				} 			
			}
		}

		$this->load->view('includes/templatecolorbox', $data);
		
	}

	//validator call back
	public function select_check($val, $which_option){
		if( $val == 0 ){
			$this->form_validation->set_message('select_check', 'Please select a '.$which_option.'.');
			return FALSE;
		} else {
			return TRUE;
		}
	}


	

	//management page
	public function hdmfs(){
		$data['content'] = 'hdmf_loans';
		
		$data['headers'] =  array('applicant', 'loan type', 'status', 'date submitted', 'file');
		$data['hdmf_loan_status'] = $this->textM->constantArr('hdmf_loan_status');
		$data['col_options'] = '';

		//check for post
		if( $this->input->is_ajax_request() ){
			$id = $this->input->post('id');
			$status = $this->input->post('status');
			$empID = $this->input->post('empID');
			if( isset($id) AND !empty($id) ){

				$all_staff = $this->staffM->get_cached_all_staff();
				//auto-email for each status
				switch( $status ){
					//printed
					case 1:
						$msg = '<p>Hi '. $all_staff[ $empID ]->fname .' '. $all_staff[ $empID ]->lname.',</p>
						<p>Please be informed that your HDMF loan application form in now printed and may be claimed at HR/Admin office.</p>
						<p><i>Thanks,</i><br/>
						<b>CAREERPH</b></p>';
					break;
					//endorsed to employee
					case 2:
						$msg = '<p>Hi '. $all_staff[ $empID ]->fname .' '. $all_staff[ $empID ]->lname.',</p>
						<p>Good day!</p>
						<p>This e-mail serves as a confirmation that the HDMF loan application form you requested has already been endorsed to you.</p>
						<p><span style="color:red;">Note:</span> Please notify HR thru <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a> as soon as you have successfully filed your HDMF loan so that your payments can be deducted from your salary and remitted to Pag-IBIG on time. Failure to do so would delay remittance of your loan payments and Tate Publishing will not be held liable for the penalties that might be incurred.</p>
						<p><i>Thanks,</i><br/>
						<b>CAREERPH</b></p>';
					break;
					//approved loans
					case 3:
						$msg = '<p>Hi '. $all_staff[ $empID ]->fname .' '. $all_staff[ $empID ]->lname.',</p>

						<p>This is to confirm that HR has been notified of your HDMF loan approval. Please be advised to personally go to Pag-IBIG to get your loan ledger and endorse it to accounting for salary deduction.</p>
						<p><i>Thanks,</i><br/>
						<b>CAREERPH</b></p>';
					break;
				}

				 $this->dbmodel->updateQuery('staff_hdmf_loan', array('hdmf_loan_id' => $id), array('hdmf_loan_status' => $status) );

				 $ntexts = 'Update on your Pag-IBIG loan application. Click <a href="'.$this->config->base_url().'hdmf/'.$id.'">here</a> to view the application';
				 if( $msg ){
				 	$this->commonM->addMyNotif( $this->input->post('empID'), $ntexts, 5, 1);		
				 }
				 
			}
		}

		$data_query = $this->dbmodel->getQueryResults('staff_hdmf_loan', 'staff_hdmf_loan.*, CONCAT(fname, " ", lname) AS "applicant", username', 1, 'LEFT JOIN staffs ON empID = empID_fk');

		foreach( $data_query as $val ){
			$info = array();
			$data['data_query_'. $val->hdmf_loan_status ]['headers'] = array('applicant', 'loan type', 'status', 'date submitted', 'file');
			$info['applicant'] = $val->applicant;
			$info['loan type'] = $val->hdmf_loan_type;
			$info['date submitted'] = date('F d, Y', strtotime($val->hdmf_loan_date_submitted) );
			$info['status'] = $this->textM->formfield('selectoption', 'hdmf_loan_status', $val->hdmf_loan_status, 'stat_select', '', 'data-id="'.$val->hdmf_loan_id.'" data-empid="'.$val->empID_fk.'"', $data['hdmf_loan_status']);
			//approved loans
			if( $val->hdmf_loan_status == 3 ){
				array_push($data['data_query_'. $val->hdmf_loan_status ]['headers'], 'voucher');
				if( !empty($val->hdmf_loan_voucher_url) ){
					$file_name = pathinfo( $val->hdmf_loan_voucher_url, PATHINFO_FILENAME );
					//$info['voucher'] = '  <a class="iframe" href="'.$this->config->base_url().'uploads/staffs/'.$val->username.'/'.$file_name.'">View uploaded voucher</a>';	
					$info['voucher'] = '  <a class="iframe" href="'.$this->config->base_url().'attachment.php?u='.urlencode($this->textM->encryptText('staffs/'.$val->username)).'&f='.urlencode($this->textM->encryptText($file_name.'.pdf')).'">View uploaded voucher</a>';
				} else {
					$info['voucher'] = '  <a class="iframe" href="'.$this->config->base_url().'hdmf/'.$val->hdmf_loan_id.'/?a=upload">Upload voucher</a>';
				}				
			}
			//salary deductions
			if( $val->hdmf_loan_status == 4 ){
				array_push($data['data_query_'. $val->hdmf_loan_status ]['headers'], 'voucher');
				array_push($data['data_query_'. $val->hdmf_loan_status ]['headers'], 'action');
				if( !empty($val->hdmf_loan_voucher_url) ){
					
					$file_name = pathinfo( $val->hdmf_loan_voucher_url, PATHINFO_FILENAME );
					//$info['voucher'] = '  <a class="iframe" href="'.$this->config->base_url().'uploads/staffs/'.$val->username.'/'.$file_name.'">View uploaded voucher</a>';	
					$info['voucher'] = '  <a class="iframe" href="'.$this->config->base_url().'attachment.php?u='.urlencode($this->textM->encryptText('staffs/'.$val->username)).'&f='.urlencode($this->textM->encryptText($file_name.'.pdf')).'">View uploaded voucher</a>';
				} else {
					$info['voucher'] = '  <img src="'.$this->config->base_url().'css/images/404-error-sign.jpg" style="width: 30px; height: 30px;" />';
				}					
				$info['action'] = ' <a class="iframe" href="'.$this->config->base_url().'hdmf/'.$val->hdmf_loan_id.'/?a=accounting">Payroll Setting</a>';
			}

			//done
			if( $val->hdmf_loan_status == 5 ){
				array_push($data['data_query_'.$val->hdmf_loan_status]['headers'], 'voucher');
				if( !empty($val->hdmf_loan_voucher_url) ){
					
					$file_name = pathinfo( $val->hdmf_loan_voucher_url, PATHINFO_FILENAME );
					//$info['voucher'] = '  <a class="iframe" href="'.$this->config->base_url().'uploads/staffs/'.$val->username.'/'.$file_name.'">View uploaded voucher</a>';	
					$info['voucher'] = '  <a class="iframe" href="'.$this->config->base_url().'attachment.php?u='.urlencode($this->textM->encryptText('staffs/'.$val->username)).'&f='.urlencode($this->textM->encryptText($file_name.'.pdf')).'">View uploaded voucher</a>';
				} else {
					$info['voucher'] = '  <img src="'.$this->config->base_url().'css/images/404-error-sign.jpg" style="width: 30px; height: 30px;" />';
				}
			}

			if( $val->hdmf_loan_status < 3 AND ($key = array_search( array('voucher', 'action'), $data['data_query_'.$val->hdmf_loan_status]['headers'] ) !== false ) ){
				unset( $data['data_query_'. $val->hdmf_loan_status ]['headers'][$key] );
			}

			$info['file'] = '<a class="iframe" href="'.$this->config->base_url().'hdmf/'.$val->hdmf_loan_id.'"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a>';

			$data['data_query_'. $val->hdmf_loan_status ][] = $info;
		}
		
		//$this->textM->aaa($data);
		$this->load->view('includes/template', $data);
	}

	//survey
	public function surveys(){
		$data['content'] = 'misc/benefits_survey';


		if( $this->input->post('submit') ){

			$insert_array['empID_fk'] = $this->user->empID;
			$insert_array['answers'] = json_encode($_POST);
			$insert_array['date_submitted'] = date('Y-m-d H:i:s');
			$this->dbmodel->insertQuery( 'staffSurveyResults', $insert_array );
			$data['success'] = true;

		}

		$this->load->view('includes/templatecolorbox', $data);
	}
	//end survey

	public function survey_result(){

		$data['content'] = 'misc/benefits_survey_result';

		$data['survey_results'] = $this->dbmodel->getSQLQueryResults('SELECT * FROM staffSurveyResults');

		$frequencies = $this->config->item('frequencies');
		$questions = $this->config->item('questions');
		$maxicare_rating = $this->config->item('maxicare_rating');
		$ratings = $this->config->item('ratings');
		$second_questions = $this->config->item('second_questions');

		$frequency_result = [];
		$maxicare_rating_result = [];
		$all_comments = [];
		$satisfaction_result = [];
		$suggestions = [];
		$counter = 0;
		foreach( $data['survey_results'] as $results ){
			$result = json_decode( $results->answers );
			
			
			foreach( $questions as $qKey => $question ){
				foreach( $frequencies as $key => $frequency ){
					$key_name = $question['name'].'_frequency';
					if( isset($result->$key_name) AND $result->$key_name == $key ){
						$frequency_result[ $qKey ][ $key ] ++;	
					}					
				}				
			}

			foreach( $maxicare_rating as $mkey => $mrating ){
				if( $result->maxicare_rating == $mkey ){
					$maxicare_rating_result[ $mkey ] ++;
				}
			}

			foreach( $second_questions as $sKey => $second_question ){
				foreach( $ratings as $rating ){

					$qname = 'satisfaction_'.$second_question['name'];
					if( isset($result->$qname) AND $result->$qname === $rating ){
						
						$satisfaction_result[ $sKey ][ $rating ] ++;
					}
				}
				$cname = 'comments_'.$second_question['name'];
				
				if( !empty($result->$cname) ){
					$all_comments[ $sKey ][] = $result->$cname;	
					//array_push($all_comments[$sKey], $result->$cname);
				}
				
			}

			$suggestions[$counter] = $result->suggestion;
			
			$counter++;

		}
		//dd($all_comments,false);
		$data['frequency_result'] = $frequency_result;
		$data['maxicare_rating_result'] = $maxicare_rating_result;
		$data['all_comments'] = $all_comments;
		$data['satisfaction_result'] = $satisfaction_result;
		$data['suggestions'] = $suggestions;
		$data['label_frequencies'] = $this->config->item('frequencies');
		$data['label_questions'] = $this->config->item('questions');
		$data['label_maxicare_rating'] = $this->config->item('maxicare_rating');
		$data['label_ratings'] = $this->config->item('ratings');
		$data['label_second_questions'] = $this->config->item('second_questions');
		$data['suggestions'] = $suggestions;
		
		$this->load->view('includes/template', $data);
		
	}
	public function results(){
		if( !$this->user ){
			show_404();
			return true;
		}
		$data['results'] = $this->dbmodel->getQueryResults('exams', '*');
		$data['content'] = 'exams/results';

		if( $id = $this->uri->segment(3) ){
			$data['questions'] = $this->textM->questions();
			$data['answer_key'] = $this->textM->answers();
			$data['results'] = $this->dbmodel->getQueryResults('exams', '*', 'id = '. $id)[0];
			$data['content'] = 'exams/result';			
		}

		
		$this->load->view('includes/template', $data);
	}	
	
} //end class



