<h2>Generate Coaching Form for <?= $row->name ?></h2>
<hr/>
<div id="form1" class="">
	<table class="tableInfo">
		<tr>
			<td width="40%">When will this Coaching be discussed to the employee?</td>
			<td><input type="text" class="forminput datepick" id="coachedDate" value="<?= date('F d, Y') ?>" onBlur="changeEnd();"/></td>
		</tr>
		<tr>
			<td width="40%">When will be the performance evaluation?</td>
		<?php
			$segment3 = $this->uri->segment(3);
			if($segment3!='') $segval = urldecode($segment3);
			else $segval = date('F d, Y', strtotime('+2 weeks'));
		?>
			<td><input type="text" class="forminput datepick" id="coachedEval" value="<?= $segval ?>" onBlur="changeEnd();"/></td>
		</tr>
		
		<tr>
			<td>How long will this coaching period be?</td>
			<td><input type="text" id="coachedPeriod" value="2 weeks" class="forminput" disabled="disabled"/></td>
		</tr>
		<tr>
			<td>Who will do the coaching for this employee?</td>
			<td>
				<select id="whocoached" class="forminput">
				<?php
					$stitle = '';
					foreach($supervisors AS $s):
						if($s->empID==$row->supervisor) $stitle = $s->title;
						echo '<option value="'.$s->empID.'" '.(($s->empID==$row->supervisor)?'selected="selected"':'').' data-title="'.$s->title.'">'.$s->name.'</option>';
					endforeach;
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Position Title of Coach</td>
			<td><input type="text" id="coachedTitle" value="<?= $stitle ?>" class="forminput" disabled="disabled"/></td>
		</tr>
		<tr>
			<td>What is the required area of improvement?</td>
			<td>
				<select id="areaofimprovement" class="forminput">
				<?php
					echo '<option value=""></option>';
					foreach($areaofimprovementArr AS $k=>$a):
						echo '<option value="'.$k.'">'.$a.'</option>';
					endforeach;
				?>
				</select>
			</td>
		</tr>
		<tr id="trareatext">
			<td><br/></td>
			<td><input type="text" id="areaofimprovementText" value="" class="forminput" placeholder="If others, please specify"/></td>
		</tr>
	</table>

	<p>&nbsp;</p>
	<div class="tacenter width100">
		<button style="padding:5px 150px;" onClick="tocoachingdetails();">Next - Coaching Details</button>
	</div>
</div>
<!---- End if form1 ---->

<div id="form2" class="hidden">
	<table class="tableInfo">
	<tr class="trhead">
		<td>In this page you are to write down up to four specific behaviors or aspect of performance that you need to be corrected.</td>
	</tr>
	<tr>
		<td>In the space below, write down the <b id="numaspect" value="1">first</b> aspect of behaviours or performance that need to be corrected or improved. <span class="fs11px" style="color:#555; font-style:italic">(Maximum characters: 270)</span>
		<textarea class="forminput" rows="4" id="aspect" maxlength="270"></textarea>
		</td>
	</tr>
	<tr>
		<td>What exactly is the specific behaviour or performance level that is expected from the employee? <span class="fs11px" style="color:#555; font-style:italic">(Maximum characters: 270)</span><br/>
			<i style="color:#555;">Avoid writing very vague statements. (e.g instead of "Carl must be more diligent with work.", write "Carl must not miss any one deadline within the coaching period.")</i><br/>
			<textarea class="forminput" rows="4" id="expected" maxlength="270"></textarea>
		</td>
	</tr>
	</table>
	
	<p>&nbsp;</p>
	<div class="tacenter width100">
		<button style="padding:5px 150px;" onClick="addAI();" id="addAIbtn">Add another Area for Improvement</button><br/>
		<button style="padding:5px 150px;" onClick="nextIM();">Next - Support from Immediate Supervisor</button>
	</div>
</div>
<!---- End if form2 ---->

<div id="form3" class="hidden">
	<table class="tableInfo">
		<tr class="trhead">
			<td>The aim of coaching is to help the employee succeed and correct behaviour and NOT to punish him/her for underperforming or mishehaving. The employee will not be able to succeed without support from immediate supervisor.<br/>In this page, please write down all the support you can give to the employee to succeed iin this improvement exercise.</td>
		</tr>
		<tr>
			<td>
			In the three boxes below, write down one support that you will give to the employee to succeed in achieving the expected outcomes.<br/>
			<i style="color:#555;">Sample support are already inputted in the boxes below, however, you may edit them as necessary.</i>
			<td>
		</tr>
		<tr>
			<td><textarea class="forminput" id="support1" maxlength="270">I will make sure to provide guidance to <?= $row->name ?> whenever needed.</textarea></td>
		</tr>
		<tr>
			<td><textarea class="forminput" id="support2" maxlength="270">I will make sure all questions are answered and that I am available whenever the employee needs my assistance.</textarea></td>
		</tr>
		<tr>
			<td><textarea class="forminput" id="support3" maxlength="270">I will give feedback as to employee's progress on the goals listed above.</textarea></td>
		</tr>
		<tr id="trsupport4" class="hidden">
			<td><textarea class="forminput" id="support4" maxlength="270"></textarea></td>
		</tr>
	</table>
		
	<p>&nbsp;</p>
	<div class="tacenter width100">
		<button style="padding:5px 150px;" onClick="$('#trsupport4').removeClass('hidden'); $(this).hide();">Add one more support from supervisor</button><br/>
		<button style="padding:5px 150px;" onClick="generateForm();">That's it! Generate the Coaching Form</button>
	</div>
