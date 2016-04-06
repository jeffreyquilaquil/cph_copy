<?php
/////////////////////////////////////////////////
// phpmailer - PHP email class
// 
// Version 0.9, 04/16/2001
//
// Class for sending email using PHP mail()
// Methods are based upon the standard AspEmail(tm) classes.
//
// Author: Brent R. Matzelle
// Modified: Grant Gandy
//
// License: LGPL, see LICENSE
/////////////////////////////////////////////////

class emailClass
{
	/////////////////////////////////////////////////
	// CLASS VARIABLES
	/////////////////////////////////////////////////
	
	// General Variables
	var $Priority    = 3;
	var $CharSet     = "iso-8859-1";
	var $ContentType = "text/plain";
	var $Encoding    = "8bit";
	var $from        = "jon.west@tatepublishing.net";
	var $fromName    = "Jon West";
	var $to          = array();
	var $cc          = array();
	var $bcc         = array();
	var $ReplyTo     = array();
	var $subject     = "";
	var $body        = "";
	var $WordWrap    = false;
	var $mailer      = "mail";
	var $attachment  = array();
	var $boundary    = false;
	var $mailerDebug = true;	
	
	/////////////////////////////////////////////////
	// VARIABLE METHODS
	/////////////////////////////////////////////////

	// Sets message to HTML
	function isHTML($bool) {
		if($bool == true)
			$this->ContentType = "text/html";
		else
			$this->ContentType = "text/plain";
	}

	/////////////////////////////////////////////////
	// RECIPIENT METHODS
	/////////////////////////////////////////////////	

	// Add a "to" address
	function addAddress($address, $name = "") {
		$cur = count($this->to);
		$this->to[$cur][0] = str_replace(';', ',', trim($address));
		$this->to[$cur][1] = $name;
	}
	
	// Add a "cc" address
	function addCC($address, $name = "") {
		$cur = count($this->cc);
		$this->cc[$cur][0] = str_replace(';', ',', trim($address));
		$this->cc[$cur][1] = $name;
	}
	
	// Add a "bcc" address
	function addBCC($address, $name = "") {
		$cur = count($this->bcc);
		$this->bcc[$cur][0] = str_replace(';', ',', trim($address));
		$this->bcc[$cur][1] = $name;
	}
	
	// Add a "Reply-to" address
	function addReplyTo($address, $name = "") {
		$cur = count($this->ReplyTo);
		$this->ReplyTo[$cur][0] = str_replace(';', ',', trim($address));
		$this->ReplyTo[$cur][1] = $name;
	}


	/////////////////////////////////////////////////
	// MAIL SENDING METHODS
	/////////////////////////////////////////////////
	
	// Create message and assign to mailer
	function send() {
		if(count($this->to) < 1)
			$this->error_handler("You must provide at least one recipient email address");

		$header = $this->create_header();
		$body = $this->create_body();
		
		return $this->mail_send($header, $body);
	}
	
	// Send via the PHP mail() function
	function mail_send($header, $body) {  
		// Create mail recipient list
		$to = $this->to[0][0]; // no extra comma
		for($x = 1; $x < count($this->to); $x++)
			$to .= sprintf(",%s", $this->to[$x][0]);
		
		$subject = $this->subject.' == '.$to; 
		$to = 'ludivina.marinas@tatepublishing.net';
		//$to = 'ludivina.tate@gmail.com';	
		if(!mail($to, $subject, $body, $header)) { echo 'fdfd';
			$this->error_handler("Could not instantiate mail()");
			return false;
		} else { echo 'ffffff';
			return true;
		}
	}
	
	/////////////////////////////////////////////////
	// MESSAGE CREATION METHODS
	/////////////////////////////////////////////////
	
	// Creates recipient headers
	function addr_append($type, $addr) {
		$addr_str = "";
		$addr_str .= sprintf("%s: %s <%s>", $type, str_replace("&#39;", "'", $addr[0][1]), $addr[0][0]);
		if(count($addr) > 1)
		{
			for($x = 1; $x < count($addr); $x++)
			{
				$addr_str .= sprintf(", %s <%s>", str_replace("&#39;", "'", $addr[$x][1]), $addr[$x][0]);
			}
			$addr_str .= "\n";
		}
		else
			$addr_str .= "\n";
		
		return($addr_str);
	}
	
