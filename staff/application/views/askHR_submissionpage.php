<style>
	input[type="text"]{
		height: 30px;
		width:380px;
	}
	td{
		padding-bottom: 15px;

	}


</style>

	<div class = 'submission-form'>

		<form method="POST" action="<?php $this->config->base_url();?>hr_cs/askhr">
			<table>
				<tr>
					<td>SUBJECT<br><small>What is your concern all about?</small></td>
					<td><input type="text" name="hr_subject"></td>
				<tr>
					<td>CC<br><small>Who else needs to know about this</small></td>
					<td><input type="text" name="hr_cc"></td>
				</tr>
				<tr>
					<td style="vertical-align: top">DETAILS<br><small>Please explain the details of your concern</small></td>
					<td><textarea style="width: 380px; height: 170px; resize:none " name="hr_details"></textarea></td>
				</tr>
				<tr>
					<td></td>
					<td align="right">

					<!--
						<input type="file" name="attachment[]" id="attachment" onchange="document.getElementById('moreAttachmentLink').style.display = 'block';" />
						<div id="moreAttachment"></div>
						<div id="moreAttachmentLink" style="display:none;"><a href="javascript:add_anotherattachment();">+ Add another attachment</a></div> 					
					

					-->

						<div class="sup_docs_div">
							<input type="file" name="supporting_docs[]" class="sup_docs" /><br/>
						</div>

						<div class="add_docs_label">
							<a href="#" class="label_add_docs">+ Add another documents</a>
						</div>

					</td>	
				</tr>
				<tr>
					<td></td>
					<td align="right"><input type="submit" value="SEND"></td>
				</tr>
				</tr>
			</table>		
		</form>	
		
	</div>

	<script type="text/javascript">
		 
		/*
		var upload_number=1;
		
		function add_anotherattachment() {
		
		var div= document.createElement("div");
		var input = document.createElement("input");
		
		input.setAttribute("type", "file");
		input.setAttribute("name", "attachment[]"+upload_number);
		
		div.appendChild(input);
		document.getElementById("moreAttachment").appendChild(div);
		
		upload_number++;
		}

		*/

		(function(){
		var sup_counter = 1;
		('a.label_add_docs').hide();
		
		('a.label_add_docs').click(function(){
			sup_counter += 1;			
			if( sup_counter <= 5 ){				
				('.sup_docs_div').append('<input type="file" name="supporting_docs[]" class="sup_docs" /><br/>');
				if( sup_counter == 5 ){
					('a.label_add_docs').hide();								
				}
			} 
		});
		('.sup_docs').change(function(){
			if( sup_counter = 1 ){
				('a.label_add_docs').show();
			}			
		});
	});

</script>


