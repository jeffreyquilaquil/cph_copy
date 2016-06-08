<style type="text/css">
      td{
            text-align: center;
       }
</style>

<h2>HR/Accounting Customer Satifaction Survey</h2>

<!-- table for hr customer satisfaction survey -->
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
                        <td><?php echo date_format(date_create($value->date_submited), 'F d, Y G:ia'); ?></td>
                        <td><?php echo date_format(date_create($value->last_update), 'F d, Y G:ia'); ?></td>
      			<td><?php if($value->assign_category==''){echo "-";}else{echo $value->assign_category;} ?></td>
      			<td><?php echo $value->cs_post_subject ?></td>
      			<td>
                        <?php if($value->cs_post_urgency=='Urgent'){ 
                                    echo "<div class='urgent'>$value->cs_post_urgency</div>";
                              }elseif($value->cs_post_urgency=='Need Attention'){
                                    echo "<div class='need_attention'>$value->cs_post_urgency</div>";
                              }elseif($value->cs_post_urgency=='Not Urgent'){
                                    echo "<div class='not_urgent'>$value->cs_post_urgency</div>";
                              }else{
                                    echo "-";
                              }
                        ?>
                        </td>
      			<td><?php echo $value->hr_own_empUSER ?></td>
      			<td><?php echo $value->remark ?></td>
      		</tr>
            <?php } ?>  
      	</tbody>
 		
      </table>
</div>

