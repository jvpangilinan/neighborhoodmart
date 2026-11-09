<?php
// ACTION: requester confirms pickup (or traveler marks handed over: who = user id).
$t=row("SELECT * FROM trips WHERE id=?",'i',$id);
$uid=(int)($_POST['who']??$me['id']); if($uid!=$me['id']&&!trip_owner($t)) go('Not allowed.');
ex("UPDATE trip_members SET pickup='collected' WHERE trip_id=? AND user_id=? AND pickup='waiting'",'ii',$id,$uid);
run_timers(); go('Pickup confirmed.','?p=trip&id='.$id);
