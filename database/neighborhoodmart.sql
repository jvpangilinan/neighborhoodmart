-- NeighborhoodMart v7: schema + full demo community. phpMyAdmin: select your database > Import > choose this file.
-- Import it ON (or at most 1 day before) the defense: dates are relative to the import time.
-- Receipt photos and the demo bill are files in uploads/ (upload that folder too).
-- Demo accounts (one-click buttons on the login page while DEMO_MODE is on in config/config.php):
--   HOA Admin  : admin     / admin123      (verifies residents, travelers, stores, wallet, disputes)
--   Traveler   : traveler  / traveler123   (Juan Roberto Dela Cruz Jr., approved traveler, S&R + Landers)
--   Requester  : requester / requester123  (Maria Clara Santos, ₱5,000 wallet credit)
--   Requester 2: requester2/ requester123  (Jose Miguel Reyes)
--   Other residents: <name>@demo.ph / demo123  (ana = 2nd traveler, liza, carlo, teresa, mark, grace, paolo, rosa, cristina,
--                    ramon = traveler application pending, bea/kevin = waiting for HOA review, mike = rejected)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS trip_receipts, notifications, audit, announcements, disputes, wallet_tx, order_lines, trip_members, trips,
  survey_votes, item_suggestions, item_prices, items, traveler_stores, traveler_areas, traveler_profiles,
  areas, stores, hoa_residents, users;
SET FOREIGN_KEY_CHECKS=1;

-- ---------- people ----------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(40) UNIQUE,                -- optional login name (admin uses "admin")
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  pass VARCHAR(255) NOT NULL,
  block VARCHAR(10), lot VARCHAR(10), street VARCHAR(100),
  phone VARCHAR(30),
  role ENUM('member','admin') DEFAULT 'member',         -- admin = HOA administrator
  verify_status ENUM('approved','pending','rejected') DEFAULT 'pending',  -- 3.1 HOA verification
  verify_note VARCHAR(255),                   -- how the match went / admin decision
  match_score INT DEFAULT 0,                  -- best fuzzy score vs HOA master list (0-100)
  proof_file VARCHAR(120),                    -- uploaded utility bill / village ID (stored outside public access)
  traveler_status ENUM('none','pending','approved','rejected') DEFAULT 'none',
  host_ban_until DATETIME NULL,               -- 6.2 late-dropout ban
  must_change_pw TINYINT DEFAULT 0,
  last_mode ENUM('traveler','requester') NULL, -- remembered UI, so the role picker shows only once
  guided TINYINT NULL,                        -- Guided mode: NULL = not asked yet, 1 = on, 0 = off
  consent TINYINT DEFAULT 0,                  -- Data Privacy Act consent
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE hoa_residents (                  -- official HOA master resident database (imported by admin)
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  block VARCHAR(10) NOT NULL,
  lot VARCHAR(10) NOT NULL
);

-- ---------- places (admin-managed options) ----------
CREATE TABLE stores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  card_required TINYINT DEFAULT 0,            -- traveler must hold a card to shop here
  bulk_ok ENUM('unverified','allowed','not_allowed') DEFAULT 'unverified', -- admin checked: buying for several people allowed?
  active TINYINT DEFAULT 1,                   -- shows in "post a trip"
  rules_note TEXT
);
CREATE TABLE areas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  active TINYINT DEFAULT 1
);

