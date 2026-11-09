<?php
// FILE UPLOADS (proof of residence, receipt photos). Upload problems? Edit ONLY this file.
// How it works on the host: the FILE is saved in /uploads (blocked from direct web access by uploads/.htaccess);
// the DATABASE only stores the file name. Files are shown through a PHP page that checks who is asking first.
// Phone photos are auto-rotated and shrunk to max 1600px JPEG (~200-400 KB) so hosting space lasts.
const UPLOAD_MAX_MB=12;
function save_upload($f){
 if(empty($f['name'])||($f['error']??1)===UPLOAD_ERR_NO_FILE) return '';
 if($f['error']!==UPLOAD_ERR_OK) go('Upload failed (file too big for the server?). Try a smaller photo.');
 if($f['size']>UPLOAD_MAX_MB*1024*1024) go('File must be '.UPLOAD_MAX_MB.' MB or smaller.');
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);             // real type, not the file extension
 $ok=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf'];
 if(!isset($ok[$mime])) go('Only JPG, PNG, WEBP photos or PDF files are allowed.');
 $dir=ROOT.'/uploads/'; if(!is_dir($dir)) mkdir($dir,0755,true);
 $name=date('Ymd').'_'.bin2hex(random_bytes(8));
 if($mime!=='application/pdf'&&function_exists('imagecreatefromstring')){
  $im=@imagecreatefromstring(file_get_contents($f['tmp_name']));
  if($im){
   if($mime==='image/jpeg'&&function_exists('exif_read_data')){ $o=@exif_read_data($f['tmp_name'])['Orientation']??1;
    if($o==3)$im=imagerotate($im,180,0); elseif($o==6)$im=imagerotate($im,-90,0); elseif($o==8)$im=imagerotate($im,90,0); }
   $w=imagesx($im);$h=imagesy($im);$s=min(1,1600/max($w,$h));
   if($s<1){ $n=imagecreatetruecolor((int)($w*$s),(int)($h*$s)); imagecopyresampled($n,$im,0,0,0,0,(int)($w*$s),(int)($h*$s),$w,$h); $im=$n; }
   $flat=imagecreatetruecolor(imagesx($im),imagesy($im)); imagefill($flat,0,0,imagecolorallocate($flat,255,255,255)); imagecopy($flat,$im,0,0,0,0,imagesx($im),imagesy($im));
   imagejpeg($flat,$dir.$name.'.jpg',82); return $name.'.jpg';
  }
 }
 $name.='.'.$ok[$mime]; move_uploaded_file($f['tmp_name'],$dir.$name); return $name;
}
// stream a stored file to the browser (call only after checking permission)
function send_upload($file){
 $path=ROOT.'/uploads/'.basename((string)$file);
 if(!$file||!is_file($path)){ http_response_code(404); exit('File not found.'); }
 $types=['pdf'=>'application/pdf','png'=>'image/png','webp'=>'image/webp','jpg'=>'image/jpeg','jpeg'=>'image/jpeg'];
 header('Content-Type: '.($types[strtolower(pathinfo($path,PATHINFO_EXTENSION))]??'application/octet-stream'));
 header('X-Content-Type-Options: nosniff'); header('Cache-Control: private, max-age=86400'); readfile($path); exit;
}
// who may see a trip's receipts: HOA admin, the trip's traveler, and families who paid into it
function can_see_receipts($tid){ global $me,$mod;
 if(!$me) return false; if($mod) return true;
 return (bool)val("SELECT 1 FROM trips t WHERE t.id=? AND (t.traveler_id=? OR EXISTS(SELECT 1 FROM trip_members m WHERE m.trip_id=t.id AND m.user_id=? AND m.status IN('funded','settled')))",'iii',$tid,$me['id'],$me['id']);
}
