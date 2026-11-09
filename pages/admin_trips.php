<?php // SCREEN (HOA Admin): all trips. ?>
<div class="card"><h2>All trips</h2><div class="x"><table><tr><th>Trip</th><th>Store</th><th>Traveler</th><th>Trip date</th><th class="n">Families</th><th>Status</th></tr>
<?php foreach(rows("SELECT t.*,s.name sname,u.name tname,(SELECT COUNT(*) FROM trip_members m WHERE m.trip_id=t.id AND m.status NOT IN('cancelled','dropped')) n FROM trips t JOIN stores s ON s.id=t.store_id JOIN users u ON u.id=t.traveler_id ORDER BY t.id DESC") as $t)
 echo '<tr><td><a href="?p=trip&id='.$t['id'].'">'.h($t['title']).'</a></td><td>'.h($t['sname']).'</td><td>'.h($t['tname']).'</td><td>'.h($t['go_date']).'</td><td class="n">'.$t['n'].'</td><td>'.tag($t['status']).'</td></tr>'; ?>
</table></div></div>
