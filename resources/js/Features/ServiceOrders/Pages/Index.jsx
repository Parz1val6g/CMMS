import { useState, useEffect, useCallback, useRef } from 'react';
import { usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/Common/DataManager';
import Modal from '@/Components/Common/Modal';

export default function ServiceOrdersIndex({ service_orders, columns, formSchema, createFormSchema, routes, filterSchema }) {
  const [showModal, setShowModal] = useState(false);
  const [saving, setSaving] = useState(false);
  const [formErrors, setFormErrors] = useState({});
  const [photoPreview, setPhotoPreview] = useState(null);
  const savingRef = useRef(false);
  const { flash } = usePage().props;

  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
    { name: 'Service Orders', url: '/service-orders' },
  ];

  /* ── Flash message auto-dismiss ───────────────────────────── */
  const [toast, setToast] = useState(null);
  useEffect(() => {
    if (flash?.success || flash?.error) {
      setToast(flash);
      const t = setTimeout(() => setToast(null), 4000);
      return () => clearTimeout(t);
    }
  }, [flash]);

  /* ── Create form submission via FormData (multipart) ─────── */
  const handleCreate = useCallback(async (e) => {
    e.preventDefault();
    if (!routes.store || savingRef.current) return;
    savingRef.current = true;
    setSaving(true);
    setFormErrors({});

    const form = e.target;
    const formData = new FormData(form);

    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    try {
      const res = await fetch(routes.store, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
      });
      const body = await res.json();

      if (res.ok) {
        setShowModal(false);
        setPhotoPreview(null);
        window.location.reload();
      } else {
        if (body.errors) setFormErrors(body.errors);
        else alert(body.error ?? 'Failed to create');
      }
    } catch {
      alert('An error occurred');
    } finally {
      savingRef.current = false;
      setSaving(false);
    }
  }, [routes.store]);

  /* ── Open modal for create ────────────────────────────────── */
  const openCreate = useCallback(() => {
    setFormErrors({});
    setPhotoPreview(null);
    setShowModal(true);
  }, []);

  /* ── Handle file input change for preview ─────────────────── */
  const onFileChange = useCallback((e) => {
    const file = e.target.files?.[0];
    if (file) {
      setPhotoPreview(URL.createObjectURL(file));
    } else {
      setPhotoPreview(null);
    }
  }, []);

  /* ── Render validation errors ─────────────────────────────── */
  const errorList = Object.values(formErrors).flat();

  return (
    <AppLayout title="Service Orders Management" breadcrumbs={breadcrumbs}>
      {/* Flash Toast */}
      {toast && (
        <div
          className={`mb-4 rounded-lg px-4 py-3 text-sm shadow-sm ${
            toast.success
              ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300'
              : 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300'
          }`}
        >
          {toast.success ?? toast.error}
          <button
            type="button"
            className="ml-3 font-medium underline"
            onClick={() => setToast(null)}
          >
            Dismiss
          </button>
        </div>
      )}

      <Modal
        formSchema={createFormSchema}
        routes={routes}
        size="lg"
        open={showModal}
        onClose={() => setShowModal(false)}
        onSubmit={handleCreate}
      >
        {/* Validation errors */}
        {errorList.length > 0 && (
          <div className="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
            {errorList.map((msg, i) => (
              <p key={i}>{msg}</p>
            ))}
          </div>
        )}

        {/* Photo preview */}
        {photoPreview && (
          <div className="mb-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <img
              src={photoPreview}
              alt="Photo preview"
              className="h-40 w-full object-cover"
            />
          </div>
        )}
      </Modal>

      {/* Hidden file change handler — wire via DOM delegation */}
      {showModal && (
        <PhotoListener onFileChange={onFileChange} />
      )}

      <DataManager
        title="Service Orders"
        items={service_orders}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
        onNew={openCreate}
      />
    </AppLayout>
  );
}

/* ── Photo file listener (renders outside Modal to avoid re-render issues) ── */
function PhotoListener({ onFileChange }) {
  useEffect(() => {
    const el = document.querySelector('input[name="photo"]');
    if (!el) return;
    el.addEventListener('change', onFileChange);
    return () => el.removeEventListener('change', onFileChange);
  }, [onFileChange]);
  return null;
}
