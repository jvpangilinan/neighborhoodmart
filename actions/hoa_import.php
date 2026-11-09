<?php
// ACTION: add HOA master records. One per line: Full Name, Block, Lot   (also accepts a CSV upload)
$txt=post('lines'); if(!empty($_FILES['csv']['tmp_name'])) $txt.="\n".file_get_contents($_FILES['csv']['tmp_name']);
$n=0;
foreach(preg_split('/\r?\n/',$txt) as $line){
 $c=array_map('trim',str_getcsv($line)); if(count($c)<3||$c[0]===''||strtolower($c[0])=='full name') continue;
 ex("INSERT INTO hoa_residents(full_name,block,lot) VALUES(?,?,?)",'sss',$c[0],$c[1],$c[2]); $n++;
}
// re-check people still waiting: some may now match exactly
foreach(rows("SELECT * FROM users WHERE verify_status='pending'") as $u){ $c=hoa_check($u['name'],$u['block'],$u['lot']);
 if($c['status']=='approved'){ ex("UPDATE users SET verify_status='approved',verify_note=? WHERE id=?",'si',$c['note'],$u['id']); notify($u['id'],'Your residency was verified by the HOA records.','?p=account'); } }
audit("imported $n HOA records"); go("$n HOA records added.",'?p=admin_users');
