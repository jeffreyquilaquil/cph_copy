<style type="text/css">
      td{
            text-align: center;
       }
</style>

<h2>HR Customer Satifaction Survey</h2>

<div>

 <table class="datatable">
      	<thead>
	      	<tr>
	      		<th>Incident #</th>
	      		<th>Customer</th>
	      		<th>Date Submitted</th>
                        <th>Date Resolved</th>
	      		<th>Category</th>
	      		<th>Subject</th>
	      		<th>Priority</th>
	      		<th>Owner</th>
	      		<th>Remark</th>	
	      	</tr>
      	</thead>
      	<tbody>
            <?php foreach ($Remark_incident as $key => $value) { ?>
      		<tr>
      			<td><?php echo $value->post_id ?></td>
      			<td><?php echo $value->fname." ".$value->lname ?></td>
      			<td><?php echo $value->date_submited ?></td>
                        <td><?php echo $value->last_update ?></td>
      			<td><?php echo $value->assign_category ?></td>
      			<td><?php echo $value->cs_post_subject ?></td>
      			<td><?php echo $value->cs_post_urgency ?></td>
      			<td><?php echo $value->hr_own_empUSER ?></td>
      			<td><?php echo $value->remark ?></td>
      		</tr>
            <?php } ?>  
      	</tbody>
 		
      </table>
</div>