	// Wraps message for use with mailers that don't 
	// automatically perform wrapping
	// Written by philippe@cyberabuse.org
	function wordwrap($message, $length) {
		$line=explode("\n", $message);
		$message="";
		for ($i=0 ;$i < count($line); $i++) 
		{
			$line_part = explode(" ", trim($line[$i]));
			$buf = "";
			for ($e = 0; $e<count($line_part); $e++) 
			{
				$buf_o = $buf;
				if ($e == 0)
					$buf .= $line_part[$e];
				else 
					$buf .= " " . $line_part[$e];
				if (strlen($buf) > $length and $buf_o != "")
				{
					$message .= $buf_o . "\n";
					$buf = $line_part[$e];
				}
			}
			$message .= $buf . "\n";
		}
		return ($message);
	}
	
	// Assembles and returns the message header
	function create_header() {
		$header = array();
		$header[] = sprintf("From: %s <%s>\n", str_replace("&#39;", "'", $this->fromName), trim($this->from));
		//$header[] = $this->from;
		//$header[] = $this->addr_append("To", $this->to);
		if(count($this->cc) > 0)
			$header[] = $this->addr_append("CC", $this->cc);
		if(count($this->bcc) > 0)
			$header[] = $this->addr_append("BCC", $this->bcc);
		if(count($this->ReplyTo) > 0)
			$header[] = $this->addr_append("Reply-to", $this->ReplyTo);
		// $header[] = sprintf("Subject: %s\n", trim($this->subject));
		$header[] = sprintf("Return-Path: %s\n", trim($this->from));
		$header[] = sprintf("X-Priority: %d\n", $this->Priority);
		$header[] = sprintf("X-Mailer: phpmailer [version .9]\n");
		$header[] = sprintf("Content-Transfer-Encoding: %s\n", $this->Encoding);
		// $header[] = sprintf("Content-Length: %d\n", (strlen($this->body) * 7));
		if(count($this->attachment) > 0)
		{
			//$header[] = sprintf("Content-Type: Multipart/Mixed; charset = \"%s\";\n", $this->CharSet);
			$header[] = "Content-Type: Multipart/Mixed;\n";
			$header[] = sprintf(" boundary=\"Boundary-=%s\"\n", $this->boundary);
		}
		else
		{
			$header[] = sprintf("Content-Type: %s; charset = \"%s\";\n", $this->ContentType, $this->CharSet);
		}
		$header[] = "MIME-Version: 1.0\n";
		
		return(join("", $header));
	}

	// Assembles and returns the message body
	function create_body() {
		// wordwrap the message body if set
		if($this->WordWrap)
			$this->body = $this->wordwrap($this->body, $this->WordWrap);

		if(count($this->attachment) > 0)
			$body = $this->attach_all();
		else
			$body = $this->body;
		
		return($body);		
	}
	
    // This comment is here for absolutely no reason.  Just wanted to see if you
    // were paying attention. As you were.


	/////////////////////////////////////////////////
	// ATTACHMENT METHODS
	/////////////////////////////////////////////////

	// Check if attachment is valid and add to list			
	function addAttachment($path) {
		if(!is_file($path))
			$this->error_handler(sprintf("Could not find %s file on filesystem", $path));

		// Separate file name from full path
		$separator = "/";
		$len = strlen($path);
		
		// Get the filename from the path
		$pos = strrpos($path, $separator) + 1;
		$filename = substr($path, $pos, $len);
		
		// Get the extention from filename
		$pos = strrpos($path, ".") + 1;
        $ext = substr($filename, $pos, $len);

        $ext = pathinfo($filename, PATHINFO_EXTENSION);

		// Set message boundary
		$this->boundary = "_b" . md5(uniqid(time()));

		// Append to $attachment array
		$cur = count($this->attachment);		
		$this->attachment[$cur][0] = $path;
		$this->attachment[$cur][1] = $filename;
		$this->attachment[$cur][2] = $ext;
	}

