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


	.btn_ans{
		background-color: #CCCCCC; 
		font-weight: bold;
		width: 100%;
		padding: 3px;
	}
</style>


<div id="hr_incidentinfo_form">

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
					<option></option>
				<?php foreach ($category as $key => $val): ?>
					<option><?php echo $val->categorys; ?></option>
				<?php endforeach ?>
		</select>
		
		<br>
		
		<span id="add_category" style="text-decoration: underline;cursor: pointer;">Add Category</span>
		
		<div id="show_add_category">
		
		<br>

			<form id="form" name="form">
				<input type="hidden" name="postid" value="<?php echo $value->cs_post_id; ?>">
				Category name: <input id="newcategory" type="text" name="category_name" required> <input id="submit" type="submit" value="Add">
			</form>	
		</div>
		</td>
	</tr>
	<tr>
		<td>Investigation Required:</td>
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
			
			<span id="found_answer" class="resol_link">The answer can be found in employee.tatepublishing.net</span>
			
			<br>
			<br>
			
			<span id="custom_answer" class="resol_link">Send custom resolution response</span>
			
			<br>
			<br>
			
			<span id="not_found_answer" class="resol_link">This is not an HR inquiry. Redirect to another department.</span>
			
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
	
<div id="found_answer_form">
	<table class="tableInfo">
		<tr>
			<td>
				<h2>
					HR Incident Number <?php echo $value->cs_post_id; ?> 

					<br>

					<small>You have owned responsibility for incident number <?php echo $value->cs_post_id; ?> </small>
				</h2>
			</td>	
		</tr>
		<tr>
			<td>
				<small>Please place below the link to the page in employee.tatepublishing.net</small>

				<br>

				<input type="text" style="width: 100%">
			</td>
		</tr>
		<tr>
			<td>
				<small>You may right below an additional custom message to the customer (optional):</small>

				<br>

				<textarea style="height:200px; resize: none;"></textarea>

				<br><br>

				<input type="button" class="btn_ans" value="Resolve Incident">
			</td>
		</tr>
	</table>
</div>

<div id="custom_answer_form">
	<table class="tableInfo">
		<tr>
			<td>
				<h2>
					HR Incident Number <?php echo $value->cs_post_id; ?> 

					<br>

					<small>You have owned responsibility for incident number <?php echo $value->cs_post_id; ?> </small>
				</h2>
			</td>	
		</tr>
		<tr>
			<td>
				<small>Please write below your resolution message to employee:</small>

				<br>

				<textarea style="height:200px; resize: none;"></textarea>

				<br><br>

				<input type="button" class="btn_ans" value="Resolve Incident">
			</td>
		</tr>
	</table>
</div>

<div id="notfound_answer_form">
	<table class="tableInfo">
		<tr>
			<td colspan="2">
				<h2>
					HR Incident Number <?php echo $value->cs_post_id; ?> 

					<br>

					<small>You have owned responsibility for incident number <?php echo $value->cs_post_id; ?> </small>
				</h2>
			</td>	
		</tr>
		<tr>
			<td>To what deparment does this person need to be redirected to?</td>
			<td>
				<select required>
					<option></option>
					<option>Accounting Team (accounting.cebu@tatepublishing.net)</option>
					<option>IT Team (helpdesk.cebu@tatepublishing.net)</option>
					<option>Immeadiate Supervisor (Immeadiate supervisors'email add)</option>
				</select>
			</td>	
		</tr>
		<tr>
			<td valign="top">Add custom message</td>
			<td><textarea style="height:200px; resize: none;" placeholder="<Insert Custom Message Here>"></textarea></td>
		</tr>
		<tr>
			<td></td>
			<td>
				<span  id="add_redirect_dept" class="resol_link">Add redirection department</span>
				<input type="button" style="background-color: #CCCCCC; font-weight: bold; padding: 3px" value="Resolve incident">
			</td>
		</tr>
		

	</table>
</div>



<script type="text/javascript">

	$(document).ready(function() { // jquery for insertion new category
		$("#submit").click(function() {
			var category = $("#newcategory").val();
			
			if (category == '') {
				alert("Insert new category!");
			} else {
	// Returns successful data submission message when the entered information is stored in database.
			$.post("<?php echo $this->config->base_url(); ?>hr_cs/addcategory", {
				category_name: category,
				
	}, function(data) {
		alert("New Category Inserted!");
			$('#form')[0].reset(); // To reset form fields
			});
			}
		});
	}); //end funtion

$(document).ready(function(){

	$("#show_add_category").hide();
	
	// ===== sHOW ADD CATEGORY ======
	$("#add_category").click(function(){$("#show_add_category").show();});

	$("#appear_furtherinfo").hide();
	// ===== SHOW FURTHER INFO LINK =====
	$("#yes").click(function(){$("#appear_furtherinfo").show();});
	
	// ===== HIDE FURTHER INFO LINK =====
	$("#no").click(function(){$("#appear_furtherinfo").hide();});

	$("#hr_incidentinfo_form").show();
	$("#found_answer_form").hide();
	$("#custom_answer_form").hide();

	// ===== SHOW FOUND ANSWER FORM =====
	$("#found_answer").click(function(){

	$("#hr_incidentinfo_form").hide();
	$("#found_answer_form").show();
	
	});

	// ===== SHOW CUSTOM ANSWER FORM =====
	$("#custom_answer").click(function(){
    	
    	$("#hr_incidentinfo_form").hide();
    	$("#custom_answer_form").show();
	});
});

</script>