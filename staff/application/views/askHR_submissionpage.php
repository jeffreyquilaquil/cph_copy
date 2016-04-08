<style>
	
	 input[type="text"]{
		height: 30px;
		width:100%;
	}
	td{
		padding-bottom: 15px;

	}

	.button {
    background-color: #006600;
    border: none; 
    color: white;
    padding: 7px 30px 7px 30px;
    margin-top:5px;
    text-align: center;
    text-decoration: none;
    display: inline-block;

 
}
</style>

	<div>

		<form method="POST" action="<?php $this->config->base_url();?>hr_cs">
			<table class="tableInfo">
				<tr>
					<td>SUBJECT<br><small>What is your concern all about?</small></td>
					<td><input type="text" name="hr_subject"></td>
				<tr>
					<td>CC<br><small>Who else needs to know about this</small></td>
					<td><input type="text" name="hr_cc"></td>
				</tr>
				<tr>
					<td style="vertical-align: top">DETAILS<br><small>Please explain the details of your concern</small></td>
					<td><textarea style="height: 170px; resize:none " name="hr_details"></textarea></td>
				</tr>
				<tr>
					<td>
						
					</td>
					<td align="right">	
						<div class="sup_docs_div">
							<input type="file" name="arr_attachments[]" class="sup_docs" /><br/>
						</div>

						<div class="add_docs_label">
							<a href="#" class="label_add_docs">+ Add another attachment</a>
						</div>

						<input type="submit" class="button" value="SEND">
					</td>	
				</tr>
				</tr>
			</table>		
		</form>	
		
	</div>

	<script>

		$(function(){
		var sup_counter = 1;
		$('a.label_add_docs').hide();
		
		$('a.label_add_docs').click(function(){
			sup_counter += 1;			
			if( sup_counter <= 5 ){				
				$('.sup_docs_div').append('<input type="file" name="arr_attachments[]" class="sup_docs" /><br/>');
				if( sup_counter == 5 ){
					$('a.label_add_docs').hide();								
				}
			} 
		});
		$('.sup_docs').change(function(){
			if( sup_counter = 1 ){
				$('a.label_add_docs').show();
			}			
		});
	});

</script>


