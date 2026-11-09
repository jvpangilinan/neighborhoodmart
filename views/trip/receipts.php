<?php // TRIP SECTION: receipt photos (proof of the real prices). Upload = traveler; view = traveler, paying families, HOA. ?>
<?php if(!in_array($t['status'],['shopping','delivered','settled'])||!can_see_receipts($id)) return;
$rc=rows("SELECT * FROM trip_receipts WHERE trip_id=? ORDER BY id",'i',$id); $canUp=!empty($own); ?>
<div class="card" data-tour="receipts"><div class="page-head"><h3 style="margin:0">🧾 Receipts <small>(<?=count($rc)?>)</small></h3></div>
<?php if(!$rc) echo '<p class="muted">'.($canUp?'Take a photo of every receipt before you tap Done. Families see them here.':'No receipt photos yet.').'</p>'; ?>
<div class="receipts"><?php foreach($rc as $r){ $u='?p=receipt&id='.$r['id']; $pdf=str_ends_with($r['file'],'.pdf');
 echo '<figure><a href="'.$u.'" target="_blank" title="Open full size">'.($pdf?'<span class="pdf">PDF</span>':'<img src="'.$u.'" alt="Receipt '.$r['id'].'" loading="lazy">').'</a><figcaption>'.h($r['note']?:'Receipt').' · '.when($r['created_at'])
 .($canUp&&$t['status']!='settled'?form('delete_receipt',hid('id',$r['id']),'data-confirm="Remove this receipt photo?"').'<button class="link sm">Remove</button></form>':'').'</figcaption></figure>'; } ?></div>
<?php if($canUp&&$t['status']!='settled'){ ?>
<?=form('upload_receipt',hid('id',$id),'enctype="multipart/form-data" data-tour="receipt-upload"')?><div class="inline" style="margin-top:12px">
<input type="file" name="photos[]" accept="image/*,application/pdf" capture="environment" multiple required class="grow">
<input name="note" placeholder="Note (e.g. S&R receipt 1 of 2)" class="grow"><button>Upload</button></div>
<small>On a phone this opens the camera. Big photos are shrunk automatically.</small></form>
<?php } ?></div>
