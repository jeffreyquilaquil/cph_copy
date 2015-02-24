<?php
require 'config.php';

if(!isset($_GET['no']) && $_GET['no']!='head'){
	require "includes/header.php";
}else{
	echo '<link href="css/yeti.bootstrap.min.css" rel="stylesheet">';
}

$cancel = 0;
if(isset($_GET['c']) && $_GET['c']=='yes'){
	$cancel = 1;
}

if(isset($_POST['cancelJR']) && $_POST['cancelJR']=='yes' && $_POST['cancelRemarks'] != ''){
	$db->updateQuery('jobReqData', array('status'=> 2, 'cancelRemarks' => $_POST['cancelRemarks'], 'closedBy'=>$_SESSION['u'], 'dateClosed'=>'NOW()'),'jobReqID="'.$_POST['reqID'].'" AND status=0');
	echo 'Job Requisition has been cancelled.';
	exit;
}

$infoQ = $db->selectQueryArray('
	SELECT jobReqData.*, org, dept, grp, subgrp, title, (SELECT salaryAllowance FROM salaryRange WHERE salID=jobReqData.minSal) AS minSal, (SELECT salaryAllowance FROM salaryRange WHERE salID=jobReqData.maxSal) AS maxSal
	FROM jobReqData
	LEFT JOIN newPositions ON posID = positionID
	WHERE jobReqID = "'.$_GET['id'].'"
');

foreach($infoQ AS $in){
	$info = $in;
} 

$rName = $ptDb->selectSingleQueryArray('staff', 'sFirst, sLast' , 'username="'.$info['requestor'].'"');
?>
<div class="container">
	<fieldset>
		<legend>Job Requisition Info</legend>
	</fieldset>
<?php if($cancel==1){ ?>
	<form action="" method="POST" onSubmit="return checkCancel();">
		Note why you want to cancel the job requisition:<br/>
		<textarea id="cancelRemarks" cols=75 rows=10 name="cancelRemarks"></textarea><br/>
		<input type="hidden" name="reqID" value="<?= $_GET['id'] ?>"/>
		<input type="hidden" name="cancelJR" value="yes"/>
		<input type="Submit" value="Submit"/>
	</form>
<?php }else{ ?>	
	<table width="90%" cellpadding=5 cellspacing=5>
		<tr>
			<td>Job Requisition ID</td>
			<td><?= $info['jobReqID'] ?></td>
		</tr>
		<tr bgcolor="#DCDCDC">
			<td>Date Requested</td>
			<td><?= date('m/d/Y h:s', strtotime($info['dateSubmitted'])) ?></td>
		</tr>
		<tr>
			<td>Requestor</td>
			<td><?= $rName['sFirst'].' '.$rName['sLast']; ?></td>
		</tr>
		<tr bgcolor="#DCDCDC">
			<td>Is this a new position?</td>
			<td><?= ucfirst($info['requestType']).' Position' ?></td>
		</tr>
		<tr>
			<td>Organization</td>
			<td><?= $info['org'] ?></td>
		</tr>
		<tr bgcolor="#DCDCDC">
			<td>Department</td>
			<td><?= $info['dept'] ?></td>
		</tr>
		<tr>
			<td>Group</td>
			<td><?= $info['grp'] ?></td>
		</tr>
		<tr bgcolor="#DCDCDC">
			<td>Subgroup</td>
			<td><?= $info['subgrp'] ?></td>
		</tr>
		<tr>
			<td>Name of Position</td>
			<td><?= $info['title'] ?></td>
		</tr>
		
		<tr bgcolor="#DCDCDC">
			<td>Target Start Date</td>
			<td><?= $info['startDate'] ?></td>
		</tr>
		<tr>
			<td>Immediate Supervisor</td>
			<td><?= $info['supervisor'] ?></td>
		</tr>
		<tr bgcolor="#DCDCDC">
			<td>Minimum Salary Offer</td>
			<td><?= $info['minSal'] ?></td>
		</tr>
		<tr>
			<td>Maximum Salary Offer</td>
			<td><?= $info['maxSal'] ?></td>
		</tr>
		<tr bgcolor="#DCDCDC">
			<td>Shift</td>
			<td><?= $info['shift'] ?></td>
		</tr>
		<tr>
			<td>Additional Remarks for HR</td>
			<td><?= $info['remarks'] ?></td>
		</tr>
		<tr bgcolor="#DCDCDC">
			<td>Number Requested</td>
			<td><?= $info['num'] ?></td>
		</tr>
	<?php if($info['status']==2){ ?>
		<tr>
			<td>Cancelled Reason</td>
			<td><?= $info['cancelRemarks'] ?></td>
		</tr>
	<?php } ?>
		<tr><td colspan=2></td></tr>
		<tr>
			<td colspan=2>
				<table width="100%" cellpadding=5 cellspacing=5 border=1>
					<tr bgcolor="#DCDCDC">
						<td colspan=2 align="center"><b>Status</b></td>
					</tr>
					<?php
						$closed = 0;
						$cancelled = 0;
						$open = 0;
						foreach($infoQ AS $in){
							if($in['status']==0)
								$open++;
							else if($in['status']==1)
								$closed++;
							else
								$cancelled++;
						}
					?>
					<tr>
						<td width=50%>Open</td>
						<td width=50% align="center"><?php echo $open; if($open>0 && is_admin()){ ?> <br/><b><a href="/req-info.php?id=<?= $_GET['id'] ?>&c=yes&no=head" onclick="return confirmDelete()" style="color:red;">Cancel</a><?php } ?></b></td>
					</tr>
					<tr>
						<td>Closed</td>
						<td align="center"><?= $closed ?></td>
					</tr>
					<tr>
						<td>Cancelled</td>
						<td align="center"><?= $cancelled ?></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<?php } ?>
</div>

<script type="text/javascript">
function confirmDelete() {
    return confirm('Are you sure you want to cancel open job requisition?')
}

function checkCancel(){ 
	if(document.getElementById('cancelRemarks').value==''){
		alert('Please input reason.');
		return false;
	}else
		return true;
}
</script>
<?php 
if(!isset($_GET['no']) && $_GET['no']!='head')
	require "includes/footer.php";
?>