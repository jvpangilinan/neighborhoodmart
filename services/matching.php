<?php
// MATCHING RULES R1-R5. Wrong accept/reject result? Edit ONLY this file.
// Returns '' when accepted, otherwise the reason shown to the requester.
function trip_load($tid){
 return (int)val("SELECT COALESCE(SUM(l.qty*i.load_units),0) FROM order_lines l JOIN items i ON i.id=l.item_id WHERE l.trip_id=? AND l.status='matched'",'i',$tid);
}
function check_request($me,$t,$it,$q){
 if($me['verify_status']!=='approved') return 'The HOA has not checked your account yet. (R1)';
 $price=row("SELECT p.* FROM item_prices p JOIN stores s ON s.id=p.store_id WHERE p.item_id=? AND p.store_id=? AND s.bulk_ok<>'not_allowed'",'ii',$it['id'],$t['store_id']);
 if(!$price||!$it['active']) return "This store doesn't have this item. (R2)";
 if($t['status']!='open'||strtotime($t['window_closes_at'])<time()) return 'Sorry, this trip is no longer taking orders. (R3)';
 if(trip_load($t['id'])+$q*$it['load_units']>$t['capacity']) return 'Sorry, the car is already full. Try a smaller amount or another trip. (R4)';
 $mine=(int)val("SELECT COALESCE(SUM(qty),0) FROM order_lines WHERE trip_id=? AND user_id=? AND item_id=? AND status='matched'",'iii',$t['id'],$me['id'],$it['id']);
 if($it['max_per_member']&&$mine+$q>$it['max_per_member']) return 'You can only order up to '.$it['max_per_member'].' of this per trip. (R5)';
 if($t['traveler_id']==$me['id']) return 'This is your own trip.';
 return '';
}
