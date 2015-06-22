<?php
	echo '<table class="tableInfo">';
	if(count($myNotes)==0){
		echo '<tr><td>No notes.</td></tr>';
	}else{	
		$cntCome = count($myNotes);
		for($cnt=0; $cnt<$cntCome; $cnt++){
			$m = $myNotes[$cnt];
			
			if($m['type']!=5 || ($m['type'] && $myID==$empID)){			
				if($m['exec']!=''){
					$eexplde = explode('|', $m['exec']);
					if(isset($eexplde[0])) $m['username'] = $eexplde[0];
					if(isset($eexplde[1])) $m['name'] = $eexplde[1];
				}
				
				echo '<tr class="nnotes nstat_'.$m['type'].'" valign="top">';	

				$img = '/'.UPLOAD_DIR.$m['username'].'/'.$m['username'].'.jpg';	

				if($m['from']=='careerPH' && !file_exists($img) || $m['from']=='pt'){
					if($m['from']=='pt'){
						$img = 'http://staffthumbnails.s3.amazonaws.com/'.$m['username'].'.jpg';
					}else{
						$img = $this->config->base_url().'css/images/logo.png';	
					}					
				}	
				
				
				echo '<td class="nTD" width="70px"><img src="'.$img.'" width="60px"/></td>';
				
				$note = $m['note'];
				
				if($m['from']=='careerPH') 
					echo '<td><b>'.(($m['name']=='')?'CareerPH':$m['name']).'</b> ('.date('M d y h:i a', strtotime($m['timestamp'])).')<br/><br/>'.$note.'<br/><br/></td>';
				else 
					echo '<td>'.$note.'</td>';	
				echo '</tr>';
			}
		}
		
	}
	echo '</table>';
?>