/* =========================================================
   GoDeliver — main.js
   Shared behaviour across all public pages.
   ========================================================= */

initPreloader();

document.addEventListener('DOMContentLoaded', () => {
  initHeader();
  initMobileNav();
  initRevealOnScroll();
  initCounters();
  initBackToTop();
  initCookieBanner();
  initFaqAccordion();
  initFaqTabs();
  initCalculator();
  initYear();
  initLangSwitch();
});

/* ---------- Language switch (UI ready; EN content coming soon) ---------- */
function initLangSwitch(){
  document.querySelectorAll('.lang-switch button').forEach(btn => {
    btn.addEventListener('click', () => {
      if(btn.dataset.lang === 'en'){
        showToast('Versiunea în engleză va fi disponibilă în curând.');
        return;
      }
      document.querySelectorAll('.lang-switch button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });
}

/* ---------- Preloader (scooter crossing the screen) ---------- */
function initPreloader(){
  const pre = document.getElementById('preloader');
  if(!pre){ document.body.classList.remove('is-loading'); return; }
  const minVisible = 900; // ms — avoids an instant flash on fast/cached loads
  const start = performance.now();
  const finish = () => {
    const elapsed = performance.now() - start;
    const wait = Math.max(0, minVisible - elapsed);
    setTimeout(() => {
      pre.classList.add('hide');
      document.body.classList.remove('is-loading');
      setTimeout(() => pre.remove(), 600);
    }, wait);
  };
  if(document.readyState === 'complete') finish();
  else window.addEventListener('load', finish);
  // Safety net: never block the page for more than 4s even if some
  // resource (font/image) stalls.
  setTimeout(finish, 4000);
}

/* ---------- Header shrink on scroll ---------- */
function initHeader(){
  const header = document.querySelector('.site-header');
  if(!header) return;
  const onScroll = () => {
    if(window.scrollY > 20) header.classList.add('scrolled');
    else header.classList.remove('scrolled');
  };
  onScroll();
  window.addEventListener('scroll', onScroll, { passive:true });
}

/* ---------- Mobile nav toggle ---------- */
function initMobileNav(){
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.querySelector('.nav-main');
  if(!toggle || !nav) return;
  toggle.addEventListener('click', () => {
    nav.classList.toggle('open');
    toggle.classList.toggle('active');
  });
  nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
    nav.classList.remove('open');
  }));
}

/* ---------- Reveal on scroll ---------- */
function initRevealOnScroll(){
  const items = document.querySelectorAll('.reveal');
  if(!items.length) return;
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if(entry.isIntersecting){
        entry.target.classList.add('in');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });
  items.forEach(el => io.observe(el));
}

/* ---------- Animated counters ---------- */
function initCounters(){
  const counters = document.querySelectorAll('[data-count]');
  if(!counters.length) return;
  const animate = (el) => {
    const target = parseFloat(el.dataset.count);
    const suffix = el.dataset.suffix || '';
    const duration = 1400;
    const start = performance.now();
    const step = (now) => {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const value = Math.round(target * eased);
      el.textContent = value + suffix;
      if(progress < 1) requestAnimationFrame(step);
      else el.textContent = target + suffix;
    };
    requestAnimationFrame(step);
  };
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if(entry.isIntersecting){
        animate(entry.target);
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.6 });
  counters.forEach(el => io.observe(el));
}

/* ---------- Back to top ---------- */
function initBackToTop(){
  const btn = document.querySelector('.back-to-top');
  if(!btn) return;
  window.addEventListener('scroll', () => {
    if(window.scrollY > 500) btn.classList.add('show');
    else btn.classList.remove('show');
  }, { passive:true });
  btn.addEventListener('click', () => window.scrollTo({ top:0, behavior:'smooth' }));
}

