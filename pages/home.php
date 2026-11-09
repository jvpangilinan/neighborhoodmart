<?php // SCREEN: home. Each user type has its own home in views/home/<type>.php
$shell=!$me?'guest':($mod?'admin':($mode?:'requester'));
foreach(rows("SELECT * FROM announcements ORDER BY id DESC LIMIT 1") as $n) echo '<div class="card notice"><b>📢 '.h($n['title']).'</b><br>'.nl2br(h($n['body'])).'</div>';
require ROOT."/views/home/$shell.php";
