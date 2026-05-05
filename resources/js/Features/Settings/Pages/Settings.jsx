import { useState, useEffect, useRef } from 'react';
import AppLayout from '@/Layouts/AppLayout';

/* ── Toast Context ────────────────────────────────────────────── */
function Toast({ message, type = 'success', show, onClose }) {
  useEffect(() => {
    if (!show) return;
    const t = setTimeout(onClose, 4000);
    return () => clearTimeout(t);
  }, [show, onClose]);

  if (!show) return null;

  const bg = type === 'success' ? 'bg-green-600' : 'bg-red-600';

  return (
    <div className={`fixed right-4 top-4 z-50 flex items-center gap-3 rounded-lg ${bg} px-4 py-3 text-sm text-white shadow-lg transition-all`}>
      <span>{message}</span>
      <button type="button" className="shrink-0 text-white/70 hover:text-white" onClick={onClose}>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708l2.647-2.646-2.647-2.646a.5.5 0 0 1 0-.708z" />
        </svg>
      </button>
    </div>
  );
}

/* ── Single Tab Panel ─────────────────────────────────────────── */
function TabPanel({ id, active, children }) {
  if (!active) return null;
  return (
    <div id={id} role="tabpanel" aria-labelledby={`${id}-tab`} className="tab-pane fade show active">
      {children}
    </div>
  );
}

/* ── Form Section Layout ──────────────────────────────────────── */
function FormSection({ title, description, children }) {
  return (
    <div className="mb-5 grid gap-5 border-b border-gray-200 pb-5 dark:border-gray-700 md:grid-cols-3">
      <div className="md:col-span-1">
        <h6 className="mb-1 text-sm font-bold text-gray-900 dark:text-white">{title}</h6>
        <p className="text-xs text-gray-500 dark:text-gray-400">{description}</p>
      </div>
      <div className="md:col-span-2">{children}</div>
    </div>
  );
}

