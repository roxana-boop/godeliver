document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('adminMobileToggle');
  const sidebar = document.getElementById('adminSidebar');
  if (toggle && sidebar) {
    toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
  }
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      if (!confirm(el.dataset.confirm || 'Ești sigur?')) e.preventDefault();
    });
  });
});
