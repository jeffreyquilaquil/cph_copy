<?php 
	$this->load->view('includes/header_timecard'); 
	$dataArr['content'] = array();
	
	
	$this->load->view('tc_calendartemplate', $dataArr);
?>