<?php
if(post('name')==='') go('Type the item.','?p=demand');
ex("INSERT INTO item_suggestions(user_id,name,packaging) VALUES(?,?,?)",'iss',$me['id'],post('name'),post('packaging'));
go('Suggestion sent. The admin adds it to the catalog.','?p=demand');
