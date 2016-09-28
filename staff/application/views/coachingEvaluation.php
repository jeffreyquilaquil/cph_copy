<?php
	$num = 0;
	if($row->selfRating==''){
		echo '<h2>Coaching Self-Evaluation</h2>';
	}else{
		echo '<h2>Coaching Evaluation</h2>';
	}
	echo '<hr/>';
	
	if($row->status==3 && $this->user->empID==$row->empID_fk){
		echo '<div class="tacenter">';
			echo '<p>You are about to acknowledge that your immediate supervisor has discussed the performance evaluation with you and that you have signed the performance evaluation document. Once you completed this form, your performance evaluation on this Coaching Period will be saved in your personnel file.</p>';
			echo '<p style="font-size:14px;">To acknowledge the receipt of the performance evaluation, please type in the box below the final weighed rating discussed to you by your coach/immediate supervisor:</p>';
			echo '<input id="empRa" type="text" style="width:100px; padding:20px; border:1px solid #ccc; text-align:center; font-size:16px;" onKeyup="rates(this)">';
			echo '<div style="font-size:16px; font-weight:bold; margin-top:15px;" id="txtrate"></div>';
			echo '<p>&nbsp;</p>';
			echo '<button style="padding:5px 50px; text-align:center;" onClick="submitAck();"><b>That\'s it! Submit Acknowledgement</b><br/>
				I confirm that my coach/immediate supervisor has discussed the performance evaluation to me and that I fully understand and accept the basis of the evaluation.
			</button>';
		echo '</div>';
	}else if($this->uri->segment(3)=='submitted'){
		echo '<p class="errortext">Rating has been submitted.</p>';
	}else if($row->selfRating=='' && $this->user->empID!=$row->empID_fk){
		echo '<p class="errortext">Self rating is not yet submitted.</p>';
	}else if($this->user->empID==$row->empID_fk && $row->selfRating!=''){
		echo '<p class="errortext">Self rating already submitted.</p>';
	}else if($this->user->empID==$row->coachedBy && $row->supervisorsRating=='' && $row->HRoptionStatus<2){
		echo '<p class="errortext">Signed coaching form is not yet uploaded by HR.  Click <a href="'.$this->config->base_url().'sendEmail/followupcoaching/'.$row->coachID.'/">here</a> to send email to HR..</p>';
	}else if(($row->selfRating=='' && $row->empID_fk==$this->user->empID) || (($row->coachedBy==$this->user->empID || $row->supervisor==$this->user->empID) && ($row->supervisorsRating=='' || $row->status==2))){ 		
		$aspect = explode('--^_^--', $row->coachedAspectExpected);
		$support = explode('--^_^--', $row->coachedSupport);
		$selfRating = explode('|', $row->selfRating);
		$supRating = explode('|', $row->supervisorsRating);
		$selfNotes = explode('+++', $row->selfRatingNotes);
		
		$num = count($aspect);
		for($i=0; $i<$num; $i++){
			$adata = explode('++||++', $aspect[$i]);
			echo '<div id="aspect'.$i.'" class="aspectdiv hidden">';
			
			if($i==0){
				if($this->user->empID==$row->empID_fk){
					echo '<p>Hello '.$this->user->fname.'!</p>';
					echo '<p>Your coaching period started on '.date('F d, Y', strtotime($row->coachedDate)).' and your performance is due on '.date('F d, Y',strtotime($row->coachedEval)).'.</p>';
				}else{
					echo '<p>Hello '.$this->user->name.'!</p>';
					echo $row->name.'\'s coaching period started on '.date('F d, Y', strtotime($row->coachedDate)).' and the performance evaluation is due on '.date('F d, Y', strtotime($row->coachedEval)).'.<br/>'.$row->name.' has already submitted his/her self-rating. It is your turn to give your performance evaluation.';
				}
			}
				echo '<p><b>Performance '.($i+1).'</b></p>';
				echo '<p><b>Please rate '.(($this->user->empID==$row->empID_fk)?'your':$row->name.'\'s').' performance on the below behaviour that needs to be corrected or improved:</b></p>';
				echo '<div style="width:97%; border:1px solid #ccc; padding:10px;">'.$adata[0].'</div>';
				echo '<p><b>The specific behaviour expected is:</b></p>';
				echo '<div style="width:97%; border:1px solid #ccc; padding:10px;">'.$adata[1].'</div>';
				
				if($this->user->empID!=$row->empID_fk){
					echo '<p><b>The employee\'s Self-Rating was:</b></p>';
					echo '<div style="border:1px solid #ccc; width:170px; padding:10px; text-align:center;"><b>'.$selfRating[$i].'</b></div>';
				}
				if(isset($selfNotes[$i]) && !empty($selfNotes[$i])){
					echo '<p>Employee\'s Note:<br/>
						<i>'.$selfNotes[$i].'</i></p>';
				}
				
				echo '<p><b>Please rate how well did you achieve the expected behaviour:</b><br/>
					Rate 0 if you fail to meet the expectation on any level<br/>
					Rate 1 if you meet the expectation<br/>			
					Rate 2 if you went beyond and exceeded the expectations.
				</p>';				
				echo '<select name="rate" class="forminput">';
				echo '<option value=""></option>';
				for($e=0; $e<=2; $e++){
					echo '<option value="'.$e.'" '.(($row->status==2 && $supRating[$i]==$e)?'selected="selected"':'').'>'.$e.'</option>';
				}
				echo '</select>';
				echo '<p>Write any comment or note below:<br/><textarea class="forminput" name="note"></textarea></p>';
				echo '<p><br/></p>';
				echo '<div class="tacenter">';
					if($i!=0)
						echo '<button style="padding:5px 150px;" onClick="viewAspect('.($i-1).')">Back (Review your Ratings)</button><br/>';
					if(($i+1)<$num)
						echo '<button style="padding:5px 150px;" onClick="viewAspect('.($i+1).')">Rate for the next Area for Improvement</button><br/>';
					if(($i+1)==$num){
						if($pageType=='self')
							echo '<button style="padding:5px 150px;" onClick="submitRating(\''.$pageType.'\', 0);">That\'s it! Submit this form to Coach for evaluation.</button>';
						else{
							echo '<button style="padding:5px 150px;" onClick="submitRating(\''.$pageType.'\', 0);">Next (Employee\'s Weighed Score)</button>';
						}
					}
						
					if($pageType=='self')
						echo '<p>Note that your self-rating consist 20% of the final rating. Your coach\'s evaluation of your performance consist 80% of the final evaluation rating.</p>';
				echo '</div>';
			echo '</div>';
		}
		
		echo '<div id="eweighteddiv" class="tacenter hidden">';
			echo '<center><div id="score" style="border:1px solid #ccc; padding:10px; width:300px; font-weight:bold; font-size:14px; margin-bottom:20px;"></div></center>';
			
			echo '<button style="padding:5px 150px;" onClick="viewAspect('.($num-1).')">Back (Review your Ratings)</button>';
			echo '<p>Note that your self-rating consist 80% of the final rating. Employee\'s self-evaluation of his/her performance consist 20% of the final evaluation rating.</p>';
			echo '<p><b>IMPORTANT NOTE: Once your ratings are submitted, the ratings may no longer be changed.</b></p>';
			echo '<p>You may save your ratings and generate the tentative form for discussion with employee. The status will change the Feedback Session in Progress. In the event that there are changes after the discussion with the employee, you may go back and review change ratings, however, the first version of the form will be permanently saved.</p>';
			echo '<button style="padding:5px 150px;" onClick="submitRating(\''.$pageType.'\', 2);">Save my Ratings and Generate Tentative Form (pdf)</button>';
			echo '<p>If you have already discussed the ratings with the employee, and no change will be required, you may lock in your ratings and generate the final evaluation form.</p>';
			echo '<button style="padding:5px 150px;" class="btngreen" onClick="submitRating(\''.$pageType.'\', 3);">That\'s it! Print the Coaching Evaluation Form</button>';
		echo '</div>';
					
		echo '<input type="hidden" id="rating" value=""/>';
		echo '<textarea class="hidden" id="notes"></textarea>';
		
		$empRating = 0;
		$emp = explode('|', $row->selfRating);
		$cntGrow = count($emp);
		for($m=0; $m<$cntGrow; $m++){
			$empRating += $emp[$m];
		}
		$empRating = ($empRating/$cntGrow)*0.20;
		echo '<input type="hidden" id="emprating" value="'.$empRating.'"/>';
	}else{
		echo '<p class="errortext">You do not have access to evaluate this coaching.</p>';
	}	
	
	$whatview = 0;
	if($row->status==2)
		$whatview = 'feedback';
