<?php
// ACTION: traveler moves the trip forward (close window, drop unpaid, accept & lock, start shopping, finish).
$t=row("SELECT * FROM trips WHERE id=?",'i',$id); if(!trip_owner($t)) go('Only the traveler or HOA can do this.');
$e='';
switch(post('step')){
 case 'close':  close_window($id); $m='Request window closed. Packs consolidated; requesters can now fund.'; break;
 case 'drop':   drop_unpaid($id); $m='Unpaid requesters removed.'; break;
 case 'lock':   $e=lock_trip($id); $m='Trip accepted and locked. Pooled funds released to your wallet for the purchase.'; break;
 case 'shop':   if($t['status']!='locked') go('Lock the trip first.'); ex("UPDATE trips SET status='shopping' WHERE id=?",'i',$id); $m='Shopping started. Mark each item as you go.'; break;
 case 'finish':
  if($t['status']!='shopping') go('Start shopping first.');
  if(val("SELECT COUNT(*) FROM order_lines WHERE trip_id=? AND status='matched' AND shop='todo'",'i',$id)) go('Mark every item first (bought, substitute, or unavailable).','?p=trip&id='.$id);
  if(!val("SELECT COUNT(*) FROM trip_receipts WHERE trip_id=?",'i',$id)) go('Upload at least one receipt photo first (Receipts section), so families can see the real prices.','?p=trip&id='.$id);
  settle_trip($id); $m='Back in the village. Shares computed, refunds sent, requesters notified.'; break;
 default: go('Unknown step.');
}
if(!$e) audit("trip $id step ".post('step'));
go($e?:$m,'?p=trip&id='.$id);
