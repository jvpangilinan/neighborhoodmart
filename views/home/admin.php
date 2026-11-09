<?php // HOME for the HOA Admin: what needs a decision + recent trips. ?>
<div class="hello"><h2>HOA Admin dashboard</h2><p>Everything waiting for a human decision.</p></div>
<?php $tiles=[['admin_users','Residents to verify',val("SELECT COUNT(*) FROM users WHERE verify_status='pending'")],
 ['admin_travelers','Traveler registrations',val("SELECT COUNT(*) FROM users WHERE traveler_status='pending'")],
 ['admin_wallet','Top-ups / cash-outs',val("SELECT COUNT(*) FROM wallet_tx WHERE status='pending'")],
 ['admin_disputes','Open disputes',val("SELECT COUNT(*) FROM disputes WHERE status='open'")],
 ['admin_catalog','Item suggestions',val("SELECT COUNT(*) FROM item_suggestions WHERE status='new'")],
 ['admin_reports','Saved vs. supermarket (₱)',(int)community_savings()[2]],
 ['admin_trips','Active trips',val("SELECT COUNT(*) FROM trips WHERE status NOT IN('settled','cancelled')")]]; ?>
<div class="grid" style="margin-bottom:16px;grid-template-columns:repeat(auto-fit,minmax(150px,1fr))">
<?php foreach($tiles as [$pg,$l,$n]) echo '<a data-tour="tile-'.$pg.'" class="card tile'.($n&&!in_array($pg,['admin_trips','admin_reports'])?' hot':'').'" href="?p='.$pg.'"><b>'.(int)$n.'</b><span>'.$l.'</span><small>'.($n&&!in_array($pg,['admin_trips','admin_reports'])?'Review →':'Open →').'</small></a>'; ?></div>
<div class="grid">
<div class="card"><h3>Recent trips</h3><table><?php foreach(rows("SELECT t.*,u.name FROM trips t JOIN users u ON u.id=t.traveler_id ORDER BY t.id DESC LIMIT 6") as $t) echo '<tr><td><a href="?p=trip&id='.$t['id'].'">'.h($t['title']).'</a><br><small>'.h($t['name']).'</small></td><td>'.tag($t['status']).'</td></tr>'; ?></table></div>
<div class="card"><h3>Recent activity</h3><table><?php foreach(rows("SELECT a.*,u.name FROM audit a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC,a.id DESC LIMIT 8") as $x) echo '<tr><td><small>'.when($x['created_at']).'</small></td><td><small>'.h(($x['name']??'system').': '.$x['action']).'</small></td></tr>'; ?></table></div>
</div>
