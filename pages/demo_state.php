<?php // RAW (Demo mode): how far the tour data really is (milestones 0-16), so Chanelly can spot mistakes.
header('Content-Type: application/json'); header('Cache-Control: no-store');
if(!DEMO_MODE){ echo '{"m":-1}'; exit; }
echo json_encode(['m'=>demo_progress($_GET['email']??'',(int)($_GET['trip']??0)),'who'=>$me?($me['username']?:$me['email']):'guest']);
