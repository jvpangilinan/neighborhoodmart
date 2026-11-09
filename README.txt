NEIGHBORHOODMART v10 - SETUP, DEMO, AND "WHICH ONE FILE DO I EDIT?"

SETUP (Hostinger or XAMPP)
1. Create a MySQL database + user (hPanel > Databases, or phpMyAdmin on XAMPP).
2. phpMyAdmin > Import > database/neighborhoodmart.sql   (WARNING: it drops and recreates all tables.)
3. Edit config/config.php (DB_USER, DB_PASS, DB_NAME). Set DEBUG to false when live.
4. Upload everything in this folder to public_html (keep the folders), INCLUDING uploads/ (it holds the demo
   receipt photos and demo bill). In File Manager, uploads/ should be permission 755 so PHP can save new photos.
5. Open the site and log in as  admin / admin123 , then change the password (Account page).

GUIDED MODE (real users, not the demo)
 - First login: Chanelly asks "Want me to guide you?" -> Requester / Traveler / No thanks (the "Guided setup").
 - When on, every screen rings the ONE next thing to do for that person's real situation (pay now, approve a
   substitute, tap Bought, upload the receipt...). Nothing is clicked or filled for them; nothing is blocked.
 - Turn on/off: "Guided mode" switch in the Aa panel, or "Turn off guided mode" in Chanelly's bubble.
   Saved on the account (users.guided: NULL = not asked yet, 1 = on, 0 = off). Demo accounts start OFF.
 - Hints and their words: views/guide.php.  Behaviour: assets/guide.js.
 - Existing database without re-import? Run once in phpMyAdmin > SQL:
     ALTER TABLE users ADD guided TINYINT NULL AFTER last_mode;

EASY TO USE (for seniors, low vision, shaky hands, first-time users)
 - "Aa" button (top bar): text size A / A+ / A++, Simple view (hides extra numbers/details), High contrast.
   Saved per device, applied before the page draws. Extra details are marked class="adv" in the views.
 - Plain words everywhere: statuses ("Waiting for payment", "Ready for pickup"), reasons ("Sorry, the car is full"),
   checkboxes ("OK to get a different brand of the same size"). Words: core/helpers.php (TAG_WORDS), services/matching.php.
 - Big tap targets (44px+), icons on every menu item, phones get a bottom tab bar.
 - "Are you sure?" Yes/No before paying, cancelling, declining, locking, finishing, rejecting, cash-outs (data-confirm).
 - "Need help?" Chanelly button on every screen with 2-3 plain steps for that screen: views/help.php.
 - Skip-to-content link, labelled buttons for screen readers, visible keyboard focus.

DEMO MODE WITH CHANELLY (guided tour, for presenting)
 Logged out, click "Demo mode" (top bar, home page or login page). Chanelly walks through the real app in 5 chapters:
  1 Traveler: Ed Santiago registers (pre-filled; near HOA match -> pending) and fills the traveler form
  2 HOA Admin: reviews Ed (66% match), approves residency and the traveler application
  3 Traveler: Ed posts "Ed's Sunday S&R run"
  4 Requester: Maria joins (Jose, Liza, Grace join automatically), sees the checkout breakdown + savings
  5 Shopping day: close requests, Maria pays, lock, shop (promo price), receipt, Done, ledger, pickup, savings, HOA Reports
 Each bubble has "Continue" or "Click the highlighted button" (+ "Do it for me"). Accounts switch automatically.
 Every new tour deletes the previous tour's account/trip first, so you can run it again and again.
 Words and order of the steps: assets/tour.js (STEPS list). Server helpers: services/demo.php.
 DEMO_MODE=false in config/config.php hides the tour and demo logins.

DEMO COMMUNITY (already inside database/neighborhoodmart.sql)
 IMPORT THE SQL ON DEFENSE DAY (or the day before). All dates are relative to the import time, so open trips stay open.
 The "waiting for pickup" trip turns "uncollected" 16 hours after import, which is the 24-hour rule working.

 Main accounts (one-click buttons on the login page; DEMO_MODE=false in config/config.php hides them):
  HOA Admin   admin      / admin123
  Traveler    traveler   / traveler123   Juan, S&R, sedan
  Requester   requester  / requester123  Maria
  Requester 2 requester2 / requester123  Jose
 Other residents: <first name>@demo.ph / demo123
  ana (2nd traveler, Landers, SUV), liza, carlo, teresa, mark, grace, paolo, rosa, cristina
  ramon = traveler application pending | bea = near HOA match + uploaded bill | kevin = no HOA match | mike = rejected

 One trip at every stage:
  1 End-of-October S&R run        DONE: full ledger, everyone collected, 1 resolved dispute
  2 Mid-week Landers run          CANCELLED after lock: P100 penalty split as apology credits (see Wallet)
  3 Friday Landers run            PICKUP: Maria & Mark collected, Grace & Paolo still waiting, 1 open dispute
  4 Saturday Landers stock-up     SHOPPING (Ana): Maria has a substitution to Approve/Decline
  5 Weekend S&R bulk order        FUNDING (Juan): Maria and Mark have not paid yet
  6 Saturday S&R run              OPEN (Juan): 4 families joined, Maria can join live
  7 Sunday public-market run      OPEN (Ana)

