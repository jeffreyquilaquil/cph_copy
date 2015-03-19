<h2>My Notifications</h2>
<hr/>

<?php
if(count($row)==0){
	echo 'No notifications.';
}else{
foreach($row AS $r):
?>
	<div class="notifdiv" id="n<?= $r->notifID ?>" style="background-color:#ddd">
	<?php
		if($r->nName!='') echo '<b>'.$r->nName.'</b> ';
		else echo '<b>CareerPH</b> ';
		
		$note = $r->ntexts;
		/* $note = str_replace($this->user->name, 'you', $note);
		$note = ucfirst($note); */
		
		echo '['.date('d M Y h:i a', strtotime($r->dateissued)).'] <br/>'.$note;
	?>
		<br/><br/>
		<input type="button" value="Acknowledge" onClick="removeNotif('<?= $r->notifID ?>');"/>
	</div>
<?php
endforeach;
}
?>

<script type="text/javascript">
	function removeNotif(id){
		$('#n'+id).hide('slow');		
		$.post('<?= $this->config->item('career_url').$_SERVER['REQUEST_URI'] ?>',{notifID:id}, function(){
			bisan = $(window.parent.document).find("#headnotification").html();
			pa = bisan-1;
			if(pa==0) $(window.parent.document).find("#headnotification").hide();
			else $(window.parent.document).find("#headnotification").html(pa);
		});
	}
</script>
