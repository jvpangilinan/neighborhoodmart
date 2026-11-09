<?php
require __DIR__.'/_upload.php';
$f=save_proof($_FILES['proof']??[]); if(!$f) go('Choose a file first.','?p=account');
ex("UPDATE users SET proof_file=? WHERE id=?",'si',$f,$me['id']); go('Proof uploaded. The HOA will review it.','?p=account');
