<?php
// ACTION: answer to Chanelly's "Guided setup" question (first login, or from the Aa panel).
$c=post('choice');
if($c=='off'){ ex("UPDATE users SET guided=0 WHERE id=?",'i',$me['id']); go('Okay! You can turn on Guided mode anytime from the Aa button.',post('back')?:'index.php'); }
$m=$c=='traveler'?'traveler':'requester';
ex("UPDATE users SET guided=1,last_mode=? WHERE id=?",'si',$m,$me['id']); $_SESSION['mode']=$m;
if($m=='traveler'&&$me['traveler_status']!='approved') go('Great! First, tell the HOA about your trips. Chanelly will show you each step.','?p=traveler_apply');
go($m=='traveler'?'Great! Chanelly will show you each step of your trips.':'Great! Chanelly will show you each step.','index.php');
