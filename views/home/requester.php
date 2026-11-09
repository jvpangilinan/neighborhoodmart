<?php // HOME for Requesters: what to do now, open trips, my orders. ?>
<div class="hello"><h2>Hi, <?=h(explode(' ',$me['name'])[0])?> 👋</h2><p>Wallet credit: <b><?=peso(wallet_balance($me['id']))?></b> · <a href="?p=wallet">Top up</a><?php $ts=user_total_savings($me['id']); if($ts>0) echo ' · 💰 Saved so far vs. supermarket: <b>'.peso($ts).'</b>'; ?></p></div>
<?php
$todo=[];
foreach(rows("SELECT m.*,t.title FROM trip_members m JOIN trips t ON t.id=m.trip_id WHERE m.user_id=? AND m.status='unpaid'",'i',$me['id']) as $m)
 $todo[]=['💳','Pay your share for "'.$m['title'].'"','Requests are closed. The trip goes to the store only when every family has paid.',form('fund',hid('id',$m['trip_id']),'data-confirm="Pay '.peso($m['due']).' from your wallet for this trip? After paying you can no longer cancel."').'<button data-tour="pay">Pay '.peso($m['due']).' from wallet</button></form>'];
foreach(rows("SELECT l.*,i.name FROM order_lines l JOIN items i ON i.id=l.item_id JOIN trips t ON t.id=l.trip_id WHERE l.user_id=? AND l.shop='needs_ok' AND l.status='matched' AND t.status='shopping'",'i',$me['id']) as $l)
 $todo[]=['⚠️','Your traveler needs an answer now',$l['alert_note'],form('answer_alert',hid('id',$l['id']).hid('ok','1')).'<button class="sm" data-tour="approve-alert">Approve</button></form>'.form('answer_alert',hid('id',$l['id']).hid('ok','0'),'data-confirm="Decline this? The item will not be bought and you will get that money back."').'<button class="sm danger">Decline</button></form>'];
foreach(rows("SELECT m.*,t.title,t.delivered_at FROM trip_members m JOIN trips t ON t.id=m.trip_id WHERE m.user_id=? AND m.status='settled' AND m.pickup='waiting'",'i',$me['id']) as $m)
 $todo[]=['📦','Pick up your items from "'.$m['title'].'"','Back in the village. Final share '.peso($m['share']).', refund '.peso(max(0,$m['balance'])).'. Collect within '.PICKUP_HOURS.' hours.',form('collect',hid('id',$m['trip_id']),'data-confirm="Did you already get your items from the traveler?"').'<button data-tour="collect">I collected my items</button></form>'];
?>
<div class="card"><h3>To do</h3>
<?php if(!$todo) echo '<p class="muted">✅ Nothing needs your attention. Join a trip below.</p>'; else { echo '<div class="todo">';
 foreach($todo as [$ic,$t,$d,$act]) echo '<div class="todo-item"><span class="ic">'.$ic.'</span><div class="txt"><b>'.h($t).'</b><small>'.h($d).'</small></div><div class="inline">'.$act.'</div></div>'; echo '</div>'; } ?></div>
<div class="card"><div class="page-head"><h3 style="margin:0">Open trips you can join</h3><a href="?p=trips">See all →</a></div>
<?php $tripLimit=3; require ROOT.'/views/trip_cards.php'; ?></div>
<?php $act=rows("SELECT m.*,t.title,t.status ts FROM trip_members m JOIN trips t ON t.id=m.trip_id WHERE m.user_id=? AND m.status IN('requesting','unpaid','funded') ORDER BY m.id DESC",'i',$me['id']);
if($act){ ?><div class="card"><div class="page-head"><h3 style="margin:0">My active orders</h3><a href="?p=my_orders">All orders →</a></div><table>
<?php foreach($act as $m) echo '<tr><td><a href="?p=trip&id='.$m['trip_id'].'">'.h($m['title']).'</a></td><td>'.tag($m['ts']).'</td><td class="n">'.peso($m['status']=='funded'?$m['paid']:$m['due']).'</td><td>'.tag($m['status']=='requesting'?'adding items':$m['status']).'</td></tr>'; ?>
</table></div><?php } ?>
