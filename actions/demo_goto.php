<?php
// ACTION (Demo mode): roll back / jump. Rebuilds the tour data to a checkpoint, logs in as the right person, opens the right page.
demo_on();
$need=max(0,min(16,(int)post('need'))); $email=post('email'); $who=post('who');
$tid=demo_build($email,$need);
$to=str_replace('{trip}',(string)$tid,post('to','index.php')); if(!preg_match('/^(index\.php|\?p=)[\w=&\-]*$/',$to)) $to='index.php';
$to.=(str_contains($to,'?')?'&':'?').'tt='.$tid;
if($who=='guest'){ $_SESSION=[]; session_regenerate_id(true); $_SESSION['csrf']=bin2hex(random_bytes(16)); }
else {
 $u=in_array($who,['tour','tour0'])?row("SELECT * FROM users WHERE email=?",'s',$email):demo_user($who);
 if(!$u) go('Demo account not found. Restart the demo.','index.php');
 demo_login($u,$who=='tour'?'traveler':($who=='tour0'||$who=='admin'?null:'requester'));
}
header('Location: '.$to); exit;
