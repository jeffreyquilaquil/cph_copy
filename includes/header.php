<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="">

    <title><?php echo TITLE;?></title>

    <!-- Bootstrap core CSS -->
    <link href="css/yeti.bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/jumbotron.css" rel="stylesheet">   
    
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="css/colorbox.css" />
	 <link href="css/custom.css" rel="stylesheet">
    
    <!-- Javascript -->
    <script src="js/jquery.js"></script>

	<!-- Jquery UI -->
    <script src="js/jquery-ui-1.10.3.custom.min.js"></script>
    <!-- Date time picker -->
	<link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css"/ >
	<script src="js/jquery.datetimepicker.js"></script>
	<script src="js/jquery.colorbox.js"></script>
	<!-- /Date time picker -->
	
	<script src='https://www.google.com/recaptcha/api.js'></script>

  </head>

  <body>

    <div class="navbar navbar-inverse navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" style="font-weight: bold;" href="<?php echo HOME_URL?>"><?php echo TITLE?></a>
        </div>
        <div class="navbar-collapse collapse" style="margin-left: 21px;">
        <ul class="nav navbar-nav">
            <li><a href="https://tatepublishing.com">Home</a></li>
        </ul>
        <?php if(in_array($_SESSION['u'],$authorized)):?>
          <ul class="nav navbar-nav">
            <!--<li><a href="applicants.php">Applicants</a></li>-->
            <?php if(is_admin()){?>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="reports.php">Reports</a></li>
			<?php }?>
            <li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color:white;">Recruitment <b class="caret"></b></a>
				<ul class="dropdown-menu">					
					<li><a href="recruitment-manager.php">Recruitment Manager</a></li>
				<?php if(is_admin()){?>
					<li><a href="recruitment-interface.php">Job Requisitions</a></li>
				<?php }?>
				</ul>
			</li>
           
          </ul>
			<?php
				$nameR = $ptDb->selectSingleQueryArray("staff", array("username","sFirst","sLast"), "username='{$_SESSION['u']}'");				
			?>
          <ul class="nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color:white;"><?php echo $nameR['sFirst']." ".$nameR['sLast'] ?> <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="login.php?logout=1">Logout</a></li>
              </ul>
            </li>
          </ul>
         <?php endif;?>
        </div><!--/.navbar-collapse -->
      </div>
    </div>
    <?php if($current_page === "index" || empty($current_page) ) :?>
    <div class='container'>
    	<a href="http://www.facebook.com/sharer.php?u=http://careerph.tatepublishing.net"><img src='img/footerfacebookicon.png'/></a>
    	<a href="https://twitter.com/share?url=http://careerph.tatepublishing.net"><img src='img/footertwittericon.png'/></a>
    	<a href="mailto:?subject=Tate+Publishing+Philippines+Application+Portal&body=I+found+this+Application+Portal+Site+from+Tate+Publishing+Philippines+that+I+thought+you%27d+be+interested+in.http://careerph.tatepublishing.net"><img src='img/shareemail.png'/></a>
    </div>
    <?php endif;?>
    <div class="jumbotron">
