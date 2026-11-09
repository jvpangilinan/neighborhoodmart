<?php
// ACTION: requester approves/declines a substitution or a price above the buffer.
$l=row("SELECT * FROM order_lines WHERE id=? AND user_id=? AND shop='needs_ok'",'ii',$id,$me['id']);
if(!$l) go('This alert was already answered or closed.');
$ok=post('ok')==='1';
ex("UPDATE order_lines SET shop=? WHERE id=?",'si',$ok?(str_starts_with((string)$l['alert_note'],'Substitute')?'substituted':'bought'):'declined',$id);
$t=row("SELECT * FROM trips WHERE id=?",'i',$l['trip_id']);
notify($t['traveler_id'],'Requester '.($ok?'APPROVED':'DECLINED').': '.$l['alert_note'],"?p=trip&id={$t['id']}");
go($ok?'Approved.':'Declined. It will not be bought and you will be refunded.','?p=trip&id='.$l['trip_id']);
