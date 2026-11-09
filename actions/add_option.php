<?php
// ACTION: add a store or area to the options list (optionally attach it to the traveler who mentioned it).
$k=post('kind'); $name=post('name'); if($name==='') go('Type a name.');
if($k=='store'){ $oid=ex("INSERT INTO stores(name,card_required) VALUES(?,?)",'si',$name,isset($_POST['card'])?1:0);
 if($id) ex("INSERT IGNORE INTO traveler_stores VALUES(?,?)",'ii',$id,$oid); }
else { $oid=ex("INSERT INTO areas(name) VALUES(?)",'s',$name); if($id) ex("INSERT IGNORE INTO traveler_areas VALUES(?,?)",'ii',$id,$oid); }
audit("added $k option $name"); go(ucfirst($k).' added to the options.',post('back')?:'?p=admin_catalog');
