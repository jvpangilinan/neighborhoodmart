<?php // SCREEN: traveler registration (section 3). Admin reviews it and adds new stores/areas to the options.
$tp=row("SELECT * FROM traveler_profiles WHERE user_id=?",'i',$me['id']);
$mySt=array_column(rows("SELECT store_id FROM traveler_stores WHERE user_id=?",'i',$me['id']),'store_id');
$myAr=array_column(rows("SELECT area_id FROM traveler_areas WHERE user_id=?",'i',$me['id']),'area_id'); ?>
<div class="card mid"><div class="page-head"><div><h2>Traveler registration</h2><p>Tell the HOA about your bulk-buying trips. Status: <?=tag($me['traveler_status'])?></p></div></div>
<?php if($tp&&$tp['admin_note']) echo '<div class="msg">HOA note: '.h($tp['admin_note']).'</div>'; ?>
<?=form('traveler_apply')?><div class="f" style="max-width:none">
<fieldset><legend>1. What stores do you regularly visit for bulk purchases?</legend><div class="checks">
<?php foreach(rows("SELECT * FROM stores WHERE active=1 ORDER BY name") as $s) echo '<label class="check"><input type="checkbox" name="stores[]" value="'.$s['id'].'"'.(in_array($s['id'],$mySt)?' checked':'').'> '.h($s['name']).'</label>'; ?>
</div><label style="margin-top:10px">Other stores (the admin will add them as options)<input name="other_stores" value="<?=h($tp['other_stores']??'')?>" placeholder="e.g. PureGold Price Club"></label></fieldset>
<fieldset><legend>2. Membership</legend><div class="stack">
<label class="check"><input type="checkbox" name="has_membership" <?=!empty($tp['has_membership'])?'checked':''?>> I have an active membership or subscription</label>
<label>Which store's membership?<input name="membership_store" value="<?=h($tp['membership_store']??'')?>" placeholder="e.g. S&R"></label>
<label class="check"><input type="checkbox" name="has_card" <?=!empty($tp['has_card'])?'checked':''?>> I own a valid membership card</label>
<label>Card expiry <small>(optional)</small><input type="date" name="card_expiry" value="<?=h($tp['card_expiry']??'')?>"></label></div></fieldset>
<fieldset><legend>3. What areas do you usually travel to?</legend><div class="checks">
<?php foreach(rows("SELECT * FROM areas WHERE active=1 ORDER BY name") as $a) echo '<label class="check"><input type="checkbox" name="areas[]" value="'.$a['id'].'"'.(in_array($a['id'],$myAr)?' checked':'').'> '.h($a['name']).'</label>'; ?>
</div><label style="margin-top:10px">Other areas<input name="other_areas" value="<?=h($tp['other_areas']??'')?>"></label></fieldset>
<fieldset><legend>4. Usual vehicle</legend><select name="vehicle"><?php foreach(VEHICLES as $k=>[$n,$c]) echo opt($k,"$n (up to $c load units)",$tp['vehicle']??'sedan'); ?></select></fieldset>
<div><button data-tour="traveler-submit"><?=$tp?'Update profile':'Submit for review'?></button></div></div></form></div>
