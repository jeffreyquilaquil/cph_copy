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
	<strong style="font-size:15px;">Are you sure you want to cancel <?php echo $name; ?>'s evaluation form?</strong>
	<hr>
	<strong>If yes, please state a valid reason below. Otherwise, click on the back button below.</strong>
	<textarea placeholder="Type message here..." class="forminput" id="cancelReason"></textarea>
	<br>
	<div style="right:-1px;" style="width:100%;align:right">
		<input type="button" value="Back" class="btnclass">
		<input type="button" value="Cancel Evaluation" class="btnclass" id="btnCancel" onclick="submitReason()">
	</div>
</body>
	<script type="text/javascript">
	function submitReason(){
		if($('#cancelReason').val() == ""){
			alert("Please state a reason");
		}else{
			$data = {
				'notifyId' : "<?php echo $notifyId ?>",
				'cancelReason' : $('#cancelReason').val(),
				'canceller':"<?php echo $this->user->empID ?>",
				'cancelDate': "<?php echo date('Y-m-d H:i:s') ?>"
			}

			$.ajax({
				data:{'data':$data},
				url:'../../saveCancelEvaluation',
				type:'POST',
			}).done(function(r){
				console.log('done');
				console.log(r);
			});
		}
	};

	</script>
</html>