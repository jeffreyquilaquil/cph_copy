<style type="text/css">
	.note{
		font-style: italic;
		font-size: x-small;
	}

	.btn_ans{
		background-color: #CCCCCC; 
		font-weight: bold;
		width: 100%;
		padding: 3px;
	}

	.btn_ans_small{
		background-color: #CCCCCC; 
		font-weight: bold; padding: 3px;
	}

	input[type=text]{

		width: 100%;

	}

	a{
		text-decoration: underline;
		cursor: pointer;
	}

	table{
		width: 100%;
	}
</style>

<!-- ===== INCIDENT INFORMATION  ===== -->
<div id="hr_incidentinfo_form"> 

<?php foreach ($HrIncident as $key => $value): ?>
<?php endforeach ?>
<input type="hidden" id="categoryid" name="postid" value="<?php echo $value->cs_post_id; ?>">
<input type="hidden" id="hr_username" name="postid" value="<?php echo $this->user->username; ?>">
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
		
		&nbsp;

		<a id="add_category">Add Category</a>
		
		<div id="show_add_category">
		
		<br>

			<form id="form" name="form">
				<input type="hidden" id="insedentid" name="postid" value="<?php echo $value->cs_post_id; ?>">
				Category name: <input id="newcategory" type="text" name="category_name" required style="width: 123px">
				
				&nbsp;

				<input id="submit" type="submit" value="Add">
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
			
			<a id="found_answer">The answer can be found in employee.tatepublishing.net</a>

			<!-- ===== FOUND ANSWER  ===== -->
			<div id="found_answer_form"> 
				<form id="found_answer_forms">
					<input id="foundid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
					<input id="foundcategid" type="hidden" name="insedentcategid" value="<?php echo $value->cs_post_id; ?> ">

					<table class="tableInfo">
						<tr>
							<td>
								<small>Please place below the link to the page in employee.tatepublishing.net</small>

								<br>

								<input type="text" id="found_answer_link" name="found_answer_link" required>
							</td>
						</tr>
						<tr>
							<td>
								<small>You may right below an additional custom message to the customer (optional):</small>

								<br>

								<textarea style="height:200px;  resize: none;" id="found_answer_custom" name="found_answer_custom"></textarea>

								<br><br>

								<input id="found_answerer_submit" type="submit" class="btn_ans" value="Resolve Incident">
							</td>
						</tr>
					</table>

					<br>

				</form>
			</div>
			
			<br>
			<br>
			
			<a id="custom_answer">Send custom resolution response</a>

			<!-- ===== CUSTOM ANSWER  ===== -->
			<div id="custom_answer_form"> 
				<form id="custom_ans_form">
				<input id="customid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
				<input id="customcategid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
					<table class="tableInfo">
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

					<br>

					</form>
			</div>
			
			<br>
			<br>
			
			<a id="not_found_answer">This is not an HR inquiry. Redirect to another department.</a>
			
			<!-- ===== NOT FOUND ANSWER  ===== -->
			<div id="notfound_answer_form"> 


			<input id="notfoundid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
			<input id="notfoundcategid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
			<form id="not_found_ans_form">
				<table class="tableInfo">
					<tr>
						<td>To what deparment does this person need to be redirected to?</td>
						<td>
							<select id="redirect_department" name="redirect_department" required style="width: 100%">
								<option></option>

								<?php foreach ($department_email as $k => $v): ?>
								<option value="<?php echo $v->email; ?>"><?php echo $v->department." (".$v->email.")"; ?></option>
							<?php endforeach ?>

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
							<a id="add_redirect_dept">Add redirection department</a>
							<input type="submit" id="not_found_answer_submit" class="btn_ans_small" value="Resolve incident" style="float:right">
						</td>
					</tr>
				</table>

				<br>

			</form>

			</div>

			<!--  ====== ADD A REDIRECTION DEPARTMENT ===== -->
			<div id="add_redirect_dept_form">
			<form id="add_new_department_form">
				<table class="tableInfo">
					<tr>
						<td colspan="2"><h2>Add a Redirection Department</h2></td>
					</tr>
					<tr>
						<td colspan="2">
							<small>Name of the department/team customers can be directed to:</small>

							<br>
							
							<input id="name_department" type="text" required>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<small>What is the email address/es that the employee can</small>

							<br>
							
							<input id="email_department" type="text" required>

						</td>
					</tr>
					<tr>
						<td align="left">
							<a id="see_all_redirect_dept" class="resol_link">See All Redirection Departments</a>
						</td>
						<td align="right"><input type="submit" id="submit_add_department" class="btn_ans_small" value="Submit"></td>
					</tr>
				</table>
				</form>
			</div>

			<!-- ====== SEE ALL DIRECTION DEPARTMENTS ===== -->
			<div id="see_all_redirect_dept_form">
				<h2>All Direction Departments</h2>
				<table class="datatable">
					<thead>
						<tr>
							<th>Department Name</th>
							<th>Email Address</th>
							<th>Edit</th>
							<th>Delete</th>
					</tr>	
					</thead>
					<tbody>
					<?php foreach ($department_email as $name => $dep): ?>
						<tr>
							<td><?php echo $dep->department; ?></td>
							<td><?php echo $dep->email; ?></td>
							<td align="center"><a id="edit_redirect" href="">Edit</a></td>
							<td align="center"><a id="delete_redirect" href="">Delete</a></td>
						</tr>

						<?php endforeach ?>
						
					</tbody>
				</table>

				<br>

			</div>


			<br>
			<br>
			
			<!-- ====== APPEAR ONLY IF HR STAFF SELECT YES TO INVESTIGATION REQUIRED ITEM REMOVE ====== -->
			
			<div id="appear_furtherinfo">
				<a id="further_answer">Further information (investigation) is required</a>

				<!-- ====== FURTHER ANSWER  ====== -->
				<div id="further_answer_form"> 
					<form id="further_ans_form">
					<input id="furtherid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
					<input id="furthercategid" type="hidden" name="insedentid" value="<?php echo $value->cs_post_id; ?> ">
						<table class="tableInfo">
							<tr>
								<td>
									<small>Please write below your update to the customer as to what information is requoired to resolve the matter</small>

									<br>

									<textarea id="further_answer_msg" name="further_answer_msg" style="height:200px; resize: none;"></textarea>

									<br><br>
									<a id="send_email_to_another">Send email to another department/staff</a>
									<br><br>

									<input type="submit" id="furder_submit" class="btn_ans" value="Submit">
								</td>
							</tr>	
						</table>

						<br>

					</form>
				</div>

				<!-- SEND EMAIL -->
				<div id="send_email_to_another_form">
					<table class="tableInfo">
						<tr>
							<td colspan="2"><h2>Send Email</h2></td>
						</tr>
						<tr>
							<td>From:</td>
							<td>hr.cebu@tatepublishing.net</td>
						</tr>
						<tr>
							<td>To:</td>
							<td>
								<input type="text" name="" value="" placeholder="" >
								<br>
								<small><i>Seperate email address with commas</i></small>
							</td>
						</tr>
						<tr>
							<td>Subject:</td>
							<td>
								<input type="text" name="" value="" placeholder="">
							</td>
						</tr>
						<tr>
							<td>CC</td>
							<td><!-- Default customer email address --></td>
						</tr>
						<tr>
							<td colspan="2"><textarea class="hidden tiny" style="height:200px; resize: none;"></textarea></td>
						</tr>
						<tr>
							<td colspan="2" align="left"><input type="submit" name="" value="Send" class="btn_ans_small"></td>
						</tr>
					</table>
				</div>

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

