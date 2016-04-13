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
  <br>

      <table id="dt_new">
      	<thead>
      		<th>Incident #</th>
      		<th>Customer</th>
      		<th>Date Submitted</th>
      		<th>Subject</th>
      		<th>Priority</th>
      	</thead>
      	<tbody>
      	<?php foreach ($HrHelpDesk as $key => $value) { ?>
      		<tr>
	      		<td><?php echo $value->cs_post_id; ?></td>
	      		<td><?php echo $value->fname; ?></td>
	      		<td><?php  echo $value->cs_post_date_submitted; ?></td>
	      		<td><?php echo $value->cs_post_subject ?></td>
	      		<td><?php echo $value->cs_post_urgency; ?></td>
      		</tr>	     

      		<?php } ?> 		
      	</tbody>
      </table>

<script type="text/javascript">
	$(function(){
		$("table#dt_new").dataTable();					
	});
</script>

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

B

<div class="tiptext">Text
<div class="description"> Here is the big fat description box</div>
</div>


</div>

<div id="tab-3" class="tab-content">
C
</div>

<div id="tab-4" class="tab-content">

D
</div>
