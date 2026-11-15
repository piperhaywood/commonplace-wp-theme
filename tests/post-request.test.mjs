import test from 'node:test';
import assert from 'node:assert/strict';
import { fetchPostPage } from '../js/post-request.mjs';
const url = new URL('https://example.test/page/2/');
const aborted = (signal) => new Promise((resolve, reject) => {
  signal.addEventListener('abort', () => reject(new DOMException('Timed out', 'AbortError')), { once: true });
});

test('aborts a stalled connection and permits a later request', async () => {
  await assert.rejects(fetchPostPage(url, { timeout: 10, fetchImpl: (_, { signal }) => aborted(signal) }), { name: 'AbortError' });
  assert.equal(await fetchPostPage(url, { fetchImpl: async () => ({ ok: true, url: url.href, text: async () => 'New posts' }) }), 'New posts');
});

test('aborts a stalled response body after headers have arrived', async () => {
  await assert.rejects(fetchPostPage(url, {
    timeout: 10,
    fetchImpl: async (_, { signal }) => ({ ok: true, url: url.href, text: () => aborted(signal) }),
  }), { name: 'AbortError' });
});

test('clears the deadline after success', async () => {
  let signal;
  const result = await fetchPostPage(url, {
    timeout: 10,
    fetchImpl: async (_, options) => {
      signal = options.signal;
      return { ok: true, url: url.href, text: async () => 'New posts' };
    },
  });
  await new Promise((resolve) => setTimeout(resolve, 20));
  assert.equal(result, 'New posts');
  assert.equal(signal.aborted, false);
});

test('rejects HTTP failures and cross-origin redirects', async () => {
  for (const response of [{ ok: false, url: url.href }, { ok: true, url: 'https://other.test/' }]) {
    await assert.rejects(fetchPostPage(url, { fetchImpl: async () => response }), /Page unavailable/);
  }
});
