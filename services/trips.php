<?php
// TRIP LIFECYCLE + LOCKS + PENALTIES (6, 6.2). Wrong status change or penalty? Edit ONLY this file.
// open -> funding (window closed, packs consolidated, everyone pays upfront) -> ready (100% funded, requester cancel disabled)
// -> locked (traveler tapped "Accept and Lock Trip", pooled funds released) -> shopping -> delivered (settled, pickup) -> settled
function trip_members_of($tid,$st=null){
 return $st?rows("SELECT * FROM trip_members WHERE trip_id=? AND status=?",'is',$tid,$st):rows("SELECT * FROM trip_members WHERE trip_id=?",'i',$tid);
}
function close_window($tid){
 $t=row("SELECT * FROM trips WHERE id=?",'i',$tid); if(!$t||$t['status']!='open') return;
 consolidate_trip($tid);
 // members with nothing left after trimming are dropped
 ex("UPDATE trip_members m SET status='dropped' WHERE trip_id=? AND status='requesting' AND NOT EXISTS(SELECT 1 FROM order_lines l WHERE l.trip_id=m.trip_id AND l.user_id=m.user_id AND l.status='matched')",'i',$tid);
 ex("UPDATE trip_members SET status='unpaid' WHERE trip_id=? AND status='requesting'",'i',$tid);
 recalc_trip($tid);
 if(!trip_members_of($tid,'unpaid')){ ex("UPDATE trips SET status='cancelled' WHERE id=?",'i',$tid); notify($t['traveler_id'],"Trip \"{$t['title']}\" closed with no requests and was cancelled.","?p=trip&id=$tid"); return; }
 ex("UPDATE trips SET status='funding',window_closes_at=LEAST(window_closes_at,NOW()) WHERE id=?",'i',$tid); // closed early = closes now
 foreach(trip_members_of($tid,'unpaid') as $m) notify($m['user_id'],"Requests closed for \"{$t['title']}\". Please fund your share of ".peso($m['due']).".","?p=trip&id=$tid");
 notify($t['traveler_id'],"Request window closed for \"{$t['title']}\". Waiting for requesters to fund.","?p=trip&id=$tid");
}
function check_ready($tid){
 $t=row("SELECT * FROM trips WHERE id=?",'i',$tid);
 if($t['status']!='funding'||trip_members_of($tid,'unpaid')||!trip_members_of($tid,'funded')) return;
 ex("UPDATE trips SET status='ready' WHERE id=?",'i',$tid);
 notify($t['traveler_id'],"\"{$t['title']}\" is 100% funded. Review the manifest and tap Accept and Lock Trip.","?p=trip&id=$tid");
}
function fund_member($tid,$uid){
 $m=row("SELECT * FROM trip_members WHERE trip_id=? AND user_id=?",'ii',$tid,$uid);
 if(!$m||$m['status']!='unpaid') return 'Nothing to fund.';
 if(wallet_balance($uid)+0.001<$m['due']) return 'Not enough wallet credit. Top up first (Wallet page).';
 wallet_add($uid,-$m['due'],'trip_hold',$tid,'Upfront funding (held)');
 ex("UPDATE trip_members SET paid=due,status='funded' WHERE id=?",'i',$m['id']);
 check_ready($tid); return '';
}
function drop_unpaid($tid){
 foreach(trip_members_of($tid,'unpaid') as $m){
  ex("UPDATE trip_members SET status='dropped' WHERE id=?",'i',$m['id']);
  ex("UPDATE order_lines SET status='cancelled',reason='Dropped: not funded in time' WHERE trip_id=? AND user_id=?",'ii',$tid,$m['user_id']);
  notify($m['user_id'],'You were removed from a trip because your share was not funded.',"?p=trip&id=$tid");
 }
 consolidate_trip($tid);   // packs may change; funded members get any difference back at settlement
 if(!trip_members_of($tid,'funded')){ ex("UPDATE trips SET status='cancelled' WHERE id=?",'i',$tid); return; }
 check_ready($tid);
}
function lock_trip($tid){
 $t=row("SELECT * FROM trips WHERE id=?",'i',$tid); if($t['status']!='ready') return 'Trip is not fully funded yet.';
 $pool=(float)val("SELECT COALESCE(SUM(paid),0) FROM trip_members WHERE trip_id=? AND status='funded'",'i',$tid);
 wallet_add($t['traveler_id'],$pool,'release',$tid,'Pooled funds released for the store purchase');
 ex("UPDATE trips SET status='locked',locked_at=NOW() WHERE id=?",'i',$tid);
 foreach(trip_members_of($tid,'funded') as $m) notify($m['user_id'],"\"{$t['title']}\" is locked. Your traveler is heading to the store.","?p=trip&id=$tid");
 return '';
}
// traveler cancels. Free before the lock; after the lock (before shopping) = penalty + ban.
function cancel_trip($tid){
 $t=row("SELECT * FROM trips WHERE id=?",'i',$tid);
 if(!in_array($t['status'],['open','funding','ready','locked'])) return 'This trip can no longer be cancelled. Send a dispute to the HOA instead.';
 $funded=trip_members_of($tid,'funded');
 if($t['status']=='locked') wallet_add($t['traveler_id'],-array_sum(array_column($funded,'paid')),'refund',$tid,'Return of released pool (trip cancelled)');
 foreach($funded as $m) wallet_add($m['user_id'],$m['paid'],'refund',$tid,'Full refund: traveler cancelled');
 if($t['status']=='locked'&&$funded){
  wallet_add($t['traveler_id'],-DROPOUT_PENALTY,'penalty',$tid,'Late dropout penalty');
  $each=round(DROPOUT_PENALTY/count($funded),2);
  foreach($funded as $m) wallet_add($m['user_id'],$each,'apology',$tid,'Apology credit from traveler penalty');
  ex("UPDATE users SET host_ban_until=DATE_ADD(NOW(),INTERVAL ? DAY) WHERE id=?",'ii',DROPOUT_BAN_DAYS,$t['traveler_id']);
 }
 ex("UPDATE trip_members SET status='cancelled' WHERE trip_id=? AND status NOT IN('dropped')",'i',$tid);
 ex("UPDATE order_lines SET status='cancelled' WHERE trip_id=? AND status='matched'",'i',$tid);
 ex("UPDATE trips SET status='cancelled' WHERE id=?",'i',$tid);
 foreach(trip_members_of($tid) as $m) notify($m['user_id'],"Trip \"{$t['title']}\" was cancelled by the traveler. Any held funds were refunded to your wallet.","?p=wallet");
 return '';
}
// requester cancels one line: only while the trip is open (point of no return = window closes / pool fills)
function cancel_line($lid,$uid){
 $l=row("SELECT l.*,t.status ts FROM order_lines l JOIN trips t ON t.id=l.trip_id WHERE l.id=? AND l.user_id=?",'ii',$lid,$uid);
 if(!$l) return 'Not found.';
 if($l['ts']!='open') return 'Cancel is disabled: the request window has closed (point of no return).';
 ex("UPDATE order_lines SET status='cancelled',reason='Cancelled by requester' WHERE id=?",'i',$lid);
 if(!val("SELECT COUNT(*) FROM order_lines WHERE trip_id=? AND user_id=? AND status='matched'",'ii',$l['trip_id'],$uid))
  ex("UPDATE trip_members SET status='cancelled' WHERE trip_id=? AND user_id=?",'ii',$l['trip_id'],$uid);
 recalc_trip($l['trip_id']); return '';
}
// time-based rules, run on every page load (cheap queries)
function run_timers(){
 foreach(rows("SELECT id FROM trips WHERE status='open' AND window_closes_at<NOW()") as $t) close_window($t['id']);
 foreach(rows("SELECT m.id,m.user_id,t.id tid,t.title FROM trip_members m JOIN trips t ON t.id=m.trip_id WHERE t.status='delivered' AND m.status='settled' AND m.pickup='waiting' AND t.delivered_at< NOW()-INTERVAL ? HOUR",'i',PICKUP_HOURS) as $m){
  ex("UPDATE trip_members SET pickup='uncollected' WHERE id=?",'i',$m['id']);
  notify($m['user_id'],"You did not collect your items from \"{$m['title']}\" within ".PICKUP_HOURS." hours. The traveler keeps full payment; the HOA may release the items.","?p=trip&id={$m['tid']}");
 }
 ex("UPDATE trips t SET status='settled' WHERE status='delivered' AND NOT EXISTS(SELECT 1 FROM trip_members m WHERE m.trip_id=t.id AND m.status='settled' AND m.pickup='waiting')");
}
