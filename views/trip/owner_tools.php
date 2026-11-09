<?php // TRIP SECTION: traveler controls (lifecycle buttons, manifest by store section = route, shopping checklist, pickup list). ?>
<?php if(!$owner) return; $st=$t['status'];
$CONF=['lock'=>'Lock this trip? After this, cancelling costs you '.peso(DROPOUT_PENALTY).' and a '.DROPOUT_BAN_DAYS.'-day hosting ban.','finish'=>'Finish shopping? The final shares will be computed from the prices you entered.','drop'=>'Remove the families who have not paid? Their items will be dropped.','close'=>'Stop taking orders now? Families will then be asked to pay.'];
$step=fn($s,$label,$cls='')=>form('trip_step',hid('id',$id).hid('step',$s),isset($CONF[$s])?'data-confirm="'.h($CONF[$s]).'"':'').'<button data-tour="step-'.$s.'" class="'.$cls.'">'.$label.'</button></form>';
$fm=rows("SELECT m.*,u.name,u.block,u.lot FROM trip_members m JOIN users u ON u.id=m.user_id WHERE m.trip_id=? AND m.status NOT IN('cancelled','dropped') ORDER BY u.name",'i',$id); ?>
<div class="card" style="border-color:var(--g)"><h3>Traveler controls</h3>
<?php if($st=='open') echo '<p>Requests are coming in. The window closes automatically at '.when($t['window_closes_at']).', or close it now.</p><div class="actions">'.$step('close','Close window &amp; consolidate packs').form('cancel_trip',hid('id',$id),'data-confirm="Cancel this whole trip? Everyone who paid gets a full refund."').'<button class="danger">Cancel trip (no penalty)</button></form></div>';
if($st=='funding'){ $un=count(array_filter($fm,fn($m)=>$m['status']=='unpaid'));
 echo '<p>Waiting for upfront funding: <b>'.(count($fm)-$un).' of '.count($fm).'</b> families funded. You never pay for anyone: the trip unlocks only at 100%.</p><div class="actions">'.($un?$step('drop','Remove '.$un.' unpaid requester(s)','s'):'').form('cancel_trip',hid('id',$id),'data-confirm="Cancel this whole trip? Everyone who paid gets a full refund."').'<button class="danger">Cancel trip (no penalty)</button></form></div>'; }
if($st=='ready') echo '<div class="msg"><b>100% funded.</b> Review the manifest below. Tapping <b>Accept and Lock Trip</b> is your point of no return: cancelling after it costs '.peso(DROPOUT_PENALTY).' (shared with requesters) and a '.DROPOUT_BAN_DAYS.'-day hosting ban.</div><div class="actions">'.$step('lock','✓ Accept and Lock Trip').form('cancel_trip',hid('id',$id),'data-confirm="Cancel this whole trip? Everyone who paid gets a full refund."').'<button class="danger">Cancel trip (no penalty yet)</button></form></div>';
if($st=='locked') echo '<p>Locked. Pooled funds were released to your wallet for the purchase. Start the checklist when you are at the store.</p><div class="actions">'.$step('shop','🛒 I am at the store: start shopping').form('cancel_trip',hid('id',$id),'data-confirm="Cancel AFTER locking? You will pay a '.peso(DROPOUT_PENALTY).' penalty (shared with the families) and cannot host trips for '.DROPOUT_BAN_DAYS.' days."').'<button class="danger">Cancel (penalty applies)</button></form></div>';
if($st=='delivered'||$st=='settled'){ echo '<h4>Pickup list</h4><table><tr><th>Family</th><th>Address</th><th>Pickup</th><th></th></tr>';
 foreach($fm as $m) if($m['status']=='settled') echo '<tr><td>'.h($m['name']).'</td><td>Blk '.h($m['block']).' Lot '.h($m['lot']).'</td><td>'.tag($m['pickup']).'</td><td>'.($m['pickup']=='waiting'?form('collect',hid('id',$id).hid('who',$m['user_id'])).'<button class="sm s" data-tour="handover">Mark handed over</button></form>':'').'</td></tr>';
 echo '</table>'; }
