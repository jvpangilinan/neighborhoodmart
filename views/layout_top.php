<?php // PAGE FRAME. Each user type gets its own app: own color, own menu, own home screen.
// guest = not logged in | requester | traveler | admin (HOA Admin). Menus are listed in $menus below. ?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>NeighborhoodMart</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&display=swap" rel="stylesheet">
<script>/* apply text size / simple view / contrast before the page draws (no flicker) */try{var v=JSON.parse(localStorage.getItem('nmView'))||{},c=document.documentElement.classList;if(v.t=='l'||v.t=='xl')c.add('t-'+v.t);if(v.simple)c.add('simple');if(v.hc)c.add('hc');}catch(e){}</script>
<link rel="stylesheet" href="<?=asset('assets/style.css')?>">
<meta name="csrf" content="<?=h($_SESSION['csrf'])?>"><meta name="demo" content="<?=DEMO_MODE?1:0?>">
<link rel="manifest" href="manifest.webmanifest"><meta name="theme-color" content="#1b7a4b">
<link rel="icon" href="assets/icons/icon-32.png" sizes="32x32"><link rel="icon" href="assets/icons/icon-192.png" sizes="192x192"><link rel="apple-touch-icon" href="assets/icons/icon-180.png">
<meta name="apple-mobile-web-app-capable" content="yes"><meta name="mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-title" content="NbhdMart">
<script>if('serviceWorker' in navigator) addEventListener('load',()=>navigator.serviceWorker.register('sw.js',{updateViaCache:'none'}).catch(()=>{}));</script></head>
<?php $cur=$p??'home';
$shell=!$me?'guest':($mod?'admin':($mode?:'requester'));
$label=['guest'=>'','requester'=>'🛒 Requester','traveler'=>'🚗 Traveler','admin'=>'🏠 HOA Admin'][$shell];
$menus=[
 'guest'=>[['home','🏠','Home'],['trips','🛒','Trips'],['rules','❓','How it works']],
 'requester'=>[['home','🏠','Home'],['trips','🛒','Join a trip'],['my_orders','📦','My orders'],['demand','🗳️','Survey'],['wallet','👛','Wallet']],
 'traveler'=>$isTraveler?[['home','🏠','Home'],['new_trip','➕','Post a trip'],['price_check','🏷️','Prices'],['demand','📋','Needs'],['wallet','👛','Earnings']]
                        :[['home','🏠','Home'],['traveler_apply','📝','Traveler form']],
 'admin'=>[['home','📊','Dashboard'],['admin_users','👥','Residents'],['admin_travelers','🚗','Travelers'],['admin_trips','🧭','Trips'],['admin_reports','📈','Reports'],['admin_catalog','🏬','Catalog &amp; stores'],['admin_wallet','👛','Wallet'],['admin_disputes','⚖️','Disputes'],['admin_announce','📢','Announcements']],
][$shell];
$unseen=$me?(int)val("SELECT COUNT(*) FROM notifications WHERE user_id=? AND seen=0",'i',$me['id']):0; ?>
<body class="app-<?=$shell?><?=$shell!='admin'?' tabs':''?>">
<a class="skip" href="#main">Skip to main content</a>
<header class="top"><div class="wrap top-in">
<a class="brand" href="index.php"><img src="assets/brand/logo.png" alt="" width="60" height="43">NeighborhoodMart</a>
<?php if($label) echo '<span class="app-badge">'.$label.'</span>'; ?>
<div class="auth">
<div class="menu"><button type="button" class="icon-btn" data-view-open aria-expanded="false" aria-label="Text size and display settings" title="Text size &amp; display">Aa</button>
<div class="view-panel" hidden><h4>Make it easier to read</h4>
<small>Text size</small><div class="seg"><button type="button" data-view-t="n" style="font-size:.9rem">A</button><button type="button" data-view-t="l" style="font-size:1.1rem">A+</button><button type="button" data-view-t="xl" style="font-size:1.3rem">A++</button></div>
<label class="switch"><span>Simple view<small>Hide extra details and numbers</small></span><input type="checkbox" data-view-sw="simple"></label>
<label class="switch"><span>High contrast<small>Darker text, stronger lines</small></span><input type="checkbox" data-view-sw="hc"></label>
<small>Saved on this device.</small>
<?php if($me&&!$mod) echo form('set_guided',hid('on',(int)$me['guided']===1?'0':'1').hid('back','?'.($_SERVER['QUERY_STRING']??'')),'class="guided-sw"').'<label class="switch"><span>🐾 Guided mode<small>Chanelly points to your next step</small></span><input type="checkbox" onchange="this.form.submit()"'.((int)$me['guided']===1?' checked':'').'></label></form>'; ?></div></div>
<?php if($me): ?>
 <a class="bell" href="?p=notifications" title="Notifications" aria-label="Notifications<?=$unseen?', '.$unseen.' new':''?>">🔔<?=$unseen?'<b>'.$unseen.'</b>':''?></a>
 <details class="menu"><summary><?=h(explode(' ',$me['name'])[0])?> ▾</summary><div class="menu-box">
  <small><?=h($me['name'])?></small>
  <?php if(!$mod) echo form('set_mode',hid('mode',$shell=='traveler'?'requester':'traveler')).'<button class="link">Switch to '.($shell=='traveler'?'🛒 Requester':'🚗 Traveler').'</button></form>'; ?>
  <a href="?p=account">My account</a><a href="?p=rules#install">📲 Install on my phone</a><a href="?p=notifications">Notifications</a><a href="?p=rules">Rules &amp; privacy</a>
  <?=form('logout')?><button class="link">Log out</button></form></div></details>
<?php else: ?><?php if(DEMO_MODE) echo '<button type="button" class="demo-btn sm" data-tour-start>▶ Demo mode</button>'; ?><a class="login" href="?p=login">Log in</a><a class="btn" href="?p=register" data-tour="register">Register</a><?php endif; ?>
</div></div>
<div class="top-nav"><div class="wrap"><nav class="main-nav">
<?php foreach($menus as [$pg,$ic,$t]) echo '<a data-tour="nav-'.$pg.'" href="'.($pg=='home'?'index.php':'?p='.$pg).'" class="'.($cur==$pg||($pg=='home'&&$cur=='trip'&&$shell!='requester')?'on':'').'"><span class="ni" aria-hidden="true">'.$ic.'</span>'.$t.'</a>'; ?>
</nav></div></div></header>
<main class="wrap" id="main">
<?php if($msg) echo '<div class="msg">'.h($msg).'</div>';
if($me&&$me['must_change_pw']&&$cur!='account') echo '<div class="warn">You are using the default password. <a href="?p=account">Change it now</a>.</div>';
if($me&&!$mod&&$me['verify_status']=='pending'&&$cur!='account') echo '<div class="notice card">Your residency is waiting for HOA review. You can look around, but joining or hosting trips unlocks after approval. <a href="?p=account">Upload proof of residence</a></div>';
if($me&&$me['verify_status']=='rejected') echo '<div class="warn">Your residency was not verified. See <a href="?p=account">My account</a> or contact the HOA.</div>'; ?>
