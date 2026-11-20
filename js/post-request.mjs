// Include the response body in the deadline, not just receipt of the headers.
export async function fetchPostPage(url, { timeout = 15_000, fetchImpl = fetch } = {}) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), timeout);
  try {
    const response = await fetchImpl(url, { signal: controller.signal });
    if (!response.ok || new URL(response.url).origin !== url.origin) {
      throw new Error('Page unavailable');
    }
    return await response.text();
  } finally {
    clearTimeout(timer);
  }
}
