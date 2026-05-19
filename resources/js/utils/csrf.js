export function csrfHeader() {
  const xsrfCookie = document.cookie
    .split('; ')
    .find((row) => row.startsWith('XSRF-TOKEN='));

  if (xsrfCookie) {
    const raw = decodeURIComponent(xsrfCookie.split('=')[1]);
    return { 'X-XSRF-TOKEN': raw };
  }

  const meta = document.querySelector('meta[name="csrf-token"]')?.content;
  return meta ? { 'X-CSRF-TOKEN': meta } : {};
}
