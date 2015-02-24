<?php
require 'config.php';

if(!isset($_SESSION['u']) || !in_array($_SESSION['u'], $authorized)){
	header("Location: login.php");
	exit();
} 

if(isset($_GET['pos']) && $_GET['pos']=='change'){
	$up = array(
		'position' => $_POST['position'],
		'position1' => 0,
		'position2' => 0,
		'isNew' => 1		
	);
	$db->updateQuery('applicants',$up, 'id="'.$_GET['id'].'"');
}

if(isset($_POST['edit_entry'])){
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
		if(is_good_email($_POST['email'], $_GET['id']) !== TRUE){
			$error['email'] = is_good_email($_POST['email']);
		}
	}
	if(empty($_POST['text_resume'])){
		$error['text_resume'] = "Text Resume empty.";
	}
	if(sizeof($error)==0){
		unset($_POST['edit_entry']);
		$db->updateQuery("applicants", $_POST, "id=".$_POST['id']);
		header("Location: view_info.php?id={$_POST['id']}");
		exit();
	}
}

if(isset($_POST['submit']) && isset($_GET['id'])){
	if(!empty($_POST['status'])){
		$current_stat = $db->selectSingleQuery("applicants", "status", "id=".$_GET['id']);
		if( $_POST['status'] == 2 && $current_stat!=2 ){
			header('Location:hired.php?id='.$_GET['id']);
			exit;
		}else{			
			if($current_stat !== $_POST['status'] && $current_stat != 2){
				$db->updateQuery("applicants", array('status'=>$_POST['status']), "id=".$_GET['id']);
				$old_status = $db->selectSingleQuery("applicant_status", "status", "id=$current_stat");
				$new_status = $db->selectSingleQuery("applicant_status", "status", "id={$_POST['status']}");
				$_POST['remarks'] = "<span class='success'>Changed status from <b>$old_status</b> to <b>$new_status</b></span> <br/>".$_POST['remarks'];
				$update[] = "Applicant Status updated.";
			}
		}
	
		
	}
	if(!empty($_POST['remarks'])){
		$db->insertQuery("applicant_feedbacks", array('applicant_id'=>$_GET['id'], 'pt_username'=>$_SESSION['u'], 'remarks'=>$_POST['remarks'], 'date_created'=>"NOW()") );
		$update[] = "Remarks saved.";
	}
	
}

$result = $db->selectSingleQueryArray("applicants a", "a.*, a_s.status AS status, a.status AS status_id,a.uploads","a.id={$_GET['id']}","LEFT JOIN applicant_status a_s ON a_s.id=a.status" );

require 'includes/header.php';
if(!is_array($result) || sizeof($result) == 0):
	echo "<p class='error'>Applicant info not found.</p>";
else :
	if(!isset($_POST['edit_entry'])){
		$_POST = $result;
	}
?>
<style type="text/css">
.highlight {
    background-color: #fff34d;
    -moz-border-radius: 5px; /* FF1+ */
    -webkit-border-radius: 5px; /* Saf3-4 */
    border-radius: 5px; /* Opera 10.5, IE 9, Saf5, Chrome */
    -moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.7); /* FF3.5+ */
    -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.7); /* Saf3.0+, Chrome */
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.7); /* Opera 10.5+, IE 9.0 */
}

