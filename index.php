<?php
// FRONT CONTROLLER: the only public entry point. Loads the core, checks access (config/routes.php),
// then runs one action file (POST) or shows one page file (GET). No business logic or screens here.
define('ROOT',__DIR__);
require ROOT.'/config/config.php';
require ROOT.'/core/helpers.php';
set_exception_handler(function($e){
 @file_put_contents(ROOT.'/error.log',date('c').' '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine()."\n",FILE_APPEND);
 http_response_code(500); echo '<h3>Something went wrong.</h3>';
 if(DEBUG) echo '<pre>'.h($e->getMessage()).'
File: '.h(str_replace(ROOT,'',$e->getFile())).' line '.$e->getLine().'</pre>';
});
require ROOT.'/core/db.php';
require ROOT.'/core/auth.php';
foreach(glob(ROOT.'/services/*.php') as $s) require $s;
$routes=require ROOT.'/config/routes.php';
run_timers(); // time-based rules: closes windows, marks uncollected orders (services/trips.php)

if($_SERVER['REQUEST_METHOD']=='POST'){
 $a=preg_replace('/[^a-z_]/','',$_POST['a']??'');
 if(!hash_equals($_SESSION['csrf'],$_POST['csrf']??'')) go('Session expired. Please try again.');
 if(!array_key_exists($a,$routes['actions'])||!is_file(ROOT."/actions/$a.php")) go('Unknown action.');
 if(!can($routes['actions'][$a])) go(access_msg($routes['actions'][$a]),$me?'index.php':'?p=login');
 $id=(int)($_POST['id']??0);
 require ROOT."/actions/$a.php"; exit;
}
$p=preg_replace('/[^a-z_]/','',$_GET['p']??'home');
if(!array_key_exists($p,$routes['pages'])||!is_file(ROOT."/pages/$p.php")) $p='home';
if(!can($routes['pages'][$p])) go(access_msg($routes['pages'][$p]),$me?'index.php':'?p=login');
// after login, everyone picks Traveler or Requester first
if($me&&!$mode&&!$mod&&!in_array($p,['choose_role','account','rules','traveler_apply','proof','receipt','export','demo_state'])) { header('Location: ?p=choose_role'); exit; }
if(in_array($p,$routes['raw'])){ require ROOT."/pages/$p.php"; exit; }
$msg=$_SESSION['m']??''; unset($_SESSION['m']);
require ROOT.'/views/layout_top.php';
require ROOT."/pages/$p.php";
require ROOT.'/views/layout_bottom.php';

function access_msg($rule){ return match($rule){'login'=>'Please log in first.','verified'=>'Your account is waiting for HOA verification.',
 'traveler'=>'You need an approved Traveler profile for this.','mod'=>'Admins only.',default=>'Access denied.'}; }
