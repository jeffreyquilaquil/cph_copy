<?php
	require 'config.php';
	require_once('includes/labels.php');
	date_default_timezone_set("Asia/Manila");
	setlocale(LC_MONETARY, 'en_US');
		
	if(!isset($_SESSION['u']) || !in_array($_SESSION['u'], $authorized)){
		echo '<script>window.parent.location = "http://careerph.tatepublishing.net/login.php";</script>';
		exit();
	} 
	
	$id = $_GET['id'];
	$info = $db->selectSingleQueryArray('applicants','*','id="'.$id.'"');
	
	$recProcess = $db->selectQueryArray('SELECT * FROM recruitmentProcess');
	if($info['isNew']==0){
		$position = $db->selectSingleQuery('positions', 'title' , 'id="'.$info['position'].'"');
	}else if(!empty($info['position'])){
		$pp = $db->selectSingleQueryArray('newPositions', 'title, org, dept, grp, subgrp' , 'posID="'.$info['position'].'"');
		$position = $pp['title'];
		$info += $pp;
	}
			

	if(isset($_GET['pos'])){
		if($_GET['pos']=='back'){
			$db->updateQuery('applicants', array('process' => $_GET['stat']), 'id='.$id);
			if($_GET['stat']==5)
				addStatusNote($id, 'changeStatus', '', $info['position'], 'Status changed from Hired to Job Offering');
				
			echo '<script>window.location.href="editstatus.php?id='.$id.'";</script>';
		}else if($_GET['pos']=='reprof'){
			echo $db->selectSingleQuery('jobReqData', 'positionID' , 'status=0 AND positionID="'.$_GET['posid'].'"');
			
		}else if($_GET['pos']=='testing'){
			if($_POST['type']=='jobOffer'){
				$offID = $db->selectSingleQuery('generatedJO', 'joID' , 'appID='.$_POST['appID'].' ORDER BY timestamp DESC');
				addStatusNote($_POST['appID'], $_POST['type'], $_POST['testStatus'], $offID, $_POST['reason']);
			}else{
				addStatusNote($_POST['appID'], $_POST['type'], $_POST['testStatus'], $_POST['positionID'], $_POST['reason']);
			}
			
			if($_POST['testStatus']=='failed'){
				$freeze = array('iq', 'typing', 'written', 'hr', 'final');
				if(in_array($_POST['type'], $freeze))
					$db->updateQuery('applicants', array('processStat' => '0', 'processText' => 'Failed '.$processLabels[$_POST['type']]), 'id='.$_POST['appID']);
				else
					$db->updateQuery('applicants', array('processText' => 'Failed '.$processLabels[$_POST['type']]), 'id='.$_POST['appID']);
			}
		}
		
		exit;		
	}
	
	if(isset($_POST) && !empty($_POST)){
		if($_POST['formSubmit']=='reprofile'){
			$db->updateQuery('applicants', array('position' => $_POST['newPos'], 'isNew' => 1), 'id='.$id);
			
			$updateArr['processStat'] = 1;
			$updateArr['processText'] = 'Reprofiled';
			if($_POST['prevStat']>2){
				$updateArr['process'] = 2;
			}

			$db->updateQuery('applicants', $updateArr, 'id='.$id);		
			
			$old = $db->selectSingleQuery('newPositions', 'title' , 'posID='.$info['position']);
			$newP = $db->selectSingleQuery('newPositions', 'title' , 'posID='.$_POST['newPos']);
			addStatusNote($id, 'reprofiled', 'reprofiled', $info['position'], '<b>Reprofiled from '.$old.' to '.$newP.'<br/>'.$_POST['reason'].'</b>');
			echo '<script>alert("Reprofiled");</script>';
			
		}else if( $_POST['formSubmit']=='cancel' && !empty($_POST['reason'])){		
			$stat = 0;
			$txt = 'Cancelled';
			if($_POST['cancelMessage']!=''){
				$m = explode('|', $_POST['cancelMessage']);
				$stat = $m[1];
				$txt .= ' ('.$m[0].')';
			}			
		
			$db->updateQuery('applicants', array('processStat' => $stat, 'processText' =>$txt), 'id='.$id);
			addStatusNote($id, 'cancelled', 'cancelled', $info['position'], $_POST['reason']);
		}else if( $_POST['formSubmit']=='advanceProcess'){
			if($_POST['process']==6){
				$db->updateQuery('applicants', array('process' => $_POST['process'], 'pHEnrolled'=>1, 'pHBDay'=>1, 'pHCompensation'=>1, 'batchID'=>1, 'referral'=>1,), 'id='.$id);
			}else{
				$db->updateQuery('applicants', array('process' => $_POST['process'], 'processText'=>''), 'id='.$id);
			}
			addStatusNote($id, 'advance', '', $info['position'], 'Advanced to '.$recProcess[$info['process']+1]['processType']);
		}else if($_POST['formSubmit'] == 'Generate' || $_POST['formSubmit'] == 'Regenerate'){		
			$joTxt = 'Job offer is: Php'.number_format($_POST['salary'], 2).'<br/>Start date is: '.$_POST['startDate'];			
			if(!empty($_POST['salReason'])){
				$joTxt .= '<br/>Reason:'.$_POST['salReason'];
			}	
			if(isset($_POST['genReason']) && !empty($_POST['genReason'])){
				$joTxt .= '<br/>Regenerate Reason:'.$_POST['genReason'];
			}
			addStatusNote($id, 'genJobOffer', 'admin', $info['position'], $joTxt);
			$gJO = array('appID' => $id, 
						'offer' => number_format($_POST['salary'], 2), 
						'startDate' => date('Y-m-d', strtotime($_POST['startDate']) ), 
						'offeredBy' => $_SESSION['u']);
			$joInsID = $db->insertQuery('generatedJO', $gJO);
				
			ob_end_clean();
			require_once('includes/fpdf/fpdf.php');
			require_once('includes/fpdf/fpdi.php');

			$pdf = new FPDI();
			$pdf->AddPage();
			$pdf->setSourceFile('includes/forms/jobOffer.pdf');
			$tplIdx = $pdf->importPage(1);
			$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
			
			$pdf->SetFont('Arial','',10);
			$pdf->setTextColor(0, 0, 0);
			
			$pdf->setXY(12, 47);
			$pdf->Write(0, date('F d, Y'));	
			
			$pdf->SetFont('Arial','B',10);
			$pdf->setXY(12, 60);
			$pdf->Write(0, $info['fname'].' '.$info['lname']);	
			
			$pdf->setXY(20, 81.8);
			$pdf->Write(0, $_POST['prefix'].' '.$info['lname']);
			
			$sal = (int)$_POST['salary'] * 13;
			$pdf->setXY(84.5, 109.2);
			$pdf->Write(0, number_format($sal, 2));
			
			$pdf->setXY(105, 120);
			$pdf->Write(0, number_format((int)$_POST['salary'], 2));

			$pdf->setXY(120, 185.8);
			$pdf->Write(4, strtoupper($_POST['position']));
			
			$pdf->AddPage();
			$tplIdx = $pdf->importPage(2);
			$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
			
			$pdf->AddPage();
			$tplIdx = $pdf->importPage(3);
			$pdf->useTemplate($tplIdx, null, null, 0, 0, true); 
			
			$pdf->AddPage();
			$tplIdx = $pdf->importPage(4);
			$pdf->useTemplate($tplIdx, null, null, 0, 0, true); 
			
			$pdf->SetFont('Arial','',10);
			$pdf->setXY(18, 195);
			$pdf->Write(0, strtoupper($info['fname'].' '.$info['lname']));
			
			$pdf->setXY(43, 206);
			$pdf->Write(0, $_POST['startDate']);
			
			$pdf->Output("uploads/joboffers/JobOffer".$joInsID.".pdf", "F");
			$pdf->Output($info['lname'].$info['fname']."JobOffer.pdf", "D");
			
		}else if($_POST['formSubmit']=='checklist'){
			$uArr['pHEnrolled'] = false;
			$uArr['pHBDay'] = false;
			$uArr['pHCompensation'] = false;
			$uArr['batchID'] = false;
			$uArr['referral'] = false;
			if(isset($_POST['pHEnrolled']) && $_POST['pHEnrolled']=='on') $uArr['pHEnrolled'] = true;
			if(isset($_POST['pHBDay']) && $_POST['pHBDay']=='on') $uArr['pHBDay'] = true;
			if(isset($_POST['pHCompensation']) && $_POST['pHCompensation']=='on') $uArr['pHCompensation'] = true;
			if(isset($_POST['batchID']) && $_POST['batchID']=='on') $uArr['batchID'] = true;
			if(isset($_POST['referral']) && $_POST['referral']=='on') $uArr['referral'] = true;
			
			$db->updateQuery('applicants', $uArr, 'id='.$_POST['appID']);			
		}
		
		echo '<script>window.location.href="'.$_SERVER['REQUEST_URI'].'";</script>';
		exit; 
	}
	
		
	
	function techTest($type, $s, $rows='3', $options=''){
		global $db, $id, $info, $processLabels;
		
		$styl ='';
		if($s==1) $styl = 'bgcolor="#dcdcdc"';
		
		
		if($type=='iq' || $type=='typing' || $type=='written')
			$testQ = $db->selectSingleQueryArray('processStatusData', 'testStatus, reason, positionID' , 'appID="'.$id.'" AND type="'.$type.'"');
		else if($type=='jobOffer'){
			$offID = $db->selectSingleQuery('generatedJO', 'joID' , 'appID='.$id.' ORDER BY timestamp DESC');
			$testQ = $db->selectSingleQueryArray('processStatusData', 'testStatus, reason, positionID' , 'appID="'.$id.'" AND type="'.$type.'" AND positionID='.$offID);		
		}else
			$testQ = $db->selectSingleQueryArray('processStatusData', 'testStatus, reason, positionID' , 'appID="'.$id.'" AND type="'.$type.'" AND positionID='.$info['position']);
		
		
		$txt = '
		<tr '.$styl.'>
			<td width="40%">'.$processLabels[$type].'</td>
			<td>';
		if(isset($testQ['testStatus']) && 
			(( $testQ['positionID'] == $info['position'] || $type=='iq' || $type=='typing' || $type=='written') ||
			($type=='jobOffer' && $testQ['positionID'] == $offID ))
		){
			$txt .=	'<input type="text" disabled class="form-control" id="'.$type.'" value="'.ucfirst($testQ['testStatus']).'"/>';
		}else{
			if(empty($options)){
				$txt .=	'<select class="form-control" onChange="showDiv(\''.$type.'\')" id="'.$type.'">
						<option></option value=""><option value="passed">Pass</option><option value="failed">Fail</option>
					</select>';
			}else{
				$op = explode('|', $options);
				$txt .= '<select class="form-control" onChange="showDiv(\''.$type.'\')" id="'.$type.'"><option></option value="">';
				for($i=0;$i<count($op);$i++){
					$txt .= '<option value="'.$op[$i].'">'.ucfirst($op[$i]).'</option>';
				}
				$txt .= '</select>';
			}
			
		}
					
			if(($type=='hr' || $type=='final') && !empty($testQ['testStatus'])){
				$txt .=	'<div id="'.$type.'Div">';
				$txt .=		'<textarea class="form-control" id="'.$type.'reason" rows="'.$rows.'" disabled>'.$testQ['reason'].'</textarea>						
						</div>';
				
			}else{
				$txt .=	'<div id="'.$type.'Div" style="display:none;">';
				$txt .=		'<textarea class="form-control" id="'.$type.'reason" rows="'.$rows.'"></textarea>
							<input type="button" value="Submit" onClick="checkTest(\''.$type.'\')"/>
						</div>';
			}
		
		$txt .=	'</td>
		</tr>';
		return $txt;
	}
	
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">	
	<title>Edit Status of <?= ucfirst($info['fname']).' '.ucfirst($info['lname']) ?></title>
	<link href="css/yeti.bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="css/progressbar.css" type="text/css" />
	<style type="text/css">
		.pad5px{ padding:5px; }		
		.gen{ display:none; }		
	</style>
	<script src="js/jquery.js"></script>
	<!-- Date time picker -->
	<link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css"/ >
	<script src="js/jquery.datetimepicker.js"></script>
