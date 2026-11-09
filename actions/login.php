<?php
// ACTION: log in with email or username, then open the right app:
// admin -> HOA Admin dashboard; others -> their last used app (Traveler/Requester), or the role picker the first time.
$u=row("SELECT * FROM users WHERE email=? OR username=?",'ss',post('email'),post('email'));
if(!$u||!password_verify($_POST['pass']??'',$u['pass'])) go('Wrong email/username or password.','?p=login');
session_regenerate_id(true); $_SESSION['u']=$u['id']; unset($_SESSION['mode']);
if($u['role']==='admin') go('Welcome, '.$u['name'].'.','index.php');
if($u['last_mode']){ $_SESSION['mode']=$u['last_mode']; go('Welcome back, '.explode(' ',$u['name'])[0].'.','index.php'); }
go('Welcome, '.$u['name'].'.','?p=choose_role');