// manifest grouped by store section (route optimization) + shopping checklist
if(in_array($st,['funding','ready','locked','shopping'])){
 $ls=rows("SELECT l.*,i.name,i.packaging,i.section,u.name uname,u.email uemail FROM order_lines l JOIN items i ON i.id=l.item_id JOIN users u ON u.id=l.user_id JOIN trip_members m ON m.trip_id=l.trip_id AND m.user_id=l.user_id
  WHERE l.trip_id=? AND l.status='matched' AND m.status IN('unpaid','funded') ORDER BY i.section,i.name,u.name",'i',$id);
 echo '<h4>'.($st=='shopping'?'Shopping checklist':'Manifest').' <small>– grouped by store section so you pass each aisle once</small></h4>';
 $sec=null; echo '<div class="x"><table class="stack-table"><tr><th>Item</th><th>For</th><th class="n">Qty</th><th class="n">Est.</th><th>'.($st=='shopping'?'Mark':'Subs').'</th></tr>';
 foreach($ls as $l){
  if($l['section']!==$sec){ $sec=$l['section']; echo '<tr class="sec"><td colspan="5" style="background:var(--paper)"><b>📍 '.h($sec?:'Other').'</b></td></tr>'; }
  echo '<tr data-who="'.h($l['uemail']).'" data-item="'.$l['item_id'].'"><td data-l="Item"><b>'.h($l['name']).'</b> <small>'.h($l['packaging']).'</small></td><td data-l="For">'.h($l['uname']).'</td><td class="n" data-l="Qty">'.$l['final_qty'].'</td><td class="n" data-l="Est. /unit">'.peso($l['est_price']).'</td><td data-l="">';
  if($st!='shopping') echo $l['allow_sub']?'<small>same-size OK</small>':'<small>ask first</small>';
  elseif($l['shop']=='todo') echo form('shop_line',hid('id',$l['id']),'class="inline"').'<input type="number" step="0.01" name="price" value="'.$l['est_price'].'" title="Shelf price per unit" style="width:95px"><input name="brand" placeholder="Brand" style="width:110px">'
   .'<button class="sm" name="do" value="bought">Bought</button><button class="sm s" name="do" value="unavailable" formnovalidate>Unavailable</button>'
   .'<details><summary><small>Substitute…</small></summary><div class="inline" style="margin-top:6px"><label class="check"><input type="checkbox" name="same_size" checked> same package size</label><input name="new_pack" placeholder="new size, e.g. 6-pack" style="width:140px"><button class="sm s" name="do" value="sub">Record substitute</button></div></details></form>';
  else echo tag($l['shop']).($l['actual_price']!==null?' <small>'.peso($l['actual_price']).' '.h($l['bought_brand']).'</small>':'');
  echo '</td></tr>'; }
 echo '</table></div>';
 if($st=='shopping'){ $left=count(array_filter($ls,fn($l)=>$l['shop']=='todo')); $wait=count(array_filter($ls,fn($l)=>$l['shop']=='needs_ok'));
  echo '<div class="actions">'.($left?'<small>'.$left.' item(s) left to mark.</small>':$step('finish','✓ Done: back in the village, compute shares')).($wait?' <small>'.$wait.' waiting for requester approval (unanswered = declined).</small>':'').'</div>'; }
}
if($st=='funding'||$st=='ready'){ echo '<h4>Families</h4><table><tr><th>Name</th><th>Address</th><th class="n">Upfront</th><th>Status</th></tr>';
 foreach($fm as $m) echo '<tr><td>'.h($m['name']).'</td><td>Blk '.h($m['block']).' Lot '.h($m['lot']).'</td><td class="n">'.peso($m['due']?:$m['paid']).'</td><td>'.tag($m['status']).'</td></tr>'; echo '</table>'; } ?>
</div>
