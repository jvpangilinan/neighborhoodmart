<?php // TRIP SECTION: my lines, checkout breakdown, fund button, substitution alerts, pickup, dispute. ?>
<?php if(!$me) return;
$lines=rows("SELECT l.*,i.name,i.packaging FROM order_lines l JOIN items i ON i.id=l.item_id WHERE l.trip_id=? AND l.user_id=? ORDER BY l.id",'ii',$id,$me['id']);
if(!$lines) return; ?>
<div class="card"><h3>📦 My order</h3>
<?php foreach($lines as $l) if($l['shop']=='needs_ok'&&$l['status']=='matched'&&$t['status']=='shopping') echo '<div class="warn"><b>Approval needed:</b> '.h($l['alert_note']).'<div class="actions">'
 .form('answer_alert',hid('id',$l['id']).hid('ok','1')).'<button class="sm" data-tour="approve-alert">Approve</button></form>'.form('answer_alert',hid('id',$l['id']).hid('ok','0'),'data-confirm="Decline this? The item will not be bought and you will get that money back."').'<button class="sm danger">Decline</button></form></div></div>'; ?>
<div class="x"><table><tr><th>Item</th><th class="n">How many</th><th class="n adv">Final</th><th class="n adv">Price each</th><th>Status</th><th class="adv">At the store</th><th></th></tr>
<?php foreach($lines as $l) echo '<tr><td>'.h($l['name']).' <small>'.h($l['packaging']).'</small>'.($l['allow_sub']?'':'<br><small>no substitutes</small>').'</td><td class="n">'.$l['qty'].'</td><td class="n adv">'.($l['final_qty']??'-').'</td><td class="n adv">'.peso($l['est_price']).'</td><td>'.tag($l['status']).($l['reason']?'<br><small>'.h($l['reason']).'</small>':'').'</td>'
 .'<td class="adv">'.($l['status']=='matched'&&$l['shop']!='todo'?tag($l['shop']).($l['actual_price']!==null?' <small>'.peso($l['actual_price']).($l['bought_brand']?' · '.h($l['bought_brand']):'').'</small>':''):'-').'</td>'
 .'<td>'.($l['status']=='matched'&&$t['status']=='open'?form('cancel_line',hid('id',$l['id']),'data-confirm="Remove '.h($l['name']).' from your order?"').'<button class="sm s">Remove</button></form>':'').'</td></tr>'; ?>
</table></div>
<?php if($mem&&in_array($mem['status'],['requesting','unpaid','funded'])): ?>
<h4>Checkout breakdown <?=$mem['status']=='requesting'?'<small>(estimate, final after the window closes)</small>':''?></h4>
<div class="breakdown" data-tour="breakdown"><span>Item price share</span><span><?=peso($mem['est_items'])?></span>
<span>Safety extra, in case prices go up <small>(unused part comes back)</small></span><span><?=peso($mem['buffer'])?></span>
<span>Your share of gas &amp; toll</span><span><?=peso($mem['est_expense'])?></span>
<span>Traveler Effort Fee</span><span><?=peso($mem['est_effort'])?></span>
<span class="tot">You pay</span><span class="tot"><?=peso($mem['status']=='funded'?$mem['paid']:$mem['due'])?></span></div>
<?=savings_line(member_savings($id,$me['id']))?>
<?php if($mem['status']=='unpaid'): $bal=wallet_balance($me['id']); ?>
<div class="actions"><?=form('fund',hid('id',$id),'data-confirm="Pay '.peso($mem['due']).' from your wallet for this trip? After paying you can no longer cancel."')?><button data-tour="pay-trip" <?=$bal+0.001<$mem['due']?'disabled':''?>>Pay <?=peso($mem['due'])?> from my wallet</button></form>
<?php if($bal+0.001<$mem['due']) echo '<a class="btn ghost" href="?p=wallet">Top up '.peso($mem['due']-$bal).' first</a>'; ?></div>
<?php elseif($mem['status']=='funded'): ?><p><?=tag('funded')?> <small>Your money is safely held. You can't cancel anymore because the traveler is counting on it.</small></p><?php endif; ?>
<?php endif; ?>
<?php if($mem&&$mem['status']=='settled'): ?>
<h4>Final share</h4><div class="breakdown"><span>Items you got</span><span><?=peso($mem['item_cost'])?></span><span>Your share of gas &amp; toll</span><span><?=peso($mem['expense_share'])?></span><span>Traveler Effort Fee</span><span><?=peso($mem['effort_fee'])?></span>
<span class="tot">Final share</span><span class="tot"><?=peso($mem['share'])?></span><span>Paid upfront</span><span><?=peso($mem['paid'])?></span><span class="tot"><?=$mem['balance']>=0?'Refunded to wallet':'Collected from wallet'?></span><span class="tot"><?=peso(abs($mem['balance']))?></span></div>
<?=savings_line(member_savings($id,$me['id']))?>
<p>Pickup: <?=tag($mem['pickup'])?> <?php if($mem['pickup']=='waiting') echo '<small>Collect within '.PICKUP_HOURS.' hours of '.when($t['delivered_at']).', or your order is forfeited to the traveler (buffer refunds are kept).</small>'; ?></p>
<?php if($mem['pickup']=='waiting') echo '<div class="actions">'.form('collect',hid('id',$id),'data-confirm="Did you already get your items from the traveler?"').'<button data-tour="collect">I collected my items</button></form></div>'; ?>
<?php endif; ?>
<details style="margin-top:16px"><summary><small>Disagree with something? Send a dispute to the HOA</small></summary>
<?=form('dispute',hid('id',$id))?><div class="f" style="margin-top:10px"><textarea name="msg" required placeholder="Explain the issue"></textarea><button class="s">Send dispute</button></div></form></details></div>
