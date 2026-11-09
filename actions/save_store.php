<?php
$b=in_array(post('bulk_ok'),['unverified','allowed','not_allowed'])?post('bulk_ok'):'unverified';
ex("UPDATE stores SET name=?,card_required=?,bulk_ok=?,active=?,rules_note=? WHERE id=?",'sisisi',post('name'),isset($_POST['card'])?1:0,$b,isset($_POST['active'])?1:0,post('note'),$id);
audit("updated store $id ($b)"); go('Store saved.','?p=admin_catalog');
