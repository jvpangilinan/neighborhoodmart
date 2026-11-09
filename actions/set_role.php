<?php
// ACTION: admin changes a user's permission role.
if($me['role']!='admin') go('Only the main admin can change roles.','?p=admin_users');
$r=in_array(post('role'),['member','admin'])?post('role'):'member';
if($id==$me['id']) go('You cannot change your own role.','?p=admin_users');
ex("UPDATE users SET role=? WHERE id=?",'si',$r,$id); audit("set role of $id to $r"); go('Role updated.','?p=admin_users');
