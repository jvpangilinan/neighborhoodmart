<?php
// SETTINGS ONLY. Database errors ("Access denied", "Unknown database") are fixed here.
define('DB_HOST','localhost');
define('DB_USER','DB_USER');
define('DB_PASS','DB_PASSWORD');
define('DB_NAME','DB_NAME');
define('DEBUG',true); // set to false when the site is live
define('DEMO_MODE',true); // shows one-click demo account buttons on the login page; set false when live
define('TIMEZONE','Asia/Manila'); // all dates and deadlines use this time zone

// ---- business rules (change numbers here, not in the code) ----
define('BUFFER_RATE',0.10);              // refundable price buffer held at checkout (+10%)
define('PICKUP_HOURS',24);               // requester must collect within this many hours
define('DROPOUT_PENALTY',100.00);        // traveler cancels after "Accept and Lock Trip"
define('DROPOUT_BAN_DAYS',7);            // and cannot host trips for this many days
define('FUZZY_REVIEW_MIN',60);           // HOA match score (0-100) at or above this = "near match" in review queue
// vehicle => load-unit capacity (an item's load_units says how much space one unit takes)
define('VEHICLES',['motorcycle'=>['Motorcycle',20],'sedan'=>['Sedan',80],'suv'=>['SUV / Van',150]]);
// Traveler Effort Fee tiers on each requester's item cost Ci: [up to amount, flat fee, percent]
define('EFFORT_TIERS',[[499.99,50,0],[1499.99,100,0],[INF,150,0.07]]);
