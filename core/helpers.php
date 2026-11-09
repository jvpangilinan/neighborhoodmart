<?php
// SMALL DISPLAY HELPERS used by every page.
function h($x){return htmlspecialchars((string)$x);}
function peso($x){return '₱'.number_format((float)$x,2);}
function go($m='',$to='index.php'){$_SESSION['m']=$m;header('Location: '.$to);exit;}
function hid($n,$v){return '<input type="hidden" name="'.$n.'" value="'.h($v).'">';}
function form($a,$extra='',$attr=''){return '<form method="post" '.$attr.'>'.hid('a',$a).hid('csrf',$_SESSION['csrf']??'').$extra;}
const TAG_WORDS=['open'=>'Open for orders','funding'=>'Waiting for payment','ready'=>'All paid','locked'=>'Going to the store','shopping'=>'Shopping now',
 'delivered'=>'Ready for pickup','settled'=>'Done','cancelled'=>'Cancelled','requesting'=>'Adding items','unpaid'=>'Not paid yet','funded'=>'Paid',
 'dropped'=>'Removed (not paid)','matched'=>'Accepted','rejected'=>'Not accepted','trimmed'=>'Removed (box not full)','needs_ok'=>'Needs your answer',
 'todo'=>'To buy','bought'=>'Bought','substituted'=>'Other brand','declined'=>'Declined','unavailable'=>'Out of stock','waiting'=>'Not picked up yet',
 'collected'=>'Picked up','uncollected'=>'Not picked up','pending'=>'Being checked','approved'=>'Approved','none'=>'Not yet','done'=>'Done',
 'unverified'=>'Not checked yet','allowed'=>'Allowed','not_allowed'=>'Not allowed','joined'=>'You joined','new'=>'New','resolved'=>'Resolved',
 'adding items'=>'Adding items'];
function tag($s){ $w=TAG_WORDS[$s]??ucfirst(str_replace('_',' ',$s)); return '<span class="tag t-'.h(str_replace(' ','_',$s)).'">'.h($w).'</span>'; }
function when($d){return $d?date('M j, g:i A',strtotime($d)):'-';}
function opt($v,$label,$sel=null){return '<option value="'.h($v).'"'.((string)$sel===(string)$v?' selected':'').'>'.h($label).'</option>';}
function asset($f){ return $f.'?v='.@filemtime(ROOT.'/'.$f); } // cache-busting: new upload -> new URL
function post($k,$d=''){return trim((string)($_POST[$k]??$d));}