PANEL TOUR (about 6 minutes)
 1. Requester (Maria) Home: show the To-do box.
    - Approve the tuna substitute (trip 4).  - Pay P1,033 for trip 5.  - Join trip 6 and add rice.
    Open trip 1 to show "Final share" and the refund.
 2. Traveler (Juan) Home: one button per trip. Trip 5 -> "Remove 1 unpaid" (Mark) -> Review & lock
    -> Accept and Lock -> I'm at the store -> mark items -> Done. Open trip 1: ledger + "Your take".
 3. Ana (ana@demo.ph): trip 4 shopping checklist (try it on a phone) + upload a receipt photo; trip 3 pickup list.
 4. HOA Admin: Reports page (savings, charts, CSV). Dashboard tiles -> Residents (approve Bea after viewing her bill; Kevin needs proof)
    -> Travelers (approve Ramon) -> Wallet (confirm Rosa's top-up) -> Disputes (resolve Grace's).
 5. Optional: register a new account live with an HOA name (e.g. "Eduardo Jose Santiago", Blk 10 Lot 1)
    -> instantly approved.

PHOTOS (receipts, proof of residence): HOW THEY ARE STORED
 - The photo FILE is saved in public_html/uploads/ on the host. The DATABASE only keeps its file name
   (users.proof_file, trip_receipts.file). So: SQL in phpMyAdmin, PHP files + uploads/ folder in File Manager.
 - uploads/ is locked (uploads/.htaccess), so nobody can open a photo by guessing its address. Photos are shown
   through index.php?p=receipt&id=.. / ?p=proof&id=.., which first checks: HOA admin, the trip's traveler, or a
   family that paid into that trip.
 - Big phone photos are auto-rotated and shrunk to 1600px JPEG (~30-400 KB). Max upload 12 MB; JPG/PNG/WEBP/PDF only,
   checked by real file type. The traveler must upload at least one receipt before tapping Done.
 - Backups: download public_html/uploads/ together with the SQL export, or the photos are lost.

OTHER v7 FEATURES
 - "You saved" vs. regular supermarket price (items.retail_price, edit in Admin > Catalog). Placeholder prices!
 - Admin > Reports: delivered trips, families, money pooled, community savings, effort fees, charts, CSV exports.
 - Install on phone (Android "Install app", iPhone "Add to Home Screen"): manifest.webmanifest + sw.js. Needs HTTPS.

THE FORMULA (services/allocation.php)
 Share_i = C_i + (C_i / sum C) x F + EffortFee(C_i)
 Upfront = item estimate + 10% refundable buffer + estimated expense share + effort fee.
 Effort fee tiers, buffer, penalty, ban days, vehicle capacities: config/config.php

IF THIS BREAKS...                                  EDIT THIS ONE FILE
Database "Access denied" / wrong times             config/config.php
Instant approval / review queue wrong (HOA match)  services/verification.php
Request wrongly accepted/rejected (R1-R5)          services/matching.php
Pack leftover / round-up / trim wrong              services/consolidation.php
Upfront amount / effort fee wrong                  services/pricing.php
Photo upload / who can see receipts                services/uploads.php
"You saved" figure                                 services/savings.php
Report charts                                      services/charts.php  (page: pages/admin_reports.php)
Demo tour words / steps                            assets/tour.js
Help text per screen                               views/help.php
Guided mode hints (next step per screen)           views/guide.php + assets/guide.js
Text size / simple view / confirm dialogs          assets/app.js (+ end of assets/style.css)
Status words shown to users                        core/helpers.php (TAG_WORDS)
Logo / mascot images                               assets/brand/ (logo.png, chanelly.png, chanelly-sm.png)
Final share / refund / ledger amounts wrong        services/allocation.php
Status flow, locks, cancel penalty, 24h pickup     services/trips.php
Wallet balance                                     services/wallet.php
Who can open which page or action                  config/routes.php
Login / session / Traveler-Requester mode          core/auth.php
A specific button does nothing or errors           actions/<that button's name>.php
A screen looks wrong                               pages/<that screen>.php
A section of the trip screen looks wrong           views/trip/<section>.php
Top bar / menus                                    views/layout_top.php
Colors / fonts / spacing / alignment               assets/style.css
Rules & Legal wording                              pages/rules.php

When a page crashes, the error message shows the file and line (DEBUG on). Errors are also written to error.log.
