<?php
$tid=(int)val("SELECT trip_id FROM order_lines WHERE id=?",'i',$id);
$e=cancel_line($id,$me['id']); go($e?:'Request cancelled.','?p=trip&id='.$tid);
