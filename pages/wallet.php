<?php // SCREEN: wallet credits (ledger only). Top-ups/cash-outs are real GCash/cash transfers confirmed by the HOA.
$bal=wallet_balance($me['id']); ?>
<div class="grid">
<div class="card kpi"><span>Available credit</span><b><?=peso($bal)?></b><small>Used to fund trips upfront. Refunds and apology credits land here.</small></div>
<div class="card" data-tour="topup"><h3>Top up</h3><?=form('topup')?><div class="f">
<label>Amount (₱)<input type="number" name="amount" min="1" step="0.01" required></label>
<label>Method<select name="method"><option value="gcash">GCash (send to the HOA GCash, then enter the reference)</option><option value="cash">Cash (hand to the HOA office)</option></select></label>
<label>GCash reference no.<input name="ref" placeholder="13-digit reference"></label><button>Submit top-up</button></div></form></div>
<div class="card"><h3>Cash out</h3><?=form('cashout','','data-confirm="Send this amount from your wallet to your GCash?"')?><div class="f">
<label>Amount (₱)<input type="number" name="amount" min="1" step="0.01" max="<?=max(0,$bal)?>" required></label>
<label>Send to (GCash number)<input name="dest" required></label><button class="s">Request cash-out</button></div></form></div>
</div>
<div class="card"><h3>History</h3><div class="x"><table><tr><th>Date</th><th>Type</th><th>Note</th><th>Trip</th><th class="n">Amount</th><th>Status</th></tr>
<?php foreach(rows("SELECT * FROM wallet_tx WHERE user_id=? ORDER BY id DESC LIMIT 100",'i',$me['id']) as $x)
 echo '<tr><td>'.when($x['created_at']).'</td><td>'.h(str_replace('_',' ',$x['kind'])).'</td><td>'.h($x['note']).($x['ref']?' <small>ref '.h($x['ref']).'</small>':'').'</td><td>'.($x['trip_id']?'<a href="?p=trip&id='.$x['trip_id'].'">#'.$x['trip_id'].'</a>':'').'</td><td class="n" style="color:'.($x['amount']<0?'var(--red)':'var(--g-d)').'">'.($x['amount']<0?'−':'+').peso(abs($x['amount'])).'</td><td>'.tag($x['status']).'</td></tr>'; ?>
</table></div></div>
