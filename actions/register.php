<?php
// ACTION: create account + automatic HOA check (services/verification.php).
require __DIR__.'/_upload.php';
if(empty($_POST['consent'])) go('Consent is required (Data Privacy Act).','?p=register');
foreach(['name','email','pass','block','lot'] as $k) if(post($k)==='') go('Please fill in all required fields.','?p=register');
if(strlen(post('pass'))<6) go('Password must be at least 6 characters.','?p=register');
if(row("SELECT id FROM users WHERE email=?",'s',post('email'))) go('Email already registered.','?p=register');
$chk=hoa_check(post('name'),post('block'),post('lot'));
$proof=save_proof($_FILES['proof']??[]);
$uid=ex("INSERT INTO users(name,email,pass,block,lot,street,phone,verify_status,verify_note,match_score,proof_file,consent) VALUES(?,?,?,?,?,?,?,?,?,?,?,1)",'sssssssssis',
 post('name'),post('email'),password_hash(post('pass'),PASSWORD_DEFAULT),post('block'),post('lot'),post('street'),post('phone'),$chk['status'],$chk['note'],$chk['score'],$proof);
foreach(rows("SELECT id FROM users WHERE role='admin'") as $a) if($chk['status']=='pending') notify($a['id'],'New resident waiting for review: '.post('name'),'?p=admin_users');
go($chk['status']=='approved'?'Registered and instantly verified by the HOA records. Please log in.'
 :'Registered. Your name/address did not exactly match the HOA records, so an admin will review it. You can log in and upload proof of residence meanwhile.','?p=login');
