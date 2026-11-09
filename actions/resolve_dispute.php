<?php
ex("UPDATE disputes SET status='resolved',resolution=? WHERE id=?",'si',post('res'),$id);
$d=row("SELECT * FROM disputes WHERE id=?",'i',$id); notify($d['user_id'],'Your dispute was resolved: '.post('res'),'?p=trip&id='.$d['trip_id']);
audit("resolved dispute $id"); go('Dispute resolved.','?p=admin_disputes');
