<?php // CHANELLY HELP: plain-language "what can I do here?" for every screen. Edit the words here.
$hs=!$me?'guest':($mod?'admin':($mode?:'requester')); $pg=$p??'home';
$H=[
 'guest:home'=>['Welcome!',['Tap <b>Register</b> to make an account. Use your full name and Block/Lot like in the HOA records.','Already have one? Tap <b>Log in</b>.','Not sure how it works? Tap <b>Start Demo mode</b> and I\'ll show you.']],
 'guest:register'=>['Making your account',['Type your <b>full name</b> exactly as the HOA knows it.','Type your <b>Block</b> and <b>Lot</b> numbers.','Tick the box to agree, then tap <b>Register</b>.'],'If your name doesn\'t match exactly, that\'s OK: the HOA will check it for you.'],
 'guest:login'=>['Logging in',['Type your email (or username).','Type your password.','Tap <b>Log in</b>.'],'Forgot your password? Ask the HOA office to reset it.'],
 '*:choose_role'=>['Pick what you want to do',['<b>Requester</b>: you want items bought for you.','<b>Traveler</b>: you are the one driving to the store.'],'You can switch later from the menu with your name on it.'],
 'requester:home'=>['Your home page',['Look at <b>To do</b> first. If there is a button there, that is what you need to do.','To order something, tap a trip under <b>Open trips you can join</b>.','Your money is in <b>Wallet</b>.']],
 'requester:trips'=>['Joining a trip',['Pick a trip that is going to the store you like.','Tap <b>Join this trip</b>.']],
 'requester:trip'=>['This trip',['Read the green line with 👉. It tells you what to do now.','To order: pick the item, type how many, tap <b>Add to pool</b>.','When asked, tap <b>Pay</b>. Your exact share is shown before you pay.'],'Extra money is held just in case prices change. Anything not used comes back to your wallet.'],
 'requester:my_orders'=>['Your orders',['Every trip you joined is listed here.','Tap a trip name to see the details.']],
 'requester:demand'=>['Product survey',['Find something you need often.','Type how many, then tap <b>I need this</b>.'],'This helps travelers know what to buy.'],
 '*:wallet'=>['Your wallet',['To add money: type the amount, choose GCash or cash, then tap <b>Submit top-up</b>.','The HOA confirms it, then you can use it to pay for trips.'],'Refunds come back here automatically.'],
 'traveler:home'=>['Your trips',['Each of your trips has <b>one</b> main button. Tap it to do the next step.','To start a new trip, tap <b>+ Post a new trip</b>.']],
 'traveler:new_trip'=>['Posting a trip',['Choose the store and the date.','Choose when people must stop adding items.','Type your gas/toll cost, then tap <b>Publish trip</b>.']],
 'traveler:trip'=>['Your trip',['Read the line with 👉. It says what to do now.','At the store, tap <b>Bought</b> or <b>Unavailable</b> for each item.','Take a photo of the receipt before tapping <b>Done</b>.']],
 'traveler:price_check'=>['Checking prices',['Pick the product and the store.','Type the price you see on the shelf, then tap <b>Save price</b>.']],
 'traveler:traveler_apply'=>['Traveler form',['Tick the stores you go to.','Tell us about your membership card and car.','Tap <b>Submit</b>. The HOA will check it.']],
 '*:account'=>['Your account',['You can upload a photo of a bill or village ID here.','You can also change your password.']],
 '*:notifications'=>['Messages',['These are updates about your trips.','Tap a message to open that trip.']],
 'admin:home'=>['HOA dashboard',['Each box is something waiting for you.','Tap a box with a number to review it.']],
];
$h=$H["$hs:$pg"]??$H["*:$pg"]??['Need a hand?',['Use the menu to move around.','Tap 🏠 <b>Home</b> to see what you need to do next.']];
$hoaPhone='HOA office · Mon–Sat, 9 AM–5 PM';
?>
<button type="button" class="help-fab" aria-expanded="false" aria-controls="help-panel"><img src="assets/brand/chanelly-sm.png" alt=""><span>Need help?</span></button>
<div class="help-panel" id="help-panel" hidden role="dialog" aria-label="Help">
<div class="hp-top"><img src="assets/brand/chanelly-sm.png" alt="Chanelly"><div><small>Chanelly says</small><h3 tabindex="-1"><?=$h[0]?></h3></div></div>
<ol><?php foreach($h[1] as $x) echo "<li>$x</li>"; ?></ol>
<?php if(!empty($h[2])) echo '<p class="muted">💡 '.$h[2].'</p>'; ?>
<div class="hp-call">🙋 Still stuck? Ask a family member, or visit the <?=h($hoaPhone)?>.<?php if(!$me&&DEMO_MODE) echo '<br><button type="button" class="link" data-tour-start>▶ Show me with the guided tour</button>'; ?></div>
<div class="actions"><button type="button" class="s hp-close">Close</button><a class="btn ghost" href="?p=rules">Full rules</a></div></div>
