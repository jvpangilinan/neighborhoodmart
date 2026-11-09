<?php
// ACTION: requester adds an item to a trip. The accept/reject decision lives in services/matching.php.
$t=row("SELECT * FROM trips WHERE id=?",'i',$id); $it=row("SELECT * FROM items WHERE id=?",'i',(int)post('item'));
if(!$t||!$it) go('Invalid trip or item.');
$q=max(1,(int)post('qty')); $why=check_request($me,$t,$it,$q);
$price=(float)val("SELECT unit_price FROM item_prices WHERE item_id=? AND store_id=?",'ii',$it['id'],$t['store_id']);
if(!$why){
 ex("INSERT INTO trip_members(trip_id,user_id) VALUES(?,?) ON DUPLICATE KEY UPDATE status=IF(status='cancelled','requesting',status)",'ii',$id,$me['id']);
}
ex("INSERT INTO order_lines(trip_id,user_id,item_id,qty,accept_roundup,allow_sub,est_price,status,reason) VALUES(?,?,?,?,?,?,?,?,?)",'iiiiiidss',
 $id,$me['id'],$it['id'],$q,isset($_POST['ru'])?1:0,isset($_POST['sub'])?1:0,$price,$why?'rejected':'matched',$why);
if(!$why){ recalc_trip($id); notify($t['traveler_id'],"New request on \"{$t['title']}\": {$q} × {$it['name']}","?p=trip&id=$id"); }
go($why?:'Request matched and added to the pool.','?p=trip&id='.$id);
