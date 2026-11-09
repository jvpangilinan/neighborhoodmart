<?php // SCREEN (HOA Admin): analytics dashboard (FR-13) + CSV exports for HOA records.
$done=(int)val("SELECT COUNT(*) FROM trips WHERE status IN('delivered','settled')");
$fam=(int)val("SELECT COUNT(DISTINCT user_id) FROM trip_members WHERE status='settled'");
$orders=(int)val("SELECT COUNT(*) FROM trip_members WHERE status='settled'");
[$retail,$paid,$saved]=community_savings();
$fees=(float)val("SELECT COALESCE(SUM(effort_fee),0) FROM trip_members WHERE status='settled'");
$weeks=[]; for($i=5;$i>=0;$i--){ $from=date('Y-m-d',strtotime("monday this week -$i week")); $to=date('Y-m-d',strtotime("$from +7 day"));
 $weeks[]=[date('M j',strtotime($from)),(float)val("SELECT COALESCE(SUM(m.share),0) FROM trip_members m JOIN trips t ON t.id=m.trip_id WHERE m.status='settled' AND t.delivered_at>=? AND t.delivered_at<?",'ss',$from,$to)]; }
$items=array_map(fn($r)=>[$r['name'],(int)$r['u']],rows("SELECT i.name,SUM(l.final_qty) u FROM order_lines l JOIN items i ON i.id=l.item_id JOIN trip_members m ON m.trip_id=l.trip_id AND m.user_id=l.user_id WHERE m.status='settled' AND l.shop IN('bought','substituted') GROUP BY i.id ORDER BY u DESC LIMIT 7"));
$st=array_map(fn($r)=>[ucfirst($r['status']),(int)$r['n']],rows("SELECT status,COUNT(*) n FROM trips GROUP BY status ORDER BY FIELD(status,'open','funding','ready','locked','shopping','delivered','settled','cancelled')"));
$tr=rows("SELECT u.name,COUNT(DISTINCT t.id) trips,COUNT(m.id) orders,COALESCE(SUM(m.effort_fee),0) fees,COALESCE(SUM(m.share),0) val FROM users u JOIN trips t ON t.traveler_id=u.id LEFT JOIN trip_members m ON m.trip_id=t.id AND m.status='settled' GROUP BY u.id ORDER BY fees DESC");
$cancel=(int)val("SELECT COUNT(*) FROM trips WHERE status='cancelled'"); $all=max(1,(int)val("SELECT COUNT(*) FROM trips")); ?>
<div class="page-head"><div><h2>Reports</h2><p>Community bulk-buying results. Dates are by delivery day.</p></div>
<div class="actions" style="margin:0"><a class="btn ghost sm" href="?p=export&what=ledger">⬇ Ledger CSV</a><a class="btn ghost sm" href="?p=export&what=wallet">⬇ Wallet CSV</a><a class="btn ghost sm" href="?p=export&what=residents">⬇ Residents CSV</a></div></div>
<div class="grid" data-tour="kpis" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));margin-bottom:16px">
<div class="card kpi"><span>Trips delivered</span><b><?=$done?></b><small><?=$cancel?> cancelled (<?=round($cancel/$all*100)?>%)</small></div>
<div class="card kpi"><span>Families served</span><b><?=$fam?></b><small><?=$orders?> orders</small></div>
<div class="card kpi"><span>Money pooled</span><b><?=peso($paid)?></b><small>final shares paid</small></div>
<div class="card kpi"><span>Saved vs. supermarket</span><b><?=peso($saved)?></b><small><?=$retail>0?round($saved/$retail*100).'% cheaper overall':''?></small></div>
<div class="card kpi"><span>Earned by travelers</span><b><?=peso($fees)?></b><small>effort fees</small></div>
</div>
<div class="card"><h3>Money pooled per week</h3><?=vbar_chart($weeks)?></div>
<div class="grid">
<div class="card"><h3>Most bought items (units)</h3><?=hbar_chart($items)?></div>
<div class="card"><h3>Trips by status</h3><?=hbar_chart($st)?></div>
</div>
<div class="card" style="margin-top:16px"><h3>Travelers</h3><div class="x"><table><tr><th>Traveler</th><th class="n">Trips</th><th class="n">Orders delivered</th><th class="n">Value handled</th><th class="n">Effort fees earned</th></tr>
<?php foreach($tr as $r) echo '<tr><td>'.h($r['name']).'</td><td class="n">'.$r['trips'].'</td><td class="n">'.$r['orders'].'</td><td class="n">'.peso($r['val']).'</td><td class="n">'.peso($r['fees']).'</td></tr>'; ?></table></div></div>
<div class="card"><h3>Weekly numbers</h3><table><tr><th>Week of</th><th class="n">Pooled</th></tr><?php foreach($weeks as [$l,$v]) echo '<tr><td>'.$l.'</td><td class="n">'.peso($v).'</td></tr>'; ?></table></div>
