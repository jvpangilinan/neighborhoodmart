<?php
// ACTION: "I need this" on the demand survey (qty 0 removes the vote).
$q=max(0,(int)post('qty'));
if($q) ex("REPLACE INTO survey_votes(user_id,item_id,qty) VALUES(?,?,?)",'iii',$me['id'],$id,$q);
else ex("DELETE FROM survey_votes WHERE user_id=? AND item_id=?",'ii',$me['id'],$id);
go($q?'Thanks, your need was counted.':'Vote removed.','?p=demand');
