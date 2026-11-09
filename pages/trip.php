<?php // SCREEN: one trip. What you see depends on who you are:
//  Requester -> join/pay/approve/pickup | Traveler (own trip) -> controls + checklist | HOA Admin -> overview + ledger
$t=row("SELECT t.*,s.name sname,s.rules_note,s.bulk_ok,u.name tname,a.name aname FROM trips t JOIN stores s ON s.id=t.store_id JOIN users u ON u.id=t.traveler_id LEFT JOIN areas a ON a.id=t.area_id WHERE t.id=?",'i',(int)($_GET['id']??0));
if(!$t){echo '<div class="card">Trip not found.</div>';return;}
$id=$t['id']; $own=$me&&$t['traveler_id']==$me['id']; $owner=trip_owner($t);
$mem=$me?row("SELECT * FROM trip_members WHERE trip_id=? AND user_id=?",'ii',$id,$me['id']):null;
$shell=!$me?'guest':($mod?'admin':($mode?:'requester'));
if($shell=='traveler'&&!$own) $shell='guest';                 // a traveler looking at someone else's trip: view only
if($shell=='requester'&&$own) $shell='traveler';              // your own trip always opens your controls
$sections=['guest'=>['details','tally'],'requester'=>['details','request_form','my_order','receipts','tally'],
 'traveler'=>['details','owner_tools','receipts','tally','ledger'],'admin'=>['details','admin_view','receipts','tally','ledger']][$shell];
foreach($sections as $v) require ROOT."/views/trip/$v.php";
if($me&&!$mod&&$mode=='traveler'&&!$own) echo '<div class="card center"><p class="muted">This is another traveler\'s trip. To add items to it, switch to Requester.</p>'.form('set_mode',hid('mode','requester')).'<button class="s">Switch to 🛒 Requester</button></form></div>';
