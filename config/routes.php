<?php
// ACCESS MAP: every page and action, and who may use it.
// 0 = anyone | 'login' = logged in | 'verified' = HOA-approved resident | 'traveler' = approved traveler | 'mod' = HOA/admin
return [
 'pages'=>['home'=>0,'trips'=>0,'login'=>0,'register'=>0,'rules'=>0,'trip'=>0,
   'choose_role'=>'login','account'=>'login','notifications'=>'login','traveler_apply'=>'login',
   'demand'=>'verified','wallet'=>'verified','my_orders'=>'verified',
   'new_trip'=>'traveler','price_check'=>'traveler',
   'admin_users'=>'mod','admin_trips'=>'mod','admin_travelers'=>'mod','admin_catalog'=>'mod','admin_wallet'=>'mod',
   'admin_disputes'=>'mod','admin_announce'=>'mod','proof'=>'mod','receipt'=>'login','demo_state'=>0,'admin_reports'=>'mod','export'=>'mod'],
 'raw'=>['proof','receipt','export','demo_state'],    // pages sent without the site frame (file download)
 'actions'=>['register'=>0,'login'=>0,'logout'=>0,'demo_start'=>0,'demo_as'=>0,'demo_goto'=>0,'set_mode'=>'login','guided_setup'=>'login','set_guided'=>'login','change_pw'=>'login','upload_proof'=>'login',
   'traveler_apply'=>'login','vote'=>'verified','suggest_item'=>'verified',
   'request_item'=>'verified','cancel_line'=>'verified','fund'=>'verified','answer_alert'=>'verified','collect'=>'verified',
   'topup'=>'verified','cashout'=>'verified','dispute'=>'verified',
   'post_trip'=>'traveler','price_check'=>'traveler','trip_step'=>'traveler','shop_line'=>'traveler','cancel_trip'=>'traveler','upload_receipt'=>'traveler','delete_receipt'=>'traveler',
   'verify_user'=>'mod','hoa_import'=>'mod','traveler_decide'=>'mod','add_option'=>'mod','save_store'=>'mod',
   'save_item'=>'mod','suggestion'=>'mod','wallet_decide'=>'mod','resolve_dispute'=>'mod','post_announcement'=>'mod','set_role'=>'mod'],
];
