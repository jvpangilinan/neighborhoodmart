<?php
// ACTION: turn Guided mode on/off (Aa panel switch or "Turn off" in Chanelly's bubble).
$on=post('on')==='1'?1:0; ex("UPDATE users SET guided=? WHERE id=?",'ii',$on,$me['id']);
$back=post('back'); if(!preg_match('/^(index\.php|\?p=)[\w=&\-]*$/',$back)) $back='index.php';
go($on?'Guided mode is on. Chanelly will point to your next step.':'Guided mode is off. Turn it back on anytime from the Aa button.',$back);
