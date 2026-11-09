<?php
// ACTION: traveler reports today's shelf price (section 5). Price is entered per pack, saved per unit.
$it=row("SELECT * FROM items WHERE id=?",'i',(int)post('item_id'));
$sid=(int)post('store_id'); $pack=(float)post('pack_price');
if(!$it||$pack<=0||!val("SELECT 1 FROM traveler_stores WHERE user_id=? AND store_id=?",'ii',$me['id'],$sid)) go('Pick an item, one of your stores, and the price.','?p=price_check');
$unit=round($pack/max(1,$it['pack_size']),2);
ex("INSERT INTO item_prices(item_id,store_id,unit_price,brand,checked_by,checked_at) VALUES(?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE unit_price=VALUES(unit_price),brand=VALUES(brand),checked_by=VALUES(checked_by),checked_at=NOW()",
 'iidsi',$it['id'],$sid,$unit,post('brand'),$me['id']);
audit("price check item {$it['id']} store $sid = $unit"); go("Price saved: {$it['name']} ".peso($pack)." per {$it['packaging']}.",'?p=price_check');
