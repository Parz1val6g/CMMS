import { useState, useEffect, useRef, useCallback } from 'react';
import { router } from '@inertiajs/react';
import EmptyState from '@/Components/Common/EmptyState';
import FormField from '@/Components/Common/FormField';

/* ── Helpers ──────────────────────────────────────────────────── */
function buildQuery(params) {
  const s = new URLSearchParams(window.location.search);
  Object.entries(params).forEach(([k, v]) => {
    if (v === '' || v === null || v === undefined) s.delete(k);
    else s.set(k, v);
  });
  return s.toString();
}

function navigateWithQuery(params) {
  const qs = buildQuery(params);
  router.get(window.location.pathname + (qs ? `?${qs}` : ''), {}, { preserveState: true, replace: true });
}

/* ── Filter Bar ───────────────────────────────────────────────── */
function FilterBar({ routes, filterSchema = [], columns = [], onNew = () => {} }) {
  const params = new URLSearchParams(window.location.search);
  const searchVal = params.get('search') ?? '';
  const sortVal = params.get('sort') ?? 'desc';
  const [search, setSearch] = useState(searchVal);
  const [showAdvanced, setShowAdvanced] = useState(
    localStorage.getItem('advancedFiltersOpen') === 'true'
  );
  const debounceRef = useRef(null);

  /* Restore advanced state on mount */
  useEffect(() => {
    if (localStorage.getItem('advancedFiltersOpen') === 'true') {
      setShowAdvanced(true);
    }
  }, []);

  /* Debounced search */
  useEffect(() => {
    if (debounceRef.current) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => {
      if (search !== searchVal) {
        navigateWithQuery({ search, page: null });
      }
    }, 800);
    return () => { if (debounceRef.current) clearTimeout(debounceRef.current); };
  }, [search]);

  const hasActiveFilters =
    params.has('search') || params.has('date_from') || params.has('date_to');

  /* ── CSV Export ────────────────────────────────────────────── */
  const handleExport = useCallback(() => {
    const pathname = window.location.pathname;
    const parts = pathname.split('/').filter(Boolean);
    const model = parts[parts.length - 1];

    const url = new URL(window.location.href);
    url.pathname = `/api/${model}/export/csv`;

    fetch(url.toString(), {
      method: 'GET',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then((r) => {
        if (!r.ok) throw new Error('CSV export failed');
        return r.blob();
      })
      .then((blob) => {
        const blobUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = blobUrl;
        a.download = `export_${model}_${Date.now()}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(blobUrl);
        document.body.removeChild(a);
      })
      .catch(() => alert('Failed to export CSV'));
  }, []);

  return (
    <form
      id="filter-form"
      className="mb-3 shrink-0 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
      onSubmit={(e) => { e.preventDefault(); navigateWithQuery({ search, page: null }); }}
    >
      {/* Main Filter Row */}
      <div className="flex flex-wrap items-center gap-2">
        {/* Search */}
        <div className="relative min-w-0 flex-1" style={{ maxWidth: 300 }}>
          <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg className="h-3.5 w-3.5 text-gray-400" fill="currentColor" viewBox="0 0 16 16">
              <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
            </svg>
          </div>
          <input
            type="text"
            name="search"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); navigateWithQuery({ search, page: null }); } }}
            className="block w-full rounded-lg border border-gray-200 bg-transparent py-2 pl-9 pr-3 text-sm shadow-none placeholder:text-gray-400 focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:text-gray-200"
            placeholder="Search..."
            aria-label="Search"
          />
        </div>

        {/* Sort */}
        <select
          name="sort"
          value={sortVal}
          onChange={(e) => navigateWithQuery({ sort: e.target.value, page: null })}
          className="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 shadow-none focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
          style={{ maxWidth: 140 }}
          aria-label="Sort by"
        >
          <option value="desc">Newest</option>
          <option value="asc">Oldest</option>
        </select>

        {/* Advanced Toggle */}
        <button
          type="button"
          onClick={() => {
            const next = !showAdvanced;
            setShowAdvanced(next);
            if (next) localStorage.setItem('advancedFiltersOpen', 'true');
            else localStorage.removeItem('advancedFiltersOpen');
          }}
          className={`inline-flex items-center gap-1 rounded-lg border px-3 py-2 text-sm transition-all ${showAdvanced
              ? 'border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'
              : 'border-gray-200 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
            }`}
          title="Toggle advanced filters"
        >
          <svg className="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 16 16">
            <path d="M1 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3zm5-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V2z" />
          </svg>
          Advanced
        </button>

        {/* Right Side */}
        <div className="ml-auto flex flex-wrap items-center gap-2">
          {/* CSV Export */}
          <button
            type="button"
            onClick={handleExport}
            className="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-500 hover:bg-gray-50 transition-colors dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
            title="Export table to CSV"
          >
            <svg className="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 16 16">
              <path fillRule="evenodd" d="M14.5 1.5a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1h1.793L9.146 1.354a.5.5 0 0 1 .708-.708L14 4.793V2a.5.5 0 0 1 .5-.5zM1.5 14.5a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1H2.707l4.147 4.146a.5.5 0 0 1-.708.708L2 11.207V14a.5.5 0 0 1-.5.5z" />
            </svg>
            Export CSV
          </button>

          {/* New Record */}
          {routes.store && (
            <button
              type="button"
              className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
              data-bs-toggle={onNew ? undefined : 'modal'}
              data-bs-target={onNew ? undefined : '#createRecordModal'}
              title="Create new record"
              onClick={onNew}
            >
              <svg className="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
              </svg>
              New
            </button>
          )}

          {/* Clear Filters */}
          {hasActiveFilters && (
            <a
              href={routes.index ?? '#'}
              className="inline-flex items-center gap-1 text-sm text-gray-400 no-underline hover:text-gray-600 dark:hover:text-gray-300"
              title="Clear all filters"
            >
              <svg className="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 16 16">
                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708l2.647-2.646-2.647-2.646a.5.5 0 0 1 0-.708z" />
              </svg>
              Clear
            </a>
          )}
        </div>
      </div>

      {/* Active Filters Display */}
      {hasActiveFilters && (
        <div className="mt-2 flex flex-wrap items-center gap-2">
          {params.get('search') && (
            <span className="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
              🔍 Search: <strong>{params.get('search')}</strong>
            </span>
          )}
          {(params.get('date_from') || params.get('date_to')) && (
            <span className="inline-flex items-center gap-1 rounded bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">
              📅 Date: <strong>{params.get('date_from') ?? '...'} → {params.get('date_to') ?? '...'}</strong>
            </span>
          )}
        </div>
      )}

      {/* Advanced Filters */}
      {showAdvanced && (filterSchema ?? []).length > 0 && (
        <div className="mt-3 border-t border-gray-100 pt-3 dark:border-gray-700">
          <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
            {(filterSchema ?? []).map((filter, i) => (
              <div key={i}>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">
                  {filter.label}
                </label>
                {filter.type === 'text' ? (
                  <input
                    type="text"
                    name={filter.name ?? filter.key}
                    defaultValue={params.get(filter.name ?? filter.key) ?? ''}
                    placeholder={filter.placeholder ?? ''}
                    onChange={(e) => navigateWithQuery({ [filter.name ?? filter.key]: e.target.value, page: null })}
                    className="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm shadow-none focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                  />
                ) : (
                  <select
                    name={filter.name ?? filter.key}
                    defaultValue={params.get(filter.name ?? filter.key) ?? ''}
                    onChange={(e) => navigateWithQuery({ [filter.name ?? filter.key]: e.target.value, page: null })}
                    className="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm shadow-none focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                  >
                    <option value="">All</option>
                    {(filter.options ?? []).map((opt, j) => (
                      <option key={j} value={opt.value}>{opt.label}</option>
                    ))}
                  </select>
                )}
              </div>
            ))}

            {/* Date From */}
            <div>
              <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">From Date</label>
              <input
                type="date"
                name="date_from"
                defaultValue={params.get('date_from') ?? ''}
                onChange={(e) => navigateWithQuery({ date_from: e.target.value, page: null })}
                className="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm shadow-none focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                aria-label="Filter by start date"
              />
            </div>

            {/* Date To */}
            <div>
              <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">To Date</label>
              <input
                type="date"
                name="date_to"
                defaultValue={params.get('date_to') ?? ''}
                onChange={(e) => navigateWithQuery({ date_to: e.target.value, page: null })}
                className="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm shadow-none focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                aria-label="Filter by end date"
              />
            </div>

            {/* Per Page */}
            <div>
              <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Per Page</label>
              <select
                name="per_page"
                defaultValue={params.get('per_page') ?? '10'}
                onChange={(e) => navigateWithQuery({ per_page: e.target.value, page: null })}
                className="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm shadow-none focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
              >
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
            </div>

            {/* Apply Button */}
            <div className="flex items-end">
              <button
                type="submit"
                className="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
              >
                Apply
              </button>
            </div>
          </div>
        </div>
      )}
    </form>
  );
}

/* ── Edit Panel ───────────────────────────────────────────────── */
function EditPanel({ title, formSchema, routes, selectedItem, onClose }) {
  const [formData, setFormData] = useState({});
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (selectedItem) {
      const data = {};
      formSchema.forEach((f) => {
        const fieldName = f.name ?? f.key;
        data[fieldName] = selectedItem[fieldName] ?? '';
      });
      setFormData(data);
      setErrors({});
    }
  }, [selectedItem, formSchema]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!routes.update || !selectedItem) return;
    setSaving(true);

    /* Read current values from the DOM instead of stale formData */
    const form = e.target;
    const data = {};
    formSchema.forEach((f) => {
      const fieldName = f.name ?? f.key;
      const input = form.elements[fieldName];
      if (input) data[fieldName] = input.value;
    });

    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    try {
      const url = routes.update.replace(':id', selectedItem.id);
      const res = await fetch(url, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(data),
      });
      const data = await res.json();

      if (res.ok) {
        onClose();
        navigateWithQuery({});
      } else {
        if (data.errors) setErrors(data.errors);
        else alert(data.error ?? 'Failed to update');
      }
    } catch {
      alert('An error occurred');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    if (!routes.destroy || !selectedItem) return;
    if (!confirm('Are you sure you want to delete this item?')) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
      const url = routes.destroy.replace(':id', selectedItem.id);
      const res = await fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': token ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (res.ok) {
        onClose();
        navigateWithQuery({});
      } else {
        alert('Failed to delete item');
      }
    } catch {
      alert('Error deleting item');
    }
  };

  if (!selectedItem) return null;

  return (
    <div
      id="sm-form-panel"
      className="flex w-96 shrink-0 flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
    >
      {/* Header */}
      <div className="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-700">
        <h6 className="text-sm font-bold text-gray-900 dark:text-white">
          Edit {title?.replace(/s$/, '')}
        </h6>
        <button
          type="button"
          className="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors dark:hover:bg-gray-700 dark:hover:text-gray-300"
          onClick={onClose}
          aria-label="Close"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708l2.647-2.646-2.647-2.646a.5.5 0 0 1 0-.708z" />
          </svg>
        </button>
      </div>

      {/* Form */}
      <div className="flex flex-1 flex-col overflow-y-auto p-4">
        {/* Error display */}
        {Object.keys(errors).length > 0 && (
          <div className="mb-3 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
            {Object.entries(errors).map(([field, msgs]) => (
              <p key={field}>{(Array.isArray(msgs) ? msgs : [msgs]).join(', ')}</p>
            ))}
          </div>
        )}

        <form id="sm-edit-form" onSubmit={handleSubmit} className="flex flex-1 flex-col" encType="multipart/form-data" noValidate>
          <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />

          <div className="flex-1">
            {formSchema.map((field, i) => {
              const fieldName = field.name ?? field.key;
              return (
                <div key={i} className="mb-4">
                  <FormField
                    field={field}
                    value={formData[fieldName]}
                  />
                </div>
              );
            })}
          </div>

          <div className="mt-auto flex flex-col gap-2 pt-4">
            <button
              type="submit"
              disabled={saving}
              className="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 transition-colors"
            >
              {saving ? 'Saving...' : 'Save Changes'}
            </button>
            <button
              type="button"
              onClick={onClose}
              className="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
            >
              Cancel
            </button>
            {routes.destroy && (
              <button
                type="button"
                onClick={handleDelete}
                className="inline-flex items-center justify-center rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/30"
              >
                Remove
              </button>
            )}
          </div>
        </form>
      </div>
    </div>
  );
}

/* ── DataManager (Main) ───────────────────────────────────────── */
export default function DataManager({ title, items, columns = [], formSchema = [], routes = {}, filterSchema = [], onNew = null }) {
  const [editItem, setEditItem] = useState(null);
  const hasEdit = !!routes.update;
  const dataToMap = Array.isArray(items) ? items : (items?.data ?? []);

  /* Delete via delegation on table */
  const handleDelete = useCallback(async (id) => {
    if (!routes.destroy) return;
    if (!confirm('Are you sure?')) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
      const url = routes.destroy.replace(':id', id);
      const res = await fetch(url, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': token ?? '', 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (res.ok) {
        setEditItem(null);
        navigateWithQuery({});
      } else {
        alert('Failed to delete item');
      }
    } catch {
      alert('Error deleting item');
    }
  }, [routes.destroy]);

  return (
    <>
      {/* Filter Bar */}
      {routes.index && (
        <FilterBar routes={routes} filterSchema={filterSchema} columns={columns} onNew={onNew} />
      )}

      {/* Table + Edit Panel */}
      <div className="flex flex-1 gap-3 overflow-hidden">
        {/* Table Panel */}
        <div id="sm-table-panel" className="flex min-w-0 flex-1 flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
          {(!dataToMap || dataToMap.length === 0) ? (
            <EmptyState title="No records found" description="Try adjusting your search or filters" />
          ) : (
            <>
              <div className="overflow-x-auto">
                <table className="min-w-full table-auto text-sm">
                  <thead className="border-b border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/80">
                    <tr>
                      {columns.map((col, i) => (
                        <th key={i} className="whitespace-nowrap px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400" scope="col">
                          {col.label ?? ''}
                        </th>
                      ))}
                      {hasEdit && (
                        <th className="whitespace-nowrap px-4 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400" scope="col">
                          Actions
                        </th>
                      )}
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-50 dark:divide-gray-800">
                    {dataToMap.map((item) => (
                      <tr key={item.id} className="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                        {columns.map((col, i) => (
                          <td key={i} className="whitespace-nowrap px-4 py-2 text-gray-700 dark:text-gray-300">
                            {col.render ? col.render(item) : resolveValue(item, col.key)}
                          </td>
                        ))}
                        {hasEdit && (
                          <td className="whitespace-nowrap px-4 py-2 text-right">
                            <div className="inline-flex items-center gap-1">
                              <button
                                type="button"
                                className="rounded-lg px-2.5 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50 transition-colors dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                onClick={() => setEditItem(item)}
                                title="Edit"
                              >
                                Edit
                              </button>
                              {routes.destroy && (
                                <button
                                  type="button"
                                  className="rounded-lg px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors dark:text-red-400 dark:hover:bg-red-900/30"
                                  onClick={() => handleDelete(item.id)}
                                  title="Delete"
                                >
                                  Delete
                                </button>
                              )}
                            </div>
                          </td>
                        )}
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              {/* Pagination */}
              {items.links && (
                <div className="mt-auto border-t border-gray-100 p-3 dark:border-gray-700">
                  <Pagination links={items.links} />
                </div>
              )}
            </>
          )}
        </div>

        {/* Edit Panel */}
        {hasEdit && editItem && (
          <EditPanel
            title={title}
            formSchema={formSchema}
            routes={routes}
            selectedItem={editItem}
            onClose={() => setEditItem(null)}
          />
        )}
      </div>
    </>
  );
}

/* ── Helpers ──────────────────────────────────────────────────── */
function resolveValue(item, key) {
  if (!key || !item) return '';
  const parts = key.split('.');
  let val = item;
  for (const p of parts) {
    if (val === null || val === undefined) return '';
    val = val[p];
  }
  return val ?? '';
}

/* ── Pagination ───────────────────────────────────────────────── */
function Pagination({ links }) {
  if (!links || links.length <= 3) return null;

  return (
    <nav className="flex items-center justify-between">
      <span className="text-xs text-gray-500 dark:text-gray-400">
        Page {links.find((l) => l.active)?.label ?? '?'}
      </span>
      <div className="flex items-center gap-1">
        {links.map((link, i) => {
          if (link.label === '...') {
            return <span key={i} className="px-2 text-xs text-gray-400">...</span>;
          }
          const label = link.label.includes('Previous')
            ? '‹'
            : link.label.includes('Next')
              ? '›'
              : link.label;

          return (
            <button
              key={i}
              type="button"
              disabled={!link.url}
              onClick={() => {
                if (link.url) {
                  const url = new URL(link.url);
                  router.get(url.pathname + url.search, {}, { preserveState: true, replace: true });
                }
              }}
              className={`inline-flex h-7 w-7 items-center justify-center rounded-lg text-xs font-medium transition-colors ${link.active
                  ? 'bg-indigo-600 text-white'
                  : link.url
                    ? 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'
                    : 'cursor-not-allowed text-gray-300 dark:text-gray-600'
                }`}
            >
              {label}
            </button>
          );
        })}
      </div>
    </nav>
  );
}
