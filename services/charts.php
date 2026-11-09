<?php
// TINY SVG CHARTS (no internet/CDN needed on the host). One color = the app's theme color.
// Hover a bar to see its exact value. Each chart is followed by a table on the page for exact numbers.
function hbar_chart($data,$fmt='n'){ // $data = [[label, value], ...]
 if(!$data) return '<p class="muted">No data yet.</p>';
 $max=max(1,max(array_column($data,1))); $rowH=36; $w=460; $lab=170; $h=count($data)*$rowH+8;
 $s='<svg class="chart" viewBox="0 0 '.$w.' '.$h.'" role="img" preserveAspectRatio="xMinYMin meet">';
 foreach($data as $i=>[$l,$v]){ $y=$i*$rowH+6; $bw=max(2,($w-$lab-70)*$v/$max); $txt=$fmt=='peso'?peso($v):number_format($v);
  $s.='<g class="bar-g"><title>'.h($l).': '.$txt.'</title><rect x="0" y="'.($y-4).'" width="'.$w.'" height="'.$rowH.'" fill="transparent"/>'
   .'<text x="'.($lab-10).'" y="'.($y+15).'" text-anchor="end" class="cl">'.h(mb_strimwidth($l,0,20,'…')).'</text>'
   .'<rect x="'.$lab.'" y="'.$y.'" width="'.round($bw,1).'" height="20" rx="4" class="cb"/>'
   .'<text x="'.($lab+$bw+8).'" y="'.($y+15).'" class="cv">'.$txt.'</text></g>'; }
 return $s.'</svg>';
}
function vbar_chart($data,$fmt='peso'){ // $data = [[label, value], ...] in time order
 if(!$data) return '<p class="muted">No data yet.</p>';
 $max=max(1,max(array_column($data,1))); $w=640; $h=220; $pad=56; $bot=26; $n=count($data); $slot=($w-$pad)/$n; $bw=min(46,$slot*0.6);
 $s='<svg class="chart" viewBox="0 0 '.$w.' '.$h.'" role="img">';
 foreach([0,0.5,1] as $g){ $y=$h-$bot-($h-$bot-14)*$g; $s.='<line x1="'.$pad.'" x2="'.$w.'" y1="'.$y.'" y2="'.$y.'" class="cg"/><text x="'.($pad-6).'" y="'.($y+4).'" text-anchor="end" class="ct">'.($fmt=='peso'?'₱'.number_format($max*$g/1000,1).'k':round($max*$g)).'</text>'; }
 foreach($data as $i=>[$l,$v]){ $bh=($h-$bot-14)*$v/$max; $x=$pad+$i*$slot+($slot-$bw)/2; $y=$h-$bot-$bh;
  $s.='<g class="bar-g"><title>'.h($l).': '.($fmt=='peso'?peso($v):$v).'</title><rect x="'.($pad+$i*$slot).'" y="0" width="'.$slot.'" height="'.($h-$bot).'" fill="transparent"/>'
   .($v>0?'<path class="cb" d="M'.$x.','.($y+$bh).' V'.($y+min(4,$bh)).' q0,-4 4,-4 h'.($bw-8).' q4,0 4,4 V'.($y+$bh).' Z"/>':'')
   .'<text x="'.($x+$bw/2).'" y="'.($h-8).'" text-anchor="middle" class="ct">'.h($l).'</text></g>'; }
 return $s.'</svg>';
}