.highlight {
    padding:1px 4px;
    margin:0 -4px;
}
</style>
<div class='container'>
			<fieldset>
                  <legend><?php echo $_POST['fname']." ".$_POST['mname']." ".$_POST['lname']." ".$_POST['suffix']." ({$_POST['status']})"; echo get_error();?></legend>
                    	<!-- Checklist -->
						<?php 
                    		$checklists = $db->selectQuery("applicant_checkboxes", "id,item", "active ORDER BY item ASC");
                    		if(is_array($checklists)){
                    			$arr_checks = explode(",", $_POST['checkbox']);
                    			$read_only = is_admin()?"":"disabled";
                    			echo "<div id='load_checkboxes'></div>";
                    			echo "<p>";
                    			foreach($checklists AS $k => $v){
                    				$checked = in_array($v['id'], $arr_checks)? "checked":"";
                    				echo "<input $read_only $checked id='check_{$v['id']}' type='checkbox'> {$v['item']} <br/>";
                    				if(is_admin()):
                    				?>
	                    			<script>
										$("#check_<?php echo $v['id'];?>").change(function(){
											<?php echo get_ajax_loader("#load_checkboxes");?>
											var act = "";
											if($(this).is(":checked")){
												act = "check";
											}
											else{
												act = "uncheck";
											}
											$.ajax({
									  			type: "POST",
									  			url: "ajax.php",
									  			data: { action: act, id: "<?php echo $v['id'];?>", aid:"<?php echo $_POST['id'];?>" }
											}).done(function( result ) {
												$('#load_checkboxes').empty();
									  		});
										});
									</script>
	                    			<?php 
	                    			endif;
                    			}
                    			echo "</p>";
                    			echo "<hr/>";
                    		}
                    	?>
                    	<!-- Comments/Status -->
                    	<?php $form->setEditable(FALSE);?>
                    	<?php 
                    	if(is_admin()){
                    		$form->setEditable(TRUE);
                    		$status = $db->selectQuery("applicant_status", "id,status", "active=1 ORDER BY status ASC");
                    		$stat = array();
                    		if(is_array($status)){
                    			foreach($status AS $v){
                    				$stat[$v['id']] = $v['status'];	
                    			}
                    		}
                    	}
                    	else{
                    		$status = $db->selectQuery("applicant_status", "id,status", "id={$_POST['status_id']} AND active=1 ORDER BY status ASC");
                    		$stat = array();
                    		if(is_array($status)){
                    			foreach($status AS $v){
                    				$stat[$v['id']] = $v['status'];	
                    			}
                    		}
                    	}
                    	$form->formStart();
                    	$form->select("status",$_POST['status_id'],$stat,'class="form-control" onChange="checkStatus()" id="status"',"Change Status");
                    	$form->setEditable(TRUE);
                    	$form->textarea("remarks","",'class="editable form-control" id="remarks" rows="6"',"Add Remarks");
                    	$form->button("submit","Submit","class='btn btn-primary'");
                    	$form->formEnd();
                    	echo "<p style='border-bottom: 1px solid #999;'>&nbsp;</p>";
                    	?>
						<?php $form->setEditable(FALSE);?>
                    	<?php
                    		$remarks = $db->selectQuery("applicant_feedbacks", "*", "applicant_id = ".$_GET['id']." ORDER BY timestamp DESC");
                    		if(is_array($remarks)){ 
                    			foreach($remarks AS $k => $v){
                    				$form->textarea("remarks",$v['remarks']."<p><span style='font-size: 0.8em'>By: {$v['pt_username']} | {$v['date_created']}</span></p>",'class="editable form-control"',"Remarks");
                    				echo "<p style='border-bottom: 1px solid #999;'>&nbsp;</p>";
                    			}
                    		}
                    	?> 	
                    	<div class="clear"></div>
                    	<hr/>
                    	<?php 
                    		if(is_admin() && empty($_GET['edit'])){
                    			echo "<a href='view_info.php?id={$_GET['id']}&edit=1#edit'><u>Edit</u></a>";
                    		}
                    	?>
						<?php $form->setEditable(FALSE);?>
						<?php 
							if(is_admin() && $_GET['edit'] == '1'){
                    			echo "<a href='view_info.php?id={$_GET['id']}'><u>Cancel Edit</u></a>";
                    			$form->setEditable(TRUE);
                    			$form->formStart();
                    			$form->hidden("id",$_POST['id']);
                    		}
						?>
						<a name='edit'></a>
						<?php $form->text("lname",$_POST['lname'],'class="form-control"',"Last Name","");?>
                    	<?php $form->text("fname",$_POST['fname'],'class="form-control"',"First Name","");?>
                    	<?php $form->text("mname",$_POST['mname'],'class="form-control"',"Middle Name");?>
                    	<?php $form->text("suffix",$_POST['suffix'],'class="form-control"',"Suffix");?>
                    	<?php $form->date("bdate",$_POST['bdate'],'class="form-control"',"Birthdate","");?>
                    	<?php $form->textarea("address",$_POST['address'],'class="form-control"',"Address","");?>
                    	<?php $form->text("mnumber",$_POST['mnumber'],'class="form-control"',"Mobile Number","Mobile #");?>
                    	<?php $form->email("email",$_POST['email'],'class="form-control"',"Email Address","");?>
						<?php
							$nPos = 0;
							if($_POST['isNew']==1)
								$nPos = $_POST['position'];
							
							$nPosQ = $db->selectQuery("newPositions", "posID,title,dept", "active=1 ORDER BY dept, title ASC");
							$dept = '';
							$newPosArray = '<option value=""></option>';
							foreach( $nPosQ AS $p ){
								if($p['dept'] != $dept){
									if($dept!='')
										$newPosArray .= '</optgroup>';
										
									$newPosArray .= '<optgroup label="'.$p['dept'].'">';
									$dept = $p['dept'];
								}	
								$selected = '';
								if($nPos == $p['posID']) $selected = ' selected ';
								$newPosArray .= '<option value="'.$p['posID'].'" '.$selected.'>'.$p['title'].'</option>';
							}
							
						if($_POST['isNew']==0)
						{
						?>						
						
						<div>
							Old Positions&nbsp;&nbsp;&nbsp;<a id="editpos"><u>edit to new position</u></a>
						</div>
						<div id="newPosDiv" class="form-group" style="margin-bottom:10px; display:none;">
							<label class="col-lg-2 control-label" for="select"><b>New Position</b></label>
							<div class="col-lg-10" style="margin-bottom:20px;">
								<select class="form-control" placeholder="Position applied" name="position" id="newPosition" onChange="changePosition()">
									<?php echo $newPosArray; ?>
								</select>
							</div>
						</div>
                    	<?php 
                    		$pos = $db->selectSingleQuery("positions", "title", "id=".$_POST['position']);
                    		$pos1 = $db->selectSingleQuery("positions", "title", "id=".$_POST['position1']);
                    		$pos2 = $db->selectSingleQuery("positions", "title", "id=".$_POST['position2']);
                    		/* if(is_admin() && $_GET['edit'] == '1'){
	                    		$pos = $db->selectQuery("positions", "id,title", "active=1 ORDER BY title ASC");
	                    		$positions = array(" ");
	                    		if(is_array($pos)){
	                    			foreach($pos AS $v){
	                    				$positions[$v['id']] = $v['title'];	
	                    			}
	                    		}
	                    		$pos = $positions;
	                    		$pos1 = $positions;
	                    		$pos2 = $positions;
                    		} */
							
							$form->select("position",$_POST['position'],$pos,'class="form-control" readonly=""',"Position applied(Primary)","");
							$form->select("position1",$_POST['position1'],$pos1,'class="form-control" readonly=""',"Position applied","");
							$form->select("position2",$_POST['position2'],$pos2,'class="form-control" readonly=""',"Position applied","");
						}else{
							$pos = $db->selectSingleQuery("newPositions", "title", "posID=".$_POST['position']);
							if(is_admin() && $_GET['edit'] == '1'){ ?>
								<div class="form-group">
									<label class="col-lg-2 control-label" for="select">Position</label>
									<div class="col-lg-10">
										<select class="form-control" placeholder="Position" name="position">
											<?php echo $newPosArray; ?>
										</select>
									</div>
								</div>
						<?php
							}else{
								$form->select("position",$_POST['position'],$pos,'class="form-control"',"Position","");
							}
						}
                    	?>
                    	<?php $form->text("source",$_POST['source'],'class="form-control"',"Where did you hear about us");?>						
						<?php if(!empty($_POST['source_field'])) $form->text("source_field",$_POST['source_field'],'class="form-control"',""); ?>						
                    	<?php 
							if(!empty($_POST['link']))
								$label = $_POST['link'];
							else
								$label = "No Portfolio Link(s) available";
							$form->textarea("link",$_POST['link'],'class="form-control" rows="2"',"Portfolio Link(s)",$label);?>
                    	<?php $form->text("expected_salary",$_POST['expected_salary'],'class="form-control"',"Expected Salary","in PHP");?>
                    	<hr/>
                    	<?php $form->text("last_employer",$_POST['last_employer'],'class="form-control"',"Last Employer");?>
                    	<?php $form->text("employment_period",$_POST['employment_period'],'class="form-control"',"Employment Period");?>
						<div class="clear"></div>
						<div id='search_tags'>
							<a name="tags"></a>
                    	<?php $form->textarea("text_resume",nl2br($_POST['text_resume']),'class="editable form-control" rows="21" ',"Text Resume","");?>
                    	</div>
						<?php 
                    		if(is_admin() && $_GET['edit'] == '1'){
                    			$form->button("edit_entry","Edit","class='btn btn-primary'");
                    			$form->formEnd();
                    		}
                    	?>
                    	
                    	<div class='form-head-title' style='margin-top:21px;'><p><strong>Attachment: </strong></p><?php echo get_uploaded_file_icon("uploads/resumes/{$_POST['uploads']}/","large");?></div>
                    	<div class="clear"></div>
                    	<hr/>
                    	<h3>HR Uploads (<?php echo trim($_POST['suffix']." ".$_POST['fname']." ".$_POST['mname']." ".$_POST['lname']);?>)</h3>
                    	<div class='container'>
                    	<a name='attach_file'></a>
                    	<?php 
						    $dir = "uploads/applicants/{$_GET['id']}/";
						    $uploader = new uploader($dir, "Choose File...","Start Upload","Cancel Upload");
						    $uploader->set_multiple(TRUE);
						    $uploader->uploader_html();
              			?>
                    	</div>
			</fieldset>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		var tag = window.location.search.substring(1);
		tag = tag.split('tag=');
		if( typeof tag[1] != 'undefined' ){
			$('#search_tags').highlight( tag[1] );
		}	

		$('#editpos').click(function(){
			$('#newPosDiv').css('display','block');
		});
	});
	
	function checkStatus(t){
		if($('#status').val() == 2){
			$("#remarks-aloha").parent().parent().css('display', 'none');
		}else{
			$("#remarks-aloha").parent().parent().css('display', '');
		}
	}
	
	function changePosition(){
		$.post("view_info.php?id=<?= $_GET['id'] ?>&pos=change",
			{
				position:$('#newPosition').val()
			},
			function(){ 
				window.location.reload();
			}
		);
	}
</script>

<script src="js/highlight.js"></script>
<!-- load the Aloha Editor CSS styles -->
<link rel="stylesheet" href="css/aloha/css/aloha.css"/>
<!-- Aloha WYSWYG from editor.php -->
<!-- load the jQuery and require.js libraries -->
<script>
	Aloha = {};
	Aloha.settings = { sidebar: { disabled: true } };
</script>
<script type="text/javascript" src="js/aloha/require.js"></script>
<!-- load the Aloha Editor core and some plugins -->
<script src="js/aloha/aloha.js"
	data-aloha-plugins="common/ui,
		common/format,
		common/list,
		common/link,
		common/highlighteditables">
</script>

<!-- make all elements with class="editable" editable with Aloha Editor -->
<script type="text/javascript">
	Aloha.ready( function() {
		var $ = Aloha.jQuery;
			$('.editable').aloha();
		});
</script>
<!-- End Aloha WYSWYG from editor.php -->

<?php 
endif;
require 'includes/footer.php';
?>