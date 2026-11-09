<?php
// ACTION (Demo mode): log out, clear the previous tour's data, start fresh on the home page.
demo_on(); demo_reset(); $_SESSION=[]; session_regenerate_id(true); $_SESSION['csrf']=bin2hex(random_bytes(16));
header('Location: index.php?tour=1'); exit;
