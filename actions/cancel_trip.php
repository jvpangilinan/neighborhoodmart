<?php
$t=row("SELECT * FROM trips WHERE id=?",'i',$id); if(!trip_owner($t)) go('Not allowed.');
$wasLocked=$t['status']=='locked'; $e=cancel_trip($id);
if(!$e) audit("cancelled trip $id".($wasLocked?' after lock (penalty)':''));
go($e?:($wasLocked?'Trip cancelled after the lock: '.peso(DROPOUT_PENALTY).' penalty shared with requesters and a '.DROPOUT_BAN_DAYS.'-day hosting ban applied.':'Trip cancelled. Everyone was refunded.'),'?p=trip&id='.$id);
