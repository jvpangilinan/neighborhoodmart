<?php
// ACTION: traveler registration form (section 3). Admin reviews it in Admin > Travelers.
$v=array_key_exists(post('vehicle'),VEHICLES)?post('vehicle'):'sedan';
ex("REPLACE INTO traveler_profiles(user_id,other_stores,other_areas,has_membership,membership_store,has_card,card_expiry,vehicle) VALUES(?,?,?,?,?,?,?,?)",'issisiss',
 $me['id'],post('other_stores'),post('other_areas'),isset($_POST['has_membership'])?1:0,post('membership_store'),isset($_POST['has_card'])?1:0,post('card_expiry')?:null,$v);
ex("DELETE FROM traveler_stores WHERE user_id=?",'i',$me['id']); ex("DELETE FROM traveler_areas WHERE user_id=?",'i',$me['id']);
foreach((array)($_POST['stores']??[]) as $s) ex("INSERT IGNORE INTO traveler_stores VALUES(?,?)",'ii',$me['id'],(int)$s);
foreach((array)($_POST['areas']??[]) as $a) ex("INSERT IGNORE INTO traveler_areas VALUES(?,?)",'ii',$me['id'],(int)$a);
if($me['traveler_status']!='approved') ex("UPDATE users SET traveler_status='pending' WHERE id=?",'i',$me['id']);
foreach(rows("SELECT id FROM users WHERE role='admin'") as $a) notify($a['id'],'Traveler profile to review: '.$me['name'],'?p=admin_travelers');
go('Traveler profile submitted. The HOA will review it.','?p=traveler_apply');
