<?php 

if( isset($success) ){

	echo $success.' has been updated.';
	echo '<script>parent.window.location.reload();</script>';
	exit();
}

echo '<h3>Update Last Pay</h3>';
echo '<hr/>';

switch( $which_to ){
	case 'scheddate':
		echo '<h4>Set Scheduled Release Date</h4><br/>';
		echo form_open( $this->config->base_url().'timecard/managelastpay');
		echo form_hidden('id', $_GET['payID']);
		echo form_input(array('name' => 'scheddate', 'class' => 'datepick') );
		echo form_submit('sched_releasedate', 'Schedule');
		echo form_close();
	break;

	case 'checkno':
		echo '<h4>Set Check No.</h4><br/>';
		echo form_open( $this->config->base_url().'timecard/managelastpay');
		echo form_hidden('id', $_GET['payID']);
		echo form_input(array('name' => 'checkno') );
		echo form_submit('update_checkno', 'Submit');
		echo form_close();
	break;
}
