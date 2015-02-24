<?php
require 'config.php';

require "includes/header.php";

$username = '';
if(isset($_GET['key']))
	$username = $db->selectSingleQuery("pt_users", "username", "md5(username) = '".$_GET['key']."'");

if(empty($username) && !isset($_SESSION['u'])){
	echo 'Sorry, you do not have access to this page.';
}else{ 
	if(isset($_GET['key']))
		$_SESSION['u'] = $username;	
	
	$job = $db->selectQuery('jobReqData', 'jobReqData.*, title AS position', 'jobReqID='.$_GET['id'], 'LEFT JOIN newPositions ON posID = positionID');

	$closed = 0;
	$position = $job[0]['position'];
	foreach( $job AS $j ){
		if($j['status']) $closed++;
	}

?>
<div style="text-align:center;"><h2>Below are the matched candidates for the Job Requisition: <?= $position ?> (ID:<?= $job[0]['jobReqID'] ?>)</h2></div>
<table border=0 cellspacing=0 cellpadding=0 width="100%">
	<tr>
		<td align="center">
			<div style="background-color:#fff; width:100%; border-radius:10px; padding:15px 0;">					
			<table cellspacing="0" cellpadding="4" width="95%" bordercolor="#000000" border="1">
				<tr style="background-color:#af1e22; color:#fff;">
					<th>Date application is received</th>
					<th>Name</th>
					<th>Phone Number</th>
					<th>Email Address</th>
					<th>Status of application</th>
				</tr>
			<?php					
				$query = $db->selectQueryArray('
					SELECT applicants.id, CONCAT(fname, " ",lname ) AS name, mnumber, email, applicants.date_created, process, processStat, processType, title
					FROM applicants
					LEFT JOIN recruitmentProcess ON processID = process
					LEFT JOIN newPositions ON position = posID
					WHERE 
					isNew = 1
					AND title = "'.$position.'"
				');
				if(count($query)>0){
					foreach($query AS $q){
						echo '<tr>
								<td>'.date('m/d/Y h:s', strtotime($q['date_created'])).'</td>
								<td><a href="http://careerph.tatepublishing.net/view_info.php?id='.$q['id'].'">'.$q['name'].'</a></td>
								<td>'.$q['mnumber'].'</td>
								<td>'.$q['email'].'</td>';
							if($q['process']==6 AND $q['processStat']==0){
								echo '<td bgcolor="#E0EEE0">'.$q['processType'].'</td>';
							}else{
								echo '<td>'.$q['processType'].'</td>';
							}
						
						echo '</tr>';
					}
				}else{
					echo '<tr>
							<td colspan=5>No applicant matches.</td>
						</tr>';
				}				
			?>
			</table>				
			</div>
		</td>
	</tr>
</table>


<script type="text/javascript">
	function whatGroup( group ){ 
		$('#firstDiv').css('display', 'none');
		$('#secondDiv').css('display', 'block');
		$('#whatGroup').val(group);
		$('#status').val('group');
		
		if(group == 1)
			$('.groupName').html('Tate Publishing');
		else if(group == 2)
			$('.groupName').html('Tate Music Group (TMG)');
		else
			$('.groupName').html('Key Marketing Group');
		
	}
	
	function whatRequest( yesNo ){			
		$('#secondDiv').css('display', 'none');
		$('#div_'+$('#whatGroup').val()).css('display', 'block');
		$('#whatRequest').val(yesNo);
		$('#status').val('request');
		
		$('#whatReq').css('display', 'block');
		if($('#whatRequest').val() == 'No')
			$('#whatReqSpan').html('This is a REPLACEMENT for the ');
		else
			$('#whatReqSpan').html('YES, requesting for a new position. For the ');
		
		if($('#div_'+$('#whatGroup').val()).length == 0 )
			$('#error').css('display', 'block');
		
	}
	
	function changeDiv(parentK, k, text){
		$('#div_'+parentK).css('display', 'none');		
		$('.text').append(' - '+text);
		var stat = $('#status').val();
		
		if( $('#div_'+k).length != 0 )			
			$('#div_'+k).css('display', 'block');
		else if( $('#pos_'+k).length != 0 )
			$('#pos_'+k).css('display', 'block');
		else
			$('#error').css('display', 'block');
		
		if(stat == 'request'){
			$('#status').val('subgroup');
			$('#subGroup1').val(text);
		}else if(stat == 'subgroup'){
			$('#status').val('subgroup2');
			$('#subGroup2').val(text);
		}else if(stat == 'subgroup2'){
			$('#status').val('subgroup3');
			$('#subGroup3').val(text);
		}		
	}
	
	function lastDiv( parentK, text ){
		$('#pos_'+parentK).css('display', 'none');
		$('#lastDiv').css('display', 'block');
		$('#position').val(text);	
		$('.text').append(' - '+text);
	}
	
</script>




<?php 
}

require "includes/footer.php";
?>