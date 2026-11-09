<?php
// CHECKOUT MATH: effort fee tiers + upfront amount. Fee numbers live in config/config.php.
// Upfront due = Item share + Refundable buffer (10%) + Estimated expense share + Traveler Effort Fee.
function effort_fee($c){
 if($c<=0) return 0.0;
 foreach(EFFORT_TIERS as [$upTo,$flat,$pct]) if($c<=$upTo) return round(max($flat,$c*$pct),2);
 return 0.0;
}
function tier_label(){ return '₱1–₱499: ₱50 · ₱500–₱1,499: ₱100 · ₱1,500+: ₱150 or 7% (whichever is higher)'; }
// recompute the estimate of every member of a trip (members already funded keep what they paid)
function recalc_trip($tid){
 $t=row("SELECT * FROM trips WHERE id=?",'i',$tid);
 $m=rows("SELECT user_id,SUM(COALESCE(final_qty,qty)*est_price) c FROM order_lines WHERE trip_id=? AND status='matched' GROUP BY user_id",'i',$tid);
 $tot=array_sum(array_column($m,'c'));
 ex("UPDATE trip_members SET est_items=0,buffer=0,est_expense=0,est_effort=0,due=0 WHERE trip_id=? AND status IN('requesting','unpaid')",'i',$tid);
 foreach($m as $r){
  $c=(float)$r['c']; $buf=round($c*BUFFER_RATE,2); $ex=$tot>0?round($c/$tot*$t['expenses'],2):0; $ef=effort_fee($c);
  ex("UPDATE trip_members SET est_items=?,buffer=?,est_expense=?,est_effort=?,due=? WHERE trip_id=? AND user_id=? AND status IN('requesting','unpaid')",
   'dddddii',$c,$buf,$ex,$ef,round($c+$buf+$ex+$ef,2),$tid,$r['user_id']);
 }
}
