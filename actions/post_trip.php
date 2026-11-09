<?php
// ACTION: traveler publishes a trip. Store must be one the traveler listed and the admin verified.
if($me['host_ban_until']&&strtotime($me['host_ban_until'])>time()) go('You cannot host trips until '.when($me['host_ban_until']).' (late dropout ban).','index.php');
$s=row("SELECT s.* FROM stores s JOIN traveler_stores ts ON ts.store_id=s.id AND ts.user_id=? WHERE s.id=? AND s.active=1",'ii',$me['id'],(int)post('store_id'));
if(!$s) go('Pick one of your verified stores.','?p=new_trip');
if($s['bulk_ok']=='not_allowed') go('The HOA found that this store does not allow buying for several people.','?p=new_trip');
$tp=row("SELECT * FROM traveler_profiles WHERE user_id=?",'i',$me['id']);
if($s['card_required']&&!$tp['has_card']) go('This store requires the traveler to hold its membership card.','?p=new_trip');
$v=array_key_exists(post('vehicle'),VEHICLES)?post('vehicle'):'sedan';
if(strtotime(post('closes'))<=time()) go('The request window must close in the future.','?p=new_trip');
$area=(int)post('area_id')?:null;
$tid=ex("INSERT INTO trips(traveler_id,store_id,area_id,title,go_date,window_closes_at,vehicle,capacity,expenses) VALUES(?,?,?,?,?,?,?,?,?)",'iiissssid',
 $me['id'],$s['id'],$area,post('title'),post('go_date'),post('closes'),$v,VEHICLES[$v][1],max(0,(float)post('expenses')));
audit("posted trip $tid"); go('Trip posted.','?p=trip&id='.$tid);
