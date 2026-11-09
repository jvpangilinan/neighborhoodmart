<?php
// DATABASE CONNECTION + query helpers (rows = many, row = one, ex = insert/update, val = single value).
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
date_default_timezone_set(TIMEZONE);
$db=new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);$db->set_charset('utf8mb4');
$db->query("SET time_zone='".date('P')."'"); // MySQL NOW() = same clock as PHP
function rows($s,$t='',...$a){global $db;$st=$db->prepare($s);if($t)$st->bind_param($t,...$a);$st->execute();$r=$st->get_result();return $r?$r->fetch_all(MYSQLI_ASSOC):[];}
function row($s,$t='',...$a){return rows($s,$t,...$a)[0]??null;}
function val($s,$t='',...$a){$r=row($s,$t,...$a);return $r?array_values($r)[0]:null;}
function ex($s,$t='',...$a){global $db;$st=$db->prepare($s);if($t)$st->bind_param($t,...$a);$st->execute();return $db->insert_id;}
