/* GoDeliver — dynamic blog feed (progressive enhancement) */
(() => {
  const grid = document.getElementById('blogGrid');
  if (!grid) return;

  const palette = ['#2a2410', '#101820', '#1c1010', '#101c14', '#1a1420', '#201810'];

  fetch('backend/api_blog.php')
    .then(res => res.json())
    .then(data => {
      if (!data.success || !Array.isArray(data.posts) || data.posts.length === 0) {
        return; // keep the static example cards already in the page
      }
      grid.innerHTML = '';
      data.posts.forEach((post, i) => {
        const color = palette[i % palette.length];
        const date = post.published_at
          ? new Date(post.published_at).toLocaleDateString('ro-RO', { day: '2-digit', month: 'short', year: 'numeric' })
          : '';
        const card = document.createElement('article');
        card.className = 'blog-card reveal in';
        card.innerHTML = `
          <div class="blog-thumb" style="background:linear-gradient(135deg,${color},#0A0A0B);"></div>
          <div class="blog-body">
            <span class="blog-tag">${escapeHtml(post.category || 'Noutăți')}</span>
            <h3>${escapeHtml(post.title)}</h3>
            <p style="font-size:13.5px;">${escapeHtml(post.excerpt || '')}</p>
            <div class="blog-meta">${date}</div>
          </div>`;
        grid.appendChild(card);
      });
    })
    .catch(() => { /* silently keep the static fallback */ });

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }
})();
