<?php // TRIP SECTION: item tally with pack progress (everyone can see it). ?>
<?php $rs=rows("SELECT i.name,i.packaging,i.pack_size p,MAX(l.est_price) est,SUM(l.qty) q,SUM(COALESCE(l.final_qty,l.qty)) f,COUNT(DISTINCT l.user_id) fam FROM order_lines l JOIN items i ON i.id=l.item_id WHERE l.trip_id=? AND l.status IN('matched','trimmed') GROUP BY i.id ORDER BY i.section,i.name",'i',$id);
if(!$rs) return; ?>
<div class="card" data-tour="tally"><h3>👪 Everyone's items in this trip</h3><div class="x"><table><tr><th>Item</th><th class="n">Families</th><th class="n">Units</th><th class="adv">Box progress</th></tr>
<?php foreach($rs as $r){ $pk=max(1,$r['p']); $q=$t['status']=='open'?$r['q']:$r['f']; $rem=$q%$pk; $pct=$rem?round($rem/$pk*100):100; ?>
<tr><td><?=h($r['name'])?> <small><?=h($r['packaging'])?> · <?=peso($r['est'])?>/unit</small></td><td class="n"><?=$r['fam']?></td><td class="n"><?=$r['q']?><?=$t['status']!='open'?' → '.$r['f']:''?></td>
<td class="adv"><?php if($pk>1){ ?><div class="bar"><i style="width:<?=$pct?>%"></i></div><small><?=intdiv($q,$pk)?> full pack(s)<?=$rem?', '.($pk-$rem).' more to complete the next':''?></small><?php } else echo '<small>sold per unit</small>'; ?></td></tr>
<?php } ?></table></div></div>
