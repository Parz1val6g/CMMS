import { useState, useRef } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import Tabs from '@/Components/Common/Tabs';
import FormSection from '@/Components/Common/FormSection';
import DialogModal from '@/Components/Common/DialogModal';
import { t } from '@/utils/i18n';
import { submitForm } from '@/utils/form';
import { useToast } from '@/Components/Toast/ToastContext';


/* ── Settings Page ──────────────────────────────────────────────── */
export default function Settings({ user, preferences, appSettings, isAdmin, routes: apiRoutes }) {

  const toast = useToast();

  /* Logo state */
  const [logoPreviewSrc, setLogoPreviewSrc] = useState(appSettings?.logo_path ? `/storage/${appSettings.logo_path}` : null);
  const [deleteLogo, setDeleteLogo] = useState(false);
  const logoInputRef = useRef(null);

  /* ── Form Handlers ──────────────────────────────────────────── */
  const handleDetailsSubmit = async (e) => {
    e.preventDefault();
    const r = await submitForm(e.target, apiRoutes.updateUser);
    if (r.ok) { toast.success(r.message); setTimeout(() => e.target.reset(), 1000); }
    else toast.error(r.message);
  };

  const handlePasswordSubmit = async (e) => {
    e.preventDefault();
    const r = await submitForm(e.target, apiRoutes.updatePassword);
    if (r.ok) { toast.success(r.message); setTimeout(() => e.target.reset(), 1000); }
    else toast.error(r.message);
  };

  const handleAdminSubmit = async (e) => {
    e.preventDefault();
    const r = await submitForm(e.target, apiRoutes.updateAdmin);
    if (r.ok) { toast.success(r.message); setTimeout(() => e.target.reset(), 1000); }
    else toast.error(r.message);
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
      toast.error(t('pages.settings.error_confirmation_mismatch'));
      return;
    }
    if (!confirmPassword) {
      toast.error(t('pages.settings.error_password_required'));
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
        toast.success(t('pages.settings.success_account_deleted'));
        setTimeout(() => { window.location.href = '/login?deleted=true'; }, 1500);
      } else {
        toast.error(data.error ?? t('pages.settings.error_generic'));
      }
    } catch {
      toast.error(t('pages.settings.error_try_again'));
    }
  };

  /* Supported locales */
  const locales = [
    { key: 'en', flag: '🇬🇧', name: 'English' },
    { key: 'pt', flag: '🇵🇹', name: 'Português' },
  ];

  const inputClass = 'block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm shadow-none focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200';

  const tabs = [
    {
      id: 'details', label: t('pages.settings.tab_details'),
      content: (
        <FormSection title={t('pages.settings.section_personal_info')} description={t('pages.settings.section_personal_desc')}>
          <form id="detailsForm" onSubmit={handleDetailsSubmit} className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />
            <div className="max-w-md space-y-4">
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_first_name')} <span className="text-red-500">*</span></label>
                <input type="text" name="first_name" defaultValue={user.first_name} className={inputClass} required />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_last_name')} <span className="text-red-500">*</span></label>
                <input type="text" name="last_name" defaultValue={user.last_name} className={inputClass} required />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_email')} <span className="text-red-500">*</span></label>
                <input type="email" name="email" defaultValue={user.email} className={inputClass} required />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_language')}</label>
                <select name="language" defaultValue={preferences?.language ?? 'en'} className={inputClass}>
                  {locales.map((l) => (
                    <option key={l.key} value={l.key}>{l.flag} {l.name}</option>
                  ))}
                </select>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_timezone')}</label>
                <select name="timezone" defaultValue={preferences?.timezone ?? 'UTC'} className={inputClass} disabled>
                  <option value="UTC">UTC</option>
                  <option value="Europe/Lisbon">Europe/Lisbon</option>
                  <option value="Europe/London">Europe/London</option>
                  <option value="Europe/Paris">Europe/Paris</option>
                </select>
                <p className="mt-1 text-xs text-gray-400">{t('pages.settings.hint_timezone_soon')}</p>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div className="pt-2 text-right">
                <button type="submit" className="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                  {t('pages.settings.btn_save')}
                </button>
              </div>
            </div>
          </form>
        </FormSection>
      ),
    },
    {
      id: 'password', label: t('pages.settings.tab_password'),
      content: (
        <FormSection title={t('pages.settings.section_change_password')} description={t('pages.settings.section_password_desc')}>
          <form id="passwordForm" onSubmit={handlePasswordSubmit} className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />
            <div className="max-w-md space-y-4">
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_current_password')} <span className="text-red-500">*</span></label>
                <input type="password" name="current_password" className={inputClass} required />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_new_password')} <span className="text-red-500">*</span></label>
                <input type="password" name="password" className={inputClass} required />
                <p className="mt-1 text-xs text-gray-400">{t('pages.settings.hint_password_length')}</p>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_new_password_confirm')} <span className="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" className={inputClass} required />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div className="flex items-center justify-end gap-2 pt-2">
                <button type="button" onClick={() => document.getElementById('passwordForm').reset()} className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                  {t('pages.settings.btn_cancel')}
                </button>
                <button type="submit" className="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                  {t('pages.settings.btn_save')}
                </button>
              </div>
            </div>
          </form>
        </FormSection>
      ),
    },
    ...(isAdmin ? [{
      id: 'admin', label: t('pages.settings.tab_admin'),
      content: (
        <FormSection title={t('pages.settings.section_app_settings')} description={t('pages.settings.section_app_desc')}>
          <form id="adminForm" onSubmit={handleAdminSubmit} className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />
            <input type="hidden" name="delete_logo" id="deleteLogo" value={deleteLogo ? '1' : '0'} />
            <div className="max-w-md space-y-4">
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_company_name')}</label>
                <input type="text" name="company_name" defaultValue={appSettings?.company_name ?? ''} className={inputClass} placeholder={t('pages.settings.placeholder_company_name')} />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_logo')}</label>
                <div className="flex flex-col gap-3">
                  <div className="relative inline-block" style={{ width: 'fit-content' }}>
                    <div id="logoPreview" className="flex h-24 w-24 items-center justify-center rounded-xl border-2 border-dashed bg-gray-100 dark:bg-gray-700">
                      {logoPreviewSrc ? (
                        <img src={logoPreviewSrc} alt={t('pages.settings.logo_alt')} style={{ maxWidth: '100%', maxHeight: '100%', objectFit: 'contain' }} />
                      ) : (
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" className="text-gray-400" viewBox="0 0 16 16">
                          <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                          <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z" />
                        </svg>
                      )}
                    </div>
                    {logoPreviewSrc && (
                      <button type="button" id="clearLogo" onClick={handleClearLogo} className="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white shadow-sm hover:bg-red-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708l2.647-2.646-2.647-2.646a.5.5 0 0 1 0-.708z" />
                        </svg>
                      </button>
                    )}
                  </div>
                  <input ref={logoInputRef} type="file" name="logo" id="logoInput" accept="image/*" onChange={handleLogoChange} className="block w-full text-sm text-gray-500 file:mr-3 file:rounded file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-400 dark:file:bg-indigo-900/50 dark:file:text-indigo-300" />
                  <p className="text-xs text-gray-400">{t('pages.settings.hint_logo_size')}</p>
                  <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                </div>
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_website')}</label>
                <input type="url" name="company_website" defaultValue={appSettings?.company_website ?? ''} className={inputClass} disabled />
                <p className="mt-1 text-xs text-gray-400">{t('pages.settings.hint_website_soon')}</p>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_support_email')}</label>
                <input type="email" name="support_email" defaultValue={appSettings?.support_email ?? ''} className={inputClass} disabled />
                <p className="mt-1 text-xs text-gray-400">{t('pages.settings.hint_email_soon')}</p>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_default_language')}</label>
                <select name="default_language" defaultValue={appSettings?.default_language ?? 'PT'} className={inputClass}>
                  <option value="PT">Português</option>
                  <option value="EN">English</option>
                </select>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_default_timezone')}</label>
                <select name="default_timezone" defaultValue={appSettings?.default_timezone ?? 'UTC'} className={inputClass} disabled>
                  <option value="UTC">UTC</option>
                  <option value="Europe/Lisbon">Europe/Lisbon</option>
                  <option value="Europe/London">Europe/London</option>
                  <option value="Europe/Paris">Europe/Paris</option>
                </select>
                <p className="mt-1 text-xs text-gray-400">{t('pages.settings.hint_timezone_soon')}</p>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_currency')}</label>
                <input type="text" name="currency" maxLength={3} placeholder={t('pages.settings.placeholder_currency')} defaultValue={appSettings?.currency ?? ''} className={inputClass} disabled />
                <p className="mt-1 text-xs text-gray-400">{t('pages.settings.hint_currency_soon')}</p>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div className="flex items-center gap-3">
                <input type="checkbox" name="csv_enabled" id="csvEnabled" value="1" defaultChecked={appSettings?.csv_enabled === true || appSettings?.csv_enabled === '1'} className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600" />
                <label htmlFor="csvEnabled" className="text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_csv_export')}</label>
              </div>
              <div className="flex items-center gap-3">
                <input type="checkbox" name="user_registration_enabled" id="regEnabled" value="1" defaultChecked={appSettings?.user_registration_enabled === true || appSettings?.user_registration_enabled === '1'} className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600" />
                <label htmlFor="regEnabled" className="text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_allow_registration')}</label>
              </div>
              <div className="pt-2 text-right">
                <button type="submit" className="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">{t('pages.settings.btn_save')}</button>
              </div>
            </div>
          </form>
        </FormSection>
      ),
    }] : []),
    {
      id: 'account', label: t('pages.settings.tab_account'),
      content: (
        <FormSection title={t('pages.settings.section_delete_account')} description={t('pages.settings.section_delete_desc')}>
          <div className="rounded-xl border border-red-200 bg-red-50 p-5 dark:border-red-800 dark:bg-red-900/20">
            <p className="mb-3 text-sm text-gray-700 dark:text-gray-300">
              {t('pages.settings.delete_instruction', { text: expectedText })}
            </p>
            <div className="max-w-md space-y-4">
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_confirmation_text')}</label>
                <input type="text" value={confirmText} onChange={(e) => setConfirmText(e.target.value)} className="block w-full rounded-lg border border-red-300 bg-white px-3 py-2 text-sm shadow-none focus:border-red-500 focus:ring-red-500 dark:border-red-700 dark:bg-gray-800 dark:text-gray-200" placeholder={t('pages.settings.placeholder_confirmation')} />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_confirm_password_account')}</label>
                <input type="password" value={confirmPassword} onChange={(e) => setConfirmPassword(e.target.value)} className="block w-full rounded-lg border border-red-300 bg-white px-3 py-2 text-sm shadow-none focus:border-red-500 focus:ring-red-500 dark:border-red-700 dark:bg-gray-800 dark:text-gray-200" />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div className="flex items-center justify-end gap-2 pt-2">
                <button type="button" onClick={() => { setConfirmText(''); setConfirmPassword(''); }} className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                  {t('pages.settings.btn_cancel')}
                </button>
                <button type="button" onClick={() => setDeleteModal(true)} className="rounded-lg bg-red-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition-colors">
                  {t('pages.settings.btn_delete_account')}
                </button>
              </div>
            </div>
          </div>
        </FormSection>
      ),
    },
  ];

  return (
    <AppLayout title={t('pages.settings.page_title')}>

      <div className="h-full overflow-y-auto w-full">
        <div className="max-w-7xl mx-auto py-8 px-6">
          {/* Page Header */}
          <div className="mb-4 shrink-0">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white">{t('pages.settings.page_heading')}</h2>
            <p className="text-xs text-gray-500 dark:text-gray-400">{t('pages.settings.page_subtitle')}</p>
          </div>

          <Tabs tabs={tabs} defaultTab="details" className="flex-1 overflow-y-auto pt-5" />
        </div>
      </div>

      <DialogModal
        open={deleteModal}
        onClose={() => setDeleteModal(false)}
        type="error"
        title={t('pages.settings.modal_confirm_title')}
        buttons={[
          { label: t('pages.settings.btn_cancel'), onClick: () => setDeleteModal(false), variant: 'secondary' },
          { label: t('pages.settings.btn_delete_account'), onClick: handleDeleteAccount, variant: 'primary' },
        ]}
      >
        <p className="text-sm font-semibold text-red-600 dark:text-red-400">{t('pages.settings.modal_confirm_warning')}</p>
        <p className="mb-3 text-sm text-gray-600 dark:text-gray-400">
          {t('pages.settings.modal_confirm_instruction', { text: expectedText })}
        </p>
        <input
          type="text"
          value={confirmText}
          onChange={(e) => setConfirmText(e.target.value)}
          className="block w-full rounded-lg border border-red-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500 dark:border-red-700 dark:bg-gray-700 dark:text-gray-200"
          placeholder={t('pages.settings.placeholder_confirmation')}
        />
        <label className="mt-2 block text-xs font-bold text-gray-500 dark:text-gray-400">{t('pages.settings.label_confirm_password_account')}</label>
        <input
          type="password"
          value={confirmPassword}
          onChange={(e) => setConfirmPassword(e.target.value)}
          className="block w-full rounded-lg border border-red-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500 dark:border-red-700 dark:bg-gray-700 dark:text-gray-200"
        />
      </DialogModal>
    </AppLayout>
  );
}
