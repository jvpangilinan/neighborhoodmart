<?php
ex("INSERT INTO disputes(trip_id,user_id,message) VALUES(?,?,?)",'iis',$id,$me['id'],post('msg'));
go('Dispute sent to the HOA.','?p=trip&id='.$id);
