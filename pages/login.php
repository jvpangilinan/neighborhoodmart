<?php // SCREEN: login (+ one-click demo accounts while DEMO_MODE is on in config/config.php). ?>
<div class="card narrow"><h2>Log in</h2>
<?= form('login') ?><div class="f">
<label>Email or username<input name="email" required autocomplete="username"></label>
<label>Password<input type="password" name="pass" required autocomplete="current-password"></label>
<button data-tour="login-submit">Log in</button></div></form>
<p class="center"><small>No account yet? <a href="?p=register">Register</a></small></p>
<?php if(DEMO_MODE){ ?><button type="button" class="demo-btn" data-tour-start style="width:100%;margin-top:14px">▶ Demo mode with Chanelly (guided tour)</button><h4>Demo accounts</h4><div class="demo">
<?php foreach([['admin','admin123','🏠 HOA Admin','verifies residents &amp; travelers'],['traveler','traveler123','🚗 Traveler','Juan · posts trips'],['requester','requester123','🛒 Requester','Maria · ₱5,000 credit'],['requester2','requester123','🛒 Requester 2','Jose · already in a trip']] as [$u,$pw,$l,$d])
 echo form('login',hid('email',$u).hid('pass',$pw)).'<button><span>'.$l.'</span><small>'.$u.' / '.$pw.' · '.$d.'</small></button></form>'; ?>
</div><?php } ?></div>
