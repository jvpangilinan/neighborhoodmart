/* CHANELLY'S DEMO TOUR — a step-by-step, video-game-style walkthrough of the real app.
   How it works: each step names the page it runs on, the element to highlight, what Chanelly says, and
   whether you must click the highlighted thing ("click") or just press Continue ("next").
   Forms are pre-filled with a typing effect. Switching accounts uses the demo_as action (only when DEMO_MODE is on).
   The tour remembers its place in localStorage, so it survives page reloads and account switches.
   To change the words or the order, edit the STEPS list below. */
(function () {
  'use strict';
  if (document.querySelector('meta[name=demo]')?.content !== '1') return;
  const KEY = 'nmTour';
  const CSRF = document.querySelector('meta[name=csrf]')?.content || '';
  const qs = new URLSearchParams(location.search);
  const S = load();
  const today = (d = 0) => { const x = new Date(); x.setDate(x.getDate() + d); return x.toISOString().slice(0, 10); };
  const tripUrl = () => '?p=trip&id=' + S.trip;
  const MARIA = 'requester@neighborhoodmart.local';
  const row = (sel) => `[data-user="${S.email}"] ${sel}`;
  const onTrip = (form) => `form:has(input[name=id][value="${S.trip}"]) ${form}`;

  // ---------------------------------------------------------------- the script
  const STEPS = [
    { chapter: 0, page: '*', title: "Hi! I'm Chanelly 👋",
      text: "I'm the NeighborhoodMart mascot. I'll walk you through the whole app using real screens: a <b>Traveler</b> signs up, the <b>HOA Admin</b> checks him, then a <b>Requester</b> joins his trip. Just read and click. Ready?" },
    { chapter: 1, role: 'traveler', page: 'home', title: 'Chapter 1 · Ed wants to be a Traveler',
      text: "Meet <b>Ed Santiago</b>, Blk 10 Lot 1. He drives to S&amp;R every weekend and wants to buy for his neighbors too. First he needs an account." },
    { page: 'home', target: '[data-tour=register]', act: 'click', title: 'Create an account',
      text: 'Everyone starts here. Tap <b>Register</b>.' },
    { page: 'register', target: '[data-tour=register-submit]', act: 'click', title: 'Registration (pre-filled for you)',
      fill: () => [['[name=name]', 'Ed Santiago'], ['[name=block]', '10'], ['[name=lot]', '1'], ['[name=street]', 'Mabini St.'],
        ['[name=email]', S.email], ['[name=phone]', '0917 555 0101'], ['[name=pass]', 'demo123'], ['[name=consent]', true]],
      text: "The HOA's records say <b>Eduardo Jose Santiago</b>, but Ed typed <b>Ed Santiago</b>. Not an exact match, so the app won't auto-approve him. It sends him to a human instead of rejecting him. Tap <b>Register</b>." },
    { page: 'login', target: '[data-tour=login-submit]', act: 'click', title: 'Log in',
      fill: () => [['[name=email]', S.email], ['[name=pass]', 'demo123']],
      text: 'Account created and waiting for HOA review. Ed can still log in and look around.' },
    { page: 'choose_role', target: '[data-tour=role-traveler]', act: 'click', title: 'Pick a role',
      text: 'After logging in, everyone picks <b>Requester</b> or <b>Traveler</b>. Each one opens its own app with its own color and menu. Ed picks <b>Traveler</b>.' },
    { page: 'traveler_apply', target: '[data-tour=traveler-submit]', act: 'click', title: 'Traveler registration',
      fill: () => [['[name="stores[]"][value="1"]', true], ['[name="stores[]"][value="2"]', true], ['[name=has_membership]', true],
        ['[name=membership_store]', 'S&R'], ['[name=has_card]', true], ['[name=card_expiry]', today(300)], ['[name="areas[]"][value="1"]', true], ['[name=vehicle]', 'sedan']],
      text: "Ed lists the stores he visits, his membership card and where he usually goes. His <b>vehicle</b> sets how much the trip can carry (rule R4). Tap <b>Submit</b>." },
    { page: 'traveler_apply', target: '.msg', act: 'next', title: 'Now we wait for the HOA',
      text: 'Ed is <b>pending</b> twice: his residency and his traveler application. Time to put on the HOA hat! 🎩' },
    { chapter: 2, role: 'admin', page: '*', title: 'Chapter 2 · The HOA Admin checks',
      text: "I'm logging you in as the <b>HOA Admin</b>. Their app is a to-do list of everything that needs a human decision.",
      go: () => ({ who: 'admin', to: '?p=home' }) },
    { page: 'home', app: 'admin', target: '[data-tour=tile-admin_users]', act: 'click', title: 'HOA Admin dashboard',
      text: 'Each tile is something waiting for a decision. Ed is under <b>Residents to verify</b>. Tap it.' },
    { page: 'admin_users', target: () => `[data-user="${S.email}"]`, act: 'next', title: 'Pending manual review',
      text: 'Here\'s Ed. The app found the closest HOA record and scored it <b>66%</b>: same Block and Lot, but a shorter name. Normally Ed would upload a bill or village ID for the admin to check.' },
    { page: 'admin_users', target: () => row('[data-tour=approve]'), act: 'click', title: 'Human-in-the-loop approval',
      fill: () => [[row('[name=note]'), 'Same Blk 10 Lot 1 as HOA record Eduardo Jose Santiago']],
      text: 'The admin writes a note and makes the final call. Tap <b>Approve</b>.' },
    { page: 'admin_users', target: '[data-tour=nav-admin_travelers]', act: 'click', title: 'Residency approved ✅',
      text: 'Ed is now a verified resident. Next, his <b>traveler application</b>. Tap <b>Travelers</b>.' },
    { page: 'admin_travelers', target: () => `[data-user="${S.email}"]`, act: 'next', title: "Ed's traveler registration",
      text: 'Stores, membership, card expiry and vehicle. The HOA also checks with each store that one member may buy for several families.' },
    { page: 'admin_travelers', target: () => row('[data-tour=approve]'), act: 'click', title: 'Approve the traveler',
      text: 'Looks good. Tap <b>Approve traveler</b>.' },
    { chapter: 3, role: 'traveler', page: '*', title: 'Chapter 3 · Ed posts a trip',
      text: "Back to Ed. Now that he's approved, his blue <b>Traveler app</b> unlocks.",
      go: () => ({ who: 'tour', to: '?p=home' }) },
    { page: 'home', app: 'traveler', target: '[data-tour=post-trip]', act: 'click', title: "Ed's Traveler home",
      text: 'Every trip he hosts appears here with <b>one</b> next-step button, so there\'s nothing to hunt for. Tap <b>+ Post a new trip</b>.' },
    { page: 'new_trip', target: '[data-tour=publish]', act: 'click', title: 'Post a trip',
      fill: () => [['[name=title]', "Ed's Sunday S&R run"], ['[name=store_id]', '1'], ['[name=area_id]', '1'], ['[name=go_date]', today(2)],
        ['[name=closes]', today(1) + 'T20:00'], ['[name=vehicle]', 'sedan'], ['[name=expenses]', '180']],
      text: 'Store, date, when requests close, vehicle, and <b>trip expenses F</b> (gas, toll, parking), which are shared fairly by how much each family buys. Tap <b>Publish</b>.' },
    { page: 'trip', saveTrip: true, target: '[data-tour=steps]', act: 'next', title: 'The 7 stages of a trip',
      text: 'Taking orders → Paying → All paid → Locked → Shopping → Pickup → Done. The 👉 line always says what to do next.' },
    { chapter: 4, role: 'requester', page: '*', title: 'Chapter 4 · The neighbors join',
      text: "Word spreads! Jose, Liza and Grace already added items. Now you're <b>Maria</b>, a Requester. Her app is green.",
      go: () => ({ who: 'requester', to: tripUrl(), assist: 'join', trip: S.trip }) },
    { page: 'trip', app: 'requester', target: '[data-tour=add-to-pool]', act: 'click', title: 'Add an item to the pool',
      fill: () => [['[name=item]', '4'], ['[name=qty]', '1'], ['[name=sub]', true]],
      text: 'Maria needs a <b>25 kg sack of rice</b>. Items are generic (kind + size), so any brand of the same size counts. Tap <b>Add to pool</b>.' },
    { page: 'trip', app: 'requester', target: '[data-tour=breakdown]', act: 'next', title: 'Fair checkout breakdown',
      text: 'Item price + a <b>refundable 10% buffer</b> (in case prices change) + her share of gas/toll + the <b>Traveler Effort Fee</b>. She can cancel freely until requests close.' },
    { page: 'trip', app: 'requester', target: '[data-tour=tally]', act: 'next', title: 'Pooling in action',
      text: 'The <b>Pool tally</b> shows everyone\'s items together. Packs like a C2 box of 8 fill up as neighbors join.' },
    { chapter: 5, role: 'traveler', page: '*', title: 'Chapter 5 · Shopping day',
      text: "It's the day before the trip. Back to Ed!",
      go: () => ({ who: 'tour', to: tripUrl() }) },
    { page: 'trip', app: 'traveler', target: '[data-tour=step-close]', act: 'click', title: 'Close requests',
      text: 'Ed closes the request window. The app rounds packs up or trims them, then asks every family to <b>pay upfront</b>.' },
    { page: 'trip', app: 'traveler', target: '.hint', act: 'next', title: 'Nobody shops with their own money',
      text: 'The trip can\'t go until <b>100%</b> of families have paid, so a traveler never pays for anyone. Let\'s pay as Maria.',
      go: () => ({ who: 'requester', to: '?p=home' }) },
    { page: 'home', app: 'requester', target: () => onTrip('[data-tour=pay]'), act: 'click', title: "Maria's To-do",
      text: 'The Requester home only shows what needs her: here it\'s <b>Pay your share</b>. One tap, paid from her wallet credit.' },
    { page: 'trip', app: 'requester', target: '.hint', act: 'next', title: 'Paid and held ✅',
      text: 'Her money is held until the trip is done. Cancel is now disabled: this is the requester\'s <b>point of no return</b>. The others pay too…',
      go: () => ({ who: 'tour', to: tripUrl(), assist: 'fund', trip: S.trip }) },
    { page: 'trip', app: 'traveler', target: '[data-tour=step-lock]', act: 'click', title: '100% funded → Accept and Lock',
      text: "Everyone paid! Locking is <b>Ed's</b> point of no return. If he cancels after this, he pays a <b>₱100 penalty</b> (shared with the families) and gets a 1-week ban." },
    { page: 'trip', app: 'traveler', target: '[data-tour=step-shop]', act: 'click', title: 'At the store',
      text: 'The pooled money was released to Ed\'s wallet for the purchase. He\'s at S&amp;R. Tap <b>I\'m at the store</b>.' },
    { page: 'trip', app: 'traveler', target: () => `[data-who="${MARIA}"][data-item="4"] button[value=bought]`, act: 'click', title: 'Live shopping checklist',
      fill: () => [[`[data-who="${MARIA}"][data-item="4"] [name=price]`, '1199'], [`[data-who="${MARIA}"][data-item="4"] [name=brand]`, 'Jasmine (promo)']],
      text: 'Items are grouped by store section so Ed passes each aisle once. Rice is on promo: <b>₱1,199</b> instead of ₱1,250. Tap <b>Bought</b>. (If something is out of stock, or a substitute isn\'t the same size, the requester gets an Approve/Decline alert.)' },
    { page: 'trip', app: 'traveler', target: '[data-tour=steps]', act: 'next', title: "I'll do the rest!",
      text: "I'll tick the other families' items and attach a receipt photo for you. 🐾",
      go: () => ({ who: 'tour', to: tripUrl(), assist: 'shop_rest', trip: S.trip }) },
    { page: 'trip', app: 'traveler', target: '[data-tour=receipts]', act: 'next', title: 'Receipt photos',
      text: 'The traveler <b>must</b> upload the receipts before finishing. Families in the trip and the HOA can open them, but nobody else can.' },
    { page: 'trip', app: 'traveler', target: '[data-tour=step-finish]', act: 'click', title: 'Back in the village',
      text: 'Tap <b>Done</b>. The app now uses the <b>real receipt prices</b> to compute each family\'s final share.' },
    { page: 'trip', app: 'traveler', target: '[data-tour=ledger]', act: 'next', title: 'The cost-allocation ledger',
      text: '<b>Shareᵢ = Cᵢ + (Cᵢ / ΣC) × F + Effort Fee</b>. Because the rice was cheaper, Maria gets money <b>back</b>. Ed can see exactly what he earned.',
      go: () => ({ who: 'requester', to: '?p=home' }) },
    { page: 'home', app: 'requester', target: () => onTrip('[data-tour=collect]'), act: 'click', title: 'Pickup',
      text: 'Ed drops off the rice. Maria confirms she got it (she has 24 hours to pick up).' },
    { page: 'trip', app: 'requester', target: '[data-tour=savings]', act: 'next', title: 'Refund + savings 💰',
      text: 'Her final share, the refund back to her wallet, and how much she saved versus the regular supermarket price.',
      go: () => ({ who: 'admin', to: '?p=admin_reports' }) },
    { page: 'admin_reports', target: '[data-tour=kpis]', act: 'next', title: 'HOA Reports',
      text: 'The HOA sees totals for the whole village: trips, families, money pooled, savings and traveler earnings. Plus charts and CSV downloads for their records.' },
    { chapter: 9, role: 'requester', page: '*', title: "That's NeighborhoodMart! 🎉", final: true,
      text: 'Traveler signs up → HOA verifies → trip posted → neighbors pool → everyone pays upfront → shop with live alerts → receipts → fair shares and refunds. Thanks for touring with me!' },
  ];
  // CHECKPOINTS: for every step, [milestones done before it, who is logged in, which page]. Used to roll back.
  const T = '?p=trip&id={trip}';
  const CP = [[0,'guest','index.php'],[0,'guest','index.php'],[0,'guest','index.php'],[0,'guest','?p=register'],[1,'guest','?p=login'],
    [1,'tour0','?p=choose_role'],[1,'tour','?p=traveler_apply'],[2,'tour','?p=traveler_apply'],[2,'tour','?p=traveler_apply'],
    [2,'admin','index.php'],[2,'admin','?p=admin_users'],[2,'admin','?p=admin_users'],[3,'admin','?p=admin_users'],[3,'admin','?p=admin_travelers'],
    [3,'admin','?p=admin_travelers'],[4,'admin','?p=admin_travelers'],[4,'tour','index.php'],[4,'tour','?p=new_trip'],[5,'tour',T],[5,'tour',T],
    [6,'requester',T],[7,'requester',T],[7,'requester',T],[7,'requester',T],[7,'tour',T],[8,'tour',T],[8,'requester','index.php'],[9,'requester',T],
    [10,'tour',T],[11,'tour',T],[12,'tour',T],[13,'tour',T],[14,'tour',T],[14,'tour',T],[15,'tour',T],[15,'requester','index.php'],[16,'requester',T],
    [16,'admin','?p=admin_reports'],[16,'admin','?p=admin_reports']];
  const CHAPTERS = STEPS.map((s, i) => s.chapter > 0 && s.chapter < 9 ? i : -1).filter(i => i >= 0);
  const chapterOf = i => { let c = 0; CHAPTERS.forEach((ci, k) => { if (ci <= i) c = k; }); return c; };
  const TOTAL = STEPS.filter(s => !s.chapter && s.chapter !== 0).length;

  // ---------------------------------------------------------------- state
  function load() { try { return JSON.parse(localStorage.getItem(KEY)) || null; } catch { return null; } }
  function save() { try { localStorage.setItem(KEY, JSON.stringify(S)); } catch {} }
  function clear() { try { localStorage.removeItem(KEY); } catch {} }

  // start buttons (guest pages)
  document.querySelectorAll('[data-tour-start]').forEach(b => b.addEventListener('click', () => {
    showChapter(STEPS[0], () => {
      const st = { i: 1, email: 'tour-' + Math.random().toString(36).slice(2, 8) + '@demo.ph', trip: 0 };
      try { localStorage.setItem(KEY, JSON.stringify(st)); } catch {}
      post({ a: 'demo_start' });
    }, 'Start the tour ▶');
  }));
  if (!S || typeof S.i !== 'number') return;
  if (qs.has('tt')) { S.trip = +qs.get('tt') || 0; save(); }   // a rollback tells us the trip id (0 = no trip yet)

  // ---------------------------------------------------------------- helpers
  function post(fields) {
    const f = document.createElement('form'); f.method = 'post'; f.action = 'index.php'; f.style.display = 'none';
    Object.entries({ csrf: CSRF, ...fields }).forEach(([k, v]) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = k; i.value = v ?? ''; f.appendChild(i); });
    document.body.appendChild(f); f.submit();
  }
  function gotoStep(k) {
    const cp = CP[k] || CP[1]; S.i = k; S.good = k; if (cp[0] < 5) S.trip = 0; save();
    const ov = el('div', 'tour-chapter tour-rewind', '<div class="tc-card"><img src="assets/brand/chanelly.png" alt=""><div><h2>Rewinding time… ⏪</h2><p>Putting everything back exactly how it was.</p></div></div>');
    document.body.appendChild(ov);
    post({ a: 'demo_goto', need: cp[0], who: cp[1], to: cp[2], email: S.email });
  }
  function goAs(g) { post({ a: 'demo_as', who: g.who, to: g.to === '?p=home' ? 'index.php' : g.to, email: S.email, assist: g.assist || '', trip: g.trip || '' }); }
  const app = () => (document.body.className.match(/app-(\w+)/) || [])[1];
  const page = () => qs.get('p') || 'home';
  function onPage(st) {
    if (st.page === '*') return true;
    if (page() !== st.page) return false;
    if (st.app && app() !== st.app) return false;
    if (st.page === 'trip' && S.trip && qs.get('id') !== String(S.trip)) return false;
    return true;
  }
  function find(st) {
    const sel = typeof st.target === 'function' ? st.target() : st.target; if (!sel) return null;
    const all = [...document.querySelectorAll(sel)]; return st.pick === 'last' ? all[all.length - 1] : all[0] || null;
  }
  const wait = ms => new Promise(r => setTimeout(r, ms));
  // phones: the bubble docks at the bottom, so put the target in the top third of the screen
  function focusOn(el) {
    if (!el) return;
    if (innerWidth <= 720) { const r = el.getBoundingClientRect(); scrollTo({ top: scrollY + r.top - innerHeight * 0.22, behavior: 'smooth' }); }
    else el.scrollIntoView({ block: 'center', behavior: 'smooth' });
  }
  async function fill(list) {
    for (const [sel, val] of list) {
      const el = document.querySelector(sel); if (!el) continue;
      focusOn(el); await wait(140);
      el.classList.add('tour-typing');
      if (el.type === 'checkbox' || el.type === 'radio') { el.checked = !!val; }
      else if (el.tagName === 'SELECT' || el.type === 'date' || el.type === 'datetime-local') { el.value = val; el.dispatchEvent(new Event('change', { bubbles: true })); await wait(180); }
      else { el.value = ''; for (const ch of String(val)) { el.value += ch; await wait(22); } el.dispatchEvent(new Event('input', { bubbles: true })); }
      await wait(90); el.classList.remove('tour-typing');
    }
  }

  // ---------------------------------------------------------------- UI
  let spot, bubble, hand, blocks = [], hole = false, gap = 28;
  function ui() {
    spot = el('div', 'tour-spot'); hand = el('div', 'tour-hand', '👆'); bubble = el('div', 'tour-bubble');
    blocks = [0, 1, 2, 3].map(() => el('div', 'tour-block'));
    blocks.forEach(b => b.addEventListener('click', nudge));
    document.body.append(...blocks, spot, hand, bubble);
    addEventListener('resize', place); addEventListener('scroll', place, { passive: true });
  }
  // clicks outside the highlighted button are blocked, so the script can't go off track by accident
  function nudge(e) { e.preventDefault(); e.stopPropagation(); bubble.classList.remove('shake'); void bubble.offsetWidth; bubble.classList.add('shake'); }
  function placeBlocks(r) {
    const W = document.documentElement.scrollWidth, H = Math.max(document.documentElement.scrollHeight, innerHeight);
    const t = r ? r.top + scrollY - 6 : H, b = r ? r.bottom + scrollY + 6 : H, l = r ? r.left + scrollX - 6 : 0, rt = r ? r.right + scrollX + 6 : W;
    const set = (d, x, y, w, h) => Object.assign(d.style, { left: x + 'px', top: y + 'px', width: Math.max(0, w) + 'px', height: Math.max(0, h) + 'px', display: 'block' });
    if (!r) { set(blocks[0], 0, 0, W, H); [1, 2, 3].forEach(i => blocks[i].style.display = 'none'); return; }
    set(blocks[0], 0, 0, W, t); set(blocks[1], 0, b, W, H - b); set(blocks[2], 0, t, l, b - t); set(blocks[3], rt, t, W - rt, b - t);
  }
  function el(tag, cls, html) { const e = document.createElement(tag); e.className = cls; if (html) e.innerHTML = html; return e; }
  let curTarget = null;
  function place() {
    if (!curTarget || !spot) return;
    const r = curTarget.getBoundingClientRect(), pad = 6;
    if (blocks.length) placeBlocks(hole ? r : null);
    Object.assign(spot.style, { top: r.top + scrollY - pad + 'px', left: r.left + scrollX - pad + 'px', width: r.width + pad * 2 + 'px', height: r.height + pad * 2 + 'px', display: 'block' });
    hand.style.top = r.bottom + scrollY - 4 + 'px'; hand.style.left = r.left + scrollX + Math.min(r.width / 2, 60) + 'px';
    if (innerWidth <= 720) { bubble.classList.add('dock'); return; }
    bubble.classList.remove('dock');
    const bw = bubble.offsetWidth, bh = bubble.offsetHeight;
    // leave room for the bouncing hand under the button, so Chanelly never sits on it
    let top = r.bottom + scrollY + gap; if (r.bottom + bh + gap + 14 > innerHeight && r.top > bh + 40) top = r.top + scrollY - bh - 30;
    const left = Math.max(12, Math.min(r.left + scrollX, scrollX + innerWidth - bw - 12));
    bubble.style.top = top + 'px'; bubble.style.left = left + 'px';
  }
  function say(st, { n, onNext, onDo }) {
    bubble.innerHTML = `<img src="assets/brand/chanelly-sm.png" alt="Chanelly"><div class="tb-body"><div class="tb-top"><b>${st.title}</b><span>${n ? 'Ch. ' + (chapterOf(S.i) + 1) + ' · Step ' + n + ' of ' + TOTAL : ''}</span></div>
      <p>${st.text}</p><div class="tb-chapters" hidden>${CHAPTERS.map((ci, k) => `<button type="button" class="link" data-go="${ci}">${k + 1}. ${STEPS[ci].title.replace(/^Chapter \d+ · /, '')}</button>`).join('')}<button type="button" class="link" data-go="1">🔄 Start over</button></div>
      <div class="tb-foot"><button type="button" class="link tb-exit">Exit demo</button><button type="button" class="link tb-menu">☰ Chapters</button>
      ${st.act === 'click' ? '<span class="tb-hint">👆 Click the highlighted button</span><button type="button" class="s sm tb-do">Do it for me</button>' : '<button type="button" class="tb-next">Continue ▶</button>'}</div></div>`;
    bubble.querySelector('.tb-exit').onclick = exitTour;
    bubble.querySelector('.tb-menu').onclick = () => { const m = bubble.querySelector('.tb-chapters'); m.hidden = !m.hidden; place(); };
    bubble.querySelectorAll('[data-go]').forEach(b => b.onclick = () => gotoStep(+b.dataset.go));
    bubble.querySelector('.tb-next')?.addEventListener('click', onNext);
    bubble.querySelector('.tb-do')?.addEventListener('click', onDo);
    bubble.style.display = 'flex'; place();
  }
  function showChapter(st, onGo, label) {
    const ov = el('div', 'tour-chapter app-' + (st.role || 'guest'));
    const n = st.chapter > 0 && st.chapter < 9 ? `<small>Chapter ${st.chapter} of 5</small>` : '';
    const btns = st.final
      ? `<button type="button" class="tc-go" data-x="explore">Explore as Maria</button><button type="button" class="s tc-go" data-x="restart">Restart demo</button><button type="button" class="link tc-go" data-x="exit">Exit &amp; log out</button>`
      : `<button type="button" class="tc-go">${label || 'Continue ▶'}</button>${st.chapter ? '<button type="button" class="link tc-exit">Exit demo</button>' : '<button type="button" class="link tc-exit">Not now</button>'}`;
    ov.innerHTML = `<div class="tc-card"><img src="assets/brand/chanelly.png" alt="Chanelly"><div>${n}<h2>${st.title}</h2><p>${st.text}</p><div class="actions">${btns}</div></div></div>`;
    document.body.appendChild(ov);
    ov.querySelectorAll('.tc-go').forEach(b => b.onclick = () => { ov.remove(); onGo(b.dataset.x); });
    ov.querySelector('.tc-exit')?.addEventListener('click', () => { ov.remove(); if (st.chapter) exitTour(); });
  }
  function exitTour(logout) { clear(); [spot, hand, bubble, ...blocks].forEach(x => x?.remove()); blocks = []; if (logout === true) post({ a: 'logout' }); }

  // ---------------------------------------------------------------- run current step
  async function run() {
    const st = STEPS[S.i]; if (!st) { clear(); return; }
    if (st.saveTrip && page() === 'trip') { S.trip = +qs.get('id'); save(); }   // the trip Ed just posted
    if (st.chapter !== undefined) {
      showChapter(st, (x) => {
        if (st.final) {
          if (x === 'explore') { clear(); goAs({ who: 'requester', to: '?p=home' }); }
          else if (x === 'restart') { S.i = 1; S.email = 'tour-' + Math.random().toString(36).slice(2, 8) + '@demo.ph'; S.trip = 0; save(); post({ a: 'demo_start' }); }
          else exitTour(true);
          return;
        }
        S.i++; S.url = location.search; save();
        if (st.go) goAs(st.go()); else run();
      });
      return;
    }
    if (!onPage(st)) return lost(st, 'page');
    // ask the server whether the data really matches the script at this step (catches wrong clicks like Reject/Cancel)
    try {
      const r = await fetch('index.php?p=demo_state&email=' + encodeURIComponent(S.email) + '&trip=' + (S.trip || 0), { cache: 'no-store' }).then(x => x.json());
      const cp = CP[S.i]; const wantWho = cp[1] === 'guest' ? 'guest' : (cp[1].startsWith('tour') ? S.email : cp[1]);
      if (r.m !== cp[0] || r.who !== wantWho) return lost(st, 'state');
    } catch (e) { /* offline: just continue */ }
    S.good = S.i; save();
    await wait(150);
    const target = find(st);
    if (!target && st.act === 'click') return lost(st, 'state');   // the button to click is gone = something else was clicked
    ui();
    hole = false; gap = st.act === 'click' ? 76 : 30;   // everything stays locked until the step is fully ready
    curTarget = target || document.querySelector('main h2') || document.querySelector('main');
    focusOn(curTarget); await wait(350);
    hand.style.display = 'none'; place();
    const n = STEPS.slice(0, S.i + 1).filter(s => s.chapter === undefined).length;
    let ready = false;
    say(st, { n, onNext: next, onDo: () => { if (ready && target) target.click(); } });
    bubble.classList.add('busy');
    if (st.fill) { await fill(st.fill()); focusOn(curTarget); await wait(400); }
    if (st.act === 'click') {
      // listen for the click BEFORE unlocking the button, so no click can slip through un-counted
      const advance = () => { S.prev = S.i; S.url = location.search; S.i++; save(); blocks.forEach(b => b.style.display = 'none'); };
      target.addEventListener('click', advance, { once: true, capture: true });
      hole = true; spot.classList.add('pulse'); hand.style.display = 'block';
    }
    ready = true; bubble.classList.remove('busy'); place();
    function next() { S.prev = S.i; S.url = location.search; S.i++; save(); if (st.go) goAs(st.go()); else { [spot, hand, bubble, ...blocks].forEach(x => x.remove()); blocks = []; run(); } }
  }
  function lost(st, why) {
    ui(); hole = false; curTarget = null; spot.style.display = 'none'; hand.style.display = 'none'; placeBlocks(null);
    const good = typeof S.good === 'number' ? S.good : 1, gs = STEPS[good], ch = chapterOf(good);
    const gNum = STEPS.slice(0, good + 1).filter(x => x.chapter === undefined).length;
    bubble.innerHTML = `<img src="assets/brand/chanelly-sm.png" alt="Chanelly"><div class="tb-body"><div class="tb-top"><b>Oops! We went off the script 🐾</b></div>
      <p>${why === 'state' ? 'Something was clicked that the tour didn\'t expect, so the data no longer matches.' : 'This isn\'t the screen the tour expected.'}
      Don't worry, I saved your spot: <b>Chapter ${ch + 1}</b>${gs && gs.chapter === undefined ? ', step ' + gNum + ' "' + gs.title + '"' : ''}.</p>
      <div class="tb-choices">
        <button type="button" class="tb-c1">↩ Roll back to my last good step</button>
        <button type="button" class="s tb-c2">⏮ Restart Chapter ${ch + 1}</button>
        <button type="button" class="s tb-c3">🔄 Start over from the beginning</button>
        <button type="button" class="link tb-exit">Exit demo</button></div></div>`;
    bubble.classList.add('dock'); bubble.style.display = 'flex';
    bubble.querySelector('.tb-c1').onclick = () => gotoStep(good);
    bubble.querySelector('.tb-c2').onclick = () => gotoStep(CHAPTERS[ch] || 1);
    bubble.querySelector('.tb-c3').onclick = () => { S.trip = 0; gotoStep(1); };
    bubble.querySelector('.tb-exit').onclick = exitTour;
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run); else run();
})();
