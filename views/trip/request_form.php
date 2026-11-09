<?php // TRIP SECTION: add an item (requesters, while open). ?>
<?php if(!$me||$t['status']!='open'||$t['traveler_id']==$me['id']) return;
if(!$verified){ echo '<div class="card"><small>Joining trips unlocks after HOA verification (rule R1).</small></div>'; return; }
$its=rows("SELECT i.*,p.unit_price,p.brand,p.checked_at FROM items i JOIN item_prices p ON p.item_id=i.id AND p.store_id=? WHERE i.active=1 ORDER BY i.section,i.name",'i',$t['store_id']); ?>
<div class="card" data-tour="request-form"><h3>🛒 Order something</h3>
<?=form('request_item',hid('id',$id))?><div class="f wide">
<label class="full">What do you need?<select name="item"><?php foreach($its as $i) echo opt($i['id'],$i['name'].' ('.$i['packaging'].') – '.peso($i['unit_price']).' each'.($i['max_per_member']?' (max '.$i['max_per_member'].')':'')); ?></select></label>
<label>How many?<input type="number" name="qty" value="1" min="1" inputmode="numeric"></label>
<div class="stack" style="align-self:end">
<label class="check"><input type="checkbox" name="ru"> If it doesn't fill a whole box, I'm OK buying a few extra</label>
<label class="check"><input type="checkbox" name="sub" checked> OK to get a different brand of the same size</label></div>
<div class="full"><small>You pay later, when the traveler stops taking orders. You can cancel anytime before that.</small></div>
<div class="full"><button data-tour="add-to-pool">Add to pool</button></div></div></form></div>
