/**
 * Enum label helpers — centralizes all backend enum → PT-PT display label mappings.
 * Used in badges, drawers, kanban cards and anywhere raw enum strings appear in the UI.
 */

export const STATUS_LABELS = {
    // ServiceOrder / Task / MiniTask / WorkLog
    pending:     'Pendente',
    in_progress: 'Em Progresso',
    completed:   'Concluído',
    cancelled:   'Cancelado',
    canceled:    'Cancelado',
    done:        'Concluído',
    finished:    'Concluído',
    // WorkLog specific
    approved:    'Aprovado',
    rejected:    'Rejeitado',
    // Equipment
    active:              'Ativo',
    in_use:              'Em Uso',
    maintenance_pending: 'Manutenção Pendente',
    under_maintenance:   'Em Manutenção',
    broken:              'Avariado',
    under_repair:        'Em Reparação',
    inactive:            'Inativo',
    retired:             'Retirado',
    // User
    enabled:  'Ativo',
    disabled: 'Inativo',
};

export const PRIORITY_LABELS = {
    low:    'Baixa',
    normal: 'Normal',
    high:   'Alta',
    urgent: 'Urgente',
};

export const WORKFLOW_LABELS = {
    standard: 'Padrão',
    loan:     'Empréstimo',
};

/**
 * Semantic badge colour palette — single source of truth for Tailwind v4 badge styles.
 * Map business values to a variant, then change colours in one place.
 */
const BADGE_VARIANT = {
  success: 'bg-green-500/20 text-green-300',
  warning: 'bg-yellow-500/20 text-yellow-300',
  danger:  'bg-red-500/20 text-red-300',
  info:    'bg-blue-500/20 text-blue-300',
  neutral: 'bg-brand-mid/20 text-brand-mid',
  urgent:  'bg-orange-500/20 text-orange-300',
  teal:    'bg-teal-500/20 text-teal-300',
};

/** Business value → semantic variant mapping */
const STATUS_VARIANT = {
  pending: 'warning', in_progress: 'info', completed: 'success',
  cancelled: 'danger', canceled: 'danger', done: 'success',
  finished: 'success', active: 'info',
  // Equipment statuses
  in_use: 'info', maintenance_pending: 'warning', under_maintenance: 'warning',
  broken: 'danger', under_repair: 'danger', inactive: 'neutral', retired: 'neutral',
  // User
  enabled: 'success', disabled: 'neutral',
};

const PRIORITY_VARIANT = {
  low: 'teal', normal: 'neutral', high: 'urgent', urgent: 'danger',
};

const CRITICAL_VALUES = new Set(['critical', 'urgent', 'high']);

/**
 * Returns Tailwind badge classes for any enum value (status, priority, etc.).
 * Falls back to 'neutral' (slate) if no mapping exists.
 */
export function badgeStyle(value, { border = false } = {}) {
  const key = value?.toLowerCase() ?? '';
  const variant =
    STATUS_VARIANT[key] ??
    PRIORITY_VARIANT[key] ??
    (CRITICAL_VALUES.has(key) ? 'danger' : 'neutral');
  const cls = BADGE_VARIANT[variant] ?? BADGE_VARIANT.neutral;
  return border ? `${cls} border border-${variant === 'danger' ? 'red' : variant === 'warning' ? 'yellow' : variant === 'info' ? 'blue' : variant === 'urgent' ? 'orange' : variant === 'teal' ? 'teal' : 'brand-mid'}-500/40` : cls;
}

/**
 * Returns the PT-PT label for any enum value, falling back to the raw value
 * capitalised if no mapping exists.
 */
export function labelFor(value) {
    if (!value) return '—';
    const key = String(value).toLowerCase();
    return (
        STATUS_LABELS[key] ??
        PRIORITY_LABELS[key] ??
        WORKFLOW_LABELS[key] ??
        String(value).replace(/_/g, ' ')
    );
}
