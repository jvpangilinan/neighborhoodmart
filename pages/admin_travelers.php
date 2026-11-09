<?php // SCREEN (HOA/admin): review traveler registrations; turn typed-in stores/areas into options. ?>
<?php $ps=rows("SELECT p.*,u.name,u.traveler_status,u.host_ban_until,u.id uid FROM traveler_profiles p JOIN users u ON u.id=p.user_id ORDER BY FIELD(u.traveler_status,'pending','approved','rejected'),p.submitted_at DESC"); ?>
<div class="card"><h2>Traveler registrations</h2><?php if(!$ps) echo '<div class="empty"><b>No traveler registrations yet</b></div>'; ?></div>
<?php foreach($ps as $p){ $st=array_column(rows("SELECT s.name FROM traveler_stores ts JOIN stores s ON s.id=ts.store_id WHERE ts.user_id=?",'i',$p['uid']),'name');
 $ar=array_column(rows("SELECT a.name FROM traveler_areas ta JOIN areas a ON a.id=ta.area_id WHERE ta.user_id=?",'i',$p['uid']),'name'); ?>
<div class="card" data-user="<?=h(val("SELECT email FROM users WHERE id=?",'i',$p['uid']))?>"><div class="page-head"><h3 style="margin:0"><?=h($p['name'])?> <?=tag($p['traveler_status'])?></h3><small>Submitted <?=when($p['submitted_at'])?></small></div>
<div class="grid">
<div><small>STORES</small><br><?=h(implode(', ',$st)?:'—')?><?=$p['other_stores']?'<br><b>Other:</b> '.h($p['other_stores']):''?>
<?php if($p['other_stores']) echo form('add_option',hid('id',$p['uid']).hid('kind','store').hid('back','?p=admin_travelers'),'class="inline" style="margin-top:6px"').'<input name="name" value="'.h($p['other_stores']).'" class="grow"><label class="check"><input type="checkbox" name="card"> card</label><button class="sm s">Add as store option</button></form>'; ?></div>
<div><small>AREAS</small><br><?=h(implode(', ',$ar)?:'—')?><?=$p['other_areas']?'<br><b>Other:</b> '.h($p['other_areas']):''?>
<?php if($p['other_areas']) echo form('add_option',hid('id',$p['uid']).hid('kind','area').hid('back','?p=admin_travelers'),'class="inline" style="margin-top:6px"').'<input name="name" value="'.h($p['other_areas']).'" class="grow"><button class="sm s">Add as area option</button></form>'; ?></div>
<div><small>MEMBERSHIP</small><br>Active membership: <?=$p['has_membership']?'Yes ('.h($p['membership_store']).')':'No'?><br>Valid card: <?=$p['has_card']?'Yes'.($p['card_expiry']?' until '.h($p['card_expiry']):''):'No'?><br>Vehicle: <?=h(VEHICLES[$p['vehicle']][0])?></div>
</div>
<?=form('traveler_decide',hid('id',$p['uid']),'class="inline" style="margin-top:14px"')?><input name="note" class="grow" placeholder="Note to traveler (e.g. verified S&R allows buying for others)" value="<?=h($p['admin_note'])?>"><button class="sm" name="decision" value="approve" data-tour="approve">Approve traveler</button><button class="sm danger" name="decision" value="reject" data-confirm="Reject this traveler application?">Reject</button></form>
</div><?php } ?>
