<?php
// ACTION: HOA admin makes the final decision on a resident (human-in-the-loop).
$d=post('decision')=='reject'?'rejected':'approved';
ex("UPDATE users SET verify_status=?,verify_note=CONCAT(COALESCE(verify_note,''),' | Admin: ',?) WHERE id=?",'ssi',$d,$d.(post('note')?' - '.post('note'):''),$id);
notify($id,$d=='approved'?'Your residency was verified by the HOA. You can now join trips.':'Your residency could not be verified: '.post('note'),'?p=account');
audit("$d user $id"); go('Resident '.$d.'.','?p=admin_users');
