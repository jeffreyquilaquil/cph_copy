<h2>Generate Reports</h2>
<hr>
<table style="border: 1px solid #000;">
	<tr>
		<td align="left">From:</td>
		<td align="right"><input type="text" id="" name="" class="datepick"  value="" placeholder="Start Date" style="width: 400px" readonly/></td>
	</tr>
	<tr>
		<td align="left">To:</td>
		<td align="right"><input type="text" id="" name="" class="datepick"  value="" placeholder="End Date" style="width: 400px" readonly/></td>
	</tr>
	<tr>
		<td align="left" valign="top">Who:</td>
		<td align="right">
			<select id="select_who" multiple style="width: 100%">
			 <?php if ($this->access->myaccess[0] == "hr") { ?>
				 <?php foreach ($hr_list as $hr_list_key => $hr_list_value) { ?>
				 	<option value=""><?php echo $hr_list_value->fname." ".$hr_list_value->lname; ?></option>
				 <?php } ?>
			 <?php } ?>

			 <?php if ($this->access->myaccess[0] == "finance") { ?>
				 <?php foreach ($finance_list as $finance_list_key => $finance_list_value) { ?>
				 	<option value=""><?php echo $finance_list_value->fname." ".$finance_list_value->lname; ?></option>
				 <?php } ?>
			 <?php } ?>

			 <?php if ($this->access->myaccess[0] == "full") { ?>
				 <?php foreach ($full_list as $full_list_key => $full_list_value) { ?>
				 	<option value=""><?php echo $full_list_value->fname." ".$full_list_value->lname; ?></option>
				 <?php } ?>
		 <?php } ?>
		</select>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="checkbox" id="lbl_checkbox_tat" name="" value="" style="position: relative; vertical-align: middle; bottom: 1px;"><label for="lbl_checkbox_tat">Turnaround Time</label>&nbsp;&nbsp;
			<input type="checkbox" id="lbl_checkbox_ratings" name="" value="" style="position: relative; vertical-align: middle; bottom: 1px;"><label for="lbl_checkbox_ratings">Rating</label>&nbsp;&nbsp;
			<input type="checkbox" id="lbl_checkbox_count_closed" name="" value="" style="position: relative; vertical-align: middle; bottom: 1px;"><label for="lbl_checkbox_count_closed">Number of Close Incidents</label>&nbsp;&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="2" align="right"><input type="submit" class="btngreen" value="Submit"></td>
	</tr>
</table>
<br><br>
As of mm/dd/yy - mm/dd/yy
<br><br>
<h2>Turnaround Time</h2>
<ul style="list-style-type: none;">
	<li><h3>0.00</h3></li>
</ul>
<h2>Rating</h2>
<ul style="list-style-type: none;">
	<li><h3>0.00</h3></li>
</ul>
<h2>Number of closed tickets</h2>
<ul style="list-style-type: none;">
	<li><h3>0.00</h3></li>
</ul>

<script type="text/javascript">
	$(document).ready(function(){

	
	});
	
</script>
	