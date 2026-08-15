/* GoDeliver — dynamic job listings feed (progressive enhancement) */
(() => {
  const list = document.getElementById('jobsList');
  if (!list) return;

  fetch('backend/api_jobs.php')
    .then(res => res.json())
    .then(data => {
      if (!data.success || !Array.isArray(data.jobs) || data.jobs.length === 0) {
        return; // keep the static example jobs already in the page
      }
      list.innerHTML = '';
      data.jobs.forEach(job => {
        const card = document.createElement('div');
        card.className = 'job-card reveal in';
        card.innerHTML = `
          <div>
            <h3>${escapeHtml(job.title)}</h3>
            <div class="job-meta"><span>🏢 ${escapeHtml(job.department)}</span><span>📍 ${escapeHtml(job.city)}</span><span>🕑 ${escapeHtml(job.employment_type)}</span></div>
          </div>
          <a href="contact.html" class="btn btn-ghost btn-sm">Aplică</a>`;
        list.appendChild(card);
      });
    })
    .catch(() => { /* silently keep the static fallback */ });

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }
})();
