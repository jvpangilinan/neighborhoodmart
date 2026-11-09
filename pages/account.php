<?php // SCREEN: my account, verification status, proof upload, password. ?>
<div class="grid">
<div class="card"><h3>My account</h3>
<p><b><?=h($me['name'])?></b><br>Blk <?=h($me['block'])?> Lot <?=h($me['lot'])?> <?=h($me['street'])?><br><?=h($me['email'])?><?=$me['username']?' · username '.h($me['username']):''?></p>
<p>Residency: <?=tag($me['verify_status'])?><br>Traveler: <?=tag($me['traveler_status'])?></p>
<?php if($me['verify_status']!='approved'): ?><small><?=h($me['verify_note'])?></small><?php endif; ?>
<?php if($me['host_ban_until']&&strtotime($me['host_ban_until'])>time()) echo '<div class="warn">Hosting ban until '.when($me['host_ban_until']).'</div>'; ?>
</div>
<div class="card"><h3>Proof of residence</h3>
<p class="muted"><?=$me['proof_file']?'A file is on record. Upload again to replace it.':'No file yet. Upload a utility bill or village ID so the HOA can verify you.'?></p>
<?=form('upload_proof','','enctype="multipart/form-data"')?><div class="f"><input type="file" name="proof" accept=".jpg,.jpeg,.png,.webp,.pdf" required><button class="s">Upload</button></div></form></div>
<div class="card"><h3>Change password</h3><?=form('change_pw')?><div class="f">
<label>Current password<input type="password" name="old" required></label>
<label>New password <small>(8+ characters)</small><input type="password" name="new" required minlength="8"></label>
<button>Save password</button></div></form></div>
</div>
