/**
 * Minimal frontend i18n helper.
 * Uses Vite's `import.meta.glob` with `eager: true` to load JSON translation
 * files at build time — no async loading, no race conditions.
 *
 * Usage:
 *   import { t } from '@/utils/i18n';
 *   t('pages.sidebar.dashboard');
 *   t('pages.sidebar.greeting', { name: 'João' });
 */

const dictionaries = import.meta.glob('/resources/lang/**/*.json', {
  eager: true,
  import: 'default',
});

const currentLocale = /** @type {string} */ (window.__LOCALE__ || 'pt_PT');

/**
 * All loaded dictionaries grouped by locale, then by filename.
 * e.g. { en: { pages: { sidebar: { ... } } }, pt_PT: { ... } }
 * @type {Record<string, Record<string, any>>}
 */
const store = {};

for (const [path, mod] of Object.entries(dictionaries)) {
  const parts = path.replace(/\\/g, '/').split('/');
  const localeIdx = parts.indexOf('lang') + 1;
  if (localeIdx === 0 || localeIdx >= parts.length) continue;
  const locale = parts[localeIdx];
  const filename = parts[parts.length - 1].replace(/\.json$/, '');
  if (!store[locale]) store[locale] = {};
  store[locale][filename] = mod;
}

/**
 * Translate a dot-notation key like 'pages.sidebar.dashboard'.
 * Falls back to the key itself if not found.
 *
 * @param {string} key - Dot-notation path
 * @param {Record<string, string>} [replacements] - Optional `:param` replacements
 * @returns {string}
 */
export function t(key, replacements = {}) {
  const parts = key.split('.');
  let val = store[currentLocale];

  for (const part of parts) {
    if (val && typeof val === 'object' && part in val) {
      val = val[part];
    } else {
      return key;
    }
  }

  if (typeof val !== 'string') return key;
  return val.replace(/:(\w+)/g, (_, p) => replacements[p] ?? `:${p}`);
}

export { currentLocale };
