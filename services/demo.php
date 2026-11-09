<?php
// DEMO MODE helpers for Chanelly's guided tour (assets/tour.js). Everything here is OFF when DEMO_MODE=false.
// Tour accounts are tour-<random>@demo.ph; each new tour first deletes the previous tour's data so it can run again and again.
function demo_on(){ if(!DEMO_MODE) go('Demo mode is turned off.'); }
function demo_reset(){
 $U=array_column(rows("SELECT id FROM users WHERE email LIKE 'tour-%@demo.ph'"),'id'); if(!$U) return;
 $T=array_column(rows("SELECT id FROM trips WHERE traveler_id IN(".implode(',',array_map('intval',$U)).")"),'id');
 $u=implode(',',array_map('intval',$U)); $t=$T?implode(',',array_map('intval',$T)):'0';
 foreach(rows("SELECT file FROM trip_receipts WHERE trip_id IN($t)") as $r) if(!str_starts_with($r['file'],'demo_')) @unlink(ROOT.'/uploads/'.basename($r['file']));
 foreach(["DELETE FROM trip_receipts WHERE trip_id IN($t)","DELETE FROM order_lines WHERE trip_id IN($t) OR user_id IN($u)","DELETE FROM trip_members WHERE trip_id IN($t) OR user_id IN($u)",
  "DELETE FROM wallet_tx WHERE trip_id IN($t) OR user_id IN($u)","DELETE FROM disputes WHERE trip_id IN($t) OR user_id IN($u)","DELETE FROM trips WHERE id IN($t)",
  "DELETE FROM traveler_stores WHERE user_id IN($u)","DELETE FROM traveler_areas WHERE user_id IN($u)","DELETE FROM traveler_profiles WHERE user_id IN($u)",
  "DELETE FROM survey_votes WHERE user_id IN($u)","DELETE FROM notifications WHERE user_id IN($u)","DELETE FROM audit WHERE user_id IN($u)","DELETE FROM users WHERE id IN($u)"] as $q) ex($q);
 foreach($T as $id) ex("DELETE FROM notifications WHERE link LIKE ? OR link LIKE ?",'ss',"%trip&id=$id",'%trip&id='.$id.'"%');
}
function demo_login($u,$mode=null){
 session_regenerate_id(true); $_SESSION['u']=$u['id']; $_SESSION['mode']=$mode; unset($_SESSION['m']);
 if($mode) ex("UPDATE users SET last_mode=? WHERE id=?",'si',$mode,$u['id']);
}
// neighbors that join automatically so the pool looks real
function demo_bot_join($tid){
 $t=row("SELECT * FROM trips WHERE id=?",'i',$tid); if(!$t||$t['status']!='open') return;
 foreach([['requester2',3,4,1],['requester2',1,1,0],['liza@demo.ph',4,1,0],['grace@demo.ph',2,6,0]] as [$who,$item,$q,$ru]){
  $u=row("SELECT * FROM users WHERE username=? OR email=?",'ss',$who,$who); $it=row("SELECT * FROM items WHERE id=?",'i',$item);
  if(!$u||!$it||val("SELECT 1 FROM order_lines WHERE trip_id=? AND user_id=? AND item_id=?",'iii',$tid,$u['id'],$item)) continue;
  if(check_request($u,$t,$it,$q)) continue;
  ex("INSERT INTO trip_members(trip_id,user_id) VALUES(?,?) ON DUPLICATE KEY UPDATE status='requesting'",'ii',$tid,$u['id']);
  ex("INSERT INTO order_lines(trip_id,user_id,item_id,qty,accept_roundup,allow_sub,est_price) VALUES(?,?,?,?,?,1,?)",'iiiiid',$tid,$u['id'],$item,$q,$ru,
   (float)val("SELECT unit_price FROM item_prices WHERE item_id=? AND store_id=?",'ii',$item,$t['store_id']));
 }
 recalc_trip($tid);
}
function demo_bot_fund($tid){
 foreach(rows("SELECT m.*,u.username FROM trip_members m JOIN users u ON u.id=m.user_id WHERE m.trip_id=? AND m.status='unpaid' AND COALESCE(u.username,'')<>'requester'",'i',$tid) as $m){
  if(wallet_balance($m['user_id'])<$m['due']) wallet_add($m['user_id'],ceil($m['due']),'topup',null,'Demo top-up','gcash','DEMO');
  fund_member($tid,$m['user_id']);
 }
}
function demo_shop_rest($tid){
 $t=row("SELECT * FROM trips WHERE id=?",'i',$tid); if(!$t||$t['status']!='shopping') return;
 foreach(rows("SELECT * FROM order_lines WHERE trip_id=? AND status='matched' AND shop='todo'",'i',$tid) as $l)
  ex("UPDATE order_lines SET shop='bought',actual_price=est_price,bought_brand=(SELECT brand FROM item_prices WHERE item_id=? AND store_id=?) WHERE id=?",'iii',$l['item_id'],$t['store_id'],$l['id']);
 if(!val("SELECT 1 FROM trip_receipts WHERE trip_id=?",'i',$tid)){
  $n=date('Ymd').'_tour_'.bin2hex(random_bytes(4)).'.jpg'; @copy(ROOT.'/uploads/demo_receipt_tour.jpg',ROOT.'/uploads/'.$n);
  ex("INSERT INTO trip_receipts(trip_id,uploaded_by,file,note) VALUES(?,?,?,?)",'iiss',$tid,$t['traveler_id'],$n,'Store receipt (sample)');
 }
}

