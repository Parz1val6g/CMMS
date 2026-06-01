import { useState, useRef, useMemo } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import Tabs from '@/Components/Common/Tabs';
import FormSection from '@/Components/Common/FormSection';
import DialogModal from '@/Components/Common/DialogModal';
import Badge from '@/Components/Common/Badge';
import SearchableSelect from '@/Components/Common/SearchableSelect';
import { t } from '@/utils/i18n';
import { submitForm } from '@/utils/form';
import { useToast } from '@/Components/Toast/ToastContext';

const ROLE_VARIANT = {
  admin:             'danger',
  manager:           'primary',
  equipment_manager: 'info',
  supervisor:        'warning',
  worker:            'secondary',
  client:            'success',
  entidade:          'info',
  task_manager:      'primary',
  mini_task_manager: 'secondary',
  work_log_manager:  'secondary',
  sector_manager:    'warning',
  ticket_manager:    'info',
  team_manager:      'primary',
  attendant:         'success',
};

function UserAvatar({ name }) {
  const initials = (name ?? '?')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((w) => w[0].toUpperCase())
    .join('');
  return (
    <div className="flex h-20 w-20 items-center justify-center rounded-full bg-brand-accent text-white text-xl font-bold shadow-md ring-4 ring-white">
      {initials}
    </div>
  );
}

function StatCard({ label, value, icon }) {
  return (
    <div className="flex flex-col gap-1 rounded-xl border border-gray-100 bg-gray-50 p-4">
      <div className="flex items-center gap-2 text-gray-400">
        <span className="text-base">{icon}</span>
        <span className="text-[10px] font-semibold uppercase tracking-wide">{label}</span>
      </div>
      <span className="text-lg font-bold text-gray-800">{value ?? '—'}</span>
    </div>
  );
}

function formatDate(iso) {
  if (!iso) return null;
  return new Date(iso).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
}


