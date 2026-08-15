/* =========================================================
   GoDeliver — Application form (Devino Curier)
   5-step wizard, client-side validation, canvas signature,
   submits multipart/form-data to backend/submit_application.php
   ========================================================= */
(() => {
  const form = document.getElementById('applicationForm');
  if (!form) return;

  const panels = [...form.querySelectorAll('.form-panel')];
  const progressSteps = [...document.querySelectorAll('.progress-step')];
  let currentStep = 1;
  const TOTAL_STEPS = 5;

  function showPanel(step) {
    panels.forEach(p => p.classList.toggle('active', p.dataset.panel == step));
    progressSteps.forEach(ps => {
      const n = parseInt(ps.dataset.step, 10);
      ps.classList.toggle('active', n === step);
      ps.classList.toggle('done', n < step);
    });
    window.scrollTo({ top: document.querySelector('.form-card').offsetTop - 110, behavior: 'smooth' });
  }

  function validatePanel(step) {
    const panel = form.querySelector(`.form-panel[data-panel="${step}"]`);
    const requiredFields = panel.querySelectorAll('[required]');
    let valid = true;
    let firstInvalid = null;

    // Group radios by name so only one message per group
    const seenGroups = new Set();

    requiredFields.forEach(field => {
      if (field.type === 'radio') {
        if (seenGroups.has(field.name)) return;
        seenGroups.add(field.name);
        const checked = panel.querySelector(`input[name="${field.name}"]:checked`);
        if (!checked) { valid = false; if (!firstInvalid) firstInvalid = field; }
        return;
      }
      if (field.type === 'checkbox') {
        if (!field.checked) { valid = false; if (!firstInvalid) firstInvalid = field; }
        return;
      }
      if (field.type === 'file') {
        if (!field.files || field.files.length === 0) { valid = false; if (!firstInvalid) firstInvalid = field; }
        return;
      }
      if (!field.value.trim()) { valid = false; if (!firstInvalid) firstInvalid = field; }
    });

    // CNP basic check (13 digits) on step 1
    if (step === 1) {
      const cnp = form.querySelector('[name="cnp"]');
      if (cnp && cnp.value && !/^\d{13}$/.test(cnp.value.trim())) {
        valid = false; firstInvalid = firstInvalid || cnp;
        showToast('CNP-ul trebuie să conțină exact 13 cifre.', 'error');
      }
      const email = form.querySelector('[name="email"]');
      if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        valid = false; firstInvalid = firstInvalid || email;
        showToast('Adresa de email nu este validă.', 'error');
      }
    }

    if (!valid) {
      if (firstInvalid) firstInvalid.focus({ preventScroll: true });
      showToast('Completează toate câmpurile obligatorii (*) înainte de a continua.', 'error');
    }
    return valid;
  }

  form.querySelectorAll('[data-next]').forEach(btn => {
    btn.addEventListener('click', () => {
      if (!validatePanel(currentStep)) return;
      if (currentStep === 4) buildReview();
      currentStep = Math.min(currentStep + 1, TOTAL_STEPS);
      showPanel(currentStep);
      if (currentStep === 5) {
        // The canvas is inside a panel that was just switched from
        // display:none to visible, so it must be sized *after* that
        // switch — sizing it while hidden is what caused the signature
        // to appear offset from the finger/mouse position.
        requestAnimationFrame(setupSignatureCanvas);
      }
    });
  });
  form.querySelectorAll('[data-prev]').forEach(btn => {
    btn.addEventListener('click', () => {
      currentStep = Math.max(currentStep - 1, 1);
      showPanel(currentStep);
    });
  });

  /* ---------- Choice cards (radio pills) ---------- */
  form.querySelectorAll('.choice-card input[type=radio]').forEach(input => {
    input.addEventListener('change', () => {
      const group = form.querySelectorAll(`input[name="${input.name}"]`);
      group.forEach(i => i.closest('.choice-card').classList.toggle('selected', i.checked));
    });
  });

  /* ---------- File uploads: show filename + drag state ---------- */
  form.querySelectorAll('.upload-box').forEach(box => {
    const input = box.querySelector('input[type=file]');
    const nameEl = box.querySelector('.name');
    input.addEventListener('change', () => {
      if (input.files && input.files[0]) {
        const f = input.files[0];
        if (f.size > 10 * 1024 * 1024) {
          showToast('Fișierul „' + f.name + '” depășește 10MB.', 'error');
          input.value = '';
          nameEl.textContent = '';
          return;
        }
        nameEl.textContent = f.name;
      }
    });
    ['dragenter', 'dragover'].forEach(evt => box.addEventListener(evt, (e) => { e.preventDefault(); box.classList.add('drag'); }));
    ['dragleave', 'drop'].forEach(evt => box.addEventListener(evt, (e) => { e.preventDefault(); box.classList.remove('drag'); }));
  });

  /* ---------- Review builder ---------- */
  function buildReview() {
    const data = new FormData(form);
    const get = (k) => (data.get(k) || '—');
    const container = document.getElementById('reviewContainer');
    container.innerHTML = `
      <div class="review-block">
        <h4>Informații Personale</h4>
        <dl>
          <dt>Nume</dt><dd>${get('firstName')} ${get('lastName')}</dd>
          <dt>Telefon</dt><dd>${get('phone')}</dd>
          <dt>Email</dt><dd>${get('email')}</dd>
          <dt>Oraș</dt><dd>${get('city')}</dd>
        </dl>
      </div>
      <div class="review-block">
        <h4>Informații de Muncă</h4>
        <dl>
          <dt>Platformă</dt><dd>${get('platform')}</dd>
          <dt>Vehicul</dt><dd>${get('vehicle')}</dd>
          <dt>Disponibilitate</dt><dd>${get('availability')}</dd>
          <dt>Tip Contract</dt><dd>${get('contractType')}</dd>
        </dl>
      </div>
      <div class="review-block">
        <h4>Documente Încărcate</h4>
        <dl>
          <dt>Carte Identitate</dt><dd>${fileName('docId')}</dd>
          <dt>IBAN</dt><dd>${fileName('docIban')}</dd>
          <dt>Selfie ID</dt><dd>${fileName('docSelfie')}</dd>
          <dt>Permis</dt><dd>${fileName('docLicense')}</dd>
        </dl>
      </div>
    `;
  }
  function fileName(name) {
    const input = form.querySelector(`[name="${name}"]`);
    return (input && input.files && input.files[0]) ? input.files[0].name : '—';
  }

  /* ---------- Signature pad ----------
     Fix for "semnez într-un loc, apare în altul": the canvas' internal
     pixel buffer must be sized from getBoundingClientRect() *while the
     panel is visible*. Sizing it earlier (panel still display:none)
     measures a 0×0 box, so every coordinate drawn afterwards is scaled
     against the wrong reference and lands in the wrong place. We now
     size it lazily (right when step 5 opens) and re-measure the rect
     on every stroke instead of caching it. */
  const canvas = document.getElementById('signaturePad');
  let drawing = false, hasSignature = false, activePointerId = null;
  let ctx = null;

  function setupSignatureCanvas() {
    if (!canvas) return;
    const ratio = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    if (rect.width === 0) return; // still hidden somehow — skip, will retry on next open
    canvas.width = rect.width * ratio;
    canvas.height = rect.height * ratio;
    ctx = canvas.getContext('2d');
    ctx.scale(ratio, ratio);
    ctx.strokeStyle = '#FFC400';
    ctx.lineWidth = 2.4;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
  }

  function pointFromEvent(e) {
    const rect = canvas.getBoundingClientRect(); // measured fresh every time
    return { x: e.clientX - rect.left, y: e.clientY - rect.top };
  }
  function startDraw(e) {
    if (!ctx) setupSignatureCanvas();
    if (!ctx) return;
    drawing = true;
    hasSignature = true;
    activePointerId = e.pointerId;
    canvas.setPointerCapture(e.pointerId);
    const p = pointFromEvent(e);
    ctx.beginPath();
    ctx.moveTo(p.x, p.y);
    e.preventDefault();
  }
  function moveDraw(e) {
    if (!drawing || e.pointerId !== activePointerId) return;
    const p = pointFromEvent(e);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
    e.preventDefault();
  }
  function endDraw(e) {
    if (e.pointerId !== undefined && e.pointerId !== activePointerId) return;
    drawing = false;
  }

  if (canvas) {
    canvas.addEventListener('pointerdown', startDraw);
    canvas.addEventListener('pointermove', moveDraw);
    window.addEventListener('pointerup', endDraw);
    window.addEventListener('pointercancel', endDraw);
    window.addEventListener('resize', () => { if (form.querySelector('[data-panel="5"]').classList.contains('active')) setupSignatureCanvas(); });
    document.getElementById('clearSignature').addEventListener('click', () => {
      if (!ctx) return;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      hasSignature = false;
    });
  }

  /* ---------- Submit ---------- */
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!validatePanel(5)) return;
    if (!hasSignature) { showToast('Te rugăm să semnezi înainte de a trimite aplicația.', 'error'); return; }

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Se trimite...';

    const formData = new FormData(form);
    formData.append('signature', canvas.toDataURL('image/png'));

    try {
      const res = await fetch('backend/submit_application.php', { method: 'POST', body: formData });
      const result = await res.json();
      if (result.success) {
        document.getElementById('appIdDisplay').textContent = result.applicationId;
        panels.forEach(p => p.classList.remove('active'));
        form.querySelector('[data-panel="success"]').classList.add('active');
        progressSteps.forEach(ps => ps.classList.add('done'));
        window.scrollTo({ top: document.querySelector('.form-card').offsetTop - 110, behavior: 'smooth' });
      } else {
        showToast(result.message || 'A apărut o eroare. Încearcă din nou.', 'error');
      }
    } catch (err) {
      showToast('Nu am putut trimite aplicația. Verifică conexiunea și încearcă din nou.', 'error');
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Trimite Aplicația';
    }
  });
})();
