<?php
require 'config.php';
require 'includes/header.php';
if(isset($_SESSION['u'])){
        $LOGIN = TRUE;
}

?>
<style>
	p{
		margin-top: 21px;
		font-weight: bold;
	}
</style>
<main>
<div class='container'>
<article>
	<header><h3>To access exams online:</h3></header>
	<p>Go to <a href='http://training.tatepublishing.net' target='_blank'>http://training.tatepublishing.net</a> and click on "Log In" in the right corner:</p>
	<img src='training/images/training1.jpg'/>
	<p>Returning users should log in. New users should create an account by following the on-screen instructions. Email verification is required for new accounts. Once confirm, repeat this step to log in.</p>
	<img src='training/images/training2.jpg'/>	
	<p>Once logged in, candidates should scroll down the home page and click "Pre-Employment Exams"</p>
	<img src='training/images/training3.jpg'/>
	<p>Once the page loads, locate the appropriate exam:</p>
	<img src='training/images/training4.jpg'/>
	<p>Exam Protected Password</p>
	<?php if($LOGIN):?>
	<p style='color: #FF0000;'>Exams may be password protected (the IT exam is). For exams that are password protected, the user will be directed to a screen that asks them to enter the password and confirm enrollment.</p>
	<p style='color: #FF0000;'>Password = Lokvos37</p>
	<?php endif; ?>
	<img src='training/images/training5.jpg'/>
	<p>Once successful password</p>
	<?php if($LOGIN): ?>	
	<p style='color: #FF0000;'>Once they have entered the correct key, they will be taken to the exam screen:</p>
	<?php endif;?>
	<img src='training/images/training6.jpg'/>
	<p>Click the "Attemp quiz now" button to start the exam and follow the on screen instructions. The exam will load in a separate window. Once complete, log out of your account and you're done!</p>
	<?php if($LOGIN):?>
	<p style='color: #FF0000;'>Scores from the IT exam are displayed on completion and can be printed, and they are also logged in the training system.</p>
	<p style='color: #FF0000;'>This exam is 30 multiple-choice questions. At this point, we're not going to implement a second exam. We'll assess fit for a specific role in the
interview phase rather than a formal proctored exam.</p>
	<?php endif;?>
</article>
</div>
</main>
<?php
require 'includes/footer.php';
?>
