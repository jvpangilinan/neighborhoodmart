<?php
// PACK-SIZE CONSOLIDATION (e.g. a box of 8). Wrong leftover/round-up/trim result? Edit ONLY this file.
// Leftover units (total % pack): if anyone ticked "round up" the pack is completed by them (latest first);
// otherwise the leftover is trimmed from the latest requests.
function consolidate_trip($id){
 foreach(rows("SELECT DISTINCT l.item_id,i.pack_size p FROM order_lines l JOIN items i ON i.id=l.item_id WHERE l.trip_id=? AND l.status IN('matched','trimmed')",'i',$id) as $x){
  $L=rows("SELECT id,qty,accept_roundup FROM order_lines WHERE trip_id=? AND item_id=? AND status IN('matched','trimmed') ORDER BY id",'ii',$id,$x['item_id']);
  foreach($L as $k=>$l) $L[$k]['f']=$l['qty'];
  $p=max(1,(int)$x['p']); $left=array_sum(array_column($L,'qty'))%$p;
  if($left){
   $ru=[]; foreach($L as $k=>$l) if($l['accept_roundup']) $ru[]=$k;
   if($ru){ $need=$p-$left; $i=count($ru)-1; while($need-->0){$L[$ru[$i]]['f']++; $i=($i+count($ru)-1)%count($ru);} }
   else { $cut=$left; for($k=count($L)-1;$k>=0&&$cut>0;$k--){$d=min($cut,$L[$k]['f']);$L[$k]['f']-=$d;$cut-=$d;} }
  }
  foreach($L as $l) ex("UPDATE order_lines SET final_qty=?,status=?,reason=? WHERE id=?",'issi',$l['f'],$l['f']?'matched':'trimmed',
   $l['f']==$l['qty']?'':($l['f']>$l['qty']?'Rounded up to complete a pack':'Trimmed: not enough to complete a pack'),$l['id']);
 }
}
