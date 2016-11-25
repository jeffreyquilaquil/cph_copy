<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
</head>
<body>
	<form action="" method="POST" onsubmit="return submitReason()">
		<strong style="font-size:15px;">Are you sure you want to cancel <?php echo $name; ?>'s evaluation form?</strong>
		<hr>
		<strong>If yes, please state a valid reason below. Otherwise, click on the back button below.</strong>
		<textarea placeholder="Type message here..." class="forminput" id="cancelReason" name="cancelReason"></textarea>
		
			<input type="hidden" value="<?php echo $notifyId ?>" name="notifyId">
			<input type="hidden" value="<?php echo $empId ?>" name="empId">		

		<div style="float:right;">
			<input type="button" value="Back" class="btnclass">
			<input type="Submit" value="Cancel Evaluation" class="btnclass btngreen">
		</div>
		<br><br><br>
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