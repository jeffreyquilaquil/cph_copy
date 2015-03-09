<?php
	echo '<table class="tableInfo">';
	if(count($myNotes)==0){
		echo '<tr><td>No notes.</td></tr>';
	}else{		
		for($cnt=0; $cnt<count($myNotes); $cnt++){
			$m = $myNotes[$cnt];
			/* if($m['access']=='' || $m['access']=='assoc' || 
				$m['access']=='full' || (count(array_intersect($this->myaccess,array('full','hr')))>0) ||
				$m['access']=='exec'
			){ */
				echo '<tr class="nnotes nstat_'.$m['type'].'" valign="top">';	

				$img = UPLOAD_DIR.$m['username'].'/'.$m['username'].'.jpg';	

				if($m['from']=='careerPH' && !file_exists($img) || $m['from']=='pt'){
					if($m['from']=='pt'){
						$img = 'http://staffthumbnails.s3.amazonaws.com/'.$m['username'].'.jpg';
					}else{
						$img = $this->config->base_url().'css/images/logo.png';	
					}					
				}	
				
				
				echo '<td class="nTD" width="70px"><img src="'.$img.'" width="60px"/></td>';
				
				if($m['from']=='careerPH') echo '<td><b>'.(($m['name']=='')?'CareerPH':$m['name']).'</b> ('.date('M d y h:i a', strtotime($m['timestamp'])).')<br/><br/>'.$m['note'].'<br/><br/></td>';
				else echo '<td>'.$m['note'].'</td>';	
				echo '</tr>';
			//}
		}
		
	}
	echo '</table>';
?>