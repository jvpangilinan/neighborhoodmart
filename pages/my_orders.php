<?php // SCREEN: requester's orders across trips. ?>
<div class="card"><h2>My orders</h2>
<?php $ms=rows("SELECT m.*,t.title,t.status ts,s.name sname FROM trip_members m JOIN trips t ON t.id=m.trip_id JOIN stores s ON s.id=t.store_id WHERE m.user_id=? ORDER BY m.id DESC",'i',$me['id']);
if(!$ms) echo '<div class="empty"><b>No orders yet</b>Open a trip on the <a href="index.php">Trips</a> board and add your items.</div>'; else { ?>
<div class="x"><table><tr><th>Trip</th><th>Trip status</th><th>My status</th><th class="n">Upfront</th><th class="n">Final share</th><th class="n">Refund / collect</th><th class="n">Saved</th><th>Pickup</th></tr>
<?php foreach($ms as $m) echo '<tr><td><a href="?p=trip&id='.$m['trip_id'].'">'.h($m['title']).'</a><br><small>'.h($m['sname']).'</small></td><td>'.tag($m['ts']).'</td><td>'.tag($m['status']).'</td><td class="n">'.peso($m['status']=='requesting'?$m['due']:$m['paid']).'</td><td class="n">'.($m['share']!==null?peso($m['share']):'-').'</td><td class="n">'.($m['balance']!==null?($m['balance']>=0?'+':'−').peso(abs($m['balance'])):'-').'</td><td class="n">'.($m['status']=='settled'&&($s=member_savings($m['trip_id'],$me['id']))?peso($s['saved']):'-').'</td><td>'.($m['status']=='settled'?tag($m['pickup']):'-').'</td></tr>'; ?>
</table></div><?php } ?></div>
