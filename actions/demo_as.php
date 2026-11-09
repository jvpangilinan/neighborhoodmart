<?php
// ACTION (Demo mode): Chanelly switches to another account mid-tour (and may make bot neighbors act first).
demo_on();
$who=post('who'); $to=post('to','index.php'); if(!preg_match('/^(index\.php|\?p=)[\w=&\-]*$/',$to)) $to='index.php';
$tid=(int)post('trip');
if($tid&&post('assist')=='join') demo_bot_join($tid);
if($tid&&post('assist')=='fund') demo_bot_fund($tid);
if($tid&&post('assist')=='shop_rest') demo_shop_rest($tid);
if($who=='tour'){ $u=row("SELECT * FROM users WHERE email=? AND email LIKE 'tour-%@demo.ph'",'s',post('email')); $mode='traveler'; }
else { $u=row("SELECT * FROM users WHERE username=? AND username IN('admin','requester','requester2','traveler')",'s',$who); $mode=$u&&$u['role']=='admin'?null:($who=='traveler'?'traveler':'requester'); }
if(!$u) go('Demo account not found. Restart the demo.','index.php');
demo_login($u,$mode); header('Location: '.$to); exit;
