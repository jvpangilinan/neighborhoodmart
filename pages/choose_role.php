<?php // SCREEN: after login, choose Traveler or Requester (switch anytime in the top bar). ?>
<div class="card mid"><h2>How are you joining today, <?=h(explode(' ',$me['name'])[0])?>?</h2>
<p class="muted">You can switch anytime using the Requester / Traveler switch at the top.</p>
<div class="big-choice">
<?=form('set_mode',hid('mode','requester'))?><button><span class="ic">🛒</span><b>Requester</b><span>Join a neighbor's store run, answer product surveys, pay your share upfront, and pick up your items.</span></button></form>
<?=form('set_mode',hid('mode','traveler'))?><button data-tour="role-traveler"><span class="ic">🚗</span><b>Traveler</b><span>Post bulk-buying trips to S&amp;R, Landers and others, check prices, and earn a Traveler Effort Fee per order.<?=$me['traveler_status']=='approved'?'':' Requires a traveler registration.'?></span></button></form>
</div></div>
