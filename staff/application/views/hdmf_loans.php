<h2>HDMF Loan Applications</h2>
<hr/>

<ul class="tabs">
<?php 
	$counter = 0;
	foreach( $hdmf_loan_status as $val ){
		$current = '';
		$current = ($counter == 0 ? ' current' : '');
		$count = 0;
		$data_query = 'data_query_'.$counter;
		if( isset(${$data_query}) ){
			$data = ${$data_query};
			unset($data['headers']);
			$count = count( $data );	
		}
		
		echo '<li class="tab-link'.$current.'" data-tab="tab-'.$counter.'">'.ucwords($val).' ('.$count.')</li>';
		$counter++;
	}
?>
</ul>

<?php
	$counter = 0;
	foreach( $hdmf_loan_status as $val ){
		$current = '';
		$current = ($counter == 0 ? ' current' : '');
		echo '<div id="tab-'.$counter.'" class="tab-content'.$current.'"><br>';
		$data_query = 'data_query_'.$counter;
		if( isset(${$data_query}) ){
			$data = ${$data_query};
			unset($data['headers']);
			$count = count( $data );	
		}
		echo '<h3>'.ucwords($val).' ('.$count.')</h3>';
		if( !isset(${$data_query}['headers']) ){
			${$data_query}['headers'] = $headers;
		}
		
		$this->textM->renderTable( ${$data_query}['headers'], $col_options, ${$data_query}, true, true);
		echo '</div>';
		$counter++;
	}

?>

<script>
	$(document).ready(function(){

		$('select.stat_select').change(function(){
			var that = $(this);
			var stat_val = that.val();
			var id = that.data('id');
			var empID = that.data('empid');
			$('body').css({'cursor':'progress'});
			$.ajax({
				type: 'POST',
				data: {id : id, status : stat_val, empID : empID },
				url: '<?php echo $this->config->base_url().'hdmfs'; ?>',
				success: function(){
					alert('Status has updated.');
					window.location.reload();
				}

			});

		});

	});
</script>







