import { useState, useCallback } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { t } from '@/utils/i18n';
import { Shield } from 'lucide-react';
import RolePalette from '@/Components/RolePalette';

export default function SelectRole({ availableRoles }) {
  const { props: { auth } } = usePage();
  const userName = auth?.user ? `${auth.user.first_name} ${auth.user.last_name}` : null;

  // Palette auto-opens on mount for login-time selection
  const [paletteOpen, setPaletteOpen] = useState(true);

  const handleSelect = useCallback((roleName) => {
    router.post('/select-role', { role: roleName });
  }, []);

  const handleClose = useCallback(() => {
    setPaletteOpen(false);
  }, []);

  return (
    <>
      <Head title={t('pages.select_role.title')} />

      {/* ── Background page ─────────────────────────────── */}
      <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-brand-light to-brand-light px-3 py-8">
        <div className="w-full max-w-sm text-center">
          <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-accent to-brand-accent/80 text-brand-white shadow-lg">
            <Shield className="h-8 w-8" />
          </div>
          {userName && (
            <p className="mb-1 text-sm font-medium text-brand-mid">
              {userName}
            </p>
          )}
          <h1 className="text-2xl font-bold text-brand-darkest">
            {t('pages.select_role.heading')}
          </h1>
          <p className="mt-1.5 text-sm text-brand-mid">
            {t('pages.select_role.description')}
          </p>

          {/* Re-open trigger if palette was dismissed */}
          {!paletteOpen && (
            <button
              type="button"
              onClick={() => setPaletteOpen(true)}
              className="mt-6 inline-flex items-center gap-2 rounded-xl bg-brand-accent px-5 py-2.5 text-sm font-semibold text-brand-white shadow-sm transition-colors hover:bg-brand-accent/90"
            >
              Selecionar função
            </button>
          )}
        </div>
      </div>

      {/* ── Command palette (auto-open) ──────────────────── */}
      <RolePalette
        open={paletteOpen}
        onClose={handleClose}
        onSelect={handleSelect}
        onRequestOpen={() => setPaletteOpen(true)}
        availableRoles={availableRoles}
        userName={userName}
      />
    </>
  );
}
