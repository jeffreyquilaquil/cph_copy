<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<script type='text/javascript' src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<style>
	.page-header{margin:0px;}
	.container{margin-left: -10px;}
	.form-horizontal{margin-left: 10px;}
	#colorboxcontainer{width:100%;}
	.container{width:100%;}
	.fa-spin {
	  -webkit-animation: fa-spin 2s infinite linear;
	  animation: fa-spin 2s infinite linear;
	}
	@keyframes fa-spin {
	  0% {
	    -webkit-transform: rotate(0deg);
	    transform: rotate(0deg);
	  }
	  100% {
	    -webkit-transform: rotate(359deg);
	    transform: rotate(359deg);
	  }
	}
</style>
<div class='container'>
	<!-- Preview -->
	<div class="panel panel-default panel-danger">
		<div class="panel-heading">Requests Details</div>
		<div class="panel-body">
			<p>Current Status: <strong><?=$this->dbmodel->getSingleField('kudosRequestStatusLabels','statusName','statusID ='. $kudosRequestStatus)?></strong></p>
			<p><strong>Reason of Kudos Bonus.</strong></p>
			<P><?= str_replace("\n",'<br/>',str_replace("\r\n", "<br/>", $evaluationContent[0]['kudosReason'])); ?></P>
			 
			<?php
				if( $kudosRequestStatus == 5){
			?>
				<p><strong>Reason of Kudos Bonus.</strong></p>
				<P><?= str_replace('\\\\n','<br/>',str_replace('\r\n',"<br/>",mysql_real_escape_string($evaluationContent[0]['reasonForDisapproving']))); ?></P>
			<?php		
				}
			?>
			<p><strong>Recommendation amount of Kudos Bonus.</strong></p>
			<input type='number' id='requestorAmount' value="<?=$evaluationContent[0]['kudosAmount']?>" disabled class='form-control' />
		</div>
	</div>
	
</div>

