<header>
<?php
	$segment2 = $this->uri->segment(2);
	
	if($this->user!=false && $this->user->empID!=0){
	
	//if($this->session->userdata('testing')==true){
	if($this->config->item('devmode')==true || $this->session->userdata('testing')==true){
		echo $this->user->name.' '.$this->user->empID.'---'.$this->user->idNum.'<br/>';
		print_r($this->access->myaccess);
		echo '<form action="'.$this->config->base_url().'hello/" method="POST">
			<b>Change logged in user</b><br/>
			<input type="text" name="username" value="" class="padding5px" placeholder="username"/><input type="submit" value="Submit"/><br/>
		</form>';
		
		echo '  <a href="http://10.100.0.1'.$_SERVER['REQUEST_URI'].'" target="_blank"><button>Go to test page</button></a>';
		echo '  <a href="https://careerph.tatepublishing.net'.$_SERVER['REQUEST_URI'].'" target="_blank"><button>Go to live page</button></a>';
		
		/* echo '<form action="'.$this->config->base_url().'hello/empID/" method="POST">
			<b>Change logged in user</b><br/>
			<input type="text" name="empID" value="" class="padding5px" placeholder="emp ID"/><input type="submit" value="Submit"/><br/>
		</form>'; */
	}
	
		
	if(!isset($current)) $current = '';	
?>
	<div id="logo"></div>
<?php if($content != 'changepassword'){ ?>
	<div id="menubar">
		<ul class="menu">
			<li <?php if($content=='index'){ echo 'class="current"'; } ?>><a href="<?= $this->config->base_url() ?>">Homepage</a></li>
			<li <?php if($current=='myinfo'){ echo 'class="current"'; } ?>>
				<a href="<?= $this->config->base_url() ?>myinfo/">My HR Info</a>
			</li>			
		<?php
			echo '<li '.(($content=='myattendance')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/">Timecard and Payroll</a>';
			
				echo '<ul class="dropdown">';							
					echo '<li '.(($segment2=='timelogs')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/timelogs/">My Time Logs</a></li>';
					echo '<li '.(($segment2=='calendar')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/calendar/">My Calendar</a></li>';
					echo '<li '.(($segment2=='payslips')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/payslips/">My Payslips</a></li>';
					
					if($this->user->level>0 || $this->access->accessFullHRFinance==true) 
						echo '<li '.(($segment2=='attendance')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/attendance/">Attendance</a></li>';
					
					
					if($this->access->accessFullHR==true)
						echo '<li '.(($segment2=='scheduling')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/scheduling/">Scheduling</a></li>';
					/* if($this->access->accessFullFinance==true)	
						echo '<li '.(($segment2=='payrolls')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/payrolls/">Payrolls</a></li>';
					if($this->access->accessFullHRFinance==true)	
						echo '<li '.(($segment2=='reports')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/reports/">Reports</a></li>';	 */
					if($this->access->accessFullHRFinance==true)
						echo '<li '.(($content=='sched_schedules')?'class="current"':'').'><a href="'.$this->config->base_url().'schedules/">Schedules Settings</a></li>';
					
					if($this->access->accessFullHRFinance==true){
						$unpublished = $this->commonM->countResults('unpublishedLogs');
						
						echo '<li '.(($segment2=='unpublishedlogs')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/unpublishedlogs/">Unpublished Logs <b>'.(($unpublished>0)?'['.$unpublished.']':'').'</b></a></li>';
					}
					
					if($this->access->accessFullHRFinance==true){
						$logRequest = $this->commonM->countResults('timelogRequests');
						
						echo '<li '.(($segment2=='logpendingrequest')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/logpendingrequest/">Log Pending Requests <b>'.(($logRequest>0)?'['.$logRequest.']':'').'</b></a></li>';
					}
					
					if($this->access->accessFullHRFinance==true){
						echo '<li '.(($segment2=='managelastpay')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/managelastpay/">Manage Last Pay</a></li>';
					}
					
					if($this->access->accessFullFinance==true){
						echo '<li '.(($segment2=='managepayroll')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/managepayroll/">Manage Payroll</a></li>';
						//echo '<li '.(($segment2=='reports')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/reports/">Reports</a></li>';
					}
					
					
				echo '</ul>';
			
			echo '</li>';
			
		?>			
			
		<?php
			if($this->user->dept== 'IT'){
				echo '<li '.(($content=='itchecklist')?'class="current"':'').'><a href="'.$this->config->base_url().'itchecklist/">IT Checklist</a>';
					echo '<ul class="dropdown">';
						echo '<li '.(($content=='deactivateuser')?'class="current"':'').'><a href="'.$this->config->base_url().'itchecklist/deactivateuser/">Deactivate User</a></li>';
						echo '<li '.(($content=='newhirestatus')?'class="current"':'').'><a href="'.$this->config->base_url().'itchecklist/newhirestatus/">New Hire Status</a></li>';
					echo '</ul>';
				echo '</li>';
			}
			
			if($this->access->accessFullHRFinance==true ||  $this->access->accessMedPerson || $this->user->level>0){
				echo '<li '.(($content=='manageStaff')?'class="current"':'').'><a href="'.$this->config->base_url().'manageStaff/">Manage Staff</a>';
					echo '<ul class="dropdown">';
					
					$cisNum = $this->commonM->countResults('cis');
					$coachingNum = $this->commonM->countResults('coaching');
					$updateRequestNum = $this->commonM->countResults('updateRequest');
					$pendingCOENum = $this->commonM->countResults('pendingCOE');
					$staffLeavesNum = $this->commonM->countResults('staffLeaves');
					$nteNum = $this->commonM->countResults('nte');
					$eval90th = $this->commonM->countResults('eval90th');
					$medrequests = $this->commonM->countResults('medrequests');
					$hdmf_loans = $this->commonM->countResults('hdmf_loans');
					
					if($this->access->accessFullHR==true){
						$cntincidentreport = $this->commonM->countResults('incidentreport');
						echo '<li '.(($content=='incidentreports')?'class="current"':'').'><a href="'.$this->config->base_url().'incidentreports/">HR Incident Reports '.(($cntincidentreport>0)?'['.$cntincidentreport.']':'').'</a></li>';
						echo '<li '.(($content=='staffupdated')?'class="current"':'').'><a href="'.$this->config->base_url().'staffupdated/">Manage Update Requests '.(($updateRequestNum>0)?'<b>['.$updateRequestNum.']</b>':'').'</a></li>';
						echo '<li '.(($content=='staffcis')?'class="current"':'').'><a href="'.$this->config->base_url().'staffcis/">Manage CIS '.(($cisNum>0)?'<b>['.$cisNum.']</b>':'').'</a></li>';
						echo '<li '.(($content=='managecoe')?'class="current"':'').'><a href="'.$this->config->base_url().'managecoe/">Manage COE '.(($pendingCOENum>0)?'<b>['.$pendingCOENum.']</b>':'').'</a></li>';						
					}
					if($this->access->accessFullHR==true OR $this->user->level > 0){
						echo '<li '.(($content=='staffcoaching')?'class="current"':'').'><a href="'.$this->config->base_url().'staffcoaching/">Manage Coaching '.(($coachingNum>0)?'<b>['.$coachingNum.']</b>':'').'</a></li>';
						echo '<li '.(($content=='staffleaves')?'class="current"':'').'><a href="'.$this->config->base_url().'staffleaves/">Manage Leaves '.(($staffLeavesNum>0)?'<b>['.$staffLeavesNum.']</b>':'').'</a></li>';
						echo '<li '.(($content=='nteissued')?'class="current"':'').'><a href="'.$this->config->base_url().'nteissued/">Manage NTE '.(($nteNum>0)?'<b>['.$nteNum.']</b>':'').'</a></li>';
					}	
					if($this->access->accessMedPerson OR $this->access->accessFullHR){
						echo '<li '.(($content=='probationmanagement')?'class="current"':'').'><a href="'.$this->config->base_url().'probationmanagement/">Probation Management '.(($eval90th>0)?'<b>['.$eval90th.']</b>':'').'</a></li>';
					}				
					if($this->access->accessFullHR){						
						echo '<li '.(($content=='referralmanagement')?'class="current"':'').'><a href="'.$this->config->base_url().'referralmanagement/">Referral Management</a></li>';
						echo '<li '.(($content=='writtenmanagement')?'class="current"':'').'><a href="'.$this->config->base_url().'writtenmanagement/">Written Warning Management</a></li>';
						echo '<li '.(($content=='hdmf_loan')?'class="current"':'').'><a href="'.$this->config->base_url().'hdmfs/">HDMF Loan Application '.(($hdmf_loans > 0)?'<b>['.$hdmf_loans.']</b>':'').'</a></li>';
					}
						
					if( $this->access->accessMedPerson OR $this->access->accessFullFinance ){
						echo '<li '.(($content=='medrequests')?'class="current"':'').'><a href="'.$this->config->base_url().'medrequests/">Medicine Reimbursement '.(($medrequests>0)?'<b>['.$medrequests.']</b>':'').'</a></li>';
					}
						
					echo '</ul>';
				echo '</li>';
				
			}
			
			if($this->access->accessFullHR==true){
				echo '<li '.(($content=='CAREERPH')?'class="current"':'').'><a href="'.$this->config->item('career_url').'/" target="_blank">CAREERPH</a>';
				echo '<ul class="dropdown">';
					echo '<li><a href="'.$this->config->item('career_url').'/recruitment-manager.php" target="_blank">Recruitment Manager</a></li>';
					echo '<li><a href="'.$this->config->item('career_url').'/recruitment-interface.php" target="_blank">Job Requisitions</a></li>';
				echo '</ul>';
				echo '</li>';
			}
		?>		
		
			<li><a href="#">Miscellaneous</a>
				<ul class="dropdown">
					<li><a href="http://employee.tatepublishing.net/hr/forms/" target="_blank">Download Forms</a></li>
					<li <?= (($content=='organizationalchart')?'class="current"':'') ?>><a href="<?= $this->config->base_url().'organizationalchart/' ?>">Organizational Chart</a></li>
			<?php
				if($this->access->accessFullHRFinance==true || $this->user->level>0){
					echo '<li '.(($content=='allpositions')?'class="current"':'').'><a href="'.$this->config->base_url().'allpositions/">List of all Positions</a></li>';
					echo '<li><a href="'.$this->config->base_url().'addnewposition/" class="iframe">Add New Position</a></li>';
				}
			?>
				</ul>
			</li>
			
			<?php
				if($this->access->accessFullHRFinance==true){
					echo '<li><a href="'.$this->config->base_url().'reports/">Reports and Stats</a>
						<ul class="dropdown">
							<li><a href="'.$this->config->base_url().'reports/upward_feedback/">Upward Feedback Report</a></li>
						</ul>
					</li>';
				}
			?>
			
		</ul>
		
	</div>	
	<a class="iframe" href="<?= $this->config->base_url() ?>detailsnotifications/">
		<div id="menu-text">
		<?php
			if($cnotif>0){
				echo '<span id="headnotification">'.$cnotif.'</span>';
			}
		?>
			<img src="<?= $this->config->base_url() ?>css/images/message.png" style="margin-bottom: 5px"/>
		</div>
	</a>
<?php } ?>

	<div id="menu-logout">		
		<a href="<?= $this->config->base_url() ?>logout/"><div id="logout"></div></a>		
	</div>
<?php } ?>
</header>