// ---------------- CHECKPOINTS (rollback) ----------------
// The tour path is a list of 16 milestones. demo_build() recreates the tour data up to milestone $need,
// so Chanelly can roll back to the exact state before any step, whatever went wrong.
// demo_progress() reads the database and says how many milestones are really done (used to spot mistakes).
const DEMO_PW='demo123';
function demo_user($k){ return row("SELECT * FROM users WHERE username=?",'s',$k); }
function demo_build($email,$need){
 demo_reset(); $tid=0; if($need<1) return 0;
 if(!preg_match('/^tour-[a-z0-9]{3,12}@demo\.ph$/',$email)) go('Bad demo account.','index.php');
 $c=hoa_check('Ed Santiago','10','1');
 $uid=ex("INSERT INTO users(name,email,pass,block,lot,street,phone,verify_status,verify_note,match_score,consent) VALUES('Ed Santiago',?,?,'10','1','Mabini St.','0917 555 0101',?,?,?,1)",'ssssi',
  $email,password_hash(DEMO_PW,PASSWORD_DEFAULT),$c['status'],$c['note'],$c['score']);
 if($need>=2){ ex("INSERT INTO traveler_profiles(user_id,has_membership,membership_store,has_card,card_expiry,vehicle) VALUES(?,1,'S&R',1,CURDATE()+INTERVAL 300 DAY,'sedan')",'i',$uid);
  ex("INSERT INTO traveler_stores VALUES(?,1),(?,2)",'ii',$uid,$uid); ex("INSERT INTO traveler_areas VALUES(?,1)",'i',$uid); ex("UPDATE users SET traveler_status='pending' WHERE id=?",'i',$uid); }
 if($need>=3) ex("UPDATE users SET verify_status='approved',verify_note=CONCAT(verify_note,' | Admin: approved') WHERE id=?",'i',$uid);
 if($need>=4) ex("UPDATE users SET traveler_status='approved' WHERE id=?",'i',$uid);
 if($need>=5) $tid=ex("INSERT INTO trips(traveler_id,store_id,area_id,title,go_date,window_closes_at,vehicle,capacity,expenses) VALUES(?,1,1,'Ed''s Sunday S&R run',CURDATE()+INTERVAL 2 DAY,CONCAT(CURDATE()+INTERVAL 1 DAY,' 20:00:00'),'sedan',?,180)",'ii',$uid,VEHICLES['sedan'][1]);
 if($need>=6) demo_bot_join($tid);
 $maria=demo_user('requester');
 if($need>=7){ $t=row("SELECT * FROM trips WHERE id=?",'i',$tid);
  ex("INSERT INTO trip_members(trip_id,user_id) VALUES(?,?)",'ii',$tid,$maria['id']);
  ex("INSERT INTO order_lines(trip_id,user_id,item_id,qty,accept_roundup,allow_sub,est_price) VALUES(?,?,4,1,0,1,(SELECT unit_price FROM item_prices WHERE item_id=4 AND store_id=1))",'ii',$tid,$maria['id']); recalc_trip($tid); }
 if($need>=8) close_window($tid);
 if($need>=9) fund_member($tid,$maria['id']);
 if($need>=10) demo_bot_fund($tid);
 if($need>=11) lock_trip($tid);
 if($need>=12) ex("UPDATE trips SET status='shopping' WHERE id=?",'i',$tid);
 if($need>=13) ex("UPDATE order_lines SET shop='bought',actual_price=1199,bought_brand='Jasmine (promo)' WHERE trip_id=? AND user_id=? AND item_id=4",'ii',$tid,$maria['id']);
 if($need>=14) demo_shop_rest($tid);
 if($need>=15) settle_trip($tid);
 if($need>=16) ex("UPDATE trip_members SET pickup='collected' WHERE trip_id=? AND user_id=?",'ii',$tid,$maria['id']);
 return $tid;
}
function demo_progress($email,$tid){
 $u=row("SELECT * FROM users WHERE email=? AND email LIKE 'tour-%@demo.ph'",'s',$email); if(!$u) return 0;
 if(!row("SELECT 1 FROM traveler_profiles WHERE user_id=?",'i',$u['id'])) return 1;
 if($u['verify_status']!='approved') return 2;
 if($u['traveler_status']!='approved') return 3;
 $t=$tid?row("SELECT * FROM trips WHERE id=? AND traveler_id=?",'ii',$tid,$u['id']):null; if(!$t||$t['status']=='cancelled') return 4;
 $maria=demo_user('requester');
 if((int)val("SELECT COUNT(*) FROM trip_members WHERE trip_id=? AND user_id<>? AND status<>'cancelled'",'ii',$tid,$maria['id'])<3) return 5;
 if(!val("SELECT 1 FROM order_lines WHERE trip_id=? AND user_id=? AND item_id=4 AND status='matched'",'ii',$tid,$maria['id'])) return 6;
 $st=$t['status']; $after=fn(...$s)=>in_array($st,$s);
 if(!$after('funding','ready','locked','shopping','delivered','settled')) return 7;
 if(!val("SELECT 1 FROM trip_members WHERE trip_id=? AND user_id=? AND status IN('funded','settled')",'ii',$tid,$maria['id'])) return 8;
 if(!$after('ready','locked','shopping','delivered','settled')) return 9;
 if(!$after('locked','shopping','delivered','settled')) return 10;
 if(!$after('shopping','delivered','settled')) return 11;
 if(!val("SELECT 1 FROM order_lines WHERE trip_id=? AND user_id=? AND item_id=4 AND shop IN('bought','substituted')",'ii',$tid,$maria['id'])) return 12;
 if(!$after('delivered','settled')&&(val("SELECT COUNT(*) FROM order_lines WHERE trip_id=? AND status='matched' AND shop='todo'",'i',$tid)||!val("SELECT 1 FROM trip_receipts WHERE trip_id=?",'i',$tid))) return 13;
 if(!$after('delivered','settled')) return 14;
 if(val("SELECT pickup FROM trip_members WHERE trip_id=? AND user_id=?",'ii',$tid,$maria['id'])!='collected') return 15;
 return 16;
}
