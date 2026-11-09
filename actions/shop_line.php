<?php
// ACTION: live shopping checklist (section 6 + in-store exception rules). One line at a time.
// bought: price within +10% buffer -> OK; above -> requester must approve.
// substitute: same package size + requester allowed swaps + within buffer -> OK; otherwise -> requester must approve.
$l=row("SELECT l.*,i.name,i.packaging FROM order_lines l JOIN items i ON i.id=l.item_id WHERE l.id=?",'i',$id);
$t=row("SELECT * FROM trips WHERE id=?",'i',$l['trip_id']??0);
if(!$t||!trip_owner($t)||$t['status']!='shopping') go('Start shopping first.');
$pr=round((float)post('price'),2); $limit=$l['est_price']*(1+BUFFER_RATE)+0.001; $brand=post('brand'); $do=post('do');
$back='?p=trip&id='.$t['id'];
if($do=='unavailable'){ ex("UPDATE order_lines SET shop='unavailable',actual_price=NULL,alert_note='Out of stock' WHERE id=?",'i',$id);
 notify($l['user_id'],"{$l['name']} was unavailable. You will be refunded for it.",$back); go('Marked unavailable.',$back); }
if($pr<=0) go('Enter the shelf price per unit.',$back);
if($do=='bought'){
 $ok=$pr<=$limit; $note=$ok?null:"Price: {$l['name']} is ".peso($pr)." per unit, above your buffer (".peso($l['est_price'])." + 10%).";
 ex("UPDATE order_lines SET shop=?,actual_price=?,bought_brand=?,alert_note=? WHERE id=?",'sdssi',$ok?'bought':'needs_ok',$pr,$brand,$note,$id);
 if(!$ok) notify($l['user_id'],'Approval needed: '.$note,$back);
 go($ok?'Bought.':'Price is above the buffer: the requester was asked to approve.',$back);
}
if($do=='sub'){
 $same=isset($_POST['same_size']); $ok=$same&&$l['allow_sub']&&$pr<=$limit;
 $note="Substitute: {$l['name']} → {$brand}".($same?'':' (different package size: '.post('new_pack').')').' at '.peso($pr).' per unit';
 ex("UPDATE order_lines SET shop=?,actual_price=?,bought_brand=?,alert_note=? WHERE id=?",'sdssi',$ok?'substituted':'needs_ok',$pr,$brand,$note,$id);
 notify($l['user_id'],($ok?'Substituted (auto-approved): ':'Approval needed: ').$note,$back);
 go($ok?'Substitute recorded (auto-approved, same tier and size).':'Substitution alert sent to the requester.',$back);
}
go('Unknown.',$back);
