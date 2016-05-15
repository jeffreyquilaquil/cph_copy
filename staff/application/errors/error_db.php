<!DOCTYPE html>
<html lang="en">
<head>
<title>Database Error</title>
<style type="text/css">

::selection{ background-color: #E13300; color: white; }
::moz-selection{ background-color: #E13300; color: white; }
::webkit-selection{ background-color: #E13300; color: white; }

body {
	background-color: #fff;
	margin: 40px;
	font: 13px/20px normal Helvetica, Arial, sans-serif;
	color: #4F5155;
}

a {
	color: #003399;
	background-color: transparent;
	font-weight: normal;
}

h1 {
	color: #444;
	background-color: transparent;
	border-bottom: 1px solid #D0D0D0;
	font-size: 19px;
	font-weight: normal;
	margin: 0 0 14px 0;
	padding: 14px 15px 10px 15px;
}

code {
	font-family: Consolas, Monaco, Courier New, Courier, monospace;
	font-size: 12px;
	background-color: #f9f9f9;
	border: 1px solid #D0D0D0;
	color: #002166;
	display: block;
	margin: 14px 0 14px 0;
	padding: 12px 10px 12px 10px;
}

#container {
	margin: 10px;
	border: 1px solid #D0D0D0;
	-webkit-box-shadow: 0 0 8px #D0D0D0;
}

p {
	margin: 12px 15px 12px 15px;
}
</style>
</head>
<body>
	<div id="container">
<?php 
//send email for the error message
$CI =& get_instance();
$CI->load->model('emailmodel');
$from = 'careers.cebu@tatepublishing.net';
$to = 'marjune.abellana@tatepublishing.net';
$server_info = print_r($_SERVER, true);
$body = '<h1>'.$heading.'</h1>'.$message.'<p>'.$server_info.'</p>'.'<p>'.date('Y-m-d H:i:s').'</p>';
$subject = 'CPH DB error';
$fromName = 'CPH';


$CI->emailmodel->sendEmail( $from, $to, $subject, $body, $fromName);
?>
		<h1><?php echo $heading; ?></h1>
		<p>Please email helpdesk.cebu@tatepublishing.net for assistance.</p>
	</div>
</body>
</html>