<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">

$(document).ready(function() { 

	$('#hr_incidentinfo_form').show();

	// ====== ADD CATEGORY =====
	$('#show_add_category').hide();

		$('#add_category').click(function(){

			$('#show_add_category').toggle();

				if ($.trim($(this).text()) === 'Hide Category') {
					
					$(this).text('Add Category');
				}

				else{
					$(this).text('Hide Category');
				}
		});

	// ===== FURTHER ANSWER LINK =====
	$('#appear_furtherinfo').hide();

		$('#yes').click(function(){
			$('#appear_furtherinfo').show();
		});
		
		$('#no').click(function(){
			$('#appear_furtherinfo').hide();
		});

	// ===== FOUND ANSWER =====
	$('#found_answer_form').hide();

	$('#found_answer').click(function(){

		$('#found_answer_form').toggle();
		
	})	

	// ===== CUSTOM ANSWER =====
	$('#custom_answer_form').hide();

	$('#custom_answer').click(function(){

		$('#custom_answer_form').toggle();
		
	})	

	// ===== NOT FOUND ANSWER =====
	$('#notfound_answer_form').hide();

	$('#not_found_answer').click(function(){

		$('#notfound_answer_form').toggle();
		
	})	

	// ===== FURTHER ANSWER =====
	$('#further_answer_form ').hide();

	$('#further_answer').click(function(){

		$('#further_answer_form').toggle();
		
	})	

	// ===== ADD REDIRECT DEPARTMENT =====
	$('#add_redirect_dept_form').hide();

	$('#add_redirect_dept').click(function(){
    	
    	$('#add_redirect_dept_form').slideToggle("slow");
	});
	
	// ===== SEE ALL REDIRECT DEPARTMENT =====
	$('#see_all_redirect_dept_form').hide();

	$('#see_all_redirect_dept').click(function(){
    	
    	$('#see_all_redirect_dept_form').slideToggle("slow");
	});

	 
	// ===== SEND EMAIL FORM ===== 
	$('#send_email_to_another_form').hide();

	$('#send_email_to_another').click(function(){

		$('#send_email_to_another_form').slideToggle("slow");
	});

	// ===== DISPLAY TOOLBAR IN TEXTAREA =====
	tinymce.init({
	selector: "textarea.tiny",	
	menubar : false,
	toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code image"
	});	
		
});

