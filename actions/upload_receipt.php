<?php
// ACTION: traveler uploads one or more receipt photos (while shopping or after). Saved by services/uploads.php.
$t=row("SELECT * FROM trips WHERE id=?",'i',$id);
if(!trip_owner($t)||!in_array($t['status'],['shopping','delivered','settled'])) go('Receipts can be added once shopping starts.','?p=trip&id='.$id);
$files=$_FILES['photos']??null; $n=0;
if($files&&is_array($files['name'])) foreach($files['name'] as $i=>$nm){
 $f=save_upload(['name'=>$nm,'type'=>$files['type'][$i],'tmp_name'=>$files['tmp_name'][$i],'error'=>$files['error'][$i],'size'=>$files['size'][$i]]);
 if($f){ ex("INSERT INTO trip_receipts(trip_id,uploaded_by,file,note) VALUES(?,?,?,?)",'iiss',$id,$me['id'],$f,post('note')); $n++; }
}
if(!$n) go('Choose at least one photo.','?p=trip&id='.$id);
foreach(rows("SELECT user_id FROM trip_members WHERE trip_id=? AND status IN('funded','settled')",'i',$id) as $m) notify($m['user_id'],"Receipt photo added to \"{$t['title']}\".","?p=trip&id=$id");
audit("uploaded $n receipt(s) for trip $id"); go("$n receipt photo(s) uploaded.",'?p=trip&id='.$id);
