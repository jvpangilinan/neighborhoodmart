<?php // TRIP SECTION: header + progress steps. ?>
<?php $flow=['open'=>'Taking orders','funding'=>'Paying','ready'=>'All paid','locked'=>'Locked','shopping'=>'Shopping','delivered'=>'Pickup','settled'=>'Done'];
$keys=array_keys($flow); $at=array_search($t['status'],$keys); $load=trip_load($id); ?>
<div class="card"><div class="page-head"><div><h2><?=h($t['title'])?> <?=tag($t['status'])?></h2>
<p><?=h($t['sname'])?><?=$t['aname']?' · '.h($t['aname']):''?> · Traveler <b><?=h($t['tname'])?></b> · Trip date <?=h(date('M j, Y',strtotime($t['go_date'])))?></p></div>
<a class="btn ghost sm" href="index.php">← All trips</a></div>
<?php if($t['status']!='cancelled'){ echo '<ul class="steps" data-tour="steps" aria-label="Trip progress">'; foreach($keys as $k=>$s) echo '<li class="'.($k<$at?'done':($k===$at?'now':'')).'">'.($k+1).'. '.$flow[$s].'</li>'; echo '</ul>'; } ?>
<div class="grid adv" style="margin-top:14px">
<div class="kpi"><span>Requests close</span><b style="font-size:1.1rem"><?=when($t['window_closes_at'])?></b></div>
<div class="kpi"><span><?=h(VEHICLES[$t['vehicle']][0])?> load</span><b style="font-size:1.1rem"><?=$load?> / <?=$t['capacity']?> units</b><div class="bar"><i style="width:<?=min(100,round($load/max(1,$t['capacity'])*100))?>%"></i></div></div>
<div class="kpi"><span>Gas &amp; toll (shared)</span><b style="font-size:1.1rem"><?=peso($t['expenses'])?></b><small>shared by item cost</small></div>
</div>
<?php
$hints=['requester'=>['open'=>'Add the items you need below. You can cancel anytime until requests close.','funding'=>'Requests are closed. Pay your share below so the trip can go.','ready'=>'Everyone has paid. Waiting for the traveler to lock the trip.','locked'=>'The traveler is heading to the store.','shopping'=>'Shopping now. Watch for approval alerts on substitutes.','delivered'=>'Back in the village. Pick up your items and confirm below.','settled'=>'Done. Thanks for pooling!','cancelled'=>'This trip was cancelled. Any money held was refunded to your wallet.'],
 'traveler'=>['open'=>'Neighbors are adding items. Close requests when you are ready (or it closes automatically).','funding'=>'Waiting for every family to pay. You never pay for anyone.','ready'=>'100% paid. Review the list below, then Accept and Lock.','locked'=>'At the store? Start the shopping checklist.','shopping'=>'Mark every item. Then tap Done.','delivered'=>'Hand over items and mark each family.','settled'=>'Trip complete.','cancelled'=>'Cancelled.']];
if(isset($hints[$shell][$t['status']])) echo '<div class="hint">👉 '.$hints[$shell][$t['status']].'</div>';
if($t['rules_note']) echo '<p class="adv"><small>Store rules ('.h(str_replace('_',' ',$t['bulk_ok'])).'): '.h($t['rules_note']).'</small></p>'; ?></div>
