<?php // REUSABLE: open trips as cards. Set $tripLimit before including. Used by requester home and the Trips page.
$tc=rows("SELECT t.*,s.name sname,u.name tname,a.name aname,(SELECT COUNT(*) FROM trip_members m WHERE m.trip_id=t.id AND m.status NOT IN('cancelled','dropped')) n
 FROM trips t JOIN stores s ON s.id=t.store_id JOIN users u ON u.id=t.traveler_id LEFT JOIN areas a ON a.id=t.area_id
 WHERE t.status='open' ORDER BY t.window_closes_at LIMIT ".(int)($tripLimit??50));
if(!$tc){ echo '<div class="empty"><b>No open trips right now</b>When a neighbor posts a store run, it shows up here. Vote in the Product survey so travelers know what to buy.</div>'; return; }
echo '<div class="cards">';
foreach($tc as $t){ $load=trip_load($t['id']); $pct=min(100,round($load/max(1,$t['capacity'])*100));
 $joined=$me?(bool)val("SELECT 1 FROM trip_members WHERE trip_id=? AND user_id=? AND status='requesting'",'ii',$t['id'],$me['id']):false;
 $left=strtotime($t['window_closes_at'])-time(); $l=$left>86400?(floor($left/86400)==1?'1 day':floor($left/86400).' days'):($left>3600?floor($left/3600).' hrs':max(1,floor($left/60)).' min');
 echo '<div class="tcard"><h4>'.h($t['title']).'</h4><div class="meta">🏬 '.h($t['sname']).($t['aname']?' · '.h($t['aname']):'').'<br>🚗 '.h($t['tname']).' · '.h(VEHICLES[$t['vehicle']][0])
 .'<br>📅 Goes '.date('D, M j',strtotime($t['go_date'])).' · ⏳ closes in '.$l.'</div><div class="bar"><i style="width:'.$pct.'%"></i></div><small>'.$t['n'].' famil'.($t['n']==1?'y':'ies').' joined · '.$pct.'% of vehicle space used</small>'
 .'<div class="foot">'.($joined?tag('joined'):'<span></span>').'<a class="btn sm" data-tour="join" href="?p=trip&id='.$t['id'].'">'.($joined?'View / add more':($me?'Join this trip →':'View trip')).'</a></div></div>'; }
echo '</div>';
