<?php
// "YOU SAVED" = what the same items cost at a regular supermarket (items.retail_price) minus what you paid.
// Before shopping it uses the estimate; after settlement it uses the final share. Edit ONLY this file.
function member_savings($tid,$uid){
 $m=row("SELECT * FROM trip_members WHERE trip_id=? AND user_id=?",'ii',$tid,$uid); if(!$m) return null;
 $final=$m['status']=='settled';
 $retail=(float)val("SELECT COALESCE(SUM(".($final?'l.final_qty':'COALESCE(l.final_qty,l.qty)')."*i.retail_price),0) FROM order_lines l JOIN items i ON i.id=l.item_id
  WHERE l.trip_id=? AND l.user_id=? AND l.status='matched'".($final?" AND l.shop IN('bought','substituted')":''),'ii',$tid,$uid);
 $cost=$final?(float)$m['share']:(float)($m['est_items']+$m['est_expense']+$m['est_effort']); // buffer excluded: it is refundable
 if($retail<=0) return null;
 return ['retail'=>$retail,'cost'=>$cost,'saved'=>round($retail-$cost,2),'pct'=>round(($retail-$cost)/$retail*100),'final'=>$final];
}
// total saved by one user over all settled orders
function user_total_savings($uid){
 $t=0; foreach(rows("SELECT trip_id FROM trip_members WHERE user_id=? AND status='settled'",'i',$uid) as $r){ $s=member_savings($r['trip_id'],$uid); if($s) $t+=$s['saved']; }
 return round($t,2);
}
// community totals for the HOA report: [retail, paid, saved]
function community_savings(){
 $retail=(float)val("SELECT COALESCE(SUM(l.final_qty*i.retail_price),0) FROM order_lines l JOIN items i ON i.id=l.item_id JOIN trip_members m ON m.trip_id=l.trip_id AND m.user_id=l.user_id
  WHERE m.status='settled' AND l.status='matched' AND l.shop IN('bought','substituted')");
 $paid=(float)val("SELECT COALESCE(SUM(share),0) FROM trip_members WHERE status='settled'");
 return [$retail,$paid,round($retail-$paid,2)];
}
function savings_line($s){
 if(!$s) return '';
 return '<div data-tour="savings" class="save'.($s['saved']<0?' neg':'').'">'.($s['saved']>=0?'💰 You '.($s['final']?'saved ':'will save about ').'<b>'.peso($s['saved']).'</b> ('.$s['pct'].'%)':'⚠️ About '.peso(-$s['saved']).' more than the supermarket this time')
  .' <small>vs. regular supermarket price of '.peso($s['retail']).'</small></div>';
}
