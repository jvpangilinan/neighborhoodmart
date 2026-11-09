<?php
$d=post('decision')=='reject'?'rejected':'approved';
ex("UPDATE users SET traveler_status=? WHERE id=?",'si',$d,$id);
ex("UPDATE traveler_profiles SET admin_note=? WHERE user_id=?",'si',post('note'),$id);
notify($id,$d=='approved'?'You are now an approved Traveler. You can post trips and check prices.':'Your traveler profile was not approved: '.post('note'),'?p=traveler_apply');
audit("traveler $d user $id"); go('Traveler '.$d.'.','?p=admin_travelers');