</div>
<script type="text/javascript">
	var cform = {};
				
	$(function(){
		changeEnd();
		$('#whocoached').change(function(){
			$('#coachedTitle').val($('option:selected', this).attr('data-title'));
		});
				
		$('#areaofimprovement').change(function(){
			if($(this).val()==''){
				$('#areaofimprovementText').val('');
				$('#trareatext').removeClass('hidden');
			}else{
				$('#areaofimprovementText').val($('option:selected', this).text());
				$('#trareatext').addClass('hidden');
			}
		});
	});
	
	
	function tocoachingdetails(){
		if($('#coachedDate').val()=='' || $('#coachedEval').val()=='' || $('#areaofimprovementText').val()==''){
			alert('All fields are required.');
		}else{
		
			var start = new Date($('#coachedDate').val());
			var eval = new Date($('#coachedEval').val());
			var timeDiff = Math.abs(eval.getTime() - start.getTime());
			var days = Math.ceil(timeDiff / (1000 * 3600 * 24)); 
			if(days<14){
				alert('Minimum coaching period is 2 weeks.');
			}else{		
				//assigning values to global variable
				cform['coachedDate'] = $('#coachedDate').val();
				cform['coachedPeriod'] = $('#coachedPeriod').val();
				cform['coachedEval'] = $('#coachedEval').val();
				cform['whocoached'] = $('#whocoached').val();
				cform['areaofimprovement'] = $('#areaofimprovementText').val();
					
				//proceed to form 2
				$('#form1').addClass('hidden');
				$('#form2').removeClass('hidden');
			}
		}
	}
	
	function addAI(){
		if($('#aspect').val()=='' || $('#expected').val()==''){
			alert('All fields are required.');
		}else{
			spanval = $('#numaspect').attr('value');
			if(spanval<=4){
				ttext = 'first';
				newval = parseInt(spanval)+1;
				if(newval==2) ttext = 'second';
				else if(newval==3) ttext = 'third';
				else if(newval==4) ttext = 'fourth';
				$('#numaspect').attr('value', newval);
				$('#numaspect').text(ttext);
				
				
				aspectExpected = $('#aspect').val()+'++||++'+$('#expected').val();				
				cform['aspectExpected'+spanval] = aspectExpected.replace(/(\r\n|\n|\r)/gm," ");
				
				$('#aspect').val('');
				$('#expected').val('');	
				if(newval==4){
					$('#addAIbtn').hide();
				}
			}else{
				$('#addAIbtn').hide();
			}
		}
	}
	
	function nextIM(){
		spanval = $('#numaspect').attr('value');
		aspectExpected = $('#aspect').val()+'++||++'+$('#expected').val();	
		cform['aspectExpected'+spanval] = aspectExpected.replace(/(\r\n|\n|\r)/gm," ");
				
		//proceed to form 3
		$('#form2').addClass('hidden');
		$('#form3').removeClass('hidden');
	}
	
	function changeEnd(){
		start = $('#coachedDate').val();
		eval = $('#coachedEval').val();
		
		if(start!='' && eval!=''){	
			var hindi = '';
			var start = new Date(start);
			var eval = new Date(eval);
			var timeDiff = Math.abs(eval.getTime() - start.getTime());
			var days = Math.ceil(timeDiff / (1000 * 3600 * 24)); 
			
			weeks = Math.floor(days/7);
			days = days - (weeks*7);
			if(weeks==1) hindi += weeks+' week';
			else if(weeks>1) hindi += weeks+' weeks';
						
			if(days>0){
				if(hindi!='') hindi+= ' and ';
				if(days==1) hindi += days+ ' day';
				else if(days>1) hindi += days+ ' days';
			}
			
			$('#coachedPeriod').val(hindi);			
		}else{
			$('#coachedPeriod').val('');
		}
	}
	
	function generateForm(){
		if($('#support1').val()=='' || $('#support2').val()=='' || $('#support3').val()=='' || (!$('#trsupport4').hasClass('hidden') && $('#support4').val()=='')){
			alert('All fields are required.');
		}else{
			for(v=1; v<=4; v++){
				thisval = $('#support'+v).val();
				cform['support'+v] = thisval.replace(/(\r\n|\n|\r)/gm," ");
			}
			
			
			displaypleasewait();
			cform['submitType'] = 'generateC';
			$.post('<?= $this->config->item('career_uri') ?>',cform, 
			function(d){
				location.href='<?= $this->config->base_url().'coachingform/expectation/' ?>'+d+'/';
			});
		}		
	}
</script>