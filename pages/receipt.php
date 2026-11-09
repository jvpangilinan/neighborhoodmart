<?php // RAW: show one receipt photo, only to people allowed to see that trip's receipts.
$r=row("SELECT * FROM trip_receipts WHERE id=?",'i',(int)($_GET['id']??0));
if(!$r||!can_see_receipts($r['trip_id'])){ http_response_code(403); exit('Not allowed.'); }
send_upload($r['file']);
