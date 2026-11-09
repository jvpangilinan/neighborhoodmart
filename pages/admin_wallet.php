<?php // SCREEN (HOA/admin): confirm GCash/cash top-ups and cash-outs. ?>
<?php $pend=rows("SELECT w.*,u.name FROM wallet_tx w JOIN users u ON u.id=w.user_id WHERE w.status='pending' ORDER BY w.id"); ?>
<div class="card"><h2>Wallet requests</h2><p class="muted">Confirm a top-up only after the GCash/cash actually arrived. Confirm a cash-out after you sent it.</p>
<?php if(!$pend) echo '<div class="empty"><b>Nothing to confirm</b></div>'; else { ?><div class="x"><table><tr><th>Date</th><th>Resident</th><th>Type</th><th class="n">Amount</th><th>Method / ref</th><th></th></tr>
<?php foreach($pend as $x) echo '<tr><td>'.when($x['created_at']).'</td><td>'.h($x['name']).'</td><td>'.h($x['kind']).'</td><td class="n">'.peso(abs($x['amount'])).'</td><td>'.h($x['method']).' '.h($x['ref']).' <small>'.h($x['note']).'</small></td><td>'
 .form('wallet_decide',hid('id',$x['id']),'class="inline"').'<button class="sm" name="decision" value="ok" data-confirm="Only confirm if the money really arrived (or you really sent it). Continue?">Confirm</button><button class="sm danger" name="decision" value="reject">Reject</button></form></td></tr>'; ?></table></div><?php } ?></div>
<div class="card"><h3>Balances</h3><div class="x"><table><tr><th>Resident</th><th class="n">Balance</th></tr>
<?php foreach(rows("SELECT id,name FROM users ORDER BY name") as $u){ $b=wallet_balance($u['id']); if(abs($b)>0.004) echo '<tr><td>'.h($u['name']).'</td><td class="n">'.peso($b).'</td></tr>'; } ?></table></div></div>
