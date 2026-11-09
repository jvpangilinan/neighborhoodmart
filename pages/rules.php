<?php // SCREEN: Rules & Legal (static text; edit wording here). Numbers come from config/config.php. ?>
<div class="card legal mid"><h2>Rules &amp; Legal</h2>
<h3>1. Accounts and HOA verification</h3>
<p>Everyone registers with their full name and Block/Lot. If it matches the HOA master list exactly, the account is approved instantly. A near match (typos, initials, missing middle name) or no match goes to an HOA admin, who checks the uploaded utility bill or village ID. The system never rejects anyone automatically.</p>
<h3>2. Travelers</h3>
<p>Travelers register their usual stores, areas, membership and card. The HOA verifies each store's rules on buying for several families before trips there are allowed.</p>
<h3>3. Matching rules</h3><p>R1 verified resident · R2 item has a checked price at the trip's store · R3 before the window closes · R4 within the vehicle's capacity (Motorcycle <?=VEHICLES['motorcycle'][1]?>, Sedan <?=VEHICLES['sedan'][1]?>, SUV <?=VEHICLES['suv'][1]?> load units) · R5 within the per-member limit. Every rejection shows its reason.</p>
<h3>4. What you pay</h3>
<p>Upfront, after the window closes: <b>Item price share + Temporary price buffer (<?=BUFFER_RATE*100?>%, refundable) + share of trip expenses + Traveler Effort Fee</b>. The trip goes to the store only when 100% of families have funded, so a traveler never pays for anyone.</p>
<p>Final share: <b>Shareᵢ = Cᵢ + (Cᵢ / ΣC) × F + Effort Fee(Cᵢ)</b>, where Cᵢ is the receipt cost of the items you actually received and F the trip expenses. Any unused buffer and unavailable items go back to your wallet.</p>
<p>Traveler Effort Fee: <?=h(tier_label())?>.</p>
<h3>5. Pack sizes</h3><p>Items sold in packs (e.g. a box of 8) are pooled per pack. A leftover is completed by those who ticked "round up", otherwise the latest units are trimmed.</p>
<h3>6. Points of no return and penalties</h3>
<ul><li><b>Requesters</b> can cancel freely while the trip is open. Once the window closes, cancel is disabled. Unpaid families can be removed by the traveler.</li>
<li>Items must be collected within <?=PICKUP_HOURS?> hours of delivery. If not, the traveler keeps full payment for the items and fee; the HOA may release the items. Buffer refunds are kept.</li>
<li><b>Travelers</b> tap "Accept and Lock Trip" once 100% funded. Cancelling after that and before shopping costs a <?=peso(DROPOUT_PENALTY)?> penalty, shared equally with the affected families as an apology credit, plus a <?=DROPOUT_BAN_DAYS?>-day hosting ban. All families get a full refund.</li></ul>
<h3>7. In-store exception rules</h3>
<p>The traveler uploads a photo of every receipt before finishing; families in the trip and the HOA can view them on the trip page.</p>
<ul><li><b>Out of stock:</b> marked unavailable; it is not charged.</li>
<li><b>Price higher but within the <?=BUFFER_RATE*100?>% buffer:</b> bought automatically using the buffer.</li>
<li><b>Price above the buffer:</b> the requester gets an alert to approve or decline. If approved, the extra is collected from the wallet.</li>
<li><b>Different brand, same tier and same package size:</b> substituted automatically if the requester allowed substitutes, and still within the buffer.</li>
<li><b>Different package size</b> (e.g. 8-pack becomes 6-pack), or the requester did not allow substitutes: the requester must approve.</li>
<li>Alerts not answered by the time shopping ends count as declined (not bought, refunded).</li></ul>
<h3>8. Wallet</h3><p>The wallet is a record of credits between neighbors. Real money moves by GCash or cash through the HOA, which confirms each top-up and cash-out. Pooled funds are released to the traveler at the lock so they never shop with their own money.</p>
<h3>9. Data privacy (RA 10173)</h3><p>We collect only what verification, trips and payments need. Proof-of-residence files are visible only to HOA admins. You can request access, correction or deletion through the HOA.</p>
<h3 id="install">10. Install on your phone</h3><p><b>Android (Chrome):</b> open the site, tap ⋮ then <i>Install app</i> / <i>Add to Home screen</i>. <b>iPhone (Safari):</b> tap Share then <i>Add to Home Screen</i>. It opens full-screen like a normal app. (Needs the site on HTTPS, which Hostinger provides free.)</p>
<h3>11. Store terms</h3><p>Membership terms are set by each store and can change. Regular reselling for profit may need business registration (BIR / DTI); the HOA should consult an accountant or lawyer. This page is not legal advice.</p></div>
