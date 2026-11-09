<?php // SCREEN: post a trip (approved travelers). Stores/areas = the traveler's admin-verified options.
$stores=rows("SELECT s.* FROM stores s JOIN traveler_stores ts ON ts.store_id=s.id WHERE ts.user_id=? AND s.active=1 AND s.bulk_ok<>'not_allowed' ORDER BY s.name",'i',$me['id']);
$areas=rows("SELECT a.* FROM areas a JOIN traveler_areas ta ON ta.area_id=a.id WHERE ta.user_id=? AND a.active=1",'i',$me['id']);
$tp=row("SELECT * FROM traveler_profiles WHERE user_id=?",'i',$me['id']);
if($me['host_ban_until']&&strtotime($me['host_ban_until'])>time()){ echo '<div class="warn">You cannot host trips until '.when($me['host_ban_until']).' because of a late dropout.</div>'; return; } ?>
<div class="card mid"><h2>Post a trip</h2>
<?php if(!$stores){ echo '<p>You have no verified stores yet. Update your <a href="?p=traveler_apply">traveler registration</a> or ask the HOA to add your stores.</p></div>'; return; } ?>
<?=form('post_trip')?><div class="f wide">
<label class="full">Trip title<input name="title" required placeholder="e.g. Saturday S&R run"></label>
<label>Store<select name="store_id"><?php foreach($stores as $s) echo opt($s['id'],$s['name'].($s['card_required']?' (card)':'').($s['bulk_ok']=='unverified'?' – rules unverified':'')); ?></select></label>
<label>Area / branch<select name="area_id"><option value="">—</option><?php foreach($areas as $a) echo opt($a['id'],$a['name']); ?></select></label>
<label>Trip date<input type="date" name="go_date" required min="<?=date('Y-m-d')?>"></label>
<label>Request window closes<input type="datetime-local" name="closes" required></label>
<label>Vehicle <small>(sets the capacity)</small><select name="vehicle"><?php foreach(VEHICLES as $k=>[$n,$c]) echo opt($k,"$n – $c load units",$tp['vehicle']??'sedan'); ?></select></label>
<label>Trip expenses F (₱) <small>gas, toll, parking; shared proportionally</small><input type="number" step="0.01" min="0" name="expenses" value="0"></label>
<div class="full msg" style="margin:0"><b>Your earnings:</b> each requester also pays a Traveler Effort Fee on their order: <?=h(tier_label())?>.</div>
<div class="full"><button data-tour="publish">Publish trip</button></div></div></form></div>
