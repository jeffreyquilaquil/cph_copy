<?php if( isset($success) AND $success == true ){
	echo 'Thank you for answering the survey';
	exit();
} ?>

<style>
	ul{list-style-type:none;}
	ul li{margin-bottom:10px;}
	textarea{width:100%;}
</style>

<h2>2016 Employee Benefits Satifaction Survey</h2>
<hr/>
<p>
	Tate Publishing and Enterprises (Philippines), Inc. strives to provide valuable and comprehensive benefit programs. Each year, we review our current programs particularly in our health benefits to ensure they live up to these goals and are meeting employees’ needs. Please review our current benefits in this link : http://employee.tatepublishing.net/hr/benefits-list/
</p>
<p>
	This survey should only take 2-3 minutes to complete. Be assured that all answers you provide will be kept in the strictest confidentiality. To help us provide benefits that meet your needs, please complete this survey.
</p>
<p>
Thank you! 
</p>

<h3>Which of the benefits package that you frequently used?</h3>

<?php 
	$frequencies = $this->config->item('frequencies');
	$questions = $this->config->item('questions');
	$maxicare_rating = $this->config->item('maxicare_rating');
	$ratings = $this->config->item('ratings');
	$second_questions = $this->config->item('second_questions');
		

 ?>
<form method="post" action="" id="survey">
 <table>
 	<thead>
 		<tr>
 			<td>&nbsp;</td>
 			<?php 
 				foreach( $frequencies as $frequency ){
 					echo '<td align="center">'. $frequency . '</td>';
 				}
 			 ?>
 		</tr>	
 	</thead>
 	<tbody>
 		<?php 
 			foreach( $questions as $question ){
 				echo '<tr height="30px">';
 					echo '<td>'. $question['label'] .'</td>';
 					foreach( $frequencies as $key => $frequency ){
 						echo '<td align="center"><input type="radio" name="'.$question['name'].'_frequency" value="'.$key.'" />';
 					}
 				echo '</tr>';
 			}
 		 ?>
 	</tbody>
 </table>

 <p>We would like to know how you rate our current benefits, please select your level of satisfaction of each category:</p>
<div>
	<ol>
		<li>
			<p>Does our health insurance (Maxicare) plan better, worse, or about the same as those of other employers? </p>
			<ul>
				<?php 
					foreach( $maxicare_rating as $key => $mrating ){
						echo '<li><input type="radio" name="maxicare_rating" value="'. $key .'" id="maxicare_rating_'.$key.'" /><label for="maxicare_rating_'.$key.'">'.$mrating.'</label>'; 
					}
				 ?>
			</ul>
			<p>Comments or suggestions for your Healthcare Insurance (Maxicare)<br/>
				<textarea name="maxicare_comments"></textarea></p>
		</li>
		<?php 
			foreach( $second_questions as $question ){
				echo '<li>';
					echo '<p>'.$question['label'].'</p>';
					echo '<ul>';
						foreach( $ratings as $key => $rating ){
							echo '<li>';
								echo '<input type="radio" name="satisfaction_'.$question['name'].'" value="'.$rating.'" id="satisfaction_'.$question['name'].'_'.$key.'" /><label for="satisfaction_'.$question['name'].'_'.$key.'">'.$rating.'</label>';
							echo '</li>';
						}
					echo '</ul>';
					echo '<textarea name="comments_'.$question['name'].'"></textarea>';
				echo '</li>';
			}
		 ?>
		<li>
			<p>Benefit suggestion that you want to include in the revamping of benefit package on 2017:</p>
			<textarea name="suggestion"></textarea>
		</li>
	</ol>
	<div style="text-align:center;"><input type="submit" name="submit" value="Submit" class="btnclass" /></div>
</div>
</form>

<script type="text/javascript">
	$(function() {
		

		$('form#survey').on('submit', function(e){
			//e.preventDefault();
			var all_answered = true;
			$('input[type="radio"]').each( function(){
				var name = $(this).attr('name');
				if( $('input:radio[name="'+name+'"]:checked').length == 0 ){
					all_answered = false;
				}
			});
			if( all_answered == false ){
				alert('Please answer each item.');
			} 
			return all_answered;
		});
	});
</script>