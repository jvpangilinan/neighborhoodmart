/* GUIDED MODE: Chanelly points to the ONE next thing to do on this screen, based on the person's real situation
   (the server decides the hints in views/guide.php). Gentle: no dark overlay, nothing is blocked or clicked for you.
   Off while the Demo tour runs. "Got it" hides a tip for this visit; "Turn off" saves the choice to the account. */
(function () {
  'use strict';
  const touring = () => { try { return !!localStorage.getItem('nmTour'); } catch { return false; } };
  if (touring()) return;
  const setup = document.querySelector('.guide-setup');
  if (setup) { setup.hidden = false; setup.querySelector('button')?.focus(); return; }
  const dataEl = document.getElementById('guide-data'); if (!dataEl) return;
  const D = JSON.parse(dataEl.textContent), seenKey = 'nmGuideSeen:' + D.page;
  const seen = new Set(JSON.parse(sessionStorage.getItem(seenKey) || '[]'));
  const visible = el => el && el.getClientRects().length && getComputedStyle(el).visibility !== 'hidden';
  const hints = D.hints.map(h => ({ ...h, el: [...document.querySelectorAll(h.sel)].find(visible) })).filter(h => h.el && !seen.has(h.sel));
  if (!hints.length) return;
  let k = 0, ring, bub;
  function post(fields) {
    const f = document.createElement('form'); f.method = 'post'; f.action = 'index.php'; f.hidden = true;
    Object.entries({ csrf: D.csrf, ...fields }).forEach(([n, v]) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = n; i.value = v; f.appendChild(i); });
    document.body.appendChild(f); f.submit();
  }
  function place() {
    const h = hints[k]; if (!h) return; const r = h.el.getBoundingClientRect(), pad = 6;
    Object.assign(ring.style, { top: r.top + scrollY - pad + 'px', left: r.left + scrollX - pad + 'px', width: r.width + pad * 2 + 'px', height: r.height + pad * 2 + 'px' });
    if (innerWidth <= 720) {
      // phones: the bubble docks at the top or bottom, whichever side the button is NOT on, so it never covers it
      const low = r.top + r.height / 2 > innerHeight / 2;
      bub.classList.add('dock'); bub.classList.toggle('dock-top', low); return;
    }
    bub.classList.remove('dock', 'dock-top');
    const bh = bub.offsetHeight, bw = bub.offsetWidth;
    let top = r.bottom + scrollY + 18; if (r.bottom + bh + 30 > innerHeight && r.top > bh + 30) top = r.top + scrollY - bh - 18;
    bub.style.top = top + 'px'; bub.style.left = Math.max(12, Math.min(r.left + scrollX, scrollX + innerWidth - bw - 12)) + 'px';
  }
  function show() {
    const h = hints[k];
    bub.innerHTML = `<img src="assets/brand/chanelly-sm.png" alt="Chanelly"><div class="tb-body"><div class="tb-top"><b></b><span>Guided mode</span></div><p></p>
      <div class="tb-foot"><button type="button" class="link g-off">Turn off guided mode</button>${hints.length > 1 ? '<button type="button" class="s sm g-next">Next tip ▶</button>' : ''}<button type="button" class="sm g-ok">Got it 👍</button></div></div>`;
    bub.querySelector('b').textContent = h.title; bub.querySelector('p').textContent = h.text;
    bub.querySelector('.g-ok').onclick = () => { seen.add(h.sel); sessionStorage.setItem(seenKey, JSON.stringify([...seen])); ring.remove(); bub.remove(); document.body.classList.remove('guiding'); };
    bub.querySelector('.g-next')?.addEventListener('click', () => { k = (k + 1) % hints.length; show(); });
    bub.querySelector('.g-off').onclick = () => post({ a: 'set_guided', on: '0', back: D.back });
    const r = h.el.getBoundingClientRect();
    if (innerWidth <= 720) scrollTo({ top: Math.max(0, scrollY + r.top - 110), behavior: 'smooth' });   // phones: button near the top, bubble below
    else if (r.top < 70 || r.bottom > innerHeight - 40) h.el.scrollIntoView({ block: 'center', behavior: 'smooth' });
    setTimeout(place, 450); setTimeout(place, 900); place();
  }
  ring = document.createElement('div'); ring.className = 'guide-ring';
  bub = document.createElement('div'); bub.className = 'tour-bubble guide-bubble'; bub.setAttribute('role', 'status');
  document.body.append(ring, bub); bub.style.display = 'flex';
  document.body.classList.add('guiding');   // extra space at the bottom so any button can scroll clear of the bubble
  addEventListener('resize', place); addEventListener('scroll', place, { passive: true });
  setTimeout(show, 250);
})();
