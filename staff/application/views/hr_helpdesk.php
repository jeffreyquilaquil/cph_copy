<style type="text/css">
	th{
	 	background-color: #CCCCCC;
	 }
	 td{
	 	text-align: center;
	 }

	 a.other_links:link, a.other_links:visited, a.other_links:hover, a.other_links:active{
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

	.td_hover {
	    position: relative;
	    display: inline-block;
	    text-align: center;
	}

.td_hover .show_details {
    visibility: hidden;
    width: 120px;
    background-color: black;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px 0;
    position: absolute;
    z-index: 1;
    top: -5px;
    left: 110%;
}

.td_hover .show_details::after {
    content: "";
    position: absolute;
    top: 50%;
    right: 100%;
    margin-top: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: transparent black transparent transparent;
}
.td_hover:hover .show_details {
    visibility: visible;
} 



</style>

<h2>HR HelpDesk</h2>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">New</li>
	<li class="tab-link" data-tab="tab-2">Active</li>
	<li class="tab-link" data-tab="tab-3">Resolved</li>
	<li class="tab-link" data-tab="tab-4">Cancelled</li>

	<div class="options-right">
		<a class="other_links" href="#">Generate Reports</a>	
		<a class="other_links" href="#">HR Customer CSatResults</a>
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
	      		<td class="td_hover">
	      			<a href="<?php echo $this->config->base_url(); ?>hr_incidentinfo" class="iframe">
	      				<?php echo $value->cs_post_id; ?>
	      			</a>
      			<div class="show_details">
      				Details of the incident
      			</div>
	      		</td>
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
		<a class="other_links" href="#">Add Categories</a>
  		<a class="other_links" href="#">Edit HR User Permissions</a>
  		<a class="other_links" href="#">Edit Message Templates</a>
  		<a class="other_links" href="#">Add a Redirection Department</a>
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
