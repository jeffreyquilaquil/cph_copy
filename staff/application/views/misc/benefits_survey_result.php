<style type="text/css">
	tbody tr{ height: 25px;}

</style>

<h2>Benefits Survey Results</h2>

<p><strong>Total Respondents as of <?php echo date('F d, Y g:i a') ?>:</strong> <?php echo count($survey_results) ?></p>

<h4>Frequencies of benefits usage</h4>
<hr>

<div class="table_container">
<table class="survey_table">
	<thead>
		<tr>
			<td align="center">Benefits</td>
			<?php foreach( $label_frequencies as $fre_label ):
				echo '<td align="center">'.$fre_label.'</td>';
			endforeach;	 ?>
		</tr>
	</thead>
	<tbody>
		<?php 
			foreach( $frequency_result as $name => $result ){
				echo '<tr>';
				echo '<td>'. $label_questions[ $name ]['label'] .'</td>';
				foreach( $label_frequencies as $key => $rat ){

					echo '<td align="center">'.( empty($result[$key]) ? 0 : $result[$key] ).'</td>';
				}
				echo '</tr>';
			}
		 ?>
	</tbody>
</table>
</div>

<div class="table_container">
<h4>Maxicare Rating</h4>
<hr>
<table class="survey_table">
	<thead>
		<tr>
			<td>Benefit</td>
			<?php foreach( $label_maxicare_rating as $label ):
				echo '<td>'. $label .'</td>';
			endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>HMO (Maxicare)</td>
			<?php foreach( $label_maxicare_rating as $mKey => $rating ):
				echo '<td align="center">'. (empty($maxicare_rating_result[$mKey]) ? 0 : $maxicare_rating_result[$mKey]) .'</td>';
			endforeach; ?>
		</tr>
	</tbody>
</table>
</div>


<h4>Satisfaction Rating of Benefits</h4>
<hr>
<table class="survey_table">
	<thead>
		<tr>
			<td>Benefits</td>
			<?php foreach( $label_ratings as $rating ):
				echo '<td>'.$rating.'</td>';
			endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $satisfaction_result as $ssKey => $sresult ):
			echo '<tr>';
			echo '<td>'. $label_second_questions[ $ssKey ]['label'] .'</td>';
			foreach( $label_ratings as $key => $rat ){

				echo '<td align="center">'.( empty($sresult[$rat]) ? 0 : $sresult[$rat] ).'</td>';
			}
			echo '</tr>';
		endforeach; ?>
	</tbody>
</table>
<br>
<h4>Comments</h4>
<hr>
<table class="survey_table">
	<thead>
		<tr>
			<td>Category</td>
			<td>Comments</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $all_comments as $cat => $comments ): 
			foreach( $comments as $comment ):
				if( !empty( $comment ) ){
					echo '<tr>';
					echo '<td>'. $label_second_questions[ $cat ]['label'] .'</td>';
					echo '<td>'. $comment . '</td>';	
					echo '</tr>';
				}
				
			endforeach;
		endforeach; ?>
	</tbody>
</table>
<br>
<h4>Suggestions</h4>
<hr>
<table class="survey_table">
	<thead>
		<tr>
			<td>Suggestions</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $suggestions as $suggestion):
			if( !empty( $suggestion ) ){
				echo '<tr>';
				echo '<td>'. $suggestion .'</td>';
				echo '</tr>';	
			}
			
		endforeach; ?>
	</tbody>
</table>