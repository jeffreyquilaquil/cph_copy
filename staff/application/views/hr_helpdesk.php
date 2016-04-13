<style type="text/css">
	th{
	 	background-color: #CCCCCC;
	 }

	 .setting_options{
	 	padding: 8px 60px 8px 10px;
	 	background-color: #CCCCCC; 
	 }

	 .setting_pos{
	 	padding-right: 10px;
	 }

	 a:link, a:visited, a:hover, a:active{
	 	color: black;
	 	text-decoration: none;
	 }
	
</style>

<h2>HR HelpDesk</h2>
<hr/>

<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">New</li>
	<li class="tab-link" data-tab="tab-2">Active</li>
	<li class="tab-link" data-tab="tab-3">Resolved</li>
	<li class="tab-link" data-tab="tab-4">Cancelled</li>
</ul>

<div id="tab-1" class="tab-content current">
<!-- <?php echo $this->textM->reimbursementTableDisplay($data_query_medical, 'pending_med'); ?> -->
  <br>
  <table class="tableInfo fs11px">
      	<tr>
      		<th>Incident #</th>
      		<th>Customer</th>
      		<th>Date Submitted</th>
      		<th>Subject</th>
      		<th>Priority</th>
      	</tr>
		<tr>
      		<td>1</td>
      		<td>Shem</td>
      		<td>04/12/16</td>
      		<td>Char lang</td>
      		<td>Needs Attention</td>
      	</tr>
	      	<tr>
	      		<td>2</td>
	      		<td>Chavez</td>
	      		<td>04/12/16</td>
	      		<td>Char lang</td>
	      		<td>Needs Attention</td>
	      	</tr>   
      </table>

      <br>
      <h2>SETTINGS</h2><br>
      <table>
      	<tr>
      		<td class="setting_pos"><a href="#" class="setting_options">Add Categories</a></td>
      		<td class="setting_pos"><a href="#" class="setting_options">Edit HR User Permissions</a></td>
      		<td class="setting_pos"><a href="#" class="setting_options">Edit Message Templates</a></td>
      		<td class="setting_pos"><a href="#" class="setting_options">Add a Redirection Department</a></td>
      	</tr>	
      </table>

</div>

<div id="tab-2" class="tab-content">
<!-- <?php echo $this->textM->reimbursementTableDisplay($data_query_accounting, 'pending_accounting'); ?> -->
B
</div>

<div id="tab-3" class="tab-content">
C
</div>

<div id="tab-4" class="tab-content">
<!-- <?php echo $this->textM->reimbursementTableDisplay($data_query_medical, 'pending_med'); ?> -->
D
</div>



