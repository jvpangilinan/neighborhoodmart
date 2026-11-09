<?php // HOME for Travelers: my trips with ONE next-action button each, earnings, what people need. ?>
<div class="hello"><h2>Hi, <?=h(explode(' ',$me['name'])[0])?> 🚗</h2><p>Your store runs and what to do next.</p></div>
<?php if(!$isTraveler){ ?>
<div class="card mid center"><h3>Become a traveler</h3><p class="muted">Tell the HOA which stores you visit and your membership card. Status: <?=tag($me['traveler_status'])?></p>
<a class="btn" data-tour="become-traveler" href="?p=traveler_apply"><?=$me['traveler_status']=='none'?'Start traveler registration':'View my registration'?></a></div>
<?php return; }
if($me['host_ban_until']&&strtotime($me['host_ban_until'])>time()) echo '<div class="warn">Hosting ban until '.when($me['host_ban_until']).' (late dropout).</div>';
$earned=(float)val("SELECT COALESCE(SUM(m.effort_fee),0) FROM trip_members m JOIN trips t ON t.id=m.trip_id WHERE t.traveler_id=?",'i',$me['id']);
$mine=rows("SELECT t.*,s.name sname,(SELECT COUNT(*) FROM trip_members m WHERE m.trip_id=t.id AND m.status NOT IN('cancelled','dropped')) n,
 (SELECT COUNT(*) FROM trip_members m WHERE m.trip_id=t.id AND m.status='unpaid') unpaid,(SELECT COUNT(*) FROM trip_members m WHERE m.trip_id=t.id AND m.status='funded') funded
 FROM trips t JOIN stores s ON s.id=t.store_id WHERE t.traveler_id=? AND t.status NOT IN('settled','cancelled') ORDER BY t.go_date",'i',$me['id']); ?>
<div class="grid" style="margin-bottom:16px">
<div class="card kpi"><span>Active trips</span><b><?=count($mine)?></b></div>
<div class="card kpi"><span>Effort fees earned</span><b><?=peso($earned)?></b></div>
<div class="card kpi"><span>Wallet</span><b><?=peso(wallet_balance($me['id']))?></b><a href="?p=wallet"><small>Cash out →</small></a></div>
</div>
<div class="card"><div class="page-head"><h3 style="margin:0">My trips</h3><a class="btn" href="?p=new_trip" data-tour="post-trip">+ Post a new trip</a></div>
<?php if(!$mine) echo '<div class="empty"><b>No active trips</b>Post a trip and your neighbors can start adding items.</div>';
$step=fn($id,$s,$l,$c='')=>form('trip_step',hid('id',$id).hid('step',$s),['close'=>'data-confirm="Stop taking orders now? Families will then be asked to pay."','drop'=>'data-confirm="Remove the families who have not paid?"'][$s]??'').'<button data-tour="step-'.$s.'" class="'.$c.'">'.$l.'</button></form>';
echo '<div class="todo">';
foreach($mine as $t){ $id=$t['id']; $open='<a class="btn ghost" href="?p=trip&id='.$id.'">Open</a>';
 [$hint,$act]=match($t['status']){
  'open'=>[$t['n'].' famil'.($t['n']==1?'y':'ies').' joined. Requests close '.when($t['window_closes_at']).'.',$t['n']?$step($id,'close','Close requests now','s'):''],
  'funding'=>[$t['funded'].' of '.($t['funded']+$t['unpaid']).' families paid. Waiting for the rest.',$t['unpaid']?$step($id,'drop','Remove '.$t['unpaid'].' unpaid','s'):''],
  'ready'=>['100% paid! Review the list and lock the trip.','<a class="btn" data-tour="trip-next" href="?p=trip&id='.$id.'">Review &amp; lock →</a>'],
  'locked'=>['Locked. Pooled money is in your wallet. Go shopping!',$step($id,'shop','🛒 I\'m at the store')],
  'shopping'=>['Mark each item as you shop.','<a class="btn" data-tour="trip-next" href="?p=trip&id='.$id.'">Open checklist →</a>'],
  'delivered'=>['Back home. Hand over items to each family.','<a class="btn" data-tour="trip-next" href="?p=trip&id='.$id.'">Pickup list →</a>'],
  default=>['','']};
 echo '<div class="todo-item go"><div class="txt"><b>'.h($t['title']).' '.tag($t['status']).'</b><small>'.h($t['sname']).' · '.date('D, M j',strtotime($t['go_date'])).'</small><small>'.$hint.'</small></div><div class="inline">'.$act.$open.'</div></div>'; }
echo '</div>'; ?></div>
<div class="card"><div class="page-head"><h3 style="margin:0">What your neighbors need most</h3><a href="?p=demand">Full survey →</a></div><table>
<?php foreach(rows("SELECT i.id,i.name,i.packaging,COUNT(v.user_id) fam,SUM(v.qty) u FROM survey_votes v JOIN items i ON i.id=v.item_id GROUP BY i.id ORDER BY fam DESC,u DESC LIMIT 5") as $r)
 echo '<tr><td><b>'.h($r['name']).'</b> <small>'.h($r['packaging']).'</small></td><td class="n">'.$r['fam'].' famil'.($r['fam']==1?'y':'ies').' · '.$r['u'].' unit'.($r['u']==1?'':'s').'</td><td class="n"><a href="?p=price_check&item='.$r['id'].'">Check price</a></td></tr>'; ?></table></div>