</head>
<body>
<h3>Recruitment Status of <?= ucfirst($info['fname']).' '.ucfirst($info['lname']) ?></h3>
<?php if($info['isNew']==0){ ?>
	<p><b>Old Position Applied:</b> <?= $position ?> <span style="color:red">(Reprofile Needed)</span></p>
<?php }else{ ?>
	<p><b>Position Applied:</b> <?= $position ?> <?php if($info['processStat']==2){ echo '<span style="color:red">(for reprofile)</span>';} ?></p>
<?php } ?>
<table width="100%">
	<tr><td>
		<div class="wizard-steps">
		<?php
			foreach($recProcess AS $p):
				if($p['processID']<$info['process'])
					echo '<div class="completed-step"><a><span>'.$p['processID'].'</span> '.$p['processType'].'</a></div>';
				else if($p['processID']==$info['process'])
					echo '<div class="active-step"><a><span>'.$p['processID'].'</span> '.$p['processType'].'</a></div>';
				else
					echo '<div><a><span>'.$p['processID'].'</span> '.$p['processType'].'</a></div>';
			endforeach;
		?>		
		</div>
	</td></tr>	
	<tr><td><br/></td></tr>
	<tr><td align="center">
	<?php if($info['process']<5 && $info['processStat']>0){ ?>
		<div style="margin-bottom:10px;">
			<? if($info['process']>0){?><button id="rep" class="pad5px">Reprofile</button>&nbsp;&nbsp;&nbsp;<? } ?><button id="cancelApp" class="pad5px">Cancel Application</button>
		</div>
	<?php }else if($info['processStat']==0 && $info['processText']=='Failed Final Interview'){ echo '<button id="rep" class="pad5px">Reprofile</button>'; } ?>
		<div id="reprofile" style="display:none;">
			<form id="reprofileForm" action="" method="POST">
			<table>
				<tr>
					<td align="right"><b>Reprofile to:</b></td>
					<td>
						<select class="pad5px" name="newPos" id="newPos">
							<option value=""></option>
							<?php
								$dept = '';
								$oPos = array();
								$pos = $db->selectQuery("newPositions", "posID,title,dept", "active=1 ORDER BY dept, title ASC");
								$oq = $db->selectQueryArray('SELECT DISTINCT positionID FROM jobReqData WHERE status=0');
								foreach($oq AS $o):
									array_push($oPos, $o['positionID']);
								endforeach;
																
								foreach( $pos AS $p ){
									if($p['dept'] != $dept){
										if($dept!='')
											echo '</optgroup>';
											
										echo '<optgroup label="'.$p['dept'].'">';
										$dept = $p['dept'];
									}
									if($p['posID'] != $info['position']){										
										if(in_array($p['posID'], $oPos))
											echo '<option value="'.$p['posID'].'" style="color:red;">'.$p['title'].'</option>';
										else
											echo '<option value="'.$p['posID'].'">'.$p['title'].'</option>';	
									}										
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right"><b>Reason:</b></td>
					<td><textarea name="reason" id="reason" cols=37 rows=8></textarea></td>
				</tr>
				<tr>
					<td colspan=2 align="center">
						<input type="hidden" name="prevStat" value="<?= $info['process'] ?>"/>
						<input type="hidden" name="formSubmit" value="reprofile"/>
						<input type="button" id="reprofilebtn" value="Submit"/>
						<input type="button" value="Cancel" id="reprofileCancel"/>
					</td>
				</tr>
			</table>
			</form>
			<hr/>
		</div>
		
		<div id="cancelApplication" style="display:none">
			<form action="" method="POST" onSubmit="return checkCancelReason()">
				<b>Why do you want to cancel the application?</b><br/>
				<select style="width:245px; padding:5px; margin-bottom:10px;" name="cancelMessage" id="cancelMessage">
					<option value="">Other Reason</option>
				<?php
					$cQuery = $db->selectQueryArray('SELECT * FROM cancelReasons');
					foreach($cQuery AS $c):
						echo '<option value="'.$c['reason'].'|'.$c['isReprofiled'].'">'.$c['reason'].'</option>';
					endforeach;
				?>
				</select><br/>
				<textarea rows="8" cols=30 name="reason" id="cancelReason"></textarea></br>
				<input type="hidden" name="formSubmit" value="cancel">
				<input type="submit" value="Submit"/>&nbsp;&nbsp;&nbsp;	
				<input type="button" value="Cancel" onClick="location.reload();"/>			
				<hr/>
			</form>
		</div>
		
		
		<?php if($info['process']<6 && $info['processStat']==0){ ?>
		<div>
			<?php if(!empty($info['processText'])){ echo '<b>Status:</b> '.$info['processText'].'</br>';} ?>
			<b>Application has been cancelled.</b>
		</div>
		<?php }else if($info['process']<=6){?>
			<div id="processDiv" style="margin-top:20px;">
				<?php
				if($info['process']==1){
					$openPos = $db->selectSingleQuery('jobReqData', 'positionID' , 'positionID="'.$info['position'].'"');
					if(empty($openPos)){
						echo 'No job requisition for <b>'.$position.'</b>.  Reprofile needed.';
						echo '<input type="hidden" id="submitForm" value="Reprofile Needed."/>';
					}else{
						echo '<input type="hidden" id="submitForm" value="yes"/>';
					}
				}else if($info['process']==2 || $info['process']==3){
					$tests = 'iq,typing,written';
					if($info['processText'] != 'Failed Typing Test'){
						if($info['dept']=='Project Management'){ $tests .= ',pmEmail'; }
						if($info['dept']=='Press Release Writing'){ $tests .= ',pressRelease'; }
						if($info['grp']=='Design'){ $tests .= ',design'; }
						if($info['grp']=='Editing'){ $tests .= ',editing'; }
						if($info['grp']=='IT'){ $tests .= ',it'; }
					}
					
					echo '<table width="50%" cellspacing=10 cellpadding=10>';
					if($info['process']==2){ 						
						echo techTest('iq', 1);
						if($info['processText'] != 'Failed IQ Test'){
							echo techTest('typing', 0);
							echo techTest('written', 1);
							
							if($info['processText'] != 'Failed Typing Test'){
								if($info['dept']=='Project Management'){ echo techTest('pmEmail', 0); }
								if($info['dept']=='Press Release Writing'){ echo techTest('pressRelease', 0); }
								if($info['grp']=='Design'){ echo techTest('design', 0); }
								if($info['grp']=='Editing'){ echo techTest('editing', 0); }
								if($info['grp']=='IT'){ echo techTest('it', 0); }
							}
						}
						
					}else if($info['process']==3){ 
						echo techTest('hr', 1, '8');
					}
					echo '</table>';
					
					$tQuery = $db->selectQueryArray('SELECT type, testStatus FROM processStatusData WHERE appID='.$info['id'].' AND positionID='.$info['position'].' AND type IN ("'.str_replace(',','","',$tests).'")');
					
					$tests = str_replace(',','',$tests);
					foreach($tQuery AS $t){
						if($t['testStatus'] == 'passed'){ 
							$tests = str_replace($t['type'],'',$tests);
						}
					}
					
					$isTestEmpty = $tests;
					echo '<input type="hidden" id="processTests" value="'.$tests.'"/>';
				}else if($info['process']==4){ ?>
					<table width="50%" cellspacing=10 cellpadding=10>
						<?php echo techTest('final', 1, '8'); ?>
					</table>
				<?php 
				}else if($info['process']==5){ 
					$jo = $db->selectSingleQueryArray('jobReqData', 'salary, startDate' , 'status=0 AND positionID="'.$info['position'].'" ORDER BY startDate', 'LEFT JOIN salaryRange ON minSal=salID');
					$joStat = $db->selectSingleQuery('processStatusData', 'testStatus' , 'appID="'.$id.'" AND type="jobOffer" AND positionID='.$info['position']);
					$maxSal = $db->selectSingleQuery('jobReqData', 'salary' , 'status=0 AND positionID="'.$info['position'].'" ORDER BY startDate', 'LEFT JOIN salaryRange ON maxSal=salID');
				
					$gen = $db->selectQueryArray('SELECT * FROM generatedJO WHERE appID='.$id.' ORDER BY timestamp DESC');
				?>
					<table width="50%" cellspacing=10 cellpadding=10>					
						<tr>
							<td width="40%"><br/></td>
							<td><button id="generate" class="pad5px"><?php if(count($gen)==0){ echo 'Generate Job Offer'; }else{ echo 'Regenerate Job Offer'; } ?></button></td>
						</tr>
					<form method="POST" action="" onSubmit="return validGen()">
						<tr class="gen">
							<td>Position Title</td>
							<td><input type="text" name="position" value="<?= $position ?>" class="form-control" readonly /></td>
						</tr>
						<tr class="gen">
							<td>Name Prefix</td>
							<td><select name="prefix" class="form-control"><option value="Ms.">Ms.</option><option value="Mr.">Mr.</option></select></td>
						</tr>
						<tr class="gen">
							<td>Basic Salary Offer</td>						
							<td>
								<input type="text" value="<?= $jo['salary'] ?>" name="salary" id="salary" class="form-control"/>
								<input type="hidden" value="<?= $jo['salary'] ?>" id="minsalary"/>
								<input type="hidden" value="<?= $maxSal ?>" id="maxsalary"/>
								<textarea class="form-control" name="salReason" id="salReason" style="display:none;"></textarea>
							</td>
						</tr>
						<tr class="gen">
							<td>Start Date</td>
							<?php
								if(date('N')<4){
									$sD = date('F d, Y', strtotime("next monday"));
								}else{
									$sD = date('F d, Y', strtotime("next monday + 1 week"));
								}
							?>
							<td><input type="text" id="startDate" name="startDate" value="<?= $sD ?>" class="form-control"/></td>
						</tr>
					<?php if(count($gen)>0){ ?>
						<tr class="gen">
							<td>Reason</td>
							<td><textarea class="form-control" name="genReason" id="genReason"></textarea></td>
						</tr>
					<?php } ?>
						<tr class="gen">
							<td><br/></td><td><input type="submit" name="formSubmit" value="<? if(count($gen)==0){ echo 'Generate'; }else{ echo 'Regenerate'; } ?>"/></td>
						</tr>
					<?php if(count($gen)>0){ ?>
						<tr>
							<td colspan=2>		
								<h5>Generated Job Offers</h5>
								<table width="100%" cellpadding=5>					
									<thead>
										<th>Generated by</th>
										<th>Offer</th>
										<th>Start Date</th>
										<th>View</th>
									</thead>
								<?php
									$c=0;
									foreach($gen AS $g):
										if($c==0) echo '<tr bgcolor="#ce2029" style="color:#fff; font-weight:bold;">';
										else if($c%2==0) echo '<tr bgcolor="#dcdcdc">';
										else echo '<tr>';
										echo '
												<td>'.$g['offeredBy'].' | <span style="font-size:10px;">'.$g['timestamp'].'</span></td>
												<td>PhP'.$g['offer'].'</td>
												<td>'.date('F d, Y', strtotime($g['startDate'])).'</td>';
										
										$filename = 'uploads/joboffers/JobOffer'.$g['joID'].'.pdf';
										if(file_exists($filename))
											echo '<td><a href="http://careerph.tatepublishing.net/'.$filename.'" target="_blank"><img src="img/attach_file.png"></a></td>';
										else
											echo '<td><br/></td>';
										
										echo '</tr>';
										
										$c++;
									endforeach;
								?>
								</table>							
							</td>
						</tr>
					<?php } ?>						
						
						<tr <?= empty($joStat) ? '': 'class="gen"'?>><td colspan=2><hr/></td></tr>	
						<?php echo techTest('jobOffer', 1, '8', 'accepted|declined'); ?>						
					</table>
					</form>
					<?php
						$offID = $db->selectSingleQuery('generatedJO', 'joID' , 'appID='.$id.' ORDER BY timestamp DESC');
						$isAccepted = $db->selectSingleQuery('processStatusData', 'testStatus' , 'appID="'.$id.'" AND type="jobOffer" AND positionID='.$offID);
						
						if($isAccepted== 'accepted'){
					?>					
					<form method="POST" action="">
						<table width="40%">
							<tr><td colspan=2><h4>Checklist</h4></td></tr>
							<tr><td width="10%"><input type="checkbox" name="pHEnrolled" id="pHEnrolled" <? if($info['pHEnrolled']){ echo 'checked'; } ?>/></td><td>Enrolled in PayrollHero</td></tr>
							<tr><td><input type="checkbox" name="pHBDay" id="pHBDay" <? if($info['pHBDay']){ echo 'checked'; } ?>/></td><td>Updated PayrollHero Birth of Date</td></tr>
							<tr><td><input type="checkbox" name="pHCompensation" id="pHCompensation" <? if($info['pHCompensation']){ echo 'checked'; } ?>/></td><td>Double Check PayrollHero Compensation Amount</td></tr>
							<tr><td><input type="checkbox" name="batchID" id="batchID" <? if($info['batchID']){ echo 'checked'; } ?>/></td><td>Updated Batch ID</td></tr>
							<tr><td><input type="checkbox" name="referral" id="referral" <? if($info['referral']){ echo 'checked'; } ?>/></td><td>Employee Referral Program (<a href="http://goo.gl/BEK3q3">http://goo.gl/BEK3q3</a>)</td></tr>
						</table>
						<input type="hidden" name="formSubmit" value="checklist"/>
						<input type="hidden" name="appID" value="<?= $id ?>"/>
						<input type="submit" value="Update Checklist" class="btn btn-xs btn-warning">
					</form>
					<? } ?>
					
				<?php
				}else if($info['process']==6 && $info['processStat']==0){ ?>	
					<div>
						<b>Applicant is successfully hired on <?= date('F d, Y', strtotime($info['hiredDate'])) ?>. Start date is on <?= date('F d, Y', strtotime($info['startDate'])) ?>.<br/>An email has been sent to careerph.tatepublishing.net, immediate supervisor, IT, and the Job Requisition requester.</b>
					</div>				
				<?php
				}else if($info['process']==6 && $info['processStat']==1){											
					$usernameTest = 1;
					$shortFirst = 0;
					$number = 0;
					$tryNumbers = false;
					while($usernameTest > 0) {
						if($tryNumbers === true and $number == 9) {
							print "<p class='white'>Could not find a username to work.  Please contact IT.</p>";
							exit();
						} else if($tryNumbers === true or $shortFirst == strlen($info['fname'])) {
							$number++;
							$tryNumbers = true;
						} else $shortFirst++;
						
						if($tryNumbers === true) $potentialUsername = substr(strtolower($info['fname'] . $info['lname']),0,11) . $number;
						else $potentialUsername = substr(strtolower(substr($info['fname'], 0, $shortFirst) . $info['lname']),0,12);
						$usernameTest = count($ptDb->selectQueryArray("SELECT username FROM staff WHERE username = '". $potentialUsername ."'"));
					}
					
					#for email
					$e = explode(' ',$info['fname']);
					$email = $e[0].'.'.str_replace(' ','',$info['lname']).'@tatepublishing.net';
					$emailq = $ptDb->selectSingleQuery('staff', 'email' , 'email="'.$email.'"');
					if(!empty($emailq) && count($e)>1){
						$email = str_replace(' ','',$e[0].$e[1].'.'.$info['lname'].'@tatepublishing.net');
						$emailq = $ptDb->selectSingleQuery('staff', 'email' , 'email="'.$email.'"');	
					}

					if(!empty($emailq)) $email = 'Email address already assigned. Please contact IT.';
					else $email = strtolower($email);

					$jobReq = $db->selectSingleQueryArray('jobReqData', 'shift', 'status=0 AND positionID="'.$info['position'].'" ORDER BY startDate');
					
					$startD = $db->selectSingleQuery('generatedJO', 'startDate' , 'appID='.$id.' ORDER BY timestamp DESC');
					
						if(isset($_GET['hired']) && $_GET['hired']=='no'){
				?>
						<div style="color:red; margin:10px;">
							<b>Unable to create new profile.  See below for errors and re-enter values:</b><br/>
								<?= $_GET['err'] ?>
						</div>
				<?php } ?>
					<p><button class="pad5px" onClick="window.location.href='editstatus.php?id=<?= $id ?>&pos=back&stat=5'">Back to Job Offer</button></p>
					<form action="hired.php?id=<?= $id ?>" method="POST" onSubmit="return validateForm(<?=$info['isNew'] ?>);">
					<table border=0 cellspacing=0 cellpadding=0 width="80%">	
						<tr>
							<td width="40%">Username *</td>
							<td><input type="text" name="username" value="<? if(isset($_POST['username'])){ echo $_POST['username']; }else{ echo $potentialUsername; } ?>" class="form-control"></td>
						</tr>
						<tr>
							<td>Password *</td>
							<td><input type="text" name="password" id="password" value="<? if(isset($_POST['password'])){ echo $_POST['password']; }else{ echo $potentialUsername; } ?>" class="form-control"></td>
						</tr>
						<tr>
							<td>Company Email *</td>
							<td><input type="text" name="email" id="email" value="<? if(isset($_POST['email'])){ echo $_POST['email']; }else{ echo $email; } ?>" class="form-control"></td>
						</tr>
						<tr>
							<td>Office *</td>
							<td>
								<select name="office" id="office" class="form-control">
									<option value="cebu" <? if(isset($_POST['office']) && $_POST['office']=='cebu'){ echo 'selected';} ?>>Cebu</option>
									<option value="okc" <? if(isset($_POST['office']) && $_POST['office']=='okc'){ echo 'selected';} ?>>OKC</option>													
								</select>	
							</td>
						</tr>
						<tr>
							<td>Group</td>
							<td><input type="text" value="<?= $info['org'].' > '.$info['dept'].' > '.$info['grp'].' > '.$info['subgrp'] ?>" class="form-control" disabled></td>
						</tr>
						<tr>
							<td>Position Title</td>
							<td><input type="text" value="<?= $info['title'] ?>" class="form-control" disabled></td>
						</tr>
						<tr>
							<td>Start Date *</td>
							<td><input type="text" name="startdate" id="startdate" class="form-control" value="<?= date('F d, Y', strtotime($startD)); ?>" disabled></td>
						</tr>
						<tr>
							<td>Shift *</td>
							<td>
								<input type="text" name="shift" id="shift" value="<?php if(isset($_POST['shift'])){ echo $_POST['shift']; }else{ echo $jobReq['shift']; } ?>" class="form-control"/>
							</td>
						</tr>		
						<tr>
							<td>Type of Account *</td>
							<td>
								<select name="accountType" id="accountType" onChange="changeType()" class="form-control">
									<option value="1" <? if(isset($_POST['accountType']) && $_POST['accountType']=='1'){ echo 'selected';} ?>>Unique Account Type</option>
									<option value="2" <? if(isset($_POST['accountType']) && $_POST['accountType']=='2'){ echo 'selected';} ?>>Mimic User's Account</option>
								</select>
							</td>
						</tr>
						<tr id="mimictr" style="display:none;">
							<td><br/></td>
							<td style="position:relative;">
								<select name="mimicUser" id="mimicUser" class="form-control">
									<option value="">Existing username to mimic</option>
								<?php
									$st = $ptDb->selectQueryArray('SELECT username, sFirst, sLast FROM staff WHERE active="Y" AND sLast !="" ORDER BY sLast');
									foreach($st AS $s):
										echo '<option value="'.$s['username'].'">'.$s['sLast'].', '.$s['sFirst'].'</option>';
									endforeach;
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td><b>Other Info</b></td>
							<td><br/></td>
						</tr>
						<tr>
							<?php
								$last_payID = $ptDb->selectSingleQuery('eData', 'py' , 's="PH" ORDER BY py DESC');
							?>
							<td>Payroll ID <span id="cebuPay" style="font-size:10px; font-style:italic; color:#808080;">(Cebu's last payroll id is <?= $last_payID ?>)</span></td>
							<td>
								<input type="hidden" id="payrollID" value="<?php echo $last_payID+1; ?>"/>
								<input type="text" name="payroll" id="payroll" value="<? if(isset($_POST['payroll'])){ echo $_POST['payroll']; }else{ echo $last_payID+1; } ?>" class="form-control" readonly />
								</td>
						</tr>
						<tr>
							<td>SSS *</td>
							<td><input type="text" name="sss" id="sss" value="<? if(isset($_POST['sss'])){ echo $_POST['sss']; } ?>" class="form-control" placeholder="00-0000000-0"/></td>
						</tr>
						<tr>
							<td>TIN *</td>
							<td><input type="text" name="tin" id="tin" value="<? if(isset($_POST['tin'])){ echo $_POST['tin']; } ?>" class="form-control" placeholder="000-000-000-0000"/></td>
						</tr>
						<tr>
							<td>Philhealth</td>
							<td><input type="text" name="philhealth" id="philhealth" value="<? if(isset($_POST['philhealth'])){ echo $_POST['philhealth']; } ?>" class="form-control" placeholder="00-000000000-0"/></td>
						</tr>
						<tr>
							<td>HDMF</td>
							<td><input type="text" name="hdmf" id="hdmf" value="<? if(isset($_POST['hdmf'])){ echo $_POST['hdmf']; } ?>" class="form-control" placeholder="0000-0000-0000"/></td>
						</tr>
						<tr>
							<td colspan=2 align="right">Fields with (*) are required fields.</td>
						</tr>
						<tr>
							<td colspan=2 align="right"><br/></td>
						</tr>
						<tr>
							<td colspan=2 align="right">
								<input type="hidden" name="appID" value="<?= $_GET['id'] ?>"/>
								<input type="submit" value="Create PT Account"/>
							</td>
						</tr>
					</table>
					</form>
				<?php
				}
				?>
			</div>
			
			<?php if($info['processStat']==1 && $info['process']<6 && is_admin()){ 
				if(!empty($isTestEmpty)){
					if($info['process']==2){
						echo '<br/><input type="submit" value="Submit Later" onClick="window.location.href=\'editstatus.php?id='.$id.'&pos=back&stat=3\'" class="btn btn-xs btn-warning">';
					}else if($info['process']==3){
						echo '<br/><input type="submit" value="Back to Technical Testing" onClick="window.location.href=\'editstatus.php?id='.$id.'&pos=back&stat=2\'" class="btn btn-xs btn-warning">';
					}
				}
			?>
			
			<form action="" method="POST" onSubmit="return validateAdvance()">	
				<div style="margin-top:15px;" id="advanceProcessDiv">
					<input type="hidden" name="formSubmit" value="advanceProcess"/>	
					<input type="hidden" name="process" id="process" value="<?= $info['process']+1 ?>"/>	
					<input type="submit" value='Advance to "<?= $recProcess[$info['process']+1]['processType'] ?>"' class="btn btn-primary">
				</div>
			</form>
			<?php } ?>		
	<?php } ?>
					
	</td></tr>	
<table>

<script type="text/javascript">
	$(function () { 
		$('#startDate').datetimepicker({
			format:'F d, Y',
			timepicker:false
		}); 

		$('#startdate').datetimepicker({
			format:'F d, Y',
			timepicker:false
		}); 

		if($('#accountType').val() == 2){
			$('#mimictr').css('display', '');
		}
		
		$('#office').change(function() {
			$('#payroll').val('');
			$('#cebuPay').css('display', 'none');
			if( $('#office').val() == 'cebu' ){
				$('#payroll').val($('#payrollID').val());
				$('#payroll').attr('disabled','disabled');
				$('#cebuPay').css('display', '');
			}else{
				$("#payroll").removeAttr('disabled');
			}
		});	
		
		$('#cancelMessage').change(function(){
			v = $('#cancelMessage').val().split('|');			
			$('#cancelReason').val(v[0]);
			
			if($('#cancelMessage').val()==''){
				$('#cancelReason').css('display','');
			}else if($(this).val()=='Application Withdrawn|0'){
				$('#cancelReason').val('');
				$('#cancelReason').css('display','');
			}else{
				$('#cancelReason').css('display','none');
			}
		});
		
	   
	

		$('#rep').click(function(){
			$('#reprofile').css('display', 'block');
		});
		
		$('#cancelApp').click(function(){
			$('#cancelApplication').css('display', 'block');
		});
		
		$('#generate').click(function(){
			$(this).css('display', 'none');
			$('.gen').removeClass('gen');
		});
		
		$('#reprofileCancel').click(function(){
			location.reload();
		});
		
		$('#reprofilebtn').click(function(){
			if($('#newPos').val() == '' || $('#reason').val() == ''){
				alert('Please input all fields.');
			}else{
				$.post("editstatus.php?pos=reprof&posid="+$('#newPos').val(),				
				function(data){
					if(data==''){
						alert('No job requisition for this position.');
					}else{
						$("#reprofileForm").submit();
					}
				});
			}
		});	
	
	});
	
	function validateAdvance(){
		valid = true;
		var process = $('#process').val()- 1;
		
		if(process == 1 && $('#submitForm').val() != 'yes'){
			alert($('#submitForm').val());
			valid = false;
		}else if(process==2 || process==3){
			var tests = $('#processTests').val();
			
			if( tests != '' ){
				valid = false;
				alert('Unable to advance to the next status.  Please check test results.');
			}	
			
			if(process==3 && valid!=false){
				if($('#hr').val()=='' || $('#hr').val()=='Failed' || $('#hr').is(':disabled')==false){
					valid = false;
					alert('Unable to advance to the next status.  Please check HR interview result.');
				}
			}
		}else if(process==4){
			if($('#final').val()=='' || $('#final').val()=='Failed'  || $('#final').is(':disabled')==false){
				alert('Unable to advance to the next status.  Please check Final Interview result.');
				valid = false;
			}				
		}else if(process==5){
			if($('#jobOffer').val()=='' || $('#jobOffer').val()=='Declined' || $('#jobOffer').is(':disabled')==false){
				alert('Unable to advance to the next status.  Please check Job Offer result.');
				valid = false;
			}else{
				if(	$("#pHEnrolled").prop('checked') == false ||
					$("#pHBDay").prop('checked') == false ||
					$("#pHCompensation").prop('checked') == false ||
					$("#batchID").prop('checked') == false ||
					$("#referral").prop('checked') == false
				){
					valid = false;
					alert('Unable to advance to the next status.  Please check checklist.');
				}
				
			}
		}
		
		return valid;
	}
	
	function showDiv(d){
		var dval = $('#'+d).val(); 
		if(dval=='')
			$('#'+d+'Div').css('display', 'none');
		else
			$('#'+d+'Div').css('display', 'block');
	}
	
	function checkTest(t){
		if($('#'+t+'test').val()=='' || $('#'+t+'reason').val()==''){
			alert('Please input all fields.');
		}else{
			$.post("editstatus.php?pos=testing",
			{
				appID:'<?= $id ?>',
				type: t,
				testStatus: $('#'+t).val(),
				positionID: '<?= $info['position'] ?>',
				reason: $('#'+t+'reason').val(),
				examiner: '<?= $_SESSION['u'] ?>',
			},
			function(){
				if((t=='final' && $('#'+t).val()=='failed') || (t=='jobOffer' && $('#'+t).val()=='declined')){
					if (confirm('Do you want to reprofile this candidate?')) {
						$('#reprofile').css('display', 'block');
						$('#processDiv').css('display', 'none');
						$('#advanceProcessDiv').css('display', 'none');
					} else {
						location.reload();
					}
				}else{
					location.reload();
				}
			});
		}			
	}
	
	//for hired
	function changeTitle(title){
		if(title == ''){
			$('#newTitle').css('display','none');
			$('#title').val('');
		}else{
			$('#newTitle').css('display','');
			$('#title').val(title);
		}
	}
			
	function changeType(){
		$('#mimictr').css('display', 'none');
		if($('#accountType').val() == 2){
			$('#mimictr').css('display', '');
		}
	}
	
	function validateForm(isNew){
		var valid = true;
		var validText = '';
		var ssspattern = new RegExp(/^[0-9]{2}-[0-9]{7}-[0-9]{1}$/);
		var tinpattern = new RegExp(/^[0-9]{3}-[0-9]{3}-[0-9]{3}-[0-9]{4}$/); 
		var phpattern = new RegExp(/^[0-9]{2}-[0-9]{9}-[0-9]{1}$/);  
		var hdmfpattern = new RegExp(/^[0-9]{4}-[0-9]{4}-[0-9]{4}$/);
		
		if($('#office').val() == '')
			validText += '\t- Office\n';		
		if($('#startdate').val()=='')
			validText += '\t- Start date\n';	
		if($('#shift').val()=='')
			validText += '\t- Shift\n';
		if($('#password').val()=='')
			validText += '\t- Password\n';
		if($('#email').val()=='')
			validText += '\t- Email\n';
		if($('#accountType').val()=='')
			validText += '\t- Type of account\n';
		else if($('#accountType').val()=='2' && $('#mimicUser').val()=='')
			validText += '\t- Existing username\n';
		if(isNew==0 && $('#position').val()==''){
			validText += '\t- Assign to job requisition\n';
		}
		if($('#sss').val()==''){
			validText += '\t- SSS\n';
		}else if(ssspattern.test($('#sss').val())==false){
			validText += '\t- SSS Invalid\n';
			valid = false;			
		}
		if($('#tin').val()==''){
			validText += '\t- TIN\n';
		}else if(tinpattern.test($('#tin').val())==false){
			validText += '\t- TIN Invalid\n';
			valid = false;			
		}
		
		if($('#philhealth').val() != '' &&  phpattern.test($('#philhealth').val())==false){
			validText += '\t- Philhealth Invalid\n';
			valid = false;	
		}
		if($('#hdmf').val() != '' &&  hdmfpattern.test($('#hdmf').val())==false){
			validText += '\t- HDMF Invalid\n';
			valid = false;	
		}
		
		if(validText != ''){
			valid = false;
			alert('Please complete missing values: \n'+validText);
		}
		
		return valid;
	}
	
	function validGen(){
		valid = true;
		txt = '';
		if($('#salary').val()==''){
			txt += 'Salary offer empty\n';
			valid = false;
		}else if( $.isNumeric($('#salary').val())== false){
			txt += 'Invalid salary offer (enter digits only)\n';
			valid = false;
		}else if( ($('#salary').val() < $('#minsalary').val() || $('#salary').val() > $('#maxsalary').val()) && $('#salReason').val()==''){	
			valid = false;
			if($('#salary').val() > $('#maxsalary').val())
				r = confirm('The amount you entered exceeded the maximum salary offer entered for the job requisition.\nPlease provide a rationale for this.');
			else if($('#salary').val() < $('#minsalary').val())
				r = confirm('The amount you entered is below the minimum salary offer entered for the job requisition.\nPlease provide a rationale for this.');
				
			if(r){
				$('#salReason').css('display', '');
			}else{
				$('#salReason').css('display', 'none');
			}
			
		}		
		if($('#startDate').val()==''){
			txt += 'Invalid start date\n';
			valid = false;
		}
		if($('#genReason').length!=0 && $('#genReason').val()==''){
			txt += 'Reason is empty\n';
			valid = false;
		}
		
		if(txt != '') alert(txt);
		
		return valid;
	}
	
	function advanceS(s){
		$.post("editstatus.php?advance=stat",
		{
			appID:'<?= $_GET['id'] ?>',
			status:s
		});	
	}
	
	function checkCancelReason(){
		if($('#cancelReason').val()==''){
			alert('Please state reason.');
			return false;
		}else{
			return true;
		}
	}
</script>

</body>
</html>