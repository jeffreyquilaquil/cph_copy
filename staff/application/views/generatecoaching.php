<h2>Generate Coaching Form for <?= $row->name ?></h2>
<hr/>
<div id="form1" class="">
	<table class="tableInfo">
		<tr>
			<td width="40%">When will this Coaching be discussed to the employee?</td>
			<td><input type="text" class="forminput datepick" id="coachedDate" value="<?= date('F d, Y') ?>" onBlur="changeEnd();"/></td>
		</tr>
		<tr>
			<td>How long will this coaching period be?</td>
			<td>
			<?php
				echo '<select class="forminput50" id="longVal" onChange="changeEnd();">';
				for($i=1; $i<=12; $i++){
					echo '<option value="'.$i.'">'.$i.'</option>';
				}
				echo '</select>';
				echo '&nbsp;&nbsp;';
				echo '<select class="forminput50" id="longName" onChange="changeEnd();">';
					echo '<option value="Week">Week</option>';
					echo '<option value="Month">Month</option>';
				echo '</select>';
			?>
			</td>
		</tr>
		<tr>
			<td>When will the performance evaluation be done?</td>
			<td><input type="text" id="coachedEnd" value="<?= date('F d, Y', strtotime('+1 week')) ?>" class="forminput" disabled="disabled"/></td>
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
		<td>In the space below, write down the <b id="numaspect" value="1">first</b> aspect of behaviours or performance that need to be corrected or improved.
		<textarea class="forminput" rows="4" id="aspect"></textarea>
		</td>
	</tr>
	<tr>
		<td>What exactly is the specific behaviour or performance level that is expected from the employee?<br/>
			<i style="color:#555;">Avoid writing very vague statements. (e.g instead of "Carl must be more diligent with work.", write "Carl must not miss any one deadline within the coaching period.")</i><br/>
			<textarea class="forminput" rows="4" id="expected"></textarea>
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
			<td><textarea class="forminput" id="support1">I will make sure to provide guidance to <?= $row->name ?> whenever needed.</textarea></td>
		</tr>
		<tr>
			<td><textarea class="forminput" id="support2">I will make sure all questions are answered and that I am available whenever the employee needs my assistance.</textarea></td>
		</tr>
		<tr>
			<td><textarea class="forminput" id="support3">I will give feedback as to employee's progress on the goals listed above.</textarea></td>
		</tr>
		<tr id="trsupport4" class="hidden">
			<td><textarea class="forminput" id="support4"></textarea></td>
		</tr>
	</table>
		
	<p>&nbsp;</p>
	<div class="tacenter width100">
		<button style="padding:5px 150px;" onClick="$('#trsupport4').removeClass('hidden'); $(this).hide();">Add one more support from supervisor</button><br/>
		<button style="padding:5px 150px;" onClick="generateForm();">That's it! Generate the Coaching Form</button>
	</div>
</div>
<script src="<?= $this->config->base_url() ?>js/moment.js" type="text/javascript"></script>
<script type="text/javascript">
	var cform2 = [];
	var cform = {};
				
	$(function(){		
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
	
	function changeEnd(){
		cdate = $('#coachedDate').val();
		if(cdate!=''){
			num = $('#longVal').val();
			ntype = $('#longName').val();
			
			cVal = moment(cdate).add(num, ntype).format("MMMM DD, YYYY");
			$('#coachedEnd').val(cVal);			
		}else{
			$('#coachedEnd').val('');
		}	
		
		dName = $('#longName').prop("selected", true).val();
		if($('#longVal').val()>1)
			$('#longName option:selected').text(dName+'s');
		else
			$('#longName option:selected').text(dName);
	}
	
	function tocoachingdetails(){
		if($('#coachedDate').val()=='' || $('#areaofimprovementText').val()==''){
			alert('All fields are required.');
		}else{
			//assigning values to global variable
			cform['coachedDate'] = $('#coachedDate').val();
			cform['coachedPeriod'] = $('#longVal').val()+' '+$('#longName').val();
			cform['coachedEnd'] = $('#coachedEnd').val();
			cform['whocoached'] = $('#whocoached').val();
			cform['areaofimprovement'] = $('#areaofimprovementText').val();
				
			//proceed to form 2
			$('#form1').addClass('hidden');
			$('#form2').removeClass('hidden');
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
				
				cform['aspectExpected'+spanval] = $('#aspect').val()+'++||++'+$('#expected').val();
				
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
		cform['aspectExpected'+spanval] = $('#aspect').val()+'++||++'+$('#expected').val();
		
		//proceed to form 3
		$('#form2').addClass('hidden');
		$('#form3').removeClass('hidden');
	}
	
	function generateForm(){
		if($('#support1').val()=='' || $('#support2').val()=='' || $('#support3').val()=='' || (!$('#trsupport4').hasClass('hidden') && $('#support4').val()=='')){
			alert('All fields are required.');
		}else{
			for(v=1; v<=4; v++){
				cform['support'+v] = $('#support'+v).val();
			}
						
			$.post('<?= $this->config->item('career_uri') ?>',{cform}, 
			function(){
				alert('Coaching form has been generated.');
			});
		}		
	}
</script>