/**
 * Triggers a CSV download from the backend.
 * Derives the model name from the current URL path if not provided.
 */
export async function exportCSV(model) {
  if (!model) {
    const parts = window.location.pathname.split('/').filter(Boolean);
    model = parts[parts.length - 1];
  }

  const url = new URL(window.location.href);
  url.pathname = `/api/exports/${model}`;

  try {
    const res = await fetch(url.toString(), {
      credentials: 'include',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!res.ok) throw new Error('Export failed');
    const blob = await res.blob();
    const blobUrl = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = blobUrl;
    a.download = `export_${model}_${Date.now()}.csv`;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(blobUrl);
    document.body.removeChild(a);
  } catch {
    throw new Error('Export failed');
  }
}
