<?php // SCREEN (HOA/admin): disputes list + resolve (HOA has the final say). ?>
<div class="card"><h2>Disputes</h2>
<?php $ds=rows("SELECT d.*,u.name,t.title FROM disputes d JOIN users u ON u.id=d.user_id JOIN trips t ON t.id=d.trip_id ORDER BY d.status,d.id DESC"); if(!$ds) echo '<div class="empty"><b>No disputes</b></div>';
foreach($ds as $d) echo '<div style="border-bottom:1px solid var(--line);padding:12px 0"><b>'.h($d['name']).'</b> on <a href="?p=trip&id='.$d['trip_id'].'">'.h($d['title']).'</a> '.tag($d['status']).' <small>'.when($d['created_at']).'</small><p>'.nl2br(h($d['message'])).'</p>'
 .($d['status']=='open'?form('resolve_dispute',hid('id',$d['id']),'class="inline"').'<input name="res" class="grow" placeholder="Decision" required><button class="sm">Resolve</button></form>':'<small>Decision: '.h($d['resolution']).'</small>').'</div>'; ?></div>