/* ── Helpers ──────────────────────────────────────────────────── */
async function submitForm(formEl, endpoint) {
  const submitBtn = formEl.querySelector('[type="submit"]');
  const orig = submitBtn?.textContent;
  if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving...'; }

  try {
    const fd = new FormData(formEl);
    const res = await fetch(endpoint, {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    const data = await res.json();

    // Clear previous errors
    formEl.querySelectorAll('.form-feedback').forEach((el) => {
      el.classList.remove('show', 'is-valid', 'is-invalid');
      el.textContent = '';
    });
    formEl.querySelectorAll('input, select').forEach((el) => el.classList.remove('is-invalid'));

    if (res.ok) {
      return { ok: true, message: data.message ?? 'Updated successfully' };
    }

    const errs = data.errors;
    if (errs) {
      Object.entries(errs).forEach(([field, msgs]) => {
        const input = formEl.querySelector(`[name="${field}"]`);
        if (input) {
          input.classList.add('is-invalid');
          const fb = input.parentElement.querySelector('.form-feedback');
          if (fb) { fb.classList.add('show', 'is-invalid'); fb.textContent = Array.isArray(msgs) ? msgs[0] : msgs; }
        }
      });
      return { ok: false, message: 'Please check the form for errors' };
    }
    return { ok: false, message: data.error ?? 'An error occurred' };
  } catch {
    return { ok: false, message: 'Please try again' };
  } finally {
    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = orig ?? 'Save'; }
  }
}

/* ── Settings Page ──────────────────────────────────────────────── */
export default function Settings({ user, preferences, appSettings, isAdmin, routes: apiRoutes }) {

  /* Tabs */
  const tabs = [
    { id: 'details', label: 'My Details', admin: false },
    { id: 'password', label: 'Password', admin: false },
    ...(isAdmin ? [{ id: 'admin', label: 'Admin Settings', admin: true }] : []),
    { id: 'account', label: 'Account', admin: false },
  ];
  const [activeTab, setActiveTab] = useState('details');

  /* Toast */
  const [toast, setToast] = useState({ show: false, message: '', type: 'success' });
  const showToast = (message, type = 'success') => setToast({ show: true, message, type });

  /* Logo state */
  const [logoPreviewSrc, setLogoPreviewSrc] = useState(appSettings?.logo_path ? `/storage/${appSettings.logo_path}` : null);
  const [deleteLogo, setDeleteLogo] = useState(false);
  const logoInputRef = useRef(null);

  /* ── Form Handlers ──────────────────────────────────────────── */
  const handleDetailsSubmit = async (e) => {
    e.preventDefault();
    const r = await submitForm(e.target, apiRoutes.updateUser);
    showToast(r.message, r.ok ? 'success' : 'error');
    if (r.ok) setTimeout(() => e.target.reset(), 1000);
  };

  const handlePasswordSubmit = async (e) => {
    e.preventDefault();
    const form = e.target;
    const submitBtn = form.querySelector('[type="submit"]');
    const orig = submitBtn?.textContent;
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving...'; }

    try {
      const fd = new FormData(form);
      const res = await fetch(apiRoutes.updatePassword, {
        method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await res.json();

      form.querySelectorAll('.form-feedback').forEach((el) => { el.classList.remove('show', 'is-valid', 'is-invalid'); el.textContent = ''; });
      form.querySelectorAll('input').forEach((el) => el.classList.remove('is-invalid'));

      if (res.ok) {
        showToast('Password changed successfully', 'success');
        setTimeout(() => form.reset(), 1000);
      } else {
        if (data.errors) {
          Object.entries(data.errors).forEach(([field, msgs]) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
              input.classList.add('is-invalid');
              const fb = input.parentElement.querySelector('.form-feedback');
              if (fb) { fb.classList.add('show', 'is-invalid'); fb.textContent = Array.isArray(msgs) ? msgs[0] : msgs; }
            }
          });
        }
        showToast(data.error ?? 'Please check the form for errors', 'error');
      }
    } catch {
      showToast('Please try again', 'error');
    } finally {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = orig ?? 'Save'; }
    }
  };

  const handleAdminSubmit = async (e) => {
    e.preventDefault();
    const r = await submitForm(e.target, apiRoutes.updateAdmin);
    showToast(r.message, r.ok ? 'success' : 'error');
    if (r.ok) setTimeout(() => e.target.reset(), 1000);
  };

  /* Logo handlers */
  const handleLogoChange = (e) => {
    setDeleteLogo(false);
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (ev) => setLogoPreviewSrc(ev.target.result);
      reader.readAsDataURL(file);
    }
  };

  const handleClearLogo = () => {
    setDeleteLogo(true);
    setLogoPreviewSrc(null);
    if (logoInputRef.current) logoInputRef.current.value = '';
  };

  /* Delete Account */
  const [deleteModal, setDeleteModal] = useState(false);
  const [confirmText, setConfirmText] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const expectedText = `DELETE ${(user.first_name ?? '').toUpperCase()}`;

  const handleDeleteAccount = async () => {
    if (confirmText !== expectedText) {
      showToast('Confirmation text does not match', 'error');
      return;
    }
    if (!confirmPassword) {
      showToast('Password is required', 'error');
      return;
    }

    try {
      const fd = new FormData();
      fd.append('confirmation_text', confirmText);
      fd.append('password', confirmPassword);
      fd.append('_token', document.querySelector('input[name="_token"]')?.value ?? '');

      const res = await fetch(apiRoutes.deleteAccount, {
        method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await res.json();

      if (res.ok) {
        showToast('Account deleted successfully', 'success');
        setTimeout(() => { window.location.href = '/login?deleted=true'; }, 1500);
      } else {
        showToast(data.error ?? 'An error occurred', 'error');
      }
    } catch {
      showToast('Please try again', 'error');
    }
  };

  /* Supported locales */
  const locales = [
    { key: 'en', flag: '🇬🇧', name: 'English' },
    { key: 'pt', flag: '🇵🇹', name: 'Português' },
  ];

  const inputClass = 'block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm shadow-none focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200';

  return (
    <AppLayout title="Settings">
      {/* Toast */}
      <Toast message={toast.message} type={toast.type} show={toast.show} onClose={() => setToast({ ...toast, show: false })} />

      <div className="h-full overflow-y-auto w-full">
        <div className="max-w-7xl mx-auto py-8 px-6">
          {/* Page Header */}
          <div className="mb-4 shrink-0">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white">Account Settings</h2>
            <p className="text-xs text-gray-500 dark:text-gray-400">Manage your personal information</p>
          </div>

          {/* Tabs */}
          <div className="shrink-0 border-b border-gray-200 dark:border-gray-700">
            <nav className="flex gap-6 overflow-x-auto" role="tablist" style={{ scrollbarWidth: 'none' }}>
              {tabs.map((tab) => (
                <button
                  key={tab.id}
                  id={`${tab.id}-tab`}
                  role="tab"
                  aria-selected={activeTab === tab.id}
                  aria-controls={`${tab.id}-pane`}
                  onClick={() => setActiveTab(tab.id)}
                  className={`shrink-0 border-b-2 bg-transparent px-0 pb-3 pt-2 text-sm font-medium transition-colors ${activeTab === tab.id
                      ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                      : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-200'
                    }`}
                >
                  {tab.label}
                </button>
              ))}
            </nav>
          </div>

          {/* Tab Content */}
          <div className="flex-1 overflow-y-auto pt-5">
            {/* Details Tab */}
            <TabPanel id="details-pane" label="My Details" active={activeTab === 'details'}>
              <FormSection title="Personal Information" description="Update your personal details">
                <form id="detailsForm" onSubmit={handleDetailsSubmit} className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                  <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />
                  <div className="max-w-md space-y-4">
                    <div>
                      <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">First Name <span className="text-red-500">*</span></label>
                      <input type="text" name="first_name" defaultValue={user.first_name} className={inputClass} required />
                      <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                    </div>
                    <div>
                      <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Last Name <span className="text-red-500">*</span></label>
                      <input type="text" name="last_name" defaultValue={user.last_name} className={inputClass} required />
                      <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                    </div>
                    <div>
                      <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Email Address <span className="text-red-500">*</span></label>
                      <input type="email" name="email" defaultValue={user.email} className={inputClass} required />
                      <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                    </div>
                    <div>
                      <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Language</label>
                      <select name="language" defaultValue={preferences?.language ?? 'en'} className={inputClass}>
                        {locales.map((l) => (
                          <option key={l.key} value={l.key}>{l.flag} {l.name}</option>
                        ))}
                      </select>
                      <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                    </div>
                    <div>
                      <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Timezone</label>
                      <select name="timezone" defaultValue={preferences?.timezone ?? 'UTC'} className={inputClass} disabled>
                        <option value="UTC">UTC</option>
                        <option value="Europe/Lisbon">Europe/Lisbon</option>
                        <option value="Europe/London">Europe/London</option>
                        <option value="Europe/Paris">Europe/Paris</option>
                      </select>
                      <p className="mt-1 text-xs text-gray-400">Timezone settings coming soon</p>
                      <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                    </div>
                    <div className="pt-2 text-right">
                      <button type="submit" className="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                        Save
                      </button>
                    </div>
                  </div>
                </form>
              </FormSection>
            </TabPanel>

            {/* Password Tab */}
            <TabPanel id="password-pane" label="Password" active={activeTab === 'password'}>
              <FormSection title="Change Password" description="Update your password">
                <form id="passwordForm" onSubmit={handlePasswordSubmit} className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                  <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />
                  <div className="max-w-md space-y-4">
                    <div>
                      <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Current Password <span className="text-red-500">*</span></label>
                      <input type="password" name="current_password" className={inputClass} required />
                      <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                    </div>
                    <div>
                      <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">New Password <span className="text-red-500">*</span></label>
                      <input type="password" name="password" className={inputClass} required />
                      <p className="mt-1 text-xs text-gray-400">Must be more than 8 characters</p>
                      <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                    </div>
                    <div>
                      <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Confirm New Password <span className="text-red-500">*</span></label>
                      <input type="password" name="password_confirmation" className={inputClass} required />
                      <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                    </div>
                    <div className="flex items-center justify-end gap-2 pt-2">
                      <button type="button" onClick={() => document.getElementById('passwordForm').reset()} className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Cancel
                      </button>
                      <button type="submit" className="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                        Save
                      </button>
                    </div>
                  </div>
                </form>
              </FormSection>
            </TabPanel>

            {/* Admin Tab */}
            {isAdmin && (
              <TabPanel id="admin-pane" label="Admin Settings" active={activeTab === 'admin'}>
                <FormSection title="Application Settings" description="Configure global settings">
                  <form id="adminForm" onSubmit={handleAdminSubmit} className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />
                    <input type="hidden" name="delete_logo" id="deleteLogo" value={deleteLogo ? '1' : '0'} />
                    <div className="max-w-md space-y-4">
                      {/* Company Name */}
                      <div>
                        <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Company Name</label>
                        <input type="text" name="company_name" defaultValue={appSettings?.company_name ?? ''} className={inputClass} placeholder="Enter company name" />
                        <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                      </div>

                      {/* Logo */}
                      <div>
                        <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Website Logo</label>
                        <div className="flex flex-col gap-3">
                          <div className="relative inline-block" style={{ width: 'fit-content' }}>
                            <div
                              id="logoPreview"
                              className="flex h-24 w-24 items-center justify-center rounded-xl border-2 border-dashed bg-gray-100 dark:bg-gray-700"
                            >
                              {logoPreviewSrc ? (
                                <img src={logoPreviewSrc} alt="Logo" style={{ maxWidth: '100%', maxHeight: '100%', objectFit: 'contain' }} />
                              ) : (
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" className="text-gray-400" viewBox="0 0 16 16">
                                  <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                                  <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z" />
                                </svg>
                              )}
                            </div>
                            {logoPreviewSrc && (
                              <button
                                type="button"
                                id="clearLogo"
                                onClick={handleClearLogo}
                                className="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white shadow-sm hover:bg-red-700 transition-colors"
                              >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708l2.647-2.646-2.647-2.646a.5.5 0 0 1 0-.708z" />
                                </svg>
                              </button>
                            )}
                          </div>
                          <input ref={logoInputRef} type="file" name="logo" id="logoInput" accept="image/*" onChange={handleLogoChange} className="block w-full text-sm text-gray-500 file:mr-3 file:rounded file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-400 dark:file:bg-indigo-900/50 dark:file:text-indigo-300" />
                          <p className="text-xs text-gray-400">Recommended size: 200x200px</p>
                          <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                        </div>
                      </div>

                      {/* Company Website */}
                      <div>
                        <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Company Website</label>
                        <input type="url" name="company_website" defaultValue={appSettings?.company_website ?? ''} className={inputClass} disabled />
                        <p className="mt-1 text-xs text-gray-400">Website settings coming soon</p>
                        <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                      </div>

                      {/* Support Email */}
                      <div>
                        <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Support Email</label>
                        <input type="email" name="support_email" defaultValue={appSettings?.support_email ?? ''} className={inputClass} disabled />
                        <p className="mt-1 text-xs text-gray-400">Email settings coming soon</p>
                        <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                      </div>

                      {/* Default Language */}
                      <div>
                        <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Default Language</label>
                        <select name="default_language" defaultValue={appSettings?.default_language ?? 'PT'} className={inputClass}>
                          <option value="PT">Português</option>
                          <option value="EN">English</option>
                        </select>
                        <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                      </div>

                      {/* Default Timezone */}
                      <div>
                        <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Default Timezone</label>
                        <select name="default_timezone" defaultValue={appSettings?.default_timezone ?? 'UTC'} className={inputClass} disabled>
                          <option value="UTC">UTC</option>
                          <option value="Europe/Lisbon">Europe/Lisbon</option>
                          <option value="Europe/London">Europe/London</option>
                          <option value="Europe/Paris">Europe/Paris</option>
                        </select>
                        <p className="mt-1 text-xs text-gray-400">Timezone settings coming soon</p>
                        <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                      </div>

                      {/* Currency */}
                      <div>
                        <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Currency</label>
                        <input type="text" name="currency" maxLength={3} placeholder="EUR" defaultValue={appSettings?.currency ?? ''} className={inputClass} disabled />
                        <p className="mt-1 text-xs text-gray-400">Currency settings coming soon</p>
                        <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                      </div>

                      {/* CSV Export Toggle */}
                      <div className="flex items-center gap-3">
                        <input
                          type="checkbox"
                          name="csv_enabled"
                          id="csvEnabled"
                          value="1"
                          defaultChecked={appSettings?.csv_enabled === true || appSettings?.csv_enabled === '1'}
                          className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600"
                        />
                        <label htmlFor="csvEnabled" className="text-xs font-bold text-gray-500 dark:text-gray-400">
                          Enable CSV Export
                        </label>
                      </div>

                      {/* Registration Toggle */}
                      <div className="flex items-center gap-3">
                        <input
                          type="checkbox"
                          name="user_registration_enabled"
                          id="regEnabled"
                          value="1"
                          defaultChecked={appSettings?.user_registration_enabled === true || appSettings?.user_registration_enabled === '1'}
                          className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600"
                        />
                        <label htmlFor="regEnabled" className="text-xs font-bold text-gray-500 dark:text-gray-400">
                          Allow User Registration
                        </label>
                      </div>

                      <div className="pt-2 text-right">
                        <button type="submit" className="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                          Save
                        </button>
                      </div>
                    </div>
                  </form>
                </FormSection>
              </TabPanel>
            )}

            {/* Account Tab */}
            <TabPanel id="account-pane" label="Account" active={activeTab === 'account'}>
              <FormSection title="Delete Account" description="This action cannot be undone">
                <div className="rounded-xl border border-red-200 bg-red-50 p-5 dark:border-red-800 dark:bg-red-900/20">
                  <p className="mb-3 text-sm text-gray-700 dark:text-gray-300">
                    Type <strong>"{expectedText}"</strong> to confirm deletion.
                  </p>
                  <div className="max-w-md space-y-4">
                    <div>
                      <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Confirmation Text</label>
                      <input
                        type="text"
                        value={confirmText}
                        onChange={(e) => setConfirmText(e.target.value)}
                        className="block w-full rounded-lg border border-red-300 bg-white px-3 py-2 text-sm shadow-none focus:border-red-500 focus:ring-red-500 dark:border-red-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Confirmation text"
                      />
                      <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                    </div>
                    <div>
                      <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">Confirm Your Password</label>
                      <input
                        type="password"
                        value={confirmPassword}
                        onChange={(e) => setConfirmPassword(e.target.value)}
                        className="block w-full rounded-lg border border-red-300 bg-white px-3 py-2 text-sm shadow-none focus:border-red-500 focus:ring-red-500 dark:border-red-700 dark:bg-gray-800 dark:text-gray-200"
                      />
                      <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                    </div>
                    <div className="flex items-center justify-end gap-2 pt-2">
                      <button type="button" onClick={() => { setConfirmText(''); setConfirmPassword(''); }} className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Cancel
                      </button>
                      <button type="button" onClick={() => setDeleteModal(true)} className="rounded-lg bg-red-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition-colors">
                        Delete Account
                      </button>
                    </div>
                  </div>
                </div>
              </FormSection>
            </TabPanel>
          </div>
        </div>
      </div>

      {/* Delete Account Confirmation Modal */}
      {deleteModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div className="absolute inset-0 bg-black/50 backdrop-blur-sm" onClick={() => setDeleteModal(false)} />
          <div className="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-800">
            <div className="border-b border-red-200 px-6 py-4 dark:border-red-800">
              <h5 className="text-lg font-semibold text-gray-900 dark:text-white">Are you absolutely sure?</h5>
            </div>
            <div className="space-y-4 px-6 py-4">
              <p className="text-sm font-semibold text-red-600 dark:text-red-400">This action cannot be undone!</p>
              <p className="text-sm text-gray-600 dark:text-gray-400">
                Type <strong>"{expectedText}"</strong> to confirm.
              </p>
              <input
                type="text"
                value={confirmText}
                onChange={(e) => setConfirmText(e.target.value)}
                className="block w-full rounded-lg border border-red-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500 dark:border-red-700 dark:bg-gray-700 dark:text-gray-200"
                placeholder="Confirmation text"
              />
              <label className="block text-xs font-bold text-gray-500 dark:text-gray-400">Confirm Your Password</label>
              <input
                type="password"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
                className="block w-full rounded-lg border border-red-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500 dark:border-red-700 dark:bg-gray-700 dark:text-gray-200"
              />
            </div>
            <div className="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
              <button type="button" onClick={() => setDeleteModal(false)} className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                Cancel
              </button>
              <button type="button" onClick={handleDeleteAccount} className="rounded-lg bg-red-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition-colors">
                Delete Account
              </button>
            </div>
          </div>
        </div>
      )}
    </AppLayout>
  );
}
