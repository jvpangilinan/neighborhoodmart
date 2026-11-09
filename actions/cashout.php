<?php
$amt=round((float)post('amount'),2); if($amt<=0) go('Enter an amount.','?p=wallet');
if($amt>wallet_balance($me['id'])) go('Amount is more than your balance.','?p=wallet');
wallet_add($me['id'],-$amt,'cashout',null,'Cash-out to '.post('dest'),'gcash','','pending');
go('Cash-out requested. The HOA sends it and confirms.','?p=wallet');
