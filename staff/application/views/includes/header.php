<header>
<?php
	if($this->user!=false && $this->user->empID!=0){
	
	if($this->session->userdata('testing')==true){
		echo $this->user->name.' '.$this->user->empID.'<br/>';
		print_r($this->access->myaccess);
		echo '
		<form action="'.$this->config->base_url().'hello/" method="POST">
			<b>Change logged in user</b><br/>
			<input type="text" name="username" value="" class="padding5px" placeholder="username"/><input type="submit" value="Submit"/><br/>
		</form>';
	}
	
	
		
	if(!isset($current)) $current = '';	
?>
	<div id="logo"></div>
<?php if($content != 'changepassword'){ ?>
	<div id="menubar">
		<ul class="menu">
			<li <?php if($content=='index'){ echo 'class="current"'; } ?>><a href="<?= $this->config->base_url() ?>">Homepage</a></li>
			<li <?php if($current=='myinfo'){ echo 'class="current"'; } ?>><a href="<?= $this->config->base_url() ?>myinfo/">My HR Info</a></li>
			<li><a href="http://employee.tatepublishing.net/hr/forms/" target="_blank">Download Forms</a></li>
			<li <?php if($content=='myattendance'){ echo 'class="current"'; } ?>><a href="<?= $this->config->base_url() ?>timecard/">My Timecard and Payroll</a></li>			
			
		<?php
			if($this->user->dept== 'IT'){
				echo '<li '.(($content=='itchecklist')?'class="current"':'').'><a href="'.$this->config->base_url().'itchecklist/">IT Checklist</a>';
					echo '<ul class="dropdown">';
						echo '<li '.(($content=='deactivateuser')?'class="current"':'').'><a href="'.$this->config->base_url().'itchecklist/deactivateuser/">Deactivate User</a></li>';
					echo '</ul>';
				echo '</li>';
			}
			
			if($this->user->access!='' || $this->user->level>0){
				echo '<li '.(($content=='manageStaff')?'class="current"':'').'><a href="'.$this->config->base_url().'manageStaff/">Manage Staff</a>';
					echo '<ul class="dropdown">';
					
					$cisNum = $this->staffM->countResults('cis');
					$coachingNum = $this->staffM->countResults('coaching');
					$updateRequestNum = $this->staffM->countResults('updateRequest');
					$pendingCOENum = $this->staffM->countResults('pendingCOE');
					$staffLeavesNum = $this->staffM->countResults('staffLeaves');
					
					if($this->access->accessFullHR==true){
						echo '<li '.(($content=='staffcis')?'class="current"':'').'><a href="'.$this->config->base_url().'staffcis/">Staff CIS '.(($cisNum>0)?'<b>['.$cisNum.']</b>':'').'</a></li>';						
						echo '<li '.(($content=='staffupdated')?'class="current"':'').'><a href="'.$this->config->base_url().'staffupdated/">Info Update Requests '.(($updateRequestNum>0)?'<b>['.$updateRequestNum.']</b>':'').'</a></li>';
						echo '<li '.(($content=='managecoe')?'class="current"':'').'><a href="'.$this->config->base_url().'managecoe/">Manage COE '.(($pendingCOENum>0)?'<b>['.$pendingCOENum.']</b>':'').'</a></li>';
						echo '<li '.(($content=='schedules')?'class="current"':'').'><a href="'.$this->config->base_url().'schedules/">Manage Schedules</a></li>';
						echo '<li '.(($content=='organizationalchart')?'class="current"':'').'><a href="'.$this->config->base_url().'organizationalchart/">Organizational Chart</a></li>';
					}
					if($this->access->accessFinance==false){
						echo '<li '.(($content=='staffcoaching')?'class="current"':'').'><a href="'.$this->config->base_url().'staffcoaching/">Staff Coaching '.(($coachingNum>0)?'<b>['.$coachingNum.']</b>':'').'</a></li>';
						echo '<li '.(($content=='nteissued')?'class="current"':'').'><a href="'.$this->config->base_url().'nteissued/">NTE Issued</a></li>';
						echo '<li '.(($content=='staffleaves')?'class="current"':'').'><a href="'.$this->config->base_url().'staffleaves/">Staff Leaves '.(($staffLeavesNum>0)?'<b>['.$staffLeavesNum.']</b>':'').'</a></li>';
						echo '<li '.(($content=='others')?'class="current"':'').'><a class="iframe" href="'.$this->config->base_url().'others/">Other Pages</a></li>';
					}
										
						
					echo '</ul>';
				echo '</li>';
				
			}
			
			if($this->access->accessFullHR==true){
				echo '<li '.(($content=='CAREERPH')?'class="current"':'').'><a href="'.$this->config->item('career_url').'/" target="_blank">CAREERPH</a>';
				echo '<ul class="dropdown">';
					echo '<li><a href="/recruitment-manager.php" target="_blank">Recruitment Manager</a></li>';
					echo '<li><a href="/recruitment-interface.php" target="_blank">Job Requisitions</a></li>';
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
