<?php // SCREEN: traveler reports today's shelf prices (section 5).
$stores=rows("SELECT s.* FROM stores s JOIN traveler_stores ts ON ts.store_id=s.id WHERE ts.user_id=? AND s.active=1",'i',$me['id']);
$items=rows("SELECT i.*,(SELECT COUNT(*) FROM survey_votes v WHERE v.item_id=i.id) fam FROM items i WHERE active=1 ORDER BY fam DESC,name"); ?>
<div class="card mid"><h2>Price check</h2><p class="muted">On your day off or during a trip, check the requested products at your usual stores and report the price on the shelf today.</p>
<?=form('price_check')?><div class="f wide">
<label class="full">Product (most requested first)<select name="item_id"><?php foreach($items as $i) echo opt($i['id'],$i['name'].' – '.$i['packaging'].($i['fam']?" ({$i['fam']} families)":''),$_GET['item']??null); ?></select></label>
<label>Store<select name="store_id"><?php foreach($stores as $s) echo opt($s['id'],$s['name']); ?></select></label>
<label>Shelf price per pack (₱)<input type="number" step="0.01" min="0.01" name="pack_price" required></label>
<label class="full">Brand on the shelf<input name="brand" placeholder="e.g. Bounty Fresh, Club Brand"></label>
<div class="full"><button data-tour="save-price">Save price</button></div></div></form></div>
<div class="card"><h3>Latest checked prices</h3><div class="x"><table><tr><th>Product</th><th>Store</th><th>Brand</th><th class="n">Per pack</th><th class="n">Per unit</th><th>Checked</th></tr>
<?php foreach(rows("SELECT p.*,i.name,i.packaging,i.pack_size,s.name sname,u.name by_name FROM item_prices p JOIN items i ON i.id=p.item_id JOIN stores s ON s.id=p.store_id LEFT JOIN users u ON u.id=p.checked_by ORDER BY p.checked_at DESC LIMIT 40") as $r)
 echo '<tr><td>'.h($r['name']).' <small>'.h($r['packaging']).'</small></td><td>'.h($r['sname']).'</td><td>'.h($r['brand']).'</td><td class="n">'.peso($r['unit_price']*$r['pack_size']).'</td><td class="n">'.peso($r['unit_price']).'</td><td><small>'.when($r['checked_at']).' · '.h($r['by_name']).'</small></td></tr>'; ?>
</table></div></div>
