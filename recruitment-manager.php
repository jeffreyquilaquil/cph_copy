<?php
require 'config.php';

require "includes/header.php";

$status = array();
$statusQuery = $db->selectQuery('applicant_status', 'id, status', '1');
foreach($statusQuery AS $s){
	$status[$s['id']] = $s['status'];
}

if(isset($_POST) && !empty($_POST)){
	if( $_POST['status'] == 2 ){
		header('Location:hired.php?id='.$_POST['applicant_id']);
		exit;
	}else if( $_POST['old_stat'] != 'Hired' ){	
		$db->updateQuery('applicants', array('status'=> $_POST['status']), 'id='.$_POST['applicant_id']);
		$insertArr = array(
				'applicant_id' => $_POST['applicant_id'],
				'pt_username' => $_SESSION['u'],
				'remarks' => '<span class="success">Changed status from <b>'.$_POST['old_stat'].'</b> to <b>'.$status[$_POST['status']].'</b></span>',
				'date_created' => 'NOW()'
				);
		$db->insertQuery('applicant_feedbacks', $insertArr);
	}
}
$openJobs = $db->selectQueryArray('SELECT reqID, positionID FROM jobReqData WHERE status=0');
$hahe = array();
foreach($openJobs AS $o){
	$hahe[] = $o['positionID'];
}
$openJobsText = implode(',', $hahe);

