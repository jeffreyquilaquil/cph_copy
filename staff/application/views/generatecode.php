<style type="text/css">
	.insideTbl td{ padding:0px; }
</style>
<h2>Generate Code</h2>
<hr/>
<?php 
	if($this->access->accessFullHR==false && $this->user->level==0){
		echo 'You do not have permission to access this page.';
	}else{
?>
<table class="tableInfo">
	<tr>
		<td width="30%"><b>For whom are you generating this code?</b><br/><i>Name of employee requesting the code.</i></td>
		<td><select name="forWhom" class="forminput">
				<option value=""></option>
			<?php
				foreach($staffs AS $s){
					echo '<option value="'.$s->empID.'">'.$s->lname.', '.$s->fname.'</option>';
				}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td><b>Why is the employee being allowed to violate COC leaves timelines?</b><br/><i>A valid reason is required.</i></td>
		<td><input type="text" name="whyOHwhy" class="forminput"/></td>
	</tr>
	<tr>
		<td><br/></td><td><button id="generate" class="btnclass">Generate code</button></td>
	</tr>
	</table>
<?php 
	if(count($codes)>0){
		echo '<br/><br/>';
		echo '<h3>Generated Codes</h3>';
		echo '<table class="tableInfo">';
		echo '<tr class="trhead">
				<td>Generated Code</td>
				<td>Details</td>
				<td>Status</td>
			</tr>';
		foreach($codes AS $c):
			echo '<tr>';
				echo '<td><b>'.$c->code.'</b></td>';
				
				echo '<td>';
					echo '<table class="insideTbl" width="100%">';
						echo '<tr><td width="18%">Date Generated:</td><td><b>'.date('M d, Y h:i a', strtotime($c->dategenerated)).'</b></td></tr>';
						if(!empty($c->forWhomName)) echo '<tr><td>For Whom:</td><td><b>'.$c->forWhomName.'</b></td></tr>';
						if(!empty($c->why)) echo '<tr><td>Reason:</td><td><b>'.$c->why.'</b></td></tr>';
						if(!empty($c->useByName)) echo '<tr><td>Used By:</td><td><b>'.$c->useByName.'</b></td></tr>';
						if($c->dateUsed!='0000-00-00 00:00:00') echo '<tr><td>Date Used:</td><td><b>'.date('M d, Y h:i a', strtotime($c->dateUsed)).'</b></td></tr>';
						if(!empty($c->type)) echo '<tr><td>Type:</td><td><b>'.$c->type.'</b></td></tr>';						
					echo '</table>';
				echo '</td>';
				
				echo '<td>';
					if($c->status==0) echo 'Expired';
					else if($c->status==1) echo 'Active';
					else echo 'Redeemed';
				echo '</td>';
			echo '</tr>';			
		endforeach;
		echo '</table>';
	}
?>
	<script type="text/javascript">
		$(function(){
			$('#generate').click(function(){
				if($('select[name=forWhom]').val()=='' || $('input[name=whyOHwhy]').val()==''){
					alert('All fields are required.');
				}else{
					$(this).attr('disabled', 'disabled');
					$('<?= '<img src="'.$this->config->base_url().'css/images/small_loading.gif" width="20px"/>' ?>').insertAfter(this);
					
					$.post('<?= $this->config->item('career_url').$_SERVER['REQUEST_URI'] ?>',
						{submitType:'gencode', forWhom:$('select[name=forWhom]').val(), why:$('input[name=whyOHwhy]').val()},
					function(d){
						alert('Generated Code:\n\n'+d);
						parent.$.fn.colorbox.close();
					});
				}
			});
		});
	</script>
<?php
}
?>



