<?php
// 3.1 HYBRID HOA VERIFICATION. Wrong auto-approve / review decision? Edit ONLY this file.
// Exact match of full name + block + lot against hoa_residents -> instant approval.
// Anything else -> "pending" review queue with a score. The system NEVER rejects; only an admin can.
function norm_name($s){
 $s=strtolower(preg_replace('/[^a-z0-9 ]/i',' ',$s));
 return trim(preg_replace('/\s+/',' ',$s));
}
function norm_loc($s){ return ltrim(strtolower(preg_replace('/[^a-z0-9]/i','',(string)$s)),'0'); }
// 0-100 similarity between two names. Handles initials ("JR" = Juan Roberto), missing middle names, Jr./Sr.
function name_score($a,$b){
 $sfx=['jr','sr','ii','iii','iv'];
 $ta=array_values(array_diff(explode(' ',norm_name($a)),$sfx)); $tb=array_values(array_diff(explode(' ',norm_name($b)),$sfx));
 if(!$ta||!$tb) return 0;
 $initials=implode('',array_map(fn($w)=>$w[0],$tb));
 $hit=0;
 foreach($ta as $w){
  if(in_array($w,$tb)) $hit++;
  elseif(strlen($w)<=3&&str_contains($initials,$w)) $hit+=0.8;         // initials
  else foreach($tb as $v){ similar_text($w,$v,$p); if($p>=80){$hit+=0.7;break;} } // small typo
 }
 $cover=$hit/max(count($ta),count($tb)); $prec=$hit/count($ta);
 return (int)round(100*($cover*0.4+$prec*0.6));
}
// returns ['status'=>approved|pending, 'score'=>int, 'note'=>string]
function hoa_check($name,$block,$lot){
 $best=['score'=>0,'r'=>null];
 foreach(rows("SELECT * FROM hoa_residents") as $r){
  $sameLoc=norm_loc($r['block'])===norm_loc($block)&&norm_loc($r['lot'])===norm_loc($lot);
  if($sameLoc&&norm_name($r['full_name'])===norm_name($name))
   return ['status'=>'approved','score'=>100,'note'=>'Exact match with HOA record: '.$r['full_name'].', Blk '.$r['block'].' Lot '.$r['lot']];
  $s=(int)round(name_score($name,$r['full_name'])*0.6+($sameLoc?40:0));
  if($s>$best['score']) $best=['score'=>$s,'r'=>$r];
 }
 if($best['r']&&$best['score']>=FUZZY_REVIEW_MIN)
  return ['status'=>'pending','score'=>$best['score'],'note'=>'Near match ('.$best['score'].'%): '.$best['r']['full_name'].', Blk '.$best['r']['block'].' Lot '.$best['r']['lot'].'. Needs manual review.'];
 return ['status'=>'pending','score'=>$best['score'],'note'=>'No close HOA record found. Needs manual review of proof of residence.'];
}
