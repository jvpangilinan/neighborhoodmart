<?php // SCREEN (HOA/admin): verification queue (human-in-the-loop), HOA master list, all residents. ?>
<?php $q=rows("SELECT * FROM users WHERE verify_status='pending' ORDER BY match_score DESC,id"); ?>
<div class="card"><div class="page-head"><div><h2>Pending manual review</h2><p>Exact HOA matches were approved automatically. These need a human decision. The system never rejects on its own.</p></div><?=tag(count($q).' waiting')?></div>
<?php if(!$q) echo '<div class="empty"><b>Queue is empty</b>Everyone is reviewed.</div>'; else { ?>
<div class="x"><table><tr><th>Registered as</th><th>Closest HOA record</th><th class="n">Score</th><th>Proof</th><th>Decision</th></tr>
<?php foreach($q as $u) echo '<tr data-user="'.h($u['email']).'"><td><b>'.h($u['name']).'</b><br><small>Blk '.h($u['block']).' Lot '.h($u['lot']).' · '.h($u['email']).'</small></td><td><small>'.h($u['verify_note']).'</small></td><td class="n">'.tag($u['match_score'].'%').'</td>'
 .'<td>'.($u['proof_file']?'<a href="?p=proof&id='.$u['id'].'" target="_blank">View file</a>':'<small>none</small>').'</td>'
 .'<td>'.form('verify_user',hid('id',$u['id']),'class="inline"').'<input name="note" placeholder="Note (optional)" style="width:150px"><button class="sm" name="decision" value="approve" data-tour="approve">Approve</button><button class="sm danger" name="decision" value="reject" data-confirm="Reject this resident? They will not be able to join trips.">Reject</button></form></td></tr>'; ?>
</table></div><?php } ?></div>
<div class="grid">
<div class="card"><h3>HOA master resident list</h3><p class="muted"><?=val("SELECT COUNT(*) FROM hoa_residents")?> records. Add one per line as <b>Full Name, Block, Lot</b> or upload a CSV. People waiting are re-checked automatically.</p>
<?=form('hoa_import','','enctype="multipart/form-data"')?><div class="f"><textarea name="lines" placeholder="Juan Roberto Dela Cruz Jr., 3, 12"></textarea><input type="file" name="csv" accept=".csv,.txt"><button class="s">Add records</button></div></form>
<details style="margin-top:12px"><summary><small>Show records</small></summary><table><?php foreach(rows("SELECT * FROM hoa_residents ORDER BY block+0,lot+0") as $r) echo '<tr><td>'.h($r['full_name']).'</td><td>Blk '.h($r['block']).' Lot '.h($r['lot']).'</td></tr>'; ?></table></details></div>
<div class="card"><h3>All residents</h3><div class="x"><table><tr><th>Name</th><th>Residency</th><th>Traveler</th><th>Role</th></tr>
<?php foreach(rows("SELECT * FROM users ORDER BY name") as $u) echo '<tr><td>'.h($u['name']).'<br><small>Blk '.h($u['block']).' Lot '.h($u['lot']).'</small></td><td>'.tag($u['verify_status']).'</td><td>'.tag($u['traveler_status']).'</td><td>'
 .($u['id']!=$me['id']?form('set_role',hid('id',$u['id']),'class="inline"').'<select name="role" onchange="this.form.submit()">'.opt('member','member',$u['role']).opt('admin','admin',$u['role']).'</select></form>':h($u['role'])).'</td></tr>'; ?>
</table></div></div></div>
