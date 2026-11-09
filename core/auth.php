<?php
// SESSION, CURRENT USER, ROLE MODE, ACCESS CHECKS, AUDIT LOG.
session_start();
$_SESSION['csrf']=$_SESSION['csrf']??bin2hex(random_bytes(16));
$me=isset($_SESSION['u'])?row("SELECT * FROM users WHERE id=?",'i',$_SESSION['u']):null;
if(!$me) unset($_SESSION['u'],$_SESSION['mode']);
$mod=$me&&$me['role']==='admin';
$mode=$mod?'admin':($_SESSION['mode']??null);    // 'admin', 'traveler' or 'requester' -> decides which app (UI) is shown
$verified=$me&&$me['verify_status']==='approved';
$isTraveler=$me&&$me['traveler_status']==='approved';
// rule names used in config/routes.php
function can($rule){global $me,$mod,$verified,$isTraveler;
 return match($rule){0=>true,'login'=>(bool)$me,'verified'=>$verified,'traveler'=>$verified&&$isTraveler,'mod'=>$mod,default=>false};}
function trip_owner($t){global $me,$mod;return $t&&$me&&($t['traveler_id']==$me['id']||$mod);}
function audit($a){global $me;ex("INSERT INTO audit(user_id,action) VALUES(?,?)",'is',$me['id']??0,$a);}
function notify($uid,$msg,$link=''){ex("INSERT INTO notifications(user_id,msg,link) VALUES(?,?,?)",'iss',$uid,$msg,$link);}
