<?php // SCREEN (HOA/admin): stores (bulk-buying verification), areas, generic catalog, item suggestions. ?>
<div class="card"><h2>Stores</h2><p class="muted">Verify with each store whether one member may buy bulk for several families. "Not allowed" stores cannot host trips.</p>
<?php foreach(rows("SELECT * FROM stores ORDER BY name") as $s) echo form('save_store',hid('id',$s['id'])).'<div class="f wide" style="border-bottom:1px solid var(--line);padding:12px 0">'
 .'<label>Name<input name="name" value="'.h($s['name']).'"></label><label>Bulk buying for others<select name="bulk_ok">'.opt('unverified','Unverified',$s['bulk_ok']).opt('allowed','Allowed (confirmed)',$s['bulk_ok']).opt('not_allowed','Not allowed',$s['bulk_ok']).'</select></label>'
 .'<div class="stack" style="align-self:end"><label class="check"><input type="checkbox" name="card"'.($s['card_required']?' checked':'').'> Membership card required</label><label class="check"><input type="checkbox" name="active"'.($s['active']?' checked':'').'> Active option</label></div>'
 .'<label class="full">Store rules (confirmed in writing)<textarea name="note">'.h($s['rules_note']).'</textarea></label><div><button class="sm">Save store</button></div></div></form>'; ?>
<div class="grid" style="margin-top:16px">
<?=form('add_option',hid('kind','store'))?><div class="f"><label>New store<input name="name" required></label><label class="check"><input type="checkbox" name="card"> Card required</label><button class="sm s">Add store</button></div></form>
<?=form('add_option',hid('kind','area'))?><div class="f"><label>New area / branch<input name="name" required></label><button class="sm s">Add area</button></div><small>Areas: <?=h(implode(', ',array_column(rows("SELECT name FROM areas WHERE active=1"),'name')))?></small></form>
</div></div>
<?php $sg=rows("SELECT s.*,u.name uname FROM item_suggestions s JOIN users u ON u.id=s.user_id WHERE s.status='new'"); if($sg){ ?>
<div class="card"><h3>Item suggestions from residents</h3><table>
<?php foreach($sg as $x) echo '<tr><td><b>'.h($x['name']).'</b> <small>'.h($x['packaging']).' · by '.h($x['uname']).'</small></td><td>'.form('save_item',hid('id',0).hid('sugg',$x['id']).hid('name',$x['name']).hid('packaging',$x['packaging']).hid('pack_size',1).hid('load_units',1).hid('max',0),'class="inline"').'<button class="sm">Add to catalog</button></form></td><td>'.form('suggestion',hid('id',$x['id'])).'<button class="sm s">Dismiss</button></form></td></tr>'; ?>
</table></div><?php } ?>
<div class="card"><h2>Generic catalog</h2><p class="muted">"Supermarket ₱/unit" is the regular neighborhood price, used for the "You saved" figure. Name items by kind and packaging, not brand (e.g. "Fresh Eggs Large – 1 tray of 30"). Travelers record the brand they find.</p>
<div class="x"><table><tr><th>Item</th><th>Packaging</th><th>Section</th><th class="n">Pack</th><th class="n">Load</th><th class="n">Max</th><th class="n">Supermarket ₱/unit</th><th>Active</th><th></th></tr>
<?php foreach(rows("SELECT * FROM items ORDER BY section,name") as $i){ $f='it'.$i['id'];
 echo '<tr><td><input form="'.$f.'" name="name" value="'.h($i['name']).'"></td><td><input form="'.$f.'" name="packaging" value="'.h($i['packaging']).'"></td><td><input form="'.$f.'" name="section" value="'.h($i['section']).'" style="width:110px"><input form="'.$f.'" type="hidden" name="category" value="'.h($i['category']).'"></td>'
 .'<td class="n"><input form="'.$f.'" type="number" name="pack_size" value="'.$i['pack_size'].'" style="width:64px"></td><td class="n"><input form="'.$f.'" type="number" name="load_units" value="'.$i['load_units'].'" style="width:64px"></td><td class="n"><input form="'.$f.'" type="number" name="max" value="'.$i['max_per_member'].'" style="width:64px"></td><td class="n"><input form="'.$f.'" type="number" step="0.01" name="retail" value="'.$i['retail_price'].'" style="width:90px"></td>'
 .'<td><input form="'.$f.'" type="checkbox" name="active"'.($i['active']?' checked':'').'></td><td>'.form('save_item',hid('id',$i['id']),'id="'.$f.'"').'<button class="sm s">Save</button></form></td></tr>'; } ?>
</table></div>
<h4>Add item</h4><?=form('save_item',hid('id',0))?><div class="f wide">
<label>Item<input name="name" required placeholder="Whole Fresh Milk 1L"></label><label>Packaging<input name="packaging" placeholder="case of 6"></label><label>Store section<input name="section" placeholder="Dairy"></label><label>Category<input name="category"></label>
<label>Units per pack<input type="number" name="pack_size" value="1" min="1"></label><label>Load units per unit<input type="number" name="load_units" value="1" min="1"></label><label>Max per member (0 = none)<input type="number" name="max" value="0" min="0"></label><label>Regular supermarket price per unit (₱)<input type="number" step="0.01" name="retail" value="0"></label>
<div style="align-self:end"><button>Add item</button></div></div></form></div>
