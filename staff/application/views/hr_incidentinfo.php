<style type="text/css">
	.note{
		font-style: italic;
	}
</style>

<div>
	<table>
		<tr>
			<td colspan="2"><h2>HR Incident Number 000003</h2></td>
			<td></td>
		</tr>
		<tr>
			<td colspan="2"><h3>You have owned responsibility for incident number 000003</h3></td>
			<td></td>
		</tr>
		<tr>
			<td>Customer</td>
			<td>Ann Margaret Yap</td>
		</tr>
		<tr>
			<td>Date Submitted</td>
			<td>6 March, 2015, 01:56</td>
		</tr>
		<tr>
			<td>Subject</td>
			<td>Immediate Supervisor update inquiry</td>
		</tr>
		<tr>
			<td>Customer selected Priority Level</td>
			<td>Needs attention</td>
		</tr>
		<tr>
			<td>Assign Category</td>
			<td>
				<select required>
				<option value="">Compensation and Benefits</option>
				<option value="">Compensation</option>
				<option value="">Benefits-SSS</option>
				<option value="">Benefits-Philhealth</option>
				<option value="">Benefits-Pag-IBIG</option>
				<option value="">Benefits-Leave Application</option>
				<option value="">Benefits-Medical Reimbursement</option>
				<option value="">Benefits-Offset Application</option>
				<option value="">Code of Conduct Policy</option>
				<option value="">Disciplinary</option>
				<option value="">Health Insurance</option>
				<option value="">Payroll Related</option>
				<option value="">CareerPH/Staff</option>
				<option value="">Organizational Chart</option>
			</select>
			<a href="#">Assign Category</a>
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
			<td>Details of the incident</td>
			<td>
			Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
			tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
			quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
			consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
			cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
			proident, sunt in culpa qui officia deserunt mollit anim id est laborum.			
			<br>
			<span class="note">
				Instruction to HR: Search <a href="#">www.employee.tatepublishing.net</a> first to see if the
				answer to the employee's inquiry above can be found there. If it is, then click on the appropriate
				<b>RESOLVE</b> action below.
			</span>
			<br>
			<span style="background: #CCCCCC; font-weight: bold; width: 100%">Resolution Options</span>
			<a href="#">The answer can be found in employee.tatepublishing.net</a><br>
			<a href="#">Send custom resolution response</a><br>
			<a href="#">This is not an HR inquiry, Redirect to another department</a><br>
			<!-- APPEAR ONLY IF HR STAFF SELECT YES TO INVESTIGATION REQUIRED ITEM REMOVE -->
			<div id="appear_furtherinfo">
				<a href="#">Further information (investigation) is required</a> 
			</div>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<th>Notes</th>
			<th colspan="2"><a href="#">Add Note</a></th>
			<th></th>
		</tr>
		<tr>
			<td></td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$("#yes").click(function(){
	    	$("#appear_furtherinfo").show();
		});
	
		$("#no").click(function(){
		    $("#appear_furtherinfo").hide();
		});
	});	
</script>