?>

<script type="text/javascript">
	$(function(){
		viewAspect('<?= $whatview ?>');
	});
	
	function viewAspect(id){
		if(id=="feedback"){
			submitRating('coach', 4);
		}else{
			if($('#aspect'+(id-1)+' select[name="rate"]').val()==''){
				alert('Please input your rating.');
			}else{
				$('.aspectdiv').addClass('hidden');		
				$('#eweighteddiv').addClass('hidden');
				$('#aspect'+id).removeClass('hidden');
			}			
		}
	}
	
	//type = self or coach
	function submitRating(type, sub){
		valid=true;
		$('#rating').val('');
		$('#notes').val('');
		for(i=0;i<<?= $num ?>; i++){
			prate = $('#rating').val();
			pnotes = $('#notes').val();
			if($('#aspect'+i+' select[name="rate"]').val()=='')
				valid = false;
			$('#rating').val(prate+'|'+$('#aspect'+i+' select[name="rate"]').val());			
			$('#notes').val(pnotes+'+++'+$('#aspect'+i+' textarea[name="note"]').val());			
		}
		
		if(type=='coach'){			
			$('#eweighteddiv').removeClass('hidden');
			$('.aspectdiv').addClass('hidden');
			
			rates = $('#rating').val();
			rarr = rates.split('|');
			n = 0;
			for(r=1; r<rarr.length;r++){
				n = n+parseInt(rarr[r]);
			}
			wvalue = (n/(rarr.length-1))*0.80;
			wvalue = wvalue+parseFloat($('#emprating').val());
			wvalue = wvalue.toFixed(2);
			$('#score').html('Employee\'s Weighed Score: '+wvalue+'<br/>'+coachingScore(wvalue));			
		}
		
		if(valid==false){
			alert('Please review your ratings. There is a performance with no rating.');
		}else{
			if(type=='self'){
				displaypleasewait();
				$.post('<?= $this->config->item('career_uri') ?>',{submitType:'self', selfRating:$('#rating').val(), selfRatingNotes:$('#notes').val()}, 
				function(){
					location.href='<?= $this->config->base_url().'coachingEvaluation/'.$row->coachID.'/submitted/' ?>';
				});
			}else if(sub!=0 && sub!=4){
				displaypleasewait();
				$.post('<?= $this->config->item('career_uri') ?>',{submitType:'coach', supervisorsRating:$('#rating').val(), supervisorsRatingNotes:$('#notes').val(), status:sub, finalRating:wvalue}, 
				function(){
						location.href='<?= $this->config->base_url().'coachingform/evaluation/'.$row->coachID.'/' ?>';
				});
			}			
		}
		
	}
	
	function coachingScore(score){
		scoretext = '';
		if(score>=8.00 && score<=10.00)
			scoretext = 'Excellent (Exceeds expectations)';
		else if(score>=5.00 && score<=7.99)
			scoretext = 'Good (Meets expectations)';
		else if(score>=3.00 && score<=4.99)
			scoretext = 'Fair (Improvement needed)';
		else if(score<=2.99)
			scoretext = 'Poor (Unsatisfactory)';
		
		return scoretext;
	}
	
	function rates(d){
		if($(d).val()=='')
			score = '';
		else
			score = coachingScore($(d).val());
		$('#txtrate').html(score);
	}
	
	function submitAck(){
		if($('#empRa').val()!='<?= $row->finalRating?>'){
			alert('The number does not match the rating that the immediate supervisor locked in.');
		}else{
			displaypleasewait();
			$.post('<?= $this->config->item('career_uri') ?>',{submitType:'acknowledge'}, 
			function(){
				location.href='<?= $this->config->base_url().'coachingEvaluation/'.$row->coachID.'/' ?>';
			});
		}
	}
</script>