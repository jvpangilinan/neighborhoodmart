<?php
if(!password_verify($_POST['old']??'',$me['pass'])) go('Current password is wrong.','?p=account');
if(strlen(post('new'))<8) go('New password must be at least 8 characters.','?p=account');
ex("UPDATE users SET pass=?,must_change_pw=0 WHERE id=?",'si',password_hash(post('new'),PASSWORD_DEFAULT),$me['id']);
audit('changed own password'); go('Password changed.','?p=account');