/* ── Settings Page ──────────────────────────────────────────────── */
export default function Settings({ user, preferences, appSettings, isAdmin, locationOptions, routes: apiRoutes }) {

  const toast = useToast();

  /* Company location state */
  const [companyDistrictId, setCompanyDistrictId]       = useState(appSettings?.company_district_id ?? '');
  const [companyMunicipalityId, setCompanyMunicipalityId] = useState(appSettings?.company_municipality_id ?? '');
  const filteredMunicipalities = useMemo(
    () => (locationOptions?.municipalities ?? []).filter(m => !companyDistrictId || m.district_id === companyDistrictId),
    [locationOptions?.municipalities, companyDistrictId]
  );

  /* Logo state */
  const [logoPreviewSrc, setLogoPreviewSrc] = useState(appSettings?.logo_path ? `/storage/${appSettings.logo_path}` : null);
  const [deleteLogo, setDeleteLogo] = useState(false);
  const logoInputRef = useRef(null);

  /* ── Form Handlers ──────────────────────────────────────────── */
  const handleDetailsSubmit = async (e) => {
    e.preventDefault();

    const langBefore = preferences?.language ?? (window.__LOCALE__ === 'en' ? 'en' : 'pt');
    const langAfter = e.target.language?.value;
    const r = await submitForm(e.target, apiRoutes.updateUser);
    if (r.ok) {
      if (langAfter && langAfter !== langBefore) {
        window.__LOCALE__ = langAfter === 'en' ? 'en' : 'pt_PT';
        toast.success(t('pages.settings.success_updated'));
        setTimeout(() => window.location.reload(), 500);
      } else {
        toast.success(t('pages.settings.success_updated'));
      }
    } else toast.error(r.message);
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

  const inputClass = 'block w-full rounded-lg border border-brand-mid/20 bg-brand-light px-3 py-2 text-sm shadow-none focus:border-brand-accent focus:ring-brand-accent';

  const tabs = [
    {
      id: 'details', label: t('pages.settings.tab_details'),
      content: (
        <FormSection title={t('pages.settings.section_personal_info')} description={t('pages.settings.section_personal_desc')}>
          <form id="detailsForm" onSubmit={handleDetailsSubmit} className="rounded-xl border border-brand-mid/20 bg-brand-white p-5 shadow-sm">
            <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />
            <div className="max-w-md space-y-4">
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_first_name')} <span className="text-red-500">*</span></label>
                <input type="text" name="first_name" defaultValue={user.first_name} className={inputClass} required />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_last_name')} <span className="text-red-500">*</span></label>
                <input type="text" name="last_name" defaultValue={user.last_name} className={inputClass} required />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_email')} <span className="text-red-500">*</span></label>
                <input type="email" name="email" defaultValue={user.email} className={inputClass} required />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_language')}</label>
                <select name="language" defaultValue={preferences?.language ?? 'en'} className={inputClass}>
                  {locales.map((l) => (
                    <option key={l.key} value={l.key}>{l.flag} {l.name}</option>
                  ))}
                </select>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_timezone')}</label>
                <select name="timezone" defaultValue={preferences?.timezone ?? 'UTC'} className={inputClass} disabled>
                  <option value="UTC">UTC</option>
                  <option value="Europe/Lisbon">Europe/Lisbon</option>
                  <option value="Europe/London">Europe/London</option>
                  <option value="Europe/Paris">Europe/Paris</option>
                </select>
                <p className="mt-1 text-xs text-brand-mid">{t('pages.settings.hint_timezone_soon')}</p>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div className="pt-2 text-right">
                <button type="submit" className="rounded-lg bg-brand-accent px-5 py-2 text-sm font-medium text-brand-white shadow-sm hover:bg-brand-accent/90 transition-colors">
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
          <form id="passwordForm" onSubmit={handlePasswordSubmit} className="rounded-xl border border-brand-mid/20 bg-brand-white p-5 shadow-sm">
            <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />
            <div className="max-w-md space-y-4">
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_current_password')} <span className="text-red-500">*</span></label>
                <input type="password" name="current_password" className={inputClass} required />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_new_password')} <span className="text-red-500">*</span></label>
                <input type="password" name="password" className={inputClass} required />
                <p className="mt-1 text-xs text-brand-mid">{t('pages.settings.hint_password_length')}</p>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_new_password_confirm')} <span className="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" className={inputClass} required />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div className="flex items-center justify-end gap-2 pt-2">
                <button type="button" onClick={() => document.getElementById('passwordForm').reset()} className="rounded-lg border border-brand-mid/20 bg-brand-white px-4 py-2 text-sm font-medium text-brand-mid hover:bg-brand-light transition-colors">
                  {t('pages.settings.btn_cancel')}
                </button>
                <button type="submit" className="rounded-lg bg-brand-accent px-5 py-2 text-sm font-medium text-brand-white shadow-sm hover:bg-brand-accent/90 transition-colors">
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
          <form id="adminForm" onSubmit={handleAdminSubmit} className="rounded-xl border border-brand-mid/20 bg-brand-white p-5 shadow-sm">
            <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />
            <input type="hidden" name="delete_logo" id="deleteLogo" value={deleteLogo ? '1' : '0'} />
            <div className="max-w-md space-y-4">
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_company_name')}</label>
                <input type="text" name="company_name" defaultValue={appSettings?.company_name ?? ''} className={inputClass} placeholder={t('pages.settings.placeholder_company_name')} />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_company_location')}</label>
                <p className="mb-2 text-xs text-brand-mid">{t('pages.settings.hint_company_location')}</p>
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="mb-1 block text-xs font-medium text-brand-mid">{t('pages.cascading_parish.district')}</label>
                    <SearchableSelect
                      name="company_district_id"
                      options={locationOptions?.districts ?? []}
                      value={companyDistrictId}
                      onChange={val => { setCompanyDistrictId(val); setCompanyMunicipalityId(''); }}
                      placeholder="—"
                    />
                  </div>
                  <div>
                    <label className="mb-1 block text-xs font-medium text-brand-mid">{t('pages.cascading_parish.municipality')}</label>
                    <SearchableSelect
                      name="company_municipality_id"
                      options={filteredMunicipalities}
                      value={companyMunicipalityId}
                      onChange={setCompanyMunicipalityId}
                      placeholder="—"
                      disabled={!companyDistrictId}
                    />
                  </div>
                </div>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_logo')}</label>
                <div className="flex flex-col gap-3">
                  <div className="relative inline-block" style={{ width: 'fit-content' }}>
                    <div id="logoPreview" className="flex h-24 w-24 items-center justify-center rounded-xl border-2 border-dashed bg-brand-light">
                      {logoPreviewSrc ? (
                        <img src={logoPreviewSrc} alt={t('pages.settings.logo_alt')} style={{ maxWidth: '100%', maxHeight: '100%', objectFit: 'contain' }} />
                      ) : (
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" className="text-brand-mid" viewBox="0 0 16 16">
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
                  <input ref={logoInputRef} type="file" name="logo" id="logoInput" accept="image/*" onChange={handleLogoChange} className="block w-full text-sm text-brand-mid file:mr-3 file:rounded file:border-0 file:bg-brand-accent/10 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-brand-accent hover:file:bg-brand-accent/20" />
                  <p className="text-xs text-brand-mid">{t('pages.settings.hint_logo_size')}</p>
                  <div className="form-feedback mt-1 hidden text-xs text-red-600" />
                </div>
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_website')}</label>
                <input type="url" name="company_website" defaultValue={appSettings?.company_website ?? ''} className={inputClass} disabled />
                <p className="mt-1 text-xs text-brand-mid">{t('pages.settings.hint_website_soon')}</p>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_support_email')}</label>
                <input type="email" name="support_email" defaultValue={appSettings?.support_email ?? ''} className={inputClass} disabled />
                <p className="mt-1 text-xs text-brand-mid">{t('pages.settings.hint_email_soon')}</p>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_default_language')}</label>
                <select name="default_language" defaultValue={appSettings?.default_language ?? 'PT'} className={inputClass}>
                  <option value="PT">Português</option>
                  <option value="EN">English</option>
                </select>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_default_timezone')}</label>
                <select name="default_timezone" defaultValue={appSettings?.default_timezone ?? 'UTC'} className={inputClass} disabled>
                  <option value="UTC">UTC</option>
                  <option value="Europe/Lisbon">Europe/Lisbon</option>
                  <option value="Europe/London">Europe/London</option>
                  <option value="Europe/Paris">Europe/Paris</option>
                </select>
                <p className="mt-1 text-xs text-brand-mid">{t('pages.settings.hint_timezone_soon')}</p>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_currency')}</label>
                <input type="text" name="currency" maxLength={3} placeholder={t('pages.settings.placeholder_currency')} defaultValue={appSettings?.currency ?? ''} className={inputClass} disabled />
                <p className="mt-1 text-xs text-brand-mid">{t('pages.settings.hint_currency_soon')}</p>
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div className="flex items-center gap-3">
                <label className="flex cursor-pointer items-center gap-3">
                  <span className="relative inline-flex items-center">
                    <input type="checkbox" name="csv_enabled" value="1" defaultChecked={appSettings?.csv_enabled === true || appSettings?.csv_enabled === '1'} className="peer sr-only" />
                    <div className="h-5 w-9 rounded-full bg-brand-mid/30 transition-colors peer-checked:bg-brand-accent">
                      <div className="h-4 w-4 translate-x-0.5 translate-y-0.5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-full" />
                    </div>
                  </span>
                  <span className="text-xs font-bold text-brand-mid">{t('pages.settings.label_csv_export')}</span>
                </label>
              </div>
              <div className="flex items-center gap-3">
                <label className="flex cursor-pointer items-center gap-3">
                  <span className="relative inline-flex items-center">
                    <input type="checkbox" name="user_registration_enabled" value="1" defaultChecked={appSettings?.user_registration_enabled === true || appSettings?.user_registration_enabled === '1'} className="peer sr-only" />
                    <div className="h-5 w-9 rounded-full bg-brand-mid/30 transition-colors peer-checked:bg-brand-accent">
                      <div className="h-4 w-4 translate-x-0.5 translate-y-0.5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-full" />
                    </div>
                  </span>
                  <span className="text-xs font-bold text-brand-mid">{t('pages.settings.label_allow_registration')}</span>
                </label>
              </div>
              <div className="pt-2 text-right">
                <button type="submit" className="rounded-lg bg-brand-accent px-5 py-2 text-sm font-medium text-brand-white shadow-sm hover:bg-brand-accent/90 transition-colors">{t('pages.settings.btn_save')}</button>
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
          <div className="rounded-xl border border-red-200 bg-red-50 p-5">
            <p className="mb-3 text-sm text-brand-darkest">
              {t('pages.settings.delete_instruction', { text: expectedText })}
            </p>
            <div className="max-w-md space-y-4">
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_confirmation_text')}</label>
                <input type="text" value={confirmText} onChange={(e) => setConfirmText(e.target.value)} className="block w-full rounded-lg border border-red-300 bg-brand-white px-3 py-2 text-sm shadow-none focus:border-red-500 focus:ring-red-500" placeholder={t('pages.settings.placeholder_confirmation')} />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-bold text-brand-mid">{t('pages.settings.label_confirm_password_account')}</label>
                <input type="password" value={confirmPassword} onChange={(e) => setConfirmPassword(e.target.value)} className="block w-full rounded-lg border border-red-300 bg-brand-white px-3 py-2 text-sm shadow-none focus:border-red-500 focus:ring-red-500" />
                <div className="form-feedback mt-1 hidden text-xs text-red-600" />
              </div>
              <div className="flex items-center justify-end gap-2 pt-2">
                <button type="button" onClick={() => { setConfirmText(''); setConfirmPassword(''); }} className="rounded-lg border border-brand-mid/20 bg-brand-white px-4 py-2 text-sm font-medium text-brand-mid hover:bg-brand-light transition-colors">
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

  const fullName = user.full_name ?? `${user.first_name} ${user.last_name}`;

  return (
    <AppLayout title={t('pages.settings.page_title')}>

      <div className="h-full overflow-y-auto w-full bg-gray-50">
        <div className="max-w-7xl mx-auto py-8 px-4 sm:px-6">

          {/* Page Header */}
          <div className="mb-6">
            <h1 className="text-2xl font-bold text-brand-darkest">{t('pages.settings.page_heading')}</h1>
            <p className="text-xs text-brand-mid">{t('pages.settings.page_subtitle')}</p>
          </div>

          {/* ── 2-column grid ── */}
          <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {/* ── Left: Identity card ── */}
            <div className="lg:col-span-1">
              <div className="rounded-2xl border border-gray-100 bg-white shadow-sm p-6 flex flex-col items-center text-center gap-4 sticky top-6">
                <UserAvatar name={fullName} />

                <div className="flex flex-col gap-0.5">
                  <h2 className="text-lg font-bold text-gray-900">{fullName}</h2>
                  <p className="text-sm text-gray-500">{user.email}</p>
                </div>

                {user.roles && user.roles.length > 0 && (
                  <div className="flex flex-wrap justify-center gap-1.5">
                    {user.roles.map((role) => (
                      <Badge key={role} variant={ROLE_VARIANT[role] ?? 'secondary'} pill>
                        {role}
                      </Badge>
                    ))}
                  </div>
                )}

                <div className="w-full border-t border-gray-100 pt-4">
                  <p className="mb-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                    {t('pages.profile.section_stats')}
                  </p>
                  <div className="flex flex-col gap-2">
                    <StatCard label={t('pages.profile.label_permissions')} value={user.permissions_count ?? 0} icon="🔑" />
                    <StatCard label={t('pages.profile.label_member_since')} value={formatDate(user.created_at)} icon="📅" />
                  </div>
                </div>
              </div>
            </div>

            {/* ── Right: Forms ── */}
            <div className="lg:col-span-2">
              <Tabs tabs={tabs} defaultTab="details" />
            </div>

          </div>
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
        <p className="text-sm font-semibold text-red-600">{t('pages.settings.modal_confirm_warning')}</p>
        <p className="mb-3 text-sm text-brand-mid">
          {t('pages.settings.modal_confirm_instruction', { text: expectedText })}
        </p>
        <input
          type="text"
          value={confirmText}
          onChange={(e) => setConfirmText(e.target.value)}
          className="block w-full rounded-lg border border-red-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500"
          placeholder={t('pages.settings.placeholder_confirmation')}
        />
        <label className="mt-2 block text-xs font-bold text-brand-mid">{t('pages.settings.label_confirm_password_account')}</label>
        <input
          type="password"
          value={confirmPassword}
          onChange={(e) => setConfirmPassword(e.target.value)}
          className="block w-full rounded-lg border border-red-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500"
        />
      </DialogModal>
    </AppLayout>
  );
}
