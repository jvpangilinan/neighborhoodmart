<?php
// ACTION: admin confirms that a GCash/cash top-up arrived or a cash-out was sent.
$tx=row("SELECT * FROM wallet_tx WHERE id=? AND status='pending'",'i',$id); if(!$tx) go('Already handled.','?p=admin_wallet');
$d=post('decision')=='reject'?'rejected':'done';
ex("UPDATE wallet_tx SET status=? WHERE id=?",'si',$d,$id);
notify($tx['user_id'],ucfirst($tx['kind']).' of '.peso(abs($tx['amount'])).($d=='done'?' confirmed.':' was rejected. Contact the HOA.'),'?p=wallet');
audit("wallet tx $id $d"); go('Saved.','?p=admin_wallet');
