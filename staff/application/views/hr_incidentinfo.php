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

<div id="hr_incidentinfo_form"> <!-- ===== INCEDENT INFORMATION FORM ===== -->

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
			<select id="assign_category" name="assign_category">
					<option></option>
				<?php foreach ($category as $key => $val): ?>
					<option value="<?php echo $val->categorys; ?>"><?php echo $val->categorys; ?></option>
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
			<input id="yes" type="radio" name="investigation_required_radio" value="yes" required> Yes
			<input id="no" type="radio" name="investigation_required_radio" value="no" checked="true"> No
			
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

	
<div id="found_answer_form"> <!-- ======================== FOUND ANSWER FORM =========================== -->
<form id="found_answer_forms">
	<input id="insedentid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">

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

				<input type="text" id="found_answer_link" name="found_answer_link" style="width: 100%" required>
			</td>
		</tr>
		<tr>
			<td>
				<small>You may right below an additional custom message to the customer (optional):</small>

				<br>

				<textarea style="height:200px; resize: none;" id="found_answer_custom" name="found_answer_custom"></textarea>

				<br><br>

				<input id="found_answer_submit" type="submit" class="btn_ans" value="Resolve Incident">
			</td>
		</tr>
	</table>
</form>
</div>

<div id="custom_answer_form"> <!-- =================== Custom ANSWER FORM ====================== -->
<form id="custom_ans_form">
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

				<textarea id="custom_answer_msg" name="custom_answer_msg" style="height:200px; resize: none;"></textarea>

				<br><br>

				<input type="submit" id="custom_answer_submit" class="btn_ans" value="Resolve Incident">
			</td>
		</tr>
	</table>
	</form>
</div>

<div id="notfound_answer_form"> <!-- ===================== NOT FOUND ANSWER FORM ============================ -->
<form id="not_found_ans_form">
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
				<select id="redirect_department" name="redirect_department" required>
					<option></option>
					<option>Accounting Team (accounting.cebu@tatepublishing.net)</option>
					<option>IT Team (helpdesk.cebu@tatepublishing.net)</option>
					<option>Immeadiate Supervisor (Immeadiate supervisors'email add)</option>
				</select>
			</td>	
		</tr>
		<tr>
			<td valign="top">Add custom message</td>
			<td><textarea id="not_found_custom_msg" name="not_found_custom_msg" style="height:200px; resize: none;" placeholder="<Insert Custom Message Here>"></textarea></td>
		</tr>
		<tr>
			<td></td>
			<td>
				<span  id="add_redirect_dept" class="resol_link">Add redirection department</span>
				<input type="submit" id="not_found_answer_submit" style="background-color: #CCCCCC; font-weight: bold; padding: 3px" value="Resolve incident">
			</td>
		</tr>
		

	</table>
	</form>
</div>

<div id="further_answer_form"> <!-- ====================== FURTHER ANSWER FORM ============================= -->
<form id="further_ans_form">
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
				<small>Please write below your update to the customer as to what information is requoired to resolve the matter</small>

				<br>

				<textarea id="further_answer_msg" name="further_answer_msg" style="height:200px; resize: none;"></textarea>

				<br><br>
				<a href="#">Send email to another department / staff</a>
				<br><br>

				<input type="submit" id="furder_submit" class="btn_ans" value="Submit">
			</td>
		</tr>
	</table>
	</form>
</div>


<script type="text/javascript">

$(document).ready(function() { // jquery for insertion Found answer in link
		$("#found_answer_submit").click(function() {
			var ins_id = $("#insedentid").val();
			var ass_categ = $("#assign_category").val();
			var fnd_answer_link = $("#found_answer_link").val();
			var custom_ans = $("#found_answer_custom").val();

			var dataString = 'insedentid='+ ins_id + '&assign_category=' + ass_categ + '&found_answer_link=' + fnd_answer_link +'&found_answer_custom='+ custom_ans;
			
			
			if (fnd_answer_link == '') {
				alert("Some Field is Empty!");
			}else{
				// AJAX Code To Submit Form.
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/found_answer_sulotion",
				data: dataString,
				cache: false,
				success: function(result){
				alert("Success!");
				$('#found_answer_forms')[0].reset(); // To reset form fields
				}
				});
				}
				return false;
				});

		$("#custom_answer_submit").click(function() {
			var ins_id = $("#insedentid").val();
			var ass_categ = $("#assign_category").val();
			var fnd_answer_link = null;
			var custom_ans = $("#custom_answer_msg").val();

			var dataString = 'insedentid='+ ins_id + '&assign_category=' + ass_categ +'&found_answer_custom='+ custom_ans;
			
			
			if (custom_ans == '') {
				alert("Some Field is Empty!");
			}else{
				// AJAX Code To Submit Form.
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/custom_answer_sulotion",
				data: dataString,
				cache: false,
				success: function(result){
				alert("Success!");
				$('#custom_ans_form')[0].reset(); // To reset form fields
				}
				});
				}
				return false;
				});


		$("#not_found_answer_submit").click(function() {

			var ins_id = $("#insedentid").val();
			var ass_categ = $("#assign_category").val();
			var redirect = $("#redirect_department option:selected").val();
			var custom_ans = $("#not_found_custom_msg").val();

			var dataString = 'insedentid='+ ins_id + '&assign_category=' + ass_categ +'&notfound_answer_custom='+ custom_ans + '&redirect_department=' + redirect;
			
			
			if (redirect == '' || custom_ans== '') {
				alert("Some Field is Empty!");
			}else{
				// AJAX Code To Submit Form.
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/notfound_answe_sulotion",
				data: dataString,
				cache: false,
				success: function(result){
				alert("Success!");
				$('#not_found_ans_form')[0].reset(); // To reset form fields
				}
				});
				}
				return false;
				}); 


		$("#furder_submit").click(function() {
			var ins_id = $("#insedentid").val();
			var ass_categ = $("#assign_category").val();
			var custom_ans = $("#further_answer_msg").val();

			var dataString = 'insedentid='+ ins_id + '&assign_category=' + ass_categ +'&found_answer_custom='+ custom_ans;
			
			if (custom_ans == '') {
				alert("Some Field is Empty!");
			}else{
				// AJAX Code To Submit Form.
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/custom_answer_sulotion",
				data: dataString,
				cache: false,
				success: function(result){
				alert("Success!");
				$('#further_ans_form')[0].reset(); // To reset form fields
				}
				});
				}
				return false;
				});

		//}); //end funtion





	//$(document).ready(function() { // jquery for insert new category
		$("#submit").click(function() {
			var category = $("#newcategory").val();

			var datacategorys = 'category_name='+ category;
			
			if (category == '') {
				alert("Insert new category!");
			} else {

				// AJAX Code To Submit Form.
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/addcategory",
				data: datacategorys,
				cache: false,
				success: function(result){
				alert("Success!");
				$('#form')[0].reset(); // To reset form fields
				}
				});
				}
		});
	//}); //end funtion

//$(document).ready(function(){

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
	$("#notfound_answer_form").hide();
	$("#further_answer_form").hide();

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
	// ===== SHOW NOT FOUND ANSWER FORM =====
	$("#not_found_answer").click(function(){
    	
    	$("#hr_incidentinfo_form").hide();
    	$("#notfound_answer_form").show();
	});

	$("#further_answer").click(function(){
    	
    	$("#hr_incidentinfo_form").hide();
    	$("#further_answer_form").show();
	});
});

</script>