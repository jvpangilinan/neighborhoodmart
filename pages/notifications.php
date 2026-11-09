<?php // SCREEN: in-app alerts (substitutions, deadlines, payments). Opening this page marks them read. ?>
<div class="card mid"><h2>Notifications</h2>
<?php $ns=rows("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 60",'i',$me['id']);
if(!$ns) echo '<div class="empty"><b>Nothing yet</b>Trip updates and substitution alerts show up here.</div>';
echo '<table>'; foreach($ns as $n) echo '<tr><td>'.($n['seen']?'':'<span class="tag t-new">new</span> ').($n['link']?'<a href="'.h($n['link']).'">'.h($n['msg']).'</a>':h($n['msg'])).'</td><td class="n"><small>'.when($n['created_at']).'</small></td></tr>'; echo '</table>';
ex("UPDATE notifications SET seen=1 WHERE user_id=?",'i',$me['id']); ?></div>
