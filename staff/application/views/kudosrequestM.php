<h2>Kudos Bonus Requests</h2>
<hr/>
<style>
	.toggleKudos, .toggleKudosR{
		color:#ffa500;
		text-decoration: underline;
		cursor: pointer;
	}
	.tooltipR{
		position: relative;
    	display: inline-block;
	}
	.kudosDiv, .kudosRDiv{
		position: absolute;
		background: white;
		border: solid 1px;
		padding: 5px;
		border-radius: 3px;
		display: none;
		width: 300px;
		bottom: 100%;
	}
</style>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">Pending Immediate Supervisor's Evaluation <?php echo '('. $cnt_is .')'; ?></li>
	<?php if($this->access->accessFullHR){ ?>
	<li class="tab-link" data-tab="tab-2">Pending HR's Evaluation <?php echo '('. $cnt_hr .')'; ?></li>
	<?php }if($this->access->accessFullFinance || $this->access->accessFullHR){ ?>
	<li class="tab-link" data-tab="tab-3">Pending Accounting's Approval <?php echo '('. $cnt_acc .')'; ?></li>
	<?php }?>
	<li class="tab-link" data-tab="tab-4">All Requests <?php echo '('. $cnt_all .')'; ?></li>
</ul>
<div id="tab-1"class="tab-content current">
	<h3>Pending Immediate Supervisor's Evaluation (<?=$cnt_is?>)</h3>
	<?php echo $this->textM->kudosRequestTableDisplay($results[1], TRUE, TRUE); ?>
</div>
<div id="tab-2"class="tab-content">
	<h3>Pending HR's Evaluation (<?=$cnt_hr?>)</h3>
	<?php echo $this->textM->kudosRequestTableDisplay($results[2]); ?>
</div>
<div id="tab-3"class="tab-content">
	<h3>Pending Accounting's Approval (<?=$cnt_acc?>)</h3>
	<?php echo $this->textM->kudosRequestTableDisplay($results[3]); ?>
</div>
<div id="tab-4"class="tab-content">
	<h3>All Requests(<?=$cnt_all?>)</h3>
	<div class="cpointer" onClick="showTbl(1, this)"><h3>Pending Immediate Supervisor's Evaluation (<?=$cnt_is?>) <a class="fs11px">[show]</a></h3></div>
	<?php echo $this->textM->kudosRequestTableDisplay($results[1], TRUE, TRUE, 1, FALSE); ?><br/></br>

	<div class="cpointer" onClick="showTbl(2, this)"><h3>Pending HR's Evaluation (<?=$cnt_hr?>) <a class="fs11px">[show]</a></h3></div>
	<?php echo $this->textM->kudosRequestTableDisplay($results[2], TRUE, FALSE, 2, FALSE); ?><br/></br>

	<div class="cpointer" onClick="showTbl(3, this)"><h3>Pending Accounting's Approval (<?=$cnt_acc?>) <a class="fs11px">[show]</a></h3></div>
	<?php echo $this->textM->kudosRequestTableDisplay($results[3], TRUE, FALSE, 3, FALSE); ?><br/></br>

	<div class="cpointer" onClick="showTbl(4, this)"><h3>Approved (<?=$cnt_approved?>) <a class="fs11px">[show]</a></h3></div>
	<?php echo $this->textM->kudosRequestTableDisplay($results[4], TRUE, FALSE, 4, FALSE, 'approved'); ?><br/></br>

	<div class="cpointer" onClick="showTbl(5, this)"><h3>Disapproved (<?=$cnt_disapproved?>) <a class="fs11px">[show]</a></h3></div>
	<?php echo $this->textM->kudosRequestTableDisplay($results[5], TRUE, FALSE, 5, FALSE, 'disapproved'); ?><br/></br>
</div>

<script type="text/javascript">

	function showTbl(tbl, p){
		$('.table'+tbl).toggleClass('hidden');
		$(p).find('a').text($(p).find('a').text() == '[show]' ? '[hide]' : '[show]'); 
	}

	$(document).ready(function(){
		$('.toggleKudos, .toggleKudosR').click(function(){
			var toggle = $(this).data('toggle');
			// var tClass = $(this).data('tClass');

			// if( tClass == 'reason')
			// 	$()
			// else
				$('#'+toggle).toggle();
		});
	});
</script>


