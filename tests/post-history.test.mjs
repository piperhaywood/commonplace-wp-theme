import test from 'node:test';
import assert from 'node:assert/strict';
import { createPostHistoryCache, HISTORY_PREFIX, MAX_SNAPSHOTS, MAX_CHARACTERS } from '../js/post-history.mjs';

function storage() {
  const values = new Map();
  return {
    writes: [],
    get length() { return values.size; },
    key(i) { return [...values.keys()][i]; },
    getItem(key) { return values.get(key) ?? null; },
    setItem(key, value) { this.writes.push(key); values.set(key, value); },
    removeItem(key) { values.delete(key); },
  };
}
const snapshot = (html = '<li>Post</li>') => ({ html, url: 'https://example.test/archive/', next: '/page/2/', scroll: 300 });

test('position updates preserve rows and next page without rewriting markup', () => {
  const store = storage();
  const cache = createPostHistoryCache('one', () => store);
  cache.saveContent(snapshot());
  store.writes.length = 0;
  cache.savePosition(900);
  assert.deepEqual(store.writes, [`${HISTORY_PREFIX}one:state`]);
  const saved = cache.load(snapshot().url);
  assert.equal(saved.html, snapshot().html);
  assert.equal(saved.next, '/page/2/');
  assert.equal(saved.scroll, 900);
  assert.equal(cache.load('https://example.test/other/'), null);
});

test('evicts oldest snapshots at the count limit and preserves unrelated storage', () => {
  const store = storage();
  store.setItem('other-feature', 'keep');
  for (let i = 0; i <= MAX_SNAPSHOTS; i++) createPostHistoryCache(String(i), () => store).saveContent(snapshot());
  assert.equal(createPostHistoryCache('0', () => store).load(snapshot().url), null);
  assert.ok(createPostHistoryCache(String(MAX_SNAPSHOTS), () => store).load(snapshot().url));
  assert.equal(store.getItem('other-feature'), 'keep');
  assert.equal(store.length, MAX_SNAPSHOTS * 2 + 1);
});

test('enforces the total size budget', () => {
  const store = storage();
  createPostHistoryCache('one', () => store).saveContent(snapshot('a'.repeat(MAX_CHARACTERS * 0.6)));
  createPostHistoryCache('two', () => store).saveContent(snapshot('b'.repeat(MAX_CHARACTERS * 0.6)));
  assert.equal(createPostHistoryCache('one', () => store).load(snapshot().url), null);
  assert.ok(createPostHistoryCache('two', () => store).load(snapshot().url));
});

test('oversized or failed writes remove the old snapshot, including partial writes', () => {
  const store = storage();
  const cache = createPostHistoryCache('one', () => store);
  cache.saveContent(snapshot());
  cache.saveContent(snapshot('a'.repeat(MAX_CHARACTERS)));
  assert.equal(cache.load(snapshot().url), null);
  cache.saveContent(snapshot());
  const write = store.setItem;
  store.setItem = function (key, value) {
    if (key.endsWith(':state')) throw new Error('Quota exceeded');
    write.call(this, key, value);
  };
  cache.saveContent(snapshot('New rows'));
  assert.equal(store.length, 0);
  assert.equal(cache.load(snapshot().url), null);
});

test('expires stale snapshots and cleans old formats', () => {
  const store = storage();
  store.setItem('cp-posts-v2:old', 'obsolete');
  const cache = createPostHistoryCache('one', () => store);
  cache.saveContent(snapshot());
  const stateKey = `${HISTORY_PREFIX}one:state`;
  const state = JSON.parse(store.getItem(stateKey));
  store.setItem(stateKey, JSON.stringify({ ...state, updated: Date.now() - 31 * 60 * 1000 }));
  assert.equal(cache.load(snapshot().url), null);
  assert.equal(store.length, 0);
});

test('blocked storage does not interrupt navigation or loading', () => {
  const cache = createPostHistoryCache('one', () => { throw new Error('Blocked'); });
  assert.doesNotThrow(() => cache.saveContent(snapshot()));
  assert.doesNotThrow(() => cache.savePosition(500));
  assert.equal(cache.load(snapshot().url), null);
});
