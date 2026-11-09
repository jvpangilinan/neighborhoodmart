<?php
// WALLET = LEDGER CREDITS. The app records money moving between neighbors; it does not hold real money.
// Top-ups and cash-outs are real GCash/cash transfers that an admin confirms.
function wallet_balance($uid){ return (float)val("SELECT COALESCE(SUM(amount),0) FROM wallet_tx WHERE user_id=? AND (status='done' OR (status='pending' AND amount<0))",'i',$uid); }
function wallet_add($uid,$amt,$kind,$trip=null,$note='',$method='system',$ref='',$status='done'){
 if(abs($amt)<0.005&&$status=='done') return;
 ex("INSERT INTO wallet_tx(user_id,trip_id,amount,kind,method,ref,status,note) VALUES(?,?,?,?,?,?,?,?)",'iidsssss',$uid,$trip,round($amt,2),$kind,$method,$ref,$status,$note);
}