-- ---------- traveler registration (section 3) ----------
CREATE TABLE traveler_profiles (
  user_id INT PRIMARY KEY,
  other_stores VARCHAR(255),                  -- typed-in stores not yet in the options list
  other_areas VARCHAR(255),
  has_membership TINYINT DEFAULT 0,           -- active membership / subscription
  membership_store VARCHAR(100),
  has_card TINYINT DEFAULT 0,                 -- owns a valid membership card
  card_expiry DATE NULL,
  vehicle ENUM('motorcycle','sedan','suv') DEFAULT 'sedan',
  admin_note VARCHAR(255),
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE traveler_stores (user_id INT, store_id INT, PRIMARY KEY(user_id,store_id));
CREATE TABLE traveler_areas  (user_id INT, area_id INT,  PRIMARY KEY(user_id,area_id));

-- ---------- generic catalog + checked prices (sections 4-5) ----------
CREATE TABLE items (                          -- generic item + packaging, no fixed brand
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,                 -- e.g. "Fresh Eggs Large"
  packaging VARCHAR(80),                      -- e.g. "1 tray of 30"
  category VARCHAR(50),
  section VARCHAR(50),                        -- store section, used for the in-store route
  pack_size INT DEFAULT 1,                    -- units per sellable pack (C2 box = 8)
  load_units INT DEFAULT 1,                   -- vehicle space one unit takes (rice sack = 10)
  max_per_member INT DEFAULT 0,               -- per-member limit per trip (0 = none)
  retail_price DECIMAL(10,2) DEFAULT 0,       -- regular supermarket price per unit, for the "you saved" figure
  active TINYINT DEFAULT 1
);
CREATE TABLE item_prices (                    -- latest price a traveler checked at a store
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_id INT NOT NULL,
  store_id INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  brand VARCHAR(80),                          -- brand seen on the shelf
  checked_by INT,
  checked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (item_id,store_id),
  FOREIGN KEY (item_id) REFERENCES items(id),
  FOREIGN KEY (store_id) REFERENCES stores(id)
);
CREATE TABLE survey_votes (                   -- "I need this" demand board
  user_id INT, item_id INT, qty INT DEFAULT 1,
  voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(user_id,item_id)
);
CREATE TABLE item_suggestions (
  id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, name VARCHAR(120), packaging VARCHAR(80),
  status ENUM('new','added','dismissed') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------- trips ----------
CREATE TABLE trips (
  id INT AUTO_INCREMENT PRIMARY KEY,
  traveler_id INT NOT NULL,
  store_id INT NOT NULL,
  area_id INT NULL,
  title VARCHAR(120),
  go_date DATE,
  window_closes_at DATETIME NOT NULL,         -- R3
  vehicle ENUM('motorcycle','sedan','suv') DEFAULT 'sedan',
  capacity INT DEFAULT 80,                    -- R4: load units, preset by vehicle
  expenses DECIMAL(10,2) DEFAULT 0,           -- F: gas, toll, parking (shared proportionally)
  status ENUM('open','funding','ready','locked','shopping','delivered','settled','cancelled') DEFAULT 'open',
  locked_at DATETIME NULL, delivered_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (traveler_id) REFERENCES users(id),
  FOREIGN KEY (store_id) REFERENCES stores(id)
);
CREATE TABLE trip_members (                   -- one row per requester per trip = their pooled order + ledger line
  id INT AUTO_INCREMENT PRIMARY KEY,
  trip_id INT NOT NULL, user_id INT NOT NULL,
  est_items DECIMAL(10,2) DEFAULT 0,          -- after consolidation
  buffer DECIMAL(10,2) DEFAULT 0,             -- refundable +10%
  est_expense DECIMAL(10,2) DEFAULT 0,
  est_effort DECIMAL(10,2) DEFAULT 0,
  due DECIMAL(10,2) DEFAULT 0,                -- amount held upfront
  paid DECIMAL(10,2) DEFAULT 0,
  item_cost DECIMAL(10,2) NULL,               -- Ci (actual)
  expense_share DECIMAL(10,2) NULL,           -- (Ci / sum C) x F
  effort_fee DECIMAL(10,2) NULL,              -- tier on Ci
  share DECIMAL(10,2) NULL,                   -- Ci + expense_share + effort_fee
  balance DECIMAL(10,2) NULL,                 -- paid - share (+ refunded / - collected)
  status ENUM('requesting','unpaid','funded','dropped','cancelled','settled') DEFAULT 'requesting',
  pickup ENUM('waiting','collected','uncollected') DEFAULT 'waiting',
  UNIQUE KEY (trip_id,user_id),
  FOREIGN KEY (trip_id) REFERENCES trips(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE order_lines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trip_id INT NOT NULL, user_id INT NOT NULL, item_id INT NOT NULL,
  qty INT NOT NULL,
  accept_roundup TINYINT DEFAULT 0,
  allow_sub TINYINT DEFAULT 1,                -- OK to swap brand, same tier and same package size
  est_price DECIMAL(10,2) DEFAULT 0,          -- unit price snapshot
  final_qty INT NULL,                         -- after pack consolidation
  status ENUM('matched','rejected','trimmed','cancelled') DEFAULT 'matched',
  reason VARCHAR(160),
  shop ENUM('todo','bought','substituted','needs_ok','declined','unavailable') DEFAULT 'todo',
  actual_price DECIMAL(10,2) NULL,
  bought_brand VARCHAR(80), alert_note VARCHAR(160),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (trip_id) REFERENCES trips(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (item_id) REFERENCES items(id)
);

CREATE TABLE trip_receipts (               -- receipt photos: FILE lives in /uploads, only its name is stored here
  id INT AUTO_INCREMENT PRIMARY KEY,
  trip_id INT NOT NULL,
  uploaded_by INT NOT NULL,
  file VARCHAR(120) NOT NULL,
  note VARCHAR(160),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (trip_id) REFERENCES trips(id)
);

-- ---------- money (ledger credits: the app records money, it does not hold it) ----------
CREATE TABLE wallet_tx (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  trip_id INT NULL,
  amount DECIMAL(10,2) NOT NULL,              -- + credit, - debit
  kind ENUM('topup','cashout','trip_hold','refund','collect','payout','penalty','apology','release') NOT NULL,
  method ENUM('gcash','cash','system') DEFAULT 'system',
  ref VARCHAR(60),
  status ENUM('pending','done','rejected') DEFAULT 'done',  -- top-ups/cash-outs wait for admin
  note VARCHAR(160),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ---------- community ----------
CREATE TABLE disputes (
  id INT AUTO_INCREMENT PRIMARY KEY, trip_id INT NOT NULL, user_id INT NOT NULL, message TEXT,
  status ENUM('open','resolved') DEFAULT 'open', resolution TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE announcements (
  id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(120), body TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, msg VARCHAR(255), link VARCHAR(120),
  seen TINYINT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE audit (
  id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, action VARCHAR(200), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


SET time_zone='+08:00'; -- same clock the app uses (config TIMEZONE)
-- ================= DEMO DATA (generated by playing every user through the app) =================
-- All dates are relative to the moment you import this file, so open trips stay open on defense day.
INSERT INTO users (id,username,name,email,pass,block,lot,street,phone,role,verify_status,verify_note,match_score,proof_file,traveler_status,host_ban_until,must_change_pw,last_mode,consent,created_at) VALUES
(1,'admin','HOA Administrator','admin@neighborhoodmart.local','$2y$10$.5To0sVfh0wYP1x/KEDOUumGVSu7uwZyKThG5FoQywvczeyMhA9/i','0','0','HOA Office','','admin','approved','Built-in admin',100,NULL,'none',NULL,0,NULL,1,NOW()+INTERVAL -1728004 SECOND),
(2,'traveler','Juan Roberto Dela Cruz Jr.','traveler@neighborhoodmart.local','$2y$10$PofS7UXQIhpYv/I2WuNNruAzHFbZ0B424Ps8S.hf2tZXgiCjgfcmO','3','12','Mabini St.','09171234567','member','approved','Exact match with HOA record',100,NULL,'approved',NULL,0,'traveler',1,NOW()+INTERVAL -1728004 SECOND),
(3,'requester','Maria Clara Santos','requester@neighborhoodmart.local','$2y$10$ZtK7qpfOPWOfOGPuUCHi0.oM05JQ5V.H8lqY341uFN63qPUu1VksC','5','4','Rizal St.','09181234567','member','approved','Exact match with HOA record',100,NULL,'none',NULL,0,'requester',1,NOW()+INTERVAL -1728004 SECOND),
(4,'requester2','Jose Miguel Reyes','requester2@neighborhoodmart.local','$2y$10$ZtK7qpfOPWOfOGPuUCHi0.oM05JQ5V.H8lqY341uFN63qPUu1VksC','2','18','Luna St.','09191234567','member','approved','Exact match with HOA record',100,NULL,'none',NULL,0,'requester',1,NOW()+INTERVAL -1728004 SECOND),
(5,NULL,'Ana Liza Mendoza','ana@demo.ph','$2y$10$JCjS9d7dEtbJ8SjkXEnYSeBFDl2oLBtiwnLiPUClBtG/zV8KIKdsO','7','9','Soluna Executive Village','09177411485','member','approved','Exact match with HOA record: Ana Liza Mendoza, Blk 7 Lot 9',100,'','approved',NOW()+INTERVAL -288001 SECOND,0,'traveler',1,NOW()+INTERVAL -1728004 SECOND),
(6,NULL,'Ramon Antonio Garcia','ramon@demo.ph','$2y$10$Kf./JYT1F1yrCPbXq0hZyOSpajLcWkLERKcTNZsMjQH2eNSSI/E9S','1','6','Soluna Executive Village','09173161432','member','approved','Exact match with HOA record: Ramon Antonio Garcia, Blk 1 Lot 6',100,'','pending',NULL,0,'requester',1,NOW()+INTERVAL -1728004 SECOND),
(7,NULL,'Liza Marie Bautista','liza@demo.ph','$2y$10$tt9xZW/VV3pWlPGsvWVoSON/bZryIVYPbm2ZQy/gV94PvUu78plCO','4','15','Soluna Executive Village','09172184215','member','approved','Exact match with HOA record: Liza Marie Bautista, Blk 4 Lot 15',100,'','none',NULL,0,'requester',1,NOW()+INTERVAL -1728004 SECOND),
(8,NULL,'Carlo Benedict Villanueva','carlo@demo.ph','$2y$10$T9K5HDCD9Hmwp3sqQtrycOmUhOw6PwY6OixuoDRO9jbODCHHI.9hK','6','3','Soluna Executive Village','09175927565','member','approved','Exact match with HOA record: Carlo Benedict Villanueva, Blk 6 Lot 3',100,'','none',NULL,0,'requester',1,NOW()+INTERVAL -1728004 SECOND),
(9,NULL,'Teresa Gomez Aquino','teresa@demo.ph','$2y$10$iU/wcWMAwKvDIXSuse.DxeUfcv0u7.blXzqf1duntOt8C1T8Lg0Mq','8','21','Soluna Executive Village','09173051912','member','approved','Exact match with HOA record: Teresa Gomez Aquino, Blk 8 Lot 21',100,'','none',NULL,0,'requester',1,NOW()+INTERVAL -1728004 SECOND),
(10,NULL,'Mark Anthony Ramos','mark@demo.ph','$2y$10$Ci8ZCt/dK2i6Kth.FeB8sOVJiri.FqszMkrt9aVrARbnDmdfb5JLm','2','7','Soluna Executive Village','09171517903','member','approved','Exact match with HOA record: Mark Anthony Ramos, Blk 2 Lot 7',100,'','none',NULL,0,'requester',1,NOW()+INTERVAL -1728004 SECOND),
(11,NULL,'Grace Ann Navarro','grace@demo.ph','$2y$10$Xx1Ic/MqsgNzQm/WXEstS.ecu6FxvFYwFodncGTtwb2ixjL79x6TG','9','11','Soluna Executive Village','09176422887','member','approved','Exact match with HOA record: Grace Ann Navarro, Blk 9 Lot 11',100,'','none',NULL,0,'requester',1,NOW()+INTERVAL -1728004 SECOND),
(12,NULL,'Paolo Miguel Fernandez','paolo@demo.ph','$2y$10$2akZDv.jMP3hXd3Seph78OkSGgozPRm/fp5DG2WbzFdjfxDUkB8ty','5','16','Soluna Executive Village','09173622521','member','approved','Exact match with HOA record: Paolo Miguel Fernandez, Blk 5 Lot 16',100,'','none',NULL,0,'requester',1,NOW()+INTERVAL -1728004 SECOND),
(13,NULL,'Rosa Linda Castillo','rosa@demo.ph','$2y$10$Z4EuW.aWR46Dt07M97DAROR3QhUPFT82pYP2h/NmpKpmUO8u2Oxm6','3','2','Soluna Executive Village','09172230666','member','approved','Exact match with HOA record: Rosa Linda Castillo, Blk 3 Lot 2',100,'','none',NULL,0,'requester',1,NOW()+INTERVAL -1728004 SECOND),
(14,NULL,'Cristina Mae Lopez','cristina@demo.ph','$2y$10$ZZEFJn45WFiU4SSiGibOGu7JEfgeoHNs65.eYsfTt0P84dMdXwQWy','4','3','Soluna Executive Village','09174780564','member','approved','Exact match with HOA record: Cristina Mae Lopez, Blk 4 Lot 3',100,'','none',NULL,0,'requester',1,NOW()+INTERVAL -1728004 SECOND),
(15,NULL,'Bea Torres','bea@demo.ph','$2y$10$IiaKHi0OxSd6sTN..h5NpuPYUB5JqrCuJl6JkHN5bLjdxDN2r2rH6','6','8','Soluna Executive Village','','member','pending','Near match (66%): Beatriz Anne Torres, Blk 6 Lot 8. Needs manual review.',66,'demo_bill.png','none',NULL,0,NULL,1,NOW()+INTERVAL -1728004 SECOND),
(16,NULL,'Kevin Lim','kevin@demo.ph','$2y$10$n72cCn5.1M5my1egzQQ3cOS2P2R.hJ9xaRTCPcltia6.vftwIP6dG','11','20','Outside village?','','member','pending','No close HOA record found. Needs manual review of proof of residence.',0,'','none',NULL,0,NULL,1,NOW()+INTERVAL -1728004 SECOND),
(17,NULL,'Mike Santos','mike@demo.ph','$2y$10$Nv40MnsZiNLEmnRRq9E3EecEyKmfyHfx3rESr7m589ttxcIKbNza.','99','99','','','member','rejected','No close HOA record found. Needs manual review of proof of residence. | Admin: rejected - No HOA record and no proof of residence. Please visit the HOA office.',26,'','none',NULL,0,NULL,1,NOW()+INTERVAL -1728004 SECOND);
INSERT INTO hoa_residents (id,full_name,block,lot) VALUES
(1,'Juan Roberto Dela Cruz Jr.','3','12'),
(2,'Maria Clara Santos','5','4'),
(3,'Jose Miguel Reyes','2','18'),
(4,'Ana Liza Mendoza','7','9'),
(5,'Ramon Antonio Garcia','1','6'),
(6,'Liza Marie Bautista','4','15'),
(7,'Carlo Benedict Villanueva','6','3'),
(8,'Teresa Gomez Aquino','8','21'),
(9,'Mark Anthony Ramos','2','7'),
(10,'Grace Ann Navarro','9','11'),
(11,'Paolo Miguel Fernandez','5','16'),
(12,'Rosa Linda Castillo','3','2'),
(13,'Beatriz Anne Torres','6','8'),
(14,'Eduardo Jose Santiago','10','1'),
(15,'Cristina Mae Lopez','4','3');
INSERT INTO stores (id,name,card_required,bulk_ok,active,rules_note) VALUES
(1,'S&R Membership Shopping',1,'allowed',1,'Confirmed in writing: one member may buy for several households; rice limited to 2 sacks per member.'),
(2,'Landers Superstore',1,'allowed',1,'Confirmed by phone and e-mail with Landers Alabang: members may buy for others; max 3 sacks of rice per card.'),
(3,'Local public market',0,'allowed',1,'No membership needed.'),
(4,'PureGold Price Club (Molino)',1,'unverified',1,'HOA still waiting for written reply.');
INSERT INTO areas (id,name,active) VALUES
(1,'S&R Dr. A. Santos Ave (Parañaque)',1),
(2,'Landers Alabang West',1),
(3,'Zapote Public Market (Bacoor)',1);
INSERT INTO traveler_profiles (user_id,other_stores,other_areas,has_membership,membership_store,has_card,card_expiry,vehicle,admin_note,submitted_at) VALUES
(2,NULL,NULL,1,'S&R',1,CURDATE()+INTERVAL 364 DAY,'sedan','Membership card checked by the HOA',NOW()+INTERVAL -5 SECOND),
(5,'PureGold Price Club Molino','',1,'Landers',1,CURDATE()+INTERVAL 200 DAY,'suv','Landers card valid until next year. SUV.',NOW()+INTERVAL -3 SECOND),
(6,'','Divisoria (Manila)',1,'',1,NULL,'motorcycle',NULL,NOW()+INTERVAL -3 SECOND);
INSERT INTO traveler_stores (user_id,store_id) VALUES
(2,1),
(2,2),
(5,2),
(5,3),
(5,4),
(6,1);
INSERT INTO traveler_areas (user_id,area_id) VALUES
(2,1),
(2,2),
(5,2),
(5,3),
(6,1);
INSERT INTO items (id,name,packaging,category,section,pack_size,load_units,max_per_member,retail_price,active) VALUES
(1,'Fresh Eggs Large','1 tray of 30','Fresh','Fresh',1,2,0,315.00,1),
(2,'Whole Fresh Milk 1L','case of 6','Dairy','Dairy',6,1,0,112.00,1),
(3,'Green Tea 500ml','box of 8','Beverages','Beverages',8,1,0,38.00,1),
(4,'Well-milled Rice','25kg sack','Dry goods','Dry goods',1,10,2,1475.00,1),
(5,'Canned Tuna 155g','pack of 10','Canned goods','Canned goods',10,1,0,44.00,1),
(6,'Cooking Oil','5L bottle','Dry goods','Dry goods',1,3,0,795.00,1),
(7,'Laundry Detergent Powder','pack of 4','Household','Household',4,1,0,182.00,1);
INSERT INTO item_prices (id,item_id,store_id,unit_price,brand,checked_by,checked_at) VALUES
(1,1,1,265.00,'Bounty Fresh',2,NOW()+INTERVAL -3 SECOND),
(2,1,2,259.00,'Club Brand',5,NOW()+INTERVAL -3 SECOND),
(3,1,3,245.00,'Local farm',5,NOW()+INTERVAL -2 SECOND),
(4,2,1,92.00,'Kirkland',2,NOW()+INTERVAL -3 SECOND),
(5,2,2,89.50,'Harvey Fresh',5,NOW()+INTERVAL -3 SECOND),
(6,3,1,30.00,'C2',1,NOW()+INTERVAL -28805 SECOND),
(7,3,2,29.50,'C2',1,NOW()+INTERVAL -28805 SECOND),
(8,4,1,1250.00,'Jasmine',2,NOW()+INTERVAL -3 SECOND),
(9,4,2,1230.00,'Dinorado',5,NOW()+INTERVAL -2 SECOND),
(10,4,3,1180.00,'Local NFA',5,NOW()+INTERVAL -2 SECOND),
(11,5,1,35.00,'Century',1,NOW()+INTERVAL -28805 SECOND),
(12,5,2,34.00,'Century',5,NOW()+INTERVAL -2 SECOND),
(13,6,1,650.00,'Baguio Oil',1,NOW()+INTERVAL -28805 SECOND),
(14,6,2,640.00,'Minola',5,NOW()+INTERVAL -3 SECOND),
(15,7,1,150.00,'Tide',1,NOW()+INTERVAL -28805 SECOND),
(16,7,2,145.00,'Ariel',5,NOW()+INTERVAL -2 SECOND);
INSERT INTO survey_votes (user_id,item_id,qty,voted_at) VALUES
(3,2,1,NOW()+INTERVAL -2 SECOND),
(3,3,1,NOW()+INTERVAL -2 SECOND),
(3,4,6,NOW()+INTERVAL -2 SECOND),
(4,1,1,NOW()+INTERVAL -2 SECOND),
(4,3,6,NOW()+INTERVAL -2 SECOND),
(4,5,1,NOW()+INTERVAL -2 SECOND),
(5,1,3,NOW()+INTERVAL -2 SECOND),
(5,4,1,NOW()+INTERVAL -2 SECOND),
(5,7,1,NOW()+INTERVAL -2 SECOND),
(6,1,1,NOW()+INTERVAL -2 SECOND),
(6,4,1,NOW()+INTERVAL -2 SECOND),
(6,5,6,NOW()+INTERVAL -2 SECOND),
(7,2,1,NOW()+INTERVAL -2 SECOND),
(7,5,6,NOW()+INTERVAL -2 SECOND),
(7,6,6,NOW()+INTERVAL -2 SECOND),
(8,1,6,NOW()+INTERVAL -2 SECOND),
(8,2,1,NOW()+INTERVAL -2 SECOND),
(8,4,1,NOW()+INTERVAL -2 SECOND),
(9,2,6,NOW()+INTERVAL -2 SECOND),
(9,3,6,NOW()+INTERVAL -2 SECOND),
(9,4,1,NOW()+INTERVAL -2 SECOND),
(10,2,6,NOW()+INTERVAL -2 SECOND),
(10,3,1,NOW()+INTERVAL -2 SECOND),
(10,5,6,NOW()+INTERVAL -2 SECOND),
(11,2,6,NOW()+INTERVAL -2 SECOND),
(11,3,1,NOW()+INTERVAL -2 SECOND),
(11,6,1,NOW()+INTERVAL -2 SECOND),
(12,1,3,NOW()+INTERVAL -2 SECOND),
(12,5,1,NOW()+INTERVAL -2 SECOND),
(12,7,6,NOW()+INTERVAL -2 SECOND),
(13,3,3,NOW()+INTERVAL -2 SECOND),
(13,4,6,NOW()+INTERVAL -2 SECOND),
(13,7,2,NOW()+INTERVAL -2 SECOND),
(14,2,1,NOW()+INTERVAL -2 SECOND),
(14,3,1,NOW()+INTERVAL -2 SECOND),
(14,6,6,NOW()+INTERVAL -2 SECOND);
INSERT INTO item_suggestions (id,user_id,name,packaging,status,created_at) VALUES
(1,11,'Frozen Chicken Breast','2kg pack','new',NOW()+INTERVAL -2 SECOND),
(2,13,'Bath Tissue','12 rolls','new',NOW()+INTERVAL -2 SECOND);
INSERT INTO trips (id,traveler_id,store_id,area_id,title,go_date,window_closes_at,vehicle,capacity,expenses,status,locked_at,delivered_at,created_at) VALUES
(1,2,1,1,'End-of-October S&R run',CURDATE()+INTERVAL -15 DAY,NOW()+INTERVAL -1314002 SECOND,'sedan',80,250.00,'settled',NOW()+INTERVAL -1249202 SECOND,NOW()+INTERVAL -1227602 SECOND,NOW()+INTERVAL -1458002 SECOND),
(2,5,2,2,'Mid-week Landers run',CURDATE()+INTERVAL -10 DAY,NOW()+INTERVAL -928801 SECOND,'suv',150,150.00,'cancelled',NOW()+INTERVAL -864001 SECOND,NULL,NOW()+INTERVAL -1072801 SECOND),
(3,5,2,2,'Friday Landers run',CURDATE()+INTERVAL -1 DAY,NOW()+INTERVAL -115201 SECOND,'suv',150,300.00,'delivered',NOW()+INTERVAL -50401 SECOND,NOW()+INTERVAL -28801 SECOND,NOW()+INTERVAL -259201 SECOND),
(4,5,2,2,'Saturday Landers stock-up',CURDATE()+INTERVAL -1 DAY,NOW()+INTERVAL -72001 SECOND,'suv',150,200.00,'shopping',NOW()+INTERVAL -7201 SECOND,NULL,NOW()+INTERVAL -216001 SECOND),
(5,2,1,1,'Weekend S&R bulk order',CURDATE()+INTERVAL 0 DAY,NOW()+INTERVAL -18000 SECOND,'sedan',80,220.00,'funding',NULL,NULL,NOW()+INTERVAL -162000 SECOND),
(6,2,1,1,'Saturday S&R run',CURDATE()+INTERVAL 2 DAY,NOW()+INTERVAL 144000 SECOND,'sedan',80,200.00,'open',NULL,NULL,NOW()+INTERVAL -18000 SECOND),
(7,5,3,3,'Sunday public-market run (fresh goods)',CURDATE()+INTERVAL 3 DAY,NOW()+INTERVAL 230400 SECOND,'suv',150,120.00,'open',NULL,NULL,NOW()+INTERVAL -18000 SECOND);
INSERT INTO trip_members (id,trip_id,user_id,est_items,buffer,est_expense,est_effort,due,paid,item_cost,expense_share,effort_fee,share,balance,status,pickup) VALUES
(1,1,3,1430.00,143.00,93.17,100.00,1766.17,1766.17,1445.00,112.89,100.00,1657.89,108.28,'settled','collected'),
(3,1,4,60.00,6.00,3.91,50.00,119.91,119.91,60.00,4.69,50.00,114.69,5.22,'settled','collected'),
(4,1,7,1082.00,108.20,70.50,100.00,1360.70,1360.70,1070.00,83.59,100.00,1253.59,107.11,'settled','collected'),
(6,1,8,350.00,35.00,22.80,50.00,457.80,457.80,360.00,28.13,50.00,438.13,19.67,'settled','collected'),
(7,1,9,915.00,91.50,59.62,100.00,1166.12,1166.12,265.00,20.70,50.00,335.70,830.42,'settled','collected'),
(9,2,10,1230.00,123.00,77.26,100.00,1530.26,1530.26,NULL,NULL,NULL,NULL,NULL,'cancelled','waiting'),
(10,2,11,518.00,51.80,32.54,100.00,702.34,702.34,NULL,NULL,NULL,NULL,NULL,'cancelled','waiting'),
(11,2,12,640.00,64.00,40.20,100.00,844.20,844.20,NULL,NULL,NULL,NULL,NULL,'cancelled','waiting'),
(12,3,3,537.00,53.70,33.82,100.00,724.52,724.52,537.00,35.60,100.00,672.60,51.92,'settled','collected'),
(13,3,10,1489.00,148.90,93.79,100.00,1831.69,1831.69,1230.00,81.55,100.00,1411.55,420.14,'settled','collected'),
(15,3,11,1117.00,111.70,70.35,100.00,1399.05,1399.05,1108.00,73.46,100.00,1281.46,117.59,'settled','waiting'),
(17,3,12,1620.00,162.00,102.04,150.00,2034.04,2034.04,1650.00,109.39,150.00,1909.39,124.65,'settled','waiting'),
(19,4,3,599.00,59.90,34.58,100.00,793.48,793.48,NULL,NULL,NULL,NULL,NULL,'funded','waiting'),
(21,4,4,1230.00,123.00,71.02,100.00,1524.02,1524.02,NULL,NULL,NULL,NULL,NULL,'funded','waiting'),
(22,4,7,580.00,58.00,33.49,100.00,771.49,771.49,NULL,NULL,NULL,NULL,NULL,'funded','waiting'),
(23,4,13,1055.00,105.50,60.91,100.00,1321.41,1321.41,NULL,NULL,NULL,NULL,NULL,'funded','waiting'),
(25,5,8,1250.00,125.00,97.80,100.00,1572.80,1572.80,NULL,NULL,NULL,NULL,NULL,'funded','waiting'),
(26,5,9,240.00,24.00,18.78,50.00,332.78,332.78,NULL,NULL,NULL,NULL,NULL,'funded','waiting'),
(27,5,3,792.00,79.20,61.96,100.00,1033.16,0.00,NULL,NULL,NULL,NULL,NULL,'unpaid','waiting'),
(29,5,10,530.00,53.00,41.47,100.00,724.47,0.00,NULL,NULL,NULL,NULL,NULL,'unpaid','waiting'),
(30,6,4,355.00,35.50,26.42,50.00,466.92,0.00,NULL,NULL,NULL,NULL,NULL,'requesting','waiting'),
(32,6,7,1250.00,125.00,93.04,100.00,1568.04,0.00,NULL,NULL,NULL,NULL,NULL,'requesting','waiting'),
(33,6,11,530.00,53.00,39.45,100.00,722.45,0.00,NULL,NULL,NULL,NULL,NULL,'requesting','waiting'),
(34,6,13,552.00,55.20,41.09,100.00,748.29,0.00,NULL,NULL,NULL,NULL,NULL,'requesting','waiting'),
(35,7,12,490.00,49.00,30.70,50.00,619.70,0.00,NULL,NULL,NULL,NULL,NULL,'requesting','waiting'),
(36,7,9,1180.00,118.00,73.94,100.00,1471.94,0.00,NULL,NULL,NULL,NULL,NULL,'requesting','waiting'),
(37,7,14,245.00,24.50,15.35,50.00,334.85,0.00,NULL,NULL,NULL,NULL,NULL,'requesting','waiting');
INSERT INTO order_lines (id,trip_id,user_id,item_id,qty,accept_roundup,allow_sub,est_price,final_qty,status,reason,shop,actual_price,bought_brand,alert_note,created_at) VALUES
(1,1,3,4,1,0,1,1250.00,1,'matched','','bought',1265.00,'Jasmine',NULL,NOW()+INTERVAL -1227602 SECOND),
(2,1,3,3,5,1,1,30.00,6,'matched','Rounded up to complete a pack','bought',30.00,'Same brand',NULL,NOW()+INTERVAL -1227602 SECOND),
(3,1,4,3,2,0,1,30.00,2,'matched','','bought',30.00,'Same brand',NULL,NOW()+INTERVAL -1227602 SECOND),
(4,1,7,1,2,0,1,265.00,2,'matched','','bought',265.00,'Same brand',NULL,NOW()+INTERVAL -1227602 SECOND),
(5,1,7,2,6,0,1,92.00,6,'matched','','substituted',90.00,'Harvey Fresh','Substitute: Whole Fresh Milk 1L → Harvey Fresh at ₱90.00 per unit',NOW()+INTERVAL -1227602 SECOND),
(6,1,8,5,10,0,1,35.00,10,'matched','','bought',36.00,'Century',NULL,NOW()+INTERVAL -1227602 SECOND),
(7,1,9,1,1,0,1,265.00,1,'matched','','bought',265.00,'Same brand',NULL,NOW()+INTERVAL -1227602 SECOND),
(8,1,9,6,1,0,1,650.00,1,'matched','','unavailable',NULL,NULL,'Out of stock',NOW()+INTERVAL -1227602 SECOND),
(9,2,10,4,1,0,1,1230.00,1,'cancelled','','todo',NULL,NULL,NULL,NOW()+INTERVAL -864001 SECOND),
(10,2,11,1,2,0,1,259.00,2,'cancelled','','todo',NULL,NULL,NULL,NOW()+INTERVAL -864001 SECOND),
(11,2,12,6,1,0,1,640.00,1,'cancelled','','todo',NULL,NULL,NULL,NOW()+INTERVAL -864001 SECOND),
(12,3,3,2,6,0,1,89.50,6,'matched','','bought',89.50,'Same brand',NULL,NOW()+INTERVAL -28801 SECOND),
(13,3,10,4,1,0,1,1230.00,1,'matched','','bought',1230.00,'Same brand',NULL,NOW()+INTERVAL -28801 SECOND),
(14,3,10,1,1,0,1,259.00,1,'matched','','unavailable',NULL,NULL,'Out of stock',NOW()+INTERVAL -28801 SECOND),
(15,3,11,2,6,0,1,89.50,6,'matched','','substituted',88.00,'Kirkland Whole Milk','Substitute: Whole Fresh Milk 1L → Kirkland Whole Milk at ₱88.00 per unit',NOW()+INTERVAL -28801 SECOND),
(16,3,11,7,4,0,1,145.00,4,'matched','','bought',145.00,'Same brand',NULL,NOW()+INTERVAL -28801 SECOND),
(17,3,12,6,2,0,1,640.00,2,'matched','','bought',655.00,'Baguio Oil',NULL,NOW()+INTERVAL -28801 SECOND),
(18,3,12,5,10,0,1,34.00,10,'matched','','bought',34.00,'Same brand',NULL,NOW()+INTERVAL -28801 SECOND),
(19,4,3,5,10,0,1,34.00,10,'matched','','needs_ok',38.00,'555 Tuna','Substitute: Canned Tuna 155g → 555 Tuna (different package size: pack of 8) at ₱38.00 per unit',NOW()+INTERVAL -7201 SECOND),
(20,4,3,1,1,0,1,259.00,1,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL -7201 SECOND),
(21,4,4,4,1,0,1,1230.00,1,'matched','','bought',1230.00,'Dinorado',NULL,NOW()+INTERVAL -7201 SECOND),
(22,4,7,7,4,0,1,145.00,4,'matched','','bought',145.00,'Ariel',NULL,NOW()+INTERVAL -7201 SECOND),
(23,4,13,2,6,0,1,89.50,6,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL -7201 SECOND),
(24,4,13,1,2,0,1,259.00,2,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL -7201 SECOND),
(25,5,8,4,1,0,1,1250.00,1,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL -18001 SECOND),
(26,5,9,3,8,0,1,30.00,8,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL -18001 SECOND),
(27,5,3,2,6,0,1,92.00,6,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL -18000 SECOND),
(28,5,3,3,4,1,1,30.00,8,'matched','Rounded up to complete a pack','todo',NULL,NULL,NULL,NOW()+INTERVAL -18000 SECOND),
(29,5,10,1,2,0,1,265.00,2,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL -18000 SECOND),
(30,6,4,3,3,1,1,30.00,NULL,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL 0 SECOND),
(31,6,4,1,1,0,1,265.00,NULL,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL 0 SECOND),
(32,6,7,4,1,0,1,1250.00,NULL,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL 0 SECOND),
(33,6,11,1,2,0,1,265.00,NULL,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL 0 SECOND),
(34,6,13,2,6,0,1,92.00,NULL,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL 0 SECOND),
(35,7,12,1,2,0,1,245.00,NULL,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL 0 SECOND),
(36,7,9,4,1,0,1,1180.00,NULL,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL 0 SECOND),
(37,7,14,1,1,0,1,245.00,NULL,'matched','','todo',NULL,NULL,NULL,NOW()+INTERVAL 0 SECOND);
INSERT INTO trip_receipts (id,trip_id,uploaded_by,file,note,created_at) VALUES
(1,1,2,'demo_receipt_1.jpg','Store receipt',NOW()+INTERVAL -2 SECOND),
(2,3,5,'demo_receipt_2.jpg','Store receipt',NOW()+INTERVAL -1 SECOND),
(3,4,5,'demo_receipt_3.jpg','Receipt (partial, still shopping)',NOW()+INTERVAL -1 SECOND);
INSERT INTO wallet_tx (id,user_id,trip_id,amount,kind,method,ref,status,note,created_at) VALUES
(1,3,NULL,8000.00,'topup','gcash','9643421581089','done','Top-up request',NOW()+INTERVAL -1382402 SECOND),
(2,4,NULL,5000.00,'topup','gcash','9592759215392','done','Top-up request',NOW()+INTERVAL -1382402 SECOND),
(3,5,NULL,4000.00,'topup','gcash','9229163414222','done','Top-up request',NOW()+INTERVAL -1382402 SECOND),
(4,6,NULL,5000.00,'topup','gcash','9929637194964','done','Top-up request',NOW()+INTERVAL -1382402 SECOND),
(5,7,NULL,4000.00,'topup','gcash','9561661581186','done','Top-up request',NOW()+INTERVAL -1382402 SECOND),
(6,8,NULL,3000.00,'topup','gcash','9838571248116','done','Top-up request',NOW()+INTERVAL -1382402 SECOND),
(7,9,NULL,3000.00,'topup','gcash','9713169162910','done','Top-up request',NOW()+INTERVAL -1382402 SECOND),
(8,10,NULL,4000.00,'topup','gcash','9861670025794','done','Top-up request',NOW()+INTERVAL -1382402 SECOND),
(9,11,NULL,4000.00,'topup','gcash','9648013645773','done','Top-up request',NOW()+INTERVAL -1382402 SECOND),
(10,12,NULL,5000.00,'topup','gcash','9620850423169','done','Top-up request',NOW()+INTERVAL -1382402 SECOND),
(11,13,NULL,3000.00,'topup','gcash','9903419457547','done','Top-up request',NOW()+INTERVAL -1382402 SECOND),
(12,14,NULL,4000.00,'topup','gcash','9734139589761','done','Top-up request',NOW()+INTERVAL -1382402 SECOND),
(13,3,1,-1766.17,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -1227602 SECOND),
(14,4,1,-119.91,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -1227602 SECOND),
(15,7,1,-1360.70,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -1227602 SECOND),
(16,8,1,-457.80,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -1227602 SECOND),
(17,9,1,-1166.12,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -1227602 SECOND),
(18,2,1,4870.70,'release','system','','done','Pooled funds released for the store purchase',NOW()+INTERVAL -1227602 SECOND),
(19,3,1,108.28,'refund','system','','done','Unused buffer / unavailable items',NOW()+INTERVAL -1227602 SECOND),
(20,2,1,-108.28,'refund','system','','done','Refund to requester #3',NOW()+INTERVAL -1227602 SECOND),
(21,4,1,5.22,'refund','system','','done','Unused buffer / unavailable items',NOW()+INTERVAL -1227602 SECOND),
(22,2,1,-5.22,'refund','system','','done','Refund to requester #4',NOW()+INTERVAL -1227602 SECOND),
(23,7,1,107.11,'refund','system','','done','Unused buffer / unavailable items',NOW()+INTERVAL -1227602 SECOND),
(24,2,1,-107.11,'refund','system','','done','Refund to requester #7',NOW()+INTERVAL -1227602 SECOND),
(25,8,1,19.67,'refund','system','','done','Unused buffer / unavailable items',NOW()+INTERVAL -1227602 SECOND),
(26,2,1,-19.67,'refund','system','','done','Refund to requester #8',NOW()+INTERVAL -1227602 SECOND),
(27,9,1,830.42,'refund','system','','done','Unused buffer / unavailable items',NOW()+INTERVAL -1227602 SECOND),
(28,2,1,-830.42,'refund','system','','done','Refund to requester #9',NOW()+INTERVAL -1227602 SECOND),
(29,10,2,-1530.26,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -864001 SECOND),
(30,11,2,-702.34,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -864001 SECOND),
(31,12,2,-844.20,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -864001 SECOND),
(32,5,2,3076.80,'release','system','','done','Pooled funds released for the store purchase',NOW()+INTERVAL -864001 SECOND),
(33,5,2,-3076.80,'refund','system','','done','Return of released pool (trip cancelled)',NOW()+INTERVAL -864001 SECOND),
(34,10,2,1530.26,'refund','system','','done','Full refund: traveler cancelled',NOW()+INTERVAL -864001 SECOND),
(35,11,2,702.34,'refund','system','','done','Full refund: traveler cancelled',NOW()+INTERVAL -864001 SECOND),
(36,12,2,844.20,'refund','system','','done','Full refund: traveler cancelled',NOW()+INTERVAL -864001 SECOND),
(37,5,2,-100.00,'penalty','system','','done','Late dropout penalty',NOW()+INTERVAL -864001 SECOND),
(38,10,2,33.33,'apology','system','','done','Apology credit from traveler penalty',NOW()+INTERVAL -864001 SECOND),
(39,11,2,33.33,'apology','system','','done','Apology credit from traveler penalty',NOW()+INTERVAL -864001 SECOND),
(40,12,2,33.33,'apology','system','','done','Apology credit from traveler penalty',NOW()+INTERVAL -864001 SECOND),
(41,3,3,-724.52,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -28801 SECOND),
(42,10,3,-1831.69,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -28801 SECOND),
(43,11,3,-1399.05,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -28801 SECOND),
(44,12,3,-2034.04,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -28801 SECOND),
(45,5,3,5989.30,'release','system','','done','Pooled funds released for the store purchase',NOW()+INTERVAL -28801 SECOND),
(46,3,3,51.92,'refund','system','','done','Unused buffer / unavailable items',NOW()+INTERVAL -28801 SECOND),
(47,5,3,-51.92,'refund','system','','done','Refund to requester #3',NOW()+INTERVAL -28801 SECOND),
(48,10,3,420.14,'refund','system','','done','Unused buffer / unavailable items',NOW()+INTERVAL -28801 SECOND),
(49,5,3,-420.14,'refund','system','','done','Refund to requester #10',NOW()+INTERVAL -28801 SECOND),
(50,11,3,117.59,'refund','system','','done','Unused buffer / unavailable items',NOW()+INTERVAL -28801 SECOND),
(51,5,3,-117.59,'refund','system','','done','Refund to requester #11',NOW()+INTERVAL -28801 SECOND),
(52,12,3,124.65,'refund','system','','done','Unused buffer / unavailable items',NOW()+INTERVAL -28801 SECOND),
(53,5,3,-124.65,'refund','system','','done','Refund to requester #12',NOW()+INTERVAL -28801 SECOND),
(54,3,4,-793.48,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -7201 SECOND),
(55,4,4,-1524.02,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -7201 SECOND),
(56,7,4,-771.49,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -7201 SECOND),
(57,13,4,-1321.41,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -7201 SECOND),
(58,5,4,4410.40,'release','system','','done','Pooled funds released for the store purchase',NOW()+INTERVAL -7201 SECOND),
(59,8,5,-1572.80,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -18000 SECOND),
(60,9,5,-332.78,'trip_hold','system','','done','Upfront funding (held)',NOW()+INTERVAL -18000 SECOND),
(61,13,NULL,1500.00,'topup','gcash','9012345678901','pending','Top-up request',NOW()+INTERVAL 0 SECOND),
(62,2,NULL,-300.00,'cashout','gcash','','pending','Cash-out to 0917 123 4567',NOW()+INTERVAL 0 SECOND);
INSERT INTO disputes (id,trip_id,user_id,message,status,resolution,created_at) VALUES
(1,1,9,'Two eggs in my tray were cracked.','resolved','Traveler confirmed and gave ₱20 cash back to Teresa. Closed.',NOW()+INTERVAL -1227602 SECOND),
(2,3,11,'My milk was swapped to Kirkland. I am fine with it, but can the traveler confirm the expiry date?','open',NULL,NOW()+INTERVAL -28801 SECOND);
INSERT INTO announcements (id,title,body,created_at) VALUES
(1,'Welcome to the NeighborhoodMart pilot!','Soluna Executive Village residents can now pool bulk orders. Questions? Visit the HOA office, Mon-Sat 9 AM-5 PM.',NOW()+INTERVAL -1123200 SECOND),
(2,'Saturday S&R run is open','Juan is going to S&R this Saturday. Add your items before the window closes.',NOW()+INTERVAL 0 SECOND);
INSERT INTO notifications (id,user_id,msg,link,seen,created_at) VALUES
(1,1,'New resident waiting for review: Bea Torres','?p=admin_users',1,NOW()+INTERVAL -1382402 SECOND),
(2,1,'New resident waiting for review: Kevin Lim','?p=admin_users',1,NOW()+INTERVAL -1382402 SECOND),
(3,1,'New resident waiting for review: Mike Santos','?p=admin_users',1,NOW()+INTERVAL -1382402 SECOND),
(4,17,'Your residency could not be verified: No HOA record and no proof of residence. Please visit the HOA office.','?p=account',1,NOW()+INTERVAL -1382402 SECOND),
(5,1,'Traveler profile to review: Ana Liza Mendoza','?p=admin_travelers',1,NOW()+INTERVAL -1382402 SECOND),
(6,5,'You are now an approved Traveler. You can post trips and check prices.','?p=traveler_apply',1,NOW()+INTERVAL -1382402 SECOND),
(7,1,'Traveler profile to review: Ramon Antonio Garcia','?p=admin_travelers',1,NOW()+INTERVAL -1382402 SECOND),
(8,1,'Top-up to confirm: Maria Clara Santos ₱8,000.00','?p=admin_wallet',1,NOW()+INTERVAL -1382402 SECOND),
(9,1,'Top-up to confirm: Jose Miguel Reyes ₱5,000.00','?p=admin_wallet',1,NOW()+INTERVAL -1382402 SECOND),
(10,1,'Top-up to confirm: Ana Liza Mendoza ₱4,000.00','?p=admin_wallet',1,NOW()+INTERVAL -1382402 SECOND),
(11,1,'Top-up to confirm: Ramon Antonio Garcia ₱5,000.00','?p=admin_wallet',1,NOW()+INTERVAL -1382402 SECOND),
(12,1,'Top-up to confirm: Liza Marie Bautista ₱4,000.00','?p=admin_wallet',1,NOW()+INTERVAL -1382402 SECOND),
(13,1,'Top-up to confirm: Carlo Benedict Villanueva ₱3,000.00','?p=admin_wallet',1,NOW()+INTERVAL -1382402 SECOND),
(14,1,'Top-up to confirm: Teresa Gomez Aquino ₱3,000.00','?p=admin_wallet',1,NOW()+INTERVAL -1382402 SECOND),
(15,1,'Top-up to confirm: Mark Anthony Ramos ₱4,000.00','?p=admin_wallet',1,NOW()+INTERVAL -1382402 SECOND),
(16,1,'Top-up to confirm: Grace Ann Navarro ₱4,000.00','?p=admin_wallet',1,NOW()+INTERVAL -1382402 SECOND),
(17,1,'Top-up to confirm: Paolo Miguel Fernandez ₱5,000.00','?p=admin_wallet',1,NOW()+INTERVAL -1382402 SECOND),
(18,1,'Top-up to confirm: Rosa Linda Castillo ₱3,000.00','?p=admin_wallet',1,NOW()+INTERVAL -1382402 SECOND),
(19,1,'Top-up to confirm: Cristina Mae Lopez ₱4,000.00','?p=admin_wallet',1,NOW()+INTERVAL -1382402 SECOND),
(20,3,'Topup of ₱8,000.00 confirmed.','?p=wallet',1,NOW()+INTERVAL -1382402 SECOND),
(21,4,'Topup of ₱5,000.00 confirmed.','?p=wallet',1,NOW()+INTERVAL -1382402 SECOND),
(22,5,'Topup of ₱4,000.00 confirmed.','?p=wallet',1,NOW()+INTERVAL -1382402 SECOND),
(23,6,'Topup of ₱5,000.00 confirmed.','?p=wallet',1,NOW()+INTERVAL -1382402 SECOND),
(24,7,'Topup of ₱4,000.00 confirmed.','?p=wallet',1,NOW()+INTERVAL -1382402 SECOND),
(25,8,'Topup of ₱3,000.00 confirmed.','?p=wallet',1,NOW()+INTERVAL -1382402 SECOND),
(26,9,'Topup of ₱3,000.00 confirmed.','?p=wallet',1,NOW()+INTERVAL -1382402 SECOND),
(27,10,'Topup of ₱4,000.00 confirmed.','?p=wallet',1,NOW()+INTERVAL -1382402 SECOND),
(28,11,'Topup of ₱4,000.00 confirmed.','?p=wallet',1,NOW()+INTERVAL -1382402 SECOND),
(29,12,'Topup of ₱5,000.00 confirmed.','?p=wallet',1,NOW()+INTERVAL -1382402 SECOND),
(30,13,'Topup of ₱3,000.00 confirmed.','?p=wallet',1,NOW()+INTERVAL -1382402 SECOND),
(31,14,'Topup of ₱4,000.00 confirmed.','?p=wallet',1,NOW()+INTERVAL -1382402 SECOND),
(32,2,'New request on \"End-of-October S&R run\": 1 × Well-milled Rice','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(33,2,'New request on \"End-of-October S&R run\": 5 × Green Tea 500ml','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(34,2,'New request on \"End-of-October S&R run\": 2 × Green Tea 500ml','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(35,2,'New request on \"End-of-October S&R run\": 2 × Fresh Eggs Large','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(36,2,'New request on \"End-of-October S&R run\": 6 × Whole Fresh Milk 1L','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(37,2,'New request on \"End-of-October S&R run\": 10 × Canned Tuna 155g','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(38,2,'New request on \"End-of-October S&R run\": 1 × Fresh Eggs Large','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(39,2,'New request on \"End-of-October S&R run\": 1 × Cooking Oil','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(40,3,'Requests closed for \"End-of-October S&R run\". Please fund your share of ₱1,766.17.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(41,4,'Requests closed for \"End-of-October S&R run\". Please fund your share of ₱119.91.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(42,7,'Requests closed for \"End-of-October S&R run\". Please fund your share of ₱1,360.70.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(43,8,'Requests closed for \"End-of-October S&R run\". Please fund your share of ₱457.80.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(44,9,'Requests closed for \"End-of-October S&R run\". Please fund your share of ₱1,166.12.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(45,2,'Request window closed for \"End-of-October S&R run\". Waiting for requesters to fund.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(46,2,'\"End-of-October S&R run\" is 100% funded. Review the manifest and tap Accept and Lock Trip.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(47,3,'\"End-of-October S&R run\" is locked. Your traveler is heading to the store.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(48,4,'\"End-of-October S&R run\" is locked. Your traveler is heading to the store.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(49,7,'\"End-of-October S&R run\" is locked. Your traveler is heading to the store.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(50,8,'\"End-of-October S&R run\" is locked. Your traveler is heading to the store.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(51,9,'\"End-of-October S&R run\" is locked. Your traveler is heading to the store.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(52,7,'Substituted (auto-approved): Substitute: Whole Fresh Milk 1L → Harvey Fresh at ₱90.00 per unit','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(53,9,'Cooking Oil was unavailable. You will be refunded for it.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(54,3,'Receipt photo added to \"End-of-October S&R run\".','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(55,4,'Receipt photo added to \"End-of-October S&R run\".','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(56,7,'Receipt photo added to \"End-of-October S&R run\".','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(57,8,'Receipt photo added to \"End-of-October S&R run\".','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(58,9,'Receipt photo added to \"End-of-October S&R run\".','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(59,3,'\"End-of-October S&R run\" is back in the village. Your final share is ₱1,657.89, ₱108.28 refunded. Collect within 24 hours.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(60,4,'\"End-of-October S&R run\" is back in the village. Your final share is ₱114.69, ₱5.22 refunded. Collect within 24 hours.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(61,7,'\"End-of-October S&R run\" is back in the village. Your final share is ₱1,253.59, ₱107.11 refunded. Collect within 24 hours.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(62,8,'\"End-of-October S&R run\" is back in the village. Your final share is ₱438.13, ₱19.67 refunded. Collect within 24 hours.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(63,9,'\"End-of-October S&R run\" is back in the village. Your final share is ₱335.70, ₱830.42 refunded. Collect within 24 hours.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(64,9,'Your dispute was resolved: Traveler confirmed and gave ₱20 cash back to Teresa. Closed.','?p=trip&id=1',1,NOW()+INTERVAL -1227602 SECOND),
(65,5,'New request on \"Mid-week Landers run\": 1 × Well-milled Rice','?p=trip&id=2',1,NOW()+INTERVAL -864001 SECOND),
(66,5,'New request on \"Mid-week Landers run\": 2 × Fresh Eggs Large','?p=trip&id=2',1,NOW()+INTERVAL -864001 SECOND),
(67,5,'New request on \"Mid-week Landers run\": 1 × Cooking Oil','?p=trip&id=2',1,NOW()+INTERVAL -864001 SECOND),
(68,10,'Requests closed for \"Mid-week Landers run\". Please fund your share of ₱1,530.26.','?p=trip&id=2',1,NOW()+INTERVAL -864001 SECOND),
(69,11,'Requests closed for \"Mid-week Landers run\". Please fund your share of ₱702.34.','?p=trip&id=2',1,NOW()+INTERVAL -864001 SECOND),
(70,12,'Requests closed for \"Mid-week Landers run\". Please fund your share of ₱844.20.','?p=trip&id=2',1,NOW()+INTERVAL -864001 SECOND),
(71,5,'Request window closed for \"Mid-week Landers run\". Waiting for requesters to fund.','?p=trip&id=2',1,NOW()+INTERVAL -864001 SECOND),
(72,5,'\"Mid-week Landers run\" is 100% funded. Review the manifest and tap Accept and Lock Trip.','?p=trip&id=2',1,NOW()+INTERVAL -864001 SECOND),
(73,10,'\"Mid-week Landers run\" is locked. Your traveler is heading to the store.','?p=trip&id=2',1,NOW()+INTERVAL -864001 SECOND),
(74,11,'\"Mid-week Landers run\" is locked. Your traveler is heading to the store.','?p=trip&id=2',1,NOW()+INTERVAL -864001 SECOND),
(75,12,'\"Mid-week Landers run\" is locked. Your traveler is heading to the store.','?p=trip&id=2',1,NOW()+INTERVAL -864001 SECOND),
(76,10,'Trip \"Mid-week Landers run\" was cancelled by the traveler. Any held funds were refunded to your wallet.','?p=wallet',1,NOW()+INTERVAL -864001 SECOND),
(77,11,'Trip \"Mid-week Landers run\" was cancelled by the traveler. Any held funds were refunded to your wallet.','?p=wallet',1,NOW()+INTERVAL -864001 SECOND),
(78,12,'Trip \"Mid-week Landers run\" was cancelled by the traveler. Any held funds were refunded to your wallet.','?p=wallet',1,NOW()+INTERVAL -864001 SECOND),
(79,5,'New request on \"Friday Landers run\": 6 × Whole Fresh Milk 1L','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(80,5,'New request on \"Friday Landers run\": 1 × Well-milled Rice','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(81,5,'New request on \"Friday Landers run\": 1 × Fresh Eggs Large','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(82,5,'New request on \"Friday Landers run\": 6 × Whole Fresh Milk 1L','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(83,5,'New request on \"Friday Landers run\": 4 × Laundry Detergent Powder','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(84,5,'New request on \"Friday Landers run\": 2 × Cooking Oil','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(85,5,'New request on \"Friday Landers run\": 10 × Canned Tuna 155g','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(86,3,'Requests closed for \"Friday Landers run\". Please fund your share of ₱724.52.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(87,10,'Requests closed for \"Friday Landers run\". Please fund your share of ₱1,831.69.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(88,11,'Requests closed for \"Friday Landers run\". Please fund your share of ₱1,399.05.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(89,12,'Requests closed for \"Friday Landers run\". Please fund your share of ₱2,034.04.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(90,5,'Request window closed for \"Friday Landers run\". Waiting for requesters to fund.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(91,5,'\"Friday Landers run\" is 100% funded. Review the manifest and tap Accept and Lock Trip.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(92,3,'\"Friday Landers run\" is locked. Your traveler is heading to the store.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(93,10,'\"Friday Landers run\" is locked. Your traveler is heading to the store.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(94,11,'\"Friday Landers run\" is locked. Your traveler is heading to the store.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(95,12,'\"Friday Landers run\" is locked. Your traveler is heading to the store.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(96,10,'Fresh Eggs Large was unavailable. You will be refunded for it.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(97,11,'Substituted (auto-approved): Substitute: Whole Fresh Milk 1L → Kirkland Whole Milk at ₱88.00 per unit','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(98,3,'Receipt photo added to \"Friday Landers run\".','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(99,10,'Receipt photo added to \"Friday Landers run\".','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(100,11,'Receipt photo added to \"Friday Landers run\".','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(101,12,'Receipt photo added to \"Friday Landers run\".','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(102,3,'\"Friday Landers run\" is back in the village. Your final share is ₱672.60, ₱51.92 refunded. Collect within 24 hours.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(103,10,'\"Friday Landers run\" is back in the village. Your final share is ₱1,411.55, ₱420.14 refunded. Collect within 24 hours.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(104,11,'\"Friday Landers run\" is back in the village. Your final share is ₱1,281.46, ₱117.59 refunded. Collect within 24 hours.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(105,12,'\"Friday Landers run\" is back in the village. Your final share is ₱1,909.39, ₱124.65 refunded. Collect within 24 hours.','?p=trip&id=3',0,NOW()+INTERVAL -28801 SECOND),
(106,5,'New request on \"Saturday Landers stock-up\": 10 × Canned Tuna 155g','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(107,5,'New request on \"Saturday Landers stock-up\": 1 × Fresh Eggs Large','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(108,5,'New request on \"Saturday Landers stock-up\": 1 × Well-milled Rice','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(109,5,'New request on \"Saturday Landers stock-up\": 4 × Laundry Detergent Powder','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(110,5,'New request on \"Saturday Landers stock-up\": 6 × Whole Fresh Milk 1L','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(111,5,'New request on \"Saturday Landers stock-up\": 2 × Fresh Eggs Large','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(112,3,'Requests closed for \"Saturday Landers stock-up\". Please fund your share of ₱793.48.','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(113,4,'Requests closed for \"Saturday Landers stock-up\". Please fund your share of ₱1,524.02.','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(114,7,'Requests closed for \"Saturday Landers stock-up\". Please fund your share of ₱771.49.','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(115,13,'Requests closed for \"Saturday Landers stock-up\". Please fund your share of ₱1,321.41.','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(116,5,'Request window closed for \"Saturday Landers stock-up\". Waiting for requesters to fund.','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(117,5,'\"Saturday Landers stock-up\" is 100% funded. Review the manifest and tap Accept and Lock Trip.','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(118,3,'\"Saturday Landers stock-up\" is locked. Your traveler is heading to the store.','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(119,4,'\"Saturday Landers stock-up\" is locked. Your traveler is heading to the store.','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(120,7,'\"Saturday Landers stock-up\" is locked. Your traveler is heading to the store.','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(121,13,'\"Saturday Landers stock-up\" is locked. Your traveler is heading to the store.','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(122,3,'Approval needed: Substitute: Canned Tuna 155g → 555 Tuna (different package size: pack of 8) at ₱38.00 per unit','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(123,3,'Receipt photo added to \"Saturday Landers stock-up\".','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(124,4,'Receipt photo added to \"Saturday Landers stock-up\".','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(125,7,'Receipt photo added to \"Saturday Landers stock-up\".','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(126,13,'Receipt photo added to \"Saturday Landers stock-up\".','?p=trip&id=4',0,NOW()+INTERVAL -7201 SECOND),
(127,2,'New request on \"Weekend S&R bulk order\": 1 × Well-milled Rice','?p=trip&id=5',0,NOW()+INTERVAL -18001 SECOND),
(128,2,'New request on \"Weekend S&R bulk order\": 8 × Green Tea 500ml','?p=trip&id=5',0,NOW()+INTERVAL -18001 SECOND),
(129,2,'New request on \"Weekend S&R bulk order\": 6 × Whole Fresh Milk 1L','?p=trip&id=5',0,NOW()+INTERVAL -18000 SECOND),
(130,2,'New request on \"Weekend S&R bulk order\": 4 × Green Tea 500ml','?p=trip&id=5',0,NOW()+INTERVAL -18000 SECOND),
(131,2,'New request on \"Weekend S&R bulk order\": 2 × Fresh Eggs Large','?p=trip&id=5',0,NOW()+INTERVAL -18000 SECOND),
(132,3,'Requests closed for \"Weekend S&R bulk order\". Please fund your share of ₱1,033.16.','?p=trip&id=5',0,NOW()+INTERVAL -18000 SECOND),
(133,8,'Requests closed for \"Weekend S&R bulk order\". Please fund your share of ₱1,572.80.','?p=trip&id=5',0,NOW()+INTERVAL -18000 SECOND),
(134,9,'Requests closed for \"Weekend S&R bulk order\". Please fund your share of ₱332.78.','?p=trip&id=5',0,NOW()+INTERVAL -18000 SECOND),
(135,10,'Requests closed for \"Weekend S&R bulk order\". Please fund your share of ₱724.47.','?p=trip&id=5',0,NOW()+INTERVAL -18000 SECOND),
(136,2,'Request window closed for \"Weekend S&R bulk order\". Waiting for requesters to fund.','?p=trip&id=5',0,NOW()+INTERVAL -18000 SECOND),
(137,2,'New request on \"Saturday S&R run\": 3 × Green Tea 500ml','?p=trip&id=6',0,NOW()+INTERVAL 0 SECOND),
(138,2,'New request on \"Saturday S&R run\": 1 × Fresh Eggs Large','?p=trip&id=6',0,NOW()+INTERVAL 0 SECOND),
(139,2,'New request on \"Saturday S&R run\": 1 × Well-milled Rice','?p=trip&id=6',0,NOW()+INTERVAL 0 SECOND),
(140,2,'New request on \"Saturday S&R run\": 2 × Fresh Eggs Large','?p=trip&id=6',0,NOW()+INTERVAL 0 SECOND),
(141,2,'New request on \"Saturday S&R run\": 6 × Whole Fresh Milk 1L','?p=trip&id=6',0,NOW()+INTERVAL 0 SECOND),
(142,5,'New request on \"Sunday public-market run (fresh goods)\": 2 × Fresh Eggs Large','?p=trip&id=7',0,NOW()+INTERVAL 0 SECOND),
(143,5,'New request on \"Sunday public-market run (fresh goods)\": 1 × Well-milled Rice','?p=trip&id=7',0,NOW()+INTERVAL 0 SECOND),
(144,5,'New request on \"Sunday public-market run (fresh goods)\": 1 × Fresh Eggs Large','?p=trip&id=7',0,NOW()+INTERVAL 0 SECOND),
(145,1,'Top-up to confirm: Rosa Linda Castillo ₱1,500.00','?p=admin_wallet',0,NOW()+INTERVAL 0 SECOND);
INSERT INTO audit (id,user_id,action,created_at) VALUES
(1,1,'rejected user 17',NOW()+INTERVAL -1382402 SECOND),
(2,1,'added store option PureGold Price Club (Molino)',NOW()+INTERVAL -1382402 SECOND),
(3,1,'updated store 2 (allowed)',NOW()+INTERVAL -1382402 SECOND),
(4,1,'updated store 4 (unverified)',NOW()+INTERVAL -1382402 SECOND),
(5,1,'traveler approved user 5',NOW()+INTERVAL -1382402 SECOND),
(6,2,'price check item 1 store 1 = 265',NOW()+INTERVAL -1382402 SECOND),
(7,2,'price check item 2 store 1 = 92',NOW()+INTERVAL -1382402 SECOND),
(8,2,'price check item 4 store 1 = 1250',NOW()+INTERVAL -1382402 SECOND),
(9,5,'price check item 1 store 2 = 259',NOW()+INTERVAL -1382402 SECOND),
(10,5,'price check item 2 store 2 = 89.5',NOW()+INTERVAL -1382402 SECOND),
(11,5,'price check item 6 store 2 = 640',NOW()+INTERVAL -1382402 SECOND),
(12,5,'price check item 4 store 2 = 1230',NOW()+INTERVAL -1382402 SECOND),
(13,5,'price check item 5 store 2 = 34',NOW()+INTERVAL -1382402 SECOND),
(14,5,'price check item 7 store 2 = 145',NOW()+INTERVAL -1382402 SECOND),
(15,5,'price check item 1 store 3 = 245',NOW()+INTERVAL -1382402 SECOND),
(16,5,'price check item 4 store 3 = 1180',NOW()+INTERVAL -1382402 SECOND),
(17,1,'wallet tx 1 done',NOW()+INTERVAL -1382402 SECOND),
(18,1,'wallet tx 2 done',NOW()+INTERVAL -1382402 SECOND),
(19,1,'wallet tx 3 done',NOW()+INTERVAL -1382402 SECOND),
(20,1,'wallet tx 4 done',NOW()+INTERVAL -1382402 SECOND),
(21,1,'wallet tx 5 done',NOW()+INTERVAL -1382402 SECOND),
(22,1,'wallet tx 6 done',NOW()+INTERVAL -1382402 SECOND),
(23,1,'wallet tx 7 done',NOW()+INTERVAL -1382402 SECOND),
(24,1,'wallet tx 8 done',NOW()+INTERVAL -1382402 SECOND),
(25,1,'wallet tx 9 done',NOW()+INTERVAL -1382402 SECOND),
(26,1,'wallet tx 10 done',NOW()+INTERVAL -1382402 SECOND),
(27,1,'wallet tx 11 done',NOW()+INTERVAL -1382402 SECOND),
(28,1,'wallet tx 12 done',NOW()+INTERVAL -1382402 SECOND),
(29,2,'posted trip 1',NOW()+INTERVAL -1227602 SECOND),
(30,2,'trip 1 step close',NOW()+INTERVAL -1227602 SECOND),
(31,2,'trip 1 step lock',NOW()+INTERVAL -1227602 SECOND),
(32,2,'trip 1 step shop',NOW()+INTERVAL -1227602 SECOND),
(33,2,'uploaded 1 receipt(s) for trip 1',NOW()+INTERVAL -1227602 SECOND),
(34,2,'trip 1 step finish',NOW()+INTERVAL -1227602 SECOND),
(35,1,'resolved dispute 1',NOW()+INTERVAL -1227602 SECOND),
(36,5,'posted trip 2',NOW()+INTERVAL -864001 SECOND),
(37,5,'trip 2 step close',NOW()+INTERVAL -864001 SECOND),
(38,5,'trip 2 step lock',NOW()+INTERVAL -864001 SECOND),
(39,5,'cancelled trip 2 after lock (penalty)',NOW()+INTERVAL -864001 SECOND),
(40,5,'posted trip 3',NOW()+INTERVAL -28801 SECOND),
(41,5,'trip 3 step close',NOW()+INTERVAL -28801 SECOND),
(42,5,'trip 3 step lock',NOW()+INTERVAL -28801 SECOND),
(43,5,'trip 3 step shop',NOW()+INTERVAL -28801 SECOND),
(44,5,'uploaded 1 receipt(s) for trip 3',NOW()+INTERVAL -28801 SECOND),
(45,5,'trip 3 step finish',NOW()+INTERVAL -28801 SECOND),
(46,5,'posted trip 4',NOW()+INTERVAL -7201 SECOND),
(47,5,'trip 4 step close',NOW()+INTERVAL -7201 SECOND),
(48,5,'trip 4 step lock',NOW()+INTERVAL -7201 SECOND),
(49,5,'trip 4 step shop',NOW()+INTERVAL -7201 SECOND),
(50,5,'uploaded 1 receipt(s) for trip 4',NOW()+INTERVAL -7201 SECOND),
(51,2,'posted trip 5',NOW()+INTERVAL -18001 SECOND),
(52,2,'trip 5 step close',NOW()+INTERVAL -18000 SECOND),
(53,2,'posted trip 6',NOW()+INTERVAL 0 SECOND),
(54,5,'posted trip 7',NOW()+INTERVAL 0 SECOND),
(55,1,'posted announcement',NOW()+INTERVAL 0 SECOND),
(56,1,'posted announcement',NOW()+INTERVAL 0 SECOND);

-- demo accounts start with Guided mode OFF (turn it on in the Aa panel to show it). New sign-ups get the Guided setup prompt.
UPDATE users SET guided=0;
