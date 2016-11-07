<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<style type="text/css">
		#btnCancel{
			background: rgb(0,128,0);
			color:white;
		}
	</style>
</head>
<body>
	<form action="" method="POST" onsubmit="return submitReason()">
		<strong style="font-size:15px;">Are you sure you want to cancel <?php echo $name; ?>'s evaluation form?</strong>
		<hr>
		<strong>If yes, please state a valid reason below. Otherwise, click on the back button below.</strong>
		<textarea placeholder="Type message here..." class="forminput" id="cancelReason" name="cancelReason"></textarea>
		<br>
		<div style="right:-1px;" style="width:100%;align:right">
			<input type="hidden" value="<?php echo $notifyId ?>" name="notifyId">
			<input type="hidden" value="<?php echo $empId ?>" name="empId">
			<input type="button" value="Back" class="btnclass">
			<input type="Submit" value="Cancel Evaluation" class="btnclass" id="btnCancel">
		</div>
	</form>
</body>
	<script type="text/javascript">
	function submitReason(){
		if($('#cancelReason').val() == ""){
			alert("Please state a reason");
			return false;
		}else{
			displaypleasewait();
			return true;
		}
	};

	</script>
</html>