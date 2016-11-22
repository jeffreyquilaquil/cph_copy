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

				//$img = '/'.UPLOAD_DIR.$m['username'].'/'.$m['username'].'.jpg';	
				$img = $this->config->base_url().'attachment.php?u='.urlencode($this->textM->encryptText('staffs/'.$m['username'])).'&f='.urlencode($this->textM->encryptText($m['username'].'.jpg'));

				if($m['from']=='careerPH' && !file_exists($img) || $m['from']=='pt'){
					if($m['from']=='pt'){
						$img = 'http://staffthumbnails.s3.amazonaws.com/'.$m['username'].'.jpg';
					}else{
						$img = $this->config->base_url().'css/images/logo.png';	
					}					
                }	
                echo '<td class="nTD" width="70px">';
                if( $m['ntype'] != 6 ){				
                    echo '<img src="'.$img.'" width="60px"/>';
                }
                 echo '</td>';
				
				$note = $m['note'];
				
                if($m['from']=='careerPH'){
                    echo '<td>';
                    if( $m['ntype'] != 6 ){
                        echo '<b>'.(($m['name']=='')?'CareerPH':$m['name']).'</b>'; 
                    } 
                    echo '('.date('M d y h:i a', strtotime($m['timestamp'])).')';
                
                    echo'<br/><br/>'.$note.'<br/><br/></td>';
                } else {
                    echo '<td>'.$note.'</td>';	
                }
				echo '</tr>';
			}
		}
		
	}
	echo '</table>';
?>
