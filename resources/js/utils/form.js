import { t } from '@/utils/i18n';

/**
 * submitForm — AJAX form submission with button loading state, error feedback, and cleanup.
 * @param {HTMLFormElement} formEl
 * @param {string} endpoint
 * @returns {Promise<{ok: boolean, message: string}>}
 */
export async function submitForm(formEl, endpoint) {
  const submitBtn = formEl.querySelector('[type="submit"]');
  const orig = submitBtn?.textContent;
  if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = t('pages.settings.btn_saving'); }

  try {
    const fd = new FormData(formEl);
    const res = await fetch(endpoint, {
      method: 'POST',
      credentials: 'include',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    const data = await res.json();

    // Clear previous validation feedback
    formEl.querySelectorAll('.form-feedback').forEach((el) => {
      el.classList.remove('show', 'is-valid', 'is-invalid');
      el.textContent = '';
    });
    formEl.querySelectorAll('input, select').forEach((el) => el.classList.remove('is-invalid'));

    if (res.ok) {
      return { ok: true, message: data.message ?? t('pages.settings.success_updated') };
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
      return { ok: false, message: t('pages.settings.error_check_form') };
    }
    return { ok: false, message: data.error ?? t('pages.settings.error_generic') };
  } catch {
    return { ok: false, message: t('pages.settings.error_try_again') };
  } finally {
    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = orig ?? t('pages.settings.btn_save'); }
  }
}
