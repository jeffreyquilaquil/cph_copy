<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Staff extends CI_Controller {
 
	public function __construct(){
		parent::__construct();
		$this->db = $this->load->database('default', TRUE);
		$this->ptDB = $this->load->database('projectTracker', TRUE);
		$this->load->model('Staffmodel', 'staffM');	
		date_default_timezone_set("Asia/Manila");
		
		$this->user = $this->staffM->getLoggedUser();
		if($this->user!=false){
			$this->myaccess = explode(',',$this->user->access);
		}
		else $this->myaccess = array();
	}
		
	public function index(){
		$data['content'] = 'index';	
		$data['row'] = $this->user;
		$data['announcement'] = stripslashes($this->staffM->getSingleField('staffAnnouncements', 'announcement', '1 ORDER BY timestamp DESC'));
		
		if(isset($_POST) && !empty($_POST)){
			if($_POST['submitType']=='announcement'){
				$this->staffM->insertQuery('staffAnnouncements', array('announcement'=>addslashes($_POST['aVal']), 'createdBy'=>$this->user->empID));
				$this->staffM->addMyNotif($this->user->empID, 'You created new announcement.', 5);
				exit;
			}else if($_POST['submitType']=='updateAnn'){
				$aID = $this->staffM->getSingleField('staffAnnouncements', 'aID', '1 ORDER BY timestamp DESC');
				
				$this->staffM->updateQuery('staffAnnouncements', array('aID'=>$aID), array('announcement'=>addslashes($_POST['aVal']), 'updatedBy'=>$this->user->empID.'|'.date('Y-m-d H:i:s')));
				$this->staffM->addMyNotif($this->user->empID, 'You updated announcement.', 5);
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
					$this->staffM->addMyNotif($this->user->empID, 'You updated your profile picture.', 5);
				}else{
					echo '<script>alert("Invalid file type. Please upload .jpg file only");</script>';
				}
			}
		}
		$this->load->view('includes/template', $data);	
	}
	
	public function hello(){
		if(isset($_POST) && $_POST['username'] !=''){
			$gg = $this->staffM->getSingleInfo('staffs', 'empID, username', 'username="'.$_POST['username'].'"');
		}else{
			$gg = $this->staffM->getSingleInfo('staffs', 'empID, username', 'username="'.$this->uri->segment(2).'"');
		}
	
		if(isset($gg->empID)){
			$this->session->set_userdata('uid',$gg->empID);
			$this->session->set_userdata('u',md5($gg->username.'dv'));
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
				$query = $this->staffM->checklogged($username, $pw);
				$row = $query->row();				
				if($query->num_rows()==0){
					$data['error'] = 'Unable to login. Check your login details.';
				}else if($row->password != md5($pw)){
					$data['error'] = 'Invalid password.';
				}else{
					$this->session->set_userdata('uid', $row->empID);			
					$this->session->set_userdata('u', md5($row->username.'dv'));
					$this->session->set_userdata('popupnotification', true);
					
					if($row->username=='lmarinas' || $row->username=='lmarinastest')					
						$this->session->set_userdata('testing', true);
					
					session_start();
					$_SESSION['u'] = $row->username; 
					
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
		$this->session->sess_destroy();
		session_unset(); 
		session_destroy(); 
		header('Location:'.$this->config->base_url());
		exit;
	}
	
	public function forgotpassword(){
		$data['content'] = 'forgotpassword';
		
		if($this->uri->segment(2)!=''){
			$username = $this->staffM->getSingleField('staffs', 'username', 'md5(username)="'.$this->uri->segment(2).'"');
			if($username!=''){
				$this->staffM->updateQuery('staffs', array('username'=>$username), array('password'=>md5($username)));
				echo '<script>alert("Password reset the same as your username."); window.location.href="'.$this->config->base_url().'"</script>';
				exit;
			}
		}
		
		if(isset($_POST) && !empty($_POST)){
			$username = '';
			$type = '';
			if(!empty($_POST['username'])){
				$type = 'username';
				$info = $this->staffM->getSingleInfo('staffs', 'username, email, fname', 'active=1 AND username="'.trim($_POST['username']).'"');
			}else if(!empty($_POST['email'])){
				$type = 'email';
				$info = $this->staffM->getSingleInfo('staffs', 'username, email, fname', 'active=1 AND email="'.trim($_POST['email']).'"');
			}
			
			if(count($info)==0){
				echo ucfirst($type).' "'.$_POST[$type].'" is not found.';
			}else{
				$body = '<p>Hi '.$info->fname.',</p>
						<p>Click <a href="'.$this->config->base_url().'forgotpassword/'.md5($info->username).'/'.'">here</a> to reset password.</p>
						<p><br/></p>
						<p>Thanks!</p>
						<p>CareerPH</p>
				';
				$this->staffM->sendEmail( 'careers.cebu@tatepublishing.net', $info->email, 'CareerPH Forgot Password', $body, 'CareerPH' );
				echo 'Request to reset password sent. Please check your email address '.$info->email.'.';
			}
			exit;
		}
		
		$this->load->view('includes/templatecolorbox', $data);		
	}
	
	public function manageStaff(){
		$data['content'] = 'manageStaff';
				
		if($this->user!=false){		
			if($this->user->access=='' && $this->user->level==0 && $this->user->is_supervisor==0){
				$data['access'] = false;
			}else{	
				$condition = 'staffs.office="PH-Cebu"';
				if(isset($_POST['includeinactive']) && $_POST['includeinactive']=='on') $condition .= '';
				else $condition .= 'AND staffs.active=1';
			
				if($this->user->access==''){
					$ids = '';
					$myStaff = $this->staffM->getStaffUnder($this->user->empID, $this->user->level);				
					foreach($myStaff AS $m):
						$ids .= $m->empID.',';
					endforeach;
					
					if($ids!=''){
						if($condition!='') $condition .= ' AND ';
						$condition .= 'empID IN ('.rtrim($ids,',').')';		
					}
				}
								
				$flds = 'CONCAT(fname," ",lname) AS name, ';
				if(isset($_POST) && !empty($_POST) && isset($_POST['flds'])){					
					foreach($_POST['flds'] AS $p):
						if($p=='title') $flds .= 'newPositions.title, ';
						else if($p=='active') $flds .= 'staffs.active, ';
						else if($p=='address') $flds .= 'address, city, country, zip, ';
						else if($p=='phone') $flds .= 'phone1, phone2, ';
						else if($p=='supervisor') $flds .= '(SELECT CONCAT(fname," ",lname) AS n FROM staffs ss WHERE ss.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS supervisor, ';
						else $flds .= $p.', ';
					endforeach;
					$flds = rtrim($flds,', ');
					$data['fvalue'] = $_POST['flds'];					
				}else{
					$flds = $flds.'email, newPositions.title, dept';
					$data['fvalue'] = array('email', 'title', 'dept');
				}
				
				if($condition=='') $condition='1';
				
				$data['query'] = $this->staffM->getQueryResults('staffs', 'empID, username, '.$flds, $condition, 'LEFT JOIN newPositions ON posId=position LEFT JOIN orgLevel ON levelID=newPositions.orgLevel_fk', 'lname');
			}
		}
		
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
			if($page=='staffinfo' && $this->user->access=='' && $this->staffM->checkStaffUnderMe($this->uri->segment(2))==false){
				$data['access'] = false;
			}else{		
				$data['column'] = 'withLeft';
				$data['current'] = $page;
				
				if($page=='myinfo'){
					$data['backlink'] = 'myinfo/';
					$segment2 = $this->uri->segment(2);	
					$data['row'] = $this->staffM->getSingleInfo('staffs', 'staffs.*, CONCAT(staffs.fname," ",staffs.lname) AS name, title, dept AS department, (SELECT CONCAT(fname," ",lname) AS sname FROM staffs e WHERE e.empID=staffs.supervisor AND staffs.supervisor!=0) AS supName', 'username="'.$this->user->username.'"', 'LEFT JOIN newPositions ON posID=position');
				}else{
					$data['backlink'] = 'staffinfo/'.$this->uri->segment(2).'/';
					$segment2 = $this->uri->segment(3);				
					$data['row'] = $this->staffM->getSingleInfo('staffs', 'staffs.*, CONCAT(staffs.fname," ",staffs.lname) AS name, title, dept AS department, (SELECT CONCAT(fname," ",lname) AS sname FROM staffs e WHERE e.empID=staffs.supervisor AND staffs.supervisor!=0) AS supName', 'username="'.$this->uri->segment(2).'"', 'LEFT JOIN newPositions ON posID=position');
				}

				if(count($data['row']) > 0){				
					if(isset($_POST) && !empty($_POST)){
						if($_POST['submitType']=='pdetails' || $_POST['submitType']=='jdetails'){
							if($_POST['submitType']=='jdetails'){
								$orig = $this->staffM->getSQLQueryArrayResults('SELECT office, startDate, supervisor, title AS title2, position AS title, empStatus, regDate, endDate, accessEndDate, shift, sal, staffs.active FROM staffs LEFT JOIN newPositions ON posID=position WHERE empID="'.$_POST['empID'].'" LIMIT 1');
							}else{	
								$orig = $this->staffM->getSQLQueryArrayResults('SELECT lname, fname, mname, suffix, pemail, address, city, country, zip, phone1, phone2, bdate, gender, maritalStatus, spouse, dependents, sss, tin, philhealth, hdmf, skype, google FROM staffs WHERE empID="'.$_POST['empID'].'" LIMIT 1');
							}
							
							$what2update = $this->staffM->compareResults($_POST, $orig);					
							if(count($what2update) >0){
								if(count(array_intersect($this->myaccess,array('full','hr')))==0){								
										$upNote = 'You requested an update for:<br/>';
										foreach($what2update AS $k=>$val):
											$r['empID_fk'] = $_POST['empID'];
											$r['fieldname'] = $k;
											$r['fieldvalue'] = $val;
											$r['daterequested'] = date('Y-m-d H:i:s');
											if($_POST['submitType']=='jdetails') $r['isJob'] = 1;
											if($k=='endDate') $r['notes'] = 'Requested by: '.$this->user->name;
											
											if($k!='empStatus')
												$this->staffM->insertQuery('staffUpdated', $r);
											
											$o = $orig[0]->$k;
											if($o=='') $o = 'none';
											
											if($k=='title')
												$upNote .= $this->staffM->defineField($k).' from <i>'.$orig[0]->title2.'</i> to <u>'.$this->staffM->getSingleField('newPositions', 'title', 'posID="'.$val.'"').'</u><br/>';
											else
												$upNote .= $this->staffM->defineField($k).' from <i>'.$o.'</i> to <u>'.$val.'</u><br/>';
										endforeach;
										$upNote .= 'This needs HR approval. Please upload documents on Personal File on My Info page to support the request.';
										$this->staffM->addMyNotif($_POST['empID'], $upNote);								
								}else{
									$empID = $_POST['empID'];
									$submitType = $_POST['submitType'];
									
									unset($_POST['empID']);
									unset($_POST['submitType']);
									
									if(isset($_POST['endDate']) && $_POST['endDate']=='Not yet set') $_POST['endDate'] = '0000-00-00';
									if(isset($_POST['title'])){ $_POST['position'] = $_POST['title']; unset($_POST['title']); }
													
									if(isset($_POST['bdate']) && $_POST['bdate']!='') $_POST['bdate'] = date('Y-m-d', strtotime($_POST['bdate']));
									if(isset($_POST['startDate']) && $_POST['startDate']!='') $_POST['startDate'] = date('Y-m-d', strtotime($_POST['startDate']));
									if(isset($_POST['endDate']) && $_POST['endDate']!='') $_POST['endDate'] = date('Y-m-d', strtotime($_POST['endDate']));
									if(isset($_POST['accessEndDate']) && $_POST['accessEndDate']!='') $_POST['accessEndDate'] = date('Y-m-d', strtotime($_POST['accessEndDate']));
									if(isset($_POST['regDate']) && $_POST['regDate']!='') $_POST['regDate'] = date('Y-m-d', strtotime($_POST['regDate']));
									
									$this->staffM->updateQuery('staffs', array('empID'=>$empID), $_POST);
									
									if((isset($what2update['endDate']) && $what2update['endDate']!='0000-00-00' && $what2update['endDate']<=date('Y-m-d')) || 
										(isset($what2update['accessEndDate']) && $what2update['accessEndDate']!='0000-00-00' && $what2update['accessEndDate']<=date('Y-m-d'))
									){									
										$uInfo = $this->staffM->getSingleInfo('staffs', 'username, CONCAT(fname," ",lname) AS name, fname, office, newPositions.title, shift, endDate, accessEndDate', 'empID="'.$empID.'" AND staffs.active=1', 'LEFT JOIN newPositions ON posID=position');
										if(count($uInfo)>0){										
											//set PT and careerph user inactive
											$this->staffM->ptdbQuery('UPDATE staff SET active="N" WHERE username = "'.$uInfo->username.'"');
											$this->staffM->dbQuery('UPDATE staffs SET active="0" WHERE empID = "'.$empID.'"');
											
											//send email
											$ebody = '<p><b>Employee Separation Notice:</b></p>';
											$ebody .= '<p>Employee: <b>'.$uInfo->name.'</b></p>';
											
											$ebody .= '<p>';
											$ebody .= 'Position: <b>'.$uInfo->title.'</b><br/>';
											
											if(isset($what2update['accessEndDate']) || $uInfo->accessEndDate!='0000-00-00') 
												$ebody .= 'Access End Date: <b>'.date('F d, Y', strtotime($_POST['accessEndDate'])).'</b><br/>';											
											if(isset($what2update['endDate']) || $uInfo->endDate!='0000-00-00')
												$ebody .= 'Separation Date: <b>'.date('F d, Y', strtotime($_POST['endDate'])).'</b><br/>';	
											
											$ebody .= 'Shift: <b>'.$uInfo->shift.'</b><br/>';
											$ebody .= 'Office Branch : <b>'.$uInfo->office.'</b><br/>';											
											$ebody .= '</p>';											
											
											$ebody .= '<p><b>IT Staff:</b> Please terminate this employee\'s access to Email, ProjectTracker, and the phone system on the date of separation. Further, collect any equipment issued or checked out to the employee on their last day of work. Please coordinate with the employee\'s immediate supervisor to establish forwarding of phone and email if applicable.</p>';
											
											$ebody .= '<p>Thanks!</p>';
												
											$this->staffM->sendEmail('hr.cebu@tatepublishing.net', 'helpdesk.cebu@tatepublishing.net', 'Separation Notice for '.$uInfo->name, $ebody, 'Tate Publishing Human Resources (CareerPH)');
										}
									}
									
									if(isset($what2update['active'])){
										if($what2update['active']==1) $this->staffM->ptdbQuery('UPDATE staff SET active="Y" WHERE username = "'.$data['row']->username.'"');
										else $this->staffM->ptdbQuery('UPDATE staff SET active="N" WHERE username = "'.$data['row']->username.'"');
										
										$abody = '<p>Hi,</p>';
										
										if($what2update['active']==1){
											$subject = 'ACTIVATED PT USER';
											$abody .= $this->user->name.' ACTIVATED the account of "'.$data['row']->name.'". Please check if this is correct.';
										}else{
											$subject = 'DEACTIVATED PT USER';
											$abody .= $this->user->name.' DEACTIVATED the account of "'.$data['row']->name.'". Please check if this is correct.';
										}
										
										$abody .= '<p><br/></p>
												<p>Thanks!</p>
												<p>CAREERPH</p>';
										
										$this->staffM->sendEmail('careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', $subject, $abody, 'CAREERPH');
										$this->staffM->sendEmail('careers.cebu@tatepublishing.net', 'helpdesk.cebu@tatepublishing.net', $subject, $abody, 'CAREERPH');
									}
																	
							
									if($submitType=='jdetails')
										$upNote = $this->user->name.' updated your job details:';
									else
										$upNote = $this->user->name.' updated your personal details:';
																		
									foreach($what2update AS $k=>$val):
										if($k=='title'){
											$upNote .= '<br/>'.$this->staffM->defineField($k).' from <i>'.$orig[0]->title2.'</i> to <u>'.$this->staffM->getSingleField('newPositions', 'title', 'posID="'.$val.'"').'</u>';
										}else{
											$upNote .= '<br/>'.$this->staffM->defineField($k).' from <i>'.$orig[0]->$k.'</i> to <u>'.$val.'</u>';
										}
									endforeach;
									$this->staffM->addMyNotif($empID, $upNote, 0, 1);
								}
							}
							exit;
						}else if($_POST['submitType']=='addnote'){
							$this->staffM->addMyNotif($_POST['empID_fk'], $_POST['ntexts'], $_POST['ntype']);
						}else if($_POST['submitType']=='uploadPF'){	
							$err = '';
							if(empty($_FILES['pfilei']['name'])){
								$err = 'No file uploaded.';
							}else if(strlen($_FILES['pfilei']['name'])>100){
								$err = 'Filename is too long. Please upload filename less than 100 characters.';
							}
							
							if($err!=''){
								echo '<script>alert("'.$err.'"); window.location.href="'.$this->config->base_url().$data['backlink'].'";</script>';
							}else{
								$filename = $_FILES['pfilei']['name'];
								$dd = $this->staffM->getQueryArrayResults('staffUploads', 'fileName', 'empID_fk='.$data['row']->empID.' AND isDeleted=0');
								$ddArr = array();
								for($d=0; $d<count($dd); $d++){
									$ddArr[] = $dd[$d]->fileName;
								}
								
								if(in_array($filename, $ddArr)){
									$filename = date('YmdHis').'_'.$filename;
								}
							
								$dir = UPLOAD_DIR.$data['row']->username;
                                if (!file_exists($dir)) {
                                    # $data['dir'] doesn't have value 
                                    # replace with $dir
									mkdir($dir, 0755, true);
									chmod($dir.'/', 0777);
								}
								
								move_uploaded_file($_FILES['pfilei']['tmp_name'], $dir.'/'.$filename);
								$pIns['empID_fk'] = $data['row']->empID;
								$pIns['uploadedBy'] = $this->user->empID;
								$pIns['fileName'] = $filename;
								$pIns['dateUploaded'] = date('Y-m-d H:i:s');
								$this->staffM->insertQuery('staffUploads', $pIns);
								
								//add notifications
								$ciframe = '';
								if(strpos($filename,'.jpg') !== false || strpos($filename,'.png') !== false || strpos($filename,'.gif') !== false || strpos($filename,'.pdf') !== false){
									$ciframe = 'class="iframe"';
								}
								if($data['row']->empID==$this->user->empID){
									$this->staffM->addMyNotif($this->user->empID, 'You uploaded file <a href="'.$this->config->base_url().$dir.'/'.$filename.'" '.$ciframe.'>'.$filename.'</a>');
								}else{
									$ttxt = $this->user->name.' uploaded file <a href="'.$this->config->base_url().$dir.'/'.$filename.'" '.$ciframe.'>'.$filename.'</a>';
									$this->staffM->addMyNotif($data['row']->empID, $ttxt, 0, 1);
								}							
							}
						}else if($_POST['submitType']=='delFile'){
							$this->staffM->updateQuery('staffUploads', array('upID'=>$_POST['upID']), array('isDeleted' => 1, 'deletedBy'=>$this->user->empID, 'dateDeleted'=>date('Y-m-d H:i:s')));

							//add notifications
							if($data['row']->empID==$this->user->empID){
								$this->staffM->addMyNotif($this->user->empID, 'You deleted the file '.$_POST['fileName'], 5);
							}else{
								$ttxt = $this->user->name.' deleted the file '.$_POST['fileName'];
								$this->staffM->addMyNotif($data['row']->empID, $ttxt, 0, 1);
							}
							
							exit;
						}else if($_POST['submitType']=='cancelRequest'){					
							$this->staffM->addMyNotif($data['row']->empID, 'You cancelled your update field request: '.$_POST['fld'].' - '.$_POST['fname'], 5);
							$this->db->where('updateID', $_POST['updateID']);
							$this->db->delete('staffUpdated'); 
							exit;
						}else if($_POST['submitType']=='uLeaveC'){
							$ins['empID_fk'] = $this->user->empID;
							$ins['notes'] = $_POST['note'];
							$ins['daterequested'] = date('Y-m-d H:i:s');
							$ins['isJob'] = 2;
							$this->staffM->insertQuery('staffUpdated', $ins);
							$this->staffM->addMyNotif($this->user->empID, 'You requested to recheck your leave credits.<br/>Your note:'.$_POST['note'], 5);
							exit;
						}else if($_POST['submitType']=='editleavecredits'){
							$this->staffM->updateQuery('staffs', array('empID'=>$_POST['empID']), array('leaveCredits'=>$_POST['newleavecredits']));
							$this->staffM->addMyNotif($_POST['empID'], $this->user->name.' updated your leave credits from '.$_POST['oldleavecredit'].' to '.$_POST['newleavecredits'].'.', 0, 1);
							
							if($this->user->empID!=$_POST['empID']){
								$this->staffM->addMyNotif($this->user->empID, 'You updated leave credits of '.$_POST['empName'].' from '.$_POST['oldleavecredit'].' to '.$_POST['newleavecredits'].'.', 5, 0);
							}
							exit;
						}else if($_POST['submitType']=='uploadPTpicture'){
							require_once('includes/S3.php');
							$file = $_FILES ['PTpicture']['tmp_name'];
							$bucket = 'staffthumbnails';  
							$s3 = new S3(AWSACCESSKEY, AWSSECRETKEY);
							
							$this->staffM->photoResizer($file, $file, $width = 150, $height = 150, $quality = 70, false);
															   
							$input = S3::inputFile($file);
							$new_name = $data['row']->username.'.jpg';
							
							if(S3::getObjectInfo($bucket, $new_name))
								S3::deleteObject($bucket, $new_name);
							S3::putObject($input, $bucket, $new_name, S3::ACL_PUBLIC_READ);	
									
							header("Cache-Control: no-cache, must-revalidate"); 
							header('Location:'.$_SERVER['REQUEST_URI']);
							exit;
						}else if($_POST['submitType']=='editUploadName'){
							$this->staffM->updateQuery('staffUploads', array('upID'=>$_POST['upID']), array('docName'=>$_POST['docName']));
							exit;
						}
					} //end of POST not empty
				
					$data['leaveTypeArr'] = $this->staffM->definevar('leaveType');
					$data['leaveStatusArr'] = $this->staffM->definevar('leaveStatus');
					$data['timeoff'] = $this->staffM->getQueryResults('staffLeaves', '*', 'empID_fk="'.$data['row']->empID.'"','', 'date_requested DESC');
					$data['disciplinary'] = $this->staffM->getQueryResults('staffNTE', 'staffNTE.*, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE issuer=empID AND issuer!=0) AS issuerName', 'empID_fk="'.$data['row']->empID.'" AND status!=2','', 'timestamp DESC');
					
					$data['pfUploaded'] = $this->staffM->getQueryResults('staffUploads', 'staffUploads.*, (SELECT CONCAT(fname," ",lname) FROM staffs WHERE uploadedBy=empID) AS uploader', 'empID_fk="'.$data['row']->empID.'" AND isDeleted=0','', 'dateUploaded DESC');
											
					if($page=='myinfo'){
						$data['updatedVal'] = $this->staffM->getQueryResults('staffUpdated', '*', 'empID_fk="'.$data['row']->empID.'" AND status=0', 'timestamp');
					}
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
	
		if(count(array_intersect($this->myaccess,array('full','hr')))==0){
			$data['access'] = false;
		}else if($this->user!=false){		
			if(isset($_POST) && !empty($_POST)){
				$note = $this->staffM->getSingleField('staffUpdated', 'notes', 'updateID='.$_POST['updateID']);
				if($note!=''){
					$note .= '<br/><hr/>';
				}
				$note .= '['.date('Y-m-d H:i').'] '.$this->user->username.': ';
				
				if($_POST['submitType']=='Update'){
					$this->staffM->updateQuery('staffs', array('empID'=>$_POST['empID']), array($_POST['fieldN'] => $_POST['fieldV']));
					$this->staffM->updateQuery('staffUpdated', array('updateID'=>$_POST['updateID']), array('status'=>1, 'notes'=>$note.'<i>request approved and changed</i>'));
										
					$ntext = $this->user->name.' approved your personal details update request: '.$this->staffM->defineField($_POST['fieldN']).' - '.$_POST['fieldV'];
					$this->staffM->addMyNotif($_POST['empID'], $ntext, 0, 1);
					
					if(($_POST['fieldN']=='endDate' || $_POST['fieldN']=='accessEndDate') && $_POST['fieldV']<=date('Y-m-d') && $_POST['fieldV']!='0000-00-00'){
						$uInfo = $this->staffM->getSingleInfo('staffs', 'username, CONCAT(fname," ",lname) AS name, fname, office, newPositions.title, shift, endDate, accessEndDate', 'username="'.$this->staffM->getSingleField('staffs', 'username', 'empID="'.$_POST['empID'].'"').'" AND staffs.active=1', 'LEFT JOIN newPositions ON posID=position');						
						if(count($uInfo)>0){										
							//set PT and careerph user inactive
							$this->staffM->ptdbQuery('UPDATE staff SET active="N" WHERE username = "'.$uInfo->username.'"');
							$this->staffM->dbQuery('UPDATE staffs SET active="0" WHERE empID = "'.$_POST['empID'].'"');
							
							//send email
							$ebody = '<p><b>Employee Separation Notice:</b></p>';
							$ebody .= '<p>Employee: <b>'.$uInfo->name.'</b></p>';
							
							$ebody .= '<p>';
							$ebody .= 'Position: <b>'.$uInfo->title.'</b><br/>';
								
							if($_POST['fieldN']=='endDate' || $uInfo->endDate!='0000-00-00') $ebody .= 'Separation Date: <b>'.date('F d, Y', strtotime($uInfo->endDate)).'</b><br/>';
							if($_POST['fieldN']=='accessEndDate' || $uInfo->accessEndDate!='0000-00-00') $ebody .= 'Access End Date: <b>'.date('F d, Y', strtotime($uInfo->accessEndDate)).'</b><br/>';
							
							$ebody .= 'Shift: <b>'.$uInfo->shift.'</b><br/>';
							$ebody .= 'Office Branch : <b>'.$uInfo->office.'</b><br/>';											
							$ebody .= '</p>';											
							
							$ebody .= '<p><b>IT Staff:</b> Please terminate this employee\'s access to Email, ProjectTracker, and the phone system on the date of separation. Further, collect any equipment issued or checked out to the employee on their last day of work. Please coordinate with the employee\'s immediate supervisor to establish forwarding of phone and email if applicable.</p>';
							
							$ebody .= '<p>Thanks!</p>';
								
							$this->staffM->sendEmail('hr.cebu@tatepublishing.net', 'helpdesk.cebu@tatepublishing.net', 'Separation Notice for '.$uInfo->name, $ebody, 'Tate Publishing Human Resources (CareerPH)');
						}					
					}						
					exit;
				}else if($_POST['submitType']=='addnote'){								
					$this->staffM->updateQuery('staffUpdated', array('updateID'=>$_POST['updateID']), array('notes'=>$note.'<i>'.$_POST['notes'].'</i>'));
					$data['success'] = true;
				}else if($_POST['submitType']=='disapprove'){
					$this->staffM->updateQuery('staffUpdated', array('updateID'=>$_POST['updateID']), array('status'=>2,'notes'=>$note.'request disapproved <br/><i>'.$_POST['notes'].'</i>'));
					$data['success'] = true;
					
					$ntext = $this->user->name.' disapproved your personal details update request: '.$this->staffM->defineField($_POST['fieldN']).' - '.$_POST['fieldV'].'<br/>Reason: '.$_POST['notes'];
					$this->staffM->addMyNotif($_POST['empID_fk'], $ntext, 0, 1);
				}else if($_POST['submitType']=='sendEmail' || $_POST['submitType']=='sendEmailClose'){
					$this->staffM->sendEmail( 'hr.cebu@tatepublishing.net', $_POST['email'], $_POST['subject'], nl2br($_POST['message']), 'CAREERPH');
					
					$forevermore = $this->user->name.' sent you an email';
					if($_POST['submitType']=='sendEmailClose'){
						$this->staffM->updateQuery('staffUpdated', array('updateID'=>$_POST['updateID']), array('status'=>4,'notes'=>$note.'Closed'));
						$forevermore .= ' and closed your recheck leave request';
					}					
					$this->staffM->addMyNotif($_POST['empID'], $forevermore.'.<br/>Message: <br/>'.$_POST['message'], 0, 1);
					exit;
				}else if($_POST['submitType']=='requestclose'){
					$this->staffM->addMyNotif($_POST['empID'], $this->user->name.' closed your recheck leave request.<br/>Note: '.$_POST['note'], 0, 1);
					$this->staffM->updateQuery('staffUpdated', array('updateID'=>$_POST['updateID']), array('status'=>4,'notes'=>$note.'Closed<br/>Note:'.$_POST['note']));
					exit;
				}
			}		
			if($data['edit']==''){
				$data['row'] = $this->staffM->getQueryResults('staffUpdated', 'staffUpdated.*, staffs.*, (SELECT CONCAT(fname," ",lname) AS name FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS supervisor, (SELECT title FROM newPositions WHERE newPositions.posID=staffs.position AND position!=0 LIMIT 1) AS title', 'status=0 AND isJob<2', 'LEFT JOIN staffs ON empID=empID_fk');
				$data['rowLeave'] = $this->staffM->getQueryResults('staffUpdated', 'staffUpdated.*, staffs.*', 'status=0 AND isJob=2', 'LEFT JOIN staffs ON empID=empID_fk');
			}else if($data['edit']=='disapprove'){
				$data['row'] = $this->staffM->getSingleInfo('staffUpdated', '*', 'updateID='.$data['updateID']);
			}else if($data['edit']=='sendemailleave'){
				$data['email'] = $this->staffM->getSingleField('staffs', 'email', 'empID="'.$this->uri->segment(4).'"');
			}
		}
		
		if($data['edit']=='') $this->load->view('includes/template', $data);
		else $this->load->view('includes/templatecolorbox', $data);
	}
	
	public function issueNTE(){		
		$data['content'] = 'issueNTE';
		if($this->user!=false){			
			$data['ntegenerated']=false;
			
			$data['row'] = $this->staffM->getSingleInfo('staffs', 'empID, username, CONCAT(fname," ",lname) AS name, email, pemail, supervisor, (SELECT CONCAT(fname," ",lname) AS sname FROM staffs e WHERE e.empID=staffs.supervisor AND supervisor!=0 ) AS sName', 'empID="'.$this->uri->segment(2).'"');
			
			//check if you are allowed to issue nte
			if($this->user->access=='' && $this->staffM->checkStaffUnderMe($data['row']->username)==false){
				$data['access'] = false;
			}
			
			$data['prevID'] = $this->staffM->getSingleField('staffNTE', 'nteID', 'empID_fk="'.$data['row']->empID.'" ORDER BY dateissued DESC');
			if($data['prevID']!=''){
				$data['prev'] = $this->staffM->getSingleInfo('staffNTE', '*', 'nteID="'.$data['prevID'].'"');
				if($data['prev']->type=='tardiness') $data['sanctionArr'] = $this->staffM->definevar('sanctiontardiness');
				else $data['sanctionArr'] = $this->staffM->definevar('sanctionawol');
			} 
			$supEmail = $this->staffM->getSingleField('staffs', 'email', 'empID="'.$data['row']->supervisor.'"');
			
			if(isset($_POST['submitType']) && $_POST['submitType']=='issueNTE'){				
				$offensedates = '';
				if($_POST['type']=='AWOL'){					
					foreach($_POST['offensedates'] AS $d):
						if(!empty($d))
							$offensedates .= date('Y-m-d',strtotime($d)).'|';
					endforeach;		
				}else if($_POST['type']=='tardiness'){
					for($i=0; $i<6; $i++){
						if($_POST['tdates'][$i]!=''){
							$offensedates .= date('Y-m-d H:i', strtotime($_POST['tdates'][$i].' '.$_POST['ttime'][$i])).'|';
						}
					}
				}
				
				$ins['offensedates'] = rtrim($offensedates,'|');
				$ins['type'] = $_POST['type'];
				$ins['empID_fk'] = $_POST['empID_fk'];
				$ins['offenselevel'] = $_POST['offenselevel'];
				$ins['dateissued'] = date('Y-m-d H:i:s');
				$ins['issuer'] = $this->user->empID;				
				
				
				$insid = $this->staffM->insertQuery('staffNTE', $ins);
				$data['ntegenerated']=true;
				$data['insid'] = $insid;
				$this->staffM->addMyNotif($_POST['empID_fk'], $this->user->name.' issued an NTE to you.  Please check your disciplinary records or click <a href="'.$this->config->base_url().'ntepdf/'.$insid.'/" class="iframe">here</a> to view the NTE file.', 4, 1);
				$this->staffM->addMyNotif($this->user->empID, 'You issued an NTE to '.$data['row']->name.'. Click <a href="'.$this->config->base_url().'detailsNTE/'.$insid.'/" class="iframe">here</a> to view NTE details page or <a href="'.$this->config->base_url().'ntepdf/'.$insid.'/" class="iframe">here</a> to view PDF the file. ', 5, 0);
				
				$dateplus5 = date('l F d, Y', strtotime('+5 day', strtotime(date('Y-m-d'))));
				$nextMonday = date('l F d, Y', strtotime('next monday', strtotime($dateplus5)));
				
				$to = $data['row']->email.','.$data['row']->pemail;
				$subject = 'A Notice to Explain For '.$data['row']->name;
				$body = '<p>Hello '.$data['row']->name.'</p>
					<p>This is an automatic notification sent to inform you a Notice to Explain is generated for you. See details below:
						<ul>
							<li>Date of issuance: '.date('F d, Y').'</li>
							<li>Offense Number: '.$_POST['offenselevel'].'</li>
							<li>AWOL dates:
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
				if($_POST['offenselevel']<3){
				if($_POST['type']=='tardiness') $data['sanctionArr'] = $this->staffM->definevar('sanctiontardiness');
				else $data['sanctionArr'] = $this->staffM->definevar('sanctionawol');				
				
				$body .= '<p>Note that the Code of Conduct prescribes a sanction of '.$data['sanctionArr'][$_POST['offenselevel']].' to a '.$this->staffM->ordinal($_POST['offenselevel']).' offense of AWOL.</p>					
					<p>You are hereby requested to send an explanation to your immediate supervisor <a href="mailto:'.$supEmail.'">'.$supEmail.'</a> and cc <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a>. Please explain why this happened and why no sanction should be imposed upon you. You are given until '.$dateplus5.' (5 days) to send your explanation to <a href="mailto:'.$supEmail.'">'.$supEmail.'</a> and cc <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a>. Failure to do so will be considered an admission of fault and your waiver of your right to be heard.</p>
					<p>A <b>Corrective Action Report</b> and <b>Notice of Disciplinary Action</b> will naturally follow the Notice of Decision whether or not an explanation is received from you. The management may give a lighter sanction depending on the validity of the explanation that you provided, or in consideration of the frequency in which the offense is repeated and of your overall conduct and performance at work.</p>';
				}else{
				$body .= '<p>Note that 3 or more offenses of AWOL is already TERMINABLE as prescribed by our Code of Conduct.</p>
					<p>You are hereby requested to send an explanation to your immediate supervisor <a href="mailto:'.$supEmail.'">'.$supEmail.'</a> and cc <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a>. Please explain why this happened and why no sanction should be imposed upon you. You are given until '.$dateplus5.' to send your explanation to <a href="mailto:'.$supEmail.'">'.$supEmail.'</a> and cc <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a>.</p>
					<p>On '.$nextMonday.' you are invited for an administrative hearing at the Admin Office where you can further justify your explanation. Failure to send an explanation or attend the administrative hearing shall be considered an admission of fault and your waiver of your right to be heard.</p>
					<p>A <b>Corrective Action Report</b> and <b>Notice of Disciplinary Action</b> will naturally follow the Notice of Decision whether or not an explanation is received from you.</p>';
				}
				$body .= '<p>Please email <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a> should you have any question or concern about this.</p>
					<p>The hard copy will be routed to you for your signature.</p>
					<p><br/></p>
					<p>Yours truly,</p>
					<p><b>The Human Resources Department</b></p>
				';
				
				$this->staffM->sendEmail( 'hr.cebu@tatepublishing.net', $to, $subject, $body, 'The Human Resources Department');
				
			}
			
		}
		$this->load->view('includes/templatecolorbox', $data);
	}
			
	public function ntepdf(){
		if($this->uri->segment(2)!=''){
			$nteID = $this->uri->segment(2);
			$row = $this->staffM->getSingleInfo('staffNTE', 'staffNTE.*, CONCAT(fname," ",lname) AS name, username, idNum, staffs.title, supervisor, dept, grp', 'nteID="'.$nteID.'"', 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
			
			if(count($row)==0){
				echo 'No NTE record.';
				exit;
			}
			
			if($row->issuer!=0){
				$sName = $this->staffM->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, username', 'empID="'.$row->issuer.'"');			
			}
						
			require_once('includes/fpdf/fpdf.php');
			require_once('includes/fpdf/fpdi.php');
			
			$pdf = new FPDI();
			$pdf->AddPage();
			$pdf->setSourceFile('includes/pdftemplates/NTE.pdf');
			$tplIdx = $pdf->importPage(1);
			$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
			
			$pdf->SetFont('Helvetica','B',9);
			$pdf->setTextColor(0, 0, 0);
			
			$pdf->setXY(47, 38.7);
			$pdf->Write(0, date('l, F d, Y', strtotime($row->dateissued)));	
			
			$pdf->setXY(47, 42.8);
			$pdf->Write(0, $row->name);	
			
			if(isset($sName->name)){
				$pdf->setXY(47, 51.5);
				$pdf->Write(0, $sName->name);	
			}
			
			$pdf->setXY(47, 47);
			$pdf->Write(0, 'The Human Resource Department');	
			
			$pdf->setTextColor(255, 0, 0);
			$pdf->setXY(29, 92);		
			$pdf->Write(0, $this->staffM->ordinal($row->offenselevel).' Offense');			
			$pdf->setXY(29, 152);
			$pdf->Write(0, $this->staffM->ordinal($row->offenselevel).' Offense');	
			
			$pdf->setTextColor(0, 0, 0);
			$pdf->SetFont('Helvetica','',9);
			
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
			$pdf->setXY(33, 217);
			$pdf->Write(0, strtoupper($sName->name));	
			$pdf->SetFont('Helvetica','',9);
			$pdf->setXY(115, 217);
			$pdf->Write(0, date('l, F d, Y', strtotime($row->dateissued)));	
			if(file_exists(UPLOAD_DIR.$sName->username.'/signature.png')){
				$pdf->Image(UPLOAD_DIR.$sName->username.'/signature.png', 40, 205, 0);
			}
			
			//received by
			if($row->responsedate!='0000-00-00 00:00:00'){
				$pdf->SetFont('Helvetica','B',9);
				$pdf->setXY(33, 239);
				$pdf->Write(0, strtoupper($row->name));
				$pdf->SetFont('Helvetica','',9);
				$pdf->setXY(115, 239);
				$pdf->Write(0, date('l, F d, Y', strtotime($row->responsedate)));	
				if(file_exists(UPLOAD_DIR.$row->username.'/signature.png')){
					$pdf->Image(UPLOAD_DIR.$row->username.'/signature.png', 40, 227, 0);
				}				
			}
			
			if($row->status==0){
				$firstlevelmngr = $this->staffM->getSingleInfo('staffs', 'username, CONCAT(fname," ",lname) AS eName, title, supervisor', 'empID="'.$row->supervisor.'"');
				if($row->type=='tardiness') $sanctionArr = $this->staffM->definevar('sanctiontardiness');
				else $sanctionArr = $this->staffM->definevar('sanctionawol');

				$secondlevelmngr = '';
				if(isset($firstlevelmngr->supervisor))
					$secondlevelmngr = $this->staffM->getSingleField('staffs', 'CONCAT(fname," ",lname) AS eName', 'empID="'.$firstlevelmngr->supervisor.'"');
									
				$thru = $this->staffM->getSingleInfo('staffs', 'username, CONCAT(fname," ",lname) AS name', 'empID="'.$row->carissuer.'"');
				
				if($row->offenselevel>3) $sanction = 'Termination';
				else $sanction = $sanctionArr[$row->offenselevel];

				$nlevel = $row->offenselevel + 1;
				if($nlevel>3) $nextsanction = 'Termination';
				else $nextsanction = $sanctionArr[$nlevel];
			
				$pdf->AddPage();
				$tplIdx = $pdf->importPage(2);
				$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
				
				$pdf->SetFont('Arial','B',16);
				$pdf->setXY(132, 24);
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
				$pdf->Write(0, date('l, F d, Y'));	
				$pdf->setXY(132, 35);
				$pdf->MultiCell(150, 4, $row->title ,0,'L',false);
				if(isset($firstlevelmngr->eName) && isset($firstlevelmngr->title)){
					$pdf->setXY(139, 41.5);
					$pdf->Write(0, $firstlevelmngr->eName);	
					$pdf->setXY(147, 45.2);
					$pdf->Write(0, $firstlevelmngr->title);	
				}
				
				$pdf->setXY(25, 63);
				$pdf->Write(0, ucwords($sanction));	
				
				$pdf->setTextColor(255, 0, 0);
				$pdf->setXY(20, 78);		
				$pdf->Write(0, '('.$this->staffM->ordinal($row->offenselevel).' Offense)');
				$pdf->setXY(20, 97);
				$pdf->Write(0, '('.$this->staffM->ordinal($row->offenselevel).' Offense)');
				
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
						
				$pdf->setXY(20, 140);
				$pdf->MultiCell(175, 4, $row->planImp ,0,'L',false);	
						
				$consequences = 'Any subsequent case of tardiness within the next six months will be second case of excessive tardiness and merits '.strtoupper($nextsanction);
				$pdf->setXY(20, 157);
				$pdf->MultiCell(175, 4, $consequences ,0,'L',false);

				$pdf->SetFont('Arial','B',10);
				
				if(isset($firstlevelmngr->eName)){
					$pdf->setXY(20, 196);
					$pdf->Write(0, strtoupper($firstlevelmngr->eName));	
				}			
				/* $pdf->setXY(135, 196);
				$pdf->Write(0, date('F d, Y',strtotime($row->cardate))); 	 */
				
				$pdf->setXY(20, 222.5);
				$pdf->Write(0, strtoupper($secondlevelmngr));	
				/* $pdf->setXY(135, 222.5);
				$pdf->Write(0, date('F d, Y',strtotime($row->cardate))); */ 
								
				
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
				
				$notice = "On ".date('l F d, Y', strtotime($row->dateissued)).", you were given an NTE for ".strtoupper($row->type).". You were given 5 (five) days to respond and you were invited for an administrative hearing on ".$nextMonday.". ";
				if(!empty($row->response) && $row->satisfactory==1){
					$notice .= "The response you provided on ".date('l F d, Y', strtotime($row->responsedate))." was found to be considerable and satisfactory.";
				}else if(!empty($row->response) && $row->satisfactory==0){
					$notice .= "However, the response you provided on ".date('l F d, Y', strtotime($row->responsedate))." was found to be unsatisfactory.";
				}else{
					$notice .= "However, as of ".date('l F d, Y', strtotime($row->cardate)).", no response is received from you.";
				}
				
				$notice .= "\n\nThe gravity of misconduct committed by you is such that it warrants ".$sanction;
				
				if (strpos($row->sanction,'Suspension') !== false) {
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
						$notice .= " and we regret to inform you that your continued unauthorized absence despite repeated reminder and reprimand has amounted to a gross, habitual, and deliberate neglect of duty and of the company’s code of conduct.\n\nYou were invited for an administrative hearing on ".$nextMonday." and below is what transpired during the administrative hearing:\n\n".$row->reasonsanction."\n\nFor this reason the company has decided to TERMINATE your services effective on ".date('l F d, Y', strtotime($row->suspensiondates)).". Your last day of employment is on ".date('l F d, Y', strtotime('-1 day',strtotime($row->suspensiondates))).".";
					}
				}else{
					$notice .= " however the management has decided to take on a more lenient view on this matter and instead give you a sanction of ".$dsanc." after consideration of the below reason:\n\n".$row->reasonsanction;
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
				$pdf->setXY(20, 197);
				$pdf->Write(0, strtoupper($thru->name));	
				$pdf->setXY(135, 197);
				$pdf->Write(0, date('F d, Y', strtotime($row->cardate)));	
				if(file_exists(UPLOAD_DIR.$thru->username.'/signature.png'))
					$pdf->Image(UPLOAD_DIR.$thru->username.'/signature.png', 25, 184, 0);				
				
				$pdf->setXY(20, 223);
				$pdf->Write(0, strtoupper($row->name));	
				/* $pdf->setXY(135, 223);
				$pdf->Write(0, date('F d, Y'));	 */
			}
				
			if($this->uri->segment(3)!='')
				$pdf->Output('NTE_'.$nteID.'.pdf', $this->uri->segment(3));	
			else
				$pdf->Output('NTE_'.$nteID.'.pdf', 'I');		
		}else{
			echo 'No NTE ID.';
		}
	}
	
	public function detailsNTE(){
		$data['content'] = 'detailsNTE';
		if($this->user!=false){				
			$nteID = $this->uri->segment(2);
			
			$data['row'] = $this->staffM->getSingleInfo('staffNTE', 'staffNTE.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=issuer AND issuer!=0) AS issuerName, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=carissuer AND carissuer!=0) AS carName', 'nteID="'.$nteID.'"', 'LEFT JOIN staffs ON empID=empID_fk');
			
			if($data['row']->type=='tardiness') $data['sanctionArr'] = $this->staffM->definevar('sanctiontardiness');
			else $data['sanctionArr'] = $this->staffM->definevar('sanctionawol');
			
			if(isset($_POST) && !empty($_POST)){
				if($_POST['submitType']=='aknowledge'){
					$this->staffM->updateQuery('staffNTE', array('nteID'=>$nteID), array('response' => addslashes($_POST['response']), 'responsedate'=>date('Y-m-d H:i:s')));
					$this->staffM->addMyNotif($this->user->empID, 'You acknowledged the NTE issued to you. Click <a href="'.$this->config->base_url().'detailsNTE/'.$nteID.'/" class="iframe">here</a> to view details.', 5);
					header('Location:'.$_SERVER['REQUEST_URI']);
					exit;
				}else if($_POST['submitType']=='cancel'){
					$this->staffM->updateQuery('staffNTE', array('nteID'=>$this->uri->segment(2)), array('status' => '2', 'canceldata'=>$this->user->empID.'|'.date('Y-m-d H:i').'|'.$_POST['cancelv']));
					
					//add notifications
					$this->staffM->addMyNotif($data['row']->empID_fk, $this->user->name.' cancelled the NTE issued to you. Click <a href="'.$this->config->base_url().'detailsNTE/'.$data['row']->nteID.'/" class="iframe">here</a> to view info.', 4, 1);
					$this->staffM->addMyNotif($this->user->empID, 'You cancelled the NTE you issued to '.$data['row']->name.'. Click <a href="'.$this->config->base_url().'detailsNTE/'.$data['row']->nteID.'/" class="iframe">here</a> to view info.',5);
					
					if($this->user->username != $data['row']->issuer){
						$carID = $this->staffM->getSingleField('staffs', 'empID', 'username="'.$data['row']->issuer.'"');
						$this->staffM->addMyNotif($carID, $this->user->name.' cancelled the NTE you issued to '.$data['row']->name.'. Click <a href="'.$this->config->base_url().'ntepdf/'.$data['row']->nteID.'/" class="iframe">here</a> to view info.', 0, 1);
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
											
					$this->staffM->updateQuery('staffNTE', array('nteID'=>$_POST['nteID']), $upArr);
					
					if($upArr['status']==3){
						$ntx = $this->user->name.' approved your NTE response. No CAR generated.';
						$ntxu = 'You approved the NTE response of '.$data['row']->name.'. No CAR generated.';
					}else{
						$ntx = $this->user->name.' issued a Corrective Action Report to you.';
						$ntxu = 'You issued a Corrective Action Report to '.$data['row']->name.'.';
					}
					
					$this->staffM->addMyNotif($data['row']->empID_fk, $ntx.' Please check your disciplinary records or click <a href="'.$this->config->base_url().'detailsNTE/'.$_POST['nteID'].'/" class="iframe">here</a> to view the NTE details.', 4, 1);
					$this->staffM->addMyNotif($this->user->empID, $ntxu.' Click <a href="'.$this->config->base_url().'detailsNTE/'.$_POST['nteID'].'/" class="iframe">here</a> to view the NTE details.', 5);
					
					header('Location:'.$_SERVER['REQUEST_URI']);
					exit;
				}
			}
						
			//check if you are allowed to issue nte
			if($this->user->access=='' && $this->staffM->checkStaffUnderMe($data['row']->username)==false){
				$data['access'] = false;
			}
		}
	
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function adminsettings(){
		$data['content'] = 'adminsettings';
		
		if(!in_array('full',$this->myaccess)){
			$data['access'] = false;
		}else if($this->user!=false){	
			$id = $this->uri->segment(2);
			$data['updated'] = '';
			if(isset($_POST) && !empty($_POST)){
			
				if($_POST['submitType']=='accesstype'){
					$actext = '';
					if(isset($_POST['access'])){
						for($i=0; $i<count($_POST['access']); $i++){
							$actext .= $_POST['access'][$i].',';
						}
					}
					
					$this->staffM->updateQuery('staffs', array('empID'=>$id), array('access' => rtrim($actext,',')));
					$data['updated'] = 'Access type successfully submitted.';
				}
			}
			
			$data['row'] = $this->staffM->getSingleInfo('staffs', 'access, CONCAT(fname," ",lname) AS name', 'empID="'.$id.'"');
		}
	
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function detailsnotifications(){	
		$data['content'] = 'detailsnotifications';	
		if($this->user!=false){			
			if(isset($_POST) && !empty($_POST)){
				$this->staffM->updateQuery('staffMyNotif', array('notifID'=>$_POST['notifID']), array('isNotif'=>0));
				exit;
			}
			
			$data['row'] = $this->staffM->getQueryResults('staffMyNotif', '*', 'isNotif=1 AND empID_fk="'.$this->user->empID.'"', '', 'nstatus DESC, notifID DESC');
		}
	
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function nteissued(){
		$data['content'] = 'nteissued';
		
		if($this->user!=false){
			if($this->user->access=='' && $this->user->level==0 && $this->user->is_supervisor==0){
				$data['access'] = false;
 			}else{		
				$data['nte'] = $this->staffM->getQueryResults('staffNTE', 'staffNTE.*, username, CONCAT(fname," ",lname) AS name, (SELECT CONCAT(fname," ",lname) AS iname FROM staffs e WHERE e.empID=staffNTE.issuer LIMIT 1) AS issuerName', 'status=1', 'LEFT JOIN staffs ON empID=empID_fk', 'dateissued DESC');
				$data['ntewcar'] = $this->staffM->getQueryResults('staffNTE', 'staffNTE.*, username, CONCAT(fname," ",lname) AS name, (SELECT CONCAT(fname," ",lname) AS iname FROM staffs e WHERE e.empID=staffNTE.issuer LIMIT 1) AS issuerName, (SELECT CONCAT(fname," ",lname) AS iname FROM staffs e WHERE e.empID=staffNTE.carissuer LIMIT 1) AS carName', 'status=0', 'LEFT JOIN staffs ON empID=empID_fk', 'dateissued DESC');
			}
		}
		$this->load->view('includes/template', $data);
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
				$data['numOffset'] = $this->staffM->getQueryResults('staffLeaves', 'totalHours', 'empID_fk="'.$this->user->empID.'" AND date_requested LIKE "'.date('Y-m-d').'%" AND iscancelled=0 AND status!=3 AND leaveType=4');
			}else{
				$data['numLeaves'] = $this->staffM->getQueryResults('staffLeaves', 'leaveID', 'empID_fk="'.$this->user->empID.'" AND date_requested LIKE "'.date('Y-m-d').'%" AND iscancelled=0 AND status!=3');
			}
									
			if(isset($_POST) && !empty($_POST)){
				if($_POST['submitType']=='chooseFromUploaded'){
					$upFiles = $this->staffM->getQueryResults('staffUploads', 'upID, docName, fileName', 'empID_fk="'.$this->user->empID.'" AND isDeleted=0');
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
					for($u=0; $u<count($_POST['offdate']); $u++){
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
					
					$tambal = 0;
					foreach($data['numOffset'] AS $noffset):
						$tambal += $noffset->totalHours;
					endforeach;
					
					if(($tambal+$cnthrs)>16) $data['errortxt'] .= 'You cannot file offset leave more than 16 hours in a month.<br/>';
					
					if($offdatecheck==false) $data['errortxt'] .= 'Check your schedule of work to compensate<br/>';
					if(empty($offdates)) $data['errortxt'] .= 'Schedule of work to compensate absence is empty<br/>';
					
				}else{					
					if(empty($_POST['leaveStart'])) $data['errortxt'] .= 'Start Date and Time of Absence is empty<br/>';
					if(empty($_POST['leaveEnd'])) $data['errortxt'] .= 'End Date and Time of Absence is empty<br/>';
					
					if(isset($_POST['code']) && !empty($_POST['code'])){
						$code = $this->staffM->getSingleInfo('staffCodes', 'codeID, generatedBy', 'code="'.$_POST['code'].'" AND usedBy=0 AND status=1');
						if(count($code)==0) $data['errortxt'] = 'Code is invalid or already used.<br/>';
					}
					
					$dtoday = date('F d, Y H:i');					
					if(!empty($_POST['leaveStart']) && !empty($_POST['leaveEnd'])){
						if(strtotime($_POST['leaveStart']) > strtotime($_POST['leaveEnd'])){
							$data['errortxt'] .= 'End of Leave is less than start of leave.<br/>';
						}elseif(strtotime($_POST['leaveStart']) == strtotime($_POST['leaveEnd'])){
							$data['errortxt'] .= 'The start and end of your leave is the same. Please correct.<br/>';
						}else{							
							if($_POST['leaveType']==1){
								$vdate = date('F d, Y H:i', strtotime('+13 days'));
																
								if(strtotime($_POST['leaveStart']) < strtotime($vdate) && (!isset($code) || (isset($code) && count($code)==0))){
									$data['errortxt'] .= 'You cannot file vacation leave less than 2 weeks. Enter code to bypass this condition.<br/>';
								}
								if($_POST['totalHours']>40){
									$data['errortxt'] .= 'The maximum number of days per leave application is five (5) days or forty (40) hours.';
								}
							}else if($_POST['leaveType']==2 || $_POST['leaveType']==3){
								if(strtotime(date('Y-m-d', strtotime($_POST['leaveStart']))) >= strtotime(date('Y-m-d')) && (!isset($code) || (isset($code) && count($code)==0)))
									$data['errortxt'] .= 'Invalid Start of Leave. Sick/Emergency leave cannot be filed in advance. Enter code to bypass this condition.<br/>';
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
						
						$patHours = $this->staffM->getQueryArrayResults('staffLeaves', 'totalHours', 'leaveType=5 AND status=1 AND iscancelled=0 AND reason="'.$_POST['reason'].'" AND empID_fk="'.$this->user->empID.'" AND leaveStart>"'.$validYear.'"');
											
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
				if(empty($data['errortxt'])){
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
					
					if(isset($_POST['code']) && !empty($_POST['code']) && isset($code) && count($code)>0){
						$insArr['code'] = $_POST['code'];
						$this->staffM->updateQuery('staffCodes', array('codeID'=>$code->codeID), array('usedBy'=>$this->user->empID, 'dateUsed'=>date('Y-m-d H:i:s'), 'type'=>'Leave', 'status'=>2));
						$this->staffM->addMyNotif($code->generatedBy, $this->user->name.' used your generated code '.$_POST['code'].' for filing leave.', 0, 1);
					}
					
					if(isset($_POST['fromUploaded']) && !empty($_POST['fromUploaded']))
						$insArr['supDocs'] = $_POST['fromUploaded'];
									
					$insID = $this->staffM->insertQuery('staffLeaves', $insArr);
					
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
							$this->staffM->updateConcat('staffLeaves', 'leaveID="'.$insID.'"', 'supDocs', $insDoc);
						}
					}
									
					$leaveTypeArr = $this->staffM->definevar('leaveType');
					//add notes to employee
					$this->staffM->addMyNotif($this->user->empID, 'You filed '.$leaveTypeArr[$_POST['leaveType']].' for '.date('F d, Y h:i a', strtotime($_POST['leaveStart'])).' to '.date('F d, Y h:i a', strtotime($_POST['leaveEnd'])).'. Click <a class="iframe" href="'.$this->config->base_url().'staffleaves/'.$insID.'/">here</a> for details.', 3);
					
					//add notes to supervisors					
					$ntexts = $this->user->name.' files '.$leaveTypeArr[$_POST['leaveType']].' for '.date('F d, Y h:i a', strtotime($_POST['leaveStart'])).' to '.date('F d, Y h:i a', strtotime($_POST['leaveEnd'])).'. Check Manage Staff > Staff Leaves page, view <a href="'.$this->config->base_url().'staffleaves/'.$insID.'/" class="iframe">leave details page</a> or click <a href="'.$this->config->base_url().'leavepdf/'.$insID.'/" class="iframe"> here</a> to view the file.';
						
					$superID = $this->staffM->getStaffSupervisorsID($this->user->empID);
					for($s=0; $s<count($superID); $s++){
						$this->staffM->addMyNotif($superID[$s], $ntexts, 0, 1);
					}
										
					$data['submitted'] = true;
				}					
			}
		}
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function leavepdf(){
		if($this->uri->segment(2)!=''){
			$leave = $this->staffM->getSingleInfo('staffLeaves', 'staffLeaves.*, CONCAT(fname," ",lname) AS name, username', 'leaveID="'.$this->uri->segment(2).'"', 'LEFT JOIN staffs ON empID_fk=empID');
			
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
		$data['content'] = 'staffleaves';
				
		if($this->user!=false){
			if($this->user->access=='' && $this->user->level==0 && $segment2=='' && $this->user->is_supervisor==0){
				$data['access'] = false;
			}else{
				$data['leaveTypeArr'] = $this->staffM->definevar('leaveType');
				$data['leaveStatusArr'] = $this->staffM->definevar('leaveStatus');
				if($segment2 != ''){
					$data['content'] = 'staffleavesedit';				
									
					$data['row'] = $this->staffM->getSingleInfo('staffLeaves', 'staffLeaves.*, username, fname, CONCAT(fname," ",lname) AS name, email, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor LIMIT 1) AS supName,(SELECT email FROM staffs e WHERE e.empID=staffs.supervisor LIMIT 1) AS supEmail, leaveCredits, empStatus', 'leaveID="'.$segment2.'"', 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
					
					$data['leaveHistory'] = $this->staffM->getQueryResults('staffLeaves', 'leaveID, leaveType, leaveStart, leaveEnd, status, iscancelled, totalHours', 'empID_fk="'.$data['row']->empID_fk.'" AND leaveID!="'.$segment2.'"');
										
					if($this->user->access=='' && $this->user->level==0 && $this->user->is_supervisor==0 && $this->user->empID != $data['row']->empID_fk)
						$data['access'] = false;
				
					if(isset($_POST) && !empty($_POST)){
						$updateArr = array();
						$actby = '';
						if($_POST['submitType']=='svisor'){
							$updateArr['status'] = $_POST['approve'];
							$updateArr['approverID'] = $this->user->empID;
							$updateArr['remarks'] = $_POST['remarks'];						
							$updateArr['dateApproved'] = date('Y-m-d');	
							if($data['row']->leaveType!=4 && $data['row']->leaveType!=5)
								$updateArr['leaveCreditsUsed'] = $_POST['leaveCreditsUsed'];	
														
							$usernote = $this->user->name.' <b>'.strtolower($data['leaveStatusArr'][$updateArr['status']]).'</b> your leave request.';
							$actby = 'You <b>'.strtolower($data['leaveStatusArr'][$updateArr['status']]).'</b> '.$data['row']->name.'\'s leave request.';
							if($updateArr['status']!=3){
								$usernote .= ' This is waiting for HR approval.';
							
								$hrnote = $this->user->name.' <b>'.strtolower($data['leaveStatusArr'][$updateArr['status']]).'</b> '.$data['row']->name.'\'s leave request. This is waiting for your approval. <a href="'.$this->config->base_url().'staffleaves/'.$data['row']->leaveID.'/">Click here to take HR action</a>.';	
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
								$this->staffM->sendEmail('careers.cebu@tatepublishing.net', $_POST['toEmail'], $_POST['subjectEmail'], $_POST['message'], 'CareerPH' );
								$this->staffM->updateConcat('staffLeaves', 'leaveID="'.$data['row']->leaveID.'"', 'addInfo', $addInfo);
							}else{
								if($_POST['status']!=$_POST['oldstatus'])
									$updateArr['status'] = $_POST['status'];
								$updateArr['hrapprover'] = $this->user->empID;
								$updateArr['hrremarks'] = $_POST['remarks'];						
								$updateArr['hrdateapproved'] = date('Y-m-d');
								if($data['row']->leaveType!=4 && $data['row']->leaveType!=5)
									$updateArr['leaveCreditsUsed'] = $_POST['leaveCreditsUsed'];
																
								$usernote = 'Updated your '.strtolower($data['leaveTypeArr'][$data['row']->leaveType]).' request';							
								if($_POST['status']!=$_POST['oldstatus']){
									$usernote .= ' from <b>'.$data['leaveStatusArr'][$_POST['oldstatus']].'</b> to <b>'.$data['leaveStatusArr'][$_POST['status']].'</b>';
									$approvernote = 'Updated '.$data['row']->name.'\'s leave request from <b>'.$data['leaveStatusArr'][$_POST['oldstatus']].'</b> to <b>'.$data['leaveStatusArr'][$_POST['status']].'</b>';
									$updateArr['hrremarks'] .= ' - updated '.$data['leaveStatusArr'][$_POST['oldstatus']].' to '.$data['leaveStatusArr'][$_POST['status']];
								}							
								$usernote .= '.';
								
								$approvernote = 'Updated '.$data['row']->name.'\'s '.strtolower($data['leaveTypeArr'][$data['row']->leaveType]).' request. ';
								$actby = 'Updated '.$data['row']->name.'\'s '.strtolower($data['leaveTypeArr'][$data['row']->leaveType]).' request. ';
									
								if($data['row']->leaveType!=4 && $data['row']->leaveType!=5){
									$this->staffM->updateQuery('staffs', array('empID'=>$data['row']->empID_fk), array('leaveCredits'=>$_POST['remaining']));
								}
								
								if($data['row']->status==2 || (isset($_POST['status']) && $_POST['status']==2)){
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
									$this->staffM->sendEmail( 'careers.cebu@tatepublishing.net', 'accounting.cebu@tatepublishing.net', 'Leave Approved Without Pay', $eMsg, 'CareerPH Auto-Email');
								}
								
								$uEmail = '<p>Hi '.$data['row']->fname.',</p>
										<p>HR updated your '.strtolower($data['leaveTypeArr'][$data['row']->leaveType]).' request. Click <a href="'.$this->config->base_url().'staffleaves/'.$data['row']->leaveID.'/">here</a> to view leave details.</p><p><br/></p><p>Thanks!</p><p>CAREERPH</p>';
								$this->staffM->sendEmail( 'careers.cebu@tatepublishing.net', $data['row']->email, 'Update on Leave Request', $uEmail, 'CareerPH Auto-Email');
							}
						}else if($_POST['submitType']=='cancel'){
							$updateArr['cancelReasons'] = $_POST['cancelReasons'];
							$updateArr['datecancelled'] = date('Y-m-d H:i:s');
							
							if($data['row']->approverID==0){
								$updateArr['iscancelled'] = 1;	
								$canceldata = '^_^Cancelled: '.$this->user->username.'|'.date('Y-m-d H:i:s');	
							}else{
								$updateArr['iscancelled'] = 2;
								$approvernote = $this->user->name.' <b>cancelled</b> the leave requested. Please approve cancel request or else it will automatically disapprove if no action from you within 24 hours from the submission of the request.';
								$canceldata = '^_^Pending Cancelled for IS: '.$this->user->username.'|'.date('Y-m-d H:i:s');	
							}
							$actby = 'Cancelled your leave request. ';
						}else if($_POST['submitType']=='cancelApprover'){							
							if($_POST['capprover']==1){
								$usernote = $this->user->name.' <b>approved</b> your cancel request';	
								if($data['row']->hrapprover	!= 0){
									$usernote .= ' and forwarded to HR for changing the schedule and leave credits';
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
								$updateArr['cancelReasons'] = '';
								$updateArr['datecancelled'] = '0000-00-00 00:00:00';
								$canceldata = '^_^Disapproved cancel request: '.$this->user->username.'|'.date('Y-m-d H:i:s');
								$actby = 'You disapproved '.$data['row']->name.'\'s cancel leave request. ';
							}
						}else if($_POST['submitType']=='cancelHRapprove'){	
							$updateArr['iscancelled'] = 1;	
							$canceldata = '^_^Cancel request approved by HR: '.$this->user->username.'|'.date('Y-m-d H:i:s');
							
							if($_POST['leaveCredits'] != 0){
								$updateArr['leaveCreditsUsed'] = 0;
								$this->staffM->updateQuery('staffs', array('empID'=>$data['row']->empID_fk), array('leaveCredits'=>$_POST['leaveCredits']));
								$usernote .= 'Approved your cancel request. Your leave credits is back to '.$_POST['leaveCredits'].'.';
							}
							$actby = 'Approved '.$data['row']->name.'\'s cancel leave request. ';
						}else if($_POST['submitType']=='resubmit'){
							$updateArr['status'] = 0;	
							$actby = 'Resubmit your leave request. ';
						}else if($_POST['submitType']=='uploadSD'){	
							$katski = array_reverse(explode('.', $_FILES['supDocs']['name']));
							$fname = $data['row']->leaveID.'_'.$this->user->empID.'_'.date('YmdHis').'.'.$katski[0]; 
							move_uploaded_file($_FILES['supDocs']['tmp_name'], UPLOADS.'/leaves/'.$fname);	
							
							$this->staffM->dbQuery('UPDATE staffLeaves SET supDocs=CONCAT(supDocs,"'.$fname.'|'.'") WHERE leaveID="'.$data['row']->leaveID.'"');
						}else if($_POST['submitType']=='removeDoc'){							
							$sDoc = str_replace($_POST['fname'].'|', '', $data['row']->supDocs);
							$this->staffM->updateQuery('staffLeaves', array('leaveID'=>$data['row']->leaveID), array('supDocs'=>$sDoc));
							exit;
						}						
						
						$addnote = ' Check <a href="'.$this->config->base_url().'staffleaves/'.$data['row']->leaveID.'/" class="iframe">leave details page</a> or click <a class="iframe" href="'.$this->config->base_url().'leavepdf/'.$data['row']->leaveID.'/">here</a> to view the file.';
							
						if(!empty($usernote))
							$this->staffM->addMyNotif($data['row']->empID_fk, $usernote.$addnote, 0, 1);
							
						if(isset($approvernote) && !empty($approvernote)){
							$this->staffM->addMyNotif($data['row']->approverID, $approvernote.$addnote, 0, 1);
						}
						
						if(isset($hrnote) && !empty($hrnote)){
							$hrStaffs = $this->staffM->getHRStaffID();
							for($hr=0; $hr<count($hrStaffs); $hr++){
								if($hrStaffs[$hr] != $this->user->empID)
									$this->staffM->addMyNotif($hrStaffs[$hr], $hrnote.$addnote, 0, 1);
							}
						}
						
						if(isset($actby) && !empty($actby)){
							$this->staffM->addMyNotif($this->user->empID, $actby.$addnote, 5, 0);
						}
						
						if(isset($canceldata) && !empty($canceldata)){
							$this->staffM->dbQuery('UPDATE staffLeaves SET canceldata=CONCAT(canceldata,"'.$canceldata.'") WHERE leaveID="'.$data['row']->leaveID.'"');
						}
												
						if(count($updateArr)>0){
							$this->staffM->updateQuery('staffLeaves', array('leaveID'=>$data['row']->leaveID), $updateArr);
							header('Location:'.$_SERVER['REQUEST_URI'].'updated/');
						}else{
							header('Location:'.$_SERVER['REQUEST_URI']);
						}
						exit; 
					}
				}else{
					$condition = '';
					if($this->user->access==''){
						$ids = '';
						$myStaff = $this->staffM->getStaffUnder($this->user->empID, $this->user->level);				
						foreach($myStaff AS $m):
							$ids .= $m->empID.',';
						endforeach;
						if($ids!='')
							$condition .= ' AND empID_fk IN ('.rtrim($ids,',').')';
					}
					$dateToday = date('Y-m-d');
					$data['tquery'] = $this->staffM->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'status!=3 AND iscancelled=0 AND ((leaveStart <= "'.$dateToday.'" AND leaveEnd >= "'.$dateToday.'") OR (leaveType=4 AND offsetdates LIKE "%'.$dateToday.'%"))'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
					$data['imquery'] = $this->staffM->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'status=0 AND iscancelled=0'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
					$data['imcancelledquery'] = $this->staffM->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'iscancelled=2'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
					$data['hrquery'] = $this->staffM->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', '(status=1 OR status=2) AND ((iscancelled=0 AND hrapprover=0) OR iscancelled=3 OR iscancelled=4)'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
					
					//for all leaves
					$data['allpending'] = $this->staffM->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', '((status=0 AND iscancelled=0) OR iscancelled>1)'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
					$data['allapproved'] = $this->staffM->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'status=1 AND iscancelled=0'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
					$data['allapprovedNopay'] = $this->staffM->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'status=2 AND iscancelled=0'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
					$data['alldisapproved'] = $this->staffM->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'status=3 AND iscancelled=0'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
					$data['allcancelled'] = $this->staffM->getQueryResults('staffLeaves', 'staffLeaves.*, username, CONCAT(fname," ",lname) AS name, dept, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs e WHERE e.empID=staffs.supervisor) AS supName, (CASE WHEN approverID != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = approverID ) ELSE  "---" END) AS approverName, (CASE WHEN hrapprover != 0 THEN (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID = hrapprover ) ELSE  "---" END) AS hrName', 'iscancelled=1'.$condition, 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');					
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

					$this->staffM->photoResizer($data['dir'].'/signature.png', $data['dir'].'/signature.png', $width = 100, $height = 100, $quality = 70, false);	
					
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
	
	public function myattendance(){
		$data['content'] = 'myattendance';
				
		if($this->user!=false){	
			$sched = $this->staffM->getSingleInfo('staffSchedules', '*', 'empID_fk="'.$this->user->empID.'" AND shiftstart LIKE "'.date('Y-m-d').'%"');
			
			$data['shiftstart'] = '';
			$data['shiftend'] = '';
			if(count($sched)!=0){
				$data['shiftstart'] = $sched->shiftstart;
				$data['shiftend'] = $sched->shiftend;
			}else if(strpos($this->user->shift,'|') !== false){
				list($data['shiftstart'], $data['shiftend']) = explode('|', $this->user->shift); 	
			}
			
			$data['clockedin'] = $this->staffM->getSingleInfo('staffTimelogs', '*', 'empID_fk="'.$this->user->empID.'" AND clockin LIKE "'.date('Y-m-d').'%"');
			
			if($this->uri->segment(2)!=''){
				$data['today'] = $this->uri->segment(2);
			}else{
				$data['today'] = date('Y-m-d');
			}
			
			$data['myattendance'] = $this->staffM->getQueryResults('staffTimelogs', '*', 'empID_fk="'.$this->user->empID.'" AND clockin LIKE "'.date('Y-m', strtotime($data['today'])).'%"');
			
		}
		
		$this->load->view('includes/template', $data);			
	}
	
	public function upsnapshot(){
		if($this->user!=false){	
			$dir = 'uploads/timelogs/'.date('Y').'/';
			if (!file_exists($dir)) {
				mkdir($dir, 0777, true);
				chmod($dir, 0777);
			}
					
			$dir .= date('Y-m-d').'/';
			if (!file_exists($dir)) {
				mkdir($dir, 0777, true);
				chmod($dir, 0777);
			}
			
			$dtoday = date('Y-m-d H:i:s');

			$filename = strtotime($dtoday) . '.jpg';
			$result = file_put_contents($dir.$filename, file_get_contents('php://input') );
			if (!$result) {
				echo "ERROR: Failed to save snapshot. Please relogin.";
			}else{
				chmod($dir.$filename, 0777);
				echo 'You clocked in successfully. Have a great day ahead!';
				
				$this->staffM->insertQuery('staffTimelogs', array('empID_fk'=>$this->user->empID, 'clockin'=>$dtoday));
			}
		}else{
			echo 'Please log in.';
		}
		
		
	}
	
	function deleteFrom(){
		$dir  = 'uploads/staffs/mabellana';
		echo $dir.'<br/>';
		if (is_dir($dir)) {
			$objects = scandir($dir); 
			foreach ($objects as $object) { 
				if ($object != "." && $object != "..") { 
					if (filetype($dir."/".$object) == "dir") 
						rrmdir($dir."/".$object); 
					else{ 
						echo $dir."/".$object."<br/>"; 	
						unlink($dir."/".$object); 
					}  
				} 
			} 
			reset($objects); 
			rmdir($dir); 
		}
	}
	
	public function changepassword(){
		$data['content'] = 'changepassword';
		$segment2 = $this->uri->segment(2);
		if($this->user!=false){
			$data['error'] = '';
			$data['updated'] = false;
			if(isset($_POST) && !empty($_POST)){ 	
				if($this->user->password!=md5($_POST['curpassword']))
					$data['error'] = 'Current password is not correct.<br/>';				
				if($_POST['newpassword'] != $_POST['confirmpassword'])
					$data['error'] .= 'New and confirm passwords does not match.';
				else if($this->user->username==$_POST['newpassword'])
					$data['error'] .= 'Password should not be your username.';
				
				if(empty($data['error'])){	
					$this->staffM->updateQuery('staffs', array('empID'=>$this->user->empID), array('password'=>md5($_POST['newpassword'])));
					$data['updated'] = true;
					unset($_POST);
					
					if($segment2=='required'){
						echo '<script>location.href="'.$this->config->base_url().'"; </script>';
					}
				}
			}			
		}
		if($segment2=='required')
			$this->load->view('includes/template', $data);	
		else
			$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function generatecis(){	
		$data['content'] = 'generatecis';
		$data['updated'] = false;
			
		if($this->user!=false){
			$id = $this->uri->segment(2);	
				
			$data['row'] = $this->staffM->getSingleInfo('staffs', 'CONCAT(fname," ",lname) as name, title, position, office, shift, supervisor, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0) AS supName, org, dept, grp, subgrp, endDate, empStatus, sal', 'empID="'.$id.'" AND position!=0', 'LEFT JOIN newPositions ON posID=position');
						
			if(isset($_POST) && !empty($_POST)){			
				$updatetext = array();
				$updateArr = array();
				if(!empty($_POST['position'])){
					$p = explode('|',$_POST['position']);
					$updateArr['position'] = $p[0];
					$updateArr['title'] = $p[1];
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
				
				if(!empty($_POST['dayfrom']) && !empty($_POST['dayto']) && !empty($_POST['timestarts']) && !empty($_POST['timeends'])){
					$updateArr['shift'] = date('H:s',strtotime($_POST['timestarts'])).'|'.date('H:s',strtotime($_POST['timeends'])).' '.$_POST['dayfrom'].'-'.$_POST['dayto'];
					$updatetext['shift'] = array(
										'c' => $data['row']->shift,
										'n' => date('H:s',strtotime($_POST['timestarts'])).'|'.date('H:s',strtotime($_POST['timeends'])).' '.$_POST['dayfrom'].'-'.$_POST['dayto']
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
										'c' => $data['row']->sal,
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
							'changes' => mysql_real_escape_string(json_encode($updatetext)),
							'dbchanges' => mysql_real_escape_string(json_encode($updateArr)),
							'preparedby' => $this->user->empID
						);
				
				$insid = $this->staffM->insertQuery('staffCIS', $insCIS);
				if($this->uri->segment(3)!=''){
					$wonka = $this->staffM->getSingleInfo('staffUpdated', 'empID_fk, fieldname, fieldvalue, CONCAT(fname," ",lname) AS name', 'updateID="'.$this->uri->segment(3).'"', 'LEFT JOIN staffs ON empID=empID_fk');
					if(count($wonka)>0){
						$this->staffM->updateQuery('staffUpdated', array('updateID'=>$this->uri->segment(3)), array('status'=>3));
						$this->staffM->addMyNotif($wonka->empID_fk, $this->user->name.' generated CIS for your update request:<br/>'.$this->staffM->defineField($wonka->fieldname).' - '.$wonka->fieldvalue.'<br/>. Claim the printed copy of the CIS from '.$this->user->fname.', sign and submit it to HR so they can proceed with the changes.', 0, 1);
						$this->staffM->addMyNotif($this->user->empID, 'You generated a CIS for '.$wonka->name.'. Update requests:<br/>'.$this->staffM->defineField($wonka->fieldname).' - '.$wonka->fieldvalue.'<br/>. Print the CIS and let '.$this->user->fname.'sign and submit it to HR so they can proceed with the changes.', 5);
					}
				}
				header('Location:'.$this->config->base_url().'cispdf/'.$insid.'/');				
				exit;
			}
			
			if(count($data['row'])>0){
				$data['departments'] = $this->staffM->getQueryResults('newPositions', 'posID, title, org, dept, grp, subgrp', '1', '', 'title');
				$data['supervisorsArr'] = $this->staffM->getQueryResults('staffs', 'empID, CONCAT(fname," ",lname) AS name', 'orgLevel_fk>0', 'LEFT JOIN newPositions ON posID=position', 'fname');
				$data['salaryArr'] = $this->staffM->getQueryResults('salaryRange', '*', '1');
			}
			
			if($this->uri->segment(3)!=''){
				$data['wonka'] = $this->staffM->getSingleInfo('staffUpdated', 'empID_fk, fieldname, fieldvalue, CONCAT(fname," ",lname) AS name', 'updateID="'.$this->uri->segment(3).'"', 'LEFT JOIN staffs ON empID=empID_fk');
			}
		}
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function updatecis(){
		$data['content'] = 'generatecis';
		$data['updated'] = true;
		
		if($this->user!=false){
			$cisID = $this->uri->segment(2);
			$data['row'] = $this->staffM->getSingleInfo('staffCIS','staffCIS.*, CONCAT(fname," ",lname) AS name, (SELECT CONCAT(fname," ",lname) AS n FROM staffs ss WHERE ss.empID=staffCIS.preparedby) AS prepName', 'cisID="'.$cisID.'"', 'LEFT JOIN staffs ON empID=empID_fk');
			
			if(isset($_POST) && !empty($_POST)){
				if($_POST['submitType']=='approve'){
					if($_POST['effectivedate']==date('F d, Y')){
						$upArr['status'] = 3;
						$upArr['reason'] = $_POST['reason'];
						$upArr['effectivedate'] = date('Y-m-d',strtotime($_POST['effectivedate']));
						
						$changes = json_decode($data['row']->dbchanges);	
						$this->staffM->updateQuery('staffs', array('empID'=>$data['row']->empID_fk), $changes);
						
						$this->staffM->addMyNotif($data['row']->preparedby, 'The CIS you requested for '.$data['row']->name.' has been approved by '.$this->user->name.'. Click <a href="'.$this->config->base_url().'updatecis/'.$cisID.'/" class="iframe">here</a> for details.', 0, 1);
						$this->staffM->addMyNotif($data['row']->empID_fk, 'A change in status of your employee\'s detail has been approved by '.$this->user->name.'. Click <a href="'.$this->config->base_url().'updatecis/'.$cisID.'/" class="iframe">here</a> for details.', 0, 1);						
					}else{						
						$upArr['status'] = 1;
						$upArr['effectivedate'] = date('Y-m-d',strtotime($_POST['effectivedate']));
						$upArr['reason'] = $_POST['reason'];
						
						$this->staffM->addMyNotif($data['row']->preparedby, 'The CIS you requested for '.$data['row']->name.' has been approved by '.$this->user->name.' but changes will be reflected based on the effective date on '.date('F d, Y',strtotime($_POST['effectivedate'])).'. Click <a href="'.$this->config->base_url().'updatecis/'.$cisID.'/" class="iframe">here</a> for details.', 0, 1);												
					}			
				}else if($_POST['submitType']=='disapprove'){					
					$upArr['status'] = 2;	
					$upArr['reason'] = $_POST['reason'];
					
					$this->staffM->addMyNotif($data['row']->preparedby, 'The CIS you requested for '.$data['row']->name.' has been disapproved by '.$this->user->name.'. Click <a href="'.$this->config->base_url().'updatecis/'.$cisID.'/" class="iframe">here</a> for details.', 0, 1);	
				}else if($_POST['submitType']=='signedCIS' && !empty($_FILES['signedFile'])){					
					if($_FILES['signedFile']['name']!=''){
						$dir = UPLOADS.'CIS/';	
						$n = array_reverse(explode('.', $_FILES['signedFile']['name']));
						move_uploaded_file($_FILES['signedFile']['tmp_name'], $dir.'CIS_'.$cisID.'.'.$n[0]);	
						$upArr['signedDoc'] = 'CIS_'.$cisID.'.'.$n[0];
						$this->staffM->addMyNotif($this->user->empID, 'You uploaded signed CIS document. Click <a href="'.$this->config->base_url().'updatecis/'.$cisID.'/" class="iframe">here</a> to see details.', 5);	
					}
				}
				
				if(count($upArr)>0){
					$upArr['updatedby'] = $this->user->name.'|'.date('Y-m-d H:i:s');
					$this->staffM->updateQuery('staffCIS', array('cisID'=>$cisID), $upArr);
					header('Location:'.$_SERVER['REQUEST_URI']);
					exit;
				}
			}
						
		}
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function cispdf(){
		$id = $this->uri->segment(2);
		
		$row = $this->staffM->getSingleInfo('staffCIS', 'staffCIS.*, CONCAT(fname," ",lname) AS name, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=preparedby AND preparedby!=0) AS prepby, supervisor', 'cisID="'.$id.'"', 'LEFT JOIN staffs ON empID=empID_fk');
		
		if(count($row)>0){
			$isupname = '';
			$nsupname = '';
			$isup = $this->staffM->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, supervisor', 'empID="'.$row->supervisor.'"');
			if(count($isup)>0){
				$isupname = $isup->name;
				$nsup = $this->staffM->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name', 'empID="'.$isup->supervisor.'"');
				if(isset($nsup->name)) $nsupname = $nsup->name;				
			}
			
			if($this->uri->segment(3)=='') $tt = 'I';
			else $tt = $this->uri->segment(3);
					
			$this->staffM->createCISpdf($row, $isupname, $nsupname, $tt);
		}else{
			echo 'Sorry, no record for this CIS.';
		}
	}
	
	public function staffcis(){
		$data['content'] = 'staffcis';
				
		if($this->user!=false){		
			if(count(array_intersect($this->myaccess,array('full','hr')))==0){
				$data['access'] = false;
			}else{	
				$data['pending'] = $this->staffM->getQueryResults('staffCIS', 'staffCIS.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS supName, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=preparedby) AS prepby', 'status=0', 'LEFT JOIN staffs ON empID=empID_fk');
				$data['approved'] = $this->staffM->getQueryResults('staffCIS', 'staffCIS.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS supName, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=preparedby) AS prepby', 'status=1', 'LEFT JOIN staffs ON empID=empID_fk');
				$data['done'] = $this->staffM->getQueryResults('staffCIS', 'staffCIS.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor) AS supName, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=preparedby) AS prepby', 'status=3', 'LEFT JOIN staffs ON empID=empID_fk');
			}
		}
		
		$this->load->view('includes/template', $data);			
	}
	
	public function requestcoe(){
		$data['content'] = 'requestcoe';
		
		if($this->user!=false){
			if($this->uri->segment(2)!=''){
				$coeID = $this->uri->segment(2);
				$data['toupdate'] = true;
				$data['row'] = $this->staffM->getSingleInfo('staffCOE', 'staffCOE.*, CONCAT(fname," ",lname) AS name, newPositions.title, startDate, endDate, sal, fname, username, empStatus', 'coeID="'.$coeID.'"', 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
				if($data['row']->dateissued!='0000-00-00'){
					$this->generatecoe($coeID);
				}
				
				if(isset($_POST) && !empty($_POST)){
					if($_POST['submitType']=='generate'){
						$this->staffM->updateQuery('staffCOE', array('coeID'=>$coeID), array('issuedby'=>$this->user->empID, 'dateissued'=>date('Y-m-d', strtotime($_POST['dateissued'])), 'status'=>'1'));
						$this->generatecoe($coeID);
						$this->staffM->addMyNotif($data['row']->empID_fk, $this->user->name.' generated the COE you requested. Click <a href="'.$this->config->base_url().'requestcoe/'.$coeID.'/" class="iframe">here</a> to view the file.', 0, 1);
						$this->staffM->addMyNotif($this->user->empID, 'You generated COE for '.$data['row']->name.'. Click <a href="'.$this->config->base_url().'requestcoe/'.$coeID.'/" class="iframe">here</a> to view the file.', 5);
					}
				}				
			}else{
				$data['toupdate'] = false;
				$data['row'] = $this->user;
				$data['prevRequests'] = $this->staffM->getQueryResults('staffCOE', 'staffCOE.*', 'empID_fk="'.$this->user->empID.'" AND status=1');
				if(isset($_POST) && !empty($_POST) && $_POST['submitType']=='request'){	
					$id = $this->staffM->insertQuery('staffCOE', array('empID_fk'=>$this->user->empID, 'additionalNote'=>$_POST['note'], 'daterequested'=>date('Y-m-d H:i:s')));
					$this->staffM->addMyNotif($this->user->empID, 'You requested for a Certificate of Employment.', 5);
					
					$body = '<p>Hi,</p>
						<p>This is an automatic notification that employee '.$this->user->name.' has requested for a COE. Please click <a href="'.$this->config->base_url().'generatecoe/'.$id.'/">here</a> to generate the COE.</p>
						<p>For HR, pleave validate information in the COE. In the event of any information discrepancy, please update PT.</p>
						<p style="color:red;">Refrain from manually editing the COE.</p>
						<p>Once validated, printed, and signed, click "Send email" button on Manage COE page to send and email to employee to claim their employment certificate in HR Office.</p>
						<p>Authorized signatories are only HR Personnel and Director of Operations.</p>
						<p><br/></p>
						<p>Thanks!</p>';
						
					$this->staffM->sendEmail( 'careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', 'Request for COE', $body, 'CareerPH Auto-Email');
					$data['inserted'] = true;
				}
			}
		}
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function generatecoe($id){	
		$row = $this->staffM->getSingleInfo('staffCOE', 'coeID, dateissued, empID, CONCAT(fname, " ",lname) AS name, newPositions.title, startDate, endDate, sal AS salary, empStatus', 'coeID="'.$id.'"', 'LEFT JOIN staffs ON empID_fk=empID LEFT JOIN newPositions ON posID=position');
		
		$this->staffM->genCOEpdf($row);
		
	}
	
	public function managecoe(){
		$data['content'] = 'managecoe';
		
		if($this->user!=false){
			$data['pending'] = $this->staffM->getQueryResults('staffCOE', 'staffCOE.*, CONCAT(fname," ",lname) AS name, username', 'status=0', 'LEFT JOIN staffs ON empID=empID_fk');
			$data['printed'] = $this->staffM->getQueryResults('staffCOE', 'staffCOE.*, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=staffCOE.issuedby) AS genName', 'status=1', 'LEFT JOIN staffs ON empID=empID_fk');
		}
		$this->load->view('includes/template', $data);	
	}
	
	public function sendEmail(){
		$data['content'] = 'sendemail';
		
		if($this->user!=false){
			$segment2 = $this->uri->segment(2);
			$segment3 = $this->uri->segment(3);
							
			$data['sent'] = false;
			if(isset($_POST) && !empty($_POST)){								
				if($segment3=='fromHR'){
					$this->staffM->sendEmail( 'hr.cebu@tatepublishing.net', $_POST['to'], $_POST['subject'], $_POST['message'], 'HR Cebu');
					$ntexts = 'From: hr.cebu@tatepublishing.net<br/>
								To: '.$_POST['to'].'<br/>
								Subject: '.$_POST['subject'].'<br/>
								'.$_POST['message'];
					$this->staffM->addMyNotif($this->user->empID, $ntexts, 5);
					$this->staffM->addMyNotif($segment2, $ntexts, 5);
				}else if($segment2=='addinfoleavesubmitted'){
					$this->staffM->sendEmail( 'careers.cebu@tatepublishing.net', $_POST['to'], $_POST['subject'], $_POST['message'], 'CareerPH');
					
					$addInfo = '<b>'.strtoupper($this->user->username).'</b><br/>
									Sent: '.date('M d, Y h:i A').'<br/>
									To: '.ltrim($_POST['to'],',').'<br/>
									Subject: '.$_POST['subject'].'<br/><br/>
									'.$_POST['message'].'<br/><br/>
									============================================================================================<br/>
								';
					$this->staffM->sendEmail('careers.cebu@tatepublishing.net', $_POST['to'], $_POST['subject'], $_POST['message'], 'CareerPH' );
					$this->staffM->updateConcat('staffLeaves', 'leaveID="'.$segment3.'"', 'addInfo', $addInfo);							
				}else{		
					$body = $_POST['message'];
					$body .= '<br/><br/>---- <i>Message sent from CareerPH through '.$this->user->name.'<i> ----';
					$this->staffM->sendEmail( 'careers.cebu@tatepublishing.net', $_POST['to'], $_POST['subject'], $body, 'CareerPH');
				}
				$data['sent'] = true;
			}else{			
				$data['subject'] = '';
				$data['to'] = '';
				$data['message'] = '';
				
				if($segment2=='addinfoleavesubmitted'){
					$lEmpID = $this->staffM->getSingleField('staffLeaves', 'empID_fk', 'leaveID="'.$segment3.'"');
					$supID = $this->staffM->getSingleField('staffs', 'supervisor', 'empID="'.$lEmpID.'"');
					$supEmail = $this->staffM->getSingleField('staffs', 'email', 'empID="'.$supID.'"');
					$data['subject'] = 'Additional Information for Leave Application #'.$segment3.' Submitted';
					$data['to'] = $supEmail.',hr.cebu@tatepublishing.net';
					$data['message'] = '';
				
				}else if($segment2!=''){
					$data['row'] = $this->staffM->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, fname, lname, email', 'empID="'.$segment2.'"');
					$data['to'] = $data['row']->email;
				}
			}
			
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	
	public function testpage(){
		$data['content'] = 'test';
		
		if($this->user!=false){
			if(isset($_POST) && !empty($_POST)){			
				if($_POST['shift']==0){
					$c = '09:00pm - 06:00am Mon-Fri';
				}else{
					$c = '07:00am - 04:00pm Mon-Fri';
				}
				$this->staffM->updateQuery('staffs', array('empID'=>$_POST['empID']), array('shift'=>$c));
				echo $c;
				exit;
			}
			$data['staffs'] = $this->staffM->getQueryResults('staffs', '*, CONCAT(lname," ",fname) AS name', 'shift=""', '', 'lname');
			/* $data['staffsPT'] = $this->staffM->getPTQueryResults('eData', 'eData.*, sFirst, sLast, active, CONCAT(sLast," ",sFirst) AS name', '1', 'LEFT JOIN staff ON username=u', 'sLast'); */
		}
		$this->load->view('includes/template', $data);	
	}
	
	public function supportingdocs(){
		$data['content'] = 'supportingdocs';
		
		if($this->user!=false && $this->uri->segment(2)!=''){
			$data['docs'] = $this->staffM->getQueryResults('staffUploads', 'staffUploads.*, (SELECT CONCAT(fname," ",lname) FROM staffs WHERE uploadedBy=empID) AS uploader, username', 'empID_fk="'.$this->uri->segment(2).'" AND isDeleted=0','LEFT JOIN staffs ON empID=empID_fk', 'dateUploaded DESC');
			
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
	
	public function sendMeEmail(){
		$this->staffM->sendEmail( 'hr.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'cron', date('F d, Y H:i:s'), 'The Human Resources Department');	
	}
	
	public function uploadFiles(){
		$data['content'] = 'uploadFiles';
		
		if(isset($_FILES) && !empty($_FILES['fileToUpload'])){
			$n = array_reverse(explode('.', $_FILES['fileToUpload']['name']));			
			move_uploaded_file($_FILES['fileToUpload']['tmp_name'], UPLOADS.'others/'.date('YmdHis').'.'.$n[0]);
		}
		
		if(isset($_POST) && !empty($_POST)){
			if($_POST['submitType']=='delete'){
				unlink($_POST['fname']);
				$this->staffM->addMyNotif($this->user->empID, 'You deleted this file '.$_POST['fname'], 5);
			}
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function downloadforms(){
		$data['content'] = 'downloadforms';
		
		$this->load->view('includes/template', $data);	
	}
	
	public function generatecode(){
		$data['content'] = 'generatecode';
		
		if($this->user!=false){
			if(isset($_POST) && !empty($_POST)){
				if($_POST['submitType']=='gencode'){
					$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
					$res = "";
					for ($i = 0; $i < 10; $i++) {
						$res .= $chars[mt_rand(0, strlen($chars)-1)];
					}
					$insArr['code'] = $res;
					$insArr['generatedBy'] = $this->user->empID;
					$insArr['dategenerated'] = date('Y-m-d H:i:s');
					$this->staffM->insertQuery('staffCodes', $insArr);
					
					echo $res;
					exit;
				}
			}			
			$data['codes'] = $this->staffM->getQueryResults('staffCodes', 'staffCodes.*, (SELECT CONCAT(fname," ",lname) FROM staffs WHERE usedBy!=0 AND empID=usedBy) AS useByName', 'generatedBy="'.$this->user->empID.'"', '', 'dateUsed');
			
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
			
	public function getStaffEmails(){	
		$condition = '';
		if($this->user->access==''){
			$ids = '';
			$myStaff = $this->staffM->getStaffUnder($this->user->empID, $this->user->level);				
			foreach($myStaff AS $m):
				$ids .= $m->empID.',';
			endforeach;
			if($ids!='') $condition .= ' AND empID IN ('.rtrim($ids,',').')';
		}
	
		$query = $this->staffM->getQueryResults('staffs', 'empID, username, CONCAT(fname," ",lname) AS name, email', 'active=1'.$condition);
		$disp = '<button id="cboxClose" type="button" style="top:27px; right:8px;" onClick="$(\'.staffEmails\').addClass(\'hidden\'); $(\'#filter\').val(\'\');">close</button>';
		$disp .= '<table class="tableInfo" style="text-align:left;">';
		foreach($query AS $q):
			$disp .= '<tr class="cpointer"><td onClick="sendEmailOpen('.$q->empID.')">'.$q->name.'</td></tr>';
		endforeach;
		$disp .= '</table>';
		echo $disp;		
	}
	
	public function myNotes(){
		$myNotes = $this->staffM->mergeMyNotes($_POST['empID'], $_POST['username']);
		
		echo '<table class="tableInfo">';
		if(count($myNotes)==0){
			echo '<tr><td>No notes.</td></tr>';
		}else{
			if(isset($_POST['halo'])){
				$h = $_POST['halo'];
				$hEnd = $_POST['halo'] + 15;
			}else{
				$h = 0;
				$hEnd = 15;
			}
		
			for($cnt=$h; $cnt<$hEnd && $cnt<count($myNotes); $cnt++){
				$m = $myNotes[$cnt];
				if($m['access']=='' || $m['access']=='assoc' || 
					$m['access']=='full' && (count(array_intersect($this->myaccess,array('full','hr')))>0) ||
					$m['access']=='exec' && $this->user->is_supervisor==1
				){
					echo '<tr class="nnotes nstat_'.$m['type'].'" valign="top">';					
					if($m['from']=='careerPH')
						$img = $this->config->base_url().'uploads/staffs/'.$m['username'].'/'.$m['username'].'.jpg';					
					
					if($m['from']=='pt' || ($m['from']=='careerPH' && !(@file_get_contents($img, 0, NULL, 0, 1))))
						$img = 'http://staffthumbnails.s3.amazonaws.com/'.$m['username'].'.jpg';
										
					if(!(@file_get_contents($img, 0, NULL, 0, 1))) $img = $this->config->base_url().'css/images/logo.png';
					
					
					
					echo '<td class="nTD"><img src="'.$img.'" width="60px"/></td>';
					
					if($m['from']=='careerPH') echo '<td><b>'.$m['name'].'</b> ('.date('M d y h:i a', strtotime($m['timestamp'])).')<br/><br/>'.$m['note'].'<br/><br/></td>';
					else echo '<td>'.$m['note'].'</td>';	
					echo '</tr>';
				}
			}
			
		}
		echo '</table>';
		if(isset($cnt) && $cnt<count($myNotes)){
			echo '<div style="cursor:pointer; text-align:center; padding:5px; border:1px solid #ccc;" onClick="addGetNotes('.$_POST['empID'].', \''.$_POST['username'].'\', '.$cnt.', this);"><b>Load More...</b></div>';
		}
		
	}
				
	public function notes(){
		
	}
}

