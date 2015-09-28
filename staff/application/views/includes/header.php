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
			<li><a href="http://employee.tatepublishing.net/hr/forms/" target="_blank">Download Forms</a></li>
			
		<?php
			echo '<li '.(($content=='myattendance')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/">Timecard and Payroll</a>';
			
			echo '<ul class="dropdown">';
			
			if($this->access->accessFullHR===true)
				echo '<li '.(($content=='sched_schedules')?'class="current"':'').'><a href="'.$this->config->base_url().'schedules/">Schedules Settings</a></li>';	
			
			echo '<li '.(($segment2=='timelogs')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/timelogs/">My Time Logs</a></li>';
			echo '<li '.(($segment2=='calendar')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/calendar/">My Calendar</a></li>';
			echo '<li '.(($segment2=='payslips')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/payslips/">My Payslips</a></li>';
			
			if($this->user->is_supervisor==1 || $this->access->accessFullHR==true) 
				echo '<li '.(($segment2=='attendance')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/attendance/">Attendance</a></li>';
			
			if($this->access->accessFullHR==true){	
				echo '<li '.(($segment2=='scheduling')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/scheduling/">Scheduling</a></li>';
				echo '<li '.(($segment2=='payrolls')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/payrolls/">Payrolls</a></li>';
				echo '<li '.(($segment2=='reports')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/reports/">Reports</a></li>';				
				echo '<li '.(($segment2=='reports')?'class="current"':'').'><a href="'.$this->config->base_url().'timecard/managetimecard/">Manage Timecard</a></li>';				
			}
			
			
			echo '</li>';
			echo '</ul>';
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
			
			if($this->access->accessFullHRFinance==true || $this->user->level>0){
				echo '<li '.(($content=='manageStaff')?'class="current"':'').'><a href="'.$this->config->base_url().'manageStaff/">Manage Staff</a>';
					echo '<ul class="dropdown">';
					
					$cisNum = $this->commonM->countResults('cis');
					$coachingNum = $this->commonM->countResults('coaching');
					$updateRequestNum = $this->commonM->countResults('updateRequest');
					$pendingCOENum = $this->commonM->countResults('pendingCOE');
					$staffLeavesNum = $this->commonM->countResults('staffLeaves');
					$nteNum = $this->commonM->countResults('nte');
					$eval90th = $this->commonM->countResults('eval90th');
					
					if($this->access->accessFullHR==true){												
						echo '<li '.(($content=='staffupdated')?'class="current"':'').'><a href="'.$this->config->base_url().'staffupdated/">Info Update Requests '.(($updateRequestNum>0)?'<b>['.$updateRequestNum.']</b>':'').'</a></li>';
						echo '<li '.(($content=='staffcis')?'class="current"':'').'><a href="'.$this->config->base_url().'staffcis/">Manage CIS '.(($cisNum>0)?'<b>['.$cisNum.']</b>':'').'</a></li>';
						echo '<li '.(($content=='managecoe')?'class="current"':'').'><a href="'.$this->config->base_url().'managecoe/">Manage COE '.(($pendingCOENum>0)?'<b>['.$pendingCOENum.']</b>':'').'</a></li>';						
					}
					
					echo '<li '.(($content=='staffcoaching')?'class="current"':'').'><a href="'.$this->config->base_url().'staffcoaching/">Manage Coaching '.(($coachingNum>0)?'<b>['.$coachingNum.']</b>':'').'</a></li>';
					echo '<li '.(($content=='staffleaves')?'class="current"':'').'><a href="'.$this->config->base_url().'staffleaves/">Manage Leaves '.(($staffLeavesNum>0)?'<b>['.$staffLeavesNum.']</b>':'').'</a></li>';
					echo '<li '.(($content=='nteissued')?'class="current"':'').'><a href="'.$this->config->base_url().'nteissued/">Manage NTE '.(($nteNum>0)?'<b>['.$nteNum.']</b>':'').'</a></li>';
					echo '<li '.(($content=='others')?'class="current"':'').'><a class="iframe" href="'.$this->config->base_url().'others/">Other Pages</a></li>';
					
					if($this->access->accessFullHRFinance==true || $this->user->level>0){
						echo '<li '.(($content=='organizationalchart')?'class="current"':'').'><a href="'.$this->config->base_url().'organizationalchart/">Organizational Chart</a></li>';
					}
					
					if($this->access->accessFullHR){
						echo '<li '.(($content=='probationmanagement')?'class="current"':'').'><a href="'.$this->config->base_url().'probationmanagement/">Probation Management '.(($eval90th>0)?'<b>['.$eval90th.']</b>':'').'</a></li>';
						echo '<li '.(($content=='referralmanagement')?'class="current"':'').'><a href="'.$this->config->base_url().'referralmanagement/">Referral Management</a></li>';
						echo '<li '.(($content=='incidentreports')?'class="current"':'').'><a href="'.$this->config->base_url().'incidentreports/">HR Incident Reports</a></li>';
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