$(function(){

	// ===== JQUERY FOR ADD NEW DEPARTMENT =====
	$("#submit_add_department").click(function() {

		var name_dep = $("#name_department").val();
		var email_dep = $("#email_department").val();
		

		var dataString = 'name_department='+ name_dep +'&email_department=' + email_dep;	

		if (name_dep == '' || email_dep == '') {
			alert("Some Field is Empty!");
		}else{
		// ===== AJAX CODE TO SUBMIT FORM =====
				$.ajax({
					type: "POST",
					url: "<?php echo $this->config->base_url(); ?>hr_cs/addnewdeparment",
					data: dataString,
					cache: false,
						success: function(result){
						alert('success!');
						// ===== TO RESET FORM FIELDS =====
						$('#add_new_department_form')[0].reset(); 
						$('#not_found_ans_form')[0].reset(); // ===== TO RESET FORM FIELDS =====
						$('#form')[0].reset(); // ===== TO RESET FORM FIELDS =====
					
						}
				});
			}

			return false;
	});

	// ===== JQUERY FOR INSERTION FOUND ANSWER IN LINK =====
	$("#found_answer_submit").click(function() {
		var hr_sname = $("#hr_username").val();
		var ins_id = $("#foundid").val();
		var inscateg_id = $("#categoryid").val();
		var ass_categ = $("#assign_category").val();
		var fnd_answer_link = $("#found_answer_link").val();
		var custom_ans = $("#found_answer_custom").val();

		var dataString = 'insedentid='+ ins_id +'&assign_category=' + ass_categ + '&found_answer_link=' + fnd_answer_link +'&found_answer_custom='+ custom_ans +'&categid=' + inscateg_id +'&hr_username='+ hr_sname;
		
		

		if (fnd_answer_link == '') {
			alert("Some Field is Empty!");
		}else{
		// ===== AJAX CODE TO SUBMIT FORM =====
				$.ajax({
					type: "POST",
					url: "<?php echo $this->config->base_url(); ?>hr_cs/found_answer_solution",
					data: dataString,
					cache: false,
						success: function(result){
						alert('success!');
						// ===== TO RESET FORM FIELDS =====
						$('#found_answer_forms')[0].reset(); 
						}
				});
			}

			return false;
	});


	// ===== JQUERY FOR INSERTION CUSTOM FOUND ANSWER IN LINK =====
	$("#custom_answer_submit").click(function() {
		var hr_sname = $("#hr_username").val();
		var ins_id = $("#customid").val();
		var inscateg_id = $("#customcategid").val();
		var ass_categ = $("#assign_category option:selected").val();
		var custom_ans = $("#custom_answer_msg").val();

		var dataString = 'insedentid='+ ins_id + '&assign_category=' + ass_categ +'&custom_answer_msg='+ custom_ans + '&customcategid=' + inscateg_id +'&hr_username='+ hr_sname;
		
		
			if (custom_ans == '') {

				alert("Some Field is Empty!");
			}else{

					// ===== AJAX CODE TO SUBMIT FORM =====
					$.ajax({
					type: "POST",
					url: "<?php echo $this->config->base_url(); ?>hr_cs/custom_answer_solution",
					data: dataString,
					cache: false,
						success: function(result){
						alert("Success!");
						$('#custom_ans_form')[0].reset(); // ===== TO RESET FORM FIELDS =====
						}
					});
				}
				return false;
		});

	// ===== JQUERY FOR INSERTION NOT FOUND ANSWER IN LINK =====
	$("#not_found_answer_submit").click(function() {
		var hr_sname = $("#hr_username").val();
		var ins_id = $("#notfoundid").val();
		var inscateg_id = $("#notfoundcategid").val();
		var ass_categ = $("#assign_category").val();
		var redirect = $("#redirect_department").val();
		var custom_ans = $("#not_found_custom_msg").val();

		var dataString = 'insedentid=' + ins_id + '&assign_category=' + ass_categ + '&notfound_answer_custom=' + custom_ans + '&redirect_department=' + redirect + '&notfoundcategid=' + inscateg_id +'&hr_username='+ hr_sname;
		
			if (redirect == '' || custom_ans== '') {

				alert("Some Field is Empty!");
			}else{

					// ===== AJAX CODE TO SUBMIT FORM =====
					$.ajax({
					type: "POST",
					url: "<?php echo $this->config->base_url(); ?>hr_cs/notfound_answe_solution",
					data: dataString,
					cache: false,
					
						success: function(result){
						alert("Success!");
						$('#not_found_ans_form')[0].reset(); // ===== TO RESET FORM FIELDS =====

						}
					});
				}

			return false;
	}); 

	// ===== JQUERY FOR INSERTION FURTHER ANSWER IN LINK =====
	$("#furder_submit").click(function() {
		var hr_sname = $("#hr_username").val();
		var ins_id = $("#furtherid").val();
		var inscateg_id = $("#furthercategid").val();
		var ass_categ = $("#assign_category option:selected").val();
		var custom_ans = $("#further_answer_msg").val();

		var dataString = 'insedentid='+ ins_id + '&assign_category=' + ass_categ +'&found_answer_custom='+ custom_ans +'&furthercategid=' + inscateg_id +'&hr_username='+ hr_sname;
		
		if (custom_ans == '') {
			alert("Some Field is Empty!");
		}else{
			
			// ===== AJAX CODE TO SUBMIT FORM =====
			$.ajax({
			type: "POST",
			url: "<?php echo $this->config->base_url(); ?>hr_cs/further_investigation",
			data: dataString,
			cache: false,
			
				success: function(result){
				alert("Success!");
				$('#further_ans_form')[0].reset(); // ===== TO RESET FORM FIELDS =====
				}
			});
		}

			return false;
	});

	// ====== INSERT NEW CATEGORY =====
	$("#submit").click(function() {

		var category = $("#newcategory").val();

		var datacategorys = 'category_name='+ category;
		
		if (category == '') {
			alert("Insert new category!");
		} else {

			// ===== AJAX CODE TO SUBMIT FORM =====
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/addcategory",
				data: datacategorys,
				cache: false,
					success: function(result){
					alert("Success!");
					$('#form')[0].reset(); // ===== TO RESET FORM FIELDS =====
					}
				});
			}
	});

});

</script>