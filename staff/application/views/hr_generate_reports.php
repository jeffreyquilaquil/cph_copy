<style type="text/css">
	
	}
</style>
<table>
<tr>
	<td colspan="2"><h2>Generate Reports</h2></td>
</tr>
<tr>
	<td colspan="2">
	From:&nbsp;&nbsp;<input type="text" id="" name="" class="datepick"  value="" placeholder="Start Date" style="width:200px" readonly/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	To: &nbsp;&nbsp;<input type="text" id="" name="" class="datepick"  value="" placeholder="End Date" style="width:200px" readonly/>
	</td>
</tr>
<tr>
	<td valign="top">Who:</td>
	<td align="left">
	<select id="select_who" multiple style="width: 100px">
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
</table>

<script type="text/javascript">
	
</script>

