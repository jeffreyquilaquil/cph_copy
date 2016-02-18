<?php
if($this->access->accessFullHR==true OR $this->user->is_supervisor == 1){
        //if is supervisor search only your team
    if( $this->user->is_supervisor == 1 ){
            $ids = '';
            $condition = '';
            $my_staff = $this->commonM->getStaffUnder( $this->user->empID, $this->user->level );
            foreach( $my_staff as $m ){
                $ids .= $m->empID.',';
            }
            if( $ids != '' ) {
                $condition .= ' AND empID IN ('.rtrim($ids,',').')';
            }
            $allStaffs = $this->dbmodel->getQueryResults('staffs', 'empID, lname, fname, active', 'active=1'.$condition, '', 'lname');
        } else {
    		$allStaffs = $this->dbmodel->getQueryResults('staffs', 'empID, fname, lname, active');
        }
		
        echo '<div class="floatright" style="width:300px;">';
			echo $this->textM->formfield('text', '', '', 'forminput', 'Search Employee', 'id="searchTimeEmp" style="border:1px solid #800000;"');
			echo '<div id="divstaffs" style="position:absolute; width:300px; z-index:999;">';
				echo '<table id="timetable" class="hidden" style="background-color:#ccc; width:100%; border:1px solid #800000;">';
					foreach($allStaffs AS $a){
						echo '<tr class="timetabletr"><td onClick="gototimelogpage('.$a->empID.')" '.(($a->active==1)?'class="weightbold"':'').'>'.$a->fname.' '.$a->lname.'</td></tr>';
					}
				echo '</table>';
			echo '</div>';
		echo '</div>';
?>	
	<script type="text/javascript">
		$(function(){
			$("#searchTimeEmp").keyup(function(){ 
				var filter = $(this).val();
				$('#timetable').removeClass('hidden');
				
				$("#timetable tr").each(function(){ 
					if ($(this).text().search(new RegExp(filter, "i")) < 0) {
						$(this).fadeOut();				
					 } else {				
						$(this).show();
						//count++;
					}
				});
				
				if(filter=='') $('#timetable').addClass('hidden');
			});
		});
		
		function gototimelogpage(id){
			location.href='<?= $this->config->base_url().'timecard/' ?>'+id+'/timelogs/';
			displaypleasewait();
		}
	</script>
<?php
	}
?>
