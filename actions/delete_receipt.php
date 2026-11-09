<?php
// ACTION: traveler removes a wrong photo (only before the trip is settled).
$r=row("SELECT r.*,t.traveler_id,t.status FROM trip_receipts r JOIN trips t ON t.id=r.trip_id WHERE r.id=?",'i',$id);
if(!$r||!trip_owner($r)||$r['status']=='settled') go('Cannot remove this receipt.');
@unlink(ROOT.'/uploads/'.basename($r['file'])); ex("DELETE FROM trip_receipts WHERE id=?",'i',$id);
audit("deleted receipt $id"); go('Receipt removed.','?p=trip&id='.$r['trip_id']);
