<?php
// PROPORTIONAL COST ALLOCATION. Wrong amounts? Edit ONLY this file.
// Share_i = C_i + (C_i / ΣC) × F + E(C_i)
//   C_i  = actual receipt cost of items requester i received (bought or approved substitutes)
//   F    = trip expenses (gas, toll, parking)      E = Traveler Effort Fee tier on C_i
// Balance = paid upfront - share: positive -> refunded to wallet, negative -> collected. The traveler's wallet
// already received the pool at the lock, so refunds/collections move between requester and traveler.
function settle_trip($tid){
 $t=row("SELECT * FROM trips WHERE id=?",'i',$tid);
 // unanswered alerts at the end of shopping count as declined
 ex("UPDATE order_lines SET shop='declined',alert_note=CONCAT(COALESCE(alert_note,''),' (no answer)') WHERE trip_id=? AND shop='needs_ok'",'i',$tid);
 $ms=rows("SELECT m.*,COALESCE((SELECT SUM(l.final_qty*l.actual_price) FROM order_lines l WHERE l.trip_id=m.trip_id AND l.user_id=m.user_id AND l.status='matched' AND l.shop IN('bought','substituted')),0) c
  FROM trip_members m WHERE m.trip_id=? AND m.status='funded'",'i',$tid);
 $tot=array_sum(array_column($ms,'c'));
 foreach($ms as $m){
  $c=round((float)$m['c'],2); $es=$tot>0?round($c/$tot*$t['expenses'],2):0; $ef=effort_fee($c);
  $share=round($c+$es+$ef,2); $bal=round($m['paid']-$share,2);
  ex("UPDATE trip_members SET item_cost=?,expense_share=?,effort_fee=?,share=?,balance=?,status='settled' WHERE id=?",'dddddi',$c,$es,$ef,$share,$bal,$m['id']);
  if($bal>0){ wallet_add($m['user_id'],$bal,'refund',$tid,'Unused buffer / unavailable items'); wallet_add($t['traveler_id'],-$bal,'refund',$tid,'Refund to requester #'.$m['user_id']); }
  if($bal<0){ wallet_add($m['user_id'],$bal,'collect',$tid,'Approved price above buffer'); wallet_add($t['traveler_id'],-$bal,'collect',$tid,'Collected from requester #'.$m['user_id']); }
  notify($m['user_id'],"\"{$t['title']}\" is back in the village. Your final share is ".peso($share).($bal>0?', '.peso($bal).' refunded':'').". Collect within ".PICKUP_HOURS." hours.","?p=trip&id=$tid");
 }
 ex("UPDATE trips SET status='delivered',delivered_at=NOW() WHERE id=?",'i',$tid);
}
