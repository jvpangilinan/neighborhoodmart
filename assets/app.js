/* EASY-TO-USE HELPERS (all pages): display settings (Aa), big Yes/No confirmations, Chanelly help panel. */
(function () {
  'use strict';
  const root = document.documentElement, KEY = 'nmView';
  const get = () => { try { return JSON.parse(localStorage.getItem(KEY)) || {}; } catch { return {}; } };
  const set = v => { try { localStorage.setItem(KEY, JSON.stringify(v)); } catch {} apply(v); };
  function apply(v) {
    root.classList.remove('t-l', 't-xl'); if (v.t === 'l' || v.t === 'xl') root.classList.add('t-' + v.t);
    root.classList.toggle('simple', !!v.simple); root.classList.toggle('hc', !!v.hc);
    document.querySelectorAll('[data-view-t]').forEach(b => b.classList.toggle('on', (v.t || 'n') === b.dataset.viewT));
    document.querySelectorAll('[data-view-sw]').forEach(c => c.checked = !!v[c.dataset.viewSw]);
  }
  apply(get());
  // Aa panel
  const aa = document.querySelector('[data-view-open]'), panel = document.querySelector('.view-panel');
  if (aa && panel) {
    aa.addEventListener('click', e => { e.stopPropagation(); panel.hidden = !panel.hidden; aa.setAttribute('aria-expanded', !panel.hidden); });
    document.addEventListener('click', e => { if (!panel.hidden && !panel.contains(e.target)) { panel.hidden = true; aa.setAttribute('aria-expanded', 'false'); } });
    panel.querySelectorAll('[data-view-t]').forEach(b => b.addEventListener('click', () => set({ ...get(), t: b.dataset.viewT })));
    panel.querySelectorAll('[data-view-sw]').forEach(c => c.addEventListener('change', () => set({ ...get(), [c.dataset.viewSw]: c.checked })));
  }
  // Yes/No confirmation for important buttons. Skipped while Chanelly's tour is running (she explains each step instead).
  const touring = () => { try { return !!localStorage.getItem('nmTour'); } catch { return false; } };
  document.addEventListener('submit', e => {
    const f = e.target, b = e.submitter, msg = (b && b.dataset.confirm) || f.dataset.confirm;
    if (!msg || f.dataset.ok === '1' || touring()) return;
    e.preventDefault();
    const ov = document.createElement('div'); ov.className = 'confirm-ov';
    ov.innerHTML = `<div class="confirm-box" role="alertdialog" aria-modal="true" aria-labelledby="cf-t"><h3 id="cf-t">Are you sure?</h3><p></p>
      <div class="actions"><button type="button" class="s cf-no">No, go back</button><button type="button" class="cf-yes">Yes</button></div></div>`;
    ov.querySelector('p').textContent = msg;
    document.body.appendChild(ov); ov.querySelector('.cf-no').focus();
    const close = () => ov.remove();
    ov.querySelector('.cf-no').onclick = close; ov.addEventListener('click', ev => { if (ev.target === ov) close(); });
    document.addEventListener('keydown', function esc(ev) { if (ev.key === 'Escape') { close(); document.removeEventListener('keydown', esc); } });
    ov.querySelector('.cf-yes').onclick = () => {
      close(); f.dataset.ok = '1';
      if (b && b.name) { const h = document.createElement('input'); h.type = 'hidden'; h.name = b.name; h.value = b.value; f.appendChild(h); }
      f.submit();
    };
  });
  // Chanelly help panel
  const hb = document.querySelector('.help-fab'), hp = document.querySelector('.help-panel');
  if (hb && hp) {
    if (touring()) hb.hidden = true;
    hb.addEventListener('click', () => { hp.hidden = !hp.hidden; hb.setAttribute('aria-expanded', !hp.hidden); if (!hp.hidden) hp.querySelector('h3').focus(); });
    hp.querySelector('.hp-close')?.addEventListener('click', () => { hp.hidden = true; hb.focus(); });
  }
})();
