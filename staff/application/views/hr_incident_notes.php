<style type="text/css">
	a{
		text-decoration: underline;
		cursor: pointer;
	}

	table{
		width: 100%;
	}


</style>

<div>
	<?php foreach ($hr_note as $key => $value): ?>
		
	<?php endforeach ?>
	<table class="tableInfo">
		<tr>
			<td colspan="3">
				<h2>HR Incident Number <?php echo $value->cs_post_id?><br>You have owned responsibility for incident number <?php echo $value->cs_post_id?></h2>		
			</td>
		</tr>
		<tr>
			<td>Customer</td>
			<td colspan="2"><?php echo $value->fname." ".$value->lname; ?></td>
		</tr>
		<tr>
			<td>Date Submitted</td>
			<td colspan="2"><?php echo $value->cs_post_date_submitted; ?></td>
		</tr>
		<tr>
			<td>Subject</td>
			<td colspan="2"><?php echo $value->cs_post_subject; ?></td>
		</tr>
		<tr>
			<td>Customer selected Priority level</td>
			<td colspan="2"><?php echo $value->cs_post_urgency; ?></td>
		</tr>
		
	</table>
	<br>
	<table class="tableInfo">
		<tr>
			<td valign="top"><h3>Notes</h3></td>
			<td colspan="2">
				<a id="add_notes" style="float: right"><b>Add Notes</b></a>
				<br>
				<div id="show_add_notes_textarea">
					<br>
					<textarea style="height: 100px; float: right; resize: none;"></textarea>
					<input type="submit" name="" class="btngreen" value="Submit" style="float:right;">
				</div>
			</td>
		</tr>
		<tr>
			<td>aaa</td>
			<td>bbb</td>
			<td>ccc</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
		$(document).ready(function(){

			$('#show_add_notes_textarea').hide();	
				$('#add_notes').click(function(){

				$('#show_add_notes_textarea').toggle();

				if ($.trim($(this).text()) === 'Hide Notes') {

				$(this).text('Add Notes');
				}

				else{
				$(this).text('Hide Notes');
				}
				});

		});
</script>