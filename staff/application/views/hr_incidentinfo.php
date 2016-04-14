<style type="text/css">
	.note{
		font-style: italic;
		font-size: x-small;
	}

	.resol_link{
		color: #660808;
		text-decoration: underline;
		cursor: pointer;
	}

	.resol_link:hover{
		color: red;
		text-decoration: red;
		cursor: pointer;
	}
</style>

<div>

<?php foreach ($HrIncident as $key => $value): ?>
<?php endforeach ?>

<table class="tableInfo">
	<tr>
		<td colspan="2">
			<h2>HR Incident Number <?php echo $value->cs_post_id; ?> <br><small>You have owned responsibility for incident number <?php echo $value->cs_post_id; ?> </small></h2>
		</td>	
	<td></td>
	</tr>
	<tr>
		<td >Customer</td>
		<td><?php echo $value->fname." ". $value->lname; ?> </td>
	</tr>
	<tr>
		<td>Date Submitted</td>
		<td><?php echo $value->cs_post_date_submitted; ?></td>
	</tr>
	<tr>
		<td>Subject</td>
		<td><?php echo $value->cs_post_subject; ?></td>
	</tr>
	<tr>
		<td>Customer selected Priority Level</td>
		<td><?php echo $value->cs_post_urgency; ?></td>
	</tr>
	<tr>
		<td>Assign Category</td>
		<td>
			<select required>
			<option value="">Compensation and Benefits</option>
			<option></option>
		</select>
		<br>
		<span id="add_category" style="text-decoration: underline;cursor: pointer;">Add Category</span>
		
		<div id="show_add_category">
		<br>
			<form>
				Category name: <input type="text" name="category_name"> <input type="submit" value="Add">
			</form>	
		</div>
		</td>
	</tr>
	<tr>
		<td>Inverstigation Required:</td>
		<td>
			<input id="yes" type="radio" name="investigation_required_radio" value="" required> Yes
			<input id="no" type="radio" name="investigation_required_radio" value=""> No
			<br>
			<span class="note">
				Note to HR: If you are able to provide answer to the question within 24 hours,
				select <b>NO</b> if you need to involve or collect information from other departments,
				Select <b>YES</b>.
			</span>
		</td>
	</tr>
	<tr>
		<td valign="top">Details of the incident</td>
		<td>
			<?php echo $value->cs_msg_text; ?>
			
			<br>
			<br>
			
			<span class="note">
				Instruction to HR: Search <a href="#">www.employee.tatepublishing.net</a> first to see if the
				answer to the employee's inquiry above can be found there. If it is, then click on the appropriate
				<b>RESOLVE</b> action below.
			</span>
			
			<br>
			<br>
			
			<div style="background: #CCCCCC; font-weight: bold; width: 100%; padding: 5px 0px 5px 5px">Resolution Options</div>
			
			<br>
			
			<span id="found_answer" class="resol_link"></span>
			
			<br>
			<br>
			
			<span id="custom_answer" class="resol_link"></span>
			
			<br>
			<br>
			
			<span id="not_found_answer" class="resol_link"></span>
			
			<br>
			<br>
			
			<!-- APPEAR ONLY IF HR STAFF SELECT YES TO INVESTIGATION REQUIRED ITEM REMOVE -->
			
			<div id="appear_furtherinfo">
				<span id="further_answer" class="resol_link">Further information (investigation) is required</span>

				<br>
				
			</div>
		</td>
	</tr>
</table>

<table class="tableInfo">
	<tr>
		<th align="left">Notes</th>
		<th colspan="2" align="right"><a href="#">Add Note</a></th>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td></td>
	</tr>
</table>
</div>

<script type="text/javascript">
	$(function(){

		$("#appear_furtherinfo").hide();
		
		$("#yes").click(function(){
	    	$("#appear_furtherinfo").show();
		});
	
		$("#no").click(function(){
		    $("#appear_furtherinfo").hide();
		});
	});	

	$(function(){

		$("#show_add_category").hide();
		
		$("#add_category").click(function(){
	    	$("#show_add_category").show();
		});
	
	});
</script>