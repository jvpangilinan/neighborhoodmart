<?php // GUIDED MODE: (1) the "Guided setup" question for people who haven't chosen yet, (2) the next-step hint for this screen.
// Hints come from the person's REAL situation (what they still need to do), not from a script. Words are below.
if(!$me) return;
if($me['guided']===null&&!$mod&&!in_array($p??'',['account','rules'])){ ?>
<div class="guide-setup" hidden><div class="gs-card" role="dialog" aria-modal="true" aria-labelledby="gs-t">
<img src="assets/brand/chanelly.png" alt="Chanelly">
<div><small>Guided setup</small><h2 id="gs-t">Want me to guide you, <?=h(explode(' ',$me['name'])[0])?>?</h2>
<p>I'll point to the one thing to do next on every screen, so you never have to guess. You can turn this off anytime.</p>
<div class="gs-choices">
<?=form('guided_setup',hid('choice','requester'))?><button><span>🛒</span><b>Yes: I want to order things</b><small>Requester · join a neighbor's trip and pay your share</small></button></form>
<?=form('guided_setup',hid('choice','traveler'))?><button><span>🚗</span><b>Yes: I'm the one driving to the store</b><small>Traveler · post trips and buy for neighbors</small></button></form>
<?=form('guided_setup',hid('choice','off').hid('back','index.php'))?><button class="s"><b>No thanks, I'll look around myself</b></button></form>
</div></div></div></div>
<?php return; }
if((int)$me['guided']!==1) return;
// ---------------- work out the next step ----------------
$G=[]; $pg=$p??'home'; $shell=$mod?'admin':($mode?:'requester');
$hint=function($sel,$title,$text) use(&$G){ $G[]=['sel'=>$sel,'title'=>$title,'text'=>$text]; };
$tid=(int)($_GET['id']??0);
if($shell=='requester'){
 if($pg=='home'){
  if(val("SELECT 1 FROM trip_members WHERE user_id=? AND status='unpaid'",'i',$me['id'])) $hint('[data-tour=pay]','Time to pay','A trip you joined is ready. Tap this green button to pay your share from your wallet.');
  if(val("SELECT 1 FROM order_lines l JOIN trips t ON t.id=l.trip_id WHERE l.user_id=? AND l.shop='needs_ok' AND l.status='matched' AND t.status='shopping'",'i',$me['id'])) $hint('[data-tour=approve-alert]','Your traveler is asking you','They found something a little different. Read it, then tap Approve if it\'s OK, or Decline if not.');
  if(val("SELECT 1 FROM trip_members WHERE user_id=? AND status='settled' AND pickup='waiting'",'i',$me['id'])) $hint('[data-tour=collect]','Your items are here!','Get your items from the traveler, then tap this button.');
  if(!$G){ if(val("SELECT 1 FROM trips WHERE status='open'")) $hint('[data-tour=join]','Order something','Pick a trip going to a store you like, then tap Join this trip.');
   else $hint('[data-tour=nav-demand]','No trips right now','Tell travelers what you need in the Survey. You\'ll get a message when a trip opens.'); }
  if(wallet_balance($me['id'])<300) $hint('[data-tour=nav-wallet]','Add money first','You need money in your wallet to pay for trips. Tap Wallet to add some by GCash or cash.');
 }
 if($pg=='trips') $hint('[data-tour=join]','Pick a trip','Tap Join this trip on the one you like.');
 if($pg=='trip'&&$tid){ $t=row("SELECT * FROM trips WHERE id=?",'i',$tid); $m=row("SELECT * FROM trip_members WHERE trip_id=? AND user_id=?",'ii',$tid,$me['id']);
  $lines=(int)val("SELECT COUNT(*) FROM order_lines WHERE trip_id=? AND user_id=? AND status='matched'",'ii',$tid,$me['id']);
  if($t&&$t['status']=='open'&&$t['traveler_id']!=$me['id']){
   if($lines){ $hint('[data-tour=breakdown]','Ordered! This is your estimate','You don\'t pay yet. When the traveler stops taking orders, you\'ll get a message to pay.');
    $hint('[data-tour=request-form] select','Need something else?','Pick another item here and tap Add to pool again.'); }
   else $hint('[data-tour=request-form] select','Step 1: choose an item','Tap this box and pick what you need. Then type how many and tap Add to pool.');
  }
  if($m&&$m['status']=='unpaid') $hint('[data-tour=pay-trip]','Time to pay','Check the amount, then tap Pay. Any unused money comes back to you.');
  if($t&&$t['status']=='shopping'&&val("SELECT 1 FROM order_lines WHERE trip_id=? AND user_id=? AND shop='needs_ok'",'ii',$tid,$me['id'])) $hint('[data-tour=approve-alert]','Your traveler is asking you','Read the red box. Tap Approve if it\'s OK, or Decline if not.');
  if($m&&$m['status']=='settled'&&$m['pickup']=='waiting') $hint('[data-tour=collect]','Pick up your items','After you get your items, tap I collected my items.');
  if($m&&in_array($m['status'],['funded'])&&!$G) $hint('.hint','All set for now','You already paid. Just wait. You\'ll get a message when your items arrive.');
 }
 if($pg=='wallet') $hint('[data-tour=topup] [name=amount]','Adding money','Type the amount. Send it by GCash to the HOA, type the reference number, then tap Submit top-up.');
 if($pg=='demand') $hint('[data-tour=vote]','Tell travelers what you need','Type how many you usually need, then tap I need this.');
}
if($shell=='traveler'){
 if($pg=='home'){
  if($me['traveler_status']!='approved') $hint('[data-tour=become-traveler]','First, the traveler form','Tell the HOA which stores you go to. Tap this button.');
  else { $sel='[data-tour=trip-next],[data-tour=step-close],[data-tour=step-drop],[data-tour=step-shop]';
   if(val("SELECT 1 FROM trips WHERE traveler_id=? AND status NOT IN('settled','cancelled')",'i',$me['id'])) $hint($sel,'Your next step','Each trip shows one main button. Tap it to do the next step.');
   $hint('[data-tour=post-trip]','Start a trip','Going to the store soon? Tap here and fill in where and when.'); }
 }
 if($pg=='traveler_apply') $hint('[data-tour=traveler-submit]','Fill in, then submit','Tick the stores you go to and tell us about your card and car. Then tap Submit for review.');
 if($pg=='new_trip'){ $hint('[name=title]','Fill in from top to bottom','Give your trip a name, then pick the store, the date and when orders stop.'); $hint('[data-tour=publish]','Then publish','Tap Publish trip. Your neighbors can start ordering right away.'); }
 if($pg=='price_check') $hint('[data-tour=save-price]','Save the shelf price','Pick the item and store, type the price you see, then tap Save price.');
 if($pg=='trip'&&$tid){ $t=row("SELECT * FROM trips WHERE id=? AND traveler_id=?",'ii',$tid,$me['id']);
  if($t){ $s=$t['status'];
   if($s=='open'){ if(val("SELECT COUNT(*) FROM trip_members WHERE trip_id=? AND status='requesting'",'i',$tid)) $hint('[data-tour=step-close]','When you\'re ready','Neighbors are ordering. When it\'s time, tap Close window so everyone can pay.');
    else $hint('.hint','Your trip is posted! 🎉','Neighbors can order now. You\'ll get a message for each order. Come back when you\'re ready to close orders.'); }
   if($s=='funding') $hint('.hint','Waiting for payments','Everyone must pay before you shop. You can remove people who don\'t pay.');
   if($s=='ready') $hint('[data-tour=step-lock]','Everyone paid!','Check the list below, then tap Accept and Lock Trip.');
   if($s=='locked') $hint('[data-tour=step-shop]','At the store?','Tap this when you arrive at the store.');
   if($s=='shopping'){
    if(val("SELECT 1 FROM order_lines WHERE trip_id=? AND status='matched' AND shop='todo'",'i',$tid)) $hint('tr[data-who] button[value=bought]','Mark each item','Type the price on the shelf, then tap Bought. If it\'s not there, tap Unavailable.');
    elseif(!val("SELECT 1 FROM trip_receipts WHERE trip_id=?",'i',$tid)) $hint('[data-tour=receipt-upload]','Take a photo of the receipt','Tap Choose File to take a photo of your receipt, then tap Upload.');
    else $hint('[data-tour=step-finish]','All done!','Tap Done when you\'re back home. The app works out everyone\'s share.');
   }
   if($s=='delivered') $hint('[data-tour=handover]','Hand over the items','When a family gets their items, tap Mark handed over.');
  }
 }
}
if($shell=='admin'&&$pg=='home') $hint('.tile.hot','Something is waiting','Boxes with an orange number need your decision. Tap one.');
?>
<script type="application/json" id="guide-data"><?=json_encode(['hints'=>$G,'page'=>$pg.'-'.$tid,'csrf'=>$_SESSION['csrf'],'back'=>'?'.($_SERVER['QUERY_STRING']??'')],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS)?></script>
