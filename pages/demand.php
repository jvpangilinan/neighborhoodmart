<?php // SCREEN: community product survey (section 4). Requesters vote; travelers read the ranking.
$mine=[]; foreach(rows("SELECT item_id,qty FROM survey_votes WHERE user_id=?",'i',$me['id']) as $v) $mine[$v['item_id']]=$v['qty'];
$items=rows("SELECT i.*,COUNT(v.user_id) fam,COALESCE(SUM(v.qty),0) units,
 (SELECT MIN(p.unit_price) FROM item_prices p WHERE p.item_id=i.id) lo,(SELECT MAX(p.checked_at) FROM item_prices p WHERE p.item_id=i.id) chk
 FROM items i LEFT JOIN survey_votes v ON v.item_id=i.id AND v.voted_at>NOW()-INTERVAL 30 DAY WHERE i.active=1 GROUP BY i.id ORDER BY fam DESC,units DESC,i.name"); ?>
<div class="card"><div class="page-head"><div><h2><?=$mode=='traveler'?'Survey results':'Product survey'?></h2>
<p><?=$mode=='traveler'?'What the community needs (last 30 days). Use it to plan which products to check on your next trip.':'Tell travelers what you need. Items are generic (any brand of the same size and quality).'?></p></div>
<?php if($mode=='traveler'&&$isTraveler) echo '<a class="btn ghost" href="?p=price_check">Check prices →</a>'; ?></div>
<div class="x"><table><tr><th>#</th><th>Item &amp; packaging</th><th class="n">Families</th><th class="n">Units</th><th>Last price check</th><th>My need</th></tr>
<?php foreach($items as $k=>$i){ $max=max(1,$items[0]['fam']); ?>
<tr><td><?=$k+1?></td><td><b><?=h($i['name'])?></b> <small>– <?=h($i['packaging'])?></small><div class="bar" style="margin-top:6px;max-width:220px"><i style="width:<?=round($i['fam']/$max*100)?>%"></i></div></td>
<td class="n"><?=$i['fam']?></td><td class="n"><?=$i['units']?></td>
<td><?=$i['lo']?'from '.peso($i['lo']).'/unit<br><small>'.when($i['chk']).'</small>':'<small>not checked yet</small>'?></td>
<td><?=form('vote',hid('id',$i['id']),'class="inline"')?><input type="number" name="qty" min="0" value="<?=$mine[$i['id']]??0?>" title="Units"><button data-tour="vote" class="sm <?=isset($mine[$i['id']])?'s':''?>"><?=isset($mine[$i['id']])?'Update':'I need this'?></button></form></td></tr>
<?php } ?></table></div></div>
<div class="card mid"><h3>Not on the list?</h3><?=form('suggest_item')?><div class="f wide">
<label>Item<input name="name" required placeholder="e.g. Whole Fresh Milk 1L"></label><label>Packaging<input name="packaging" placeholder="e.g. case of 6"></label>
<div class="full"><button class="s">Suggest item</button></div></div></form></div>
