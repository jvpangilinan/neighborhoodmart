<?php // RAW (HOA Admin only, see config/routes.php): show a resident's proof-of-residence file.
send_upload(val("SELECT proof_file FROM users WHERE id=?",'i',(int)($_GET['id']??0)));
