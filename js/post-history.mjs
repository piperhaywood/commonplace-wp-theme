// Keep a small, disposable cache. Browser back/forward caching works independently.
export const HISTORY_PREFIX = 'cp-posts-v3:';
export const MAX_SNAPSHOTS = 8;
export const MAX_CHARACTERS = 1_000_000; // About 2 MiB of UTF-16 text.
const MAX_AGE = 30 * 60 * 1000;

export function createPostHistoryCache(id, getStorage = () => sessionStorage) {
  const key = `${HISTORY_PREFIX}${id}`;
  const contentKey = `${key}:content`;
  const stateKey = `${key}:state`;

  function remove(storage, entry = key) {
    storage.removeItem(`${entry}:content`);
    storage.removeItem(`${entry}:state`);
  }

  function discard() {
    try { remove(getStorage()); } catch { /* Storage may be unavailable. */ }
  }

  function entries(storage) {
    const keys = Array.from({ length: storage.length }, (_, i) => storage.key(i));
    const result = [];
    for (const name of keys) {
      // Old formats cannot be restored by this version and should not fill storage.
      if (/^cp-(posts-v[12]:|grid-v[12]:)/.test(name)) {
        storage.removeItem(name);
        continue;
      }
      if (!name?.startsWith(HISTORY_PREFIX)) continue;
      const entry = name.replace(/:(content|state)$/, '');
      if (name.endsWith(':content')) {
        if (!storage.getItem(`${entry}:state`)) remove(storage, entry);
        continue;
      }
      if (!name.endsWith(':state')) continue;
      try {
        const state = JSON.parse(storage.getItem(name));
        const html = storage.getItem(`${entry}:content`);
        if (!html || !Number.isFinite(state.updated) || Date.now() - state.updated > MAX_AGE) {
          remove(storage, entry);
        } else {
          result.push({ key: entry, size: html.length + storage.getItem(name).length, updated: state.updated });
        }
      } catch { remove(storage, entry); }
    }
    return result.sort((a, b) => a.updated - b.updated);
  }

  return {
    load(url) {
      try {
        const storage = getStorage();
        entries(storage);
        const state = JSON.parse(storage.getItem(stateKey));
        const html = storage.getItem(contentKey);
        if (state?.url === url && html && Number.isFinite(state.scroll)) return { ...state, html };
      } catch { discard(); }
      return null;
    },
    saveContent({ html, ...state }) {
      try {
        const storage = getStorage();
        // Remove the old snapshot first: a failed write must never restore stale rows.
        remove(storage);
        const metadata = JSON.stringify({ ...state, updated: Date.now() });
        const size = html.length + metadata.length;
        if (size > MAX_CHARACTERS) return;
        const cached = entries(storage);
        let total = cached.reduce((sum, entry) => sum + entry.size, 0);
        while (cached.length >= MAX_SNAPSHOTS || total + size > MAX_CHARACTERS) {
          const oldest = cached.shift();
          remove(storage, oldest.key);
          total -= oldest.size;
        }
        storage.setItem(contentKey, html);
        storage.setItem(stateKey, metadata);
      } catch { discard(); }
    },
    savePosition(scroll) {
      try {
        const storage = getStorage();
        const state = JSON.parse(storage.getItem(stateKey));
        if (!state) return;
        storage.setItem(stateKey, JSON.stringify({ ...state, scroll, updated: Date.now() }));
      } catch { discard(); }
    },
  };
}