/* ---------- Cookie consent banner ---------- */
function initCookieBanner(){
  const banner = document.querySelector('.cookie-banner');
  if(!banner) return;
  const KEY = 'godeliver_cookie_consent';
  if(!localStorage.getItem(KEY)){
    setTimeout(() => banner.classList.add('show'), 900);
  }
  banner.querySelectorAll('[data-cookie]').forEach(btn => {
    btn.addEventListener('click', () => {
      localStorage.setItem(KEY, btn.dataset.cookie);
      banner.classList.remove('show');
    });
  });
}

/* ---------- FAQ accordion ---------- */
function initFaqAccordion(){
  document.querySelectorAll('.faq-item .faq-q').forEach(q => {
    q.addEventListener('click', () => {
      const item = q.closest('.faq-item');
      const wasOpen = item.classList.contains('open');
      item.closest('.faq-list').querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      if(!wasOpen) item.classList.add('open');
    });
  });
}

/* ---------- FAQ category tabs ---------- */
function initFaqTabs(){
  const tabs = document.querySelectorAll('.faq-tab');
  if(!tabs.length) return;
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const cat = tab.dataset.cat;
      document.querySelectorAll('.faq-list .faq-item').forEach(item => {
        item.style.display = (cat === 'all' || item.dataset.cat === cat) ? '' : 'none';
      });
    });
  });
}

/* ---------- Earnings calculator ---------- */
const CALC_BASE_RATES = { // RON per hour, base averages by platform
  glovo: 21, bolt: 19.5, wolt: 20
};
const CALC_CITY_MULTIPLIER = {
  bucuresti: 1.15, cluj: 1.05, timisoara: 1.0, iasi: 0.92, constanta: 0.95
};
const CALC_VEHICLE_MULTIPLIER = { bike: 0.85, scooter: 1.0, car: 1.2 };

function initCalculator(){
  const calc = document.querySelector('.calculator');
  if(!calc) return;
  const city = calc.querySelector('#calc-city');
  const platform = calc.querySelector('#calc-platform');
  const vehicle = calc.querySelector('#calc-vehicle');
  const hours = calc.querySelector('#calc-hours');
  const days = calc.querySelector('#calc-days');
  const hoursOut = calc.querySelector('#calc-hours-val');
  const daysOut = calc.querySelector('#calc-days-val');
  const amountEl = calc.querySelector('.calc-result .amount');
  const rangeEl = calc.querySelector('.calc-result .range');

  function compute(){
    const rate = CALC_BASE_RATES[platform.value] || 20;
    const cityMul = CALC_CITY_MULTIPLIER[city.value] || 1;
    const vehicleMul = CALC_VEHICLE_MULTIPLIER[vehicle.value] || 1;
    const h = parseInt(hours.value, 10);
    const d = parseInt(days.value, 10);
    hoursOut.textContent = h + ' h/zi';
    daysOut.textContent = d + ' zile/săpt.';
    const weekly = rate * cityMul * vehicleMul * h * d;
    const monthly = Math.round(weekly * 4.33 / 10) * 10;
    const low = Math.round(monthly * 0.9 / 10) * 10;
    const high = Math.round(monthly * 1.12 / 10) * 10;
    amountEl.textContent = monthly.toLocaleString('ro-RO') + ' RON';
    rangeEl.textContent = 'Estimare lunară: ' + low.toLocaleString('ro-RO') + ' – ' + high.toLocaleString('ro-RO') + ' RON';
  }

  [city, platform, vehicle, hours, days].forEach(el => el && el.addEventListener('input', compute));
  compute();
}

/* ---------- Footer year ---------- */
function initYear(){
  document.querySelectorAll('[data-year]').forEach(el => el.textContent = new Date().getFullYear());
}

/* ---------- Toast notifications (shared) ---------- */
function showToast(message, type){
  let wrap = document.querySelector('.toast-wrap');
  if(!wrap){
    wrap = document.createElement('div');
    wrap.className = 'toast-wrap';
    document.body.appendChild(wrap);
  }
  const toast = document.createElement('div');
  toast.className = 'toast' + (type === 'error' ? ' error' : '');
  toast.textContent = message;
  wrap.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(20px)';
    setTimeout(() => toast.remove(), 300);
  }, 4200);
}
