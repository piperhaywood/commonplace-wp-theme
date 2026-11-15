document.querySelectorAll('[data-post-browser]').forEach((root) => {
  const grid = root.querySelector('[data-post-items]');
  const loader = root.querySelector('.post-loader');
  if (!grid || !loader) return;
  const button = loader.querySelector('button');
  const status = loader.querySelector('[role="status"]');
  const pagination = root.querySelector('[data-post-pagination]') ||
    (root.dataset.postBrowser === 'archive' ? document.querySelector('.pagination--archive') : null);
  const label = button.textContent;
  let next = button.dataset.next;
  let loading = false;
  let cacheKey;

  // Associate the snapshot with this history entry, not just this archive URL.
  // Failure to access storage must never prevent loading or ordinary navigation.
  try {
    const id = history.state?.cpPostBrowserId || crypto.randomUUID();
    history.replaceState({ ...history.state, cpPostBrowserId: id }, '');
    cacheKey = `cp-posts-v2:${id}:${root.dataset.postBrowser}`;
    const saved = JSON.parse(sessionStorage.getItem(cacheKey));
    if (saved?.url === location.href) {
      const restored = new DOMParser().parseFromString(saved.html, 'text/html');
      grid.replaceChildren(...restored.body.children);
      next = saved.next;
      // Restore after fonts settle; preview boxes already reserve image space.
      addEventListener('pageshow', async (event) => {
        if (event.persisted) return;
        await document.fonts.ready;
        requestAnimationFrame(() => {
          if (!location.hash) window.scrollTo(0, saved.scroll);
        });
      }, { once: true });
    }
  } catch {
    // Browser history and the back/forward cache remain available without storage.
  }

  function save() {
    if (!cacheKey) return;
    try {
      sessionStorage.setItem(cacheKey, JSON.stringify({
        url: location.href,
        html: grid.innerHTML,
        next,
        scroll: window.scrollY,
      }));
    } catch {
      // Storage can be disabled or full. The live grid still works.
    }
  }

  function updateButton() {
    button.hidden = !next;
    button.removeAttribute('aria-disabled');
    button.textContent = label;
  }

  loader.hidden = false;
  if (pagination) pagination.hidden = true;
  updateButton();
  if (!next) status.textContent = button.dataset.end;

  // pagehide preserves eligibility for the browser's back/forward cache.
  addEventListener('pagehide', save);
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') save();
  });
  grid.addEventListener('click', save);

  button.addEventListener('click', async () => {
    if (loading || !next) return;
    loading = true;
    button.setAttribute('aria-disabled', 'true');
    button.textContent = button.dataset.loading;
    status.textContent = button.dataset.loading;
    grid.setAttribute('aria-busy', 'true');

    try {
      const url = new URL(next, location.href);
      if (url.origin !== location.origin) throw new Error('Unexpected origin');
      const response = await fetch(url);
      if (!response.ok || new URL(response.url).origin !== location.origin) {
        throw new Error('Page unavailable');
      }
      const page = new DOMParser().parseFromString(await response.text(), 'text/html');
      const nextRoot = [...page.querySelectorAll('[data-post-browser]')]
        .find((element) => element.dataset.postBrowser === root.dataset.postBrowser);
      const cards = [...(nextRoot?.querySelector('[data-post-items]')?.children || [])];
      const nextButton = nextRoot?.querySelector('.post-loader__button');
      if (!cards.length || !nextButton) throw new Error('Invalid posts response');
      grid.append(...cards);
      next = nextButton.dataset.next;
      const shouldMoveFocus = document.activeElement === button;
      updateButton();
      status.textContent = next ? button.dataset.loaded : button.dataset.end;
      if (pagination) pagination.hidden = true;
      // Keep keyboard users at the start of the newly appended batch.
      // Prevent focus from scrolling the page away from the reading position.
      if (shouldMoveFocus) {
        const firstLink = cards[0].querySelector('.post-item__text')?.closest('a') ||
          cards[0].querySelector('a');
        firstLink?.focus({ preventScroll: true });
      }
      save();
    } catch {
      updateButton();
      status.textContent = button.dataset.error;
      if (pagination) pagination.hidden = false;
    } finally {
      loading = false;
      grid.removeAttribute('aria-busy');
    }
  });
});
