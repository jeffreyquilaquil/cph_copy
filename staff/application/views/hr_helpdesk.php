<style type="text/css">
	th{
	 	background-color: #CCCCCC;
	 }
	 td{
	 	text-align: center;
	 }

	 a:link, a:visited, a:hover, a:active{
	 	color: black;
	 	text-decoration: none;
	 	background-color: #CCCCCC; 
	 	padding: 8px 20px 8px 20px;	 
	 }

	 .settings-bottom{
	 	margin-bottom: 5px;
	 }

	 .options-right{
	 	float: right; 
	 	margin-top: 12.5px;
	 }
	
</style>

<h2>HR HelpDesk</h2>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">New</li>
	<li class="tab-link" data-tab="tab-2">Active</li>
	<li class="tab-link" data-tab="tab-3">Resolved</li>
	<li class="tab-link" data-tab="tab-4">Cancelled</li>

	<div class="options-right">
		<a href="#">Generate Reports</a>	
		<a href="#">HR Customer CSatResults</a>
	</div>
	
</ul>


<hr/>
<div id="tab-1" class="tab-content current">
<!-- <?php echo $this->textM->reimbursementTableDisplay($data_query_medical, 'pending_med'); ?> -->
  <br>
  <!--
  <table class="tableInfo fs11px">
      	<tr>
      		<th>Incident #</th>
      		<th>Customer</th>
      		<th>Date Submitted</th>
      		<th>Subject</th>
      		<th>Priority</th>
      	</tr>
		<tr>
      		<td>1 <div>Insert your code here!</div></td>
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

      -->

      <div id="tbl_570dc0bf52069_wrapper" class="dataTables_wrapper no-footer">
      	<div class="dataTables_length" id="tbl_570dc0bf52069_length">
      		<label>
      		Show
	      		<select name="tbl_570dc0bf52069_length" aria-controls="tbl_570dc0bf52069" class>
		      		<option value="10">10</option>
		      		<option value="25">25</option>
		      		<option value="50">50</option>
		      		<option value="100">100</option>
	      		</select>
      		entries
      		</label>
      	</div>
      </div>

      <br>
      <h2>SETTINGS</h2><br>
	
	<div class="settings-bottom">
		<a href="#">Add Categories</a>
  		<a href="#">Edit HR User Permissions</a>
  		<a href="#">Edit Message Templates</a>
  		<a href="#">Add a Redirection Department</a>
	</div>    	
</div>

<div id="tab-2" class="tab-content">
<!-- <?php echo $this->textM->reimbursementTableDisplay($data_query_accounting, 'pending_accounting'); ?> -->
B

<div class="tiptext">Text
<div class="description"> Here is the big fat description box</div>
</div>


</div>

<div id="tab-3" class="tab-content">
C
</div>

<div id="tab-4" class="tab-content">
<!-- <?php echo $this->textM->reimbursementTableDisplay($data_query_medical, 'pending_med'); ?> -->
D
</div>
