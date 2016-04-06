<<<<<<< HEAD
<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 */
	define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
switch (ENVIRONMENT)
{
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

/*
 *---------------------------------------------------------------
 * SYSTEM DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" directory.
 * Set the path if it is not in the same directory as this file.
 */
	$system_path = 'system';

/*
 *---------------------------------------------------------------
 * APPLICATION DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * directory than the default one you can set its name here. The directory
 * can also be renamed or relocated anywhere on your server. If you do,
 * use an absolute (full) server path.
 * For more info please see the user guide:
 *
 * https://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 */
	$application_folder = 'application';

/*
 *---------------------------------------------------------------
 * VIEW DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * If you want to move the view directory out of the application
 * directory, set the path to it here. The directory can be renamed
 * and relocated anywhere on your server. If blank, it will default
 * to the standard location inside your application directory.
 * If you do move this, use an absolute (full) server path.
 *
 * NO TRAILING SLASH!
 */
	$view_folder = '';


/*
 * --------------------------------------------------------------------
 * DEFAULT CONTROLLER
 * --------------------------------------------------------------------
 *
 * Normally you will set your default controller in the routes.php file.
 * You can, however, force a custom routing by hard-coding a
 * specific controller class/function here. For most applications, you
 * WILL NOT set your routing here, but it's an option for those
 * special instances where you might want to override the standard
 * routing in a specific front controller that shares a common CI installation.
 *
 * IMPORTANT: If you set the routing here, NO OTHER controller will be
 * callable. In essence, this preference limits your application to ONE
 * specific controller. Leave the function name blank if you need
 * to call functions dynamically via the URI.
 *
 * Un-comment the $routing array below to use this feature
 */
	// The directory name, relative to the "controllers" directory.  Leave blank
	// if your controller is not in a sub-directory within the "controllers" one
	// $routing['directory'] = '';

	// The controller class file name.  Example:  mycontroller
	// $routing['controller'] = '';

	// The controller function you wish to be called.
	// $routing['function']	= '';


/*
 * -------------------------------------------------------------------
 *  CUSTOM CONFIG VALUES
 * -------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class when initialized. This allows you to set custom config
 * items or override any default config values found in the config.php file.
 * This can be handy as it permits you to share one application between
 * multiple front controller files, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 */
	// $assign_to_config['name_of_config_item'] = 'value of config item';



// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

	// Set the current directory correctly for CLI requests
	if (defined('STDIN'))
	{
		chdir(dirname(__FILE__));
	}

	if (($_temp = realpath($system_path)) !== FALSE)
	{
		$system_path = $_temp.DIRECTORY_SEPARATOR;
	}
	else
	{
		// Ensure there's a trailing slash
		$system_path = strtr(
			rtrim($system_path, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		).DIRECTORY_SEPARATOR;
	}

	// Is the system path correct?
	if ( ! is_dir($system_path))
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
		exit(3); // EXIT_CONFIG
	}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	// Path to the system directory
	define('BASEPATH', $system_path);

	// Path to the front controller (this file) directory
	define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

	// Name of the "system" directory
	define('SYSDIR', basename(BASEPATH));

	// The path to the "application" directory
	if (is_dir($application_folder))
	{
		if (($_temp = realpath($application_folder)) !== FALSE)
		{
			$application_folder = $_temp;
		}
		else
		{
			$application_folder = strtr(
				rtrim($application_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
	{
		$application_folder = BASEPATH.strtr(
			trim($application_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}

	define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);

	// The path to the "views" directory
	if ( ! isset($view_folder[0]) && is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR))
	{
		$view_folder = APPPATH.'views';
	}
	elseif (is_dir($view_folder))
	{
		if (($_temp = realpath($view_folder)) !== FALSE)
		{
			$view_folder = $_temp;
		}
		else
		{
			$view_folder = strtr(
				rtrim($view_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(APPPATH.$view_folder.DIRECTORY_SEPARATOR))
	{
		$view_folder = APPPATH.strtr(
			trim($view_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}

	define('VIEWPATH', $view_folder.DIRECTORY_SEPARATOR);

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */
require_once BASEPATH.'core/CodeIgniter.php';
=======
<?php 
require 'config.php';
require_once 'includes/recaptchalib.php';
if(isset($_POST['submit'])){
	//Google captcha
	$resp = $recaptcha->is_valid($_POST['g-recaptcha-response']);
	if(!$resp){
		$error[] = "Please check \"I'm not a robot\"";
	}
	unset($_POST['g-recaptcha-response']);
	//end Google captcha
	if(empty($_POST['lname'])){
		$error['lname'] = "Last Name empty.";
	}
	if(empty($_POST['fname'])){
		$error['fname'] = "First Name empty.";
	}
	if(empty($_POST['bdate'])){
		$error['bdate'] = "Birthdate empty.";
	}
	if(empty($_POST['address'])){
		$error['address'] = "Address is empty.";
	}
	if(empty($_POST['mnumber'])){
		$error['mnumber'] = "Mobile empty.";
	}
	
	if(strpos($_POST['email'],"djuban@careerfirstinstitute.com") === FALSE){
		if(is_good_email($_POST['email']) !== TRUE){
			$error['email'] = is_good_email($_POST['email']);
		} 
	
	}
	
	if(checkDuplicateApplicant($_POST['fname'],$_POST['lname'], $_POST['bdate'] ) !== TRUE){	
		$error['glaiza'] = checkDuplicateApplicant($_POST['fname'],$_POST['lname'], $_POST['bdate'] );		
	}
	
	if(empty($_POST['position'])){
		$error['position'] = "Position applied is empty.";
	}
	
	if(empty($_POST['gender'])){
		$error['gender'] = "Gender is empty.";
	}
	
	if(empty($_POST['source'])){
		$error['source'] = "Where did you hear about Tate Publishing is empty.";		
	} else {
		if($_POST['source'] == "Referred by a Tate Employee" || $_POST['source'] == "Online Portals" || $_POST['source'] == "Other"){
			if(empty($_POST['source_field']))
				$error['source_field'] = "Referal box is empty.";		
		}
	}
	
	if(empty($_POST['text_resume'])){
		$error['text_resume'] = "Text Resume is empty.";
	}
	
	if(sizeof($error)==0){
		unset($_POST['submit']);
		$_POST['ipaddress'] = $_SERVER["REMOTE_ADDR"]."|".$_SERVER['HTTP_X_FORWARDED_FOR'];
		$_POST['date_created'] = "NOW()";
		
		if(isset($_GET['id']) && $_POST['source']=='Referred by a Tate Employee'){
			$_POST['referrerID'] = $_GET['id'];
		}
			
		$db->insertQuery("applicants", $_POST);
		$update[] = "<h2>Thank You!</h2><p>You have successfully sent your Application Form to us. Please check your mobile and email inbox regularly for updates on your application.</p><p>For more info, please visit our website at <a href='https://www.tatepublishing.com'>https://www.tatepublishing.com</a></p>";
		
		//check if there is an open job requisition send email if none
		$jobReq = $db->selectSingleQueryArray('jobReqData', 'reqID, supervisor, requestor' , 'positionID="'.$_POST['position'].'" AND status=0 AND appID=0', 'LEFT JOIN newPositions ON positionID = posID'); 
	
		if(empty($jobReq['reqID'])){
			$posName = $db->selectSingleQuery('newPositions', 'title', 'posID="'.$_POST['position'].'"');
			$queryOpen = $db->selectQueryArray('SELECT title FROM jobReqData LEFT JOIN newPositions ON posID=positionID WHERE status = 0 GROUP BY positionID ORDER BY title');
			
			$openPositions = '<ul>';
			foreach($queryOpen AS $o){
				$openPositions .= '<li>'.$o['title'].'</li>';
			}
			$openPositions .= '</ul>';
			
			$to = $_POST['email'];
			$subject = 'Thank you for submitting your application to Tate Publishing';
			$bod = '<p>Hello '.$_POST['fname'].',</p>

					<p>This is to confirm that we have received your application for the position of '.$posName.'.<br/>
					Please be informed that the said position is currently not open.<br/>
					Your application will be processed as soon as the position opens.</p>

					In the mean time, you may be interested to apply for the below positions that are currently open in Tate Publishing:<br/>
					'.$openPositions.'
					
					<p>If you are interested to know more about any of the above positions or if you would like to apply for any of the above positions, please reply to this email and we will be glad to process your application. Thank you very much.</p>
					
					<p><br/></p>
					<p>Tate Publishing HR</p>';		

			sendEmail( 'careers.cebu@tatepublishing.net', $to, $subject, $bod, 'Tate Publishing HR' );			
		}
		
		unset($_POST);
		unset($_SESSION['uploads']);
	}
}

$oQuery = $db->selectQueryArray('SELECT posID, title, `desc` FROM jobReqData LEFT JOIN newPositions ON posID=positionID WHERE status = 0 GROUP BY positionID ORDER BY title');

require 'includes/header.php';
?>
    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="container">
        <div class="well">
        	<?php echo get_error();?>
        	<?php if(sizeof($update) == 0):?>
			<div style="float:right; width:19%; padding:10px; line-height:1;">
				<div style="font-size:18px; color:#800000; font-weight:bold; text-align:center; padding-bottom:10px;">Open Positions</div>
			<?php
				foreach($oQuery AS $o){
					echo '<div id="oDiv_'.$o['posID'].'" style="background-color:#fff; color:#800000;">
							<div style="border:1px solid #000; padding:5px; font-weight:bold; cursor:pointer;" onClick="showOpos('.$o['posID'].')">
								'.$o['title'].'
							</div>
							<div class="oposDivDesc" style="font-size:11px; padding:10px;">
								'.((empty($o['desc']))?'No description':nl2br($o['desc'])).'
							</div>
						</div>';
				}
			?>
				<br/>
				<i style="font-size:11px; color:#888; padding:5px; text-align:center;">You may still apply for other positions that are not currently open, and once the positions open, we will gladly process your application!</i>
			</div>		
			
        	<!-- Form -->
              <?php $form->formStart("","POST",'class="bs-example form-horizontal" style="width:80%"');?>
                <fieldset>
                  <legend>Application Form    <br/><font size="2px;" ><b><i>***Check your mobile and email inbox regularly for updates on your application. </i></b></font></legend>
                    	<?php $form->text("lname",$_POST['lname'],'class="form-control"',"Last Name","",TRUE);?>
                    	<?php $form->text("fname",$_POST['fname'],'class="form-control"',"First Name","",TRUE);?>
                    	<?php $form->text("mname",$_POST['mname'],'class="form-control"',"Middle Name");?>
                    	<?php $form->text("suffix",$_POST['suffix'],'class="form-control"',"Suffix");?>
						
						<div class="form-group has-warning">
							<label for="select" class="col-lg-2 control-label">Gender</label>
							<div class="col-lg-10">
								<select class="form-control " id="gender" name="gender">
									<option></option>
									<option <?php if($_POST["gender"] == "male" ) echo "selected";?> value="male" >Male</option>
									<option <?php if($_POST["gender"] == "female" ) echo "selected";?> value="female">Female</option>									
								</select>
							</div>
						</div>
						
						
                    	<?php $form->date("bdate",$_POST['bdate'],'class="form-control"',"Birthdate","",TRUE);?>
                    	<?php $form->textarea("address",$_POST['address'],'class="form-control"',"Address","",TRUE);?>
                    	<?php $form->text("mnumber",$_POST['mnumber'],'class="form-control"',"Mobile Number","Mobile #",TRUE);?>
                    	<?php $form->email("email",$_POST['email'],'class="form-control"',"Email Address","",TRUE);?>
                    	<?php 						
                    		/* $pos = $db->selectQuery("newPositions", "posID,title,dept", "active=1 ORDER BY dept, title ASC");
                    		$positions = array(" ");
							$dept = '';
                    		if(is_array($pos)){
                    			foreach($pos AS $v){
									if($v['dept'] != $dept ){
										$positions[$v['dept']] = $v['dept'];
										$dept = $v['dept'];
									}
										
                    				$positions[$v['posID']] = $v['title'];	
                    			}
                    		}	 */						
                    	?>
                    	<?php //$form->select("position",$_POST['position'],$positions,'class="form-control"',"Position applied","",TRUE);?>
						
						<div class="form-group has-warning">
							<label for="select" class="col-lg-2 control-label">Position applied</label>
							<div class="col-lg-10">
								<select class="form-control" placeholder="Position applied" name="position">
									<option value=""></option>
									<?php
										$pos = $db->selectQuery("newPositions", "posID,title,dept", "active=1 ORDER BY dept, title ASC");
										$dept = '';
										foreach( $pos AS $p ){
											if($p['dept'] != $dept){
												if($dept!='')
													echo '</optgroup>';
													
												echo '<optgroup label="'.$p['dept'].'">';
												$dept = $p['dept'];
											}												
											echo '<option value="'.$p['posID'].'" '.((isset($_POST['position']) && $_POST['position']==$p['posID'])?'selected':'').'>'.$p['title'].'</option>';
										}
									?>
								</select>
							</div>
						</div>
                    						
						<div class="form-group has-warning" <? if(isset($_GET['refername'])){ echo 'style="display:none;"'; } ?>>
							<label for="select" class="col-lg-2 control-label">How did you learn about job opportunities in Tate Publishing?</label>
							<div class="col-lg-10">
								<select class="form-control " onchange='onchangeSource(this.value);' id="source" name="source">
									<option></option>
									<option <?php if($_POST["source"] == "Career First Institute" ) echo "selected";?> value="Career First Institute" >Career First Institute</option>
									<option <?php if($_POST["source"] == "Cebu Recruitment Agency" ) echo "selected";?> value="Cebu Recruitment Agency">Cebu Recruitment Agency</option>
									<option <?php if($_POST["source"] == "Friends" ) echo "selected";?> value="Friends">Friends</option>									
									<option <?php if($_POST["source"] == "Facebook Page of Tate Publishing" ) echo "selected";?> value="Facebook Page of Tate Publishing">Facebook Page of Tate Publishing</option>
									<option <?php if($_POST["source"] == "Job Fair" ) echo "selected";?> value="Job Fair">Job Fair</option>
									<option <?php if($_POST["source"] == "Jobstreet" ) echo "selected";?> value="Job Fair">Jobstreet</option>
									<option <?php if($_POST["source"] == "Mynimo" ) echo "selected";?> value="Mynimo">Mynimo</option>
									<!--<option <?php //if($_POST["source"] == "Online Portals" ) echo "selected";?> value="Online Portals">Online Portals</option>-->
									<option <?php if($_POST["source"] == "Orient Express" ) echo "selected";?> value="Orient Express">Orient Express</option>
									<option <?php if($_POST["source"] == "Referred by a Tate Employee" || isset($_GET['refername'])) echo "selected";?> value="Referred by a Tate Employee">Referred by a Tate Employee</option>									
									<option <?php if($_POST["source"] == "Walk In" ) echo "selected";?> value="Walk In">Walk In</option>	
									<option <?php if($_POST["source"] == "Other" ) echo "selected";?> value="Other">Other</option>
								</select>
							</div>
						</div>
				
						<?php 
							// echo $_POST['source_field'];
							if(!empty($_POST['source_field']) && !isset($_GET['refername']))
								$style = 'style="display:block;"';
							else
								$style = 'style="display:none;"';
							
							if(empty($_POST['source_field']) && isset($_GET['refername']))
								$_POST['source_field'] = $_GET['refername'];
							
							$form->text("source_field",$_POST['source_field'],'class="form-control" '.$style.' id="source_field"',"","");
						?>
                    	<?php $form->textarea("link",$_POST['link'],'class="form-control" rows="2"',"Portfolio Link(s)");?>
                    	<?php $form->text("expected_salary",$_POST['expected_salary'],'class="form-control"',"Expected Salary","in PHP");?>
                    	<hr/>
                    	<?php $form->text("last_employer",$_POST['last_employer'],'class="form-control"',"Last Employer");?>
                    	<?php $form->text("employment_period",$_POST['employment_period'],'class="form-control"',"Employment Period from your Last Employer");?>
                    	<?php $form->textarea("text_resume",$_POST['text_resume'],'class="editable form-control" rows="21"',"Text Resume","",TRUE);?>
                    	<div class="clear"></div>
                    	<?php $random = isset($_SESSION['uploads'])?$_SESSION['uploads']:md5(time()); $form->hidden("uploads",$random);?>
                    	<?php //$form->google_recaptcha(); ?>
			<?php $recaptcha->display('style="margin-left: 187px;"'); ?>
                    	<?php $form->button("submit","Submit","id='submit_button' class='btn btn-primary' style='display:none;'");?>
                		<hr/>
                </fieldset>
              <?php $form->formEnd();?>
              <div class="form-head-title">
              <?php 
              				$_SESSION['uploads'] = $random;
						    $dir = "uploads/resumes/$random/";
						    $uploader = new uploader($dir, "Upload file resume...",FALSE,FALSE);
						    $uploader->set_num_file(1);
						    $uploader->uploader_html();
              ?>
              <a id='submit_decoy' class='btn btn-primary'><b style='font-size: 1.5em;'>Submit</b></a>
              </div>
              <?php endif;?>
            </div>
		<!-- End Well -->
      </div>
      <script>
	
		function onchangeSource(value) {
			$('#source_field').val('');
			if(value == "Referred by a Tate Employee"){								
				document.getElementById("source_field").style.display= "block";
				document.getElementById("source_field").placeholder= "Enter name of Tate Employee here";			
			}
			
			else if(value == "Online Portals"){								
				document.getElementById("source_field").style.display= "block";
				document.getElementById("source_field").placeholder= "What Online Portals?";			
			}
			else if(value == "Other"){								
				document.getElementById("source_field").style.display= "block";
				document.getElementById("source_field").placeholder= "Please list down here.";			
			}
			else {
				document.getElementById("source_field").style.display= "none";
			}
			
		}
	  
	  
      	$('#submit_decoy').click(function(){
			$('#submit_button').trigger('click');
        });
		
		function showOpos(id){
			$('#oDiv_'+id+' .oposDivDesc').toggle();
		}
      </script>
	  <!-- Date time picker -->
		<link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css"/ >
		<script src="js/jquery.datetimepicker.js"></script>
		<!-- /Date time picker -->
<?php 
	require 'includes/footer.php';
?>
>>>>>>> 340dee2471200d1f63ea5511cfb770288537e5ea
