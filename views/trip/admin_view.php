<?php // TRIP SECTION (HOA Admin): families and their money, read-only. ?>
<?php $fm=rows("SELECT m.*,u.name,u.block,u.lot FROM trip_members m JOIN users u ON u.id=m.user_id WHERE m.trip_id=? ORDER BY u.name",'i',$id); if(!$fm) return; ?>
<div class="card"><h3>Families in this trip</h3><div class="x"><table><tr><th>Name</th><th>Address</th><th class="n">Upfront due</th><th class="n">Paid</th><th>Status</th><th>Pickup</th></tr>
<?php foreach($fm as $m) echo '<tr><td>'.h($m['name']).'</td><td>Blk '.h($m['block']).' Lot '.h($m['lot']).'</td><td class="n">'.peso($m['due']).'</td><td class="n">'.peso($m['paid']).'</td><td>'.tag($m['status']).'</td><td>'.($m['status']=='settled'?tag($m['pickup']):'-').'</td></tr>'; ?>
</table></div></div>
