<?php // HOME for visitors (not logged in). ?>
<div class="card hero"><img class="hero-logo" src="assets/brand/logo.png" alt=""><h1>Bulk prices, shared by neighbors.</h1>
<p>One neighbor drives to S&amp;R or Landers. Everyone else adds what they need, pays their exact share upfront, and picks up at home. No group-chat math.</p>
<div class="actions" style="justify-content:center"><a class="btn" href="?p=register">Create an account</a><a class="btn ghost" href="?p=login">Log in</a></div><?php if(DEMO_MODE){ ?><div class="demo-hero"><img src="assets/brand/chanelly.png" alt="Chanelly, the NeighborhoodMart mascot"><div><b>New here? Let Chanelly show you around!</b><p>A guided tour with real screens. Just read and click.</p><button type="button" class="demo-btn" data-tour-start>▶ Start Demo mode</button></div></div><?php } ?></div>
<div class="grid">
<div class="card"><h3>1. Join or post a trip</h3><p class="muted">Requesters add items to an open trip. Travelers post where and when they are going.</p></div>
<div class="card"><h3>2. Pay your share upfront</h3><p class="muted">Item price + a refundable 10% buffer + a fair share of gas/toll + a small Traveler Effort Fee.</p></div>
<div class="card"><h3>3. Pick up and get refunds</h3><p class="muted">After the store, real receipt prices are used and any unused buffer goes back to your wallet.</p></div>
</div>
<div class="card" style="margin-top:16px"><h3>Open trips</h3><?php $tripLimit=6; require ROOT.'/views/trip_cards.php'; ?></div>
