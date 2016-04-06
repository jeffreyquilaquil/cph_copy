<h2>Manage Referrals</h2>
<hr/>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">Employee Referrals</li>
	<li class="tab-link" data-tab="tab-2">Top Referrers</li>
	<li class="tab-link" data-tab="tab-3">Released Mini Referral Bonus</li>
</ul>
<br/>
<div id="tab-1" class="tab-content current">
	<table class="tableInfo datatable">
		<thead>
			<tr>
				<th>Date Submitted</th>
				<th>Employee Name</th>
				<th>Referral Last Name</th>
				<th>Referral First Name</th>
				<th>Referral Email Add</th>
				<th>Referral Contact #</th>
			</tr>
		</thead>
	<?php
		foreach($queryReferrals AS $ref){
			echo '<tr>';
				echo '<td>'.date('m/d/y h:i A', strtotime($ref->dateReferred)).'</td>';
				echo '<td>'.$ref->referrer.'</td>';
				echo '<td>'.$ref->lastName.'</td>';
				echo '<td>'.$ref->firstName.'</td>';
				echo '<td>'.$ref->emails.'</td>';
				echo '<td>'.$ref->contacts.'</td>';
			echo '</tr>';
		}
	?>
	</table>
</div>

<div id="tab-2" class="tab-content">
	<table class="tableInfo datatable">
		<thead>
			<tr>
				<th>Employee Name</th>
				<th>Referral Bonus<br/><i class="fs11px weightnormal">Php 500 will be given first if bonus is more than 500</i></th>
				<th>Action</th>
			</tr>
		</thead>
	<?php
		foreach($queryTop AS $t){
			echo '<tr>';
				echo '<td>'.$t->name.'</td>';
				echo '<td>Php '.$this->textM->convertNumFormat($t->bonus).'</td>';
				echo '<td>';
					if($t->bonus>=500){
						echo '<a href="'.$this->config->base_url().'referralrelease/'.$t->empID.'/" class="iframe">Click to confirm release</a>';
					}else{
						echo '<a href="'.$this->config->base_url().'validatereferrrals/'.$t->empID.'/" class="iframe">Validate Referrals</a>';
					}
				echo '</td>';
				/* if($t->referralBonus<500){
					echo '<td>Php '.$this->textM->convertNumFormat($t->referralBonus).'</td>';
					echo '<td><a href="'.$this->config->base_url().'validatereferrrals/'.$t->empID.'/" class="iframe">Validate Referrals</a></td>';
				}else{
					$bbnou = $t->referralBonus - 500;
					echo '<td>';
					if($bbnou>0)
						echo 'Php '.$this->textM->convertNumFormat($bbnou).'<br/>';
					
					echo '<i>Pending Released of Php 500.00</i></td>';
					echo '<td><a href="'.$this->config->base_url().'referralrelease/'.$t->empID.'/'.$t->empID.'/" class="iframe">Click to confirm release</a></td>';
				} */
				
				
			echo '</tr>';
		}
	?>
	</table>
</div>

<div id="tab-3" class="tab-content">
	<table class="tableInfo datatable">
		<thead>
			<tr class="trhead">
				<th>Employee Name</th>
				<th>Referral Bonus</th>
				<th>Released Date</th>
				<th>MRB ID #</th>
			</tr>
		</thead>
	<?php
		foreach($queryMRB AS $t){
			echo '<tr>';
				echo '<td>'.$t->lname.', '.$t->fname.'</td>';
				echo '<td>Php '.$this->textM->convertNumFormat($t->releasedAmount).'</td>';
				echo '<td>'.(($t->dateReleased!='0000-00-00')?date('F d, Y', strtotime($t->dateReleased)):'<i>Pending Released</i>').'</td>';
				echo '<td><a href="'.$this->config->base_url().'referrralreleasedetails/'.$t->mrbID.'/" class="iframe">'.sprintf('%04d',$t->mrbID).'</a></td>';
			echo '</tr>';
		}
	?>
	</table>
</div>