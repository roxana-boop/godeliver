/* GoDeliver — Contact form submit handler */
(() => {
  const form = document.getElementById('contactForm');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('button[type=submit]');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Se trimite...';

    const formData = new FormData(form);

    try {
      const res = await fetch('backend/submit_contact.php', { method: 'POST', body: formData });
      const result = await res.json();
      if (result.success) {
        showToast('Mesajul a fost trimis! Te contactăm în curând.');
        form.reset();
      } else {
        showToast(result.message || 'A apărut o eroare. Încearcă din nou.', 'error');
      }
    } catch (err) {
      showToast('Nu am putut trimite mesajul. Verifică conexiunea.', 'error');
    } finally {
      btn.disabled = false;
      btn.textContent = originalText;
    }
  });
})();
