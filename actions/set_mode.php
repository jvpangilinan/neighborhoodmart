<?php
// ACTION: choose Traveler or Requester (can switch anytime from the top bar).
$m=post('mode')=='traveler'?'traveler':'requester';
ex("UPDATE users SET last_mode=? WHERE id=?",'si',$m,$me['id']); // remembered for next login
if($m=='traveler'){
 $_SESSION['mode']='traveler';
 if($me['traveler_status']=='approved') go('Traveler mode.','index.php');
 go($me['traveler_status']=='pending'?'Traveler mode. Your traveler profile is being reviewed.':'Complete your traveler registration first.','?p=traveler_apply');
}
$_SESSION['mode']='requester'; go('Requester mode.','index.php');
