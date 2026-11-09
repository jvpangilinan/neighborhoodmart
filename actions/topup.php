<?php
$amt=round((float)post('amount'),2); if($amt<=0) go('Enter an amount.','?p=wallet');
$m=post('method')=='cash'?'cash':'gcash'; if($m=='gcash'&&post('ref')==='') go('Enter the GCash reference number.','?p=wallet');
wallet_add($me['id'],$amt,'topup',null,'Top-up request','gcash'==$m?'gcash':'cash',post('ref'),'pending');
foreach(rows("SELECT id FROM users WHERE role='admin'") as $a) notify($a['id'],"Top-up to confirm: {$me['name']} ".peso($amt),'?p=admin_wallet');
go('Top-up recorded. It becomes usable once the HOA confirms the payment.','?p=wallet');