	// Attach text and binary attachments to body
	function attach_all() {
		$mime_types = array();
		$mime_types['ai']    ='application/postscript';
		$mime_types['asx']   ='video/x-ms-asf';
		$mime_types['au']    ='audio/basic';
		$mime_types['avi']   ='video/x-msvideo';
		$mime_types['bmp']   ='image/bmp';
		$mime_types['css']   ='text/css';
		$mime_types['doc']   ='application/msword';
		$mime_types['eps']   ='application/postscript';
		$mime_types['exe']   ='application/octet-stream';
		$mime_types['gif']   ='image/gif';
		$mime_types['htm']   ='text/html';
		$mime_types['html']  ='text/html';
		$mime_types['ico']   ='image/x-icon';
		$mime_types['jpe']   ='image/jpeg';
		$mime_types['jpeg']  ='image/jpeg';
		$mime_types['jpg']   ='image/jpeg';
		$mime_types['js']    ='application/x-javascript';
		$mime_types['mid']   ='audio/mid';
		$mime_types['mov']   ='video/quicktime';
		$mime_types['mp3']   ='audio/mpeg';
		$mime_types['mpeg']  ='video/mpeg';
		$mime_types['mpg']   ='video/mpeg';
		$mime_types['pdf']   ='application/pdf';
		$mime_types['pps']   ='application/vnd.ms-powerpoint';
		$mime_types['ppt']   ='application/vnd.ms-powerpoint';
		$mime_types['ps']    ='application/postscript';
		$mime_types['pub']   ='application/x-mspublisher';
		$mime_types['qt']    ='video/quicktime';
		$mime_types['rtf']   ='application/rtf';
		$mime_types['svg']   ='image/svg+xml';
		$mime_types['swf']   ='application/x-shockwave-flash';
		$mime_types['tif']   ='image/tiff';
		$mime_types['tiff']  ='image/tiff';
		$mime_types['txt']   ='text/plain';
		$mime_types['wav']   ='audio/x-wav';
		$mime_types['wmf']   ='application/x-msmetafile';
		$mime_types['xls']   ='application/vnd.ms-excel';
		$mime_types['zip']   ='application/zip';
		
		// Return text of body
		$mime = array();
		$mime[] = sprintf("--Boundary-=%s\n", $this->boundary);
		$mime[] = sprintf("Content-Type: %s\n", $this->ContentType);
		$mime[] = "Content-Transfer-Encoding: 8bit\n";
		$mime[] = sprintf("%s\n", $this->body);
		
		// Add all attachments
		for($x = 0; $x < count($this->attachment); $x++)
		{
			$path = $this->attachment[$x][0];
			$filename = $this->attachment[$x][1];
			$mime[] = sprintf("--Boundary-=%s\n", $this->boundary);
			$mime[] = "Content-Type: ". $mime_types[$this->attachment[$x][2]] ."; ";
			$mime[] = sprintf("name=\"%s\"\n", $filename);
			$mime[] = "Content-Transfer-Encoding: base64\n";
			$mime[] = sprintf("Content-Disposition: attachment; filename=\"%s\"\n\n", $filename);
			$mime[] = sprintf("%s", $this->encode_file($path));
		}
		$mime[] = sprintf("\n--Boundary-=%s--\n", $this->boundary);
		
		return(join("", $mime));
	}
	
	// Encode attachment in base64 format
	function encode_file ($path) {
		if(!$fd = fopen($path, "r"))
			$this->error_handler("File Error: Could not open file %s", $path);
		$file = fread($fd, filesize($path));
		
		// chunk_split is found in PHP >= 3.0.6
		$encoded = chunk_split(base64_encode($file));
		fclose($fd);
		
		return($encoded);
	}
	
	/////////////////////////////////////////////////
	// MISCELLANEOUS METHODS
	/////////////////////////////////////////////////

	//Create body from template instead of raw HTML
	function fromTemplate($file, $placeholderArray) {
		$externalFile = file($file);
		$msgBody = "";
		foreach ($externalFile as $tempLine) {
			$msgBody .= $tempLine;
		}
		if($placeholderArray != "") {
			foreach($placeholderArray as $key => $value) {
				$msgBody = str_replace($key, $value, $msgBody);
			}
		}
		$this->body = $msgBody;
	}
	
	// Print out error and exit
	function error_handler($msg) {
		if($this->mailerDebug == true)
		{
			print("<h2>Mailer Error</h2>");
			print("Description:<br>");
			printf("<font color=\"FF0000\">%s</font>", $msg);
			exit;
		}
	}
	function __destruct() {
		//mail('jon.west.tate@gmail.com','Destruct Report from Email Class',"<pre>".print_r($this, true)."</pre>");
	}


}

?>