$appOpenJobReq = $db->selectQueryArray('SELECT applicants.id, CONCAT(fname," ",lname) AS name, email, mnumber, position, processType, applicants.date_created, title, processText, processStat 
		FROM applicants 
		LEFT JOIN recruitmentProcess ON processID=process 
		LEFT JOIN newPositions ON posID=position 
		WHERE processStat=1 AND isNew = 1 AND position IN ('.$openJobsText.') 
		ORDER BY applicants.date_created DESC');

$appNoJobReq = $db->selectQueryArray('SELECT applicants.id, CONCAT(fname," ",lname) AS name, email, mnumber, position, processType, title, applicants.date_created, processText, processStat 
		FROM applicants 
		LEFT JOIN newPositions ON posID=position 
		LEFT JOIN recruitmentProcess ON processID=process 
		WHERE processStat=1 AND isNew = 1 AND position NOT IN ('.$openJobsText.')
		ORDER BY applicants.date_created DESC');
		
$petrifiedQ = $db->selectQueryArray('SELECT applicants.id, CONCAT(fname," ",lname) AS name, email, mnumber, position, processType, title, applicants.date_created, processText FROM applicants LEFT JOIN jobReqData ON positionID=position LEFT JOIN newPositions ON posID=position LEFT JOIN recruitmentProcess ON processID=process WHERE processStat=0 AND processText IN ("Failed IQ Test", "Failed Typing Test", "Failed Written Comprehension Test", "Failed HR Interview") GROUP BY applicants.id ORDER BY applicants.date_created DESC');
$infoQuery = $db->selectQuery("applicants", "applicants.id, CONCAT(fname, ' ', lname) AS name, email, mnumber, applicants.date_created, isNew, process, processType, processText, processStat, IF(isNew = 0, (SELECT title FROM positions WHERE position=positions.id LIMIT 1), (SELECT title FROM newPositions WHERE position=posID LIMIT 1)) as title", "1 ORDER BY date_created DESC", "LEFT JOIN recruitmentProcess ON processID=process");

?>

<link href="css/jquery.dataTables.css" rel="stylesheet">
<script type="text/javascript" language="javascript" src="js/jquery.dataTables.min.js"></script>
<style type="text/css">
	.tab-content{ padding-bottom:35px; }
</style>
<div class="container">	
	<?php 
		if(isset($_GET['hired']) && !empty($_GET['hired'])){
			echo '<div style="text-align:center;">
				<p class="text-success">Newly hired employee: <b>'.urldecode($_GET['hired']).'</b></p>
				<p>HR PT link: <a href="https://pt.tatepublishing.net/detailshr.php?key='.$_GET['uname'].'" target="_blank">https://pt.tatepublishing.net/detailshr.php?key='.$_GET['uname'].'</a></p>			
			</div>';
		}
	?>
	<fieldset>
		<legend>Recruitment Manager</legend>
	</fieldset>
	
	<div style="display:none;">
		<div id="prodpreview">
			<br/>
			Select an email to send:<br/><br/>
			<center>
			   <a class="iframe" href="/emailTemplate.php?type=invitationtoapplyopenposition"><button style="width:370px; padding:10px;">Invitation to Apply for Open Positions</button></a><br/>
			   <a class="iframe" href="/emailTemplate.php?type=internalannouncementopenposition"><button style="width:370px; padding:10px;">Internal Announcement for Open Positions</button></a>
			</center>
		</div>
	</div>
	
	
	<button style="float:right; margin:8px 120px 0 0;" class="iframeSmall" href="#prodpreview">Send Email</button>
	<ul class="tabs">
		<li class="tab-link current" data-tab="tab-1">Applicants with Open Job Requisitions (<?= count($appOpenJobReq) ?>)</li>
		<li class="tab-link" data-tab="tab-2">Applicants with No Open Job Requisitions (<?= count($appNoJobReq) ?>)</li>
		<li class="tab-link" data-tab="tab-3">Petrified (<?= count($petrifiedQ) ?>)</li>
		<li class="tab-link" data-tab="tab-4">All Applicants (<?= count($infoQuery) ?>)</li>
	</ul>
	<div id="tab-1" class="tab-content current">
		<?php
		if( count($appOpenJobReq) > 0 ){
		?>
		*Applicants below are still in "<b>in progress</b>" recruitment status but no open job requisitions.
		<table class="applicants" cellpadding=5 cellspacing=5 width="100%">
			<thead style="border-bottom:1px solid #000;">
			<tr>
				<th>ID</th>
				<th>Applicant's Name</th>
				<th>Email</th>
				<th>Contact Number</th>
				<th>Position Applied</th>
				<th>Date Applied</th>
				<th>Recruitment Status</th>
				<th></br></th>
			</tr>
			</thead>
			<tbody>
			<?php
				$app1=0;
				foreach($appOpenJobReq AS $aa){
					if($app1%2==0) echo '<tr bgcolor="#dcdcdc">';
					else echo '<tr>';
					$txt = '';
					echo '<td>'.$aa['id'].'</td>';
					if(!empty($aa['processText'])) $txt = '<span style="color:red;">('.$aa['processText'].')</span>';
					echo '<td><a href="view_info.php?id='.$aa['id'].'">'.$aa['name'].'</a></td>
							<td><a href="mailto:'.$aa['email'].'">'.$aa['email'].'</a></td>
							<td>'.$aa['mnumber'].'</td>
							<td>'.$aa['title'].'</td>
							<td>'.date('Y-m-d',strtotime($aa['date_created'])).'</td>
							<td>'.$aa['processType'].' '.$txt.'</td>';
							
						if($aa['processStat']==0)
							echo '<td><br/></td>';
						else
							echo '<td><a class="iframe" href="editstatus.php?id='.$aa['id'].'"><img src="images/edit_icon.png"/></a></td>';
						
					echo '</tr>';
					$app1++;
				}
			?>			
			</tbody>
		</table>
		<?php }else{
				echo 'No applicants with no matching open job requisitons.';
			} ?>
	</div>
	<div id="tab-2" class="tab-content">
		<?php
		if( count($appNoJobReq) > 0 ){
		?>
		*Applicants below are still in "<b>in progress</b>" recruitment status but no open job requisitions.
		<table class="applicants" cellpadding=5 cellspacing=5 width="100%">
			<thead style="border-bottom:1px solid #000;">
			<tr>
				<th>ID</th>
				<th>Applicant's Name</th>
				<th>Email</th>
				<th>Contact Number</th>
				<th>Position Applied</th>
				<th>Date Applied</th>
				<th>Recruitment Status</th>
				<th></br></th>
			</tr>
			</thead>
			<tbody>
			<?php
				$app=0;
				foreach($appNoJobReq AS $a){
					if($app%2==0) echo '<tr bgcolor="#dcdcdc">';
					else echo '<tr>';
					$txt = '';
					echo '<td>'.$a['id'].'</td>';
					if(!empty($a['processText'])) $txt = '<span style="color:red;">('.$a['processText'].')</span>';
					echo '<td><a href="view_info.php?id='.$a['id'].'">'.$a['name'].'</a></td>
							<td><a href="mailto:'.$a['email'].'">'.$a['email'].'</a></td>
							<td>'.$a['mnumber'].'</td>
							<td>'.$a['title'].'</td>
							<td>'.date('Y-m-d', strtotime($a['date_created'])).'</td>
							<td>'.$a['processType'].' '.$txt.'</td>';
							
						if($a['processStat']==0)
							echo '<td><br/></td>';
						else
							echo '<td><a class="iframe" href="editstatus.php?id='.$a['id'].'"><img src="images/edit_icon.png"/></a></td>';
						
					echo '</tr>';
					$app++;
				}
			?>			
			</tbody>
		</table>
		<?php }else{
				echo 'No applicants with no matching open job requisitons.';
			} ?>
	</div>
	<div id="tab-3" class="tab-content">
		<?php
		if( count($petrifiedQ) > 0 ){
		?>
		*Applicants who failed IQ, Typing, Written Comprehension and HR Interview.
		<table class="applicants" cellpadding=5 cellspacing=5 width="100%">
			<thead style="border-bottom:1px solid #000;">
			<tr>
				<th>ID</th>
				<th>Applicant's Name</th>
				<th>Email</th>
				<th>Contact Number</th>
				<th>Position</th>
				<th>Date Applied</th>
				<th>Recruitment Status</th>
			</tr>
			</thead>
			<tbody>
			<?php
				$app=0;
				foreach($petrifiedQ AS $a){
					if($app%2==0) echo '<tr bgcolor="#dcdcdc">';
					else echo '<tr>';
					$txt = '';
					echo '<td>'.$a['id'].'</td>';
					if(!empty($a['processText'])) $txt = '<span style="color:red;">('.$a['processText'].')</span>';
					echo '<td><a href="view_info.php?id='.$a['id'].'">'.$a['name'].'</a></td>
							<td><a href="mailto:'.$a['email'].'">'.$a['email'].'</a></td>
							<td>'.$a['mnumber'].'</td>
							<td>'.$a['title'].'</td>
							<td>'.date('Y-m-d', strtotime($a['date_created'])).'</td>
							<td>'.$a['processType'].' '.$txt.'</td>
						</tr>';
					$app++;
				}
			?>			
			</tbody>
		</table>
		<?php }else{
				echo 'No applicants.';
			} ?>
	</div>
	<div id="tab-4" class="tab-content">
	<table class="applicants" class="hover stripe row-border">
		<thead>
			<tr>
				<th>ID</th>
				<th>Applicant's Name</th>
				<th>Email</th>
				<th>Contact Number</th>
				<th>Position Applied</th>
				<th>Date Applied</th>
				<th>Status</th>
				<th></br></th>
			</tr>
		</thead>
		<tbody>
		<?php				
			foreach($infoQuery AS $info){
				$nw = '';
				if($info['isNew']==0) $nw = ' [<b>old</b>]';
				echo '<tr>
						<td>'.$info['id'].'</td>
						<td><a href="view_info.php?id='.$info['id'].'">'.$info['name'].'</a></td>
						<td><a href="mailto:'.$info['email'].'">'.$info['email'].'</a></td>
						<td>'.$info['mnumber'].'</td>
						<td>'.$info['title'].$nw.'</td>
						<td>'.date('Y-m-d', strtotime($info['date_created'])).'</td>';
				if(empty($info['processText'])) echo '<td>'.$info['processType'].' <span style="color:green;">[in progress]</span></td>';
				else echo '<td><span style="color:red;">'.$info['processText'].'</span></td>';
				
				if($info['processStat']==0)
					echo 	'<td><br/></td>';
				else
					echo 	'<td><a class="iframe" href="editstatus.php?id='.$info['id'].'"><img src="images/edit_icon.png"/></a></td>';
					
				echo	'</tr>';			
			} 
		?>		
		</tbody>
	</table>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	$(".iframe").colorbox({iframe:true, width:"990px", height:"600px"});
	$(".iframeSmall").colorbox({inline:true, width:"450px", height:"300px"});
	
	var oTable;
	oTable = $('.applicants').dataTable({
		"aaSorting": [[4,"desc"],[5,"desc"],[0,"asc"]],
		"sDom": 'RC<"clear">lfrtip',
		"oLanguage": {
			"sSearch": "Search all columns:"
		},
		"bSortCellsTop": true
	});
	
	$('ul.tabs li').click(function(){
		var tab_id = $(this).attr('data-tab');

		$('ul.tabs li').removeClass('current');
		$('.tab-content').removeClass('current');

		$(this).addClass('current');
		$("#"+tab_id).addClass('current');
	});
});

function changeStatus(id){
	$("#statusForm_"+id).submit();
}
</script>
<?php 
require "includes/footer.php";
?>