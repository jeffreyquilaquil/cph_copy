<h2>Reports</h2>
<hr/>
<ul>
	<li><h3>Generated Leave Codes</h3>
		<form action="" method="POST">
		<table style="border:1px solid #000" cellpadding=5>
			<tr>
				<td>Date From:</td>
				<td><?= $this->textM->formfield('text', 'dateFrom', '', 'datepick', '', 'required width:250px;'); ?></td>
			</tr>
			<tr>
				<td>Date To:</td>
				<td><?= $this->textM->formfield('text', 'dateTo', '', 'datepick', '', 'required width:250px;'); ?></td>
			</tr>
			<tr>
				<td><br/></td>
				<td><button class="btnclass btngreen">Generate</button></td>
			</tr>
		</table>
		<?= $this->textM->formfield('hidden', 'submitType', 'genLeaveCodes'); ?>
		</form>
	</li>
</ul>