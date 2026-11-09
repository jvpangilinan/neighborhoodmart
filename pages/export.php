<?php // RAW (HOA Admin): download records as CSV (opens in Excel / Google Sheets).
$w=$_GET['what']??'ledger';
$q=['ledger'=>["SELECT t.id trip_id,t.title,s.name store,u.name family,m.item_cost,m.expense_share,m.effort_fee,m.share,m.paid,m.balance,m.pickup,t.delivered_at FROM trip_members m JOIN trips t ON t.id=m.trip_id JOIN users u ON u.id=m.user_id JOIN stores s ON s.id=t.store_id WHERE m.status='settled' ORDER BY t.id,u.name"],
 'wallet'=>["SELECT w.id,w.created_at,u.name,w.kind,w.amount,w.method,w.ref,w.status,w.trip_id,w.note FROM wallet_tx w JOIN users u ON u.id=w.user_id ORDER BY w.id"],
 'residents'=>["SELECT name,email,block,lot,street,phone,verify_status,traveler_status,role,created_at FROM users ORDER BY name"]][$w]??null;
if(!$q){ http_response_code(404); exit; }
header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="neighborhoodmart_'.$w.'_'.date('Y-m-d').'.csv"');
$out=fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF"); $rs=rows($q[0]);
if($rs){ fputcsv($out,array_keys($rs[0])); foreach($rs as $r) fputcsv($out,$r); }
audit("exported $w CSV");
